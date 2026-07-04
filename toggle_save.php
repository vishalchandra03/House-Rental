
<?php
session_start();
require 'config.php';


// Check login
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo 'login';
    exit;
}

// Validate POST data
if (!isset($_POST['house_id']) || !is_numeric($_POST['house_id'])) {
    echo 'error';
    exit;
}

$user_id  = (int) $_SESSION['user_id'];
$house_id = (int) $_POST['house_id'];

// Check if already saved
$stmt = $conn->prepare(
    "SELECT id FROM saved_houses WHERE user_id = ? AND house_id = ?"
);
$stmt->bind_param("ii", $user_id, $house_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Remove from saved
    $stmt->close();

    $del = $conn->prepare(
        "DELETE FROM saved_houses WHERE user_id = ? AND house_id = ?"
    );
    $del->bind_param("ii", $user_id, $house_id);
    $del->execute();
    $del->close();

    echo 'removed';
} else {
    // Save listing
    $stmt->close();

    $ins = $conn->prepare(
        "INSERT INTO saved_houses (user_id, house_id) VALUES (?, ?)"
    );
    $ins->bind_param("ii", $user_id, $house_id);
    $ins->execute();
    $ins->close();

    echo 'saved';
}
