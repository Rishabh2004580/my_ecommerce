<?php
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/admin-layout.php';
$pdo = require_admin_auth();

$search = trim((string) ($_GET['search'] ?? ''));
$categoryId = filter_var($_GET['category_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$status = (string) ($_GET['status'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;
$conditions = [];
$params = [];
if ($search !== '') {
    $conditions[] = '(products.name LIKE :search OR products.description LIKE :search)';
    $params['search'] = '%' . $search . '%';
}
if ($categoryId !== false && $categoryId !== null) {
    $conditions[] = 'products.category_id = :category_id';
    $params['category_id'] = $categoryId;
}
if (in_array($status, ['active', 'inactive'], true)) {
    $conditions[] = 'products.status = :status';
    $params['status'] = $status;
}
$where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
$count = $pdo->prepare("SELECT COUNT(*) FROM products $where");
$count->execute($params);
$total = (int) $count->fetchColumn();
$pages = max(1, (int) ceil($total / $perPage));
$page = min($page, $pages);
$stmt = $pdo->prepare(
    "SELECT products.id, products.name, products.price, products.mrp, products.stock, products.image,
            products.status, categories.name AS category_name
     FROM products INNER JOIN categories ON products.category_id = categories.id
     $where ORDER BY products.created_at DESC, products.id DESC LIMIT :limit OFFSET :offset"
);
foreach ($params as $key => $value) $stmt->bindValue(':' . $key, $value);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$flash = admin_get_flash();
admin_header('Products | Maison Gift Co.', 'products');
?>
<div class="admin-page-head"><div><span class="admin-eyebrow">Inventory</span><h1>Products</h1><p>Manage your real catalog without changing customer order history.</p></div><a class="admin-btn admin-btn-primary" href="<?= e(site_url('admin/add-product.php')); ?>">+ Add Product</a></div>
<?php if ($flash): ?><p class="admin-flash <?= e($flash['type']); ?>"><?= e($flash['message']); ?></p><?php endif; ?>
<div class="admin-card">
    <div class="admin-card-body">
        <form class="admin-filter-form" method="get">
            <input type="search" name="search" placeholder="Search products..." value="<?= e($search); ?>">
            <select name="category_id"><option value="">All categories</option><?php foreach ($categories as $category): ?><option value="<?= e((string) $category['id']); ?>" <?= (string) $categoryId === (string) $category['id'] ? 'selected' : ''; ?>><?= e($category['name']); ?></option><?php endforeach; ?></select>
            <select name="status"><option value="">All statuses</option><option value="active" <?= $status === 'active' ? 'selected' : ''; ?>>Active</option><option value="inactive" <?= $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option></select>
            <button class="admin-btn admin-btn-primary" type="submit">Filter</button>
            <a class="admin-btn admin-btn-light" href="<?= e(site_url('admin/products.php')); ?>">Reset</a>
        </form>
    </div>
    <div class="admin-card-head"><h2>Catalog</h2><span><?= e((string) $total); ?> items</span></div>
    <div class="admin-card-body"><div class="admin-table-wrap"><table class="admin-table">
        <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>MRP</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody><?php foreach ($products as $product): ?><tr>
            <td><?php $imageUrl = product_image_url($product['image']); ?><?php if ($imageUrl): ?><img class="admin-product-thumb" src="<?= e($imageUrl); ?>" alt=""><?php else: ?><span class="admin-product-placeholder">-</span><?php endif; ?></td>
            <td><?= e($product['name']); ?></td><td><?= e($product['category_name']); ?></td>
            <td><?= e(format_price((float) $product['price'])); ?></td><td><?= $product['mrp'] !== null ? e(format_price((float) $product['mrp'])) : '-'; ?></td>
            <td><?= e((string) $product['stock']); ?></td><td><span class="admin-badge <?= e($product['status']); ?>"><?= e(ucfirst($product['status'])); ?></span></td>
            <td class="admin-actions"><a class="admin-btn admin-btn-light" href="<?= e(site_url('admin/edit-product.php?id=' . (int) $product['id'])); ?>">Edit</a><?php if ($product['status'] === 'active'): ?><form method="post" action="<?= e(site_url('admin/delete-product.php')); ?>"><input type="hidden" name="id" value="<?= e((string) $product['id']); ?>"><input type="hidden" name="csrf_token" value="<?= e(admin_csrf_token()); ?>"><button class="admin-btn admin-btn-light" type="submit" onclick="return confirm('Deactivate this product? Existing order history will be preserved.');">Deactivate</button></form><?php endif; ?></td>
        </tr><?php endforeach; ?><?php if (!$products): ?><tr><td colspan="8">No products match these filters.</td></tr><?php endif; ?></tbody>
    </table></div>
    <?php if ($pages > 1): ?><nav class="admin-pagination"><?php for ($number = 1; $number <= $pages; $number++): ?><a class="<?= $number === $page ? 'is-active' : ''; ?>" href="?<?= e(http_build_query(['search' => $search, 'category_id' => $categoryId ?: '', 'status' => $status, 'page' => $number])); ?>"><?= e((string) $number); ?></a><?php endfor; ?></nav><?php endif; ?>
    </div>
</div>
<?php admin_footer(); ?>
