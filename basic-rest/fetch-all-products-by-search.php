<?php
header('Content-Type: application/json');

// Include database connection file
include 'dbcon.php';

try {
    // Base URL for product images
    $imageBaseUrl = 'http://192.168.1.32/efvFrontend2025/basic-rest/product-images/';

    // Fetch variants
    $variantsQuery = "SELECT 
                        variant_id, 
                        model_id, 
                        product_name, 
                        variant_image AS image_url, 
                        part_id, 
                        price, 
                        specification, 
                        description, 
                        stocks_quantity, 
                        created_at, 
                        updated_at 
                      FROM variants 
                      WHERE price > 0";
    $variantsResult = mysqli_query($conn, $variantsQuery);

    $variants = [];
    if ($variantsResult) {
        while ($row = mysqli_fetch_assoc($variantsResult)) {
            // Ensure image URL is complete
            $row['image_url'] = $imageBaseUrl . $row['image_url'];
            $variants[] = $row;
        }
    }

    // Fetch models
    $modelsQuery = "SELECT 
                      model_id, 
                      model_name, 
                      model_img, 
                      price, 
                      brand_id, 
                      w_variant, 
                      status, 
                      created_at, 
                      updated_at 
                    FROM models 
                    WHERE price > 0";
    $modelsResult = mysqli_query($conn, $modelsQuery);

    $models = [];
    if ($modelsResult) {
        while ($row = mysqli_fetch_assoc($modelsResult)) {
            // Prepend the base URL to the model image
            $row['image_url'] = $imageBaseUrl . $row['model_img'];
            unset($row['model_img']); // Remove original field if unnecessary
            $models[] = $row;
        }
    }

    // Combine data and return as JSON
    echo json_encode([
        'variants' => $variants,
        'models' => $models
    ]);

} catch (Exception $e) {
    // Return error message in case of failure
    http_response_code(500);
    echo json_encode(['message' => 'Failed to fetch products', 'error' => $e->getMessage()]);
}

// Close the database connection
mysqli_close($conn);
?>
