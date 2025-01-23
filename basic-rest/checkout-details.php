<?php
header('Content-Type: application/json');

include 'dbcon.php'; // Include the database connection

$response = [];

try {
    // Retrieve the JSON data sent in the POST request
    $inputData = json_decode(file_get_contents('php://input'), true);

    // Check if user_id is provided
    if (!isset($inputData['user_id']) || empty($inputData['user_id'])) {
        $response['success'] = false;
        $response['message'] = 'User ID is required.';
        echo json_encode($response);
        exit;
    }

    $user_id = $inputData['user_id'];
    $imageBaseUrl = 'http://192.168.1.2/efvFrontend2025/basic-rest/product-images/';

    // Query to fetch checkout details from the cart table for the given user_id
    $query = "
        SELECT 
            c.cart_id, 
            c.model_id, 
            c.product_name, 
            c.brand_name, 
            c.price, 
            c.quantity, 
            c.total_price, 
            c.status,
            CONCAT(?, m.model_img) AS model_img
        FROM 
            cart c
        INNER JOIN 
            models m ON c.model_id = m.model_id
        WHERE 
            c.user_id = ? 
            AND c.status IN ('Pending', 'Pre-order')  -- Fetch both Pending and Pre-order statuses
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $imageBaseUrl, $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    // Check if any rows are returned
    if ($result->num_rows > 0) {
        $details = [];
        while ($row = $result->fetch_assoc()) {
            $details[] = $row;
        }

        $response['success'] = true;
        $response['details'] = $details;
    } else {
        $response['success'] = false;
        $response['message'] = 'No checkout details found for this user.';
    }

    $stmt->close();
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Return the response in JSON format
echo json_encode($response);

$conn->close();
?>
