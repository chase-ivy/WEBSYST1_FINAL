<?php
require 'ams/config/config.php';

$cols = $pdo->query('DESCRIBE students')->fetchAll(PDO::FETCH_ASSOC);
$fields = array_column($cols, 'Field');

if (in_array('user_id', $fields)) {
    echo "user_id column already exists\n";
} else {
    echo "user_id column does NOT exist. Need to add it.\n";
    echo "Current columns: " . implode(", ", $fields) . "\n";
}
?>
