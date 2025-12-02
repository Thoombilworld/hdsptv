<?php
require __DIR__ . '/../bootstrap.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password_confirm'] ?? '';
    if ($name === '' || $email === '' || $pass === '') {
        $error = 'Please fill all required fields.';
    } elseif ($pass !== $pass2) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = mysqli_prepare(hs_db(), "INSERT INTO hs_frontend_users (name, email, password_hash) VALUES (?, ?, ?)");
        if (!$stmt) {
            $error = 'System error.';
        } else {
            mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $hash);
            if (!@mysqli_stmt_execute($stmt)) {
                $error = 'Email already in use.';
            } else {
                header('Location: ' . hs_base_url('auth/login.php'));
                exit;
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>User Registration – NEWS HDSPTV</title>
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
</head>
<body style="max-width:480px;margin:40px auto;padding:0 16px;">
  <h1>User Registration</h1>
  <?php if ($error): ?><div style="color:red;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <label>Name</label><br>
    <input type="text" name="name" style="width:100%;" required><br><br>
    <label>Email</label><br>
    <input type="email" name="email" style="width:100%;" required><br><br>
    <label>Password</label><br>
    <input type="password" name="password" style="width:100%;" required><br><br>
    <label>Confirm Password</label><br>
    <input type="password" name="password_confirm" style="width:100%;" required><br><br>
    <button type="submit">Register</button>
  </form>
</body>
</html>
