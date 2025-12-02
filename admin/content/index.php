
<?php
require __DIR__ . '/../../bootstrap.php';
hs_require_staff(['admin', 'editor', 'reporter']);
$settings = hs_settings();
$staff = hs_current_staff();
$role = $staff['role'] ?? 'admin';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Content Manager – NEWS HDSPTV</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
  <style>
    body { margin:0; font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; background:#020617; color:#E5E7EB; }
    header { padding:12px 20px; border-bottom:1px solid #111827; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; }
    .logo { font-size:16px; font-weight:700; letter-spacing:.12em; }
    nav a { margin-right:10px; font-size:12px; color:#9CA3AF; }
    nav a:hover, nav a.active { color:#FACC15; }
    nav a.highlight { color:#FACC15; font-weight:600; }
    .container { max-width:1040px; margin:18px auto; padding:0 16px 24px; }
    h1 { margin:0 0 6px; font-size:20px; }
    p.lead { margin:0 0 16px; font-size:13px; color:#E5E7EB; }
    .cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:14px; margin-top:10px; }
    .card { background:radial-gradient(circle at top left,#1E3A8A,#020617); border-radius:18px; padding:16px 18px; box-shadow:0 18px 50px rgba(15,23,42,0.8); }
    .card h2 { margin:0 0 4px; font-size:15px; }
    .card p { margin:0 0 8px; font-size:12px; color:#E5E7EB; }
    a.button { display:inline-flex; align-items:center; justify-content:center; border-radius:999px; border:1px solid rgba(148,163,184,.4); padding:6px 12px; background:rgba(15,23,42,.7); font-size:12px; color:#E5E7EB; }
    a.button:hover { border-color:#FACC15; text-decoration:none; }
  </style>
</head>
<body>
<header>
  <div class="logo">NEWS HDSPTV • <?= strtoupper($role) ?> CONTENT</div>
  <nav>
    <?php hs_render_admin_nav($role, 'content'); ?>
  </nav>
</header>
<main class="container">
  <h1>Content Manager</h1>
  <p class="lead">Manage the newsroom pipeline. Access depends on your role.</p>
  <div class="cards">
    <section class="card">
      <h2>Articles</h2>
      <p>Create and edit news posts, set category, region, type (standard, video, gallery) and publish status.</p>
      <a class="button" href="<?= hs_base_url('admin/content/articles.php') ?>">Open Articles</a>
    </section>
    <?php if (in_array($role, ['admin', 'editor'])): ?>
      <section class="card">
        <h2>Categories</h2>
        <p>News sections such as India, GCC, Kerala, World, Sports and custom categories.</p>
        <a class="button" href="<?= hs_base_url('admin/content/categories.php') ?>">Open Categories</a>
      </section>
      <section class="card">
        <h2>Tags</h2>
        <p>Topic tags used for SEO, tag pages and internal linking.</p>
        <a class="button" href="<?= hs_base_url('admin/content/tags.php') ?>">Open Tags</a>
      </section>
    <?php else: ?>
      <section class="card">
        <h2>Reporter Brief</h2>
        <p>Reporters can draft and publish stories. Categories and tags stay pre-defined by admins and editors.</p>
        <a class="button" href="<?= hs_base_url('admin/content/articles.php') ?>">Start Writing</a>
      </section>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
