<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "
SELECT 
  b.id,
  b.status,
  b.payment_status,
  b.checkin_date,
  b.checkout_date,
  h.title,
  h.city
FROM bookings b
JOIN houses h ON b.house_id = h.id
WHERE b.user_id = ?
ORDER BY b.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Bookings</title>
    <!-- <link rel="stylesheet" href="assets/css/style.css"> -->

    <style>
/* ===== GLOBAL RESET ===== */
* {
    box-sizing: border-box;
    font-family: "Segoe UI", Arial, sans-serif;
}

/* ===== BODY ===== */
body {
    margin: 0;
    background: radial-gradient(circle at top, #0f172a, #020617);
    color: #f8fafc;
}

/* ===== TOPBAR ===== */
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 40px;
    background: linear-gradient(135deg, #2563eb, #38bdf8);
    box-shadow: 0 10px 30px rgba(0,0,0,0.4);
}

.topbar .logo {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
}

.topbar .top-links a {
    color: #fff;
    text-decoration: none;
    margin-left: 20px;
    font-weight: 500;
}

/* ===== PAGE CONTAINER ===== */
.container {
    max-width: 1200px;
    margin: 50px auto;
    padding: 0 20px;
}

.container h1 {
    text-align: center;
    margin-bottom: 40px;
    font-size: 34px;
    color: #ffffff;
}

/* ===== GRID ===== */
.bookings-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 30px;
}

/* ===== BOOKING CARD ===== */
.booking-card {
    background: rgba(30,41,59,0.85);
    backdrop-filter: blur(14px);
    border-radius: 22px;
    padding: 26px 28px;
    box-shadow: 0 30px 60px rgba(0,0,0,0.6);
    transition: 0.3s ease;
}

.booking-card:hover {
    transform: translateY(-6px);
}

/* ===== TEXT ===== */
.booking-card h3 {
    margin: 0 0 8px;
    font-size: 20px;
    color: #ffffff;
}

.booking-card .city {
    color: #cbd5f5;
    margin-bottom: 14px;
}

.booking-card p {
    margin: 6px 0;
    color: #e5e7eb;
}

.booking-card strong {
    color: #ffffff;
}

/* ===== BADGES ===== */
.badges {
    margin: 16px 0;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.badge {
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
}

/* STATUS */
.status.pending {
    background: rgba(234,179,8,0.2);
    color: #fde047;
    border: 1px solid #fde047;
}

.status.approved {
    background: rgba(34,197,94,0.2);
    color: #86efac;
    border: 1px solid #86efac;
}

.status.cancelled {
    background: rgba(239,68,68,0.2);
    color: #fca5a5;
    border: 1px solid #fca5a5;
}

/* PAYMENT */
.badge.paid {
    background: rgba(34,197,94,0.2);
    color: #86efac;
    border: 1px solid #86efac;
}

.badge.unpaid {
    background: rgba(239,68,68,0.2);
    color: #fca5a5;
    border: 1px solid #fca5a5;
}

/* ===== ACTIONS ===== */
.actions {
    margin-top: 18px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

/* BUTTONS */
.btn {
    padding: 10px 16px;
    border-radius: 12px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: 0.25s;
}

/* BUTTON COLORS */
.btn-danger {
    background: #ef4444;
    color: #fff;
}

.btn-danger:hover {
    background: #dc2626;
}

.btn-warning {
    background: #facc15;
    color: #111827;
}

.btn-warning:hover {
    background: #eab308;
}

.btn-success {
    background: #22c55e;
    color: #052e16;
}

.btn-success:hover {
    background: #16a34a;
}

/* ===== EMPTY STATE ===== */
.container p {
    text-align: center;
    color: #cbd5f5;
}
</style>

</head>
<body>

<div class="topbar">
    <div class="logo">HouseRental</div>
    <div class="top-links">
        <a href="index.php">Home</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
<h1>My Bookings</h1>

<div class="bookings-container">

<?php if ($result->num_rows > 0): ?>
<?php while ($row = $result->fetch_assoc()): ?>

<div class="booking-card">

    <h3><?= htmlspecialchars($row['title']) ?></h3>
    <p class="city"><?= htmlspecialchars($row['city']) ?></p>

    <p><strong>Start:</strong> <?= $row['checkin_date'] ?: 'N/A' ?></p>
    <p><strong>End:</strong> <?= $row['checkout_date'] ?: 'N/A' ?></p>

    <!-- Status badges -->
    <div class="badges">
        <span class="badge status <?= $row['status'] ?>">
            <?= ucfirst($row['status']) ?>
        </span>

        <span class="badge <?= $row['payment_status'] === 'paid' ? 'paid' : 'unpaid' ?>">
            <?= ucfirst($row['payment_status']) ?>
        </span>
    </div>

    <!-- Actions -->
    <div class="actions">

        <?php if ($row['status'] === 'pending'): ?>
            <form method="POST" action="cancel_booking.php"
                  onsubmit="return confirm('Cancel booking?');">
                <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                <button class="btn btn-danger">Cancel</button>
            </form>
        <?php endif; ?>

        <?php if ($row['status'] === 'approved' && $row['payment_status'] === 'unpaid'): ?>
            <a href="payment.php?id=<?= $row['id'] ?>" class="btn btn-warning">
                Pay Now
            </a>
        <?php endif; ?>

        <?php if ($row['payment_status'] === 'paid'): ?>
            <a href="pdf_receipt.php?id=<?= $row['id'] ?>" class="btn btn-success">
                Download Receipt
            </a>
        <?php endif; ?>

    </div>
</div>

<?php endwhile; ?>
<?php else: ?>
    <p>No bookings found.</p>
<?php endif; ?>

</div>
</div>

</body>
</html>
