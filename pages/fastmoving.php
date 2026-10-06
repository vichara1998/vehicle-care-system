<?php
require_once __DIR__ . '/../app/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <base href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Care Services | Vehicle Care System</title>
    <link rel="stylesheet" href="assets/css/app-ui.css?v=20261006a">
</head>
<body>
    <nav class="menu-bar" aria-label="Main navigation">
        <ul>
            <li><a href="pages/home.php">Home</a></li>
            <li><a href="pages/spareparts.php">Products</a></li>
            <li><a href="pages/user_details.php">Profile</a></li>
            <li><a href="pages/livesupport.php">Support</a></li>
            <li><a href="auth/logout.php">Logout</a></li>
        </ul>
    </nav>
    <main class="container page-placeholder">
        <h1>Vehicle Care Services</h1>
        <p>Service listings are being prepared. Browse spare parts or reach out to customer support while this section is in progress.</p>
        <div class="placeholder-actions">
            <a href="pages/spareparts.php">Browse spare parts</a>
            <a href="pages/livesupport.php">Contact support</a>
        </div>
    </main>
</body>
</html>
