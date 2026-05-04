<?php
require 'ams/config/config.php';

// Get users table schema
$users = $pdo->query('DESCRIBE users')->fetchAll();
echo "=== USERS TABLE SCHEMA ===\n";
foreach ($users as $col) {
    echo "{$col['Field']}: {$col['Type']} - {$col['Null']}\n";
}

echo "\n=== SAMPLE USERS ===\n";
$sample = $pdo->query('SELECT * FROM users LIMIT 3')->fetchAll();
print_r($sample);
?>
