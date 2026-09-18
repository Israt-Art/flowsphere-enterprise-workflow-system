<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed.', 405);
}

$body = read_json_body();
$email = isset($body['email']) ? strtolower(trim($body['email'])) : '';
$password = isset($body['password']) ? $body['password'] : '';

if ($email === '' || $password === '') {
    Response::error('Email and password are required.', 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Response::error('Enter a valid email address.', 422);
}

$pdo = Database::connection();
$statement = $pdo->prepare(
    'SELECT id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1'
);
$statement->execute(array($email));
$user = $statement->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    Response::error('Invalid email or password.', 401);
}

Auth::login($user);

Response::success('Login successful.', public_user($user));
