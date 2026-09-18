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
    Response::error('You are not authorized to view this document.', 403);
}

Response::success('Document loaded.', WorkflowService::formatDocument($document));
