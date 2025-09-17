<?php
// Admin logout script. Clears the session and redirects back to the admin login page.
session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit();
?>