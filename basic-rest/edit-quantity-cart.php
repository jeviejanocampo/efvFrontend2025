<?php
header('Content-Type: application/json');

include 'dbcon.php'; // Database connection

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the raw POST data
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['cart_id']) && isset($data['quantity'])) {
        $cart_id = (int)$data['cart_id'];
        $quantity = (int)$data['quantity'];

        if ($quantity < 1) {
            echo json_encode(['success' => false, 'message' => 'Quantity must be at least 1']);
            exit;
        }

        $cart_query = "SELECT model_id, variant_id FROM cart WHERE cart_id = ?";
        if ($stmt = $conn->prepare($cart_query)) {
            $stmt->bind_param('i', $cart_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $cart = $result->fetch_assoc();
                $model_id = $cart['model_id'];
                $variant_id = $cart['variant_id'];

                $stock_quantity = 0;
                if ($variant_id != 0) {
                    $stock_query = "SELECT stocks_quantity FROM variants WHERE variant_id = ?";
                    if ($stock_stmt = $conn->prepare($stock_query)) {
                        $stock_stmt->bind_param('i', $variant_id);
                        $stock_stmt->execute();
                        $stock_result = $stock_stmt->get_result();
                        if ($stock_result->num_rows > 0) {
                            $variant = $stock_result->fetch_assoc();
                            $stock_quantity = $variant['stocks_quantity'];
                        }
                        $stock_stmt->close();
                    }
                } else {
                    $stock_query = "SELECT stocks_quantity FROM products WHERE model_id = ?";
                    if ($stock_stmt = $conn->prepare($stock_query)) {
                        $stock_stmt->bind_param('i', $model_id);
                        $stock_stmt->execute();
                        $stock_result = $stock_stmt->get_result();
                        if ($stock_result->num_rows > 0) {
                            $product = $stock_result->fetch_assoc();
                            $stock_quantity = $product['stocks_quantity'];
                        }
                        $stock_stmt->close();
                    }
                }

                if ($quantity > $stock_quantity) {
                    echo json_encode(['success' => false, 'message' => 'Quantity exceeds available stock']);
                    exit;
                }

                $update_query = "UPDATE cart SET quantity = ? WHERE cart_id = ?";
                if ($update_stmt = $conn->prepare($update_query)) {
                    $update_stmt->bind_param('ii', $quantity, $cart_id);
                    if ($update_stmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Quantity updated successfully']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
                    }
                    $update_stmt->close();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Database error: Unable to prepare update statement']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Cart item not found']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: Unable to prepare statement']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
