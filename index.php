<?php
session_start();
require 'config.php';

/* AUTH FUNCTIONS — MUST BE INSIDE PHP */

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
$query = "
SELECT 
    h.id,
    h.title,
    h.city,
    h.price,
    MIN(hi.image_path) AS image
FROM houses h
LEFT JOIN house_images hi ON h.id = hi.house_id
GROUP BY h.id
";
$result = mysqli_query($conn, $query);
// Filters (keeps existing behavior)
$city     = isset($_GET['city'])     ? mysqli_real_escape_string($conn, $_GET['city']) : '';
$min_rent = isset($_GET['min_rent']) ? (int)$_GET['min_rent'] : 0;
$max_rent = isset($_GET['max_rent']) ? (int)$_GET['max_rent'] : 0;
// Filters (Location + City combined search)
$location = isset($_GET['location'])
    ? mysqli_real_escape_string($conn, $_GET['location'])
    : '';
$filterSql = " WHERE status='available' ";
if ($location !== '') {
    $filterSql .= " AND (
        location LIKE '%$location%' 
        OR city LIKE '%$location%'
    )";
}
// Section queries
$popularQuery = "
SELECT h.*, MIN(hi.image_path) AS image
FROM houses h
LEFT JOIN house_images hi ON h.id = hi.house_id
$filterSql
GROUP BY h.id
ORDER BY h.id DESC
LIMIT 15
";
$recommendedQuery = "
SELECT h.*, MIN(hi.image_path) AS image
FROM houses h
LEFT JOIN house_images hi ON h.id = hi.house_id
$filterSql
GROUP BY h.id
ORDER BY RAND()
LIMIT 12
";
$popularRes = mysqli_query($conn, $popularQuery);
$recommendedRes = mysqli_query($conn, $recommendedQuery);
// Helper to format price
function price_format($n) {
    return number_format((float)$n, 0);
}
// Helper: render a single card HTML (uses $row from DB)
// function render_card($row) {
//     $id = (int)$row['id'];
function render_card($row) {
    global $conn;   // IMPORTANT: access DB inside function
    $id = (int)$row['id'];
    // 🔥 CHECK IF HOUSE IS SAVED
    $isSaved = false;
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $stmt = $conn->prepare(
        "SELECT id FROM saved_houses WHERE user_id=? AND house_id=?"
    );
    $stmt->bind_param("ii", $_SESSION['user_id'], $id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $isSaved = true;
    }
}
    $title = htmlspecialchars($row['title']);
    $location = trim($row['location']) !== '' ? htmlspecialchars($row['location']) . ', ' . htmlspecialchars($row['city']) : htmlspecialchars($row['city']);
    $rent = price_format($row['rent']);
    $bhk = (int)$row['bhk'];
    $area = (int)$row['area_sqft'];
    $desc_short = htmlspecialchars(mb_substr($row['description'], 0, 90));
    $img = !empty($row['image']) ? htmlspecialchars($row['image']) : 'assets/images/default.jpg';
    // Use a fake rating for UI (you can replace this if you have a rating column)
    $rating = isset($row['rating']) ? number_format((float)$row['rating'],1) : (4.9);
    ob_start();
    ?>
    <article class="ab-card" data-id="<?php echo $id; ?>">
        <div class="ab-media">
            <a href="house_details.php?id=<?php echo $id; ?>">
                <!-- <img src="<?php echo $img; ?>" alt="<?php echo $title; ?>"
                     onerror="this.onerror=null;this.src='assets/images/default.jpg'"> -->
                     <!-- <img src="assets/images/<?php echo $row['image']; ?>"
     onerror="this.src='assets/images/default.jpg';"> -->
    <!-- <img src="assets/images/<?php echo htmlspecialchars($row['image']); ?>"
     onerror="this.src='assets/images/default.jpg';"> -->
<!-- <img 
  src="assets/images/<?php echo htmlspecialchars($row['image']); ?>" 
  class="card-img"
  onerror="this.src='assets/images/default.jpg'"
