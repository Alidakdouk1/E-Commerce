<?php
// Include configuration
require_once 'config.php';

// Call the logout function from user.php
require_once 'user.php';
logoutUser();

// Redirect to home page
header('Location: ../index.php');
exit;
?>
