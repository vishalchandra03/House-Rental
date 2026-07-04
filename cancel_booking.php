<?php
session_start();
include 'config.php';

/* 1️⃣ Login check */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

/* 2️⃣ Booking ID check */
if (!isset($_POST['booking_id'])) {
    header("Location: user_bookings.php");
    exit;
}
$booking_id = (int)$_POST['booking_id'];
$user_id    = $_SESSION['user_id'];

/* 3️⃣ Verify booking belongs to user & is pending */
$check = $conn->prepare("
    SELECT id 
    FROM bookings 
    WHERE id = ? AND user_id = ? AND status = 'pending'
");
$check->bind_param("ii", $booking_id, $user_id);
$check->execute();
$result = $check->get_result();

/* 4️⃣ If not found or not pending → go back */
if ($result->num_rows === 0) {
    header("Location: user_bookings.php");
    exit;
}
/* 5️⃣ Cancel booking */
$update = $conn->prepare("
    UPDATE bookings 
    SET status = 'cancelled' 
    WHERE id = ?
");
$update->bind_param("i", $booking_id);
$update->execute();
/* 6️⃣ Redirect back */
header("Location: user_bookings.php");
exit;
