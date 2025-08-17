<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php';
$id = (int)$_GET['id'];
$pdo->prepare("DELETE FROM movies WHERE id = ?")->execute([$id]);

header("Location: admin.php");
exit();
?>