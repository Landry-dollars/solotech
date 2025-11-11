<?php
// save_order.php
// Accepts cart and validates items against products table, requires login, stores order and order_items into database.

header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';
session_start();
// load config (admin email, SMTP)
$config = @include __DIR__ . '/config.php';
if (!is_array($config)) $config = [];

// Require login so orders are associated with users
require_login();

$current_user_id = $_SESSION['user_id'] ?? null;

$resp = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode($resp);
    exit;
}

$token = $_POST['csrf'] ?? '';
if (!validate_csrf($token)) {
    http_response_code(400);
    echo json_encode($resp);
    exit;
}

$cart_raw = $_POST['cart'] ?? '';
$total = $_POST['total'] ?? '';

if (empty($cart_raw)) {
    http_response_code(400);
    echo json_encode($resp);
    exit;
}

$cart = json_decode($cart_raw, true);
if (!is_array($cart)) {
    http_response_code(400);
    echo json_encode($resp);
    exit;
}

// create tables if they do not exist (simple migration)
$createOrders = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    total_price DECIMAL(12,2) NOT NULL,
    message TEXT,
    status VARCHAR(20) DEFAULT 'pending',
    raw_cart JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$createItems = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    quantity INT NOT NULL,
    image VARCHAR(255),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$conn->query($createOrders);
$conn->query($createItems);

// Validate cart items against products table and compute server-side total
$calc_total = 0.0;
$validated_items = [];

// Ensure product has a stock column (add if missing)
$colRes = $conn->query("SHOW COLUMNS FROM product LIKE 'stock'");
if (! $colRes) {
    // If table doesn't exist or query failed, return error
    http_response_code(500);
    echo json_encode(array_merge($resp, ['error' => 'db', 'message' => 'Product table missing or inaccessible']));
    exit;
}
if ($colRes->num_rows === 0) {
    // add stock column default 0 (best-effort)
    $conn->query("ALTER TABLE product ADD COLUMN stock INT DEFAULT 0");
}

$prodStmt = $conn->prepare('SELECT id, name, price, image, stock FROM product WHERE id = ? LIMIT 1');
if (!$prodStmt) {
    error_log('prepare select product failed: ' . $conn->error);
    http_response_code(500);
    echo json_encode(array_merge($resp, ['error' => 'db_prepare', 'message' => 'Database error']));
    exit;
}

// Start transaction so we can atomically insert order and decrement stock
if (! $conn->begin_transaction()) {
    error_log('begin transaction failed: ' . $conn->error);
}

foreach ($cart as $it) {
    $pid = (int)($it['id'] ?? 0);
    $qty = max(1, (int)($it['quantity'] ?? 1));
    if ($pid <= 0) continue;
    $prodStmt->bind_param('i', $pid);
    if (!$prodStmt->execute()) {
        // skip this item but continue validating others
        continue;
    }
    $res = $prodStmt->get_result();
    $row = $res->fetch_assoc();
    if (!$row) continue; // product not found -> skip
    $price = (float)$row['price'];
    $name = $row['name'];
    $image = $row['image'];
    $stock = isset($row['stock']) ? (int)$row['stock'] : 0;

    // Check stock
    if ($stock < $qty) {
        // Rollback and return clear JSON error
        $conn->rollback();
        http_response_code(400);
        echo json_encode(array_merge($resp, [
            'error' => 'insufficient_stock',
            'message' => "Insufficient stock for product",
            'product' => ['id' => $pid, 'name' => $name, 'available' => $stock]
        ]));
        exit;
    }

    $calc_total += $price * $qty;
    $validated_items[] = [
        'product_id' => $pid,
        'product_name' => $name,
        'price' => $price,
        'quantity' => $qty,
        'image' => $image,
        'stock_before' => $stock
    ];
}

// If no valid items, abort
if (empty($validated_items)) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(array_merge($resp, ['error' => 'empty_cart', 'message' => 'No valid items in cart']));
    exit;
}

