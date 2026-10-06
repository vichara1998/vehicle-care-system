<?php
require_once __DIR__ . '/../../app/bootstrap.php';

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
$item_id = $_POST['item_id'];

// Remove the item from the cart
$sql = "DELETE FROM cart WHERE cart_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $item_id, $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

$stmt->close();
$conn->close();
?>
