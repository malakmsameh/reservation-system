<?php
require_once 'config.php';

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Start a new session just to pass the success message
session_start();
$_SESSION['message'] = "<div class='alert success'>You have been logged out.</div>";

header("Location: login.php");
exit();
?>