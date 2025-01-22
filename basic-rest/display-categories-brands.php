<?php
header('Content-Type: application/json');

include 'dbcon.php'; // Include your database connection file

// Function to fetch active categories
function getCategories($conn) {
    // Fetch active categories
    $categoriesQuery = "SELECT category_id, category_name, cat_image FROM categories WHERE status = 'active'";
    $categoriesResult = $conn->query($categoriesQuery);

    // Check if categories are found
    $categories = [];
    if ($categoriesResult->num_rows > 0) {
        while ($row = $categoriesResult->fetch_assoc()) {
            // Append the base URL for the category image path
            $row['cat_image'] = 'http://192.168.1.2/efvFrontend2025/basic-rest/product-images/' . $row['cat_image'];
            $categories[] = $row;
        }
    }

    return $categories;
}

// Get categories
$categories = getCategories($conn);

// Return as JSON
echo json_encode(['categories' => $categories]);
?>
