<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db.php';

require_login();

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed']));
}

// Validate CSRF token
if (!validate_csrf($_POST['token'] ?? '')) {
    http_response_code(403);
    die(json_encode(['error' => 'Invalid token']));
}

$action = $_POST['action'] ?? '';
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($action === 'mark_message_status') {
    if ($id <= 0) {
        http_response_code(400);
        die(json_encode(['error' => 'Invalid message ID']));
    }

    $status = $_POST['status'] === 'read' ? 'read' : 'unread';
    
    $stmt = $conn->prepare('UPDATE message SET status = ? WHERE id = ?');
    if (!$stmt) {
        http_response_code(500);
        die(json_encode(['error' => 'Database error']));
    }
    
    $stmt->bind_param('si', $status, $id);
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Status updated successfully',
            'status' => $status,
            'id' => $id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update status']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
?>