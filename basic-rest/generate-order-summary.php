<?php
ob_start();

require('libs/fpdf/fpdf.php');
include 'dbcon.php';
include 'ip-config.php';

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;
if (!$order_id) {
    die('Order ID is required');
}

// Fetch main order data
$query = "
SELECT 
    od.product_name,
    od.brand_name,
    od.quantity,
    od.price,
    od.total_price,
    od.product_status,
    o.order_id,
    o.total_price AS order_total_price,
    o.status AS order_status,
    o.payment_method,
    o.created_at
FROM order_details od
JOIN orders o ON od.order_id = o.order_id
WHERE od.order_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

if (count($data) === 0) {
    die('No order found.');
}

$order = $data[0];

// Fetch reference_id from order_reference table
$refQuery = "SELECT reference_id FROM order_reference WHERE order_id = ?";
$refStmt = $conn->prepare($refQuery);
$refStmt->bind_param("i", $order_id);
$refStmt->execute();
$refResult = $refStmt->get_result();
$refData = $refResult->fetch_assoc();
$reference_id = $refData['reference_id'] ?? 'N/A';

$pdf = new FPDF();
$pdf->AddPage();

// Logo
$pdf->Image('https://midnightblue-rail-125415.hostingersite.com/public/product-images/EFVLOGOREPORT.jpg', 10, 10, 30);

// Header text
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'EFV Auto Parts - Order Summary', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'Sibulan Diversion Road, Dumaguete City, Negros Oriental', 0, 1, 'C');
$pdf->Cell(0, 5, 'TEL # 035 227808  |  CELL # 09271445539  |  EMAIL: imh.ksa@gmail.com', 0, 1, 'C');
$pdf->Ln(10);

// Order Info
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 8, 'Reference ID: ' . $reference_id, 0, 1); // ✅ Changed label
$pdf->Cell(0, 8, 'Status: ' . $order['order_status'], 0, 1);
$pdf->Cell(0, 8, 'Payment Method: ' . $order['payment_method'], 0, 1);
$pdf->Cell(0, 8, 'Created At: ' . date("F j, Y, g:i A", strtotime($order['created_at'])), 0, 1);
$pdf->Ln(5);

// Top border line before body


// Table Header (borders)
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(60, 8, 'Product', 1);
$pdf->Cell(40, 8, 'Brand', 1);
$pdf->Cell(20, 8, 'Qty', 1);
$pdf->Cell(30, 8, 'Price', 1);
$pdf->Cell(40, 8, 'Total', 1);
$pdf->Ln();

// Table Body - no borders
$pdf->SetFont('Arial', '', 9);
$subtotal = 0;

foreach ($data as $item) {
    $status = strtolower($item['product_status']);
    if (in_array($status, ['completed', 'refunded'])) {
        $pdf->Cell(60, 8, $item['product_name'], 0);
        $pdf->Cell(40, 8, $item['brand_name'], 0);
        $pdf->Cell(20, 8, $item['quantity'], 0);
        $pdf->Cell(30, 8, 'Php ' . number_format($item['price'], 2), 0);
        $pdf->Cell(40, 8, 'Php ' . number_format($item['total_price'], 2), 0);
        $pdf->Ln();

        $subtotal += $item['total_price'];

        if ($status === 'refunded') {
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->Cell(190, 6, 'Status: Refunded', 0, 1);
            $pdf->SetFont('Arial', '', 9);
        }
    }
}

$pdf->Cell(190, 0, '', 'T');
$pdf->Ln(2);
// Totals
$pdf->Ln(3);
$pdf->SetFont('Arial', '', 9, 'T');
$pdf->Cell(150, 8, 'Subtotal:', 0);
$pdf->Cell(40, 8, 'Php ' . number_format($order['order_total_price'], 2), 0);
$pdf->Ln(5);

// VAT
$vat = $subtotal * 0.12;
$vatable = $subtotal - $vat;

$pdf->Cell(150, 8, 'VATable Sales:', 0);
$pdf->Cell(40, 8, 'Php ' . number_format($vatable, 2), 0);
$pdf->Ln();
$pdf->Cell(150, 8, 'VAT (12%):', 0);
$pdf->Cell(40, 8, 'Php ' . number_format($vat, 2), 0);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(150, 8, 'Total:', 0);
$pdf->Cell(40, 8, 'Php ' . number_format($subtotal, 2), 0);
$pdf->Ln(12);

// Footer
$pdf->SetFont('Arial', 'I', 9);
$pdf->Cell(0, 10, 'Thank you for your purchase!', 0, 1, 'C');
$pdf->Cell(0, 10, 'Operated by EFV Auto Parts Management System', 0, 1, 'C');
$pdf->Cell(0, 10, '2025 All Rights Reserved', 0, 1, 'C');

header('Content-Type: application/pdf');
header("Content-Disposition: inline; filename=Order_Summary_{$order_id}.pdf");
ob_end_clean();
$pdf->Output('I', "Order_Summary_{$order_id}.pdf");
exit;
