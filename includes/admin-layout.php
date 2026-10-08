<?php

require_once __DIR__ . '/functions.php';

function admin_header(string $title, string $active = 'dashboard'): void
{
    $items = [
        'dashboard' => ['Dashboard', 'admin/index.php'],
        'products' => ['Products', 'admin/products.php'],
        'categories' => ['Categories', 'admin/categories.php'],
        'orders' => ['Orders', 'admin/orders.php'],
        'users' => ['Users', 'admin/users.php'],
    ];
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title); ?></title>
        <link rel="stylesheet" href="<?= e(asset_url('css/admin.css')); ?>">
        <script defer src="<?= e(asset_url('js/script.js')); ?>"></script>
    </head>
    <body class="admin-body">
        <header class="admin-topbar">
            <button class="admin-menu-toggle" type="button" aria-label="Toggle admin menu" aria-expanded="false" data-admin-menu-toggle>☰</button>
            <a class="admin-brand" href="<?= e(site_url('admin/index.php')); ?>">
                <span class="admin-brand-mark">S</span>
                <span><strong>Maison Gift Co.</strong><small>Admin Panel</small></span>
            </a>
            <div class="admin-user">
                <span class="admin-avatar"><?= e(strtoupper(substr((string) ($_SESSION['admin_name'] ?? 'A'), 0, 1))); ?></span>
                <span><?= e((string) ($_SESSION['admin_name'] ?? 'Administrator')); ?></span>
                <a href="<?= e(site_url('admin/logout.php')); ?>">Logout</a>
            </div>
        </header>
        <div class="admin-shell">
            <aside class="admin-sidebar" data-admin-sidebar>
                <nav>
                    <?php foreach ($items as $key => [$label, $path]): ?>
                        <a class="<?= $active === $key ? 'is-active' : ''; ?>" href="<?= e(site_url($path)); ?>">
                            <span class="admin-nav-icon"><?= e(strtoupper(substr($label, 0, 1))); ?></span><?= e($label); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <a class="admin-sidebar-logout" href="<?= e(site_url('admin/logout.php')); ?>">↪ <span>Logout</span></a>
            </aside>
            <main class="admin-main">
                <div class="admin-content">
    <?php
}

function admin_footer(): void
{
    ?>
                </div>
            </main>
        </div>
    </body>
    </html>
    <?php
}
