<?php
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/admin-layout.php';
$pdo = require_admin_auth();
$customers = $pdo->query("SELECT id, name, email, created_at FROM users WHERE role = 'customer' ORDER BY created_at DESC, id DESC")->fetchAll();
admin_header('Admin Customers | Maison Gift Co.', 'users');
?>
<div class="admin-page-head"><div><span class="admin-eyebrow">Accounts</span><h1>Customers</h1><p>Registered customer accounts.</p></div></div>
<div class="admin-card"><div class="admin-card-head"><h2>All Customers</h2></div><div class="admin-card-body"><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Name</th><th>Email</th><th>Joined</th></tr></thead><tbody><?php foreach ($customers as $customer): ?><tr><td><?= e($customer['name']); ?></td><td><?= e($customer['email']); ?></td><td><?= e(date('d M Y', strtotime($customer['created_at']))); ?></td></tr><?php endforeach; ?><?php if (!$customers): ?><tr><td colspan="3">No customers yet.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php admin_footer(); ?>
