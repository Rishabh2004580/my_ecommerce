<?php
require_once __DIR__ . '/config/database.php';
$pageTitle = 'Create Account | ' . site_name();
$errors = [];
$values = ['name' => '', 'email' => ''];
$pdo = get_db_connection();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $values['name'] = trim((string) ($_POST['name'] ?? ''));
    $values['email'] = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    if ($values['name'] === '' || strlen($values['name']) > 120) $errors[] = 'Enter your name.';
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if (!$errors) {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $check->execute(['email' => $values['email']]);
        if ($check->fetchColumn()) {
            $errors[] = 'An account with this email already exists.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)');
            $stmt->execute(['name' => $values['name'], 'email' => $values['email'], 'password' => password_hash($password, PASSWORD_DEFAULT), 'role' => 'customer']);
            $_SESSION['user_id'] = (int) $pdo->lastInsertId();
            $_SESSION['user_name'] = $values['name'];
            $_SESSION['user_role'] = 'customer';
            redirect('products.php');
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-banner section-spacing"><div class="container"><span class="eyebrow">Create account</span><h1>Make gifting personal.</h1></div></section>
<section class="section-spacing"><div class="container auth-shell"><form class="auth-form" method="post">
    <?php if ($errors): ?><div class="form-errors"><?php foreach ($errors as $error): ?><p><?= e($error); ?></p><?php endforeach; ?></div><?php endif; ?>
    <label>Full name<input required type="text" name="name" maxlength="120" value="<?= e($values['name']); ?>" autocomplete="name"></label>
    <label>Email<input required type="email" name="email" value="<?= e($values['email']); ?>" autocomplete="email"></label>
    <label>Password<input required type="password" name="password" minlength="8" autocomplete="new-password"></label>
    <button type="submit" class="btn btn-primary">Create account</button>
    <p class="auth-note">Already have an account? <a href="<?= e(site_url('login.php')); ?>">Sign in</a></p>
</form></div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
