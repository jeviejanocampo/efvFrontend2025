<?php
header('Content-Type: application/json');

// Include database connection file
include 'dbcon.php';

// Initialize response array
$response = [
    'variants' => [],
    'models' => [],
];

try {
    // Fetch all variants from the 'variants' table
    $variantQuery = "SELECT variant_id, model_id, product_name, variant_image, part_id, price, specification, description, stocks_quantity 
                     FROM variants";
    $variantResult = mysqli_query($conn, $variantQuery);

    if (!$variantResult) {
        throw new Exception('Error fetching variants: ' . mysqli_error($conn));
    }

    while ($variant = mysqli_fetch_assoc($variantResult)) {
        // Append the full image URL for the variant image
        $variant['variant_image'] = "http://192.168.1.32/efvFrontend2025/basic-rest/product-images/" . $variant['variant_image'];
        $response['variants'][] = $variant;
    }

    // Fetch all models from the 'models' table, joining with the 'brands' table to get the brand name
    $modelQuery = "SELECT models.model_id, models.model_name, models.model_img, models.price, models.brand_id, models.w_variant, models.status, brands.brand_name
                   FROM models
                   LEFT JOIN brands ON models.brand_id = brands.brand_id";  // Join with 'brands' table
    $modelResult = mysqli_query($conn, $modelQuery);

    if (!$modelResult) {
        throw new Exception('Error fetching models: ' . mysqli_error($conn));
    }

    while ($model = mysqli_fetch_assoc($modelResult)) {
        // Append the full image URL for the model image
        $model['model_img'] = "http://192.168.1.32/efvFrontend2025/basic-rest/product-images/" . $model['model_img'];
        
        // Optionally, you can include the brand name in the model data if you want
        $model['brand_name'] = $model['brand_name'];

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
