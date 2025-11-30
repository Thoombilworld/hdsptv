<?php
require __DIR__ . '/bootstrap.php';

$settings = hs_settings();
$db = hs_db();
$languageCode = hs_current_language_code();

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    echo "Tag not specified.";
    exit;
}

$stmt = mysqli_prepare($db, "SELECT id, name, slug FROM hs_tags WHERE slug = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $slug);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$tag = $res ? mysqli_fetch_assoc($res) : null;

if (!$tag) {
    echo "Tag not found.";
    exit;
}

$tag_id = (int)$tag['id'];

$posts = [];
$pRes = mysqli_query($db, "SELECT p.*, c.name AS category_name
                           FROM hs_posts p
                           LEFT JOIN hs_categories c ON c.id = p.category_id
                           JOIN hs_post_tags pt ON pt.post_id = p.id
                           WHERE p.status='published' AND pt.tag_id = " . $tag_id . "
                           ORDER BY p.created_at DESC
                           LIMIT 40");
if ($pRes) {
    while ($row = mysqli_fetch_assoc($pRes)) $posts[] = $row;
}

hs_track_event([
    'type'        => 'tag',
    'category_id' => !empty($posts) ? (int)$posts[0]['category_id'] : null,
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
        $html .= '<img src="' . htmlspecialchars(hs_base_url($ad['image_url'])) . '" alt="Advertisement">';
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
$page_title = '#' . $tag['name'] . ' – ' . $site_title;
$meta_desc = 'Articles tagged with ' . $tag['name'] . ' on ' . $site_title;
$meta_keys = ($settings['seo_meta_keywords'] ?? '') . ', ' . $tag['name'];
$canonical = hs_tag_url($tag['slug']);
?>
<!doctype html>
<html lang="<?= htmlspecialchars($languageCode) ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($meta_desc) ?>">
  <meta name="keywords" content="<?= htmlspecialchars($meta_keys) ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
  <link rel="icon" href="<?= htmlspecialchars($settings['favicon'] ?? hs_base_url('assets/images/favicon.png')) ?>">
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

    .page {
      width:100%;
      min-height:100vh;
      padding:18px 12px 32px;
      box-sizing:border-box;
    }
    .layout-tag {
      max-width:1160px;
      margin:0 auto;
    }
    .tag-header {
      margin-bottom:14px;
    }
    .tag-title {
      font-size:22px;
      font-weight:800;
      letter-spacing:.08em;
      text-transform:uppercase;
    }
    .tag-sub {
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
    <a href="<?= hs_category_url('') ?>">India</a>
    <a href="<?= hs_category_url('') ?>">GCC</a>
    <a href="<?= hs_category_url('') ?>">Kerala</a>
    <a href="<?= hs_category_url('') ?>">World</a>
    <a href="<?= hs_category_url('') ?>">Sports</a>
    <a href="<?= hs_category_url('') ?>">Entertainment</a>
    <a href="<?= hs_category_url('') ?>">Business</a>
    <a href="<?= hs_category_url('') ?>">Technology</a>
    <a href="<?= hs_category_url('') ?>">Lifestyle</a>
    <a href="<?= hs_category_url('') ?>">Health</a>
    <a href="<?= hs_category_url('') ?>">Travel</a>
    <a href="<?= hs_category_url('') ?>">Auto</a>
    <a href="<?= hs_category_url('') ?>">Opinion</a>
    <a href="<?= hs_category_url('') ?>">Politics</a>
    <a href="<?= hs_category_url('') ?>">Crime</a>
    <a href="<?= hs_category_url('') ?>">Education</a>
    <a href="<?= hs_category_url('') ?>">Religion</a>
  </nav>
  <form class="nav-search" action="<?= hs_search_url() ?>" method="get">
    <input type="text" name="q" placeholder="Search news..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
    <button type="submit">Search</button>
  </form>
</header>

<?= $render_ad('global_top', 'Advertisement') ?>

<main class="page">
  <div class="layout-tag">
    <div class="tag-header">
      <div class="tag-title">#<?= htmlspecialchars($tag['name']) ?></div>
      <div class="tag-sub">Articles with this tag</div>
    </div>

    <?= $render_ad('tag_inline', 'Advertisement') ?>

    <?php if (empty($posts)): ?>
      <p>No posts with this tag yet.</p>
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
  </div>
</main>

<?= $render_ad('global_footer', 'Advertisement') ?>

<footer>
  <div class="footer-links"><?= hs_footer_links_html(); ?></div>
  <div class="footer-copy">© <?= date('Y') ?> <?= htmlspecialchars($settings['site_title'] ?? 'NEWS HDSPTV') ?>. All rights reserved.</div>
</footer>
</body>
</html>
