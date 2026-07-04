<?php
include 'config.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $pass = md5($_POST['password']);
    $cpass = md5($_POST['confirm']);

    if ($pass !== $cpass) {
        $error = "Passwords do not match!";
    } else {
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Email already registered!";
        } else {
            mysqli_query($conn, "INSERT INTO users (name, email, phone, password, role)
                                 VALUES ('$name', '$email', '$phone', '$pass', 'tenant')");
            $success = "Registration successful! You can now login.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <!-- <link rel="stylesheet" href="assets/css/style.css"> -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
          <style>
/* Page center */
body{
    margin:0;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:#f6f7fb;
    font-family:Segoe UI, Arial, sans-serif;
}

/* Form card */
form{
    width:380px;
    background:#fff;
    padding:32px;
    border-radius:18px;
    box-shadow:0 20px 40px rgba(0,0,0,0.12);
}

/* Input group */
.reg-input-group{
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 14px;
    border:1px solid #ddd;
    border-radius:14px;
    margin-bottom:16px;
    background:#fff;
}

/* Icon */
.reg-input-group i{
    font-size:16px;
    color:#999;
    min-width:18px;
}

/* Input */
.reg-input-group input{
    width:100%;
    border:none;
    outline:none;
    font-size:14px;
    background:transparent;
}

/* Focus effect */
.reg-input-group:focus-within{
    border-color:#ff4f7a;
    box-shadow:0 0 0 2px rgba(255,79,122,0.15);
}

/* Register button */
.register-btn{
    width:100%;
    margin-top:14px;
    padding:13px;
    border:none;
    border-radius:14px;
    background:#ff4f7a;
    color:#fff;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    transition:0.2s ease;
}

.register-btn i{
    margin-right:6px;
}

.register-btn:hover{
    background:#ff3566;
}

/* Login link */
.login-link{
    margin-top:18px;
    text-align:center;
    font-size:14px;
}

.login-link a{
    color:#5a67ff;
    text-decoration:none;
    font-weight:600;
}
</style>

</head>

<body class="register-body">

<div class="register-container">

    <div class="register-card">

        <h2 class="register-title"><i class="fa-solid fa-user-plus"></i> Create Account</h2>

        <?php if (!empty($error)) : ?>
            <div class="reg-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
            <div class="reg-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="post">

            <div class="reg-input-group">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="name" placeholder="Full Name" required>
            </div>

            <div class="reg-input-group">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>

            <div class="reg-input-group">
                <i class="fa-solid fa-phone"></i>
                <input type="text" name="phone" placeholder="Phone Number">
            </div>

            <div class="reg-input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <div class="reg-input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="confirm" placeholder="Confirm Password" required>
            </div>

            <button type="submit" class="register-btn">
                <i class="fa-solid fa-user-check"></i> Register
            </button>

            <p class="login-link">Already have an account?  
                <a href="login.php">Login Here</a>
            </p>

        </form>

    </div>

</div>

</body>
</html>
