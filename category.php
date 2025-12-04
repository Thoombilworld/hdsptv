<?php
require __DIR__ . '/bootstrap.php';

$settings = hs_settings();
$db = hs_db();
$languageCode = hs_current_language_code();
$languageDir = hs_is_rtl($languageCode) ? 'rtl' : 'ltr';

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    echo hs_t('category_not_specified', 'Category not specified.');
    exit;
}

$stmt = mysqli_prepare($db, "SELECT id, name, slug FROM hs_categories WHERE slug = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $slug);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$category = $res ? mysqli_fetch_assoc($res) : null;

if (!$category) {
    echo hs_t('category_not_found', 'Category not found.');
    exit;
}

$cat_id = (int)$category['id'];

$posts = [];
$pRes = mysqli_query($db, "SELECT p.*, c.name AS category_name
                           FROM hs_posts p
                           LEFT JOIN hs_categories c ON c.id = p.category_id
                           WHERE p.status='published' AND p.category_id = " . $cat_id . "
                           ORDER BY p.created_at DESC
                           LIMIT 40");
if ($pRes) {
    while ($row = mysqli_fetch_assoc($pRes)) $posts[] = $row;
}

hs_track_event([
    'type'        => 'category',
    'category_id' => $cat_id,
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

$site_title = $settings['site_title'] ?? 'NEWS HDSPTV';
$page_title = $category['name'] . ' – ' . $site_title;
$meta_desc = $category['name'] . ' news – ' . ($settings['seo_meta_description'] ?? '');
$meta_keys = ($settings['seo_meta_keywords'] ?? '') . ', ' . $category['name'];
$canonical = hs_category_url($category['slug']);
?>
<!doctype html>
<html lang="<?= htmlspecialchars($languageCode) ?>" dir="<?= htmlspecialchars($languageDir) ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($meta_desc) ?>">
  <meta name="keywords" content="<?= htmlspecialchars($meta_keys) ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="<?= htmlspecialchars($settings['favicon'] ?? hs_base_url('assets/images/favicon.png')) ?>">
  <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
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
    a { color: #1D4ED8; text-decoration: none; }
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

    .nav-main {
      display:flex; align-items:center; gap:12px;
      font-size:12px; text-transform:uppercase; letter-spacing:.12em;
      flex-wrap:wrap;
    }
    .nav-main a { color:#E5E7EB; padding:6px 8px; border-radius:999px; }
    .nav-main a:hover { background:rgba(15,23,42,0.8); color:#FACC15; text-decoration:none; }

    .nav-search {
      margin-left:auto;
      margin-right:12px;
      margin-top:4px;
    }
    .nav-search input[type="text"] {
      padding:6px 12px;
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
    .language-switcher { min-width:120px; padding:6px 10px; border-radius:10px; border:1px solid rgba(148,163,184,0.5); background:#0B1120; color:#E5E7EB; }

    .page {
      width:100%;
      min-height:100vh;
      padding:18px 12px 32px;
      box-sizing:border-box;
    }
    .layout-category {
      max-width:1160px;
      margin:0 auto;
    }
    .category-header {
      margin-bottom:14px;
    }
    .category-title {
      font-size:22px;
      font-weight:800;
      letter-spacing:.08em;
      text-transform:uppercase;
    }
    .category-sub {
      font-size:12px;
      color:#E5E7EB;
      margin-top:4px;
    }

    .card-grid {
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap:14px;
      margin-top:16px;
    }
    .news-card {
      background:#F9FAFB;
      border-radius:16px;
      box-shadow:0 16px 36px rgba(15,23,42,0.6);
      color:#111827;
      overflow:hidden;
      display:flex;
      flex-direction:column;
    }
    .news-thumb {
      width:100%;
      height:160px;
      background:#E5E7EB;
      overflow:hidden;
    }
    .news-thumb img {
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
    }
    .news-inner {
      padding:12px 14px 14px;
      font-size:14px;
    }
    .news-kicker {
      font-size:11px;
      text-transform:uppercase;
      letter-spacing:.18em;
      color:#6B7280;
      margin-bottom:4px;
    }
    .news-title {
      font-weight:700;
      margin-bottom:4px;
      color:#111827;
    }
    .news-title a { color:#111827; }
    .news-title a:hover { color:#1D4ED8; text-decoration:none; }
    .news-meta {
      font-size:11px;
      color:#6B7280;
    }

    .card-grid { grid-auto-flow:dense; }
    @media (min-width:1100px) {
      .card-grid { grid-template-columns:repeat(3, minmax(0,1fr)); }
    }
    @media (max-width:900px) {
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
      .card-grid { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
      .news-card { flex-direction:row; align-items:stretch; }
      .news-thumb { width:150px; height:auto; }
      .news-inner { flex:1; }
    }

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
    @media (max-width:640px) {
      header { padding:8px 10px; }
      .page { padding:14px 8px 24px; }
      .card-grid { grid-template-columns:minmax(0,1fr); }
      .news-card { flex-direction:column; }
      .news-thumb { width:100%; height:180px; }
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
        <?php if (!empty($u['is_premium'])): ?> · <strong>Premium</strong><?php endif; ?>
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
  <div class="layout-category">
    <div class="category-header">
      <div class="category-title"><?= htmlspecialchars(hs_t('category_heading', '{category} News', ['category' => $category['name']])) ?></div>
      <div class="category-sub"><?= htmlspecialchars(hs_t('category_latest', 'Latest stories from {category}', ['category' => $category['name']])) ?></div>
    </div>

    <?= $render_ad('category_top', hs_t('advertisement', 'Advertisement')) ?>

    <?php if (empty($posts)): ?>
      <p><?= htmlspecialchars(hs_t('category_empty', 'No posts in this category yet.')) ?></p>
    <?php else: ?>
      <div class="card-grid">
        <?php foreach ($posts as $p): ?>
          <article class="news-card">
            <?php if (!empty($p['image_main'])): ?>
              <div class="news-thumb">
                <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
              </div>
            <?php endif; ?>
            <div class="news-inner">
              <div class="news-kicker">
                <?= htmlspecialchars($p['category_name'] ?: 'News') ?>
              </div>
                <h2 class="news-title">
                  <a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a>
                </h2>
              <div class="news-meta">
                <?= hs_post_date_local($p) ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?= $render_ad('category_inline', hs_t('advertisement', 'Advertisement')) ?>
  </div>
</main>

<?= $render_ad('global_footer', hs_t('advertisement', 'Advertisement')) ?>

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
