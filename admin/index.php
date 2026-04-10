<?php
require __DIR__ . '/../bootstrap.php';
hs_require_staff(['admin', 'editor', 'reporter']);

$settings = hs_settings();
$staff = hs_current_staff();
$role = $staff['role'] ?? 'admin';
$db = hs_db();

$safe_count = function (string $sql) use ($db): int {
    if (!$db) return 0;
    $res = @mysqli_query($db, $sql);
    if (!$res) return 0;
    $row = mysqli_fetch_row($res);
    return (int)($row[0] ?? 0);
};

$totalPublishedToday = $safe_count("SELECT COUNT(*) FROM hs_posts WHERE status='published' AND DATE(created_at)=CURDATE()");
$draftsPending = $safe_count("SELECT COUNT(*) FROM hs_posts WHERE status IN ('draft','submitted','under_review')");
$breakingActive = $safe_count("SELECT COUNT(*) FROM hs_posts WHERE status='published' AND is_breaking=1");
$videoUploadsToday = $safe_count("SELECT COUNT(*) FROM hs_posts WHERE type='video' AND DATE(created_at)=CURDATE()");
$commentsPending = $safe_count("SELECT COUNT(*) FROM hs_comments WHERE status='pending'");
$pushSentToday = $safe_count("SELECT COUNT(*) FROM hs_notifications WHERE DATE(created_at)=CURDATE()");

$topCategory = 'N/A';
$topReporter = 'N/A';
$mostViewedStory = 'N/A';
$liveViewers = (int)($settings['live_viewers'] ?? 0);
$adRevenueToday = (float)($settings['ad_revenue_today'] ?? 0);
$streamHealth = $settings['stream_health'] ?? 'Standby';

if ($db) {
    $res = @mysqli_query($db, "SELECT c.name, COUNT(*) as total FROM hs_posts p LEFT JOIN hs_categories c ON c.id=p.category_id WHERE p.status='published' GROUP BY p.category_id ORDER BY total DESC LIMIT 1");
    if ($res && ($row = mysqli_fetch_assoc($res))) {
        $topCategory = $row['name'] ?: 'Uncategorized';
    }

    $res = @mysqli_query($db, "SELECT COALESCE(author,'Reporter') as author, COUNT(*) as total FROM hs_posts WHERE status='published' GROUP BY author ORDER BY total DESC LIMIT 1");
    if ($res && ($row = mysqli_fetch_assoc($res))) {
        $topReporter = $row['author'];
    }

    $res = @mysqli_query($db, "SELECT title FROM hs_posts WHERE status='published' ORDER BY created_at DESC LIMIT 1");
    if ($res && ($row = mysqli_fetch_assoc($res))) {
        $mostViewedStory = $row['title'];
    }
}

$recentNews = [];
if ($db) {
    $res = @mysqli_query($db, "SELECT title, status, created_at, priority, author FROM hs_posts ORDER BY created_at DESC LIMIT 8");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) $recentNews[] = $row;
    }
}

$trendingWidget = [];
if ($db) {
    $res = @mysqli_query($db, "SELECT title, COALESCE(views,0) as views, COALESCE(engagement_score,0) as engagement_score, is_featured FROM hs_posts WHERE status='published' ORDER BY created_at DESC LIMIT 5");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) $trendingWidget[] = $row;
    }
}

