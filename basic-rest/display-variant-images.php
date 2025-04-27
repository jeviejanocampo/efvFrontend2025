<?php
header('Content-Type: application/json');

// Include database connection
include 'dbcon.php';

$baseImageUrl = "http://192.168.223.22/efvManagement2025/public/product-images/";

if (!isset($_GET['variant_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "variant_id parameter is required"
    ]);
    exit;
}

$variant_id = intval($_GET['variant_id']);

$sql = "SELECT id, variant_id, image FROM variant_images WHERE variant_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare SQL statement"
    ]);
    exit;
}

$stmt->bind_param("i", $variant_id);
$stmt->execute();
$result = $stmt->get_result();

$images = [];

while ($row = $result->fetch_assoc()) {
    $images[] = [
        "id" => $row['id'],
        "variant_id" => $row['variant_id'],
        "image_url" => $baseImageUrl . $row['image']
    ];
}

$stmt->close();
$conn->close();

echo json_encode([
    "success" => true,
    "images" => $images
]);
?>
