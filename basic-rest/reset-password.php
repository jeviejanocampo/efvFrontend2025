<?php
header('Content-Type: application/json');

// Include database connection
include 'dbcon.php';

// Get POST data from the frontend
$data = json_decode(file_get_contents("php://input"));

// Validate required fields
if (empty($data->userId) || empty($data->newPassword)) {
    echo json_encode(["success" => false, "message" => "User ID and new password are required."]);
    exit;
}

// Sanitize the input data
$userId = $data->userId;
$newPassword = password_hash($data->newPassword, PASSWORD_BCRYPT); // Secure the password with bcrypt

// Update the user's password in the database
$sql = "UPDATE customers SET password = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $newPassword, $userId);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Password reset successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to reset password."]);
}

$stmt->close();
$conn->close();
?>
