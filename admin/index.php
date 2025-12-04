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
</head>
<body class="admin-shell">
<header class="admin-header">
  <div class="admin-logo">NEWS HDSPTV • <?= strtoupper($role) ?></div>
  <nav class="admin-nav">
    <?php hs_render_admin_nav($role, 'dashboard'); ?>
  </nav>
</header>
<main class="admin-container">
  <div class="admin-grid">
    <section class="admin-card">
      <div class="admin-pill">Content</div>
      <h2>Articles & Categories</h2>
      <p>Integrate your content manager here (posts, categories, tags).</p>
      <a class="button" href="<?= hs_base_url('admin/content/index.php') ?>">Open Content Manager</a>
    </section>
    <?php if ($role === 'admin'): ?>
      <section class="admin-card">
        <div class="admin-pill">Branding</div>
        <h2>Site Settings</h2>
        <p>Control site title, tagline, default theme, and logo URL.</p>
        <a class="button" href="<?= hs_base_url('admin/settings.php') ?>">Edit Site Settings</a>
      </section>
    <?php endif; ?>
    <?php if (in_array($role, ['admin', 'editor'])): ?>
      <section class="admin-card">
        <div class="admin-pill">Homepage</div>
        <h2>Homepage Layout</h2>
        <p>Manage visibility of breaking ticker, featured slider, trending box, video, gallery, ads etc.</p>
        <a class="button" href="<?= hs_base_url('admin/homepage.php') ?>">Homepage Manager</a>
      </section>
    <?php endif; ?>
    <?php if ($role === 'admin'): ?>
      <section class="admin-card">
        <div class="admin-pill">SEO</div>
        <h2>SEO Center</h2>
        <p>Meta tags, keywords and default Open Graph data.</p>
        <a class="button" href="<?= hs_base_url('admin/seo.php') ?>">SEO Settings</a>
      </section>
      <section class="admin-card">
        <div class="admin-pill">Social</div>
        <h2>Social Media</h2>
        <p>Official HDSPTV links for Facebook, YouTube, Instagram, etc.</p>
        <a class="button" href="<?= hs_base_url('admin/social.php') ?>">Social Links</a>
      </section>
      <section class="admin-card">
        <div class="admin-pill">Ads</div>
        <h2>Ad Spots</h2>
        <p>Homepage top, sidebar and inline ads.</p>
        <a class="button" href="<?= hs_base_url('admin/ads.php') ?>">Ads Manager</a>
      </section>
      <section class="admin-card">
        <div class="admin-pill">Analytics</div>
        <h2>Performance</h2>
        <p>Track visitors, reads, devices, countries, and staff performance.</p>
        <a class="button" href="<?= hs_base_url('admin/analytics.php') ?>">Open Analytics</a>
      </section>
      <section class="admin-card">
        <div class="admin-pill">Staff</div>
        <h2>Staff Users</h2>
        <p>Admin, editor and reporter accounts.</p>
        <a class="button" href="<?= hs_base_url('admin/users.php') ?>">Staff Manager</a>
      </section>
    <?php else: ?>
      <section class="admin-card">
        <div class="admin-pill">Role</div>
        <h2>Your Access</h2>
        <p>You are signed in as a <?= htmlspecialchars($role) ?>. Content tools are enabled; administrative settings stay locked for your safety.</p>
        <a class="button" href="<?= hs_base_url('admin/content/index.php') ?>">Work on Articles</a>
      </section>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
