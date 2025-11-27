<?php
require __DIR__ . '/../bootstrap.php';
$token = $_GET['token'] ?? '';
$error = '';
$msg   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password_confirm'] ?? '';
    if ($pass === '' || $pass2 === '' || $pass !== $pass2) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = mysqli_prepare(hs_db(), "SELECT email FROM hs_password_resets WHERE token = ? AND created_at >= (NOW() - INTERVAL 1 DAY) LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $token);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        if ($row) {
            $email = $row['email'];
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $up = mysqli_prepare(hs_db(), "UPDATE hs_frontend_users SET password_hash = ? WHERE email = ? LIMIT 1");
            mysqli_stmt_bind_param($up, 'ss', $hash, $email);
            mysqli_stmt_execute($up);
            $msg = 'Password updated. You can now login.';
        } else {
            $error = 'Invalid or expired token.';
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Reset Password – NEWS HDSPTV</title>
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
</head>
<body style="max-width:480px;margin:40px auto;padding:0 16px;">
  <h1>Reset Password</h1>
  <?php if ($error): ?><div style="color:red;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($msg): ?><div><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    <label>New Password</label><br>
    <input type="password" name="password" style="width:100%;" required><br><br>
    <label>Confirm Password</label><br>
    <input type="password" name="password_confirm" style="width:100%;" required><br><br>
    <button type="submit">Update Password</button>
  </form>
</body>
</html>
