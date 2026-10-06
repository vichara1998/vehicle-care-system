<?php
require_once __DIR__ . '/../app/bootstrap.php';

app_require_admin();
$conn = app_db_connect();

$countsResult = $conn->query(
    'SELECT
        (SELECT COUNT(*) FROM users) AS users_count,
        (SELECT COUNT(*) FROM spare_parts) AS products_count,
        (SELECT COUNT(*) FROM orders) AS orders_count,
        (SELECT COUNT(*) FROM ads) AS ads_count,
        (SELECT COUNT(*) FROM questions) AS questions_count'
);

if (!$countsResult) {
    error_log('Admin dashboard counts query failed: ' . $conn->error);
    http_response_code(500);
    exit('Unable to load the admin dashboard right now.');
}

$counts = $countsResult->fetch_assoc();
$conn->close();

$dashboardCards = [
    ['label' => 'Registered accounts', 'count' => $counts['users_count'], 'icon' => '01', 'detail' => 'Accounts in the system'],
    ['label' => 'Spare parts', 'count' => $counts['products_count'], 'icon' => '02', 'detail' => 'Catalog products'],
    ['label' => 'Orders', 'count' => $counts['orders_count'], 'icon' => '03', 'detail' => 'Orders placed'],
    ['label' => 'Vehicle ads', 'count' => $counts['ads_count'], 'icon' => '04', 'detail' => 'Community listings'],
    ['label' => 'Forum questions', 'count' => $counts['questions_count'], 'icon' => '05', 'detail' => 'Topics in Q&A'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <base href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Vehicle Care System</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/app-ui.css?v=20261006l">
</head>
<body class="admin-page">
    <header class="menu-bar">
        <ul>
            <li><a href="pages/home.php">Home</a></li>
            <li><a href="pages/spareparts.php">Products</a></li>
            <li><a href="pages/ads.php">Vehicle Ads</a></li>
            <li><a href="orders/ordersview.php">Orders</a></li>
            <li><a href="pages/q_a.php">Q&amp;A Forum</a></li>
            <li>
                <form method="POST" action="auth/logout.php" class="logout-form" onsubmit="return confirm('Are you sure you want to logout?');">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(app_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="logout-button">Logout</button>
                </form>
            </li>
        </ul>
    </header>

    <main class="admin-main">
        <section class="admin-hero">
            <div>
                <span class="admin-eyebrow">VEHICLE CARE SYSTEM / CONTROL CENTER</span>
                <h1>Admin dashboard</h1>
                <p>Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'Administrator', ENT_QUOTES, 'UTF-8') ?>. Here’s a snapshot of your platform.</p>
            </div>
            <span class="admin-badge"><span aria-hidden="true">●</span> Administrator access</span>
        </section>

        <section class="admin-overview" aria-labelledby="admin-overview-title">
            <div class="admin-section-heading">
                <div>
                    <span class="admin-eyebrow">AT A GLANCE</span>
                    <h2 id="admin-overview-title">Platform overview</h2>
                </div>
                <p>Live totals from your application database</p>
            </div>
            <div class="admin-stat-grid">
                <?php foreach ($dashboardCards as $card): ?>
                    <article class="admin-stat-card">
                        <div class="admin-stat-top">
                            <span class="admin-stat-icon"><?= htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="admin-stat-dot" aria-hidden="true"></span>
                        </div>
                        <p class="admin-stat-count"><?= number_format((int) $card['count']) ?></p>
                        <h3><?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="admin-stat-detail"><?= htmlspecialchars($card['detail'], ENT_QUOTES, 'UTF-8') ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="admin-tools" aria-labelledby="admin-tools-title">
            <div class="admin-section-heading">
                <div>
                    <span class="admin-eyebrow">QUICK ACCESS</span>
                    <h2 id="admin-tools-title">Manage the platform</h2>
                </div>
                <p>Open an existing section to review its content.</p>
            </div>
            <div class="admin-tool-grid">
                <a class="admin-tool-card" href="pages/spareparts.php">
                    <span class="admin-tool-icon" aria-hidden="true">▦</span>
                    <span><strong>Product catalog</strong><small>Browse spare parts and stock</small></span>
                    <span class="admin-tool-arrow" aria-hidden="true">↗</span>
                </a>
                <a class="admin-tool-card" href="orders/ordersview.php">
                    <span class="admin-tool-icon" aria-hidden="true">▤</span>
                    <span><strong>Orders</strong><small>Review customer orders</small></span>
                    <span class="admin-tool-arrow" aria-hidden="true">↗</span>
                </a>
                <a class="admin-tool-card" href="pages/ads.php">
                    <span class="admin-tool-icon" aria-hidden="true">◇</span>
                    <span><strong>Vehicle ads</strong><small>Review community listings</small></span>
                    <span class="admin-tool-arrow" aria-hidden="true">↗</span>
                </a>
                <a class="admin-tool-card" href="pages/q_a.php">
                    <span class="admin-tool-icon" aria-hidden="true">?</span>
                    <span><strong>Q&amp;A forum</strong><small>Review questions and replies</small></span>
                    <span class="admin-tool-arrow" aria-hidden="true">↗</span>
                </a>
                <a class="admin-tool-card" href="pages/livesupport.php">
                    <span class="admin-tool-icon" aria-hidden="true">⌕</span>
                    <span><strong>Customer support</strong><small>Open the support workspace</small></span>
                    <span class="admin-tool-arrow" aria-hidden="true">↗</span>
                </a>
                <a class="admin-tool-card" href="pages/user_details.php">
                    <span class="admin-tool-icon" aria-hidden="true">◎</span>
                    <span><strong>My profile</strong><small>View administrator profile</small></span>
                    <span class="admin-tool-arrow" aria-hidden="true">↗</span>
                </a>
            </div>
        </section>
        <p class="admin-note">This dashboard provides an overview and shortcuts. Existing pages only allow actions currently supported by the application.</p>
    </main>
</body>
</html>
