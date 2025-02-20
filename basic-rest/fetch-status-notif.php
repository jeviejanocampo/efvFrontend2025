<?php

header('Content-Type: application/json');
// Include database connection
include 'dbcon.php';

// Check if customer_id is provided
if (!isset($_GET['customer_id'])) {
    echo json_encode(['error' => 'customer_id is required']);
    exit();
}

$customer_id = intval($_GET['customer_id']);
$last_updated_at = isset($_GET['last_updated_at']) ? $_GET['last_updated_at'] : null;

try {
    // Base query
    $query = "
        SELECT order_id, status, updated_at 
        FROM orders 
        WHERE user_id = ? AND status = 'Ready To Pickup'
    ";

    // If last_updated_at is provided, fetch only newer orders
    if ($last_updated_at) {
        $query .= " AND updated_at > ?";
    }

    $query .= " ORDER BY updated_at DESC";

    $stmt = $conn->prepare($query);

    if ($last_updated_at) {
        $stmt->bind_param("is", $customer_id, $last_updated_at);
    } else {
        $stmt->bind_param("i", $customer_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    echo json_encode($orders);

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to fetch status', 'message' => $e->getMessage()]);
}

$conn->close();
?>
