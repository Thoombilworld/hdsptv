<?php
require __DIR__ . '/../bootstrap.php';
hs_require_admin();

$db = hs_db();
$settings = hs_settings();
$role = hs_staff_role() ?? 'admin';
$msg = '';

function hs_save_setting($db, $key, $value) {
    $stmt = mysqli_prepare($db, "INSERT INTO hs_settings (`key`,`value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
    mysqli_stmt_bind_param($stmt, 'ss', $key, $value);
    mysqli_stmt_execute($stmt);
}

function hs_delete_setting($db, $key) {
    $stmt = mysqli_prepare($db, "DELETE FROM hs_settings WHERE `key` = ?");
    mysqli_stmt_bind_param($stmt, 's', $key);
    mysqli_stmt_execute($stmt);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    $form = $_POST['form'] ?? '';

    if (in_array($form, ['terms', 'privacy', 'contact_content'], true)) {
        $key = $form === 'contact_content' ? 'contact_content' : $form . '_content';
        if ($action === 'delete') {
            hs_delete_setting($db, $key);
            $msg = ucfirst(str_replace('_', ' ', $form)) . ' content removed. Default copy will display publicly.';
        } else {
            $content = trim($_POST['content'] ?? '');
            hs_save_setting($db, $key, $content);
            $msg = ucfirst(str_replace('_', ' ', $form)) . ' saved.';
        }
    } elseif ($form === 'contact_info') {
        $email = trim($_POST['contact_email'] ?? '');
        $phone = trim($_POST['contact_phone'] ?? '');
        $address = trim($_POST['office_address'] ?? '');

        hs_save_setting($db, 'contact_email', $email);
        hs_save_setting($db, 'contact_phone', $phone);
        hs_save_setting($db, 'office_address', $address);
        $msg = 'Contact info updated.';
    }

    $settings = hs_settings(true);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Legal Pages – NEWS HDSPTV</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.23/dist/summernote-lite.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-3gJwYp4d6dk+Nr6dcw3AV/Qdi03y7P03a06f5dFtg3E=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.23/dist/summernote-lite.min.js"></script>
  <style>
    body { margin:0; font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; background:#0B1120; color:#E5E7EB; }
    header { padding:12px 20px; border-bottom:1px solid #111827; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; }
    .logo { font-size:16px; font-weight:700; letter-spacing:.12em; }
    nav a { margin-right:10px; font-size:12px; color:#9CA3AF; }
    nav a:hover, nav a.active { color:#FACC15; }
    nav a.highlight { color:#FACC15; font-weight:600; }
    .container { max-width:1100px; margin:18px auto 26px; padding:0 16px; }
    .panel { background:radial-gradient(circle at top left,#1E3A8A,#020617); border-radius:14px; padding:16px 18px; box-shadow:0 22px 60px rgba(15,23,42,0.75); margin-bottom:18px; }
    .panel h2 { margin:0 0 6px; font-size:16px; }
    .panel p { margin:0 0 10px; font-size:13px; color:#E5E7EB; }
    textarea { width:100%; min-height:160px; border-radius:8px; border:1px solid rgba(248,250,252,.16); background:rgba(2,6,23,0.8); color:#F9FAFB; padding:10px; font-size:13px; }
    input[type="text"] { width:100%; border-radius:8px; border:1px solid rgba(248,250,252,.16); background:rgba(2,6,23,0.8); color:#F9FAFB; padding:10px; font-size:13px; }
    button { padding:8px 14px; border-radius:999px; border:1px solid rgba(248,250,252,.26); background:#0F172A; color:#E5E7EB; cursor:pointer; margin-right:8px; }
    button:hover { border-color:#FACC15; color:#FACC15; }
    .actions { margin-top:10px; display:flex; gap:10px; flex-wrap:wrap; }
    .muted { color:#9CA3AF; font-size:12px; }
    .message { margin:10px 0; padding:10px 12px; border-radius:10px; background:rgba(34,197,94,0.14); color:#BBF7D0; border:1px solid rgba(34,197,94,0.4); }
  </style>
</head>
<body>
<header>
  <div class="logo">NEWS HDSPTV • ADMIN</div>
  <nav>
    <?php hs_render_admin_nav($role, 'legal'); ?>
  </nav>
</header>
<main class="container">
  <h1>Legal Pages &amp; Contact</h1>
  <p class="muted">Manage the Terms &amp; Conditions, Privacy Policy, and Contact page content shown on the public site. Leave a field blank or delete to fall back to the built-in defaults.</p>
  <?php if ($msg): ?><div class="message"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <section class="panel">
    <h2>Terms &amp; Conditions</h2>
    <p>Edit or replace the Terms copy that appears on the public terms page. HTML is allowed.</p>
    <form method="post">
      <input type="hidden" name="form" value="terms">
      <textarea class="summernote" name="content" placeholder="Enter Terms &amp; Conditions HTML or text..."><?= htmlspecialchars($settings['terms_content'] ?? '') ?></textarea>
      <div class="actions">
        <button type="submit" name="action" value="save">Save Terms</button>
        <button type="submit" name="action" value="delete" onclick="return confirm('Remove custom Terms content? Default copy will be used.');">Delete Custom Terms</button>
      </div>
    </form>
  </section>

  <section class="panel">
    <h2>Privacy Policy</h2>
    <p>Control the privacy copy, including data practices and contact details. HTML is allowed.</p>
    <form method="post">
      <input type="hidden" name="form" value="privacy">
      <textarea class="summernote" name="content" placeholder="Enter Privacy Policy HTML or text..."><?= htmlspecialchars($settings['privacy_content'] ?? '') ?></textarea>
      <div class="actions">
        <button type="submit" name="action" value="save">Save Privacy</button>
        <button type="submit" name="action" value="delete" onclick="return confirm('Remove custom Privacy content? Default copy will be used.');">Delete Custom Privacy</button>
      </div>
    </form>
  </section>

  <section class="panel">
    <h2>Contact Page Content</h2>
    <p>Optional custom HTML/text displayed at the top of the public contact page.</p>
    <form method="post">
      <input type="hidden" name="form" value="contact_content">
      <textarea class="summernote" name="content" placeholder="Add intro, office hours, or location details..."><?= htmlspecialchars($settings['contact_content'] ?? '') ?></textarea>
      <div class="actions">
        <button type="submit" name="action" value="save">Save Contact Content</button>
        <button type="submit" name="action" value="delete" onclick="return confirm('Remove custom Contact content? Default copy will be used.');">Delete Custom Contact Content</button>
      </div>
    </form>
  </section>

  <section class="panel">
    <h2>Contact Info</h2>
    <p>Update the email, phone, and office address used on the Contact page.</p>
    <form method="post">
      <input type="hidden" name="form" value="contact_info">
      <label>Email</label><br>
      <input type="text" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" placeholder="support@example.com"><br><br>
      <label>Phone</label><br>
      <input type="text" name="contact_phone" value="<?= htmlspecialchars($settings['contact_phone'] ?? '') ?>" placeholder="+971 ..."><br><br>
      <label>Office Address</label><br>
      <input type="text" name="office_address" value="<?= htmlspecialchars($settings['office_address'] ?? '') ?>" placeholder="Newsroom address"><br>
      <div class="actions">
        <button type="submit" name="action" value="save">Save Contact Info</button>
      </div>
    </form>
  </section>
</main>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    $('.summernote').summernote({
      placeholder: 'Write content here...',
      height: 320,
      minHeight: 200,
      toolbar: [
        ['style', ['style']],
        ['font', ['bold', 'underline', 'italic', 'strikethrough', 'clear']],
        ['fontname', ['fontname']],
        ['fontsize', ['fontsize']],
        ['color', ['color']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['insert', ['link', 'picture', 'video', 'table', 'hr']],
        ['view', ['fullscreen', 'codeview', 'help']]
      ],
      fontSizes: ['10', '12', '14', '16', '18', '20', '22', '24', '28', '32', '36'],
      lineHeights: ['0.8', '1.0', '1.2', '1.4', '1.6', '1.8', '2.0']
    });
  });
</script>
</body>
</html>
