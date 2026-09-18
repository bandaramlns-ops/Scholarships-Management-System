<?php

require_once __DIR__ . '/functions.php';

require_login();

$applicationId = filter_input(
    INPUT_GET,
    'application_id',
    FILTER_VALIDATE_INT
);

if (!$applicationId) {
    http_response_code(400);
    exit('Invalid application selection.');
}

// Get the application's document and owner.
$statement = $conn->prepare(
    'SELECT id, user_id, document_path
     FROM applications
     WHERE id = ?
     LIMIT 1'
);

$statement->bind_param('i', $applicationId);
$statement->execute();

$application = $statement
    ->get_result()
    ->fetch_assoc();

if (!$application) {
    http_response_code(404);
    exit('Application not found.');
}

// Students may only open their own documents.
// Administrators may open any application document.
$currentUserId = (int) $_SESSION['user_id'];

if (
    !is_admin()
    && (int) $application['user_id'] !== $currentUserId
) {
    http_response_code(403);
    exit('You are not authorized to access this document.');
}

if (!$application['document_path']) {
    http_response_code(404);
    exit('No supporting document was uploaded.');
}

// Extract only the filename to prevent invalid folder access.
$fileName = basename($application['document_path']);

$filePath = UPLOAD_DIR . $fileName;

if (!is_file($filePath)) {
    http_response_code(404);
    exit('The supporting document could not be found.');
}

$extension = strtolower(
    pathinfo(
        $fileName,
        PATHINFO_EXTENSION
    )
);

$allowedExtensions = [
    'pdf',
    'jpg',
    'jpeg',
    'png'
];

if (!in_array($extension, $allowedExtensions, true)) {
    http_response_code(403);
    exit('This document type is not permitted.');
}

$fileInfo = new finfo(FILEINFO_MIME_TYPE);

$mimeType = $fileInfo->file($filePath);

$allowedMimeTypes = [
    'application/pdf',
    'image/jpeg',
    'image/png'
];

if (!in_array($mimeType, $allowedMimeTypes, true)) {
    http_response_code(403);
    exit('The document content is not permitted.');
}

header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($filePath));
header(
    'Content-Disposition: inline; filename="' .
    rawurlencode($fileName) .
    '"'
);
header('X-Content-Type-Options: nosniff');

readfile($filePath);
exit;