<?php

namespace App\Controllers;

use App\Services\ELearningService;
use App\Services\PermissionService;

class ELearningGestorController
{
    private ELearningService $service;

    public function __construct()
    {
        $this->service = new ELearningService();
    }

    public function dashboard(): void
    {
        $this->requireProfessor();
        $this->render('elearning/gestor/dashboard', [
            'title' => 'E-Learning Professor',
            'data' => $this->service->teacherDashboardData($this->userId()),
            'canEdit' => $this->canEdit(),
            'canDelete' => $this->canDelete(),
        ]);
    }

    public function cursos(): void
    {
        $this->requireProfessor();
        $this->render('elearning/gestor/courses', [
            'title' => 'Cursos | E-Learning Professor',
            'data' => $this->service->teacherCoursesData($this->userId()),
            'canEdit' => $this->canEdit(),
            'canDelete' => $this->canDelete(),
        ]);
    }

    public function aulas(int $cursoId): void
    {
        $this->renderWorkspace($cursoId, 'lessons');
    }

    public function provas(int $cursoId): void
    {
        $this->renderWorkspace($cursoId, 'exams');
    }

    public function matriculas(int $cursoId): void
    {
        $this->renderWorkspace($cursoId, 'students');
    }

    public function progressoDashboard(int $cursoId): void
    {
        $this->renderWorkspace($cursoId, 'reports');
    }

    public function armazenamento(): void
    {
        $this->requireProfessor();
        $this->render('elearning/gestor/storage', [
            'title' => 'Armazenamento | E-Learning Professor',
            'data' => $this->service->storagePageData($this->userId()),
        ]);
    }

    public function relatorios(): void
    {
        $this->requireProfessor();
        $this->render('elearning/gestor/reports', [
            'title' => 'Relatórios | E-Learning Professor',
            'data' => $this->service->teacherReportsData($this->userId()),
        ]);
    }

    public function diplomaConfig(): void
    {
        $this->requireProfessor();
        $coursesData = $this->service->teacherCoursesData($this->userId());
        $this->render('elearning/gestor/certificate_library', [
            'title' => 'Biblioteca de Certificados',
            'templates' => $this->service->getCertificateTemplates(),
            'storage' => $this->service->getStorageSummary(),
            'courses' => $coursesData['courses'] ?? [],
            'schemaReady' => $coursesData['schema_ready'] ?? false,
        ]);
    }

    public function thumbnailCurso(): void
    {
        $courseId = (int) ($_GET['id'] ?? 0);
        $file = $courseId > 0 ? $this->service->courseCoverData($courseId) : null;
        if (!$file) {
            header('Location: /assets/logo.png');
            exit;
        }

        header('Content-Type: ' . $file['mime']);
        header('Content-Length: ' . filesize($file['path']));
        readfile($file['path']);
        exit;
    }

    public function storeCurso(): void
    {
        $this->requireProfessor();
        $this->authorizeEdit();

        try {
            $courseId = $this->service->createCourse($_POST, $_FILES, $this->userId());
            $this->json(['success' => true, 'message' => 'Curso criado com sucesso.', 'course_id' => $courseId]);
        } catch (\Throwable $exception) {
            $this->jsonError($exception);
        }
    }

    public function updateCurso(): void
    {
        $this->requireProfessor();
        $this->authorizeEdit();

        try {
            $courseId = (int) ($_POST['id'] ?? 0);
            $this->service->updateCourse($courseId, $_POST, $_FILES, $this->userId());
            $this->json(['success' => true, 'message' => 'Curso atualizado com sucesso.']);
        } catch (\Throwable $exception) {
            $this->jsonError($exception);
        }
    }

    public function deleteCurso(): void
    {
        $this->requireProfessor();
        $this->authorizeDelete();

        try {
            $result = $this->service->deleteCourse((int) ($_POST['id'] ?? 0), $this->userId());
            $warnings = $result['warnings'] ?? [];
            $message = $warnings
                ? 'Curso excluido com sucesso, mas alguns videos externos podem precisar de conferencia manual.'
                : 'Curso excluido com sucesso. Os videos hospedados e arquivos vinculados tambem foram removidos.';
            $this->json([
                'success' => true,
                'message' => $message,
                'data' => $result,
            ]);
        } catch (\Throwable $exception) {
            $this->jsonError($exception);
        }
    }

    public function storeAula(): void
    {
        $this->requireProfessor();
        $this->authorizeEdit();

        try {
            $lessonId = $this->service->saveLesson($_POST, $_FILES, $this->userId());
            $hasVideoUpload = !empty($_FILES['video']['tmp_name']);
            $message = $hasVideoUpload
                ? 'Aula salva com sucesso. O video pode levar alguns instantes para ficar pronto no Bunny Stream.'
                : 'Aula salva com sucesso.';
            $this->json(['success' => true, 'message' => $message, 'lesson_id' => $lessonId]);
        } catch (\Throwable $exception) {
            $this->jsonError($exception);
        }
    }

