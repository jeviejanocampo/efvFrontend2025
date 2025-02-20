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
            // Check stock availability before proceeding
            foreach ($data['order_details'] as $item) {
                $model_id = $item['model_id'];
                $variant_id = $item['variant_id'];
                $quantity = $item['quantity'];

                if ($variant_id != 0) {
                    // Check stock in variants table
                    $checkStockQuery = "SELECT stocks_quantity FROM variants WHERE variant_id = ?";
                    $stmtCheckStock = mysqli_prepare($conn, $checkStockQuery);
                    mysqli_stmt_bind_param($stmtCheckStock, 'i', $variant_id);
                    mysqli_stmt_execute($stmtCheckStock);
                    mysqli_stmt_bind_result($stmtCheckStock, $stocks_quantity);
                    mysqli_stmt_fetch($stmtCheckStock);
                    mysqli_stmt_close($stmtCheckStock);

                    if ($stocks_quantity < $quantity) {
                        throw new Exception("Insufficient stock for variant_id: $variant_id");
                    }
                } else {
                    // Check stock in products table
                    $checkStockQuery = "SELECT stocks_quantity FROM products WHERE model_id = ?";
                    $stmtCheckStock = mysqli_prepare($conn, $checkStockQuery);
                    mysqli_stmt_bind_param($stmtCheckStock, 'i', $model_id);
                    mysqli_stmt_execute($stmtCheckStock);
                    mysqli_stmt_bind_result($stmtCheckStock, $stocks_quantity);
                    mysqli_stmt_fetch($stmtCheckStock);
                    mysqli_stmt_close($stmtCheckStock);

                    if ($stocks_quantity < $quantity) {
                        throw new Exception("Insufficient stock for model_id: $model_id");
                    }
                }
            }

            // Insert into `orders` table
            $orderQuery = "INSERT INTO orders (user_id, total_items, total_price, order_notes, pickup_date, pickup_location, payment_method, status, created_at, updated_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $orderQuery);
            mysqli_stmt_bind_param($stmt, 'iissssssss', $user_id, $total_items, $total_price, $order_notes, $pickup_date, $pickup_location, $payment_method, $status, $created_at, $updated_at);
            mysqli_stmt_execute($stmt);

            // Get the last inserted order_id
            $order_id = mysqli_insert_id($conn);

            // Insert each item into `order_details` table
            $orderDetailsQuery = "INSERT INTO order_details (order_id, model_id, variant_id, product_name, brand_name, quantity, price, total_price, product_status, part_id, created_at, updated_at) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtDetails = mysqli_prepare($conn, $orderDetailsQuery);

            foreach ($data['order_details'] as $item) {
                $model_id = $item['model_id'];
                $variant_id = $item['variant_id'];
                $product_name = $item['product_name'];
                $brand_name = $item['brand_name'];
                $quantity = $item['quantity'];
                $price = $item['price'];
                $total_price_item = $item['total_price'];
                $product_status = $item['status']; // Get the status for each item
            
                // Generate part_id
                if ($variant_id == 0) {
                    // Fetch m_part_id from the products table
                    $getPartIdQuery = "SELECT m_part_id FROM products WHERE model_id = ?";
                    $stmtGetPartId = mysqli_prepare($conn, $getPartIdQuery);
                    mysqli_stmt_bind_param($stmtGetPartId, 'i', $model_id);
                    mysqli_stmt_execute($stmtGetPartId);
                    mysqli_stmt_bind_result($stmtGetPartId, $m_part_id);
                    mysqli_stmt_fetch($stmtGetPartId);
                    mysqli_stmt_close($stmtGetPartId);
            
                    $part_id = $m_part_id;
                } else {
                    // Generate part_id using order_id if variant_id is not 0
                    $last_four_digits = substr($order_id, -4);
                    $part_id = $order_id . '-' . $last_four_digits;
                }
            
                $created_at_item = $created_at;
                $updated_at_item = $updated_at;
            
                mysqli_stmt_bind_param($stmtDetails, 'iiissiddssss', $order_id, $model_id, $variant_id, $product_name, $brand_name, $quantity, $price, $total_price_item, $product_status, $part_id, $created_at_item, $updated_at_item);
                mysqli_stmt_execute($stmtDetails);
            
                // Subtract quantity from the respective table
                if ($variant_id == 0) {
                    // Update products table for model_id
                    $updateProductQuery = "UPDATE products SET stocks_quantity = stocks_quantity - ? WHERE model_id = ?";
                    $stmtUpdateProduct = mysqli_prepare($conn, $updateProductQuery);
                    mysqli_stmt_bind_param($stmtUpdateProduct, 'ii', $quantity, $model_id);
                    mysqli_stmt_execute($stmtUpdateProduct);
                } else {
                    // Update variants table for variant_id
                    $updateVariantQuery = "UPDATE variants SET stocks_quantity = stocks_quantity - ? WHERE variant_id = ?";
                    $stmtUpdateVariant = mysqli_prepare($conn, $updateVariantQuery);
                    mysqli_stmt_bind_param($stmtUpdateVariant, 'ii', $quantity, $variant_id);
                    mysqli_stmt_execute($stmtUpdateVariant);
                }
            }
            

            // Delete cart items where user_id matches
            $deleteCartQuery = "DELETE FROM cart WHERE user_id = ?";
            $stmtDeleteCart = mysqli_prepare($conn, $deleteCartQuery);
            mysqli_stmt_bind_param($stmtDeleteCart, 'i', $user_id);
            mysqli_stmt_execute($stmtDeleteCart);

            // Commit the transaction
            mysqli_commit($conn);

            // Return a success response
            echo json_encode(['success' => true, 'message' => 'Order placed successfully, stock updated, and cart items deleted.']);
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            mysqli_rollback($conn);

            // Return an error response
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to place the order, out of stocks or your quantity exceeds the available stocks. Please try again later, thank you.', 'error' => $e->getMessage()]);
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
