<?php
require_once 'config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['items'])) {
    echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
    exit;
}

$userId = $_SESSION['user_id'];
$orderId = 'ML-' . mt_rand(100000, 999999);
$totalAmount = 0;

foreach ($input['items'] as $item) {
    $totalAmount += ($item['price'] * $item['qty']);
}

try {
    $pdo->beginTransaction();

    $stmtOrder = $pdo->prepare("INSERT INTO orders (user_id, order_id, total_amount, status) VALUES (?, ?, ?, 'In Process')");
    $stmtOrder->execute([$userId, $orderId, $totalAmount]);
    $dbOrderId = $pdo->lastInsertId();

    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_name, stall_name, price, quantity) VALUES (?, ?, ?, ?, ?)");

    foreach ($input['items'] as $item) {
        $stmtItem->execute([
            $dbOrderId,
            $item['productName'],
            $item['stallName'],
            $item['price'],
            $item['qty']
        ]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'order_id' => $orderId]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>