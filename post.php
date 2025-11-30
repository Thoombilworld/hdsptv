<?php
require __DIR__ . '/bootstrap.php';

$settings = hs_settings();
$db = hs_db();

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    http_response_code(404);
    echo hs_t('article_not_found', 'Article not found.');
    exit;
}

$stmt = mysqli_prepare($db, "SELECT p.*, c.name AS category_name, c.slug AS category_slug
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
    echo hs_t('article_not_found', 'Article not found.');
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

// Adjacent navigation
$prevPost = $nextPost = null;
if (!empty($post['created_at'])) {
    $catClause = !empty($post['category_id']) ? ' AND category_id = ?' : '';

    $prevSql = "SELECT title, slug FROM hs_posts WHERE status='published' AND created_at < ?{$catClause} ORDER BY created_at DESC LIMIT 1";
    $prevStmt = mysqli_prepare($db, $prevSql);
    if ($prevStmt) {
        if (!empty($post['category_id'])) {
            mysqli_stmt_bind_param($prevStmt, 'si', $post['created_at'], $post['category_id']);
        } else {
            mysqli_stmt_bind_param($prevStmt, 's', $post['created_at']);
        }
        mysqli_stmt_execute($prevStmt);
        $prevRes = mysqli_stmt_get_result($prevStmt);
        $prevPost = $prevRes ? mysqli_fetch_assoc($prevRes) : null;
    }

    $nextSql = "SELECT title, slug FROM hs_posts WHERE status='published' AND created_at > ?{$catClause} ORDER BY created_at ASC LIMIT 1";
    $nextStmt = mysqli_prepare($db, $nextSql);
    if ($nextStmt) {
        if (!empty($post['category_id'])) {
            mysqli_stmt_bind_param($nextStmt, 'si', $post['created_at'], $post['category_id']);
        } else {
            mysqli_stmt_bind_param($nextStmt, 's', $post['created_at']);
        }
        mysqli_stmt_execute($nextStmt);
        $nextRes = mysqli_stmt_get_result($nextStmt);
        $nextPost = $nextRes ? mysqli_fetch_assoc($nextRes) : null;
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

hs_track_event([
    'type'        => 'post',
    'post_id'     => (int)$post['id'],
    'category_id' => (int)($post['category_id'] ?? 0),
    'reporter_id' => (int)($post['reporter_id'] ?? 0),
    'editor_id'   => (int)($post['editor_id'] ?? 0),
]);

$ads = hs_active_ads();
$ad_for = function ($slot) use ($ads) {
    return $ads[$slot] ?? null;
};
$render_ad = function ($slot, $label = '') use ($ad_for) {
    $ad = $ad_for($slot);
    if (!$ad) return '';

    $html = '<div class="ad-slot">';
    if (!empty($ad['code'])) {
        $html .= $ad['code'];
    } elseif (!empty($ad['image_url'])) {
        $href = htmlspecialchars($ad['link_url'] ?: '#');
        $html .= '<a href="' . $href . '" target="_blank" rel="noopener">';
        $html .= '<img src="' . htmlspecialchars(hs_base_url($ad['image_url'])) . '" alt="' . htmlspecialchars(hs_t('advertisement', 'Advertisement')) . '">';
        $html .= '</a>';
    }
    if ($label !== '') {
        $html .= '<span class="label">' . htmlspecialchars($label) . '</span>';
    }
    $html .= '</div>';

    return $html;
};

function hs_post_date_local($p) {
    return !empty($p['created_at']) ? date('M j, Y', strtotime($p['created_at'])) : '';
}

$authorName = $post['author_name'] ?? ($settings['seo_default_author'] ?? 'NEWS HDSPTV');
$reporterName = $post['reporter_name'] ?? ($post['author_name'] ?? ($settings['seo_default_author'] ?? 'NEWS HDSPTV'));
$regionLabel = !empty($post['region']) && $post['region'] !== 'global'
    ? strtoupper($post['region'])
    : 'GLOBAL';

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
$categorySlug = $post['category_slug'] ?: strtolower($categoryName);
$canonical = hs_news_url($post['slug']);
$languageCode = hs_current_language_code();
$languageDir = hs_is_rtl($languageCode) ? 'rtl' : 'ltr';

$og_image = '';
if (!empty($post['image_main'])) {
    $og_image = hs_base_url($post['image_main']);
} elseif (!empty($settings['default_article_og_image'])) {
    $og_image = $settings['default_article_og_image'];
}
?>
<!doctype html>
<html lang="<?= htmlspecialchars($languageCode) ?>" dir="<?= htmlspecialchars($languageDir) ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($meta_desc) ?>">
  <meta name="keywords" content="<?= htmlspecialchars($meta_keys) ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
  <link rel="icon" href="<?= htmlspecialchars($settings['favicon'] ?? hs_base_url('assets/images/favicon.png')) ?>">

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
      padding: 10px 18px;
      display:grid;
      grid-template-columns:auto 1fr;
      gap:12px;
      align-items:center;
    }
    .top-left { display:flex; align-items:center; gap:10px; min-width:0; }
    .logo-link { display:flex; align-items:center; gap:10px; color:inherit; text-decoration:none; }
    .logo-link:hover { color:#FACC15; text-decoration:none; }
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

    .nav-toggle { display:none; align-items:center; justify-content:center; width:40px; height:40px; border-radius:12px; border:1px solid rgba(148,163,184,0.6); background:rgba(15,23,42,0.8); color:#E5E7EB; cursor:pointer; }
    .header-right { display:flex; align-items:center; gap:12px; justify-content:flex-end; flex-wrap:wrap; }

    .nav-main { display:flex; align-items:center; gap:12px; font-size:12px; text-transform:uppercase; letter-spacing:.12em; flex-wrap:wrap; }
    .nav-main a { color:#E5E7EB; padding:6px 8px; border-radius:999px; }
    .nav-main a:hover { background:rgba(15,23,42,0.8); color:#FACC15; text-decoration:none; }

    .nav-search { margin-left:auto; margin-right:12px; margin-top:4px; }
    .nav-search input[type="text"] {
      padding:6px 12px;
      border-radius:999px;
      border:1px solid rgba(148,163,184,0.9);
      font-size:12px;
      background:#FFFFFF;
      color:#111827;
      min-width:200px;
    }
    .nav-search input[type="text"]::placeholder { color:#9CA3AF; }
    .nav-search button { display:none; }
    .language-switcher { min-width:120px; padding:6px 10px; border-radius:10px; border:1px solid rgba(148,163,184,0.5); background:#0B1120; color:#E5E7EB; }

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
      max-height:520px;
      background:#e5e7eb;
      overflow:hidden;
      position:relative;
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
    .article-meta { font-size:12px; color:var(--hs-text-muted); margin-bottom:12px; display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
    .badge { display:inline-flex; align-items:center; gap:6px; padding:5px 10px; border-radius:999px; font-size:11px; font-weight:700; letter-spacing:.02em; }
    .badge-category { background:#EEF2FF; color:#312E81; border:1px solid #C7D2FE; }
    .badge-region { background:#DCFCE7; color:#065F46; border:1px solid #A7F3D0; }
    .pill { display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:12px; font-size:12px; background:#0F172A; color:#E5E7EB; border:1px solid rgba(15,23,42,0.2); box-shadow:0 10px 30px rgba(15,23,42,0.2); }
    .pill svg { width:14px; height:14px; }

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

    .related-block { margin-top:18px; padding-top:14px; border-top:1px solid var(--hs-border-soft); }
    .related-title { font-size:13px; text-transform:uppercase; letter-spacing:.16em; color:#6B7280; margin-bottom:10px; }
    .related-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px; }
    .related-card { background:#F8FAFC; border:1px solid #E5E7EB; border-radius:14px; padding:12px 14px; box-shadow:0 10px 28px rgba(15,23,42,0.08); }
    .related-card a { color:#0F172A; font-weight:700; display:block; margin-bottom:6px; }
    .related-card a:hover { color:#1D4ED8; text-decoration:none; }
    .related-meta { font-size:12px; color:#6B7280; }

    .article-nav { display:flex; justify-content:space-between; gap:10px; margin:18px 0 6px; padding:14px 0; border-top:1px solid var(--hs-border-soft); border-bottom:1px solid var(--hs-border-soft); }
    .article-nav a { display:block; background:#0F172A; color:#E5E7EB; padding:10px 12px; border-radius:12px; border:1px solid rgba(15,23,42,0.25); box-shadow:0 10px 32px rgba(15,23,42,0.25); width:100%; }
    .article-nav a:hover { background:#111827; text-decoration:none; color:#FACC15; }

    .comment-block { margin-top:18px; padding:14px; background:#F8FAFC; border-radius:14px; border:1px solid #E5E7EB; }
    .comment-title { font-size:14px; font-weight:800; margin-bottom:8px; color:#0F172A; }
    .comment-note { font-size:12px; color:#6B7280; margin-bottom:10px; }
    .comment-field { width:100%; min-height:120px; border-radius:12px; border:1px solid #E5E7EB; padding:10px; font-size:14px; box-sizing:border-box; }
    .comment-action { margin-top:10px; display:flex; justify-content:flex-end; }
    .comment-action button { background:#1D4ED8; color:#FFF; border:none; padding:10px 14px; border-radius:10px; font-weight:700; cursor:not-allowed; opacity:.7; }

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
    .footer-links { margin-bottom:6px; }
    .footer-links a { color:#E5E7EB; }
    .footer-links a:hover { color:#FACC15; text-decoration:none; }

    @media (max-width:960px) {
      header { grid-template-columns:1fr auto; align-items:flex-start; }
      .nav-toggle { display:inline-flex; }
      .header-right { width:100%; display:none; flex-direction:column; align-items:flex-start; }
      header.nav-open .header-right { display:flex; }
      .nav-main { width:100%; flex-direction:column; align-items:flex-start; }
      .nav-main a { width:100%; padding:10px; border-radius:12px; background:rgba(15,23,42,0.65); }
      .nav-search { width:100%; margin:0; }
      .nav-search input[type="text"] { width:100%; }
      .user-bar { width:100%; text-align:left; }
      .language-switcher { width:100%; }
      .layout-article { grid-template-columns:minmax(0,1fr); }
      .article-title { font-size:22px; }
    }
    @media (max-width:640px) {
      header { padding:8px 10px; }
      .page { padding:14px 8px 24px; }
      .article-inner { padding:14px 14px 16px; }
      .article-title { font-size:20px; }
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
    </a>
  </div>
  <button class="nav-toggle" aria-label="Toggle menu" aria-expanded="false">☰</button>
  <div class="header-right">
      <nav class="nav-main">
        <?php foreach (hs_primary_nav_items() as $item): ?>
          <a href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars(hs_t('nav_' . $item['slug'], $item['label'])) ?></a>
        <?php endforeach; ?>
      </nav>
      <div class="nav-utilities stack-mobile" style="align-items:flex-start; width:100%;">
        <form class="nav-search" action="<?= hs_search_url() ?>" method="get">
          <input type="text" name="q" placeholder="<?= htmlspecialchars(hs_t('search_placeholder', 'Search news...')) ?>" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
          <button type="submit"><?= htmlspecialchars(hs_t('search_label', 'Search')) ?></button>
        </form>
        <form method="get" action="" class="language-form" style="display:flex; align-items:center; gap:6px;">
          <label class="language-label sr-only" for="language-select"><?= htmlspecialchars(hs_t('language_label', 'Language')) ?></label>
          <select id="language-select" class="language-switcher" name="lang" aria-label="<?= htmlspecialchars(hs_t('language_label', 'Language')) ?>" onchange="this.form.submit()">
            <?php foreach (hs_supported_languages() as $code => $label): ?>
              <option value="<?= htmlspecialchars($code) ?>" <?= $languageCode === $code ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
            <?php endforeach; ?>
          </select>
          <?php foreach ($_GET as $key => $value): if ($key === 'lang') continue; ?>
            <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
          <?php endforeach; ?>
        </form>
      </div>
      <div class="user-bar">
        <?php $u = hs_current_user(); ?>
        <?php if ($u): ?>
          <?= htmlspecialchars($u['name']) ?>
          <?php if (!empty($u['is_premium'])): ?> · <strong><?= htmlspecialchars(hs_t('nav_premium', 'Premium')) ?></strong><?php endif; ?>
          · <a href="<?= hs_dashboard_url() ?>"><?= htmlspecialchars(hs_t('nav_dashboard', 'Dashboard')) ?></a>
          · <a href="<?= hs_logout_url() ?>"><?= htmlspecialchars(hs_t('nav_logout', 'Logout')) ?></a>
        <?php else: ?>
          <a href="<?= hs_login_url() ?>"><?= htmlspecialchars(hs_t('nav_login', 'Login')) ?></a> ·
          <a href="<?= hs_register_url() ?>"><?= htmlspecialchars(hs_t('nav_register', 'Register')) ?></a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <?= $render_ad('global_top', hs_t('advertisement', 'Advertisement')) ?>

<main class="page">
  <div class="layout-article">
    <article class="article-card">
      <?php if (!empty($post['image_main'])): ?>
        <div class="article-hero-image">
          <img src="<?= hs_base_url($post['image_main']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
        </div>
      <?php endif; ?>
  <?= $render_ad('article_top', hs_t('advertisement', 'Advertisement')) ?>
      <div class="article-inner">
        <nav class="breadcrumb">
          <a href="<?= hs_base_url('index.php') ?>">Home</a>
          <?php if (!empty($categoryName)): ?>
            › <a href="<?= hs_category_url(strtolower($categorySlug)) ?>"><?= htmlspecialchars($categoryName) ?></a>
          <?php endif; ?>
        </nav>
        <div class="article-kicker">Premium Report</div>
        <h1 class="article-title"><?= htmlspecialchars($post['title']) ?></h1>
        <div class="article-meta">
          <span class="badge badge-category">Category · <?= htmlspecialchars($categoryName) ?></span>
          <span class="badge badge-region">Region · <?= htmlspecialchars($regionLabel) ?></span>
          <span class="pill" title="Author">
            <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 10a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4 0-6 1.79-6 3.33C4 16.67 5.33 18 10 18s6-1.33 6-2.67C16 13.79 14 12 10 12Z"/></svg>
            <?= htmlspecialchars($authorName) ?>
          </span>
          <span class="pill" title="Reporter">
            <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 4.5A1.5 1.5 0 0 1 4.5 3h11A1.5 1.5 0 0 1 17 4.5v11A1.5 1.5 0 0 1 15.5 17h-11A1.5 1.5 0 0 1 3 15.5Z"/><path d="M6 6h8v2H6Zm0 3.5h8v2H6Zm0 3.5h5v2H6Z"/></svg>
            <?= htmlspecialchars($reporterName) ?>
          </span>
          <span style="color:#64748B;">· <?= hs_post_date_local($post) ?></span>
        </div>

        <div class="share-block">
          <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=YOUR_PROFILE_ID"></script>
          <div class="addthis_inline_share_toolbox"></div>
        </div>

        <div class="article-body">
          <?php if (!empty($post['content'])): ?>
            <?= $post['content'] ?>
          <?php else: ?>
            <p><?= htmlspecialchars(hs_t('no_content', 'No content.')) ?></p>
          <?php endif; ?>
        </div>

        <?= $render_ad('article_inline', hs_t('advertisement', 'Advertisement')) ?>

        <?php if (!empty($tags)): ?>
          <div class="article-tags">
            <strong><?= htmlspecialchars(hs_t('tags_label', 'Tags:')) ?></strong>
            <?php foreach ($tags as $tag): ?>
            <a href="<?= hs_tag_url($tag['slug']) ?>"><?= htmlspecialchars($tag['name']) ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="share-block">
          <div><?= htmlspecialchars(hs_t('share_article', 'Share this article')) ?></div>
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
            <div class="related-title"><?= htmlspecialchars(hs_t('related_articles', 'Related articles')) ?></div>
            <div class="related-grid">
              <?php foreach ($related as $r): ?>
                <article class="related-card">
                  <a href="<?= hs_news_url($r['slug']) ?>"><?= htmlspecialchars($r['title']) ?></a>
                  <div class="related-meta"><?= htmlspecialchars(hs_t('published_on', 'Published {date}', ['date' => hs_post_date_local($r)])) ?></div>
                </article>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($prevPost || $nextPost): ?>
          <div class="article-nav">
            <div style="flex:1;">
              <?php if ($prevPost): ?>
                <a href="<?= hs_news_url($prevPost['slug']) ?>">← <?= htmlspecialchars(hs_t('previous_article', 'Previous')) ?>: <?= htmlspecialchars($prevPost['title']) ?></a>
              <?php endif; ?>
            </div>
            <div style="flex:1; text-align:right;">
              <?php if ($nextPost): ?>
                <a href="<?= hs_news_url($nextPost['slug']) ?>"><?= htmlspecialchars(hs_t('next_article', 'Next')) ?>: <?= htmlspecialchars($nextPost['title']) ?> →</a>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="comment-block" aria-live="polite">
          <div class="comment-title"><?= htmlspecialchars(hs_t('comments_title', 'Comments')) ?></div>
          <div class="comment-note"><?= htmlspecialchars(hs_t('comments_note', 'Optional section — wire up your preferred provider or enable native comments.')) ?></div>
          <textarea class="comment-field" placeholder="<?= htmlspecialchars(hs_t('comments_placeholder', 'Share your thoughts (coming soon)...')) ?>" disabled></textarea>
          <div class="comment-action"><button type="button" disabled><?= htmlspecialchars(hs_t('comments_submit', 'Post Comment')) ?></button></div>
        </div>
      </div>
    </article>

    <aside class="sidebar">
      <?= $render_ad('article_sidebar', hs_t('advertisement', 'Advertisement')) ?>
      <section class="sidebar-card">
        <div class="sidebar-title">Trending</div>
        <?php if (empty($trending)): ?>
          <p style="font-size:12px;color:#9CA3AF;">No trending posts.</p>
        <?php else: ?>
          <ul class="sidebar-list">
            <?php foreach ($trending as $t): ?>
              <li>
                  <a href="<?= hs_news_url($t['slug']) ?>"><?= htmlspecialchars($t['title']) ?></a>
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

<?= $render_ad('global_footer', hs_t('advertisement', 'Advertisement')) ?>

<footer>
  <div class="footer-links"><?= hs_footer_links_html(); ?></div>
  <div class="footer-copy">© <?= date('Y') ?> <?= htmlspecialchars($settings['site_title'] ?? 'NEWS HDSPTV') ?>. <?= htmlspecialchars(hs_t('footer_rights', 'All rights reserved.')) ?></div>
</footer>
<script>
  const navToggle = document.querySelector('.nav-toggle');
  const headerEl = document.querySelector('header');
  if (navToggle && headerEl) {
    navToggle.addEventListener('click', () => {
      const isOpen = headerEl.classList.toggle('nav-open');
      navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  }
</script>
</body>
</html>
