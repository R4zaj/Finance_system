<?php
// index.php
session_start();

// Check if the user is already authenticated
if (isset($_SESSION['user_id'])) {
    // User is logged in, send them to the main system
    header("Location: pages/dashboard.php");
    exit();
} else {
    // User is not logged in, send them to the login screen
    header("Location: login.php");
    exit();
}
?>