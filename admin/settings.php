<?php
require __DIR__ . '/../bootstrap.php';
hs_require_admin();
$settings = hs_settings();
$role = hs_staff_role() ?? 'admin';
$languages = hs_supported_languages();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = hs_db();
    $site_title = trim($_POST['site_title'] ?? '');
    $tagline = trim($_POST['tagline'] ?? '');
    $logo = trim($_POST['logo'] ?? '');
    $favicon = trim($_POST['favicon'] ?? '');
    $theme = strtolower(trim($_POST['theme'] ?? 'dark'));
    $default_language = strtolower(trim($_POST['default_language'] ?? ''));
    if (!in_array($theme, ['light', 'dark'], true)) {
        $theme = 'dark';
    }
    if (!isset($languages[$default_language])) {
        $default_language = array_key_first($languages);
    }

    $pairs = [
        'site_title' => $site_title !== '' ? $site_title : HS_APP_NAME,
        'tagline'    => $tagline,
        'logo'       => $logo,
        'theme'      => $theme,
        'favicon'    => $favicon,
        'default_language' => $default_language,
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
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Site Settings – NEWS HDSPTV</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
  <style>
    body { margin:0; font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; background:#0B1120; color:#E5E7EB; }
    header { padding:12px 20px; border-bottom:1px solid #111827; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; }
    .logo { font-size:16px; font-weight:700; letter-spacing:.12em; }
    nav a { margin-right:10px; font-size:12px; color:#9CA3AF; }
    nav a:hover, nav a.active { color:#FACC15; }
    nav a.highlight { color:#FACC15; font-weight:600; }
    .container { max-width:900px; margin:18px auto 28px; padding:0 16px; }
    .panel { background:radial-gradient(circle at top left,#1E3A8A,#020617); border-radius:14px; padding:16px 18px; box-shadow:0 22px 60px rgba(15,23,42,0.75); margin-bottom:18px; }
    .panel h2 { margin:0 0 6px; font-size:16px; }
    .panel p { margin:0 0 10px; font-size:13px; color:#E5E7EB; }
    label { font-size:13px; color:#E5E7EB; display:block; margin-bottom:6px; }
    input[type="text"], select { width:100%; border-radius:10px; border:1px solid rgba(148,163,184,0.35); background:rgba(2,6,23,0.75); color:#F9FAFB; padding:10px 12px; font-size:14px; }
    small { color:#9CA3AF; }
    button { padding:10px 18px; border-radius:999px; border:1px solid rgba(248,250,252,.26); background:#0F172A; color:#E5E7EB; cursor:pointer; margin-right:8px; font-size:13px; }
    button:hover { border-color:#FACC15; color:#FACC15; }
    .message { margin:10px 0; padding:10px 12px; border-radius:10px; background:rgba(34,197,94,0.14); color:#BBF7D0; border:1px solid rgba(34,197,94,0.4); }
    .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:14px; }
  </style>
</head>
<body>
  <header>
    <div class="logo">NEWS HDSPTV • ADMIN</div>
    <nav>
      <?php hs_render_admin_nav($role, 'settings'); ?>
    </nav>
  </header>
  <main class="container">
    <h1>Site Settings</h1>
    <p style="color:#9CA3AF; margin-top:0;">Control global branding, theme defaults, and language for the public site.</p>
    <?php if ($msg): ?><div class="message" role="status"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <form method="post">
      <div class="grid">
        <section class="panel">
          <h2>Brand Basics</h2>
          <p>Update the key identity values shown in headers, footers, and metadata.</p>
          <label for="site-title">Site Title</label>
          <input id="site-title" type="text" name="site_title" value="<?= htmlspecialchars($settings['site_title'] ?? HS_APP_NAME) ?>" placeholder="NEWS HDSPTV">
          <br><br>
          <label for="tagline">Tagline</label>
          <input id="tagline" type="text" name="tagline" value="<?= htmlspecialchars($settings['tagline'] ?? '') ?>" placeholder="News for India, GCC, Kerala &amp; the World">
          <br><br>
          <label for="logo">Logo URL (optional)</label>
          <input id="logo" type="text" name="logo" value="<?= htmlspecialchars($settings['logo'] ?? '') ?>" placeholder="assets/images/logo.png">
          <small>Provide a full URL or relative path to your logo image.</small>
          <br><br>
          <label for="favicon">Favicon URL</label>
          <input id="favicon" type="text" name="favicon" value="<?= htmlspecialchars($settings['favicon'] ?? '') ?>" placeholder="assets/images/favicon.png">
          <small>Displayed in browser tabs and bookmarks.</small>
        </section>

        <section class="panel">
          <h2>Theme &amp; Language</h2>
          <p>Choose default theme and audience language shown to new visitors.</p>
          <label for="theme">Default Theme</label>
          <select id="theme" name="theme">
            <option value="dark" <?= ($settings['theme'] ?? 'dark') === 'dark' ? 'selected' : '' ?>>Dark</option>
            <option value="light" <?= ($settings['theme'] ?? 'dark') === 'light' ? 'selected' : '' ?>>Light</option>
          </select>
          <br><br>
          <label for="language">Default Language</label>
          <select id="language" name="default_language">
            <?php foreach ($languages as $code => $label): ?>
              <option value="<?= htmlspecialchars($code) ?>" <?= ($settings['default_language'] ?? array_key_first($languages)) === $code ? 'selected' : '' ?>><?= htmlspecialchars($label) ?> (<?= strtoupper($code) ?>)</option>
            <?php endforeach; ?>
          </select>
          <small>Applies to the language selector and page lang attributes for SEO.</small>
        </section>
      </div>
      <div style="margin-top:12px;">
        <button type="submit">Save Settings</button>
      </div>
    </form>
  </main>
</body>
</html>
