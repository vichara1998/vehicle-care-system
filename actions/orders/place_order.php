<?php
require_once __DIR__ . '/../../app/bootstrap.php';

// Start session
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vehicle_care_system";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user_id from session
$user_id = $_SESSION['user_id'] ?? null;

// Check if user is logged in
if (!$user_id) {
    die("Unauthorized access. Please log in.");
}

// Fetch and validate POST data
$address = $_POST['address'] ?? null;
$payment_method = $_POST['payment_method'] ?? null;
$email = $_POST['email'] ?? null;

// Validation
if (!$address || !$payment_method || !$email) {
    die("Please fill out all required fields.");
}

// Fetch cart items for the user
$sql = "SELECT c.cart_id, c.spare_id, c.quantity, s.item_name, s.price, s.stock 
        FROM cart c 
        JOIN spare_parts s ON c.spare_id = s.spare_id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if there are items in the cart
if ($result->num_rows === 0) {
    die("Your cart is empty. Add items before placing an order.");
}

// Begin transaction to ensure atomicity
$conn->begin_transaction();

try {
    // Insert into orders table
    $order_sql = "INSERT INTO orders (user_id, address, payment_method, email, total_price) VALUES (?, ?, ?, ?, ?)";
    $total_price = 0;

    // Calculate total price and check stock
    $items = [];
    while ($row = $result->fetch_assoc()) {
        if ($row['stock'] < $row['quantity']) {
            throw new Exception("Insufficient stock for item: " . $row['item_name']);
        }

        $total_price += $row['quantity'] * $row['price'];
        $items[] = $row; // Store the row for later use
    }

    $stmt_order = $conn->prepare($order_sql);
    $stmt_order->bind_param("isssd", $user_id, $address, $payment_method, $email, $total_price);

    if (!$stmt_order->execute()) {
        throw new Exception("Failed to create order: " . $stmt_order->error);
    }

    $order_id = $conn->insert_id; // Get the inserted order ID

    // Insert into order_items table and update stock
    $item_sql = "INSERT INTO order_items (order_id, item_name, spare_id, quantity, price, total_price, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $update_stock_sql = "UPDATE spare_parts SET stock = stock - ? WHERE spare_id = ?";

    $stmt_items = $conn->prepare($item_sql);
    $stmt_update_stock = $conn->prepare($update_stock_sql);

    foreach ($items as $row) {
        $item_total_price = $row['quantity'] * $row['price'];

        // Insert into order_items
        $stmt_items->bind_param(
            "isiiidi",
            $order_id,
            $row['item_name'],
            $row['spare_id'],
            $row['quantity'],
            $row['price'],
            $item_total_price,
            $user_id
        );

        if (!$stmt_items->execute()) {
            throw new Exception("Failed to insert order item: " . $stmt_items->error);
        }

        // Update stock
        $stmt_update_stock->bind_param("ii", $row['quantity'], $row['spare_id']);
        if (!$stmt_update_stock->execute()) {
            throw new Exception("Failed to update stock: " . $stmt_update_stock->error);
        }
    }

    // Clear the user's cart after the order is placed
    $clear_cart_sql = "DELETE FROM cart WHERE user_id = ?";
    $stmt_clear_cart = $conn->prepare($clear_cart_sql);
    $stmt_clear_cart->bind_param("i", $user_id);

    if (!$stmt_clear_cart->execute()) {
        throw new Exception("Failed to clear cart: " . $stmt_clear_cart->error);
    }

    // Commit the transaction
    $conn->commit();

    // Redirect to order confirmation page
    header('Location: ' . app_url('orders/order_confirmation.php?order_id=' . $order_id));
    exit(); // Always call exit() after redirect

} catch (Exception $e) {
    // Rollback transaction in case of error
    $conn->rollback();
    die("Error placing order: " . $e->getMessage());
}

// Close the prepared statements and connection
$stmt->close();
$stmt_order->close();
$stmt_items->close();
$stmt_update_stock->close();
$stmt_clear_cart->close();
$conn->close();
?>
