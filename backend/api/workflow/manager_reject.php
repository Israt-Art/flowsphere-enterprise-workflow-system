<?php
// POST /backend/api/workflow/manager_reject.php
// Body: { "document_id": 5, "comment": "reason" }
// Comment is required, and validated here too - never trust only the frontend.

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');
require_role(['manager']);

$data = json_decode(file_get_contents("php://input"), true);
$document_id = (int)($data['document_id'] ?? 0);
$comment = trim($data['comment'] ?? '');
$manager_id = $_SESSION['user_id'];

if ($document_id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid document id"]);
    exit;
}

if ($comment === '') {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "A comment is required when rejecting"]);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE documents
     SET current_status = 'rejected'
     WHERE id = ? AND current_status = 'pending_manager'"
);
$stmt->bind_param("i", $document_id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "This document is no longer waiting for manager review"]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO workflow_history (document_id, action_by, role_at_time, action, comment)
     VALUES (?, ?, 'manager', 'rejected', ?)"
);
$stmt->bind_param("iis", $document_id, $manager_id, $comment);
$stmt->execute();

echo json_encode(["success" => true, "message" => "Document rejected"]);
