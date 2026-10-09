# 🎁 My Gift Store — E-Commerce Website

<div align="center">

### A Modern PHP & MySQL E-Commerce Platform

*Discover thoughtful gifts, premium chocolates, and beautifully curated collections for every occasion.*

![PHP](https://img.shields.io/badge/PHP-Backend-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-Interactive-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![HTML5](https://img.shields.io/badge/HTML5-Markup-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-Styling-1572B6?style=for-the-badge&logo=css3&logoColor=white)

[![Live Demo](https://img.shields.io/badge/🌐_LIVE_DEMO-Visit_Website-22C55E?style=for-the-badge)](https://my-ecommerce.42web.io/my_practice/ecommerce/)
![Hosting](https://img.shields.io/badge/Hosting-InfinityFree-blue?style=for-the-badge)
![Status](https://img.shields.io/badge/Status-In_Development-orange?style=for-the-badge)

**[🚀 Explore Live Website](https://my-ecommerce.42web.io/my_practice/ecommerce/)** · **[🛍️ Browse Products](https://my-ecommerce.42web.io/my_practice/ecommerce/products.php)** · **[🔐 Admin Login](https://my-ecommerce.42web.io/my_practice/ecommerce/admin/)**

</div>

---

## 📖 About the Project

**My Gift Store** is a full-stack e-commerce web application developed using PHP, MySQL, HTML, CSS, and JavaScript.

The platform provides a shopping interface for customers and a dedicated administration dashboard for managing products, categories, orders, and website settings.

The project was developed and tested in a local XAMPP environment before being deployed to InfinityFree hosting.

## ✨ Features

### 🛍️ Customer Features

- Responsive storefront interface
- Product browsing and individual product pages
- Product categories and occasion-based collections
- Search and filtering options
- Product sorting by price, name, and newest arrivals
- Product pricing, discounts, and stock availability
- Customer registration and login
- Session-based shopping cart
- Checkout and order placement workflow
- Customer order history

### 🔐 Admin Dashboard

- Admin authentication
- Dashboard overview
- Product management — add, edit, and manage products
- Category management
- Inventory and product status controls
- Order management
- Website name and settings management
- WhatsApp business settings

### ⚙️ Technical Features

- PHP-based backend architecture
- MySQL relational database
- PDO database connections
- Password hashing and verification
- PHP session management
- Prepared SQL statements
- Reusable header, footer, and helper functions
- Dynamic product rendering
- Server-side search, filtering, and pagination

---

## 🖥️ Project Preview

**Live website:** [My Gift Store](https://my-ecommerce.42web.io/my_practice/ecommerce/)

> Screenshots can be added to the repository after uploading images to a `screenshots/` folder.

| Storefront | Admin Dashboard |
|---|---|
| `screenshots/homepage.png` | `screenshots/admin-dashboard.png` |

---

## 🧰 Tech Stack

| Technology | Purpose |
|---|---|
| PHP | Server-side programming |
| MySQL / MariaDB | Database management |
| HTML5 | Page structure |
| CSS3 | Layout and styling |
| JavaScript | Frontend interactions |
| PDO | Database operations |
| XAMPP | Local development environment |
| phpMyAdmin | Database administration |
| InfinityFree | Web hosting |

---

## 📂 Project Structure

```text
ecommerce/
│
├── admin/
│   └── ...               # Admin dashboard and management
│
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── script.js
│   └── images/
│
├── config/
│   ├── database.php
│   └── whatsapp.php
│
├── includes/
│   ├── functions.php
│   ├── header.php
│   └── footer.php
│
├── index.php
├── products.php
├── product.php
├── cart.php
├── checkout.php
├── login.php
├── register.php
├── logout.php
├── orders.php
├── database.sql
└── README.md
```

---

## 🚀 Installation & Local Setup

<details>
<summary><b>📥 Step 1 — Download the Project</b></summary>

Clone your GitHub repository:

```bash
git clone https://github.com/YOUR_USERNAME/YOUR_REPOSITORY.git
```

Or download the repository as a ZIP and extract it.

Place the project in:

```text
C:\xampp\htdocs\ecommerce
```

Alternatively, configure your local Apache setup to use your preferred project location.

</details>

<details>
<summary><b>🛠️ Step 2 — Start XAMPP</b></summary>

1. Open the XAMPP Control Panel.
2. Start **Apache**.
3. Start **MySQL**.
4. Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

</details>

<details>
<summary><b>🗄️ Step 3 — Configure the Database</b></summary>

1. Create a MySQL database named `ecommerce`.
2. Select the new database.
3. Open the **Import** tab.
4. Upload `database.sql`.
5. Complete the import.

Update your local database connection file with the correct environment-specific settings.

Example development configuration:

```php
$host = 'localhost';
$dbname = 'ecommerce';
$username = 'root';
$password = '';
```

Do not commit production database passwords to GitHub.

</details>

<details>
<summary><b>🌐 Step 4 — Run the Application</b></summary>

Open the storefront:

```text
http://localhost/ecommerce/
```

Open the administration panel:

```text
http://localhost/ecommerce/admin/
```

Ensure your `site_url()` helper points to the correct project directory.

</details>

---

## ☁️ Deployment on InfinityFree

The application has been deployed using InfinityFree hosting.

### Deployment Process

1. Create an InfinityFree hosting account and domain.
2. Upload the application files to the website directory.
3. Create a MySQL database.
4. Import the SQL database through phpMyAdmin.
5. Update the PHP database connection settings.
6. Configure the live website's base URL.
7. Verify the storefront, admin panel, authentication, and checkout functionality.

**Live deployment:**

```text
https://my-ecommerce.42web.io/my_practice/ecommerce/
```

> Production database credentials and private configuration values should never be stored in a public GitHub repository.

---

## 🔒 Security Considerations

The project uses PHP backend authentication, password hashing, PDO prepared statements, and sessions.

Additional security measures should be reviewed before accepting real customer orders, including:

- CSRF protection on state-changing forms
- Session and cookie security
- Input validation and output escaping
- Authorization checks on admin routes
- File-upload validation
- Secret and credential management
- HTTPS enforcement
- Checkout and payment security

**Note:** This is a development and demonstration project. Production readiness requires independent security and functionality testing.

---

## 🗺️ Future Improvements

- [ ] Improve the mobile administration experience
- [ ] Enhance responsive UI across screen sizes
- [ ] Add payment gateway integration
- [ ] Add order tracking and notifications
- [ ] Implement customer wishlist storage
- [ ] Improve product image management
- [ ] Add advanced reporting and analytics
- [ ] Add automated testing and CI/CD
- [ ] Improve SEO and accessibility

---

## 🤝 Contributing

Contributions, suggestions, and improvements are welcome.

1. Fork the repository.
2. Create a new feature branch.
3. Commit your changes.
4. Push the branch.
5. Open a pull request.

---

## 📬 Feedback

If you discover an issue or have a feature suggestion, open an issue in this GitHub repository.

You can also explore the deployed project:

**[🌐 Visit My Gift Store](https://my-ecommerce.42web.io/my_practice/ecommerce/)**

---

<div align="center">

### 🎁 Made for Meaningful Moments

**Built with PHP, MySQL, and a passion for web development.**

⭐ If you find this project useful, consider giving the repository a star!

</div>
