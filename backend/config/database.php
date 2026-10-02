<?php
// Shared database connection - every backend file includes this and uses $conn

$DB_HOST = "localhost";
$DB_USER = "root";       // change if your XAMPP MySQL user is different
$DB_PASS = "";           // change if your XAMPP MySQL has a password set
$DB_NAME = "flowsphere";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    header('Content-Type: application/json');
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]));
}
