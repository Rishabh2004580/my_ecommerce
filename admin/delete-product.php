<?php
require_once __DIR__ . '/../includes/admin-auth.php';
$pdo = require_admin_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['admin_csrf']) || !hash_equals($_SESSION['admin_csrf'], (string) ($_POST['csrf_token'] ?? ''))) {
    http_response_code(400);
    admin_set_flash('error', 'Invalid deactivation request.');
    redirect('admin/products.php');
}

$productId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($productId === false || $productId === null) {
    admin_set_flash('error', 'Invalid product.');
    redirect('admin/products.php');
}

$stmt = $pdo->prepare("UPDATE products SET status = 'inactive' WHERE id = :id AND status = 'active'");
$stmt->execute(['id' => $productId]);
admin_set_flash($stmt->rowCount() === 1 ? 'success' : 'error', $stmt->rowCount() === 1 ? 'Product deactivated.' : 'Product was not found or was already inactive.');
redirect('admin/products.php');