> -->
<!-- <img src="assets/images/uploads/<?php echo $row['image']; ?>" alt="<?php echo $row['title']; ?>"> -->
<img src="<?php echo htmlspecialchars($row['image']); ?>"
     alt="<?php echo htmlspecialchars($row['title']); ?>"
     onerror="this.src='assets/images/default.jpg'">
            </a>
            <span class="badge">Guest favourite</span>
          <?php if (isset($_SESSION['user_id'])): ?>
    <button class="heart-btn <?php echo $isSaved ? 'saved' : ''; ?>"
            data-id="<?php echo $id; ?>">
        <svg class="heart-icon" viewBox="0 0 24 24">
            <path d="M12 21s-7.6-4.73-10.3-8.02C-0.02 7.9 3.2 4 6.6 4
                     8.7 4 10 5.1 12 7.1
                     14 5.1 15.3 4 17.4 4
                     c3.4 0 6.6 3.9 4.9 8.98
                     C19.6 16.27 12 21 12 21z"/>
        </svg>
    </button>
<?php else: ?>
    <button class="heart-btn guest"
            onclick="window.location.href='login.php'">
        <svg class="heart-icon" viewBox="0 0 24 24">
            <path d="M12 21s-7.6-4.73-10.3-8.02C-0.02 7.9 3.2 4 6.6 4
                     8.7 4 10 5.1 12 7.1
                     14 5.1 15.3 4 17.4 4
                     c3.4 0 6.6 3.9 4.9 8.98
                     C19.6 16.27 12 21 12 21z"/>
        </svg>
    </button>
