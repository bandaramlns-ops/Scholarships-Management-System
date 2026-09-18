<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_URL', '/scholarship_system');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_UPLOAD_BYTES', 2 * 1024 * 1024); 

$dbHost = '127.0.0.1';
$dbPort = 4306;
$dbUser = 'root';
$dbPass = '';
$dbName = 'scholarship_management';

$conn = new mysqli(
    $dbHost,
    $dbUser,
    $dbPass,
    $dbName,
    $dbPort
);
if ($conn->connect_error) {
    exit('Database connection failed. Check config.php and make sure MySQL is running.');
}
$conn->set_charset('utf8mb4');

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
