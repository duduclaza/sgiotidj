<?php
$course = $data['course'] ?? null;
$lessons = $data['lessons'] ?? [];
$exams = $data['exams'] ?? [];
$enrollments = $data['enrollments'] ?? [];
$users = $data['users'] ?? [];
$templates = $data['templates'] ?? [];
$storage = $data['storage'] ?? [];
$reports = $data['reports'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);
$activeTab = $activeTab ?? 'lessons';
$courseSettings = $course['certificate_settings'] ?? [];

$selectedTemplate = $templates[0] ?? null;
foreach ($templates as $template) {
    if ((int) ($template['id'] ?? 0) === (int) ($course['certificate_template_id'] ?? 1)) {
        $selectedTemplate = $template;
        break;
    }
}

$courseStatus = match ((string) ($course['status'] ?? 'draft')) {
    'published' => ['label' => 'Publicado', 'class' => 'bg-emerald-100 text-emerald-700'],
    'archived' => ['label' => 'Arquivado', 'class' => 'bg-slate-200 text-slate-700'],
    default => ['label' => 'Rascunho', 'class' => 'bg-amber-100 text-amber-700'],
};

$enrollmentStatus = static function (string $status): array {
    return match ($status) {
        'approved' => ['label' => 'Aprovado', 'class' => 'bg-emerald-100 text-emerald-700'],
        'completed' => ['label' => 'Concluído', 'class' => 'bg-sky-100 text-sky-700'],
        'awaiting_exam' => ['label' => 'Aguardando prova', 'class' => 'bg-amber-100 text-amber-700'],
        'failed' => ['label' => 'Reprovado', 'class' => 'bg-rose-100 text-rose-700'],
        default => ['label' => 'Em andamento', 'class' => 'bg-slate-200 text-slate-700'],
    };
};
?>

