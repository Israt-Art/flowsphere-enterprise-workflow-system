<?php
// POST /backend/api/workflow/director_approve.php
// Final stage: 'pending_director' -> 'approved' (workflow complete)

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');
require_role(['director']);

$data = json_decode(file_get_contents("php://input"), true);
$document_id = (int)($data['document_id'] ?? 0);
$comment = trim($data['comment'] ?? '');
$director_id = $_SESSION['user_id'];

if ($document_id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid document id"]);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE documents
     SET current_status = 'approved'
     WHERE id = ? AND current_status = 'pending_director'"
);
$stmt->bind_param("i", $document_id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "This document is no longer waiting for Director review"]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO workflow_history (document_id, action_by, role_at_time, action, comment)
     VALUES (?, ?, 'director', 'approved', ?)"
);
$stmt->bind_param("iis", $document_id, $director_id, $comment);
$stmt->execute();

echo json_encode(["success" => true, "message" => "Document given final approval - workflow completed"]);
