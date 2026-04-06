<?php

namespace App\Services;

use PDO;
use Throwable;

class Homologacoes2Service
{
    public function __construct(private PDO $db)
    {
    }

    public function getCurrentUser(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.name, u.email, u.setor, u.department, u.role, u.profile_id, p.name AS profile_name
             FROM users u
             LEFT JOIN profiles p ON p.id = u.profile_id
             WHERE u.id = ?"
        );
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ? $this->mapUser($user) : null;
    }

    public function getActiveUsers(): array
    {
        $stmt = $this->db->query(
            "SELECT u.id, u.name, u.email, u.setor, u.department, u.role, u.profile_id, p.name AS profile_name
             FROM users u
             LEFT JOIN profiles p ON p.id = u.profile_id
             WHERE COALESCE(u.status, 'active') = 'active'
             ORDER BY u.name"
        );

        return array_map(fn (array $user) => $this->mapUser($user), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getTiposProduto(): array
    {
        $stmt = $this->db->query(
            "SELECT id, nome
             FROM homologacoes_2_tipos
             WHERE ativo = 1
             ORDER BY nome"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFornecedores(): array
    {
        try {
            $stmt = $this->db->query("SELECT id, nome FROM fornecedores ORDER BY nome");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable) {
            return [];
        }
    }

    public function getClientes(): array
    {
        try {
            $stmt = $this->db->query("SELECT id, codigo, nome FROM clientes ORDER BY nome");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable) {
            return [];
        }
    }

    public function getChecklistMapByType(): array
    {
        $stmt = $this->db->query(
            "SELECT c.id, c.titulo, t.nome AS tipo_nome, i.chave, i.label, i.ordem
             FROM homologacoes_2_checklists c
             LEFT JOIN homologacoes_2_tipos t ON t.id = c.tipo_id
             LEFT JOIN homologacoes_2_checklist_itens i ON i.checklist_id = c.id
             ORDER BY t.nome, c.id, i.ordem, i.id"
        );

        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (empty($row['tipo_nome']) || empty($row['chave'])) {
                continue;
            }

            $map[$row['tipo_nome']][$row['chave']] = $row['label'];
        }

        return $map;
    }

    public function getChecklistCards(): array
    {
        $stmt = $this->db->query(
            "SELECT c.id, c.titulo, c.tipo_id, t.nome AS tipo_produto_nome, c.created_at
             FROM homologacoes_2_checklists c
             LEFT JOIN homologacoes_2_tipos t ON t.id = c.tipo_id
             ORDER BY c.created_at DESC, c.id DESC"
        );

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) {
            return [];
        }

        $ids = array_column($rows, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmtItens = $this->db->prepare(
            "SELECT checklist_id, chave, label, ordem
             FROM homologacoes_2_checklist_itens
             WHERE checklist_id IN ($placeholders)
             ORDER BY checklist_id, ordem, id"
        );
        $stmtItens->execute($ids);

        $itensPorChecklist = [];
        foreach ($stmtItens->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $itensPorChecklist[(int) $item['checklist_id']][] = $item['label'];
        }

        foreach ($rows as &$row) {
            $row['itens'] = $itensPorChecklist[(int) $row['id']] ?? [];
        }

        return $rows;
    }

    public function getHomologacoes(): array
    {
        $stmt = $this->db->query(
            "SELECT h.*,
                    t.nome AS tipo_equipamento,
                    COALESCE(f.nome, h.fornecedor_nome) AS fornecedor,
                    cli.nome AS cliente_nome,
                    u.name AS criador_nome,
                    ur.name AS recebido_por_nome
             FROM homologacoes_2 h
             LEFT JOIN homologacoes_2_tipos t ON t.id = h.tipo_id
             LEFT JOIN fornecedores f ON f.id = h.fornecedor_id
             LEFT JOIN clientes cli ON cli.id = h.cliente_id
             LEFT JOIN users u ON u.id = h.criado_por
             LEFT JOIN users ur ON ur.id = h.recebido_por
             WHERE h.deleted_at IS NULL
             ORDER BY h.created_at DESC, h.id DESC"
        );

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) {
            return [];
        }

        $ids = array_map('intval', array_column($rows, 'id'));
        $responsaveis = $this->getResponsaveisMap($ids);
        $versions = $this->buildVersionMap($rows);
        $anexosResumo = $this->getAnexosResumoMap($ids);

        foreach ($rows as &$row) {
            $row['responsaveis'] = $responsaveis[(int) $row['id']] ?? [];
            $row['dados_comercial'] = [
                'vendedor_nome' => $row['vendedor_nome'] ?? '',
                'vendedor_email' => $row['vendedor_email'] ?? '',
                'supervisor_email' => $row['supervisor_email'] ?? '',
            ];

            if (!empty($row['cliente_nome']) && empty($row['nome_cliente'])) {
                $row['nome_cliente'] = $row['cliente_nome'];
            }

            $row['versao_numero'] = $versions[(int) $row['id']] ?? 1;
            $row['logistica_anexos_count'] = $anexosResumo[(int) $row['id']]['logistica'] ?? 0;
            $row['laudo_anexos_count'] = $anexosResumo[(int) $row['id']]['laudo'] ?? 0;
            if ($row['logistica_anexos_count'] > 0 && empty($row['foto_carga'])) {
                $row['foto_carga'] = 'anexos_blob';
            }
        }

        return $rows;
    }

    public function getHomologacao(int $id): ?array
    {
        foreach ($this->getHomologacoes() as $homologacao) {
            if ((int) $homologacao['id'] === $id) {
                $homologacao['logistica_anexos'] = $this->getAnexosForView((int) $homologacao['id'], 'logistica');
                $homologacao['laudo_anexos'] = $this->getAnexosForView((int) $homologacao['id'], 'laudo');
                return $homologacao;
            }
        }

        return null;
    }

    public function getVersionMap(array $homologacoes): array
    {
        return $this->buildVersionMap($homologacoes);
    }

    public function buildUsersLookup(array $users): array
    {
        $lookup = [];
        foreach ($users as $user) {
            $lookup[(int) $user['id']] = $user;
        }

        return $lookup;
    }

    public function buildHomologacoesLookup(array $homologacoes): array
    {
        $lookup = [];
        foreach ($homologacoes as $homologacao) {
            $lookup[(int) $homologacao['id']] = $homologacao;
        }

        return $lookup;
    }

    public function getDashboardData(array $filters = []): array
    {
        $homologacoes = $this->getHomologacoes();
        $filtroStatus = trim((string) ($filters['status'] ?? ''));
        $filtroTipo = trim((string) ($filters['tipo'] ?? ''));

        $totais = [
            'total' => count($homologacoes),
            'aguardando' => count(array_filter($homologacoes, fn ($h) => $h['status'] === 'aguardando_chegada')),
            'em_andamento' => count(array_filter($homologacoes, fn ($h) => in_array($h['status'], ['item_recebido', 'em_homologacao'], true))),
            'concluidas' => count(array_filter($homologacoes, fn ($h) => $h['status'] === 'concluida')),
            'canceladas' => count(array_filter($homologacoes, fn ($h) => $h['status'] === 'cancelada')),
        ];

        $lista = array_values(array_filter($homologacoes, function (array $h) use ($filtroStatus, $filtroTipo) {
            if ($filtroStatus !== '' && $h['status'] !== $filtroStatus) {
                return false;
            }

            if ($filtroTipo !== '' && $h['tipo_equipamento'] !== $filtroTipo) {
                return false;
            }

            return true;
        }));

        $alertas = [];
        foreach ($homologacoes as $h) {
            if ($h['status'] === 'aguardando_chegada' && !empty($h['data_prevista_chegada'])) {
                $diasChegada = calcularDiasRestantes($h['data_prevista_chegada']);
                if ($diasChegada !== null && $diasChegada <= (int) ($h['dias_antecedencia_notif'] ?? 3)) {
                    $alertas[] = [
                        'tipo' => 'logistica',
                        'msg' => "A homologação <strong>{$h['codigo']}</strong> ({$h['modelo']}) tem chegada prevista para daqui a <strong>{$diasChegada} dias</strong>.",
                    ];
                }
            }

            if (!in_array($h['status'], ['concluida', 'cancelada'], true) && !empty($h['data_vencimento'])) {
                $diasVenc = calcularDiasRestantes($h['data_vencimento']);
                if ($diasVenc !== null && $diasVenc <= (int) ($h['dias_vencimento_notif'] ?? 5)) {
                    $setorTxt = ucfirst($h['setor_responsavel'] ?? 'tecnico');
                    if ($diasVenc < 0) {
                        $msg = "A homologação <strong>{$h['codigo']}</strong> está atrasada há <strong>" . abs($diasVenc) . " dia(s)</strong>. Equipe <strong>{$setorTxt}</strong> em cobrança.";
                    } else {
                        $msg = "Prazo da homologação <strong>{$h['codigo']}</strong>: vence em <strong>{$diasVenc} dia(s)</strong> para o setor <strong>{$setorTxt}</strong>.";
                    }

                    $alertas[] = ['tipo' => 'vencimento', 'msg' => $msg];
                }
            }
        }

        return compact('homologacoes', 'totais', 'lista', 'alertas', 'filtroStatus', 'filtroTipo');
    }

    public function getQueueData(int $userId): array
    {
        $homologacoes = $this->getHomologacoes();

        $minhaFila = array_values(array_filter($homologacoes, function (array $h) use ($userId) {
            if (!in_array($userId, $h['responsaveis'], true)) {
                return false;
            }

            return in_array($h['status'], ['aguardando_chegada', 'item_recebido', 'em_homologacao'], true);
        }));

        $historico = array_values(array_filter($homologacoes, function (array $h) use ($userId) {
            return in_array($userId, $h['responsaveis'], true) && in_array($h['status'], ['concluida', 'cancelada'], true);
        }));

        return compact('homologacoes', 'minhaFila', 'historico');
    }

    public function getLogisticsData(): array
    {
        $homologacoes = $this->getHomologacoes();
        $aguardando = array_values(array_filter($homologacoes, fn ($h) => $h['status'] === 'aguardando_chegada'));
        $recebidos = array_values(array_filter($homologacoes, fn ($h) => $h['status'] === 'item_recebido'));

        return compact('homologacoes', 'aguardando', 'recebidos');
    }

    public function getMonitoringData(): array
    {
        return ['homologacoes' => $this->getHomologacoes()];
    }

    public function getManagementData(): array
    {
        $tipos = $this->getTiposProduto();
        $checklists = [];
        $checklistsPorTipo = [];

        foreach ($this->getChecklistCards() as $checklist) {
            if (!empty($checklist['tipo_produto_nome'])) {
                $checklistsPorTipo[$checklist['tipo_produto_nome']] = [];
                foreach ($checklist['itens'] as $label) {
                    $checklistsPorTipo[$checklist['tipo_produto_nome']][$this->normalizeChecklistKey($label)] = $label;
                }
                continue;
            }

            $checklists[] = $checklist;
        }

        return compact('tipos', 'checklists', 'checklistsPorTipo');
    }

    public function getCreateFormData(array $currentUser): array
    {
        $tiposReais = $this->getTiposProduto();
        $fornecedoresReais = $this->getFornecedores();
        $ultimasHomologacoes = $this->getUltimasHomologacoesPorProduto(
            array_values(array_filter($this->getHomologacoes(), fn ($h) => $h['status'] === 'concluida'))
        );
        $tipoHomologacao = $currentUser['perfil'] === 'qualidade' ? 'rehomologacao' : 'primeira';

        return compact('tiposReais', 'fornecedoresReais', 'ultimasHomologacoes', 'tipoHomologacao');
    }

    public function createHomologacao(array $input, int $userId): int
    {
        $tipoId = (int) ($input['tipo_id'] ?? 0);
        $tipoHomologacao = ($input['tipo_homologacao'] ?? 'primeira') === 'rehomologacao' ? 'rehomologacao' : 'primeira';
        $fornecedorId = !empty($input['fornecedor']) ? (int) $input['fornecedor'] : null;
        $fornecedorNome = $this->resolveFornecedorNome($fornecedorId);
        $responsaveis = array_values(array_unique(array_map('intval', $input['responsaveis'] ?? [])));
        $homologacaoAnteriorId = !empty($input['homologacao_anterior_id']) ? (int) $input['homologacao_anterior_id'] : null;

        if ($tipoId <= 0) {
            throw new \RuntimeException('Selecione o tipo de equipamento.');
        }

        if ($fornecedorNome === '') {
            throw new \RuntimeException('Selecione um fornecedor válido.');
        }

        if (empty($responsaveis)) {
            throw new \RuntimeException('Selecione ao menos um responsável.');
        }

        if ($tipoHomologacao === 'rehomologacao' && !$homologacaoAnteriorId) {
            throw new \RuntimeException('A rehomologação precisa apontar uma homologação anterior.');
        }

        $this->db->beginTransaction();
        try {
            $publicToken = bin2hex(random_bytes(16));
            $stmt = $this->db->prepare(
                "INSERT INTO homologacoes_2 (
                    codigo, titulo, tipo_id, descricao, fornecedor_id, fornecedor_nome, modelo, numero_serie,
                    quantidade, tipo_aquisicao, tipo_homologacao, homologacao_anterior_id, produto_original_id,
                    data_prevista_chegada, dias_antecedencia_notif, data_vencimento, dias_vencimento_notif,
                    setor_responsavel, vendedor_nome, vendedor_email, supervisor_email, notificar_envolvidos,
                    status, criado_por, data_criacao, public_token
                 ) VALUES (
                    '', :titulo, :tipo_id, :descricao, :fornecedor_id, :fornecedor_nome, :modelo, :numero_serie,
                    :quantidade, :tipo_aquisicao, :tipo_homologacao, :homologacao_anterior_id, NULL,
                    :data_prevista_chegada, :dias_antecedencia_notif, :data_vencimento, :dias_vencimento_notif,
                    :setor_responsavel, :vendedor_nome, :vendedor_email, :supervisor_email, :notificar_envolvidos,
                    'aguardando_chegada', :criado_por, CURDATE(), :public_token
                 )"
            );
            $stmt->execute([
                ':titulo' => trim((string) ($input['titulo'] ?? '')),
                ':tipo_id' => $tipoId,
                ':descricao' => trim((string) ($input['descricao'] ?? '')),
                ':fornecedor_id' => $fornecedorId ?: null,
                ':fornecedor_nome' => $fornecedorNome,
                ':modelo' => trim((string) ($input['modelo'] ?? '')),
                ':numero_serie' => trim((string) ($input['numero_serie'] ?? '')) ?: null,
                ':quantidade' => max(1, (int) ($input['quantidade'] ?? 1)),
                ':tipo_aquisicao' => ($input['tipo_aquisicao'] ?? 'comprado') === 'emprestado' ? 'emprestado' : 'comprado',
                ':tipo_homologacao' => $tipoHomologacao,
                ':homologacao_anterior_id' => $homologacaoAnteriorId,
                ':data_prevista_chegada' => $input['data_prevista_chegada'] ?: null,
                ':dias_antecedencia_notif' => max(1, (int) ($input['dias_antecedencia_notif'] ?? 3)),
                ':data_vencimento' => $input['data_vencimento'] ?: null,
                ':dias_vencimento_notif' => max(1, (int) ($input['dias_vencimento_notif'] ?? 5)),
                ':setor_responsavel' => $this->sanitizeSetorResponsavel($input['setor_responsavel'] ?? 'tecnico'),
                ':vendedor_nome' => trim((string) ($input['vendedor_nome'] ?? '')) ?: null,
                ':vendedor_email' => trim((string) ($input['vendedor_email'] ?? '')) ?: null,
                ':supervisor_email' => trim((string) ($input['supervisor_email'] ?? '')) ?: null,
                ':notificar_envolvidos' => !empty($input['notificar_envolvidos']) ? 1 : 0,
                ':criado_por' => $userId,
                ':public_token' => $publicToken,
            ]);

            $id = (int) $this->db->lastInsertId();
            $produtoOriginalId = $id;

            if ($tipoHomologacao === 'rehomologacao') {
                $anterior = $this->getHomologacaoBase($homologacaoAnteriorId);
                if (!$anterior) {
                    throw new \RuntimeException('Homologação anterior não encontrada.');
                }
                $produtoOriginalId = (int) ($anterior['produto_original_id'] ?: $anterior['id']);
            }

            $codigo = $this->generateCodigo($id, $produtoOriginalId, $tipoHomologacao);

            $stmt = $this->db->prepare("UPDATE homologacoes_2 SET codigo = ?, produto_original_id = ? WHERE id = ?");
            $stmt->execute([$codigo, $produtoOriginalId, $id]);

            $this->syncResponsaveis($id, $responsaveis);
            $this->registrarHistorico($id, 'criada', null, 'aguardando_chegada', 'Homologação aberta no sistema.', $userId);

            $this->db->commit();

            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function registerRecebimento(int $id, array $input, array $user, ?array $files = null): void
    {
        $homologacao = $this->getHomologacaoBase($id);
        if (!$homologacao) {
            throw new \RuntimeException('Homologação não encontrada.');
        }

        $stmt = $this->db->prepare(
            "UPDATE homologacoes_2
             SET status = 'item_recebido',
                 data_recebimento = :data_recebimento,
                 observacoes_logistica = :observacoes_logistica,
                 foto_carga = :foto_carga,
                 recebido_por = :recebido_por,
                 updated_at = NOW()
             WHERE id = :id"
        );
        $uploadedImages = $this->normalizeUploadedFileCount($files['logistica_anexos'] ?? null);
        $stmt->execute([
            ':data_recebimento' => $input['data_recebimento'] ?: date('Y-m-d'),
            ':observacoes_logistica' => trim((string) ($input['observacoes_logistica'] ?? '')) ?: null,
            ':foto_carga' => $uploadedImages > 0 ? 'anexos_blob' : (trim((string) ($input['foto_carga'] ?? '')) ?: null),
            ':recebido_por' => (int) $user['id'],
            ':id' => $id,
        ]);

        $this->storeAttachments($id, $files['logistica_anexos'] ?? null, 'logistica', (int) $user['id']);

        $this->registrarHistorico($id, 'recebimento', $homologacao['status'], 'item_recebido', 'Recebimento físico registrado pela logística.', (int) $user['id']);
    }

    public function startHomologacao(int $id, array $input, array $user): void
    {
        $homologacao = $this->getHomologacaoBase($id);
        if (!$homologacao) {
            throw new \RuntimeException('Homologação não encontrada.');
        }

        $clienteId = null;
        $nomeCliente = trim((string) ($input['nome_cliente'] ?? ''));
        if ($nomeCliente !== '') {
            $clienteId = $this->resolveClienteId($nomeCliente);
        }

        $stmt = $this->db->prepare(
            "UPDATE homologacoes_2
             SET status = 'em_homologacao',
                 local_homologacao = :local_homologacao,
                 data_inicio_homologacao = :data_inicio_homologacao,
                 cliente_id = :cliente_id,
                 nome_cliente = :nome_cliente,
                 data_instalacao_cliente = :data_instalacao_cliente,
                 updated_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute([
            ':local_homologacao' => ($input['local_homologacao'] ?? 'laboratorio') === 'cliente' ? 'cliente' : 'laboratorio',
            ':data_inicio_homologacao' => $input['data_inicio_homologacao'] ?: date('Y-m-d'),
            ':cliente_id' => $clienteId,
            ':nome_cliente' => $nomeCliente ?: null,
            ':data_instalacao_cliente' => $input['data_instalacao_cliente'] ?: null,
            ':id' => $id,
        ]);

        $this->registrarHistorico($id, 'inicio_testes', $homologacao['status'], 'em_homologacao', 'Equipe técnica iniciou os testes.', (int) $user['id']);
    }

    public function saveChecklistDraft(int $id, array $input, array $user): void
    {
        $homologacao = $this->getHomologacao($id);
        if (!$homologacao) {
            throw new \RuntimeException('Homologação não encontrada.');
        }

        $this->persistChecklistAnswers(
            $id,
            (int) $homologacao['tipo_id'],
            $input['checklist'] ?? [],
            (int) $user['id'],
            $user['nome'],
            'interno'
        );

        $observacao = $this->appendHistoricoTexto(
            $homologacao['observacoes_checklist'] ?? '',
            trim((string) ($input['nova_observacao'] ?? '')),
            $user['nome']
        );

        $stmt = $this->db->prepare("UPDATE homologacoes_2 SET observacoes_checklist = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$observacao ?: null, $id]);

        $this->registrarHistorico($id, 'checklist_rascunho', $homologacao['status'], $homologacao['status'], 'Checklist salvo em rascunho.', (int) $user['id']);
    }

    public function finalizarHomologacao(int $id, array $input, array $files, array $user): void
    {
        $homologacao = $this->getHomologacao($id);
        if (!$homologacao) {
            throw new \RuntimeException('Homologação não encontrada.');
        }

        $this->db->beginTransaction();
        try {
            $respostas = $this->persistChecklistAnswers(
                $id,
                (int) $homologacao['tipo_id'],
                $input['checklist'] ?? [],
                (int) $user['id'],
                $user['nome'],
                'interno'
            );

            $checklistItens = $this->getChecklistItemsByTipoId((int) $homologacao['tipo_id']);
            $temPendente = false;
            foreach ($checklistItens as $item) {
                $valor = $respostas[$item['chave']] ?? null;
                if (!in_array($valor, ['1', '0'], true)) {
                    $temPendente = true;
                    break;
                }
            }

            $resultado = $this->sanitizeResultado($input['resultado'] ?? '');
            if ($temPendente) {
                $resultado = 'pendente';
            }

            $statusNovo = $resultado === 'pendente' ? 'em_homologacao' : 'concluida';
            $observacoesChecklist = $this->appendHistoricoTexto(
                $homologacao['observacoes_checklist'] ?? '',
                trim((string) ($input['nova_observacao'] ?? '')),
                $user['nome']
            );
            $parecerFinal = $this->appendHistoricoTexto(
                $homologacao['parecer_final'] ?? '',
                trim((string) ($input['novo_parecer_final'] ?? '')),
                $user['nome']
            );

            $stmt = $this->db->prepare(
                "UPDATE homologacoes_2
                 SET status = :status,
                     data_fim_homologacao = :data_fim_homologacao,
                     resultado = :resultado,
                     parecer_final = :parecer_final,
                     observacoes_checklist = :observacoes_checklist,
                     updated_at = NOW()
                 WHERE id = :id"
            );
            $stmt->execute([
                ':status' => $statusNovo,
                ':data_fim_homologacao' => $input['data_fim_homologacao'] ?: date('Y-m-d'),
                ':resultado' => $resultado,
                ':parecer_final' => $parecerFinal ?: null,
                ':observacoes_checklist' => $observacoesChecklist ?: null,
                ':id' => $id,
            ]);

            $this->storeAttachments($id, $files['laudo_anexos'] ?? null, 'laudo', (int) $user['id']);
            $this->registrarHistorico($id, 'finalizacao', $homologacao['status'], $statusNovo, 'Homologação atualizada com veredito final.', (int) $user['id']);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function cancelHomologacao(int $id, bool $excluirDefinitivo, array $user): void
    {
        $homologacao = $this->getHomologacaoBase($id);
        if (!$homologacao) {
            throw new \RuntimeException('Homologação não encontrada.');
        }

        if ($excluirDefinitivo) {
            $this->deleteHomologacaoPermanently($id);
            return;
        }

        $stmt = $this->db->prepare("UPDATE homologacoes_2 SET status = 'cancelada', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$id]);
        $this->registrarHistorico($id, 'cancelamento', $homologacao['status'], 'cancelada', 'Processo cancelado e mantido em histórico.', (int) $user['id']);
    }

    private function deleteHomologacaoPermanently(int $id): void
    {
        $this->db->beginTransaction();

        try {
            $this->detachLinkedHomologacoes($id);

            foreach ([
                'homologacoes_2_respostas',
                'homologacoes_2_responsaveis',
                'homologacoes_2_anexos',
                'homologacoes_2_historico',
            ] as $table) {
                $stmt = $this->db->prepare("DELETE FROM {$table} WHERE homologacao_id = ?");
                $stmt->execute([$id]);
            }

            $stmt = $this->db->prepare("DELETE FROM homologacoes_2 WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }

        $this->purgeAttachmentDirectory($id);
    }

    private function detachLinkedHomologacoes(int $id): void
    {
        foreach (['homologacao_anterior_id', 'produto_original_id'] as $column) {
            $stmt = $this->db->prepare("UPDATE homologacoes_2 SET {$column} = NULL WHERE {$column} = ?");
            $stmt->execute([$id]);
        }
    }

    public function addTipoProduto(string $nome): void
    {
        $stmt = $this->db->prepare("INSERT INTO homologacoes_2_tipos (nome, ativo) VALUES (?, 1)");
        $stmt->execute([$nome]);
    }

    public function deleteTipoProduto(int $id): void
    {
        $stmtUso = $this->db->prepare("SELECT COUNT(*) FROM homologacoes_2 WHERE tipo_id = ? AND deleted_at IS NULL");
        $stmtUso->execute([$id]);
        if ((int) $stmtUso->fetchColumn() > 0) {
            throw new \RuntimeException('Este tipo já possui homologações vinculadas e não pode ser excluído.');
        }

        $stmt = $this->db->prepare("DELETE FROM homologacoes_2_tipos WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function saveChecklist(array $payload, int $userId): void
    {
        $acao = $payload['acao_checklist'] ?? 'adicionar';
        $tipoNome = trim((string) ($payload['tipo_produto_nome'] ?? ''));
        $titulo = trim((string) ($payload['titulo'] ?? ''));
        $itens = array_values(array_filter(array_map(fn ($item) => trim((string) $item), $payload['itens'] ?? [])));

        if (empty($itens)) {
            throw new \RuntimeException('Informe ao menos um item no checklist.');
        }

        if ($acao === 'editar_existente' && $tipoNome === '') {
            throw new \RuntimeException('Tipo do checklist não informado.');
        }

        if ($acao === 'editar_custom') {
            $id = (int) ($payload['id_checklist'] ?? 0);
            if ($id <= 0) {
                throw new \RuntimeException('Checklist inválido.');
            }

            $this->upsertChecklistItens($id, $titulo !== '' ? $titulo : 'Checklist', $tipoNome, $itens, $userId);
            return;
        }

        if ($acao === 'adicionar' && $tipoNome === '') {
            $stmt = $this->db->prepare(
                "INSERT INTO homologacoes_2_checklists (titulo, tipo_id, created_by, created_at)
                 VALUES (?, NULL, ?, NOW())"
            );
            $stmt->execute([$titulo !== '' ? $titulo : 'Checklist personalizado', $userId]);
            $checklistId = (int) $this->db->lastInsertId();
            $this->replaceChecklistItens($checklistId, $itens);
            return;
        }

        $tipoId = $this->resolveTipoIdByName($tipoNome);
        if (!$tipoId) {
            throw new \RuntimeException('Tipo de produto não encontrado.');
        }

        $stmt = $this->db->prepare("SELECT id FROM homologacoes_2_checklists WHERE tipo_id = ?");
        $stmt->execute([$tipoId]);
        $existingId = (int) $stmt->fetchColumn();

        if ($existingId > 0) {
            $this->upsertChecklistItens($existingId, $titulo !== '' ? $titulo : ('Checklist de ' . $tipoNome), $tipoNome, $itens, $userId);
            return;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO homologacoes_2_checklists (titulo, tipo_id, created_by, created_at)
             VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute([$titulo !== '' ? $titulo : ('Checklist de ' . $tipoNome), $tipoId, $userId]);
        $checklistId = (int) $this->db->lastInsertId();
        $this->replaceChecklistItens($checklistId, $itens);
    }

    public function deleteChecklist(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM homologacoes_2_checklists WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function getPublicHomologacao(string $token): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT h.*, t.nome AS tipo_equipamento, COALESCE(f.nome, h.fornecedor_nome) AS fornecedor
             FROM homologacoes_2 h
             LEFT JOIN homologacoes_2_tipos t ON t.id = h.tipo_id
             LEFT JOIN fornecedores f ON f.id = h.fornecedor_id
             WHERE h.public_token = ? AND h.deleted_at IS NULL"
        );
        $stmt->execute([$token]);
        $homologacao = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$homologacao) {
            return null;
        }

        return $homologacao;
    }

    public function submitPublicChecklist(string $token, array $input): void
    {
        $homologacao = $this->getPublicHomologacao($token);
        if (!$homologacao) {
            throw new \RuntimeException('Homologação não encontrada.');
        }

        $nomeAvaliador = trim((string) ($input['nome_avaliador'] ?? 'Avaliador Externo'));
        $observacoes = trim((string) ($input['observacoes_checklist'] ?? ''));

        $respostas = $this->persistChecklistAnswers(
            (int) $homologacao['id'],
            (int) $homologacao['tipo_id'],
            $input['checklist'] ?? [],
            null,
            $nomeAvaliador,
            'publico'
        );

        $hasPass = in_array('1', $respostas, true);
        $hasFail = in_array('0', $respostas, true);

        if ($hasPass && !$hasFail) {
            $resultado = 'aprovado';
        } elseif (!$hasPass && $hasFail) {
            $resultado = 'reprovado';
        } else {
            $resultado = 'aprovado_restricoes';
        }

        $laudo = "Avaliação externa realizada via link público.\nAvaliador: {$nomeAvaliador}\nObservações: " . ($observacoes !== '' ? $observacoes : 'Nenhuma registrada.');

        $stmt = $this->db->prepare(
            "UPDATE homologacoes_2
             SET status = 'concluida',
                 resultado = :resultado,
                 parecer_final = :parecer_final,
                 observacoes_checklist = :observacoes_checklist,
                 data_fim_homologacao = CURDATE(),
                 updated_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute([
            ':resultado' => $resultado,
            ':parecer_final' => $laudo,
            ':observacoes_checklist' => $observacoes ?: null,
            ':id' => $homologacao['id'],
        ]);

        $this->registrarHistorico((int) $homologacao['id'], 'avaliacao_publica', $homologacao['status'], 'concluida', 'Checklist concluído por avaliador externo.', null, $nomeAvaliador);
    }

    public function getChecklistItemsByTipoId(int $tipoId): array
    {
        $stmt = $this->db->prepare(
            "SELECT i.id, i.chave, i.label, i.ordem
             FROM homologacoes_2_checklists c
             INNER JOIN homologacoes_2_checklist_itens i ON i.checklist_id = c.id
             WHERE c.tipo_id = ?
             ORDER BY i.ordem, i.id"
        );
        $stmt->execute([$tipoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getChecklistValuesForHomologacao(int $homologacaoId): array
    {
        $stmt = $this->db->prepare(
            "SELECT i.chave, r.resultado
             FROM homologacoes_2_respostas r
             INNER JOIN homologacoes_2_checklist_itens i ON i.id = r.checklist_item_id
             WHERE r.homologacao_id = ?"
        );
        $stmt->execute([$homologacaoId]);

        $values = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $values[$row['chave']] = $row['resultado'];
        }

        return $values;
    }

    private function persistChecklistAnswers(
        int $homologacaoId,
        int $tipoId,
        array $rawAnswers,
        ?int $userId,
        string $nomeAvaliador,
        string $origem
    ): array {
        $itens = $this->getChecklistItemsByTipoId($tipoId);
        $itensPorChave = [];
        foreach ($itens as $item) {
            $itensPorChave[$item['chave']] = $item;
        }

        $normalized = [];
        foreach ($rawAnswers as $chave => $valor) {
            if (!isset($itensPorChave[$chave])) {
                continue;
            }

            $resultado = match ((string) $valor) {
                '1', 'pass', 'true' => '1',
                '0', 'fail', 'false' => '0',
                default => 'pendente',
            };
            $normalized[$chave] = $resultado;

            $stmt = $this->db->prepare(
                "INSERT INTO homologacoes_2_respostas (
                    homologacao_id, checklist_item_id, resultado, avaliador_nome, origem, respondido_por, created_at, updated_at
                 ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                    resultado = VALUES(resultado),
                    avaliador_nome = VALUES(avaliador_nome),
                    origem = VALUES(origem),
                    respondido_por = VALUES(respondido_por),
                    updated_at = NOW()"
            );
            $stmt->execute([
                $homologacaoId,
                $itensPorChave[$chave]['id'],
                $resultado,
                $nomeAvaliador,
                $origem,
                $userId,
            ]);
        }

        return $normalized;
    }

    private function getResponsaveisMap(array $homologacaoIds): array
    {
        if (empty($homologacaoIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($homologacaoIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT homologacao_id, user_id
             FROM homologacoes_2_responsaveis
             WHERE homologacao_id IN ($placeholders)"
        );
        $stmt->execute($homologacaoIds);

        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $map[(int) $row['homologacao_id']][] = (int) $row['user_id'];
        }

        return $map;
    }

    private function buildVersionMap(array $rows): array
    {
        $lookup = [];
        foreach ($rows as $row) {
            $lookup[(int) $row['id']] = $row;
        }

        $versions = [];
        foreach ($rows as $row) {
            $currentId = (int) $row['id'];
            $versao = 1;
            $cursor = (int) ($row['homologacao_anterior_id'] ?? 0);

            while ($cursor > 0 && isset($lookup[$cursor])) {
                $versao++;
                $cursor = (int) ($lookup[$cursor]['homologacao_anterior_id'] ?? 0);
            }

            $versions[$currentId] = $versao;
        }

        return $versions;
    }

    private function getUltimasHomologacoesPorProduto(array $homologacoes): array
    {
        $produtos = [];
        foreach ($homologacoes as $h) {
            if (($h['resultado'] ?? null) !== 'aprovado') {
                continue;
            }

            $chave = (int) (($h['tipo_homologacao'] ?? 'primeira') === 'primeira' ? $h['id'] : ($h['produto_original_id'] ?: $h['id']));
            if (!isset($produtos[$chave]) || strtotime((string) $h['created_at']) > strtotime((string) $produtos[$chave]['created_at'])) {
                $produtos[$chave] = $h;
            }
        }

        return array_values($produtos);
    }

    private function syncResponsaveis(int $homologacaoId, array $responsaveis): void
    {
        $stmtDelete = $this->db->prepare("DELETE FROM homologacoes_2_responsaveis WHERE homologacao_id = ?");
        $stmtDelete->execute([$homologacaoId]);

        $stmtInsert = $this->db->prepare(
            "INSERT INTO homologacoes_2_responsaveis (homologacao_id, user_id, created_at)
             VALUES (?, ?, NOW())"
        );

        foreach ($responsaveis as $userId) {
            $stmtInsert->execute([$homologacaoId, $userId]);
        }
    }

    private function replaceChecklistItens(int $checklistId, array $itens): void
    {
        $stmtDelete = $this->db->prepare("DELETE FROM homologacoes_2_checklist_itens WHERE checklist_id = ?");
        $stmtDelete->execute([$checklistId]);

        $stmtInsert = $this->db->prepare(
            "INSERT INTO homologacoes_2_checklist_itens (checklist_id, chave, label, ordem, created_at)
             VALUES (?, ?, ?, ?, NOW())"
        );

        foreach (array_values($itens) as $index => $label) {
            $stmtInsert->execute([
                $checklistId,
                $this->normalizeChecklistKey($label),
                $label,
                $index + 1,
            ]);
        }
    }

    private function upsertChecklistItens(int $checklistId, string $titulo, string $tipoNome, array $itens, int $userId): void
    {
        $tipoId = $tipoNome !== '' ? $this->resolveTipoIdByName($tipoNome) : null;
        $stmt = $this->db->prepare(
            "UPDATE homologacoes_2_checklists
             SET titulo = ?, tipo_id = ?, created_by = ?, updated_at = NOW()
             WHERE id = ?"
        );
        $stmt->execute([$titulo, $tipoId, $userId, $checklistId]);
        $this->replaceChecklistItens($checklistId, $itens);
    }

    private function resolveTipoIdByName(string $nome): ?int
    {
        if ($nome === '') {
            return null;
        }

        $stmt = $this->db->prepare("SELECT id FROM homologacoes_2_tipos WHERE nome = ? LIMIT 1");
        $stmt->execute([$nome]);
        $id = $stmt->fetchColumn();

        return $id ? (int) $id : null;
    }

    private function resolveFornecedorNome(?int $fornecedorId): string
    {
        if (!$fornecedorId) {
            return '';
        }

        try {
            $stmt = $this->db->prepare("SELECT nome FROM fornecedores WHERE id = ?");
            $stmt->execute([$fornecedorId]);
            return (string) ($stmt->fetchColumn() ?: '');
        } catch (Throwable) {
            return '';
        }
    }

    private function resolveClienteId(string $nomeCliente): ?int
    {
        if ($nomeCliente === '') {
            return null;
        }

        try {
            $stmt = $this->db->prepare("SELECT id FROM clientes WHERE nome = ? LIMIT 1");
            $stmt->execute([$nomeCliente]);
            $id = $stmt->fetchColumn();
            return $id ? (int) $id : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function mapUser(array $row): array
    {
        $effectiveRole = $this->resolveRuntimeRole($row);
        $perfil = $this->inferPerfil($row);
        return [
            'id' => (int) $row['id'],
            'nome' => $row['name'],
            'email' => $row['email'] ?? '',
            'setor' => ($row['setor'] ?? '') ?: (($row['department'] ?? '') ?: ($row['profile_name'] ?? '')),
            'perfil' => $perfil,
            'role' => $effectiveRole,
            'profile_name' => $row['profile_name'] ?? '',
        ];
    }

    private function inferPerfil(array $row): string
    {
        $role = strtolower($this->resolveRuntimeRole($row));
        if (in_array($role, ['admin', 'super_admin', 'superadmin'], true)) {
            // Trata admin e super_admin como 'admin' para efeito de permissão
            return 'admin';
        }

        $haystack = strtolower(trim(
            ($row['setor'] ?? '') . ' ' .
            ($row['department'] ?? '') . ' ' .
            ($row['profile_name'] ?? '') . ' ' .
            ($row['role'] ?? '')
        ));

        return match (true) {
            str_contains($haystack, 'compr') => 'compras',
            str_contains($haystack, 'log') => 'logistica',
            str_contains($haystack, 'qualid') => 'qualidade',
            str_contains($haystack, 'tecn'), str_contains($haystack, 'engenh'), str_contains($haystack, 'ti'), str_contains($haystack, 'suporte') => 'tecnico',
            default => 'tecnico',
        };
    }

    private function resolveRuntimeRole(array $row): string
    {
        $email = strtolower(trim((string) ($row['email'] ?? '')));
        if ($email === 'du.claza@gmail.com') {
            return 'super_admin';
        }

        $sessionUserId = (int) ($_SESSION['user_id'] ?? 0);
        $sessionRole = (string) ($_SESSION['user_role'] ?? '');
        if ($sessionUserId > 0 && (int) ($row['id'] ?? 0) === $sessionUserId && $sessionRole !== '') {
            return $sessionRole;
        }

        return (string) ($row['role'] ?? '');
    }

    private function getHomologacaoBase(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM homologacoes_2
             WHERE id = ? AND deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    private function generateCodigo(int $id, int $produtoOriginalId, string $tipoHomologacao): string
    {
        if ($tipoHomologacao === 'primeira') {
            $ano = date('Y');
            return 'HOM-' . $ano . '-' . str_pad((string) $id, 3, '0', STR_PAD_LEFT);
        }

        $stmt = $this->db->prepare("SELECT codigo FROM homologacoes_2 WHERE id = ?");
        $stmt->execute([$produtoOriginalId]);
        $codigoOriginal = (string) ($stmt->fetchColumn() ?: '');
        $codigoBase = preg_replace('/-R\d+$/', '', $codigoOriginal) ?: ('HOM-' . date('Y') . '-' . str_pad((string) $produtoOriginalId, 3, '0', STR_PAD_LEFT));

        $stmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM homologacoes_2
             WHERE produto_original_id = ? AND tipo_homologacao = 'rehomologacao'"
        );
        $stmt->execute([$produtoOriginalId]);
        $sequencia = (int) $stmt->fetchColumn();

        return $codigoBase . '-R' . $sequencia;
    }

    private function registrarHistorico(
        int $homologacaoId,
        string $acao,
        ?string $statusAnterior,
        ?string $statusNovo,
        string $descricao,
        ?int $userId,
        ?string $nome = null
    ): void {
        $nomeUsuario = $nome;
        if ($nomeUsuario === null && $userId) {
            $usuario = $this->getCurrentUser($userId);
            $nomeUsuario = $usuario['nome'] ?? 'Sistema';
        }

        $stmt = $this->db->prepare(
            "INSERT INTO homologacoes_2_historico (
                homologacao_id, acao, status_anterior, status_novo, descricao,
                created_by, created_by_name, created_at
             ) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)"
        );
        $stmt->execute([
            $homologacaoId,
            $acao,
            $statusAnterior,
            $statusNovo,
            $descricao,
            $userId,
            $nomeUsuario ?: 'Sistema',
        ]);
    }

    private function appendHistoricoTexto(string $textoAtual, string $novoTexto, string $nomeUsuario): string
    {
        if ($novoTexto === '') {
            return $textoAtual;
        }

        $bloco = '[' . date('d/m/Y \à\s H:i') . " - {$nomeUsuario}]\n" . $novoTexto;
        return trim($textoAtual) === '' ? $bloco : ($textoAtual . "\n\n" . $bloco);
    }

    private function sanitizeSetorResponsavel(string $setor): string
    {
        return in_array($setor, ['tecnico', 'qualidade', 'comercial'], true) ? $setor : 'tecnico';
    }

    private function sanitizeResultado(string $resultado): string
    {
        return match ($resultado) {
            'aprovado' => 'aprovado',
            'reprovado' => 'reprovado',
            'aprovado_restricoes', 'aprovado com restrições' => 'aprovado_restricoes',
            default => 'pendente',
        };
    }

    private function normalizeChecklistKey(string $label): string
    {
        $normalized = strtolower(trim($label));
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized) ?: $normalized;
        $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized) ?: '';
        return trim($normalized, '_');
    }

    private function storeAttachments(int $homologacaoId, ?array $files, string $tipo, int $userId): int
    {
        $totalFiles = $this->normalizeUploadedFileCount($files);
        if ($totalFiles === 0) {
            return 0;
        }

        if ($totalFiles > 10) {
            throw new \RuntimeException('Envie no máximo 10 arquivos por etapa.');
        }

        $supportsBlob = $this->supportsBlobAttachments();
        $stmtBlob = null;
        $stmtPath = null;

        if ($supportsBlob) {
            $stmtBlob = $this->db->prepare(
                "INSERT INTO homologacoes_2_anexos (
                    homologacao_id, tipo, caminho, arquivo_blob, nome_original, mime_type, tamanho_bytes, created_by, created_at
                 ) VALUES (?, ?, NULL, ?, ?, ?, ?, ?, NOW())"
            );
        } else {
            $baseDir = dirname(__DIR__, 2) . '/storage/uploads/homologacoes/' . $homologacaoId . '/' . $tipo;
            if (!is_dir($baseDir)) {
                mkdir($baseDir, 0777, true);
            }

            $stmtPath = $this->db->prepare(
                "INSERT INTO homologacoes_2_anexos (
                    homologacao_id, tipo, caminho, nome_original, mime_type, tamanho_bytes, created_by, created_at
                 ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
            );
        }

        $saved = 0;
        foreach ($files['name'] as $index => $originalName) {
            if (($files['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if (($files['error'][$index] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                throw new \RuntimeException('Falha ao enviar um dos arquivos.');
            }

            $tmpName = $files['tmp_name'][$index] ?? '';
            if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                continue;
            }

            $mimeType = $this->detectImageMimeType($tmpName, (string) ($files['type'][$index] ?? ''));
            $allowedTypes = ['image/png', 'image/jpeg', 'application/pdf'];
            if (!in_array($mimeType, $allowedTypes, true)) {
                throw new \RuntimeException('Apenas arquivos PNG, JPEG ou PDF são permitidos.');
            }

            $binary = file_get_contents($tmpName);
            if ($binary === false) {
                throw new \RuntimeException('Não foi possível ler um dos arquivos enviados.');
            }

            if (strlen($binary) > (15 * 1024 * 1024)) {
                throw new \RuntimeException('Cada arquivo deve ter no máximo 15 MB.');
            }

            if ($supportsBlob && $stmtBlob instanceof \PDOStatement) {
                $stmtBlob->bindValue(1, $homologacaoId, PDO::PARAM_INT);
                $stmtBlob->bindValue(2, $tipo);
                $stmtBlob->bindValue(3, $binary, PDO::PARAM_LOB);
                $stmtBlob->bindValue(4, (string) $originalName);
                $stmtBlob->bindValue(5, $mimeType);
                $stmtBlob->bindValue(6, (int) ($files['size'][$index] ?? strlen($binary)), PDO::PARAM_INT);
                $stmtBlob->bindValue(7, $userId, PDO::PARAM_INT);
                $stmtBlob->execute();
            } elseif ($stmtPath instanceof \PDOStatement) {
                $extension = $mimeType === 'image/png' ? 'png' : ($mimeType === 'application/pdf' ? 'pdf' : 'jpg');
                $filename = uniqid($tipo . '_', true) . '.' . $extension;
                $target = $baseDir . '/' . $filename;
                if (!move_uploaded_file($tmpName, $target)) {
                    throw new \RuntimeException('Não foi possível salvar um dos arquivos enviados.');
                }

                $relative = 'storage/uploads/homologacoes/' . $homologacaoId . '/' . $tipo . '/' . $filename;
                $stmtPath->execute([
                    $homologacaoId,
                    $tipo,
                    $relative,
                    $originalName,
                    $mimeType,
                    (int) ($files['size'][$index] ?? strlen($binary)),
                    $userId,
                ]);
            }

            $saved++;
        }

        return $saved;
    }

    private function getAnexosResumoMap(array $homologacaoIds): array
    {
        if (empty($homologacaoIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($homologacaoIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT homologacao_id, tipo, COUNT(*) AS total
             FROM homologacoes_2_anexos
             WHERE homologacao_id IN ($placeholders)
             GROUP BY homologacao_id, tipo"
        );
        $stmt->execute($homologacaoIds);

        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $map[(int) $row['homologacao_id']][(string) $row['tipo']] = (int) $row['total'];
        }

        return $map;
    }

    private function getAnexosForView(int $homologacaoId, string $tipo): array
    {
        if ($this->supportsBlobAttachments()) {
            $stmt = $this->db->prepare(
                "SELECT id, nome_original, mime_type, tamanho_bytes, arquivo_blob, caminho
                 FROM homologacoes_2_anexos
                 WHERE homologacao_id = ? AND tipo = ?
                 ORDER BY id"
            );
            $stmt->execute([$homologacaoId, $tipo]);

            $anexos = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $data = $row['arquivo_blob'] ?? null;
                if ($data !== null && $data !== '') {
                    $row['data_uri'] = 'data:' . ($row['mime_type'] ?: 'image/jpeg') . ';base64,' . base64_encode($data);
                } elseif (!empty($row['caminho'])) {
                    $row['data_uri'] = '../' . ltrim((string) $row['caminho'], '/');
                } else {
                    $row['data_uri'] = '';
                }
                $anexos[] = $row;
            }

            return $anexos;
        }

        $stmt = $this->db->prepare(
            "SELECT id, nome_original, mime_type, tamanho_bytes, caminho
             FROM homologacoes_2_anexos
             WHERE homologacao_id = ? AND tipo = ?
             ORDER BY id"
        );
        $stmt->execute([$homologacaoId, $tipo]);

        return array_map(function (array $row) {
            $row['data_uri'] = !empty($row['caminho']) ? ('../' . ltrim((string) $row['caminho'], '/')) : '';
            return $row;
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function normalizeUploadedFileCount(?array $files): int
    {
        if (!$files || empty($files['name'])) {
            return 0;
        }

        $names = is_array($files['name']) ? $files['name'] : [$files['name']];
        return count(array_filter($names, fn ($name) => trim((string) $name) !== ''));
    }

    private function detectImageMimeType(string $tmpName, string $fallbackMime): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mime = finfo_file($finfo, $tmpName) ?: '';
                finfo_close($finfo);
                if ($mime !== '') {
                    return $mime;
                }
            }
        }

        return strtolower(trim($fallbackMime));
    }

    // Remove apenas a pasta do registro dentro do diretório de uploads do módulo.
    private function purgeAttachmentDirectory(int $homologacaoId): void
    {
        $baseDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'homologacoes';
        $targetDir = $baseDir . DIRECTORY_SEPARATOR . $homologacaoId;

        if (!is_dir($targetDir)) {
            return;
        }

        $resolvedBase = realpath($baseDir);
        $resolvedTarget = realpath($targetDir);
        if ($resolvedBase === false || $resolvedTarget === false) {
            return;
        }

        $allowedPrefix = rtrim($resolvedBase, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if ($resolvedTarget !== $resolvedBase && !str_starts_with($resolvedTarget, $allowedPrefix)) {
            return;
        }

        $this->deleteDirectoryRecursively($resolvedTarget);
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

            if (is_file($path)) {
                @unlink($path);
            }
        }

        @rmdir($directory);
    }

    private function supportsBlobAttachments(): bool
    {
        static $supportsBlob = null;
        if ($supportsBlob !== null) {
            return $supportsBlob;
        }

        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM homologacoes_2_anexos LIKE 'arquivo_blob'");
            $supportsBlob = $stmt !== false && $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (Throwable) {
            $supportsBlob = false;
        }

        return $supportsBlob;
    }
}