<section class="el-ios el-ios-workspace space-y-8">
    <div class="overflow-hidden rounded-[2.25rem] border border-white/10 bg-white/[0.045] shadow-soft backdrop-blur-xl">
        <div class="grid gap-0 xl:grid-cols-[1.25fr,0.75fr]">
            <div class="relative overflow-hidden bg-[linear-gradient(135deg,_rgba(15,23,42,0.96),_rgba(8,47,73,0.78)_58%,_rgba(15,118,110,0.58))] p-8 text-white">
                <div class="absolute inset-0 opacity-20" style="background-image:url('<?= e($course['cover_url'] ?? '/assets/logo.png') ?>');background-size:cover;background-position:center;"></div>
                <div class="relative space-y-5">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-white">Professor</span>
                        <span class="rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] <?= e($courseStatus['class']) ?>"><?= e($courseStatus['label']) ?></span>
                        <?php if (!empty($course['category'])): ?>
                            <span class="rounded-full border border-white/20 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-cyan-100"><?= e($course['category']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h1 class="text-4xl font-black tracking-tight sm:text-5xl"><?= e($course['title'] ?? 'Curso') ?></h1>
                        <p class="mt-3 max-w-3xl text-base leading-relaxed text-sky-50/80"><?= e($course['description'] ?? 'Gerencie aulas, provas, matrículas e certificação em um fluxo único.') ?></p>
                    </div>
                    <div class="flex flex-wrap gap-5 text-sm text-sky-50/75">
                        <span>Professor: <strong class="text-white"><?= e($course['teacher_name'] ?? 'A definir') ?></strong></span>
                        <span>Carga horária: <strong class="text-white"><?= (int) ($course['workload_hours'] ?? 0) ?>h</strong></span>
                        <span>Template: <strong class="text-white"><?= e($selectedTemplate['name'] ?? 'Horizonte Acadêmico') ?></strong></span>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <?php if ($canEdit && $activeTab === 'lessons'): ?>
                            <button type="button" class="rounded-full bg-white px-5 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]" onclick="openLessonModal()">Nova aula</button>
                            <a href="/elearning/gestor/cursos/<?= (int) ($course['id'] ?? 0) ?>/provas" class="rounded-full border border-white/20 px-5 py-3 text-sm font-black text-white transition hover:bg-white/10">Criar prova</a>
                        <?php endif; ?>
                        <?php if ($canEdit && $activeTab === 'exams'): ?>
                            <button type="button" class="rounded-full bg-white px-5 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]" onclick="openExamModal()">Nova prova</button>
                            <a href="/elearning/gestor/cursos/<?= (int) ($course['id'] ?? 0) ?>/aulas" class="rounded-full border border-white/20 px-5 py-3 text-sm font-black text-white transition hover:bg-white/10">Voltar para aulas</a>
                        <?php endif; ?>
                        <a href="/elearning/gestor/diploma/config?course_id=<?= (int) ($course['id'] ?? 0) ?>" class="rounded-full border border-white/20 px-5 py-3 text-sm font-black text-white transition hover:bg-white/10">Certificado</a>
                        <a href="/elearning/gestor/cursos" class="rounded-full border border-white/20 px-5 py-3 text-sm font-black text-white transition hover:bg-white/10">Voltar</a>
                    </div>
                </div>
            </div>

            <aside class="grid gap-4 bg-slate-950/50 p-6 sm:grid-cols-2 xl:grid-cols-1">
                <?php foreach ([
                    ['label' => 'Aulas', 'value' => count($lessons), 'icon' => 'ph-play-circle'],
                    ['label' => 'Provas', 'value' => count($exams), 'icon' => 'ph-clipboard-text'],
                    ['label' => 'Alunos', 'value' => count($enrollments), 'icon' => 'ph-users-three'],
                    ['label' => 'Minutos em video', 'value' => $reports['course_video_human'] ?? '0 min', 'icon' => 'ph-hard-drives'],
                ] as $card): ?>
                    <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-900 text-white">
                                <i class="ph <?= e($card['icon']) ?> text-xl"></i>
                            </div>
                            <span class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Curso</span>
                        </div>
                        <p class="mt-5 text-3xl font-black text-slate-900"><?= e((string) $card['value']) ?></p>
                        <p class="mt-2 text-sm text-slate-600"><?= e($card['label']) ?></p>
                    </article>
                <?php endforeach; ?>
            </aside>
        </div>
    </div>

    <?php if (!$schemaReady): ?>
        <div class="rounded-[1.75rem] border border-amber-300 bg-amber-50 px-5 py-4 text-sm leading-relaxed text-amber-950">
            O schema MariaDB do módulo ainda não foi aplicado. Esta interface já está pronta, mas a persistência depende da execução do SQL do E-Learning.
        </div>
    <?php endif; ?>

    <nav class="flex flex-wrap gap-3">
        <?php foreach ([
            'lessons' => ['label' => 'Aulas', 'href' => '/elearning/gestor/cursos/' . (int) ($course['id'] ?? 0) . '/aulas', 'icon' => 'ph-play-circle'],
            'exams' => ['label' => 'Provas', 'href' => '/elearning/gestor/cursos/' . (int) ($course['id'] ?? 0) . '/provas', 'icon' => 'ph-clipboard-text'],
            'students' => ['label' => 'Matrículas', 'href' => '/elearning/gestor/cursos/' . (int) ($course['id'] ?? 0) . '/matriculas', 'icon' => 'ph-user-list'],
            'reports' => ['label' => 'Progresso', 'href' => '/elearning/gestor/cursos/' . (int) ($course['id'] ?? 0) . '/progresso', 'icon' => 'ph-chart-line-up'],
        ] as $tabKey => $tab): ?>
            <a href="<?= e($tab['href']) ?>" class="inline-flex items-center gap-2 rounded-full px-5 py-3 text-sm font-black transition <?= $activeTab === $tabKey ? 'bg-white text-slate-950 shadow-soft' : 'border border-white/10 bg-white/[0.045] text-slate-200 hover:bg-white/10' ?>">
                <i class="ph <?= e($tab['icon']) ?> text-base"></i>
                <?= e($tab['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="grid gap-6 xl:grid-cols-[1.2fr,0.8fr]">
        <section class="space-y-5">
            <?php if ($activeTab === 'lessons'): ?>
                <?php if (!$lessons): ?>
                    <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                        Nenhuma aula cadastrada ainda. Você pode criar quantas aulas quiser para o curso. Cada aula aceita 1 vídeo MP4 de até 80 MB e vários anexos de até 20 MB.
                    </div>
                <?php endif; ?>
                <?php foreach ($lessons as $lesson): ?>
                    <?php
                    $hasVideo = !empty($lesson['video_id']);
                    $videoProvider = (string) ($lesson['video_provider'] ?? '');
                    $isVideoProcessing = $hasVideo && $videoProvider === 'bunny';
                    $videoBadgeLabel = !$hasVideo
                        ? 'Sem video'
                        : ($isVideoProcessing ? 'Em preparo' : 'Pronto');
                    $videoBadgeClass = !$hasVideo
                        ? 'bg-slate-200 text-slate-700'
                        : ($isVideoProcessing ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700');
                    $videoActionLabel = !$hasVideo
                        ? ''
                        : ($isVideoProcessing ? 'Acompanhar' : 'Abrir video');
                    $videoStatusMessage = $isVideoProcessing
                        ? 'Estamos preparando este video no SGI STREAM.'
                        : '';
                    $videoPanelClass = $isVideoProcessing
                        ? 'border-amber-200 bg-[linear-gradient(135deg,_#fff7ed,_#ffffff_55%,_#fef3c7)]'
                        : 'border-slate-200 bg-slate-50';
                    ?>
                    <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-lg" data-lesson-card="<?= (int) ($lesson['id'] ?? 0) ?>">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="rounded-full bg-slate-900 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-white">Aula <?= (int) ($lesson['sequence_order'] ?? 1) ?></span>
                                    <span class="rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] <?= e($videoBadgeClass) ?>" data-role="video-status-badge"><?= e($videoBadgeLabel) ?></span>
                                </div>
                                <h2 class="text-2xl font-black tracking-tight text-slate-900"><?= e($lesson['title']) ?></h2>
                                <p class="text-sm leading-relaxed text-slate-600"><?= e($lesson['description'] ?? 'Sem descrição detalhada.') ?></p>
                                <p class="text-sm text-slate-500"><?= (int) ($lesson['estimated_minutes'] ?? 0) ?> min • <?= count($lesson['attachments'] ?? []) ?> anexo(s)</p>
                            </div>
                            <?php if ($canEdit): ?>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" class="rounded-full border border-slate-200 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-slate-700 transition hover:bg-slate-50" data-lesson='<?= e(json_encode($lesson, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>' onclick="openLessonModal(this)">Editar</button>
                                    <button type="button" class="rounded-full border border-slate-200 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-slate-700 transition hover:bg-slate-50" onclick="reorderLesson(<?= (int) $lesson['id'] ?>, 'up')">Subir</button>
                                    <button type="button" class="rounded-full border border-slate-200 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-slate-700 transition hover:bg-slate-50" onclick="reorderLesson(<?= (int) $lesson['id'] ?>, 'down')">Descer</button>
                                    <?php if ($canDelete): ?>
                                        <button type="button" class="rounded-full border border-rose-200 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-rose-700 transition hover:bg-rose-50" onclick="deleteLesson(<?= (int) $lesson['id'] ?>)">Excluir</button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-5 grid gap-4 lg:grid-cols-2">
                            <div class="rounded-[1.5rem] border p-4 shadow-sm <?= e($videoPanelClass) ?>" data-video-panel data-lesson-id="<?= (int) ($lesson['id'] ?? 0) ?>" data-video-provider="<?= e($videoProvider) ?>" data-has-video="<?= $hasVideo ? '1' : '0' ?>" data-video-ready="<?= $hasVideo && $videoProvider !== 'bunny' ? '1' : '0' ?>">
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Vídeo</p>
                                <p class="mt-3 text-sm leading-relaxed text-slate-600 [overflow-wrap:anywhere]" data-role="video-details"><?= !empty($lesson['video_id']) ? e($lesson['video_name']) . ' • ' . e($lesson['video_size_human'] ?? '0 B') . ' • ' . e($lesson['video_duration_human'] ?? '0 min') : 'Nenhum video enviado' ?></p>
                                <p class="mt-2 text-sm leading-relaxed text-slate-500" data-role="video-status-message"><?= e($videoStatusMessage) ?></p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <?php if (!empty($lesson['video_id'])): ?>
                                        <a href="/elearning/gestor/videos/<?= (int) $lesson['id'] ?>" target="_blank" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:scale-[1.02]" data-role="video-open-button"><?= e($videoActionLabel) ?></a>
                                        <?php if ($canDelete): ?>
                                            <button type="button" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-black text-rose-700 transition hover:bg-rose-50" onclick="deleteLessonVideo(<?= (int) $lesson['id'] ?>)">Remover</button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Anexos</p>
                                <div class="mt-3 space-y-3">
                                    <?php if (empty($lesson['attachments'])): ?>
                                        <p class="text-sm text-slate-500">Nenhum material de apoio cadastrado.</p>
                                    <?php endif; ?>
                                    <?php foreach ($lesson['attachments'] ?? [] as $attachment): ?>
                                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                            <div>
                                                <p class="text-sm font-bold text-slate-900"><?= e($attachment['title'] ?? $attachment['file_name']) ?></p>
                                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400"><?= e(strtoupper((string) ($attachment['extension'] ?? 'ARQ'))) ?></p>
                                            </div>
                                            <div class="flex gap-2">
                                                <a href="/elearning/gestor/anexos/<?= (int) $attachment['id'] ?>/download" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-black uppercase tracking-[0.2em] text-slate-700 transition hover:bg-slate-50">Baixar</a>
                                                <?php if ($canDelete): ?>
                                                    <button type="button" class="rounded-full border border-rose-200 px-3 py-2 text-xs font-black uppercase tracking-[0.2em] text-rose-700 transition hover:bg-rose-50" onclick="deleteAttachment(<?= (int) $attachment['id'] ?>)">Excluir</button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php elseif ($activeTab === 'exams'): ?>
                <?php if (!$exams): ?>
                    <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                        Nenhuma prova criada ainda. Cadastre avaliações objetivas com mínimo de 70% de aproveitamento.
                    </div>
                <?php endif; ?>
                <?php foreach ($exams as $exam): ?>
                    <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-lg">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex flex-wrap gap-3">
                                    <span class="rounded-full bg-slate-900 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-white"><?= !empty($exam['is_mandatory']) ? 'Obrigatória' : 'Opcional' ?></span>
                                    <span class="rounded-full bg-sky-100 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-sky-700"><?= number_format((float) ($exam['passing_score'] ?? 70), 0) ?>% mínimo</span>
                                </div>
                                <h2 class="text-2xl font-black tracking-tight text-slate-900"><?= e($exam['title']) ?></h2>
                                <p class="text-sm leading-relaxed text-slate-600"><?= e($exam['instructions'] ?? 'Sem instruções adicionais.') ?></p>
                                <p class="text-sm text-slate-500"><?= (int) ($exam['questions_count'] ?? 0) ?> questões • <?= (int) ($exam['attempts_allowed'] ?? 1) ?> tentativa(s) • <?= (int) ($exam['attempts_count'] ?? 0) ?> tentativa(s) realizadas</p>
                            </div>
                            <?php if ($canEdit): ?>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" class="rounded-full border border-slate-200 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-slate-700 transition hover:bg-slate-50" data-exam='<?= e(json_encode($exam, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>' onclick="openExamModal(this)">Editar</button>
                                    <?php if ($canDelete): ?>
                                        <button type="button" class="rounded-full border border-rose-200 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-rose-700 transition hover:bg-rose-50" onclick="deleteExam(<?= (int) $exam['id'] ?>)">Excluir</button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-5 space-y-3">
                            <?php foreach ($exam['questions'] ?? [] as $questionIndex => $question): ?>
                                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                                    <h3 class="text-sm font-black uppercase tracking-[0.2em] text-slate-500">Questão <?= $questionIndex + 1 ?></h3>
                                    <p class="mt-2 text-base font-bold text-slate-900"><?= e($question['statement']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php elseif ($activeTab === 'students'): ?>
                <?php if (!$enrollments): ?>
                    <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                        Nenhum aluno matriculado neste curso ainda.
                    </div>
                <?php endif; ?>
                <?php foreach ($enrollments as $enrollment): ?>
                    <?php $meta = $enrollmentStatus((string) ($enrollment['status'] ?? 'in_progress')); ?>
                    <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-lg">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h2 class="text-2xl font-black tracking-tight text-slate-900"><?= e($enrollment['student_name'] ?? 'Aluno') ?></h2>
                                    <span class="rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] <?= e($meta['class']) ?>"><?= e($meta['label']) ?></span>
                                </div>
                                <p class="text-sm text-slate-500"><?= e($enrollment['student_email'] ?? 'Sem e-mail cadastrado') ?></p>
                                <p class="text-sm text-slate-500">Progresso: <?= number_format((float) ($enrollment['progress_percent'] ?? 0), 0) ?>% • Melhor nota: <?= isset($enrollment['best_score']) && $enrollment['best_score'] !== null ? number_format((float) $enrollment['best_score'], 0) . '%' : '--' ?></p>
                            </div>
                            <?php if ($canEdit && in_array((string) ($enrollment['status'] ?? ''), ['approved', 'completed'], true)): ?>
                                <button type="button" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:scale-[1.02]" onclick="issueCertificate(<?= (int) $enrollment['id'] ?>)">Emitir certificado</button>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="grid gap-4 md:grid-cols-2">
                    <?php foreach ([
                        ['label' => 'Progresso médio', 'value' => number_format((float) ($reports['average_progress'] ?? 0), 0) . '%'],
                        ['label' => 'Aguardando prova', 'value' => (int) ($reports['pending_exams'] ?? 0)],
                        ['label' => 'Concluíram/aprovados', 'value' => (int) ($reports['completed_students'] ?? 0)],
                        ['label' => 'Taxa de aprovação', 'value' => number_format((float) ($reports['approval_rate'] ?? 0), 0) . '%'],
                    ] as $card): ?>
                        <article class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-lg">
                            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400"><?= e($card['label']) ?></p>
                            <p class="mt-4 text-3xl font-black text-slate-900"><?= e((string) $card['value']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Alunos do curso</p>
                    <div class="mt-5 space-y-3">
                        <?php foreach ($enrollments as $enrollment): ?>
                            <?php $meta = $enrollmentStatus((string) ($enrollment['status'] ?? 'in_progress')); ?>
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div>
                                    <p class="text-sm font-bold text-slate-900"><?= e($enrollment['student_name'] ?? 'Aluno') ?></p>
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400"><?= e($meta['label']) ?></p>
                                </div>
                                <strong class="text-slate-900"><?= number_format((float) ($enrollment['progress_percent'] ?? 0), 0) ?>%</strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </section>

        <aside class="space-y-6">
            <?php if ($activeTab === 'students'): ?>
                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Matrículas</p>
                    <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Adicionar aluno</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">Associe usuários ao curso para liberar aulas, avaliações, progresso e certificado.</p>
                    <?php if ($canEdit): ?>
                        <form id="enrollment-form" class="mt-6 space-y-4">
                            <input type="hidden" name="course_id" value="<?= (int) ($course['id'] ?? 0) ?>">
                            <div>
                                <label for="student_id" class="mb-2 block text-sm font-bold text-slate-700">Aluno</label>
                                <select id="student_id" name="student_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900">
                                    <option value="">Selecione um usuário</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= (int) $user['id'] ?>"><?= e($user['name']) ?><?= !empty($user['email']) ? ' • ' . e($user['email']) : '' ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="w-full rounded-full bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:scale-[1.02]">Matricular aluno</button>
                        </form>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if ($activeTab === 'reports'): ?>
                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Leitura rápida</p>
                    <div class="mt-5 space-y-3">
                        <?php foreach ([
                            ['label' => 'Minutos do curso', 'value' => $reports['course_video_human'] ?? '0 min'],
                            ['label' => 'Global contratado', 'value' => $storage['contracted_human'] ?? '10.000 min'],
                            ['label' => 'Global usado', 'value' => $storage['used_human'] ?? '0 min'],
                            ['label' => 'Consumo global', 'value' => number_format((float) ($storage['percent_used'] ?? 0), 2, ',', '.') . '%'],
                        ] as $row): ?>
                            <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <span class="text-sm font-bold text-slate-700"><?= e($row['label']) ?></span>
                                <strong class="text-slate-900"><?= e((string) $row['value']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Governança</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Regras principais</h2>
                <ul class="mt-5 space-y-3 text-sm leading-relaxed text-slate-600">
                    <li>1 vídeo MP4 por aula, com limite de 80 MB.</li>
                    <li>Vários anexos por aula, com limite de 20 MB por arquivo.</li>
                    <li>Certificado exige 70% ou mais em prova obrigatória.</li>
                    <li>Uploads de video sao bloqueados quando o modulo atingir 10.000 minutos.</li>
                </ul>
            </section>

            <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Certificado do curso</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900"><?= e($selectedTemplate['name'] ?? 'Horizonte Acadêmico') ?></h2>
                <p class="mt-3 text-sm leading-relaxed text-slate-600"><?= e($selectedTemplate['description'] ?? 'Modelo selecionado para emissão automática ao aluno elegível.') ?></p>
                <div class="mt-5 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Cor de destaque</p>
                            <p class="mt-2 text-sm font-bold text-slate-900"><?= e($courseSettings['accent_color'] ?? ($selectedTemplate['default_settings']['accent_color'] ?? '#1d4ed8')) ?></p>
                        </div>
                        <span class="h-10 w-10 rounded-2xl border border-white shadow-inner" style="background: <?= e($courseSettings['accent_color'] ?? ($selectedTemplate['default_settings']['accent_color'] ?? '#1d4ed8')) ?>"></span>
                    </div>
                </div>
                <a href="/elearning/gestor/diploma/config?course_id=<?= (int) ($course['id'] ?? 0) ?>" class="mt-5 inline-flex rounded-full bg-slate-900 px-4 py-3 text-sm font-black text-white transition hover:scale-[1.02]">Personalizar</a>
            </section>
        </aside>
    </div>
</section>

<div id="elearning-professor-toast" class="pointer-events-none fixed right-4 top-4 z-[90] hidden rounded-2xl bg-slate-900 px-4 py-3 text-sm font-bold text-white shadow-2xl"></div>

<?php if ($activeTab === 'lessons' && $canEdit): ?>
    <div id="lesson-modal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
        <div class="w-full max-w-4xl rounded-[2rem] bg-white p-8 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Aula</p>
                    <h2 id="lesson-modal-title" class="mt-2 text-3xl font-black tracking-tight text-slate-900">Nova aula</h2>
                </div>
                <button type="button" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50" onclick="closeLessonModal()">Fechar</button>
            </div>
            <form id="lesson-form" class="mt-8 grid gap-5 md:grid-cols-2">
                <input type="hidden" name="course_id" value="<?= (int) ($course['id'] ?? 0) ?>">
                <input type="hidden" name="lesson_id" id="lesson_id">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Título da aula</label>
                    <input type="text" name="title" id="lesson_title" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900" required>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Descrição</label>
                    <textarea name="description" id="lesson_description" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900"></textarea>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700">Sequência</label>
                    <input type="number" min="1" name="sequence_order" id="lesson_sequence_order" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700">Duração estimada (min)</label>
                    <input type="number" min="0" name="estimated_minutes" id="lesson_estimated_minutes" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Vídeo</label>
                    <input type="file" name="video" id="lesson_video" accept=".mp4,video/mp4" class="block w-full rounded-2xl border border-dashed border-slate-300 px-4 py-4 text-sm text-slate-700">
                    <p class="mt-2 text-xs uppercase tracking-[0.2em] text-slate-400">MP4 obrigatório • até 80 MB</p>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Anexos</label>
                    <input type="file" name="attachments[]" id="lesson_attachments" multiple accept=".pdf,.png,.jpg,.jpeg,.webp,.doc,.docx,.xls,.xlsx,.ppt,.pptx,image/*,application/pdf" class="block w-full rounded-2xl border border-dashed border-slate-300 px-4 py-4 text-sm text-slate-700">
                    <p class="mt-2 text-xs uppercase tracking-[0.2em] text-slate-400">Múltiplos arquivos • até 20 MB por item</p>
                </div>
                <div id="lesson-upload-feedback" class="hidden md:col-span-2 overflow-hidden rounded-[1.75rem] border border-slate-200 bg-[linear-gradient(135deg,_#0f172a,_#1d4ed8_62%,_#0f766e)] p-5 text-white shadow-lg">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="mt-1 flex h-11 w-11 items-center justify-center rounded-2xl bg-white/12">
                                <span class="relative flex h-3 w-3">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-300/70"></span>
                                    <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-200"></span>
                                </span>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-100/70">Upload de video</p>
                                <p id="lesson-upload-stage" class="mt-2 text-lg font-black text-white">Preparando envio</p>
                                <p id="lesson-upload-caption" class="mt-1 text-sm leading-relaxed text-sky-50/80">Organizando o arquivo para iniciar o envio.</p>
                            </div>
                        </div>
                        <strong id="lesson-upload-percent" class="rounded-full bg-white/10 px-4 py-2 text-sm font-black text-white/90">0%</strong>
                    </div>
                    <div class="mt-5 h-2.5 overflow-hidden rounded-full bg-white/10">
                        <div id="lesson-upload-bar" class="h-full rounded-full bg-white transition-all duration-500" style="width:0%"></div>
                    </div>
                </div>
                <div class="md:col-span-2 flex justify-end gap-3">
                    <button type="button" class="rounded-full border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50" onclick="closeLessonModal()">Cancelar</button>
                    <button type="submit" class="rounded-full bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:scale-[1.02]">Salvar aula</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if ($activeTab === 'exams' && $canEdit): ?>
    <div id="exam-modal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
        <div class="w-full max-w-5xl rounded-[2rem] bg-white p-8 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Prova</p>
                    <h2 id="exam-modal-title" class="mt-2 text-3xl font-black tracking-tight text-slate-900">Nova prova</h2>
                </div>
                <button type="button" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50" onclick="closeExamModal()">Fechar</button>
            </div>
            <form id="exam-form" class="mt-8 space-y-6">
                <input type="hidden" name="course_id" value="<?= (int) ($course['id'] ?? 0) ?>">
                <input type="hidden" name="exam_id" id="exam_id">
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-bold text-slate-700">Título</label>
                        <input type="text" id="exam_title" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-bold text-slate-700">Instruções</label>
                        <textarea id="exam_instructions" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900"></textarea>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Tentativas</label>
                        <input type="number" min="1" id="exam_attempts_allowed" value="1" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Nota mínima (%)</label>
                        <input type="number" min="70" max="100" id="exam_passing_score" value="70" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Status</label>
                        <select id="exam_status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900">
                            <option value="published">Publicado</option>
                            <option value="draft">Rascunho</option>
                            <option value="archived">Arquivado</option>
                        </select>
                    </div>
                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
                        <input type="checkbox" id="exam_is_mandatory" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                        Exigir aprovação para certificado
                    </label>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Questões</p>
                            <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Construtor</h3>
                        </div>
                        <button type="button" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:scale-[1.02]" onclick="addQuestionBlock()">Adicionar questão</button>
                    </div>
                    <div id="questions-container" class="mt-5 space-y-4"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" class="rounded-full border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50" onclick="closeExamModal()">Cancelar</button>
                    <button type="submit" class="rounded-full bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:scale-[1.02]">Salvar prova</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
function showProfessorToast(message, type = 'success') {
    const toast = document.getElementById('elearning-professor-toast');
    if (!toast) {
        alert(message);
        return;
    }
    toast.textContent = message;
    toast.className = 'pointer-events-none fixed right-4 top-4 z-[90] rounded-2xl px-4 py-3 text-sm font-bold text-white shadow-2xl ' + (type === 'error' ? 'bg-rose-600' : 'bg-slate-900');
    toast.classList.remove('hidden');
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => toast.classList.add('hidden'), 2600);
}

async function parseELearningResponse(response) {
    const text = await response.text();
    let result = {};

    try {
        result = text ? JSON.parse(text) : {};
    } catch (error) {
        throw new Error('O servidor retornou uma resposta invalida.');
    }

    if (!response.ok || !result.success) {
        throw new Error(result.message || 'Nao foi possivel concluir a operacao.');
    }

    return result;
}

async function postELearning(url, formData, options = {}) {
    const { reload = true, persistToast = true } = options;
    const response = await fetch(url, { method: 'POST', body: formData });
    const result = await parseELearningResponse(response);
    if (persistToast) {
        sessionStorage.setItem('elearning-professor-toast', result.message || 'Operacao concluida com sucesso.');
    }
    if (reload) {
        window.location.reload();
    }
    return result;
}

window.addEventListener('DOMContentLoaded', () => {
    const pendingToast = sessionStorage.getItem('elearning-professor-toast');
    if (pendingToast) {
        showProfessorToast(pendingToast);
        sessionStorage.removeItem('elearning-professor-toast');
    }
    if (window.TomSelect && document.getElementById('student_id')) {
        new TomSelect('#student_id', { create: false, maxOptions: 500, placeholder: 'Digite para localizar um aluno' });
    }
});
</script>

<?php if ($activeTab === 'lessons' && $canEdit): ?>
    <script>
    const lessonModal = document.getElementById('lesson-modal');
    const lessonForm = document.getElementById('lesson-form');
    const lessonUploadFeedback = document.getElementById('lesson-upload-feedback');
    const lessonUploadStage = document.getElementById('lesson-upload-stage');
    const lessonUploadCaption = document.getElementById('lesson-upload-caption');
    const lessonUploadPercent = document.getElementById('lesson-upload-percent');
    const lessonUploadBar = document.getElementById('lesson-upload-bar');
    const lessonStatusPollTimers = new Map();

    function validateLessonFiles() {
        const videoInput = document.getElementById('lesson_video');
        const attachmentsInput = document.getElementById('lesson_attachments');
        const video = videoInput?.files?.[0];
        if (video) {
            if (!video.name.toLowerCase().endsWith('.mp4')) {
                showProfessorToast('O vídeo deve ser obrigatoriamente MP4.', 'error');
                videoInput.value = '';
                return false;
            }
            if (video.size > 80 * 1024 * 1024) {
                showProfessorToast('O vídeo excede o limite de 80 MB.', 'error');
                videoInput.value = '';
                return false;
            }
        }

        for (const file of Array.from(attachmentsInput?.files || [])) {
            if (file.size > 20 * 1024 * 1024) {
                showProfessorToast('Cada anexo deve ter no máximo 20 MB.', 'error');
                attachmentsInput.value = '';
                return false;
            }
        }
        return true;
    }

    function setLessonUploadFeedback(stage, percent = 0, caption = '') {
        if (!lessonUploadFeedback) {
            return;
        }

        lessonUploadFeedback.classList.remove('hidden');
        if (lessonUploadStage) lessonUploadStage.textContent = stage;
        if (lessonUploadCaption) lessonUploadCaption.textContent = caption;
        if (lessonUploadPercent) lessonUploadPercent.textContent = `${Math.max(0, Math.min(100, Math.round(percent)))}%`;
        if (lessonUploadBar) lessonUploadBar.style.width = `${Math.max(0, Math.min(100, percent))}%`;
    }

    function resetLessonUploadFeedback() {
        if (!lessonUploadFeedback) {
            return;
        }

        lessonUploadFeedback.classList.add('hidden');
        if (lessonUploadStage) lessonUploadStage.textContent = 'Preparando envio';
        if (lessonUploadCaption) lessonUploadCaption.textContent = 'Organizando o arquivo para iniciar o envio.';
        if (lessonUploadPercent) lessonUploadPercent.textContent = '0%';
        if (lessonUploadBar) lessonUploadBar.style.width = '0%';
    }

    function parseLessonUploadResult(xhr) {
        const body = xhr.response && typeof xhr.response === 'object'
            ? xhr.response
            : (() => {
                try {
                    return xhr.responseText ? JSON.parse(xhr.responseText) : {};
                } catch (error) {
                    return {};
                }
            })();

        if (xhr.status < 200 || xhr.status >= 300 || !body?.success) {
            throw new Error(body?.message || 'Nao foi possivel concluir o upload da aula.');
        }

        return body;
    }

    function uploadLessonWithFeedback(formElement) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/elearning/gestor/aulas/store');
            xhr.responseType = 'json';
            xhr.timeout = 1000 * 60 * 15;

            xhr.upload.addEventListener('progress', (event) => {
                if (!event.lengthComputable) {
                    setLessonUploadFeedback('Enviando video para o servidor...', 18, 'Transferindo o arquivo inicial.');
                    return;
                }

                const browserPercent = Math.round((event.loaded / event.total) * 100);
                const stagedPercent = Math.max(8, Math.min(68, Math.round(browserPercent * 0.68)));
                setLessonUploadFeedback(
                    'Enviando video para o servidor...',
                    stagedPercent,
                    `Upload local concluido em ${browserPercent}%`
                );
            });

            xhr.upload.addEventListener('load', () => {
                setLessonUploadFeedback(
                    'Arquivo recebido. Iniciando preparo no SGI STREAM...',
                    78,
                    'Agora estamos organizando o video para publicacao.'
                );
            });

            xhr.onreadystatechange = () => {
                if (xhr.readyState === XMLHttpRequest.HEADERS_RECEIVED) {
                    setLessonUploadFeedback('Estamos processando o video...', 86, 'Voce pode continuar na tela enquanto finalizamos a etapa final.');
                    return;
                }

                if (xhr.readyState !== XMLHttpRequest.DONE) {
                    return;
                }

                try {
                    const result = parseLessonUploadResult(xhr);
                    setLessonUploadFeedback('Aula salva. Atualizando painel...', 100, 'Tudo certo por aqui.');
                    resolve(result);
                } catch (error) {
                    reject(error);
                }
            };

            xhr.onerror = () => reject(new Error('Nao foi possivel concluir o upload da aula.'));
            xhr.ontimeout = () => reject(new Error('O upload demorou mais do que o esperado. Tente novamente.'));
            xhr.send(new FormData(formElement));
        });
    }

    function setVideoBadgeState(badge, label, variant) {
        if (!badge) {
            return;
        }

        badge.textContent = label;
        badge.className = 'rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] ' + (
            variant === 'ready'
                ? 'bg-emerald-100 text-emerald-700'
                : (variant === 'processing'
                    ? 'bg-amber-100 text-amber-700'
                    : 'bg-slate-200 text-slate-700')
        );
    }

    function setVideoButtonState(button, ready) {
        if (!button) {
            return;
        }

        button.textContent = ready ? 'Abrir video' : 'Acompanhar';
        button.className = ready
            ? 'rounded-full bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:scale-[1.02]'
            : 'rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-black text-amber-700 transition hover:bg-amber-100';
    }

    function applyLessonVideoStatus(panel, payload) {
        const lessonCard = panel.closest('[data-lesson-card]');
        const badge = lessonCard?.querySelector('[data-role="video-status-badge"]');
        const button = panel.querySelector('[data-role="video-open-button"]');
        const message = panel.querySelector('[data-role="video-status-message"]');

        if (!payload?.has_video) {
            setVideoBadgeState(badge, 'Sem video', 'empty');
            if (message) message.textContent = '';
            return false;
        }

        const isReady = !!payload.is_ready;
        panel.dataset.videoReady = isReady ? '1' : '0';

        setVideoBadgeState(
            badge,
            payload.status_label || (isReady ? 'Pronto' : 'Processando'),
            isReady ? 'ready' : 'processing'
        );
        setVideoButtonState(button, isReady);

        if (message) {
            message.textContent = isReady
                ? 'Video pronto para abrir.'
                : (payload.processing_message || 'O SGI STREAM ainda esta preparando o video desta aula.');
        }

        return isReady;
    }

    async function refreshLessonVideoStatus(panel) {
        const lessonId = Number(panel?.dataset.lessonId || 0);
        if (lessonId <= 0) {
            return true;
        }

        const response = await fetch(`/elearning/gestor/aulas/${lessonId}/video-status`, {
            headers: { Accept: 'application/json' },
        });
        const payload = await parseELearningResponse(response);
        return applyLessonVideoStatus(panel, payload);
    }

    function watchLessonVideoStatus(panel) {
        if (!panel || panel.dataset.videoProvider !== 'bunny' || panel.dataset.hasVideo !== '1') {
            return;
        }

        const lessonId = Number(panel.dataset.lessonId || 0);
        if (lessonId <= 0) {
            return;
        }

        const poll = async () => {
            try {
                const ready = await refreshLessonVideoStatus(panel);
                if (ready) {
                    if (Number(sessionStorage.getItem('elearning-professor-watch-lesson') || 0) === lessonId) {
                        sessionStorage.removeItem('elearning-professor-watch-lesson');
                        showProfessorToast('Video pronto para assistir.', 'success');
                    }
                    lessonStatusPollTimers.delete(lessonId);
                    return;
                }
            } catch (error) {
                // Best effort polling without bloquear a pagina.
            }

            const attempts = Number(panel.dataset.statusAttempts || 0) + 1;
            panel.dataset.statusAttempts = String(attempts);
            if (attempts >= 30) {
                lessonStatusPollTimers.delete(lessonId);
                return;
            }

            const timerId = window.setTimeout(poll, 6000);
            lessonStatusPollTimers.set(lessonId, timerId);
        };

        poll();
    }

    function openLessonModal(button = null) {
        lessonForm.reset();
        resetLessonUploadFeedback();
        document.getElementById('lesson_id').value = '';
        document.getElementById('lesson-modal-title').textContent = 'Nova aula';
        document.getElementById('lesson_sequence_order').value = <?= max(1, count($lessons) + 1) ?>;
        if (button?.dataset.lesson) {
            const lesson = JSON.parse(button.dataset.lesson);
            document.getElementById('lesson-modal-title').textContent = 'Editar aula';
            document.getElementById('lesson_id').value = lesson.id || '';
            document.getElementById('lesson_title').value = lesson.title || '';
            document.getElementById('lesson_description').value = lesson.description || '';
            document.getElementById('lesson_sequence_order').value = lesson.sequence_order || '';
            document.getElementById('lesson_estimated_minutes').value = lesson.estimated_minutes || '';
        }
        lessonModal.classList.remove('hidden');
        lessonModal.classList.add('flex');
    }

    function closeLessonModal() {
        resetLessonUploadFeedback();
        lessonModal.classList.add('hidden');
        lessonModal.classList.remove('flex');
    }

    lessonForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!validateLessonFiles()) {
            return;
        }

        const hasVideoUpload = Boolean(document.getElementById('lesson_video')?.files?.length);
        const submitButton = event.currentTarget.querySelector('button[type="submit"]');
        const originalLabel = submitButton?.textContent || 'Salvar aula';

        try {
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Enviando aula...';
                submitButton.classList.add('opacity-70', 'cursor-not-allowed');
            }

            let result;
            if (hasVideoUpload) {
                setLessonUploadFeedback('Preparando arquivo para envio...', 6, 'Etapa 1 de 2');
                result = await uploadLessonWithFeedback(event.currentTarget);
                sessionStorage.setItem('elearning-professor-toast', result.message || 'Aula salva com sucesso.');
            } else {
                showProfessorToast('Salvando aula e anexos...', 'success');
                result = await postELearning('/elearning/gestor/aulas/store', new FormData(event.currentTarget), {
                    reload: false,
                });
            }

            if (hasVideoUpload && result?.lesson_id) {
                sessionStorage.setItem('elearning-professor-watch-lesson', String(result.lesson_id));
            }

            window.location.reload();
        } catch (error) {
            if (hasVideoUpload) {
                setLessonUploadFeedback('Falha no envio', 100, error.message || 'Revise o arquivo e tente novamente.');
            }
            showProfessorToast(error.message, 'error');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalLabel;
                submitButton.classList.remove('opacity-70', 'cursor-not-allowed');
            }
        }
    });

    async function reorderLesson(lessonId, direction) {
        try {
            const formData = new FormData();
            formData.append('lesson_id', lessonId);
            formData.append('direction', direction);
            await postELearning('/elearning/gestor/aulas/reorder', formData);
        } catch (error) {
            showProfessorToast(error.message, 'error');
        }
    }

    async function deleteLesson(lessonId) {
        if (!confirm('Deseja excluir esta aula?')) {
            return;
        }
        try {
            const formData = new FormData();
            formData.append('id', lessonId);
            await postELearning('/elearning/gestor/aulas/delete', formData);
        } catch (error) {
            showProfessorToast(error.message, 'error');
        }
    }

    async function deleteLessonVideo(lessonId) {
        if (!confirm('Deseja remover o vídeo desta aula?')) {
            return;
        }
        try {
            const formData = new FormData();
            formData.append('type', 'video');
            formData.append('lesson_id', lessonId);
            await postELearning('/elearning/gestor/materiais/delete', formData);
        } catch (error) {
            showProfessorToast(error.message, 'error');
        }
    }

    async function deleteAttachment(attachmentId) {
        if (!confirm('Deseja excluir este anexo?')) {
            return;
        }
        try {
            const formData = new FormData();
            formData.append('type', 'attachment');
            formData.append('attachment_id', attachmentId);
            await postELearning('/elearning/gestor/materiais/delete', formData);
        } catch (error) {
            showProfessorToast(error.message, 'error');
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        const watchedLessonId = Number(sessionStorage.getItem('elearning-professor-watch-lesson') || 0);
        if (watchedLessonId > 0) {
            window.setTimeout(() => {
                showProfessorToast('A aula foi salva. Vamos acompanhar o processamento do SGI STREAM automaticamente.', 'success');
            }, 400);
        }

        document.querySelectorAll('[data-video-panel][data-video-provider="bunny"][data-has-video="1"]').forEach((panel) => {
            watchLessonVideoStatus(panel);
        });
    });
    </script>
