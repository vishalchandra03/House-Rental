<?php
session_start();
include 'config.php';
/* AUTH FUNCTIONS — MUST BE BEFORE HTML */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$id = (int)$_GET['id'];
/* ================= IMAGES ================= */
$imgRes = mysqli_query(
    $conn,
    "SELECT image_path FROM house_images WHERE house_id = $id"
);
$images = [];

while ($row = mysqli_fetch_assoc($imgRes)) {
    $images[] = $row['image_path'];
}
// Fallback image
if (empty($images)) {
    $images[] = 'assets/images/default.jpg';
}
// fallback image
if (empty($images)) {
    $images[] = 'assets/images/default.jpg';
}
/* ================= HOUSE DATA ================= */
$sql = "SELECT h.*, u.name AS owner_name, u.phone AS owner_phone
        FROM houses h
        LEFT JOIN users u ON h.owner_id = u.id
        WHERE h.id = $id";

$res = mysqli_query($conn, $sql);
if (!$res || mysqli_num_rows($res) === 0) {
    echo "House not found";
    exit;
}
$house = mysqli_fetch_assoc($res);

/* ================= SIMILAR HOUSES ================= */
$city = mysqli_real_escape_string($conn, $house['city']);
$currentId = $house['id'];
$currentHouseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$similarSql = "
SELECT h.*, 
COALESCE(MIN(hi.image_path), 'assets/images/default.jpg') AS image
FROM houses h
LEFT JOIN house_images hi ON h.id = hi.house_id
WHERE h.city = '$city'
AND h.id != $currentHouseId
GROUP BY h.id
LIMIT 4
";

$similarRes = mysqli_query($conn, $similarSql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($house['title']); ?></title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* ---------- GLOBAL ---------- */
*{
    box-sizing:border-box;
}
body{
    margin:0;
    background:#0f1115;
    color:#e5e7eb;
    font-family:Segoe UI, Arial, sans-serif;
    line-height:1.6;
}

/* ---------- TOP BAR ---------- */
.topbar{
    background:#141821;
    padding:16px 60px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    position:sticky;
    top:0;
    z-index:100;
    box-shadow:0 8px 20px rgba(0,0,0,0.6);
}
.logo{
    font-size:22px;
    font-weight:700;
    color:#ff4f7a;
}
.top-links a{
    margin-left:22px;
    color:#cbd5e1;
    text-decoration:none;
    font-weight:500;
}
.top-links a:hover{
    color:#fff;
}

/* ---------- PAGE WIDTH ---------- */
.page-wrap{
    max-width:1100px;
    margin:40px auto;
    padding:0 24px;
}

/* ---------- GALLERY ---------- */
.gallery-wrap{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:18px;
    margin-bottom:30px;
}
.gallery-main img{
    width:100%;
    height:380px;
    object-fit:cover;
    border-radius:18px;
    cursor:pointer;
}
.gallery-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
}
.gallery-grid img{
    width:100%;
    height:180px;
    object-fit:cover;
    border-radius:16px;
    cursor:pointer;
}

/* ---------- TITLE & PRICE ---------- */
h1{
    font-size:28px;
    margin-bottom:6px;
}
.location{
    color:#9ca3af;
    margin-bottom:20px;
}
.price-box{
    background:#141821;
    padding:18px 22px;
    border-radius:16px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:26px;
}
.price-box h2{
    margin:0;
    color:#ff4f7a;
}
.price-box span{
    color:#cbd5e1;
}

/* ---------- INFO GRID ---------- */
.info-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
    margin-bottom:30px;
}
.info-item{
    background:#141821;
    padding:14px;
    border-radius:14px;
    text-align:center;
    font-size:14px;
}

/* ---------- SECTIONS ---------- */
.section{
    margin-bottom:36px;
}
.section h3{
    margin-bottom:12px;
    font-size:20px;
}

/* ---------- AMENITIES ---------- */
.amenities-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
    gap:16px;
}
.amenity-item{
    background:#141821;
    padding:14px;
    border-radius:14px;
    display:flex;
    align-items:center;
    gap:10px;
    font-size:14px;
}

/* ---------- THINGS TO KNOW ---------- */
.things-know{
    margin-bottom:36px;
}
.things-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:18px;
}
.thing-card{
    background:#141821;
    padding:18px;
    border-radius:18px;
}
.thing-card h4{
    margin:10px 0 6px;
}
.thing-card p{
    color:#9ca3af;
}
.thing-card button{
    margin-top:10px;
    background:#ff4f7a;
    border:none;
    padding:8px 14px;
    border-radius:10px;
    color:#fff;
    cursor:pointer;
}

