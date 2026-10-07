<?php
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) : 'ShopStore' ?></title>
    <meta name="description" content="Premium chocolates and gifts for every occasion.">
    <?php $styleVersion = is_file(__DIR__ . '/../assets/css/style.css') ? (string) filemtime(__DIR__ . '/../assets/css/style.css') : ''; ?>
    <link rel="stylesheet" href="<?= e(asset_url('css/style.css') . ($styleVersion !== '' ? '?v=' . rawurlencode($styleVersion) : '')); ?>">
    <script defer src="<?= e(asset_url('js/script.js')); ?>"></script>
</head>
<body>
    <header class="site-header">
        <div class="announcement-bar">Free delivery on orders above ₹999</div>
        <div class="container nav-wrap">
            <a class="brand" href="<?= e(site_url()); ?>"><span>Maison</span> Gift Co.</a>
            <form class="header-search" method="get" action="<?= e(site_url('products.php')); ?>">
                <label class="sr-only" for="site-search">Search products</label>
                <input id="site-search" type="search" name="search" placeholder="Search chocolates, gifts, hampers..." value="<?= e((string) ($_GET['search'] ?? '')); ?>">
                <button type="submit" aria-label="Search">⌕</button>
            </form>
            <div class="header-actions">
                <a href="<?= e(site_url('login.php')); ?>">Account</a>
                <a href="<?= e(site_url('products.php')); ?>">♡ Wishlist</a>
                <a href="<?= e(site_url('cart.php')); ?>">Cart <span class="cart-count"><?= e((string) cart_count()); ?></span></a>
            </div>
            <button class="nav-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <nav class="main-nav" id="mainNav">
                <a href="<?= e(site_url()); ?>">Home</a>
                <a href="<?= e(site_url('products.php')); ?>">Chocolates</a>
                <a href="<?= e(site_url('products.php?search=hamper')); ?>">Gift Hampers</a>
                <a href="<?= e(site_url('products.php?search=birthday')); ?>">Birthday</a>
                <a href="<?= e(site_url('products.php?search=anniversary')); ?>">Anniversary</a>
                <a href="<?= e(site_url('products.php?search=wedding')); ?>">Wedding</a>
                <a href="<?= e(site_url('products.php?search=corporate')); ?>">Corporate Gifts</a>
                <?php if (is_logged_in()): ?>
                    <a href="<?= e(site_url('orders.php')); ?>">Orders</a>
                    <?php if (is_admin()): ?>
                        <a href="<?= e(site_url('admin/index.php')); ?>">Admin</a>
                    <?php endif; ?>
                    <a href="<?= e(site_url('logout.php')); ?>">Logout</a>
                <?php else: ?>
                    <a href="<?= e(site_url('login.php')); ?>">Login</a>
                    <a href="<?= e(site_url('register.php')); ?>">Register</a>
                <?php endif; ?>
                <a href="<?= e(site_url('products.php?search=personalized')); ?>">Personalized</a>
            </nav>
        </div>
    </header>

    <main>
