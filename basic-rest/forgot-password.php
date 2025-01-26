<?php
header('Content-Type: application/json');

// Include database connection
include 'dbcon.php';

// Get POST data from the frontend
$data = json_decode(file_get_contents("php://input"));

// Validate if the email is provided
if (empty($data->email)) {
    echo json_encode(["success" => false, "message" => "Email is required."]);
    exit;
}

$email = $data->email;

// Query the database to find the user by email
$sql = "SELECT * FROM customers WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows > 0) {
    // User found, return the user data (user ID) for password reset
    $user = $result->fetch_assoc();
    echo json_encode([
        "success" => true,
        "message" => "Email found. You can now reset your password.",
        "data" => ["id" => $user['id'], "full_name" => $user['full_name']]
    ]);
} else {
    // User not found
    echo json_encode(["success" => false, "message" => "Email not found."]);
}

$stmt->close();
$conn->close();
?>
