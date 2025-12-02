<?php
require __DIR__ . '/../bootstrap.php';
hs_require_staff(['admin', 'editor']);
$db = hs_db();

$analytics_error = null;
$role = hs_staff_role() ?? 'admin';
$analytics_ready = $db && hs_table_exists('hs_analytics_events', $db);
if (!$db) {
    $analytics_error = 'Database connection unavailable. Check HS_DB_* values in .env.php.';
} elseif ($analytics_ready && !hs_table_has_columns('hs_analytics_events', ['event_type', 'post_id', 'category_id', 'reporter_id', 'editor_id', 'visitor_hash', 'country', 'device', 'browser', 'user_agent', 'created_at'], $db)) {
    $analytics_ready = false;
    $analytics_error = 'The hs_analytics_events table is missing required columns. Run the latest installer SQL to migrate the schema.';
} elseif (!$analytics_ready) {
    $analytics_error = 'The analytics table was not found. Install or migrate hs_analytics_events to enable tracking reports.';
}

$total_visitors = 0;
$page_views = 0;
$last_day_views = 0;
$article_reads = 0;
$daily = $article_report = $category_report = [];
$country_report = $device_report = $browser_report = $reporter_report = $editor_report = [];
$active_readers = 0;

function hs_fetch_all_or_empty($db, $sql)
{
    $res = mysqli_query($db, $sql);
    return $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
}

