<?php
require_once __DIR__ . '/src/Config/Database.php';
use App\Config\Database;

try {
    $db = Database::getInstance();
    $stmt = $db->query("DESCRIBE homologacoes");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($columns, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
