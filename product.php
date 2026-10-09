<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/whatsapp.php';

$pageTitle = 'Product Details | ' . site_name();
$product = null;
$relatedProducts = [];
$productId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($productId !== false && $productId !== null) {
    $pdo = get_db_connection();
    if ($pdo !== null) {
        try {
            $stmt = $pdo->prepare(
                'SELECT products.*, categories.name AS category_name, categories.slug AS category_slug
                 FROM products
                 INNER JOIN categories ON products.category_id = categories.id
                 WHERE products.id = :id AND products.status = :status
                 LIMIT 1'
            );
            $stmt->execute(['id' => $productId, 'status' => 'active']);
            $product = $stmt->fetch();

            if ($product) {
                $relatedStmt = $pdo->prepare(
                    'SELECT products.id, products.name, products.price, products.stock, products.image,
                            categories.name AS category_name
                     FROM products
                     INNER JOIN categories ON products.category_id = categories.id
                     WHERE products.category_id = :category_id
                       AND products.id <> :product_id
                       AND products.status = :status
                     ORDER BY products.created_at DESC, products.id DESC
                     LIMIT 4'
                );
                $relatedStmt->execute([
                    'category_id' => $product['category_id'],
                    'product_id' => $productId,
                    'status' => 'active',
                ]);
                $relatedProducts = $relatedStmt->fetchAll();
            }
        } catch (PDOException $e) {
            error_log('Product detail query failed: ' . $e->getMessage());
        }
    }
}

if ($product) {
    $pageTitle = $product['name'] . ' | ' . site_name();
} else {
    http_response_code(404);
}
require_once __DIR__ . '/includes/header.php';
?>

