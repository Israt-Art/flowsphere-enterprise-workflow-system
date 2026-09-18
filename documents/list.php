<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Method not allowed.', 405);
}

$user = Auth::requireUser();
$documents = WorkflowService::listForUser($user);

Response::success('Documents loaded.', array(
    'documents' => $documents,
    'count' => count($documents),
));
