<?php
// NEWS HDSPTV - config bootstrap (V20 enterprise pro)

$envFile = __DIR__ . '/../.env.php';
if (!file_exists($envFile)) {
    define('HS_INSTALLED', false);
    return;
}

require $envFile;

define('HS_INSTALLED', true);

define('HS_APP_NAME', $HS_APP_NAME ?? 'NEWS HDSPTV');
define('HS_BASE_URL', rtrim($HS_BASE_URL ?? 'https://hdsptv.com/', '/') . '/');

$HS_DB_HOST = $HS_DB_HOST ?? 'localhost';
$HS_DB_NAME = $HS_DB_NAME ?? 'news_hdsptv';
$HS_DB_USER = $HS_DB_USER ?? 'root';
$HS_DB_PASS = $HS_DB_PASS ?? '';

$hs_db = @mysqli_connect($HS_DB_HOST, $HS_DB_USER, $HS_DB_PASS, $HS_DB_NAME);
if (!$hs_db) {
    if (php_sapi_name() === 'cli') {
        die('Database connection failed: ' . mysqli_connect_error());
    }
    echo "<h2>Database connection failed</h2><p>Please check .env.php.</p>";
    exit;
}
mysqli_set_charset($hs_db, 'utf8mb4');

function hs_db() {
    global $hs_db;
    return $hs_db;
}
