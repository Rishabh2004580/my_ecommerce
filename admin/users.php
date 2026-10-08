<?php
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/admin-layout.php';
$pdo = require_admin_auth();
$users = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC, id DESC')->fetchAll();
admin_header('Users | Maison Gift Co.', 'users');
?>
<div class="admin-page-head"><div><span class="admin-eyebrow">Accounts</span><h1>Users</h1><p>View registered customers and administrators. Passwords are never displayed.</p></div></div>
<section class="admin-card"><div class="admin-card-head"><h2>Registered users</h2><span><?= e((string) count($users)); ?></span></div><div class="admin-card-body"><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Created</th></tr></thead><tbody><?php foreach ($users as $user): ?><tr><td><?= e($user['name']); ?></td><td><?= e($user['email']); ?></td><td><span class="admin-badge <?= $user['role'] === 'admin' ? 'processing' : ''; ?>"><?= e(ucfirst($user['role'])); ?></span></td><td><?= e(date('d M Y, H:i', strtotime($user['created_at']))); ?></td></tr><?php endforeach; ?><?php if (!$users): ?><tr><td colspan="4">No users found.</td></tr><?php endif; ?></tbody></table></div></div></section>
<?php admin_footer(); ?>
