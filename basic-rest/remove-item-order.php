<?php
header('Content-Type: application/json');

include 'dbcon.php'; // Database connection

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

$model_id = $data['model_id']; // Retrieve model_id from the request body

if (empty($model_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Model ID is required.']);
    exit();
}

// SQL query to delete the item by model_id
$sql = "DELETE FROM order_details WHERE model_id = ?";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $model_id); // Bind the model_id parameter to the query

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Item removed successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to remove item']);
}

$stmt->close();
$conn->close();
?>
