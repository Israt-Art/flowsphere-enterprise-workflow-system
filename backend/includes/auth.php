<?php
// Shared auth checks, used by every protected API file so the same
// "are you logged in / are you the right role" logic isn't repeated everywhere.

function require_login() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401); // not logged in
        header('Content-Type: application/json');
        die(json_encode(["success" => false, "message" => "Not logged in"]));
    }
}

// Example: require_role(['manager']);
function require_role($allowed_roles) {
    require_login();

    if (!in_array($_SESSION['role'], $allowed_roles)) {
        http_response_code(403); // logged in, but wrong role
        header('Content-Type: application/json');
        die(json_encode(["success" => false, "message" => "You do not have permission to do this"]));
    }
}
