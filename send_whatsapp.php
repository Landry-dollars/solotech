<?php
// send_whatsapp.php
// Server-side helper to send a WhatsApp message using the WhatsApp Business Cloud API.
// IMPORTANT: You must configure the environment variables or edit the config below with
// your WhatsApp Business Phone Number ID and a permanent access token.

header('Content-Type: application/json');
require_once __DIR__ . '/csrf.php';
session_start();

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

$message = trim($_POST['message'] ?? '');
if ($message === '') {
    http_response_code(400);
    echo json_encode($resp);
    exit;
}

// Configuration: prefer environment variables
$WHATSAPP_TOKEN = getenv('WHATSAPP_TOKEN') ?: '';
$WHATSAPP_PHONE_ID = getenv('WHATSAPP_PHONE_ID') ?: '';
$STORE_NUMBER = getenv('WHATSAPP_STORE_NUMBER') ?: '237672738066'; // recipient (store) number in international format without +

// If not configured, return explicit code so client can fallback to wa.me link
if (empty($WHATSAPP_TOKEN) || empty($WHATSAPP_PHONE_ID)) {
    echo json_encode(['success' => false, 'reason' => 'not_configured']);
    exit;
}

$url = 'https://graph.facebook.com/v15.0/' . rawurlencode($WHATSAPP_PHONE_ID) . '/messages';

$payload = [
    'messaging_product' => 'whatsapp',
    'to' => $STORE_NUMBER,
    'type' => 'text',
    'text' => ['body' => $message]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $WHATSAPP_TOKEN,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$result = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

if ($result === false || $httpcode >= 400) {
    error_log('WhatsApp API error: ' . $err . ' resp: ' . $result);
    http_response_code(500);
    echo json_encode(['success' => false, 'reason' => 'api_error', 'http' => $httpcode, 'resp' => $result]);
    exit;
}

echo json_encode(['success' => true, 'resp' => json_decode($result, true)]);
exit;

?>
