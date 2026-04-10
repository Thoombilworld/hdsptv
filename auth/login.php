<?php
require __DIR__ . '/../bootstrap.php';

if (empty($_SESSION['hs_login_csrf'])) {
    $_SESSION['hs_login_csrf'] = bin2hex(random_bytes(16));
}

$error = '';
$email = trim($_POST['email'] ?? '');
$remember = !empty($_POST['remember_me']);

$attemptWindowSeconds = 300; // 5 minutes
$maxAttempts = 5;
$_SESSION['hs_login_attempts'] = $_SESSION['hs_login_attempts'] ?? 0;
$_SESSION['hs_login_attempt_time'] = $_SESSION['hs_login_attempt_time'] ?? 0;

if ((time() - (int)$_SESSION['hs_login_attempt_time']) > $attemptWindowSeconds) {
    $_SESSION['hs_login_attempts'] = 0;
}

if (!empty($_GET['expired'])) {
    $error = 'Your session has expired. Please sign in again.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['hs_login_csrf'], $token)) {
        $error = 'We’re having trouble signing you in right now. Please try again in a moment.';
    } elseif ($_SESSION['hs_login_attempts'] >= $maxAttempts) {
        $error = 'Too many login attempts. Please wait a few minutes and try again.';
    } elseif ($email === '' || ($_POST['password'] ?? '') === '') {
        $error = 'Please enter your email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $pass = (string)($_POST['password'] ?? '');
        $db = hs_db();

        if (!$db) {
            $error = 'We’re having trouble signing you in right now. Please try again in a moment.';
        } else {
            $stmt = @mysqli_prepare($db, "SELECT id, name, password_hash, is_premium, is_active FROM hs_frontend_users WHERE email = ? LIMIT 1");
            if (!$stmt) {
                // Backward-compatible fallback when is_active does not exist.
                $stmt = @mysqli_prepare($db, "SELECT id, name, password_hash, is_premium, 1 AS is_active FROM hs_frontend_users WHERE email = ? LIMIT 1");
            }

            if (!$stmt) {
                $error = 'We’re having trouble signing you in right now. Please try again in a moment.';
            } else {
                mysqli_stmt_bind_param($stmt, 's', $email);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                $user = $res ? mysqli_fetch_assoc($res) : null;

                if (!$user || !password_verify($pass, $user['password_hash'] ?? '')) {
                    $_SESSION['hs_login_attempts'] = (int)$_SESSION['hs_login_attempts'] + 1;
                    $_SESSION['hs_login_attempt_time'] = time();
                    $error = 'Invalid email or password. Please try again.';
                } elseif (isset($user['is_active']) && (int)$user['is_active'] === 0) {
                    $error = 'Your account has been disabled. Please contact support.';
                } else {
                    $_SESSION['hs_user_id'] = (int)$user['id'];
                    $_SESSION['hs_login_attempts'] = 0;
                    $_SESSION['hs_login_attempt_time'] = time();

                    if ($remember) {
                        $cookieValue = hash('sha256', (string)$user['id'] . '|' . session_id() . '|' . HS_BASE_URL);
                        setcookie('hs_user_remember', $cookieValue, time() + (86400 * 30), '/', '', false, true);
                    }

                    header('Location: ' . hs_dashboard_url());
                    exit;
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in to HDSPTV</title>
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
  <style>
    body { margin:0; font-family:system-ui,-apple-system,'Segoe UI',sans-serif; background:#F6F7FB; color:#111; }
    .login-wrap { min-height:100vh; display:grid; place-items:center; padding:24px 14px; }
    .login-card { width:min(460px,100%); background:#fff; border:1px solid #E5E7EB; border-radius:16px; padding:20px; box-shadow:0 20px 45px rgba(17,17,17,.08); }
    .brand { display:flex; align-items:center; gap:10px; margin-bottom:12px; }
    .brand-dot { width:34px; height:34px; border-radius:10px; background:linear-gradient(135deg,#D60000,#8B0000); color:#fff; display:grid; place-items:center; font-weight:800; }
    h1 { margin:0 0 6px; font-size:24px; }
    .subtitle { margin:0 0 14px; color:#6B7280; font-size:13px; line-height:1.4; }
    .error { background:rgba(214,0,0,.09); border:1px solid rgba(214,0,0,.25); color:#B91C1C; border-radius:10px; padding:10px; font-size:13px; margin:0 0 12px; }
    label { display:block; margin:0 0 6px; font-size:13px; font-weight:600; }
    input[type="email"], input[type="password"] { width:100%; box-sizing:border-box; border:1px solid #E5E7EB; border-radius:10px; padding:11px 12px; font-size:14px; margin:0 0 12px; }
    input:focus { outline:2px solid rgba(214,0,0,.2); border-color:#D60000; }
    .form-foot { display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:14px; font-size:13px; }
    .remember { display:flex; align-items:center; gap:7px; }
    .btn { width:100%; padding:11px 12px; border:0; border-radius:10px; background:#D60000; color:#fff; font-weight:700; cursor:pointer; }
    .btn:hover { filter:brightness(.98); }
    .links { margin-top:12px; text-align:center; font-size:13px; color:#6B7280; }
    .links a { color:#D60000; text-decoration:none; font-weight:600; }
    .links a:hover { text-decoration:underline; }
  </style>
</head>
<body>
  <main class="login-wrap">
    <section class="login-card">
      <div class="brand">
        <div class="brand-dot">H</div>
        <strong>HDSPTV</strong>
      </div>

      <h1>Sign in to HDSPTV</h1>
      <p class="subtitle">Access your news dashboard, live updates, saved content, and account settings.</p>

      <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['hs_login_csrf']) ?>">

        <label for="email">Email address</label>
        <input id="email" type="email" name="email" value="<?= htmlspecialchars($email) ?>" autocomplete="email" required>

        <label for="password">Password</label>
        <input id="password" type="password" name="password" autocomplete="current-password" required>

        <div class="form-foot">
          <label class="remember" for="remember_me">
            <input id="remember_me" type="checkbox" name="remember_me" value="1" <?= $remember ? 'checked' : '' ?>>
            Remember me
          </label>
          <a href="<?= hs_base_url('auth/forgot.php') ?>">Forgot Password?</a>
        </div>

        <button class="btn" type="submit">Sign In</button>
      </form>

      <div class="links">
        <a href="<?= hs_base_url('auth/register.php') ?>">Create Account</a>
      </div>
    </section>
  </main>
</body>
</html>
