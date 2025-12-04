<?php
require __DIR__ . '/../../bootstrap.php';
hs_require_staff(['admin', 'editor']);
$db = hs_db();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-',$name));
        $stmt = mysqli_prepare($db, "INSERT INTO hs_tags (name, slug) VALUES (?,?) ON DUPLICATE KEY UPDATE name=VALUES(name)");
        mysqli_stmt_bind_param($stmt, 'ss', $name, $slug);
        if (!mysqli_stmt_execute($stmt)) {
            $error = 'Error saving tag: ' . mysqli_error($db);
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        mysqli_query($db, "DELETE FROM hs_post_tags WHERE tag_id=".$id);
        mysqli_query($db, "DELETE FROM hs_tags WHERE id=".$id." LIMIT 1");
    }
    header('Location: ' . hs_base_url('admin/content/tags.php'));
    exit;
}

$res = mysqli_query($db, "SHOW TABLES LIKE 'hs_tags'");
$has_tags = $res && mysqli_num_rows($res) > 0;
$tags = [];
if ($has_tags) {
    $tr = mysqli_query($db, "SELECT * FROM hs_tags ORDER BY name ASC");
    if ($tr) { $tags = mysqli_fetch_all($tr, MYSQLI_ASSOC); }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Tags – NEWS HDSPTV</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
</head>
<body style="max-width:800px;margin:20px auto;padding:0 16px;">
  <h1>Tags</h1>
  <?php if ($error): ?><div style="color:red;"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <h2>Add Tag</h2>
  <form method="post">
    <label>Name</label><br>
    <input type="text" name="name" style="width:100%;" required><br><br>
    <button type="submit">Save Tag</button>
  </form>

  <h2 style="margin-top:20px;">Existing Tags</h2>
  <?php if (!$has_tags): ?>
    <p><strong>Note:</strong> Table <code>hs_tags</code> not found. Make sure you ran the latest installer SQL.</p>
  <?php else: ?>
    <table border="1" cellpadding="4" cellspacing="0" width="100%">
      <tr><th>ID</th><th>Name</th><th>Slug</th><th>Actions</th></tr>
      <?php foreach ($tags as $t): ?>
        <tr>
          <td><?= (int)$t['id'] ?></td>
          <td><?= htmlspecialchars($t['name']) ?></td>
          <td><?= htmlspecialchars($t['slug']) ?></td>
          <td><a href="<?= hs_base_url('admin/content/tags.php?delete='.(int)$t['id']) ?>" onclick="return confirm('Delete this tag?')">Delete</a></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</body>
</html>