<?php endif; ?>

<?php if ($activeTab === 'exams' && $canEdit): ?>
    <script>
    const examModal = document.getElementById('exam-modal');
    const examForm = document.getElementById('exam-form');
    const questionsContainer = document.getElementById('questions-container');

    function questionMarkup(question = {}) {
        const options = Array.isArray(question.options) && question.options.length
            ? question.options.map((option) => option.option_text ?? option)
            : ['', '', '', ''];
        while (options.length < 4) options.push('');
        const correctIndex = Array.isArray(question.options)
            ? Math.max(0, question.options.findIndex((option) => Number(option.is_correct) === 1))
            : 0;
        const radioGroup = `question_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`;
        return `
            <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5" data-question="item">
                <div class="flex items-center justify-between gap-4">
                    <p class="text-sm font-black uppercase tracking-[0.2em] text-slate-500">Questão objetiva</p>
                    <button type="button" class="rounded-full border border-rose-200 px-3 py-2 text-xs font-black uppercase tracking-[0.2em] text-rose-700 transition hover:bg-rose-50" onclick="this.closest('[data-question=&quot;item&quot;]').remove()">Remover</button>
                </div>
                <div class="mt-4 space-y-4">
                    <textarea data-field="statement" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900" placeholder="Enunciado da questão">${question.statement || ''}</textarea>
                    <input type="number" min="1" data-field="score" value="${question.score || 1}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900" placeholder="Pontuação">
                    <div class="grid gap-3 md:grid-cols-2">
                        ${options.map((option, index) => `
                            <label class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="${radioGroup}" value="${index}" ${index === correctIndex ? 'checked' : ''} class="h-4 w-4 border-slate-300 text-slate-900 focus:ring-slate-900">
                                    <span class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Alternativa ${index + 1}</span>
                                </div>
                                <input type="text" data-field="option" value="${String(option).replace(/"/g, '&quot;')}" class="mt-3 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900">
                            </label>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
    }

    function addQuestionBlock(question = {}) {
        questionsContainer.insertAdjacentHTML('beforeend', questionMarkup(question));
    }

    function openExamModal(button = null) {
        examForm.reset();
        questionsContainer.innerHTML = '';
        document.getElementById('exam_id').value = '';
        document.getElementById('exam-modal-title').textContent = 'Nova prova';
        document.getElementById('exam_passing_score').value = '70';
        document.getElementById('exam_attempts_allowed').value = '1';
        document.getElementById('exam_status').value = 'published';
        document.getElementById('exam_is_mandatory').checked = true;
        if (button?.dataset.exam) {
            const exam = JSON.parse(button.dataset.exam);
            document.getElementById('exam-modal-title').textContent = 'Editar prova';
            document.getElementById('exam_id').value = exam.id || '';
            document.getElementById('exam_title').value = exam.title || '';
            document.getElementById('exam_instructions').value = exam.instructions || '';
            document.getElementById('exam_attempts_allowed').value = exam.attempts_allowed || 1;
            document.getElementById('exam_passing_score').value = Math.max(70, Number(exam.passing_score || 70));
            document.getElementById('exam_status').value = exam.status || 'published';
            document.getElementById('exam_is_mandatory').checked = Number(exam.is_mandatory || 0) === 1;
            (exam.questions || []).forEach((question) => addQuestionBlock(question));
        }
        if (!questionsContainer.children.length) addQuestionBlock();
        examModal.classList.remove('hidden');
        examModal.classList.add('flex');
    }

    function closeExamModal() {
        examModal.classList.add('hidden');
        examModal.classList.remove('flex');
    }

    examForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const questions = Array.from(questionsContainer.querySelectorAll('[data-question="item"]'));
        if (!questions.length) {
            showProfessorToast('Adicione pelo menos uma questão objetiva.', 'error');
            return;
        }
        const formData = new FormData();
        formData.append('course_id', '<?= (int) ($course['id'] ?? 0) ?>');
        formData.append('exam_id', document.getElementById('exam_id').value || '');
        formData.append('title', document.getElementById('exam_title').value.trim());
        formData.append('instructions', document.getElementById('exam_instructions').value.trim());
        formData.append('attempts_allowed', document.getElementById('exam_attempts_allowed').value || '1');
        formData.append('passing_score', String(Math.max(70, Number(document.getElementById('exam_passing_score').value || 70))));
        formData.append('status', document.getElementById('exam_status').value || 'published');
        if (document.getElementById('exam_is_mandatory').checked) formData.append('is_mandatory', '1');

        let validCount = 0;
        questions.forEach((item, index) => {
            const statement = item.querySelector('[data-field="statement"]').value.trim();
            const score = item.querySelector('[data-field="score"]').value || '1';
            const options = Array.from(item.querySelectorAll('[data-field="option"]')).map((input) => input.value.trim());
            const radio = item.querySelector('input[type="radio"]:checked');
            if (!statement || options.filter(Boolean).length < 2 || !radio) return;
            validCount++;
            formData.append(`questions[${index}][statement]`, statement);
            formData.append(`questions[${index}][score]`, score);
            formData.append(`questions[${index}][correct_option]`, radio.value);
            options.forEach((option, optionIndex) => formData.append(`questions[${index}][options][${optionIndex}]`, option));
        });
        if (!validCount) {
            showProfessorToast('Preencha pelo menos uma questão válida com duas alternativas.', 'error');
            return;
        }
        try {
            await postELearning('/elearning/gestor/provas/store', formData);
        } catch (error) {
            showProfessorToast(error.message, 'error');
        }
    });

    async function deleteExam(examId) {
        if (!confirm('Deseja excluir esta prova?')) return;
        try {
            const formData = new FormData();
            formData.append('id', examId);
            await postELearning('/elearning/gestor/provas/delete', formData);
        } catch (error) {
            showProfessorToast(error.message, 'error');
        }
    }
    </script>
<?php endif; ?>

<?php if ($activeTab === 'students' && $canEdit): ?>
    <script>
    document.getElementById('enrollment-form')?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const studentField = document.getElementById('student_id');
        if (!studentField?.value) {
            showProfessorToast('Selecione um aluno para matricular.', 'error');
            return;
        }
        try {
            await postELearning('/elearning/gestor/matriculas/store', new FormData(event.currentTarget));
        } catch (error) {
            showProfessorToast(error.message, 'error');
        }
    });

    async function issueCertificate(enrollmentId) {
        if (!confirm('Deseja emitir o certificado agora para este aluno?')) return;
        try {
            const formData = new FormData();
            formData.append('enrollment_id', enrollmentId);
            await postELearning('/elearning/gestor/certificados/emitir', formData);
        } catch (error) {
            showProfessorToast(error.message, 'error');
        }
    }
    </script>
<?php endif; ?>
