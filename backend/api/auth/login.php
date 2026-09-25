<?php
// ============================================================
// POST /backend/api/auth/login.php
// ============================================================
// Expects JSON body: { "email": "...", "password": "..." }
//
// What this file does, step by step:
//   1. Read the JSON the frontend sent
//   2. Make sure email + password aren't empty
//   3. Look up the user by email
//   4. Compare the password using password_verify()
//   5. If correct -> start a session, save user_id + role
//   6. Send back a JSON response
// ============================================================

require_once __DIR__ . '/../../config/database.php';

session_start();
header('Content-Type: application/json');

// Step 1: read the JSON body sent by fetch()
$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

// Step 2: basic validation
if ($email === '' || $password === '') {
    http_response_code(400); // 400 = "Bad Request"
    echo json_encode([
        "success" => false,
        "message" => "Email and password are required"
    ]);
    exit;
}

// Step 3: look up the user
// We use a PREPARED STATEMENT (the ? placeholder + bind_param) instead
// of pasting $email directly into the SQL string. This is what stops
// SQL injection: the database treats $email as pure data, never as
// part of the SQL command, no matter what the user types.
$stmt = $conn->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Step 4: verify password
// password_verify() compares the plain password the user typed against
// the bcrypt hash stored in the database. We NEVER store or compare
// plain text passwords.
if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password"
    ]);
    exit;
}

// Step 5: create the session
// regenerate_id() gives the session a fresh ID on every login,
// which helps prevent "session fixation" attacks.
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['role'] = $user['role'];

// Step 6: respond
echo json_encode([
    "success" => true,
    "message" => "Login successful",
    "user" => [
        "name" => $user['full_name'],
        "role" => $user['role']
    ]
]);
