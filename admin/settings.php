<?php
require __DIR__ . '/../bootstrap.php';
hs_require_admin();
$settings = hs_settings();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = hs_db();
    $site_title = trim($_POST['site_title'] ?? '');
    $tagline = trim($_POST['tagline'] ?? '');
    $logo = trim($_POST['logo'] ?? '');
    $theme = strtolower(trim($_POST['theme'] ?? 'dark'));
    if (!in_array($theme, ['light', 'dark'], true)) {
        $theme = 'dark';
    }

    $pairs = [
        'site_title' => $site_title !== '' ? $site_title : HS_APP_NAME,
        'tagline'    => $tagline,
        'logo'       => $logo,
        'theme'      => $theme,
    ];

    foreach ($pairs as $key => $value) {
        $stmt = mysqli_prepare($db, "INSERT INTO hs_settings (`key`,`value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
        mysqli_stmt_bind_param($stmt, 'ss', $key, $value);
        mysqli_stmt_execute($stmt);
    }

    $msg = 'Site settings updated.';
    $settings = hs_settings(true);
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Site Settings – NEWS HDSPTV</title>
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
</head>
<body style="max-width:800px;margin:20px auto;padding:0 16px;">
  <h1>Site Settings</h1>
  <?php if ($msg): ?><div style="color:green; margin-bottom:12px;"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <form method="post">
    <label>Site Title</label><br>
    <input type="text" name="site_title" style="width:100%;" value="<?= htmlspecialchars($settings['site_title'] ?? HS_APP_NAME) ?>"><br><br>

    <label>Tagline</label><br>
    <input type="text" name="tagline" style="width:100%;" value="<?= htmlspecialchars($settings['tagline'] ?? '') ?>"><br><br>

    <label>Logo URL (optional)</label><br>
    <input type="text" name="logo" style="width:100%;" value="<?= htmlspecialchars($settings['logo'] ?? '') ?>"><br>
    <small>Provide a full URL or path to your logo image.</small><br><br>

    <label>Default Theme</label><br>
    <select name="theme" style="width:100%;">
      <option value="dark" <?= ($settings['theme'] ?? 'dark') === 'dark' ? 'selected' : '' ?>>Dark</option>
      <option value="light" <?= ($settings['theme'] ?? 'dark') === 'light' ? 'selected' : '' ?>>Light</option>
    </select><br><br>

    <button type="submit">Save Settings</button>
  </form>
</body>
</html>
