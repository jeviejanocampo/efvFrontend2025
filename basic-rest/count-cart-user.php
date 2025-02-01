<?php
header('Content-Type: application/json');

include 'dbcon.php'; // Include the database connection file

// Get the customer_id (user_id) from the request body
$data = json_decode(file_get_contents('php://input'), true);
$customer_id = $data['customer_id'];

if (!$customer_id) {
    echo json_encode(['status' => 'error', 'message' => 'Customer ID is required']);
    exit;
}

// Query to sum the quantity of cart items for the given customer_id (user_id)
$query = "SELECT SUM(quantity) as cart_count 
          FROM cart WHERE user_id = ? AND status != 'completed'"; // Exclude completed orders
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id); // Bind the customer_id (user_id) parameter
$stmt->execute();
$stmt->bind_result($cart_count);
$stmt->fetch();

// If cart_count is null (i.e., no items found), set cart_count to 0
if ($cart_count === null) {
    $cart_count = 0;
}

// Return the result as a JSON response
echo json_encode([
    'status' => 'success',
    'cart_count' => $cart_count
]);

$stmt->close();
$conn->close();
?>
