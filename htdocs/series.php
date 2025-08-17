<?php
session_start();
if (!isset($_SESSION['user_logged_in']) && !isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php';

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ? AND type = 'series'");
$stmt->execute([$id]);
$series = $stmt->fetch();

if (!$series) {
    die("Serial nie znaleziony.");
}

$stmt = $pdo->prepare("SELECT * FROM series_episodes WHERE movie_id = ? ORDER BY episode_number ASC");
$stmt->execute([$id]);
$episodes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($series['title']) ?> – Strefa Filmowa</title>
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <!-- Flaticon / Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.min.js"></script>
</head>
<body>
<header>
    <div class="logo"></div>
    <nav>
        <a href="index.php"><i class="fas fa-arrow-left"></i> <span>Powrót</span></a>
        <?php if (!empty($series['filmweb_url'])): ?>
            <a href="<?= htmlspecialchars($series['filmweb_url']) ?>" target="_blank"><i class="fas fa-external-link-alt"></i> <span>Filmweb</span></a>
        <?php endif; ?>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Wyloguj</span></a>
    </nav>
</header>

<main>
    <h2><i class="fas fa-tv"></i> <?= htmlspecialchars($series['title']) ?></h2>
    
    <div class="episodes-list">
        <h3><i class="fas fa-list-ol"></i> Lista odcinków</h3>
        <?php foreach ($episodes as $ep): ?>
            <a href="player.php?id=<?= $ep['id'] ?>&type=episode" class="btn-small">
                <i class="fas fa-play"></i> 
                Odcinek <?= $ep['episode_number'] ?>: <?= htmlspecialchars($ep['episode_title']) ?>
                <?php if (!empty($ep['filmweb_url'])): ?>
                    <i class="fas fa-external-link-alt" style="margin-left: 8px; font-size: 0.8rem;"></i>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const playerElement = document.getElementById('player');
    const player = new Plyr(playerElement, {
        controls: ['play', 'progress', 'current-time', 'mute', 'volume', 'fullscreen'],
        settings: ['quality', 'speed']
    });

    const videoSrc = "<?= htmlspecialchars($video_url) ?>";
    const supported = Hls.isSupported();
    const videoType = videoSrc.includes('.m3u8') ? 'application/x-mpegURL' : 'video/mp4';

    if (videoSrc.includes('.m3u8')) {
        if (supported) {
            const hls = new Hls();
            hls.loadSource(videoSrc);
            hls.attachMedia(player.media);
            hls.on(Hls.Events.MANIFEST_PARSED, () => {
                player.source = { type: 'video', sources: [{ src: videoSrc, type: 'application/x-mpegURL' }] };
            });
        } else {
            player.source = { type: 'video', sources: [{ src: videoSrc, type: 'application/x-mpegURL' }] };
        }
    } else {
        player.source = { type: 'video', sources: [{ src: videoSrc, type: 'video/mp4' }] };
    }
});
</script>
</body>
</html>