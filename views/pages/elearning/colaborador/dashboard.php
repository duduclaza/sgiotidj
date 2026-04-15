<?php
$stats = $data['stats'] ?? [];
$enrolledCourses = $data['enrolled_courses'] ?? [];
$availableCourses = $data['available_courses'] ?? [];
$nextLesson = $data['next_lesson'] ?? null;
$certificates = $data['certificates'] ?? [];
$history = $data['history'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);

$statCards = [
    ['label' => 'Em andamento', 'value' => $stats['in_progress'] ?? 0, 'icon' => 'ph-book-open-text', 'tone' => 'blue'],
    ['label' => 'Concluidos', 'value' => $stats['completed'] ?? 0, 'icon' => 'ph-check-circle', 'tone' => 'green'],
    ['label' => 'Provas pendentes', 'value' => $stats['pending_exams'] ?? 0, 'icon' => 'ph-clipboard-text', 'tone' => 'orange'],
    ['label' => 'Progresso geral', 'value' => ($stats['overall_progress'] ?? 0) . '%', 'icon' => 'ph-chart-line-up', 'tone' => 'cyan'],
];
?>

<div class="el-ios">
    <div class="el-page el-stack">
        <header class="el-page-head">
            <div>
                <p class="el-eyebrow">Aluno</p>
                <h1 class="el-title">Inicio</h1>
                <p class="el-subtitle">Continue suas trilhas, encontre novos cursos e acompanhe certificados em uma experiencia mais limpa.</p>
            </div>
            <div class="el-actions">
                <a href="/elearning/colaborador/certificados" class="el-btn el-btn-secondary"><i class="ph ph-certificate"></i> Certificados</a>
                <a href="/elearning/colaborador/historico" class="el-btn el-btn-outline"><i class="ph ph-clock-counter-clockwise"></i> Historico</a>
                <a href="/inicio" class="el-btn el-btn-outline"><i class="ph ph-house"></i> Inicio SGQ</a>
            </div>
        </header>

        <?php if (!$schemaReady): ?>
            <div class="el-alert">O front do modulo ja esta disponivel, mas o esquema do banco de dados ainda nao foi aplicado.</div>
        <?php endif; ?>

        <section class="el-hero">
            <div class="el-hero-inner">
                <div>
                    <p class="el-eyebrow">Campus digital</p>
                    <?php if ($nextLesson): ?>
                        <div class="el-badges" style="margin-bottom:12px">
                            <span class="el-badge blue">Continuar aula</span>
                            <span class="el-badge green">Curso: <?= e($nextLesson['course_title']) ?></span>
                        </div>
                        <h2 class="el-title">Aula: <?= e($nextLesson['title']) ?></h2>
                        <p class="el-subtitle">Retome esta aula dentro do curso <?= e($nextLesson['course_title']) ?>.</p>
                        <div class="el-actions" style="margin-top:18px">
                            <a href="/elearning/colaborador/materiais/<?= (int) $nextLesson['id'] ?>/assistir" class="el-btn el-btn-primary"><i class="ph ph-play-circle"></i> Continuar aula</a>
                            <a href="/elearning/colaborador/cursos/<?= (int) ($nextLesson['course_id'] ?? 0) ?>" class="el-btn el-btn-secondary">Ver curso</a>
                        </div>
                    <?php else: ?>
                        <h2 class="el-title">Sua jornada esta em dia.</h2>
                        <p class="el-subtitle">Explore o catalogo para encontrar novos conhecimentos ou revise o historico dos cursos concluídos.</p>
                    <?php endif; ?>
                </div>
                <aside class="el-hero-panel">
                    <span class="el-icon blue"><i class="ph ph-chart-line-up"></i></span>
                    <p class="el-metric-value"><?= e((string) ($stats['overall_progress'] ?? 0)) ?>%</p>
                    <p class="el-metric-label">progresso medio das suas trilhas</p>
                    <div class="el-progress" style="margin-top:16px">
                        <div class="el-progress-track">
                            <div class="el-progress-fill" style="width: <?= min(100, max(0, (float) ($stats['overall_progress'] ?? 0))) ?>%"></div>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="el-metric-grid">
            <?php foreach ($statCards as $card): ?>
                <article class="el-metric">
                    <div class="el-metric-top">
                        <span class="el-icon <?= e($card['tone']) ?>"><i class="ph <?= e($card['icon']) ?>"></i></span>
                    </div>
                    <p class="el-metric-value"><?= e((string) $card['value']) ?></p>
                    <p class="el-metric-label"><?= e($card['label']) ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <div class="el-grid">
            <section class="el-col-8">
                <div class="el-section-head">
                    <div>
                        <h2 class="el-section-title">Cursos ativos</h2>
                        <p class="el-section-copy"><?= count($enrolledCourses) ?> trilha(s) em andamento.</p>
                    </div>
                </div>

                <div class="el-list">
                    <?php if (!$enrolledCourses): ?>
                        <div class="el-empty">Nenhuma trilha matriculada ainda.</div>
                    <?php endif; ?>

                    <?php foreach ($enrolledCourses as $course): ?>
                        <?php $progress = min(100, max(0, (float) ($course['progress_percent'] ?? 0))); ?>
                        <article class="el-course horizontal">
                            <div class="el-cover">
                                <img src="<?= e($course['cover_url']) ?>" alt="<?= e($course['title']) ?>">
                            </div>
                            <div class="el-course-body">
                                <div class="el-badges">
                                    <span class="el-badge blue"><?= e($course['category'] ?? 'Geral') ?></span>
                                    <span class="el-badge green"><?= e((string) ($course['enrollment_status'] ?? 'in_progress')) ?></span>
                                    <span class="el-badge"><?= (int) ($course['workload_hours'] ?? 0) ?>h</span>
                                </div>
                                <div>
                                    <h3 class="el-course-title"><?= e($course['title']) ?></h3>
                                    <p class="el-course-meta">Prof. <?= e($course['teacher_name'] ?? 'A definir') ?></p>
                                </div>
                                <div class="el-progress">
                                    <div class="el-progress-label"><span>Progresso</span><strong><?= number_format($progress, 0) ?>%</strong></div>
                                    <div class="el-progress-track"><div class="el-progress-fill" style="width: <?= $progress ?>%"></div></div>
                                </div>
                                <div class="el-course-actions">
                                    <a href="/elearning/colaborador/cursos/<?= (int) $course['id'] ?>/continuar" class="el-btn el-btn-sm el-btn-primary">Continuar</a>
                                    <a href="/elearning/colaborador/cursos/<?= (int) $course['id'] ?>" class="el-btn el-btn-sm el-btn-secondary">Visao geral</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="el-section-head" style="margin-top:24px">
                    <div>
                        <h2 class="el-section-title">Catalogo</h2>
                        <p class="el-section-copy">Novas oportunidades publicadas para voce.</p>
                    </div>
                </div>

                <div class="el-course-grid">
                    <?php if (!$availableCourses): ?>
                        <div class="el-empty" style="grid-column:1/-1">Todos os cursos publicados ja estao na sua trilha.</div>
                    <?php endif; ?>

                    <?php foreach ($availableCourses as $course): ?>
                        <article class="el-course">
                            <div class="el-cover">
                                <img src="<?= e($course['cover_url']) ?>" alt="<?= e($course['title']) ?>">
                            </div>
                            <div class="el-course-body">
                                <div class="el-badges">
                                    <span class="el-badge blue"><?= e($course['category'] ?? 'Geral') ?></span>
                                    <span class="el-badge"><?= (int) ($course['workload_hours'] ?? 0) ?>h</span>
                                </div>
                                <h3 class="el-course-title"><?= e($course['title']) ?></h3>
                                <p class="el-course-meta">Prof. <?= e($course['teacher_name'] ?? 'Padrao') ?></p>
                                <button type="button" class="el-btn el-btn-primary el-btn-block" onclick="enrollCourse(<?= (int) $course['id'] ?>)">Matricular agora</button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <aside class="el-col-4 el-stack">
                <section class="el-panel">
                    <div class="el-section-head">
                        <div>
                            <h2 class="el-section-title">Certificados</h2>
                            <p class="el-section-copy">Ultimas conquistas liberadas.</p>
                        </div>
                        <a href="/elearning/colaborador/certificados" class="el-btn el-btn-sm el-btn-outline">Ver</a>
                    </div>
                    <div class="el-list">
                        <?php if (!$certificates): ?>
                            <div class="el-empty">Conclua aulas e provas para liberar certificados.</div>
                        <?php endif; ?>
                        <?php foreach (array_slice($certificates, 0, 3) as $certificate): ?>
                            <a href="/elearning/colaborador/certificados/<?= e($certificate['validation_code']) ?>" class="el-list-item">
                                <span class="el-list-main">
                                    <strong class="el-list-title"><?= e($certificate['course_title']) ?></strong>
                                    <span class="el-list-subtitle"><?= !empty($certificate['issued_at']) ? date('d/m/Y', strtotime((string) $certificate['issued_at'])) : '--' ?></span>
                                </span>
                                <i class="ph ph-printer"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="el-panel">
                    <div class="el-section-head">
                        <div>
                            <h2 class="el-section-title">Historico</h2>
                            <p class="el-section-copy">Cursos finalizados recentemente.</p>
                        </div>
                        <a href="/elearning/colaborador/historico" class="el-btn el-btn-sm el-btn-outline">Ver</a>
                    </div>
                    <div class="el-list">
                        <?php if (!$history): ?>
                            <div class="el-empty">Nenhum evento registrado.</div>
                        <?php endif; ?>
                        <?php foreach (array_slice($history, 0, 4) as $item): ?>
                            <div class="el-list-item">
                                <span class="el-list-main">
                                    <strong class="el-list-title"><?= e($item['title']) ?></strong>
                                    <span class="el-list-subtitle"><?= e((string) $item['status']) ?> | <?= !empty($item['completed_at']) ? date('d/m/Y', strtotime((string) $item['completed_at'])) : '--' ?></span>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>

<script>
async function enrollCourse(courseId) {
    const formData = new FormData();
    formData.append('course_id', courseId);

    const response = await fetch('/elearning/colaborador/matricular', { method: 'POST', body: formData });
    const result = await response.json();
    if (!result.success) {
        if (typeof showELToast === 'function') showELToast(result.message || 'Nao foi possivel concluir a matricula.', 'error');
        else alert(result.message || 'Erro ao matricular');
        return;
    }

    if (typeof showELToast === 'function') showELToast(result.message || 'Matricula concluida com sucesso.', 'success');
    setTimeout(() => window.location.reload(), 700);
}
</script>
