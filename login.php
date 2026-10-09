<?php
require_once __DIR__ . '/config/database.php';
$pageTitle = 'Login | ' . site_name();
$errors = [];
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$pdo = get_db_connection();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $password = (string) ($_POST['password'] ?? '');
    $stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password'])) {
        $errors[] = 'The email or password is incorrect.';
    } else {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        if ($user['role'] === 'admin') {
            $_SESSION['admin_id'] = (int) $user['id'];
            redirect('admin/index.php');
        }
        redirect('products.php');
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-banner section-spacing"><div class="container"><span class="eyebrow">Account access</span><h1>Welcome back.</h1></div></section>
<section class="section-spacing"><div class="container auth-shell"><form class="auth-form" method="post">
    <?php if ($errors): ?><div class="form-errors"><?php foreach ($errors as $error): ?><p><?= e($error); ?></p><?php endforeach; ?></div><?php endif; ?>
    <label>Email<input required type="email" name="email" value="<?= e($email); ?>" autocomplete="email"></label>
    <label>Password<input required type="password" name="password" autocomplete="current-password"></label>
    <button type="submit" class="btn btn-primary">Login</button>
    <p class="auth-note">New here? <a href="<?= e(site_url('register.php')); ?>">Create an account</a></p>
</form></div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
