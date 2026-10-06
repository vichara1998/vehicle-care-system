# Vehicle Care System

A PHP and MySQL website for vehicle owners in Sri Lanka, bringing together garage services, spare-parts shopping, customer support, and vehicle advertisements.

## Features

- Browse spare parts and manage a shopping cart.
- Place orders and view order history.
- Browse garage and vehicle advertisement pages.
- Use customer support and the Q&A forum.
- Register, sign in, and manage a customer profile.

## Project structure

```text
index.php               Application entry point; redirects to login
app/                    Shared application bootstrap and URL helper
auth/                   Login, registration, and logout
pages/                  Main site pages
cart/                   Cart and checkout pages
orders/                 Order history and confirmation pages
actions/
  cart/                 Cart request handlers
  orders/               Order submission handler
assets/
  css/                  Stylesheets
  js/                   JavaScript
  images/
    backgrounds/        Page backgrounds
    banners/            Page banners
    categories/         Category images
    products/           Spare-part images
    social/             Social media icons
```

## Requirements

- PHP with the `mysqli` extension
- MySQL
- Apache or another PHP-enabled web server

## Local setup with XAMPP

1. Place the project folder in XAMPP's `htdocs` directory.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Create the `vehicle_care_system` MySQL database and set up the tables required by the PHP pages. This repository does not currently include a database schema or seed data.
4. Configure the database connection settings in the PHP files for your local MySQL installation.
5. Visit `http://localhost/VCS_website_group_project/` in your browser.

The application URL helper supports installations in a subdirectory.
