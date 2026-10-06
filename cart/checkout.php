<?php
require_once __DIR__ . '/../app/bootstrap.php';

session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vehicle_care_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'] ?? 1;

// Fetch cart items for the user
$sql = "SELECT c.cart_id, s.spare_id, s.item_name, c.quantity, s.price 
        FROM cart c 
        JOIN spare_parts s ON c.spare_id = s.spare_id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total_price = 0;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $total_price += $row['quantity'] * $row['price'];
    }
} else {
    die("Your cart is empty. Add items before placing an order.");
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
    <title>Checkout</title>
    <style>
        /* Style for Checkout Page */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            font-size: 24px;
            color: #333;
        }

        .cart-summary {
            margin-bottom: 20px;
        }

        .cart-summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-summary th,
        .cart-summary td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .total {
            text-align: left;
            font-size: 18px;
            font-weight: bold;
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .checkout-form {
            margin-top: 30px;
        }

        .checkout-form input[type="text"],
        .checkout-form input[type="email"],
        .checkout-form input[type="number"],
        .checkout-form select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .checkout-form button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .checkout-form button:hover {
            opacity: 0.8;
        }

        .menu-bar {
            background-color: #fff;
            color: #333;
            padding: 20px 0;
            /* Decrease top and bottom padding to reduce height */
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: -5px;
            /* Move the menu-bar further up */
            width: 100%;
            /* Ensure the menu-bar takes the full width of the page */
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
    <link rel="stylesheet" href="assets/css/app-ui.css?v=20261006a">
</head>

<body>

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

    <div class="container">
        <h1>Checkout</h1>

        <!-- Cart Summary -->
        <div class="cart-summary">
            <h3>Your Cart</h3>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>$<?= $item['price'] ?></td>
                            <td>$<?= $item['quantity'] * $item['price'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="total">
                Total: $<?= $total_price ?>
            </div>
        </div>

        <!-- Checkout Form -->
        <form class="checkout-form" method="POST" action="actions/orders/place_order.php" onsubmit="return confirmOrder();">
            <h3>Delivery Address</h3>
            <input type="text" name="address" placeholder="Enter your address" required>

            <h3>Payment Method</h3>
            <select name="payment_method" required>
                <option value="">Select payment method</option>
                <option value="credit_card">Credit Card</option>
                <option value="paypal">PayPal</option>
                <option value="bank_transfer">Bank Transfer</option>
            </select>

            <h3>Email Address</h3>
            <input type="email" name="email" placeholder="Enter your email" required>

            <button type="submit">Pay Now</button>
        </form>

        <script>
            function confirmOrder() {
                return confirm("Are you sure you want to confirm the order?");
            }
        </script>
    </div>

</body>

</html>