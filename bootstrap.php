<?php
session_start();
require __DIR__ . '/config/config.php';

if (!defined('HS_INSTALLED') || !HS_INSTALLED) {
    http_response_code(503);
    echo "<h2 style=\"font-family:system-ui,sans-serif; text-align:center; margin-top:32px;\">Application not installed</h2>";
    echo "<p style=\"font-family:system-ui,sans-serif; text-align:center;\">Please create .env.php via the installer before accessing admin pages.</p>";
    exit;
}

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

function hs_is_rtl($code = null)
{
    $rtl_codes = ['ar', 'fa', 'ur', 'he'];
    $code = $code ?: hs_current_language_code();
    return in_array(strtolower($code), $rtl_codes, true);
}

function hs_translation_path($code)
{
    return __DIR__ . '/lang/' . strtolower($code) . '.php';
}

function hs_translations($force_reload = false)
{
    static $cache = [];
    $code = hs_current_language_code();

    if (!$force_reload && isset($cache[$code])) {
        return $cache[$code];
    }

    $translations = [];
    $fallbackPath = hs_translation_path('en');
    if (is_readable($fallbackPath)) {
        $base = include $fallbackPath;
        if (is_array($base)) {
            $translations = $base;
        }
    }

    $localePath = hs_translation_path($code);
    if ($code !== 'en' && is_readable($localePath)) {
        $locale = include $localePath;
        if (is_array($locale)) {
            $translations = array_merge($translations, $locale);
        }
    }

    $cache[$code] = $translations;
    return $translations;
}

function hs_t($key, $default = '', array $replacements = [])
{
    $translations = hs_translations();
    $text = $translations[$key] ?? $default;

    foreach ($replacements as $search => $value) {
        $text = str_replace('{' . $search . '}', $value, $text);
    }

    return $text !== '' ? $text : $default;
}

function hs_current_language_code()
{
    $supported = hs_supported_languages();
    $settings = hs_settings();

    $requested = strtolower(trim($_GET['lang'] ?? ''));
    if ($requested !== '' && isset($supported[$requested])) {
        $_SESSION['hs_lang'] = $requested;
    }

    if (!isset($_SESSION['hs_lang']) && !empty($settings['default_language'])) {
        $candidate = strtolower(trim($settings['default_language']));
        if (isset($supported[$candidate])) {
            $_SESSION['hs_lang'] = $candidate;
        }
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
        'favicon'    => hs_base_url('assets/images/favicon.png'),
        'default_language' => 'en',
        // Homepage layout defaults
        'hp_show_breaking' => '1',
        'hp_show_featured' => '1',
        'hp_show_trending' => '1',
        'hp_show_video' => '1',
        'hp_show_gallery' => '1',
        'hp_show_ads_top' => '1',
        'hp_show_ads_inline' => '1',
        'hp_show_ads_sidebar' => '1',
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

function hs_home_layout(array $settings)
{
    $flag = function ($key, $default = true) use ($settings) {
        if (!array_key_exists($key, $settings)) {
            return $default;
        }
        return $settings[$key] === '1';
    };

    return [
        'breaking'   => $flag('hp_show_breaking'),
        'featured'   => $flag('hp_show_featured'),
        'trending'   => $flag('hp_show_trending'),
        'video'      => $flag('hp_show_video'),
        'gallery'    => $flag('hp_show_gallery'),
        'ads_top'    => $flag('hp_show_ads_top'),
        'ads_inline' => $flag('hp_show_ads_inline'),
        'ads_sidebar'=> $flag('hp_show_ads_sidebar'),
    ];
}

function hs_ad_slots()
{
    return [
        'global_top'       => 'Sitewide Top Banner',
        'global_sidebar'   => 'Sitewide Sidebar',
        'global_footer'    => 'Sitewide Footer',
        'homepage_top'     => 'Homepage Top',
        'homepage_right'   => 'Homepage Right Sidebar',
        'homepage_inline'  => 'Homepage Inline',
        'article_top'      => 'Article Top',
        'article_inline'   => 'Article Inline',
        'article_sidebar'  => 'Article Sidebar',
        'category_top'     => 'Category Top',
        'category_inline'  => 'Category Inline',
        'search_inline'    => 'Search Inline',
        'tag_inline'       => 'Tag Inline',
    ];
}

function hs_active_ads(array $slots = [])
{
    static $cached = null;

    if ($cached === null) {
        $cached = [];

        if (defined('HS_INSTALLED') && HS_INSTALLED) {
            $res = mysqli_query(hs_db(), "SELECT * FROM hs_ads WHERE active = 1");
            if ($res) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $cached[$row['slot']] = $row;
                }
            }
        }
    }

    if (!empty($slots)) {
        return array_intersect_key($cached, array_flip($slots));
    }

    return $cached;
}

