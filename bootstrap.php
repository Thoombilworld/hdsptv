<?php
session_start();
require __DIR__ . '/config/config.php';

function hs_base_url($path = '') {
    return HS_BASE_URL . ltrim($path, '/');
}

function hs_news_url($slug) {
    return hs_base_url('news/' . urlencode($slug));
}

function hs_category_url($slug) {
    return hs_base_url('category/' . urlencode($slug));
}

function hs_tag_url($slug) {
    return hs_base_url('tag/' . urlencode($slug));
}

function hs_search_url($query = '') {
    if ($query === '') {
        return hs_base_url('search');
    }
    return hs_base_url('search?q=' . urlencode($query));
}

function hs_login_url() {
    return hs_base_url('login');
}

function hs_register_url() {
    return hs_base_url('register');
}

function hs_forgot_password_url() {
    return hs_base_url('forgot-password');
}

function hs_logout_url() {
    return hs_base_url('logout');
}

function hs_reset_password_url($token = '') {
    return hs_base_url('reset-password' . ($token !== '' ? ('?token=' . urlencode($token)) : ''));
}

function hs_dashboard_url() {
    return hs_base_url('dashboard');
}

function hs_primary_nav_items()
{
    return [
        ['label' => 'Home',           'slug' => 'home',          'url' => hs_base_url('index.php#top')],
        ['label' => 'India',          'slug' => 'india',         'url' => hs_category_url('india')],
        ['label' => 'GCC',            'slug' => 'gcc',           'url' => hs_category_url('gcc')],
        ['label' => 'Kerala',         'slug' => 'kerala',        'url' => hs_category_url('kerala')],
        ['label' => 'World',          'slug' => 'world',         'url' => hs_category_url('world')],
        ['label' => 'Sports',         'slug' => 'sports',        'url' => hs_category_url('sports')],
        ['label' => 'Entertainment',  'slug' => 'entertainment', 'url' => hs_category_url('entertainment')],
        ['label' => 'Business',       'slug' => 'business',      'url' => hs_category_url('business')],
        ['label' => 'Technology',     'slug' => 'technology',    'url' => hs_category_url('technology')],
        ['label' => 'Lifestyle',      'slug' => 'lifestyle',     'url' => hs_category_url('lifestyle')],
        ['label' => 'Health',         'slug' => 'health',        'url' => hs_category_url('health')],
        ['label' => 'Travel',         'slug' => 'travel',        'url' => hs_category_url('travel')],
        ['label' => 'Auto',           'slug' => 'auto',          'url' => hs_category_url('auto')],
        ['label' => 'Opinion',        'slug' => 'opinion',       'url' => hs_category_url('opinion')],
        ['label' => 'Politics',       'slug' => 'politics',      'url' => hs_category_url('politics')],
        ['label' => 'Crime',          'slug' => 'crime',         'url' => hs_category_url('crime')],
        ['label' => 'Education',      'slug' => 'education',     'url' => hs_category_url('education')],
        ['label' => 'Religion',       'slug' => 'religion',      'url' => hs_category_url('religion')],
    ];
}

function hs_supported_languages()
{
    return [
        'en' => 'English',
        'ar' => 'العربية',
        'ml' => 'മലയാളം',
    ];
}

function hs_current_language_code()
{
    $supported = hs_supported_languages();
    $requested = strtolower(trim($_GET['lang'] ?? ''));
    if ($requested !== '' && isset($supported[$requested])) {
        $_SESSION['hs_lang'] = $requested;
    }

    $selected = $_SESSION['hs_lang'] ?? array_key_first($supported);
    if (!isset($supported[$selected])) {
        $selected = array_key_first($supported);
    }

    return $selected;
}

function hs_current_language_label()
{
    $supported = hs_supported_languages();
    $code = hs_current_language_code();
    return $supported[$code] ?? 'English';
}

function hs_current_theme()
{
    static $theme = null;
    if ($theme !== null) {
        return $theme;
    }

    $requested = strtolower(trim($_GET['theme'] ?? ''));
    if (in_array($requested, ['light', 'dark'], true)) {
        $_SESSION['hs_theme'] = $requested;
    }

    $from_settings = hs_settings()['theme'] ?? 'dark';
    $theme = $_SESSION['hs_theme'] ?? $from_settings;
    if (!in_array($theme, ['light', 'dark'], true)) {
        $theme = 'dark';
    }

    return $theme;
}

