<div align="center">

# 🚗 Vehicle Care System

### Your vehicle care essentials, all in one place.

An online platform for vehicle owners in Sri Lanka to explore spare parts, connect with garage services, find vehicle advertisements, and get customer support.

<p>
  <img src="https://img.shields.io/badge/PHP-8%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/JavaScript-Frontend-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
  <img src="https://img.shields.io/badge/Apache-Web_Server-D22128?style=for-the-badge&logo=apache&logoColor=white" alt="Apache">
</p>

</div>

---

## ✨ What you can do

| 🛒 Spare Parts | 🔧 Garage Services | 🚘 Vehicle Ads |
|:---|:---|:---|
| Browse products, manage a cart, and place orders. | Explore garage services from one place. | Browse vehicle advertisements. |

| 💬 Customer Support | 👤 Customer Account | 📦 Order History |
|:---|:---|:---|
| Get help and use the Q&A forum. | Register, sign in, and manage your profile. | Review your orders and confirmations. |

## 🧰 Built with

- **PHP** for server-rendered pages and request handlers
- **MySQL** for application data
- **HTML, CSS, and JavaScript** for the web interface
- **Apache** for local hosting through XAMPP

## 📁 Project layout

```text
vehicle-care-system/
├── index.php                 # Application entry point
├── app/                      # Shared bootstrap and URL helper
├── config/
│   ├── .htaccess             # Prevent direct access to local configuration
│   └── database.example.php  # Safe template for private database settings
├── auth/                     # Login, registration, and logout
├── pages/                    # Main site pages
├── cart/                     # Cart and checkout pages
├── orders/                   # Order history and confirmation
├── actions/
│   ├── cart/                 # Cart request handlers
│   └── orders/               # Order submission handler
└── assets/
    ├── css/                  # Page styles and shared responsive UI
    ├── js/                   # JavaScript
    └── images/
        ├── backgrounds/
        ├── banners/
        ├── categories/
        ├── products/
        └── social/
```

## 🔐 Account security

- New passwords must be at least 12 characters and no more than 72 bytes (the current bcrypt limit), and are hashed with PHP's `PASSWORD_DEFAULT`.
- Login sessions use strict mode, HTTP-only cookies, SameSite=Lax, and session ID rotation after successful login.
- State-changing account, cart, order, ad, forum, support, and logout requests require a session-bound CSRF token.
- Password resets are not yet configured; contact the site administrator if you cannot sign in.

## 🚀 Run locally with XAMPP

### Requirements

- XAMPP (Apache, MySQL, and PHP with the `mysqli` extension)
- A web browser

### Setup

1. Copy the project folder into XAMPP's `htdocs` directory.
2. Open the XAMPP Control Panel and start **Apache** and **MySQL**.
3. In phpMyAdmin, create a database named `vehicle_care_system`.
4. Create the database tables required by the application. A database schema or seed-data file is not currently included in this repository.
5. Copy `config/database.example.php` to `config/database.local.php`.
6. Edit `config/database.local.php` with your MySQL host, database name, username, and password. Prefer a dedicated database user with access limited to this database; do not use MySQL's `root` account outside local development.
7. Open [http://localhost/VCS_website_group_project/](http://localhost/VCS_website_group_project/) in your browser.

> **Tip:** The shared URL helper supports hosting the project from a subdirectory.

## 📌 Notes

- The root `index.php` redirects visitors to the login page.
- `config/database.local.php` is excluded from Git; never commit local credentials.
- All application database connections use the shared `app_db_connect()` helper.
- Images, stylesheets, and scripts are stored under `assets/`.
