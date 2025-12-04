<?php
require __DIR__ . '/../bootstrap.php';
hs_require_admin();
$settings = hs_settings();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meta_desc  = $_POST['seo_meta_description'] ?? '';
    $meta_keys  = $_POST['seo_meta_keywords'] ?? '';
    $og_home    = $_POST['homepage_og_image'] ?? '';
    $og_article = $_POST['default_article_og_image'] ?? '';
    $schema_on  = !empty($_POST['seo_schema_enabled']) ? '1' : '0';
    $author_def = $_POST['seo_default_author'] ?? '';

    $db = hs_db();
    $pairs = [
        'seo_meta_description'     => $meta_desc,
        'seo_meta_keywords'        => $meta_keys,
        'homepage_og_image'        => $og_home,
        'default_article_og_image' => $og_article,
        'seo_schema_enabled'       => $schema_on,
        'seo_default_author'       => $author_def,
    ];
    foreach ($pairs as $k => $v) {
        $stmt = mysqli_prepare($db, "INSERT INTO hs_settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
        mysqli_stmt_bind_param($stmt, 'ss', $k, $v);
        mysqli_stmt_execute($stmt);
    }
    $msg = 'SEO settings updated.';
    $settings = hs_settings(true); // reload
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>SEO Settings – NEWS HDSPTV</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
</head>
<body style="max-width:820px;margin:20px auto;padding:0 16px;">
  <h1>SEO Settings</h1>
  <?php if ($msg): ?><div style="color:green;"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <form method="post">
    <h2>Global Meta</h2>
    <label>Meta Description</label><br>
    <textarea name="seo_meta_description" style="width:100%;height:80px;"><?= htmlspecialchars($settings['seo_meta_description'] ?? '') ?></textarea><br><br>

    <label>Meta Keywords (comma separated)</label><br>
    <textarea name="seo_meta_keywords" style="width:100%;height:60px;"><?= htmlspecialchars($settings['seo_meta_keywords'] ?? '') ?></textarea><br><br>

    <h2>OpenGraph / Social</h2>
    <label>Homepage OG Image URL</label><br>
    <input type="text" name="homepage_og_image" style="width:100%;" value="<?= htmlspecialchars($settings['homepage_og_image'] ?? '') ?>"><br><br>

    <label>Default Article OG Image URL</label><br>
    <input type="text" name="default_article_og_image" style="width:100%;" value="<?= htmlspecialchars($settings['default_article_og_image'] ?? '') ?>"><br><br>

    <h2>Article Schema</h2>
    <label>
      <input type="checkbox" name="seo_schema_enabled" value="1" <?= !empty($settings['seo_schema_enabled']) && $settings['seo_schema_enabled'] === '1' ? 'checked' : '' ?>>
      Enable JSON-LD NewsArticle schema on article pages
    </label><br><br>

    <label>Default Article Author / Publisher Name</label><br>
    <input type="text" name="seo_default_author" style="width:100%;" value="<?= htmlspecialchars($settings['seo_default_author'] ?? 'NEWS HDSPTV') ?>"><br><br>

    <button type="submit">Save SEO Settings</button>
  </form>
</body>
</html>