if ($analytics_ready) {
    $row = mysqli_fetch_row(mysqli_query($db, "SELECT COUNT(*) FROM hs_analytics_events"));
    $page_views = $row ? (int)$row[0] : 0;

    $row = mysqli_fetch_row(mysqli_query($db, "SELECT COUNT(DISTINCT visitor_hash) FROM hs_analytics_events"));
    $total_visitors = $row ? (int)$row[0] : 0;

    $row = mysqli_fetch_row(mysqli_query($db, "SELECT COUNT(*) FROM hs_analytics_events WHERE event_type='post'"));
    $article_reads = $row ? (int)$row[0] : 0;

    $row = mysqli_fetch_row(mysqli_query($db, "SELECT COUNT(*) FROM hs_analytics_events WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)"));
    $last_day_views = $row ? (int)$row[0] : 0;

    $active_row = mysqli_fetch_row(mysqli_query($db, "SELECT COUNT(*) FROM hs_analytics_events WHERE created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)"));
    $active_readers = $active_row ? (int)$active_row[0] : 0;

    $daily = hs_fetch_all_or_empty($db, "SELECT DATE(created_at) as day, COUNT(*) as views, COUNT(DISTINCT visitor_hash) as visitors
                                   FROM hs_analytics_events
                                   GROUP BY DATE(created_at)
                                   ORDER BY day DESC
                                   LIMIT 10");

    $article_report = hs_fetch_all_or_empty($db, "SELECT p.title, p.slug, COUNT(e.id) as reads
                                      FROM hs_analytics_events e
                                      JOIN hs_posts p ON p.id = e.post_id
                                      WHERE e.event_type='post'
                                      GROUP BY e.post_id
                                      ORDER BY reads DESC
                                      LIMIT 10");

    $category_report = hs_fetch_all_or_empty($db, "SELECT c.name, COUNT(e.id) as reads
                                       FROM hs_analytics_events e
                                       JOIN hs_categories c ON c.id = e.category_id
                                       GROUP BY e.category_id
                                       ORDER BY reads DESC
                                       LIMIT 10");

    $country_report = hs_fetch_all_or_empty($db, "SELECT COALESCE(country,'Unknown') as country, COUNT(*) as hits
                                      FROM hs_analytics_events
                                      GROUP BY country
                                      ORDER BY hits DESC
                                      LIMIT 10");

    $device_report = hs_fetch_all_or_empty($db, "SELECT COALESCE(device,'Unknown') as device, COUNT(*) as hits
                                     FROM hs_analytics_events
                                     GROUP BY device
                                     ORDER BY hits DESC");

    $browser_report = hs_fetch_all_or_empty($db, "SELECT COALESCE(browser,'Unknown') as browser, COUNT(*) as hits
                                      FROM hs_analytics_events
                                      GROUP BY browser
                                      ORDER BY hits DESC");

    $reporter_report = hs_fetch_all_or_empty($db, "SELECT u.name, COUNT(e.id) as reads
                                        FROM hs_analytics_events e
                                        JOIN hs_posts p ON p.id = e.post_id
                                        JOIN hs_users u ON u.id = p.reporter_id
                                        WHERE e.event_type='post'
                                        GROUP BY p.reporter_id
                                        ORDER BY reads DESC
                                        LIMIT 10");

    $editor_report = hs_fetch_all_or_empty($db, "SELECT u.name, COUNT(e.id) as reads
                                     FROM hs_analytics_events e
                                     JOIN hs_posts p ON p.id = e.post_id
                                     JOIN hs_users u ON u.id = p.editor_id
                                     WHERE e.event_type='post'
                                     GROUP BY p.editor_id
                                     ORDER BY reads DESC
                                     LIMIT 10");
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Analytics – NEWS HDSPTV</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
  <style>
    body { margin:0; font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; background:#020617; color:#E5E7EB; }
    header { padding:12px 20px; border-bottom:1px solid #111827; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; }
    nav a { margin-right:10px; font-size:12px; color:#9CA3AF; }
    nav a:hover, nav a.active { color:#FACC15; }
    nav a.highlight { color:#FACC15; font-weight:600; }
    .container { max-width:1200px; margin:18px auto; padding:0 16px; }
    h1 { margin:0 0 8px; font-size:22px; letter-spacing:.02em; }
    .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:12px; margin:14px 0; }
    .card { background:radial-gradient(circle at top left,#1E3A8A,#020617); border-radius:14px; padding:14px; box-shadow:0 22px 60px rgba(15,23,42,0.65); }
    .card h3 { margin:6px 0 4px; font-size:16px; }
    .muted { color:#9CA3AF; font-size:12px; margin:0; }
    table { width:100%; border-collapse:collapse; margin:12px 0; }
    th, td { text-align:left; padding:8px 6px; border-bottom:1px solid rgba(148,163,184,0.2); font-size:12px; }
    th { color:#FACC15; letter-spacing:.08em; text-transform:uppercase; }
    tr:hover td { background:rgba(15,23,42,0.4); }
    .pill { font-size:11px; text-transform:uppercase; letter-spacing:.12em; color:#FACC15; }
    .empty { color:#9CA3AF; font-size:12px; margin:6px 0 0; }
  </style>
</head>
<body>
<header>
  <div style="font-weight:700; letter-spacing:.14em;">NEWS HDSPTV • ANALYTICS</div>
  <nav>
    <?php hs_render_admin_nav($role, 'analytics'); ?>
  </nav>
</header>
<main class="container">
  <h1>Analytics Dashboard</h1>
  <p class="muted">Track visitors, reads, devices, countries, and staff performance across your newsroom.</p>

  <?php if (!$analytics_ready): ?>
    <div class="card" style="border:1px solid rgba(248,113,113,0.3); background:linear-gradient(135deg,#1f2937,#0f172a);">
      <h3 style="color:#fecdd3; margin-top:0;">Analytics unavailable</h3>
      <p class="muted" style="margin-bottom:6px;"><?= htmlspecialchars($analytics_error ?? 'Analytics storage is missing.') ?></p>
      <ul class="muted" style="margin:0 0 6px 16px; padding:0;">
        <li>Confirm database connectivity in <code>.env.php</code>.</li>
        <li>Apply the latest <code>install/install.sql</code> to create/upgrade <code>hs_analytics_events</code>.</li>
        <li>Reload this page after migrating.</li>
      </ul>
    </div>
  <?php else: ?>
    <div class="grid">
      <div class="card">
        <div class="pill">Visitors</div>
        <h3><?= number_format($total_visitors) ?></h3>
        <p class="muted">Total unique visitors tracked.</p>
      </div>
      <div class="card">
        <div class="pill">Page Views</div>
        <h3><?= number_format($page_views) ?></h3>
        <p class="muted">All recorded analytics events.</p>
      </div>
      <div class="card">
        <div class="pill">Article Reads</div>
        <h3><?= number_format($article_reads) ?></h3>
        <p class="muted">Total article view events.</p>
      </div>
      <div class="card">
        <div class="pill">Last 24h</div>
        <h3><?= number_format($last_day_views) ?></h3>
        <p class="muted">Events captured in the past day.</p>
      </div>
      <div class="card">
        <div class="pill">Active Now</div>
        <h3><?= number_format($active_readers) ?></h3>
        <p class="muted">Readers in the past 5 minutes.</p>
      </div>
    </div>

    <div class="card">
      <div class="pill">Daily Visitors</div>
      <?php if (empty($daily)): ?>
        <p class="empty">No visits recorded yet.</p>
      <?php else: ?>
        <table>
          <thead><tr><th>Date</th><th>Visitors</th><th>Page Views</th></tr></thead>
          <tbody>
            <?php foreach ($daily as $d): ?>
              <tr>
                <td><?= htmlspecialchars($d['day']) ?></td>
                <td><?= number_format($d['visitors']) ?></td>
                <td><?= number_format($d['views']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <div class="card">
      <div class="pill">Article-wise Reads</div>
      <?php if (empty($article_report)): ?>
        <p class="empty">No article reads yet.</p>
      <?php else: ?>
        <table>
          <thead><tr><th>Article</th><th>Reads</th></tr></thead>
          <tbody>
            <?php foreach ($article_report as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= number_format($row['reads']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <div class="card">
      <div class="pill">Category-wise Reads</div>
      <?php if (empty($category_report)): ?>
        <p class="empty">No category reads yet.</p>
      <?php else: ?>
        <table>
          <thead><tr><th>Category</th><th>Reads</th></tr></thead>
          <tbody>
            <?php foreach ($category_report as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= number_format($row['reads']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <div class="grid">
      <div class="card">
        <div class="pill">Countries</div>
        <?php if (empty($country_report)): ?>
          <p class="empty">No country data yet.</p>
        <?php else: ?>
          <table>
            <thead><tr><th>Country</th><th>Hits</th></tr></thead>
            <tbody>
              <?php foreach ($country_report as $row): ?>
                <tr><td><?= htmlspecialchars($row['country'] ?: 'Unknown') ?></td><td><?= number_format($row['hits']) ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
      <div class="card">
        <div class="pill">Devices</div>
        <?php if (empty($device_report)): ?>
          <p class="empty">No device data yet.</p>
        <?php else: ?>
          <table>
            <thead><tr><th>Device</th><th>Hits</th></tr></thead>
            <tbody>
              <?php foreach ($device_report as $row): ?>
                <tr><td><?= htmlspecialchars($row['device'] ?: 'Unknown') ?></td><td><?= number_format($row['hits']) ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
      <div class="card">
        <div class="pill">Browsers</div>
        <?php if (empty($browser_report)): ?>
          <p class="empty">No browser data yet.</p>
        <?php else: ?>
          <table>
            <thead><tr><th>Browser</th><th>Hits</th></tr></thead>
            <tbody>
              <?php foreach ($browser_report as $row): ?>
                <tr><td><?= htmlspecialchars($row['browser'] ?: 'Unknown') ?></td><td><?= number_format($row['hits']) ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <div class="grid">
      <div class="card">
        <div class="pill">Reporter Performance</div>
        <?php if (empty($reporter_report)): ?>
          <p class="empty">No reporter reads yet.</p>
        <?php else: ?>
          <table>
            <thead><tr><th>Reporter</th><th>Reads</th></tr></thead>
            <tbody>
              <?php foreach ($reporter_report as $row): ?>
                <tr><td><?= htmlspecialchars($row['name']) ?></td><td><?= number_format($row['reads']) ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
      <div class="card">
        <div class="pill">Editor Performance</div>
        <?php if (empty($editor_report)): ?>
          <p class="empty">No editor reads yet.</p>
        <?php else: ?>
          <table>
            <thead><tr><th>Editor</th><th>Reads</th></tr></thead>
            <tbody>
              <?php foreach ($editor_report as $row): ?>
                <tr><td><?= htmlspecialchars($row['name']) ?></td><td><?= number_format($row['reads']) ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</main>
</body>
</html>
