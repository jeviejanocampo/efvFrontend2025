<?php
header('Content-Type: application/json');

// Include the database connection
include 'dbcon.php';

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

// Retrieve order_id and user_id from the request
$order_id = $data['order_id'];
$user_id = $data['user_id'];

// Check if order_id and user_id are provided
if (empty($order_id) || empty($user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing order_id or user_id']);
    exit();
}

// Check if the order exists and belongs to the user
$query = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Order not found or does not belong to the user']);
    exit();
}

// Proceed to cancel the order
$query = "UPDATE orders SET status = 'Cancelled' WHERE order_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $order_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Order cancelled successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to cancel the order']);
}

// Close the connection
$stmt->close();
$conn->close();
?>
