<?php
session_start();
include 'config.php';
require_once 'config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$house_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

$error = '';
$success = '';

$res = mysqli_query($conn, "SELECT * FROM houses WHERE id=$house_id");
if (!$res || mysqli_num_rows($res) == 0) {
    echo "House not found.";
    exit;
}
$house = mysqli_fetch_assoc($res);
$per_day_rent = $house['rent'] / 30;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date   = mysqli_real_escape_string($conn, $_POST['end_date']);

    if (empty($start_date) || empty($end_date)) {
        $error = "Please select both start and end date.";
    } else {

        // 🔹 Step 1: Total Days Calculate
        $start = new DateTime($start_date);
        $end   = new DateTime($end_date);

        $interval = $start->diff($end);
        $total_days = $interval->days;

        if ($total_days <= 0) {
            $error = "Invalid booking dates.";
        } else {

            // 🔹 Step 2: Monthly Rent lo
            $monthly_rent = $house['rent'];  // assuming column name price hai

            // 🔹 Step 3: Per Day Rent
            $per_day_rent = $monthly_rent / 30;

            // 🔹 Step 4: Total Amount
            $total_amount = $per_day_rent * $total_days;

            // 🔹 Step 5: Insert with total_amount
            $sql = "INSERT INTO bookings 
               (house_id, user_id, checkin_date, checkout_date, total_days, total_amount, message, status)
               VALUES 
               ($house_id, $user_id, '$start_date', '$end_date', $total_days, $total_amount, 'Booking Request', 'pending')";

            if (mysqli_query($conn, $sql)) {
                $success = "Booking request sent successfully. Total Rent: ₹" . number_format($total_amount);
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book House - <?php echo htmlspecialchars($house['title']); ?></title>
    <!-- <link rel="stylesheet" href="assets/css/style.css"> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    /* PAGE BACKGROUND */
.booking-page{
    min-height:100vh;
    background:linear-gradient(135deg,#0f1b2e,#081426);
    display:flex;
    justify-content:center;
    align-items:center;
    padding:40px 20px;
}
.booking-card{
    width:100%;
    max-width:900px;   /* Pehle 1200 tha */
    background:#ffffff;
    border-radius:18px;
    padding:30px;      /* Pehle 40 tha */
    box-shadow:0 20px 50px rgba(0,0,0,0.18);
}


/* TITLE */
.booking-title{
    text-align:center;
    margin-bottom:30px;
    font-size:28px;
    font-weight:600;
}

/* FLEX LAYOUT */
.booking-layout{
    display:flex;
    gap:30px;
    align-items:flex-start;
}

/* CALENDAR SECTION */
.calendar-section{
    flex:2;
}

/* CALENDAR BOXES */
.calendar-wrapper{
    display:flex;
    gap:25px;
}
.calendar-wrapper{
    display:flex;
    gap:20px;   /* Pehle zyada tha */
}

.calendar-box{
    flex:1;
    background:#f7f9fc;
    padding:15px;   /* Pehle 20px */
    border-radius:14px;
    box-shadow:0 6px 16px rgba(0,0,0,0.05);
}

/* BOOKING SUMMARY CARD */
.booking-summary{
    flex:1;
    background:#f7f9fc;
    padding:25px;
    border-radius:18px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
    position:sticky;
    top:30px;
}

.booking-summary h3{
    margin-bottom:20px;
    font-size:20px;
    font-weight:600;
}

/* SUMMARY ROW */
.summary-row{
    display:flex;
    justify-content:space-between;
    padding:10px 0;
    font-size:15px;
    border-bottom:1px solid #e4e7ec;
}

.summary-row:last-child{
    border-bottom:none;
}

/* TOTAL STYLE */
.summary-row.total{
    font-size:18px;
    font-weight:700;
    color:#1e73ff;
}

/* BUTTON */
.booking-btn{
    margin-top:30px;
    width:100%;
    padding:16px;
    border:none;
    border-radius:14px;
    background:linear-gradient(90deg,#1e73ff,#2a9df4);
    color:#fff;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

.booking-btn:hover{
    opacity:0.9;
}

/* BACK LINK */
.back-link{
    display:block;
    text-align:center;
    margin-top:15px;
    color:#666;
    text-decoration:none;
    font-size:14px;
}

/* RESPONSIVE */
@media(max-width:900px){
    .booking-layout{
        flex-direction:column;
    }
    .calendar-wrapper{
        flex-direction:column;
    }
    .booking-summary{
        position:relative;
        top:0;
    }
}

/* MODAL OVERLAY */
.success-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.75);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

/* MODAL BOX */
.success-modal {
    background: rgba(15,23,42,0.9);
    border-radius: 20px;
    padding: 35px 40px;
    width: 100%;
    max-width: 420px;
    text-align: center;
    box-shadow: 0 40px 80px rgba(0,0,0,0.9);
    animation: popIn 0.35s ease;
}

/* ICON */
.success-icon {
    width: 70px;
    height: 70px;
    margin: 0 auto 15px;
    border-radius: 50%;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #fff;
    font-size: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 25px rgba(34,197,94,0.6);
}

/* TEXT */
.success-modal h3 {
    color: #f8fafc;
    font-size: 22px;
    margin-bottom: 10px;
}

.success-modal p {
    color: #cbd5f5;
    font-size: 15px;
    line-height: 1.6;
}

/* ACTIONS */
.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-top: 25px;
}

.modal-btn {
    padding: 12px 18px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
    font-size: 14px;
}

.modal-btn.primary {
    background: linear-gradient(135deg, #0ea5e9, #2563eb);
    color: #fff;
}

.modal-btn.secondary {
    background: rgba(255,255,255,0.08);
    color: #e5e7eb;
}


</style>
</head>
<body>
<div class="booking-page">

    <div class="booking-card">

        <h2 class="booking-title">Book This House</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="post">

    <div class="calendar-wrapper">

        <div class="calendar-box">
            <h4>Start Date</h4>
            <div id="start_calendar"></div>
            <input type="hidden" name="start_date" id="start_date" required>
        </div>

        <div class="calendar-box">
            <h4>End Date</h4>
            <div id="end_calendar"></div>
            <input type="hidden" name="end_date" id="end_date" required>
        </div>

    </div>
    <!-- <div id="bookingSummary" style="
    margin:20px 0;
    padding:15px;
    background:#f4f7fc;
    border-radius:10px;
    text-align:center;
    font-size:15px;
">
    <p>Selected Days: <b id="totalDays">0</b></p>
    <p>Per Day Rent: ₹<?= number_format($per_day_rent) ?></p>
    <p>Total Amount: ₹<b id="totalAmount">0</b></p>
</div> -->
<div class="booking-summary">
    <h3>Booking Summary</h3>

    <div class="summary-row">
        <span>Selected Days</span>
        <strong id="totalDays">0</strong>
    </div>

    <div class="summary-row">
        <span>Per Day Rent</span>
        <strong>₹<?= number_format($per_day_rent) ?></strong>
    </div>

    <div class="summary-row total">
        <span>Total Amount</span>
        <strong>₹<span id="totalAmount">0</span></strong>
    </div>
</div>


    <button type="submit" class="booking-btn">Confirm Booking</button>

    <a href="house_details.php?id=<?php echo $house_id; ?>" class="back-link">
        ← Back to Details
    </a>

</form>


    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- <script>
let endCalendar;

flatpickr("#start_calendar", {
    inline: true,
    minDate: "today",
    dateFormat: "Y-m-d",
    onChange: function(selectedDates, dateStr) {
        document.getElementById("start_date").value = dateStr;

        // reset end date
        document.getElementById("end_date").value = "";

        // update end calendar min date
        endCalendar.set("minDate", dateStr);
    }
});

endCalendar = flatpickr("#end_calendar", {
    inline: true,
    minDate: "today",
    dateFormat: "Y-m-d",
    onChange: function(selectedDates, dateStr) {
        document.getElementById("end_date").value = dateStr;
    }
});
</script> -->
<script>
let endCalendar;

const perDayRent = <?= $per_day_rent ?>;

function calculateTotal() {

    let startDate = document.getElementById("start_date").value;
    let endDate   = document.getElementById("end_date").value;

    if (!startDate || !endDate) return;

    let start = new Date(startDate);
    let end   = new Date(endDate);

    let diffTime = end - start;
    let diffDays = diffTime / (1000 * 60 * 60 * 24);

    if (diffDays > 0) {
        document.getElementById("totalDays").innerText = diffDays;
        document.getElementById("totalAmount").innerText =
            (diffDays * perDayRent).toLocaleString();
    } else {
        document.getElementById("totalDays").innerText = 0;
        document.getElementById("totalAmount").innerText = 0;
    }
}

flatpickr("#start_calendar", {
    inline: true,
    minDate: "today",
    dateFormat: "Y-m-d",
    onChange: function(selectedDates, dateStr) {
        document.getElementById("start_date").value = dateStr;
        document.getElementById("end_date").value = "";
        document.getElementById("totalDays").innerText = 0;
        document.getElementById("totalAmount").innerText = 0;

        endCalendar.set("minDate", dateStr);
    }
});

endCalendar = flatpickr("#end_calendar", {
    inline: true,
    minDate: "today",
    dateFormat: "Y-m-d",
    onChange: function(selectedDates, dateStr) {
        document.getElementById("end_date").value = dateStr;
        calculateTotal();
    }
});
</script>

<?php if (!empty($success)): ?>
<div class="success-modal-overlay" id="successModal">
    <div class="success-modal">
        <div class="success-icon">✓</div>
        <h3>Booking Request Sent</h3>
        <p>Your booking request has been sent successfully.<br>
           We will notify you once it is approved.</p>

        <div class="modal-actions">
            <a href="index.php" class="modal-btn secondary">Go to Home</a>
            <button onclick="closeSuccessModal()" class="modal-btn primary">
                OK
            </button>
        </div>
    </div>
</div>
<?php endif; ?>
<script>
function closeSuccessModal() {
    document.getElementById("successModal").style.display = "none";
}
</script>


</body>
</html>
