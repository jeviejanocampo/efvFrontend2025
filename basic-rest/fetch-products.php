<?php
header('Content-Type: application/json');

include 'dbcon.php'; // Include your database connection file

// Function to fetch products based on brand_id
function getProducts($conn, $brand_id) {
    // Fetch active products from the models table based on the brand_id and join with the brands table
    $productsQuery = "
        SELECT 
            m.model_id, 
            m.model_name, 
            m.model_img, 
            m.price, 
            m.brand_id, 
            m.w_variant, 
            m.status, 
            m.created_at, 
            m.updated_at,
            b.brand_name  -- Include brand_name from the brands table
        FROM 
            models m
        JOIN 
            brands b ON m.brand_id = b.brand_id  -- Join with the brands table to get the brand_name
        WHERE 
            m.brand_id = ? AND m.status = 'active'";  // Only select active models

    $stmt = $conn->prepare($productsQuery);
    $stmt->bind_param("i", $brand_id); // Bind the brand_id parameter
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if products are found
    $products = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Append the base URL for the model image path
            $row['model_img'] = 'http://192.168.1.2/efvFrontend2025/basic-rest/product-images/' . $row['model_img'];
            $products[] = $row;
        }
    }

    return $products;
}

// Get the brand_id from the request
$brand_id = isset($_GET['brand_id']) ? $_GET['brand_id'] : 0;
if ($brand_id > 0) {
    $products = getProducts($conn, $brand_id);
    echo json_encode($products); // Return products as JSON
} else {
    echo json_encode(['error' => 'Brand ID is missing or invalid']);
}
?>
