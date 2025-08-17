<?php
session_start();
if (!isset($_SESSION['user_logged_in']) && !isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php';

$id = (int)$_GET['id'];
$type = $_GET['type'] ?? 'movie';

// Pobierz dane wideo
if ($type === 'episode') {
    $stmt = $pdo->prepare("SELECT * FROM series_episodes WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    
    if (!$item) {
        die("Odcinek nie znaleziony.");
    }
    
    $title = $item['episode_title'];
    $video_url = $item['video_url'];
    
} elseif ($type === 'source') {
    $stmt = $pdo->prepare("SELECT * FROM stream_sources WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    
    if (!$item) {
        die("Źródło nie znalezione.");
    }
    
    $title = $item['source_name'];
    $video_url = $item['video_url'];
    
} else {
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ? AND type = 'movie'");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    
    if (!$item) {
        die("Film nie znaleziony.");
    }
    
    $title = $item['title'];
    $video_url = $item['video_url'];
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Odtwarzanie: <?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/plyr/3.7.8/plyr.css" />
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
            <?php if (!empty($item['filmweb_url'])): ?>
                <a href="<?= htmlspecialchars($item['filmweb_url']) ?>" target="_blank"><i class="fas fa-external-link-alt"></i> <span>Filmweb</span></a>
            <?php endif; ?>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Wyloguj</span></a>
        </nav>
    </header>

    <main>
        <div class="player-container">
            <h2 class="player-title"><?= htmlspecialchars($title) ?></h2>
            <div class="plyr__video-wrapper">
                <video id="player" class="plyr" playsinline controls></video>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/plyr/3.7.8/plyr.polyfilled.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
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