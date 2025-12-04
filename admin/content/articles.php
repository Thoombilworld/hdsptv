<?php
require __DIR__ . '/../../bootstrap.php';
hs_require_staff(['admin', 'editor', 'reporter']);
$db = hs_db();
$staff = hs_current_staff();
$role = $staff['role'] ?? 'admin';

$typeFilter = $_GET['type'] ?? 'all';
$statusFilter = $_GET['status'] ?? 'all';
$regionFilter = $_GET['region'] ?? 'all';

$conditions = [];
if (in_array($typeFilter, ['article', 'video', 'gallery'], true)) {
    $conditions[] = "p.type='" . mysqli_real_escape_string($db, $typeFilter) . "'";
}
if (in_array($statusFilter, ['draft', 'published'], true)) {
    $conditions[] = "p.status='" . mysqli_real_escape_string($db, $statusFilter) . "'";
}
if (in_array($regionFilter, ['global', 'india', 'gcc', 'kerala', 'world', 'sports'], true)) {
    $conditions[] = "p.region='" . mysqli_real_escape_string($db, $regionFilter) . "'";
}

$sql = "SELECT p.id, p.title, p.type, p.status, p.is_featured, p.is_breaking, p.is_trending, p.region, c.name AS category_name, p.created_at
        FROM hs_posts p
        LEFT JOIN hs_categories c ON c.id = p.category_id";
if ($conditions) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY p.created_at DESC';

$res = mysqli_query($db, $sql);
$posts = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];

$typeLabel = function ($type) {
    if ($type === 'article') return 'Standard';
    if ($type === 'video') return 'Video';
    if ($type === 'gallery') return 'Gallery';
    return $type;
};

$regionOptions = ['global' => 'Global', 'india' => 'India', 'gcc' => 'GCC', 'kerala' => 'Kerala', 'world' => 'World', 'sports' => 'Sports'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Articles – NEWS HDSPTV</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
</head>
<body style="max-width:1200px;margin:20px auto;padding:0 16px;">
  <h1>Articles</h1>
  <p>Signed in as <strong><?= htmlspecialchars($role) ?></strong>. <?php if ($role === 'reporter'): ?>You can add and edit stories; deletion stays restricted to editors and admins.<?php else: ?>You can add, edit, and remove stories.<?php endif; ?></p>
  <p style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
    <a href="<?= hs_base_url('admin/content/article_add.php') ?>">+ Add New Article</a>
    <span style="font-size:12px;color:#555;">Filter by status, type, or region to quickly audit published work.</span>
  </p>

  <form method="get" style="margin-bottom:14px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
    <label style="font-size:13px;">Status<br>
      <select name="status">
        <option value="all" <?= $statusFilter==='all'?'selected':'' ?>>All</option>
        <option value="published" <?= $statusFilter==='published'?'selected':'' ?>>Published</option>
        <option value="draft" <?= $statusFilter==='draft'?'selected':'' ?>>Draft</option>
      </select>
    </label>
    <label style="font-size:13px;">Type<br>
      <select name="type">
        <option value="all" <?= $typeFilter==='all'?'selected':'' ?>>All</option>
        <option value="article" <?= $typeFilter==='article'?'selected':'' ?>>Standard</option>
        <option value="video" <?= $typeFilter==='video'?'selected':'' ?>>Video</option>
        <option value="gallery" <?= $typeFilter==='gallery'?'selected':'' ?>>Gallery</option>
      </select>
    </label>
    <label style="font-size:13px;">Region<br>
      <select name="region">
        <option value="all" <?= $regionFilter==='all'?'selected':'' ?>>All</option>
        <?php foreach ($regionOptions as $key => $label): ?>
          <option value="<?= htmlspecialchars($key) ?>" <?= $regionFilter===$key?'selected':'' ?>><?= htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button type="submit" style="height:32px;">Apply</button>
    <?php if ($conditions): ?>
      <a href="<?= hs_base_url('admin/content/articles.php') ?>" style="font-size:12px;">Clear filters</a>
    <?php endif; ?>
  </form>

  <table border="1" cellpadding="4" cellspacing="0" width="100%">
    <tr>
      <th>ID</th>
      <th>Title</th>
      <th>Category</th>
      <th>Type</th>
      <th>Region</th>
      <th>Status</th>
      <th>Flags</th>
      <th>Created</th>
      <th>Actions</th>
    </tr>
    <?php if (!$posts): ?>
      <tr><td colspan="9" style="text-align:center;">No articles match the current filters.</td></tr>
    <?php endif; ?>
    <?php foreach ($posts as $p): ?>
      <tr>
        <td><?= (int)$p['id'] ?></td>
        <td><?= htmlspecialchars($p['title']) ?></td>
        <td><?= htmlspecialchars($p['category_name'] ?: 'News') ?></td>
        <td><?= htmlspecialchars($typeLabel($p['type'])) ?></td>
        <td><?= htmlspecialchars($regionOptions[$p['region']] ?? $p['region']) ?></td>
        <td><?= htmlspecialchars(ucfirst($p['status'])) ?></td>
        <td>
          <?php if ($p['is_breaking']): ?>B<?php endif; ?>
          <?php if ($p['is_featured']): ?> F<?php endif; ?>
          <?php if ($p['is_trending']): ?> T<?php endif; ?>
        </td>
        <td><?= htmlspecialchars($p['created_at']) ?></td>
        <td>
          <a href="<?= hs_base_url('admin/content/article_edit.php?id='.(int)$p['id']) ?>">Edit</a>
          <?php if (in_array($role, ['admin', 'editor'])): ?> |
            <a href="<?= hs_base_url('admin/content/article_delete.php?id='.(int)$p['id']) ?>" onclick="return confirm('Delete this article?')">Delete</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
