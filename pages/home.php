<?php
require_once __DIR__ . '/../app/bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . app_url('auth/login.php'));
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <base href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Care System | Home</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/app-ui.css?v=20261006h">
</head>
<body id="top" class="home-page">
    <!-- Menu Bar -->
    <div class="menu-bar">
        <ul>
            <li><a href="pages/home.php">Home</a></li>
            <li><a href="pages/spareparts.php">Products</a></li>
            <li><a href="pages/user_details.php">Profile</a></li>
            <li><a href="pages/livesupport.php">Support</a></li>
            <li><form method="POST" action="auth/logout.php" class="logout-form" onsubmit="return confirm('Are you sure you want to logout?');"><input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(app_csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><button type="submit" class="logout-button">Logout</button></form></li>
        </ul>
    </div>

    <main class="home-main">
        <section class="home-hero" aria-labelledby="home-title">
            <div class="home-hero-copy">
                <span class="home-eyebrow">YOUR VEHICLE, WELL CARED FOR</span>
                <h1 id="home-title">Welcome back, <?= htmlspecialchars($_SESSION['name'] ?? 'Driver', ENT_QUOTES, 'UTF-8') ?>.</h1>
                <p>Find the parts, people, and support that keep every journey moving.</p>
                <a class="home-primary-link" href="pages/spareparts.php">Explore spare parts <span aria-hidden="true">→</span></a>
            </div>
            <div class="home-hero-mark" aria-hidden="true">
                <span class="home-mark-ring"></span>
                <span class="home-mark-core">VCS</span>
                <span class="home-mark-caption">CARE FOR THE ROAD AHEAD</span>
            </div>
        </section>

        <section class="home-services" aria-labelledby="services-title">
            <div class="home-section-intro">
                <div>
                    <span class="home-eyebrow">ONE PLACE. MORE PEACE OF MIND.</span>
                    <h2 id="services-title">How can we help today?</h2>
                </div>
                <p>Choose a service and get started.</p>
            </div>

            <div id="cards-container" class="cards-container home-cards">
                <div class="card home-card" data-id="1" role="link" tabindex="0" onclick="navigateToPage('pages/spareparts.php');" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); navigateToPage('pages/spareparts.php'); }">
                    <img src="assets/images/categories/c1.png" alt="" loading="lazy">
                    <div class="card-content"><span class="home-card-number">01</span><h3>Spare Parts</h3><span class="home-card-arrow" aria-hidden="true">↗</span></div>
                </div>
                <div class="card home-card" data-id="2" role="link" tabindex="0" onclick="navigateToPage('pages/garages.php');" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); navigateToPage('pages/garages.php'); }">
                    <img src="assets/images/categories/c2.png" alt="" loading="lazy">
                    <div class="card-content"><span class="home-card-number">02</span><h3>Garages</h3><span class="home-card-arrow" aria-hidden="true">↗</span></div>
                </div>
                <div class="card home-card" data-id="3" role="link" tabindex="0" onclick="navigateToPage('pages/ads.php');" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); navigateToPage('pages/ads.php'); }">
                    <img src="assets/images/categories/c3.png" alt="" loading="lazy">
                    <div class="card-content"><span class="home-card-number">03</span><h3>Vehicle Ads</h3><span class="home-card-arrow" aria-hidden="true">↗</span></div>
                </div>
                <div class="card home-card" data-id="4" role="link" tabindex="0" onclick="navigateToPage('pages/livesupport.php');" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); navigateToPage('pages/livesupport.php'); }">
                    <img src="assets/images/categories/c4.png" alt="" loading="lazy">
                    <div class="card-content"><span class="home-card-number">04</span><h3>Live Support</h3><span class="home-card-arrow" aria-hidden="true">↗</span></div>
                </div>
                <div class="card home-card" data-id="5" role="link" tabindex="0" onclick="navigateToPage('pages/fastmoving.php');" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); navigateToPage('pages/fastmoving.php'); }">
                    <img src="assets/images/categories/c5.png" alt="" loading="lazy">
                    <div class="card-content"><span class="home-card-number">05</span><h3>Fast Moving Services</h3><span class="home-card-arrow" aria-hidden="true">↗</span></div>
                </div>
                <div class="card home-card" data-id="6" role="link" tabindex="0" onclick="navigateToPage('pages/q_a.php');" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); navigateToPage('pages/q_a.php'); }">
                    <img src="assets/images/categories/c6.png" alt="" loading="lazy">
                    <div class="card-content"><span class="home-card-number">06</span><h3>Q&amp;A Forum</h3><span class="home-card-arrow" aria-hidden="true">↗</span></div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer home-footer">
        <div class="home-footer-cta">
            <div>
                <span class="home-eyebrow">WE’RE HERE WHEN YOU NEED US</span>
                <h2>Keep your next journey running smoothly.</h2>
                <p>Browse services or get a hand from our support team.</p>
            </div>
            <a class="home-footer-button" href="pages/livesupport.php">Get support <span aria-hidden="true">→</span></a>
        </div>
        <div class="home-footer-grid">
            <div class="home-footer-brand">
                <a class="home-brand-lockup" href="pages/home.php" aria-label="Vehicle Care System home"><span class="home-brand-icon" aria-hidden="true">V</span><span>VEHICLE CARE<span class="home-brand-subtitle">SYSTEM</span></span></a>
                <p>Your everyday companion for smarter vehicle care.</p>
            </div>
            <nav class="home-footer-links" aria-label="Explore">
                <h3>Explore</h3>
                <a href="pages/spareparts.php">Spare parts</a>
                <a href="pages/garages.php">Garages</a>
                <a href="pages/ads.php">Vehicle ads</a>
            </nav>
            <nav class="home-footer-links" aria-label="Your account and help">
                <h3>Account &amp; help</h3>
                <a href="pages/user_details.php">Your profile</a>
                <a href="pages/livesupport.php">Live support</a>
                <a href="pages/q_a.php">Q&amp;A forum</a>
            </nav>
            <div class="home-footer-note">
                <h3>Made for the road ahead</h3>
                <p>Parts, services, and helpful people — all in one place.</p>
            </div>
        </div>
        <div class="footer-bottom home-footer-bottom">
            <span>&copy; <?= date('Y') ?> Vehicle Care System. All rights reserved.</span>
            <a href="#top" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;">Back to top ↑</a>
        </div>
    </footer>


    <script src="assets/js/script.js"></script>
</body>
</html>

<!--VCS-->