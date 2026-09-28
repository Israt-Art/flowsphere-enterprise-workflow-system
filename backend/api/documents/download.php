<?php
// GET /backend/api/documents/download.php?id=5
// Employees may only download their own documents. Managers/HR/Director
// can download any document's attachment, since reviewing it is their job.

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

require_login();

$document_id = (int)($_GET['id'] ?? 0);
$role = $_SESSION['role'];

if ($role === 'employee') {
    $employee_id = $_SESSION['user_id'];
    $stmt = $conn->prepare(
        "SELECT file_path, title FROM documents WHERE id = ? AND submitted_by = ?"
    );
    $stmt->bind_param("ii", $document_id, $employee_id);
} else {
    $stmt = $conn->prepare(
        "SELECT file_path, title FROM documents WHERE id = ?"
    );
    $stmt->bind_param("i", $document_id);
}

$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();

if (!$document || !$document['file_path']) {
    http_response_code(404);
    die("File not found");
}

$file_path = __DIR__ . '/../../uploads/' . $document['file_path'];

if (!file_exists($file_path)) {
    http_response_code(404);
    die("File not found on server");
}

// Force the browser to download rather than try to render it inline
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
