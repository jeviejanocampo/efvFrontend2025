<?php
header('Content-Type: application/json');

include 'dbcon.php'; // Database connection

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the raw POST data
    $data = json_decode(file_get_contents('php://input'), true);

    // Ensure that cart_id is set in the request
    if (isset($data['cart_id'])) {
        $cart_id = (int)$data['cart_id']; // Cast cart_id to integer for security

        // Prepare the SQL query to delete the cart item
        $query = "DELETE FROM cart WHERE cart_id = ?";

        // Use prepared statements to prevent SQL injection
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param('i', $cart_id); // Bind the cart_id to the query

            // Execute the query
            if ($stmt->execute()) {
                // Check if any rows were affected (i.e., item was found and deleted)
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Item successfully deleted from the cart']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Item not found or already deleted']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete cart item']);
            }

            // Close the statement
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error: Unable to prepare statement']);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'cart_id is required']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

// Close the database connection
$conn->close();
?>
