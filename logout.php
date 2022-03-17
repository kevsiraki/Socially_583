<?php
// Initialize the session
session_start();

// Unset all of the session/cookie variables
$_SESSION = array();
$_COOKIE = array();

setcookie("username", "", time()-3600);
setcookie("remember", false , time()-3600);

// Destroy the session.
session_destroy();

// Redirect to login page
header("location: login.php");
 
exit;
?>