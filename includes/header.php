<?php
require_once __DIR__ . '/../functions.php';
$pageTitle = $pageTitle ?? 'Scholarship Management System';
$user = current_user($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="<?= BASE_URL ?>/home.php">Online Scholarship Management System</a>
        <nav>
            <a href="<?= BASE_URL ?>/home.php">Home</a>
            <a href="<?= BASE_URL ?>/student/scholarships.php">Scholarships</a>
            <a href="<?= BASE_URL ?>/functionalities.php">Functionalities</a>
            <a href="<?= BASE_URL ?>/help.php">Help</a>
            <?php if ($user): ?>
                <?php if ($user['role'] === 'student'): ?>
                    <a href="<?= BASE_URL ?>/student/my_applications.php">My Applications</a>
                    <a href="<?= BASE_URL ?>/student/profile.php">Profile</a>
                <?php endif; ?>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="<?= BASE_URL ?>/admin/index.php">Admin</a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/logout.php">Logout</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/index.php">Login</a>
                <a href="<?= BASE_URL ?>/student/register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container page-space">
    <?php show_flash(); ?>
