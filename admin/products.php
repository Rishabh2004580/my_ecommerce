<?php
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/admin-layout.php';
$pdo = require_admin_auth();
$pageTitle = 'Admin Products | ShopStore';
$flash = admin_get_flash();
if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

$stmt = $pdo->query(
    'SELECT products.id, products.name, products.price, products.stock, products.image,
            products.status, products.created_at, categories.name AS category_name
     FROM products
     INNER JOIN categories ON products.category_id = categories.id
     ORDER BY products.created_at DESC, products.id DESC'
);
$products = $stmt->fetchAll();
admin_header($pageTitle, 'products');
?>
<div class="admin-page-head"><div><span class="admin-eyebrow">Inventory</span><h1>Products</h1><p>Manage your store catalog.</p></div><a class="admin-btn admin-btn-primary" href="<?= e(site_url('admin/add-product.php')); ?>">+ Add Product</a></div>
<?php if ($flash): ?><p class="admin-flash <?= e($flash['type']); ?>"><?= e($flash['message']); ?></p><?php endif; ?>
<div class="admin-card">
    <div class="admin-card-head"><h2>All Products</h2><span><?= e((string) count($products)); ?> items</span></div>
    <div class="admin-card-body">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th><th>Name</th><th>Category</th><th>Price</th>
                        <th>Stock</th><th>Status</th><th>Created</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <?php if (!empty($product['image'])): ?>
                                    <img class="admin-product-thumb" src="<?= e(product_image_url($product['image'])); ?>" alt="">
                                <?php else: ?>
                                    <span class="admin-product-placeholder">✦</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($product['name']); ?></td>
                            <td><?= e($product['category_name']); ?></td>
                            <td><?= e(format_price((float) $product['price'])); ?></td>
                            <td><?= e((string) $product['stock']); ?></td>
                            <td><span class="status-pill <?= e($product['status']); ?>"><?= e(ucfirst($product['status'])); ?></span></td>
                            <td><?= e(date('Y-m-d', strtotime($product['created_at']))); ?></td>
                            <td class="admin-actions">
                                <a class="admin-btn admin-btn-light" href="<?= e(site_url('admin/edit-product.php?id=' . (int) $product['id'])); ?>">Edit</a>
                                <?php if ($product['status'] === 'active'): ?>
                                    <form method="post" action="<?= e(site_url('admin/delete-product.php')); ?>">
                                        <input type="hidden" name="id" value="<?= e((string) $product['id']); ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['admin_csrf'] ?? ''); ?>">
                                        <button class="admin-btn admin-btn-light" type="submit" onclick="return confirm('Deactivate this product?');">Deactivate</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$products): ?>
                        <tr><td colspan="8">No products found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php admin_footer(); ?>
