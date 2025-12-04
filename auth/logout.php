<?php
require __DIR__ . '/../bootstrap.php';
unset($_SESSION['hs_user_id']);
header('Location: ' . hs_base_url('/'));
exit;
