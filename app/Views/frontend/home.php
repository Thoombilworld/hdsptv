<?php
  $theme = hs_current_theme();
  $palette = hs_theme_palette($theme);
  $ads = $ads ?? [];
  $ad_for = function ($slot) use ($ads) {
      return $ads[$slot] ?? null;
  };

  $languageCode = hs_current_language_code();

  function hs_render_ad($ad) {
      if (!$ad) return '';
      if (!empty($ad['code'])) {
          return $ad['code'];
      }
      if (!empty($ad['image_url'])) {
          $img = '<img src="' . hs_base_url($ad['image_url']) . '" alt="Advertisement">';
          if (!empty($ad['link_url'])) {
              return '<a href="' . htmlspecialchars($ad['link_url']) . '" target="_blank" rel="noopener">' . $img . '</a>';
          }
          return $img;
      }
      return '';
  }
?>
<!doctype html>
<html lang="<?= htmlspecialchars($languageCode) ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($settings['site_title'] ?? 'NEWS HDSPTV') ?></title>
  <meta name="description" content="<?= htmlspecialchars($settings['seo_meta_description'] ?? ($settings['tagline'] ?? '')) ?>">
  <meta name="keywords" content="<?= htmlspecialchars($settings['seo_meta_keywords'] ?? '') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">

  <style>
    :root {
      --hs-primary: <?= $palette['primary'] ?>;
      --hs-primary-dark: <?= $palette['primary_dark'] ?>;
      --hs-accent: <?= $palette['accent'] ?>;
      --hs-bg: <?= $palette['bg'] ?>;
      --hs-surface: <?= $palette['surface'] ?>;
      --hs-card: <?= $palette['card'] ?>;
      --hs-text: <?= $palette['text'] ?>;
      --hs-muted: <?= $palette['muted'] ?>;
      --hs-border: <?= $palette['border'] ?>;
    }

    body {
      margin: 0;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: radial-gradient(circle at top, var(--hs-primary) 0, var(--hs-bg) 45%, var(--hs-bg) 100%);
      color: var(--hs-text);
    }

    a { color: var(--hs-accent); text-decoration: none; }
    a:hover { text-decoration: underline; }

    header {
      position: sticky;
      top: 0;
      z-index: 40;
      backdrop-filter: blur(18px);
      background: linear-gradient(90deg, rgba(15,23,42,0.92), rgba(15,23,42,0.96));
      border-bottom: 1px solid var(--hs-border);
      padding: 10px 0;
    }

    .header-inner {
      width: min(1280px, 100% - 20px);
      margin: 0 auto;
      display: grid;
      grid-template-columns: auto 1fr;
      gap: 12px;
      align-items: center;
      padding: 0 10px;
    }

    .top-left {
      display:flex;
      align-items:center;
      gap:10px;
      min-width:0;
    }

    .logo-link {
      display:flex;
      align-items:center;
      gap:10px;
      color:inherit;
      text-decoration:none;
    }
    .logo-link:hover { text-decoration:none; color:#FACC15; }

    .logo-mark {
      width:32px;
      height:32px;
      border-radius:14px;
      background: radial-gradient(circle at 20% 0, #FACC15 0, #1E3A8A 45%, #020617 100%);
      display:flex;
      align-items:center;
      justify-content:center;
      font-weight:800;
      font-size:16px;
      color:#F9FAFB;
      box-shadow:0 10px 25px rgba(15,23,42,0.6);
    }

    .logo-text { display:flex; flex-direction:column; }
    .logo-text-main { font-weight:800; letter-spacing:.18em; font-size:13px; }
    .logo-text-tag { font-size:11px; color:#E5E7EB; opacity:.85; }

    .nav-toggle {
      display:none;
      align-items:center;
      justify-content:center;
      width:40px;
      height:40px;
      border-radius:12px;
      border:1px solid rgba(148,163,184,0.6);
      background:rgba(15,23,42,0.8);
      color:#E5E7EB;
      cursor:pointer;
    }

    .header-right {
      display:grid;
      grid-template-columns: minmax(0, 1fr) auto;
      align-items:center;
      gap:12px;
      justify-content:flex-end;
    }

    .nav-main {
      display:flex;
      align-items:center;
      gap:12px;
      font-size:12px;
      text-transform:uppercase;
      letter-spacing:.12em;
      flex-wrap:wrap;
    }
    .nav-main a {
      color:#E5E7EB;
      padding:6px 8px;
      border-radius:999px;
    }
    .nav-main a:hover { background:rgba(15,23,42,0.8); color:#FACC15; text-decoration:none; }

    .nav-utilities {
      width:100%;
      display:grid;
      grid-template-columns: minmax(0, 1fr) auto;
      align-items:center;
      gap:10px;
    }

    .nav-search { margin:0; }
    .nav-search input[type="text"] {
      padding:10px 14px;
      border-radius:12px;
      border:1px solid rgba(148,163,184,0.9);
      font-size:13px;
      background:#FFFFFF;
      color:#111827;
      width:100%;
      min-width:240px;
    }
    .nav-search input[type="text"]::placeholder { color:#9CA3AF; }
    .nav-search button { display:none; }

    .language-form { display:flex; align-items:center; gap:6px; }
    .language-switcher { min-width:140px; padding:9px 10px; border-radius:10px; border:1px solid rgba(148,163,184,0.5); background:#0B1120; color:#E5E7EB; }
    .language-label { font-size:11px; color:#E5E7EB; opacity:0.75; }

    .user-bar { font-size:11px; color:#E5E7EB; text-align:right; }
    .user-bar a { color:#FACC15; }

    .sr-only { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0; }

    @media (max-width:960px) {
      .header-inner { grid-template-columns:1fr auto; align-items:flex-start; }
      .nav-toggle { display:inline-flex; }
      .header-right { width:100%; display:none; grid-template-columns:1fr; }
      header.nav-open .header-right { display:flex; flex-direction:column; align-items:flex-start; }
      .nav-main { width:100%; flex-direction:column; align-items:flex-start; }
      .nav-main a { width:100%; padding:10px; border-radius:12px; background:rgba(15,23,42,0.65); }
      .nav-utilities { width:100%; grid-template-columns:1fr; }
      .nav-search { width:100%; margin:0; }
      .nav-search input[type="text"] { width:100%; }
      .language-form { width:100%; }
      .language-switcher { width:100%; }
      .user-bar { width:100%; text-align:left; }
    }

    .page {
      width:min(1280px, 100% - 20px);
      margin:0 auto;
      min-height:100vh;
    }

    .layout-main {
      display:grid;
      grid-template-columns: minmax(0,3.2fr) minmax(0,2fr);
      gap:14px;
      padding:12px 0 28px;
    }

    @media (max-width:1080px) {
      .layout-main { grid-template-columns:1fr; }
      .sidebar { order:2; }
      .column { order:1; }
    }

    .column {
      min-width:0;
    }

    .card {
      background: radial-gradient(circle at top left, rgba(30,64,175,0.30), var(--hs-card));
      border-radius:18px;
      box-shadow:0 18px 45px rgba(15,23,42,0.35);
      border:1px solid var(--hs-border);
      padding:14px 16px;
      margin-bottom:12px;
    }

    .pill {
      display:inline-flex;
      align-items:center;
      gap:6px;
      font-size:10px;
      text-transform:uppercase;
      letter-spacing:.2em;
      background:rgba(15,23,42,0.94);
      color:#FACC15;
      border-radius:999px;
      padding:4px 10px;
      margin-bottom:6px;
    }

    .pill-dot {
      width:6px;
      height:6px;
      border-radius:999px;
      background:#FACC15;
    }

    .section-title {
      font-size:15px;
      font-weight:700;
      letter-spacing:.08em;
      text-transform:uppercase;
      margin-bottom:8px;
    }

    .ticker {
      width:100%;
      display:flex;
      align-items:center;
      gap:10px;
      padding:6px 18px;
      box-sizing:border-box;
      border-bottom:1px solid rgba(15,23,42,0.85);
      background:linear-gradient(90deg, rgba(15,23,42,0.9), rgba(15,23,42,0.85));
      overflow-x:auto;
      white-space:nowrap;
    }
    .ticker-label {
      font-size:11px;
      font-weight:700;
      text-transform:uppercase;
      letter-spacing:.16em;
      color:#FACC15;
    }
    .ticker-items {
      font-size:12px;
      display:flex;
      gap:18px;
      color:#E5E7EB;
    }
    .ticker-item {
      opacity:.9;
    }

    .hero-grid {
      display:grid;
      grid-template-columns:minmax(0,2.1fr) minmax(0,1.6fr);
      gap:12px;
    }

    .hero-main {
      position:relative;
      border-radius:20px;
      overflow:hidden;
      padding:16px 18px 18px;
      background:
        radial-gradient(circle at top left, rgba(250,204,21,0.25), transparent 55%),
        radial-gradient(circle at bottom right, rgba(30,64,175,0.35), transparent 55%),
        linear-gradient(135deg, #020617, #020617);
      min-height:190px;
      display:flex;
      flex-direction:column;
      justify-content:flex-end;
    }
    .hero-image {
      margin-bottom:8px;
      border-radius:16px;
      overflow:hidden;
      max-height:220px;
    }
    .hero-image img {
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
    }


    .hero-kicker {
      font-size:11px;
      letter-spacing:.18em;
      text-transform:uppercase;
      color:#FACC15;
      margin-bottom:6px;
    }
    .hero-title {
      font-size:22px;
      font-weight:800;
      line-height:1.2;
      margin-bottom:4px;
      color:#F9FAFB;
      text-shadow:0 8px 25px rgba(15,23,42,0.9);
    }
    .hero-meta {
      font-size:12px;
      color:#E5E7EB;
      opacity:.9;
    }

    .hero-overlay-tag {
      position:absolute;
      top:12px;
      right:14px;
      font-size:11px;
      padding:4px 10px;
      border-radius:999px;
      background:rgba(15,23,42,0.9);
      border:1px solid rgba(250,204,21,0.5);
      color:#FACC15;
      letter-spacing:.14em;
      text-transform:uppercase;
    }

    .hero-list {
      display:flex;
      flex-direction:column;
      gap:8px;
    }
    .hero-list-item {
      padding:8px 10px;
      border-radius:12px;
      background:rgba(15,23,42,0.85);
      border:1px solid rgba(15,23,42,0.9);
      cursor:pointer;
      transition:background .15s, transform .15s;
    }
    .hero-list-item:hover {
      background:rgba(30,64,175,0.45);
      transform:translateY(-1px);
    }
    .hero-list-title {
      font-size:13px;
      font-weight:600;
      color:#F9FAFB;
      margin-bottom:2px;
    }
    .hero-list-meta {
      font-size:11px;
      color:#9CA3AF;
    }

    .region-row {
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap:12px;
      margin-top:10px;
    }
    .region-block {
      padding:10px 11px;
      border-radius:14px;
      background:rgba(15,23,42,0.92);
      border:1px solid rgba(15,23,42,0.9);
      min-height:120px;
      display:flex;
      flex-direction:column;
    }
    .region-header {
      font-size:11px;
      letter-spacing:.16em;
      text-transform:uppercase;
      color:#E5E7EB;
      margin-bottom:6px;
      display:flex;
      justify-content:space-between;
      align-items:center;
    }
    .region-header span:last-child {
      font-size:10px;
      opacity:.7;
    }
    .region-post-list {
      list-style:none;
      padding:0;
      margin:0;
      font-size:12px;
    }
    .region-post-list li {
      margin-bottom:4px;
    }
    .region-post-title {
      color:#F9FAFB;
    }
    
    .region-thumb {
      margin:4px 0 4px;
      border-radius:8px;
      overflow:hidden;
      max-height:74px;
    }
    .region-thumb img {
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
    }

    .region-post-meta {
      font-size:10px;
      color:#9CA3AF;
    }

    .side-card {
      margin-bottom:12px;
    }

    .trending-list {
      list-style:none;
      padding:0;
      margin:0;
      font-size:12px;
    }
    .trending-list li {
      display:flex;
      justify-content:space-between;
      align-items:flex-start;
      padding:6px 0;
      border-bottom:1px dashed rgba(31,41,55,0.6);
    }
    
    .trending-thumb {
      width:72px;
      height:52px;
      border-radius:10px;
      overflow:hidden;
      margin-right:8px;
      flex-shrink:0;
    }
    .trending-thumb img {
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
    }

    .trending-title {
      flex:1;
      margin-right:6px;
      color:#F9FAFB;
    }
    .trending-meta {
      font-size:10px;
      color:#9CA3AF;
      text-align:right;
      min-width:70px;
    }

    .video-list, .gallery-list {
      list-style:none;
      padding:0;
      margin:0;
      font-size:12px;
    }
    .video-list li,
    .gallery-list li {
      display:flex;
      align-items:center;
      padding:5px 0;
      border-bottom:1px dashed rgba(31,41,55,0.6);
      gap:8px;
    }
    .video-thumb,
    .gallery-thumb {
      width:42px;
      height:28px;
      border-radius:8px;
      background: radial-gradient(circle at top, rgba(250,204,21,0.28), rgba(15,23,42,0.95));
      display:flex;
      align-items:center;
      justify-content:center;
      font-size:14px;
      color:#FACC15;
    }
    .video-text,
    .gallery-text {
      flex:1;
    }

    .ads-slot {
      border-radius:14px;
      border:1px dashed var(--hs-border);
      padding:18px 10px;
      text-align:center;
      font-size:11px;
      color: var(--hs-muted);
      background:radial-gradient(circle at top, rgba(30,64,175,0.18), var(--hs-card));
    }
    .ads-slot img { max-width:100%; height:auto; border-radius:10px; }
    .ads-top { margin:12px 0; }
    .ads-inline { margin:16px 0; }

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

    @media (max-width:980px) {
      .layout-main {
        grid-template-columns: minmax(0,1fr);
      }
    }

    @media (max-width:640px) {
      header {
        padding:8px 10px;
      }
      .layout-main {
        padding:10px 10px 20px;
      }
      .hero-grid {
        grid-template-columns: minmax(0,1fr);
      }
      .region-row {
        grid-template-columns: minmax(0,1fr);
      }
    }
  </style>
</head>
<body>
<?php
  $india_posts  = [];
  $gcc_posts    = [];
  $kerala_posts = [];
  $world_posts  = [];
  $sports_posts = [];

  // New category collections
  $entertainment_posts = [];
  $business_posts      = [];
  $technology_posts    = [];
  $lifestyle_posts     = [];
  $health_posts        = [];
  $travel_posts        = [];
  $auto_posts          = [];
  $opinion_posts       = [];
  $politics_posts      = [];
  $crime_posts         = [];
  $education_posts     = [];
  $religion_posts      = [];

  foreach ($posts as $p) {
    $region = $p['region'] ?? 'global';
    switch ($region) {
      case 'india':  $india_posts[]  = $p; break;
      case 'gcc':    $gcc_posts[]    = $p; break;
      case 'kerala': $kerala_posts[] = $p; break;
      case 'world':  $world_posts[]  = $p; break;
      case 'sports': $sports_posts[] = $p; break;
    }

    $cat_name = strtolower($p['category_name'] ?? '');
    switch ($cat_name) {
      case 'entertainment': $entertainment_posts[] = $p; break;
      case 'business':      $business_posts[]      = $p; break;
      case 'technology':    $technology_posts[]    = $p; break;
      case 'lifestyle':     $lifestyle_posts[]     = $p; break;
      case 'health':        $health_posts[]        = $p; break;
      case 'travel':        $travel_posts[]        = $p; break;
      case 'auto':          $auto_posts[]          = $p; break;
      case 'opinion':       $opinion_posts[]       = $p; break;
      case 'politics':      $politics_posts[]      = $p; break;
      case 'crime':         $crime_posts[]         = $p; break;
      case 'education':     $education_posts[]     = $p; break;
      case 'religion':      $religion_posts[]      = $p; break;
    }
  }

  $hero = !empty($featured) ? $featured[0] : (!empty($posts) ? $posts[0] : null);
  $hero_list = [];
  if (!empty($featured)) {
    $hero_list = array_slice($featured, 1, 4);
  } elseif (!empty($posts)) {
    $hero_list = array_slice($posts, 1, 5);
  }

  function hs_post_date($p) {
    return !empty($p['created_at']) ? date('M j, Y', strtotime($p['created_at'])) : '';
  }
?>
<header>
  <div class="header-inner">
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
          <a href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($item['label']) ?></a>
        <?php endforeach; ?>
      </nav>
      <div class="nav-utilities stack-mobile">
        <form class="nav-search" action="<?= hs_search_url() ?>" method="get" data-testid="nav-search-form">
          <label class="sr-only" for="nav-search">Search</label>
          <input id="nav-search" type="text" name="q" placeholder="Search news..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" data-testid="nav-search-input">
          <button type="submit">Search</button>
        </form>
        <form class="language-form" method="get" action="">
          <label class="language-label" for="language-select">Language</label>
          <select id="language-select" class="language-switcher" name="lang" aria-label="Language" onchange="this.form.submit()" data-testid="language-switcher">
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
          <?php if (!empty($u['is_premium'])): ?> · <strong>Premium</strong><?php endif; ?>
          · <a href="<?= hs_dashboard_url() ?>">Dashboard</a>
          · <a href="<?= hs_logout_url() ?>">Logout</a>
        <?php else: ?>
          <a href="<?= hs_login_url() ?>">Login</a> ·
          <a href="<?= hs_register_url() ?>">Register</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<?php if ($topAd = $ad_for('homepage_top')): ?>
  <div class="ads-slot ads-top">
    <?= hs_render_ad($topAd) ?>
  </div>
<?php endif; ?>

<?php if (!empty($breaking)): ?>
  <div class="ticker">
    <div class="ticker-label">Breaking</div>
    <div class="ticker-items">
      <?php foreach ($breaking as $b): ?>
        <div class="ticker-item">• <?= htmlspecialchars($b['title'] ?? '') ?></div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<main class="page">
  <div class="layout-main">
    <section class="column">
      <div class="card">
        <div class="pill"><span class="pill-dot"></span> Top Stories</div>
        <?php if ($hero): ?>
          <div class="hero-grid">
            <article class="hero-main">
              <?php if (!empty($hero['image_main'])): ?>
                <div class="hero-image">
                  <img src="<?= hs_base_url($hero['image_main']) ?>" alt="<?= htmlspecialchars($hero['title']) ?>">
                </div>
              <?php endif; ?>
              <div class="hero-overlay-tag">Lead Story</div>
              <div class="hero-kicker">
                <?= htmlspecialchars($hero['category_name'] ?: 'News') ?>
                <?php if (!empty($hero['region']) && $hero['region'] !== 'global'): ?>
                  · <?= strtoupper(htmlspecialchars($hero['region'])) ?>
                <?php endif; ?>
              </div>
              <h1 class="hero-title"><a href="<?= hs_news_url($hero['slug']) ?>"><?= htmlspecialchars($hero['title']) ?></a></h1>
              <div class="hero-meta">
                <?= hs_post_date($hero) ?>
              </div>
            </article>
            <aside class="hero-list">
              <?php if (empty($hero_list)): ?>
                <div style="font-size:12px; color:#9CA3AF;">Mark posts as <strong>featured</strong> in Content Manager to see more here.</div>
              <?php else: ?>
                <?php foreach ($hero_list as $f): ?>
                  <div class="hero-list-item">
                    <div class="hero-list-title"><a href="<?= hs_news_url($f['slug']) ?>"><?= htmlspecialchars($f['title']) ?></a></div>
                    <div class="hero-list-meta">
                      <?= htmlspecialchars($f['category_name'] ?: 'News') ?> · <?= hs_post_date($f) ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </aside>
          </div>
        <?php else: ?>
          <p style="font-size:12px; color:#9CA3AF;">No stories yet. Add posts from Admin → Content Manager.</p>
        <?php endif; ?>
      </div>

      <?php if ($inlineAd = $ad_for('homepage_inline')): ?>
        <div class="ads-slot ads-inline">
          <?= hs_render_ad($inlineAd) ?>
        </div>
      <?php endif; ?>

      <div class="card" id="regions">
        <div class="pill"><span class="pill-dot"></span> Region Highlights</div>
        <div class="region-row">
          <div class="region-block" id="india">
            <div class="region-header">
              <span>India</span>
              <span><a href="<?= hs_category_url('india') ?>" style="color:#FACC15;">View All</a></span>
            </div>
            <?php if (empty($india_posts)): ?>
              <div style="font-size:11px; color:#9CA3AF;">No India posts yet.</div>
            <?php else: ?>
              <ul class="region-post-list">
                <?php foreach (array_slice($india_posts, 0, 4) as $p): ?>
                  <li>
                    <div class="region-post-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                                        <?php if (!empty($p['image_main'])): ?>
                      <div class="region-thumb">
                        <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                      </div>
                    <?php endif; ?>
                    <div class="region-post-meta"><?= htmlspecialchars($p['category_name'] ?: 'News') ?> · <?= hs_post_date($p) ?></div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>

          <div class="region-block" id="gcc">
            <div class="region-header">
              <span>GCC</span>
              <span><a href="<?= hs_category_url('gcc') ?>" style="color:#FACC15;">View All</a></span>
            </div>
            <?php if (empty($gcc_posts)): ?>
              <div style="font-size:11px; color:#9CA3AF;">No GCC posts yet.</div>
            <?php else: ?>
              <ul class="region-post-list">
                <?php foreach (array_slice($gcc_posts, 0, 4) as $p): ?>
                  <li>
                    <div class="region-post-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                                        <?php if (!empty($p['image_main'])): ?>
                      <div class="region-thumb">
                        <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                      </div>
                    <?php endif; ?>
                    <div class="region-post-meta"><?= htmlspecialchars($p['category_name'] ?: 'News') ?> · <?= hs_post_date($p) ?></div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>

          <div class="region-block" id="kerala">
            <div class="region-header">
              <span>Kerala</span>
              <span><a href="<?= hs_category_url('kerala') ?>" style="color:#FACC15;">View All</a></span>
            </div>
            <?php if (empty($kerala_posts)): ?>
              <div style="font-size:11px; color:#9CA3AF;">No Kerala posts yet.</div>
            <?php else: ?>
              <ul class="region-post-list">
                <?php foreach (array_slice($kerala_posts, 0, 4) as $p): ?>
                  <li>
                    <div class="region-post-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                                        <?php if (!empty($p['image_main'])): ?>
                      <div class="region-thumb">
                        <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                      </div>
                    <?php endif; ?>
                    <div class="region-post-meta"><?= htmlspecialchars($p['category_name'] ?: 'News') ?> · <?= hs_post_date($p) ?></div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        </div>

        <div class="region-row" style="margin-top:10px;">
          <div class="region-block" id="world">
            <div class="region-header">
              <span>World</span>
              <span><a href="<?= hs_category_url('world') ?>" style="color:#FACC15;">View All</a></span>
            </div>
            <?php if (empty($world_posts)): ?>
              <div style="font-size:11px; color:#9CA3AF;">No World posts yet.</div>
            <?php else: ?>
              <ul class="region-post-list">
                <?php foreach (array_slice($world_posts, 0, 4) as $p): ?>
                  <li>
                    <div class="region-post-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                                        <?php if (!empty($p['image_main'])): ?>
                      <div class="region-thumb">
                        <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                      </div>
                    <?php endif; ?>
                    <div class="region-post-meta"><?= htmlspecialchars($p['category_name'] ?: 'News') ?> · <?= hs_post_date($p) ?></div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>

          <div class="region-block" id="sports">
            <div class="region-header">
              <span>Sports</span>
              <span><a href="<?= hs_category_url('sports') ?>" style="color:#FACC15;">View All</a></span>
            </div>
            <?php if (empty($sports_posts)): ?>
              <div style="font-size:11px; color:#9CA3AF;">No Sports posts yet.</div>
            <?php else: ?>
              <ul class="region-post-list">
                <?php foreach (array_slice($sports_posts, 0, 4) as $p): ?>
                  <li>
                    <div class="region-post-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                                        <?php if (!empty($p['image_main'])): ?>
                      <div class="region-thumb">
                        <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                      </div>
                    <?php endif; ?>
                    <div class="region-post-meta"><?= htmlspecialchars($p['category_name'] ?: 'News') ?> · <?= hs_post_date($p) ?></div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>

          <div class="region-block">
            <div class="region-header">
              <span>All News</span>
              <span>Latest</span>
            </div>
            <?php if (empty($posts)): ?>
              <div style="font-size:11px; color:#9CA3AF;">No posts yet.</div>
            <?php else: ?>
              <ul class="region-post-list">
                <?php foreach (array_slice($posts, 0, 4) as $p): ?>
                  <li>
                    <div class="region-post-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                                        <?php if (!empty($p['image_main'])): ?>
                      <div class="region-thumb">
                        <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                      </div>
                    <?php endif; ?>
                    <div class="region-post-meta"><?= htmlspecialchars($p['category_name'] ?: 'News') ?> · <?= hs_post_date($p) ?></div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>

    <section class="card">
      <div class="pill"><span class="pill-dot"></span> Category Highlights</div>
      <div class="region-row">
        <div class="region-block" id="entertainment">
          <div class="region-header">
            <span>Entertainment</span>
            <span><a href="<?= hs_category_url('entertainment') ?>" style="color:#FACC15;">View All</a></span>
          </div>
          <?php if (empty($entertainment_posts)): ?>
            <div style="font-size:11px; color:#9CA3AF;">No entertainment posts yet.</div>
          <?php else: ?>
            <ul class="region-post-list">
              <?php foreach (array_slice($entertainment_posts, 0, 3) as $p): ?>
                <li>
                  <div class="region-post-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                  <?php if (!empty($p['image_main'])): ?>
                    <div class="region-thumb">
                      <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                    </div>
                  <?php endif; ?>
                  <div class="region-post-meta"><?= htmlspecialchars($p['category_name'] ?: 'News') ?> · <?= hs_post_date($p) ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <div class="region-block" id="business">
          <div class="region-header">
            <span>Business</span>
            <span><a href="<?= hs_category_url('business') ?>" style="color:#FACC15;">View All</a></span>
          </div>
          <?php if (empty($business_posts)): ?>
            <div style="font-size:11px; color:#9CA3AF;">No business posts yet.</div>
          <?php else: ?>
            <ul class="region-post-list">
              <?php foreach (array_slice($business_posts, 0, 3) as $p): ?>
                <li>
                  <div class="region-post-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                  <?php if (!empty($p['image_main'])): ?>
                    <div class="region-thumb">
                      <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                    </div>
                  <?php endif; ?>
                  <div class="region-post-meta"><?= htmlspecialchars($p['category_name'] ?: 'News') ?> · <?= hs_post_date($p) ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <div class="region-block" id="technology">
          <div class="region-header">
            <span>Technology</span>
            <span><a href="<?= hs_category_url('technology') ?>" style="color:#FACC15;">View All</a></span>
          </div>
          <?php if (empty($technology_posts)): ?>
            <div style="font-size:11px; color:#9CA3AF;">No technology posts yet.</div>
          <?php else: ?>
            <ul class="region-post-list">
              <?php foreach (array_slice($technology_posts, 0, 3) as $p): ?>
                <li>
                  <div class="region-post-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                  <?php if (!empty($p['image_main'])): ?>
                    <div class="region-thumb">
                      <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                    </div>
                  <?php endif; ?>
                  <div class="region-post-meta"><?= htmlspecialchars($p['category_name'] ?: 'News') ?> · <?= hs_post_date($p) ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <div class="region-block" id="lifestyle">
          <div class="region-header">
            <span>Lifestyle</span>
            <span><a href="<?= hs_category_url('lifestyle') ?>" style="color:#FACC15;">View All</a></span>
          </div>
          <?php if (empty($lifestyle_posts)): ?>
            <div style="font-size:11px; color:#9CA3AF;">No lifestyle posts yet.</div>
          <?php else: ?>
            <ul class="region-post-list">
              <?php foreach (array_slice($lifestyle_posts, 0, 3) as $p): ?>
                <li>
                  <div class="region-post-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                  <?php if (!empty($p['image_main'])): ?>
                    <div class="region-thumb">
                      <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                    </div>
                  <?php endif; ?>
                  <div class="region-post-meta"><?= htmlspecialchars($p['category_name'] ?: 'News') ?> · <?= hs_post_date($p) ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>

      <div class="region-row" style="margin-top:10px;">
        <div class="region-block" id="health">
          <div class="region-header">
            <span>Health</span>
            <span><a href="<?= hs_category_url('health') ?>" style="color:#FACC15;">View All</a></span>
          </div>
          <?php if (empty($health_posts)): ?>
            <div style="font-size:11px; color:#9CA3AF;">No health posts yet.</div>
          <?php else: ?>
            <ul class="region-post-list">
              <?php foreach (array_slice($health_posts, 0, 3) as $p): ?>
                <li>
                  <div class="region-post-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                  <?php if (!empty($p['image_main'])): ?>
                    <div class="region-thumb">
                      <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                    </div>
                  <?php endif; ?>
                  <div class="region-post-meta"><?= htmlspecialchars($p['category_name'] ?: 'News') ?> · <?= hs_post_date($p) ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <div class="region-block" id="travel">
          <div class="region-header">
            <span>Travel</span>
            <span><a href="<?= hs_category_url('travel') ?>" style="color:#FACC15;">View All</a></span>
          </div>
          <?php if (empty($travel_posts)): ?>
            <div style="font-size:11px; color:#9CA3AF;">No travel posts yet.</div>
          <?php else: ?>
            <ul class="region-post-list">
              <?php foreach (array_slice($travel_posts, 0, 3) as $p): ?>
                <li>
                  <div class="region-post-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                  <?php if (!empty($p['image_main'])): ?>
                    <div class="region-thumb">
                      <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                    </div>
                  <?php endif; ?>
                  <div class="region-post-meta"><?= htmlspecialchars($p['category_name'] ?: 'News') ?> · <?= hs_post_date($p) ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <div class="region-block" id="auto">
          <div class="region-header">
            <span>Auto</span>
            <span><a href="<?= hs_category_url('auto') ?>" style="color:#FACC15;">View All</a></span>
          </div>
          <?php if (empty($auto_posts)): ?>
            <div style="font-size:11px; color:#9CA3AF;">No auto posts yet.</div>
          <?php else: ?>
            <ul class="region-post-list">
              <?php foreach (array_slice($auto_posts, 0, 3) as $p): ?>
                <li>
                  <div class="region-post-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                  <?php if (!empty($p['image_main'])): ?>
                    <div class="region-thumb">
                      <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                    </div>
                  <?php endif; ?>
                  <div class="region-post-meta"><?= htmlspecialchars($p['category_name'] ?: 'News') ?> · <?= hs_post_date($p) ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <div class="region-block" id="opinion">
          <div class="region-header">
            <span>Opinion</span>
            <span><a href="<?= hs_category_url('opinion') ?>" style="color:#FACC15;">View All</a></span>
          </div>
          <?php if (empty($opinion_posts)): ?>
            <div style="font-size:11px; color:#9CA3AF;">No opinion posts yet.</div>
          <?php else: ?>
            <ul class="region-post-list">
              <?php foreach (array_slice($opinion_posts, 0, 3) as $p): ?>
                <li>
                  <div class="region-post-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                  <?php if (!empty($p['image_main'])): ?>
                    <div class="region-thumb">
                      <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                    </div>
                  <?php endif; ?>
                  <div class="region-post-meta"><?= htmlspecialchars($p['category_name'] ?: 'News') ?> · <?= hs_post_date($p) ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

      <div class="region-row" style="margin-top:10px;">
        <div class="region-block" id="politics">
          <div class="region-header">
            <span>Politics</span>
            <span><a href="<?= hs_category_url('politics') ?>" style="color:#FACC15;">View All</a></span>
          </div>
          <?php if (empty($politics_posts)): ?>
            <div style="font-size:11px; color:#9CA3AF;">No politics posts yet.</div>
          <?php else: ?>
            <ul class="region-post-list">
              <?php foreach (array_slice($politics_posts, 0, 3) as $p): ?>
                <li>
                  <div class="region-post-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                  <?php if (!empty($p['image_main'])): ?>
                    <div class="region-thumb">
                      <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                    </div>
                  <?php endif; ?>
                  <div class="region-post-meta"><?= htmlspecialchars($p['category_name'] ?: 'News') ?> · <?= hs_post_date($p) ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <div class="region-block" id="crime">
          <div class="region-header">
            <span>Crime</span>
            <span><a href="<?= hs_category_url('crime') ?>" style="color:#FACC15;">View All</a></span>
          </div>
          <?php if (empty($crime_posts)): ?>
            <div style="font-size:11px; color:#9CA3AF;">No crime posts yet.</div>
          <?php else: ?>
            <ul class="region-post-list">
              <?php foreach (array_slice($crime_posts, 0, 3) as $p): ?>
                <li>
                  <div class="region-post-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                  <?php if (!empty($p['image_main'])): ?>
                    <div class="region-thumb">
                      <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                    </div>
                  <?php endif; ?>
                  <div class="region-post-meta"><?= htmlspecialchars($p['category_name'] ?: 'News') ?> · <?= hs_post_date($p) ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <div class="region-block" id="education">
          <div class="region-header">
            <span>Education</span>
            <span><a href="<?= hs_category_url('education') ?>" style="color:#FACC15;">View All</a></span>
          </div>
          <?php if (empty($education_posts)): ?>
            <div style="font-size:11px; color:#9CA3AF;">No education posts yet.</div>
          <?php else: ?>
            <ul class="region-post-list">
              <?php foreach (array_slice($education_posts, 0, 3) as $p): ?>
                <li>
                  <div class="region-post-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                  <?php if (!empty($p['image_main'])): ?>
                    <div class="region-thumb">
                      <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                    </div>
                  <?php endif; ?>
                  <div class="region-post-meta"><?= htmlspecialchars($p['category_name'] ?: 'News') ?> · <?= hs_post_date($p) ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <div class="region-block" id="religion">
          <div class="region-header">
            <span>Religion</span>
            <span><a href="<?= hs_category_url('religion') ?>" style="color:#FACC15;">View All</a></span>
          </div>
          <?php if (empty($religion_posts)): ?>
            <div style="font-size:11px; color:#9CA3AF;">No religion posts yet.</div>
          <?php else: ?>
            <ul class="region-post-list">
              <?php foreach (array_slice($religion_posts, 0, 3) as $p): ?>
                <li>
                  <div class="region-post-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                  <?php if (!empty($p['image_main'])): ?>
                    <div class="region-thumb">
                      <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                    </div>
                  <?php endif; ?>
                  <div class="region-post-meta"><?= htmlspecialchars($p['category_name'] ?: 'News') ?> · <?= hs_post_date($p) ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>

    </section>


          </div>
        </div>
      </div>
    </section>

    <aside class="column">
      <?php if ($rightAd = $ad_for('homepage_right')): ?>
        <div class="ads-slot ads-inline">
          <?= hs_render_ad($rightAd) ?>
        </div>
      <?php endif; ?>

      <section class="card side-card">
        <div class="pill"><span class="pill-dot"></span> Trending</div>
        <?php if (empty($trending)): ?>
          <p style="font-size:12px; color:#9CA3AF;">No trending posts yet.</p>
        <?php else: ?>
          <ul class="trending-list">
            <?php foreach ($trending as $t): ?>
              <li>
                <?php if (!empty($t['image_main'])): ?>
                <div class="trending-thumb">
                  <img src="<?= hs_base_url($t['image_main']) ?>" alt="<?= htmlspecialchars($t['title']) ?>">
                </div>
              <?php endif; ?>
              <div class="trending-title"><a href="<?= hs_news_url($t['slug']) ?>"><?= htmlspecialchars($t['title']) ?></a></div>
                <div class="trending-meta">
                  <?= htmlspecialchars($t['category_name'] ?: 'News') ?><br>
                  <?= hs_post_date($t) ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>

      <section class="card side-card">
        <div class="pill"><span class="pill-dot"></span> Video</div>
        <div class="section-title" style="margin-bottom:4px;">Video News</div>
        <?php if (empty($video_posts)): ?>
          <p style="font-size:12px; color:#9CA3AF;">No video posts yet.</p>
        <?php else: ?>
          <ul class="video-list">
            <?php foreach ($video_posts as $v): ?>
              <li>
                <div class="video-thumb">▶</div>
                <div class="video-text">
                  <div><a href="<?= hs_news_url($v['slug']) ?>"><?= htmlspecialchars($v['title']) ?></a></div>
                  <div style="font-size:10px; color:#9CA3AF;"><?= htmlspecialchars($v['category_name'] ?: 'Video') ?></div>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>

      <section class="card side-card">
        <div class="pill"><span class="pill-dot"></span> Gallery</div>
        <div class="section-title" style="margin-bottom:4px;">Photo Gallery</div>
        <?php if (empty($gallery_posts)): ?>
          <p style="font-size:12px; color:#9CA3AF;">No gallery posts yet.</p>
        <?php else: ?>
          <ul class="gallery-list">
            <?php foreach ($gallery_posts as $g): ?>
              <li>
                <div class="gallery-thumb">🖼</div>
                <div class="gallery-text">
                  <div><a href="<?= hs_news_url($g['slug']) ?>"><?= htmlspecialchars($g['title']) ?></a></div>
                  <div style="font-size:10px; color:#9CA3AF;"><?= htmlspecialchars($g['category_name'] ?: 'Gallery') ?></div>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>

      <section class="card side-card">
        <div class="section-title">Follow NEWS HDSPTV</div>
        <p style="font-size:12px; color:#9CA3AF;">
          <?php if (!empty($settings['social_facebook'])): ?>
            <a href="<?= htmlspecialchars($settings['social_facebook']) ?>" target="_blank">Facebook</a> ·
          <?php endif; ?>
          <?php if (!empty($settings['social_youtube'])): ?>
            <a href="<?= htmlspecialchars($settings['social_youtube']) ?>" target="_blank">YouTube</a> ·
          <?php endif; ?>
          <?php if (!empty($settings['social_instagram'])): ?>
            <a href="<?= htmlspecialchars($settings['social_instagram']) ?>" target="_blank">Instagram</a> ·
          <?php endif; ?>
          <?php if (!empty($settings['social_x'])): ?>
            <a href="<?= htmlspecialchars($settings['social_x']) ?>" target="_blank">X</a> ·
          <?php endif; ?>
          <?php if (!empty($settings['social_tiktok'])): ?>
            <a href="<?= htmlspecialchars($settings['social_tiktok']) ?>" target="_blank">TikTok</a> ·
          <?php endif; ?>
          <?php if (!empty($settings['social_telegram'])): ?>
            <a href="<?= htmlspecialchars($settings['social_telegram']) ?>" target="_blank">Telegram</a>
          <?php endif; ?>
        </p>
      </section>

      <section class="card side-card">
        <div class="section-title">Homepage Sidebar Ad</div>
        <div class="ads-slot">
          Homepage Sidebar Ad Slot<br>
          (Manage this from Admin → Ads)
        </div>
      </section>
    </aside>
  </div>
</main>

<footer>
  <div class="footer-links"><?= hs_footer_links_html(); ?></div>
  <div class="footer-copy">© <?= date('Y') ?> <?= htmlspecialchars($settings['site_title'] ?? 'NEWS HDSPTV') ?>. All rights reserved.</div>
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