// Insert order using server computed total and associate with current user
$stmt = $conn->prepare('INSERT INTO orders (user_id, total_price, message, raw_cart, status) VALUES (?, ?, ?, ?, ?)');
if (!$stmt) {
    error_log('prepare orders failed: ' . $conn->error);
    $conn->rollback();
    http_response_code(500);
    echo json_encode(array_merge($resp, ['error' => 'db_prepare', 'message' => 'Failed to prepare order insert']));
    exit;
}

$message = $_POST['message'] ?? '';
$raw_cart_json = json_encode($validated_items, JSON_UNESCAPED_UNICODE);
$user_id_param = $current_user_id ? (int)$current_user_id : 0; // 0 = guest
$status = 'pending';
$stmt->bind_param('idsss', $user_id_param, $calc_total, $message, $raw_cart_json, $status);
if (!$stmt->execute()) {
    error_log('execute orders failed: ' . $stmt->error);
    $conn->rollback();
    http_response_code(500);
    echo json_encode(array_merge($resp, ['error' => 'db_execute', 'message' => 'Failed to insert order']));
    exit;
}

$order_id = $stmt->insert_id;
$stmt->close();

// Insert items and decrement stock
$itemStmt = $conn->prepare('INSERT INTO order_items (order_id, product_id, product_name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)');
$stockUpd = $conn->prepare('UPDATE product SET stock = stock - ? WHERE id = ?');
if (! $itemStmt || ! $stockUpd) {
    error_log('prepare item/stock statements failed: ' . $conn->error);
    $conn->rollback();
    http_response_code(500);
    echo json_encode(array_merge($resp, ['error' => 'db_prepare', 'message' => 'Failed to prepare item/stock statements']));
    exit;
}

foreach ($validated_items as $it) {
    $pname = substr((string)$it['product_name'], 0, 255);
    $pprice = (float)$it['price'];
    $pqty = (int)$it['quantity'];
    $pimage = substr((string)$it['image'], 0, 255);
    $ppid = (int)$it['product_id'];

    $itemStmt->bind_param('iisdis', $order_id, $ppid, $pname, $pprice, $pqty, $pimage);
    if (!$itemStmt->execute()) {
        error_log('execute order_items failed: ' . $itemStmt->error);
        // try to rollback and return error
        $conn->rollback();
        http_response_code(500);
        echo json_encode(array_merge($resp, ['error' => 'db_execute', 'message' => 'Failed to insert order items']));
        exit;
    }

    // decrement stock
    $stockUpd->bind_param('ii', $pqty, $ppid);
    if (!$stockUpd->execute()) {
        error_log('stock update failed: ' . $stockUpd->error);
        $conn->rollback();
        http_response_code(500);
        echo json_encode(array_merge($resp, ['error' => 'db_execute', 'message' => 'Failed to update stock']));
        exit;
    }
}

$itemStmt->close();
$stockUpd->close();

// Commit transaction
if (! $conn->commit()) {
    error_log('commit failed: ' . $conn->error);
    $conn->rollback();
    http_response_code(500);
    echo json_encode(array_merge($resp, ['error' => 'db_commit', 'message' => 'Failed to commit order']));
    exit;
}

// Try to send sample order email to admin and to the user (best-effort)
$mail_admin_sent = false;
$mail_user_sent = false;

// Build a simple HTML/plain text representation of the order
$order_lines_html = '';
$order_lines_plain = "";
foreach ($validated_items as $it) {
    $line = htmlspecialchars($it['product_name']) . ' x ' . (int)$it['quantity'] . ' @ ' . number_format($it['price'], 2) . " FCFA";
    $order_lines_plain .= $line . "\n";
    $order_lines_html .= '<tr>' .
        '<td>' . htmlspecialchars($it['product_name']) . '</td>' .
        '<td style="text-align:center">' . (int)$it['quantity'] . '</td>' .
        '<td style="text-align:right">' . number_format($it['price'], 2) . ' FCFA</td>' .
        '<td style="text-align:right">' . number_format($it['price'] * $it['quantity'], 2) . ' FCFA</td>' .
        '</tr>';
}

