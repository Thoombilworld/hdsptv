<?php
require __DIR__ . '/../bootstrap.php';
hs_require_admin();

$role = hs_staff_role() ?? 'admin';
$checks = hs_system_checks();
$passing = array_filter($checks, function ($c) { return $c['status'] === 'ok'; });
$warnings = array_filter($checks, function ($c) { return $c['status'] === 'warn'; });
$failures = array_filter($checks, function ($c) { return $c['status'] === 'fail'; });

$overall = 'healthy';
if (count($failures) > 0) {
    $overall = 'issues detected';
} elseif (count($warnings) > 0) {
    $overall = 'warnings';
}

global $HS_DB_NAME, $HS_DB_HOST;
$environment = [
    'App name' => HS_APP_NAME,
    'Base URL' => HS_BASE_URL,
    'PHP version' => PHP_VERSION,
    'Default language' => hs_current_language_label(),
    'Theme' => hs_current_theme(),
    'Database host' => $HS_DB_HOST ?? 'localhost',
    'Database name' => $HS_DB_NAME ?? 'news_hdsptv',
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>System Health – NEWS HDSPTV</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
  <style>
    body { margin:0; font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; background:#020617; color:#E5E7EB; }
    header { padding:12px 20px; border-bottom:1px solid #111827; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; }
    .logo { font-size:16px; font-weight:700; letter-spacing:.12em; }
    nav a { margin-right:10px; font-size:12px; color:#9CA3AF; }
    nav a:hover, nav a.active { color:#FACC15; }
    nav a.highlight { color:#FACC15; font-weight:600; }
    .container { max-width:1080px; margin:18px auto; padding:0 16px 28px; }
    h1 { margin:0 0 8px; }
    p.lead { margin:0 0 16px; color:#CBD5E1; font-size:13px; }
    .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:14px; }
    .card { background:radial-gradient(circle at top left,#1E3A8A,#020617); border-radius:14px; padding:16px 18px; box-shadow:0 22px 60px rgba(15,23,42,0.75); border:1px solid rgba(148,163,184,0.14); }
    .pill { font-size:11px; text-transform:uppercase; letter-spacing:.16em; color:#FACC15; margin-bottom:6px; display:inline-block; }
    .status { font-size:12px; border-radius:999px; padding:4px 10px; display:inline-block; }
    .ok { background:rgba(34,197,94,0.12); color:#4ade80; border:1px solid rgba(34,197,94,0.5); }
    .warn { background:rgba(251,191,36,0.14); color:#fbbf24; border:1px solid rgba(251,191,36,0.4); }
    .fail { background:rgba(248,113,113,0.14); color:#f87171; border:1px solid rgba(248,113,113,0.45); }
    .muted { color:#94A3B8; font-size:13px; margin:6px 0 0; }
    .env-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:10px; margin-top:14px; }
    .env { background:rgba(15,23,42,0.65); border:1px solid rgba(148,163,184,0.14); border-radius:10px; padding:10px 12px; }
    .env span { display:block; font-size:12px; color:#9CA3AF; }
    .env strong { display:block; color:#E5E7EB; }
  </style>
</head>
<body>
<header>
  <div class="logo">NEWS HDSPTV • ADMIN</div>
  <nav>
    <?php hs_render_admin_nav($role, 'system'); ?>
  </nav>
</header>
<main class="container">
  <h1>System Health</h1>
  <p class="lead">Review installation status, database connectivity, directories, and extensions to keep the newsroom stable. Overall status: <strong><?= htmlspecialchars($overall) ?></strong>.</p>

  <div class="grid">
    <?php foreach ($checks as $check): ?>
      <section class="card">
        <div class="pill"><?= htmlspecialchars($check['label']) ?></div>
        <div class="status <?= htmlspecialchars($check['status']) ?>"><?= strtoupper($check['status']) ?></div>
        <p class="muted"><?= htmlspecialchars($check['detail']) ?></p>
      </section>
    <?php endforeach; ?>
  </div>

  <section class="card" style="margin-top:16px;">
    <div class="pill">Environment</div>
    <h3 style="margin:0 0 6px;">Configuration snapshot</h3>
    <div class="env-grid">
      <?php foreach ($environment as $key => $value): ?>
        <div class="env">
          <span><?= htmlspecialchars($key) ?></span>
          <strong><?= htmlspecialchars($value) ?></strong>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</main>
</body>
</html>
