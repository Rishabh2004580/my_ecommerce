<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
$pdo = get_db_connection();
if (!$pdo) { http_response_code(503); exit('Store unavailable.'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $action = (string) ($_POST['action'] ?? '');
    if ($id && in_array($action, ['add', 'increase', 'decrease', 'remove'], true)) {
        $cart = is_array($_SESSION['cart'] ?? null) ? $_SESSION['cart'] : [];
        $current = (int) ($cart[$id] ?? 0);
        $requested = max(1, (int) ($_POST['quantity'] ?? 1));
        if ($action === 'add' || $action === 'increase') {
            $stockStmt = $pdo->prepare("SELECT stock FROM products WHERE id = :id AND status = 'active'");
            $stockStmt->execute(['id' => $id]);
            $stock = (int) $stockStmt->fetchColumn();
            $cart[$id] = min($action === 'add' ? $current + $requested : $current + 1, $stock);
        } elseif ($action === 'decrease') {
            $cart[$id] = $current - 1;
        } else {
            unset($cart[$id]);
        }
        $_SESSION['cart'] = array_filter($cart, static fn ($quantity): bool => (int) $quantity > 0);
    }
    redirect('cart.php');
}
$items = cart_items($pdo);
$subtotal = array_sum(array_column($items, 'subtotal'));
$cartMessage = 'Hello ' . site_name() . ", I would like to order:\n";
foreach ($items as $item) {
    $cartMessage .= '- ' . $item['name'] . ' | Quantity: ' . $item['quantity']
        . ' | Price: ' . format_price((float) $item['price'])
        . ' | Subtotal: ' . format_price((float) $item['subtotal']) . "\n";
}
$cartMessage .= 'Total: ' . format_price((float) $subtotal);
$cartWhatsappUrl = whatsapp_url($pdo, $cartMessage);
$pageTitle = 'Your Cart | ' . site_name();
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-banner section-spacing"><div class="container"><span class="eyebrow">Your gift selection</span><h1>Shopping cart</h1></div></section>
<section class="section-spacing"><div class="container cart-layout">
<?php if (!$items): ?><div class="cart-placeholder"><p>Your cart is ready for something thoughtful.</p><a class="btn btn-primary" href="<?= e(site_url('products.php')); ?>">Browse gifts</a></div>
<?php else: ?><div class="cart-items"><?php foreach ($items as $item): ?><article class="cart-item"><div class="cart-item-image"><?php $image = product_image_url($item['image']); ?><?php if ($image): ?><img src="<?= e($image); ?>" alt="<?= e($item['name']); ?>"><?php else: ?><div class="fallback-image"><span>Gift</span></div><?php endif; ?></div><div><span class="product-category"><?= e($item['category_name']); ?></span><h2><?= e($item['name']); ?></h2><p><?= e(format_price((float) $item['price'])); ?></p><div class="cart-controls"><form method="post"><input type="hidden" name="product_id" value="<?= e((string) $item['id']); ?>"><input type="hidden" name="action" value="decrease"><button type="submit">−</button></form><strong><?= e((string) $item['quantity']); ?></strong><form method="post"><input type="hidden" name="product_id" value="<?= e((string) $item['id']); ?>"><input type="hidden" name="action" value="increase"><button type="submit">+</button></form><form method="post"><input type="hidden" name="product_id" value="<?= e((string) $item['id']); ?>"><input type="hidden" name="action" value="remove"><button type="submit" class="text-button">Remove</button></form></div></div><strong><?= e(format_price((float) $item['subtotal'])); ?></strong></article><?php endforeach; ?></div><aside class="cart-summary"><span class="eyebrow">Order summary</span><div><span>Subtotal</span><strong><?= e(format_price((float) $subtotal)); ?></strong></div><p>Delivery and taxes are calculated at checkout.</p><a class="btn btn-primary" href="<?= e(site_url('checkout.php')); ?>">Continue to checkout</a><?php if ($cartWhatsappUrl !== null): ?><a class="btn btn-whatsapp" href="<?= e($cartWhatsappUrl); ?>" target="_blank" rel="noopener noreferrer"><span class="whatsapp-icon" aria-hidden="true">◉</span> Place Order on WhatsApp</a><?php endif; ?></aside><?php endif; ?>
</div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
