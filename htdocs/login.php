<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Strefa Filmowa – Logowanie</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <!-- Flaticon / Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
</head>
<body class="login-body">
    <div class="login-container" id="loginBox">
        <div class="logo"></div>
        <h1>STREFA FILMOWA</h1>
        <p>Wybierz typ konta i zaloguj się</p>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <form action="process_login.php" method="POST">
            <select name="account_type" required style="width: 100%; padding: 15px; margin: 10px 0; background: rgba(20, 20, 20, 0.8); border: 2px solid rgba(255, 0, 0, 0.3); color: white; border-radius: 10px; font-size: 1rem;">
                <option value="">-- Wybierz typ konta --</option>
                <option value="user">🎬 Użytkownik (Oglądanie filmów)</option>
                <option value="admin">🛡️ Administrator (Zarządzanie treścią)</option>
            </select>
            <input type="text" name="username" placeholder="Login" required>
            <input type="password" name="password" placeholder="Hasło" required>
            <button type="submit"><i class="fas fa-sign-in-alt"></i> Zaloguj się</button>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const box = document.getElementById('loginBox');
            box.style.opacity = '0';
            box.style.transform = 'translateY(30px)';
            setTimeout(() => {
                box.style.transition = 'all 0.8s ease';
                box.style.opacity = '1';
                box.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>