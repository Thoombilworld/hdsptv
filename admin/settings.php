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
</head>
<body class="admin-shell">
  <header class="admin-header">
    <div class="admin-logo">NEWS HDSPTV • ADMIN</div>
    <nav class="admin-nav">
      <?php hs_render_admin_nav($role, 'settings'); ?>
    </nav>
  </header>
  <main class="admin-container">
    <section class="admin-panel">
      <div class="admin-pill">Branding</div>
      <h1 style="margin:8px 0 6px;">Site Settings</h1>
      <p class="admin-subtext">Control global branding, theme defaults, favicon, and primary language. The new material-inspired palette is reflected instantly across the public site.</p>
      <?php if ($msg): ?><div class="admin-message" role="status"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
      <form method="post" class="admin-form">
        <div class="form-row">
          <div class="field">
            <label>Site Title</label>
            <input type="text" name="site_title" value="<?= htmlspecialchars($settings['site_title'] ?? HS_APP_NAME) ?>" required>
          </div>
          <div class="field">
            <label>Tagline</label>
            <input type="text" name="tagline" value="<?= htmlspecialchars($settings['tagline'] ?? '') ?>">
          </div>
        </div>
        <div class="form-row">
          <div class="field">
            <label>Logo URL</label>
            <input type="text" name="logo" value="<?= htmlspecialchars($settings['logo'] ?? '') ?>" placeholder="https://.../logo.png">
            <div class="admin-subtext">Used in the sticky header and footer.</div>
          </div>
          <div class="field">
            <label>Favicon URL</label>
            <input type="text" name="favicon" value="<?= htmlspecialchars($settings['favicon'] ?? '') ?>" placeholder="https://.../favicon.png">
          </div>
        </div>
        <div class="form-row">
          <div class="field">
            <label>Default Theme</label>
            <select name="theme">
              <option value="light" <?= ($settings['theme'] ?? '') === 'light' ? 'selected' : '' ?>>Light</option>
              <option value="dark" <?= ($settings['theme'] ?? 'dark') === 'dark' ? 'selected' : '' ?>>Dark</option>
            </select>
          </div>
          <div class="field">
            <label>Default Language</label>
            <select name="default_language">
              <?php foreach ($languages as $code => $meta): ?>
                <option value="<?= htmlspecialchars($code) ?>" <?= ($settings['default_language'] ?? '') === $code ? 'selected' : '' ?>><?= htmlspecialchars($meta['name'] ?? strtoupper($code)) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="admin-subtext">Drives the initial locale for the multi-language frontend.</div>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="admin-button">Save Settings</button>
        </div>
      </form>
    </section>
  </main>
</body>
</html>
