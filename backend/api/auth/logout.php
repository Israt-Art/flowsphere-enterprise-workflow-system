<?php
// ============================================================
// POST /backend/api/auth/logout.php
// ============================================================
// Destroying a session = clearing $_SESSION and removing the
// session cookie so the browser is no longer "recognized".
// ============================================================

session_start();
header('Content-Type: application/json');

$_SESSION = [];          // clear all session data
session_destroy();       // tell PHP to delete the session on the server

echo json_encode([
    "success" => true,
    "message" => "Logged out"
]);
