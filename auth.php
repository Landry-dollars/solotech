<?php
// Simple session-based auth helper
// Accepts either legacy 'admin_logged_in' or newer 'logged_in' session keys.
session_start();

function is_logged_in() {
    // Backwards compatible: some code sets 'admin_logged_in', others set 'logged_in'
    return !empty($_SESSION['admin_logged_in']) || !empty($_SESSION['logged_in']);
}

function require_login() {
    if (!is_logged_in()) {
        // Point to the actual login page (case matches file in repo)
        header('Location: Login.php');
        exit;
    }
}

?>
