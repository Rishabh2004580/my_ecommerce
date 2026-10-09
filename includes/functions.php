<?php

function start_app_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

start_app_session();

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function site_url(string $path = ''): string
{
    return '/ecommerce/' . ltrim($path, '/');
}

function asset_url(string $path = ''): string
{
    return site_url('assets/' . ltrim($path, '/'));
}

function site_setting(string $key, string $default = ''): string
{
    require_once __DIR__ . '/../config/database.php';
    $pdo = get_db_connection();
    if ($pdo === null) {
        return $default;
    }

    $stmt = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = :setting_key LIMIT 1');
    $stmt->execute(['setting_key' => $key]);
    $value = $stmt->fetchColumn();

    return is_string($value) && trim($value) !== '' ? trim($value) : $default;
}

function site_name(): string
{
    return site_setting('website_name', 'Maison Gift Co.');
}

function absolute_site_url(string $path = ''): string
{
    $configuredBaseUrl = getenv('MAISON_SITE_URL');
    if ($configuredBaseUrl !== false && trim($configuredBaseUrl) !== '') {
        return rtrim($configuredBaseUrl, '/') . '/' . ltrim($path, '/');
    }

    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    if (!preg_match('/^[A-Za-z0-9.-]+(?::\d+)?$/', $host)) {
        $host = 'localhost';
    }
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    return ($isHttps ? 'https' : 'http') . '://' . $host . '/' . ltrim(site_url($path), '/');
}

function whatsapp_url(PDO $pdo, string $message): ?string
{
    require_once __DIR__ . '/../config/whatsapp.php';
    $businessNumber = whatsapp_business_number($pdo);

    if ($businessNumber === '') {
        return null;
    }

    return 'https://wa.me/' . $businessNumber . '?text=' . rawurlencode($message);
}

function product_image_url(?string $path): string
{
    $path = ltrim((string) $path, '/');

    if ($path === '') {
        return '';
    }

    $relativePath = strpos($path, 'assets/') === 0 ? $path : 'assets/' . $path;
    if (!is_file(__DIR__ . '/../' . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath))) {
        return '';
    }

    return strpos($path, 'assets/') === 0 ? site_url($path) : asset_url($path);
}

function format_price(float $amount): string
{
    return '₹' . number_format($amount, 2, '.', ',');
}

function product_discount(float $mrp, float $price): float
{
    if ($mrp <= 0 || $price >= $mrp) {
        return 0;
    }

    return round((($mrp - $price) / $mrp) * 100, 1);
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function is_admin(): bool
{
    return !empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function redirect(string $path): void
{
    header('Location: ' . site_url($path));
    exit;
}

function cart_items(PDO $pdo): array
{
    $cart = $_SESSION['cart'] ?? [];
    if (!is_array($cart) || !$cart) {
        return [];
    }
    $ids = array_values(array_filter(array_map('intval', array_keys($cart)), static fn (int $id): bool => $id > 0));
    if (!$ids) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare(
        "SELECT products.id, products.name, products.price, products.stock, products.image,
                categories.name AS category_name
         FROM products INNER JOIN categories ON products.category_id = categories.id
         WHERE products.id IN ($placeholders) AND products.status = 'active'"
    );
    $stmt->execute($ids);
    $items = [];
    foreach ($stmt->fetchAll() as $item) {
        $quantity = max(0, min((int) ($cart[$item['id']] ?? 0), (int) $item['stock']));
        if ($quantity > 0) {
            $item['quantity'] = $quantity;
            $item['subtotal'] = $quantity * (float) $item['price'];
            $items[] = $item;
        }
    }
    $_SESSION['cart'] = array_column($items, 'quantity', 'id');
    return $items;
}

function cart_count(): int
{
    return array_sum(array_map('intval', is_array($_SESSION['cart'] ?? null) ? $_SESSION['cart'] : []));
}
