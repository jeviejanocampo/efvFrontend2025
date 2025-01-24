<?php
header('Content-Type: application/json');
error_reporting(0); // Disable PHP warnings and notices
ini_set('display_errors', 0); // Suppress error display

// Include database connection file
include 'dbcon.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the raw POST data
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input data
    if (
        isset($data['user_id'], $data['total_items'], $data['total_price'], $data['pickup_date'], $data['pickup_location'], $data['payment_method'], $data['order_details'])
        && is_array($data['order_details'])
    ) {
        $user_id = $data['user_id'];
        $total_items = $data['total_items'];
        $total_price = $data['total_price'];
        $order_notes = isset($data['order_notes']) ? $data['order_notes'] : '';
        $pickup_date = $data['pickup_date'];
        $pickup_location = $data['pickup_location'];
        $payment_method = $data['payment_method'];
        $status = 'Pending'; // Default status
        $created_at = date('Y-m-d H:i:s');
        $updated_at = date('Y-m-d H:i:s');

        // Start a database transaction
        mysqli_begin_transaction($conn);

        try {
            // Insert into `orders` table
            $orderQuery = "INSERT INTO orders (user_id, total_items, total_price, order_notes, pickup_date, pickup_location, payment_method, status, created_at, updated_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $orderQuery);
            mysqli_stmt_bind_param($stmt, 'iissssssss', $user_id, $total_items, $total_price, $order_notes, $pickup_date, $pickup_location, $payment_method, $status, $created_at, $updated_at);
            mysqli_stmt_execute($stmt);

            // Get the last inserted order_id
            $order_id = mysqli_insert_id($conn);

            // Insert each item into `order_details` table
            $orderDetailsQuery = "INSERT INTO order_details (order_id, model_id, product_name, brand_name, quantity, price, total_price, product_status, created_at, updated_at) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtDetails = mysqli_prepare($conn, $orderDetailsQuery);

            foreach ($data['order_details'] as $item) {
                $model_id = $item['model_id'];
                $product_name = $item['product_name'];
                $brand_name = $item['brand_name'];
                $quantity = $item['quantity'];
                $price = $item['price'];
                $total_price_item = $item['total_price'];
                $product_status = $item['status']; // Get the status for each item
                $created_at_item = $created_at;
                $updated_at_item = $updated_at;

                mysqli_stmt_bind_param($stmtDetails, 'iissiddsss', $order_id, $model_id, $product_name, $brand_name, $quantity, $price, $total_price_item, $product_status, $created_at_item, $updated_at_item);
                mysqli_stmt_execute($stmtDetails);
            }

            // Delete cart items where user_id matches
            $deleteCartQuery = "DELETE FROM cart WHERE user_id = ?";
            $stmtDeleteCart = mysqli_prepare($conn, $deleteCartQuery);
            mysqli_stmt_bind_param($stmtDeleteCart, 'i', $user_id);
            mysqli_stmt_execute($stmtDeleteCart);

            // Commit the transaction
            mysqli_commit($conn);

            // Return a success response
            echo json_encode(['success' => true, 'message' => 'Order placed successfully and cart items deleted.']);
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            mysqli_rollback($conn);

            // Return an error response
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to place the order. Please try again.', 'error' => $e->getMessage()]);
        }
    } else {
        // Return an error response for invalid input
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    }
} else {
    // Return an error response for invalid request method
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
