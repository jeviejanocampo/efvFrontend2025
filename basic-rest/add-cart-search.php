<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection file
include 'dbcon.php';

// Retrieve POST data
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input data
    $requiredFields = ['user_id', 'model_id', 'product_name', 'price', 'quantity'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            exit;
        }
    }

    // Sanitize inputs
    $user_id = $conn->real_escape_string($data['user_id']);
    $model_id = $conn->real_escape_string($data['model_id']);
    $variant_id = isset($data['variant_id']) ? $conn->real_escape_string($data['variant_id']) : null;
    $product_name = $conn->real_escape_string($data['product_name']);
    $brand_name = isset($data['brand_name']) ? $conn->real_escape_string($data['brand_name']) : null;
    $price = $conn->real_escape_string($data['price']);
    $quantity = $conn->real_escape_string($data['quantity']);
    $total_price = $conn->real_escape_string($data['total_price']);
    $status = $conn->real_escape_string($data['status']);

    // Check if the product already exists in the cart for the user
    $checkQuery = "SELECT * FROM cart WHERE user_id = '$user_id' AND model_id = '$model_id'";
    if ($variant_id) {
        $checkQuery .= " AND variant_id = '$variant_id'";
    }
    $result = $conn->query($checkQuery);

    if ($result->num_rows > 0) {
        // Product already exists in the cart
        echo json_encode(['success' => false, 'message' => 'Product already in the cart']);
        exit;
    }

    // Insert new cart item
    $insertQuery = "INSERT INTO cart (user_id, model_id, variant_id, product_name, brand_name, price, quantity, total_price, status)
                    VALUES ('$user_id', '$model_id', " . ($variant_id ? "'$variant_id'" : "NULL") . ", 
                            '$product_name', " . ($brand_name ? "'$brand_name'" : "NULL") . ", 
                            '$price', '$quantity', '$total_price', '$status')";

    if ($conn->query($insertQuery)) {
        echo json_encode(['success' => true, 'message' => 'Product added to cart successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to add product to cart']);
    }
} else {
    // Invalid request method
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
