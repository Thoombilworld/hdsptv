<?php
  $theme = hs_current_theme();
  $palette = hs_theme_palette($theme);
  $ads = $ads ?? [];
  $ad_for = function ($slot) use ($ads) {
      return $ads[$slot] ?? null;
  };
  $layout = $layout ?? hs_home_layout($settings ?? []);

  $languageCode = hs_current_language_code();
  $languageDir = hs_is_rtl($languageCode) ? 'rtl' : 'ltr';

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
<html lang="<?= htmlspecialchars($languageCode) ?>" dir="<?= htmlspecialchars($languageDir) ?>">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($settings['site_title'] ?? 'NEWS HDSPTV') ?></title>
  <meta name="description" content="<?= htmlspecialchars($settings['seo_meta_description'] ?? ($settings['tagline'] ?? '')) ?>">
  <meta name="keywords" content="<?= htmlspecialchars($settings['seo_meta_keywords'] ?? '') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
  <link rel="icon" href="<?= htmlspecialchars($settings['favicon'] ?? hs_base_url('assets/images/favicon.png')) ?>">

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
      background: #F5F7FB;
      color: var(--hs-text);
    }

    a { color: var(--hs-accent); text-decoration: none; }
    a:hover { text-decoration: underline; }

    header {
      position: sticky;
      top: 0;
      z-index: 40;
      backdrop-filter: blur(18px);
      background: #0F172A;
      border-bottom: 1px solid var(--hs-border);
      padding: 10px 0;
      box-shadow: 0 10px 35px rgba(15, 23, 42, 0.18);
    }

    .header-inner {
      width: min(1280px, 100% - 20px);
      margin: 0 auto;
      display: grid;
      grid-template-columns: auto 1fr;
      gap: 14px;
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
      gap:14px;
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
      padding:8px 10px;
      border-radius:12px;
    }
    .nav-main a:hover { background:rgba(15,23,42,0.8); color:#FACC15; text-decoration:none; }

    .nav-categories {
      display:flex;
      align-items:center;
      gap:10px;
      flex-wrap:wrap;
    }
    .nav-categories .chip {
      background:rgba(30,41,59,0.9);
      color:#E5E7EB;
      border:1px solid rgba(148,163,184,0.5);
      border-radius:12px;
      padding:8px 12px;
      font-size:11px;
      letter-spacing:.08em;
      text-transform:uppercase;
      box-shadow:0 8px 20px rgba(0,0,0,0.18);
    }
    .nav-categories .chip:hover { background:rgba(30,64,175,0.5); color:#FACC15; text-decoration:none; }

    .category-ribbon {
      width:100%;
      background:#0B1120;
      border-bottom:1px solid rgba(148,163,184,0.25);
      box-shadow:0 18px 38px rgba(15,23,42,0.18);
    }
    .category-ribbon-inner {
      width:min(1280px, 100% - 20px);
      margin:0 auto;
      padding:10px 12px 12px;
    }
    .category-ribbon-head {
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:10px;
      margin-bottom:10px;
      color:#E2E8F0;
    }
    .category-ribbon-title {
      font-size:12px;
      letter-spacing:.2em;
      text-transform:uppercase;
    }
    .category-ribbon-toggle {
      background:#1E293B;
      color:#E5E7EB;
      border:1px solid rgba(148,163,184,0.45);
      border-radius:10px;
      padding:8px 12px;
      font-size:12px;
      cursor:pointer;
    }

    .category-drawer {
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap:10px;
      max-height:420px;
      overflow:hidden;
      transition:max-height .25s ease;
    }
    .category-drawer.is-collapsed { max-height:0; padding:0; margin:0; border:0; }
    .category-pill {
      background:#111827;
      border:1px solid rgba(148,163,184,0.3);
      border-radius:12px;
      padding:10px 12px;
      display:flex;
      flex-direction:column;
      gap:4px;
      color:#E5E7EB;
      box-shadow:0 10px 22px rgba(15,23,42,0.25);
    }
    .category-pill h4 {
      margin:0;
      font-size:13px;
      letter-spacing:.1em;
      text-transform:uppercase;
      color:#FACC15;
    }
    .category-pill small { color:#94A3B8; font-size:11px; }

    .nav-utilities {
      width:100%;
      display:grid;
      grid-template-columns: minmax(0, 2fr) auto;
      align-items:center;
      gap:10px;
    }

    .nav-search { margin:0; }
    .nav-search input[type="text"] {
      padding:12px 14px;
      border-radius:12px;
      border:1px solid rgba(148,163,184,0.9);
      font-size:13px;
      background:#FFFFFF;
      color:#111827;
      width:100%;
      min-width:320px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
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
      background: #FFFFFF;
      border-radius:18px;
      box-shadow:0 16px 40px rgba(15,23,42,0.10);
      border:1px solid rgba(15,23,42,0.08);
      padding:14px 16px;
      margin-bottom:14px;
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
      display:flex;
      gap:8px;
      flex-wrap:wrap;
      opacity:.9;
    }

    /* Redesigned featured slider */
    .slider-shell {
      position: relative;
      display: grid;
      grid-template-columns: minmax(0, 2.2fr) minmax(0, 1.1fr);
      gap: 14px;
      align-items: stretch;
    }
    .slider-frame {
      background:#0B1120;
      border-radius:22px;
      box-shadow:0 18px 55px rgba(15,23,42,0.35);
      overflow:hidden;
      position:relative;
      min-height:360px;
    }
    .slider-track {
      position:relative;
      height:100%;
    }
    .slider-slide {
      position:absolute;
      inset:0;
      opacity:0;
      transition:opacity .45s ease;
      display:grid;
      grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
      gap:16px;
      padding:18px;
      box-sizing:border-box;
      background:linear-gradient(135deg, rgba(15,23,42,0.95), rgba(15,23,42,0.94));
    }
    .slider-slide.is-active { opacity:1; position:absolute; }
    .slider-media img {
      width:100%; height:100%; object-fit:cover;
      border-radius:16px; display:block;
    }
    .slider-placeholder {
      width:100%; height:100%; min-height:240px;
      border-radius:16px;
      background:radial-gradient(circle at 20% 20%, rgba(99,102,241,0.35), rgba(15,23,42,0.95));
      color:#E2E8F0;
      display:flex;
      align-items:center;
      justify-content:center;
      font-weight:700;
      letter-spacing:.08em;
      text-transform:uppercase;
    }
    .slider-body { display:flex; flex-direction:column; gap:8px; justify-content:center; }
    .slider-kicker { font-size:11px; letter-spacing:.18em; color:#FACC15; text-transform:uppercase; }
    .slider-title { font-size:24px; font-weight:800; color:#F9FAFB; line-height:1.2; }
    .slider-meta { font-size:13px; color:#CBD5E1; display:flex; gap:10px; flex-wrap:wrap; }
    .slider-excerpt { font-size:13px; color:#E2E8F0; line-height:1.5; max-width:58ch; }
    .slider-nav {
      position:absolute;
      right:16px;
      bottom:16px;
      display:flex;
      gap:6px;
      background:rgba(15,23,42,0.7);
      padding:6px 10px;
      border-radius:12px;
      backdrop-filter: blur(10px);
    }
    .slider-dot {
      width:12px; height:12px; border-radius:999px;
      background:#1E293B;
      border:1px solid rgba(255,255,255,0.15);
      cursor:pointer;
    }
    .slider-dot.is-active { background:#FACC15; border-color:#FACC15; }

    .slider-stack {
      display:grid;
      gap:10px;
    }
    .slider-mini-card {
      display:grid;
      grid-template-columns: 92px 1fr;
      gap:10px;
      background:#FFFFFF;
      border-radius:14px;
      border:1px solid rgba(15,23,42,0.08);
      box-shadow:0 10px 25px rgba(15,23,42,0.12);
      overflow:hidden;
      padding:8px;
    }
    .slider-mini-card img { width:100%; height:78px; object-fit:cover; border-radius:12px; }
    .slider-mini-placeholder {
      width:100%; height:78px; border-radius:12px;
      background:linear-gradient(135deg, rgba(99,102,241,0.3), rgba(15,23,42,0.9));
      display:flex; align-items:center; justify-content:center; color:#E2E8F0; font-size:11px; letter-spacing:.12em;
    }
    .slider-mini-meta { font-size:11px; text-transform:uppercase; letter-spacing:.12em; color:#6366F1; }
    .slider-mini-title { font-size:14px; font-weight:700; color:#0F172A; line-height:1.35; }
    .slider-mini-date { font-size:12px; color:#475569; }

    @media (max-width:1080px) {
      .slider-shell { grid-template-columns:1fr; }
      .slider-slide { grid-template-columns:1fr; }
      .slider-mini-card { grid-template-columns: 84px 1fr; }
      .slider-frame { min-height:320px; }
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

    .category-grid {
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap:12px;
      margin-top:10px;
    }
    .category-card {
      background:#FFFFFF;
      border-radius:14px;
      border:1px solid rgba(15,23,42,0.08);
      box-shadow:0 12px 30px rgba(15,23,42,0.12);
      padding:12px;
      display:flex;
      flex-direction:column;
      gap:6px;
    }
    .category-card h3 {
      margin:0;
      font-size:13px;
      letter-spacing:.12em;
      text-transform:uppercase;
      color:#0F172A;
      display:flex;
      justify-content:space-between;
      align-items:center;
    }
    .category-card ul {
      list-style:none;
      padding:0;
      margin:0;
      display:grid;
      gap:6px;
    }
    .category-card li { font-size:13px; color:#0F172A; }
    .category-card small { color:#475569; font-size:12px; }

    .section-shell {
      margin:18px 0 8px;
      display:flex;
      align-items:center;
      gap:10px;
    }
    .section-shell h2 {
      margin:0;
      font-size:15px;
      letter-spacing:.14em;
      text-transform:uppercase;
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
  $all_categories = $categories ?? [];
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

  $gcc_country_blocks = [
    'qatar'   => [],
    'uae'     => [],
    'saudi'   => [],
    'oman'    => [],
    'kuwait'  => [],
    'bahrain' => [],
  ];

  $gcc_country_labels = [
    'qatar'   => 'Qatar News',
    'uae'     => 'UAE News',
    'saudi'   => 'Saudi News',
    'oman'    => 'Oman News',
    'kuwait'  => 'Kuwait News',
    'bahrain' => 'Bahrain News',
  ];

  $category_posts = [];

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
    $cat_slug = strtolower($p['category_slug'] ?? '');

    if (isset($gcc_country_blocks[$cat_slug])) {
      $gcc_country_blocks[$cat_slug][] = $p;
    }

    if (!empty($cat_slug)) {
      if (!isset($category_posts[$cat_slug])) {
        $category_posts[$cat_slug] = [];
      }
      $category_posts[$cat_slug][] = $p;
    }

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

  $slider_posts = !empty($featured) ? $featured : array_slice($posts, 0, 5);
  if (empty($slider_posts) && !empty($posts)) {
    $slider_posts = array_slice($posts, 0, 5);
  }
  $slider_mini = array_slice($slider_posts, 1, 6);

  $category_blocks = [];

  if (!empty($all_categories)) {
    foreach ($all_categories as $cat) {
      $slug = strtolower($cat['slug']);
      $category_blocks[$slug] = [
        'label' => $cat['name'],
        'slug'  => $slug,
        'items' => $category_posts[$slug] ?? [],
      ];
    }
  } else {
    foreach ($category_posts as $slug => $items) {
      $category_blocks[$slug] = [
        'label' => ucwords(str_replace('-', ' ', $slug)),
        'slug'  => $slug,
        'items' => $items,
      ];
    }
  }

  $category_nav_cards = array_values($category_blocks);
  if (count($category_nav_cards) > 18) {
    $category_nav_cards = array_slice($category_nav_cards, 0, 18);
  }

  function hs_post_date($p) {
    return !empty($p['created_at']) ? date('M j, Y', strtotime($p['created_at'])) : '';
  }

  function hs_excerpt($text, $length = 140) {
    $clean = trim(strip_tags($text ?? ''));
    if (strlen($clean) <= $length) return $clean;
    return substr($clean, 0, $length - 3) . '...';
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
          <a href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars(hs_t('nav_' . $item['slug'], $item['label'])) ?></a>
        <?php endforeach; ?>
      </nav>
      <?php if (!empty($category_nav_cards)): ?>
        <div class="nav-categories" aria-label="Quick category chips">
          <?php foreach (array_slice($category_nav_cards, 0, 6) as $chip): ?>
            <?php $chipSlug = $chip['slug'] ?? strtolower($chip['label']); ?>
            <a class="chip" href="<?= hs_category_url($chipSlug) ?>"><?= htmlspecialchars($chip['label']) ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div class="nav-utilities stack-mobile">
        <form class="nav-search" action="<?= hs_search_url() ?>" method="get" data-testid="nav-search-form">
          <label class="sr-only" for="nav-search"><?= htmlspecialchars(hs_t('search_label', 'Search')) ?></label>
          <input id="nav-search" type="text" name="q" placeholder="<?= htmlspecialchars(hs_t('search_placeholder', 'Search news...')) ?>" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" data-testid="nav-search-input">
          <button type="submit"><?= htmlspecialchars(hs_t('search_label', 'Search')) ?></button>
        </form>
        <form class="language-form" method="get" action="">
          <label class="language-label" for="language-select"><?= htmlspecialchars(hs_t('language_label', 'Language')) ?></label>
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
          <?php if (!empty($u['is_premium'])): ?> · <strong><?= htmlspecialchars(hs_t('nav_premium', 'Premium')) ?></strong><?php endif; ?>
          · <a href="<?= hs_dashboard_url() ?>"><?= htmlspecialchars(hs_t('nav_dashboard', 'Dashboard')) ?></a>
          · <a href="<?= hs_logout_url() ?>"><?= htmlspecialchars(hs_t('nav_logout', 'Logout')) ?></a>
        <?php else: ?>
          <a href="<?= hs_login_url() ?>"><?= htmlspecialchars(hs_t('nav_login', 'Login')) ?></a> ·
          <a href="<?= hs_register_url() ?>"><?= htmlspecialchars(hs_t('nav_register', 'Register')) ?></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<div class="category-ribbon" aria-label="Category quick links">
  <div class="category-ribbon-inner">
    <div class="category-ribbon-head">
      <div>
        <div class="category-ribbon-title"><?= htmlspecialchars(hs_t('categories', 'Categories')) ?></div>
        <div style="font-size:12px; color:#94A3B8;">Blocks pull directly from Admin → Categories so you can reorder and test quickly.</div>
      </div>
      <button class="category-ribbon-toggle" type="button" data-category-toggle>
        <?= htmlspecialchars(hs_t('toggle_categories', 'Show / Hide')) ?>
      </button>
    </div>
    <div class="category-drawer" data-category-drawer>
      <?php if (empty($category_nav_cards)): ?>
        <div style="color:#E2E8F0; font-size:12px;">No categories found. Add some from the Admin panel.</div>
      <?php else: ?>
        <?php foreach ($category_nav_cards as $block): ?>
          <?php $navSlug = $block['slug'] ?? strtolower($block['label']); ?>
          <a class="category-pill" href="<?= hs_category_url($navSlug) ?>">
            <h4><?= htmlspecialchars($block['label']) ?></h4>
            <small><?= !empty($block['items']) ? count($block['items']) . ' ' . htmlspecialchars(hs_t('posts', 'Posts')) : htmlspecialchars(hs_t('empty_category', 'Awaiting content')) ?></small>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if ($layout['ads_top'] && ($topAd = $ad_for('homepage_top'))): ?>
  <div class="ads-slot ads-top">
    <?= hs_render_ad($topAd) ?>
  </div>
<?php endif; ?>

<?php if ($layout['breaking'] && !empty($breaking)): ?>
  <div class="ticker">
    <div class="ticker-label"><?= htmlspecialchars(hs_t('breaking_title', 'Breaking')) ?></div>
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
      <?php if ($layout['featured']): ?>
      <div class="card">
        <div class="section-shell">
          <div class="pill"><span class="pill-dot"></span> <?= htmlspecialchars(hs_t('featured_slider', 'Top Featured Slider')) ?></div>
          <h2><?= htmlspecialchars(hs_t('featured_rotation', 'Automatic rotation')) ?></h2>
        </div>
        <?php if (!empty($slider_posts)): ?>
          <div class="slider-shell" data-testid="featured-slider">
            <div class="slider-frame">
              <div class="slider-track" data-slider-track>
                <?php foreach ($slider_posts as $idx => $p): ?>
                  <article class="slider-slide <?= $idx === 0 ? 'is-active' : '' ?>" data-index="<?= $idx ?>">
                    <div class="slider-media">
                      <?php if (!empty($p['image_main'])): ?>
                        <img src="<?= hs_base_url($p['image_main']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                      <?php else: ?>
                        <div class="slider-placeholder"><?= htmlspecialchars(hs_t('featured_placeholder', 'Top Story')) ?></div>
                      <?php endif; ?>
                    </div>
                    <div class="slider-body">
                      <div class="slider-kicker">
                        <?= htmlspecialchars($p['category_name'] ?: hs_t('featured_category_placeholder', 'News')) ?>
                        <?php if (!empty($p['region']) && $p['region'] !== 'global'): ?>
                          · <?= strtoupper(htmlspecialchars($p['region'])) ?>
                        <?php endif; ?>
                      </div>
                      <h1 class="slider-title"><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></h1>
                      <div class="slider-meta">
                        <span><?= hs_post_date($p) ?></span>
                        <?php if (!empty($p['author'])): ?>
                          <span>By <?= htmlspecialchars($p['author']) ?></span>
                        <?php endif; ?>
                      </div>
                      <div class="slider-excerpt"><?= htmlspecialchars(hs_excerpt($p['description'] ?? $p['title'], 170)) ?></div>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
              <div class="slider-nav" aria-label="Slider navigation dots">
                <?php foreach ($slider_posts as $idx => $p): ?>
                  <button type="button" class="slider-dot <?= $idx === 0 ? 'is-active' : '' ?>" data-target="<?= $idx ?>" aria-label="Go to slide <?= $idx + 1 ?>"></button>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="slider-stack">
              <?php if (empty($slider_mini)): ?>
                <div style="font-size:12px; color:#475569;">Mark posts as featured to populate this tray.</div>
              <?php else: ?>
                <?php foreach ($slider_mini as $mini): ?>
                  <article class="slider-mini-card">
                    <div>
                      <?php if (!empty($mini['image_main'])): ?>
                        <img src="<?= hs_base_url($mini['image_main']) ?>" alt="<?= htmlspecialchars($mini['title']) ?>">
                      <?php else: ?>
                        <div class="slider-mini-placeholder">News</div>
                      <?php endif; ?>
                    </div>
                    <div>
                      <div class="slider-mini-meta"><?= htmlspecialchars($mini['category_name'] ?: hs_t('featured_category_placeholder', 'News')) ?></div>
                      <div class="slider-mini-title"><a href="<?= hs_news_url($mini['slug']) ?>"><?= htmlspecialchars($mini['title']) ?></a></div>
                      <div class="slider-mini-date"><?= hs_post_date($mini) ?></div>
                    </div>
                  </article>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        <?php else: ?>
          <p style="font-size:12px; color:#475569;">No stories yet. Add posts from Admin → Content Manager.</p>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if ($layout['ads_inline'] && ($inlineAd = $ad_for('homepage_inline'))): ?>
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
      <div class="section-shell">
        <div class="pill"><span class="pill-dot"></span> GCC Country Blocks</div>
        <h2>GCC Country News</h2>
      </div>
      <div class="category-grid">
        <?php foreach ($gcc_country_labels as $slug => $label): $items = $gcc_country_blocks[$slug] ?? []; ?>
          <div class="category-card">
            <h3><?= htmlspecialchars($label) ?> <a href="<?= hs_category_url($slug) ?>" style="font-size:11px; color:#2563EB;">View All</a></h3>
            <?php if (empty($items)): ?>
              <small>No <?= htmlspecialchars($label) ?> posts yet.</small>
            <?php else: ?>
              <ul>
                <?php foreach (array_slice($items, 0, 3) as $p): ?>
                  <li>
                    <div><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                    <small><?= hs_post_date($p) ?></small>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="card">
      <div class="section-shell">
        <div class="pill"><span class="pill-dot"></span> Category Blocks</div>
        <h2>All categories</h2>
      </div>
      <div class="category-grid">
        <?php if (empty($category_blocks)): ?>
          <small>No categories configured yet.</small>
        <?php else: ?>
          <?php foreach ($category_blocks as $slug => $block): $items = $block['items'] ?? []; ?>
            <div class="category-card">
              <h3><?= htmlspecialchars($block['label'] ?? $slug) ?> <a href="<?= hs_category_url($slug) ?>" style="font-size:11px; color:#2563EB;">View All</a></h3>
              <?php if (empty($items)): ?>
                <small>No stories published yet.</small>
              <?php else: ?>
                <ul>
                  <?php foreach (array_slice($items, 0, 4) as $p): ?>
                    <li>
                      <div><a href="<?= hs_news_url($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                      <small><?= hs_post_date($p) ?></small>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    </section>

    <aside class="column">
      <?php $rightAd = $layout['ads_sidebar'] ? $ad_for('homepage_right') : null; ?>
      <?php if ($layout['ads_sidebar'] && $rightAd): ?>
        <div class="ads-slot ads-inline">
          <?= hs_render_ad($rightAd) ?>
        </div>
      <?php endif; ?>

      <?php if ($layout['trending']): ?>
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
      <?php endif; ?>

      <?php if ($layout['video']): ?>
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
      <?php endif; ?>

      <?php if ($layout['gallery']): ?>
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
      <?php endif; ?>

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

      <?php if ($layout['ads_sidebar'] && !$rightAd): ?>
      <section class="card side-card">
        <div class="section-title">Homepage Sidebar Ad</div>
        <div class="ads-slot">
          Homepage Sidebar Ad Slot<br>
          (Manage this from Admin → Ads)
        </div>
      </section>
      <?php endif; ?>
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

  const sliderTrack = document.querySelector('[data-slider-track]');
  if (sliderTrack) {
    const slides = Array.from(sliderTrack.querySelectorAll('.slider-slide'));
    const dots = Array.from(document.querySelectorAll('.slider-dot'));
    let active = 0;

    const setSlide = (idx) => {
      active = idx;
      slides.forEach((s, i) => s.classList.toggle('is-active', i === idx));
      dots.forEach((d, i) => d.classList.toggle('is-active', i === idx));
    };

    let intervalId = slides.length > 1 ? setInterval(() => {
      setSlide((active + 1) % slides.length);
    }, 6000) : null;

    dots.forEach((dot, i) => {
      dot.addEventListener('click', () => {
        setSlide(i);
        if (intervalId) {
          clearInterval(intervalId);
          intervalId = setInterval(() => setSlide((active + 1) % slides.length), 6000);
        }
      });
    });
  }

  const categoryDrawer = document.querySelector('[data-category-drawer]');
  const categoryToggle = document.querySelector('[data-category-toggle]');
  if (categoryDrawer && categoryToggle) {
    let collapsed = false;
    categoryToggle.addEventListener('click', () => {
      collapsed = !collapsed;
      categoryDrawer.classList.toggle('is-collapsed', collapsed);
      categoryToggle.innerText = collapsed ? '<?= addslashes(hs_t('show_categories', 'Show categories')) ?>' : '<?= addslashes(hs_t('hide_categories', 'Hide categories')) ?>';
    });
  }
</script>
</body>
</html>
