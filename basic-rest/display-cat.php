<?php
header('Content-Type: application/json');

include 'dbcon.php';
include 'ip-config.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Query to fetch brand_id and brand_image
    $query = "SELECT brand_id, CONCAT('$baseImageUrl', brand_image) AS brand_image FROM brands WHERE status = 'active'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $brands = [];

        while ($row = $result->fetch_assoc()) {
            $brands[] = [
                'brand_id' => $row['brand_id'],
                'brand_image' => $row['brand_image']
            ];
        }

        echo json_encode($brands);
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
