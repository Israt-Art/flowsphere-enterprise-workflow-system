<?php
// GET /backend/api/documents/list.php
// Returns the logged-in employee's own documents, newest first.

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');
require_role(['employee']);

$employee_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT id, title, current_status, created_at
     FROM documents
     WHERE submitted_by = ?
     ORDER BY created_at DESC"
);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

$documents = [];
while ($row = $result->fetch_assoc()) {
    $documents[] = $row;
}

echo json_encode(["success" => true, "documents" => $documents]);
