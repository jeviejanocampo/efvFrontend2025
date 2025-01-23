<?php
header('Content-Type: application/json');

include 'dbcon.php'; // Database connection

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the raw POST data
    $data = json_decode(file_get_contents('php://input'), true);

    // Ensure data is valid
    if (isset($data['cart_id']) && isset($data['quantity'])) {
        $cart_id = (int)$data['cart_id'];
        $quantity = (int)$data['quantity'];

        // Validate that the quantity is greater than 0
        if ($quantity < 1) {
            echo json_encode(['success' => false, 'message' => 'Quantity must be at least 1']);
            exit;
        }

        // Prepare the SQL query to update the quantity
        $query = "UPDATE cart SET quantity = ? WHERE cart_id = ?";

        // Use prepared statements to avoid SQL injection
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param('ii', $quantity, $cart_id); // Bind the parameters

            // Execute the statement
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Quantity updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
            }

            // Close the statement
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: Unable to prepare statement']);
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
