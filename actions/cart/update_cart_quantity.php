<?php
require_once __DIR__ . '/../../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit(json_encode(['success' => false, 'message' => 'Method not allowed.']));
}

app_require_csrf_token();
$userId = app_require_authenticated_user();
$itemId = filter_var($_POST['item_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 99]]);

if ($itemId === false || $quantity === false) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Invalid cart item or quantity.']));
}

$conn = app_db_connect(true);

$stmt = $conn->prepare('UPDATE cart c JOIN spare_parts s ON s.spare_id = c.spare_id SET c.quantity = ? WHERE c.cart_id = ? AND c.user_id = ? AND s.stock >= ?');
$stmt->bind_param('iiii', $quantity, $itemId, $userId, $quantity);
$stmt->execute();
$updated = $stmt->affected_rows > 0;
$stmt->close();
$conn->close();

if (!$updated) {
    http_response_code(409);
    exit(json_encode(['success' => false, 'message' => 'Cart item not found or stock is insufficient.']));
}

echo json_encode(['success' => true]);
