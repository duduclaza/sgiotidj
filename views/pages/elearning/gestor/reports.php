<?php
$courses = $data['courses'] ?? [];
$storage = $data['storage'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);

$totalEnrollments = array_sum(array_map(fn($course) => (int) ($course['enrollments_count'] ?? 0), $courses));
$totalCompleted = array_sum(array_map(fn($course) => (int) ($course['completed_count'] ?? 0), $courses));
$totalCertificates = array_sum(array_map(fn($course) => (int) ($course['certificates_count'] ?? 0), $courses));
$avgProgress = count($courses) > 0
    ? array_sum(array_map(fn($course) => (float) ($course['avg_progress'] ?? 0), $courses)) / max(1, count($courses))
    : 0;
?>

<div class="el-ios">
    <div class="el-page el-stack">
        <header class="el-page-head">
            <div>
                <p class="el-eyebrow">Professor</p>
                <h1 class="el-title">Relatorios</h1>
                <p class="el-subtitle">Indicadores de desempenho, conclusao, aprovacao e certificados por trilha.</p>
            </div>
            <div class="el-actions">
                <a href="/elearning/gestor/cursos" class="el-btn el-btn-primary"><i class="ph ph-books"></i> Cursos</a>
                <a href="/elearning/gestor/armazenamento" class="el-btn el-btn-secondary"><i class="ph ph-hard-drives"></i> Armazenamento</a>
                <a href="/elearning/gestor" class="el-btn el-btn-outline"><i class="ph ph-squares-four"></i> Painel</a>
            </div>
        </header>

        <?php if (!$schemaReady): ?>
            <div class="el-alert">Os indicadores abaixo sao estruturais. O banco de dados do e-learning ainda requer atualizacao neste ambiente para exibir dados vivos.</div>
        <?php endif; ?>

        <section class="el-metric-grid">
            <?php foreach ([
                ['label' => 'Matriculas', 'value' => $totalEnrollments, 'icon' => 'ph-users-three', 'tone' => 'blue'],
                ['label' => 'Concluidos', 'value' => $totalCompleted, 'icon' => 'ph-check-circle', 'tone' => 'green'],
                ['label' => 'Certificados', 'value' => $totalCertificates, 'icon' => 'ph-certificate', 'tone' => 'orange'],
                ['label' => 'Progresso medio', 'value' => number_format($avgProgress, 0) . '%', 'icon' => 'ph-chart-line-up', 'tone' => 'cyan'],
            ] as $card): ?>
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
                        <h2 class="el-section-title">Performance por curso</h2>
                        <p class="el-section-copy"><?= count($courses) ?> trilha(s) mapeadas.</p>
                    </div>
                </div>

                <div class="el-list">
                    <?php if (!$courses): ?>
                        <div class="el-empty">Nenhum curso ou aluno associado disponivel para leitura de indicadores.</div>
                    <?php endif; ?>

                    <?php foreach ($courses as $course): ?>
                        <?php
                        $progress = min(100, max(0, (float) ($course['avg_progress'] ?? 0)));
                        $approval = min(100, max(0, (float) ($course['approval_rate'] ?? 0)));
                        ?>
                        <article class="el-card">
                            <div class="el-section-head">
                                <div>
                                    <div class="el-badges">
                                        <span class="el-badge blue"><?= e($course['status_label'] ?? 'Rascunho') ?></span>
                                        <span class="el-badge green"><?= e($course['category'] ?? 'Geral') ?></span>
                                    </div>
                                    <h3 class="el-course-title" style="margin-top:12px"><?= e($course['title']) ?></h3>
                                </div>
                                <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/progresso" class="el-btn el-btn-sm el-btn-outline">Detalhes</a>
                            </div>

                            <div class="el-metric-grid" style="grid-template-columns:repeat(3,minmax(0,1fr));margin:14px 0">
                                <div class="el-list-item"><span>Matriculas</span><strong><?= (int) ($course['enrollments_count'] ?? 0) ?></strong></div>
                                <div class="el-list-item"><span>Concluidos</span><strong><?= (int) ($course['completed_count'] ?? 0) ?></strong></div>
                                <div class="el-list-item"><span>Certificados</span><strong><?= (int) ($course['certificates_count'] ?? 0) ?></strong></div>
                            </div>

                            <div class="el-grid" style="gap:14px">
                                <div class="el-col-6" style="grid-column:span 6">
                                    <div class="el-progress">
                                        <div class="el-progress-label"><span>Andamento</span><strong><?= number_format($progress, 0) ?>%</strong></div>
                                        <div class="el-progress-track"><div class="el-progress-fill" style="width: <?= $progress ?>%"></div></div>
                                    </div>
                                </div>
                                <div class="el-col-6" style="grid-column:span 6">
                                    <div class="el-progress">
                                        <div class="el-progress-label"><span>Aprovacao</span><strong><?= number_format($approval, 0) ?>%</strong></div>
                                        <div class="el-progress-track"><div class="el-progress-fill green" style="width: <?= $approval ?>%"></div></div>
                                    </div>
                                </div>
                            </div>

                            <?php if ((int) ($course['failed_count'] ?? 0) > 0): ?>
                                <p class="el-course-meta" style="margin-top:12px"><?= (int) ($course['failed_count'] ?? 0) ?> reprova(s) registradas para acompanhamento.</p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <aside class="el-col-4 el-stack">
                <section class="el-panel">
                    <h2 class="el-section-title">Storage</h2>
                    <p class="el-metric-value"><?= e($storage['used_human'] ?? '0 min') ?></p>
                    <p class="el-metric-label">de <?= e($storage['contracted_human'] ?? '10.000 min') ?> contratados</p>
                    <div class="el-progress" style="margin-top:16px">
                        <div class="el-progress-label">
                            <span>Uso global</span>
                            <strong><?= number_format((float) ($storage['percent_used'] ?? 0), 1, ',', '.') ?>%</strong>
                        </div>
                        <div class="el-progress-track">
                            <div class="el-progress-fill <?= ($storage['alert_level'] ?? 'healthy') === 'critical' ? 'pink' : ((($storage['alert_level'] ?? 'healthy') === 'warning') ? 'orange' : 'green') ?>" style="width: <?= min(100, max(0, (float) ($storage['percent_used'] ?? 0))) ?>%"></div>
                        </div>
                    </div>
                </section>

                <section class="el-panel">
                    <h2 class="el-section-title">Leitura rapida</h2>
                    <div class="el-list" style="margin-top:14px">
                        <div class="el-list-item"><span>Reprova acima de 30%</span><strong>revisar</strong></div>
                        <div class="el-list-item"><span>Curso sem conclusao</span><strong>acompanhar</strong></div>
                        <div class="el-list-item"><span>Storage acima de 80%</span><strong>planejar</strong></div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>
