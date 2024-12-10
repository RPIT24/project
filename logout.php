<?php
session_start(); // Start the session to manage user login status
include 'db.php'; // Include database connection filesession_start(); // Start the session

// Destroy all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to login page
header("Location:login.php");
exit;
?>
