<?php

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/database.php';

function require_admin_auth(): PDO
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $adminId = filter_var($_SESSION['admin_id'] ?? null, FILTER_VALIDATE_INT);
    $pdo = get_db_connection();

    if ($adminId === false || $adminId === null || $pdo === null) {
        redirect('admin/login.php');
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, name, email, role FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $adminId]);
    $admin = $stmt->fetch();

    if (!$admin || $admin['role'] !== 'admin') {
        unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email'], $_SESSION['admin_role']);
        redirect('admin/login.php');
        exit;
    }

    function admin_csrf_token(): string
    {
        if (empty($_SESSION['admin_csrf'])) {
            $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['admin_csrf'];
    }

    function admin_verify_csrf(): bool
    {
        $token = (string) ($_POST['csrf_token'] ?? '');
        return $token !== '' && !empty($_SESSION['admin_csrf'])
            && hash_equals($_SESSION['admin_csrf'], $token);
    }

    $_SESSION['admin_name'] = $admin['name'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_role'] = $admin['role'];

    return $pdo;
}

function admin_set_flash(string $type, string $message): void
{
    $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
}

function admin_get_flash(): ?array
{
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);

    return is_array($flash) ? $flash : null;
}

function product_upload(string $field, ?string $currentImage = null): array
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return ['path' => $currentImage, 'error' => null, 'uploaded' => false];
    }

    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['path' => $currentImage, 'error' => 'The image upload failed.', 'uploaded' => false];
    }

    if (!is_uploaded_file($file['tmp_name']) || $file['size'] <= 0 || $file['size'] > 5 * 1024 * 1024) {
        return ['path' => $currentImage, 'error' => 'Images must be valid uploads no larger than 5 MB.', 'uploaded' => false];
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($extensions[$mime]) || @getimagesize($file['tmp_name']) === false) {
        return ['path' => $currentImage, 'error' => 'Only JPEG, PNG, or WebP images are allowed.', 'uploaded' => false];
    }

    $directory = __DIR__ . '/../assets/images/products';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        return ['path' => $currentImage, 'error' => 'The image directory could not be created.', 'uploaded' => false];
    }

    try {
        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    } catch (Throwable $exception) {
        return ['path' => $currentImage, 'error' => 'A secure image filename could not be generated.', 'uploaded' => false];
    }

    if (!move_uploaded_file($file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
        error_log('Admin product image upload failed for temporary file.');
        return ['path' => $currentImage, 'error' => 'The image could not be saved.', 'uploaded' => false];
    }

    return [
        'path' => 'assets/images/products/' . $filename,
        'error' => null,
        'uploaded' => true,
    ];
}

function remove_product_image(?string $image): void
{
    if (!$image || strpos($image, 'assets/images/products/') !== 0) {
        return;
    }

    $file = realpath(__DIR__ . '/../' . $image);
    $directory = realpath(__DIR__ . '/../assets/images/products');
    if ($file !== false && $directory !== false && strpos($file, $directory . DIRECTORY_SEPARATOR) === 0 && is_file($file)) {
        unlink($file);
    }
}
