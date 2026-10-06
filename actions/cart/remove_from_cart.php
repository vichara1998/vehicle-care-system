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

if ($itemId === false) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Invalid cart item.']));
}

$conn = app_db_connect(true);

$stmt = $conn->prepare('DELETE FROM cart WHERE cart_id = ? AND user_id = ?');
$stmt->bind_param('ii', $itemId, $userId);
$stmt->execute();
$removed = $stmt->affected_rows > 0;
$stmt->close();
$conn->close();

if (!$removed) {
    http_response_code(404);
    exit(json_encode(['success' => false, 'message' => 'Cart item not found.']));
}

echo json_encode(['success' => true]);
