<?php
// rate.php - increment product rate (like) via AJAX POST
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';
session_start();

// Require login to rate
require_login();

$out = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode($out);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$token = $_POST['csrf'] ?? $_POST['token'] ?? '';

// basic validation
if ($id <= 0 || !validate_csrf($token)) {
    http_response_code(400);
    echo json_encode($out);
    exit;
}

// Prevent duplicate likes per session (simple approach)
if (!isset($_SESSION['liked_products']) || !is_array($_SESSION['liked_products'])) {
    $_SESSION['liked_products'] = [];
}
if (in_array($id, $_SESSION['liked_products'], true)) {
    // already liked by this session/user
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Already liked']);
    exit;
}

// Use transaction: increment then select
if (!$conn->begin_transaction()) {
    http_response_code(500);
    echo json_encode($out);
    exit;
}

try {
    $upd = $conn->prepare('UPDATE product SET rate = rate + 1 WHERE id = ?');
    if (!$upd) throw new Exception($conn->error);
    $upd->bind_param('i', $id);
    if (!$upd->execute()) throw new Exception($upd->error);

    $sel = $conn->prepare('SELECT rate FROM product WHERE id = ? LIMIT 1');
    if (!$sel) throw new Exception($conn->error);
    $sel->bind_param('i', $id);
    if (!$sel->execute()) throw new Exception($sel->error);
    $res = $sel->get_result();
    $row = $res->fetch_assoc();
    $newRate = isset($row['rate']) ? (int)$row['rate'] : null;

    $conn->commit();

    $out['success'] = true;
    $out['rate'] = $newRate;
    // mark as liked for this session/user
    $_SESSION['liked_products'][] = $id;
    echo json_encode($out);
    exit;
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    error_log('Rate error: ' . $e->getMessage());
    echo json_encode($out);
    exit;
}

?>