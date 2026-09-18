<?php
require_once __DIR__ . '/functions.php';
if (is_logged_in()) {
    redirect('/home.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare('SELECT id, username, password, role, is_active FROM users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $account = $stmt->get_result()->fetch_assoc();

    if ($account && (int) $account['is_active'] === 1 && password_verify($password, $account['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $account['id'];
        $_SESSION['username'] = $account['username'];
        $_SESSION['role'] = $account['role'];
        set_flash('success', 'Welcome back, ' . $account['username'] . '.');
        redirect('/home.php');
    }
    $error = 'Invalid username/password or inactive account.';
}

$pageTitle = 'Login';
include __DIR__ . '/includes/header.php';
?>
<section class="auth-card">
    <h1>Login</h1>
    <p>This is the first page shown to users.</p>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Username
            <input type="text" name="username" required autocomplete="username">
        </label>
        <label>Password
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <button type="submit">Login</button>
    </form>
    <div class="demo-box">
        <strong>Required default ordinary user:</strong><br>
        Username: <code>ucsc</code> · Password: <code>ucsc</code>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
