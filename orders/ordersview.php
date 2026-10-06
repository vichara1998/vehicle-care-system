<?php
require_once __DIR__ . '/../app/bootstrap.php';

$conn = app_db_connect();

// Start session to retrieve logged-in user's ID
$user_id = $_SESSION['user_id'] ?? null; // Replace this with your actual login authentication logic

if (!$user_id) {
    die("Unauthorized access. Please log in."); // Prevent unauthorized access if user_id is not set
}

// Fetch orders for the logged-in user
$sql = "SELECT order_id, item_name, spare_id, quantity, price, total_price 
        FROM order_items 
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <base href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        .order-item {
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 10px 0;
            padding: 10px;
            background: #fff;
        }

        .order-item h3 {
            font-size: 1.2em;
            color: #333;
        }

        .order-item p {
            color: #666;
            margin: 5px 0;
        }

        .menu-bar {
            background-color: #fff;
            color: #333;
            padding: 20px 0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .menu-bar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
        }

        .menu-bar li {
            display: inline;
            margin: 0 20px;
        }

        .menu-bar a {
            color: #333;
            text-decoration: none;
            font-weight: bold;
            font-size: 18px;
            padding: 8px 12px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .menu-bar a:hover {
            background-color: rgb(62, 62, 62);
            color: #fff;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
        }

    </style>
    <link rel="stylesheet" href="assets/css/app-ui.css?v=20261006c">
</head>

<body>

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

<!-- Orders Section -->
<div class="container">
    <h1>Your Orders</h1>
    <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-item">
                <h3>Order ID: <?= htmlspecialchars($order['order_id']) ?></h3>
                <p>Item Name: <?= isset($order['item_name']) ? htmlspecialchars($order['item_name']) : 'N/A' ?></p>
                <p>Spare ID: <?= htmlspecialchars($order['spare_id']) ?></p>
                <p>Quantity: <?= htmlspecialchars($order['quantity']) ?></p>
                <p>Price: $<?= htmlspecialchars($order['price']) ?></p>
                <p>Total Price: $<?= htmlspecialchars($order['total_price']) ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No orders found for your account.</p>
    <?php endif; ?>
</div>

<script>
    // Confirm logout
    function confirmLogout(event) {
        if (!confirm("Are you sure you want to log out?")) {
            event.preventDefault();
        }
    }
</script>

</body>
</html>
