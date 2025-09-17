<?php
// User logout script. Destroys session and redirects to user login page.
session_start();
session_unset();
session_destroy();
header('Location: login.php');
exit();
?>