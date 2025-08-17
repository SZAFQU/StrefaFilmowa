<?php
session_start();
if (!isset($_SESSION['user_logged_in']) && !isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php';

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ? AND type = 'restream'");
$stmt->execute([$id]);
$stream = $stmt->fetch();

if (!$stream) {
    die("Stream nie znaleziony.");
}

$stmt = $pdo->prepare("SELECT * FROM stream_sources WHERE movie_id = ? ORDER BY id ASC");
$stmt->execute([$id]);
$sources = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($stream['title']) ?> – Strefa Filmowa</title>
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
            <a href="index.php"><i class="fas fa-arrow-left"></i> <span>Powrót</span></a>
            <?php if (!empty($stream['filmweb_url'])): ?>
                <a href="<?= htmlspecialchars($stream['filmweb_url']) ?>" target="_blank"><i class="fas fa-external-link-alt"></i> <span>Filmweb</span></a>
            <?php endif; ?>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Wyloguj</span></a>
        </nav>
    </header>

    <main>
        <h2><i class="fas fa-satellite"></i> <?= htmlspecialchars($stream['title']) ?></h2>
        
        <?php if (empty($sources)): ?>
            <div class="no-data">
                <i class="fas fa-satellite"></i>
                <h3>Brak źródeł</h3>
                <p>Ten stream nie ma jeszcze żadnych źródeł.</p>
            </div>
        <?php else: ?>
            <div class="sources-list">
                <h3><i class="fas fa-satellite-dish"></i> Dostępne źródła</h3>
                <?php foreach ($sources as $src): ?>
                    <a href="player.php?id=<?= $src['id'] ?>&type=source" class="btn-small">
                        <i class="fas fa-play"></i> 
                        <?= htmlspecialchars($src['source_name']) ?>
                        <?php if (!empty($src['filmweb_url'])): ?>
                            <i class="fas fa-external-link-alt" style="margin-left: 8px; font-size: 0.8rem;"></i>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2025 Strefa Filmowa</p>
    </footer>
</body>
</html>