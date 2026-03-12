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
                SELECT c.id, c.titulo, c.descricao, c.status, c.carga_horaria, c.criado_em,
                       CASE WHEN c.thumbnail IS NOT NULL THEN 1 ELSE 0 END AS has_thumbnail,
                       u.name AS gestor_nome,
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
                SELECT c.id, c.titulo, c.descricao, c.status, c.carga_horaria, c.id_gestor, c.criado_em,
                       CASE WHEN c.thumbnail IS NOT NULL THEN 1 ELSE 0 END AS has_thumbnail,
                       u.name AS gestor_nome,
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

        // Thumbnail como BLOB
        $thumbData = null;
        $thumbTipo = null;
        $hasNewThumb = false;
        $thumbUrl = trim($_POST['thumbnail_url'] ?? '');

        if ($thumbUrl && filter_var($thumbUrl, FILTER_VALIDATE_URL)) {
            $imgData = @file_get_contents($thumbUrl);
            if ($imgData && strlen($imgData) > 0) {
                $thumbData = $imgData;
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $thumbTipo = $finfo->buffer($imgData) ?: 'image/jpeg';
                $hasNewThumb = true;
            }
        } elseif (!empty($_FILES['thumbnail']['tmp_name']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
            if (isset($allowed[$ext]) && $_FILES['thumbnail']['size'] <= 10*1024*1024) {
                $thumbData = file_get_contents($_FILES['thumbnail']['tmp_name']);
                $thumbTipo = $allowed[$ext];
                $hasNewThumb = true;
            }
        }

        try {
            if ($hasNewThumb) {
                $this->db->prepare("UPDATE elearning_cursos SET titulo=?,descricao=?,status=?,carga_horaria=?,thumbnail=?,thumbnail_tipo=? WHERE id=?")
                    ->execute([$titulo, $desc, $status, $ch, $thumbData, $thumbTipo, $id]);
            } else {
                $this->db->prepare("UPDATE elearning_cursos SET titulo=?,descricao=?,status=?,carga_horaria=? WHERE id=?")
                    ->execute([$titulo, $desc, $status, $ch, $id]);
            }
            $this->json(['success' => true, 'message' => 'Curso atualizado!']);
        } catch (\PDOException $e) { $this->json(['success' => false, 'message' => $e->getMessage()]); }
    }

    // ---------- SERVIR THUMBNAIL (BLOB) ----------
    public function thumbnailCurso(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { http_response_code(404); exit; }
        try {
            $st = $this->db->prepare("SELECT thumbnail, thumbnail_tipo FROM elearning_cursos WHERE id=? AND thumbnail IS NOT NULL");
            $st->execute([$id]);
            $row = $st->fetch(\PDO::FETCH_ASSOC);
            if (!$row || !$row['thumbnail']) { http_response_code(404); exit; }
            $tipo = $row['thumbnail_tipo'] ?: 'image/jpeg';
            header('Content-Type: ' . $tipo);
            header('Content-Length: ' . strlen($row['thumbnail']));
            header('Cache-Control: public, max-age=86400');
            header('ETag: "thumb-' . $id . '-' . md5(substr($row['thumbnail'], 0, 256)) . '"');
            echo $row['thumbnail'];
            exit;
        } catch (\PDOException $e) { http_response_code(500); exit; }
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

            // Buscar materiais de todas as aulas deste curso
            $aulaIds = array_column($aulas, 'id');
            $materiais = [];
            if (!empty($aulaIds)) {
                $placeholders = implode(',', array_fill(0, count($aulaIds), '?'));
                $stm = $this->db->prepare("SELECT id, id_aula, tipo, titulo, arquivo_path, conteudo_texto, tamanho_bytes, ordem, criado_em FROM elearning_materiais WHERE id_aula IN ($placeholders) ORDER BY ordem, criado_em");
                $stm->execute($aulaIds);
                foreach ($stm->fetchAll(\PDO::FETCH_ASSOC) as $mat) {
                    $materiais[$mat['id_aula']][] = $mat;
                }
            }
            // Anexar materiais às aulas
            foreach ($aulas as &$aula) {
                $aula['materiais'] = $materiais[$aula['id']] ?? [];
            }
            unset($aula);
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
        if (!$aulaId || !in_array($tipo, ['video','pdf','imagem','slide','texto']) || !$titulo)
            $this->json(['success' => false, 'message' => 'Dados inválidos.']);

        // Tipo TEXTO — salvar conteúdo diretamente, sem arquivo
        if ($tipo === 'texto') {
            $conteudo = trim($_POST['conteudo_texto'] ?? '');
            if (!$conteudo) $this->json(['success' => false, 'message' => 'Conteúdo do texto é obrigatório.']);
            try {
                $this->db->prepare("INSERT INTO elearning_materiais (id_aula,tipo,titulo,arquivo_path,conteudo_texto,tamanho_bytes,ordem,criado_em) VALUES (?,?,?,NULL,?,?,?,NOW())")
                    ->execute([$aulaId, $tipo, $titulo, $conteudo, strlen($conteudo), (int)($_POST['ordem'] ?? 0)]);
                $this->json(['success' => true, 'message' => 'Material de texto salvo!']);
            } catch (\PDOException $e) { $this->json(['success' => false, 'message' => $e->getMessage()]); }
            return;
        }

        // Tipos com arquivo (video, pdf, imagem, slide)
        if (empty($_FILES['arquivo']['tmp_name']))
            $this->json(['success' => false, 'message' => 'Arquivo obrigatório para este tipo.']);
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

    public function updateMaterial(): void
    {
        $this->requireGestor();
        if (!$this->canEdit()) $this->json(['success' => false, 'message' => 'Sem permissão.']);
        $id = (int)($_POST['id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        if (!$id || !$titulo) $this->json(['success' => false, 'message' => 'Dados inválidos.']);

        try {
            // Buscar material atual
            $st = $this->db->prepare("SELECT * FROM elearning_materiais WHERE id=?");
            $st->execute([$id]);
            $mat = $st->fetch(\PDO::FETCH_ASSOC);
            if (!$mat) $this->json(['success' => false, 'message' => 'Material não encontrado.']);

            if ($mat['tipo'] === 'texto') {
                // Atualizar título e conteúdo do texto
                $conteudo = trim($_POST['conteudo_texto'] ?? '');
                if (!$conteudo) $this->json(['success' => false, 'message' => 'Conteúdo do texto é obrigatório.']);
                $this->db->prepare("UPDATE elearning_materiais SET titulo=?, conteudo_texto=?, tamanho_bytes=? WHERE id=?")
                    ->execute([$titulo, $conteudo, strlen($conteudo), $id]);
            } else {
                // Atualizar título (e opcionalmente o arquivo)
                if (!empty($_FILES['arquivo']['tmp_name']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
                    $tipo = $mat['tipo'];
                    $limites = ['video' => 20,'pdf' => 20,'imagem' => 10,'slide' => 20];
                    $exts = ['video' => ['mp4','avi','mov','webm'],'pdf' => ['pdf'],'imagem' => ['jpg','jpeg','png','gif','webp'],'slide' => ['pptx','ppt','pdf']];
                    $ext = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
                    if ($_FILES['arquivo']['size'] > ($limites[$tipo] ?? 20) * 1024 * 1024)
                        $this->json(['success' => false, 'message' => "Arquivo excede limite."]);
                    if (!in_array($ext, $exts[$tipo] ?? []))
                        $this->json(['success' => false, 'message' => "Extensão não permitida."]);

                    // Buscar id_curso via aula
                    $sta = $this->db->prepare("SELECT id_curso FROM elearning_aulas WHERE id=?");
                    $sta->execute([$mat['id_aula']]);
                    $aula = $sta->fetch(\PDO::FETCH_ASSOC);
                    $cid = $aula['id_curso'] ?? 0;
                    $sub = $tipo === 'slide' ? 'slides' : $tipo . 's';
                    $dir = __DIR__ . "/../../../uploads/elearning/cursos/{$cid}/{$sub}/";
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $fn = uniqid($tipo . '_') . '.' . $ext;
                    if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $dir . $fn))
                        $this->json(['success' => false, 'message' => 'Falha ao salvar arquivo.']);

                    // Excluir arquivo antigo
                    if (!empty($mat['arquivo_path'])) {
                        $old = __DIR__ . '/../../../' . ltrim($mat['arquivo_path'], '/');
                        if (file_exists($old)) @unlink($old);
                    }
                    $path = "/uploads/elearning/cursos/{$cid}/{$sub}/{$fn}";
                    $this->db->prepare("UPDATE elearning_materiais SET titulo=?, arquivo_path=?, tamanho_bytes=? WHERE id=?")
                        ->execute([$titulo, $path, $_FILES['arquivo']['size'], $id]);
                } else {
                    // Só atualizar título
                    $this->db->prepare("UPDATE elearning_materiais SET titulo=? WHERE id=?")->execute([$titulo, $id]);
                }
            }
            $this->json(['success' => true, 'message' => 'Material atualizado!']);
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

    // ---------- DIPLOMAS ----------
    public function diplomaConfig(): void
    {
        $this->requireGestor();
        if (!$this->canDelete()) { // Somente admins (seguindo a lógica que admins podem excluir)
            echo "Acesso negado."; exit;
        }
        try {
            $st = $this->db->prepare("SELECT * FROM elearning_config_diploma WHERE id = 1");
            $st->execute();
            $config = $st->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) { $config = null; }

        $this->render('elearning/gestor/diploma_config', [
            'title' => 'Configurar Diploma',
            'config' => $config
        ]);
    }

    public function saveDiplomaConfig(): void
    {
        $this->requireGestor();
        if (!$this->canDelete()) $this->json(['success' => false, 'message' => 'Sem permissão.']);
        
        $layout = (int)($_POST['layout_ativo'] ?? 1);
        $assinatura = trim($_POST['assinatura_texto'] ?? 'Diretoria SGQDJ');
        $logoX = (int)($_POST['logo_x'] ?? 50);
        $logoY = (int)($_POST['logo_y'] ?? 10);
        $logoW = (int)($_POST['logo_width'] ?? 150);
        
        try {
            if (!empty($_FILES['logo']['tmp_name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $type = $_FILES['logo']['type'];
                $blob = file_get_contents($_FILES['logo']['tmp_name']);
                $this->db->prepare("UPDATE elearning_config_diploma SET logo_diploma = ?, logo_tipo = ?, layout_ativo = ?, assinatura_texto = ?, logo_x = ?, logo_y = ?, logo_width = ? WHERE id = 1")
                    ->execute([$blob, $type, $layout, $assinatura, $logoX, $logoY, $logoW]);
            } else {
                $this->db->prepare("UPDATE elearning_config_diploma SET layout_ativo = ?, assinatura_texto = ?, logo_x = ?, logo_y = ?, logo_width = ? WHERE id = 1")
                    ->execute([$layout, $assinatura, $logoX, $logoY, $logoW]);
            }
            $this->json(['success' => true, 'message' => 'Configuração salva!']);
        } catch (\PDOException $e) { $this->json(['success' => false, 'message' => $e->getMessage()]); }
    }

    public function diplomaLogo(): void
    {
        try {
            $st = $this->db->prepare("SELECT logo_diploma, logo_tipo FROM elearning_config_diploma WHERE id = 1");
            $st->execute();
            $row = $st->fetch(\PDO::FETCH_ASSOC);
            if (!$row || !$row['logo_diploma']) {
                http_response_code(404); exit;
            }
            header('Content-Type: ' . $row['logo_tipo']);
            header('Content-Length: ' . strlen($row['logo_diploma']));
            echo $row['logo_diploma'];
            exit;
        } catch (\PDOException $e) { http_response_code(500); exit; }
    }
}
