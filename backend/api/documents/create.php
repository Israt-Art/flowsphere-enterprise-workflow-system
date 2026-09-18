<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed.', 405);
}

$user = Auth::requireRole(array('EMPLOYEE'));

$title = isset($_POST['title']) ? $_POST['title'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';
$attachment = isset($_FILES['attachment']) ? $_FILES['attachment'] : null;

$document = WorkflowService::createDocument($user, $title, $description, $attachment);

Response::success('Document submitted for manager review.', $document, 201);
