<?php
// Set the content type to JSON
header('Content-Type: application/json');

// Include the database connection
include 'dbcon.php'; 

// Get the order_id from the query string
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;

// Check if order_id is provided
if ($order_id === null) {
    echo json_encode(['error' => 'Order ID is required']);
    exit;
}

// The base URL for the images
$image_base_url = 'http://192.168.1.32/efvFrontend2025/basic-rest/product-images/';

// Prepare the SQL query to fetch order details based on the order_id
$query = "
    SELECT 
        od.order_detail_id,
        od.order_id,
        od.model_id,
        od.product_name,
        od.brand_name,
        od.quantity,
        od.price,
        od.total_price,
        od.product_status,
        o.status AS order_status,
        o.updated_at,  -- Include the updated_at column for polling
        od.created_at,
        od.updated_at AS detail_updated_at,
        m.model_img  -- Fetch the model image based on model_id
    FROM 
        order_details od
    INNER JOIN 
        orders o ON od.order_id = o.order_id
    LEFT JOIN 
        models m ON od.model_id = m.model_id  -- Join with models table to fetch model_img
    WHERE 
        od.order_id = ?
";

// Use prepared statements to prevent SQL injection
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order_details = $result->fetch_all(MYSQLI_ASSOC);

// Add the full image URL to each order detail
foreach ($order_details as &$order_detail) {
    if (!empty($order_detail['model_img'])) {
        // Concatenate the base URL with the model_img path
        $order_detail['model_img_url'] = $image_base_url . $order_detail['model_img'];
    } else {
        $order_detail['model_img_url'] = null;  // No image available
    }
}

// Return the order details as JSON with the full image URLs
echo json_encode($order_details);

// Close the statement and connection
$stmt->close();
$conn->close();
?>
