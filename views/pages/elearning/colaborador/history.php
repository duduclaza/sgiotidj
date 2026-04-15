<?php
$courses = $data['courses'] ?? [];
?>

<div class="el-ios">
    <div class="el-page el-stack">
        <header class="el-page-head">
            <div>
                <p class="el-eyebrow">Aluno</p>
                <h1 class="el-title">Historico</h1>
                <p class="el-subtitle">Acompanhe cursos concluidos, trilhas em andamento e progresso acumulado.</p>
            </div>
            <div class="el-actions">
                <a href="/elearning/colaborador" class="el-btn el-btn-primary"><i class="ph ph-house"></i> Inicio</a>
                <a href="/elearning/colaborador/certificados" class="el-btn el-btn-secondary"><i class="ph ph-certificate"></i> Certificados</a>
            </div>
        </header>

        <section class="el-metric-grid">
            <article class="el-metric">
                <div class="el-metric-top"><span class="el-icon blue"><i class="ph ph-books"></i></span></div>
                <p class="el-metric-value"><?= count($courses) ?></p>
                <p class="el-metric-label">Cursos no historico</p>
            </article>
            <article class="el-metric">
                <div class="el-metric-top"><span class="el-icon green"><i class="ph ph-check-circle"></i></span></div>
                <p class="el-metric-value"><?= count(array_filter($courses, fn($course) => in_array((string) ($course['status'] ?? ''), ['approved', 'completed'], true))) ?></p>
                <p class="el-metric-label">Concluidos</p>
            </article>
        </section>

        <section>
            <div class="el-section-head">
                <div>
                    <h2 class="el-section-title">Linha do tempo</h2>
                    <p class="el-section-copy">Seu percurso dentro do modulo e-learning.</p>
                </div>
            </div>

            <div class="el-table-wrap">
                <table class="el-table">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Categoria</th>
                            <th>Carga horaria</th>
                            <th>Progresso</th>
                            <th>Status</th>
                            <th>Conclusao</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$courses): ?>
                            <tr>
                                <td colspan="6" style="text-align:center;color:var(--el-muted)">Seu historico sera preenchido conforme voce avancar nos cursos do modulo.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($courses as $course): ?>
                            <?php $progress = min(100, max(0, (float) ($course['progress_percent'] ?? 0))); ?>
                            <tr>
                                <td><strong><?= e($course['title']) ?></strong></td>
                                <td><?= e($course['category'] ?? 'Geral') ?></td>
                                <td><?= (int) ($course['workload_hours'] ?? 0) ?>h</td>
                                <td>
                                    <div class="el-progress">
                                        <div class="el-progress-label"><span><?= number_format($progress, 0) ?>%</span></div>
                                        <div class="el-progress-track"><div class="el-progress-fill" style="width: <?= $progress ?>%"></div></div>
                                    </div>
                                </td>
                                <td><span class="el-badge <?= in_array((string) ($course['status'] ?? ''), ['approved', 'completed'], true) ? 'green' : 'blue' ?>"><?= e((string) ($course['status'] ?? 'in_progress')) ?></span></td>
                                <td><?= !empty($course['completed_at']) ? date('d/m/Y', strtotime((string) $course['completed_at'])) : '--' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
