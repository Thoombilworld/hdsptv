<?php
require __DIR__ . '/../../bootstrap.php';
hs_require_admin();
$db = hs_db();

$res = mysqli_query($db, "SELECT p.id, p.title, p.type, p.status, p.is_featured, p.is_breaking, p.is_trending, p.region, c.name AS category_name, p.created_at
                          FROM hs_posts p
                          LEFT JOIN hs_categories c ON c.id = p.category_id
                          ORDER BY p.created_at DESC");
$posts = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
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
  <p><a href="<?= hs_base_url('admin/content/article_add.php') ?>">+ Add New Article</a></p>
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
    <?php foreach ($posts as $p): ?>
      <tr>
        <td><?= (int)$p['id'] ?></td>
        <td><?= htmlspecialchars($p['title']) ?></td>
        <td><?= htmlspecialchars($p['category_name'] ?: 'News') ?></td>
        <td><?= htmlspecialchars($p['type']) ?></td>
        <td><?= htmlspecialchars($p['region']) ?></td>
        <td><?= htmlspecialchars($p['status']) ?></td>
        <td>
          <?php if ($p['is_breaking']): ?>B<?php endif; ?>
          <?php if ($p['is_featured']): ?> F<?php endif; ?>
          <?php if ($p['is_trending']): ?> T<?php endif; ?>
        </td>
        <td><?= htmlspecialchars($p['created_at']) ?></td>
        <td>
          <a href="<?= hs_base_url('admin/content/article_edit.php?id='.(int)$p['id']) ?>">Edit</a> |
          <a href="<?= hs_base_url('admin/content/article_delete.php?id='.(int)$p['id']) ?>" onclick="return confirm('Delete this article?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
