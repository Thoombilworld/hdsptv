<?php
require __DIR__ . '/../bootstrap.php';
if (hs_current_staff()) {
    header('Location: ' . hs_base_url('admin/index.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = hs_authenticate_staff($email, $password);
    if ($user) {
        hs_set_staff_session($user);
        header('Location: ' . hs_base_url('admin/index.php'));
        exit;
    }
    $error = 'Invalid credentials or inactive account.';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Staff Login – NEWS HDSPTV</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
</head>
<body class="admin-shell" style="display:flex; align-items:center; justify-content:center; min-height:100vh;">
  <section class="admin-card" style="width:100%; max-width:420px; text-align:center; padding:26px 24px;">
    <div class="admin-pill" style="margin-bottom:10px;">Staff Access</div>
    <h1 style="margin:0 0 8px; font-size:20px;">Sign in to Admin</h1>
    <p class="admin-subtext" style="margin:0 0 14px;">Material-inspired white + blue experience for admins, editors, and reporters.</p>
    <?php if ($error): ?><div class="admin-message" style="background:rgba(239,68,68,0.1); border-color:rgba(239,68,68,0.4); color:#991B1B;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" class="admin-form" style="text-align:left;">
      <label>Email</label>
      <input type="email" name="email" required>
      <label>Password</label>
      <input type="password" name="password" required>
      <div class="form-actions" style="margin-top:14px;">
        <button type="submit" class="admin-button" style="width:100%; justify-content:center;">Login</button>
      </div>
    </form>
  </section>
</body>
</html>
