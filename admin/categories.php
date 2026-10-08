<?php
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/admin-layout.php';
$pdo = require_admin_auth();
$errors = [];
$flash = admin_get_flash();
$editId = filter_var($_GET['edit'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$editing = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT id, name, slug, description FROM categories WHERE id = :id');
    $stmt->execute(['id' => $editId]);
    $editing = $stmt->fetch() ?: null;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_verify_csrf()) $errors[] = 'The form expired. Please try again.';
    $action = (string) ($_POST['action'] ?? '');
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($action === 'delete') {
        if (!$id) $errors[] = 'Invalid category.';
        if (!$errors) {
            $check = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = :id');
            $check->execute(['id' => $id]);
            if ((int) $check->fetchColumn() > 0) {
                admin_set_flash('error', 'This category has products and cannot be deleted. Move products first.');
            } else {
                $delete = $pdo->prepare('DELETE FROM categories WHERE id = :id');
                $delete->execute(['id' => $id]);
                admin_set_flash($delete->rowCount() ? 'success' : 'error', $delete->rowCount() ? 'Category deleted.' : 'Category not found.');
            }
            redirect('admin/categories.php');
        }
    } elseif ($action === 'save') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        if ($name === '' || strlen($name) > 120) $errors[] = 'Category name is required and must be 120 characters or fewer.';
        if (strlen($description) > 65535) $errors[] = 'Description is too long.';
        $id = $id ?: null;
        $slug = trim((string) preg_replace('/[^a-z0-9]+/i', '-', strtolower($name)), '-');
        if ($slug === '') $errors[] = 'Enter a valid category name.';
        if (!$errors) {
            $duplicate = $pdo->prepare('SELECT id FROM categories WHERE slug = :slug AND id <> COALESCE(:id, 0)');
            $duplicate->execute(['slug' => $slug, 'id' => $id]);
            if ($duplicate->fetchColumn()) $errors[] = 'A category with this name already exists.';
        }
        if (!$errors) {
            if ($id) {
                $stmt = $pdo->prepare('UPDATE categories SET name = :name, slug = :slug, description = :description WHERE id = :id');
                $stmt->execute(['name' => $name, 'slug' => $slug, 'description' => $description ?: null, 'id' => $id]);
                admin_set_flash('success', 'Category updated.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :description)');
                $stmt->execute(['name' => $name, 'slug' => $slug, 'description' => $description ?: null]);
                admin_set_flash('success', 'Category added.');
            }
            redirect('admin/categories.php');
        }
    }
}
$categories = $pdo->query('SELECT categories.id, categories.name, categories.slug, categories.description, COUNT(products.id) AS product_count FROM categories LEFT JOIN products ON products.category_id = categories.id GROUP BY categories.id, categories.name, categories.slug, categories.description ORDER BY categories.name')->fetchAll();
admin_header('Categories | Maison Gift Co.', 'categories');
?>
<div class="admin-page-head"><div><span class="admin-eyebrow">Catalog</span><h1>Categories</h1><p>Manage existing gifting categories safely.</p></div></div>
<?php if ($flash): ?><p class="admin-flash <?= e($flash['type']); ?>"><?= e($flash['message']); ?></p><?php endif; ?><?php if ($errors): ?><div class="admin-errors"><?php foreach ($errors as $error): ?><p><?= e($error); ?></p><?php endforeach; ?></div><?php endif; ?>
<div class="admin-grid-two"><section class="admin-card"><div class="admin-card-head"><h2><?= $editing ? 'Edit category' : 'Add category'; ?></h2></div><div class="admin-card-body"><form class="admin-form" method="post"><input type="hidden" name="csrf_token" value="<?= e(admin_csrf_token()); ?>"><input type="hidden" name="action" value="save"><?php if ($editing): ?><input type="hidden" name="id" value="<?= e((string) $editing['id']); ?>"><?php endif; ?><label>Name<input required maxlength="120" name="name" value="<?= e((string) ($editing['name'] ?? '')); ?>"></label><label>Description<textarea name="description" rows="4"><?= e((string) ($editing['description'] ?? '')); ?></textarea></label><div class="admin-actions"><button class="admin-btn admin-btn-primary" type="submit"><?= $editing ? 'Update' : 'Add'; ?> Category</button><?php if ($editing): ?><a class="admin-btn admin-btn-light" href="<?= e(site_url('admin/categories.php')); ?>">Cancel</a><?php endif; ?></div></form></div></section>
<section class="admin-card"><div class="admin-card-head"><h2>Existing categories</h2><span><?= e((string) count($categories)); ?></span></div><div class="admin-card-body"><div class="admin-list"><?php foreach ($categories as $category): ?><div class="admin-list-row"><div><strong><?= e($category['name']); ?></strong><small><?= e((string) $category['product_count']); ?> products · <?= e($category['slug']); ?></small></div><div class="admin-actions"><a class="admin-btn admin-btn-light" href="?edit=<?= e((string) $category['id']); ?>">Edit</a><?php if ((int) $category['product_count'] === 0): ?><form method="post" onsubmit="return confirm('Delete this empty category?');"><input type="hidden" name="csrf_token" value="<?= e(admin_csrf_token()); ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string) $category['id']); ?>"><button class="admin-btn admin-btn-light" type="submit">Delete</button></form><?php endif; ?></div></div><?php endforeach; ?><?php if (!$categories): ?><p class="admin-muted">No categories exist yet.</p><?php endif; ?></div></div></section></div>
<?php admin_footer(); ?>
