<?php
session_start();
require 'config.php';

/* 1️⃣ Login check */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

/* 2️⃣ Booking ID check */
if (!isset($_GET['id'])) {
    die("Invalid request");
}

$booking_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

/* 3️⃣ SQL QUERY */
$sql = "
SELECT 
    b.id,
    b.status,
    b.checkin_date,
    b.checkout_date,
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

/* 👇 FIX — calculation yaha hona chahiye */
$monthlyRent = (float)$row['rent'];
$perDayRent  = round($monthlyRent / 30, 2);

/* 4️⃣ Dates assign */
$checkinDate  = !empty($row['checkin_date']) ? $row['checkin_date'] : 'N/A';
$checkoutDate = !empty($row['checkout_date']) ? $row['checkout_date'] : 'N/A';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Booking Receipt</title>
    <style>
        body { font-family: Arial; background:#f4f6f8; }
        .receipt {
            width: 420px;
            background:#fff;
            padding: 20px;
            margin: 40px auto;
            border-radius: 8px;
        }
        h2 { margin-bottom: 10px; }
        .btn {
            display:inline-block;
            margin-top:15px;
            padding:10px 16px;
            background:#6c757d;
            color:#fff;
            text-decoration:none;
            border-radius:5px;
        }
    </style>
</head>
<body>
<div class="receipt">
    <h2>Booking Receipt</h2>
    <hr>
    <p><strong>Receipt ID:</strong> #<?= $row['id'] ?></p>
    <p><strong>Name:</strong> <?= htmlspecialchars($row['name']) ?></p>
    <p><strong>Property:</strong> <?= htmlspecialchars($row['title']) ?></p>
    <p><strong>City:</strong> <?= htmlspecialchars($row['city']) ?></p>

    <p><strong>Per Day Rent:</strong> ₹ <?= number_format($perDayRent,2) ?></p>
    <p><strong>Total Days:</strong> <?= $row['total_days'] ?></p>
    <p><strong>Total Amount Paid:</strong> ₹ <?= number_format($row['total_amount'],2) ?></p>

    <p><strong>Check-in Date:</strong> <?= $checkinDate ?></p>
    <p><strong>Check-out Date:</strong> <?= $checkoutDate ?></p>
    <p><strong>Status:</strong> <?= ucfirst($row['status']) ?></p>

    <a class="btn" href="user_bookings.php">Back to My Bookings</a>
</div>
</body>
</html>
