<?php
header('Content-Type: application/json');
include 'dbcon.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
        exit;
    }

    $customer_id = intval($_POST['id']); // Get and sanitize customer ID

    if (!isset($_FILES['profile_pic'])) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded']);
        exit;
    }

    $file = $_FILES['profile_pic'];
    $fileName = basename($file['name']);
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExt, $allowedExtensions)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type']);
        exit;
    }

    if ($fileError !== 0) {
        echo json_encode(['success' => false, 'message' => 'Error uploading file']);
        exit;
    }

    if ($fileSize > 5 * 1024 * 1024) { // 5MB limit
        echo json_encode(['success' => false, 'message' => 'File size exceeds limit (5MB)']);
        exit;
    }

    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $newFileName = 'profile_' . $customer_id . '_' . time() . '.' . $fileExt;
    $uploadPath = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmpName, $uploadPath)) {
        // Update profile_pic in customers table
        $stmt = $conn->prepare("UPDATE customers SET profile_pic = ? WHERE id = ?");
        $stmt->bind_param("si", $newFileName, $customer_id);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Profile picture updated successfully';
            $response['profile_pic'] = 'https://midnightblue-rail-125415.hostingersite.com/public/uploads/' . $newFileName; // Adjust URL as needed
        } else {
            $response['message'] = 'Failed to update database';
        }
        $stmt->close();
    } else {
        $response['message'] = 'Failed to move uploaded file';
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
