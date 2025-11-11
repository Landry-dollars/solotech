<?php
// actions.php - centralized handler for admin actions
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db.php';

require_login();

// Simple helper to redirect back to admin with a status
function redirect_back($params = []) {
    $qs = http_build_query($params);
    header('Location: dashboard.php' . ($qs ? "?" . $qs : ''));
    exit;
}

// Support both POST (forms) and GET (links) for actions
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $token = $_POST['csrf'] ?? $_POST['token'] ?? '';
} else {
    $action = $_GET['action'] ?? '';
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $token = $_GET['token'] ?? $_GET['csrf'] ?? '';
}

// Validate CSRF token for all actions
if (!validate_csrf($token)) {
    redirect_back(['error' => 'csrf']);
}

switch($action) {
    case 'delete':
    case 'remove':
        if ($id > 0) {
            // Fetch product to delete image file
            $stmt = $conn->prepare('SELECT image FROM product WHERE id = ?');
            if (!$stmt) redirect_back(['error' => 'db']);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;

            // Delete DB row
            $del = $conn->prepare('DELETE FROM product WHERE id = ?');
            if (!$del) redirect_back(['error' => 'db']);
            $del->bind_param('i', $id);
            if ($del->execute()) {
                // Remove image file if exists
                if (!empty($row['image'])) {
                    $path = __DIR__ . '/uploads/' . $row['image'];
                    if (is_file($path)) @unlink($path);
                }
                redirect_back(['deleted' => 1]);
            } else {
                redirect_back(['error' => 'delete']);
            }
        }
        break;

    case 'edit':
        if ($id > 0) {
            $type = $_GET['type'] ?? 'product';
            header("Location: edit_form.php?id=$id&type=$type&token=$token");
            exit;
        }
        break;

    case 'update_product':
        if ($id > 0) {
            $name = trim($_POST['name'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = (float)($_POST['price'] ?? 0);

            if (!$name || !$category || !$description || $price <= 0) {
                redirect_back(['error' => 'missing_fields']);
            }

            // Handle image upload if present
            $image_update = '';
            $params = [$name, $category, $description, $price, $id];
            $types = 'sssdi';

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/';
                $temp_name = $_FILES['image']['tmp_name'];
                $original_name = $_FILES['image']['name'];
                $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    redirect_back(['error' => 'invalid_file']);
                }

                $new_filename = uniqid() . '.' . $extension;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($temp_name, $upload_path)) {
                    $image_update = ', image = ?';
                    $params[] = $new_filename;
                    $types .= 's';
                }
            }

            $sql = "UPDATE product SET name = ?, category = ?, description = ?, price = ?" . $image_update . " WHERE id = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param($types, ...$params);
                if ($stmt->execute()) {
                    redirect_back(['success' => 'product_updated']);
                }
            }
            redirect_back(['error' => 'update_failed']);
        }
        break;

    case 'update_user':
        if ($id > 0) {
            $name = trim($_POST['name'] ?? '');
            $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
            $phone = trim($_POST['phone'] ?? '');

            if (!$name || !$email) {
                redirect_back(['error' => 'missing_fields']);
            }

            $stmt = $conn->prepare("UPDATE user SET name = ?, email = ?, phone = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('sssi', $name, $email, $phone, $id);
                if ($stmt->execute()) {
                    redirect_back(['success' => 'user_updated']);
                }
            }
            redirect_back(['error' => 'update_failed']);
        }
        break;

    case 'delete_message':
        if ($id > 0) {
            $stmt = $conn->prepare('DELETE FROM message WHERE id = ?');
            if (!$stmt) redirect_back(['error' => 'db']);
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                redirect_back(['success' => 'message_deleted']);
            } else {
                redirect_back(['error' => 'delete_failed']);
            }
        }
        break;

    default:
        redirect_back(['error' => 'invalid_action']);
}

?>