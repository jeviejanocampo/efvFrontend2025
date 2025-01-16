<?php
header('Content-Type: application/json');

include 'dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $baseImageUrl = 'http://192.168.1.2/efvFrontend2025/basic-rest/product-images/';

    // Query to fetch brand_id and brand_image
    $query = "SELECT brand_id, CONCAT('$baseImageUrl', brand_image) AS brand_image FROM categories WHERE status = 'active'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $categories = [];

        while ($row = $result->fetch_assoc()) {
            $categories[] = [
                'brand_id' => $row['brand_id'],
                'brand_image' => $row['brand_image']
            ];
        }

        echo json_encode($categories);
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
