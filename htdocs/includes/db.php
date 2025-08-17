<?php
// === DANE DO BAZY DANYCH – PODMIENIAJ NA WŁASNE ===
$host = 'sql312.byethost14.com';         // Host z panelu
$db   = 'b14_39676884_Admin'; // PEŁNA NAZWA BAZY Z PANELU
$user = 'b14_39676884';                 // Nazwa użytkownika z panelu
$pass = 's89mc1x6';              // Twoje hasło z panelu
$charset = 'utf8mb4';

// === NIE DOTYKAJ PONIŻSZEGO ===
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}
?>