<?php
header('Content-Type: application/json');

include 'dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validate required fields
    if (
        empty($data['full_name']) ||
        empty($data['email']) ||
        empty($data['phone_number']) ||
        empty($data['address']) ||
        empty($data['city']) ||
        empty($data['password'])
    ) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
        exit;
    }

    // Sanitize input data
    $full_name = $conn->real_escape_string($data['full_name']);
    $email = $conn->real_escape_string($data['email']);
    $phone_number = $conn->real_escape_string($data['phone_number']);
    $second_phone_number = isset($data['second_phone_number']) ? $conn->real_escape_string($data['second_phone_number']) : null;
    $address = $conn->real_escape_string($data['address']);
    $city = $conn->real_escape_string($data['city']);
    $password = password_hash($conn->real_escape_string($data['password']), PASSWORD_BCRYPT); // Hash password
    $status = 'active';

    // Check if email already exists
    $checkEmailQuery = "SELECT id FROM customers WHERE email = '$email'";
    $result = $conn->query($checkEmailQuery);

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }

    // Insert data into customers table
    $query = "INSERT INTO customers (full_name, email, phone_number, second_phone_number, address, city, password, status)
              VALUES ('$full_name', '$email', '$phone_number', '$second_phone_number', '$address', '$city', '$password', '$status')";

    if ($conn->query($query)) {
        echo json_encode(['success' => true, 'message' => 'User registered successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
