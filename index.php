<?php
require_once __DIR__ . '/config/database.php';

$pageTitle = site_name() . ' | Premium Gifts & Chocolates';
$pdo = get_db_connection();
$products = [];
$categories = [];

if ($pdo) {
    try {
        $categories = $pdo->query('SELECT id, name, slug, description FROM categories ORDER BY id DESC LIMIT 8')->fetchAll();
        $categories = array_reverse($categories);
        $stmt = $pdo->prepare(
            'SELECT products.*, categories.name AS category_name
             FROM products INNER JOIN categories ON products.category_id = categories.id
             WHERE products.status = :status ORDER BY products.id DESC LIMIT 8'
        );
        $stmt->execute(['status' => 'active']);
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Homepage query failed: ' . $e->getMessage());
    }
}
require_once __DIR__ . '/includes/header.php';
?>

<section class="gift-hero">
    <div class="container gift-hero-inner">
        <div class="hero-copy">
            <span class="eyebrow">Made for meaningful moments</span>
            <h1>Make every moment <em>special.</em></h1>
            <p>Premium chocolates and thoughtful gifts, curated to make celebrations unforgettable.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="<?= e(site_url('products.php?search=chocolate')); ?>">Shop Chocolates</a>
                <a class="btn btn-secondary" href="<?= e(site_url('products.php')); ?>">Explore Gifts</a>
            </div>
        </div>
        <div class="hero-art" role="img" aria-label="Premium gift collection">
            <div class="hero-art-box"><span>With love</span><strong>For every<br>occasion</strong></div>
            <div class="hero-art-chocolate">✦</div>
        </div>
    </div>
</section>

<section class="section-spacing">
    <div class="container">
        <div class="section-heading"><span class="eyebrow">Find the perfect gesture</span><h2>Shop by occasion</h2></div>
        <div class="occasion-grid">
            <?php foreach (['Birthday' => 'For their brightest day', 'Anniversary' => 'Celebrate your story', 'Wedding' => 'For a beautiful beginning', 'Romantic' => 'A little love, wrapped', 'Congratulations' => 'Mark the milestone', 'Thank You' => 'Say it with feeling'] as $occasion => $description): ?>
                <a class="occasion-card" href="<?= e(site_url('products.php?search=' . rawurlencode($occasion))); ?>"><span><?= e(substr($occasion, 0, 1)); ?></span><strong><?= e($occasion); ?></strong><small><?= e($description); ?></small></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-spacing category-section">
    <div class="container">
        <div class="section-heading"><span class="eyebrow">Curated with care</span><h2>Shop by category</h2></div>
        <div class="category-feature-grid">
            <?php foreach ($categories as $category): ?>
                <a class="category-feature-card" href="<?= e(site_url('products.php?category=' . rawurlencode($category['slug']))); ?>"><span><?= e(substr($category['name'], 0, 1)); ?></span><strong><?= e($category['name']); ?></strong><small>Explore collection →</small></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-spacing soft-section">
    <div class="container">
        <div class="section-heading inline-heading"><div><span class="eyebrow">Loved by gift-givers</span><h2>Our bestsellers</h2></div><a class="inline-link" href="<?= e(site_url('products.php')); ?>">View all →</a></div>
        <?php if ($products): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <?php $imageUrl = product_image_url($product['image'] ?? null); $stock = (int) $product['stock']; $mrp = (float) ($product['mrp'] ?? 0); $discount = product_discount($mrp, (float) $product['price']); ?>
                    <article class="product-card">
                        <div class="product-image-wrap"><button class="wishlist-button" type="button" aria-label="Add <?= e($product['name']); ?> to wishlist">♡</button><?php if ($imageUrl): ?><img src="<?= e($imageUrl); ?>" alt="<?= e($product['name']); ?>" loading="lazy" onerror="this.style.display='none'; this.parentElement.classList.add('fallback-image');"><?php else: ?><div class="fallback-image"><span aria-hidden="true">✦</span><small>Maison selection</small></div><?php endif; ?></div>
                        <div class="product-details"><span class="product-category"><?= e($product['category_name']); ?></span><h3><?= e($product['name']); ?></h3><div class="price-row"><strong><?= e(format_price((float) $product['price'])); ?></strong><?php if ($mrp > (float) $product['price']): ?><del><?= e(format_price($mrp)); ?></del><b><?= e((string) (int) $discount); ?>% OFF</b><?php endif; ?></div><p class="product-stock <?= $stock === 0 ? 'is-out' : ''; ?>"><?= $stock > 0 ? '✓ In stock' : 'Out of stock'; ?></p><a class="btn btn-primary product-card-button" href="<?= e(site_url('product.php?id=' . (int) $product['id'])); ?>">View Product</a></div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?><p class="empty-state">No products are available right now.</p><?php endif; ?>
    </div>
</section>

<section class="section-spacing chocolate-highlight">
    <div class="container split-promo"><div><span class="eyebrow">A taste of luxury</span><h2>Luxury chocolates</h2><p>Make an ordinary day feel extraordinary with a box chosen just for them.</p><a class="btn btn-secondary" href="<?= e(site_url('products.php?search=chocolate')); ?>">Discover chocolates</a></div><div class="split-promo-art"><span>crafted<br>to delight</span></div></div>
</section>

<section class="section-spacing occasion-panels">
    <div class="container"><div class="section-heading"><span class="eyebrow">For every chapter</span><h2>Celebrate beautifully</h2></div><div class="occasion-panel-grid"><?php foreach (['Birthday', 'Anniversary', 'Wedding', 'Corporate'] as $occasion): ?><a href="<?= e(site_url('products.php?search=' . rawurlencode($occasion))); ?>"><span><?= e($occasion); ?></span><strong>Explore gifts →</strong></a><?php endforeach; ?></div></div>
</section>

<section class="section-spacing">
    <div class="container offer-banner"><div><span class="eyebrow">A little luxury, under ₹999</span><h2>Premium gifts for every budget.</h2><p>Small gestures. Beautifully wrapped.</p></div><a class="btn btn-primary" href="<?= e(site_url('products.php?max_price=999')); ?>">Shop the edit</a></div>
</section>

<section class="section-spacing trust-section">
    <div class="container"><div class="section-heading"><span class="eyebrow">The Maison promise</span><h2>Gifting made easy</h2></div><div class="benefit-grid"><article><div class="benefit-icon">✦</div><h3>Premium packaging</h3><p>Every gift is presented with care and attention to detail.</p></article><article><div class="benefit-icon">♡</div><h3>Thoughtfully curated</h3><p>Beautiful choices for the people and moments that matter.</p></article><article><div class="benefit-icon">✓</div><h3>Secure shopping</h3><p>A trusted, simple shopping experience from browse to delivery.</p></article></div></div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
