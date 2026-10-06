<?php
require_once __DIR__ . '/../app/bootstrap.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . app_url('auth/login.php'));
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <base href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>">
    <title>Home</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/app-ui.css?v=20261006a">
</head>
<body class="img-background">
    <!-- Menu Bar -->
    <div class="menu-bar">
        <ul>
            <li><a href="pages/home.php">Home</a></li>
            <li><a href="pages/spareparts.php">Products</a></li>
            <li><a href="pages/user_details.php">Profile</a></li>
            <li><a href="pages/livesupport.php">Support</a></li>
            <li><a href="auth/logout.php" id="logout-button" onclick="return confirmLogout(event);">Logout</a></li>
        </ul>
    </div>

    <div class="content">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
        <p>Explore the categories below:</p>

        <!-- Category Cards -->
        <div class="cards-container">
            <!-- Card 1 -->
            <div id="cards-container" class="cards-container">
            <!-- Card 1 -->
            <div class="card" data-id="1" onclick="navigateToPage('pages/spareparts.php');">
                <img src="assets/images/categories/c1.png" alt="Category 1">
                <div class="card-content">
                    <h3>Spare Parts</h3>
                </div>
                <div class="favorite-icon" onclick="toggleFavorite(event, this);"></div>
            </div>

            <!-- Card 2 -->
            <div class="card" data-id="2" onclick="navigateToPage('pages/garages.php');">
                <img src="assets/images/categories/c2.png" alt="Category 2">
                <div class="card-content">
                    <h3>Garages</h3>
                </div>
                <div class="favorite-icon" onclick="toggleFavorite(event, this);"></div>
            </div>

            <!-- Card 3 -->
            <div class="card" data-id="3" onclick="navigateToPage('pages/ads.php');">
                <img src="assets/images/categories/c3.png" alt="Category 3">
                <div class="card-content">
                    <h3>Vehicle Ads</h3>
                </div>
                <div class="favorite-icon" onclick="toggleFavorite(event, this);"></div>
            </div>

            <!-- Card 4 -->
            <div class="card" data-id="4" onclick="navigateToPage('pages/livesupport.php');">
                <img src="assets/images/categories/c4.png" alt="Category 4">
                <div class="card-content">
                    <h3>Live Support</h3>
                </div>
                <div class="favorite-icon" onclick="toggleFavorite(event, this);"></div>
            </div>

            <!-- Card 5 -->
            <div class="card" data-id="5" onclick="navigateToPage('pages/fastmoving.php');">
                <img src="assets/images/categories/c5.png" alt="Category 5">
                <div class="card-content">
                    <h3>Fast Moving Services</h3>
                </div>
                <div class="favorite-icon" onclick="toggleFavorite(event, this);"></div>
            </div>

            <!-- Card 6 -->
            <div class="card" data-id="6" onclick="navigateToPage('pages/q_a.php');">
                <img src="assets/images/categories/c6.png" alt="Category 6">
                <div class="card-content">
                    <h3>Q & A Forum</h3>
                </div>
                <div class="favorite-icon" onclick="toggleFavorite(event, this);"></div>
            </div>
        </div>
    </div>


    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <!-- About Section -->
        <div class="footer-section about">
            <h3>About Us</h3>
            <p>
                Vehicle Care System is dedicated to providing seamless services for vehicle enthusiasts. 
                Explore spare parts, garages, vehicle ads, live support, and more at your fingertips.
            </p>
        </div>

        <!-- Quick Links Section -->
        <div class="footer-section links">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="pages/home.php">Home</a></li>
                <li><a href="pages/spareparts.php">Products</a></li>
                <li><a href="pages/user_details.php">Profile</a></li>
                <li><a href="pages/livesupport.php">Support</a></li>
                <li><a href="pages/q_a.php">Q&A Forum</a></li>
            </ul>
        </div>
        
            <!-- Social Media Section -->
            <div class="footer-section social">
                <h3>Follow Us</h3>
                <div class="social-icons">
                    <a href="#"><img src="assets/images/social/fb.png" alt="Facebook" style="width: 40px; height: 40px;"></a>
                    <a href="#"><img src="assets/images/social/x.png" alt="Twitter" style="width: 40px; height: 40px;"></a>
                    <a href="#"><img src="assets/images/social/insta.png" alt="Instagram" style="width: 40px; height: 40px;"></a>
                    <a href="#"><img src="assets/images/social/linked.png" alt="LinkedIn" style="width: 40px; height: 40px;"></a>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="footer-bottom">
            &copy; 2024 Vehicle Care System. All rights reserved.
        </div>
    </footer>


    <script src="assets/js/script.js"></script>
</body>
</html>

<!--VCS-->