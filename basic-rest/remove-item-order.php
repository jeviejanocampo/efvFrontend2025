<?php
    header('Content-Type: application/json');

    include 'dbcon.php'; // Database connection

    // Get the POST data
    $data = json_decode(file_get_contents('php://input'), true);

    // Retrieve model_id or variant_id from the request body
    $id = isset($data['model_id']) ? $data['model_id'] : (isset($data['variant_id']) ? $data['variant_id'] : null);

    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'ID (model_id or variant_id) is required.']);
        exit();
    }

    // Retrieve the order_id of the product based on model_id or variant_id
    $sql_get_order = "SELECT o.order_id 
                    FROM order_details od
                    INNER JOIN orders o ON od.order_id = o.order_id
                    WHERE od.model_id = ? OR od.variant_id = ? LIMIT 1"; 

    $stmt_get_order = $conn->prepare($sql_get_order);
    $stmt_get_order->bind_param("is", $id, $id); // Bind the ID parameter to the query
    $stmt_get_order->execute();
    $result = $stmt_get_order->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $order_id = $row['order_id'];

        // Step: Update product status to 'to be refunded' in order_details
        $sql_update_status = "UPDATE order_details SET product_status = 'to be refunded' WHERE model_id = ? OR variant_id = ?";
        $stmt_update_status = $conn->prepare($sql_update_status);
        $stmt_update_status->bind_param("is", $id, $id);
        $stmt_update_status->execute();

        echo json_encode(['status' => 'success', 'message' => 'Item status updated to "to be refunded".']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found']);
    }

    // Close statements and connection
    $stmt_get_order->close();
    $stmt_update_status->close();
    $conn->close();
?>
