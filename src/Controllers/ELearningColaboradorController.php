<?php
namespace App\Controllers;

use App\Config\Database;
use App\Controllers\AuthController;
use App\Services\PermissionService;

class ELearningColaboradorController
{
    private $db;

    public function __construct() { $this->db = Database::getInstance(); }

    private function requireColaborador(): void
    {
        AuthController::requireAuth();
        $uid = (int)($_SESSION['user_id'] ?? 0);
        if (!PermissionService::hasPermission($uid, 'elearning_colaborador', 'view')) {
            http_response_code(403); echo '<h1>Acesso Negado</h1>'; exit;
        }
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../../views/pages/' . $view . '.php';
        $title = $data['title'] ?? 'eLearning';
        include __DIR__ . '/../../views/layouts/main.php';
    }

    private function json(array $p): void
    {
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode($p);
        exit;
    }

    // ---------- MEUS CURSOS ----------
    public function meusCursos(): void
    {
        $this->requireColaborador();
        $uid = (int)($_SESSION['user_id'] ?? 0);
        try {
            $st = $this->db->prepare("
                SELECT c.id, c.titulo, c.descricao, c.thumbnail, c.carga_horaria, c.status,
                       m.progresso_pct, m.status AS matricula_status, m.concluido_em,
                       u.name AS gestor_nome,
                       COUNT(DISTINCT a.id) AS total_aulas
                FROM elearning_matriculas m
                JOIN elearning_cursos c ON c.id = m.id_curso
                JOIN users u ON u.id = c.id_gestor
                LEFT JOIN elearning_aulas a ON a.id_curso = c.id
                WHERE m.id_usuario = ? AND c.status = 'ativo'
                GROUP BY c.id, m.progresso_pct, m.status, m.concluido_em, u.name
                ORDER BY m.data_matricula DESC
            ");
            $st->execute([$uid]);
            $cursos = $st->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) { $cursos = []; }
        $this->render('elearning/colaborador/meus_cursos', ['title' => 'Meus Cursos — eLearning', 'cursos' => $cursos]);
    }

    // ---------- DETALHE DO CURSO ----------
    public function verCurso(int $cursoId): void
    {
        $this->requireColaborador();
        $uid = (int)($_SESSION['user_id'] ?? 0);
        try {
            // Verificar matrícula
            $stM = $this->db->prepare("SELECT * FROM elearning_matriculas WHERE id_usuario=? AND id_curso=?");
            $stM->execute([$uid, $cursoId]); $matricula = $stM->fetch(\PDO::FETCH_ASSOC);
            if (!$matricula) { http_response_code(403); echo 'Você não está matriculado neste curso.'; exit; }

            $stC = $this->db->prepare("SELECT * FROM elearning_cursos WHERE id=? AND status='ativo'");
            $stC->execute([$cursoId]); $curso = $stC->fetch(\PDO::FETCH_ASSOC);
            if (!$curso) { http_response_code(404); echo 'Curso não encontrado.'; exit; }

            $stA = $this->db->prepare("SELECT a.*, COUNT(m.id) AS total_materiais FROM elearning_aulas a LEFT JOIN elearning_materiais m ON m.id_aula=a.id WHERE a.id_curso=? GROUP BY a.id ORDER BY a.ordem");
            $stA->execute([$cursoId]); $aulas = $stA->fetchAll(\PDO::FETCH_ASSOC);

            // Progresso por material
            $stP = $this->db->prepare("SELECT id_material, visualizado FROM elearning_progresso WHERE id_usuario=?");
            $stP->execute([$uid]); $progressoRaw = $stP->fetchAll(\PDO::FETCH_ASSOC);
            $progresso = [];
            foreach ($progressoRaw as $p) $progresso[$p['id_material']] = $p['visualizado'];

            // Provas disponíveis
            $stPr = $this->db->prepare("SELECT p.id, p.titulo, p.nota_minima, p.tentativas_max, COUNT(t.id) AS tentativas_feitas FROM elearning_provas p LEFT JOIN elearning_tentativas t ON t.id_prova=p.id AND t.id_usuario=? WHERE p.id_curso=? AND p.ativa=1 GROUP BY p.id");
            $stPr->execute([$uid, $cursoId]); $provas = $stPr->fetchAll(\PDO::FETCH_ASSOC);

            // Certificado
            $stCert = $this->db->prepare("SELECT * FROM elearning_certificados WHERE id_usuario=? AND id_curso=?");
            $stCert->execute([$uid, $cursoId]); $certificado = $stCert->fetch(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) { $aulas = []; $provas = []; $certificado = null; $progresso = []; }
        $this->render('elearning/colaborador/curso_detalhe', [
            'title' => $curso['titulo'] ?? 'Curso',
            'curso' => $curso, 'aulas' => $aulas, 'provas' => $provas,
            'progresso' => $progresso, 'certificado' => $certificado, 'matricula' => $matricula,
        ]);
    }

    // ---------- ASSISTIR AULA (servir arquivo com validação de sessão) ----------
    public function assistirAula(int $materialId): void
    {
        $this->requireColaborador();
        $uid = (int)($_SESSION['user_id'] ?? 0);
        try {
            $stMat = $this->db->prepare("SELECT m.*, a.id_curso FROM elearning_materiais m JOIN elearning_aulas a ON a.id=m.id_aula WHERE m.id=?");
            $stMat->execute([$materialId]); $mat = $stMat->fetch(\PDO::FETCH_ASSOC);
            if (!$mat) { http_response_code(404); echo 'Material não encontrado.'; exit; }
            // verifica matrícula
            $stMatr = $this->db->prepare("SELECT id FROM elearning_matriculas WHERE id_usuario=? AND id_curso=?");
            $stMatr->execute([$uid, $mat['id_curso']]); $matr = $stMatr->fetch();
            if (!$matr) { http_response_code(403); echo 'Acesso negado.'; exit; }

            // Marcar início do progresso
            $this->db->prepare("INSERT INTO elearning_progresso (id_usuario,id_material,data_inicio) VALUES (?,?,NOW()) ON DUPLICATE KEY UPDATE data_inicio=COALESCE(data_inicio,NOW())")
                ->execute([$uid, $materialId]);

            // Retornar URL para exibição (não faz readfile, retorna JSON com path)
            $this->json([
                'success' => true,
                'titulo'  => $mat['titulo'],
                'tipo'    => $mat['tipo'],
                'path'    => $mat['arquivo_path'],
            ]);
        } catch (\PDOException $e) { $this->json(['success' => false, 'message' => $e->getMessage()]); }
    }

    // ---------- REGISTRAR PROGRESSO ----------
    public function registrarProgresso(): void
    {
        $this->requireColaborador();
        $uid        = (int)($_SESSION['user_id'] ?? 0);
        $materialId = (int)($_POST['id_material'] ?? 0);
        $pct        = min(100, max(0, (float)($_POST['pct'] ?? 100)));
        if (!$materialId) $this->json(['success' => false, 'message' => 'ID inválido.']);
        try {
            $concluido = $pct >= 90 ? 'NOW()' : 'NULL';
            $this->db->prepare("
                INSERT INTO elearning_progresso (id_usuario, id_material, visualizado, pct_assistido, data_conclusao)
                VALUES (?, ?, ?, ?, " . ($pct >= 90 ? 'NOW()' : 'NULL') . ")
                ON DUPLICATE KEY UPDATE
                    visualizado = IF(VALUES(pct_assistido) >= 90, 1, visualizado),
                    pct_assistido = GREATEST(pct_assistido, VALUES(pct_assistido)),
                    data_conclusao = IF(VALUES(pct_assistido) >= 90 AND data_conclusao IS NULL, NOW(), data_conclusao)
            ")->execute([$uid, $materialId, $pct >= 90 ? 1 : 0, $pct]);

            // Recalcular progresso geral do curso
            $stCurso = $this->db->prepare("SELECT a.id_curso FROM elearning_materiais m JOIN elearning_aulas a ON a.id=m.id_aula WHERE m.id=?");
            $stCurso->execute([$materialId]); $r = $stCurso->fetch(\PDO::FETCH_ASSOC);
            if ($r) {
                $totalMat = $this->db->prepare("SELECT COUNT(*) FROM elearning_materiais m JOIN elearning_aulas a ON a.id=m.id_aula WHERE a.id_curso=?");
                $totalMat->execute([$r['id_curso']]); $tot = (int)$totalMat->fetchColumn();

                $visMat = $this->db->prepare("SELECT COUNT(*) FROM elearning_progresso pg JOIN elearning_materiais m ON m.id=pg.id_material JOIN elearning_aulas a ON a.id=m.id_aula WHERE a.id_curso=? AND pg.id_usuario=? AND pg.visualizado=1");
                $visMat->execute([$r['id_curso'], $uid]); $vis = (int)$visMat->fetchColumn();

                $novoPct = $tot > 0 ? round($vis / $tot * 100, 2) : 0;
                $status  = $novoPct >= 100 ? 'concluido' : 'em_andamento';
                $this->db->prepare("UPDATE elearning_matriculas SET progresso_pct=?, status=?, concluido_em=IF(?='concluido' AND concluido_em IS NULL, NOW(), concluido_em) WHERE id_usuario=? AND id_curso=?")
                    ->execute([$novoPct, $status, $status, $uid, $r['id_curso']]);
            }

            $this->json(['success' => true, 'pct' => $pct]);
        } catch (\PDOException $e) { $this->json(['success' => false, 'message' => $e->getMessage()]); }
    }

    // ---------- FAZER PROVA ----------
    public function fazerProva(int $provaId): void
    {
        $this->requireColaborador();
        $uid = (int)($_SESSION['user_id'] ?? 0);
        try {
            $stPr = $this->db->prepare("SELECT p.*, c.titulo AS titulo_curso FROM elearning_provas p JOIN elearning_cursos c ON c.id=p.id_curso WHERE p.id=? AND p.ativa=1");
            $stPr->execute([$provaId]); $prova = $stPr->fetch(\PDO::FETCH_ASSOC);
            if (!$prova) { http_response_code(404); echo 'Prova não encontrada.'; exit; }

            // Verificar tentativas
            $stT = $this->db->prepare("SELECT COUNT(*) FROM elearning_tentativas WHERE id_usuario=? AND id_prova=?");
            $stT->execute([$uid, $provaId]); $tentativas = (int)$stT->fetchColumn();
            if ($tentativas >= $prova['tentativas_max']) { echo 'Número máximo de tentativas atingido.'; exit; }

            // Iniciar nova tentativa
            $this->db->prepare("INSERT INTO elearning_tentativas (id_usuario,id_prova,iniciado_em,numero_tentativa) VALUES (?,?,NOW(),?)")
                ->execute([$uid, $provaId, $tentativas + 1]);
            $tentativaId = (int)$this->db->lastInsertId();

            $stQ = $this->db->prepare("SELECT q.*, GROUP_CONCAT(CONCAT(a.id,'|',a.texto,'|',0) ORDER BY a.ordem SEPARATOR ';;') AS alternativas_raw FROM elearning_questoes q LEFT JOIN elearning_alternativas a ON a.id_questao=q.id WHERE q.id_prova=? GROUP BY q.id ORDER BY q.ordem");
            $stQ->execute([$provaId]); $questoes = $stQ->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) { $questoes = []; $tentativaId = 0; }
        $this->render('elearning/colaborador/fazer_prova', [
            'title' => 'Prova — ' . ($prova['titulo'] ?? ''),
            'prova' => $prova, 'questoes' => $questoes, 'tentativaId' => $tentativaId,
        ]);
    }

    public function submeterProva(): void
    {
        $this->requireColaborador();
        $uid        = (int)($_SESSION['user_id'] ?? 0);
        $tentId     = (int)($_POST['tentativa_id'] ?? 0);
        $respostas  = $_POST['respostas'] ?? [];
        if (!$tentId) $this->json(['success' => false, 'message' => 'Tentativa inválida.']);
        try {
            // Verificar que a tentativa pertence ao usuário
            $stT = $this->db->prepare("SELECT t.*, p.nota_minima, p.id AS prova_id FROM elearning_tentativas t JOIN elearning_provas p ON p.id=t.id_prova WHERE t.id=? AND t.id_usuario=?");
            $stT->execute([$tentId, $uid]); $tent = $stT->fetch(\PDO::FETCH_ASSOC);
            if (!$tent || $tent['finalizado_em']) $this->json(['success' => false, 'message' => 'Tentativa inválida ou já finalizada.']);

            // Buscar questões e calcular nota
            $stQ = $this->db->prepare("SELECT q.id, q.pontos, q.tipo, a.id AS alt_id, a.correta FROM elearning_questoes q LEFT JOIN elearning_alternativas a ON a.id_questao=q.id WHERE q.id_prova=?");
            $stQ->execute([$tent['prova_id']]); $questoesRows = $stQ->fetchAll(\PDO::FETCH_ASSOC);

            $pontosPossiveis = 0; $pontosObtidos = 0;
            $questoesMap = [];
            foreach ($questoesRows as $row) {
                if (!isset($questoesMap[$row['id']])) {
                    $questoesMap[$row['id']] = ['pontos' => $row['pontos'], 'tipo' => $row['tipo'], 'corretas' => []];
                    $pontosPossiveis += $row['pontos'];
                }
                if ($row['alt_id'] && $row['correta']) $questoesMap[$row['id']]['corretas'][] = $row['alt_id'];
            }

            foreach ($questoesMap as $qid => $q) {
                $resposta = (int)($respostas[$qid] ?? 0);
                $correta  = in_array($resposta, $q['corretas']) ? 1 : 0;
                if ($correta) $pontosObtidos += $q['pontos'];
                $this->db->prepare("INSERT INTO elearning_respostas (id_tentativa,id_questao,id_alternativa,correta) VALUES (?,?,?,?)")
                    ->execute([$tentId, $qid, $resposta ?: null, $correta]);
            }

            $notaPct  = $pontosPossiveis > 0 ? round($pontosObtidos / $pontosPossiveis * 100, 2) : 0;
            $aprovado = $notaPct >= $tent['nota_minima'] ? 1 : 0;
            $this->db->prepare("UPDATE elearning_tentativas SET finalizado_em=NOW(), nota_obtida=?, aprovado=? WHERE id=?")
                ->execute([$notaPct, $aprovado, $tentId]);

            $this->json(['success' => true, 'nota' => $notaPct, 'aprovado' => (bool)$aprovado, 'tentativa_id' => $tentId]);
        } catch (\PDOException $e) { $this->json(['success' => false, 'message' => $e->getMessage()]); }
    }

    // ---------- RESULTADO DA PROVA ----------
    public function resultadoProva(int $tentativaId): void
    {
        $this->requireColaborador();
        $uid = (int)($_SESSION['user_id'] ?? 0);
        try {
            $stT = $this->db->prepare("SELECT t.*, p.titulo AS titulo_prova, p.nota_minima FROM elearning_tentativas t JOIN elearning_provas p ON p.id=t.id_prova WHERE t.id=? AND t.id_usuario=?");
            $stT->execute([$tentativaId, $uid]); $tentativa = $stT->fetch(\PDO::FETCH_ASSOC);
            if (!$tentativa) { http_response_code(404); echo 'Resultado não encontrado.'; exit; }

            $stR = $this->db->prepare("SELECT r.*, q.enunciado, q.pontos, a.texto AS alt_texto FROM elearning_respostas r JOIN elearning_questoes q ON q.id=r.id_questao LEFT JOIN elearning_alternativas a ON a.id=r.id_alternativa WHERE r.id_tentativa=? ORDER BY q.ordem");
            $stR->execute([$tentativaId]); $respostas = $stR->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) { $respostas = []; }
        $this->render('elearning/colaborador/resultado_prova', [
            'title' => 'Resultado — ' . ($tentativa['titulo_prova'] ?? ''),
            'tentativa' => $tentativa, 'respostas' => $respostas,
        ]);
    }

    // ---------- MEUS CERTIFICADOS ----------
    public function meusCertificados(): void
    {
        $this->requireColaborador();
        $uid = (int)($_SESSION['user_id'] ?? 0);
        try {
            $st = $this->db->prepare("SELECT cert.*, c.titulo AS titulo_curso, c.carga_horaria FROM elearning_certificados cert JOIN elearning_cursos c ON c.id=cert.id_curso WHERE cert.id_usuario=? ORDER BY cert.emitido_em DESC");
            $st->execute([$uid]); $certificados = $st->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) { $certificados = []; }
        $this->render('elearning/colaborador/meus_certificados', ['title' => 'Meus Certificados', 'certificados' => $certificados]);
    }

    public function downloadCertificado(string $codigo): void
    {
        $this->requireColaborador();
        $uid = (int)($_SESSION['user_id'] ?? 0);
        try {
            $st = $this->db->prepare("
                SELECT cert.*, c.titulo AS titulo_curso, c.carga_horaria, 
                       u.name AS nome_usuario, ug.name AS gestor_nome,
                       cert.emitido_em
                FROM elearning_certificados cert
                JOIN elearning_cursos c ON c.id = cert.id_curso
                JOIN users u ON u.id = cert.id_usuario
                JOIN users ug ON ug.id = c.id_gestor
                WHERE cert.codigo_validacao = ? AND cert.id_usuario = ?
            ");
            $st->execute([$codigo, $uid]);
            $cert = $st->fetch(\PDO::FETCH_ASSOC);
            if (!$cert) { http_response_code(404); echo 'Certificado não encontrado.'; exit; }

            // Gerar PDF simples inline se TCPDF não disponível
            // Se TCPDF disponível: usar biblioteca. Caso contrário: HTML para impressão.
            header('Content-Type: text/html; charset=utf-8');
            include __DIR__ . '/../../views/pages/elearning/colaborador/certificado_template.php';
        } catch (\PDOException $e) {
            http_response_code(500); echo 'Erro ao gerar certificado: ' . $e->getMessage();
        }
    }
}
