<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/config/database.php';
$pdo = get_db_connection();
$stmt = $pdo->prepare('SELECT id, total_amount, status, created_at FROM orders WHERE user_id = :user_id ORDER BY created_at DESC, id DESC');
$stmt->execute(['user_id' => (int) $_SESSION['user_id']]);
$orders = $stmt->fetchAll();
$pageTitle = 'My Orders | Maison Gift Co.';
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-banner section-spacing"><div class="container"><span class="eyebrow">Your Maison</span><h1>My orders</h1></div></section>
<section class="section-spacing"><div class="container orders-list"><?php if (isset($_GET['placed'])): ?><div class="order-success">Thank you. Your order #<?= e((string) $_GET['placed']); ?> has been placed.</div><?php endif; ?><?php if (!$orders): ?><div class="cart-placeholder"><p>Your order history will appear here.</p><a class="btn btn-primary" href="<?= e(site_url('products.php')); ?>">Explore gifts</a></div><?php else: ?><?php foreach ($orders as $order): ?><article class="order-card"><div><span class="eyebrow">Order #<?= e((string) $order['id']); ?></span><h2><?= e(date('d M Y', strtotime($order['created_at']))); ?></h2></div><strong><?= e(format_price((float) $order['total_amount'])); ?></strong><span class="status-pill <?= e($order['status']); ?>"><?= e(ucfirst($order['status'])); ?></span></article><?php endforeach; ?><?php endif; ?></div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
