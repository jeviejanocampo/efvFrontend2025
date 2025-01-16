<?php
header('Content-Type: application/json');

include 'dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validate required fields
    if (empty($data['customer_id'])) {
        echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
        exit;
    }

    // Sanitize input data
    $customer_id = $conn->real_escape_string($data['customer_id']);

    // Fetch the full name for the given customer ID
    $query = "SELECT full_name FROM customers WHERE id = '$customer_id'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode(['success' => true, 'full_name' => $user['full_name']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Customer not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