    public function deleteAula(): void
    {
        $this->requireProfessor();
        $this->authorizeDelete();

        try {
            $this->service->deleteLesson((int) ($_POST['id'] ?? 0), $this->userId());
            $this->json(['success' => true, 'message' => 'Aula removida com sucesso.']);
        } catch (\Throwable $exception) {
            $this->jsonError($exception);
        }
    }

    public function reorderAula(): void
    {
        $this->requireProfessor();
        $this->authorizeEdit();

        try {
            $this->service->reorderLesson(
                (int) ($_POST['lesson_id'] ?? 0),
                (string) ($_POST['direction'] ?? 'up'),
                $this->userId()
            );
            $this->json(['success' => true, 'message' => 'Ordem das aulas atualizada.']);
        } catch (\Throwable $exception) {
            $this->jsonError($exception);
        }
    }

    public function uploadMaterial(): void
    {
        $this->storeAula();
    }

    public function updateMaterial(): void
    {
        $this->storeAula();
    }

    public function deleteMaterial(): void
    {
        $this->requireProfessor();
        $this->authorizeDelete();

        try {
            $type = (string) ($_POST['type'] ?? '');
            if ($type === 'video') {
                $this->service->removeLessonVideo((int) ($_POST['lesson_id'] ?? 0), $this->userId());
            } else {
                $this->service->removeAttachment((int) ($_POST['attachment_id'] ?? 0), $this->userId());
            }

            $this->json(['success' => true, 'message' => 'Arquivo removido com sucesso.']);
        } catch (\Throwable $exception) {
            $this->jsonError($exception);
        }
    }

    public function storeProva(): void
    {
        $this->requireProfessor();
        $this->authorizeEdit();

        try {
            $examId = $this->service->saveExam($_POST, $this->userId());
            $this->json(['success' => true, 'message' => 'Prova salva com sucesso.', 'exam_id' => $examId]);
        } catch (\Throwable $exception) {
            $this->jsonError($exception);
        }
    }

    public function storeQuestao(): void
    {
        $this->json([
            'success' => false,
            'message' => 'Use o construtor de prova integrado na tela do curso para salvar questões e alternativas.',
        ]);
    }

    public function deleteProva(): void
    {
        $this->requireProfessor();
        $this->authorizeDelete();

        try {
            $this->service->deleteExam((int) ($_POST['id'] ?? 0), $this->userId());
            $this->json(['success' => true, 'message' => 'Prova arquivada com sucesso.']);
        } catch (\Throwable $exception) {
            $this->jsonError($exception);
        }
    }

    public function matricularColaborador(): void
    {
        $this->requireProfessor();
        $this->authorizeEdit();

        try {
            $this->service->enrollStudent(
                (int) ($_POST['course_id'] ?? $_POST['id_curso'] ?? 0),
                (int) ($_POST['student_id'] ?? $_POST['id_usuario'] ?? 0),
                $this->userId()
            );
            $this->json(['success' => true, 'message' => 'Aluno matriculado com sucesso.']);
        } catch (\Throwable $exception) {
            $this->jsonError($exception);
        }
    }

    public function emitirCertificado(): void
    {
        $this->requireProfessor();
        $this->authorizeEdit();

        try {
            $certificate = $this->service->issueCertificateFromEnrollment((int) ($_POST['enrollment_id'] ?? $_POST['matricula_id'] ?? 0), $this->userId());
            $this->json([
                'success' => true,
                'message' => 'Certificado emitido com sucesso.',
                'validation_code' => $certificate['validation_code'] ?? null,
            ]);
        } catch (\Throwable $exception) {
            $this->jsonError($exception);
        }
    }

    public function saveDiplomaConfig(): void
    {
        $this->requireProfessor();
        $this->authorizeEdit();

        try {
            $this->service->saveCourseCertificateConfig($_POST, $_FILES, $this->userId());
            $this->json(['success' => true, 'message' => 'Configuração de certificado salva com sucesso.']);
        } catch (\Throwable $exception) {
            $this->jsonError($exception);
        }
    }

    public function diplomaLogo(): void
    {
        http_response_code(404);
        echo 'Recurso movido para a configuração por curso.';
    }

    public function streamLessonVideo(int $lessonId): void
    {
        $this->requireProfessor();
        $file = $this->service->lessonVideoData($lessonId, $this->userId(), true);
        if (!$file) {
            http_response_code(404);
            echo 'Vídeo não encontrado.';
            return;
        }

        if (($file['provider'] ?? '') === 'bunny' && !empty($file['playback_url'])) {
            if (empty($file['is_ready'])) {
                http_response_code(202);
                header('Content-Type: text/html; charset=UTF-8');
                echo '<!doctype html><html lang="pt-br"><head><meta charset="utf-8"><meta http-equiv="refresh" content="8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Video em processamento</title><style>body{margin:0;font-family:Outfit,Segoe UI,sans-serif;background:#0f172a;color:#e2e8f0;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}.card{max-width:640px;background:#111827;border:1px solid #1f2937;border-radius:24px;padding:32px;box-shadow:0 25px 60px rgba(0,0,0,.35)}h1{margin:0 0 12px;font-size:32px}p{line-height:1.6;color:#cbd5e1}a{display:inline-block;margin-top:16px;padding:12px 18px;border-radius:999px;background:#38bdf8;color:#082f49;text-decoration:none;font-weight:700}</style></head><body><div class="card"><h1>Video em processamento</h1><p>' . htmlspecialchars((string) ($file['processing_message'] ?? 'O Bunny Stream ainda esta preparando o video desta aula.'), ENT_QUOTES, 'UTF-8') . '</p><p>Esta tela atualiza sozinha em alguns segundos.</p><a href="javascript:window.location.reload()">Atualizar pagina agora</a></div></body></html>';
                return;
            }
            header('Location: ' . $file['playback_url']);
            exit;
        }

        $this->streamFile($file['path'], $file['mime'], false, $file['name'], true);
    }

