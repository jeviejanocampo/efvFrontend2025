<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

include 'dbcon.php';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id === 0) {
    echo json_encode(["error" => "Invalid user ID"]);
    exit();
}

$query = "SELECT order_id, created_at FROM orders WHERE user_id = ? AND status = 'Completed' ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $order_id = $row['order_id'];
    
    $refQuery = "SELECT reference_id FROM order_reference WHERE order_id = ?";
    $refStmt = $conn->prepare($refQuery);
    $refStmt->bind_param("i", $order_id);
    $refStmt->execute();
    $refResult = $refStmt->get_result();
    $reference = $refResult->fetch_assoc();

    $row['reference_id'] = $reference ? $reference['reference_id'] : null;

    $orders[] = $row;

    $refStmt->close();
}

$stmt->close();
$conn->close();

echo json_encode($orders);
?>
