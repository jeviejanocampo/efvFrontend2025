<?php
header('Content-Type: application/json');

include 'dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $baseImageUrl = 'http://192.168.1.2/efvFrontend2025/basic-rest/product-images/';

    // Query to fetch model_id, model_name, model_img, price, and w_variant
    $query = "SELECT model_id, model_name, CONCAT('$baseImageUrl', model_img) AS model_img, price, w_variant
              FROM models
              WHERE (status = 'active' OR status = 'on order') AND w_variant = 'none'";  // Filter for models with w_variant = 'none'
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $models = [];

        while ($row = $result->fetch_assoc()) {
            $models[] = [
                'model_id' => $row['model_id'],
                'model_name' => $row['model_name'],
                'model_img' => $row['model_img'],
                'price' => $row['price'],
                'w_variant' => $row['w_variant'],  // Include the w_variant field if needed later
            ];
        }

        echo json_encode($models);
    } else {
        echo json_encode([]);  // Return an empty array if no models match the filter
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
