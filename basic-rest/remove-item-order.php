<?php
header('Content-Type: application/json');

include 'dbcon.php'; // Database connection

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

// Retrieve model_id or variant_id from the request body
$id = isset($data['model_id']) ? $data['model_id'] : (isset($data['variant_id']) ? $data['variant_id'] : null);

if (empty($id)) {
    echo json_encode(['status' => 'error', 'message' => 'ID (model_id or variant_id) is required.']);
    exit();
}

// Retrieve the price and order_id of the product based on model_id or variant_id
$sql_get_price = "SELECT od.price, od.quantity, o.order_id 
                  FROM order_details od
                  INNER JOIN orders o ON od.order_id = o.order_id
                  WHERE od.model_id = ? OR od.variant_id = ? LIMIT 1"; 

$stmt_get_price = $conn->prepare($sql_get_price);
$stmt_get_price->bind_param("is", $id, $id); // Bind the ID parameter to the query
$stmt_get_price->execute();
$result = $stmt_get_price->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $price = $row['price'];
    $quantity = $row['quantity'];
    $order_id = $row['order_id'];

    // Step 1: Update product status to 'refunded' in order_details
    $sql_update_status = "UPDATE order_details SET product_status = 'to be refunded' WHERE model_id = ? OR variant_id = ?";
    $stmt_update_status = $conn->prepare($sql_update_status);
    $stmt_update_status->bind_param("is", $id, $id);
    $stmt_update_status->execute();

    // Step 2: Update the total_price in orders by subtracting the price of the refunded product
    $total_refunded = $price * $quantity;
    $sql_update_order = "UPDATE orders SET total_price = total_price - ? WHERE order_id = ?";
    $stmt_update_order = $conn->prepare($sql_update_order);
    $stmt_update_order->bind_param("di", $total_refunded, $order_id);
    $stmt_update_order->execute();

    echo json_encode(['status' => 'success', 'message' => 'Item status updated to refunded, and total price updated']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Item not found']);
}

// Close statements and connection
$stmt_get_price->close();
$stmt_update_status->close();
$stmt_update_order->close();
$conn->close();


?>
