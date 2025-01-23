<?php
header('Content-Type: application/json');

include 'dbcon.php'; // Include your database connection file

// Function to fetch variants for a given model_id
function getVariants($conn, $model_id) {
    // Fetch active variants based on the model_id
    $variantsQuery = "SELECT variant_id, model_id, product_name, variant_image, part_id, price, stocks_quantity, specification, description
                      FROM variants
                      WHERE model_id = ?";

    $stmt = $conn->prepare($variantsQuery);
    $stmt->bind_param("i", $model_id); // Bind the model_id parameter
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if variants are found
    $variants = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Append the base URL for the variant image path
            $row['variant_image'] = 'http://192.168.1.2/efvFrontend2025/basic-rest/product-images/' . $row['variant_image'];
            $variants[] = $row;
        }
    }

    return $variants;
}

// Get the model_id from the request
$model_id = isset($_GET['model_id']) ? $_GET['model_id'] : 0;

if ($model_id > 0) {
    $variants = getVariants($conn, $model_id);
    echo json_encode($variants); // Return variants as JSON
} else {
    echo json_encode(['error' => 'Model ID is missing or invalid']);
}
?>
