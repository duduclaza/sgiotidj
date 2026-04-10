<?php

namespace App\Services;

use App\Config\Database;
use PDO;
use RuntimeException;

class ELearningService
{
    public const DEFAULT_STORAGE_LIMIT_MINUTES = 10000;
    public const DEFAULT_STORAGE_LIMIT_SECONDS = 600000; // 10.000 min
    public const LEGACY_STORAGE_LIMIT_BYTES = 536870912000; // 500 GB
    public const LEGACY_STORAGE_LIMIT_BYTES_V2 = 53687091200; // 50 GB
    public const VIDEO_LIMIT_BYTES = 83886080; // 80 MB
    public const ATTACHMENT_LIMIT_BYTES = 20971520; // 20 MB
    public const PASSING_SCORE = 70.0;

    private PDO $db;
    private BunnyStreamService $bunnyStream;
    private ?bool $schemaReady = null;
    private string $basePath;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance();
        $this->bunnyStream = new BunnyStreamService();
        $this->basePath = dirname(__DIR__, 2);
    }

    public function schemaReady(): bool
    {
        if ($this->schemaReady !== null) {
            return $this->schemaReady;
        }

        try {
            $stmt = $this->db->query("SHOW TABLES LIKE 'elearning_courses'");
            $this->schemaReady = (bool) $stmt->fetchColumn();
        } catch (\Throwable) {
            $this->schemaReady = false;
        }

        return $this->schemaReady;
    }

    public function teacherDashboardData(int $teacherId): array
    {
        $storage = $this->getStorageSummary();

        if (!$this->schemaReady()) {
            return [
                'schema_ready' => false,
                'stats' => [
                    'total_courses' => 0,
                    'total_lessons' => 0,
                    'total_students' => 0,
                    'published_courses' => 0,
                    'certificates_issued' => 0,
                    'exams_applied' => 0,
                    'approval_rate' => 0,
                ],
                'courses' => [],
                'charts' => [
                    'enrollments' => [],
                    'conclusions' => [],
                ],
                'storage' => $storage,
                'templates' => $this->getCertificateTemplates(),
            ];
        }

        $stats = [
            'total_courses' => (int) $this->fetchValue(
                "SELECT COUNT(*) FROM elearning_courses WHERE teacher_id = ? AND deleted_at IS NULL",
                [$teacherId]
            ),
            'total_lessons' => (int) $this->fetchValue(
                "SELECT COUNT(*) FROM elearning_lessons l
                 INNER JOIN elearning_courses c ON c.id = l.course_id
                 WHERE c.teacher_id = ? AND c.deleted_at IS NULL AND l.deleted_at IS NULL",
                [$teacherId]
            ),
            'total_students' => (int) $this->fetchValue(
                "SELECT COUNT(DISTINCT e.student_id) FROM elearning_enrollments e
                 INNER JOIN elearning_courses c ON c.id = e.course_id
                 WHERE c.teacher_id = ? AND c.deleted_at IS NULL AND e.deleted_at IS NULL",
                [$teacherId]
            ),
            'published_courses' => (int) $this->fetchValue(
                "SELECT COUNT(*) FROM elearning_courses
                 WHERE teacher_id = ? AND status = 'published' AND deleted_at IS NULL",
                [$teacherId]
            ),
            'certificates_issued' => (int) $this->fetchValue(
                "SELECT COUNT(*) FROM elearning_certificates cert
                 INNER JOIN elearning_courses c ON c.id = cert.course_id
                 WHERE c.teacher_id = ? AND cert.deleted_at IS NULL AND c.deleted_at IS NULL",
                [$teacherId]
            ),
            'exams_applied' => (int) $this->fetchValue(
                "SELECT COUNT(*) FROM elearning_exam_attempts a
                 INNER JOIN elearning_courses c ON c.id = a.course_id
                 WHERE c.teacher_id = ? AND a.status <> 'started' AND c.deleted_at IS NULL",
                [$teacherId]
            ),
            'approval_rate' => 0,
        ];

        $approvedAttempts = (int) $this->fetchValue(
            "SELECT COUNT(*) FROM elearning_exam_attempts a
             INNER JOIN elearning_courses c ON c.id = a.course_id
             WHERE c.teacher_id = ? AND a.status = 'approved' AND c.deleted_at IS NULL",
            [$teacherId]
        );
        $submittedAttempts = (int) $this->fetchValue(
            "SELECT COUNT(*) FROM elearning_exam_attempts a
             INNER JOIN elearning_courses c ON c.id = a.course_id
             WHERE c.teacher_id = ? AND a.status IN ('approved','failed') AND c.deleted_at IS NULL",
            [$teacherId]
        );
        $stats['approval_rate'] = $submittedAttempts > 0
            ? round(($approvedAttempts / $submittedAttempts) * 100, 2)
            : 0;

        $courses = $this->fetchAll(
            "SELECT c.id, c.title, c.category, c.status, c.workload_hours, c.cover_path,
                    c.certificate_template_id, c.certificate_settings, c.created_at, u.name AS teacher_name,
                    COUNT(DISTINCT l.id) AS lessons_count,
                    COUNT(DISTINCT e.id) AS enrollments_count,
                    ROUND(AVG(COALESCE(e.progress_percent, 0)), 2) AS avg_progress,
                    COUNT(DISTINCT cert.id) AS certificates_count
             FROM elearning_courses c
             LEFT JOIN users u ON u.id = c.teacher_id
             LEFT JOIN elearning_lessons l ON l.course_id = c.id AND l.deleted_at IS NULL
             LEFT JOIN elearning_enrollments e ON e.course_id = c.id AND e.deleted_at IS NULL
             LEFT JOIN elearning_certificates cert ON cert.course_id = c.id AND cert.deleted_at IS NULL
             WHERE c.teacher_id = ? AND c.deleted_at IS NULL
             GROUP BY c.id
             ORDER BY c.updated_at DESC, c.created_at DESC",
            [$teacherId]
        );

        $enrollmentChart = $this->fetchAll(
            "SELECT c.title AS label, COUNT(e.id) AS value
             FROM elearning_courses c
             LEFT JOIN elearning_enrollments e ON e.course_id = c.id AND e.deleted_at IS NULL
             WHERE c.teacher_id = ? AND c.deleted_at IS NULL
             GROUP BY c.id
             ORDER BY value DESC, c.title ASC
             LIMIT 6",
            [$teacherId]
        );

        $conclusionChart = $this->fetchAll(
            "SELECT c.title AS label,
                    SUM(CASE WHEN e.status IN ('approved','completed') THEN 1 ELSE 0 END) AS value
             FROM elearning_courses c
             LEFT JOIN elearning_enrollments e ON e.course_id = c.id AND e.deleted_at IS NULL
             WHERE c.teacher_id = ? AND c.deleted_at IS NULL
             GROUP BY c.id
             ORDER BY value DESC, c.title ASC
             LIMIT 6",
            [$teacherId]
        );

        return [
            'schema_ready' => true,
            'stats' => $stats,
            'courses' => array_map(fn(array $course) => $this->formatCourseCard($course), $courses),
            'charts' => [
                'enrollments' => $enrollmentChart,
                'conclusions' => $conclusionChart,
            ],
            'storage' => $storage,
            'templates' => $this->getCertificateTemplates(),
        ];
    }

    public function teacherCoursesData(int $teacherId): array
    {
        $dashboard = $this->teacherDashboardData($teacherId);

        return [
            'schema_ready' => $dashboard['schema_ready'],
            'courses' => $dashboard['courses'],
            'stats' => $dashboard['stats'],
            'teachers' => $this->listTeacherOptions(),
            'categories' => $this->listCourseCategories(),
            'storage' => $dashboard['storage'],
        ];
    }

    public function teacherReportsData(int $teacherId): array
    {
        $storage = $this->getStorageSummary();

        if (!$this->schemaReady()) {
            return [
                'schema_ready' => false,
                'courses' => [],
                'storage' => $storage,
            ];
        }

        $courses = $this->fetchAll(
            "SELECT c.id, c.title, c.category, c.status,
                    COUNT(DISTINCT e.id) AS enrollments_count,
                    ROUND(AVG(COALESCE(e.progress_percent, 0)), 2) AS avg_progress,
                    SUM(CASE WHEN e.status IN ('approved','completed') THEN 1 ELSE 0 END) AS completed_count,
                    SUM(CASE WHEN e.status = 'failed' THEN 1 ELSE 0 END) AS failed_count,
                    COUNT(DISTINCT cert.id) AS certificates_count,
                    SUM(CASE WHEN a.status = 'approved' THEN 1 ELSE 0 END) AS approved_attempts,
                    SUM(CASE WHEN a.status IN ('approved','failed') THEN 1 ELSE 0 END) AS submitted_attempts
             FROM elearning_courses c
             LEFT JOIN elearning_enrollments e ON e.course_id = c.id AND e.deleted_at IS NULL
             LEFT JOIN elearning_certificates cert ON cert.course_id = c.id AND cert.deleted_at IS NULL
             LEFT JOIN elearning_exam_attempts a ON a.course_id = c.id
             WHERE c.teacher_id = ? AND c.deleted_at IS NULL
             GROUP BY c.id
             ORDER BY c.title ASC",
            [$teacherId]
        );

        foreach ($courses as &$course) {
            $course = $this->formatCourseCard($course);
            $course['approval_rate'] = ((int) $course['submitted_attempts']) > 0
                ? round(((int) $course['approved_attempts'] / (int) $course['submitted_attempts']) * 100, 2)
                : 0;
        }
        unset($course);

        return [
            'schema_ready' => true,
            'courses' => $courses,
            'storage' => $storage,
        ];
    }

    public function storagePageData(int $teacherId): array
    {
        $summary = $this->getStorageSummary();

        if (!$this->schemaReady()) {
            return [
                'schema_ready' => false,
                'summary' => $summary,
                'courses' => [],
            ];
        }

        $courses = $this->courseVideoUsageMap($teacherId);

        return [
            'schema_ready' => true,
            'summary' => $summary,
            'courses' => array_values($courses),
        ];
    }

    public function teacherCourseWorkspaceData(int $teacherId, int $courseId): array
    {
        if (!$this->schemaReady()) {
            return [
                'schema_ready' => false,
                'course' => null,
                'lessons' => [],
                'exams' => [],
                'enrollments' => [],
                'users' => [],
                'templates' => $this->getCertificateTemplates(),
                'storage' => $this->getStorageSummary(),
                'reports' => [],
            ];
        }

        $course = $this->fetchOne(
            "SELECT c.*, u.name AS teacher_name
             FROM elearning_courses c
             LEFT JOIN users u ON u.id = c.teacher_id
             WHERE c.id = ? AND c.teacher_id = ? AND c.deleted_at IS NULL",
            [$courseId, $teacherId]
        );

        if (!$course) {
            throw new RuntimeException('Curso nÃƒÆ’Ã‚Â£o encontrado.');
        }

        $lessons = $this->fetchAll(
            "SELECT l.*,
                    v.id AS video_id,
                    v.file_path AS video_file_path,
                    v.file_name AS video_name,
                    v.size_bytes AS video_size_bytes,
                    COUNT(a.id) AS attachments_count
             FROM elearning_lessons l
             LEFT JOIN elearning_lesson_videos v ON v.lesson_id = l.id AND v.deleted_at IS NULL
             LEFT JOIN elearning_lesson_attachments a ON a.lesson_id = l.id AND a.deleted_at IS NULL
             WHERE l.course_id = ? AND l.deleted_at IS NULL
             GROUP BY l.id
             ORDER BY l.sequence_order ASC, l.id ASC",
            [$courseId]
        );

        $attachmentsByLesson = [];
        $attachments = $this->fetchAll(
            "SELECT * FROM elearning_lesson_attachments
             WHERE deleted_at IS NULL AND lesson_id IN (
                 SELECT id FROM elearning_lessons WHERE course_id = ? AND deleted_at IS NULL
             )
             ORDER BY created_at ASC",
            [$courseId]
        );
        foreach ($attachments as $attachment) {
            $attachmentsByLesson[(int) $attachment['lesson_id']][] = $attachment;
        }

        foreach ($lessons as &$lesson) {
            $videoMeta = $this->buildStoredVideoData([
                'id' => $lesson['video_id'] ?? null,
                'file_path' => $lesson['video_file_path'] ?? null,
                'file_name' => $lesson['video_name'] ?? null,
                'mime_type' => 'video/mp4',
                'size_bytes' => $lesson['video_size_bytes'] ?? 0,
                'estimated_minutes' => $lesson['estimated_minutes'] ?? 0,
            ], false);
            $lesson['video_size_human'] = !empty($lesson['video_size_bytes'])
                ? $this->formatBytes((int) $lesson['video_size_bytes'])
                : null;
            $lesson['video_provider'] = is_array($videoMeta) ? ($videoMeta['provider'] ?? null) : null;
            $lesson['video_duration_human'] = is_array($videoMeta) ? ($videoMeta['duration_human'] ?? null) : null;
            $lesson['video_embed_url'] = is_array($videoMeta) ? ($videoMeta['embed_url'] ?? null) : null;
            $lesson['video_play_url'] = is_array($videoMeta) ? ($videoMeta['playback_url'] ?? null) : null;
            $lesson['attachments'] = $attachmentsByLesson[(int) $lesson['id']] ?? [];
        }
        unset($lesson);

        $exams = $this->fetchAll(
            "SELECT e.*,
                    COUNT(DISTINCT q.id) AS questions_count,
                    COUNT(DISTINCT a.id) AS attempts_count
             FROM elearning_exams e
             LEFT JOIN elearning_exam_questions q ON q.exam_id = e.id AND q.deleted_at IS NULL
             LEFT JOIN elearning_exam_attempts a ON a.exam_id = e.id
             WHERE e.course_id = ? AND e.deleted_at IS NULL
             GROUP BY e.id
             ORDER BY e.created_at DESC",
            [$courseId]
        );

        foreach ($exams as &$exam) {
            $exam['questions'] = $this->fetchAll(
                "SELECT q.* FROM elearning_exam_questions q
                 WHERE q.exam_id = ? AND q.deleted_at IS NULL
                 ORDER BY q.sequence_order ASC, q.id ASC",
                [(int) $exam['id']]
            );
            foreach ($exam['questions'] as &$question) {
                $question['options'] = $this->fetchAll(
                    "SELECT * FROM elearning_exam_options
                     WHERE question_id = ? AND deleted_at IS NULL
                     ORDER BY sequence_order ASC, id ASC",
                    [(int) $question['id']]
                );
            }
            unset($question);
        }
        unset($exam);

        $enrollments = $this->fetchAll(
            "SELECT e.*, u.name AS student_name, u.email AS student_email,
                    MAX(a.score_percent) AS best_score
             FROM elearning_enrollments e
             INNER JOIN users u ON u.id = e.student_id
             LEFT JOIN elearning_exam_attempts a ON a.enrollment_id = e.id
             WHERE e.course_id = ? AND e.deleted_at IS NULL
             GROUP BY e.id
             ORDER BY e.updated_at DESC, e.created_at DESC",
            [$courseId]
        );

        $courseVideoSummary = $this->courseVideoSummary($courseId);

        $reports = [
            'average_progress' => (float) $this->fetchValue(
                "SELECT ROUND(AVG(progress_percent), 2) FROM elearning_enrollments
                 WHERE course_id = ? AND deleted_at IS NULL",
                [$courseId]
            ),
            'completed_students' => (int) $this->fetchValue(
                "SELECT COUNT(*) FROM elearning_enrollments
                 WHERE course_id = ? AND status IN ('approved','completed') AND deleted_at IS NULL",
                [$courseId]
            ),
            'pending_exams' => (int) $this->fetchValue(
                "SELECT COUNT(*) FROM elearning_enrollments
                 WHERE course_id = ? AND status = 'awaiting_exam' AND deleted_at IS NULL",
                [$courseId]
            ),
            'approval_rate' => 0,
            'course_video_seconds' => (int) ($courseVideoSummary['used_seconds'] ?? 0),
        ];

        $approved = (int) $this->fetchValue(
            "SELECT COUNT(*) FROM elearning_exam_attempts
             WHERE course_id = ? AND status = 'approved'",
            [$courseId]
        );
        $submitted = (int) $this->fetchValue(
            "SELECT COUNT(*) FROM elearning_exam_attempts
             WHERE course_id = ? AND status IN ('approved','failed')",
            [$courseId]
        );
        $reports['approval_rate'] = $submitted > 0 ? round(($approved / $submitted) * 100, 2) : 0;
        $reports['course_video_human'] = $courseVideoSummary['used_human'] ?? '0 min';

        return [
            'schema_ready' => true,
            'course' => $this->formatCourseDetail($course),
            'lessons' => $lessons,
            'exams' => $exams,
            'enrollments' => $enrollments,
            'users' => $this->listStudentOptions(),
            'templates' => $this->getCertificateTemplates(),
            'storage' => $this->getStorageSummary(),
            'reports' => $reports,
        ];
    }

    public function studentDashboardData(int $studentId): array
    {
        if (!$this->schemaReady()) {
            return [
                'schema_ready' => false,
                'stats' => [
                    'in_progress' => 0,
                    'completed' => 0,
                    'pending_exams' => 0,
                    'certificates' => 0,
                    'overall_progress' => 0,
                ],
                'enrolled_courses' => [],
                'available_courses' => [],
                'next_lesson' => null,
                'certificates' => [],
                'history' => [],
            ];
        }

        $stats = [
            'in_progress' => (int) $this->fetchValue(
                "SELECT COUNT(*) FROM elearning_enrollments
                 WHERE student_id = ? AND status IN ('in_progress','awaiting_exam','failed') AND deleted_at IS NULL",
                [$studentId]
            ),
            'completed' => (int) $this->fetchValue(
                "SELECT COUNT(*) FROM elearning_enrollments
                 WHERE student_id = ? AND status IN ('approved','completed') AND deleted_at IS NULL",
                [$studentId]
            ),
            'pending_exams' => (int) $this->fetchValue(
                "SELECT COUNT(*) FROM elearning_enrollments
                 WHERE student_id = ? AND status = 'awaiting_exam' AND deleted_at IS NULL",
                [$studentId]
            ),
            'certificates' => (int) $this->fetchValue(
                "SELECT COUNT(*) FROM elearning_certificates
                 WHERE student_id = ? AND deleted_at IS NULL",
                [$studentId]
            ),
            'overall_progress' => (float) $this->fetchValue(
                "SELECT ROUND(AVG(progress_percent), 2) FROM elearning_enrollments
                 WHERE student_id = ? AND deleted_at IS NULL",
                [$studentId]
            ),
        ];

        $enrolledCourses = $this->fetchAll(
            "SELECT c.id, c.title, c.category, c.cover_path, c.workload_hours, c.status,
                    e.progress_percent, e.status AS enrollment_status, e.updated_at,
                    u.name AS teacher_name
             FROM elearning_enrollments e
             INNER JOIN elearning_courses c ON c.id = e.course_id
             LEFT JOIN users u ON u.id = c.teacher_id
             WHERE e.student_id = ? AND e.deleted_at IS NULL AND c.deleted_at IS NULL
             ORDER BY e.updated_at DESC, e.created_at DESC",
            [$studentId]
        );

        $availableCourses = $this->fetchAll(
            "SELECT c.id, c.title, c.category, c.cover_path, c.workload_hours, c.description, u.name AS teacher_name
             FROM elearning_courses c
             LEFT JOIN users u ON u.id = c.teacher_id
             WHERE c.status = 'published' AND c.deleted_at IS NULL
               AND c.id NOT IN (
                   SELECT course_id FROM elearning_enrollments
                   WHERE student_id = ? AND deleted_at IS NULL
               )
             ORDER BY c.updated_at DESC, c.created_at DESC",
            [$studentId]
        );

        $nextLesson = $this->fetchOne(
            "SELECT l.id, l.title, c.title AS course_title, c.id AS course_id
             FROM elearning_enrollments e
             INNER JOIN elearning_courses c ON c.id = e.course_id
             INNER JOIN elearning_lessons l ON l.course_id = c.id AND l.deleted_at IS NULL
             LEFT JOIN elearning_student_progress sp ON sp.lesson_id = l.id AND sp.student_id = e.student_id
             WHERE e.student_id = ? AND e.deleted_at IS NULL AND c.deleted_at IS NULL
               AND (sp.is_completed IS NULL OR sp.is_completed = 0)
             ORDER BY e.updated_at DESC, l.sequence_order ASC
             LIMIT 1",
            [$studentId]
        );

        $certificates = $this->fetchAll(
            "SELECT cert.id, cert.validation_code, cert.issued_at, cert.score_percent,
                    c.title AS course_title
             FROM elearning_certificates cert
             INNER JOIN elearning_courses c ON c.id = cert.course_id
             WHERE cert.student_id = ? AND cert.deleted_at IS NULL
             ORDER BY cert.issued_at DESC",
            [$studentId]
        );

        $history = $this->fetchAll(
            "SELECT c.title, e.progress_percent, e.completed_at, e.status
             FROM elearning_enrollments e
             INNER JOIN elearning_courses c ON c.id = e.course_id
             WHERE e.student_id = ? AND e.status IN ('approved','completed') AND e.deleted_at IS NULL
             ORDER BY e.completed_at DESC, e.updated_at DESC",
            [$studentId]
        );

        return [
            'schema_ready' => true,
            'stats' => $stats,
            'enrolled_courses' => array_map(fn(array $course) => $this->formatStudentCourseCard($course), $enrolledCourses),
            'available_courses' => array_map(fn(array $course) => $this->formatStudentCourseCard($course), $availableCourses),
            'next_lesson' => $nextLesson,
            'certificates' => $certificates,
            'history' => $history,
        ];
    }

    public function studentCourseData(int $studentId, int $courseId): array
    {
        if (!$this->schemaReady()) {
            throw new RuntimeException('A base do mÃƒÆ’Ã‚Â³dulo E-Learning ainda nÃƒÆ’Ã‚Â£o foi instalada.');
        }

        $enrollment = $this->fetchOne(
            "SELECT * FROM elearning_enrollments
             WHERE student_id = ? AND course_id = ? AND deleted_at IS NULL",
            [$studentId, $courseId]
        );

        if (!$enrollment) {
            throw new RuntimeException('VocÃƒÆ’Ã‚Âª nÃƒÆ’Ã‚Â£o estÃƒÆ’Ã‚Â¡ matriculado neste curso.');
        }

        $course = $this->fetchOne(
            "SELECT c.*, u.name AS teacher_name
             FROM elearning_courses c
             LEFT JOIN users u ON u.id = c.teacher_id
             WHERE c.id = ? AND c.deleted_at IS NULL",
            [$courseId]
        );

        if (!$course) {
            throw new RuntimeException('Curso nÃƒÆ’Ã‚Â£o encontrado.');
        }

        $lessons = $this->fetchAll(
            "SELECT l.*, sp.video_progress_percent, sp.is_completed, sp.completed_at,
                    v.id AS video_id, v.file_name AS video_name
             FROM elearning_lessons l
             LEFT JOIN elearning_student_progress sp
               ON sp.lesson_id = l.id AND sp.student_id = ?
             LEFT JOIN elearning_lesson_videos v
               ON v.lesson_id = l.id AND v.deleted_at IS NULL
             WHERE l.course_id = ? AND l.deleted_at IS NULL
             ORDER BY l.sequence_order ASC, l.id ASC",
            [$studentId, $courseId]
        );

        $attachments = $this->fetchAll(
            "SELECT * FROM elearning_lesson_attachments
             WHERE deleted_at IS NULL AND lesson_id IN (
                SELECT id FROM elearning_lessons WHERE course_id = ? AND deleted_at IS NULL
             )
             ORDER BY created_at ASC",
            [$courseId]
        );
        $attachmentsByLesson = [];
        foreach ($attachments as $attachment) {
            $attachmentsByLesson[(int) $attachment['lesson_id']][] = $attachment;
        }

        foreach ($lessons as &$lesson) {
            $lesson['attachments'] = $attachmentsByLesson[(int) $lesson['id']] ?? [];
        }
        unset($lesson);

        $exams = $this->fetchAll(
            "SELECT e.*,
                    MAX(a.score_percent) AS best_score,
                    MAX(CASE WHEN a.status = 'approved' THEN 1 ELSE 0 END) AS approved,
                    COUNT(a.id) AS attempts_count
             FROM elearning_exams e
             LEFT JOIN elearning_exam_attempts a
               ON a.exam_id = e.id AND a.student_id = ?
             WHERE e.course_id = ? AND e.deleted_at IS NULL AND e.status = 'published'
             GROUP BY e.id
             ORDER BY e.created_at DESC",
            [$studentId, $courseId]
        );

        $certificate = $this->fetchOne(
            "SELECT * FROM elearning_certificates
             WHERE student_id = ? AND course_id = ? AND deleted_at IS NULL
             ORDER BY issued_at DESC LIMIT 1",
            [$studentId, $courseId]
        );

        return [
            'course' => $this->formatCourseDetail($course),
            'enrollment' => $enrollment,
            'lessons' => $lessons,
            'exams' => $exams,
            'certificate' => $certificate,
            'can_issue_certificate' => $this->isCertificateEligible($studentId, $courseId),
        ];
    }

    public function studentLessonData(int $studentId, int $lessonId): array
    {
        if (!$this->schemaReady()) {
            throw new RuntimeException('A base do mÃƒÆ’Ã‚Â³dulo E-Learning ainda nÃƒÆ’Ã‚Â£o foi instalada.');
        }

        $lesson = $this->fetchOne(
            "SELECT l.*, c.id AS course_id, c.title AS course_title, c.teacher_id,
                    u.name AS teacher_name,
                    v.id AS video_id, v.file_path AS video_file_path, v.file_name AS video_name, v.mime_type AS video_mime_type
             FROM elearning_lessons l
             INNER JOIN elearning_courses c ON c.id = l.course_id
             LEFT JOIN users u ON u.id = c.teacher_id
             LEFT JOIN elearning_lesson_videos v ON v.lesson_id = l.id AND v.deleted_at IS NULL
             WHERE l.id = ? AND l.deleted_at IS NULL AND c.deleted_at IS NULL",
            [$lessonId]
        );

        if (!$lesson) {
            throw new RuntimeException('Aula nÃƒÆ’Ã‚Â£o encontrada.');
        }

        $enrollment = $this->fetchOne(
            "SELECT * FROM elearning_enrollments
             WHERE student_id = ? AND course_id = ? AND deleted_at IS NULL",
            [$studentId, (int) $lesson['course_id']]
        );

        if (!$enrollment) {
            throw new RuntimeException('VocÃƒÆ’Ã‚Âª nÃƒÆ’Ã‚Â£o estÃƒÆ’Ã‚Â¡ matriculado neste curso.');
        }

        $progress = $this->fetchOne(
            "SELECT * FROM elearning_student_progress WHERE student_id = ? AND lesson_id = ?",
            [$studentId, $lessonId]
        );

        $attachments = $this->fetchAll(
            "SELECT * FROM elearning_lesson_attachments
             WHERE lesson_id = ? AND deleted_at IS NULL
             ORDER BY created_at ASC",
            [$lessonId]
        );

        $playlist = $this->fetchAll(
            "SELECT l.id, l.title, l.sequence_order, sp.is_completed
             FROM elearning_lessons l
             LEFT JOIN elearning_student_progress sp
               ON sp.lesson_id = l.id AND sp.student_id = ?
             WHERE l.course_id = ? AND l.deleted_at IS NULL
             ORDER BY l.sequence_order ASC, l.id ASC",
            [$studentId, (int) $lesson['course_id']]
        );

        $previousLessonId = null;
        $nextLessonId = null;
        foreach ($playlist as $index => $playlistLesson) {
            if ((int) $playlistLesson['id'] === $lessonId) {
                $previousLessonId = $playlist[$index - 1]['id'] ?? null;
                $nextLessonId = $playlist[$index + 1]['id'] ?? null;
                break;
            }
        }

        $lesson['video'] = $this->buildStoredVideoData([
            'id' => $lesson['video_id'] ?? null,
            'lesson_id' => $lesson['id'] ?? null,
            'file_path' => $lesson['video_file_path'] ?? null,
            'file_name' => $lesson['video_name'] ?? null,
            'mime_type' => $lesson['video_mime_type'] ?? 'video/mp4',
            'size_bytes' => 0,
            'estimated_minutes' => $lesson['estimated_minutes'] ?? 0,
        ], true);
        $lesson['video_provider'] = is_array($lesson['video']) ? ($lesson['video']['provider'] ?? null) : null;
        $lesson['video_embed_url'] = is_array($lesson['video']) ? ($lesson['video']['embed_url'] ?? null) : null;
        $lesson['video_play_url'] = is_array($lesson['video']) ? ($lesson['video']['playback_url'] ?? null) : null;

        return [
            'lesson' => $lesson,
            'enrollment' => $enrollment,
            'progress' => $progress,
            'attachments' => $attachments,
            'playlist' => $playlist,
            'previous_lesson_id' => $previousLessonId,
            'next_lesson_id' => $nextLessonId,
        ];
    }

    public function examData(int $studentId, int $examId): array
    {
        if (!$this->schemaReady()) {
            throw new RuntimeException('A base do mÃƒÆ’Ã‚Â³dulo E-Learning ainda nÃƒÆ’Ã‚Â£o foi instalada.');
        }

        $exam = $this->fetchOne(
            "SELECT e.*, c.title AS course_title, c.id AS course_id
             FROM elearning_exams e
             INNER JOIN elearning_courses c ON c.id = e.course_id
             WHERE e.id = ? AND e.deleted_at IS NULL AND e.status = 'published'",
            [$examId]
        );

        if (!$exam) {
            throw new RuntimeException('Prova nÃƒÆ’Ã‚Â£o encontrada.');
        }

        $enrollment = $this->fetchOne(
            "SELECT * FROM elearning_enrollments
             WHERE student_id = ? AND course_id = ? AND deleted_at IS NULL",
            [$studentId, (int) $exam['course_id']]
        );

        if (!$enrollment) {
            throw new RuntimeException('VocÃƒÆ’Ã‚Âª nÃƒÆ’Ã‚Â£o estÃƒÆ’Ã‚Â¡ matriculado neste curso.');
        }

        $completedLessons = (int) $this->fetchValue(
            "SELECT COUNT(*) FROM elearning_student_progress sp
             INNER JOIN elearning_lessons l ON l.id = sp.lesson_id
             WHERE sp.student_id = ? AND l.course_id = ? AND sp.is_completed = 1 AND l.deleted_at IS NULL",
            [$studentId, (int) $exam['course_id']]
        );
        $totalLessons = (int) $this->fetchValue(
            "SELECT COUNT(*) FROM elearning_lessons WHERE course_id = ? AND deleted_at IS NULL",
            [(int) $exam['course_id']]
        );
        if ($totalLessons > 0 && $completedLessons < $totalLessons) {
            throw new RuntimeException('Conclua todas as aulas do curso antes de realizar a prova.');
        }

        $attemptsCount = (int) $this->fetchValue(
            "SELECT COUNT(*) FROM elearning_exam_attempts
             WHERE exam_id = ? AND student_id = ?",
            [$examId, $studentId]
        );

        $questions = $this->fetchAll(
            "SELECT * FROM elearning_exam_questions
             WHERE exam_id = ? AND deleted_at IS NULL
             ORDER BY sequence_order ASC, id ASC",
            [$examId]
        );
        foreach ($questions as &$question) {
            $question['options'] = $this->fetchAll(
                "SELECT id, option_text, sequence_order
                 FROM elearning_exam_options
                 WHERE question_id = ? AND deleted_at IS NULL
                 ORDER BY sequence_order ASC, id ASC",
                [(int) $question['id']]
            );
        }
        unset($question);

        return [
            'exam' => $exam,
            'enrollment' => $enrollment,
            'questions' => $questions,
            'attempts_count' => $attemptsCount,
            'remaining_attempts' => max(0, (int) $exam['attempts_allowed'] - $attemptsCount),
        ];
    }

    public function examResultData(int $studentId, int $attemptId): array
    {
        if (!$this->schemaReady()) {
            throw new RuntimeException('A base do mÃƒÆ’Ã‚Â³dulo E-Learning ainda nÃƒÆ’Ã‚Â£o foi instalada.');
        }

        $attempt = $this->fetchOne(
            "SELECT a.*, e.title AS exam_title, e.passing_score, c.id AS course_id, c.title AS course_title
             FROM elearning_exam_attempts a
             INNER JOIN elearning_exams e ON e.id = a.exam_id
             INNER JOIN elearning_courses c ON c.id = a.course_id
             WHERE a.id = ? AND a.student_id = ?",
            [$attemptId, $studentId]
        );

        if (!$attempt) {
            throw new RuntimeException('Resultado nÃƒÆ’Ã‚Â£o encontrado.');
        }

        $answers = $this->fetchAll(
            "SELECT ans.*, q.statement, q.score, opt.option_text
             FROM elearning_exam_answers ans
             INNER JOIN elearning_exam_questions q ON q.id = ans.question_id
             LEFT JOIN elearning_exam_options opt ON opt.id = ans.selected_option_id
             WHERE ans.attempt_id = ?
             ORDER BY q.sequence_order ASC, q.id ASC",
            [$attemptId]
        );

        return [
            'attempt' => $attempt,
            'answers' => $answers,
            'certificate' => $this->fetchOne(
                "SELECT * FROM elearning_certificates
                 WHERE student_id = ? AND course_id = ? AND deleted_at IS NULL",
                [$studentId, (int) $attempt['course_id']]
            ),
        ];
    }

    public function studentCertificatesData(int $studentId): array
    {
        if (!$this->schemaReady()) {
            return ['schema_ready' => false, 'certificates' => []];
        }

        return [
            'schema_ready' => true,
            'certificates' => $this->fetchAll(
                "SELECT cert.*, c.title AS course_title, c.workload_hours, tpl.name AS template_name
                 FROM elearning_certificates cert
                 INNER JOIN elearning_courses c ON c.id = cert.course_id
                 LEFT JOIN elearning_certificate_templates tpl ON tpl.id = cert.template_id
                 WHERE cert.student_id = ? AND cert.deleted_at IS NULL
                 ORDER BY cert.issued_at DESC",
                [$studentId]
            ),
        ];
    }

    public function studentHistoryData(int $studentId): array
    {
        if (!$this->schemaReady()) {
            return ['schema_ready' => false, 'courses' => []];
        }

        return [
            'schema_ready' => true,
            'courses' => $this->fetchAll(
                "SELECT c.title, c.category, c.workload_hours, e.progress_percent, e.status, e.completed_at
                 FROM elearning_enrollments e
                 INNER JOIN elearning_courses c ON c.id = e.course_id
                 WHERE e.student_id = ? AND e.deleted_at IS NULL
                 ORDER BY COALESCE(e.completed_at, e.updated_at) DESC",
                [$studentId]
            ),
        ];
    }

    public function createCourse(array $input, array $files, int $actorId): int
    {
        $this->assertSchemaReady();

        $title = trim((string) ($input['title'] ?? ''));
        if ($title === '') {
            throw new RuntimeException('Informe o tÃƒÆ’Ã‚Â­tulo do curso.');
        }

        $teacherId = (int) ($input['teacher_id'] ?? $actorId);
        $coverPath = $this->saveCourseCover($files['cover'] ?? null);
        $settings = $this->buildCertificateSettings($input, $files, 0);

        $stmt = $this->db->prepare(
            "INSERT INTO elearning_courses
                (title, description, category, cover_path, workload_hours, status, teacher_id,
                 certificate_template_id, certificate_settings, created_at, updated_at)
             VALUES
                (:title, :description, :category, :cover_path, :workload_hours, :status, :teacher_id,
                 :certificate_template_id, :certificate_settings, NOW(), NOW())"
        );
        $stmt->execute([
            ':title' => $title,
            ':description' => trim((string) ($input['description'] ?? '')),
            ':category' => trim((string) ($input['category'] ?? 'Geral')),
            ':cover_path' => $coverPath,
            ':workload_hours' => max(0, (int) ($input['workload_hours'] ?? 0)),
            ':status' => $this->normalizeCourseStatus((string) ($input['status'] ?? 'draft')),
            ':teacher_id' => $teacherId,
            ':certificate_template_id' => (int) ($input['certificate_template_id'] ?? 1) ?: 1,
            ':certificate_settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
        ]);

        $courseId = (int) $this->db->lastInsertId();
        $this->moveDeferredCertificateFiles($courseId, $settings);
        $this->logAction($actorId, 'teacher', 'course.created', 'course', $courseId, ['title' => $title]);

        return $courseId;
    }

    public function updateCourse(int $courseId, array $input, array $files, int $actorId): void
    {
        $this->assertSchemaReady();
        $course = $this->getOwnedCourse($courseId, $actorId);

        $title = trim((string) ($input['title'] ?? ''));
        if ($title === '') {
            throw new RuntimeException('Informe o tÃƒÆ’Ã‚Â­tulo do curso.');
        }

        $coverPath = $course['cover_path'];
        if (!empty($files['cover']) && (int) ($files['cover']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $newCoverPath = $this->saveCourseCover($files['cover']);
            if ($newCoverPath) {
                $this->deleteStoredFile($coverPath);
                $coverPath = $newCoverPath;
            }
        }

        $settings = $this->buildCertificateSettings(
            array_merge($input, ['existing_certificate_settings' => $course['certificate_settings'] ?? null]),
            $files,
            $courseId
        );

        $stmt = $this->db->prepare(
            "UPDATE elearning_courses SET
                title = :title,
                description = :description,
                category = :category,
                cover_path = :cover_path,
                workload_hours = :workload_hours,
                status = :status,
                teacher_id = :teacher_id,
                certificate_template_id = :certificate_template_id,
                certificate_settings = :certificate_settings,
                updated_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute([
            ':title' => $title,
            ':description' => trim((string) ($input['description'] ?? '')),
            ':category' => trim((string) ($input['category'] ?? 'Geral')),
            ':cover_path' => $coverPath,
            ':workload_hours' => max(0, (int) ($input['workload_hours'] ?? 0)),
            ':status' => $this->normalizeCourseStatus((string) ($input['status'] ?? 'draft')),
            ':teacher_id' => (int) ($input['teacher_id'] ?? $course['teacher_id']),
            ':certificate_template_id' => (int) ($input['certificate_template_id'] ?? $course['certificate_template_id'] ?? 1) ?: 1,
            ':certificate_settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
            ':id' => $courseId,
        ]);

        $this->moveDeferredCertificateFiles($courseId, $settings);
        $this->logAction($actorId, 'teacher', 'course.updated', 'course', $courseId, ['title' => $title]);
    }

    public function deleteCourse(int $courseId, int $actorId): array
    {
        $this->assertSchemaReady();
        $course = $this->getOwnedCourse($courseId, $actorId);
        $assets = $this->collectCourseAssetsForDeletion($course);

        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM elearning_courses WHERE id = ?")
                ->execute([$courseId]);
            $this->db->commit();
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }

        $warnings = $this->cleanupCourseAssetsAfterDeletion($assets);
        $this->recalculateStorageSummary();
        $this->logAction($actorId, 'teacher', 'course.deleted', 'course', $courseId, [
            'title' => (string) ($course['title'] ?? ''),
            'videos_removed' => count($assets['videos'] ?? []),
            'attachments_removed' => count($assets['attachments'] ?? []),
            'warnings' => $warnings,
        ]);

        return [
            'course_id' => $courseId,
            'title' => (string) ($course['title'] ?? ''),
            'videos_removed' => count($assets['videos'] ?? []),
            'attachments_removed' => count($assets['attachments'] ?? []),
            'warnings' => $warnings,
        ];
    }

    public function saveLesson(array $input, array $files, int $actorId): int
    {
        $this->assertSchemaReady();

        $courseId = (int) ($input['course_id'] ?? 0);
        $title = trim((string) ($input['title'] ?? ''));
        if ($courseId <= 0 || $title === '') {
            throw new RuntimeException('Informe curso e tÃƒÆ’Ã‚Â­tulo da aula.');
        }

        $this->getOwnedCourse($courseId, $actorId);
        $lessonId = (int) ($input['lesson_id'] ?? 0);

        if ($lessonId > 0) {
            $stmt = $this->db->prepare(
                "UPDATE elearning_lessons SET
                    title = :title,
                    description = :description,
                    sequence_order = :sequence_order,
                    estimated_minutes = :estimated_minutes,
                    updated_at = NOW()
                 WHERE id = :id AND course_id = :course_id"
            );
            $stmt->execute([
                ':title' => $title,
                ':description' => trim((string) ($input['description'] ?? '')),
                ':sequence_order' => max(1, (int) ($input['sequence_order'] ?? 1)),
                ':estimated_minutes' => max(0, (int) ($input['estimated_minutes'] ?? 0)),
                ':id' => $lessonId,
                ':course_id' => $courseId,
            ]);
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO elearning_lessons
                    (course_id, title, description, sequence_order, estimated_minutes, created_at, updated_at)
                 VALUES
                    (:course_id, :title, :description, :sequence_order, :estimated_minutes, NOW(), NOW())"
            );
            $stmt->execute([
                ':course_id' => $courseId,
                ':title' => $title,
                ':description' => trim((string) ($input['description'] ?? '')),
                ':sequence_order' => max(1, (int) ($input['sequence_order'] ?? ($this->nextLessonOrder($courseId)))),
                ':estimated_minutes' => max(0, (int) ($input['estimated_minutes'] ?? 0)),
            ]);
            $lessonId = (int) $this->db->lastInsertId();
        }

        if (!empty($files['video']) && (int) ($files['video']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $this->storeLessonVideo($courseId, $lessonId, $files['video'], $actorId);
        }

        if (!empty($files['attachments'])) {
            $this->storeLessonAttachments($courseId, $lessonId, $files['attachments'], $actorId);
        }

        $this->syncEnrollmentStatusesByCourse($courseId);
        $this->logAction($actorId, 'teacher', 'lesson.saved', 'lesson', $lessonId, ['course_id' => $courseId]);

        return $lessonId;
    }

    public function deleteLesson(int $lessonId, int $actorId): void
    {
        $this->assertSchemaReady();
        $lesson = $this->fetchOne(
            "SELECT l.*, c.teacher_id
             FROM elearning_lessons l
             INNER JOIN elearning_courses c ON c.id = l.course_id
             WHERE l.id = ? AND l.deleted_at IS NULL",
            [$lessonId]
        );

        if (!$lesson || (int) $lesson['teacher_id'] !== $actorId) {
            throw new RuntimeException('Aula nÃƒÆ’Ã‚Â£o encontrada.');
        }

        $video = $this->fetchOne(
            "SELECT * FROM elearning_lesson_videos
             WHERE lesson_id = ? AND deleted_at IS NULL",
            [$lessonId]
        );
        if ($video) {
            $this->deleteVideoRecord($video, true);
        }

        $attachments = $this->fetchAll(
            "SELECT * FROM elearning_lesson_attachments
             WHERE lesson_id = ? AND deleted_at IS NULL",
            [$lessonId]
        );
        foreach ($attachments as $attachment) {
            $this->deleteStoredFile((string) ($attachment['file_path'] ?? ''));
        }
        if ($attachments) {
            $this->db->prepare(
                "UPDATE elearning_lesson_attachments
                 SET deleted_at = NOW(), updated_at = NOW()
                 WHERE lesson_id = ? AND deleted_at IS NULL"
            )->execute([$lessonId]);
        }

        $this->db->prepare("UPDATE elearning_lessons SET deleted_at = NOW(), updated_at = NOW() WHERE id = ?")
            ->execute([$lessonId]);
        $this->recalculateStorageSummary();
        $this->syncEnrollmentStatusesByCourse((int) $lesson['course_id']);
        $this->logAction($actorId, 'teacher', 'lesson.deleted', 'lesson', $lessonId);
    }

    public function reorderLesson(int $lessonId, string $direction, int $actorId): void
    {
        $this->assertSchemaReady();
        $lesson = $this->fetchOne(
            "SELECT l.*, c.teacher_id
             FROM elearning_lessons l
             INNER JOIN elearning_courses c ON c.id = l.course_id
             WHERE l.id = ? AND l.deleted_at IS NULL",
            [$lessonId]
        );
        if (!$lesson || (int) $lesson['teacher_id'] !== $actorId) {
            throw new RuntimeException('Aula nÃƒÆ’Ã‚Â£o encontrada.');
        }

        $comparison = $direction === 'up' ? '<' : '>';
        $ordering = $direction === 'up' ? 'DESC' : 'ASC';

        $swap = $this->fetchOne(
            "SELECT * FROM elearning_lessons
             WHERE course_id = ? AND deleted_at IS NULL AND sequence_order {$comparison} ?
             ORDER BY sequence_order {$ordering}
             LIMIT 1",
            [(int) $lesson['course_id'], (int) $lesson['sequence_order']]
        );

        if (!$swap) {
            return;
        }

        $this->db->beginTransaction();
        try {
            $this->db->prepare("UPDATE elearning_lessons SET sequence_order = ? WHERE id = ?")
                ->execute([(int) $swap['sequence_order'], $lessonId]);
            $this->db->prepare("UPDATE elearning_lessons SET sequence_order = ? WHERE id = ?")
                ->execute([(int) $lesson['sequence_order'], (int) $swap['id']]);
            $this->db->commit();
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function removeLessonVideo(int $lessonId, int $actorId): void
    {
        $this->assertSchemaReady();
        $lesson = $this->getOwnedLesson($lessonId, $actorId);
        $video = $this->fetchOne(
            "SELECT * FROM elearning_lesson_videos
             WHERE lesson_id = ? AND deleted_at IS NULL",
            [$lessonId]
        );
        if (!$video) {
            return;
        }

        $this->deleteVideoRecord($video, true);
        $this->recalculateStorageSummary();
        $this->syncEnrollmentStatusesByCourse((int) $lesson['course_id']);
        $this->logAction($actorId, 'teacher', 'lesson.video.removed', 'lesson', $lessonId);
    }

    public function removeAttachment(int $attachmentId, int $actorId): void
    {
        $this->assertSchemaReady();
        $attachment = $this->fetchOne(
            "SELECT a.*, l.course_id, c.teacher_id
             FROM elearning_lesson_attachments a
             INNER JOIN elearning_lessons l ON l.id = a.lesson_id
             INNER JOIN elearning_courses c ON c.id = l.course_id
             WHERE a.id = ? AND a.deleted_at IS NULL",
            [$attachmentId]
        );
        if (!$attachment || (int) $attachment['teacher_id'] !== $actorId) {
            throw new RuntimeException('Anexo nÃƒÆ’Ã‚Â£o encontrado.');
        }

        $this->deleteStoredFile((string) $attachment['file_path']);
        $this->db->prepare("UPDATE elearning_lesson_attachments SET deleted_at = NOW(), updated_at = NOW() WHERE id = ?")
            ->execute([$attachmentId]);
        $this->logAction($actorId, 'teacher', 'lesson.attachment.removed', 'attachment', $attachmentId);
    }

    public function saveExam(array $input, int $actorId): int
    {
        $this->assertSchemaReady();
        $courseId = (int) ($input['course_id'] ?? 0);
        $title = trim((string) ($input['title'] ?? ''));
        if ($courseId <= 0 || $title === '') {
            throw new RuntimeException('Informe o curso e o tÃƒÆ’Ã‚Â­tulo da prova.');
        }
        $this->getOwnedCourse($courseId, $actorId);

        $questions = $input['questions'] ?? [];
        if (!is_array($questions) || $questions === []) {
            throw new RuntimeException('Adicione pelo menos uma questÃƒÆ’Ã‚Â£o objetiva.');
        }

        $examId = (int) ($input['exam_id'] ?? 0);
        $this->db->beginTransaction();

        try {
            if ($examId > 0) {
                $this->db->prepare(
                    "UPDATE elearning_exams SET
                        title = :title,
                        instructions = :instructions,
                        passing_score = :passing_score,
                        attempts_allowed = :attempts_allowed,
                        is_mandatory = :is_mandatory,
                        status = :status,
                        updated_at = NOW()
                     WHERE id = :id AND course_id = :course_id"
                )->execute([
                    ':title' => $title,
                    ':instructions' => trim((string) ($input['instructions'] ?? '')),
                    ':passing_score' => max(self::PASSING_SCORE, (float) ($input['passing_score'] ?? self::PASSING_SCORE)),
                    ':attempts_allowed' => max(1, (int) ($input['attempts_allowed'] ?? 1)),
                    ':is_mandatory' => !empty($input['is_mandatory']) ? 1 : 0,
                    ':status' => $this->normalizeExamStatus((string) ($input['status'] ?? 'published')),
                    ':id' => $examId,
                    ':course_id' => $courseId,
                ]);
                $questionIds = $this->fetchAll("SELECT id FROM elearning_exam_questions WHERE exam_id = ?", [$examId]);
                $this->db->prepare(
                    "UPDATE elearning_exam_questions SET deleted_at = NOW(), updated_at = NOW() WHERE exam_id = ?"
                )->execute([$examId]);
                foreach ($questionIds as $questionId) {
                    $this->db->prepare(
                        "UPDATE elearning_exam_options SET deleted_at = NOW(), updated_at = NOW() WHERE question_id = ?"
                    )->execute([(int) $questionId['id']]);
                }
            } else {
                $this->db->prepare(
                    "INSERT INTO elearning_exams
                        (course_id, title, instructions, passing_score, attempts_allowed, is_mandatory, status, created_at, updated_at)
                     VALUES
                        (:course_id, :title, :instructions, :passing_score, :attempts_allowed, :is_mandatory, :status, NOW(), NOW())"
                )->execute([
                    ':course_id' => $courseId,
                    ':title' => $title,
                    ':instructions' => trim((string) ($input['instructions'] ?? '')),
                    ':passing_score' => max(self::PASSING_SCORE, (float) ($input['passing_score'] ?? self::PASSING_SCORE)),
                    ':attempts_allowed' => max(1, (int) ($input['attempts_allowed'] ?? 1)),
                    ':is_mandatory' => !empty($input['is_mandatory']) ? 1 : 0,
                    ':status' => $this->normalizeExamStatus((string) ($input['status'] ?? 'published')),
                ]);
                $examId = (int) $this->db->lastInsertId();
            }

            foreach (array_values($questions) as $index => $question) {
                $statement = trim((string) ($question['statement'] ?? ''));
                $options = $question['options'] ?? [];
                $correctIndex = (int) ($question['correct_option'] ?? -1);
                if ($statement === '' || !is_array($options) || count($options) < 2) {
                    continue;
                }

                $this->db->prepare(
                    "INSERT INTO elearning_exam_questions
                        (exam_id, statement, question_type, score, sequence_order, created_at, updated_at)
                     VALUES
                        (:exam_id, :statement, 'single_choice', :score, :sequence_order, NOW(), NOW())"
                )->execute([
                    ':exam_id' => $examId,
                    ':statement' => $statement,
                    ':score' => max(1, (int) ($question['score'] ?? 1)),
                    ':sequence_order' => $index + 1,
                ]);

                $questionId = (int) $this->db->lastInsertId();
                foreach (array_values($options) as $optionIndex => $optionText) {
                    $optionText = trim((string) $optionText);
                    if ($optionText === '') {
                        continue;
                    }

                    $this->db->prepare(
                        "INSERT INTO elearning_exam_options
                            (question_id, option_text, is_correct, sequence_order, created_at, updated_at)
                         VALUES
                            (:question_id, :option_text, :is_correct, :sequence_order, NOW(), NOW())"
                    )->execute([
                        ':question_id' => $questionId,
                        ':option_text' => $optionText,
                        ':is_correct' => $optionIndex === $correctIndex ? 1 : 0,
                        ':sequence_order' => $optionIndex + 1,
                    ]);
                }
            }

            $this->db->commit();
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }

        $this->syncEnrollmentStatusesByCourse($courseId);
        $this->logAction($actorId, 'teacher', 'exam.saved', 'exam', $examId, ['course_id' => $courseId]);

        return $examId;
    }

    public function deleteExam(int $examId, int $actorId): void
    {
        $this->assertSchemaReady();
        $exam = $this->fetchOne(
            "SELECT e.*, c.teacher_id
             FROM elearning_exams e
             INNER JOIN elearning_courses c ON c.id = e.course_id
             WHERE e.id = ? AND e.deleted_at IS NULL",
            [$examId]
        );
        if (!$exam || (int) $exam['teacher_id'] !== $actorId) {
            throw new RuntimeException('Prova nÃƒÆ’Ã‚Â£o encontrada.');
        }

        $this->db->prepare("UPDATE elearning_exams SET deleted_at = NOW(), updated_at = NOW() WHERE id = ?")
            ->execute([$examId]);
        $this->logAction($actorId, 'teacher', 'exam.deleted', 'exam', $examId);
    }

    public function enrollStudent(int $courseId, int $studentId, int $actorId): void
    {
        $this->assertSchemaReady();
        $this->getOwnedCourse($courseId, $actorId);

        $stmt = $this->db->prepare(
            "INSERT INTO elearning_enrollments
                (course_id, student_id, status, progress_percent, enrolled_at, created_at, updated_at)
             VALUES
                (:course_id, :student_id, 'in_progress', 0, NOW(), NOW(), NOW())
             ON DUPLICATE KEY UPDATE deleted_at = NULL, updated_at = NOW()"
        );
        $stmt->execute([
            ':course_id' => $courseId,
            ':student_id' => $studentId,
        ]);

        $this->logAction($actorId, 'teacher', 'enrollment.created', 'course', $courseId, ['student_id' => $studentId]);
    }

    public function selfEnroll(int $courseId, int $studentId): void
    {
        $this->assertSchemaReady();
        $course = $this->fetchOne(
            "SELECT * FROM elearning_courses WHERE id = ? AND status = 'published' AND deleted_at IS NULL",
            [$courseId]
        );
        if (!$course) {
            throw new RuntimeException('Curso nÃƒÆ’Ã‚Â£o disponÃƒÆ’Ã‚Â­vel para matrÃƒÆ’Ã‚Â­cula.');
        }

        $stmt = $this->db->prepare(
            "INSERT INTO elearning_enrollments
                (course_id, student_id, status, progress_percent, enrolled_at, created_at, updated_at)
             VALUES
                (:course_id, :student_id, 'in_progress', 0, NOW(), NOW(), NOW())
             ON DUPLICATE KEY UPDATE deleted_at = NULL, updated_at = NOW()"
        );
        $stmt->execute([
            ':course_id' => $courseId,
            ':student_id' => $studentId,
        ]);
    }

    public function markLessonProgress(int $lessonId, int $studentId, float $percent): array
    {
        $this->assertSchemaReady();
        $percent = min(100, max(0, $percent));
        $lesson = $this->fetchOne(
            "SELECT l.*, c.id AS course_id
             FROM elearning_lessons l
             INNER JOIN elearning_courses c ON c.id = l.course_id
             WHERE l.id = ? AND l.deleted_at IS NULL AND c.deleted_at IS NULL",
            [$lessonId]
        );
        if (!$lesson) {
            throw new RuntimeException('Aula nÃƒÆ’Ã‚Â£o encontrada.');
        }

        $enrollment = $this->fetchOne(
            "SELECT * FROM elearning_enrollments
             WHERE course_id = ? AND student_id = ? AND deleted_at IS NULL",
            [(int) $lesson['course_id'], $studentId]
        );
        if (!$enrollment) {
            throw new RuntimeException('MatrÃƒÆ’Ã‚Â­cula nÃƒÆ’Ã‚Â£o encontrada.');
        }

        $isCompleted = $percent >= 90 ? 1 : 0;
        $stmt = $this->db->prepare(
            "INSERT INTO elearning_student_progress
                (enrollment_id, course_id, lesson_id, student_id, video_progress_percent, watched_seconds, is_completed, completed_at, created_at, updated_at)
             VALUES
                (:enrollment_id, :course_id, :lesson_id, :student_id, :video_progress_percent, 0, :is_completed,
                 " . ($isCompleted ? 'NOW()' : 'NULL') . ", NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                video_progress_percent = GREATEST(video_progress_percent, VALUES(video_progress_percent)),
                is_completed = GREATEST(is_completed, VALUES(is_completed)),
                completed_at = IF(VALUES(is_completed) = 1 AND completed_at IS NULL, NOW(), completed_at),
                updated_at = NOW()"
        );
        $stmt->execute([
            ':enrollment_id' => (int) $enrollment['id'],
            ':course_id' => (int) $lesson['course_id'],
            ':lesson_id' => $lessonId,
            ':student_id' => $studentId,
            ':video_progress_percent' => $percent,
            ':is_completed' => $isCompleted,
        ]);

        $updatedEnrollment = $this->syncEnrollmentStatus((int) $lesson['course_id'], $studentId);

        return [
            'lesson_progress' => $percent,
            'course_progress' => $updatedEnrollment['progress_percent'],
            'course_status' => $updatedEnrollment['status'],
        ];
    }

    public function submitExam(int $examId, int $studentId, array $answers): array
    {
        $this->assertSchemaReady();
        $examData = $this->examData($studentId, $examId);
        if ((int) $examData['remaining_attempts'] <= 0) {
            throw new RuntimeException('VocÃƒÆ’Ã‚Âª atingiu o limite de tentativas desta prova.');
        }

        $enrollment = $examData['enrollment'];
        $questions = $this->fetchAll(
            "SELECT q.id, q.score, opt.id AS option_id, opt.is_correct
             FROM elearning_exam_questions q
             LEFT JOIN elearning_exam_options opt ON opt.question_id = q.id AND opt.deleted_at IS NULL
             WHERE q.exam_id = ? AND q.deleted_at IS NULL
             ORDER BY q.sequence_order ASC, opt.sequence_order ASC",
            [$examId]
        );

        $map = [];
        $totalQuestions = 0;
        foreach ($questions as $row) {
            $questionId = (int) $row['id'];
            if (!isset($map[$questionId])) {
                $map[$questionId] = [
                    'score' => (float) $row['score'],
                    'correct_options' => [],
                ];
                $totalQuestions++;
            }
            if (!empty($row['option_id']) && (int) $row['is_correct'] === 1) {
                $map[$questionId]['correct_options'][] = (int) $row['option_id'];
            }
        }

        $attemptNumber = (int) $examData['attempts_count'] + 1;
        $this->db->beginTransaction();

        try {
            $this->db->prepare(
                "INSERT INTO elearning_exam_attempts
                    (exam_id, course_id, enrollment_id, student_id, attempt_number, status, started_at, finished_at, created_at, updated_at)
                 VALUES
                    (:exam_id, :course_id, :enrollment_id, :student_id, :attempt_number, 'started', NOW(), NULL, NOW(), NOW())"
            )->execute([
                ':exam_id' => $examId,
                ':course_id' => (int) $examData['exam']['course_id'],
                ':enrollment_id' => (int) $enrollment['id'],
                ':student_id' => $studentId,
                ':attempt_number' => $attemptNumber,
            ]);
            $attemptId = (int) $this->db->lastInsertId();

            $correctAnswers = 0;
            $totalScore = 0;
            $obtainedScore = 0;

            foreach ($map as $questionId => $question) {
                $selectedOptionId = (int) ($answers[$questionId] ?? 0);
                $isCorrect = in_array($selectedOptionId, $question['correct_options'], true);
                if ($isCorrect) {
                    $correctAnswers++;
                    $obtainedScore += $question['score'];
                }
                $totalScore += $question['score'];

                $this->db->prepare(
                    "INSERT INTO elearning_exam_answers
                        (attempt_id, question_id, selected_option_id, is_correct, score_awarded, created_at, updated_at)
                     VALUES
                        (:attempt_id, :question_id, :selected_option_id, :is_correct, :score_awarded, NOW(), NOW())"
                )->execute([
                    ':attempt_id' => $attemptId,
                    ':question_id' => $questionId,
                    ':selected_option_id' => $selectedOptionId ?: null,
                    ':is_correct' => $isCorrect ? 1 : 0,
                    ':score_awarded' => $isCorrect ? $question['score'] : 0,
                ]);
            }

            $scorePercent = $totalScore > 0 ? round(($obtainedScore / $totalScore) * 100, 2) : 0;
            $status = $scorePercent >= (float) $examData['exam']['passing_score'] ? 'approved' : 'failed';

            $this->db->prepare(
                "UPDATE elearning_exam_attempts SET
                    score_percent = :score_percent,
                    correct_answers = :correct_answers,
                    total_questions = :total_questions,
                    status = :status,
                    finished_at = NOW(),
                    updated_at = NOW()
                 WHERE id = :id"
            )->execute([
                ':score_percent' => $scorePercent,
                ':correct_answers' => $correctAnswers,
                ':total_questions' => $totalQuestions,
                ':status' => $status,
                ':id' => $attemptId,
            ]);

            $this->db->commit();
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }

        $updatedEnrollment = $this->syncEnrollmentStatus((int) $examData['exam']['course_id'], $studentId);
        $certificate = $this->ensureCertificateIfEligible((int) $examData['exam']['course_id'], $studentId);

        return [
            'attempt_id' => $attemptId,
            'score_percent' => $scorePercent,
            'approved' => $status === 'approved',
            'enrollment' => $updatedEnrollment,
            'certificate' => $certificate,
        ];
    }

    public function issueCertificateFromEnrollment(int $enrollmentId, int $actorId): array
    {
        $this->assertSchemaReady();
        $enrollment = $this->fetchOne(
            "SELECT e.*, c.teacher_id
             FROM elearning_enrollments e
             INNER JOIN elearning_courses c ON c.id = e.course_id
             WHERE e.id = ? AND e.deleted_at IS NULL",
            [$enrollmentId]
        );
        if (!$enrollment || (int) $enrollment['teacher_id'] !== $actorId) {
            throw new RuntimeException('MatrÃƒÆ’Ã‚Â­cula nÃƒÆ’Ã‚Â£o encontrada.');
        }

        $certificate = $this->ensureCertificateIfEligible((int) $enrollment['course_id'], (int) $enrollment['student_id']);
        if (!$certificate) {
            throw new RuntimeException('O aluno ainda nÃƒÆ’Ã‚Â£o atingiu os requisitos para o certificado.');
        }

        return $certificate;
    }

    public function saveCourseCertificateConfig(array $input, array $files, int $actorId): void
    {
        $this->assertSchemaReady();
        $courseId = (int) ($input['course_id'] ?? 0);
        if ($courseId <= 0) {
            throw new RuntimeException('Curso invÃƒÆ’Ã‚Â¡lido.');
        }

        $course = $this->getOwnedCourse($courseId, $actorId);
        $settings = $this->buildCertificateSettings(
            array_merge($input, ['existing_certificate_settings' => $course['certificate_settings'] ?? null]),
            $files,
            $courseId
        );

        $this->db->prepare(
            "UPDATE elearning_courses SET
                certificate_template_id = :template_id,
                certificate_settings = :certificate_settings,
                updated_at = NOW()
             WHERE id = :id"
        )->execute([
            ':template_id' => (int) ($input['certificate_template_id'] ?? 1) ?: 1,
            ':certificate_settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
            ':id' => $courseId,
        ]);
        $this->moveDeferredCertificateFiles($courseId, $settings);
    }

    public function courseCoverData(int $courseId): ?array
    {
        if (!$this->schemaReady()) {
            return null;
        }

        $course = $this->fetchOne("SELECT cover_path FROM elearning_courses WHERE id = ? AND deleted_at IS NULL", [$courseId]);
        if (!$course || empty($course['cover_path'])) {
            return null;
        }

        $path = $this->resolveStoredFilePath((string) $course['cover_path']);
        if (!is_file($path)) {
            return null;
        }

        return [
            'path' => $path,
            'mime' => mime_content_type($path) ?: 'image/jpeg',
            'name' => basename($path),
        ];
    }

    public function lessonVideoData(int $lessonId, int $userId, bool $teacherContext = false): ?array
    {
        if (!$this->schemaReady()) {
            return null;
        }

        $lesson = $this->fetchOne(
            "SELECT l.id, l.course_id, c.teacher_id
             FROM elearning_lessons l
             INNER JOIN elearning_courses c ON c.id = l.course_id
             WHERE l.id = ? AND l.deleted_at IS NULL AND c.deleted_at IS NULL",
            [$lessonId]
        );
        if (!$lesson) {
            return null;
        }

        if ($teacherContext) {
            if ((int) $lesson['teacher_id'] !== $userId) {
                return null;
            }
        } else {
            $enrollment = $this->fetchOne(
                "SELECT id FROM elearning_enrollments
                 WHERE student_id = ? AND course_id = ? AND deleted_at IS NULL",
                [$userId, (int) $lesson['course_id']]
            );
            if (!$enrollment) {
                return null;
            }
        }

        $video = $this->fetchOne(
            "SELECT * FROM elearning_lesson_videos
             WHERE lesson_id = ? AND deleted_at IS NULL AND file_path IS NOT NULL",
            [$lessonId]
        );
        if (!$video) {
            return null;
        }

        $video['estimated_minutes'] = $this->fetchValue(
            "SELECT estimated_minutes FROM elearning_lessons WHERE id = ?",
            [$lessonId]
        );

        return $this->buildStoredVideoData($video, true);
    }

    public function attachmentData(int $attachmentId, int $userId, bool $teacherContext = false): ?array
    {
        if (!$this->schemaReady()) {
            return null;
        }

        $attachment = $this->fetchOne(
            "SELECT a.*, l.course_id, c.teacher_id
             FROM elearning_lesson_attachments a
             INNER JOIN elearning_lessons l ON l.id = a.lesson_id
             INNER JOIN elearning_courses c ON c.id = l.course_id
             WHERE a.id = ? AND a.deleted_at IS NULL",
            [$attachmentId]
        );

        if (!$attachment) {
            return null;
        }

        if ($teacherContext) {
            if ((int) $attachment['teacher_id'] !== $userId) {
                return null;
            }
        } else {
            $enrollment = $this->fetchOne(
                "SELECT id FROM elearning_enrollments
                 WHERE student_id = ? AND course_id = ? AND deleted_at IS NULL",
                [$userId, (int) $attachment['course_id']]
            );
            if (!$enrollment) {
                return null;
            }
        }

        $path = $this->resolveStoredFilePath((string) $attachment['file_path']);
        if (!is_file($path)) {
            return null;
        }

        return [
            'path' => $path,
            'mime' => $attachment['mime_type'] ?: 'application/octet-stream',
            'name' => $attachment['file_name'] ?: basename($path),
        ];
    }

    public function certificateDownloadData(string $validationCode, int $studentId): array
    {
        $this->assertSchemaReady();
        $certificate = $this->fetchOne(
            "SELECT cert.*, c.title AS course_title, c.workload_hours, c.teacher_id, c.certificate_settings,
                    tpl.name AS template_name, tpl.layout_key, tpl.default_settings,
                    student.name AS student_name,
                    teacher.name AS teacher_name
             FROM elearning_certificates cert
             INNER JOIN elearning_courses c ON c.id = cert.course_id
             LEFT JOIN elearning_certificate_templates tpl ON tpl.id = cert.template_id
             INNER JOIN users student ON student.id = cert.student_id
             LEFT JOIN users teacher ON teacher.id = c.teacher_id
             WHERE cert.validation_code = ? AND cert.student_id = ? AND cert.deleted_at IS NULL",
            [$validationCode, $studentId]
        );

        if (!$certificate) {
            throw new RuntimeException('Certificado nÃƒÆ’Ã‚Â£o encontrado.');
        }

        $template = $this->resolveTemplateSettings($certificate);

        return [
            'certificate' => $certificate,
            'template' => $template,
        ];
    }

    public function getCertificateTemplates(): array
    {
        $defaults = $this->defaultCertificateTemplates();
        $defaultsById = [];
        foreach ($defaults as $defaultTemplate) {
            $defaultsById[(int) ($defaultTemplate['id'] ?? 0)] = $defaultTemplate;
        }

        if (!$this->schemaReady()) {
            return $defaults;
        }

        $rows = $this->fetchAll(
            "SELECT * FROM elearning_certificate_templates
             WHERE deleted_at IS NULL AND is_active = 1
             ORDER BY id ASC"
        );

        if ($rows === []) {
            return $defaults;
        }

        return array_map(function (array $template) use ($defaultsById): array {
            $defaultTemplate = $defaultsById[(int) ($template['id'] ?? 0)] ?? [];
            foreach (['name', 'description', 'preview_palette', 'layout_key', 'code'] as $field) {
                if (isset($defaultTemplate[$field])) {
                    $template[$field] = $defaultTemplate[$field];
                }
            }
            $template['default_settings'] = $this->decodeJsonField($template['default_settings'] ?? null, []);
            return $template;
        }, $rows);
    }

    public function getStorageSummary(): array
    {
        if (!$this->schemaReady()) {
            return $this->normalizeStorageSummary([
                'contracted_bytes' => $this->configuredVideoLimitSeconds(),
                'used_bytes' => 0,
                'warning_threshold_pct' => 80,
                'block_threshold_pct' => 100,
                'is_upload_blocked' => 0,
            ]);
        }

        $this->ensureStorageControlRow();
        $this->recalculateStorageSummary();
        $summary = $this->fetchOne("SELECT * FROM elearning_storage_control WHERE id = 1");
        if (!$summary) {
            return $this->normalizeStorageSummary([
                'contracted_bytes' => $this->configuredVideoLimitSeconds(),
                'used_bytes' => 0,
                'warning_threshold_pct' => 80,
                'block_threshold_pct' => 100,
                'is_upload_blocked' => 0,
            ]);
        }

        return $this->normalizeStorageSummary($summary);
    }

    public function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $value = $bytes;
        foreach ($units as $unit) {
            $value /= 1024;
            if ($value < 1024 || $unit === 'TB') {
                return number_format($value, $value >= 100 ? 0 : 2, ',', '.') . ' ' . $unit;
            }
        }

        return number_format($bytes, 0, ',', '.') . ' B';
    }

    private function getOwnedCourse(int $courseId, int $teacherId): array
    {
        $course = $this->fetchOne(
            "SELECT * FROM elearning_courses
             WHERE id = ? AND teacher_id = ? AND deleted_at IS NULL",
            [$courseId, $teacherId]
        );
        if (!$course) {
            throw new RuntimeException('Curso nÃƒÆ’Ã‚Â£o encontrado.');
        }

        return $course;
    }

    private function getOwnedLesson(int $lessonId, int $teacherId): array
    {
        $lesson = $this->fetchOne(
            "SELECT l.*, c.teacher_id
             FROM elearning_lessons l
             INNER JOIN elearning_courses c ON c.id = l.course_id
             WHERE l.id = ? AND l.deleted_at IS NULL",
            [$lessonId]
        );
        if (!$lesson || (int) $lesson['teacher_id'] !== $teacherId) {
            throw new RuntimeException('Aula nÃƒÆ’Ã‚Â£o encontrada.');
        }

        return $lesson;
    }

    private function storeLessonVideo(int $courseId, int $lessonId, array $file, int $actorId): void
    {
        $lesson = $this->fetchOne(
            "SELECT estimated_minutes, title FROM elearning_lessons WHERE id = ?",
            [$lessonId]
        ) ?: ['estimated_minutes' => 0, 'title' => 'Aula'];
        $summary = $this->getStorageSummary();
        $existing = $this->fetchOne(
            "SELECT * FROM elearning_lesson_videos WHERE lesson_id = ?",
            [$lessonId]
        );
        $existingDurationSeconds = $existing
            ? $this->extractVideoDurationSeconds($existing, (int) ($lesson['estimated_minutes'] ?? 0))
            : 0;
        $estimatedUploadSeconds = $this->estimateIncomingVideoSeconds((int) ($lesson['estimated_minutes'] ?? 0));

        $this->validateUpload($file, ['mp4'], self::VIDEO_LIMIT_BYTES, true);
        if ((int) $summary['available_seconds'] + $existingDurationSeconds < $estimatedUploadSeconds) {
            throw new RuntimeException('Limite de minutos de vÃƒÆ’Ã‚Â­deo atingido. Contrate mais capacidade para continuar enviando novos conteÃƒÆ’Ã‚Âºdos.');
        }

        $uploadedVideo = $this->bunnyStream->uploadVideo(
            $this->buildBunnyVideoTitle($courseId, $lessonId, (string) ($lesson['title'] ?? 'Aula')),
            (string) ($file['tmp_name'] ?? '')
        );
        $durationSeconds = max(
            $estimatedUploadSeconds,
            (int) ($uploadedVideo['duration_seconds'] ?? 0)
        );
        $projectedUsedSeconds = max(
            0,
            (int) $summary['used_seconds'] - $existingDurationSeconds + $durationSeconds
        );
        if ($projectedUsedSeconds > (int) $summary['contracted_seconds']) {
            $this->bunnyStream->deleteVideo((string) ($uploadedVideo['video_id'] ?? ''));
            throw new RuntimeException('Limite de minutos de vÃƒÆ’Ã‚Â­deo atingido. Contrate mais capacidade para continuar enviando novos conteÃƒÆ’Ã‚Âºdos.');
        }

        $required = (int) $file['size'];
        $storedReference = $this->buildBunnyVideoReference(
            (string) ($uploadedVideo['video_id'] ?? ''),
            $durationSeconds
        );

        if ($existing) {
            $this->db->prepare(
                "UPDATE elearning_lesson_videos SET
                    file_path = :file_path,
                    file_name = :file_name,
                    mime_type = :mime_type,
                    extension = 'mp4',
                    size_bytes = :size_bytes,
                    uploaded_by = :uploaded_by,
                    deleted_at = NULL,
                    updated_at = NOW()
                 WHERE lesson_id = :lesson_id"
            )->execute([
                ':file_path' => $storedReference,
                ':file_name' => basename((string) $file['name']),
                ':mime_type' => $this->uploadedMime($file),
                ':size_bytes' => $required,
                ':uploaded_by' => $actorId,
                ':lesson_id' => $lessonId,
            ]);
        } else {
            $this->db->prepare(
                "INSERT INTO elearning_lesson_videos
                    (lesson_id, file_path, file_name, mime_type, extension, size_bytes, uploaded_by, created_at, updated_at)
                 VALUES
                    (:lesson_id, :file_path, :file_name, :mime_type, 'mp4', :size_bytes, :uploaded_by, NOW(), NOW())"
            )->execute([
                ':lesson_id' => $lessonId,
                ':file_path' => $storedReference,
                ':file_name' => basename((string) $file['name']),
                ':mime_type' => $this->uploadedMime($file),
                ':size_bytes' => $required,
                ':uploaded_by' => $actorId,
            ]);
        }

        if ($existing) {
            $this->deleteVideoSourceOnly($existing);
        }

        $this->syncLessonEstimatedMinutesFromSeconds($lessonId, $durationSeconds);
        $this->recalculateStorageSummary();
        return;

        $this->validateUpload($file, ['mp4'], self::VIDEO_LIMIT_BYTES, true);
        $summary = $this->getStorageSummary();
        $required = (int) $file['size'];
        if ((int) $summary['available_bytes'] < $required) {
            throw new RuntimeException('Limite de armazenamento de vÃƒÆ’Ã‚Â­deos atingido. Contrate mais espaÃƒÆ’Ã‚Â§o para continuar enviando novos conteÃƒÆ’Ã‚Âºdos.');
        }

        $relativePath = $this->storeUploadedFile(
            $file,
            "storage/elearning/courses/{$courseId}/lessons/{$lessonId}/video",
            'lesson_video'
        );

        $existing = $this->fetchOne("SELECT * FROM elearning_lesson_videos WHERE lesson_id = ?", [$lessonId]);
        if ($existing && !empty($existing['file_path'])) {
            $this->deleteStoredFile((string) $existing['file_path']);
        }

        if ($existing) {
            $this->db->prepare(
                "UPDATE elearning_lesson_videos SET
                    file_path = :file_path,
                    file_name = :file_name,
                    mime_type = :mime_type,
                    extension = 'mp4',
                    size_bytes = :size_bytes,
                    uploaded_by = :uploaded_by,
                    deleted_at = NULL,
                    updated_at = NOW()
                 WHERE lesson_id = :lesson_id"
            )->execute([
                ':file_path' => $relativePath,
                ':file_name' => basename((string) $file['name']),
                ':mime_type' => $this->uploadedMime($file),
                ':size_bytes' => $required,
                ':uploaded_by' => $actorId,
                ':lesson_id' => $lessonId,
            ]);
        } else {
            $this->db->prepare(
                "INSERT INTO elearning_lesson_videos
                    (lesson_id, file_path, file_name, mime_type, extension, size_bytes, uploaded_by, created_at, updated_at)
                 VALUES
                    (:lesson_id, :file_path, :file_name, :mime_type, 'mp4', :size_bytes, :uploaded_by, NOW(), NOW())"
            )->execute([
                ':lesson_id' => $lessonId,
                ':file_path' => $relativePath,
                ':file_name' => basename((string) $file['name']),
                ':mime_type' => $this->uploadedMime($file),
                ':size_bytes' => $required,
                ':uploaded_by' => $actorId,
            ]);
        }

        $this->recalculateStorageSummary();
    }

    private function storeLessonAttachments(int $courseId, int $lessonId, array $files, int $actorId): void
    {
        $normalizedFiles = $this->normalizeMultipleFiles($files);
        foreach ($normalizedFiles as $file) {
            if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $this->validateUpload($file, ['pdf', 'png', 'jpg', 'jpeg', 'webp', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'], self::ATTACHMENT_LIMIT_BYTES, false);
            $relativePath = $this->storeUploadedFile(
                $file,
                "storage/elearning/courses/{$courseId}/lessons/{$lessonId}/attachments",
                'lesson_attachment'
            );
            $extension = strtolower((string) pathinfo((string) $file['name'], PATHINFO_EXTENSION));

            $this->db->prepare(
                "INSERT INTO elearning_lesson_attachments
                    (lesson_id, title, file_path, file_name, mime_type, extension, size_bytes, uploaded_by, created_at, updated_at)
                 VALUES
                    (:lesson_id, :title, :file_path, :file_name, :mime_type, :extension, :size_bytes, :uploaded_by, NOW(), NOW())"
            )->execute([
                ':lesson_id' => $lessonId,
                ':title' => pathinfo((string) $file['name'], PATHINFO_FILENAME),
                ':file_path' => $relativePath,
                ':file_name' => basename((string) $file['name']),
                ':mime_type' => $this->uploadedMime($file),
                ':extension' => $extension,
                ':size_bytes' => (int) $file['size'],
                ':uploaded_by' => $actorId,
            ]);
        }
    }

    private function saveCourseCover(?array $file): ?string
    {
        if (!$file || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $this->validateUpload($file, ['jpg', 'jpeg', 'png', 'webp'], 5242880, false);

        return $this->storeUploadedFile(
            $file,
            'storage/elearning/covers',
            'course_cover'
        );
    }

    private function buildCertificateSettings(array $input, array $files, int $courseId): array
    {
        $existing = $this->decodeJsonField($input['existing_certificate_settings'] ?? null, []);
        $settings = array_merge([
            'custom_text' => 'Certificamos que o aluno concluiu o curso com exito e atingiu os criterios minimos de aproveitamento.',
            'accent_color' => '#1d4ed8',
            'finish' => 'glass',
            'logo_path' => null,
            'signature_path' => null,
            'background_path' => null,
        ], $existing);

        $settings['custom_text'] = trim((string) ($input['custom_text'] ?? $settings['custom_text']));
        $settings['accent_color'] = trim((string) ($input['accent_color'] ?? $settings['accent_color'])) ?: '#1d4ed8';
        $settings['finish'] = trim((string) ($input['finish'] ?? $settings['finish'])) ?: 'glass';

        foreach ([
            'logo' => 'logo_path',
            'signature' => 'signature_path',
            'background' => 'background_path',
        ] as $fileKey => $settingKey) {
            $file = $files[$fileKey] ?? null;
            if ($file && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $this->validateUpload($file, ['jpg', 'jpeg', 'png', 'webp'], 5242880, false);
                $relativePath = $this->storeUploadedFile(
                    $file,
                    $courseId > 0
                        ? "storage/elearning/courses/{$courseId}/certificate"
                        : 'storage/elearning/certificates/deferred',
                    'certificate_asset'
                );
                $this->deleteStoredFile((string) ($settings[$settingKey] ?? ''));
                $settings[$settingKey] = $relativePath;
            }
        }

        return $settings;
    }

    private function moveDeferredCertificateFiles(int $courseId, array &$settings): void
    {
        foreach (['logo_path', 'signature_path', 'background_path'] as $key) {
            $relativePath = (string) ($settings[$key] ?? '');
            if ($relativePath === '' || !str_contains($relativePath, 'storage/elearning/certificates/deferred')) {
                continue;
            }

            $absolutePath = $this->resolveStoredFilePath($relativePath);
            if (!is_file($absolutePath)) {
                continue;
            }

            $targetDirectory = $this->basePath . DIRECTORY_SEPARATOR . "storage/elearning/courses/{$courseId}/certificate";
            if (!is_dir($targetDirectory)) {
                mkdir($targetDirectory, 0775, true);
            }

            $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . basename($absolutePath);
            rename($absolutePath, $targetPath);
            $settings[$key] = 'storage/elearning/courses/' . $courseId . '/certificate/' . basename($absolutePath);
        }
    }

    private function validateUpload(array $file, array $allowedExtensions, int $maxBytes, bool $forceMp4Mime): void
    {
        if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Falha ao processar o arquivo enviado.');
        }

        $extension = strtolower((string) pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            throw new RuntimeException('Formato de arquivo nao permitido.');
        }

        if ((int) ($file['size'] ?? 0) > $maxBytes) {
            throw new RuntimeException('O arquivo excede o limite permitido.');
        }

        if ($forceMp4Mime && !$this->isAcceptedMp4Upload($file)) {
            throw new RuntimeException('O video deve ser obrigatoriamente MP4.');
        }
    }

    private function uploadedMime(array $file): string
    {
        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName !== '' && is_file($tmpName)) {
            $mime = mime_content_type($tmpName);
            if (is_string($mime) && $mime !== '') {
                return $mime;
            }
        }

        return (string) ($file['type'] ?? 'application/octet-stream');
    }

    private function isAcceptedMp4Upload(array $file): bool
    {
        $extension = strtolower((string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if ($extension !== 'mp4') {
            return false;
        }

        $mime = strtolower($this->uploadedMime($file));
        if (
            str_contains($mime, 'mp4')
            || str_contains($mime, 'm4v')
            || in_array($mime, ['video/mp4', 'application/mp4', 'video/x-m4v', 'audio/mp4'], true)
        ) {
            return true;
        }

        return $this->fileLooksLikeMp4Container((string) ($file['tmp_name'] ?? ''));
    }

    private function fileLooksLikeMp4Container(string $tmpName): bool
    {
        if ($tmpName === '' || !is_file($tmpName) || !is_readable($tmpName)) {
            return false;
        }

        $handle = fopen($tmpName, 'rb');
        if ($handle === false) {
            return false;
        }

        $header = fread($handle, 16);
        fclose($handle);

        return is_string($header) && strlen($header) >= 12 && substr($header, 4, 4) === 'ftyp';
    }

    private function storeUploadedFile(array $file, string $relativeDirectory, string $prefix): string
    {
        $extension = strtolower((string) pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $directory = $this->basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, trim($relativeDirectory, '/'));
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $filename = $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $target = $directory . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file((string) $file['tmp_name'], $target)) {
            throw new RuntimeException('Nao foi possivel salvar o arquivo enviado.');
        }

        return trim($relativeDirectory, '/') . '/' . $filename;
    }

    private function deleteStoredFile(string $relativePath): void
    {
        if ($relativePath === '') {
            return;
        }

        $absolutePath = $this->resolveStoredFilePath($relativePath);
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    private function resolveStoredFilePath(string $relativePath): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($relativePath, '/'));
    }

    private function normalizeMultipleFiles(array $files): array
    {
        if (!isset($files['name']) || !is_array($files['name'])) {
            return [$files];
        }

        $normalized = [];
        foreach ($files['name'] as $index => $name) {
            $normalized[] = [
                'name' => $name,
                'type' => $files['type'][$index] ?? null,
                'tmp_name' => $files['tmp_name'][$index] ?? null,
                'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$index] ?? 0,
            ];
        }

        return $normalized;
    }

    private function syncEnrollmentStatusesByCourse(int $courseId): void
    {
        $students = $this->fetchAll(
            "SELECT student_id FROM elearning_enrollments
             WHERE course_id = ? AND deleted_at IS NULL",
            [$courseId]
        );

        foreach ($students as $student) {
            $this->syncEnrollmentStatus($courseId, (int) $student['student_id']);
        }
    }

    private function syncEnrollmentStatus(int $courseId, int $studentId): array
    {
        $enrollment = $this->fetchOne(
            "SELECT * FROM elearning_enrollments
             WHERE course_id = ? AND student_id = ? AND deleted_at IS NULL",
            [$courseId, $studentId]
        );
        if (!$enrollment) {
            throw new RuntimeException('MatrÃƒÆ’Ã‚Â­cula nÃƒÆ’Ã‚Â£o encontrada.');
        }

        $totalLessons = (int) $this->fetchValue(
            "SELECT COUNT(*) FROM elearning_lessons WHERE course_id = ? AND deleted_at IS NULL",
            [$courseId]
        );
        $completedLessons = (int) $this->fetchValue(
            "SELECT COUNT(*) FROM elearning_student_progress sp
             INNER JOIN elearning_lessons l ON l.id = sp.lesson_id
             WHERE l.course_id = ? AND sp.student_id = ? AND sp.is_completed = 1 AND l.deleted_at IS NULL",
            [$courseId, $studentId]
        );
        $progressPercent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;

        $requiredExams = (int) $this->fetchValue(
            "SELECT COUNT(*) FROM elearning_exams
             WHERE course_id = ? AND is_mandatory = 1 AND status = 'published' AND deleted_at IS NULL",
            [$courseId]
        );
        $passedRequiredExams = (int) $this->fetchValue(
            "SELECT COUNT(DISTINCT a.exam_id)
             FROM elearning_exam_attempts a
             INNER JOIN elearning_exams e ON e.id = a.exam_id
             WHERE a.course_id = ? AND a.student_id = ? AND a.status = 'approved'
               AND e.is_mandatory = 1 AND e.deleted_at IS NULL",
            [$courseId, $studentId]
        );

        $status = 'in_progress';
        $completedAt = null;
        $approvedAt = null;
        $certificateEligibleAt = null;

        if ($progressPercent >= 100) {
            if ($requiredExams === 0) {
                $status = 'completed';
                $completedAt = date('Y-m-d H:i:s');
                $approvedAt = $completedAt;
                $certificateEligibleAt = $completedAt;
            } elseif ($passedRequiredExams >= $requiredExams) {
                $status = 'approved';
                $completedAt = date('Y-m-d H:i:s');
                $approvedAt = $completedAt;
                $certificateEligibleAt = $completedAt;
            } else {
                $status = 'awaiting_exam';
            }
        } else {
            $failedRequiredAttempts = (int) $this->fetchValue(
                "SELECT COUNT(*) FROM elearning_exam_attempts a
                 INNER JOIN elearning_exams e ON e.id = a.exam_id
                 WHERE a.course_id = ? AND a.student_id = ? AND a.status = 'failed'
                   AND e.is_mandatory = 1 AND e.deleted_at IS NULL",
                [$courseId, $studentId]
            );
            $status = $failedRequiredAttempts > 0 ? 'failed' : 'in_progress';
        }

        $this->db->prepare(
            "UPDATE elearning_enrollments SET
                progress_percent = :progress_percent,
                status = :status,
                completed_at = IF(:completed_at_check IS NOT NULL, :completed_at_value, completed_at),
                approved_at = IF(:approved_at_check IS NOT NULL, :approved_at_value, approved_at),
                certificate_eligible_at = IF(:eligible_at_check IS NOT NULL, :eligible_at_value, certificate_eligible_at),
                last_access_at = NOW(),
                updated_at = NOW()
             WHERE id = :id"
        )->execute([
            ':progress_percent' => $progressPercent,
            ':status' => $status,
            ':completed_at_check' => $completedAt,
            ':completed_at_value' => $completedAt,
            ':approved_at_check' => $approvedAt,
            ':approved_at_value' => $approvedAt,
            ':eligible_at_check' => $certificateEligibleAt,
            ':eligible_at_value' => $certificateEligibleAt,
            ':id' => (int) $enrollment['id'],
        ]);

        $updated = $this->fetchOne("SELECT * FROM elearning_enrollments WHERE id = ?", [(int) $enrollment['id']]);
        if ($status === 'approved' || $status === 'completed') {
            $this->ensureCertificateIfEligible($courseId, $studentId);
        }

        return $updated ?: $enrollment;
    }

    private function ensureCertificateIfEligible(int $courseId, int $studentId): ?array
    {
        if (!$this->isCertificateEligible($studentId, $courseId)) {
            return null;
        }

        $existing = $this->fetchOne(
            "SELECT * FROM elearning_certificates
             WHERE student_id = ? AND course_id = ? AND deleted_at IS NULL
             ORDER BY issued_at DESC LIMIT 1",
            [$studentId, $courseId]
        );
        if ($existing) {
            return $existing;
        }

        $course = $this->fetchOne("SELECT * FROM elearning_courses WHERE id = ?", [$courseId]);
        $bestAttempt = $this->fetchOne(
            "SELECT * FROM elearning_exam_attempts
             WHERE student_id = ? AND course_id = ? AND status = 'approved'
             ORDER BY score_percent DESC, finished_at DESC
             LIMIT 1",
            [$studentId, $courseId]
        );
        $enrollment = $this->fetchOne(
            "SELECT * FROM elearning_enrollments WHERE student_id = ? AND course_id = ? AND deleted_at IS NULL",
            [$studentId, $courseId]
        );

        $validationCode = strtoupper(substr(bin2hex(random_bytes(8)), 0, 16));
        $scorePercent = (float) ($bestAttempt['score_percent'] ?? self::PASSING_SCORE);

        $this->db->prepare(
            "INSERT INTO elearning_certificates
                (course_id, enrollment_id, student_id, template_id, validation_code, score_percent, issued_at, created_at, updated_at)
             VALUES
                (:course_id, :enrollment_id, :student_id, :template_id, :validation_code, :score_percent, NOW(), NOW(), NOW())"
        )->execute([
            ':course_id' => $courseId,
            ':enrollment_id' => (int) ($enrollment['id'] ?? 0),
            ':student_id' => $studentId,
            ':template_id' => (int) ($course['certificate_template_id'] ?? 1) ?: 1,
            ':validation_code' => $validationCode,
            ':score_percent' => $scorePercent,
        ]);

        return $this->fetchOne(
            "SELECT * FROM elearning_certificates WHERE validation_code = ?",
            [$validationCode]
        );
    }

    private function isCertificateEligible(int $studentId, int $courseId): bool
    {
        if (!$this->schemaReady()) {
            return false;
        }

        $enrollment = $this->fetchOne(
            "SELECT * FROM elearning_enrollments
             WHERE student_id = ? AND course_id = ? AND deleted_at IS NULL",
            [$studentId, $courseId]
        );
        if (!$enrollment || !in_array((string) $enrollment['status'], ['approved', 'completed'], true)) {
            return false;
        }

        $requiredExams = (int) $this->fetchValue(
            "SELECT COUNT(*) FROM elearning_exams
             WHERE course_id = ? AND is_mandatory = 1 AND status = 'published' AND deleted_at IS NULL",
            [$courseId]
        );
        if ($requiredExams === 0) {
            return true;
        }

        $passedExams = (int) $this->fetchValue(
            "SELECT COUNT(DISTINCT a.exam_id)
             FROM elearning_exam_attempts a
             INNER JOIN elearning_exams e ON e.id = a.exam_id
             WHERE a.course_id = ? AND a.student_id = ? AND a.status = 'approved'
               AND e.is_mandatory = 1 AND e.deleted_at IS NULL",
            [$courseId, $studentId]
        );

        return $passedExams >= $requiredExams;
    }

    private function resolveTemplateSettings(array $certificate): array
    {
        $templates = $this->getCertificateTemplates();
        $selectedTemplate = null;
        foreach ($templates as $template) {
            if ((int) ($template['id'] ?? 0) === (int) ($certificate['template_id'] ?? 0)) {
                $selectedTemplate = $template;
                break;
            }
        }
        if (!$selectedTemplate) {
            $selectedTemplate = $templates[0] ?? [
                'name' => 'Horizonte Academico',
                'layout_key' => 'horizonte',
                'default_settings' => [],
            ];
        }

        $courseSettings = $this->decodeJsonField($certificate['certificate_settings'] ?? null, []);
        $templateDefaults = is_array($selectedTemplate['default_settings'] ?? null)
            ? $selectedTemplate['default_settings']
            : $this->decodeJsonField($selectedTemplate['default_settings'] ?? null, []);
        $settings = array_merge($templateDefaults, $courseSettings);

        foreach ([
            'logo_path' => 'logo_data_url',
            'signature_path' => 'signature_data_url',
            'background_path' => 'background_data_url',
        ] as $pathKey => $dataKey) {
            $settings[$dataKey] = $this->inlineStoredAsset((string) ($settings[$pathKey] ?? ''));
        }

        return [
            'template' => $selectedTemplate,
            'settings' => $settings,
        ];
    }

    private function inlineStoredAsset(string $relativePath): ?string
    {
        if ($relativePath === '') {
            return null;
        }

        $absolutePath = $this->resolveStoredFilePath($relativePath);
        if (!is_file($absolutePath)) {
            return null;
        }

        $contents = @file_get_contents($absolutePath);
        if ($contents === false) {
            return null;
        }

        $mime = mime_content_type($absolutePath) ?: 'application/octet-stream';

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    private function recalculateStorageSummary(): void
    {
        if (!$this->schemaReady()) {
            return;
        }

        $usedSeconds = 0;
        $videos = $this->fetchAll(
            "SELECT v.file_path, l.estimated_minutes
             FROM elearning_lesson_videos v
             INNER JOIN elearning_lessons l ON l.id = v.lesson_id
             INNER JOIN elearning_courses c ON c.id = l.course_id
             WHERE v.deleted_at IS NULL
               AND l.deleted_at IS NULL
               AND c.deleted_at IS NULL"
        );

        foreach ($videos as $video) {
            $usedSeconds += $this->extractVideoDurationSeconds($video, (int) ($video['estimated_minutes'] ?? 0));
        }

        $this->ensureStorageControlRow();
        $this->db->prepare(
            "UPDATE elearning_storage_control SET
                used_bytes = :used_bytes,
                is_upload_blocked = IF(:used_bytes_limit >= contracted_bytes, 1, 0),
                last_recalculated_at = NOW(),
                updated_at = NOW()
             WHERE id = 1"
        )->execute([
            ':used_bytes' => $usedSeconds,
            ':used_bytes_limit' => $usedSeconds,
        ]);
    }

    private function ensureStorageControlRow(): void
    {
        $exists = (int) $this->fetchValue("SELECT COUNT(*) FROM elearning_storage_control WHERE id = 1");
        if ($exists > 0) {
            $currentLimit = (int) $this->fetchValue("SELECT contracted_bytes FROM elearning_storage_control WHERE id = 1");
            if (
                $currentLimit === 0
                || $currentLimit === self::LEGACY_STORAGE_LIMIT_BYTES
                || $currentLimit === self::LEGACY_STORAGE_LIMIT_BYTES_V2
            ) {
                $this->db->prepare(
                    "UPDATE elearning_storage_control SET
                        contracted_bytes = :contracted_bytes,
                        is_upload_blocked = IF(used_bytes >= :contracted_bytes_limit, 1, 0),
                        updated_at = NOW()
                     WHERE id = 1"
                )->execute([
                    ':contracted_bytes' => $this->configuredVideoLimitSeconds(),
                    ':contracted_bytes_limit' => $this->configuredVideoLimitSeconds(),
                ]);
            }
            return;
        }

        $this->db->prepare(
            "INSERT INTO elearning_storage_control
                (id, contracted_bytes, used_bytes, warning_threshold_pct, block_threshold_pct, is_upload_blocked, created_at, updated_at)
             VALUES
                (1, :contracted_bytes, 0, 80, 100, 0, NOW(), NOW())"
        )->execute([
            ':contracted_bytes' => $this->configuredVideoLimitSeconds(),
        ]);
    }

    private function normalizeStorageSummary(array $summary): array
    {
        $contracted = (int) ($summary['contracted_bytes'] ?? $this->configuredVideoLimitSeconds());
        $used = (int) ($summary['used_bytes'] ?? 0);
        $available = max(0, $contracted - $used);
        $percent = $contracted > 0 ? round(($used / $contracted) * 100, 2) : 0;

        return [
            'contracted_bytes' => $contracted,
            'used_bytes' => $used,
            'available_bytes' => $available,
            'contracted_seconds' => $contracted,
            'used_seconds' => $used,
            'available_seconds' => $available,
            'contracted_minutes' => round($contracted / 60, 2),
            'used_minutes' => round($used / 60, 2),
            'available_minutes' => round($available / 60, 2),
            'percent_used' => $percent,
            'warning_threshold_pct' => (float) ($summary['warning_threshold_pct'] ?? 80),
            'block_threshold_pct' => (float) ($summary['block_threshold_pct'] ?? 100),
            'is_upload_blocked' => !empty($summary['is_upload_blocked']) || $percent >= 100,
            'contracted_human' => $this->formatMinutesLabel($contracted),
            'used_human' => $this->formatMinutesLabel($used),
            'available_human' => $this->formatMinutesLabel($available),
            'contracted_detail' => $this->formatDurationBreakdown($contracted),
            'used_detail' => $this->formatDurationBreakdown($used),
            'available_detail' => $this->formatDurationBreakdown($available),
            'alert_level' => $percent >= 100 ? 'critical' : ($percent >= 80 ? 'warning' : 'healthy'),
        ];
    }

    private function configuredVideoLimitSeconds(): int
    {
        $configuredMinutes = (int) trim((string) ($_ENV['BUNNY_STREAM_STORAGE_LIMIT_MINUTES'] ?? getenv('BUNNY_STREAM_STORAGE_LIMIT_MINUTES') ?: ''));
        $configuredMinutes = $configuredMinutes > 0 ? $configuredMinutes : self::DEFAULT_STORAGE_LIMIT_MINUTES;

        return $configuredMinutes * 60;
    }

    private function formatMinutesLabel(int $seconds): string
    {
        $minutes = (int) ceil(max(0, $seconds) / 60);

        return number_format($minutes, 0, ',', '.') . ' min';
    }

    private function formatDurationBreakdown(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $minutes = (int) floor($seconds / 60);
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours <= 0) {
            return number_format($minutes, 0, ',', '.') . ' min';
        }

        return $hours . 'h ' . str_pad((string) $remainingMinutes, 2, '0', STR_PAD_LEFT) . 'min';
    }

    private function courseVideoUsageMap(?int $teacherId = null): array
    {
        $params = [];
        $sql = "SELECT c.id, c.title, v.id AS video_id, v.file_path, l.estimated_minutes
                FROM elearning_courses c
                LEFT JOIN elearning_lessons l ON l.course_id = c.id AND l.deleted_at IS NULL
                LEFT JOIN elearning_lesson_videos v ON v.lesson_id = l.id AND v.deleted_at IS NULL
                WHERE c.deleted_at IS NULL";

        if ($teacherId !== null) {
            $sql .= " AND c.teacher_id = ?";
            $params[] = $teacherId;
        }

        $sql .= " ORDER BY c.title ASC, l.sequence_order ASC, l.id ASC";
        $rows = $this->fetchAll($sql, $params);

        $courses = [];
        foreach ($rows as $row) {
            $courseId = (int) ($row['id'] ?? 0);
            if ($courseId <= 0) {
                continue;
            }

            if (!isset($courses[$courseId])) {
                $courses[$courseId] = [
                    'id' => $courseId,
                    'title' => (string) ($row['title'] ?? 'Curso'),
                    'videos_count' => 0,
                    'used_seconds' => 0,
                    'used_human' => '0 min',
                ];
            }

            if (!empty($row['video_id'])) {
                $courses[$courseId]['videos_count']++;
                $courses[$courseId]['used_seconds'] += $this->extractVideoDurationSeconds($row, (int) ($row['estimated_minutes'] ?? 0));
            }
        }

        foreach ($courses as &$course) {
            $course['used_human'] = $this->formatMinutesLabel((int) $course['used_seconds']);
        }
        unset($course);

        uasort($courses, static function (array $left, array $right): int {
            $byUsage = ((int) ($right['used_seconds'] ?? 0)) <=> ((int) ($left['used_seconds'] ?? 0));
            if ($byUsage !== 0) {
                return $byUsage;
            }

            return strcmp((string) ($left['title'] ?? ''), (string) ($right['title'] ?? ''));
        });

        return $courses;
    }

    private function courseVideoSummary(int $courseId): array
    {
        $rows = $this->fetchAll(
            "SELECT v.id AS video_id, v.file_path, l.estimated_minutes
             FROM elearning_lesson_videos v
             INNER JOIN elearning_lessons l ON l.id = v.lesson_id
             INNER JOIN elearning_courses c ON c.id = l.course_id
             WHERE c.id = ? AND v.deleted_at IS NULL AND l.deleted_at IS NULL AND c.deleted_at IS NULL",
            [$courseId]
        );

        $usedSeconds = 0;
        foreach ($rows as $row) {
            $usedSeconds += $this->extractVideoDurationSeconds($row, (int) ($row['estimated_minutes'] ?? 0));
        }

        return [
            'used_seconds' => $usedSeconds,
            'used_human' => $this->formatMinutesLabel($usedSeconds),
        ];
    }

    private function buildStoredVideoData(array $video, bool $refreshRemoteStatus = false): ?array
    {
        $filePath = trim((string) ($video['file_path'] ?? ''));
        if ($filePath === '') {
            return null;
        }

        $reference = $this->parseVideoReference($filePath);
        $durationSeconds = $this->extractVideoDurationSeconds($video, (int) ($video['estimated_minutes'] ?? 0));

        if ($reference['provider'] === 'bunny' && !empty($reference['video_id'])) {
            $remoteVideo = $refreshRemoteStatus
                ? $this->fetchBunnyVideoMeta((string) $reference['video_id'])
                : [];
            $status = (string) ($remoteVideo['status'] ?? '');
            $remoteDurationSeconds = (int) ($remoteVideo['duration_seconds'] ?? 0);
            $durationSeconds = max($durationSeconds, $remoteDurationSeconds);
            $availableResolutions = $remoteVideo['available_resolutions'] ?? [];
            $hasRemoteState = $refreshRemoteStatus && $remoteVideo !== [];
            $isReady = $hasRemoteState
                ? $this->isBunnyVideoReady($status, $remoteDurationSeconds, $availableResolutions)
                : true;

            if ($refreshRemoteStatus && $remoteDurationSeconds > 0 && $remoteDurationSeconds !== (int) ($reference['duration_seconds'] ?? 0)) {
                $this->syncBunnyVideoDuration(
                    (int) ($video['lesson_id'] ?? 0),
                    (int) ($video['id'] ?? 0),
                    (string) $reference['video_id'],
                    $remoteDurationSeconds
                );
            }

            return [
                'provider' => 'bunny',
                'video_id' => $reference['video_id'],
                'duration_seconds' => $durationSeconds,
                'duration_human' => $this->formatMinutesLabel($durationSeconds),
                'mime' => 'video/mp4',
                'name' => (string) ($video['file_name'] ?? 'video.mp4'),
                'embed_url' => $this->bunnyStream->embedUrl($reference['video_id']),
                'playback_url' => $this->bunnyStream->playbackUrl($reference['video_id']),
                'playlist_url' => $this->bunnyStream->playlistUrl($reference['video_id']),
                'mp4_url' => $this->bunnyStream->mp4Url($reference['video_id']),
                'status' => $status,
                'status_label' => $isReady ? 'Pronto' : ($hasRemoteState ? 'Processando' : 'Enviado'),
                'is_ready' => $isReady,
                'processing_message' => $isReady
                    ? null
                    : 'O video ainda esta sendo preparado no SGI STREAM. Atualize a pagina em alguns instantes.',
            ];
        }

        $path = $this->resolveStoredFilePath($filePath);
        if (!is_file($path)) {
            return null;
        }

        return [
            'provider' => 'local',
            'video_id' => null,
            'duration_seconds' => $durationSeconds,
            'duration_human' => $this->formatMinutesLabel($durationSeconds),
            'mime' => $video['mime_type'] ?: 'video/mp4',
            'name' => $video['file_name'] ?: basename($path),
            'path' => $path,
            'embed_url' => null,
            'playback_url' => null,
            'playlist_url' => null,
            'mp4_url' => null,
            'status' => 'ready',
            'status_label' => 'Pronto',
            'is_ready' => true,
            'processing_message' => null,
        ];
    }

    private function fetchBunnyVideoMeta(string $videoId): array
    {
        if ($videoId === '' || !$this->bunnyStream->isConfigured()) {
            return [];
        }

        try {
            return $this->bunnyStream->getVideo($videoId);
        } catch (\Throwable) {
            return [];
        }
    }

    private function isBunnyVideoReady(string $status, int $durationSeconds, array $availableResolutions): bool
    {
        $normalizedStatus = strtolower(trim($status));
        if (in_array($normalizedStatus, ['3', '4', 'ready', 'encoded', 'finished', 'published'], true)) {
            return true;
        }

        if (in_array($normalizedStatus, ['0', '1', '2', 'processing', 'queued', 'uploading', 'transcoding'], true)) {
            return false;
        }

        return $durationSeconds > 0 || !empty($availableResolutions);
    }

    private function syncBunnyVideoDuration(int $lessonId, int $videoId, string $videoGuid, int $durationSeconds): void
    {
        if ($lessonId <= 0 || $videoId <= 0 || $videoGuid === '' || $durationSeconds <= 0 || !$this->schemaReady()) {
            return;
        }

        $reference = $this->buildBunnyVideoReference($videoGuid, $durationSeconds);

        $this->db->prepare(
            "UPDATE elearning_lesson_videos
             SET file_path = :file_path,
                 updated_at = NOW()
             WHERE id = :id"
        )->execute([
            ':file_path' => $reference,
            ':id' => $videoId,
        ]);

        $this->syncLessonEstimatedMinutesFromSeconds($lessonId, $durationSeconds);
        $this->recalculateStorageSummary();
    }

    private function parseVideoReference(string $value): array
    {
        if (str_starts_with($value, 'bunny:')) {
            $parts = explode(':', $value, 3);
            return [
                'provider' => 'bunny',
                'video_id' => $parts[1] ?? '',
                'duration_seconds' => isset($parts[2]) ? max(0, (int) $parts[2]) : 0,
            ];
        }

        return [
            'provider' => 'local',
            'video_id' => '',
            'duration_seconds' => 0,
        ];
    }

    private function extractVideoDurationSeconds(array $video, int $fallbackEstimatedMinutes = 0): int
    {
        $reference = $this->parseVideoReference((string) ($video['file_path'] ?? ''));
        $durationSeconds = (int) ($reference['duration_seconds'] ?? 0);
        if ($durationSeconds > 0) {
            return $durationSeconds;
        }

        return max(0, $fallbackEstimatedMinutes) * 60;
    }

    private function estimateIncomingVideoSeconds(int $estimatedMinutes): int
    {
        return max(60, max(0, $estimatedMinutes) * 60);
    }

    private function buildBunnyVideoReference(string $videoId, int $durationSeconds): string
    {
        return 'bunny:' . $videoId . ':' . max(0, $durationSeconds);
    }

    private function buildBunnyVideoTitle(int $courseId, int $lessonId, string $lessonTitle): string
    {
        $title = trim($lessonTitle) !== '' ? trim($lessonTitle) : 'Aula';

        return "SGI Curso {$courseId} Aula {$lessonId} - {$title}";
    }

    private function syncLessonEstimatedMinutesFromSeconds(int $lessonId, int $durationSeconds): void
    {
        $minutes = (int) ceil(max(0, $durationSeconds) / 60);
        $this->db->prepare(
            "UPDATE elearning_lessons
             SET estimated_minutes = :estimated_minutes,
                 updated_at = NOW()
             WHERE id = :id"
        )->execute([
            ':estimated_minutes' => $minutes,
            ':id' => $lessonId,
        ]);
    }

    private function deleteVideoSourceOnly(array $video): void
    {
        $filePath = (string) ($video['file_path'] ?? '');
        if ($filePath === '') {
            return;
        }

        try {
            $reference = $this->parseVideoReference($filePath);
            if ($reference['provider'] === 'bunny') {
                $this->bunnyStream->deleteVideo((string) ($reference['video_id'] ?? ''));
                return;
            }

            $this->deleteStoredFile($filePath);
        } catch (\Throwable) {
            // Best effort cleanup to avoid blocking the professor flow.
        }
    }

    private function deleteVideoSourceStrict(array $video): void
    {
        $filePath = (string) ($video['file_path'] ?? '');
        if ($filePath === '') {
            return;
        }

        $reference = $this->parseVideoReference($filePath);
        if ($reference['provider'] === 'bunny') {
            $this->bunnyStream->deleteVideo((string) ($reference['video_id'] ?? ''));
            return;
        }

        $this->deleteStoredFile($filePath);
    }

    private function collectCourseAssetsForDeletion(array $course): array
    {
        $courseId = (int) ($course['id'] ?? 0);
        $settings = $this->decodeJsonField($course['certificate_settings'] ?? null, []);
        $certificateFiles = [];
        foreach (['logo_path', 'signature_path', 'background_path'] as $key) {
            $path = trim((string) ($settings[$key] ?? ''));
            if ($path !== '') {
                $certificateFiles[] = $path;
            }
        }

        return [
            'cover_path' => trim((string) ($course['cover_path'] ?? '')),
            'certificate_files' => array_values(array_unique($certificateFiles)),
            'attachments' => $this->fetchAll(
                "SELECT a.file_path
                 FROM elearning_lesson_attachments a
                 INNER JOIN elearning_lessons l ON l.id = a.lesson_id
                 WHERE l.course_id = ? AND a.file_path IS NOT NULL AND a.file_path <> ''",
                [$courseId]
            ),
            'videos' => $this->fetchAll(
                "SELECT v.file_path, v.id
                 FROM elearning_lesson_videos v
                 INNER JOIN elearning_lessons l ON l.id = v.lesson_id
                 WHERE l.course_id = ? AND v.file_path IS NOT NULL AND v.file_path <> ''",
                [$courseId]
            ),
        ];
    }

    private function cleanupCourseAssetsAfterDeletion(array $assets): array
    {
        $warnings = [];

        foreach ($assets['videos'] ?? [] as $video) {
            try {
                $this->deleteVideoSourceStrict($video);
            } catch (\Throwable $exception) {
                $warnings[] = 'video:' . (string) ($video['id'] ?? '0');
            }
        }

        $localFiles = [];
        $coverPath = trim((string) ($assets['cover_path'] ?? ''));
        if ($coverPath !== '') {
            $localFiles[] = $coverPath;
        }

        foreach (($assets['attachments'] ?? []) as $attachment) {
            $path = trim((string) ($attachment['file_path'] ?? ''));
            if ($path !== '') {
                $localFiles[] = $path;
            }
        }

        foreach (($assets['certificate_files'] ?? []) as $path) {
            $path = trim((string) $path);
            if ($path !== '') {
                $localFiles[] = $path;
            }
        }

        foreach (array_values(array_unique($localFiles)) as $path) {
            $this->deleteStoredFile($path);
        }

        return $warnings;
    }

    private function deleteVideoRecord(array $video, bool $markDeleted): void
    {
        $this->deleteVideoSourceOnly($video);

        $deletedSql = $markDeleted ? 'NOW()' : 'NULL';
        $this->db->prepare(
            "UPDATE elearning_lesson_videos SET
                file_path = NULL,
                file_name = NULL,
                mime_type = NULL,
                extension = NULL,
                size_bytes = 0,
                deleted_at = {$deletedSql},
                updated_at = NOW()
             WHERE id = :id"
        )->execute([
            ':id' => (int) ($video['id'] ?? 0),
        ]);
    }

    private function formatCourseCard(array $course): array
    {
        $course['status_label'] = match ((string) ($course['status'] ?? 'draft')) {
            'published' => 'Publicado',
            'archived' => 'Arquivado',
            default => 'Rascunho',
        };
        $course['avg_progress'] = round((float) ($course['avg_progress'] ?? 0), 2);
        if (array_key_exists('certificate_settings', $course)) {
            $course['certificate_settings'] = $this->decodeJsonField($course['certificate_settings'] ?? null, []);
        }
        $course['cover_url'] = '/elearning/gestor/cursos/thumbnail?id=' . (int) $course['id'];
        return $course;
    }

    private function formatCourseDetail(array $course): array
    {
        return $this->formatCourseCard($course);
    }

    private function formatStudentCourseCard(array $course): array
    {
        $course['cover_url'] = '/elearning/gestor/cursos/thumbnail?id=' . (int) $course['id'];
        return $course;
    }

    private function listTeacherOptions(): array
    {
        try {
            return $this->fetchAll("SELECT id, name, email FROM users ORDER BY name ASC");
        } catch (\Throwable) {
            return [];
        }
    }

    private function listStudentOptions(): array
    {
        return $this->listTeacherOptions();
    }

    private function listCourseCategories(): array
    {
        $defaults = ['Tecnologia', 'Compliance', 'Qualidade', 'Processos', 'OperaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes', 'Soft Skills'];

        if (!$this->schemaReady()) {
            return $defaults;
        }

        $categories = $this->fetchAll(
            "SELECT DISTINCT category FROM elearning_courses
             WHERE deleted_at IS NULL AND category IS NOT NULL AND category <> ''
             ORDER BY category ASC"
        );

        $values = array_map(fn(array $row) => (string) $row['category'], $categories);
        return array_values(array_unique(array_merge($defaults, $values)));
    }

    private function nextLessonOrder(int $courseId): int
    {
        return ((int) $this->fetchValue(
            "SELECT COALESCE(MAX(sequence_order), 0) + 1 FROM elearning_lessons
             WHERE course_id = ? AND deleted_at IS NULL",
            [$courseId]
        )) ?: 1;
    }

    private function normalizeCourseStatus(string $status): string
    {
        return in_array($status, ['draft', 'published', 'archived'], true) ? $status : 'draft';
    }

    private function normalizeExamStatus(string $status): string
    {
        return in_array($status, ['draft', 'published', 'archived'], true) ? $status : 'published';
    }

    private function decodeJsonField(mixed $value, array $fallback): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return $fallback;
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : $fallback;
    }

    private function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    private function fetchValue(string $sql, array $params = []): mixed
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    private function assertSchemaReady(): void
    {
        if (!$this->schemaReady()) {
            throw new RuntimeException('A base do mÃƒÆ’Ã‚Â³dulo E-Learning ainda nÃƒÆ’Ã‚Â£o foi instalada. Execute o SQL do mÃƒÆ’Ã‚Â³dulo antes de usar as operaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes de gravaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o.');
        }
    }

    private function logAction(int $userId, string $context, string $action, string $entityType, ?int $entityId = null, array $payload = []): void
    {
        try {
            if (!$this->schemaReady()) {
                return;
            }

            $exists = $this->db->query("SHOW TABLES LIKE 'elearning_activity_logs'")->fetchColumn();
            if (!$exists) {
                return;
            }

            $this->db->prepare(
                "INSERT INTO elearning_activity_logs
                    (user_id, role_context, action, entity_type, entity_id, payload, ip_address, created_at)
                 VALUES
                    (:user_id, :role_context, :action, :entity_type, :entity_id, :payload, :ip_address, NOW())"
            )->execute([
                ':user_id' => $userId,
                ':role_context' => $context,
                ':action' => $action,
                ':entity_type' => $entityType,
                ':entity_id' => $entityId,
                ':payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable) {
        }
    }

    private function defaultCertificateTemplates(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Horizonte Academico',
                'code' => 'horizonte-academico',
                'layout_key' => 'horizonte',
                'description' => 'Layout clean com faixa superior e assinatura em evidencia.',
                'preview_palette' => 'Azul petroleo + dourado',
                'default_settings' => [
                    'accent_color' => '#0f4c81',
                    'finish' => 'sleek',
                ],
            ],
            [
                'id' => 2,
                'name' => 'Aurora Corporativa',
                'code' => 'aurora-corporativa',
                'layout_key' => 'aurora',
                'description' => 'Modelo contemporaneo com moldura suave e microtexturas.',
                'preview_palette' => 'Azul claro + grafite',
                'default_settings' => [
                    'accent_color' => '#2563eb',
                    'finish' => 'glass',
                ],
            ],
            [
                'id' => 3,
                'name' => 'Conquista Premium',
                'code' => 'conquista-premium',
                'layout_key' => 'premium',
                'description' => 'Composicao imponente com selo de conclusao e bordas sofisticadas.',
                'preview_palette' => 'Ambar + marinho',
                'default_settings' => [
                    'accent_color' => '#b45309',
                    'finish' => 'premium',
                ],
            ],
            [
                'id' => 4,
                'name' => 'Atlas Minimal',
                'code' => 'atlas-minimal',
                'layout_key' => 'atlas',
                'description' => 'Minimalista, com hierarquia tipografica forte e respiro generoso.',
                'preview_palette' => 'Slate + branco',
                'default_settings' => [
                    'accent_color' => '#334155',
                    'finish' => 'minimal',
                ],
            ],
            [
                'id' => 5,
                'name' => 'Legado Institucional',
                'code' => 'legado-institucional',
                'layout_key' => 'legado',
                'description' => 'Visual classico para treinamentos internos, com foco em seriedade.',
                'preview_palette' => 'Verde escuro + areia',
                'default_settings' => [
                    'accent_color' => '#166534',
                    'finish' => 'institutional',
                ],
            ],
        ];
    }
}
