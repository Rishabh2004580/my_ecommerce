<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_login();
$pdo = get_db_connection();
if (!$pdo) { http_response_code(503); exit('Store unavailable.'); }
$items = cart_items($pdo);
if (!$items) redirect('cart.php');
$userStmt = $pdo->prepare('SELECT name, email FROM users WHERE id = :id');
$userStmt->execute(['id' => (int) $_SESSION['user_id']]);
$customer = $userStmt->fetch() ?: ['name' => '', 'email' => ''];
$errors = [];
$subtotal = array_sum(array_column($items, 'subtotal'));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['customer_name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['customer_email'] ?? '')));
    $phone = trim((string) ($_POST['customer_phone'] ?? ''));
    $address = trim((string) ($_POST['shipping_address'] ?? ''));
    if ($name === '' || strlen($name) > 120) $errors[] = 'Enter your full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if ($address === '') $errors[] = 'Enter a delivery address.';
    if (!$errors) {
        try {
            $pdo->beginTransaction();
            foreach ($items as $item) {
                $stock = $pdo->prepare("SELECT stock FROM products WHERE id = :id AND status = 'active' FOR UPDATE");
                $stock->execute(['id' => $item['id']]);
                if ((int) $stock->fetchColumn() < (int) $item['quantity']) throw new RuntimeException('One or more items are no longer available in the requested quantity.');
            }
            $order = $pdo->prepare('INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, shipping_address, total_amount, status) VALUES (:user_id, :name, :email, :phone, :address, :total, :status)');
            $order->execute(['user_id' => (int) $_SESSION['user_id'], 'name' => $name, 'email' => $email, 'phone' => $phone ?: null, 'address' => $address, 'total' => $subtotal, 'status' => 'pending']);
            $orderId = (int) $pdo->lastInsertId();
            $line = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (:order_id, :product_id, :name, :price, :quantity, :subtotal)');
            $update = $pdo->prepare('UPDATE products SET stock = stock - :quantity WHERE id = :id');
            foreach ($items as $item) {
                $line->execute(['order_id' => $orderId, 'product_id' => $item['id'], 'name' => $item['name'], 'price' => $item['price'], 'quantity' => $item['quantity'], 'subtotal' => $item['subtotal']]);
                $update->execute(['quantity' => $item['quantity'], 'id' => $item['id']]);
            }
            $pdo->commit();
            $_SESSION['cart'] = [];
            redirect('orders.php?placed=' . $orderId);
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Checkout failed: ' . $exception->getMessage());
            $errors[] = 'We could not place the order. Please review your details and try again.';
        }
    }
} else {
    $_POST['customer_name'] = $customer['name'];
    $_POST['customer_email'] = $customer['email'];
}
$pageTitle = 'Checkout | Maison Gift Co.';
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-banner section-spacing"><div class="container"><span class="eyebrow">A thoughtful finish</span><h1>Checkout</h1></div></section>
<section class="section-spacing"><div class="container checkout-layout"><form class="auth-form checkout-form" method="post">
    <?php if ($errors): ?><div class="form-errors"><?php foreach ($errors as $error): ?><p><?= e($error); ?></p><?php endforeach; ?></div><?php endif; ?>
    <h2>Delivery details</h2><label>Full name<input required name="customer_name" value="<?= e((string) ($_POST['customer_name'] ?? '')); ?>"></label><label>Email<input required type="email" name="customer_email" value="<?= e((string) ($_POST['customer_email'] ?? '')); ?>"></label><label>Phone<input name="customer_phone" value="<?= e((string) ($_POST['customer_phone'] ?? '')); ?>"></label><label>Shipping address<textarea required name="shipping_address" rows="5"><?= e((string) ($_POST['shipping_address'] ?? '')); ?></textarea></label><button class="btn btn-primary" type="submit">Place order</button>
</form><aside class="cart-summary"><span class="eyebrow">Your order</span><?php foreach ($items as $item): ?><div><span><?= e($item['name']); ?> × <?= e((string) $item['quantity']); ?></span><strong><?= e(format_price((float) $item['subtotal'])); ?></strong></div><?php endforeach; ?><hr><div><span>Total</span><strong><?= e(format_price((float) $subtotal)); ?></strong></div></aside></div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
