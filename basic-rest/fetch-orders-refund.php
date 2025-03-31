<?php
header('Content-Type: application/json');
error_reporting(0); // Disable PHP warnings and notices
ini_set('display_errors', 0); // Suppress error display

// Include database connection file
include 'dbcon.php';

// Get user_id from request
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id === 0) {
    echo json_encode(["error" => "Invalid user ID"]);
    exit();
}

// Fetch refundable orders (assuming 'status' defines refundable orders)
$query = "SELECT order_id, created_at FROM orders WHERE user_id = ? AND status = 'Completed'";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($orders);
?>
