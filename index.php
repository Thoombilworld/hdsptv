<?php
require __DIR__ . '/bootstrap.php';

$settings = hs_settings();

$posts = [];
$featured = [];
$breaking = [];
$trending = [];
$video_posts = [];
$gallery_posts = [];

$ads = hs_active_ads();

if (defined('HS_INSTALLED') && HS_INSTALLED) {
    $db = hs_db();
    $res = mysqli_query($db, "SELECT p.*, c.name AS category_name
                              FROM hs_posts p
                              LEFT JOIN hs_categories c ON c.id = p.category_id
                              WHERE p.status='published'
                              ORDER BY p.created_at DESC
                              LIMIT 18");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) $posts[] = $row;
    }

    $res = mysqli_query($db, "SELECT p.*, c.name AS category_name
                              FROM hs_posts p
                              LEFT JOIN hs_categories c ON c.id = p.category_id
                              WHERE p.status='published' AND p.is_featured=1
                              ORDER BY p.created_at DESC
                              LIMIT 5");
    if ($res) while ($r = mysqli_fetch_assoc($res)) $featured[] = $r;

    $res = mysqli_query($db, "SELECT title FROM hs_posts WHERE status='published' AND is_breaking=1 ORDER BY created_at DESC LIMIT 10");
    if ($res) while ($r = mysqli_fetch_assoc($res)) $breaking[] = $r;

    $res = mysqli_query($db, "SELECT p.*, c.name AS category_name
                              FROM hs_posts p
                              LEFT JOIN hs_categories c ON c.id = p.category_id
                              WHERE p.status='published' AND p.is_trending=1
                              ORDER BY p.created_at DESC
                              LIMIT 6");
    if ($res) while ($r = mysqli_fetch_assoc($res)) $trending[] = $r;

    $res = mysqli_query($db, "SELECT p.*, c.name AS category_name
                              FROM hs_posts p
                              LEFT JOIN hs_categories c ON c.id = p.category_id
                              WHERE p.status='published' AND p.type='video'
                              ORDER BY p.created_at DESC
                              LIMIT 6");
    if ($res) while ($r = mysqli_fetch_assoc($res)) $video_posts[] = $r;

    $res = mysqli_query($db, "SELECT p.*, c.name AS category_name
                              FROM hs_posts p
                              LEFT JOIN hs_categories c ON c.id = p.category_id
                              WHERE p.status='published' AND p.type='gallery'
                              ORDER BY p.created_at DESC
                              LIMIT 6");
    if ($res) while ($r = mysqli_fetch_assoc($res)) $gallery_posts[] = $r;

}

hs_view('frontend/home', [
    'settings'      => $settings,
    'posts'         => $posts,
    'featured'      => $featured,
    'breaking'      => $breaking,
    'trending'      => $trending,
    'video_posts'   => $video_posts,
    'gallery_posts' => $gallery_posts,
    'ads'           => $ads,
]);