/* ---------- OWNER ---------- */
.section i{
    margin-right:8px;
    color:#ff4f7a;
}

/* ---------- MAP ---------- */
.location-box{
    background:#141821;
    padding:20px;
    border-radius:18px;
}

/* ---------- BOOK NOW ---------- */
.book-now-wrapper{
    display:flex;
    justify-content:center;
    margin:40px 0;  
}
.book-btn{
    background:#ff4f7a;
    padding:14px 40px;
    border-radius:18px;
    color:#fff;
    text-decoration:none;
    font-size:18px;
    font-weight:600;
}
.book-btn:hover{
    background:#ff3566;
}



/* ---------- SIMILAR HOMES ---------- */
.similar-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:18px;
}
.similar-card{
    background:#141821;
    border-radius:18px;
    overflow:hidden;
    text-decoration:none;
    color:#fff;
}
.similar-card img{
    width:100%;
    height:180px;
    object-fit:cover;
}
.similar-info{
    padding:14px;
}

/* ---------- FOOTER ---------- */
.site-footer{
    background:#141821;
    padding:20px 60px;
    margin-top:60px;
}
.footer-inner{
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
}
.footer-left a{
    color:#9ca3af;
    margin:0 6px;
    text-decoration:none;
}
.footer-social a{
    color:#ff4f7a;
    text-decoration:none;
}

/* ---------- MODALS ---------- */
.modal,
.footer-modal,
.image-modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.8);
    justify-content:center;
    align-items:center;
    z-index:999;
}
.modal-box,
.footer-modal-box{
    background:#141821;
    padding:24px;
    border-radius:18px;
    max-width:520px;
    width:90%;
}
.close{
    float:right;
    cursor:pointer;
    font-size:22px;
}

/* ---------- IMAGE MODAL ---------- */
.image-modal img{
    max-width:90%;
    max-height:90%;
    border-radius:18px;
}



/* ===== GLOBAL PAGE MARGIN ===== */
body{
    margin: 0;
    padding-left: 35px;
    padding-right: 35px;
    background: #0f1115;
    color: #e5e7eb;
    font-family: Segoe UI, Arial, sans-serif;
}

/* Remove any forced centering or width limits */
.page-wrap,
.gallery-wrap,
.price-box,
.info-grid,
.section,
.amenities-grid,
.things-know,
.location-box,
.similar-grid,
.site-footer{
    max-width: 100%;
    margin-left: 0;
    margin-right: 0;
}

/* Topbar spacing consistency */
.topbar{
    margin-left: -35px;
    margin-right: -35px;
    padding-left: 35px;
    padding-right: 35px;
}

.image-modal .close {
    position: absolute;
    top: 20px;
    right: 26px;
    font-size: 34px;
    color: #fff;
    cursor: pointer;
}
.airbnb-rating-dark{
  background:#020617;
  border:1px solid #1e293b;
  border-radius:22px;
  padding:42px 36px;
  color:#e5e7eb;
  margin:50px 0;
}

/* TOP */
.top-rating{
  display:flex;
  justify-content:center;
  align-items:center;
  gap:20px;
}

.rating-big{
  font-size:56px;
  font-weight:700;
  color:#f8fafc;
}

.leaf{
  font-size:34px;
  opacity:.6;
}

.rating-title{
  text-align:center;
  margin-top:12px;
  font-size:22px;
}

.rating-desc{
  text-align:center;
  max-width:520px;
  margin:8px auto 30px;
  color:#9ca3af;
}

/* GRID */
.rating-grid{
  display:grid;
  grid-template-columns:1.4fr repeat(6,1fr);
  gap:22px;
  border-top:1px solid #1e293b;
  border-bottom:1px solid #1e293b;
  padding:26px 0;
}

.rating-col h4{
  font-size:14px;
  color:#cbd5f5;
  margin-bottom:6px;
}

.rating-col strong{
  font-size:18px;
}

/* OVERALL BARS */
.bars{
  display:flex;
  flex-direction:column;
  gap:6px;
  margin-top:8px;
}

.bar{
  height:4px;
  background:#1e293b;
  border-radius:4px;
}

.bar.active{
  background:#e5e7eb;
}

.modal {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.7);
  z-index: 999;
}

