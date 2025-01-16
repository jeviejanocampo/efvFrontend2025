<?php
header('Content-Type: application/json');

include 'dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validate required fields
    if (empty($data['email']) || empty($data['password'])) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit;
    }

    // Sanitize input data
    $email = $conn->real_escape_string($data['email']);
    $password = $data['password'];

    // Check if email exists
    $query = "SELECT id, password FROM customers WHERE email = '$email'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            echo json_encode(['success' => true, 'message' => 'Login successful', 'customer_id' => $user['id']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid password']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Email not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
