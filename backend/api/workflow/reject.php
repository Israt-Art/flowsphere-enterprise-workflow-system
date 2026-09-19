<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed.', 405);
}

$user = Auth::requireRole(array('MANAGER', 'HR', 'DIRECTOR'));
$body = read_json_body();

$documentId = isset($body['document_id']) ? (int) $body['document_id'] : 0;
$comment = isset($body['comment']) ? $body['comment'] : '';

if ($documentId < 1) {
    Response::error('Document id is required.', 422);
}

$document = WorkflowService::reject($user, $documentId, $comment);

Response::success('Document rejected.', $document);
