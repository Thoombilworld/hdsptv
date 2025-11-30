<?php
require __DIR__ . '/../../bootstrap.php';
hs_require_staff(['admin', 'editor']);
$db = hs_db();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $parent_id = (int)($_POST['parent_id'] ?? 0);
    if ($name === '') {
        $error = 'Name is required.';
    } else {
        if ($slug === '') {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-',$name));
        }
        $stmt = mysqli_prepare($db, "INSERT INTO hs_categories (name, slug, parent_id) VALUES (?,?,?)");
        mysqli_stmt_bind_param($stmt, 'ssi', $name, $slug, $parent_id);
        if (!mysqli_stmt_execute($stmt)) {
            $error = 'Error saving category: ' . mysqli_error($db);
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        mysqli_query($db, "DELETE FROM hs_categories WHERE id=".$id." LIMIT 1");
    }
    header('Location: ' . hs_base_url('admin/content/categories.php'));
    exit;
}

$res = mysqli_query($db, "SELECT * FROM hs_categories ORDER BY parent_id ASC, name ASC");
$categories = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
$byId = [];
foreach ($categories as $c) $byId[$c['id']] = $c;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Categories – NEWS HDSPTV</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
</head>
<body style="max-width:900px;margin:20px auto;padding:0 16px;">
  <h1>Categories (Parent + Sub)</h1>
  <?php if ($error): ?><div style="color:red;"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <h2>Add Category</h2>
  <form method="post">
    <label>Name</label><br>
    <input type="text" name="name" style="width:100%;" required><br><br>
    <label>Slug (optional)</label><br>
    <input type="text" name="slug" style="width:100%;"><br><br>
    <label>Parent Category</label><br>
    <select name="parent_id" style="width:100%;">
      <option value="0">-- None (Top level) --</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
      <?php endforeach; ?>
    </select><br><br>
    <button type="submit">Save Category</button>
  </form>

  <h2 style="margin-top:20px;">Existing Categories</h2>
  <table border="1" cellpadding="4" cellspacing="0" width="100%">
    <tr><th>ID</th><th>Name</th><th>Slug</th><th>Parent</th><th>Actions</th></tr>
    <?php foreach ($categories as $c): ?>
      <?php
        $parentName = $c['parent_id'] && isset($byId[$c['parent_id']]) ? $byId[$c['parent_id']]['name'] : '—';
      ?>
      <tr>
        <td><?= (int)$c['id'] ?></td>
        <td><?= htmlspecialchars($c['name']) ?></td>
        <td><?= htmlspecialchars($c['slug']) ?></td>
        <td><?= htmlspecialchars($parentName) ?></td>
        <td><a href="<?= hs_base_url('admin/content/categories.php?delete='.(int)$c['id']) ?>" onclick="return confirm('Delete this category?')">Delete</a></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
