<?php
if (file_exists(__DIR__ . '/../.env.php')) {
    echo "<h2>NEWS HDSPTV appears to be already installed.</h2>";
    echo "<p>Delete the .env.php file and database tables if you need a clean reinstall.</p>";
    exit;
}
list($checks, $perms) = require __DIR__ . '/checks.php';
$errors = [];
$installed = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appName = trim($_POST['app_name'] ?? 'NEWS HDSPTV');
    $baseUrl = rtrim($_POST['base_url'] ?? '', '/') . '/';
    $dbHost  = trim($_POST['db_host'] ?? 'localhost');
    $dbName  = trim($_POST['db_name'] ?? '');
    $dbUser  = trim($_POST['db_user'] ?? '');
    $dbPass  = $_POST['db_pass'] ?? '';
    $adminName  = trim($_POST['admin_name'] ?? 'Admin');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminPass  = $_POST['admin_pass'] ?? '';
    if ($dbName === '' || $dbUser === '' || $adminEmail === '' || $adminPass === '') {
        $errors[] = 'Please fill in all required fields.';
    }
    if (empty($errors)) {
        $mysqli = @mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
        if (!$mysqli) {
            $errors[] = 'Database connection failed: ' . mysqli_connect_error();
        } else {
            mysqli_set_charset($mysqli, 'utf8mb4');
            $sql = file_get_contents(__DIR__ . '/install.sql');
            if (!$sql) {
                $errors[] = 'Could not read install.sql';
            } else {
                if (!mysqli_multi_query($mysqli, $sql)) {
                    $errors[] = 'Failed to run install.sql: ' . mysqli_error($mysqli);
                } else {
                    while (mysqli_more_results($mysqli) && mysqli_next_result($mysqli)) { /* flush */ }
                    $hash = password_hash($adminPass, PASSWORD_BCRYPT);
                    $stmt = mysqli_prepare($mysqli, "INSERT INTO hs_users (name, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
                    mysqli_stmt_bind_param($stmt, 'sss', $adminName, $adminEmail, $hash);
                    mysqli_stmt_execute($stmt);
                    $envTemplate = file_get_contents(__DIR__ . '/env.example.php');
                    $search  = ['{{APP_NAME}}','{{BASE_URL}}','{{DB_HOST}}','{{DB_NAME}}','{{DB_USER}}','{{DB_PASS}}'];
                    $replace = [$appName,$baseUrl,$dbHost,$dbName,$dbUser,$dbPass];
                    $envContent = str_replace($search, $replace, $envTemplate);
                    $envTarget = __DIR__ . '/../.env.php';
                    if (!file_put_contents($envTarget, $envContent)) {
                        $errors[] = 'Could not write .env.php file in project root.';
                    } else {
                        $installed = true;
                    }
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
  <title>NEWS HDSPTV Installer (V20)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; background:radial-gradient(circle at top,#1E3A8A 0,#0B1120 45%,#020617 100%); margin:0; color:#E5E7EB; }
    .wrap { max-width:860px; margin:20px auto 26px; padding:0 16px; }
    .card { background:#020617; border-radius:18px; padding:20px 22px; box-shadow:0 25px 60px rgba(15,23,42,0.85); }
    h1 { margin-top:0; font-size:22px; letter-spacing:.15em; text-transform:uppercase; }
    label { font-size:13px; display:block; margin-bottom:4px; }
    input { width:100%; padding:8px 10px; border-radius:10px; border:1px solid #1F2937; background:#020617; color:#E5E7EB; margin-bottom:8px; font-size:13px; }
    .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:12px 20px; }
    button { padding:9px 18px; border-radius:999px; border:none; background:#FACC15; color:#111827; font-weight:600; font-size:14px; cursor:pointer; margin-top:8px; }
    .errors { background:#7F1D1D; color:#FECACA; padding:10px 12px; border-radius:10px; font-size:13px; margin-bottom:12px; }
    .ok { background:#064E3B; color:#BBF7D0; padding:10px 12px; border-radius:10px; font-size:13px; margin-bottom:12px; }
    table { width:100%; border-collapse:collapse; font-size:12px; }
    th,td { border-bottom:1px solid #111827; padding:4px 6px; text-align:left; }
    th { font-weight:600; color:#F9FAFB; }
    .tag { font-size:11px; color:#9CA3AF; text-transform:uppercase; letter-spacing:.16em; }
  </style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <h1>NEWS HDSPTV • INSTALLER V20</h1>
    <p class="tag">Enterprise Pro · Homepage + Admin + Data Included</p>
    <?php if ($installed && empty($errors)): ?>
      <div class="ok">
        Installation completed successfully!<br>
        Frontend: <strong><?= htmlspecialchars($baseUrl ?? '') ?></strong><br>
        Admin: <strong><?= htmlspecialchars($baseUrl ?? '') ?>admin/</strong>
      </div>
      <p style="font-size:13px;">
        <strong>Next steps:</strong><br>
        1. Delete the <code>/install</code> folder for security.<br>
        2. Login to admin with the email/password you entered above.
      </p>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
      <div class="errors">
        <strong>There were some problems:</strong>
        <ul>
          <?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    <h2 style="font-size:16px;">1. Environment Check</h2>
    <table>
      <tr><th>Check</th><th>Status</th></tr>
      <tr><td>PHP &ge; 8.0</td><td><?= $checks['php_version'] ? 'OK ✅' : 'Missing ❌' ?></td></tr>
      <tr><td>Extension mysqli</td><td><?= $checks['ext_mysqli'] ? 'OK ✅' : 'Missing ❌' ?></td></tr>
      <tr><td>Extension mbstring</td><td><?= $checks['ext_mbstring'] ? 'OK ✅' : 'Missing ❌' ?></td></tr>
      <tr><td>Extension json</td><td><?= $checks['ext_json'] ? 'OK ✅' : 'Missing ❌' ?></td></tr>
    </table>
    <h2 style="font-size:16px; margin-top:16px;">2. Permissions</h2>
    <table>
      <tr><th>Path</th><th>Status</th></tr>
      <?php foreach ($perms as $label => $ok): ?>
        <tr><td><?= htmlspecialchars($label) ?></td><td><?= $ok ? 'Writable ✅' : 'Not Writable ❌' ?></td></tr>
      <?php endforeach; ?>
    </table>
    <h2 style="font-size:16px; margin-top:16px;">3. Configuration</h2>
    <form method="post">
      <div class="grid">
        <div>
          <h3 style="font-size:14px;">App</h3>
          <label>App Name</label>
          <input type="text" name="app_name" value="<?= htmlspecialchars($_POST['app_name'] ?? 'NEWS HDSPTV') ?>">
          <label>Base URL (with trailing /)</label>
          <input type="text" name="base_url" value="<?= htmlspecialchars($_POST['base_url'] ?? 'https://hdsptv.com/') ?>">
        </div>
        <div>
          <h3 style="font-size:14px;">Database</h3>
          <label>DB Host</label>
          <input type="text" name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>">
          <label>DB Name</label>
          <input type="text" name="db_name" required value="<?= htmlspecialchars($_POST['db_name'] ?? '') ?>">
          <label>DB User</label>
          <input type="text" name="db_user" required value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>">
          <label>DB Password</label>
          <input type="password" name="db_pass" value="<?= htmlspecialchars($_POST['db_pass'] ?? '') ?>">
        </div>
        <div>
          <h3 style="font-size:14px;">Admin User</h3>
          <label>Admin Name</label>
          <input type="text" name="admin_name" value="<?= htmlspecialchars($_POST['admin_name'] ?? 'Admin') ?>">
          <label>Admin Email</label>
          <input type="email" name="admin_email" required value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>">
          <label>Password</label>
          <input type="password" name="admin_pass" required>
        </div>
      </div>
      <button type="submit">Install NEWS HDSPTV</button>
    </form>
  </div>
</div>
</body>
</html>
