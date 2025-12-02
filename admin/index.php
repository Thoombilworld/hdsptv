<?php
require __DIR__ . '/../bootstrap.php';
hs_require_staff(['admin', 'editor', 'reporter']);
$settings = hs_settings();
$staff = hs_current_staff();
$role = $staff['role'] ?? 'admin';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard – NEWS HDSPTV</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
  <style>
    body { margin:0; font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; background:#020617; color:#E5E7EB; }
    header { padding:12px 20px; border-bottom:1px solid #111827; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; }
    .logo { font-size:16px; font-weight:700; letter-spacing:.12em; }
    nav a { margin-right:10px; font-size:12px; color:#9CA3AF; }
    nav a:hover, nav a.active { color:#FACC15; }
    nav a.highlight { color:#FACC15; font-weight:600; }
    .container { max-width:1100px; margin:18px auto; padding:0 16px; }
    .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:14px; }
    .card { background:radial-gradient(circle at top left,#1E3A8A,#020617); border-radius:14px; padding:16px 18px; box-shadow:0 22px 60px rgba(15,23,42,0.75); }
    .card h2 { margin:0 0 4px; font-size:15px; }
    .card p { margin:0; font-size:12px; color:#E5E7EB; }
    .pill { font-size:11px; text-transform:uppercase; letter-spacing:.16em; color:#FACC15; }
    a.button { display:inline-flex; align-items:center; justify-content:center; padding:7px 14px; border-radius:999px; border:1px solid rgba(248,250,252,.18); font-size:12px; color:#E5E7EB; margin-top:6px; }
    a.button:hover { border-color:#FACC15; text-decoration:none; }
  </style>
</head>
<body>
<header>
  <div class="logo">NEWS HDSPTV • <?= strtoupper($role) ?></div>
  <nav>
    <?php hs_render_admin_nav($role, 'dashboard'); ?>
  </nav>
</header>
<main class="container">
  <div class="grid">
    <section class="card">
      <div class="pill">Content</div>
      <h2>Articles & Categories</h2>
      <p>Integrate your content manager here (posts, categories, tags).</p>
      <a class="button" href="<?= hs_base_url('admin/content/index.php') ?>">Open Content Manager</a>
    </section>
    <?php if ($role === 'admin'): ?>
      <section class="card">
        <div class="pill">Branding</div>
        <h2>Site Settings</h2>
        <p>Control site title, tagline, default theme, and logo URL.</p>
        <a class="button" href="<?= hs_base_url('admin/settings.php') ?>">Edit Site Settings</a>
      </section>
    <?php endif; ?>
    <?php if (in_array($role, ['admin', 'editor'])): ?>
      <section class="card">
        <div class="pill">Homepage</div>
        <h2>Homepage Layout</h2>
        <p>Manage visibility of breaking ticker, featured slider, trending box, video, gallery, ads etc.</p>
        <a class="button" href="<?= hs_base_url('admin/homepage.php') ?>">Homepage Manager</a>
      </section>
    <?php endif; ?>
    <?php if ($role === 'admin'): ?>
      <section class="card">
        <div class="pill">SEO</div>
        <h2>SEO Center</h2>
        <p>Meta tags, keywords and default Open Graph data.</p>
        <a class="button" href="<?= hs_base_url('admin/seo.php') ?>">SEO Settings</a>
      </section>
      <section class="card">
        <div class="pill">Social</div>
        <h2>Social Media</h2>
        <p>Official HDSPTV links for Facebook, YouTube, Instagram, etc.</p>
        <a class="button" href="<?= hs_base_url('admin/social.php') ?>">Social Links</a>
      </section>
      <section class="card">
        <div class="pill">Ads</div>
        <h2>Ad Spots</h2>
        <p>Homepage top, sidebar and inline ads.</p>
        <a class="button" href="<?= hs_base_url('admin/ads.php') ?>">Ads Manager</a>
      </section>
      <section class="card">
        <div class="pill">Analytics</div>
        <h2>Performance</h2>
        <p>Track visitors, reads, devices, countries, and staff performance.</p>
        <a class="button" href="<?= hs_base_url('admin/analytics.php') ?>">Open Analytics</a>
      </section>
      <section class="card">
        <div class="pill">Staff</div>
        <h2>Staff Users</h2>
        <p>Admin, editor and reporter accounts.</p>
        <a class="button" href="<?= hs_base_url('admin/users.php') ?>">Staff Manager</a>
      </section>
    <?php else: ?>
      <section class="card">
        <div class="pill">Role</div>
        <h2>Your Access</h2>
        <p>You are signed in as a <?= htmlspecialchars($role) ?>. Content tools are enabled; administrative settings stay locked for your safety.</p>
        <a class="button" href="<?= hs_base_url('admin/content/index.php') ?>">Work on Articles</a>
      </section>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
