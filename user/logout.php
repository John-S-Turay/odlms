<?php
// Start a session to manage user data
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
if (session_destroy()) {
    // Redirect to the login page
    header('Location: login.php');
    exit(); // Stop further execution
} else {
    // Handle session destruction failure
    echo "<script>alert('Failed to log out. Please try again.');</script>";
    echo "<script>window.location.href = 'dashboard.php';</script>";
}
?>