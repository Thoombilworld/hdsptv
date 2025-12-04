<?php
require __DIR__ . '/bootstrap.php';

$settings = hs_settings();
$layout = hs_home_layout($settings);

$posts = [];
$categories = [];
$featured = [];
$breaking = [];
$trending = [];
$video_posts = [];
$gallery_posts = [];

$ads = hs_active_ads();

if (defined('HS_INSTALLED') && HS_INSTALLED) {
    $db = hs_db();
    $res = mysqli_query($db, "SELECT p.*, c.name AS category_name, c.slug AS category_slug
                              FROM hs_posts p
                              LEFT JOIN hs_categories c ON c.id = p.category_id
                              WHERE p.status='published'
                              ORDER BY p.created_at DESC
                              LIMIT 36");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) $posts[] = $row;
    }

    if ($layout['featured']) {
        $res = mysqli_query($db, "SELECT p.*, c.name AS category_name
                              FROM hs_posts p
                              LEFT JOIN hs_categories c ON c.id = p.category_id
                              WHERE p.status='published' AND p.is_featured=1
                              ORDER BY p.created_at DESC
                              LIMIT 5");
        if ($res) while ($r = mysqli_fetch_assoc($res)) $featured[] = $r;
    }

    if ($layout['breaking']) {
        $res = mysqli_query($db, "SELECT title FROM hs_posts WHERE status='published' AND is_breaking=1 ORDER BY created_at DESC LIMIT 10");
        if ($res) while ($r = mysqli_fetch_assoc($res)) $breaking[] = $r;
    }

    if ($layout['trending']) {
        $res = mysqli_query($db, "SELECT p.*, c.name AS category_name
                              FROM hs_posts p
                              LEFT JOIN hs_categories c ON c.id = p.category_id
                              WHERE p.status='published' AND p.is_trending=1
                              ORDER BY p.created_at DESC
                              LIMIT 6");
        if ($res) while ($r = mysqli_fetch_assoc($res)) $trending[] = $r;
    }

    if ($layout['video']) {
        $res = mysqli_query($db, "SELECT p.*, c.name AS category_name
                              FROM hs_posts p
                              LEFT JOIN hs_categories c ON c.id = p.category_id
                              WHERE p.status='published' AND p.type='video'
                              ORDER BY p.created_at DESC
                              LIMIT 6");
        if ($res) while ($r = mysqli_fetch_assoc($res)) $video_posts[] = $r;
    }

    if ($layout['gallery']) {
        $res = mysqli_query($db, "SELECT p.*, c.name AS category_name
                              FROM hs_posts p
                              LEFT JOIN hs_categories c ON c.id = p.category_id
                              WHERE p.status='published' AND p.type='gallery'
                              ORDER BY p.created_at DESC
                              LIMIT 6");
        if ($res) while ($r = mysqli_fetch_assoc($res)) $gallery_posts[] = $r;
    }

    $res = mysqli_query($db, "SELECT id, name, slug FROM hs_categories ORDER BY name ASC");
    if ($res) while ($r = mysqli_fetch_assoc($res)) $categories[] = $r;

}

hs_track_event(['type' => 'home']);

hs_view('frontend/home', [
    'settings'      => $settings,
    'categories'    => $categories,
    'posts'         => $posts,
    'featured'      => $featured,
    'breaking'      => $breaking,
    'trending'      => $trending,
    'video_posts'   => $video_posts,
    'gallery_posts' => $gallery_posts,
    'ads'           => $ads,
    'layout'        => $layout,
]);
