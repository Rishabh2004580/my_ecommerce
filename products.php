<?php
require_once __DIR__ . '/config/database.php';

$pageTitle = 'Gift Collection | Maison Gift Co.';
$pdo = get_db_connection();
$products = [];
$categories = [];
$totalProducts = 0;
$loadError = '';

$search = trim((string) ($_GET['search'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));
$sort = (string) ($_GET['sort'] ?? 'newest');
$minPriceInput = trim((string) ($_GET['min_price'] ?? ''));
$maxPriceInput = trim((string) ($_GET['max_price'] ?? ''));
$page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$page = $page === false ? 1 : $page;
$perPage = 8;

$sortOptions = [
    'newest' => 'products.id DESC',
    'price_asc' => 'products.price ASC, products.id DESC',
    'price_desc' => 'products.price DESC, products.id DESC',
    'name_asc' => 'products.name ASC, products.id DESC',
];
if (!isset($sortOptions[$sort])) {
    $sort = 'newest';
}

$minPrice = null;
$maxPrice = null;
if ($minPriceInput !== '' && is_numeric($minPriceInput) && (float) $minPriceInput >= 0) {
    $minPrice = (float) $minPriceInput;
}
if ($maxPriceInput !== '' && is_numeric($maxPriceInput) && (float) $maxPriceInput >= 0) {
    $maxPrice = (float) $maxPriceInput;
}
if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
    [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
}

if ($pdo === null) {
    $loadError = 'Products are temporarily unavailable. Please try again later.';
} else {
    try {
        $categories = $pdo->query(
            'SELECT id, name, slug
             FROM categories
             ORDER BY name ASC'
        )->fetchAll();

        $conditions = ['products.status = :status'];
        $params = ['status' => 'active'];

        if ($search !== '') {
            $conditions[] = '(products.name LIKE :search_name OR products.description LIKE :search_description OR categories.name LIKE :search_category OR products.occasion LIKE :search_occasion)';
            $params['search_name'] = '%' . $search . '%';
            $params['search_description'] = '%' . $search . '%';
            $params['search_category'] = '%' . $search . '%';
            $params['search_occasion'] = '%' . $search . '%';
        }

        if ($category !== '') {
            if (ctype_digit($category)) {
                $conditions[] = 'products.category_id = :category_id';
                $params['category_id'] = (int) $category;
            } else {
                $conditions[] = 'categories.slug = :category_slug';
                $params['category_slug'] = $category;
            }
        }

        if ($minPrice !== null) {
            $conditions[] = 'products.price >= :min_price';
            $params['min_price'] = $minPrice;
        }
        if ($maxPrice !== null) {
            $conditions[] = 'products.price <= :max_price';
            $params['max_price'] = $maxPrice;
        }

        $whereSql = implode(' AND ', $conditions);
        $countStmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM products
             INNER JOIN categories ON products.category_id = categories.id
             WHERE ' . $whereSql
        );
        $countStmt->execute($params);
        $totalProducts = (int) $countStmt->fetchColumn();

        $totalPages = max(1, (int) ceil($totalProducts / $perPage));
        require_once __DIR__ . '/includes/header.php';
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $productStmt = $pdo->prepare(
            'SELECT products.id, products.name, products.description, products.price,
                    products.mrp, products.discount, products.occasion, products.stock, products.image, categories.name AS category_name
             FROM products
             INNER JOIN categories ON products.category_id = categories.id
             WHERE ' . $whereSql . '
             ORDER BY ' . $sortOptions[$sort] . '
             LIMIT :limit OFFSET :offset'
        );
        foreach ($params as $key => $value) {
            $productStmt->bindValue(':' . $key, $value);
        }
        $productStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $productStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $productStmt->execute();
        $products = $productStmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Product listing query failed: ' . $e->getMessage());
        $loadError = 'Products are temporarily unavailable. Please try again later.';
    }
}

$queryParams = [
    'search' => $search,
    'category' => $category,
    'sort' => $sort,
    'min_price' => $minPriceInput,
    'max_price' => $maxPriceInput,
];
$queryParams = array_filter($queryParams, static fn ($value): bool => $value !== '');
$totalPages = max(1, (int) ceil($totalProducts / $perPage));
?>

