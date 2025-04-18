<?php
header('Content-Type: application/json');
include 'dbcon.php';
include 'ip-config.php'; 

if (isset($_GET['customer_id'])) {
    $customerId = $_GET['customer_id'];

    $stmt = $conn->prepare("SELECT full_name, profile_pic FROM customers WHERE id = ?");
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();

        $profilePic = $customer['profile_pic'] ? $baseApiUrl . $customer['profile_pic'] : null;

        echo json_encode([
            'full_name' => $customer['full_name'],
            'profile_pic' => $profilePic
        ]);
    } else {
        echo json_encode([
            'error' => 'Customer not found'
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        'error' => 'Missing customer_id'
    ]);
}

$conn->close();
