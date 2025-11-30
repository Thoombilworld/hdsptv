<?php
require __DIR__ . '/../bootstrap.php';
hs_require_user();
$db = hs_db();
$user = hs_current_user();

$profileMessages = [];
$profileErrors = [];
$passwordMessages = [];
$passwordErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($name === '') {
            $profileErrors[] = 'Name is required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $profileErrors[] = 'A valid email is required.';
        } else {
            $stmt = mysqli_prepare($db, 'SELECT id FROM hs_frontend_users WHERE email = ? AND id != ? LIMIT 1');
            mysqli_stmt_bind_param($stmt, 'si', $email, $user['id']);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($res && mysqli_fetch_assoc($res)) {
                $profileErrors[] = 'That email is already in use.';
            }
        }

        if (!$profileErrors) {
            $stmt = mysqli_prepare($db, 'UPDATE hs_frontend_users SET name = ?, email = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'ssi', $name, $email, $user['id']);
            mysqli_stmt_execute($stmt);
            $profileMessages[] = 'Profile updated successfully.';
            $user = hs_current_user();
        }
    }

    if ($action === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = mysqli_prepare($db, 'SELECT password_hash FROM hs_frontend_users WHERE id = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 'i', $user['id']);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;

        if (!$row || !password_verify($current, $row['password_hash'])) {
            $passwordErrors[] = 'Your current password is incorrect.';
        }
        if (strlen($new) < 8) {
            $passwordErrors[] = 'New password must be at least 8 characters.';
        }
        if ($new !== $confirm) {
            $passwordErrors[] = 'New password confirmation does not match.';
        }

        if (!$passwordErrors) {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($db, 'UPDATE hs_frontend_users SET password_hash = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'si', $hash, $user['id']);
            mysqli_stmt_execute($stmt);
            $passwordMessages[] = 'Password updated successfully.';
        }
    }
}

$theme = hs_current_theme();
$palette = hs_theme_palette($theme);
$settings = hs_settings();
$languageCode = hs_current_language_code();
$languageDir = hs_is_rtl($languageCode) ? 'rtl' : 'ltr';
?>
<!doctype html>
<html lang="<?= htmlspecialchars($languageCode) ?>" dir="<?= htmlspecialchars($languageDir) ?>">
<head>
  <meta charset="utf-8">
  <title>User Dashboard – <?= htmlspecialchars($settings['site_title'] ?? 'NEWS HDSPTV') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
  <link rel="icon" href="<?= htmlspecialchars($settings['favicon'] ?? hs_base_url('assets/images/favicon.png')) ?>">
  <style>
    :root {
      --hs-primary: <?= $palette['primary'] ?>;
      --hs-primary-dark: <?= $palette['primary_dark'] ?>;
      --hs-accent: <?= $palette['accent'] ?>;
      --hs-bg: <?= $palette['bg'] ?>;
      --hs-surface: <?= $palette['surface'] ?>;
      --hs-card: <?= $palette['card'] ?>;
      --hs-text: <?= $palette['text'] ?>;
      --hs-muted: <?= $palette['muted'] ?>;
      --hs-border: <?= $palette['border'] ?>;
    }

    body {
      margin: 0;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: radial-gradient(circle at 5% 10%, var(--hs-primary) 0, var(--hs-bg) 50%);
      color: var(--hs-text);
    }

    a { color: var(--hs-accent); }

    header {
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding:14px 22px;
      background: linear-gradient(90deg, rgba(15,23,42,0.92), rgba(15,23,42,0.96));
      border-bottom:1px solid var(--hs-border);
      position:sticky;
      top:0;
      z-index:10;
    }
    .logo { font-weight:800; letter-spacing:.12em; font-size:14px; }
    .back-link { color: var(--hs-muted); font-size:13px; text-decoration:none; }
    .back-link:hover { color: var(--hs-accent); }

    .wrap {
      max-width: 900px;
      margin: 24px auto;
      padding: 0 18px 40px;
      display: grid;
      gap: 18px;
    }

    .card {
      background: var(--hs-card);
      border:1px solid var(--hs-border);
      border-radius:14px;
      padding:18px;
      box-shadow:0 14px 30px rgba(0,0,0,0.22);
    }

    h1 { margin: 0 0 12px; font-size: 24px; }
    h2 { margin: 10px 0; font-size: 18px; }
    p { color: var(--hs-muted); }

    form label { display:block; font-weight:600; margin:10px 0 6px; }
    input[type="text"], input[type="email"], input[type="password"] {
      width:100%;
      padding:10px 12px;
      border-radius:10px;
      border:1px solid var(--hs-border);
      background: var(--hs-surface);
      color: var(--hs-text);
    }

    button {
      background: var(--hs-primary);
      color:#fff;
      border:none;
      border-radius:10px;
      padding:10px 14px;
      font-weight:700;
      cursor:pointer;
      margin-top:10px;
    }
    button:hover { filter:brightness(1.05); }

    .meta-grid {
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap:12px;
    }
    .badge { display:inline-block; padding:6px 10px; border-radius:999px; background:rgba(37,99,235,0.16); color:var(--hs-text); font-weight:700; }

    .alerts { margin-bottom: 8px; }
    .alert { padding:10px 12px; border-radius:10px; margin-bottom:8px; }
    .alert.success { background:rgba(34,197,94,0.15); border:1px solid rgba(34,197,94,0.4); }
    .alert.error { background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); }

    @media (max-width:640px) {
      header { flex-direction:column; align-items:flex-start; gap:4px; }
      .wrap { padding:0 14px 30px; }
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">USER DASHBOARD</div>
    <div class="links">
      <a class="back-link" href="<?= hs_base_url('index.php') ?>">← Back to Home</a>
    </div>
  </header>

  <div class="wrap">
    <div class="card">
      <h1>Welcome back, <?= htmlspecialchars($user['name']) ?>.</h1>
      <p>Manage your account details, password, and premium status.</p>
      <div class="meta-grid">
        <div>
          <div class="badge">Email</div>
          <div><?= htmlspecialchars($user['email']) ?></div>
        </div>
        <div>
          <div class="badge">Member since</div>
          <div><?= htmlspecialchars(date('F j, Y', strtotime($user['created_at'] ?? 'now'))) ?></div>
        </div>
        <div>
          <div class="badge">Plan</div>
          <div><?= !empty($user['is_premium']) ? 'Premium' : 'Standard' ?></div>
        </div>
      </div>
    </div>

    <div class="card">
      <h2>Edit Profile</h2>
      <div class="alerts">
        <?php foreach ($profileMessages as $msg): ?>
          <div class="alert success"><?= htmlspecialchars($msg) ?></div>
        <?php endforeach; ?>
        <?php foreach ($profileErrors as $err): ?>
          <div class="alert error"><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
      </div>
      <form method="post">
        <input type="hidden" name="action" value="profile">
        <label for="name">Name</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" required>

        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <button type="submit">Save profile</button>
      </form>
    </div>

    <div class="card">
      <h2>Update Password</h2>
      <div class="alerts">
        <?php foreach ($passwordMessages as $msg): ?>
          <div class="alert success"><?= htmlspecialchars($msg) ?></div>
        <?php endforeach; ?>
        <?php foreach ($passwordErrors as $err): ?>
          <div class="alert error"><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
      </div>
      <form method="post">
        <input type="hidden" name="action" value="password">
        <label for="current_password">Current password</label>
        <input type="password" name="current_password" id="current_password" required>

        <label for="new_password">New password</label>
        <input type="password" name="new_password" id="new_password" minlength="8" required>

        <label for="confirm_password">Confirm new password</label>
        <input type="password" name="confirm_password" id="confirm_password" minlength="8" required>

        <button type="submit">Change password</button>
      </form>
    </div>
  </div>
</body>
</html>
