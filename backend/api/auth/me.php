<?php
// GET /backend/api/auth/me.php
// Every dashboard calls this on load to check who's logged in - if it
// fails, the JS redirects back to login.html.

require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

echo json_encode([
    "success" => true,
    "user" => ["name" => $_SESSION['full_name'], "role" => $_SESSION['role']]
]);