<div class="product-page">
    <div class="container">
        <?php if (!$product): ?>
            <section class="not-found-card">
                <span class="eyebrow">Product unavailable</span>
                <h1>Product not found</h1>
                <p>The product you are looking for is unavailable or may have been removed.</p>
                <a class="btn btn-primary" href="<?= e(site_url('products.php')); ?>">Continue Shopping</a>
            </section>
        <?php else: ?>
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="<?= e(site_url()); ?>">Home</a><span>/</span>
                <a href="<?= e(site_url('products.php')); ?>">Products</a><span>/</span>
                <a href="<?= e(site_url('products.php?category=' . rawurlencode($product['category_slug']))); ?>"><?= e($product['category_name']); ?></a><span>/</span>
                <strong><?= e($product['name']); ?></strong>
            </nav>
            <div class="product-detail">
                <div class="product-detail-image">
                    <?php $imageUrl = product_image_url($product['image']); ?>
                    <?php if ($imageUrl !== ''): ?>
                        <img src="<?= e($imageUrl); ?>" alt="<?= e($product['name']); ?>" onerror="this.style.display='none'; this.parentElement.classList.add('fallback-image');">
                    <?php else: ?>
                        <div class="fallback-image large"><span aria-hidden="true">✦</span><small>Maison selection</small></div>
                    <?php endif; ?>
                </div>
                <div class="product-detail-copy">
                    <span class="product-category"><?= e($product['category_name']); ?></span>
                    <h1><?= e($product['name']); ?></h1>
                    <?php $mrp = (float) ($product['mrp'] ?? 0); $discount = product_discount($mrp, (float) $product['price']); ?>
                    <div class="price-row detail-price"><strong><?= e(format_price((float) $product['price'])); ?></strong><?php if ($mrp > (float) $product['price']): ?><del><?= e(format_price($mrp)); ?></del><b><?= e((string) (int) $discount); ?>% OFF</b><?php endif; ?></div>
                    <?php $stock = (int) $product['stock']; ?>
                    <p class="detail-stock <?= $stock === 0 ? 'is-out' : ($stock <= 5 ? 'is-low' : ''); ?>">
                        <?= $stock === 0 ? 'Out of stock' : ($stock <= 5 ? 'Only ' . $stock . ' left' : 'In stock · ' . $stock . ' available'); ?>
                    </p>
                    <?php if (!empty($product['occasion'])): ?><p class="product-category"><?= e($product['occasion']); ?></p><?php endif; ?>
                    <p class="detail-description"><?= e($product['description'] ?: 'A thoughtfully selected gift, ready to make someone smile.'); ?></p>
                    <?php if ($stock > 0): ?>
                        <div class="quantity-control"><label for="quantity">Quantity</label><input id="quantity" type="number" min="1" max="<?= e((string) $stock); ?>" value="1"></div>
                    <?php endif; ?>
                    <div class="purchase-area">
                        <?php if ($stock > 0): ?>
                            <form method="post" action="<?= e(site_url('cart.php')); ?>"><input type="hidden" name="product_id" value="<?= e((string) $product['id']); ?>"><input type="hidden" name="action" value="add"><input type="hidden" name="quantity" value="1" data-cart-quantity><button class="btn btn-primary" type="submit">Add to Cart</button></form>
                            <a class="btn btn-secondary" href="<?= e(site_url('cart.php')); ?>">View Cart</a>
                            <?php
                            $productMessage = 'Hello ' . site_name() . ", I would like to order:\n"
                                . 'Product: ' . $product['name'] . "\n"
                                . 'Product ID: #' . $product['id'] . "\n"
                                . "Quantity: 1\n"
                                . 'Price: ' . format_price((float) $product['price']) . "\n"
                                . 'Subtotal: ' . format_price((float) $product['price']) . "\n"
                                . 'Product page: ' . absolute_site_url('product.php?id=' . (int) $product['id']);
                            $productWhatsappUrl = whatsapp_url($pdo, $productMessage);
                            ?>
                            <?php if ($productWhatsappUrl !== null): ?>
                                <a class="btn btn-whatsapp" href="<?= e($productWhatsappUrl); ?>" target="_blank" rel="noopener noreferrer" data-whatsapp-product data-site-name="<?= e(site_name()); ?>" data-product-name="<?= e($product['name']); ?>" data-product-id="<?= e((string) $product['id']); ?>" data-product-price="<?= e(format_price((float) $product['price'])); ?>" data-product-unit-price="<?= e((string) $product['price']); ?>" data-product-url="<?= e(absolute_site_url('product.php?id=' . (int) $product['id'])); ?>"><span class="whatsapp-icon" aria-hidden="true">◉</span> Order on WhatsApp</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="trust-points"><span>✓ Premium quality</span><span>✓ Gift-ready packaging</span><span>✓ Fast delivery</span></div>
                </div>
            </div>
            <section class="product-description-card"><span class="eyebrow">Product details</span><h2>Product Description</h2><p><?= e($product['description'] ?: 'No description available.'); ?></p><div class="product-facts"><span><strong>Availability</strong><?= $stock > 0 ? 'In stock' : 'Out of stock'; ?></span><span><strong>Category</strong><?= e($product['category_name']); ?></span><span><strong>Product ID</strong>#<?= e((string) $product['id']); ?></span></div></section>
            <section class="detail-information"><article><span>✦</span><h3>Product highlights</h3><p>Thoughtfully selected and beautifully presented for meaningful gifting.</p></article><article><span>⌁</span><h3>Delivery information</h3><p>Carefully packed for a smooth delivery experience to your recipient.</p></article></section>
            <?php if ($relatedProducts): ?>
                <section class="related-products"><div class="section-heading"><span class="eyebrow">Keep exploring</span><h2>You may also like</h2></div><div class="product-grid"><?php foreach ($relatedProducts as $related): ?><article class="product-card"><div class="product-image-wrap"><?php $relatedImage = product_image_url($related['image']); ?><?php if ($relatedImage): ?><img src="<?= e($relatedImage); ?>" alt="<?= e($related['name']); ?>" loading="lazy"><?php else: ?><div class="fallback-image"><span aria-hidden="true">✦</span><small>Maison selection</small></div><?php endif; ?></div><div class="product-details"><span class="product-category"><?= e($related['category_name']); ?></span><h3><?= e($related['name']); ?></h3><p class="product-price"><?= e(format_price((float) $related['price'])); ?></p><a class="btn btn-primary product-card-button" href="<?= e(site_url('product.php?id=' . (int) $related['id'])); ?>">View Product</a></div></article><?php endforeach; ?></div></section>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
