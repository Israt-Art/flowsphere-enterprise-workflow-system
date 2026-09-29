<?php
// GET /backend/api/workflow/hr_pending.php
// Documents waiting for HR review, plus the manager's earlier comment.

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');
require_role(['hr']);

$sql = "SELECT d.id, d.title, d.description, d.file_path, d.created_at, u.full_name AS submitted_by_name
        FROM documents d
        JOIN users u ON d.submitted_by = u.id
        WHERE d.current_status = 'pending_hr'
        ORDER BY d.created_at ASC";

$result = $conn->query($sql);

$documents = [];
while ($row = $result->fetch_assoc()) {

    // Separate query per document for the manager's approval - keeps
    // each query simple instead of one big multi-table join
    $stmt = $conn->prepare(
        "SELECT wh.comment, wh.action_date, u.full_name AS manager_name
         FROM workflow_history wh
         JOIN users u ON wh.action_by = u.id
         WHERE wh.document_id = ? AND wh.role_at_time = 'manager' AND wh.action = 'approved'
         ORDER BY wh.action_date DESC
         LIMIT 1"
    );
    $stmt->bind_param("i", $row['id']);
    $stmt->execute();
    $manager_action = $stmt->get_result()->fetch_assoc();

    $row['manager_name'] = $manager_action['manager_name'] ?? null;
    $row['manager_comment'] = $manager_action['comment'] ?? null;

    $documents[] = $row;
}

echo json_encode(["success" => true, "documents" => $documents]);
