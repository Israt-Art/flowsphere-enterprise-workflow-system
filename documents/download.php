<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Method not allowed.', 405);
}

$user = Auth::requireUser();
$documentId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($documentId < 1) {
    Response::error('Document id is required.', 422);
}

$document = WorkflowService::findDocument($documentId);
if (!$document) {
    Response::error('Document not found.', 404);
}

if (!WorkflowService::canViewDocument($user, $document)) {
    Response::error('You are not authorized to download this attachment.', 403);
}

if (empty($document['attachment_path'])) {
    Response::error('This document has no attachment.', 404);
}

$fullPath = dirname(__DIR__, 2) . '/uploads/' . basename($document['attachment_path']);
if (!is_file($fullPath)) {
    Response::error('Attachment file is missing.', 404);
}

$downloadName = $document['attachment_original_name']
    ? $document['attachment_original_name']
    : basename($fullPath);

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . str_replace('"', '', $downloadName) . '"');
header('Content-Length: ' . filesize($fullPath));
header('X-Content-Type-Options: nosniff');
readfile($fullPath);
exit;
