<?php
// POST /backend/api/auth/login.php
// Expects JSON body: { "email": "...", "password": "..." }

require_once __DIR__ . '/../../config/database.php';

session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Email and password are required"]);
    exit;
}

// Prepared statement - $email is always treated as data, never as part of the SQL itself.
// This is what stops SQL injection.
$stmt = $conn->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// password_verify() checks the typed password against the stored bcrypt hash.
// We never store or compare plain text passwords.
if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid email or password"]);
    exit;
}

// New session ID on every login to prevent session fixation
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['role'] = $user['role'];

echo json_encode([
    "success" => true,
    "message" => "Login successful",
    "user" => ["name" => $user['full_name'], "role" => $user['role']]
]);
