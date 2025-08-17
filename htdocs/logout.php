<?php
session_start();
session_destroy();
// Przekieruj do strony logowania po wylogowaniu
header("Location: login.php");
exit();
?>