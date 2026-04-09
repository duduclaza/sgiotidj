<?php

namespace App\Controllers;

use App\Services\ELearningService;
use App\Services\PermissionService;

class ELearningColaboradorController
{
    private ELearningService $service;

    public function __construct()
    {
        $this->service = new ELearningService();
    }

    public function meusCursos(): void
    {
        $this->requireAluno();
        $this->render('elearning/colaborador/dashboard', [
            'title' => 'E-Learning Aluno',
            'data' => $this->service->studentDashboardData($this->userId()),
        ]);
    }

    public function matricularSe(): void
    {
        $this->requireAluno();

        try {
            $this->service->selfEnroll((int) ($_POST['course_id'] ?? $_POST['curso_id'] ?? 0), $this->userId());
            $this->json(['success' => true, 'message' => 'Matrícula realizada com sucesso.']);
        } catch (\Throwable $exception) {
            $this->jsonError($exception);
        }
    }

    public function verCurso($cursoId): void
    {
        $this->requireAluno();

        try {
            $this->render('elearning/colaborador/course', [
                'title' => 'Curso | E-Learning Aluno',
                'data' => $this->service->studentCourseData($this->userId(), (int) $cursoId),
            ]);
        } catch (\Throwable $exception) {
            $this->renderError($exception->getMessage());
        }
    }

    public function continuar($cursoId): void
    {
        $this->requireAluno();

        try {
            $course = $this->service->studentCourseData($this->userId(), (int) $cursoId);
            foreach ($course['lessons'] as $lesson) {
                if ((int) ($lesson['is_completed'] ?? 0) !== 1) {
                    header('Location: /elearning/colaborador/materiais/' . (int) $lesson['id'] . '/assistir');
                    exit;
                }
            }

            if (!empty($course['lessons'][0]['id'])) {
                header('Location: /elearning/colaborador/materiais/' . (int) $course['lessons'][0]['id'] . '/assistir');
                exit;
            }
        } catch (\Throwable) {
        }

        header('Location: /elearning/colaborador');
        exit;
    }

    public function assistirAula($lessonId): void
    {
        $this->requireAluno();

        try {
            $this->render('elearning/colaborador/lesson', [
                'title' => 'Aula | E-Learning Aluno',
                'data' => $this->service->studentLessonData($this->userId(), (int) $lessonId),
            ]);
        } catch (\Throwable $exception) {
            $this->renderError($exception->getMessage());
        }
    }

    public function registrarProgresso(): void
    {
        $this->requireAluno();

        try {
            $result = $this->service->markLessonProgress(
                (int) ($_POST['lesson_id'] ?? $_POST['id_material'] ?? 0),
                $this->userId(),
                (float) ($_POST['progress_percent'] ?? $_POST['pct'] ?? 100)
            );
            $this->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $exception) {
            $this->jsonError($exception);
        }
    }

    public function fazerProva(int $provaId): void
    {
        $this->requireAluno();

        try {
            $this->render('elearning/colaborador/exam', [
                'title' => 'Prova | E-Learning Aluno',
                'data' => $this->service->examData($this->userId(), $provaId),
            ]);
        } catch (\Throwable $exception) {
            $this->renderError($exception->getMessage());
        }
    }

    public function submeterProva(): void
    {
        $this->requireAluno();

        try {
            $result = $this->service->submitExam(
                (int) ($_POST['exam_id'] ?? $_POST['prova_id'] ?? 0),
                $this->userId(),
                $_POST['answers'] ?? $_POST['respostas'] ?? []
            );
            $this->json([
                'success' => true,
                'message' => 'Prova enviada com sucesso.',
                'attempt_id' => $result['attempt_id'],
                'approved' => $result['approved'],
                'score_percent' => $result['score_percent'],
            ]);
        } catch (\Throwable $exception) {
            $this->jsonError($exception);
        }
    }

