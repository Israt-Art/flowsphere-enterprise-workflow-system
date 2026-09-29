<?php
// GET /backend/api/workflow/director_pending.php
// Documents waiting for final review, with the FULL workflow history
// for each one (every action so far, not just the most recent step).

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');
require_role(['director']);

$sql = "SELECT d.id, d.title, d.description, d.file_path, d.created_at, u.full_name AS submitted_by_name
        FROM documents d
        JOIN users u ON d.submitted_by = u.id
        WHERE d.current_status = 'pending_director'
        ORDER BY d.created_at ASC";

$result = $conn->query($sql);

$documents = [];
while ($row = $result->fetch_assoc()) {

    $stmt = $conn->prepare(
        "SELECT wh.role_at_time, wh.action, wh.comment, wh.action_date, u.full_name AS actor_name
         FROM workflow_history wh
         JOIN users u ON wh.action_by = u.id
         WHERE wh.document_id = ?
         ORDER BY wh.action_date ASC"
    );
    $stmt->bind_param("i", $row['id']);
    $stmt->execute();
    $history_result = $stmt->get_result();

    $history = [];
    while ($h = $history_result->fetch_assoc()) {
        $history[] = $h;
    }

    $row['history'] = $history;
    $documents[] = $row;
}

echo json_encode(["success" => true, "documents" => $documents]);
