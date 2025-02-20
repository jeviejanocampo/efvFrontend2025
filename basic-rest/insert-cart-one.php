<?php
header('Content-Type: application/json');

// Include database connection file
include 'dbcon.php';

// Get the raw POST data
$input = json_decode(file_get_contents('php://input'), true);

// Check if the required data is present
if (!isset($input['user_id'], $input['model_id'], $input['product_name'], $input['brand_name'], $input['price'], $input['quantity'], $input['total_price'])) {
    echo json_encode(['message' => 'Missing required parameters.']);
    exit;
}

$user_id = $input['user_id'];
$model_id = $input['model_id'];
$variant_id = isset($input['variant_id']) ? $input['variant_id'] : 0; // Default to 0 if not provided
$product_name = $input['product_name'];
$brand_name = $input['brand_name'];
$price = $input['price'];
$quantity = $input['quantity'];
$total_price = $input['total_price'];
$status = isset($input['status']) ? $input['status'] : 'pending'; // Default to 'pending' if not provided

// Check if the product already exists in the cart based on user_id and model_id
$sql_check = "SELECT cart_id, quantity FROM cart WHERE user_id = ? AND model_id = ?";
$stmt_check = $conn->prepare($sql_check);
if (!$stmt_check) {
    echo json_encode(['message' => 'Failed to prepare SQL statement for checking cart.']);
    exit;
}

$stmt_check->bind_param("ii", $user_id, $model_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

// If the product exists, update the quantity
if ($result_check->num_rows > 0) {
    $row = $result_check->fetch_assoc();
    $new_quantity = $row['quantity'] + $quantity; // Add new quantity to existing quantity
    $new_total_price = $new_quantity * $price; // Recalculate total price

    // Update the cart with the new quantity and total price
    $sql_update = "UPDATE cart SET quantity = ?, total_price = ? WHERE cart_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    if (!$stmt_update) {
        echo json_encode(['message' => 'Failed to prepare SQL statement for updating cart.']);
        exit;
    }

    $stmt_update->bind_param("idi", $new_quantity, $new_total_price, $row['cart_id']);
    if ($stmt_update->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cart updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update cart.']);
    }

    $stmt_update->close();
} else {
    // Insert a new record into the cart
    $sql_insert = "INSERT INTO cart (user_id, model_id, variant_id, product_name, brand_name, price, quantity, total_price, status) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt_insert = $conn->prepare($sql_insert);
    if (!$stmt_insert) {
        echo json_encode(['message' => 'Failed to prepare SQL statement for inserting cart.']);
        exit;
    }

    $stmt_insert->bind_param("iiissdiis", $user_id, $model_id, $variant_id, $product_name, $brand_name, $price, $quantity, $total_price, $status);

    if ($stmt_insert->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product added to cart successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add product to cart.']);
    }

    $stmt_insert->close();
}

// Close the database connection
$stmt_check->close();
$conn->close();

?>
