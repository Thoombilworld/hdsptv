<?php
require __DIR__ . '/../../bootstrap.php';
hs_require_staff(['admin', 'editor']);
$db = hs_db();
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    mysqli_query($db, "DELETE FROM hs_post_tags WHERE post_id=".$id);
    mysqli_query($db, "DELETE FROM hs_posts WHERE id=".$id." LIMIT 1");
}
header('Location: ' . hs_base_url('admin/content/articles.php'));
exit;
