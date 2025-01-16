<?php
header('Content-Type: application/json');

include 'dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $baseImageUrl = 'http://192.168.1.2/efvFrontend2025/basic-rest/product-images/';

    // Query to fetch model_id, model_name, model_img, and price
    $query = "SELECT model_id, model_name, CONCAT('$baseImageUrl', model_img) AS model_img, price 
              FROM models 
              WHERE status = 'active'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $models = [];

        while ($row = $result->fetch_assoc()) {
            $models[] = [
                'model_id' => $row['model_id'],
                'model_name' => $row['model_name'],
                'model_img' => $row['model_img'],
                'price' => $row['price']
            ];
        }

        echo json_encode($models);
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
