<?php

// START SESSION (VERY IMPORTANT)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "house_rental";

$conn = mysqli_connect($host, $user, $pass, $db, );

if (!$conn) {
    die("DB Connection failed: " . mysqli_connect_error());
}
// LOGIN CHECK FUNCTION
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
?>

