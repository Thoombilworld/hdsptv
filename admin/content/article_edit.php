<?php
require __DIR__ . '/../../bootstrap.php';
hs_require_staff(['admin', 'editor', 'reporter']);
$db = hs_db();
$staff = hs_current_staff();
$role = $staff['role'] ?? 'admin';

// categories for select
$catRes = mysqli_query($db, "SELECT id, name FROM hs_categories ORDER BY name ASC");
$categories = $catRes ? mysqli_fetch_all($catRes, MYSQLI_ASSOC) : [];
$reporters = $editors = [];
$rRes = mysqli_query($db, "SELECT id, name FROM hs_users WHERE role='reporter' AND status='active' ORDER BY name ASC");
if ($rRes) $reporters = mysqli_fetch_all($rRes, MYSQLI_ASSOC);
$eRes = mysqli_query($db, "SELECT id, name FROM hs_users WHERE role='editor' AND status='active' ORDER BY name ASC");
if ($eRes) $editors = mysqli_fetch_all($eRes, MYSQLI_ASSOC);

function hs_slugify_local($text) {
    $text = trim($text);
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    if (empty($text)) { return 'post-' . time(); }
    return $text;
}

$id = (int)($_GET['id'] ?? 0);
$res = mysqli_query($db, "SELECT * FROM hs_posts WHERE id=".$id." LIMIT 1");
$post = $res ? mysqli_fetch_assoc($res) : null;
if (!$post) {
    echo "Post not found.";
    exit;
}
$selected_reporter = (int)($post['reporter_id'] ?? (($role === 'reporter') ? ($staff['id'] ?? 0) : 0));
$selected_editor   = (int)($post['editor_id'] ?? (($role === 'editor' || $role === 'admin') ? ($staff['id'] ?? 0) : 0));
$error = '';
$tags_str = '';
$tr = mysqli_query($db, "SELECT t.name FROM hs_tags t JOIN hs_post_tags pt ON pt.tag_id=t.id WHERE pt.post_id=".$id);
if ($tr) {
    $names = [];
    while ($row = mysqli_fetch_assoc($tr)) $names[] = $row['name'];
    $tags_str = implode(', ', $names);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $slug_in = trim($_POST['slug'] ?? '');
    $slug    = $slug_in !== '' ? $slug_in : hs_slugify_local($title);
    $cat_id  = (int)($_POST['category_id'] ?? 0);
    $type    = $_POST['type'] ?? 'article';
    $region  = $_POST['region'] ?? 'global';
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_trending = isset($_POST['is_trending']) ? 1 : 0;
    $video_url   = trim($_POST['video_url'] ?? '');
    $status  = $_POST['status'] ?? 'draft';
    $tags_raw = trim($_POST['tags'] ?? '');
    $image_main = $post['image_main'];
    $reporter_id = $role === 'reporter' ? (int)($staff['id'] ?? 0) : (int)($_POST['reporter_id'] ?? $selected_reporter);
    $editor_id   = in_array($role, ['admin','editor'], true) ? (int)($_POST['editor_id'] ?? $selected_editor) : $selected_editor;
    $selected_reporter = $reporter_id;
    $selected_editor = $editor_id;

    if ($title === '') {
        $error = 'Title is required.';
    } else {
        if (!empty($_FILES['image_main']['name'])) {
            $uploadDir = __DIR__ . '/../../writable/uploads/images/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $base = basename($_FILES['image_main']['name']);
            $safe = preg_replace('/[^A-Za-z0-9_.-]/', '_', $base);
            $target = $uploadDir . time() . '_' . $safe;
            if (move_uploaded_file($_FILES['image_main']['tmp_name'], $target)) {
                $image_main = 'writable/uploads/images/' . basename($target);
            }
        }

        $stmt = mysqli_prepare($db, "UPDATE hs_posts SET category_id=?, title=?, slug=?, excerpt=?, content=?, type=?, region=?, reporter_id=?, editor_id=?, image_main=?, video_url=?, is_breaking=?, is_featured=?, is_trending=?, status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'issssssiissiiisi', $cat_id,$title,$slug,$excerpt,$content,$type,$region,$reporter_id,$editor_id,$image_main,$video_url,$is_breaking,$is_featured,$is_trending,$status,$id);
        if (!mysqli_stmt_execute($stmt)) {
            $error = 'Error updating post: ' . mysqli_error($db);
        } else {
            mysqli_query($db, "DELETE FROM hs_post_tags WHERE post_id=".$id);
            if ($tags_raw !== '') {
                $tags = array_filter(array_map('trim', explode(',', $tags_raw)));
                foreach ($tags as $t) {
                    if ($t === '') continue;
                    $slug_tag = hs_slugify_local($t);
                    $stmtT = mysqli_prepare($db, "INSERT INTO hs_tags (name,slug) VALUES (?,?) ON DUPLICATE KEY UPDATE name=VALUES(name)");
                    mysqli_stmt_bind_param($stmtT, 'ss', $t, $slug_tag);
                    mysqli_stmt_execute($stmtT);
                    $tag_id = mysqli_insert_id($db);
                    if ($tag_id == 0) {
                        $resT = mysqli_query($db, "SELECT id FROM hs_tags WHERE slug='".mysqli_real_escape_string($db,$slug_tag)."' LIMIT 1");
                        if ($resT && ($rowT=mysqli_fetch_assoc($resT))) $tag_id = (int)$rowT['id'];
                    }
                    if ($tag_id > 0) {
                        mysqli_query($db, "INSERT IGNORE INTO hs_post_tags (post_id, tag_id) VALUES (".$id.",".(int)$tag_id.")");
                    }
                }
            }
            header('Location: ' . hs_base_url('admin/content/articles.php'));
            exit;
        }
    }

    $post['title'] = $title;
    $post['slug'] = $slug;
    $post['category_id'] = $cat_id;
    $post['type'] = $type;
    $post['region'] = $region;
    $post['excerpt'] = $excerpt;
    $post['content'] = $content;
    $post['image_main'] = $image_main;
    $post['video_url'] = $video_url;
    $post['is_breaking'] = $is_breaking;
    $post['is_featured'] = $is_featured;
    $post['is_trending'] = $is_trending;
    $post['status'] = $status;
    $post['reporter_id'] = $selected_reporter;
    $post['editor_id'] = $selected_editor;
    $tags_str = $tags_raw;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Article – NEWS HDSPTV</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.23/dist/summernote-lite.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-3gJwYp4d6dk+Nr6dcw3AV/Qdi03y7P03a06f5dFtg3E=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.23/dist/summernote-lite.min.js"></script>
</head>
<body style="max-width:900px;margin:20px auto;padding:0 16px;">
  <h1>Edit Article</h1>
  <?php if ($error): ?><div style="color:red;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <label>Title</label><br>
    <input type="text" name="title" style="width:100%;" required value="<?= htmlspecialchars($post['title']) ?>"><br><br>

    <label>Slug</label><br>
    <input type="text" name="slug" style="width:100%;" value="<?= htmlspecialchars($post['slug']) ?>"><br><br>

    <label>Category</label><br>
    <select name="category_id" style="width:100%;">
      <option value="0">-- None --</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= $post['category_id']==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
      <?php endforeach; ?>
    </select><br><br>

    <label>Type</label><br>
    <select name="type">
      <option value="article" <?= $post['type']=='article'?'selected':'' ?>>Standard (Article)</option>
      <option value="video" <?= $post['type']=='video'?'selected':'' ?>>Video</option>
      <option value="gallery" <?= $post['type']=='gallery'?'selected':'' ?>>Gallery</option>
    </select><br><br>

    <label>Region</label><br>
    <select name="region">
      <option value="global" <?= $post['region']=='global'?'selected':'' ?>>Global</option>
      <option value="india" <?= $post['region']=='india'?'selected':'' ?>>India</option>
      <option value="gcc" <?= $post['region']=='gcc'?'selected':'' ?>>GCC</option>
      <option value="kerala" <?= $post['region']=='kerala'?'selected':'' ?>>Kerala</option>
      <option value="world" <?= $post['region']=='world'?'selected':'' ?>>World</option>
      <option value="sports" <?= $post['region']=='sports'?'selected':'' ?>>Sports</option>
    </select><br><br>

    <?php if ($role === 'reporter'): ?>
      <input type="hidden" name="reporter_id" value="<?= (int)$selected_reporter ?>">
    <?php else: ?>
      <label>Reporter</label><br>
      <select name="reporter_id" style="width:100%;">
        <option value="0">-- Not assigned --</option>
        <?php foreach ($reporters as $r): ?>
          <option value="<?= (int)$r['id'] ?>" <?= $selected_reporter == $r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['name']) ?></option>
        <?php endforeach; ?>
      </select><br><br>
    <?php endif; ?>

    <?php if (in_array($role, ['admin','editor'], true)): ?>
      <label>Editor</label><br>
      <select name="editor_id" style="width:100%;">
        <option value="0">-- Not assigned --</option>
        <?php foreach ($editors as $e): ?>
          <option value="<?= (int)$e['id'] ?>" <?= $selected_editor == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['name']) ?></option>
        <?php endforeach; ?>
      </select><br><br>
    <?php else: ?>
      <input type="hidden" name="editor_id" value="<?= (int)$selected_editor ?>">
    <?php endif; ?>

    <label>Short Description (Excerpt)</label><br>
    <textarea name="excerpt" style="width:100%;height:60px;"><?= htmlspecialchars($post['excerpt']) ?></textarea><br><br>

    <label>Content (HTML allowed)</label><br>
    <textarea class="summernote" name="content" style="width:100%;height:200px;"><?= htmlspecialchars($post['content']) ?></textarea><br><br>

    <label>Main Image</label><br>
    <?php if (!empty($post['image_main'])): ?>
      <div>Current: <?= htmlspecialchars($post['image_main']) ?></div>
    <?php endif; ?>
    <input type="file" name="image_main"><br><br>

    <label>Video URL (YouTube / MP4 link)</label><br>
    <input type="text" name="video_url" style="width:100%;" value="<?= htmlspecialchars($post['video_url']) ?>"><br><br>

    <label>Tags (comma separated)</label><br>
    <input type="text" name="tags" style="width:100%;" value="<?= htmlspecialchars($tags_str) ?>"><br><br>

    <label><input type="checkbox" name="is_breaking" <?= $post['is_breaking']?'checked':'' ?>> Breaking</label><br>
    <label><input type="checkbox" name="is_featured" <?= $post['is_featured']?'checked':'' ?>> Featured</label><br>
    <label><input type="checkbox" name="is_trending" <?= $post['is_trending']?'checked':'' ?>> Trending</label><br><br>

    <label>Status</label><br>
    <select name="status">
      <option value="draft" <?= $post['status']=='draft'?'selected':'' ?>>Draft</option>
      <option value="published" <?= $post['status']=='published'?'selected':'' ?>>Published</option>
    </select><br><br>

    <button type="submit">Update Article</button>
  </form>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      $('.summernote').summernote({
        placeholder: 'Update article content here...',
        height: 360,
        minHeight: 260,
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
