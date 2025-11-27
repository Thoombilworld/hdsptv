
<?php
require __DIR__ . '/bootstrap.php';

$settings = hs_settings();
$db = hs_db();
$theme = hs_current_theme();
$palette = hs_theme_palette($theme);

$q = trim($_GET['q'] ?? '');
$posts = [];

if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = mysqli_prepare($db, "SELECT p.*, c.name AS category_name
                                 FROM hs_posts p
                                 LEFT JOIN hs_categories c ON c.id = p.category_id
                                 WHERE p.status='published'
                                   AND (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)
                                 ORDER BY p.created_at DESC
                                 LIMIT 60");
    mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $posts[] = $row;
        }
    }
}

function hs_post_date_local($p) {
    return !empty($p['created_at']) ? date('M j, Y', strtotime($p['created_at'])) : '';
}

$site_title = $settings['site_title'] ?? 'NEWS HDSPTV';
$page_title = ($q !== '' ? ('Search: ' . $q . ' – ') : 'Search – ') . $site_title;
$meta_desc = $settings['seo_meta_description'] ?? '';
$meta_keys = $settings['seo_meta_keywords'] ?? '';
$canonical = hs_base_url('search.php' . ($q !== '' ? ('?q=' . urlencode($q)) : ''));
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
      border-bottom: 1px solid rgba(15,23,42,0.9);
      padding: 8px 18px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      flex-wrap:wrap;
    }

    .top-left {
      display:flex;
      align-items:center;
      gap:10px;
    }

    .logo-link {
      display:flex;
      align-items:center;
      gap:10px;
      color:inherit;
      text-decoration:none;
    }
    .logo-link:hover {
      text-decoration:none;
      color:#FACC15;
    }

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

    .logo-text {
      display:flex;
      flex-direction:column;
    }

    .logo-text-main {
      font-weight:800;
      letter-spacing:.18em;
      font-size:13px;
    }

    .logo-text-tag {
      font-size:11px;
      color:#E5E7EB;
      opacity:.85;
    }

    .nav-main {
      display:flex;
      align-items:center;
      gap:12px;
      font-size:12px;
      text-transform:uppercase;
      letter-spacing:.12em;
    }
    .nav-main a {
      color:#E5E7EB;
      padding:4px 6px;
      border-radius:999px;
    }
    .nav-main a:hover {
      background:rgba(15,23,42,0.8);
      color:#FACC15;
      text-decoration:none;
    }

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

    .user-bar {
      font-size:11px;
      color:#E5E7EB;
      text-align:right;
    }
    .user-bar a { color:#FACC15; }

    .page {
      width:100%;
      min-height:100vh;
      padding:18px 12px 32px;
      box-sizing:border-box;
    }

    .layout-search {
      max-width:1160px;
      margin:0 auto;
    }

    .search-header {
      margin-bottom:14px;
    }
    .search-title {
      font-size:20px;
      font-weight:700;
    }
    .search-sub {
      font-size:12px;
      color:#E5E7EB;
      margin-top:4px;
    }

    .result-list {
      margin-top:16px;
      display:grid;
      grid-template-columns: minmax(0,1.8fr) minmax(0,1.8fr);
      gap:14px;
    }
    .result-card {
      background:#F9FAFB;
      border-radius:14px;
      box-shadow:0 14px 30px rgba(15,23,42,0.6);
      color:#111827;
      padding:10px 12px 12px;
      display:flex;
      gap:10px;
    }
    .result-thumb {
      width:96px;
      height:72px;
      background:#E5E7EB;
      border-radius:10px;
      overflow:hidden;
      flex-shrink:0;
    }
    .result-thumb img {
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
    }
    .result-main {
      font-size:14px;
    }
    .result-kicker {
      font-size:11px;
      text-transform:uppercase;
      letter-spacing:.16em;
      color:#6B7280;
      margin-bottom:3px;
    }
    .result-title {
      font-weight:700;
      color:#111827;
      margin-bottom:4px;
    }
    .result-title a { color:#111827; }
    .result-title a:hover { color:#1D4ED8; text-decoration:none; }
    .result-meta {
      font-size:11px;
      color:#6B7280;
    }
    .result-excerpt {
      font-size:13px;
      color:#4B5563;
      margin-top:4px;
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

    @media (max-width:900px) {
      .result-list {
        grid-template-columns:minmax(0,1fr);
      }
    }
    @media (max-width:640px) {
      header { padding:8px 10px; }
      .page { padding:14px 8px 24px; }
      .nav-search {
        width:100%;
        margin:6px 0 0;
      }
      .nav-search input[type="text"] { width:100%; }
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
    <input type="text" name="q" placeholder="Search news..." value="<?= htmlspecialchars($q) ?>">
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
  <div class="layout-search">
    <div class="search-header">
      <div class="search-title">Search NEWS HDSPTV</div>
      <div class="search-sub">
        <?php if ($q === ''): ?>
          Type a keyword above and press Enter.
        <?php else: ?>
          Showing results for "<strong><?= htmlspecialchars($q) ?></strong>" (<?= count($posts) ?> found)
        <?php endif; ?>
      </div>
    </div>

    <?php if ($q !== '' && !empty($posts)): ?>
      <div class="result-list">
        <?php foreach ($posts as $p): ?>
          <article class="result-card">
            <?php if (!empty($p['image_main'])): ?>
              <div class="result-thumb">
                <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
              </div>
            <?php endif; ?>
            <div class="result-main">
              <div class="result-kicker">
                <?= htmlspecialchars($p['category_name'] ?: 'News') ?>
                <?php if (!empty($p['region']) && $p['region'] !== 'global'): ?>
                  · <?= strtoupper(htmlspecialchars($p['region'])) ?>
                <?php endif; ?>
              </div>
              <div class="result-title">
                <a href="<?= hs_base_url('post.php?slug=' . urlencode($p['slug'])) ?>"><?= htmlspecialchars($p['title']) ?></a>
              </div>
              <div class="result-meta">
                <?= hs_post_date_local($p) ?>
              </div>
              <?php if (!empty($p['excerpt'])): ?>
                <div class="result-excerpt">
                  <?= htmlspecialchars(mb_substr($p['excerpt'], 0, 140)) ?><?= (mb_strlen($p['excerpt']) > 140 ? '…' : '') ?>
                </div>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php elseif ($q !== ''): ?>
      <p>No results found.</p>
    <?php endif; ?>
  </div>
</main>

<footer>
  <div class="footer-links"><?= hs_footer_links_html(); ?></div>
  <div class="footer-copy">© <?= date('Y') ?> <?= htmlspecialchars($settings['site_title'] ?? 'NEWS HDSPTV') ?>. All rights reserved.</div>
</footer>

</body>
</html>
