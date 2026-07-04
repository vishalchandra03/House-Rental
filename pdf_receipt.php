<?php
session_start();
require 'config.php';
require_once 'fpdf/fpdf.php';

/* Login check */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$booking_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

$sql = "
SELECT 
    b.id,
    b.checkin_date,
    b.checkout_date,
    b.status,
    b.total_days,
    b.total_amount,
    h.title,
    h.city,
    h.rent,
    u.name
FROM bookings b
JOIN houses h ON b.house_id = h.id
JOIN users u ON b.user_id = u.id
WHERE b.id = ? AND b.user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Receipt not found");
}

$row = $result->fetch_assoc();

$monthlyRent = (float)$row['rent'];
$perDayRent  = round($monthlyRent / 30, 2);

$checkinDate  = $row['checkin_date'] ?? 'N/A';
$checkoutDate = $row['checkout_date'] ?? 'N/A';

/* ---------- PDF START ---------- */
$pdf = new FPDF();
$pdf->AddPage();

/* HEADER BACKGROUND */
$pdf->SetFillColor(33,150,243); // Blue
$pdf->Rect(0,0,210,30,'F');

$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',20);
$pdf->SetY(10);
$pdf->Cell(0,10,'HouseRental Receipt',0,1,'C');

/* Reset */
$pdf->SetTextColor(0,0,0);
$pdf->SetY(40);

/* Info Box Background */
$pdf->SetFillColor(245,247,250);
$pdf->Rect(10,35,190,120,'F');

$pdf->SetDrawColor(220,220,220);
$pdf->Rect(10,35,190,120);

$pdf->SetFont('Arial','',12);
$pdf->SetX(20);

/* DETAILS */
function row($pdf, $label, $value){
    $pdf->SetX(20);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(55,10,$label,0,0);
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(0,10,$value,0,1);
}

row($pdf,'Receipt ID:','#'.$row['id']);
row($pdf,'Customer Name:',$row['name']);
row($pdf,'Property:',$row['title']);
row($pdf,'City:',$row['city']);
row($pdf,'Per Day Rent:','Rs '.number_format($perDayRent,2));
row($pdf,'Total Days:',$row['total_days']);

/* Highlight Total Amount */
$pdf->SetX(20);
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(0,128,0);
$pdf->Cell(55,10,'Total Paid:',0,0);
$pdf->Cell(0,10,'Rs '.number_format($row['total_amount'],2),0,1);

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',12);

row($pdf,'Check-in:',$checkinDate);
row($pdf,'Check-out:',$checkoutDate);
row($pdf,'Status:',ucfirst($row['status']));

/* Footer Line */
$pdf->SetY(-30);
$pdf->SetDrawColor(180,180,180);
$pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());

$pdf->SetFont('Arial','I',10);
$pdf->SetTextColor(120,120,120);
$pdf->Ln(5);
$pdf->Cell(0,10,'Thank you for choosing HouseRental!',0,1,'C');

$pdf->Output('D','receipt_'.$row['id'].'.pdf');
