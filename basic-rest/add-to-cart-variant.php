<?php
header('Content-Type: application/json');

// Include database connection file
include 'dbcon.php';

// Get the raw POST data
$input = json_decode(file_get_contents('php://input'), true);

// Check if the required data is present
if (!isset($input['user_id'], $input['model_id'], $input['variant_id'], $input['product_name'], $input['price'], $input['quantity'], $input['total_price'])) {
    echo json_encode(['message' => 'Missing required parameters.']);
    exit;
}

$user_id = $input['user_id'];
$model_id = $input['model_id'];
$variant_id = $input['variant_id'];
$product_name = $input['product_name'];
$price = $input['price'];
$quantity = $input['quantity'];
$total_price = $input['total_price'];
$status = isset($input['status']) ? $input['status'] : 'pending'; // Default to 'pending' if not provided

// Fetch the brand_name from the models and brands table
$sql_get_brand = "SELECT b.brand_name 
                  FROM models m 
                  INNER JOIN brands b ON m.brand_id = b.brand_id 
                  WHERE m.model_id = ?";
$stmt_get_brand = $conn->prepare($sql_get_brand);
if (!$stmt_get_brand) {
    echo json_encode(['message' => 'Failed to prepare SQL statement for fetching brand name.']);
    exit;
}

$stmt_get_brand->bind_param("i", $model_id);
$stmt_get_brand->execute();
$result_get_brand = $stmt_get_brand->get_result();

// Check if the brand was found
if ($result_get_brand->num_rows > 0) {
    $row_brand = $result_get_brand->fetch_assoc();
    $brand_name = $row_brand['brand_name'];
} else {
    echo json_encode(['message' => 'Brand not found for the given model ID.']);
    exit;
}

// Check if the variant already exists in the cart for the user
$sql_check = "SELECT * FROM cart WHERE user_id = ? AND model_id = ? AND variant_id = ?";
$stmt_check = $conn->prepare($sql_check);
if (!$stmt_check) {
    echo json_encode(['message' => 'Failed to prepare SQL statement for checking cart.']);
    exit;
}

$stmt_check->bind_param("iii", $user_id, $model_id, $variant_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

// If the variant exists, update the quantity
if ($result_check->num_rows > 0) {
    $row = $result_check->fetch_assoc();
    $new_quantity = $row['quantity'] + $quantity;  // Add the existing quantity to the new quantity
    $new_total_price = $new_quantity * $price;  // Recalculate the total price

    // Update the cart with the new quantity and total price
    $sql_update = "UPDATE cart SET quantity = ?, total_price = ? WHERE cart_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    if (!$stmt_update) {
        echo json_encode(['message' => 'Failed to prepare SQL statement for updating cart.']);
        exit;
    }

    $stmt_update->bind_param("idi", $new_quantity, $new_total_price, $row['cart_id']);
    if ($stmt_update->execute()) {
        echo json_encode(['message' => 'Variant added to cart successfully!']);
    } else {
        echo json_encode(['message' => 'Failed to update cart.']);
    }

    $stmt_update->close();

} else {
    // If the variant doesn't exist, insert a new record into the cart
    $sql_insert = "INSERT INTO cart (user_id, model_id, variant_id, product_name, brand_name, price, quantity, total_price, status) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt_insert = $conn->prepare($sql_insert);
    if (!$stmt_insert) {
        echo json_encode(['message' => 'Failed to prepare SQL statement for inserting cart.']);
        exit;
    }

    $stmt_insert->bind_param("iiissdiis", $user_id, $model_id, $variant_id, $product_name, $brand_name, $price, $quantity, $total_price, $status);

    if ($stmt_insert->execute()) {
        echo json_encode(['message' => 'Variant added to cart successfully!']);
    } else {
        echo json_encode(['message' => 'Failed to add variant to cart.']);
    }

    $stmt_insert->close();
}

// Close the database connection
$stmt_check->close();
$stmt_get_brand->close();
$conn->close();
?>
