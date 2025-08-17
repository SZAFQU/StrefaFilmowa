<?php
session_start();
include 'includes/db.php';

// Jeśli użytkownik jest zalogowany, pozwól na dostęp
// Jeśli nie, przekieruj do logowania użytkownika
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$movies = $pdo->query("SELECT * FROM movies ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Admina – Strefa Filmowa</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <!-- Flaticon / Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
</head>
<body class="admin-body">
    <header>
        <div class="logo"></div>
        <nav>
            <a href="index.php"><i class="fas fa-home"></i> <span>Strona główna</span></a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Wyloguj</span></a>
        </nav>
    </header>

    <main>
        <h2><i class="fas fa-plus"></i> Dodaj nowy film/serial/stream</h2>
        
        <form action="add_movie.php" method="POST" class="add-form">
            <div class="form-group">
                <label for="title">Tytuł:</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="thumbnail_url">URL miniaturki:</label>
                <input type="text" id="thumbnail_url" name="thumbnail_url" required>
            </div>
            
            <div class="form-group">
                <label for="type">Typ:</label>
                <select id="type" name="type" required>
                    <option value="">-- Wybierz typ --</option>
                    <option value="movies">🎬 Film</option>
                    <option value="series">📺 Serial</option>
                    <option value="restream">📡 Stream</option>
                </select>
            </div>
            
            <!-- Pole dla filmu -->
            <div class="form-group movie-fields" style="display: none;">
                <label for="video_url">URL filmu (.mkv, .m3u8):</label>
                <input type="text" id="video_url" name="video_url">
                
                <label for="filmweb_url">Link do Filmwebu (opcjonalnie):</label>
                <input type="text" id="filmweb_url" name="filmweb_url">
            </div>
            
<!-- Pola dla seriali -->
<div class="form-group series-fields" style="display: none;">
    <label for="series_filmweb_url">Link do Filmwebu dla całego serialu (opcjonalnie):</label>
    <input type="text" id="series_filmweb_url" name="filmweb_url" placeholder="https://www.filmweb.pl/serial/...">
    
    <label>Odcinki serialu:</label>
    <div id="episodes-container">
        <div class="episode-entry">
            <input type="number" name="episodes[number][]" placeholder="Nr odcinka" style="width: 20%; display: inline-block;">
            <input type="text" name="episodes[title][]" placeholder="Tytuł odcinka" style="width: 39%; display: inline-block; margin: 0 0.5%;">
            <input type="text" name="episodes[url][]" placeholder="URL odcinka" style="width: 34%; display: inline-block;">
            <button type="button" class="remove-episode btn-small delete-btn" style="width: 5%; display: inline-block; padding: 10px;"><i class="fas fa-times"></i></button>
        </div>
    </div>
    <button type="button" id="add-episode" class="btn"><i class="fas fa-plus"></i> Dodaj odcinek</button>
        </div>
            
            <!-- Pola dla streamów -->
            <div class="form-group stream-fields" style="display: none;">
                <label>Źródła streamu:</label>
                <div id="sources-container">
                    <div class="source-entry">
                        <input type="text" name="sources[name][]" placeholder="Nazwa źródła" style="width: 48%; display: inline-block; margin-right: 1%;">
                        <input type="text" name="sources[url][]" placeholder="URL źródła" style="width: 45%; display: inline-block;">
                        <input type="text" name="sources[filmweb][]" placeholder="Filmweb (opcj.)" style="width: 48%; display: inline-block; margin: 5px 1% 5px 0;">
                        <button type="button" class="remove-source btn-small delete-btn" style="width: 5%; display: inline-block; padding: 10px;"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <button type="button" id="add-source" class="btn"><i class="fas fa-plus"></i> Dodaj źródło</button>
            </div>
            
            <button type="submit"><i class="fas fa-save"></i> Dodaj</button>
        </form>

        <h2><i class="fas fa-list"></i> Lista pozycji</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tytuł</th>
                        <th>Typ</th>
                        <th>Miniaturka</th>
                        <th>Szczegóły</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movies as $m): ?>
                    <tr>
                        <td><?= $m['id'] ?></td>
                        <td><?= htmlspecialchars($m['title']) ?></td>
                        <td>
                            <?php
                            switch ($m['type']) {
                                case 'series': echo '📺 Serial'; break;
                                case 'restream': echo '📡 Stream'; break;
                                default: echo '🎬 Film';
                            }
                            ?>
                        </td>
                        <td>
                            <img src="<?= htmlspecialchars($m['thumbnail_url']) ?>" alt="Miniaturka" class="thumb-preview" onerror="this.style.display='none'">
                        </td>
                        <td>
                            <?php if ($m['type'] === 'series'): ?>
                                <?php
                                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM series_episodes WHERE movie_id = ?");
                                $stmt->execute([$m['id']]);
                                $count = $stmt->fetch()['count'];
                                ?>
                                <strong>Odcinki:</strong> <?= $count ?>
                            <?php elseif ($m['type'] === 'restream'): ?>
                                <?php
                                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM stream_sources WHERE movie_id = ?");
                                $stmt->execute([$m['id']]);
                                $count = $stmt->fetch()['count'];
                                ?>
                                <strong>Źródła:</strong> <?= $count ?>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($m['video_url']) ?>" target="_blank" class="btn-small">
                                    <i class="fas fa-external-link-alt"></i> Otwórz
                                </a>
                                <?php if (!empty($m['filmweb_url'])): ?>
                                    <a href="<?= htmlspecialchars($m['filmweb_url']) ?>" target="_blank" class="btn-small" style="background: #666;">
                                        <i class="fas fa-external-link-alt"></i> Filmweb
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_movie.php?id=<?= $m['id'] ?>" class="btn-small edit-btn">
                                <i class="fas fa-edit"></i> Edytuj
                            </a>
                            <a href="delete_movie.php?id=<?= $m['id'] ?>" onclick="return confirm('Na pewno usunąć?\n\n<?= htmlspecialchars($m['title']) ?>')" class="btn-small delete-btn">
                                <i class="fas fa-trash-alt"></i> Usuń
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($movies)): ?>
        <div class="no-data">
            <i class="fas fa-film"></i>
            <h3>Brak pozycji w bazie danych</h3>
            <p>Dodaj pierwszy film/serial/stream w panelu administratora.</p>
        </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2025 Strefa Filmowa | Panel Administratora</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            const movieFields = document.querySelector('.movie-fields');
            const seriesFields = document.querySelector('.series-fields');
            const streamFields = document.querySelector('.stream-fields');
            
            function toggleFields() {
                const type = typeSelect.value;
                movieFields.style.display = type === 'movies' ? 'block' : 'none';
                seriesFields.style.display = type === 'series' ? 'block' : 'none';
                streamFields.style.display = type === 'restream' ? 'block' : 'none';
            }
            
            typeSelect.addEventListener('change', toggleFields);
            toggleFields();
            
            // Dodawanie odcinków
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
            
            // Dodawanie źródeł
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