    public function resultadoProva(int $tentativaId): void
    {
        $this->requireAluno();

        try {
            $this->render('elearning/colaborador/exam_result', [
                'title' => 'Resultado da Prova',
                'data' => $this->service->examResultData($this->userId(), $tentativaId),
            ]);
        } catch (\Throwable $exception) {
            $this->renderError($exception->getMessage());
        }
    }

    public function meusCertificados(): void
    {
        $this->requireAluno();
        $this->render('elearning/colaborador/certificates', [
            'title' => 'Certificados',
            'data' => $this->service->studentCertificatesData($this->userId()),
        ]);
    }

    public function historico(): void
    {
        $this->requireAluno();
        $this->render('elearning/colaborador/history', [
            'title' => 'Histórico de Cursos',
            'data' => $this->service->studentHistoryData($this->userId()),
        ]);
    }

    public function downloadCertificado(string $codigo): void
    {
        $this->requireAluno();

        try {
            $data = $this->service->certificateDownloadData($codigo, $this->userId());
            extract($data, EXTR_OVERWRITE);
            include __DIR__ . '/../../views/pages/elearning/colaborador/certificate_print.php';
        } catch (\Throwable $exception) {
            $this->renderError($exception->getMessage());
        }
    }

    public function streamLessonVideo(int $lessonId): void
    {
        $this->requireAluno();
        $file = $this->service->lessonVideoData($lessonId, $this->userId(), false);
        if (!$file) {
            http_response_code(404);
            echo 'Vídeo não encontrado.';
            return;
        }

        if (($file['provider'] ?? '') === 'bunny' && !empty($file['playback_url'])) {
            if (empty($file['is_ready'])) {
                http_response_code(202);
                header('Content-Type: text/html; charset=UTF-8');
                echo '<!doctype html><html lang="pt-br"><head><meta charset="utf-8"><meta http-equiv="refresh" content="8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Video em processamento</title><style>body{margin:0;font-family:Outfit,Segoe UI,sans-serif;background:#020617;color:#e2e8f0;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}.card{max-width:640px;background:#0f172a;border:1px solid #1e293b;border-radius:24px;padding:32px;box-shadow:0 25px 60px rgba(0,0,0,.35)}h1{margin:0 0 12px;font-size:32px}p{line-height:1.6;color:#cbd5e1}a{display:inline-block;margin-top:16px;padding:12px 18px;border-radius:999px;background:#38bdf8;color:#082f49;text-decoration:none;font-weight:700}</style></head><body><div class="card"><h1>Video em processamento</h1><p>' . htmlspecialchars((string) ($file['processing_message'] ?? 'O Bunny Stream ainda esta preparando o video desta aula.'), ENT_QUOTES, 'UTF-8') . '</p><p>Esta tela atualiza sozinha em alguns segundos.</p><a href="javascript:window.location.reload()">Atualizar pagina agora</a></div></body></html>';
                return;
            }
            header('Location: ' . $file['playback_url']);
            exit;
        }

        $this->streamFile($file['path'], $file['mime'], false, $file['name'], true);
    }

    public function videoStatusAula(int $lessonId): void
    {
        $this->requireAluno();

        try {
            $file = $this->service->lessonVideoData($lessonId, $this->userId(), false);
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
        $this->requireAluno();
        $file = $this->service->attachmentData($attachmentId, $this->userId(), false);
        if (!$file) {
            http_response_code(404);
            echo 'Anexo não encontrado.';
            return;
        }

        $this->streamFile($file['path'], $file['mime'], true, $file['name']);
    }

    private function requireAluno(): void
    {
        AuthController::requireAuth();
        if (!PermissionService::hasPermission($this->userId(), 'elearning_colaborador', 'view')) {
            http_response_code(403);
            echo '<h1>Acesso negado</h1>';
            exit;
        }
    }

    private function userId(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../../views/pages/' . $view . '.php';
        include __DIR__ . '/../../views/layouts/elearning_student.php';
    }

    private function renderError(string $message): void
    {
        http_response_code(422);
        $this->render('elearning/colaborador/error', [
            'title' => 'E-Learning Aluno',
            'message' => $message,
        ]);
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
