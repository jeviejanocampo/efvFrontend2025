<?php
header('Content-Type: application/json');

include 'dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validate required fields
    if (empty($data['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }

    // Sanitize input data
    $user_id = $conn->real_escape_string($data['user_id']);

    // Query to fetch cart items and related model images
    $query = "
        SELECT c.cart_id, c.model_id, c.product_name, c.variant_id, c.brand_name, c.price, c.quantity, c.total_price, c.status, 
               m.model_img 
        FROM cart c
        JOIN models m ON c.model_id = m.model_id
        WHERE c.user_id = '$user_id'
    ";
    
    $result = $conn->query($query);

    // Check if cart items are found
    if ($result->num_rows > 0) {
        $cart_items = [];
        
        while ($row = $result->fetch_assoc()) {
            // Add the model image URL from the 'product-images' folder
            $row['model_img'] = 'http://192.168.1.32/efvFrontend2025/basic-rest/product-images/' . $row['model_img'];
            $cart_items[] = $row;
        }
        
        echo json_encode(['success' => true, 'cart_items' => $cart_items]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No items found for this user']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
