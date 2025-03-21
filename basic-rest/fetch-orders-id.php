<?php
header('Content-Type: application/json');

include 'dbcon.php';

try {
    // Get the user_id from the request (assumes it is sent as a GET parameter)
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

    if (!$user_id) {
        throw new Exception('User ID is required');
    }

    // Query to fetch order_id, created_at, and status for the given user_id
    $query = "SELECT order_id, created_at, status FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception("Database Query Failed: " . mysqli_error($conn));
    }

    // Initialize an array to store all order data
    $orders = [];

    // Fetch all the orders for the user
    while ($order = mysqli_fetch_assoc($result)) {
        // Add each order_id, created_at, and status to the array
        $orders[] = [
            'order_id' => $order['order_id'],
            'created_at' => $order['created_at'],
            'status' => $order['status'] // Include status in the response
        ];
    }

    if (count($orders) > 0) {
        // Send the order data as a JSON response
        echo json_encode(['orders' => $orders]);
    } else {
        // If no orders exist for the given user_id, send a meaningful response
        echo json_encode(['message' => 'No orders found for this user']);
    }

} catch (Exception $e) {
    // Handle errors and send an error response
    echo json_encode(['error' => $e->getMessage()]);
}

mysqli_close($conn); // Close the database connection
?>
