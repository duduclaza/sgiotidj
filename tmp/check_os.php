<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/Config/Database.php';

use App\Config\Database;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
try {
    $dotenv->load();
}
catch (Exception $e) {
}

try {
    $db = Database::getInstance();
    $numero_os = '146691';

    $stmt = $db->prepare("SELECT id, numero_os, numero_serie, status, status_andamento, created_at FROM controle_descartes WHERE numero_os = ?");
    $stmt->execute([$numero_os]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "=== Resultados para OS '{$numero_os}' ===\n";
    if (empty($results)) {
        echo "Nenhum registro encontrado com numero_os = '{$numero_os}'\n";
        
        // Search with LIKE to be sure
        $stmt = $db->prepare("SELECT id, numero_os, numero_serie FROM controle_descartes WHERE numero_os LIKE ?");
        $stmt->execute(["%{$numero_os}%"]);
        $likeResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($likeResults)) {
            echo "\nEncontrado com LIKE:\n";
            foreach ($likeResults as $row) {
                echo "ID: {$row['id']} | OS: '{$row['numero_os']}' | Série: {$row['numero_serie']}\n";
            }
        }
    } else {
        foreach ($results as $row) {
            echo "ID: {$row['id']} | OS: {$row['numero_os']} | Série: {$row['numero_serie']} | Status: {$row['status']} | Andamento: " . ($row['status_andamento'] ?? 'N/A') . " | Criado em: {$row['created_at']}\n";
        }
    }

}
catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
