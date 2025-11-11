<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$category = trim((string)($_GET['category'] ?? ''));

$out = [];
if ($category !== '') {
    $stmt = $conn->prepare("SELECT id, name, category, price, image, rate, description FROM product WHERE category = ? ORDER BY id DESC");
    if ($stmt) {
        $stmt->bind_param('s', $category);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) $out[] = $r;
        $stmt->close();
    }
} else {
    $res = $conn->query("SELECT id, name, category, price, image, rate, description FROM product ORDER BY id DESC");
    if ($res) {
        while ($r = $res->fetch_assoc()) $out[] = $r;
    }
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);
exit;

?>
