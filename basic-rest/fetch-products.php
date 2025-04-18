<?php
header('Content-Type: application/json');

include 'dbcon.php'; 
include 'ip-config.php';  // Include ip-config.php for baseImageUrl

// Function to fetch products based on brand_id
function getProducts($conn, $brand_id) {
    global $baseImageUrl;  // Access the global baseImageUrl from ip-config.php

    // Fetch products from the models table based on the brand_id and join with the brands and products tables
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
            b.brand_name,  -- Include brand_name from the brands table
            p.description,  -- Fetch description from the products table
            p.stocks_quantity  -- Fetch stocks_quantity from the products table
        FROM 
            models m
        JOIN 
            brands b ON m.brand_id = b.brand_id  -- Join with the brands table
        LEFT JOIN 
            products p ON m.model_id = p.model_id  -- Join with the products table to fetch additional columns
        WHERE 
            m.brand_id = ? AND (m.status = 'active' OR m.status = 'on order')";

    $stmt = $conn->prepare($productsQuery);
    $stmt->bind_param("i", $brand_id); // Bind the brand_id parameter
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if products are found
    $products = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Use baseImageUrl from ip-config.php for model image path
            $row['model_img'] = $baseImageUrl . $row['model_img'];

            // Add dynamic button text based on the status
            if ($row['status'] === 'on order') {
                $row['cart_button_text'] = 'Available for Pre-Order';
            } else {
                $row['cart_button_text'] = 'Add to Cart';
            }

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
