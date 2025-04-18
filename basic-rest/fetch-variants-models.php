<?php
header('Content-Type: application/json');

// Include database connection file
include 'dbcon.php';
include 'ip-config.php'; // Include ip-config.php for baseImageUrl

// Initialize response array
$response = [
    'variants' => [],
    'models' => [],
];

try {
    // Fetch all variants and join models to get brand_id, then join brands to get brand_name
    $variantQuery = "SELECT 
                        v.variant_id, 
                        v.model_id, 
                        v.product_name, 
                        v.variant_image, 
                        v.part_id, 
                        v.price, 
                        v.specification, 
                        v.description, 
                        v.stocks_quantity,
                        m.brand_id,
                        b.brand_name
                     FROM variants v
                     LEFT JOIN models m ON v.model_id = m.model_id
                     LEFT JOIN brands b ON m.brand_id = b.brand_id";

    $variantResult = mysqli_query($conn, $variantQuery);

    if (!$variantResult) {
        throw new Exception('Error fetching variants: ' . mysqli_error($conn));
    }

    while ($variant = mysqli_fetch_assoc($variantResult)) {
        // Use baseImageUrl from ip-config.php for variant image path
        $variant['variant_image'] = $baseImageUrl . $variant['variant_image'];
        $response['variants'][] = $variant;
    }

    // Fetch all models from the 'models' table, joining with the 'brands' table
    $modelQuery = "SELECT 
                        m.model_id, 
                        m.model_name, 
                        m.model_img, 
                        m.price, 
                        m.brand_id, 
                        m.w_variant,
                        m.status, 
                        b.brand_name,
                        p.description AS product_description,
                        p.stocks_quantity
                   FROM models m
                   LEFT JOIN brands b ON m.brand_id = b.brand_id
                   LEFT JOIN products p ON m.model_id = p.model_id"; 

    $modelResult = mysqli_query($conn, $modelQuery);

    if (!$modelResult) {
        throw new Exception('Error fetching models: ' . mysqli_error($conn));
    }

    while ($model = mysqli_fetch_assoc($modelResult)) {
        // Use baseImageUrl from ip-config.php for model image path
        $model['model_img'] = $baseImageUrl . $model['model_img'];
        
        $response['models'][] = $model;
    }

    // Send the response as JSON
    echo json_encode($response);
} catch (Exception $e) {
    // Handle errors
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
    ]);
}

// Close the database connection
mysqli_close($conn);

?>
