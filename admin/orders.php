<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';
require_login();

// Simple admin orders page with CSV export
header('X-Frame-Options: SAMEORIGIN');

// Ensure orders table has a status column (best-effort migration)
$col = $conn->query("SHOW COLUMNS FROM orders LIKE 'status'");
if ($col && $col->num_rows === 0) {
	$conn->query("ALTER TABLE orders ADD COLUMN status VARCHAR(20) DEFAULT 'pending'");
}

// Handle simple actions: mark as shipped
if (isset($_GET['action']) && $_GET['action'] === 'mark_shipped' && !empty($_GET['id'])) {
	$oid = (int)$_GET['id'];
	$u = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
	if ($u) {
		$status = 'shipped';
		$u->bind_param('si', $status, $oid);
		$u->execute();
		$u->close();
	}
	header('Location: orders.php');
	exit;
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
	// Export orders with items as CSV
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=orders_export_' . date('Ymd_His') . '.csv');
	$out = fopen('php://output', 'w');
	fputcsv($out, ['order_id', 'user_id', 'total_price', 'created_at', 'message', 'items_json']);

	$ordersRes = $conn->query('SELECT id, user_id, total_price, created_at, message, raw_cart FROM orders ORDER BY id DESC');
	while ($o = $ordersRes->fetch_assoc()) {
		$itemsJson = $o['raw_cart'];
		fputcsv($out, [$o['id'], $o['user_id'], $o['total_price'], $o['created_at'], $o['message'], $itemsJson]);
	}
	fclose($out);
	exit;
}

$orders = [];
$res = $conn->query('SELECT id, user_id, total_price, created_at, message, status FROM orders ORDER BY id DESC');
if ($res) {
	while ($r = $res->fetch_assoc()) $orders[] = $r;
}
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>Admin Orders</title>
	<link rel="stylesheet" href="/style.css">
</head>
<body>
<div class="container">
	<h1>Orders</h1>
	<p><a href="?export=csv">Export CSV</a></p>
	<table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; width:100%;">
		<thead>
			<tr>
				<th>Order ID</th>
				<th>User ID</th>
				<th>Total</th>
				<th>Created</th>
				<th>Message</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($orders as $o): ?>
			<tr>
				<td><?= htmlspecialchars($o['id']) ?></td>
				<td><?= htmlspecialchars($o['user_id']) ?></td>
				<td><?= htmlspecialchars($o['total_price']) ?></td>
				<td><?= htmlspecialchars($o['created_at']) ?></td>
				<td><?= htmlspecialchars(mb_strimwidth($o['message'] ?? '', 0, 80, '...')) ?></td>
				<td><a href="orders.php?id=<?= urlencode($o['id']) ?>">View</a></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<?php if (!empty($_GET['id'])):
		$oid = (int)$_GET['id'];
		$s = $conn->prepare('SELECT o.id,o.user_id,o.total_price,o.message,o.created_at, oi.product_id, oi.product_name, oi.price, oi.quantity, oi.image
			FROM orders o
			LEFT JOIN order_items oi ON oi.order_id = o.id
			WHERE o.id = ?');
		$s->bind_param('i', $oid);
		$s->execute();
		$res = $s->get_result();
		$rows = [];
		while ($r = $res->fetch_assoc()) $rows[] = $r;
		if ($rows):
	?>
		<h2>Order #<?= htmlspecialchars($oid) ?></h2>
		<p>User: <?= htmlspecialchars($rows[0]['user_id']) ?> — Total: <?= htmlspecialchars($rows[0]['total_price']) ?> — Created: <?= htmlspecialchars($rows[0]['created_at']) ?></p>
		<table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; width:100%;">
			<thead><tr><th>Product ID</th><th>Product</th><th>Price</th><th>Qty</th><th>Image</th></tr></thead>
			<tbody>
			<?php foreach ($rows as $it): ?>
				<tr>
					<td><?= htmlspecialchars($it['product_id']) ?></td>
					<td><?= htmlspecialchars($it['product_name']) ?></td>
					<td><?= htmlspecialchars($it['price']) ?></td>
					<td><?= htmlspecialchars($it['quantity']) ?></td>
					<td><?php if ($it['image']): ?><img src="/uploads/<?= htmlspecialchars($it['image']) ?>" style="height:40px; max-width:80px; object-fit:cover"><?php endif; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; endif; ?>

</div>
</body>
</html>
