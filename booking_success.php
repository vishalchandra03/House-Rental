<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$booking_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

$sql = "
SELECT 
  b.id,
  b.checkin_date,
  b.checkout_date,
  b.payment_status,
  h.title,
  h.city
FROM bookings b
JOIN houses h ON b.house_id = h.id
WHERE b.id = ? AND b.user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "Invalid booking.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking Successful</title>
    <style>
        /* body{
            font-family: Arial, sans-serif;
            background:#f4f7fb;
        }
        .box{
            max-width:500px;
            margin:80px auto;
            background:#fff;
            padding:30px;
            border-radius:12px;
            text-align:center;
            box-shadow:0 10px 30px rgba(0,0,0,.1);
        }
        .success{
            font-size:22px;
            color:#198754;
            font-weight:bold;
            margin-bottom:15px;
        }
        .btn{
            display:inline-block;
            margin:10px;
            padding:10px 18px;
            border-radius:6px;
            text-decoration:none;
            color:#fff;
            font-size:14px;
        }
        .primary{background:#0d6efd;}
        .success-btn{background:#198754;} */
        body{
    margin:0;
    background:#f2f5f9;
    font-family:system-ui,-apple-system,BlinkMacSystemFont;
}

.success-page{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
}

.success-card{
    width:520px;
    background:#fff;
    border-radius:18px;
    padding:34px;
    text-align:center;
    box-shadow:0 25px 50px rgba(0,0,0,0.12);
}

.check-icon{
    width:64px;
    height:64px;
    border-radius:50%;
    background:#28a745;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:32px;
    margin:0 auto 14px;
}

h1{
    margin:8px 0;
    color:#28a745;
}

.property-title{
    font-size:18px;
    font-weight:600;
    margin-top:10px;
}

.property-city{
    font-size:14px;
    color:#666;
    margin-top:4px;
}

.date-box{
    margin:20px 0;
    background:#f6f8fb;
    padding:14px;
    border-radius:12px;
    font-size:15px;
    font-weight:500;
}

.btn-group{
    display:flex;
    justify-content:center;
    gap:14px;
    margin-top:22px;
}

.btn{
    padding:12px 22px;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
    font-size:15px;
    display:inline-block;
}

.btn.primary{
    background:#0d6efd;
    color:#fff;
}

.btn.success-btn{
    background:#198754;
    color:#fff;
}

.btn:hover{
    opacity:.9;
}

.thank-note{
    font-size:12px;
    color:#888;
    margin-top:20px;
}

    </style>
</head>
<!-- <body>

<div class="box">
    <div class="success">✅ Booking Successful!</div>

    <p><b><?= htmlspecialchars($data['title']) ?></b></p>
    <p><?= htmlspecialchars($data['city']) ?></p>
    <p>
        <?= $data['checkin_date'] ?> → <?= $data['checkout_date'] ?>
    </p>

    <a class="btn primary" href="user_bookings.php">My Bookings</a>
    <a class="btn success-btn" href="pdf_receipt.php?id=<?= $booking_id ?>">Download Receipt</a>
</div>

</body>
</html> -->
<body>

<div class="success-page">
  <div class="success-card">

    <div class="check-icon">✓</div>

    <h1>Booking Confirmed</h1>

    <div class="property-title">
        <?= htmlspecialchars($data['title']) ?>
    </div>

    <div class="property-city">
        <?= htmlspecialchars($data['city']) ?>
    </div>

    <div class="date-box">
        <?= $data['checkin_date'] ?>
        &nbsp; → &nbsp;
        <?= $data['checkout_date'] ?>
    </div>

    <div class="btn-group">
        <a class="btn primary" href="user_bookings.php">My Bookings</a>
        <a class="btn success-btn" href="pdf_receipt.php?id=<?= $booking_id ?>">
            Download Receipt
        </a>
    </div>

    <div class="thank-note">
        Thank you for booking with us
    </div>

  </div>
</div>

</body>
