<?php
// Set the content type to JSON
header('Content-Type: application/json');

// Include the database connection
include 'dbcon.php'; 
include 'ip-config.php';       

// Get the order_id from the query string
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;

// Check if order_id is provided
if ($order_id === null) {
    echo json_encode(['error' => 'Order ID is required']);
    exit;
}

// Prepare the SQL query to fetch order details based on the order_id
$query = "
SELECT 
    od.order_detail_id,
    od.order_id,
    od.model_id,
    od.variant_id,
    od.product_name,
    od.brand_name,
    od.quantity,
    od.price,
    od.total_price, 
    od.product_status,

    CASE 
        WHEN m.w_variant = 'YES' OR m.w_variant = 'yes' 
        THEN (SELECT v.part_id FROM variants v WHERE v.model_id = m.model_id LIMIT 1) 
        ELSE od.part_id 
    END AS part_id,

    o.total_price AS order_total_price,  
    o.status AS order_status,
    o.payment_method,  
    o.created_at,  
    o.updated_at,
    od.created_at,
    od.updated_at AS detail_updated_at,
    m.model_img,

    gp.status AS gcash_status,
    pp.status AS pnb_status

FROM 
    order_details od
INNER JOIN 
    orders o ON od.order_id = o.order_id
LEFT JOIN 
    models m ON od.model_id = m.model_id
LEFT JOIN 
    gcash_payment gp ON o.order_id = gp.order_id
LEFT JOIN 
    pnb_payment pp ON o.order_id = pp.order_id
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
        $order_detail['model_img_url'] = $baseImageUrl . $order_detail['model_img'];
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
