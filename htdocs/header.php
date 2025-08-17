<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strefa Filmowa</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <!-- Flaticon / Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Favicon - Używamy logo.png -->
    <link rel="icon" type="image/png" href="assets/images/logo.png">
</head>
<body>
<header>
    <div class="logo"></div>
    <nav>
        <a href="index.php"><i class="fas fa-film"></i> <span>Filmy</span></a>
        <a href="index.php?type=series"><i class="fas fa-tv"></i> <span>Seriale</span></a>
        <a href="index.php?type=restream"><i class="fas fa-satellite"></i> <span>Streamy</span></a>
        <a href="regulamin.php"><i class="fas fa-book"></i> <span>Regulamin</span></a>
        <a href="kontakt.php"><i class="fas fa-envelope"></i> <span>Kontakt</span></a>
        
        <?php if (isset($_SESSION['admin_logged_in'])): ?>
            <a href="admin.php"><i class="fas fa-user-cog"></i> <span>Panel Admina</span></a>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['user_logged_in']) || isset($_SESSION['admin_logged_in'])): ?>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Wyloguj</span></a>
        <?php endif; ?>
    </nav>
</header>
    <main>