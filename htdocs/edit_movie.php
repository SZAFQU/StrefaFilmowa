<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php';

// Pobierz dane filmu/serialu/streamu
$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$id]);
$movie = $stmt->fetch();

if (!$movie) {
    die("Pozycja nie znaleziona.");
}

// Pobierz odcinki seriali
$episodes = [];
if ($movie['type'] === 'series') {
    $stmt = $pdo->prepare("SELECT * FROM series_episodes WHERE movie_id = ? ORDER BY episode_number ASC");
    $stmt->execute([$id]);
    $episodes = $stmt->fetchAll();
}

// Pobierz źródła streamów
$sources = [];
if ($movie['type'] === 'restream') {
    $stmt = $pdo->prepare("SELECT * FROM stream_sources WHERE movie_id = ? ORDER BY id ASC");
    $stmt->execute([$id]);
    $sources = $stmt->fetchAll();
}

// Obsługa aktualizacji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $thumbnail_url = trim($_POST['thumbnail_url']);
    $type = $movie['type']; // Typ nie zmienia się
    $filmweb_url = !empty($_POST['filmweb_url']) ? trim($_POST['filmweb_url']) : null;
    
    if (empty($title) || empty($thumbnail_url)) {
        $error = "Tytuł i miniaturka są wymagane.";
    } else {
        try {
            $pdo->beginTransaction();
            
            // Aktualizacja głównego rekordu
            if ($type === 'movie') {
                $video_url = trim($_POST['video_url']);
                $stmt = $pdo->prepare("UPDATE movies SET title = ?, thumbnail_url = ?, video_url = ?, filmweb_url = ? WHERE id = ?");
                $stmt->execute([$title, $thumbnail_url, $video_url, $filmweb_url, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE movies SET title = ?, thumbnail_url = ?, filmweb_url = ? WHERE id = ?");
                $stmt->execute([$title, $thumbnail_url, $filmweb_url, $id]);
            }
            
            // --- Obsługa odcinków seriali ---
            if ($type === 'series') {
                // Usuń wszystkie istniejące odcinki
                $pdo->prepare("DELETE FROM series_episodes WHERE movie_id = ?")->execute([$id]);
                
                $episode_numbers = $_POST['episodes']['number'] ?? [];
                $episode_titles = $_POST['episodes']['title'] ?? [];
                $episode_urls = $_POST['episodes']['url'] ?? [];
                $episode_filmwebs = $_POST['episodes']['filmweb'] ?? [];
                
                for ($i = 0; $i < count($episode_numbers); $i++) {
                    $number = trim($episode_numbers[$i]);
                    $title_ep = trim($episode_titles[$i]);
                    $url = trim($episode_urls[$i]);
                    $filmweb = !empty($episode_filmwebs[$i]) ? trim($episode_filmwebs[$i]) : null;
                    
                    if (!empty($number) && !empty($title_ep) && !empty($url)) {
                        $stmt = $pdo->prepare("INSERT INTO series_episodes (movie_id, episode_number, episode_title, video_url, filmweb_url) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$id, $number, $title_ep, $url, $filmweb]);
                    }
                }
            }
            
            // --- Obsługa źródeł streamów ---
            if ($type === 'restream') {
                // Usuń wszystkie istniejące źródła
                $pdo->prepare("DELETE FROM stream_sources WHERE movie_id = ?")->execute([$id]);
                
                $source_names = $_POST['sources']['name'] ?? [];
                $source_urls = $_POST['sources']['url'] ?? [];
                $source_filmwebs = $_POST['sources']['filmweb'] ?? [];
                
                for ($i = 0; $i < count($source_names); $i++) {
                    $name = trim($source_names[$i]);
                    $url = trim($source_urls[$i]);
                    $filmweb = !empty($source_filmwebs[$i]) ? trim($source_filmwebs[$i]) : null;
                    
                    if (!empty($name) && !empty($url)) {
                        $stmt = $pdo->prepare("INSERT INTO stream_sources (movie_id, source_name, video_url, filmweb_url) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$id, $name, $url, $filmweb]);
                    }
                }
            }
            
            $pdo->commit();
            header("Location: admin.php?edited=1");
            exit();
            
        } catch (Exception $e) {
            $pdo->rollback();
            $error = "Błąd przy aktualizacji: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edytuj - <?= htmlspecialchars($movie['title']) ?></title>
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
            <a href="index.php"><i class="fas fa-home"></i> <span>Strona główna</span></a>
            <a href="admin.php"><i class="fas fa-arrow-left"></i> <span>Powrót do panelu</span></a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Wyloguj</span></a>
        </nav>
    </header>

    <main>
        <h2><i class="fas fa-edit"></i> Edytuj: <?= htmlspecialchars($movie['title']) ?></h2>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="edit_movie.php?id=<?= $movie['id'] ?>" method="POST" class="add-form">
            <div class="form-group">
                <label for="title">Tytuł:</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($movie['title']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="thumbnail_url">URL miniaturki:</label>
                <input type="text" id="thumbnail_url" name="thumbnail_url" value="<?= htmlspecialchars($movie['thumbnail_url']) ?>" required>
            </div>
            
            <?php if ($movie['type'] === 'movie'): ?>
            <div class="form-group">
                <label for="video_url">URL filmu (.mkv, .m3u8):</label>
                <input type="text" id="video_url" name="video_url" value="<?= htmlspecialchars($movie['video_url']) ?>">
                
                <label for="filmweb_url">Link do Filmwebu (opcjonalnie):</label>
                <input type="text" id="filmweb_url" name="filmweb_url" value="<?= htmlspecialchars($movie['filmweb_url'] ?? '') ?>">
            </div>
            <?php endif; ?>
            
            <?php if ($movie['type'] === 'series'): ?>
            <div class="form-group">
                <label for="filmweb_url">Link do Filmwebu dla całego serialu (opcjonalnie):</label>
                <input type="text" id="filmweb_url" name="filmweb_url" value="<?= htmlspecialchars($movie['filmweb_url'] ?? '') ?>">
                
                <label>Odcinki serialu:</label>
                <div id="episodes-container">
                    <?php if (!empty($episodes)): ?>
                        <?php foreach ($episodes as $ep): ?>
                        <div class="episode-entry">
                            <input type="number" name="episodes[number][]" placeholder="Nr odcinka" value="<?= htmlspecialchars($ep['episode_number']) ?>" style="width: 20%; display: inline-block;">
                            <input type="text" name="episodes[title][]" placeholder="Tytuł odcinka" value="<?= htmlspecialchars($ep['episode_title']) ?>" style="width: 39%; display: inline-block; margin: 0 0.5%;">
                            <input type="text" name="episodes[url][]" placeholder="URL odcinka" value="<?= htmlspecialchars($ep['video_url']) ?>" style="width: 34%; display: inline-block;">
                            <input type="text" name="episodes[filmweb][]" placeholder="Filmweb (opcj.)" value="<?= htmlspecialchars($ep['filmweb_url'] ?? '') ?>" style="width: 39%; display: inline-block; margin: 5px 0.5%;">
                            <button type="button" class="remove-episode btn-small delete-btn" style="width: 5%; display: inline-block; padding: 10px;"><i class="fas fa-times"></i></button>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="episode-entry">
                            <input type="number" name="episodes[number][]" placeholder="Nr odcinka" style="width: 20%; display: inline-block;">
                            <input type="text" name="episodes[title][]" placeholder="Tytuł odcinka" style="width: 39%; display: inline-block; margin: 0 0.5%;">
                            <input type="text" name="episodes[url][]" placeholder="URL odcinka" style="width: 34%; display: inline-block;">
                            <input type="text" name="episodes[filmweb][]" placeholder="Filmweb (opcj.)" style="width: 39%; display: inline-block; margin: 5px 0.5%;">
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" id="add-episode" class="btn"><i class="fas fa-plus"></i> Dodaj odcinek</button>
            </div>
            <?php endif; ?>
            
            <?php if ($movie['type'] === 'restream'): ?>
            <div class="form-group">
                <label for="filmweb_url">Link do Filmwebu dla całego streamu (opcjonalnie):</label>
                <input type="text" id="filmweb_url" name="filmweb_url" value="<?= htmlspecialchars($movie['filmweb_url'] ?? '') ?>">
                
                <label>Źródła streamu:</label>
                <div id="sources-container">
                    <?php if (!empty($sources)): ?>
                        <?php foreach ($sources as $src): ?>
                        <div class="source-entry">
                            <input type="text" name="sources[name][]" placeholder="Nazwa źródła" value="<?= htmlspecialchars($src['source_name']) ?>" style="width: 48%; display: inline-block; margin-right: 1%;">
                            <input type="text" name="sources[url][]" placeholder="URL źródła" value="<?= htmlspecialchars($src['video_url']) ?>" style="width: 45%; display: inline-block;">
                            <input type="text" name="sources[filmweb][]" placeholder="Filmweb (opcj.)" value="<?= htmlspecialchars($src['filmweb_url'] ?? '') ?>" style="width: 48%; display: inline-block; margin: 5px 1% 5px 0;">
                            <button type="button" class="remove-source btn-small delete-btn" style="width: 5%; display: inline-block; padding: 10px;"><i class="fas fa-times"></i></button>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="source-entry">
                            <input type="text" name="sources[name][]" placeholder="Nazwa źródła" style="width: 49%; display: inline-block;">
                            <input type="text" name="sources[url][]" placeholder="URL źródła" style="width: 49%; display: inline-block; margin-left: 1%;">
                            <input type="text" name="sources[filmweb][]" placeholder="Filmweb (opcj.)" style="width: 49%; display: inline-block; margin: 5px 0;">
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" id="add-source" class="btn"><i class="fas fa-plus"></i> Dodaj źródło</button>
            </div>
            <?php endif; ?>
            
            <button type="submit"><i class="fas fa-save"></i> Zapisz zmiany</button>
        </form>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dodawanie odcinków
            <?php if ($movie['type'] === 'series'): ?>
            document.getElementById('add-episode').addEventListener('click', function() {
                const container = document.getElementById('episodes-container');
                const newEntry = document.createElement('div');
                newEntry.className = 'episode-entry';
                newEntry.innerHTML = `
                    <input type="number" name="episodes[number][]" placeholder="Nr odcinka" style="width: 20%; display: inline-block;">
                    <input type="text" name="episodes[title][]" placeholder="Tytuł odcinka" style="width: 39%; display: inline-block; margin: 0 0.5%;">
                    <input type="text" name="episodes[url][]" placeholder="URL odcinka" style="width: 34%; display: inline-block;">
                    <input type="text" name="episodes[filmweb][]" placeholder="Filmweb (opcj.)" style="width: 39%; display: inline-block; margin: 5px 0.5%;">
                    <button type="button" class="remove-episode btn-small delete-btn" style="width: 5%; display: inline-block; padding: 10px;"><i class="fas fa-times"></i></button>
                `;
                container.appendChild(newEntry);
            });
            <?php endif; ?>
            
            // Dodawanie źródeł
            <?php if ($movie['type'] === 'restream'): ?>
            document.getElementById('add-source').addEventListener('click', function() {
                const container = document.getElementById('sources-container');
                const newEntry = document.createElement('div');
                newEntry.className = 'source-entry';
                newEntry.innerHTML = `
                    <input type="text" name="sources[name][]" placeholder="Nazwa źródła" style="width: 48%; display: inline-block; margin-right: 1%;">
                    <input type="text" name="sources[url][]" placeholder="URL źródła" style="width: 45%; display: inline-block;">
                    <input type="text" name="sources[filmweb][]" placeholder="Filmweb (opcj.)" style="width: 48%; display: inline-block; margin: 5px 1% 5px 0;">
                    <button type="button" class="remove-source btn-small delete-btn" style="width: 5%; display: inline-block; padding: 10px;"><i class="fas fa-times"></i></button>
                `;
                container.appendChild(newEntry);
            });
            <?php endif; ?>
            
            // Usuwanie odcinków/źródeł
            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-episode')) {
                    e.target.closest('.episode-entry').remove();
                }
                if (e.target.closest('.remove-source')) {
                    e.target.closest('.source-entry').remove();
                }
            });
        });
    </script>
</body>
</html>