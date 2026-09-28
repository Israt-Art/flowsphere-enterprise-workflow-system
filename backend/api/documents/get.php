<?php
// GET /backend/api/documents/get.php?id=5
// One document's full detail + its workflow history.
// An employee may only view documents they submitted themselves.

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');
require_role(['employee']);

$document_id = (int)($_GET['id'] ?? 0);
$employee_id = $_SESSION['user_id'];

if ($document_id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid document id"]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, title, description, file_path, current_status, created_at
     FROM documents
     WHERE id = ? AND submitted_by = ?"
);
$stmt->bind_param("ii", $document_id, $employee_id);
$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();

if (!$document) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Document not found"]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT wh.action, wh.role_at_time, wh.comment, wh.action_date, u.full_name
     FROM workflow_history wh
     JOIN users u ON wh.action_by = u.id
     WHERE wh.document_id = ?
     ORDER BY wh.action_date ASC"
);
$stmt->bind_param("i", $document_id);
$stmt->execute();
$history_result = $stmt->get_result();

$history = [];
while ($row = $history_result->fetch_assoc()) {
    $history[] = $row;
}

echo json_encode(["success" => true, "document" => $document, "history" => $history]);
