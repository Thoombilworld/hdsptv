<?php
require __DIR__ . '/bootstrap.php';

$settings = hs_settings();
$theme = hs_current_theme();
$palette = hs_theme_palette($theme);

$site_title = $settings['site_title'] ?? 'NEWS HDSPTV';
$page_title = 'Terms & Conditions – ' . $site_title;
$meta_desc = $settings['seo_meta_description'] ?? ($settings['tagline'] ?? '');
$meta_keys = $settings['seo_meta_keywords'] ?? '';
$canonical = hs_base_url('terms');
$terms_content = $settings['terms_content'] ?? '';
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
  <form class="nav-search" action="<?= hs_base_url('search.php') ?>" method="get">
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
    <div class="eyebrow">Legal</div>
    <h1 class="content-title">Terms &amp; Conditions</h1>
    <p class="content-sub">These terms outline how you may use <?= htmlspecialchars($site_title) ?>, including content ownership, user conduct, and the rules that keep our community trustworthy.</p>

    <?php if (!empty($terms_content)): ?>
      <div class="content-section"><?= $terms_content ?></div>
    <?php else: ?>
      <div class="content-section">
        <h2>1. Acceptance of terms</h2>
        <p>By accessing or using this website you agree to abide by these Terms &amp; Conditions, our privacy commitments, and any additional guidelines posted inside the service.</p>
      </div>

      <div class="content-section">
        <h2>2. Use of content</h2>
        <p>Articles, photos, and videos are provided for your personal use. Republishing, framing, or automated scraping without prior written permission is prohibited.</p>
        <ul class="content-list">
          <li>Properly attribute and link back when sharing headlines or excerpts.</li>
          <li>Commercial syndication requires a license from the publisher.</li>
          <li>Do not modify our trademarks, branding, or copyright notices.</li>
        </ul>
      </div>

      <div class="content-section">
        <h2>3. User accounts</h2>
        <p>You are responsible for safeguarding your login credentials. Notify us immediately if you suspect unauthorized access so we can protect your account.</p>
      </div>

      <div class="content-section">
        <h2>4. Submissions and feedback</h2>
        <p>By sending tips, comments, or media you grant us a non-exclusive, royalty-free right to use and share that material for editorial purposes. You confirm you have the right to provide the submission.</p>
      </div>

      <div class="content-section">
        <h2>5. Service availability</h2>
        <p>We strive for reliable uptime but may modify or suspend features to improve performance or security. We are not liable for losses resulting from downtime or delayed publishing.</p>
      </div>

      <div class="content-section">
        <h2>6. Limitation of liability</h2>
        <p>To the fullest extent permitted by law, <?= htmlspecialchars($site_title) ?> is provided “as is.” We disclaim warranties of accuracy, availability, and fitness for a particular purpose.</p>
      </div>

      <div class="content-section">
        <h2>7. Changes</h2>
        <p>We may update these terms to reflect operational or legal changes. Material updates will be noted on this page with a refreshed effective date.</p>
      </div>

      <div class="content-section">
        <h2>8. Contact</h2>
        <p>If you have questions about these terms, please reach out through our contact page so we can assist you quickly.</p>
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