function hs_theme_palette($theme)
{
    if ($theme === 'light') {
        return [
            'primary'       => '#1E3A8A',
            'primary_dark'  => '#0B1120',
            'accent'        => '#1E3A8A',
            'bg'            => '#F3F4F6',
            'surface'       => '#FFFFFF',
            'card'          => '#FFFFFF',
            'text'          => '#111827',
            'muted'         => '#6B7280',
            'border'        => '#E5E7EB',
        ];
    }

    // default dark palette
    return [
        'primary'      => '#1E3A8A',
        'primary_dark' => '#0B1120',
        'accent'       => '#FACC15',
        'bg'           => '#020617',
        'surface'      => '#020617',
        'card'         => 'rgba(15,23,42,0.96)',
        'text'         => '#F9FAFB',
        'muted'        => '#E5E7EB',
        'border'       => 'rgba(15,23,42,0.9)',
    ];
}

function hs_view($view, $data = []) {
    extract($data);
    include __DIR__ . '/app/Views/' . $view . '.php';
}

function hs_settings($force_reload = false)
{
    static $settings = null;
    if ($settings !== null && !$force_reload) {
        return $settings;
    }

    $settings = [
        'site_title' => HS_APP_NAME,
        'tagline'    => 'News for India, GCC, Kerala & the World',
        'logo'       => hs_base_url('assets/images/logo.png'),
        'theme'      => 'dark',
    ];

    if (defined('HS_INSTALLED') && HS_INSTALLED) {
        $res = mysqli_query(hs_db(), "SELECT `key`, `value` FROM hs_settings");
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $settings[$row['key']] = $row['value'];
            }
        }
    }

    return $settings;
}

function hs_active_ads()
{
    static $ads = null;
    if ($ads !== null) {
        return $ads;
    }

    $ads = [];
    if (!defined('HS_INSTALLED') || !HS_INSTALLED) {
        return $ads;
    }

    $res = mysqli_query(hs_db(), "SELECT * FROM hs_ads WHERE active = 1");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $ads[$row['slot']] = $row;
        }
    }

    return $ads;
}

function hs_ad($slot)
{
    $ads = hs_active_ads();
    return $ads[$slot] ?? null;
}

function hs_render_ad($ad)
{
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

function hs_ad_slots_catalog()
{
    return [
        'global_header'   => 'Global Header (all pages)',
        'global_sidebar'  => 'Global Sidebar',
        'global_footer'   => 'Global Footer',
        'homepage_top'    => 'Homepage Top',
        'homepage_inline' => 'Homepage Inline',
        'homepage_right'  => 'Homepage Right Sidebar',
        'article_top'     => 'Article Top',
        'article_inline'  => 'Article Inline',
        'article_sidebar' => 'Article Sidebar',
        'category_top'    => 'Category Top',
        'category_inline' => 'Category Inline',
        'category_sidebar'=> 'Category Sidebar',
        'search_top'      => 'Search Top',
        'search_inline'   => 'Search Inline',
        'search_sidebar'  => 'Search Sidebar',
        'tag_top'         => 'Tag Top',
        'tag_inline'      => 'Tag Inline',
        'tag_sidebar'     => 'Tag Sidebar',
    ];
}

// Admin auth helpers
function hs_is_admin_logged_in() {
    return !empty($_SESSION['hs_admin_id']);
}
function hs_require_admin() {
    if (!hs_is_admin_logged_in()) {
        header('Location: ' . hs_base_url('admin/login.php'));
        exit;
    }
}

// Frontend user helpers
function hs_current_user() {
    if (empty($_SESSION['hs_user_id'])) return null;
    $id = (int) $_SESSION['hs_user_id'];
    $res = mysqli_query(hs_db(), "SELECT id, name, email, is_premium, created_at FROM hs_frontend_users WHERE id = " . $id . " LIMIT 1");
    return $res ? mysqli_fetch_assoc($res) : null;
}
function hs_require_user() {
    if (!hs_current_user()) {
        header('Location: ' . hs_login_url());
        exit;
    }
}

function hs_footer_links_html()
{
    $links = [
        ['href' => hs_base_url('terms'), 'label' => 'Terms & Conditions'],
        ['href' => hs_base_url('privacy'), 'label' => 'Privacy Policy'],
        ['href' => hs_base_url('contact'), 'label' => 'Contact'],
    ];

    $parts = [];
    foreach ($links as $link) {
        $parts[] = '<a href="' . $link['href'] . '">' . htmlspecialchars($link['label']) . '</a>';
    }

    return implode(' · ', $parts);
}
