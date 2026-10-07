<?php if ($errors): ?><div class="admin-errors"><?php foreach ($errors as $error): ?><p><?= e($error); ?></p><?php endforeach; ?></div><?php endif; ?>
<form class="admin-card admin-form" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['admin_csrf']); ?>">
    <?php if (!empty($product['id'])): ?><input type="hidden" name="id" value="<?= e((string) $product['id']); ?>"><?php endif; ?>
    <div class="admin-card-head"><h2>Product Information</h2></div>
    <div class="admin-card-body">
    <label>Product name<input required maxlength="180" type="text" name="name" value="<?= e($values['name']); ?>"></label>
    <label>Category<select required name="category_id"><option value="">Select category</option>
        <?php foreach ($categories as $category): ?><option value="<?= e((string) $category['id']); ?>" <?= (string) $values['category_id'] === (string) $category['id'] ? 'selected' : ''; ?>><?= e($category['name']); ?></option><?php endforeach; ?>
    </select></label>
    <label>Occasion<input maxlength="120" type="text" name="occasion" placeholder="Birthday, anniversary, wedding..." value="<?= e($values['occasion'] ?? ''); ?>"></label>
    <label>Description<textarea name="description" rows="5"><?= e($values['description']); ?></textarea></label>
    <label>Price<input required min="0" step="0.01" type="number" name="price" value="<?= e($values['price']); ?>"></label>
    <label>MRP<input min="0" step="0.01" type="number" name="mrp" value="<?= e($values['mrp'] ?? ''); ?>"></label>
    <label>Discount (%)<input min="0" max="100" step="0.01" type="number" name="discount" value="<?= e($values['discount'] ?? ''); ?>"></label>
    <label>Stock<input required min="0" step="1" type="number" name="stock" value="<?= e($values['stock']); ?>"></label>
    </div>
    <div class="admin-card-body admin-upload-card">
        <div class="admin-upload-zone">
            <?php if (!empty($values['image'])): ?><img class="admin-product-thumb" src="<?= e(product_image_url($values['image'])); ?>" alt=""><strong>Replace product image</strong><?php else: ?><strong>Upload product image</strong><?php endif; ?>
            <span>JPG, PNG or WebP · Maximum 5MB</span>
            <input accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" type="file" name="image">
        </div>
    </div>
    <div class="admin-card-body">
    <label>Status<select required name="status"><option value="active" <?= $values['status'] === 'active' ? 'selected' : ''; ?>>Active</option><option value="inactive" <?= $values['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option></select></label>
    <div class="admin-actions"><button class="admin-btn admin-btn-primary" type="submit">Save Product</button><a class="admin-btn admin-btn-light" href="<?= e(site_url('admin/products.php')); ?>">Cancel</a></div>
    </div>
</form>
