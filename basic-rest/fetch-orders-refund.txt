<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

include 'dbcon.php';

// Get data from POST body
$data = json_decode(file_get_contents('php://input'), true);

// Check if required parameters are provided
if (!isset($data['user_id']) || !isset($data['order_id']) || !isset($data['refund_reason'])) {
    echo json_encode(["error" => "Missing required parameters."]);
    exit();
}

$user_id = $data['user_id'];
$order_id = $data['order_id'];
$refund_reason = $data['refund_reason'];
$extra_details = isset($data['extra_details']) ? $data['extra_details'] : null;

// Start a transaction to ensure atomicity
$conn->begin_transaction();

try {
    // Check if a refund request already exists for this order_id
    $query = "SELECT * FROM refund_order WHERE order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Refund request already exists for this order_id
        echo json_encode(['status' => 'error', 'message' => 'Refund request already exists for this order.']);
        $stmt->close();
        $conn->rollback();  // Rollback transaction to prevent any insert
        $conn->close();
        exit();
    }

    // If refund request doesn't exist, proceed to insert the new request
    $insertQuery = "INSERT INTO refund_order (user_id, order_id, refund_reason, extra_details) 
                    VALUES (?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($insertQuery);
    $stmtInsert->bind_param('iiss', $user_id, $order_id, $refund_reason, $extra_details);

    if ($stmtInsert->execute()) {
        // Successfully inserted refund request
        echo json_encode(['status' => 'success', 'message' => 'Refund request submitted successfully.']);
    } else {
        // Error occurred during insertion
        echo json_encode(['status' => 'error', 'message' => 'Failed to submit refund request.']);
    }

    // Commit the transaction if everything is successful
    $conn->commit();

    $stmtInsert->close();
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // If any exception occurs, rollback the transaction
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Failed to submit refund request: ' . $e->getMessage()]);
    $conn->close();
}
?>
