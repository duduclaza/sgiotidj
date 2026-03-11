<?php
namespace App\Controllers;

use App\Config\Database;
use App\Controllers\AuthController;
use App\Services\PermissionService;

class ELearningGestorController
{
    private $db;

    public function __construct() { $this->db = Database::getInstance(); }

    private function requireGestor(): void
    {
        AuthController::requireAuth();
        $uid = (int)($_SESSION['user_id'] ?? 0);
        if (!PermissionService::hasPermission($uid, 'elearning_gestor', 'view')) {
            http_response_code(403); echo '<h1>Acesso Negado</h1>'; exit;
        }
    }

    private function canEdit(): bool
    { return PermissionService::hasPermission((int)($_SESSION['user_id'] ?? 0), 'elearning_gestor', 'edit'); }

    private function canDelete(): bool
    { return PermissionService::hasPermission((int)($_SESSION['user_id'] ?? 0), 'elearning_gestor', 'delete'); }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../../views/pages/' . $view . '.php';
        $title = $data['title'] ?? 'eLearning Gestor';
        include __DIR__ . '/../../views/layouts/main.php';
    }

    private function json(array $p): void
    {
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode($p);
        exit;
    }

    // ---------- DASHBOARD ----------
    public function dashboard(): void
    {
        $this->requireGestor();
        try {
            $totalCursos     = $this->db->query("SELECT COUNT(*) FROM elearning_cursos")->fetchColumn();
            $totalMatriculas = $this->db->query("SELECT COUNT(*) FROM elearning_matriculas")->fetchColumn();
            $totalConc       = $this->db->query("SELECT COUNT(*) FROM elearning_matriculas WHERE status='concluido'")->fetchColumn();
            $cursos          = $this->db->query("
                SELECT c.*, u.name AS gestor_nome,
                       COUNT(m.id) AS total_matriculas
                FROM elearning_cursos c
                LEFT JOIN users u ON u.id = c.id_gestor
                LEFT JOIN elearning_matriculas m ON m.id_curso = c.id
                GROUP BY c.id ORDER BY c.criado_em DESC LIMIT 50
            ")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $totalCursos = $totalMatriculas = $totalConc = 0; $cursos = [];
        }
        $this->render('elearning/gestor/dashboard', [
            'title' => 'eLearning Gestor — Dashboard',
            'totalCursos' => $totalCursos, 'totalMatriculas' => $totalMatriculas,
            'totalConcluidos' => $totalConc, 'cursos' => $cursos,
            'canEdit' => $this->canEdit(), 'canDelete' => $this->canDelete(),
        ]);
    }

    // ---------- CURSOS ----------
    public function cursos(): void
    {
        $this->requireGestor();
        try {
            $cursos = $this->db->query("
                SELECT c.*, u.name AS gestor_nome,
                       COUNT(DISTINCT a.id) AS total_aulas,
                       COUNT(DISTINCT m.id) AS total_matriculas
                FROM elearning_cursos c
                LEFT JOIN users u ON u.id = c.id_gestor
                LEFT JOIN elearning_aulas a ON a.id_curso = c.id
                LEFT JOIN elearning_matriculas m ON m.id_curso = c.id
                GROUP BY c.id ORDER BY c.criado_em DESC
            ")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) { $cursos = []; }
        $this->render('elearning/gestor/cursos', [
            'title' => 'eLearning — Cursos', 'cursos' => $cursos,
            'canEdit' => $this->canEdit(), 'canDelete' => $this->canDelete(),
        ]);
    }

    public function storeCurso(): void
    {
        $this->requireGestor();
        if (!$this->canEdit()) $this->json(['success' => false, 'message' => 'Sem permissão.']);
        $titulo = trim($_POST['titulo'] ?? '');
        if (!$titulo) $this->json(['success' => false, 'message' => 'Título obrigatório.']);
        $desc   = trim($_POST['descricao'] ?? '');
        $ch     = max(0, (int)($_POST['carga_horaria'] ?? 0));
        $status = in_array($_POST['status'] ?? '', ['ativo','inativo','rascunho']) ? $_POST['status'] : 'rascunho';
        $uid    = (int)($_SESSION['user_id'] ?? 0);

        // Thumbnail como BLOB
        $thumbData = null;
        $thumbTipo = null;
        $thumbUrl = trim($_POST['thumbnail_url'] ?? '');

        if ($thumbUrl && filter_var($thumbUrl, FILTER_VALIDATE_URL)) {
            // Baixar imagem da biblioteca e salvar como blob
            $imgData = @file_get_contents($thumbUrl);
            if ($imgData && strlen($imgData) > 0) {
                $thumbData = $imgData;
                // Detectar tipo via magic bytes
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $thumbTipo = $finfo->buffer($imgData) ?: 'image/jpeg';
            }
        } elseif (!empty($_FILES['thumbnail']['tmp_name']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
            if (isset($allowed[$ext]) && $_FILES['thumbnail']['size'] <= 10*1024*1024) {
                $thumbData = file_get_contents($_FILES['thumbnail']['tmp_name']);
                $thumbTipo = $allowed[$ext];
            }
        }

        try {
            $st = $this->db->prepare("INSERT INTO elearning_cursos (titulo,descricao,thumbnail,thumbnail_tipo,id_gestor,status,carga_horaria,criado_em) VALUES (?,?,?,?,?,?,?,NOW())");
            $st->execute([$titulo, $desc, $thumbData, $thumbTipo, $uid, $status, $ch]);
            $this->json(['success' => true, 'message' => 'Curso criado!', 'id' => $this->db->lastInsertId()]);
        } catch (\PDOException $e) { $this->json(['success' => false, 'message' => $e->getMessage()]); }
    }

    public function updateCurso(): void
    {
        $this->requireGestor();
        if (!$this->canEdit()) $this->json(['success' => false, 'message' => 'Sem permissão.']);
        $id = (int)($_POST['id'] ?? 0); $titulo = trim($_POST['titulo'] ?? '');
        if (!$id || !$titulo) $this->json(['success' => false, 'message' => 'Dados inválidos.']);
        $desc   = trim($_POST['descricao'] ?? '');
        $ch     = max(0, (int)($_POST['carga_horaria'] ?? 0));
        $status = in_array($_POST['status'] ?? '', ['ativo','inativo','rascunho']) ? $_POST['status'] : 'rascunho';
        // Thumbnail: URL da biblioteca > upload de arquivo
        $thumbUrl = trim($_POST['thumbnail_url'] ?? '');
        $thumb = null;
        if ($thumbUrl && filter_var($thumbUrl, FILTER_VALIDATE_URL)) {
            $thumb = $thumbUrl;
        } elseif (!empty($_FILES['thumbnail']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp']) && $_FILES['thumbnail']['size'] <= 10*1024*1024) {
                $dir = __DIR__ . '/../../../uploads/elearning/thumbnails/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $fn = uniqid('thumb_') . '.' . $ext;
                if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $dir . $fn))
                    $thumb = '/uploads/elearning/thumbnails/' . $fn;
            }
        }
        try {
            if ($thumb) {
                $this->db->prepare("UPDATE elearning_cursos SET titulo=?,descricao=?,status=?,carga_horaria=?,thumbnail=? WHERE id=?")
                    ->execute([$titulo, $desc, $status, $ch, $thumb, $id]);
            } else {
                $this->db->prepare("UPDATE elearning_cursos SET titulo=?,descricao=?,status=?,carga_horaria=? WHERE id=?")
                    ->execute([$titulo, $desc, $status, $ch, $id]);
            }
            $this->json(['success' => true, 'message' => 'Curso atualizado!']);
        } catch (\PDOException $e) { $this->json(['success' => false, 'message' => $e->getMessage()]); }
    }

    public function deleteCurso(): void
    {
        $this->requireGestor();
        if (!$this->canDelete()) $this->json(['success' => false, 'message' => 'Sem permissão.']);
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) $this->json(['success' => false, 'message' => 'ID inválido.']);
        try { $this->db->prepare("DELETE FROM elearning_cursos WHERE id=?")->execute([$id]);
            $this->json(['success' => true, 'message' => 'Curso excluído!']);
        } catch (\PDOException $e) { $this->json(['success' => false, 'message' => $e->getMessage()]); }
    }

    // ---------- AULAS ----------
    public function aulas(int $cursoId): void
    {
        $this->requireGestor();
        try {
            $st = $this->db->prepare("SELECT * FROM elearning_cursos WHERE id=?");
            $st->execute([$cursoId]); $curso = $st->fetch(\PDO::FETCH_ASSOC);
            if (!$curso) { http_response_code(404); echo 'Curso não encontrado.'; exit; }
            $st2 = $this->db->prepare("SELECT a.*, COUNT(m.id) AS total_materiais FROM elearning_aulas a LEFT JOIN elearning_materiais m ON m.id_aula=a.id WHERE a.id_curso=? GROUP BY a.id ORDER BY a.ordem");
            $st2->execute([$cursoId]); $aulas = $st2->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) { $aulas = []; }
        $this->render('elearning/gestor/aulas', ['title' => 'Aulas — '.($curso['titulo'] ?? ''), 'curso' => $curso, 'aulas' => $aulas,'canEdit' => $this->canEdit(),'canDelete' => $this->canDelete()]);
    }

    public function storeAula(): void
    {
        $this->requireGestor();
        if (!$this->canEdit()) $this->json(['success' => false, 'message' => 'Sem permissão.']);
        $cid = (int)($_POST['id_curso'] ?? 0); $titulo = trim($_POST['titulo'] ?? '');
        if (!$cid || !$titulo) $this->json(['success' => false, 'message' => 'Dados obrigatórios.']);
        try {
            $this->db->prepare("INSERT INTO elearning_aulas (id_curso,titulo,descricao,ordem,criado_em) VALUES (?,?,?,?,NOW())")
                ->execute([$cid, $titulo, trim($_POST['descricao'] ?? ''), (int)($_POST['ordem'] ?? 0)]);
            $this->json(['success' => true, 'message' => 'Aula criada!', 'id' => $this->db->lastInsertId()]);
        } catch (\PDOException $e) { $this->json(['success' => false, 'message' => $e->getMessage()]); }
    }

    public function deleteAula(): void
    {
        $this->requireGestor();
        if (!$this->canDelete()) $this->json(['success' => false, 'message' => 'Sem permissão.']);
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) $this->json(['success' => false, 'message' => 'ID inválido.']);
        try { $this->db->prepare("DELETE FROM elearning_aulas WHERE id=?")->execute([$id]);
            $this->json(['success' => true, 'message' => 'Aula excluída!']);
        } catch (\PDOException $e) { $this->json(['success' => false, 'message' => $e->getMessage()]); }
    }

    // ---------- MATERIAIS ----------
    public function uploadMaterial(): void
    {
        $this->requireGestor();
        if (!$this->canEdit()) $this->json(['success' => false, 'message' => 'Sem permissão.']);
        $aulaId = (int)($_POST['id_aula'] ?? 0); $tipo = $_POST['tipo'] ?? ''; $titulo = trim($_POST['titulo'] ?? '');
        if (!$aulaId || !in_array($tipo, ['video','pdf','imagem','slide']) || !$titulo || empty($_FILES['arquivo']['tmp_name']))
            $this->json(['success' => false, 'message' => 'Dados ou arquivo inválido.']);
        $limites = ['video' => 20,'pdf' => 20,'imagem' => 10,'slide' => 20];
        $exts    = ['video' => ['mp4','avi','mov','webm'],'pdf' => ['pdf'],'imagem' => ['jpg','jpeg','png','gif','webp'],'slide' => ['pptx','ppt','pdf']];
        $ext = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
        if ($_FILES['arquivo']['size'] > $limites[$tipo] * 1024 * 1024)
            $this->json(['success' => false, 'message' => "Arquivo excede {$limites[$tipo]}MB."]);
        if (!in_array($ext, $exts[$tipo]))
            $this->json(['success' => false, 'message' => "Extensão não permitida para $tipo."]);
        try {
            $st = $this->db->prepare("SELECT id_curso FROM elearning_aulas WHERE id=?"); $st->execute([$aulaId]);
            $aula = $st->fetch(\PDO::FETCH_ASSOC);
            if (!$aula) $this->json(['success' => false, 'message' => 'Aula não encontrada.']);
            $cid = $aula['id_curso'];
            $sub = $tipo === 'slide' ? 'slides' : $tipo . 's';
            $dir = __DIR__ . "/../../../uploads/elearning/cursos/{$cid}/{$sub}/";
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $fn = uniqid($tipo . '_') . '.' . $ext;
            if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $dir . $fn))
                $this->json(['success' => false, 'message' => 'Falha ao salvar arquivo.']);
            $path = "/uploads/elearning/cursos/{$cid}/{$sub}/{$fn}";
            $this->db->prepare("INSERT INTO elearning_materiais (id_aula,tipo,titulo,arquivo_path,tamanho_bytes,ordem,criado_em) VALUES (?,?,?,?,?,?,NOW())")
                ->execute([$aulaId, $tipo, $titulo, $path, $_FILES['arquivo']['size'], (int)($_POST['ordem'] ?? 0)]);
            $this->json(['success' => true, 'message' => 'Material enviado!', 'path' => $path]);
        } catch (\PDOException $e) { $this->json(['success' => false, 'message' => $e->getMessage()]); }
    }

    public function deleteMaterial(): void
    {
        $this->requireGestor();
        if (!$this->canDelete()) $this->json(['success' => false, 'message' => 'Sem permissão.']);
        $id = (int)($_POST['id'] ?? 0); if (!$id) $this->json(['success' => false, 'message' => 'ID inválido.']);
        try {
            $st = $this->db->prepare("SELECT arquivo_path FROM elearning_materiais WHERE id=?"); $st->execute([$id]);
            $m = $st->fetch(\PDO::FETCH_ASSOC);
            if ($m && !empty($m['arquivo_path'])) { $full = __DIR__ . '/../../../' . ltrim($m['arquivo_path'],'/'); if (file_exists($full)) @unlink($full); }
            $this->db->prepare("DELETE FROM elearning_materiais WHERE id=?")->execute([$id]);
            $this->json(['success' => true, 'message' => 'Material excluído!']);
        } catch (\PDOException $e) { $this->json(['success' => false, 'message' => $e->getMessage()]); }
    }

    // ---------- PROVAS ----------
    public function provas(int $cursoId): void
    {
        $this->requireGestor();
        try {
            $st = $this->db->prepare("SELECT * FROM elearning_cursos WHERE id=?"); $st->execute([$cursoId]); $curso = $st->fetch(\PDO::FETCH_ASSOC);
            $st2 = $this->db->prepare("SELECT p.*, COUNT(q.id) AS total_questoes FROM elearning_provas p LEFT JOIN elearning_questoes q ON q.id_prova=p.id WHERE p.id_curso=? GROUP BY p.id"); $st2->execute([$cursoId]);
            $provas = $st2->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) { $provas = []; }
        $this->render('elearning/gestor/provas', ['title' => 'Provas — '.($curso['titulo'] ?? ''), 'curso' => $curso, 'provas' => $provas,'canEdit' => $this->canEdit(),'canDelete' => $this->canDelete()]);
    }

    public function storeProva(): void
    {
        $this->requireGestor();
        if (!$this->canEdit()) $this->json(['success' => false, 'message' => 'Sem permissão.']);
        $cid = (int)($_POST['id_curso'] ?? 0); $titulo = trim($_POST['titulo'] ?? '');
        if (!$cid || !$titulo) $this->json(['success' => false, 'message' => 'Dados obrigatórios.']);
        try {
            $this->db->prepare("INSERT INTO elearning_provas (id_curso,titulo,nota_minima,tentativas_max,tempo_min,ativa,criado_em) VALUES (?,?,?,?,?,1,NOW())")
                ->execute([$cid, $titulo, (float)($_POST['nota_minima'] ?? 70), max(1,(int)($_POST['tentativas_max'] ?? 3)), max(0,(int)($_POST['tempo_min'] ?? 0))]);
            $this->json(['success' => true, 'message' => 'Prova criada!', 'id' => $this->db->lastInsertId()]);
        } catch (\PDOException $e) { $this->json(['success' => false, 'message' => $e->getMessage()]); }
    }

    public function storeQuestao(): void
    {
        $this->requireGestor();
        if (!$this->canEdit()) $this->json(['success' => false, 'message' => 'Sem permissão.']);
        $pid = (int)($_POST['id_prova'] ?? 0); $enunc = trim($_POST['enunciado'] ?? '');
        if (!$pid || !$enunc) $this->json(['success' => false, 'message' => 'Dados obrigatórios.']);
        $tipo = in_array($_POST['tipo'] ?? '', ['multipla','verdadeiro_falso','dissertativa']) ? $_POST['tipo'] : 'multipla';
        try {
            $this->db->prepare("INSERT INTO elearning_questoes (id_prova,enunciado,tipo,pontos,ordem) VALUES (?,?,?,?,?)")
                ->execute([$pid, $enunc, $tipo, max(0.5,(float)($_POST['pontos'] ?? 1)), (int)($_POST['ordem'] ?? 0)]);
            $qid = $this->db->lastInsertId();
            $alts = $_POST['alternativas'] ?? []; $correta = (int)($_POST['correta'] ?? -1);
            foreach ($alts as $i => $texto) {
                if (!trim($texto)) continue;
                $this->db->prepare("INSERT INTO elearning_alternativas (id_questao,texto,correta,ordem) VALUES (?,?,?,?)")
                    ->execute([$qid, trim($texto), ($i == $correta ? 1 : 0), $i]);
            }
            $this->json(['success' => true, 'message' => 'Questão criada!', 'id' => $qid]);
        } catch (\PDOException $e) { $this->json(['success' => false, 'message' => $e->getMessage()]); }
    }

    // ---------- MATRÍCULAS ----------
    public function matriculas(int $cursoId): void
    {
        $this->requireGestor();
        try {
            $st = $this->db->prepare("SELECT * FROM elearning_cursos WHERE id=?"); $st->execute([$cursoId]); $curso = $st->fetch(\PDO::FETCH_ASSOC);
            $st2 = $this->db->prepare("SELECT m.*, u.name AS usuario_nome, u.email AS usuario_email FROM elearning_matriculas m JOIN users u ON u.id=m.id_usuario WHERE m.id_curso=? ORDER BY m.data_matricula DESC");
            $st2->execute([$cursoId]); $matriculas = $st2->fetchAll(\PDO::FETCH_ASSOC);
            $usuarios = $this->db->query("SELECT id, name, email FROM users ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) { $matriculas = []; $usuarios = []; }
        $this->render('elearning/gestor/matriculas', ['title' => 'Matrículas — '.($curso['titulo'] ?? ''), 'curso' => $curso,'matriculas' => $matriculas,'usuarios' => $usuarios,'canEdit' => $this->canEdit()]);
    }

    public function matricularColaborador(): void
    {
        $this->requireGestor();
        if (!$this->canEdit()) $this->json(['success' => false, 'message' => 'Sem permissão.']);
        $cid = (int)($_POST['id_curso'] ?? 0); $uid = (int)($_POST['id_usuario'] ?? 0);
        if (!$cid || !$uid) $this->json(['success' => false, 'message' => 'Dados inválidos.']);
        try {
            $this->db->prepare("INSERT IGNORE INTO elearning_matriculas (id_usuario,id_curso,data_matricula,status,progresso_pct) VALUES (?,?,NOW(),'em_andamento',0)")->execute([$uid, $cid]);
            $this->json(['success' => true, 'message' => 'Colaborador matriculado!']);
        } catch (\PDOException $e) { $this->json(['success' => false, 'message' => $e->getMessage()]); }
    }

    // ---------- PROGRESSO ----------
    public function progressoDashboard(int $cursoId): void
    {
        $this->requireGestor();
        try {
            $st = $this->db->prepare("SELECT * FROM elearning_cursos WHERE id=?"); $st->execute([$cursoId]); $curso = $st->fetch(\PDO::FETCH_ASSOC);
            $st2 = $this->db->prepare("SELECT m.*, u.name AS usuario_nome FROM elearning_matriculas m JOIN users u ON u.id=m.id_usuario WHERE m.id_curso=? ORDER BY m.progresso_pct DESC");
            $st2->execute([$cursoId]); $progresso = $st2->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) { $progresso = []; }
        $this->render('elearning/gestor/progresso', ['title' => 'Progresso — '.($curso['titulo'] ?? ''), 'curso' => $curso, 'progresso' => $progresso]);
    }

    // ---------- CERTIFICADO ----------
    public function emitirCertificado(): void
    {
        $this->requireGestor();
        if (!$this->canEdit()) $this->json(['success' => false, 'message' => 'Sem permissão.']);
        $mid = (int)($_POST['matricula_id'] ?? 0);
        if (!$mid) $this->json(['success' => false, 'message' => 'ID inválido.']);
        try {
            $st = $this->db->prepare("SELECT m.id_usuario, m.id_curso FROM elearning_matriculas m WHERE m.id=?"); $st->execute([$mid]);
            $dados = $st->fetch(\PDO::FETCH_ASSOC);
            if (!$dados) $this->json(['success' => false, 'message' => 'Matrícula não encontrada.']);
            $stT = $this->db->prepare("SELECT t.id FROM elearning_tentativas t JOIN elearning_provas p ON p.id=t.id_prova WHERE t.id_usuario=? AND p.id_curso=? AND t.aprovado=1 ORDER BY t.finalizado_em DESC LIMIT 1");
            $stT->execute([$dados['id_usuario'], $dados['id_curso']]); $tent = $stT->fetch(\PDO::FETCH_ASSOC);
            $codigo = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000, mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff));
            $this->db->prepare("INSERT INTO elearning_certificados (id_usuario,id_curso,id_tentativa,codigo_validacao,emitido_em) VALUES (?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE codigo_validacao=VALUES(codigo_validacao),emitido_em=NOW()")
                ->execute([$dados['id_usuario'], $dados['id_curso'], $tent['id'] ?? null, $codigo]);
            $this->json(['success' => true, 'message' => 'Certificado emitido!', 'codigo' => $codigo]);
        } catch (\PDOException $e) { $this->json(['success' => false, 'message' => $e->getMessage()]); }
    }
}
