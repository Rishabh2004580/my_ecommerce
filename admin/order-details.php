<?php
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/admin-layout.php';
$pdo = require_admin_auth();
$orderId = filter_var($_GET['id'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$orderId) { http_response_code(400); admin_set_flash('error', 'Invalid order.'); redirect('admin/orders.php'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = (string) ($_POST['status'] ?? '');
    if (!admin_verify_csrf() || !in_array($newStatus, ['pending','processing','shipped','completed','cancelled'], true)) {
        admin_set_flash('error', 'Invalid order status update.');
    } else {
        $stmt = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');
        $stmt->execute(['status' => $newStatus, 'id' => $orderId]);
        admin_set_flash('success', 'Order status updated.');
    }
    redirect('admin/order-details.php?id=' . $orderId);
}
$stmt = $pdo->prepare('SELECT id, customer_name, customer_email, customer_phone, shipping_address, total_amount, status, created_at FROM orders WHERE id = :id');
$stmt->execute(['id' => $orderId]);
$order = $stmt->fetch();
if (!$order) { http_response_code(404); admin_header('Order not found | Maison Gift Co.', 'orders'); echo '<div class="admin-card admin-card-body"><h1>Order not found</h1><a class="admin-btn admin-btn-primary" href="' . e(site_url('admin/orders.php')) . '">Back to orders</a></div>'; admin_footer(); exit; }
$lines = $pdo->prepare('SELECT product_name, price, quantity, subtotal FROM order_items WHERE order_id = :id ORDER BY id');
$lines->execute(['id' => $orderId]);
$flash = admin_get_flash();
admin_header('Order #' . $orderId . ' | Maison Gift Co.', 'orders');
?>
<div class="admin-page-head"><div><span class="admin-eyebrow">Order #<?= e((string) $order['id']); ?></span><h1>Order details</h1><p><?= e(date('d M Y, H:i', strtotime($order['created_at']))); ?></p></div><a class="admin-btn admin-btn-light" href="<?= e(site_url('admin/orders.php')); ?>">Back to orders</a></div>
<?php if ($flash): ?><p class="admin-flash <?= e($flash['type']); ?>"><?= e($flash['message']); ?></p><?php endif; ?>
<div class="admin-grid-two"><section class="admin-card"><div class="admin-card-head"><h2>Customer and delivery</h2></div><div class="admin-card-body admin-detail-list"><p><strong>Name</strong><?= e($order['customer_name']); ?></p><p><strong>Email</strong><?= e($order['customer_email']); ?></p><p><strong>Phone</strong><?= e($order['customer_phone'] ?: 'Not provided'); ?></p><p><strong>Shipping address</strong><?= nl2br(e($order['shipping_address'])); ?></p></div></section>
<section class="admin-card"><div class="admin-card-head"><h2>Order status</h2></div><div class="admin-card-body"><form class="admin-form" method="post"><input type="hidden" name="csrf_token" value="<?= e(admin_csrf_token()); ?>"><input type="hidden" name="id" value="<?= e((string) $order['id']); ?>"><label>Status<select name="status"><?php foreach (['pending','processing','shipped','completed','cancelled'] as $option): ?><option value="<?= e($option); ?>" <?= $order['status'] === $option ? 'selected' : ''; ?>><?= e(ucfirst($option)); ?></option><?php endforeach; ?></select></label><button class="admin-btn admin-btn-primary" type="submit">Update status</button></form></div></section></div>
<section class="admin-card"><div class="admin-card-head"><h2>Products</h2></div><div class="admin-card-body"><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th></tr></thead><tbody><?php foreach ($lines as $line): ?><tr><td><?= e($line['product_name']); ?></td><td><?= e(format_price((float) $line['price'])); ?></td><td><?= e((string) $line['quantity']); ?></td><td><?= e(format_price((float) $line['subtotal'])); ?></td></tr><?php endforeach; ?><tr><th colspan="3">Total</th><th><?= e(format_price((float) $order['total_amount'])); ?></th></tr></tbody></table></div></div></section>
<?php admin_footer(); ?>
