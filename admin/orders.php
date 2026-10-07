<?php
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/admin-layout.php';
$pdo = require_admin_auth();
$pageTitle = 'Admin Orders | ShopStore';
$orders = $pdo->query(
    'SELECT id, customer_name, customer_email, total_amount, status, created_at
     FROM orders ORDER BY created_at DESC, id DESC'
)->fetchAll();
admin_header($pageTitle, 'orders');
?>
<div class="admin-page-head"><div><span class="admin-eyebrow">Sales</span><h1>Orders</h1><p>Review recent customer purchases.</p></div></div>
<div class="admin-card"><div class="admin-card-head"><h2>All Orders</h2></div><div class="admin-card-body">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Order</th><th>Customer</th><th>Email</th><th>Total</th><th>Status</th><th>Created</th></tr></thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= e((string) $order['id']); ?></td>
                            <td><?= e($order['customer_name']); ?></td>
                            <td><?= e($order['customer_email']); ?></td>
                            <td><?= e(format_price((float) $order['total_amount'])); ?></td>
                            <td><span class="admin-badge <?= e($order['status']); ?>"><?= e(ucfirst($order['status'])); ?></span></td>
                            <td><?= e(date('Y-m-d', strtotime($order['created_at']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$orders): ?><tr><td colspan="6">No orders found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div></div>
<?php admin_footer(); ?>
