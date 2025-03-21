<?php
header('Content-Type: application/json');
include 'dbcon.php';

$customerId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($customerId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

$stmt = $conn->prepare("SELECT profile_pic FROM customers WHERE id = ?");
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();
echo json_encode([
    'success' => (bool) $row,
    'profile_pic' => $row['profile_pic'] ?? null
]);

$stmt->close();
$conn->close();
?>