<?php endif; ?>
        </div>
        <div class="ab-body">
            <div class="ab-top">
                <div class="ab-title"><?php echo $title; ?></div>
                <div class="ab-price">₹ <?php echo $rent; ?><span class="per">/ month</span></div>
            </div>

            <div class="ab-sub"><?php echo $location; ?></div>
            <div class="ab-row">
                <div class="ab-meta"><?php echo $bhk; ?> BHK • <?php echo $area; ?> sqft</div>
                <div class="ab-rating">★ <?php echo $rating; ?></div>
            </div>

            <div class="ab-desc"><?php echo $desc_short; ?>…</div>
        </div>
    </article>
    <?php
    return ob_get_clean();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>HouseRental — Discover stays</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">

  <style>
    :root{
      --bg:#140f0b;
      --panel:#1f1510;
      --muted:#cbbdb6;
      --accent:#ff5a79;
      --card:#201612;
      --radius:18px;
      --gap:20px;
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      color: #f6efe8;
    }
    html,body{height:100%;margin:0;background:linear-gradient(180deg,#100908 0%, #160f0b 100%);-webkit-font-smoothing:antialiased;}
    .wrap{max-width:1220px;margin:20px auto;padding:20px;}
    /* Top navigation (dark) */
    .top{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;}
    .brand{display:flex;align-items:center;gap:12px}
    .logo-mark{width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,#ff6b88,#ff9aa2);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700}
    .brand-title{font-weight:700;font-size:20px}
    .nav-right a{color:var(--muted);margin-left:16px;text-decoration:none;font-weight:600}
    /* Search panel */
    .search-panel{background:linear-gradient(90deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));padding:18px;border-radius:26px;box-shadow:0 10px 30px rgba(0,0,0,0.6);display:flex;gap:12px;align-items:center;margin-bottom:26px;}
    .search-col{padding:6px 12px;border-radius:12px;min-width:180px;}
    .search-col label{font-size:12px;color:var(--muted);display:block;margin-bottom:4px}
    .search-col input{background:transparent;border:none;color:#fff;outline:none;font-size:15px}
    .search-btn{margin-left:auto;background:var(--accent);border:none;color:#111;padding:10px 14px;border-radius:12px;font-weight:700;cursor:pointer}
    /* Section title and arrows */
    .section{margin-bottom:28px;}
    .sec-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;}
    .sec-title{font-size:20px;font-weight:700}
    .sec-sub{color:var(--muted);font-size:14px}
    .sec-controls{display:flex;gap:8px}
    .sec-button{background:transparent;border:1px solid rgba(255,255,255,0.06);padding:8px;border-radius:8px;color:var(--muted);cursor:pointer}
    /* Horizontal scroller */
    /* .scroller{display:flex;gap:18px;overflow-x:auto;padding-bottom:6px;scroll-behavior:smooth;} */
    .scroller{
  display:grid;
  grid-template-columns: repeat(5, 1fr); /* 6 cards per row */
  gap:18px;
}

    .scroller::-webkit-scrollbar{height:10px}
    .scroller::-webkit-scrollbar-thumb{background:rgba(255,255,255,0.06);border-radius:8px}
    /* Card style */
    .ab-card{ width:100%;background:var(--card);border-radius:14px;overflow:hidden;color:#fff;box-shadow:0 8px 30px rgba(0,0,0,0.6);display:flex;flex-direction:column;}
    .ab-media{position:relative;height:200px;overflow:hidden;background:#000;}
    .ab-media img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .35s;}
    .ab-card:hover .ab-media img{transform:scale(1.03)}
    .badge{position:absolute;left:12px;top:12px;background:rgba(0,0,0,0.5);padding:6px 10px;border-radius:999px;color:#fff;font-weight:700;font-size:13px}
    .heart-btn{position:absolute;right:12px;top:12px;background:rgba(255,255,255,0.96);border-radius:999px;padding:8px;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;opacity:.95}
    .heart-btn svg{width:18px;height:18px;fill:#111}
    .heart-btn.active{background:var(--accent)} .heart-btn.active svg{fill:#fff}
    .ab-body{padding:12px 14px;display:flex;flex-direction:column;gap:8px}
    .ab-top{display:flex;justify-content:space-between;align-items:flex-start}
    .ab-title{font-weight:700;font-size:15px}
    .ab-sub{color:var(--muted);font-size:13px}
    .ab-row{display:flex;justify-content:space-between;align-items:center;color:var(--muted);font-size:13px}
    .ab-desc{color:var(--muted);font-size:13px;margin-top:6px}
    .ab-price{font-weight:800;color:#fff}
    /* Responsive */
    @media (max-width:900px){
      .search-panel{flex-direction:column;align-items:stretch}
      .scroller{padding:12px 2px}
      .ab-card{min-width:220px}
    }
    @media (max-width:480px){
      .search-panel{padding:12px;}
      .ab-media{height:160px}
    }
    @media (max-width:1400px){
  .scroller{ grid-template-columns: repeat(5, 1fr); }
}
  </style>
</head>
<body>
  <div class="wrap">
    <!-- Top -->
    <div class="top">
      <div class="brand">
        <div class="logo-mark">HR</div>
        <div class="brand-title">HouseRental</div>
      </div>
      <div class="nav-right">
        <?php if (isLoggedIn()): ?>
          <button id="themeToggle" class="theme-toggle" title="Toggle theme">
  🌙
</button>

          <span style="color:var(--muted);margin-right:12px">Hi, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
          <?php if (isAdmin()): ?><a href="admin/index.php">Admin</a><?php endif; ?>
          <a href="logout.php">Logout</a>
        <?php else: ?>
          <a href="login.php">Login</a> <a href="register.php">Signup</a>
        <?php endif; ?>
        
      </div>
    </div>

    <!-- Search -->
    <form method="get" class="search-panel" role="search" aria-label="Find a place">
      <div class="search-col">
        <label>Where</label>
        <input type="text" name="location" placeholder="Search locations" value="<?php echo htmlspecialchars($city); ?>">
      </div>
      <div class="search-col">
    <label>When</label>
    <input type="date" name="checkin" />
</div>

   
      <button type="submit" class="search-btn">Search</button>
    </form>
    

    <!-- Popular Homes section -->
    <section class="section">
      <div class="sec-head">
        <div>
          <div class="sec-title">Popular homes</div>
          <div class="sec-sub">Homes guests love</div>
        </div>
        <div class="sec-controls">
          <button class="sec-button" data-target="popular" aria-label="Scroll left">◀</button>
          <button class="sec-button" data-target="popular" aria-label="Scroll right">▶</button>
        </div>
      </div>

      <div id="popular" class="scroller" role="list">
        <?php
        if ($popularRes && mysqli_num_rows($popularRes) > 0) {
            while ($r = mysqli_fetch_assoc($popularRes)) {
                echo render_card($r);
            }
        } else {
            echo '<div class="empty" style="padding:18px;background:transparent;color:var(--muted)">No popular homes found.</div>';
        }
        ?>
      </div>
    </section>

    <!-- Recommended section -->
    <section class="section">
      <div class="sec-head">
        <div>
          <div class="sec-title">Recommended for you</div>
          <div class="sec-sub">Handpicked stays</div>
        </div>
        <div class="sec-controls">
          <button class="sec-button" data-target="recommended">◀</button>
          <button class="sec-button" data-target="recommended">▶</button>
        </div>
      </div>

      <div id="recommended" class="scroller" role="list">
        <?php
        if ($recommendedRes && mysqli_num_rows($recommendedRes) > 0) {
            while ($r = mysqli_fetch_assoc($recommendedRes)) {
                echo render_card($r);
            }
        } else {
            echo '<div class="empty" style="padding:18px;background:transparent;color:var(--muted)">No recommendations available.</div>';
        }
        ?>
      </div>
    </section>

  </div>

  <script>
    // Arrow buttons scroll the scroller left/right
    document.querySelectorAll('.sec-button').forEach(btn => {
      btn.addEventListener('click', function() {
        const targetId = this.dataset.target;
        const dirLeft = this.innerText.trim() === '◀';
        const scroller = document.getElementById(targetId);
        if (!scroller) return;
        const scrollAmount = scroller.clientWidth * 0.7;
        scroller.scrollBy({left: dirLeft ? -scrollAmount : scrollAmount, behavior: 'smooth'});
      });
    });

    // Heart/save UI using localStorage
    document.addEventListener('click', function(e) {
      const btn = e.target.closest('.heart-btn');
      if (!btn) return;
      btn.classList.toggle('active');
      const id = btn.dataset.id;
      const key = 'hr_saved';
      let saved = JSON.parse(localStorage.getItem(key) || '[]');
      if (btn.classList.contains('active')) {
        if (!saved.includes(id)) saved.push(id);
      } else {
        saved = saved.filter(x => x !== id);
      }
      localStorage.setItem(key, JSON.stringify(saved));
    });

    // restore saved buttons on load
    document.addEventListener('DOMContentLoaded', function() {
      const saved = JSON.parse(localStorage.getItem('hr_saved') || '[]');
      saved.forEach(id => {
        const btn = document.querySelector('.heart-btn[data-id="' + id + '"]');
        if (btn) btn.classList.add('active');
      });
    });
  </script>
  <script>
document.querySelectorAll('.heart-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const houseId = btn.dataset.houseId;

        fetch('toggle_save.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'house_id=' + houseId
        })
        .then(res => res.text())
        .then(result => {
            if (result === 'saved') {
                btn.classList.add('saved');
            } else if (result === 'removed') {
                btn.classList.remove('saved');
            } else {
                alert('Please login first');
                fetch('/house_rental/toggle_save.php', { ... })

            }
        });
    });
});

</script>
<!-- other HTML content -->

<script>
document.querySelectorAll('.heart-btn[data-id]').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();

        fetch('/house_rental/toggle_save.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'house_id=' + this.dataset.id
        })
        .then(res => res.text())
        .then(status => {
            if (status === 'saved') {
                this.classList.add('saved');
            }
            if (status === 'removed') {
                this.classList.remove('saved');
            }
            if (status === 'login') {
                window.location.href = 'login.php';
            }
        });
    });
});
</script>



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
3. Cookies and Tracking
We use cookies to:
Remember user preferences and login sessions.
Analyze website traffic and improve features.
Enhance overall browsing experience.
You can disable cookies through your browser settings, but some features may not work properly.
</p><p>
4. Data Security
We take reasonable security measures to protect your data, including:
Secure database storage
Limited access to sensitive information
Regular monitoring for unauthorized activity
However, no online system is 100% secure, and we cannot guarantee absolute security.
</p><p>
5. Third-Party Services
Our website may include third-party services such as:
Google Maps for showing property locations
External links to other websites
We are not responsible for the privacy practices of these third-party services. Please review their policies separately.
</p><p>
9. Contact Us
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
4. Account Responsibilities
When creating an account, you agree to:
Keep your login credentials confidential
Not impersonate another person
Not misuse or abuse the platform
Notify us immediately of unauthorized access
HouseRental reserves the right to suspend or terminate accounts violating these rules.
</p><p>
5. Property Listings
For property owners:
All listing information must be accurate and lawful
Images must belong to the owner or have usage rights
Fake, misleading, or duplicate listings are not allowed
We reserve the right to remove or edit listings without prior notice if they violate our policies.
</p><p>
6. Bookings & Payments
Any booking, payment, or agreement is strictly between the user and the property owner
HouseRental is not responsible for payment disputes
Prices, availability, and house rules are set by owners
Users should verify all details before making any commitment.
</p><p>
7. Cancellations & Refunds
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

  <script>
const toggleBtn = document.getElementById('themeToggle');

// Load saved theme
if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark');
    toggleBtn.textContent = '☀️';
}

// Toggle theme
toggleBtn.addEventListener('click', () => {
    document.body.classList.toggle('dark');

    if (document.body.classList.contains('dark')) {
        localStorage.setItem('theme', 'dark');
        toggleBtn.textContent = '☀️';
    } else {
        localStorage.setItem('theme', 'light');
        toggleBtn.textContent = '🌙';
    }
});
</script>

</body>
</html>
