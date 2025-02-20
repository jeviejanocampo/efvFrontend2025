<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow cross-origin requests
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include 'dbcon.php'; // Include database connection

if (!isset($_GET['user_id'])) {
    echo json_encode(['error' => 'Missing user_id']);
    exit();
}

$user_id = intval($_GET['user_id']);

// Query to get count and latest updated_at timestamp
$sql = "SELECT COUNT(*) AS count, MAX(updated_at) AS updated_at 
        FROM orders 
        WHERE user_id = ? AND status = 'Ready To Pickup'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'count' => $row['count'],
        'updated_at' => $row['updated_at'] ?? null
    ]);
} else {
    echo json_encode(['count' => 0, 'updated_at' => null]);
}

$stmt->close();
$conn->close();
?>
