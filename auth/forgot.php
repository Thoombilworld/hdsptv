<?php
require __DIR__ . '/../bootstrap.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email !== '') {
        $token = bin2hex(random_bytes(16));
        $stmt = mysqli_prepare(hs_db(), "INSERT INTO hs_password_resets (email, token) VALUES (?, ?) ON DUPLICATE KEY UPDATE token = VALUES(token), created_at = CURRENT_TIMESTAMP");
        mysqli_stmt_bind_param($stmt, 'ss', $email, $token);
        mysqli_stmt_execute($stmt);
        $msg = 'If the email exists, a reset token was generated. (SMTP integration needed to send link.)';
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Forgot Password – NEWS HDSPTV</title>
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
</head>
<body style="max-width:480px;margin:40px auto;padding:0 16px;">
  <h1>Forgot Password</h1>
  <?php if ($msg): ?><div><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <form method="post">
    <label>Email</label><br>
    <input type="email" name="email" style="width:100%;" required><br><br>
    <button type="submit">Generate Reset Token</button>
  </form>
</body>
</html>