// Analytics helpers
function hs_client_ip()
{
    $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CF_CONNECTING_IP', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        $raw = $_SERVER[$key] ?? '';
        if ($raw === '') continue;
        $ip = trim(explode(',', $raw)[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    return '';
}

function hs_is_bot($ua)
{
    return (bool) preg_match('/bot|crawl|spider|slurp|mediapartners|bingpreview/i', $ua);
}

function hs_detect_device_type($ua)
{
    if (stripos($ua, 'tablet') !== false || stripos($ua, 'ipad') !== false) {
        return 'Tablet';
    }
    if (stripos($ua, 'mobile') !== false || stripos($ua, 'iphone') !== false || stripos($ua, 'android') !== false) {
        return 'Mobile';
    }
    return 'Desktop';
}

function hs_detect_browser($ua)
{
    if (stripos($ua, 'edg') !== false) return 'Edge';
    if (stripos($ua, 'opr') !== false || stripos($ua, 'opera') !== false) return 'Opera';
    if (stripos($ua, 'chrome') !== false) return 'Chrome';
    if (stripos($ua, 'safari') !== false) return 'Safari';
    if (stripos($ua, 'firefox') !== false) return 'Firefox';
    if (stripos($ua, 'msie') !== false || stripos($ua, 'trident') !== false) return 'IE';
    return 'Other';
}

function hs_detect_country()
{
    $country = strtoupper(trim($_SERVER['HTTP_CF_IPCOUNTRY'] ?? ''));
    if ($country !== '') {
        return $country;
    }
    return 'Unknown';
}

function hs_track_event(array $event = [])
{
    if (!defined('HS_INSTALLED') || !HS_INSTALLED) return;

    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250);
    if (hs_is_bot($ua)) return;

    $db = hs_db();
    $esc = fn($v) => mysqli_real_escape_string($db, $v);

    $type = $event['type'] ?? 'pageview';
    $post_id = (int)($event['post_id'] ?? 0);
    $category_id = (int)($event['category_id'] ?? 0);
    $reporter_id = (int)($event['reporter_id'] ?? 0);
    $editor_id = (int)($event['editor_id'] ?? 0);
    $country = $event['country'] ?? hs_detect_country();
    $device = $event['device'] ?? hs_detect_device_type($ua);
    $browser = $event['browser'] ?? hs_detect_browser($ua);
    $ip = hs_client_ip();
    $visitor_hash = $ip !== '' ? substr(sha1($ip), 0, 40) : null;

    $sql = "INSERT INTO hs_analytics_events (event_type, post_id, category_id, reporter_id, editor_id, visitor_hash, country, device, browser, user_agent) VALUES ("
         . "'{$esc($type)}',"
         . ($post_id > 0 ? (int)$post_id : 'NULL') . ','
         . ($category_id > 0 ? (int)$category_id : 'NULL') . ','
         . ($reporter_id > 0 ? (int)$reporter_id : 'NULL') . ','
         . ($editor_id > 0 ? (int)$editor_id : 'NULL') . ','
         . ($visitor_hash ? "'{$esc($visitor_hash)}'" : 'NULL') . ','
         . "'{$esc($country)}','{$esc($device)}','{$esc($browser)}','{$esc($ua)}'" . ')';

    @mysqli_query($db, $sql);
}

// Admin / staff auth helpers
function hs_current_staff()
{
    static $staff = null;

    if ($staff !== null) {
        return $staff;
    }

    if (empty($_SESSION['hs_admin_id'])) {
        return null;
    }

    $id = (int) $_SESSION['hs_admin_id'];
    $res = mysqli_query(
        hs_db(),
        "SELECT id, name, email, role, status FROM hs_users WHERE id = " . $id . " LIMIT 1"
    );
    $row = $res ? mysqli_fetch_assoc($res) : null;

    if (!$row || $row['status'] !== 'active') {
        return null;
    }

    $staff = $row;
    $_SESSION['hs_admin_role'] = $row['role'];
    $_SESSION['hs_admin_name'] = $row['name'];

    return $staff;
}

function hs_staff_role()
{
    $staff = hs_current_staff();
    return $staff['role'] ?? null;
}

function hs_is_staff_logged_in()
{
    return (bool) hs_current_staff();
}

function hs_is_admin_logged_in()
{
    return hs_staff_role() === 'admin';
}

function hs_require_staff(array $roles = ['admin'])
{
    $staff = hs_current_staff();
    $role = $staff['role'] ?? null;

    if (!$staff) {
        header('Location: ' . hs_base_url('admin/login.php'));
        exit;
    }

    if (!in_array($role, $roles, true)) {
        http_response_code(403);
        echo '<h1 style="font-family:system-ui, sans-serif; text-align:center; padding:40px;">Access denied for this role.</h1>';
        exit;
    }
}

function hs_require_admin()
{
    hs_require_staff(['admin']);
}

function hs_admin_nav_links($role, $active = '')
{
    $links = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => hs_base_url('admin/index.php'), 'roles' => ['admin', 'editor', 'reporter']],
        ['key' => 'content',   'label' => 'Content',   'href' => hs_base_url('admin/content/index.php'), 'roles' => ['admin', 'editor', 'reporter']],
        ['key' => 'homepage',  'label' => 'Homepage',  'href' => hs_base_url('admin/homepage.php'), 'roles' => ['admin', 'editor']],
        ['key' => 'settings',  'label' => 'Site Settings', 'href' => hs_base_url('admin/settings.php'), 'roles' => ['admin']],
        ['key' => 'legal',     'label' => 'Legal',     'href' => hs_base_url('admin/legal.php'), 'roles' => ['admin']],
        ['key' => 'seo',       'label' => 'SEO',       'href' => hs_base_url('admin/seo.php'), 'roles' => ['admin']],
        ['key' => 'social',    'label' => 'Social',    'href' => hs_base_url('admin/social.php'), 'roles' => ['admin']],
        ['key' => 'ads',       'label' => 'Ads',       'href' => hs_base_url('admin/ads.php'), 'roles' => ['admin']],
        ['key' => 'analytics', 'label' => 'Analytics', 'href' => hs_base_url('admin/analytics.php'), 'roles' => ['admin', 'editor']],
        ['key' => 'users',     'label' => 'Staff',     'href' => hs_base_url('admin/users.php'), 'roles' => ['admin']],
        ['key' => 'logs',      'label' => 'Logs',      'href' => hs_base_url('admin/logs.php'), 'roles' => ['admin']],
        ['key' => 'system',    'label' => 'System Health', 'href' => hs_base_url('admin/system.php'), 'roles' => ['admin']],
        ['key' => 'logout',    'label' => 'Logout',    'href' => hs_base_url('admin/logout.php'), 'roles' => ['admin', 'editor', 'reporter'], 'highlight' => true],
    ];

    $role = $role ?: 'admin';

    return array_values(array_map(function ($link) use ($active) {
        $link['active'] = $link['key'] === $active;
        return $link;
    }, array_filter($links, function ($link) use ($role) {
        return in_array($role, $link['roles'], true);
    })));
}

