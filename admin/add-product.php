<?php
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/admin-layout.php';
$pdo = require_admin_auth();
$pageTitle = 'Add Product | Maison Gift Co.';
$errors = [];
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$values = ['name' => '', 'category_id' => '', 'occasion' => '', 'description' => '', 'price' => '', 'mrp' => '', 'discount' => '', 'stock' => '', 'status' => 'active', 'image' => ''];

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

    if (!admin_verify_csrf()) {
        $errors[] = 'The form expired. Please try again.';
    }
    if ($values['name'] === '' || strlen($values['name']) > 180) {
        $errors[] = 'Product name is required and must be 180 characters or fewer.';
    }
    if (!ctype_digit($values['category_id'])) {
        $errors[] = 'Please select a valid category.';
    }
    if (!is_numeric($values['price']) || (float) $values['price'] < 0 || (float) $values['price'] > 99999999.99) {
        $errors[] = 'Enter a valid non-negative price.';
    }
    if ($values['mrp'] !== '' && (!is_numeric($values['mrp']) || (float) $values['mrp'] < 0)) $errors[] = 'Enter a valid MRP.';
    if ($values['discount'] !== '' && (!is_numeric($values['discount']) || (float) $values['discount'] < 0 || (float) $values['discount'] > 100)) $errors[] = 'Discount must be between 0 and 100.';
    if (!ctype_digit($values['stock']) || (int) $values['stock'] < 0) {
        $errors[] = 'Enter a valid non-negative stock quantity.';
    }
    if (!in_array($values['status'], ['active', 'inactive'], true)) {
        $errors[] = 'Please select a valid status.';
    }

    $categoryStmt = $pdo->prepare('SELECT id FROM categories WHERE id = :id');
    $categoryStmt->execute(['id' => (int) $values['category_id']]);
    if (!$categoryStmt->fetchColumn()) {
        $errors[] = 'The selected category does not exist.';
    }

    if (!$errors) {
        $upload = product_upload('image');
        if ($upload['error'] !== null) {
            $errors[] = $upload['error'];
        }
    }

    if (!$errors) {
        $slugBase = trim((string) preg_replace('/[^a-z0-9]+/i', '-', strtolower($values['name'])), '-');
        $slug = ($slugBase !== '' ? $slugBase : 'product') . '-' . bin2hex(random_bytes(5));
        $stmt = $pdo->prepare(
            'INSERT INTO products (category_id, occasion, name, slug, description, price, mrp, discount, stock, image, status)
             VALUES (:category_id, :occasion, :name, :slug, :description, :price, :mrp, :discount, :stock, :image, :status)'
        );
        $stmt->execute([
            'category_id' => (int) $values['category_id'],
            'occasion' => $values['occasion'] !== '' ? $values['occasion'] : null,
            'name' => $values['name'],
            'slug' => $slug,
            'description' => $values['description'],
            'price' => number_format((float) $values['price'], 2, '.', ''),
            'mrp' => $values['mrp'] !== '' ? number_format((float) $values['mrp'], 2, '.', '') : null,
            'discount' => $values['discount'] !== '' ? number_format((float) $values['discount'], 2, '.', '') : null,
            'stock' => (int) $values['stock'],
            'image' => $upload['path'],
            'status' => $values['status'],
        ]);
        admin_set_flash('success', 'Product added successfully.');
        redirect('admin/products.php');
    }
}
admin_header($pageTitle, 'products');
?>
<div class="admin-page-head"><div><span class="admin-eyebrow">Inventory</span><h1>Add Product</h1><p>Create a new product for your store.</p></div></div>
<div class="admin-form-shell"><?php require __DIR__ . '/product-form.php'; ?></div>
<?php admin_footer(); ?>
