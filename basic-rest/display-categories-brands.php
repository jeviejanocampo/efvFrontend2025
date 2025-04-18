<?php
header('Content-Type: application/json');

include 'dbcon.php';           
include 'ip-config.php';       

function getCategories($conn) {
    global $baseImageUrl; 

    // Fetch active categories
    $categoriesQuery = "SELECT category_id, category_name, cat_image FROM categories WHERE status = 'active'";
    $categoriesResult = $conn->query($categoriesQuery);

    // Check if categories are found
    $categories = [];
    if ($categoriesResult->num_rows > 0) {
        while ($row = $categoriesResult->fetch_assoc()) {
            // Use baseImageUrl directly (no need to append 'product-images/')
            $row['cat_image'] = $baseImageUrl . $row['cat_image'];  // Corrected this line
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
