<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['admin_id'], $_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin') {
    redirect('admin/index.php');
    exit;
}

$error = '';
$email = '';
if (empty($_SESSION['admin_login_csrf'])) {
    $_SESSION['admin_login_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $pdo = get_db_connection();

    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['admin_login_csrf'], (string) $_POST['csrf_token'])) {
        $error = 'The form expired. Please try again.';
    } elseif ($email === '' || $password === '') {
        $error = 'Invalid email or password.';
    } elseif ($pdo !== null) {
        $stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = :email AND role = :role LIMIT 1');
        $stmt->execute(['email' => $email, 'role' => 'admin']);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_role'] = $admin['role'];
            unset($_SESSION['admin_login_csrf']);
            redirect('admin/index.php');
        }
        $error = 'Invalid email or password.';
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | Maison Gift Co.</title>
    <link rel="stylesheet" href="<?= e(asset_url('css/admin.css')); ?>">
</head>
<body class="admin-login-body">
    <main class="admin-login-card">
        <div class="admin-login-brand"><span class="admin-brand-mark">M</span><strong>Maison Gift Co.</strong></div>
        <span class="admin-eyebrow">Admin Portal</span>
        <h1>Welcome back</h1>
        <p>Sign in to your administrator account.</p>
        <?php if ($error !== ''): ?><div class="admin-login-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        <form method="post">
            <label>Email            <input type="email" name="email" value="<?= e($email); ?>" autocomplete="username" required></label>
            <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['admin_login_csrf']); ?>">
            <button class="admin-login-submit" type="submit">Sign In</button>
        </form>
        <small>Secure administrator access</small>
    </main>
</body>
</html>
