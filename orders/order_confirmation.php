<?php
require_once __DIR__ . '/../app/bootstrap.php';

$userId = app_require_authenticated_user();

$conn = app_db_connect();

$order_id = filter_var($_GET['order_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$order = null;
$items_result = null;

if ($order_id !== false) {
    // Fetch order details
    $sql_order = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("ii", $order_id, $userId);
    $stmt_order->execute();
    $order_result = $stmt_order->get_result();
    $order = $order_result->fetch_assoc();

    // Fetch order items
    $sql_items = "SELECT item_name, quantity, price, total_price FROM order_items WHERE order_id = ? AND user_id = ?";
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bind_param("ii", $order_id, $userId);
    $stmt_items->execute();
    $items_result = $stmt_items->get_result();

    $stmt_order->close();
    $stmt_items->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <base href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        /* Add your styles here (reuse your existing styles) */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 40px auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        h1 {
            color: #4CAF50;
            font-size: 28px;
            text-align: center;
        }

        .order-details, .order-items {
            margin-top: 20px;
        }

        .order-details h3, .order-items h3 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .order-details p, .order-items p {
            font-size: 18px;
            margin: 8px 0;
            line-height: 1.6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            text-align: center;
            margin-top: 20px;
        }

        .btn:hover {
            background-color: #45a049;
        }

        .not-found {
            color: red;
            font-size: 20px;
            text-align: center;
            margin-top: 20px;
        }
    </style>
    <link rel="stylesheet" href="assets/css/app-ui.css?v=20261006l">
</head>
<body>

<div class="container">
    <h1>Order Confirmation</h1>

    <?php if ($order): ?>
        <!-- Order Details -->
        <div class="order-details">
            <h3>Order ID: <?= (int) $order['order_id'] ?></h3>
            <p><strong>Total Price:</strong> $<?= htmlspecialchars((string) $order['total_price'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($order['address'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <!-- Order Items -->
        <div class="order-items">
            <h3>Order Items</h3>
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $items_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= (int) $item['quantity'] ?></td>
                            <td>$<?= htmlspecialchars((string) $item['price'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>$<?= htmlspecialchars((string) $item['total_price'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <a href="pages/home.php" class="btn">Go to Home</a>
    <?php else: ?>
        <div class="not-found">
            <p>Order not found!</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
