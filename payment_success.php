<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_POST['booking_id'], $_POST['amount'])) {
    header("Location: user_bookings.php");
    exit;
}

$booking_id = (int) $_POST['booking_id'];
$amount     = (int) $_POST['amount'];
$user_id    = $_SESSION['user_id'];

/* Verify booking */
$check = $conn->prepare("
    SELECT id 
    FROM bookings 
    WHERE id = ? AND user_id = ? AND payment_status = 'unpaid'
");
$check->bind_param("ii", $booking_id, $user_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows === 0) {
    header("Location: user_bookings.php");
    exit;
}

/* Update payment */
$update = $conn->prepare("
    UPDATE bookings
    SET 
        payment_status = 'paid',
        payment_method = 'UPI',
        payment_amount = ?,
        payment_date   = NOW()
    WHERE id = ?
");
$update->bind_param("ii", $amount, $booking_id);
$update->execute();

$txn_id = 'TXN_' . time();

$insert = $conn->prepare("
  INSERT INTO payments
  (booking_id, user_id, amount, payment_method, transaction_id, payment_status, paid_at)
  VALUES (?, ?, ?, ?, ?, 'success', NOW())
");

if (!$insert) {
    echo "PREPARE ERROR: " . $conn->error;
    exit;
}

$method = 'UPI';

$insert->bind_param(
    "iiiss",
    $booking_id,
    $user_id,
    $amount,
    $method,
    $txn_id
);

if (!$insert->execute()) {
    echo "EXECUTE ERROR: " . $insert->error;
    exit;
}

/* ✅ REDIRECT TO BOOKING SUCCESS */
header("Location: booking_success.php?id=".$booking_id);
exit;
// payment successful hone ke baad
$booking_id = $booking_id;   // jo booking abhi hui
$user_id    = $_SESSION['user_id'];
$amount     = $total_amount;
$method     = 'UPI';         // ya Card / Razorpay
$txn_id     = 'TXN_' . time(); // demo txn id

mysqli_query($conn, "
  INSERT INTO payments
  (booking_id, user_id, amount, payment_method, transaction_id, payment_status, paid_at)
  VALUES
  ('$booking_id', '$user_id', '$amount', '$method', '$txn_id', 'success', NOW())
");
if (!$insert->execute()) {
    echo "INSERT ERROR: " . $insert->error;
    exit;
}header("Location: booking_success.php?id=".$booking_id);
exit;