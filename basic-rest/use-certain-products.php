<?php
header('Content-Type: application/json');

include 'dbcon.php'; // Include your database connection file

// Function to fetch active brands based on category_id
function getBrandsByCategory($conn, $categoryId) {
    // Fetch active brands by category ID, using the category_id from categories table
    $query = "
        SELECT b.brand_id, b.brand_name, b.brand_image, b.status, b.created_at, b.updated_at
        FROM brands b
        WHERE b.cat_id = ? AND (b.status = 'active' OR b.status = 'on order')
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $categoryId);  // Bind the category_id parameter
    $stmt->execute();
    $result = $stmt->get_result();

    $brands = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Append the base URL for the brand image path
            $row['brand_image'] = 'http://192.168.1.32/efvFrontend2025/basic-rest/product-images/' . $row['brand_image'];
            $brands[] = $row;
        }
    }

    return $brands;
}

// Get category ID from request
$categoryId = isset($_GET['category_id']) ? $_GET['category_id'] : null;

// If category ID is provided, fetch the brands
if ($categoryId) {
    $brands = getBrandsByCategory($conn, $categoryId);
    echo json_encode($brands);
} else {
    echo json_encode(['message' => 'Category ID not provided']);
}
?>
