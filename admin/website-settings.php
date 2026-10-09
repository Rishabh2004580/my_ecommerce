<?php

require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/admin-layout.php';

$pdo = require_admin_auth();
$errors = [];
$flash = admin_get_flash();
$stmt = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = :setting_key LIMIT 1');
$stmt->execute(['setting_key' => 'website_name']);
$savedName = (string) ($stmt->fetchColumn() ?: 'Maison Gift Co.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedName = trim((string) ($_POST['website_name'] ?? ''));

    if (!admin_verify_csrf()) {
        $errors[] = 'The form expired. Please try again.';
    } elseif ($submittedName === '' || strlen($submittedName) > 120) {
        $errors[] = 'Enter a website name between 1 and 120 characters.';
    } elseif (preg_match('/[\x00-\x1F\x7F]/', $submittedName)) {
        $errors[] = 'The website name contains invalid control characters.';
    } else {
        $save = $pdo->prepare(
            'INSERT INTO settings (setting_key, setting_value)
             VALUES (:setting_key, :setting_value)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $save->execute([
            'setting_key' => 'website_name',
            'setting_value' => $submittedName,
        ]);
        admin_set_flash('success', 'Website name updated successfully.');
        redirect('admin/website-settings.php');
    }

    $savedName = $submittedName;
}

admin_header('Website Settings | ' . $savedName, 'website-settings');
?>
<div class="admin-page-head">
    <div>
        <span class="admin-eyebrow">Site configuration</span>
        <h1>Website Settings</h1>
        <p>Update the name displayed across the public website and Admin Panel.</p>
    </div>
</div>
<?php if ($flash): ?><p class="admin-flash <?= e($flash['type']); ?>"><?= e($flash['message']); ?></p><?php endif; ?>
<?php if ($errors): ?><div class="admin-flash error"><?php foreach ($errors as $error): ?><p><?= e($error); ?></p><?php endforeach; ?></div><?php endif; ?>
<section class="admin-card admin-form-shell">
    <div class="admin-card-head"><h2>Website Name</h2></div>
    <div class="admin-card-body">
        <form class="admin-form" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(admin_csrf_token()); ?>">
            <label for="website-name">Website Name
                <input id="website-name" type="text" name="website_name" value="<?= e($savedName); ?>" maxlength="120" autocomplete="organization" required>
            </label>
            <p class="admin-muted">This name is used for public branding, page titles, and Admin Panel branding.</p>
            <button class="admin-btn admin-btn-primary" type="submit">Save Website Name</button>
        </form>
    </div>
</section>
<?php admin_footer(); ?>
