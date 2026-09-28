<?php
// POST /backend/api/documents/create.php
// multipart/form-data (not JSON) because it can include a file.
// Fields: title, description, attachment (optional)

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');
require_role(['employee']);

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($title === '') {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Title is required"]);
    exit;
}

$stored_filename = null;

if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['attachment'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "File upload failed"]);
        exit;
    }

    // Allow-list of extensions (safer than trying to block bad ones)
    $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowed_extensions)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "File type not allowed"]);
        exit;
    }

    $max_size_bytes = 5 * 1024 * 1024; // 5 MB
    if ($file['size'] > $max_size_bytes) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "File is too large (max 5MB)"]);
        exit;
    }

    // Never trust the original filename - generate our own to avoid
    // collisions and path-traversal tricks like "../../file.php"
    $stored_filename = uniqid('doc_', true) . '.' . $extension;
    $destination = __DIR__ . '/../../uploads/' . $stored_filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Could not save uploaded file"]);
        exit;
    }
}

$employee_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "INSERT INTO documents (submitted_by, title, description, file_path, current_status)
     VALUES (?, ?, ?, ?, 'pending_manager')"
);
$stmt->bind_param("isss", $employee_id, $title, $description, $stored_filename);
$stmt->execute();

echo json_encode([
    "success" => true,
    "message" => "Document submitted",
    "document_id" => $stmt->insert_id
]);
