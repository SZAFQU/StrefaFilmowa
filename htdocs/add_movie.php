<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $thumbnail_url = trim($_POST['thumbnail_url']);
    $type = $_POST['type'];
    $filmweb_url = !empty($_POST['filmweb_url']) ? trim($_POST['filmweb_url']) : null;
    
    try {
        $pdo->beginTransaction();
        
        if ($type === 'movie') {
            // Dodawanie filmu
            $video_url = trim($_POST['video_url']);
            
            $stmt = $pdo->prepare("INSERT INTO movies (title, thumbnail_url, video_url, type, filmweb_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $thumbnail_url, $video_url, $type, $filmweb_url]);
            
        } elseif ($type === 'series') {
            // Dodawanie serialu (bez video_url głównego)
            $stmt = $pdo->prepare("INSERT INTO movies (title, thumbnail_url, type, filmweb_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $thumbnail_url, $type, $filmweb_url]);
            $movie_id = $pdo->lastInsertId();
            
            // Dodawanie odcinków
            if (isset($_POST['episodes'])) {
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
                        $stmt->execute([$movie_id, $number, $title_ep, $url, $filmweb]);
                    }
                }
            }
            
        } elseif ($type === 'restream') {
            // Dodawanie streamu (bez video_url głównego)
            $stmt = $pdo->prepare("INSERT INTO movies (title, thumbnail_url, type, filmweb_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $thumbnail_url, $type, $filmweb_url]);
            $movie_id = $pdo->lastInsertId();
            
            // Dodawanie źródeł
            if (isset($_POST['sources'])) {
                $source_names = $_POST['sources']['name'] ?? [];
                $source_urls = $_POST['sources']['url'] ?? [];
                $source_filmwebs = $_POST['sources']['filmweb'] ?? [];
                
                for ($i = 0; $i < count($source_names); $i++) {
                    $name = trim($source_names[$i]);
                    $url = trim($source_urls[$i]);
                    $filmweb = !empty($source_filmwebs[$i]) ? trim($source_filmwebs[$i]) : null;
                    
                    if (!empty($name) && !empty($url)) {
                        $stmt = $pdo->prepare("INSERT INTO stream_sources (movie_id, source_name, video_url, filmweb_url) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$movie_id, $name, $url, $filmweb]);
                    }
                }
            }
        }
        
        $pdo->commit();
        header("Location: admin.php?added=1");
        exit();
        
    } catch (Exception $e) {
        $pdo->rollback();
        die("Błąd przy dodawaniu: " . $e->getMessage());
    }
}

header("Location: admin.php");
exit();
?>