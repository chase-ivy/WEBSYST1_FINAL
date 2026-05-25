<?php
$host = "localhost";
$db   = "gems_db";
$user = "root";
$pass = "";
$char = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$char;port=3306";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit("Connection failed: " . $e->getMessage());
}