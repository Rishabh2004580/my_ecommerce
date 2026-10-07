CREATE DATABASE IF NOT EXISTS ecommerce
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE ecommerce;

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_slug (slug),
    KEY idx_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NOT NULL,
    occasion VARCHAR(120) DEFAULT NULL,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    mrp DECIMAL(10,2) DEFAULT NULL,
    discount DECIMAL(5,2) DEFAULT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_products_slug (slug),
    KEY idx_category_id (category_id),
    KEY idx_status (status),
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED DEFAULT NULL,
    customer_name VARCHAR(120) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(40) DEFAULT NULL,
    shipping_address TEXT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_orders_user_id (user_id),
    KEY idx_orders_status (status),
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    product_name VARCHAR(180) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_order_items_order_id (order_id),
    KEY idx_order_items_product_id (product_id),
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (name, slug, description) VALUES
('Chocolates', 'chocolates', 'Premium chocolates and handcrafted sweet selections.'),
('Gift Hampers', 'gift-hampers', 'Thoughtfully curated gift hampers for meaningful occasions.'),
('Birthday Gifts', 'birthday-gifts', 'Elegant gifts for memorable birthday celebrations.'),
('Anniversary Gifts', 'anniversary-gifts', 'Thoughtful gifts for celebrating lasting connections.'),
('Wedding Gifts', 'wedding-gifts', 'Beautiful gifts for weddings and new beginnings.'),
('Personalized Gifts', 'personalized-gifts', 'Meaningful gifts made personal for someone special.'),
('Corporate Gifts', 'corporate-gifts', 'Premium gifting selections for clients, teams, and partners.'),
('Premium Gift Boxes', 'premium-gift-boxes', 'Refined gift boxes for elevated celebrations.');

INSERT INTO products (category_id, name, slug, description, price, stock, image, status) VALUES
(1, 'Wireless Noise-Canceling Headphones', 'wireless-noise-canceling-headphones', 'Immersive sound experience with soft memory ear pads and long battery life.', 199.99, 18, 'assets/images/product-1.jpg', 'active'),
(1, 'Smart Fitness Watch', 'smart-fitness-watch', 'Track workouts, heart rate, and notifications with a sleek, waterproof smartwatch.', 149.50, 26, 'assets/images/product-2.jpg', 'active'),
(1, '4K Ultra HD Smart TV', '4k-ultra-hd-smart-tv', 'Crisp picture quality and a streamlined smart interface for streaming entertainment.', 899.00, 9, 'assets/images/product-3.jpg', 'active'),
(2, 'Premium Air Fryer', 'premium-air-fryer', 'Cook crispy favorites with less oil and less cleanup in a compact countertop design.', 119.00, 15, 'assets/images/product-4.jpg', 'active'),
(2, 'Smart Robot Vacuum', 'smart-robot-vacuum', 'Automated floor cleaning with app control and quiet operation.', 279.99, 11, 'assets/images/product-5.jpg', 'active'),
(2, 'Coffee Maker Pro', 'coffee-maker-pro', 'Brew café-quality coffee with customizable strength and temperature settings.', 139.99, 20, 'assets/images/product-6.jpg', 'active'),
(3, 'Classic Cotton Hoodie', 'classic-cotton-hoodie', 'A breathable everyday staple designed for comfort and style.', 59.00, 34, 'assets/images/product-7.jpg', 'active'),
(3, 'Minimal Leather Tote Bag', 'minimal-leather-tote-bag', 'Structured and spacious, ideal for workdays, weekends, and commuting.', 84.00, 14, 'assets/images/product-8.jpg', 'active'),
(4, 'Premium Leather Backpack', 'premium-leather-backpack', 'Durable craftsmanship and secure compartments for daily carry.', 89.00, 17, 'assets/images/product-9.jpg', 'active'),
(4, 'Wireless Charging Dock', 'wireless-charging-dock', 'Fast charging and elegant minimal styling for home and office setups.', 39.99, 28, 'assets/images/product-10.jpg', 'active');

INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@example.com', '$2y$10$7Bw3d1Qp3uBbG1lG3ZlF2uFbL0v/vQYh7j9mUJBfVQWb4xZ0q.0OK', 'admin'),
('Demo Customer', 'customer@example.com', '$2y$10$2ff1oEM9s0AjiLzk7e8BSuG6Q9JbpB8C2eEz1wT9K1V7l2FQ4SLhS', 'customer');

INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, shipping_address, total_amount, status) VALUES
(2, 'Demo Customer', 'customer@example.com', '1234567890', '123 Market Street, New York, NY', 199.99, 'pending');

INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES
(1, 1, 'Wireless Noise-Canceling Headphones', 199.99, 1, 199.99);
