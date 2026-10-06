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
$input = json_decode(file_get_contents('php://input'), true);
$spareId = filter_var($input['spare_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($spareId === false) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Invalid product.']));
}

$conn = app_db_connect(true);

$stockStmt = $conn->prepare('SELECT stock FROM spare_parts WHERE spare_id = ?');
$stockStmt->bind_param('i', $spareId);
$stockStmt->execute();
$product = $stockStmt->get_result()->fetch_assoc();
$stockStmt->close();

if (!$product || (int) $product['stock'] < 1) {
    http_response_code(404);
    $conn->close();
    exit(json_encode(['success' => false, 'message' => 'This product is unavailable.']));
}

$checkStmt = $conn->prepare('SELECT cart_id FROM cart WHERE user_id = ? AND spare_id = ?');
$checkStmt->bind_param('ii', $userId, $spareId);
$checkStmt->execute();
$existingItem = $checkStmt->get_result()->fetch_assoc();
$checkStmt->close();

if ($existingItem) {
    $cartId = (int) $existingItem['cart_id'];
    $updateStmt = $conn->prepare('UPDATE cart SET quantity = quantity + 1 WHERE cart_id = ? AND user_id = ? AND quantity < (SELECT stock FROM spare_parts WHERE spare_id = ?)');
    $updateStmt->bind_param('iii', $cartId, $userId, $spareId);
    $updated = $updateStmt->execute() && $updateStmt->affected_rows === 1;
    $updateStmt->close();
    $conn->close();

    if (!$updated) {
        http_response_code(409);
        exit(json_encode(['success' => false, 'message' => 'There is not enough stock for another item.']));
    }

    exit(json_encode(['success' => true, 'message' => 'Cart quantity updated.']));
}

$insertStmt = $conn->prepare('INSERT INTO cart (user_id, spare_id, quantity) VALUES (?, ?, 1)');
$insertStmt->bind_param('ii', $userId, $spareId);
$added = $insertStmt->execute();
$insertStmt->close();
$conn->close();

if (!$added) {
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'Unable to add this product right now.']));
}

echo json_encode(['success' => true, 'message' => 'Item added to cart.']);
