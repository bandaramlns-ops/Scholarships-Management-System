<?php
require_once __DIR__ . '/config.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function is_admin(): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to continue.');
        redirect('/index.php');
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        include __DIR__ . '/403.php';
        exit;
    }
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash(): void
{
    if (!isset($_SESSION['flash'])) {
        return;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    echo '<div class="alert ' . e($flash['type']) . '">' . e($flash['message']) . '</div>';
}

function current_user(mysqli $conn): ?array
{
    if (!is_logged_in()) {
        return null;
    }
    $stmt = $conn->prepare('SELECT id, username, full_name, email, phone, faculty, year_of_study, role, is_active FROM users WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: null;
}

function format_money($amount): string
{
    return 'LKR ' . number_format((float) $amount, 2);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        exit('Invalid request token. Please return to the previous page and try again.');
    }
}
