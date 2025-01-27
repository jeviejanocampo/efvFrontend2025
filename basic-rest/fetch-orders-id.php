<?php
header('Content-Type: application/json');

include 'dbcon.php'; // Ensure this file establishes the DB connection and assigns it to $conn

try {
    // Get the user_id from the request (assumes it is sent as a GET parameter)
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

    if (!$user_id) {
        throw new Exception('User ID is required');
    }

    // Query to fetch all order_ids for the given user_id
    $query = "SELECT order_id FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception("Database Query Failed: " . mysqli_error($conn));
    }

    // Initialize an array to store all order IDs
    $orders = [];

    // Fetch all the orders for the user
    while ($order = mysqli_fetch_assoc($result)) {
        $orders[] = $order['order_id']; // Add each order_id to the array
    }

    if (count($orders) > 0) {
        // Send the order_ids as a JSON response
        echo json_encode(['order_ids' => $orders]);
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
