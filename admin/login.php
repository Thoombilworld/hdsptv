<?php
require __DIR__ . '/../bootstrap.php';
if (hs_is_staff_logged_in()) {
    header('Location: ' . hs_base_url('admin/index.php'));
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (defined('HS_INSTALLED') && HS_INSTALLED) {
        $stmt = mysqli_prepare(
            hs_db(),
            "SELECT id, password_hash, name, role, status FROM hs_users WHERE email = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user = $res ? mysqli_fetch_assoc($res) : null;
        if ($user && $user['status'] === 'active' && password_verify($password, $user['password_hash'])) {
            $_SESSION['hs_admin_id'] = $user['id'];
            $_SESSION['hs_admin_name'] = $user['name'];
            $_SESSION['hs_admin_role'] = $user['role'];
            header('Location: ' . hs_base_url('admin/index.php'));
            exit;
        } else {
            $error = 'Invalid login details or inactive account';
        }
    } else {
        $error = 'System not installed yet. Run the installer.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Login – NEWS HDSPTV</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
  <style>
    body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:radial-gradient(circle at top,#1E3A8A 0,#0B1120 40%,#020617 100%); }
    .card { background:#0B1120; color:#E5E7EB; padding:24px 26px; border-radius:14px; width:100%; max-width:360px; box-shadow:0 30px 80px rgba(15,23,42,0.8); }
    h1 { margin-top:0; font-size:22px; }
    label { font-size:13px; display:block; margin-bottom:4px; }
    input { width:100%; padding:9px 10px; border-radius:10px; border:1px solid #1F2937; background:#020617; color:#E5E7EB; margin-bottom:10px; font-size:13px; }
    button { width:100%; padding:9px 10px; border-radius:999px; border:none; background:#FACC15; color:#111827; font-weight:600; font-size:14px; cursor:pointer; }
    .error { background:#7F1D1D; color:#FECACA; padding:8px 10px; border-radius:8px; font-size:12px; margin-bottom:10px; }
  </style>
</head>
<body>
<div class="card">
  <h1>NEWS HDSPTV Admin</h1>
  <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <label for="email">Admin Email</label>
    <input type="email" name="email" id="email" required>
    <label for="password">Password</label>
    <input type="password" name="password" id="password" required>
    <button type="submit">Sign In</button>
  </form>
</div>
</body>
</html>