$order_table_html = '<table border="0" cellpadding="6" cellspacing="0" style="width:100%; border-collapse: collapse;">'
    . '<thead><tr><th style="text-align:left">Product</th><th>Qty</th><th style="text-align:right">Unit</th><th style="text-align:right">Subtotal</th></tr></thead><tbody>'
    . $order_lines_html
    . '</tbody><tfoot><tr><td colspan="3" style="text-align:right"><strong>Total</strong></td><td style="text-align:right"><strong>' . number_format($calc_total, 2) . ' FCFA</strong></td></tr></tfoot></table>';

$admin_email = 'SoloTech@gmail.com'; // default admin contact - adjust if you have a settings table

// Fetch user email if available
$user_email = '';
if ($user_id_param > 0) {
    $uStmt = $conn->prepare('SELECT email, name FROM user WHERE id = ? LIMIT 1');
    if ($uStmt) {
        $uStmt->bind_param('i', $user_id_param);
        if ($uStmt->execute()) {
            $uRes = $uStmt->get_result();
            $uRow = $uRes ? $uRes->fetch_assoc() : null;
            if ($uRow && !empty($uRow['email'])) {
                $user_email = $uRow['email'];
            }
        }
        $uStmt->close();
    }
}

// Prepare email bodies
$subject_admin = "New order #{$order_id} received";
$subject_user = "Order confirmation - #{$order_id} - SoloTech";

$plain_body = "Order #{$order_id}\nTotal: " . number_format($calc_total, 2) . " FCFA\n\n" . $order_lines_plain . "\nMessage: " . ($message ?: '(none)') . "\n";

$html_body = '<html><body>' .
    '<h2>New Order #' . $order_id . '</h2>' .
    $order_table_html .
    '<p><strong>Message:</strong> ' . nl2br(htmlspecialchars($message)) . '</p>' .
    '<p>Order ID: ' . $order_id . '<br>Total: ' . number_format($calc_total, 2) . ' FCFA</p>' .
    '</body></html>';

// Try to send emails via PHPMailer (preferred). If PHPMailer is not installed, fall back to mail().
$from_email = $config['from_email'] ?? 'no-reply@localhost';
$from_name = $config['from_name'] ?? 'SoloTech';
$admin_email = $config['admin_email'] ?? $admin_email;

// PHPMailer path via Composer autoload if present
$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once $composerAutoload;
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        // SMTP config if provided
        if (!empty($config['smtp']['host'])) {
            $mail->isSMTP();
            $mail->Host = $config['smtp']['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp']['username'] ?? '';
            $mail->Password = $config['smtp']['password'] ?? '';
            $mail->SMTPSecure = $config['smtp']['secure'] ?? '';
            $mail->Port = $config['smtp']['port'] ?? 587;
        }

        $mail->setFrom($from_email, $from_name);
        // send to admin
        $mail->addAddress($admin_email);
        $mail->Subject = $subject_admin;
        $mail->isHTML(true);
        $mail->Body = $html_body;
        $mail->AltBody = $plain_body;
        $mail_admin_sent = $mail->send();

        // send to user if available
        if (!empty($user_email)) {
            $mail->clearAllRecipients();
            $mail->addAddress($user_email);
            $mail->Subject = $subject_user;
            $mail->Body = $html_body;
            $mail->AltBody = $plain_body;
            $mail_user_sent = $mail->send();
        }
    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $e->getMessage());
        // fallback to mail()
        $mail_admin_sent = false;
        $mail_user_sent = false;
    }
} else {
    // fallback: use mail() with basic headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$from_name} <{$from_email}>\r\n";
    try {
        $mail_admin_sent = @mail($admin_email, $subject_admin, $html_body, $headers);
    } catch (Exception $e) {
        error_log('mail to admin failed: ' . $e->getMessage());
        $mail_admin_sent = false;
    }

    if (!empty($user_email)) {
        try {
            $mail_user_sent = @mail($user_email, $subject_user, $html_body, $headers);
        } catch (Exception $e) {
            error_log('mail to user failed: ' . $e->getMessage());
            $mail_user_sent = false;
        }
    }
}

echo json_encode(['success' => true, 'order_id' => $order_id, 'total' => $calc_total, 'mail_admin' => $mail_admin_sent, 'mail_user' => $mail_user_sent]);
exit;

?>
