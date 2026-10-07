<?php
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/admin-layout.php';
$pdo = require_admin_auth();
$pageTitle = 'Admin Dashboard | ShopStore';

$stats = [
    'products' => 0,
    'active_products' => 0,
    'customers' => 0,
    'orders' => 0,
    'pending_orders' => 0,
];
$stats['products'] = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$stats['active_products'] = (int) $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
$stats['customers'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$stats['orders'] = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$stats['pending_orders'] = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$recentOrders = $pdo->query('SELECT id, customer_name, total_amount, status, created_at FROM orders ORDER BY created_at DESC, id DESC LIMIT 5')->fetchAll();
$lowStock = $pdo->query("SELECT id, name, stock FROM products WHERE status = 'active' AND stock <= 5 ORDER BY stock ASC, name ASC LIMIT 5")->fetchAll();
$categories = $pdo->query('SELECT categories.name, COUNT(products.id) AS product_count FROM categories LEFT JOIN products ON products.category_id = categories.id GROUP BY categories.id, categories.name ORDER BY categories.name')->fetchAll();
$recentProducts = $pdo->query('SELECT products.name, products.price, products.stock, products.status, categories.name AS category_name FROM products INNER JOIN categories ON categories.id = products.category_id ORDER BY products.created_at DESC, products.id DESC LIMIT 5')->fetchAll();
admin_header($pageTitle, 'dashboard');
?>
<div class="admin-page-head">
    <div><span class="admin-eyebrow">Admin panel</span><h1>Dashboard</h1><p>Welcome back, <?= e($_SESSION['admin_name']); ?>.</p></div>
    <div class="admin-actions"><a class="admin-btn admin-btn-light" href="<?= e(site_url()); ?>">View Website</a><a class="admin-btn admin-btn-primary" href="<?= e(site_url('admin/add-product.php')); ?>">+ Add Product</a></div>
</div>
<div class="admin-stats">
    <?php foreach ([['Total Products', $stats['products'], 'Catalog items'], ['Active Products', $stats['active_products'], 'Currently published'], ['Total Customers', $stats['customers'], 'Registered customers'], ['Total Orders', $stats['orders'], 'Orders received'], ['Pending Orders', $stats['pending_orders'], 'Need attention']] as [$label, $value, $support]): ?>
        <div class="admin-card admin-stat"><span class="admin-stat-label"><?= e($label); ?></span><strong><?= e((string) $value); ?></strong><small><?= e($support); ?></small></div>
    <?php endforeach; ?>
</div>
<div class="admin-grid-two">
    <section class="admin-card"><div class="admin-card-head"><h2>Recent Orders</h2><a href="<?= e(site_url('admin/orders.php')); ?>">View all</a></div><div class="admin-card-body"><div class="admin-list">
        <?php foreach ($recentOrders as $order): ?><div class="admin-list-row"><div><strong>#<?= e((string) $order['id']); ?> · <?= e($order['customer_name']); ?></strong><small><?= e(date('d M Y', strtotime($order['created_at']))); ?></small></div><div><strong><?= e(format_price((float) $order['total_amount'])); ?></strong><span class="admin-badge <?= e($order['status']); ?>"><?= e(ucfirst($order['status'])); ?></span></div></div><?php endforeach; ?>
        <?php if (!$recentOrders): ?><p class="admin-muted">No orders yet.</p><?php endif; ?>
    </div></div></section>
    <section class="admin-card"><div class="admin-card-head"><h2>Quick Actions</h2></div><div class="admin-card-body admin-quick-actions">
        <a href="<?= e(site_url('admin/add-product.php')); ?>">+ Add Product</a><a href="<?= e(site_url('admin/products.php')); ?>">Manage Products</a><a href="<?= e(site_url('admin/orders.php')); ?>">View Orders</a><a href="<?= e(site_url('admin/categories.php')); ?>">Manage Categories</a><a href="<?= e(site_url()); ?>">View Website</a>
    </div></section>
</div>
<div class="admin-grid-two">
    <section class="admin-card"><div class="admin-card-head"><h2>Low Stock Products</h2></div><div class="admin-card-body"><div class="admin-list"><?php foreach ($lowStock as $product): ?><div class="admin-list-row"><strong><?= e($product['name']); ?></strong><span class="admin-badge pending"><?= e((string) $product['stock']); ?> left</span></div><?php endforeach; ?><?php if (!$lowStock): ?><p class="admin-muted">All products are sufficiently stocked.</p><?php endif; ?></div></div></section>
    <section class="admin-card"><div class="admin-card-head"><h2>Categories</h2></div><div class="admin-card-body"><div class="admin-list"><?php foreach ($categories as $category): ?><div class="admin-list-row"><strong><?= e($category['name']); ?></strong><span><?= e((string) $category['product_count']); ?> products</span></div><?php endforeach; ?></div></div></section>
</div>
<section class="admin-card"><div class="admin-card-head"><h2>Recently Added Products</h2><a href="<?= e(site_url('admin/products.php')); ?>">Manage products</a></div><div class="admin-card-body"><div class="admin-list"><?php foreach ($recentProducts as $product): ?><div class="admin-list-row"><div><strong><?= e($product['name']); ?></strong><small><?= e($product['category_name']); ?> · <?= e(format_price((float) $product['price'])); ?></small></div><span class="admin-badge <?= e($product['status']); ?>"><?= e((string) $product['stock']); ?> in stock</span></div><?php endforeach; ?></div></div></section>
<?php admin_footer(); ?>