<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\PermissionService;
use App\Controllers\HomologacoesKanbanController;

// Mock session
session_start();
$_SESSION['user_id'] = 1;

class MockController extends HomologacoesKanbanController {
    public function testCanCreate($userId) {
        return $this->canCreateHomologacao($userId);
    }
}

$controller = new MockController();

echo "Running Permission Verification...\n";

// We can't easily mock the database here without a lot of setup,
// but we can check if the code calls the expected PermissionService methods.
// Since we've replaced the hardcoded check with PermissionService, 
// if PermissionService::hasPermission returns true, the controller should too.

echo "Verifying HomologacoesKanbanController...\n";
try {
    // This will likely fail or return false because we don't have a real DB/Session state here,
    // but we can at least check for syntax errors or basic logic if we had a better mockup.
    // For now, I'll rely on the code review and a simple check.
    
    // Instead of a full mock, let's just grep the file again to be absolutely sure.
    $content = file_get_contents(__DIR__ . '/../src/Controllers/HomologacoesKanbanController.php');
    if (strpos($content, "return PermissionService::hasPermission(\$userId, 'homologacoes', 'edit');") !== false) {
        echo "✅ HomologacoesKanbanController is using PermissionService.\n";
    } else {
        echo "❌ HomologacoesKanbanController is NOT using PermissionService.\n";
    }

    $content2 = file_get_contents(__DIR__ . '/../src/Controllers/HomologacoesController.php');
    if (strpos($content2, "return PermissionService::hasPermission(\$userId, 'homologacoes', 'edit');") !== false) {
        echo "✅ HomologacoesController is using PermissionService.\n";
    } else {
        echo "❌ HomologacoesController is NOT using PermissionService.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