/* center stays SAME */
.modal-box {
  background: linear-gradient(135deg, #020617, #000000);
  max-width: 420px;
  margin: 10% auto;
  padding: 24px;
  border-radius: 18px;
  position: relative;
  color: #e5e7eb;
  border: 1px solid #1e293b;
}

.modal-box h3 {
  margin-top: 0;
  color: #f8fafc;
}

.modal-box p,
.modal-box li {
  color: #cbd5e1;
}

</style>

</head>

<body>

<div class="topbar">
    <div class="logo">HouseRental</div>
    <div class="top-links">
        <a href="index.php">Home</a>
        <?php if(isLoggedIn()): ?>
            <a href="user_bookings.php">My Bookings</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </div>
</div>
<div class="page-wrap">

    <div class="gallery-wrap">

        <div class="gallery-main">
            <img src="<?= htmlspecialchars($images[0]) ?>" onclick="openImage(this.src)">
        </div>

        <div class="gallery-grid">
            <?php foreach (array_slice($images, 1, 4) as $img): ?>
                <img src="<?= htmlspecialchars($img) ?>" onclick="openImage(this.src)">
            <?php endforeach; ?>
        </div>

    </div>

</div>


<h1><?php echo htmlspecialchars($house['title']); ?></h1>
<div class="location"><?php echo htmlspecialchars($house['location']); ?>, <?php echo htmlspecialchars($house['city']); ?></div>

<div class="price-box">
    <h2>₹ <?php echo number_format($house['rent']); ?> / month</h2>
    <span>Deposit ₹ <?php echo number_format($house['deposit']); ?></span>
</div>

<div class="info-grid">
    <div class="info-item"><strong>BHK:</strong> <?php echo $house['bhk']; ?></div>
    <div class="info-item"><strong>Area:</strong> <?php echo $house['area_sqft']; ?> sqft</div>
    <div class="info-item"><strong>Furnishing:</strong> <?php echo $house['furnishing']; ?></div>
    <div class="info-item"><strong>Available:</strong> <?php echo $house['available_from']; ?></div>
</div>

<div class="section">
    <h3>Description</h3>
    <?php echo nl2br(htmlspecialchars($house['description'])); ?>
</div>

<div class="section">
    <h3>What this place offers</h3>

    <div class="amenities-grid">
        <div class="amenity-item">
            <i class="fa-solid fa-utensils"></i>
            <span>Kitchen</span>
        </div>

        <div class="amenity-item">
            <i class="fa-solid fa-square-parking"></i>
            <span>Free parking</span>
        </div>

        <div class="amenity-item">
            <i class="fa-solid fa-wifi"></i>
            <span>Wi-Fi</span>
        </div>

        <div class="amenity-item">
            <i class="fa-solid fa-snowflake"></i>
            <span>Air conditioning</span>
        </div>

        <div class="amenity-item">
            <i class="fa-solid fa-soap"></i>
            <span>Washing machine</span>
        </div>

        <div class="amenity-item">
            <i class="fa-solid fa-paw"></i>
            <span>Pets allowed</span>
        </div>
    </div>
</div>
<section class="things-know">
  <h2>Things to know</h2>

  <div class="things-grid">
    <div class="thing-card">
      <span class="icon">📅</span>
      <h4>Cancellation policy</h4>
      <p>Free cancellation before 15 January.</p>
      <button onclick="openThingModal('cancelModal')">Learn more</button>
    </div>

    <div class="thing-card">
      <span class="icon">🔑</span>
      <h4>House rules</h4>
      <p>Check-in 12:00 pm – 8:00 pm</p>
      <button onclick="openThingModal('rulesModal')">Learn more</button>
    </div>

    <div class="thing-card">
      <span class="icon">🛡️</span>
      <h4>Safety & property</h4>
      <p>Exterior security cameras present</p>
      <button onclick="openThingModal('safetyModal')">Learn more</button>

        

      
    </div>
  </div>
</section>
<!-- <section class="things-know" style="margin-bottom:40px;"> -->

 
<div class="section">
    <h3>Owner</h3>
    <p><i class="fa-solid fa-user"></i> <?= htmlspecialchars($house['owner_name1']) ?></p>
    <p><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($house['owner_phone1']) ?></p>
    <p><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($house['owner_email']) ?></p>
</div>
<!-- Location Section -->
<?php
$mapLocation = urlencode($house['city']);
?>

<div class="location-box">
    <h3>Where you’ll be</h3>
    <p><?php echo htmlspecialchars($house['city']); ?></p>

    <iframe
        src="https://www.google.com/maps?q=<?php echo $mapLocation; ?>&output=embed"
        loading="lazy"
        width="100%"
        height="380"
        style="border:0; border-radius:14px;"
        allowfullscreen>
    </iframe>

<section class="airbnb-rating-dark">

  <!-- TOP RATING -->
  <div class="top-rating">
    <span class="leaf">❮</span>
    <span class="rating-big">4.92</span>
    <span class="leaf">❯</span>
  </div>

  <h3 class="rating-title">Guest favourite</h3>
  <p class="rating-desc">
    This home is a guest favourite based on ratings, reviews and reliability
  </p>

  <!-- RATING BREAKDOWN -->
  <div class="rating-grid">
    <div class="rating-col overall">
      <h4>Overall rating</h4>
      <div class="bars">
        <span class="bar active"></span>
        <span class="bar active"></span>
        <span class="bar "></span>
        <span class="bar "></span>
        <span class="bar"></span>
      </div>
    </div>

    <div class="rating-col">
      <h4>Cleanliness</h4>
      <strong>4.9</strong>
    </div>
    <div class="rating-col">
      <h4>Accuracy</h4>
      <strong>4.8</strong>
    </div>
    <div class="rating-col">
      <h4>Check-in</h4>
      <strong>4.9</strong>
    </div>
    <div class="rating-col">
      <h4>Communication</h4>
      <strong>5.0</strong>
    </div>
    <div class="rating-col">
      <h4>Location</h4>
      <strong>4.8</strong>
    </div>
    <div class="rating-col">
      <h4>Value</h4>
      <strong>4.9</strong>
    </div>
  </div>
</section>
</section>


</div>

</div>
</div>

    </form>


    <!-- <p><strong><?php echo htmlspecialchars($house['owner_name1']); ?></strong></p>
    <p><?php echo htmlspecialchars($house['owner_phone1']); ?></p> -->

<!-- <?php if(isLoggedIn()): ?>
    <?php if($house['status']=='available'): ?>
        <a class="book-btn" href="book_house.php?id=<?php echo $house['id']; ?>">Book Now</a>
    <?php else: ?>
        <p>Not available</p>
    <?php endif; ?>
<?php else: ?>
    <a class="book-btn" href="login.php">Login to Book</a>
<?php endif; ?> -->

<?php if(isLoggedIn()): ?>
    <?php if($house['status'] == 'available'): ?>
       <div class="book-now-wrapper">
    <a class="book-btn" href="book_house.php?id=<?php echo $house['id']; ?>">
        Book Now
    </a>
</div>

    <?php else: ?>
        <button class="book-btn" disabled>Already Booked</button>
    <?php endif; ?>
<?php else: ?>
    <a class="book-btn" href="login.php">Login to Book</a>
<?php endif; ?>





<div class="modal" id="cancelModal">
  <div class="modal-box">
    <span class="close" onclick="closeThingModal('cancelModal')">×</span>
    <h3>Cancellation policy</h3>
    <p>
      Free cancellation before 15 January.
      After that date, the reservation is non-refundable.
      Refunds are processed within 5–7 business days.
    </p>
  </div>
</div>

<div class="modal" id="rulesModal">
  <div class="modal-box">
    <span class="close" onclick="closeThingModal('rulesModal')">×</span>
    <h3>House rules</h3>
    <ul>
      <li>Check-in: 12:00 pm – 8:00 pm</li>
      <li>Checkout before 10:00 am</li>
      <li>No smoking inside</li>
      <li>Maximum 4 guests</li>
    </ul>
  </div>
</div>

<div class="modal" id="safetyModal">
  <div class="modal-box">
    <span class="close" onclick="closeThingModal('safetyModal')">×</span>
    <h3>Safety & property</h3>
    <ul>
      <li>Exterior security cameras</li>
      <li>No carbon monoxide alarm</li>
      <li>No smoke alarm reported</li>
    </ul>
  </div>
</div>
<script>
function openThingModal(id) {
  document.getElementById(id).style.display = 'block';
}

function closeThingModal(id) {
  document.getElementById(id).style.display = 'none';
}

</script>

<?php if ($similarRes && mysqli_num_rows($similarRes) > 0): ?>
<div class="section">
    <h3>Similar homes</h3>

    <div class="similar-grid">
        <?php while ($s = mysqli_fetch_assoc($similarRes)): ?>
            <a href="house_details.php?id=<?php echo $s['id']; ?>" class="similar-card">
             <?php
$img = (!empty($s['image']) && is_string($s['image']))
    ? htmlspecialchars($s['image'])
    : 'assets/images/default.jpg';
?>
<img src="<?php echo $img; ?>"
     alt="<?php echo htmlspecialchars($s['title']); ?>">


                <div class="similar-info">
                    <h4><?php echo htmlspecialchars($s['title']); ?></h4>
                    <p><?php echo htmlspecialchars($s['city']); ?></p>
                    <strong>₹ <?php echo number_format($s['rent']); ?> / month</strong>
                </div>
            </a>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>



<footer class="site-footer">
  <div class="footer-inner">
    <div class="footer-left">
      <span>© <?php echo date('Y'); ?> HouseRental, Inc.</span>
      <a href="#" onclick="openModal('privacy')">Privacy</a>
      
<span>·</span>
<a href="#" onclick="openModal('terms')">Terms</a>
<span>·</span>
<a href="#" onclick="openModal('company')">Company details</a>

    </div>

    <div class="footer-right">
      
      </div>
      <div class="footer-social">
    <a href="https://www.instagram.com/vishal_chandra03" target="_blank" aria-label="Instagram">
        <i class="fa-brands fa-instagram"></i>
        <span>Instagram</span>
    </a>
</div>

</footer>
<div class="footer-modal" id="footerModal">
  <div class="footer-modal-box">
    <span class="close" onclick="closeModal()">×</span>
    <div id="modalContent"></div>
  </div>
</div>
<script>
function openModal(type) {
  const content = {

    privacy: `<h2>Privacy Policy</h2><p>🔒 Privacy Policy

Your privacy is important to us. This Privacy Policy explains how HouseRental collects, uses, and protects your personal information when you use our website and services.</p>
<p>
1. Information We Collect
When you use our platform, we may collect the following information:
Personal details such as your name, email address, phone number, and login credentials when you register or contact a property owner.
Property-related information when owners list houses, including location, pricing, and availability.
Usage data such as pages visited, search filters used, and interactions with listings to improve user experience.
Device and browser information like IP address, browser type, and operating system for security and analytics purposes.
<p>
2. How We Use Your Information
We use your information to:
Provide and manage house listings and bookings.
Allow communication between users and property owners.
Improve website functionality, performance, and security.
Send important updates related to your account or listings.
Prevent fraud, misuse, and unauthorized access.
We do not sell your personal information to third parties.
</p><p>
3. Contact Us
If you have any questions or concerns about this Privacy Policy, you may contact us at:
HouseRental Support
📧 Email: support@houserental.com
📍 Location: India</p>`,
    terms: `<h2>Terms & Conditions</h2><p>📄 Terms and Conditions
Welcome to HouseRental. By accessing or using this website, you agree to comply with and be bound by the following Terms and Conditions. Please read them carefully before using our services.
</p><p>
1. Acceptance of Terms
By using this website, creating an account, or browsing property listings, you confirm that you accept these Terms & Conditions.
If you do not agree, please do not use the website.
</p><p>
2. About the Platform
HouseRental is an online platform that allows:
Property owners to list houses for rent
Users to browse listings and contact owners
We are not a property owner or broker. We only provide a digital platform for listings and communication.
</p><p>
3. User Eligibility
Users must be 18 years or older
All information provided must be true and accurate
Users are responsible for maintaining account security
</p><p>
4. Cancellations & Refunds
Cancellation policies depend on individual property owners
HouseRental does not guarantee refunds
Users must review cancellation terms before booking</p>`,
    company: `<h2>Company Details</h2><p>About the Company
HouseRental is an online property rental platform designed to help users discover, explore, and connect with rental properties across different cities. Our goal is to make house searching simple, transparent, and accessible for everyone.

We provide a digital space where property owners can list their houses and users can view property details, amenities, locations, and contact owners directly.

</p><p>
Services Provided
Property listings with images and details
Location-based house search
Owner contact information
Rental information display
Map-based property viewing
HouseRental does not own, rent, or manage properties listed on the platform.</p>`
  };

  document.getElementById("modalContent").innerHTML = content[type];
  document.getElementById("footerModal").style.display = "flex";
}

function closeModal() {
  document.getElementById("footerModal").style.display = "none";
}
</script>
<div id="imageModal" class="image-modal">
    <span class="close" onclick="closeImage()">×</span>

    <img id="modalImage" src="">

</div>
<script>
function openImage(src) {
    const modal = document.getElementById("imageModal");
    const img = document.getElementById("modalImage");

    img.src = src;
    modal.style.display = "flex";
}

function closeImage() {
    document.getElementById("imageModal").style.display = "none";
}
</script>


</body>
</html>
