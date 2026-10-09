<?php

require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/admin-layout.php';
require_once __DIR__ . '/../config/whatsapp.php';

$pdo = require_admin_auth();
$errors = [];
$flash = admin_get_flash();
$stmt = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = :setting_key LIMIT 1');
$stmt->execute(['setting_key' => 'whatsapp_business_number']);
$savedNumber = (string) ($stmt->fetchColumn() ?: '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedNumber = trim((string) ($_POST['business_whatsapp_number'] ?? ''));

    if (!admin_verify_csrf()) {
        $errors[] = 'The form expired. Please try again.';
    } else {
        $normalizedNumber = normalize_whatsapp_number($submittedNumber);
        if ($normalizedNumber === null) {
            $errors[] = 'Enter a valid international WhatsApp number with country code, such as 919876543210.';
        } else {
            $save = $pdo->prepare(
                'INSERT INTO settings (setting_key, setting_value)
                 VALUES (:setting_key, :setting_value)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            );
            $save->execute([
                'setting_key' => 'whatsapp_business_number',
                'setting_value' => $normalizedNumber,
            ]);
            admin_set_flash('success', 'WhatsApp number updated successfully.');
            redirect('admin/whatsapp-settings.php');
        }
    }

    $savedNumber = $submittedNumber;
}

admin_header('WhatsApp Settings | Maison Gift Co.', 'whatsapp-settings');
?>
<div class="admin-page-head">
    <div>
        <span class="admin-eyebrow">Customer enquiries</span>
        <h1>WhatsApp Settings</h1>
        <p>Configure the number customers use to send product and cart enquiries.</p>
    </div>
</div>
<?php if ($flash): ?><p class="admin-flash <?= e($flash['type']); ?>"><?= e($flash['message']); ?></p><?php endif; ?>
<?php if (!$savedNumber): ?><p class="admin-flash error">WhatsApp ordering is currently disabled. Configure a business number to show WhatsApp order buttons to customers.</p><?php endif; ?>
<?php if ($errors): ?><div class="admin-flash error"><?php foreach ($errors as $error): ?><p><?= e($error); ?></p><?php endforeach; ?></div><?php endif; ?>
<section class="admin-card admin-form-shell">
    <div class="admin-card-head"><h2>Business WhatsApp Number</h2></div>
    <div class="admin-card-body">
        <form class="admin-form" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(admin_csrf_token()); ?>">
            <label>Business WhatsApp Number
                <input type="tel" name="business_whatsapp_number" value="<?= e($savedNumber); ?>" placeholder="919876543210" inputmode="tel" autocomplete="tel" required>
            </label>
            <p class="admin-muted">Include the country code. Spaces, plus signs, parentheses, and dashes are accepted and stored as digits only.</p>
            <button class="admin-btn admin-btn-primary" type="submit">Save WhatsApp Number</button>
        </form>
    </div>
</section>
<?php admin_footer(); ?>
