<?php

header('Content-Type: application/json');
include 'dbcon.php';

// Check if the customer_id parameter is provided
if (!isset($_GET['customer_id'])) {
    echo json_encode(['error' => 'customer_id is required']);
    exit();
}

$customer_id = intval($_GET['customer_id']);

try {
    // Query to fetch all orders for the given customer_id, excluding "Pending" or "pending" status
    $query = "
        SELECT order_id, status, updated_at 
        FROM orders 
        WHERE user_id = ? AND status NOT IN ('Pending', 'pending')
        ORDER BY updated_at DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    // Return the orders as JSON
    echo json_encode($orders);

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to fetch status', 'message' => $e->getMessage()]);
}

$conn->close();
?>
