<?php
$checks = [];
$checks['php_version'] = version_compare(PHP_VERSION, '8.0.0', '>=');
$checks['ext_mysqli']  = extension_loaded('mysqli');
$checks['ext_mbstring']= extension_loaded('mbstring');
$checks['ext_json']    = extension_loaded('json');

$paths = [
  'writable'         => __DIR__ . '/../writable',
  'writable/uploads' => __DIR__ . '/../writable/uploads',
  'writable/logs'    => __DIR__ . '/../writable/logs',
  '.env.php (root)'  => __DIR__ . '/../.env.php',
];
$perms = [];
foreach ($paths as $label => $path) {
    $perms[$label] = is_writable($path) || (!file_exists($path) && is_writable(dirname($path)));
}
return [$checks, $perms];
