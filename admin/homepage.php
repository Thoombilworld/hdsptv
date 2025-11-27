<?php
require __DIR__ . '/../bootstrap.php';
hs_require_admin();
$db = hs_db();
$settings = hs_settings();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = [
      'hp_show_breaking',
      'hp_show_featured',
      'hp_show_trending',
      'hp_show_video',
      'hp_show_gallery',
      'hp_show_ads_sidebar'
    ];
    foreach ($keys as $k) {
        $v = isset($_POST[$k]) ? '1' : '0';
        $stmt = mysqli_prepare($db, "INSERT INTO hs_settings (`key`,`value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
        mysqli_stmt_bind_param($stmt, 'ss', $k, $v);
        mysqli_stmt_execute($stmt);
    }
    $msg = 'Homepage layout updated.';
    $settings = hs_settings();
}
function hp_checked($settings, $key) {
    return !empty($settings[$key]) && $settings[$key] === '1' ? 'checked' : '';
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Homepage Manager – NEWS HDSPTV</title>
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
</head>
<body style="max-width:800px;margin:20px auto;padding:0 16px;">
  <h1>Homepage Layout Manager</h1>
  <?php if ($msg): ?><div style="color:green;"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <form method="post">
    <label><input type="checkbox" name="hp_show_breaking" <?= hp_checked($settings,'hp_show_breaking') ?>> Show Breaking Ticker</label><br>
    <label><input type="checkbox" name="hp_show_featured" <?= hp_checked($settings,'hp_show_featured') ?>> Show Featured Slider</label><br>
    <label><input type="checkbox" name="hp_show_trending" <?= hp_checked($settings,'hp_show_trending') ?>> Show Trending Box</label><br>
    <label><input type="checkbox" name="hp_show_video" <?= hp_checked($settings,'hp_show_video') ?>> Show Video Section</label><br>
    <label><input type="checkbox" name="hp_show_gallery" <?= hp_checked($settings,'hp_show_gallery') ?>> Show Gallery Section</label><br>
    <label><input type="checkbox" name="hp_show_ads_sidebar" <?= hp_checked($settings,'hp_show_ads_sidebar') ?>> Show Sidebar Ads Block</label><br><br>
    <button type="submit">Save Layout</button>
  </form>
</body>
</html>
