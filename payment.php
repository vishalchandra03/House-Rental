<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$booking_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

/* Fetch booking */
$q = $conn->prepare("
    SELECT b.*, h.title
    FROM bookings b
    JOIN houses h ON b.house_id = h.id
    WHERE b.id = ? AND b.user_id = ? AND b.payment_status = 'unpaid'
");

$q->bind_param("ii", $booking_id, $user_id);
$q->execute();
$r = $q->get_result();

if ($r->num_rows === 0) {
    die("Payment not allowed");
}

$booking = $r->fetch_assoc();
?>
<!-- <!DOCTYPE html>
<html>
<head>
    <title>Make Payment</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div style="max-width:420px;margin:80px auto;
            background:#fff;padding:25px;
            border-radius:10px">

    <h2>Payment</h2>
    <p><strong>Property:</strong> <?= htmlspecialchars($booking['title']) ?></p>
    <p><strong>Amount:</strong> ₹<?= number_format($booking['rent']) ?></p>

    <form method="POST" action="payment_success.php">
        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
        <input type="hidden" name="amount" value="<?= $booking['rent'] ?>">

        <button type="submit"
                style="width:100%;padding:12px;
                       background:#28a745;color:#fff;
                       border:none;border-radius:6px;
                       font-size:16px;">
            Pay ₹<?= number_format($booking['rent']) ?>
        </button>
    </form>

</div>

</body>
</html> -->
<!DOCTYPE html>
<html>
<head>
    <title>Make Payment</title>
    <!-- <link rel="stylesheet" href="assets/css/style.css"> -->
    <style>
        body{
            margin:0;
            background:#eef2f6;
            font-family:system-ui,-apple-system,BlinkMacSystemFont;
        }
        .upi-page{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .upi-card{
            width:420px;
            background:#fff;
            border-radius:18px;
            padding:26px;
            box-shadow:0 20px 45px rgba(0,0,0,.15);
        }
        .upi-header{
            text-align:center;
            margin-bottom:18px;
        }
        .upi-header h2{
            margin:6px 0;
        }
        .upi-header p{
            color:#666;
            font-size:14px;
        }
        .upi-amount{
            text-align:center;
            margin:22px 0;
        }
        .upi-amount span{
            font-size:13px;
            color:#777;
        }
        .upi-amount h1{
            margin:6px 0;
            font-size:38px;
        }
        .upi-details{
            background:#f6f8fb;
            padding:14px;
            border-radius:12px;
            margin-bottom:20px;
            font-size:14px;
        }
        .upi-details div{
            display:flex;
            justify-content:space-between;
            margin-bottom:6px;
        }
        .pay-btn{
            width:100%;
            padding:14px;
            background:#2ea44f;
            color:#fff;
            border:none;
            border-radius:12px;
            font-size:16px;
            font-weight:600;
            cursor:pointer;
        }
        .pay-btn:hover{
            background:#23863d;
        }
        .note{
            text-align:center;
            font-size:12px;
            color:#999;
            margin-top:14px;
        }
    </style>
</head>
<body>

<div class="upi-page">
  <div class="upi-card">

    <div class="upi-header">
      <h2>UPI Payment</h2>
      <p>Secure payment for your booking</p>
    </div>

    <div class="upi-amount">
      <span>Amount Payable</span>
      <h1>₹<?= number_format($booking['total_amount']) ?>
</h1>
    </div>

    <div class="upi-details">
      <div>
        <span>Property</span>
        <b><?= htmlspecialchars($booking['title']) ?></b>
      </div>
      <div>
        <span>Status</span>
        <b>Pending Payment</b>
      </div>
    </div>

    <form method="POST" action="payment_success.php">
        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
        <input type="hidden" name="amount" value="<?= $booking['total_amount'] ?>">


        <button type="submit" class="pay-btn">
            Pay ₹<?= number_format($booking['total_amount']) ?>

        </button>
    </form>

    <div class="note">
      Demo-style UI • No real UPI integration
    </div>

  </div>
</div>

</body>
</html>
