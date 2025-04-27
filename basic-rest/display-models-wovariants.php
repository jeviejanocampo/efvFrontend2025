<?php
header('Content-Type: application/json');

include 'dbcon.php';
include 'ip-config.php';       

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Query to fetch model_id, model_name, model_img, price, w_variant, and stocks_quantity
    $query = "SELECT m.model_id, m.model_name, CONCAT('$baseImageUrl', m.model_img) AS model_img, 
                     m.price, m.w_variant, p.stocks_quantity, m.status
              FROM models m
              LEFT JOIN products p ON m.model_id = p.model_id  -- Join models with products based on model_id
              WHERE (m.status = 'active' OR m.status = 'on order') 
                AND m.w_variant = 'none'";  // Filter for models with w_variant = 'none'

    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $models = [];

        while ($row = $result->fetch_assoc()) {
            $models[] = [
                'model_id' => $row['model_id'],
                'model_name' => $row['model_name'],
                'model_img' => $row['model_img'],
                'price' => $row['price'],
                'w_variant' => $row['w_variant'],
                'stocks_quantity' => $row['stocks_quantity'] ?? 0,  
                'status' => $row['status']
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
