<?php
header('Content-Type: application/json');

// Include database connection
include 'dbcon.php';

// Get user_id and order_id from the URL parameters
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;

// Check if both user_id and order_id are provided
if (!$user_id || !$order_id) {
    echo json_encode(['error' => 'user_id and order_id are required']);
    exit();
}

// Prepare SQL query to fetch all columns from refund_order, reference_id from order_reference,
// processed_by user info (name, role) from users table, and original_total_amount from orders table
$sql = "SELECT 
            ro.*,  -- Select all columns from refund_order
            orf.reference_id,  -- Select reference_id from order_reference
            u.name AS processed_by_name,  -- Fetch processed_by user name from users table
            u.role AS processed_by_role,  -- Fetch processed_by user role from users table
            o.original_total_amount, -- Fetch original_total_amount from orders table
            o.total_price 
        FROM 
            refund_order ro
        LEFT JOIN 
            order_reference orf ON ro.order_id = orf.order_id
        LEFT JOIN 
            users u ON ro.processed_by = u.id  -- Join with users table based on processed_by
        LEFT JOIN
            orders o ON ro.order_id = o.order_id  -- Join with orders table to get original_total_amount
        WHERE 
            ro.user_id = ? AND ro.order_id = ?";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind the parameters
$stmt->bind_param("ii", $user_id, $order_id);

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Check if any refund details are found
if ($result->num_rows > 0) {
    $refund_details = [];

    // Fetch each row and add to the refund_details array
    while ($row = $result->fetch_assoc()) {
        $refund_details[] = $row;
    }

    // Return the refund details as JSON
    echo json_encode($refund_details);
} else {
    // No refund details found
    echo json_encode([]);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
