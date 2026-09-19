<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Method not allowed.', 405);
}

$user = Auth::requireUser();
$documentId = isset($_GET['document_id']) ? (int) $_GET['document_id'] : 0;

if ($documentId < 1) {
    Response::error('Document id is required.', 422);
}

$payload = WorkflowService::history($user, $documentId);
Response::success('Workflow history loaded.', $payload);
