<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

include 'dbcon.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id'])) {
    echo json_encode([]);
    exit();
}

$user_id = $data['user_id'];

// Only return orders from last 5 days without existing refund requests
$query = "
    SELECT o.order_id, o.reference_id, o.created_at
    FROM orders o
    LEFT JOIN refund_order r ON o.order_id = r.order_id
    WHERE o.user_id = ?
      AND r.order_id IS NULL
      AND o.created_at >= DATE_SUB(NOW(), INTERVAL 5 DAY)
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode($orders);

$stmt->close();
$conn->close();
