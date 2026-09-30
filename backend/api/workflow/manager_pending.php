<?php
// GET /backend/api/workflow/manager_pending.php
// Documents waiting for manager review, with the submitter's name.

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');
require_role(['manager']);

$sql = "SELECT d.id, d.title, d.description, d.file_path, d.created_at, u.full_name AS submitted_by_name
        FROM documents d
        JOIN users u ON d.submitted_by = u.id
        WHERE d.current_status = 'pending_manager'
        ORDER BY d.created_at ASC";

$result = $conn->query($sql);

$documents = [];
while ($row = $result->fetch_assoc()) {
    $documents[] = $row;
}

echo json_encode(["success" => true, "documents" => $documents]);
