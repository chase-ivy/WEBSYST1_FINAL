<?php
require 'ams/config/config.php';

$cols = $pdo->query('DESCRIBE students')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo $col['Field'] . ": " . $col['Type'] . "\n";
}
?>
