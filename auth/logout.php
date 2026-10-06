<?php
require_once __DIR__ . '/../app/bootstrap.php';

session_start();
session_destroy();
header('Location: ' . app_url('auth/login.php'));
exit();
?>
