<?php
require 'ams/config/config.php';

// Check users table
echo "=== USERS TABLE SCHEMA ===\n";
$users = $pdo->query('DESCRIBE users')->fetchAll();
foreach ($users as $col) {
    echo $col['Field'] . ": " . $col['Type'] . " (Null: " . $col['Null'] . ")\n";
}

// Check students table
echo "\n=== STUDENTS TABLE SCHEMA ===\n";
$students = $pdo->query('DESCRIBE students')->fetchAll();
foreach ($students as $col) {
    echo $col['Field'] . ": " . $col['Type'] . " (Null: " . $col['Null'] . ")\n";
}

echo "\n=== SAMPLE DATA ===\n";
$sample_user = $pdo->query('SELECT * FROM users LIMIT 1')->fetch();
echo "Sample user: " . json_encode($sample_user) . "\n";

$sample_student = $pdo->query('SELECT * FROM students LIMIT 1')->fetch();
echo "Sample student keys: " . implode(", ", array_keys($sample_student)) . "\n";
?>
