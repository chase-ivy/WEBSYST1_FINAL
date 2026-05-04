<?php
require 'ams/config/config.php';

try {
    // Check if user_id column exists
    $cols = $pdo->query('DESCRIBE students')->fetchAll(PDO::FETCH_ASSOC);
    $fields = array_column($cols, 'Field');
    
    if (!in_array('user_id', $fields)) {
        // Add user_id column as nullable foreign key
        $pdo->exec('ALTER TABLE students ADD COLUMN user_id INT NULL UNIQUE AFTER sex');
        echo "✓ Added user_id column to students table\n";
    } else {
        echo "✓ user_id column already exists\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
