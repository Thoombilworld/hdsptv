<?php
require __DIR__ . '/../bootstrap.php';
hs_require_admin();
$db = hs_db();
$res = mysqli_query($db, "SELECT id, name, email, role, created_at FROM hs_users ORDER BY created_at DESC");
$staff = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Staff Users – NEWS HDSPTV</title>
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
</head>
<body style="max-width:900px;margin:20px auto;padding:0 16px;">
  <h1>Staff Users (Admin / Editor / Reporter)</h1>
  <p>This is a simple listing view. Extend with create/edit forms as needed.</p>
  <table border="1" cellpadding="4" cellspacing="0">
    <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Created</th></tr>
    <?php foreach ($staff as $u): ?>
      <tr>
        <td><?= (int)$u['id'] ?></td>
        <td><?= htmlspecialchars($u['name']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['role']) ?></td>
        <td><?= htmlspecialchars($u['created_at']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
