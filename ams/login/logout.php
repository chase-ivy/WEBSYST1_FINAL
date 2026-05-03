<?php
session_start();
require_once __DIR__ . '/../login/auth.php';

logout_user();

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$basePath = dirname($_SERVER['SCRIPT_NAME'], 2);
$loginUrl = $protocol . '://' . $host . $basePath . '/login/login.php';

header('Location: ' . $loginUrl);
exit;
?>
