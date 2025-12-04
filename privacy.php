<?php
require __DIR__ . '/bootstrap.php';

$settings = hs_settings();
$theme = hs_current_theme();
$palette = hs_theme_palette($theme);

$site_title = $settings['site_title'] ?? 'NEWS HDSPTV';
$page_title = 'Privacy Policy – ' . $site_title;
$meta_desc = $settings['seo_meta_description'] ?? ($settings['tagline'] ?? '');
$meta_keys = $settings['seo_meta_keywords'] ?? '';
$canonical = hs_base_url('privacy');
$privacy_content = $settings['privacy_content'] ?? '';
$languageCode = hs_current_language_code();
$languageDir = hs_is_rtl($languageCode) ? 'rtl' : 'ltr';
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

    .top-left { display:flex; align-items:center; gap:10px; }
    .logo-link { display:flex; align-items:center; gap:10px; color:inherit; text-decoration:none; }
    .logo-link:hover { text-decoration:none; color:#FACC15; }
    .logo-mark { width:32px; height:32px; border-radius:14px; background: radial-gradient(circle at 20% 0, #FACC15 0, #1E3A8A 45%, #020617 100%); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:16px; color:#F9FAFB; box-shadow:0 10px 25px rgba(15,23,42,0.6); }
    .logo-text { display:flex; flex-direction:column; }
    .logo-text-main { font-weight:800; letter-spacing:.18em; font-size:13px; }
    .logo-text-tag { font-size:11px; color:#E5E7EB; opacity:.85; }

    .nav-main { display:flex; align-items:center; gap:12px; font-size:12px; text-transform:uppercase; letter-spacing:.12em; }
    .nav-main a { color:#E5E7EB; padding:4px 6px; border-radius:999px; }
    .nav-main a:hover { background:rgba(15,23,42,0.8); color:#FACC15; text-decoration:none; }

    .nav-search { margin-left:auto; margin-right:12px; margin-top:4px; }
    .nav-search input[type="text"] { padding:4px 10px; border-radius:999px; border:1px solid rgba(148,163,184,0.9); font-size:12px; background:#FFFFFF; color:#111827; min-width:200px; }
    .nav-search input[type="text"]::placeholder { color:#9CA3AF; }
    .nav-search button { display:none; }

    .user-bar { font-size:12px; color:#E5E7EB; padding:6px 0; }
    .user-bar a { color:#FACC15; }

    .page { padding:20px 18px 34px; max-width:1024px; margin:0 auto; }
    .content-card { background: var(--hs-card); border:1px solid var(--hs-border); border-radius:16px; padding:22px 22px 26px; box-shadow:0 25px 55px rgba(0,0,0,0.28); }
    .eyebrow { text-transform:uppercase; letter-spacing:.2em; font-size:11px; color:var(--hs-muted); margin:0 0 8px; }
    .content-title { font-size:26px; margin:0 0 6px; color:var(--hs-text); }
    .content-sub { margin:0 0 16px; color:var(--hs-muted); }
    .content-section { margin-bottom:16px; }
    .content-section h2 { margin:0 0 6px; font-size:18px; }
    .content-section p { margin:0 0 10px; line-height:1.6; color:var(--hs-muted); }
    .content-list { padding-left:18px; color:var(--hs-muted); line-height:1.6; }

    footer { border-top:1px solid rgba(31,41,55,0.9); padding:10px 18px 16px; font-size:11px; color:#9CA3AF; text-align:center; background:linear-gradient(180deg, rgba(15,23,42,0.98), #020617); }
    .footer-links { margin-bottom:6px; }
    .footer-links a { color:#E5E7EB; }
    .footer-links a:hover { color:#FACC15; text-decoration:none; }

    @media (max-width:640px) {
      header { padding:8px 10px; }
      .nav-search { width:100%; margin:6px 0 0; }
      .nav-search input[type="text"] { width:100%; }
      .nav-main { overflow-x:auto; width:100%; padding:6px 0; }
      .content-card { padding:16px; }
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
</header>

<main class="page">
  <article class="content-card">
    <div class="eyebrow">Privacy</div>
    <h1 class="content-title">Privacy Policy</h1>
    <p class="content-sub">This page explains what data <?= htmlspecialchars($site_title) ?> collects, how we use it, and the options you have to control your information.</p>

    <?php if (!empty($privacy_content)): ?>
      <div class="content-section"><?= $privacy_content ?></div>
    <?php else: ?>
      <div class="content-section">
        <h2>1. Information we collect</h2>
        <p>We collect the information you provide when creating an account, posting comments, or contacting us. We also log technical details such as device type, browser, and approximate location for security and analytics.</p>
      </div>

      <div class="content-section">
        <h2>2. Cookies and tracking</h2>
        <p>We use cookies to remember your preferences, keep you signed in, and measure site performance. Third-party embeds such as videos or ads may set their own cookies according to their policies.</p>
      </div>

      <div class="content-section">
        <h2>3. How we use data</h2>
        <ul class="content-list">
          <li>Deliver news, alerts, and personalized content you request.</li>
          <li>Secure accounts, prevent abuse, and troubleshoot issues.</li>
          <li>Measure readership to improve coverage and product quality.</li>
        </ul>
      </div>

      <div class="content-section">
        <h2>4. Sharing</h2>
        <p>We do not sell your personal data. We may share limited information with trusted vendors for hosting, analytics, or email delivery under confidentiality obligations.</p>
      </div>

      <div class="content-section">
        <h2>5. Your choices</h2>
        <p>You can update your profile details from your dashboard, unsubscribe from marketing emails via the link provided, or request data removal by contacting us.</p>
      </div>

      <div class="content-section">
        <h2>6. Data retention</h2>
        <p>We keep account information while your profile remains active and as required by law. Backups are purged on a rolling schedule to minimize retention.</p>
      </div>

      <div class="content-section">
        <h2>7. Updates</h2>
        <p>Privacy practices may evolve as we add features. We will revise this page with the effective date when changes occur.</p>
      </div>

      <div class="content-section">
        <h2>8. Contact</h2>
        <p>If you have privacy questions or access requests, please use our contact page so we can respond promptly.</p>
      </div>
    <?php endif; ?>
  </article>
</main>

<footer>
  <div class="footer-links"><?= hs_footer_links_html(); ?></div>
  <div class="footer-copy">© <?= date('Y') ?> <?= htmlspecialchars($site_title) ?>. <?= htmlspecialchars(hs_t('footer_rights', 'All rights reserved.')) ?></div>
</footer>
</body>
</html>
