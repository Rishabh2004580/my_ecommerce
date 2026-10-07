<?php
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/admin-layout.php';
$pdo = require_admin_auth();
$categories = $pdo->query('SELECT categories.name, categories.slug, COUNT(products.id) AS product_count FROM categories LEFT JOIN products ON products.category_id = categories.id GROUP BY categories.id, categories.name, categories.slug ORDER BY categories.name')->fetchAll();
admin_header('Admin Categories | ShopStore', 'categories');
?>
<div class="admin-page-head"><div><span class="admin-eyebrow">Catalog</span><h1>Categories</h1><p>Product categories currently in your store.</p></div></div>
<div class="admin-card"><div class="admin-card-head"><h2>All Categories</h2></div><div class="admin-card-body"><div class="admin-list"><?php foreach ($categories as $category): ?><div class="admin-list-row"><div><strong><?= e($category['name']); ?></strong><small><?= e($category['slug']); ?></small></div><span><?= e((string) $category['product_count']); ?> products</span></div><?php endforeach; ?></div></div></div>
<?php admin_footer(); ?>
