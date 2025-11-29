<?php
require __DIR__ . '/../bootstrap.php';
session_destroy();
header('Location: ' . hs_base_url('admin/login.php'));
exit;
