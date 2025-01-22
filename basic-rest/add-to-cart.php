<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Include database connection
include 'dbcon.php';

// Get the request body
$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

// Check if the necessary data is provided
if (
    isset($data['user_id']) &&
    isset($data['model_id']) &&
    isset($data['variant_id']) &&
    isset($data['product_name']) &&
    isset($data['brand_name']) &&
    isset($data['price']) &&
    isset($data['quantity']) &&
    isset($data['total_price']) &&
    isset($data['status'])
) {
    $user_id = $data['user_id'];
    $model_id = $data['model_id'];
    $variant_id = $data['variant_id'];
    $product_name = $data['product_name'];
    $brand_name = $data['brand_name'];
    $price = $data['price'];
    $quantity = $data['quantity'];
    $total_price = $data['total_price'];
    $status = $data['status'];

    // Check if the product is already in the cart
    $query_check = "SELECT * FROM cart WHERE user_id = ? AND model_id = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("ii", $user_id, $model_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Product already added to cart."
        ]);
    } else {
        // Insert the product into the cart
        $query_insert = "INSERT INTO cart (user_id, model_id, variant_id, product_name, brand_name, price, quantity, total_price, status)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($query_insert);
        $stmt_insert->bind_param(
            "iiissddds",
            $user_id,
            $model_id,
            $variant_id,
            $product_name,
            $brand_name,
            $price,
            $quantity,
            $total_price,
            $status
        );

        if ($stmt_insert->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Product added to cart successfully."
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Failed to add product to cart. Please try again."
            ]);
        }
    }

    // Close statements
    $stmt_check->close();
    $stmt_insert->close();
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request. Missing required fields."
    ]);
}

// Close database connection
$conn->close();
?>
