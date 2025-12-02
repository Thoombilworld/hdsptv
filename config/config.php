<?php
// NEWS HDSPTV - config bootstrap (V20 enterprise pro)

$envFile = __DIR__ . '/../.env.php';
$defaultBase = (isset($_SERVER['HTTP_HOST'])
    ? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/'
    : 'http://localhost/');

// Default handles when the installer has not been completed yet.
$hs_db = null;
if (!file_exists($envFile)) {
    define('HS_INSTALLED', false);
    define('HS_BASE_URL', $defaultBase);

    if (!function_exists('hs_db')) {
        function hs_db() {
            return null;
        }
    }
    return;
}

require $envFile;

define('HS_INSTALLED', true);

define('HS_APP_NAME', $HS_APP_NAME ?? 'NEWS HDSPTV');
define('HS_BASE_URL', rtrim($HS_BASE_URL ?? $defaultBase, '/') . '/');

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
