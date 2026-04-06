<?php

namespace Tests\Unit\Services;

use App\Services\Homologacoes2Service;
use PDO;
use PHPUnit\Framework\TestCase;

class Homologacoes2ServiceTest extends TestCase
{
    private PDO $db;
    private string $attachmentDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->db->exec('CREATE TABLE profiles (id INTEGER PRIMARY KEY, name TEXT)');
        $this->db->exec('CREATE TABLE users (
            id INTEGER PRIMARY KEY,
            name TEXT NOT NULL,
            email TEXT,
            setor TEXT,
            department TEXT,
            role TEXT,
            profile_id INTEGER,
            status TEXT
        )');
        $this->db->exec('CREATE TABLE homologacoes_2 (
            id INTEGER PRIMARY KEY,
            status TEXT NOT NULL,
            deleted_at TEXT NULL,
            updated_at TEXT NULL,
            homologacao_anterior_id INTEGER NULL,
            produto_original_id INTEGER NULL
        )');
        $this->db->exec('CREATE TABLE homologacoes_2_responsaveis (
            id INTEGER PRIMARY KEY,
            homologacao_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL
        )');
        $this->db->exec('CREATE TABLE homologacoes_2_respostas (
            id INTEGER PRIMARY KEY,
            homologacao_id INTEGER NOT NULL,
            checklist_item_id INTEGER NOT NULL
        )');
        $this->db->exec('CREATE TABLE homologacoes_2_anexos (
            id INTEGER PRIMARY KEY,
            homologacao_id INTEGER NOT NULL,
            caminho TEXT NULL
        )');
        $this->db->exec('CREATE TABLE homologacoes_2_historico (
            id INTEGER PRIMARY KEY,
            homologacao_id INTEGER NOT NULL,
            acao TEXT NOT NULL,
            status_anterior TEXT NULL,
            status_novo TEXT NULL,
            descricao TEXT NOT NULL,
            created_by INTEGER NULL,
            created_by_name TEXT NULL,
            created_at TEXT NOT NULL
        )');

        $this->db->exec("INSERT INTO users (id, name, email, role, status) VALUES (7, 'Admin Teste', 'admin@example.com', 'admin', 'active')");

        $this->attachmentDir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'homologacoes' . DIRECTORY_SEPARATOR . '10';
        $this->recreateAttachmentDirectory();
    }

    protected function tearDown(): void
    {
        if (is_dir($this->attachmentDir)) {
            $this->deleteDirectoryRecursively($this->attachmentDir);
        }

        parent::tearDown();
    }

    public function testCancelHomologacaoExcluiRegistroEVinculosDefinitivamente(): void
    {
        $this->seedHomologacaoBase();

        $service = new Homologacoes2Service($this->db);
        $service->cancelHomologacao(10, true, ['id' => 7]);

        $this->assertSame('0', $this->queryScalar('SELECT COUNT(*) FROM homologacoes_2 WHERE id = 10'));
        $this->assertSame('0', $this->queryScalar('SELECT COUNT(*) FROM homologacoes_2_responsaveis WHERE homologacao_id = 10'));
        $this->assertSame('0', $this->queryScalar('SELECT COUNT(*) FROM homologacoes_2_respostas WHERE homologacao_id = 10'));
        $this->assertSame('0', $this->queryScalar('SELECT COUNT(*) FROM homologacoes_2_anexos WHERE homologacao_id = 10'));
        $this->assertSame('0', $this->queryScalar('SELECT COUNT(*) FROM homologacoes_2_historico WHERE homologacao_id = 10'));
        $this->assertNull($this->queryScalar('SELECT homologacao_anterior_id FROM homologacoes_2 WHERE id = 11'));
        $this->assertNull($this->queryScalar('SELECT produto_original_id FROM homologacoes_2 WHERE id = 12'));
        $this->assertDirectoryDoesNotExist($this->attachmentDir);
    }

    public function testCancelHomologacaoMantemRegistroComStatusCancelado(): void
    {
        $this->seedHomologacaoBase();

        $service = new Homologacoes2Service($this->db);
        $service->cancelHomologacao(10, false, ['id' => 7]);

        $this->assertSame('cancelada', $this->queryScalar('SELECT status FROM homologacoes_2 WHERE id = 10'));
        $this->assertSame('1', $this->queryScalar("SELECT COUNT(*) FROM homologacoes_2_historico WHERE homologacao_id = 10 AND acao = 'cancelamento'"));
        $this->assertDirectoryExists($this->attachmentDir);
    }

    private function seedHomologacaoBase(): void
    {
        $this->db->exec('DELETE FROM homologacoes_2_historico');
        $this->db->exec('DELETE FROM homologacoes_2_anexos');
        $this->db->exec('DELETE FROM homologacoes_2_respostas');
        $this->db->exec('DELETE FROM homologacoes_2_responsaveis');
        $this->db->exec('DELETE FROM homologacoes_2');

        $this->recreateAttachmentDirectory();

        $this->db->exec("INSERT INTO homologacoes_2 (id, status, homologacao_anterior_id, produto_original_id) VALUES (10, 'em_homologacao', NULL, NULL)");
        $this->db->exec("INSERT INTO homologacoes_2 (id, status, homologacao_anterior_id, produto_original_id) VALUES (11, 'aguardando_chegada', 10, NULL)");
        $this->db->exec("INSERT INTO homologacoes_2 (id, status, homologacao_anterior_id, produto_original_id) VALUES (12, 'aguardando_chegada', NULL, 10)");
        $this->db->exec('INSERT INTO homologacoes_2_responsaveis (id, homologacao_id, user_id) VALUES (1, 10, 7)');
        $this->db->exec('INSERT INTO homologacoes_2_respostas (id, homologacao_id, checklist_item_id) VALUES (1, 10, 99)');
        $this->db->exec("INSERT INTO homologacoes_2_anexos (id, homologacao_id, caminho) VALUES (1, 10, 'storage/uploads/homologacoes/10/laudo/arquivo.txt')");
        $this->db->exec("INSERT INTO homologacoes_2_historico (id, homologacao_id, acao, descricao, created_at) VALUES (1, 10, 'criacao', 'criado', CURRENT_TIMESTAMP)");
    }

    private function recreateAttachmentDirectory(): void
    {
        if (is_dir($this->attachmentDir)) {
            $this->deleteDirectoryRecursively($this->attachmentDir);
        }

        $directory = $this->attachmentDir . DIRECTORY_SEPARATOR . 'laudo';
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($directory . DIRECTORY_SEPARATOR . 'arquivo.txt', 'teste');
    }

    private function queryScalar(string $sql): mixed
    {
        $stmt = $this->db->query($sql);

        return $stmt === false ? null : $stmt->fetchColumn();
    }

    private function deleteDirectoryRecursively(string $directory): void
    {
        $items = scandir($directory);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectoryRecursively($path);
                continue;
            }

            @unlink($path);
        }

        @rmdir($directory);
    }
}
