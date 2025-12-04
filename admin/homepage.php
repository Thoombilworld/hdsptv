<?php
require __DIR__ . '/../bootstrap.php';
hs_require_staff(['admin', 'editor']);

$db = hs_db();
$settings = hs_settings();
$role = hs_staff_role() ?? 'admin';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = [
        'hp_show_breaking',
        'hp_show_featured',
        'hp_show_trending',
        'hp_show_video',
        'hp_show_gallery',
        'hp_show_ads_top',
        'hp_show_ads_inline',
        'hp_show_ads_sidebar'
    ];
    foreach ($keys as $k) {
        $v = isset($_POST[$k]) ? '1' : '0';
        $stmt = mysqli_prepare($db, "INSERT INTO hs_settings (`key`,`value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
        mysqli_stmt_bind_param($stmt, 'ss', $k, $v);
        mysqli_stmt_execute($stmt);
    }
    $msg = 'Homepage layout updated.';
    $settings = hs_settings(true);
}

function hp_checked($settings, $key) {
    return !empty($settings[$key]) && $settings[$key] === '1' ? 'checked' : '';
}

$staff = hs_current_staff();
$role = $staff['role'] ?? 'admin';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Homepage Layout Manager – NEWS HDSPTV</title>
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
</head>
<body class="admin-shell">
  <header class="admin-header">
    <div class="admin-logo">NEWS HDSPTV • <?= strtoupper($role) ?></div>
    <nav class="admin-nav">
      <?php hs_render_admin_nav($role, 'homepage'); ?>
    </nav>
  </header>

  <main class="admin-container">
    <section class="admin-panel admin-hero">
      <div class="admin-pill">Homepage</div>
      <h1 style="margin:6px 0 4px;">Homepage Layout Manager</h1>
      <p class="admin-subtext">Toggle homepage blocks for QA. Changes apply instantly to the ticker, featured slider, trending rail, media widgets, and ad placements.</p>
      <?php if ($msg): ?><div class="admin-message" role="status"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
      <form method="post" class="admin-form" style="margin-top:12px; display:grid; gap:10px;">
        <div class="admin-toggle-row">
          <label style="display:flex; gap:10px; align-items:flex-start; width:100%;">
            <input type="checkbox" name="hp_show_breaking" <?= hp_checked($settings,'hp_show_breaking') ?>>
            <div>
              <strong>Show Breaking Ticker</strong>
              <small>Controls the red ticker near the top of the homepage.</small>
            </div>
          </label>
        </div>
        <div class="admin-toggle-row">
          <label style="display:flex; gap:10px; align-items:flex-start; width:100%;">
            <input type="checkbox" name="hp_show_featured" <?= hp_checked($settings,'hp_show_featured') ?>>
            <div>
              <strong>Show Featured Slider</strong>
              <small>Turns on the automatic top featured slider and its mini-cards.</small>
            </div>
          </label>
        </div>
        <div class="admin-toggle-row">
          <label style="display:flex; gap:10px; align-items:flex-start; width:100%;">
            <input type="checkbox" name="hp_show_trending" <?= hp_checked($settings,'hp_show_trending') ?>>
            <div>
              <strong>Show Trending Box</strong>
              <small>Displays trending stories in the right rail.</small>
            </div>
          </label>
        </div>
        <div class="admin-toggle-row">
          <label style="display:flex; gap:10px; align-items:flex-start; width:100%;">
            <input type="checkbox" name="hp_show_video" <?= hp_checked($settings,'hp_show_video') ?>>
            <div>
              <strong>Show Video Section</strong>
              <small>Controls the video widget on the sidebar.</small>
            </div>
          </label>
        </div>
        <div class="admin-toggle-row">
          <label style="display:flex; gap:10px; align-items:flex-start; width:100%;">
            <input type="checkbox" name="hp_show_gallery" <?= hp_checked($settings,'hp_show_gallery') ?>>
            <div>
              <strong>Show Gallery Section</strong>
              <small>Displays gallery thumbnails in the sidebar.</small>
            </div>
          </label>
        </div>
        <div class="admin-toggle-row">
          <label style="display:flex; gap:10px; align-items:flex-start; width:100%;">
            <input type="checkbox" name="hp_show_ads_top" <?= hp_checked($settings,'hp_show_ads_top') ?>>
            <div>
              <strong>Show Top Homepage Ad</strong>
              <small>Toggles the banner above the ticker.</small>
            </div>
          </label>
        </div>
        <div class="admin-toggle-row">
          <label style="display:flex; gap:10px; align-items:flex-start; width:100%;">
            <input type="checkbox" name="hp_show_ads_inline" <?= hp_checked($settings,'hp_show_ads_inline') ?>>
            <div>
              <strong>Show Inline Ads</strong>
              <small>Controls inline homepage ads beneath story blocks.</small>
            </div>
          </label>
        </div>
        <div class="admin-toggle-row">
          <label style="display:flex; gap:10px; align-items:flex-start; width:100%;">
            <input type="checkbox" name="hp_show_ads_sidebar" <?= hp_checked($settings,'hp_show_ads_sidebar') ?>>
            <div>
              <strong>Show Sidebar Ads</strong>
              <small>Enables the sticky ad slots in the right rail.</small>
            </div>
          </label>
        </div>
        <div class="form-actions">
          <button type="submit" class="admin-button">Save Layout</button>
        </div>
      </form>
    </section>
  </main>
</body>
</html>