    public function videoStatusAula(int $lessonId): void
    {
        $this->requireProfessor();

        try {
            $file = $this->service->lessonVideoData($lessonId, $this->userId(), true);
            if (!$file) {
                $this->json([
                    'success' => true,
                    'lesson_id' => $lessonId,
                    'has_video' => false,
                ]);
            }

            $this->json([
                'success' => true,
                'lesson_id' => $lessonId,
                'has_video' => true,
                'provider' => (string) ($file['provider'] ?? 'local'),
                'status' => (string) ($file['status'] ?? ''),
                'status_label' => (string) ($file['status_label'] ?? 'Pronto'),
                'is_ready' => !empty($file['is_ready']),
                'processing_message' => (string) ($file['processing_message'] ?? ''),
                'duration_human' => (string) ($file['duration_human'] ?? ''),
                'playback_url' => (string) ($file['playback_url'] ?? ''),
                'embed_url' => (string) ($file['embed_url'] ?? ''),
                'name' => (string) ($file['name'] ?? ''),
            ]);
        } catch (\Throwable $exception) {
            $this->jsonError($exception);
        }
    }

    public function downloadAttachment(int $attachmentId): void
    {
        $this->requireProfessor();
        $file = $this->service->attachmentData($attachmentId, $this->userId(), true);
        if (!$file) {
            http_response_code(404);
            echo 'Anexo não encontrado.';
            return;
        }

        $this->streamFile($file['path'], $file['mime'], true, $file['name']);
    }

    private function renderWorkspace(int $courseId, string $tab): void
    {
        $this->requireProfessor();
        $this->render('elearning/gestor/workspace', [
            'title' => 'Gestão do Curso | E-Learning Professor',
            'data' => $this->service->teacherCourseWorkspaceData($this->userId(), $courseId),
            'activeTab' => $tab,
            'canEdit' => $this->canEdit(),
            'canDelete' => $this->canDelete(),
        ]);
    }

    private function requireProfessor(): void
    {
        AuthController::requireAuth();
        if (!PermissionService::hasPermission($this->userId(), 'elearning_gestor', 'view')) {
            http_response_code(403);
            echo '<h1>Acesso negado</h1>';
            exit;
        }
    }

    private function authorizeEdit(): void
    {
        if (!$this->canEdit()) {
            $this->json(['success' => false, 'message' => 'Sem permissão para editar.']);
        }
    }

    private function authorizeDelete(): void
    {
        if (!$this->canDelete()) {
            $this->json(['success' => false, 'message' => 'Sem permissão para excluir.']);
        }
    }

    private function canEdit(): bool
    {
        return PermissionService::hasPermission($this->userId(), 'elearning_gestor', 'edit');
    }

    private function canDelete(): bool
    {
        return PermissionService::hasPermission($this->userId(), 'elearning_gestor', 'delete');
    }

    private function userId(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../../views/pages/' . $view . '.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }

    private function json(array $payload): void
    {
        if (ob_get_level()) {
            ob_clean();
        }

        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    private function jsonError(\Throwable $exception): void
    {
        $this->json([
            'success' => false,
            'message' => $exception->getMessage(),
        ]);
    }

    private function streamFile(string $path, string $mime, bool $download, string $downloadName, bool $supportRange = false): void
    {
        if (!is_file($path)) {
            http_response_code(404);
            echo 'Arquivo não encontrado.';
            return;
        }

        $size = filesize($path);
        $start = 0;
        $end = $size - 1;

        header('Content-Type: ' . $mime);
        header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="' . rawurlencode($downloadName) . '"');
        header('Accept-Ranges: bytes');

        if ($supportRange && isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches)) {
            if ($matches[1] !== '') {
                $start = (int) $matches[1];
            }
            if ($matches[2] !== '') {
                $end = (int) $matches[2];
            }
            $end = min($end, $size - 1);
            if ($start > $end) {
                $start = 0;
                $end = $size - 1;
            }
            http_response_code(206);
            header("Content-Range: bytes {$start}-{$end}/{$size}");
        }

        $length = $end - $start + 1;
        header('Content-Length: ' . $length);

        $handle = fopen($path, 'rb');
        fseek($handle, $start);
        $remaining = $length;
        while (!feof($handle) && $remaining > 0) {
            $read = min(8192, $remaining);
            echo fread($handle, $read);
            $remaining -= $read;
            flush();
        }
        fclose($handle);
        exit;
    }
}
