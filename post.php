<?php
require __DIR__ . '/bootstrap.php';

$settings = hs_settings();
$db = hs_db();

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    http_response_code(404);
    echo "Article not found.";
    exit;
}

$stmt = mysqli_prepare($db, "SELECT p.*, c.name AS category_name
                             FROM hs_posts p
                             LEFT JOIN hs_categories c ON c.id = p.category_id
                             WHERE p.slug = ? AND p.status = 'published'
                             LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $slug);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$post = $res ? mysqli_fetch_assoc($res) : null;

if (!$post) {
    http_response_code(404);
    echo "Article not found.";
    exit;
}

// Tags
$tags = [];
$tagRes = mysqli_query($db, "SELECT t.name, t.slug
                             FROM hs_tags t
                             JOIN hs_post_tags pt ON pt.tag_id = t.id
                             WHERE pt.post_id = " . (int)$post['id'] . "
                             ORDER BY t.name ASC");
if ($tagRes) {
    while ($row = mysqli_fetch_assoc($tagRes)) $tags[] = $row;
}

// Related posts (same category or region)
$related = [];
if (!empty($post['category_id'])) {
    $relStmt = mysqli_prepare($db, "SELECT p.id, p.title, p.slug, p.created_at
                                    FROM hs_posts p
                                    WHERE p.status='published'
                                      AND p.id != ?
                                      AND (p.category_id = ? OR p.region = ?)
                                    ORDER BY p.created_at DESC
                                    LIMIT 6");
    $region = $post['region'] ?? 'global';
    mysqli_stmt_bind_param($relStmt, 'iis', $post['id'], $post['category_id'], $region);
    mysqli_stmt_execute($relStmt);
    $relRes = mysqli_stmt_get_result($relStmt);
    if ($relRes) {
        while ($r = mysqli_fetch_assoc($relRes)) $related[] = $r;
    }
}

// Trending for sidebar
$trending = [];
$tRes = mysqli_query($db, "SELECT p.id, p.title, p.slug, p.created_at
                           FROM hs_posts p
                           WHERE p.status='published' AND p.is_trending=1
                           ORDER BY p.created_at DESC
                           LIMIT 6");
if ($tRes) {
    while ($r = mysqli_fetch_assoc($tRes)) $trending[] = $r;
}

function hs_post_date_local($p) {
    return !empty($p['created_at']) ? date('M j, Y', strtotime($p['created_at'])) : '';
}

// SEO meta
$site_title = $settings['site_title'] ?? 'NEWS HDSPTV';
$page_title = $post['title'] . ' – ' . $site_title;
$meta_desc = $post['excerpt'] ?: ($settings['seo_meta_description'] ?? '');
$meta_keys = $settings['seo_meta_keywords'] ?? '';
if (!empty($tags)) {
    $tag_names = array_column($tags, 'name');
    $meta_keys = $meta_keys . ', ' . implode(', ', $tag_names);
}
$categoryName = $post['category_name'] ?: 'News';
$canonical = hs_base_url('post.php?slug=' . urlencode($post['slug']));

$og_image = '';
if (!empty($post['image_main'])) {
    $og_image = hs_base_url($post['image_main']);
} elseif (!empty($settings['default_article_og_image'])) {
    $og_image = $settings['default_article_og_image'];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($meta_desc) ?>">
  <meta name="keywords" content="<?= htmlspecialchars($meta_keys) ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">

  <?php if ($og_image): ?>
    <meta property="og:image" content="<?= htmlspecialchars($og_image) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($og_image) ?>">
  <?php endif; ?>
  <meta property="og:title" content="<?= htmlspecialchars($post['title']) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($meta_desc) ?>">
  <meta property="og:type" content="article">
  <meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars($post['title']) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($meta_desc) ?>">

  <?php if (!empty($settings['seo_schema_enabled']) && $settings['seo_schema_enabled'] === '1'): ?>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "NewsArticle",
    "headline": <?= json_encode($post['title']) ?>,
    "datePublished": "<?= !empty($post['created_at']) ? date('c', strtotime($post['created_at'])) : '' ?>",
    "dateModified": "<?= !empty($post['updated_at']) ? date('c', strtotime($post['updated_at'])) : (!empty($post['created_at']) ? date('c', strtotime($post['created_at'])) : '') ?>",
    "description": <?= json_encode($meta_desc) ?>,
    "image": <?= json_encode($og_image ?: '') ?>,
    "author": {
      "@type": "Organization",
      "name": <?= json_encode($settings['seo_default_author'] ?? $site_title) ?>

    },
    "publisher": {
      "@type": "Organization",
      "name": <?= json_encode($site_title) ?>
    }
  }
  </script>
  <?php endif; ?>

  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">

  <style>
    :root {
      --hs-primary: #1E3A8A;
      --hs-primary-dark: #0B1120;
      --hs-accent: #FACC15;
      --hs-bg: #020617;
      --hs-card: #FFFFFF;
      --hs-border-soft: #E5E7EB;
      --hs-text-main: #111827;
      --hs-text-muted: #6B7280;
    }
    body {
      margin: 0;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: radial-gradient(circle at top, #1E3A8A 0, #020617 45%, #020617 100%);
      color: #F9FAFB;
    }
    a { color: var(--hs-primary); text-decoration: none; }
    a:hover { text-decoration: underline; }

    header {
      position: sticky;
      top: 0;
      z-index: 40;
      backdrop-filter: blur(18px);
      background: linear-gradient(90deg, rgba(15,23,42,0.96), rgba(15,23,42,0.98));
      border-bottom: 1px solid rgba(15,23,42,0.9);
      padding: 8px 18px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      flex-wrap:wrap;
    }
    .top-left { display:flex; align-items:center; gap:10px; }
    .logo-mark {
      width:32px; height:32px; border-radius:14px;
      background: radial-gradient(circle at 20% 0, #FACC15 0, #1E3A8A 45%, #020617 100%);
      display:flex; align-items:center; justify-content:center;
      font-weight:800; font-size:16px; color:#F9FAFB;
      box-shadow:0 10px 25px rgba(15,23,42,0.6);
    }
    .logo-text { display:flex; flex-direction:column; }
    .logo-text-main { font-weight:800; letter-spacing:.18em; font-size:13px; }
    .logo-text-tag { font-size:11px; color:#E5E7EB; opacity:.85; }

    .nav-main {
      display:flex; align-items:center; gap:12px;
      font-size:12px; text-transform:uppercase; letter-spacing:.12em;
    }
    .nav-main a { color:#E5E7EB; padding:4px 6px; border-radius:999px; }
    .nav-main a:hover { background:rgba(15,23,42,0.8); color:#FACC15; text-decoration:none; }

    .nav-search {
      margin-left:auto;
      margin-right:12px;
      margin-top:4px;
    }
    .nav-search input[type="text"] {
      padding:4px 10px;
      border-radius:999px;
      border:1px solid rgba(148,163,184,0.9);
      font-size:12px;
      background:#FFFFFF;
      color:#111827;
      min-width:200px;
    }
    .nav-search input[type="text"]::placeholder {
      color:#9CA3AF;
    }
    .nav-search button { display:none; }

    .user-bar { font-size:11px; color:#E5E7EB; text-align:right; }
    .user-bar a { color:#FACC15; }

    .page {
      width:100%;
      min-height:100vh;
      padding:18px 12px 32px;
      box-sizing:border-box;
    }

    .layout-article {
      max-width:1160px;
      margin:0 auto;
      display:grid;
      grid-template-columns:minmax(0,3fr) minmax(0,1.3fr);
      gap:18px;
    }

    .article-card {
      background:#F9FAFB;
      border-radius:18px;
      box-shadow:0 20px 45px rgba(15,23,42,0.6);
      color:var(--hs-text-main);
      overflow:hidden;
    }
    .article-hero-image {
      width:100%;
      max-height:420px;
      background:#e5e7eb;
      overflow:hidden;
    }
    .article-hero-image img {
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
    }

    .article-inner {
      padding:18px 20px 20px;
    }
    .breadcrumb {
      font-size:11px;
      color:#9CA3AF;
      margin-bottom:6px;
    }
    .breadcrumb a { color:#9CA3AF; }
    .breadcrumb a:hover { color:#FACC15; text-decoration:none; }

    .article-kicker {
      font-size:11px;
      text-transform:uppercase;
      letter-spacing:.18em;
      color:var(--hs-primary);
      margin-bottom:6px;
    }
    .article-title {
      font-size:26px;
      font-weight:800;
      line-height:1.25;
      margin-bottom:8px;
      color:#0F172A;
    }
    .article-meta {
      font-size:12px;
      color:var(--hs-text-muted);
      margin-bottom:12px;
    }

    .article-tags {
      margin-top:12px;
      font-size:12px;
    }
    .article-tags a {
      display:inline-block;
      padding:3px 8px;
      border-radius:999px;
      background:#EFF6FF;
      color:#1D4ED8;
      margin:0 4px 4px 0;
      font-size:11px;
    }

    .article-body {
      margin-top:14px;
      font-size:15px;
      line-height:1.7;
      color:var(--hs-text-main);
    }
    .article-body p { margin:0 0 1em; }
    .article-body h2,
    .article-body h3,
    .article-body h4 {
      margin-top:1.4em;
      margin-bottom:0.6em;
      color:#111827;
    }
    .article-body img {
      max-width:100%;
      height:auto;
      margin:12px auto;
      display:block;
      border-radius:8px;
    }

    .share-block {
      margin-top:20px;
      padding-top:12px;
      border-top:1px solid var(--hs-border-soft);
      font-size:12px;
      color:var(--hs-text-muted);
    }
    .share-links a {
      display:inline-block;
      margin-right:8px;
      padding:6px 10px;
      border-radius:999px;
      border:1px solid #E5E7EB;
      font-size:11px;
      color:#111827;
      background:#FFFFFF;
    }

    .related-block {
      margin-top:18px;
      padding-top:14px;
      border-top:1px solid var(--hs-border-soft);
    }
    .related-title {
      font-size:13px;
      text-transform:uppercase;
      letter-spacing:.16em;
      color:#6B7280;
      margin-bottom:8px;
    }
    .related-list {
      list-style:none;
      padding:0;
      margin:0;
      font-size:14px;
    }
    .related-list li {
      margin-bottom:6px;
    }

    .sidebar {
      display:flex;
      flex-direction:column;
      gap:14px;
    }
    .sidebar-card {
      background:rgba(15,23,42,0.96);
      border-radius:16px;
      border:1px solid rgba(15,23,42,0.9);
      padding:14px 14px 16px;
      color:#E5E7EB;
      box-shadow:0 16px 40px rgba(15,23,42,0.8);
    }
    .sidebar-title {
      font-size:13px;
      text-transform:uppercase;
      letter-spacing:.16em;
      margin-bottom:8px;
      color:#FACC15;
    }

    .sidebar-list {
      list-style:none;
      padding:0;
      margin:0;
      font-size:13px;
    }
    .sidebar-list li {
      margin-bottom:6px;
    }
    .sidebar-list a { color:#E5E7EB; }
    .sidebar-list a:hover { color:#FACC15; text-decoration:none; }

    footer {
      border-top:1px solid rgba(31,41,55,0.9);
      padding:10px 18px 16px;
      font-size:11px;
      color:#9CA3AF;
      text-align:center;
      background:linear-gradient(180deg, rgba(15,23,42,0.98), #020617);
    }

    @media (max-width:960px) {
      .layout-article {
        grid-template-columns:minmax(0,1fr);
      }
    }
    @media (max-width:640px) {
      header { padding:8px 10px; }
      .page { padding:14px 8px 24px; }
      .article-inner { padding:14px 14px 16px; }
    }
  
.share-block {
  margin: 12px 0 16px;
  padding: 8px 0;
  border-top: 1px solid rgba(15,23,42,0.5);
  border-bottom: 1px solid rgba(15,23,42,0.5);
}

  </style>
</head>
<body>
<header>
  <div class="top-left">
    <a href="<?= hs_base_url('index.php') ?>" class="logo-link">
      <div class="logo-mark">H</div>
      <div class="logo-text">
      <div class="logo-text-main">NEWS HDSPTV</div>
      <div class="logo-text-tag"><?= htmlspecialchars($settings['tagline'] ?? 'GCC • INDIA • KERALA • WORLD') ?></div>
    </div>
  </div>
  <nav class="nav-main">
    <a href="<?= hs_base_url('index.php#top') ?>">Home</a>
    <a href="<?= hs_base_url('category.php?slug=india') ?>">India</a>
    <a href="<?= hs_base_url('category.php?slug=gcc') ?>">GCC</a>
    <a href="<?= hs_base_url('category.php?slug=kerala') ?>">Kerala</a>
    <a href="<?= hs_base_url('category.php?slug=world') ?>">World</a>
    <a href="<?= hs_base_url('category.php?slug=sports') ?>">Sports</a>
    <a href="<?= hs_base_url('category.php?slug=entertainment') ?>">Entertainment</a>
    <a href="<?= hs_base_url('category.php?slug=business') ?>">Business</a>
    <a href="<?= hs_base_url('category.php?slug=technology') ?>">Technology</a>
    <a href="<?= hs_base_url('category.php?slug=lifestyle') ?>">Lifestyle</a>
    <a href="<?= hs_base_url('category.php?slug=health') ?>">Health</a>
    <a href="<?= hs_base_url('category.php?slug=travel') ?>">Travel</a>
    <a href="<?= hs_base_url('category.php?slug=auto') ?>">Auto</a>
    <a href="<?= hs_base_url('category.php?slug=opinion') ?>">Opinion</a>
    <a href="<?= hs_base_url('category.php?slug=politics') ?>">Politics</a>
    <a href="<?= hs_base_url('category.php?slug=crime') ?>">Crime</a>
    <a href="<?= hs_base_url('category.php?slug=education') ?>">Education</a>
    <a href="<?= hs_base_url('category.php?slug=religion') ?>">Religion</a>
  </nav>
  <form class="nav-search" action="<?= hs_base_url('search.php') ?>" method="get">
    <input type="text" name="q" placeholder="Search news..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
    <button type="submit">Search</button>
  </form>
  <div class="user-bar">
    <?php $u = hs_current_user(); ?>
    <?php if ($u): ?>
      <?= htmlspecialchars($u['name']) ?>
      <?php if (!empty($u['is_premium'])): ?> · <strong>Premium</strong><?php endif; ?>
      · <a href="<?= hs_base_url('user/dashboard.php') ?>">Dashboard</a>
      · <a href="<?= hs_base_url('auth/logout.php') ?>">Logout</a>
    <?php else: ?>
      <a href="<?= hs_base_url('auth/login.php') ?>">Login</a> ·
      <a href="<?= hs_base_url('auth/register.php') ?>">Register</a>
    <?php endif; ?>
  </div>
</header>

<main class="page">
  <div class="layout-article">
    <article class="article-card">
      <?php if (!empty($post['image_main'])): ?>
        <div class="article-hero-image">
          <img src="<?= hs_base_url($post['image_main']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
        </div>
      <?php endif; ?>
      <div class="article-inner">
        <nav class="breadcrumb">
          <a href="<?= hs_base_url('index.php') ?>">Home</a>
          <?php if (!empty($categoryName)): ?>
            › <a href="<?= hs_base_url('category.php?slug=' . urlencode(strtolower($categoryName))) ?>"><?= htmlspecialchars($categoryName) ?></a>
          <?php endif; ?>
        </nav>
        <div class="article-kicker">
          <?= htmlspecialchars($categoryName) ?>
          <?php if (!empty($post['region']) && $post['region'] !== 'global'): ?>
            · <?= strtoupper(htmlspecialchars($post['region'])) ?>
          <?php endif; ?>
        </div>
        <h1 class="article-title"><?= htmlspecialchars($post['title']) ?></h1>
        <div class="article-meta">
          <?= hs_post_date_local($post) ?>
          <?php if (!empty($post['author_name'])): ?>
            · By <?= htmlspecialchars($post['author_name']) ?>
          <?php endif; ?>
        </div>

        <div class="share-block">
          <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=YOUR_PROFILE_ID"></script>
          <div class="addthis_inline_share_toolbox"></div>
        </div>

        <div class="article-body">
          <?php if (!empty($post['content'])): ?>
            <?= $post['content'] ?>
          <?php else: ?>
            <p>No content.</p>
          <?php endif; ?>
        </div>

        <?php if (!empty($tags)): ?>
          <div class="article-tags">
            <strong>Tags:</strong>
            <?php foreach ($tags as $tag): ?>
              <a href="<?= hs_base_url('tag.php?slug=' . urlencode($tag['slug'])) ?>"><?= htmlspecialchars($tag['name']) ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="share-block">
          <div>Share this article</div>
          <?php
            $shareUrl = urlencode($canonical);
            $shareText = urlencode($post['title'] . ' - ' . $site_title);
          ?>
          <div class="share-links">
            <a href="https://api.whatsapp.com/send?text=<?= $shareText ?>%20<?= $shareUrl ?>" target="_blank">WhatsApp</a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank">Facebook</a>
            <a href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareText ?>" target="_blank">X</a>
            <a href="https://t.me/share/url?url=<?= $shareUrl ?>&text=<?= $shareText ?>" target="_blank">Telegram</a>
          </div>
        </div>

        <?php if (!empty($related)): ?>
          <div class="related-block">
            <div class="related-title">More from <?= htmlspecialchars($categoryName) ?></div>
            <ul class="related-list">
              <?php foreach ($related as $r): ?>
                <li>
                  <a href="<?= hs_base_url('post.php?slug=' . urlencode($r['slug'])) ?>"><?= htmlspecialchars($r['title']) ?></a>
                  <span style="font-size:11px;color:#9CA3AF;"> · <?= hs_post_date_local($r) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>
    </article>

    <aside class="sidebar">
      <section class="sidebar-card">
        <div class="sidebar-title">Trending</div>
        <?php if (empty($trending)): ?>
          <p style="font-size:12px;color:#9CA3AF;">No trending posts.</p>
        <?php else: ?>
          <ul class="sidebar-list">
            <?php foreach ($trending as $t): ?>
              <li>
                <a href="<?= hs_base_url('post.php?slug=' . urlencode($t['slug'])) ?>"><?= htmlspecialchars($t['title']) ?></a>
                <div style="font-size:11px;color:#9CA3AF;"><?= hs_post_date_local($t) ?></div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>

      <section class="sidebar-card">
        <div class="sidebar-title">Homepage</div>
        <p style="font-size:12px;color:#E5E7EB;">
          <a href="<?= hs_base_url('index.php') ?>" style="color:#FACC15;">← Back to homepage</a>
        </p>
      </section>
    </aside>
  </div>
</main>

<footer>
  © <?= date('Y') ?> NEWS HDSPTV. All rights reserved.
</footer>
</body>
</html>
