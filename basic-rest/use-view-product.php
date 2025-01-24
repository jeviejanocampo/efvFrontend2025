<?php
header('Content-Type: application/json');

include 'dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['model_id'])) {
        echo json_encode(['error' => 'Model ID is required']);
        exit;
    }

    $model_id = $conn->real_escape_string($_GET['model_id']);
    $baseImageUrl = 'http://192.168.1.32/efvFrontend2025/basic-rest/product-images/';

    // Query to fetch product details including the status
    $query = "SELECT 
                model_id, 
                model_name, 
                brand_name, 
                price, 
                description, 
                m_part_id, 
                stocks_quantity, 
                status,  
                CONCAT('$baseImageUrl', model_img) AS model_img 
              FROM products 
              WHERE model_id = '$model_id'";

    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        echo json_encode($product);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
