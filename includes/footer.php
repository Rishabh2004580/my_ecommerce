    </main>

    <?php $siteName = site_name(); ?>
    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <h3><?= e($siteName); ?></h3>
                <p>Thoughtful chocolates and premium gifts, beautifully presented for every occasion.</p>
            </div>
            <div>
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= e(site_url()); ?>">Home</a></li>
                    <li><a href="<?= e(site_url('products.php')); ?>">Products</a></li>
                    <li><a href="<?= e(site_url('cart.php')); ?>">Cart</a></li>
                    <li><a href="<?= e(site_url('login.php')); ?>">Login</a></li>
                </ul>
            </div>
            <div>
                <h4>Customer Service</h4>
                <ul>
                    <li><a href="<?= e(site_url('orders.php')); ?>">My Orders</a></li>
                    <li><a href="<?= e(site_url('checkout.php')); ?>">Checkout</a></li>
                    <li><a href="<?= e(site_url('register.php')); ?>">Create Account</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?= date('Y'); ?> <?= e($siteName); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
