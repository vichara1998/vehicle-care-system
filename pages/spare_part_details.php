<?php
require_once __DIR__ . '/../app/bootstrap.php';

$conn = app_db_connect();

// Fetch specific spare part details
$spare_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($spare_id === false) {
    http_response_code(400);
    die("Invalid spare part ID.");
}

$stmt = $conn->prepare("SELECT * FROM spare_parts WHERE spare_id = ?");
$stmt->bind_param("i", $spare_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    http_response_code(404);
    die("Spare part not found.");
}

$spare_part = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <base href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($spare_part['item_name']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .spare-part-details {
            text-align: center;
        }

        .spare-part-details h1 {
            color: #333;
        }

        .spare-part-details p {
            color: #666;
            font-size: 18px;
        }

        .price {
            font-size: 20px;
            color: #e63946;
            font-weight: bold;
        }

        .quantity-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 10px;
        }

        .quantity-controls button {
            padding: 5px 10px;
            font-size: 18px;
            margin: 0 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .quantity-controls button:hover {
            background-color: #0056b3;
        }

        .quantity-controls input {
            width: 50px;
            text-align: center;
            font-size: 18px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .total-price {
            font-size: 18px;
            margin-top: 10px;
            color: #333;
        }

        .order-button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
        }

        .order-button:hover {
            background-color: #218838;
        }

        .menu-bar {
            background-color:rgb(255, 255, 255);
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

        .spare-part-image {
            max-width: 100%;
            height: auto;
            width: 300px;
            border-radius: 10px;
            display: block;
            margin: 0 auto 20px;
        }
    </style>
    <link rel="stylesheet" href="assets/css/app-ui.css?v=20261006l">
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

    <div class="container">
        <div class="spare-part-details">
            <img src="<?= htmlspecialchars(product_image_url($spare_part['image_path']), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($spare_part['item_name'], ENT_QUOTES, 'UTF-8') ?>" class="spare-part-image">
            <h1><?= htmlspecialchars($spare_part['item_name']) ?></h1>
            <p><?= htmlspecialchars($spare_part['description']) ?></p>
            <p class="price" id="price">Price: $<?= htmlspecialchars($spare_part['price']) ?></p>

            <div class="quantity-controls">
                <button id="decrease-btn">-</button>
                <input type="number" id="quantity" value="1" min="1" readonly>
                <button id="increase-btn">+</button>
            </div>

            <p class="total-price" id="total-price">Total: $<?= htmlspecialchars($spare_part['price']) ?></p>

            <button class="order-button" id="order-now-btn">Order Now</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const price = <?= json_encode((float) $spare_part['price'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            let quantity = 1;

            // Function to update the quantity and total price
            function updateQuantity(change) {
                quantity = Math.max(1, quantity + change);
                document.getElementById('quantity').value = quantity;
                document.getElementById('total-price').innerText = `Total: $${(price * quantity).toFixed(2)}`;
            }

            // Add event listeners for the buttons
            document.getElementById('increase-btn').addEventListener('click', function () {
                updateQuantity(1);
            });

            document.getElementById('decrease-btn').addEventListener('click', function () {
                updateQuantity(-1);
            });

            // Add event listener for the "Order Now" button
            document.getElementById('order-now-btn').addEventListener('click', function () {
                confirmOrder(<?= (int) $spare_part['spare_id'] ?>);
            });

            // Function to confirm the order and redirect to checkout
            function confirmOrder(spareId) {
                const totalAmount = (price * quantity).toFixed(2);
                const userConfirmed = confirm(`Confirm your order of ${quantity} items for a total of $${totalAmount}?`);
                if (userConfirmed) {
                    const redirectUrl = `cart/checkout.php?id=${spareId}&quantity=${quantity}&total=${totalAmount}`;
                    window.location.href = redirectUrl;
                }
            }
        });
    </script>
</body>

</html>