<section class="catalog-hero">
    <div class="container">
        <span class="eyebrow">The gift edit</span>
        <h1>Gifts with feeling.</h1>
        <p>Explore premium chocolates, hampers and thoughtful gifts for every occasion.</p>
    </div>
</section>

<section class="section-spacing">
    <div class="container">
        <form class="product-filters" method="get" action="<?= e(site_url('products.php')); ?>">
            <label>
                Search
                <input type="search" name="search" value="<?= e($search); ?>" placeholder="Search chocolates, gifts, occasions">
            </label>
            <label>
                Category
                <select name="category">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $categoryOption): ?>
                        <?php $categoryValue = (string) $categoryOption['slug']; ?>
                        <option value="<?= e($categoryValue); ?>" <?= $category === $categoryValue ? 'selected' : ''; ?>>
                            <?= e($categoryOption['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Sort
                <select name="sort">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : ''; ?>>Name: A-Z</option>
                </select>
            </label>
            <fieldset class="price-range"><legend>Price range</legend><div><input aria-label="Minimum price" type="number" name="min_price" min="0" step="0.01" placeholder="Min price" value="<?= e($minPriceInput); ?>"><span>—</span><input aria-label="Maximum price" type="number" name="max_price" min="0" step="0.01" placeholder="Max price" value="<?= e($maxPriceInput); ?>"></div></fieldset>
            <button class="btn btn-primary" type="submit">Apply filters</button>
            <a class="btn btn-secondary" href="<?= e(site_url('products.php')); ?>">Clear</a>
        </form>

        <?php if ($loadError !== ''): ?>
            <p class="empty-state"><?= e($loadError); ?></p>
        <?php elseif (!empty($products)): ?>
            <p class="results-summary">
                Showing <?= e((string) (($page - 1) * $perPage + 1)); ?>–<?= e((string) min($page * $perPage, $totalProducts)); ?>
                of <?= e((string) $totalProducts); ?> products
            </p>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <?php
                    $imageUrl = product_image_url($product['image'] ?? null);
                    $stock = (int) $product['stock'];
                    ?>
                    <article class="product-card">
                        <div class="product-image-wrap">
                            <button class="wishlist-button" type="button" aria-label="Add <?= e($product['name']); ?> to wishlist">♡</button>
                            <?php if ($imageUrl !== ''): ?>
                                <img src="<?= e($imageUrl); ?>" alt="<?= e($product['name']); ?>" loading="lazy" onerror="this.style.display='none'; this.parentElement.classList.add('fallback-image');">
                            <?php else: ?>
                                <div class="fallback-image"><span aria-hidden="true">✦</span><small>Maison selection</small></div>
                            <?php endif; ?>
                        </div>
                        <div class="product-details">
                            <span class="product-category"><?= e($product['category_name']); ?></span>
                            <h3><?= e($product['name']); ?></h3>
                            <div class="price-row"><strong><?= e(format_price((float) $product['price'])); ?></strong><?php $mrp = (float) ($product['mrp'] ?? 0); $discount = product_discount($mrp, (float) $product['price']); ?><?php if ($mrp > (float) $product['price']): ?><del><?= e(format_price($mrp)); ?></del><b><?= e((string) (int) $discount); ?>% OFF</b><?php endif; ?></div>
                            <p class="product-stock <?= $stock === 0 ? 'is-out' : ($stock <= 5 ? 'is-low' : ''); ?>"><?= $stock === 0 ? 'Out of stock' : ($stock <= 5 ? 'Only ' . $stock . ' left' : 'In stock · ' . $stock . ' left'); ?></p>
                            <div class="card-actions"><a class="btn btn-secondary product-card-button" href="<?= e(site_url('product.php?id=' . (int) $product['id'])); ?>">View</a><?php if ($stock > 0): ?><form method="post" action="<?= e(site_url('cart.php')); ?>"><input type="hidden" name="product_id" value="<?= e((string) $product['id']); ?>"><input type="hidden" name="action" value="add"><button class="btn btn-primary product-card-button" type="submit">Add to cart</button></form><?php endif; ?></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="Product pages">
                    <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                        <?php $pageLink = site_url('products.php?' . http_build_query(array_merge($queryParams, ['page' => $pageNumber]))); ?>
                        <a class="<?= $pageNumber === $page ? 'is-active' : ''; ?>" href="<?= e($pageLink); ?>">
                            <?= e((string) $pageNumber); ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <p class="empty-state">No products match your filters.</p>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
