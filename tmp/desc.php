<?php
require __DIR__ . '/../src/Config/Database.php';
$db = \App\Config\Database::getInstance();
$stmt = $db->query('SHOW COLUMNS FROM profile_permissions');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
