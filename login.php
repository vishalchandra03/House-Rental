<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'];
    $pass  = md5($_POST['password']); // 🔥 MD5 BACK

    $stmt = $conn->prepare(
        "SELECT id, name, role FROM users WHERE email=? AND password=?"
    );
    $stmt->bind_param("ss", $email, $pass);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['name']    = $row['name'];
        $_SESSION['role']    = $row['role'];

        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="user-login-body">

<div class="login-wrapper">

    <div class="login-left">
        <h1 class="login-brand">House<span>Rental</span></h1>
        <p class="login-tagline">Welcome back! Sign in and continue exploring amazing homes.</p>
    </div>

    <div class="login-right">

        <div class="user-login-card">

            <h2 class="user-login-title"><i class="fa-solid fa-door-open"></i> Sign In</h2>

            <?php if (!empty($error)): ?>
                <div class="user-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post">

                <div class="user-input-group">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>

                <div class="user-input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <button type="submit" class="user-login-btn">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </button>

                <p class="register-link">
                    New here? <a href="register.php">Create an account</a>
                </p>

            </form>
        </div>

    </div>

</div>

</body>
</html>
