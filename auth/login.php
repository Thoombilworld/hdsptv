<?php
require __DIR__ . '/../bootstrap.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $stmt = mysqli_prepare(hs_db(), "SELECT id, name, password_hash, is_premium FROM hs_frontend_users WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $user = $res ? mysqli_fetch_assoc($res) : null;
    if ($user && password_verify($pass, $user['password_hash'])) {
        $_SESSION['hs_user_id'] = $user['id'];
        header('Location: ' . hs_base_url('/'));
        exit;
    } else {
        $error = 'Invalid login details.';
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>User Login – NEWS HDSPTV</title>
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
</head>
<body style="max-width:480px;margin:40px auto;padding:0 16px;">
  <h1>User Login</h1>
  <?php if ($error): ?><div style="color:red;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <label>Email</label><br>
    <input type="email" name="email" style="width:100%;" required><br><br>
    <label>Password</label><br>
    <input type="password" name="password" style="width:100%;" required><br><br>
    <button type="submit">Login</button>
  </form>
  <p><a href="<?= hs_base_url('auth/forgot.php') ?>">Forgot password?</a></p>
</body>
</html>
