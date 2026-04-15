<?php
$course = $data['course'] ?? [];
$enrollment = $data['enrollment'] ?? [];
$lessons = $data['lessons'] ?? [];
$exams = $data['exams'] ?? [];
$certificate = $data['certificate'] ?? null;
$canIssueCertificate = (bool) ($data['can_issue_certificate'] ?? false);
$firstLessonId = (int) ($lessons[0]['id'] ?? 0);
$progress = min(100, max(0, (float) ($enrollment['progress_percent'] ?? 0)));
?>

<div class="el-ios">
    <div class="el-page el-stack">
        <header class="el-page-head">
            <div>
                <p class="el-eyebrow"><a href="/elearning/colaborador" style="color:inherit">Aluno</a> / Curso</p>
                <h1 class="el-title"><?= e($course['title'] ?? 'Curso') ?></h1>
                <p class="el-subtitle"><?= e($course['description'] ?? 'Curso disponivel no ambiente de aprendizagem do SGI.') ?></p>
            </div>
            <div class="el-actions">
                <a href="/elearning/colaborador/cursos/<?= (int) ($course['id'] ?? 0) ?>/continuar" class="el-btn el-btn-primary"><i class="ph ph-play-circle"></i> Continuar</a>
                <?php if ($firstLessonId > 0): ?>
                    <a href="/elearning/colaborador/materiais/<?= $firstLessonId ?>/assistir" class="el-btn el-btn-secondary">Primeira aula</a>
                <?php endif; ?>
                <a href="/elearning/colaborador" class="el-btn el-btn-outline">Voltar</a>
            </div>
        </header>

        <section class="el-hero">
            <div class="el-hero-inner">
                <div class="el-hero-visual">
                    <img src="<?= e($course['cover_url'] ?? '/assets/logo.png') ?>" alt="<?= e($course['title'] ?? 'Curso') ?>">
                </div>
                <aside class="el-hero-panel">
                    <div class="el-badges">
                        <span class="el-badge blue"><?= e($course['category'] ?? 'Geral') ?></span>
                        <span class="el-badge green"><?= e((string) ($enrollment['status'] ?? 'in_progress')) ?></span>
                    </div>
                    <div class="el-list">
                        <div class="el-list-item"><span>Professor</span><strong><?= e($course['teacher_name'] ?? 'A definir') ?></strong></div>
                        <div class="el-list-item"><span>Carga horaria</span><strong><?= (int) ($course['workload_hours'] ?? 0) ?>h</strong></div>
                        <div class="el-list-item"><span>Aulas</span><strong><?= count($lessons) ?></strong></div>
                    </div>
                    <div class="el-progress">
                        <div class="el-progress-label"><span>Progresso do curso</span><strong><?= number_format($progress, 0) ?>%</strong></div>
                        <div class="el-progress-track"><div class="el-progress-fill" style="width: <?= $progress ?>%"></div></div>
                    </div>
                </aside>
            </div>
        </section>

        <div class="el-grid">
            <section class="el-col-8">
                <div class="el-section-head">
                    <div>
                        <h2 class="el-section-title">Aulas e materiais</h2>
                        <p class="el-section-copy">Siga a ordem da trilha e use os anexos como apoio.</p>
                    </div>
                </div>

                <div class="el-list">
                    <?php if (!$lessons): ?>
                        <div class="el-empty">Este curso ainda nao possui aulas publicadas.</div>
                    <?php endif; ?>

                    <?php foreach ($lessons as $index => $lesson): ?>
                        <article class="el-list-item">
                            <span class="el-icon <?= !empty($lesson['is_completed']) ? 'green' : 'blue' ?>">
                                <?= $index + 1 ?>
                            </span>
                            <div class="el-list-main">
                                <div class="el-badges">
                                    <?php if (!empty($lesson['is_completed'])): ?>
                                        <span class="el-badge green">Concluida</span>
                                    <?php endif; ?>
                                    <span class="el-badge"><?= (int) ($lesson['estimated_minutes'] ?? 0) ?> min</span>
                                    <span class="el-badge"><?= count($lesson['attachments'] ?? []) ?> anexo(s)</span>
                                </div>
                                <h3 class="el-list-title" style="margin-top:8px"><?= e($lesson['title']) ?></h3>
                                <p class="el-list-subtitle"><?= e($lesson['description'] ?? 'Sem descricao informada.') ?></p>
                            </div>
                            <a href="/elearning/colaborador/materiais/<?= (int) $lesson['id'] ?>/assistir" class="el-btn el-btn-primary">Assistir</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <aside class="el-col-4 el-stack">
                <section class="el-panel">
                    <h2 class="el-section-title">Provas</h2>
                    <div class="el-list" style="margin-top:14px">
                        <?php if (!$exams): ?>
                            <div class="el-empty">Este curso ainda nao possui prova publicada.</div>
                        <?php endif; ?>
                        <?php foreach ($exams as $exam): ?>
                            <article class="el-list-item">
                                <div class="el-list-main">
                                    <h3 class="el-list-title"><?= e($exam['title']) ?></h3>
                                    <p class="el-list-subtitle">Minimo: <?= number_format((float) ($exam['passing_score'] ?? 70), 0) ?>% | Tentativas: <?= (int) ($exam['attempts_count'] ?? 0) ?>/<?= (int) ($exam['attempts_allowed'] ?? 1) ?></p>
                                    <?php if (!empty($exam['approved'])): ?>
                                        <span class="el-badge green">Aprovado</span>
                                    <?php elseif (!empty($exam['best_score'])): ?>
                                        <span class="el-badge orange">Melhor nota: <?= number_format((float) $exam['best_score'], 0) ?>%</span>
                                    <?php endif; ?>
                                </div>
                                <a href="/elearning/colaborador/provas/<?= (int) $exam['id'] ?>/fazer" class="el-btn el-btn-sm el-btn-primary">Fazer</a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="el-panel">
                    <h2 class="el-section-title">Certificado</h2>
                    <?php if ($certificate): ?>
                        <p class="el-section-copy">Seu certificado ja esta liberado para este curso.</p>
                        <div class="el-actions" style="margin-top:14px">
                            <a href="/elearning/colaborador/certificados/<?= e($certificate['validation_code']) ?>" class="el-btn el-btn-primary">Abrir certificado</a>
                        </div>
                    <?php elseif ($canIssueCertificate): ?>
                        <p class="el-section-copy">Os criterios foram cumpridos e a emissao automatica ja esta disponivel.</p>
                        <div class="el-actions" style="margin-top:14px">
                            <a href="/elearning/colaborador/certificados" class="el-btn el-btn-primary">Ir para certificados</a>
                        </div>
                    <?php else: ?>
                        <p class="el-section-copy">Finalize todas as aulas e conclua a prova obrigatoria com no minimo 70% para liberar o certificado.</p>
                    <?php endif; ?>
                </section>
            </aside>
        </div>
    </div>
</div>
