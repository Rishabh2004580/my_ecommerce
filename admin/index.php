<?php
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/admin-layout.php';
$pdo = require_admin_auth();
$stats = [];
$queries = [
    'products' => 'SELECT COUNT(*) FROM products',
    'active_products' => "SELECT COUNT(*) FROM products WHERE status = 'active'",
    'inactive_products' => "SELECT COUNT(*) FROM products WHERE status = 'inactive'",
    'categories' => 'SELECT COUNT(*) FROM categories',
    'pending_orders' => "SELECT COUNT(*) FROM orders WHERE status = 'pending'",
    'processing_orders' => "SELECT COUNT(*) FROM orders WHERE status = 'processing'",
    'shipped_orders' => "SELECT COUNT(*) FROM orders WHERE status = 'shipped'",
    'completed_orders' => "SELECT COUNT(*) FROM orders WHERE status = 'completed'",
    'cancelled_orders' => "SELECT COUNT(*) FROM orders WHERE status = 'cancelled'",
    'orders' => 'SELECT COUNT(*) FROM orders',
    'sales' => "SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed'",
];
foreach ($queries as $key => $query) $stats[$key] = $pdo->query($query)->fetchColumn();
$recentOrders = $pdo->query('SELECT id, customer_name, total_amount, status, created_at FROM orders ORDER BY created_at DESC, id DESC LIMIT 5')->fetchAll();
admin_header('Dashboard | Maison Gift Co.', 'dashboard');
?>
<div class="admin-page-head"><div><span class="admin-eyebrow">Maison Gift Co.</span><h1>Dashboard</h1><p>Live store metrics and recent activity, <?= e((string) ($_SESSION['admin_name'] ?? 'Administrator')); ?>.</p></div><div class="admin-actions"><a class="admin-btn admin-btn-light" href="<?= e(site_url()); ?>">View Website</a><a class="admin-btn admin-btn-primary" href="<?= e(site_url('admin/add-product.php')); ?>">+ Add Product</a></div></div>
<div class="admin-stats"><?php foreach ([['Total Products','products'],['Active Products','active_products'],['Inactive Products','inactive_products'],['Total Categories','categories'],['Total Orders','orders'],['Pending Orders','pending_orders'],['Processing Orders','processing_orders'],['Shipped Orders','shipped_orders'],['Completed Orders','completed_orders'],['Cancelled Orders','cancelled_orders']] as [$label, $key]): ?><div class="admin-card admin-stat"><span class="admin-stat-label"><?= e($label); ?></span><strong><?= e((string) $stats[$key]); ?></strong></div><?php endforeach; ?></div>
<div class="admin-card admin-sales-card"><span class="admin-stat-label">Total sales from completed orders</span><strong><?= e(format_price((float) $stats['sales'])); ?></strong><span class="admin-muted">Calculated from completed orders only.</span></div>
<section class="admin-card"><div class="admin-card-head"><h2>Recent Orders</h2><a href="<?= e(site_url('admin/orders.php')); ?>">View all</a></div><div class="admin-card-body"><div class="admin-list"><?php foreach ($recentOrders as $order): ?><div class="admin-list-row"><div><strong>#<?= e((string) $order['id']); ?> · <?= e($order['customer_name']); ?></strong><small><?= e(date('d M Y, H:i', strtotime($order['created_at']))); ?></small></div><div><strong><?= e(format_price((float) $order['total_amount'])); ?></strong> <span class="admin-badge <?= e($order['status']); ?>"><?= e(ucfirst($order['status'])); ?></span></div></div><?php endforeach; ?><?php if (!$recentOrders): ?><p class="admin-muted">No orders yet.</p><?php endif; ?></div></div></section>
<?php admin_footer(); ?>
