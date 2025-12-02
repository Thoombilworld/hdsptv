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
  <style>
    body { margin:0; font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; background:#0B1224; color:#E2E8F0; }
    header { padding:12px 20px; border-bottom:1px solid #1E293B; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; }
    .logo { font-size:16px; font-weight:700; letter-spacing:.14em; }
    nav a { margin-right:10px; font-size:12px; color:#94A3B8; }
    nav a:hover, nav a.active { color:#FACC15; }
    nav a.highlight { color:#FACC15; font-weight:600; }
    .container { max-width:960px; margin:18px auto; padding:0 16px; }
    .card { background:linear-gradient(145deg, rgba(30,41,59,0.8), rgba(15,23,42,0.95)); border:1px solid rgba(148,163,184,0.12); border-radius:16px; padding:18px 20px; box-shadow:0 25px 80px rgba(0,0,0,0.35); }
    .pill { font-size:11px; text-transform:uppercase; letter-spacing:.2em; color:#FACC15; margin-bottom:8px; display:inline-block; }
    h1 { margin:0 0 4px; }
    p.lead { margin:0 0 14px; color:#CBD5E1; font-size:13px; }
    form { display:grid; gap:12px; }
    .toggle-row { display:flex; align-items:flex-start; justify-content:space-between; background:rgba(15,23,42,0.7); border:1px solid rgba(148,163,184,0.18); padding:12px 14px; border-radius:12px; }
    .toggle-row label { display:flex; gap:10px; align-items:flex-start; font-size:13px; color:#E2E8F0; }
    .toggle-row small { color:#94A3B8; font-size:12px; display:block; margin-top:2px; }
    .actions { display:flex; justify-content:flex-end; margin-top:8px; }
    button { background:#FACC15; color:#0F172A; border:0; border-radius:10px; padding:10px 16px; font-weight:700; cursor:pointer; box-shadow:0 12px 30px rgba(250,204,21,0.25); }
    button:hover { background:#Fbbf24; }
    .flash { padding:10px 12px; border-radius:10px; background:rgba(34,197,94,0.12); color:#4ade80; border:1px solid rgba(74,222,128,0.35); margin-bottom:12px; }
  </style>
</head>
<body>
<header>
  <div class="logo">NEWS HDSPTV • <?= strtoupper($role) ?></div>
  <nav>
    <?php hs_render_admin_nav($role, 'homepage'); ?>
  </nav>
</header>

<main class="container">
  <div class="card">
    <div class="pill">Homepage</div>
    <h1>Homepage Layout Manager</h1>
    <p class="lead">Toggle homepage blocks for QA. Changes apply instantly to breaking ticker, featured slider, trending, media widgets, and ad slots.</p>
    <?php if ($msg): ?>
      <div class="flash"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="toggle-row">
        <label>
          <input type="checkbox" name="hp_show_breaking" <?= hp_checked($settings,'hp_show_breaking') ?>>
          <div>
            <strong>Show Breaking Ticker</strong>
            <small>Controls the red ticker near the top of the homepage.</small>
          </div>
        </label>
      </div>
      <div class="toggle-row">
        <label>
          <input type="checkbox" name="hp_show_featured" <?= hp_checked($settings,'hp_show_featured') ?>>
          <div>
            <strong>Show Featured Slider</strong>
            <small>Turns on the automatic top featured slider and its mini-cards.</small>
          </div>
        </label>
      </div>
      <div class="toggle-row">
        <label>
          <input type="checkbox" name="hp_show_trending" <?= hp_checked($settings,'hp_show_trending') ?>>
          <div>
            <strong>Show Trending Box</strong>
            <small>Displays trending stories in the right rail.</small>
          </div>
        </label>
      </div>
      <div class="toggle-row">
        <label>
          <input type="checkbox" name="hp_show_video" <?= hp_checked($settings,'hp_show_video') ?>>
          <div>
            <strong>Show Video Section</strong>
            <small>Controls the video widget on the sidebar.</small>
          </div>
        </label>
      </div>
      <div class="toggle-row">
        <label>
          <input type="checkbox" name="hp_show_gallery" <?= hp_checked($settings,'hp_show_gallery') ?>>
          <div>
            <strong>Show Gallery Section</strong>
            <small>Displays gallery thumbnails in the sidebar.</small>
          </div>
        </label>
      </div>
      <div class="toggle-row">
        <label>
          <input type="checkbox" name="hp_show_ads_top" <?= hp_checked($settings,'hp_show_ads_top') ?>>
          <div>
            <strong>Show Top Homepage Ad</strong>
            <small>Toggles the banner above the ticker.</small>
          </div>
        </label>
      </div>
      <div class="toggle-row">
        <label>
          <input type="checkbox" name="hp_show_ads_inline" <?= hp_checked($settings,'hp_show_ads_inline') ?>>
          <div>
            <strong>Show Inline Homepage Ads</strong>
            <small>Controls inline ad slots inside the main column.</small>
          </div>
        </label>
      </div>
      <div class="toggle-row">
        <label>
          <input type="checkbox" name="hp_show_ads_sidebar" <?= hp_checked($settings,'hp_show_ads_sidebar') ?>>
          <div>
            <strong>Show Sidebar Ads Block</strong>
            <small>Displays right-rail ads and the callout card.</small>
          </div>
        </label>
      </div>
      <div class="actions">
        <button type="submit">Save Layout</button>
      </div>
    </form>
  </div>
</main>
</body>
</html>
