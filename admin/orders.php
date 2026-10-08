<?php
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/admin-layout.php';
$pdo = require_admin_auth();
$search = trim((string) ($_GET['search'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$conditions = [];
$params = [];
if ($search !== '') {
    $conditions[] = '(orders.id = :order_id OR orders.customer_name LIKE :search OR orders.customer_email LIKE :search)';
    $params['order_id'] = ctype_digit($search) ? (int) $search : 0;
    $params['search'] = '%' . $search . '%';
}
if (in_array($status, ['pending', 'processing', 'shipped', 'completed', 'cancelled'], true)) {
    $conditions[] = 'orders.status = :status';
    $params['status'] = $status;
}
$where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
$stmt = $pdo->prepare("SELECT id, customer_name, customer_email, total_amount, status, created_at FROM orders $where ORDER BY created_at DESC, id DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();
admin_header('Orders | Maison Gift Co.', 'orders');
?>
<div class="admin-page-head"><div><span class="admin-eyebrow">Sales</span><h1>Orders</h1><p>Review and update customer orders.</p></div></div>
<div class="admin-card"><div class="admin-card-body"><form class="admin-filter-form" method="get"><input type="search" name="search" placeholder="Order ID, customer, or email" value="<?= e($search); ?>"><select name="status"><option value="">All statuses</option><?php foreach (['pending','processing','shipped','completed','cancelled'] as $option): ?><option value="<?= e($option); ?>" <?= $status === $option ? 'selected' : ''; ?>><?= e(ucfirst($option)); ?></option><?php endforeach; ?></select><button class="admin-btn admin-btn-primary" type="submit">Filter</button><a class="admin-btn admin-btn-light" href="<?= e(site_url('admin/orders.php')); ?>">Reset</a></form></div><div class="admin-card-head"><h2>All orders</h2><span><?= e((string) count($orders)); ?></span></div><div class="admin-card-body"><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Order ID</th><th>Customer</th><th>Email</th><th>Total</th><th>Date</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($orders as $order): ?><tr><td>#<?= e((string) $order['id']); ?></td><td><?= e($order['customer_name']); ?></td><td><?= e($order['customer_email']); ?></td><td><?= e(format_price((float) $order['total_amount'])); ?></td><td><?= e(date('d M Y, H:i', strtotime($order['created_at']))); ?></td><td><span class="admin-badge <?= e($order['status']); ?>"><?= e(ucfirst($order['status'])); ?></span></td><td><a class="admin-btn admin-btn-light" href="<?= e(site_url('admin/order-details.php?id=' . (int) $order['id'])); ?>">View Details</a></td></tr><?php endforeach; ?><?php if (!$orders): ?><tr><td colspan="7">No orders match these filters.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php admin_footer(); ?>