function hs_render_admin_nav($role, $active = '')
{
    foreach (hs_admin_nav_links($role, $active) as $link) {
        $classes = [];
        if (!empty($link['active'])) {
            $classes[] = 'active';
        }
        if (!empty($link['highlight'])) {
            $classes[] = 'highlight';
        }

        echo '<a href="' . $link['href'] . '"' . ($classes ? ' class="' . implode(' ', $classes) . '"' : '') . '>' . htmlspecialchars($link['label']) . '</a>';
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

function hs_table_exists($table, $db = null)
{
    $db = $db ?: hs_db();
    if (!$db) return false;

    $table = mysqli_real_escape_string($db, $table);
    $res = mysqli_query($db, "SHOW TABLES LIKE '" . $table . "'");
    return $res && mysqli_num_rows($res) > 0;
}

function hs_table_has_columns($table, array $columns, $db = null)
{
    $db = $db ?: hs_db();
    if (!$db) return false;

    $escaped = "'" . implode("','", array_map(function ($col) use ($db) {
        return mysqli_real_escape_string($db, $col);
    }, $columns)) . "'";

    $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . mysqli_real_escape_string($db, $table) . "' AND COLUMN_NAME IN ($escaped)";
    $res = mysqli_query($db, $sql);
    if (!$res) return false;
    $row = mysqli_fetch_row($res);
    return $row && (int)$row[0] === count($columns);
}

function hs_system_checks()
{
    $checks = [];
    $db = hs_db();
    global $HS_DB_NAME, $HS_DB_HOST;

    $checks[] = [
        'label' => 'Installer configuration',
        'status' => HS_INSTALLED ? 'ok' : 'fail',
        'detail' => HS_INSTALLED ? '.env.php loaded from installer.' : 'Installer has not generated .env.php yet.',
    ];

    if ($db) {
        $checks[] = [
            'label' => 'Database connection',
            'status' => 'ok',
            'detail' => 'Connected to ' . htmlspecialchars($HS_DB_HOST ?? 'localhost') . ' / ' . htmlspecialchars($HS_DB_NAME ?? 'news_hdsptv'),
        ];
    } else {
        $checks[] = [
            'label' => 'Database connection',
            'status' => 'fail',
            'detail' => 'Database connection unavailable. Confirm HS_DB_* values in .env.php.',
        ];
    }

    if ($db) {
        $coreTables = ['hs_settings', 'hs_categories', 'hs_posts', 'hs_tags', 'hs_users', 'hs_ads'];
        $missing = [];
        foreach ($coreTables as $table) {
            if (!hs_table_exists($table, $db)) {
                $missing[] = $table;
            }
        }
        $checks[] = [
            'label' => 'Core tables',
            'status' => empty($missing) ? 'ok' : 'fail',
            'detail' => empty($missing) ? 'All core tables are available.' : ('Missing: ' . implode(', ', $missing)),
        ];

        if (hs_table_exists('hs_analytics_events', $db)) {
            $needsMigration = !hs_table_has_columns('hs_analytics_events', ['event_type', 'post_id', 'category_id', 'reporter_id', 'editor_id', 'visitor_hash', 'country', 'device', 'browser', 'user_agent', 'created_at'], $db);
            $checks[] = [
                'label' => 'Analytics schema',
                'status' => $needsMigration ? 'warn' : 'ok',
                'detail' => $needsMigration ? 'Analytics table found but missing columns; run latest installer SQL.' : 'Analytics tracking columns detected.',
            ];
        } else {
            $checks[] = [
                'label' => 'Analytics schema',
                'status' => 'warn',
                'detail' => 'Analytics table not found; install or migrate hs_analytics_events for tracking.',
            ];
        }
    }

    $requiredDirs = [
        __DIR__ . '/writable' => 'Writable directory',
        __DIR__ . '/writable/logs' => 'Logs directory',
        __DIR__ . '/writable/uploads' => 'Uploads directory',
    ];

    foreach ($requiredDirs as $dir => $label) {
        $exists = is_dir($dir);
        $writable = $exists && is_writable($dir);
        $checks[] = [
            'label' => $label,
            'status' => ($exists && $writable) ? 'ok' : 'fail',
            'detail' => $exists ? ($writable ? 'Ready' : 'Directory is not writable: ' . $dir) : 'Directory missing: ' . $dir,
        ];
    }

    $requiredFiles = [
        __DIR__ . '/config/config.php' => 'config/config.php',
        __DIR__ . '/bootstrap.php' => 'bootstrap.php',
        __DIR__ . '/app/Views/frontend/home.php' => 'app/Views/frontend/home.php',
        __DIR__ . '/assets/css/style.css' => 'assets/css/style.css',
        __DIR__ . '/install/install.sql' => 'install/install.sql',
        __DIR__ . '/lang/en.php' => 'lang/en.php',
        __DIR__ . '/lang/ar.php' => 'lang/ar.php',
        __DIR__ . '/lang/ml.php' => 'lang/ml.php',
    ];

    $missingFiles = [];
    foreach ($requiredFiles as $path => $label) {
        if (!file_exists($path)) {
            $missingFiles[] = $label;
        }
    }

    $checks[] = [
        'label' => 'Required files',
        'status' => empty($missingFiles) ? 'ok' : 'fail',
        'detail' => empty($missingFiles) ? 'Key application files present.' : ('Missing: ' . implode(', ', $missingFiles)),
    ];

    $extensions = ['mysqli', 'json', 'mbstring'];
    $missingExtensions = array_filter($extensions, function ($ext) {
        return !extension_loaded($ext);
    });
    $checks[] = [
        'label' => 'PHP extensions',
        'status' => empty($missingExtensions) ? 'ok' : 'fail',
        'detail' => empty($missingExtensions) ? 'mysqli, json, mbstring loaded.' : ('Missing: ' . implode(', ', $missingExtensions)),
    ];

    $htaccessPath = __DIR__ . '/.htaccess';
    $checks[] = [
        'label' => 'Clean URLs (.htaccess)',
        'status' => file_exists($htaccessPath) ? 'ok' : 'warn',
        'detail' => file_exists($htaccessPath) ? '.htaccess present for rewrite rules.' : '.htaccess not found; clean URLs may fail.',
    ];

    $checks[] = [
        'label' => 'PHP version',
        'status' => version_compare(PHP_VERSION, '7.4', '>=') ? 'ok' : 'warn',
        'detail' => 'Running PHP ' . PHP_VERSION . ' (7.4+ recommended).',
    ];

    return $checks;
}
