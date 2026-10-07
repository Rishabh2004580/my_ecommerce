<?php
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/admin-layout.php';
$pdo = require_admin_auth();
$pageTitle = 'Edit Product | ShopStore';
$errors = [];
$productId = filter_var($_GET['id'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$product = null;

if ($productId !== false && $productId !== null) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
    $stmt->execute(['id' => $productId]);
    $product = $stmt->fetch();
}
if (!$product) {
    http_response_code(404);
    $pageTitle = 'Product Not Found | ShopStore';
    admin_header($pageTitle, 'products');
    echo '<div class="admin-card admin-card-body"><h1>Product not found</h1><a class="admin-btn admin-btn-primary" href="' . e(site_url('admin/products.php')) . '">Back to products</a></div>';
    admin_footer();
    exit;
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$values = $product;
if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['name'] = trim((string) ($_POST['name'] ?? ''));
    $values['category_id'] = trim((string) ($_POST['category_id'] ?? ''));
    $values['occasion'] = trim((string) ($_POST['occasion'] ?? ''));
    $values['description'] = trim((string) ($_POST['description'] ?? ''));
    $values['price'] = trim((string) ($_POST['price'] ?? ''));
    $values['mrp'] = trim((string) ($_POST['mrp'] ?? ''));
    $values['discount'] = trim((string) ($_POST['discount'] ?? ''));
    $values['stock'] = trim((string) ($_POST['stock'] ?? ''));
    $values['status'] = (string) ($_POST['status'] ?? '');
    if (!hash_equals($_SESSION['admin_csrf'], (string) ($_POST['csrf_token'] ?? ''))) $errors[] = 'The form expired. Please try again.';
    if ($values['name'] === '' || strlen($values['name']) > 180) $errors[] = 'Product name is required and must be 180 characters or fewer.';
    if (!ctype_digit($values['category_id'])) $errors[] = 'Please select a valid category.';
    if (!is_numeric($values['price']) || (float) $values['price'] < 0 || (float) $values['price'] > 99999999.99) $errors[] = 'Enter a valid non-negative price.';
    if ($values['mrp'] !== '' && (!is_numeric($values['mrp']) || (float) $values['mrp'] < 0)) $errors[] = 'Enter a valid MRP.';
    if ($values['discount'] !== '' && (!is_numeric($values['discount']) || (float) $values['discount'] < 0 || (float) $values['discount'] > 100)) $errors[] = 'Discount must be between 0 and 100.';
    if (!ctype_digit($values['stock']) || (int) $values['stock'] < 0) $errors[] = 'Enter a valid non-negative stock quantity.';
    if (!in_array($values['status'], ['active', 'inactive'], true)) $errors[] = 'Please select a valid status.';
    $categoryStmt = $pdo->prepare('SELECT id FROM categories WHERE id = :id');
    $categoryStmt->execute(['id' => (int) $values['category_id']]);
    if (!$categoryStmt->fetchColumn()) $errors[] = 'The selected category does not exist.';
    $upload = product_upload('image', $product['image']);
    if ($upload['error'] !== null) $errors[] = $upload['error'];

    if (!$errors) {
        $stmt = $pdo->prepare(
            'UPDATE products SET category_id = :category_id, occasion = :occasion, name = :name, description = :description,
             price = :price, mrp = :mrp, discount = :discount, stock = :stock, image = :image, status = :status WHERE id = :id'
        );
        $stmt->execute([
            'category_id' => (int) $values['category_id'],
            'occasion' => $values['occasion'] !== '' ? $values['occasion'] : null,
            'name' => $values['name'],
            'description' => $values['description'],
            'price' => number_format((float) $values['price'], 2, '.', ''),
            'mrp' => $values['mrp'] !== '' ? number_format((float) $values['mrp'], 2, '.', '') : null,
            'discount' => $values['discount'] !== '' ? number_format((float) $values['discount'], 2, '.', '') : null,
            'stock' => (int) $values['stock'],
            'image' => $upload['path'],
            'status' => $values['status'],
            'id' => $productId,
        ]);
        if ($upload['uploaded']) remove_product_image($product['image']);
        admin_set_flash('success', 'Product updated successfully.');
        redirect('admin/products.php');
    }
}
admin_header($pageTitle, 'products');
?>
<div class="admin-page-head"><div><span class="admin-eyebrow">Inventory</span><h1>Edit Product</h1><p>Update product details and availability.</p></div></div>
<div class="admin-form-shell"><?php require __DIR__ . '/product-form.php'; ?></div>
<?php admin_footer(); ?>
