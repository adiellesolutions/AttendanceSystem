<?php
session_start();

// remove all session data
$_SESSION = [];
session_destroy();

// redirect to login page
header("Location: ../../frontend/pages/login.php");
exit;