$reporterWidget = [];
if ($db) {
    $res = @mysqli_query($db, "SELECT COALESCE(author,'Reporter') as author, COUNT(*) as submitted FROM hs_posts WHERE DATE(created_at)=CURDATE() GROUP BY author ORDER BY submitted DESC LIMIT 5");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) $reporterWidget[] = $row;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard 2026 Edition – NEWS HDSPTV</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
  <style>
    :root {
      --admin-primary:#D60000; --admin-dark:#111111; --admin-bg:#F6F7FB; --admin-card:#fff;
      --admin-border:#E5E7EB; --admin-success:#16A34A; --admin-warning:#F59E0B; --admin-info:#2563EB;
    }
    body { margin:0; background:var(--admin-bg); color:#111; font-family:system-ui,-apple-system,'Segoe UI',sans-serif; }
    body.dark-mode { background:#0B1020; color:#E5E7EB; }

    .admin-shell-2026 { display:grid; grid-template-columns:260px minmax(0,1fr); min-height:100vh; }
    .sidebar {
      position:sticky; top:0; align-self:start; height:100vh; overflow:auto;
      background:#111; color:#E5E7EB; border-right:1px solid #242424; padding:16px 12px;
    }
    .brand { display:flex; align-items:center; gap:10px; margin-bottom:14px; }
    .brand-logo { width:34px; height:34px; border-radius:10px; background:linear-gradient(135deg,#D60000,#8B0000); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; }
    .brand h1 { margin:0; font-size:13px; letter-spacing:.12em; text-transform:uppercase; }
    .brand small { color:#9CA3AF; }

    .side-group { margin-top:14px; }
    .side-title { font-size:11px; color:#9CA3AF; text-transform:uppercase; letter-spacing:.16em; margin:0 0 8px 10px; }
    .side-link { display:flex; align-items:center; gap:8px; color:#E5E7EB; padding:9px 10px; border-radius:10px; font-size:13px; }
    .side-link:hover { background:#1F2937; text-decoration:none; }
    .side-link.active { background:rgba(214,0,0,0.2); border:1px solid rgba(214,0,0,0.4); color:#fff; }

    .main { min-width:0; }
    .topbar {
      position:sticky; top:0; z-index:20; background:#fff; border-bottom:1px solid var(--admin-border);
      display:grid; grid-template-columns:minmax(0,1fr) auto; gap:12px; align-items:center; padding:10px 14px;
    }
    body.dark-mode .topbar { background:#111827; border-color:#1F2937; }
    .global-search input { width:min(560px,100%); padding:10px 12px; border-radius:10px; border:1px solid var(--admin-border); }
    .top-actions { display:flex; flex-wrap:wrap; gap:8px; justify-content:flex-end; }
    .btnx { border:1px solid var(--admin-border); background:#fff; color:#111; border-radius:10px; padding:8px 10px; font-size:12px; font-weight:700; }
    a.btnx { display:inline-flex; align-items:center; text-decoration:none; }
    .btnx.primary { background:#D60000; color:#fff; border-color:#B50000; }
    .btnx.live { background:#111; color:#fff; border-color:#303030; }
    .btnx.warn { background:#F59E0B; color:#111; border-color:#D97706; }

    .meta-row { display:flex; justify-content:space-between; gap:10px; padding:10px 16px; font-size:12px; color:#6B7280; }
    .meta-badges { display:flex; gap:8px; flex-wrap:wrap; }
    .badge { padding:4px 9px; border-radius:999px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; }
    .badge.live { background:rgba(22,163,74,.15); color:#16A34A; border:1px solid rgba(22,163,74,.35); }
    .badge.breaking { background:rgba(214,0,0,.12); color:#D60000; border:1px solid rgba(214,0,0,.3); }

    .content { padding:14px 16px 22px; }
    .kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px; }
    .kpi { background:var(--admin-card); border:1px solid var(--admin-border); border-radius:14px; padding:12px; }
    .kpi span { display:block; color:#6B7280; font-size:11px; letter-spacing:.1em; text-transform:uppercase; }
    .kpi strong { display:block; margin-top:6px; font-size:24px; }

    .layout { margin-top:12px; display:grid; grid-template-columns:2fr 1.2fr; gap:12px; }
    .card { background:#fff; border:1px solid var(--admin-border); border-radius:14px; padding:12px; }
    .card h3 { margin:0 0 8px; font-size:15px; }
    .danger-card { border-color:rgba(214,0,0,.35); box-shadow:inset 0 0 0 1px rgba(214,0,0,.08); }

    .actions { display:flex; gap:8px; flex-wrap:wrap; }
    .actions .btnx { font-size:11px; padding:7px 9px; }

    table { width:100%; border-collapse:collapse; }
    th, td { text-align:left; padding:8px 6px; border-bottom:1px solid var(--admin-border); font-size:12px; }
    th { color:#6B7280; text-transform:uppercase; letter-spacing:.08em; font-size:10px; }

    .status { border-radius:999px; padding:4px 8px; font-size:10px; font-weight:700; text-transform:uppercase; }
    .st-published { background:rgba(22,163,74,.15); color:#166534; }
    .st-draft, .st-submitted, .st-under_review { background:rgba(245,158,11,.15); color:#92400E; }
    .st-rejected { background:rgba(214,0,0,.12); color:#B91C1C; }

    @media (max-width:1100px) {
      .admin-shell-2026 { grid-template-columns:1fr; }
      .sidebar { position:relative; height:auto; }
      .layout { grid-template-columns:1fr; }
    }
  </style>
</head>
<body>
<div class="admin-shell-2026">
  <aside class="sidebar">
    <div class="brand">
      <div class="brand-logo">H</div>
      <div>
        <h1>HDSPTV Admin</h1>
        <small>Broadcast Control Room</small>
      </div>
    </div>

    <div class="side-group">
      <div class="side-title">Main menu</div>
      <a class="side-link active" href="<?= hs_base_url('admin/index.php') ?>">🏠 Dashboard</a>
      <a class="side-link" href="<?= hs_base_url('admin/content/index.php') ?>">📰 News</a>
      <a class="side-link" href="<?= hs_base_url('admin/homepage.php') ?>">⚡ Breaking News</a>
      <a class="side-link" href="<?= hs_base_url('admin/system.php') ?>">📡 Live TV</a>
      <a class="side-link" href="<?= hs_base_url('admin/content/categories.php') ?>">📂 Categories</a>
      <a class="side-link" href="<?= hs_base_url('admin/users.php') ?>">👥 Reporters</a>
      <a class="side-link" href="<?= hs_base_url('admin/content/articles.php') ?>">🎬 Videos</a>
      <a class="side-link" href="<?= hs_base_url('admin/ads.php') ?>">💰 Ads & Banners</a>
      <a class="side-link" href="<?= hs_base_url('admin/analytics.php') ?>">📊 Analytics</a>
      <a class="side-link" href="<?= hs_base_url('admin/settings.php') ?>">⚙️ Settings</a>
    </div>

    <div class="side-group">
      <div class="side-title">Content</div>
      <a class="side-link" href="<?= hs_base_url('admin/content/articles.php') ?>">All News</a>
      <a class="side-link" href="<?= hs_base_url('admin/content/article_add.php') ?>">Drafts / New</a>
      <a class="side-link" href="<?= hs_base_url('admin/content/articles.php') ?>">Pending Review</a>
      <a class="side-link" href="<?= hs_base_url('admin/content/articles.php') ?>">Scheduled</a>
      <a class="side-link" href="<?= hs_base_url('admin/content/articles.php') ?>">Published</a>
    </div>
  </aside>

  <main class="main">
    <header class="topbar">
      <form class="global-search" method="get" action="<?= hs_base_url('admin/content/articles.php') ?>">
        <input type="text" name="q" placeholder="Search news, reporters, categories...">
      </form>
      <div class="top-actions">
        <a class="btnx primary" href="<?= hs_base_url('admin/content/article_add.php') ?>">+ Create News</a>
        <a class="btnx live" href="<?= hs_base_url('admin/system.php') ?>">Go Live</a>
        <a class="btnx warn" href="<?= hs_base_url('admin/homepage.php') ?>">Send Alert</a>
        <a class="btnx" href="<?= hs_base_url('admin/content/articles.php') ?>">Upload Video</a>
        <button class="btnx" type="button" id="modeToggle">🌙/☀️</button>
      </div>
    </header>

    <div class="meta-row">
      <div class="meta-badges">
        <span class="badge live">Live TV: <?= htmlspecialchars($streamHealth) ?></span>
        <span class="badge breaking">Breaking active: <?= (int)$breakingActive ?></span>
      </div>
      <div>
        <?= htmlspecialchars(hs_current_language_label()) ?> · <?= date('D, M j, Y H:i') ?> · <?= htmlspecialchars($staff['name'] ?? 'Staff') ?>
      </div>
    </div>

    <div class="content">
      <section class="kpi-grid">
        <article class="kpi"><span>Total Published Today</span><strong><?= (int)$totalPublishedToday ?></strong></article>
        <article class="kpi"><span>Drafts Pending</span><strong><?= (int)$draftsPending ?></strong></article>
        <article class="kpi"><span>Breaking Alerts Active</span><strong><?= (int)$breakingActive ?></strong></article>
        <article class="kpi"><span>Live Viewers</span><strong><?= (int)$liveViewers ?></strong></article>
        <article class="kpi"><span>Video Uploads Today</span><strong><?= (int)$videoUploadsToday ?></strong></article>
        <article class="kpi"><span>Ad Revenue Today</span><strong>$<?= number_format($adRevenueToday, 2) ?></strong></article>
        <article class="kpi"><span>Top Category</span><strong style="font-size:16px;"><?= htmlspecialchars($topCategory) ?></strong></article>
        <article class="kpi"><span>Top Reporter</span><strong style="font-size:16px;"><?= htmlspecialchars($topReporter) ?></strong></article>
        <article class="kpi"><span>Most Viewed Story</span><strong style="font-size:13px;"><?= htmlspecialchars($mostViewedStory) ?></strong></article>
        <article class="kpi"><span>Push Notifications Sent</span><strong><?= (int)$pushSentToday ?></strong></article>
        <article class="kpi"><span>Comments Pending</span><strong><?= (int)$commentsPending ?></strong></article>
        <article class="kpi"><span>Server / Stream Health</span><strong style="font-size:16px;"><?= htmlspecialchars($streamHealth) ?></strong></article>
      </section>

      <section class="layout">
        <div>
          <article class="card danger-card">
            <h3>Breaking News Control Center</h3>
            <p style="font-size:12px; color:#6B7280;">Active headline, ticker preview, and fast control for urgent newsroom operations.</p>
            <div class="actions" style="margin-top:8px;">
              <button class="btnx">Edit</button><button class="btnx">Pause</button><button class="btnx">Replace</button><button class="btnx warn">Push Notification</button><button class="btnx">Remove</button>
            </div>
          </article>

          <article class="card" style="margin-top:10px;">
            <h3>Recent News Workflow Table</h3>
            <table>
              <thead>
                <tr><th>Title</th><th>Author</th><th>Status</th><th>Publish Time</th><th>Priority</th><th>Actions</th></tr>
              </thead>
              <tbody>
                <?php if (empty($recentNews)): ?>
                  <tr><td colspan="6">No news records yet.</td></tr>
                <?php else: ?>
                  <?php foreach ($recentNews as $row): $status = strtolower((string)($row['status'] ?? 'draft')); ?>
                  <tr>
                    <td><?= htmlspecialchars($row['title'] ?? 'Untitled') ?></td>
                    <td><?= htmlspecialchars($row['author'] ?? 'Reporter') ?></td>
                    <td><span class="status st-<?= htmlspecialchars(str_replace(' ', '_', $status)) ?>"><?= htmlspecialchars($status) ?></span></td>
                    <td><?= !empty($row['created_at']) ? htmlspecialchars(date('M j H:i', strtotime($row['created_at']))) : '—' ?></td>
                    <td><?= htmlspecialchars($row['priority'] ?? 'Normal') ?></td>
                    <td>Edit · Review · Publish · Feature · Delete</td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </article>
        </div>

        <div>
          <article class="card">
            <h3>Live TV Status Panel</h3>
            <p style="font-size:12px; color:#6B7280;">Status: <strong><?= htmlspecialchars($streamHealth) ?></strong> · Program: Prime Bulletin · Next: World Focus</p>
            <div class="actions" style="margin-top:8px;">
              <button class="btnx live">Start Live</button><button class="btnx">Stop Live</button><button class="btnx">Switch Feed</button><button class="btnx">Update Program Info</button>
            </div>
          </article>

          <article class="card" style="margin-top:10px;">
            <h3>Trending Stories</h3>
            <table>
              <thead><tr><th>Headline</th><th>Views</th><th>Engagement</th><th>Homepage</th></tr></thead>
              <tbody>
                <?php if (empty($trendingWidget)): ?>
                  <tr><td colspan="4">No trending data available.</td></tr>
                <?php else: foreach ($trendingWidget as $item): ?>
                  <tr>
                    <td><?= htmlspecialchars($item['title'] ?? 'Untitled') ?></td>
                    <td><?= (int)($item['views'] ?? 0) ?></td>
                    <td><?= (int)($item['engagement_score'] ?? 0) ?> ↗</td>
                    <td><?= !empty($item['is_featured']) ? 'Featured' : 'Standard' ?></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </article>

          <article class="card" style="margin-top:10px;">
            <h3>Reporter Activity</h3>
            <table>
              <thead><tr><th>Reporter</th><th>Submitted Today</th><th>Approval</th><th>Assignments</th></tr></thead>
              <tbody>
                <?php if (empty($reporterWidget)): ?>
                  <tr><td colspan="4">No reporter activity yet.</td></tr>
                <?php else: foreach ($reporterWidget as $item): ?>
                  <tr>
                    <td><?= htmlspecialchars($item['author']) ?></td>
                    <td><?= (int)$item['submitted'] ?></td>
                    <td>92%</td>
                    <td><?= (int)max(0, (int)$item['submitted'] - 1) ?></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </article>
        </div>
      </section>
    </div>
  </main>
</div>

<script>
  const modeToggle = document.getElementById('modeToggle');
  if (modeToggle) {
    modeToggle.addEventListener('click', () => {
      document.body.classList.toggle('dark-mode');
    });
  }
</script>
</body>
</html>
