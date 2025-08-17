<?php
session_start();
include 'includes/db.php';

// Jeśli użytkownik nie jest zalogowany, przekieruj do logowania
if (!isset($_SESSION['user_logged_in']) && !isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Pobierz typ z URL lub ustaw domyślny
$type = $_GET['type'] ?? 'movie';
$allowed_types = ['movie', 'series', 'restream'];
if (!in_array($type, $allowed_types)) {
    $type = 'movie';
}

try {
    // Pobierz wszystkie pozycje danego typu
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE type = ? ORDER BY id DESC");
    $stmt->execute([$type]);
    $movies = $stmt->fetchAll();
} catch (Exception $e) {
    die("Błąd bazy danych: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Strefa Filmowa</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <!-- Flaticon / Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
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
            
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Wyloguj</span></a>
        </nav>
    </header>

    <main>
        <h2>
            <?php
            switch ($type) {
                case 'series': echo '📺 Seriale'; break;
                case 'restream': echo '📡 Streamy'; break;
                default: echo '🎬 Filmy';
            }
            ?>
        </h2>

        <?php if (empty($movies)): ?>
            <div class="no-data">
                <i class="fas fa-film"></i>
                <h3>Brak pozycji w tej kategorii</h3>
                <p>Dodaj pierwszy film/serial/stream w panelu administratora.</p>
            </div>
        <?php else: ?>
            <div class="movies-grid">
                <?php foreach ($movies as $movie): ?>
                <div class="movie-card">
                    <img src="<?= htmlspecialchars($movie['thumbnail_url']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>" onerror="this.style.display='none'">
                    <div class="overlay">
                        <?php if ($movie['type'] === 'series'): ?>
                            <a href="series.php?id=<?= $movie['id'] ?>" class="play-btn"><i class="fas fa-play"></i></a>
                        <?php elseif ($movie['type'] === 'restream'): ?>
                            <a href="stream.php?id=<?= $movie['id'] ?>" class="play-btn"><i class="fas fa-play"></i></a>
                        <?php else: ?>
                            <a href="player.php?id=<?= $movie['id'] ?>" class="play-btn"><i class="fas fa-play"></i></a>
                        <?php endif; ?>
                    </div>
                    <h3><?= htmlspecialchars($movie['title']) ?></h3>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2025 Strefa Filmowa</p>
    </footer>
</body>
</html>