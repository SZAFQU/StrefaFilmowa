<?php
// proxy.php
if (!isset($_GET['url'])) {
    http_response_code(400);
    die("Brak URL");
}

$url = $_GET['url'];

// Walidacja URL
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    die("Nieprawidłowy URL");
}

// Ustaw nagłówki CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Pobierz zawartość
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

// Sprawdź czy zapytanie się powiodło
if ($response === false || $httpCode >= 400) {
    http_response_code($httpCode ?: 500);
    die("Błąd pobierania pliku");
}

// Ustaw typ zawartości i wyślij odpowiedź
header('Content-Type: ' . ($contentType ?: 'application/octet-stream'));
echo $response;
?>