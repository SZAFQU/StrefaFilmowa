<?php
session_start();

$account_type = $_POST['account_type'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Dane logowania
$users = [
    'user' => 'user123'
];

$admins = [
    'admin' => 'admin123'
];

if ($account_type === 'user' && isset($users[$username]) && $users[$username] === $password) {
    $_SESSION['user_logged_in'] = true;
    header("Location: index.php");
    exit();
} elseif ($account_type === 'admin' && isset($admins[$username]) && $admins[$username] === $password) {
    $_SESSION['admin_logged_in'] = true;
    header("Location: admin.php");
    exit();
} else {
    header("Location: login.php?error=Błędny login, hasło lub typ konta");
    exit();
}
?>