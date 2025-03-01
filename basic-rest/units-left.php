<?php
header('Content-Type: application/json');
include 'dbcon.php';

$response = ["success" => false, "variants" => [], "products" => []];

try {
    // Fetch all stock quantities from 'variants' table
    $variantsQuery = "SELECT variant_id, stocks_quantity FROM variants";
    $variantsResult = $conn->query($variantsQuery);
    while ($row = $variantsResult->fetch_assoc()) {
        $response["variants"][] = $row;
    }

    // Fetch all stock quantities from 'products' table
    $productsQuery = "SELECT model_id, stocks_quantity FROM products";
    $productsResult = $conn->query($productsQuery);
    while ($row = $productsResult->fetch_assoc()) {
        $response["products"][] = $row;
    }

    $response["success"] = true;
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = "Database error: " . $e->getMessage();
}

echo json_encode($response);
?>
