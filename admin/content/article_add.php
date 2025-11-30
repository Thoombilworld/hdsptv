<?php
require __DIR__ . '/../../bootstrap.php';
hs_require_staff(['admin', 'editor', 'reporter']);
$db = hs_db();

// categories for select
$catRes = mysqli_query($db, "SELECT id, name FROM hs_categories ORDER BY name ASC");
$categories = $catRes ? mysqli_fetch_all($catRes, MYSQLI_ASSOC) : [];

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

$error = '';
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
    $image_main = null;

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

        $stmt = mysqli_prepare($db, "INSERT INTO hs_posts (category_id,title,slug,excerpt,content,type,region,image_main,video_url,is_breaking,is_featured,is_trending,status)
                                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, 'issssssssiiis', $cat_id,$title,$slug,$excerpt,$content,$type,$region,$image_main,$video_url,$is_breaking,$is_featured,$is_trending,$status);
        if (!mysqli_stmt_execute($stmt)) {
            $error = 'Error saving post: ' . mysqli_error($db);
        } else {
            $post_id = mysqli_insert_id($db);
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
                        mysqli_query($db, "INSERT IGNORE INTO hs_post_tags (post_id, tag_id) VALUES (".(int)$post_id.",".(int)$tag_id.")");
                    }
                }
            }
            header('Location: ' . hs_base_url('admin/content/articles.php'));
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add Article – NEWS HDSPTV</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= hs_base_url('assets/css/style.css') ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.23/dist/summernote-lite.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-3gJwYp4d6dk+Nr6dcw3AV/Qdi03y7P03a06f5dFtg3E=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.23/dist/summernote-lite.min.js"></script>
</head>
<body style="max-width:900px;margin:20px auto;padding:0 16px;">
  <h1>Add Article</h1>
  <?php if ($error): ?><div style="color:red;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <label>Title</label><br>
    <input type="text" name="title" style="width:100%;" required><br><br>

    <label>Slug (optional)</label><br>
    <input type="text" name="slug" style="width:100%;"><br><br>

    <label>Category</label><br>
    <select name="category_id" style="width:100%;">
      <option value="0">-- None --</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
      <?php endforeach; ?>
    </select><br><br>

    <label>Type</label><br>
    <select name="type">
      <option value="article">Article</option>
      <option value="video">Video</option>
      <option value="gallery">Gallery</option>
    </select><br><br>

    <label>Region</label><br>
    <select name="region">
      <option value="global">Global</option>
      <option value="india">India</option>
      <option value="gcc">GCC</option>
      <option value="kerala">Kerala</option>
      <option value="world">World</option>
      <option value="sports">Sports</option>
    </select><br><br>

    <label>Short Description (Excerpt)</label><br>
    <textarea name="excerpt" style="width:100%;height:60px;"></textarea><br><br>

    <label>Content (HTML allowed)</label><br>
    <textarea class="summernote" name="content" style="width:100%;height:200px;"></textarea><br><br>

    <label>Main Image</label><br>
    <input type="file" name="image_main"><br><br>

    <label>Video URL (YouTube / MP4 link)</label><br>
    <input type="text" name="video_url" style="width:100%;"><br><br>

    <label>Tags (comma separated)</label><br>
    <input type="text" name="tags" style="width:100%;"><br><br>

    <label><input type="checkbox" name="is_breaking"> Breaking</label><br>
    <label><input type="checkbox" name="is_featured"> Featured</label><br>
    <label><input type="checkbox" name="is_trending"> Trending</label><br><br>

    <label>Status</label><br>
    <select name="status">
      <option value="draft">Draft</option>
      <option value="published">Published</option>
    </select><br><br>

    <button type="submit">Save Article</button>
  </form>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      $('.summernote').summernote({
        placeholder: 'Write article content here...',
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
