<?php
$data = $data ?? [];
$stats = $data['stats'] ?? [];
$courses = $data['courses'] ?? [];
$storage = $data['storage'] ?? [];
$templates = $data['templates'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);

$statCards = [
    ['label' => 'Cursos', 'value' => $stats['total_courses'] ?? 0, 'hint' => ($stats['published_courses'] ?? 0) . ' publicados', 'icon' => 'ph-books', 'tone' => 'blue'],
    ['label' => 'Aulas', 'value' => $stats['total_lessons'] ?? 0, 'hint' => 'conteudos ativos', 'icon' => 'ph-play-circle', 'tone' => 'cyan'],
    ['label' => 'Alunos', 'value' => $stats['total_students'] ?? 0, 'hint' => 'matriculas unicas', 'icon' => 'ph-users-three', 'tone' => 'green'],
    ['label' => 'Aprovacao', 'value' => number_format((float) ($stats['approval_rate'] ?? 0), 0) . '%', 'hint' => 'nas provas enviadas', 'icon' => 'ph-seal-check', 'tone' => 'orange'],
];

$storagePercent = min(100, max(0, (float) ($storage['percent_used'] ?? 0)));
$storageTone = $storagePercent >= 90 ? 'pink' : ($storagePercent >= 80 ? 'orange' : 'green');
?>

<div class="el-ios">
    <div class="el-page el-stack">
        <header class="el-page-head">
            <div>
                <p class="el-eyebrow">Professor</p>
                <h1 class="el-title">Painel e-learning</h1>
                <p class="el-subtitle">Acompanhe cursos, alunos, provas e capacidade de video em um painel leve, direto e facil de operar.</p>
            </div>
            <div class="el-actions">
                <a href="/elearning/gestor/cursos" class="el-btn el-btn-primary"><i class="ph ph-books"></i> Cursos</a>
                <a href="/elearning/gestor/relatorios" class="el-btn el-btn-secondary"><i class="ph ph-chart-line-up"></i> Relatorios</a>
                <a href="/inicio" class="el-btn el-btn-outline"><i class="ph ph-house"></i> Inicio</a>
            </div>
        </header>

        <?php if (!$schemaReady): ?>
            <div class="el-alert">
                O schema MariaDB do e-learning ainda nao foi aplicado neste ambiente. O desenho ja esta pronto, mas algumas operacoes dependem da base.
            </div>
        <?php endif; ?>

        <section class="el-hero">
            <div class="el-hero-inner">
                <div>
                    <p class="el-eyebrow">Educacao corporativa</p>
                    <h2 class="el-title">Tudo que precisa de atencao, em um unico lugar.</h2>
                    <p class="el-subtitle">Use o painel para priorizar trilhas, revisar engajamento, abrir relatorios e conferir se ha espaco para novos videos.</p>
                    <div class="el-actions" style="margin-top:18px">
                        <?php if ($canEdit ?? true): ?>
                            <a href="/elearning/gestor/cursos" class="el-btn el-btn-primary"><i class="ph ph-plus-circle"></i> Criar curso</a>
                        <?php endif; ?>
                        <a href="/elearning/gestor/diploma/config" class="el-btn el-btn-secondary"><i class="ph ph-certificate"></i> Certificados</a>
                    </div>
                </div>
                <aside class="el-hero-panel">
                    <div class="el-metric-top">
                        <span class="el-icon <?= e($storageTone) ?>"><i class="ph ph-hard-drives"></i></span>
                        <span class="el-badge <?= e($storageTone) ?>">SGI Stream</span>
                    </div>
                    <p class="el-metric-value"><?= e($storage['used_human'] ?? '0 min') ?></p>
                    <p class="el-metric-label">de <?= e($storage['contracted_human'] ?? '10.000 min') ?> contratados</p>
                    <div class="el-progress" style="margin-top:16px">
                        <div class="el-progress-label">
                            <span>Consumo</span>
                            <strong><?= number_format($storagePercent, 1, ',', '.') ?>%</strong>
                        </div>
                        <div class="el-progress-track">
                            <div class="el-progress-fill <?= e($storageTone) ?>" style="width: <?= $storagePercent ?>%"></div>
                        </div>
                    </div>
                    <p class="el-course-meta"><?= e($storage['available_human'] ?? '0 min') ?> disponiveis para novas aulas.</p>
                </aside>
            </div>
        </section>

        <section class="el-metric-grid">
            <?php foreach ($statCards as $card): ?>
                <article class="el-metric">
                    <div class="el-metric-top">
                        <span class="el-icon <?= e($card['tone']) ?>"><i class="ph <?= e($card['icon']) ?>"></i></span>
                        <span class="el-badge"><?= e($card['hint']) ?></span>
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
                        <h2 class="el-section-title">Cursos recentes</h2>
                        <p class="el-section-copy">Trilhas que receberam atualizacoes ou atividade recente.</p>
                    </div>
                    <a href="/elearning/gestor/cursos" class="el-btn el-btn-sm el-btn-outline">Ver todos</a>
                </div>

                <div class="el-list">
                    <?php if (!$courses): ?>
                        <div class="el-empty">Nenhum curso cadastrado ainda.</div>
                    <?php endif; ?>

                    <?php foreach (array_slice($courses, 0, 6) as $course): ?>
                        <?php $progress = min(100, max(0, (float) ($course['avg_progress'] ?? 0))); ?>
                        <article class="el-course horizontal">
                            <div class="el-cover">
                                <img src="<?= e($course['cover_url']) ?>" alt="<?= e($course['title']) ?>">
                            </div>
                            <div class="el-course-body">
                                <div class="el-badges">
                                    <span class="el-badge blue"><?= e($course['status_label'] ?? 'Rascunho') ?></span>
                                    <span class="el-badge green"><?= e($course['category'] ?? 'Geral') ?></span>
                                </div>
                                <div>
                                    <h3 class="el-course-title"><?= e($course['title']) ?></h3>
                                    <p class="el-course-meta">Professor: <?= e($course['teacher_name'] ?? 'A definir') ?></p>
                                </div>
                                <div class="el-progress">
                                    <div class="el-progress-label">
                                        <span><?= (int) ($course['lessons_count'] ?? 0) ?> aulas | <?= (int) ($course['enrollments_count'] ?? 0) ?> alunos</span>
                                        <strong><?= number_format($progress, 0) ?>%</strong>
                                    </div>
                                    <div class="el-progress-track">
                                        <div class="el-progress-fill" style="width: <?= $progress ?>%"></div>
                                    </div>
                                </div>
                                <div class="el-course-actions">
                                    <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/aulas" class="el-btn el-btn-sm el-btn-primary">Aulas</a>
                                    <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/provas" class="el-btn el-btn-sm el-btn-secondary">Provas</a>
                                    <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/matriculas" class="el-btn el-btn-sm el-btn-secondary">Alunos</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <aside class="el-col-4 el-stack">
                <section class="el-panel">
                    <div class="el-section-head">
                        <div>
                            <h2 class="el-section-title">Atalhos</h2>
                            <p class="el-section-copy">Acoes frequentes do professor.</p>
                        </div>
                    </div>
                    <div class="el-list">
                        <a href="/elearning/gestor/cursos" class="el-list-item">
                            <span class="el-list-main">
                                <strong class="el-list-title">Gerenciar cursos</strong>
                                <span class="el-list-subtitle">Criar trilhas, capas e status.</span>
                            </span>
                            <i class="ph ph-caret-right"></i>
                        </a>
                        <a href="/elearning/gestor/diploma/config" class="el-list-item">
                            <span class="el-list-main">
                                <strong class="el-list-title">Biblioteca de certificados</strong>
                                <span class="el-list-subtitle"><?= count($templates) ?> modelo(s) disponiveis.</span>
                            </span>
                            <i class="ph ph-caret-right"></i>
                        </a>
                        <a href="/elearning/gestor/armazenamento" class="el-list-item">
                            <span class="el-list-main">
                                <strong class="el-list-title">Armazenamento</strong>
                                <span class="el-list-subtitle">Minutos de video e alertas.</span>
                            </span>
                            <i class="ph ph-caret-right"></i>
                        </a>
                    </div>
                </section>

                <section class="el-panel">
                    <h2 class="el-section-title">Regras do modulo</h2>
                    <div class="el-list" style="margin-top:14px">
                        <div class="el-list-item"><span>1 video MP4 por aula</span><strong>80 MB</strong></div>
                        <div class="el-list-item"><span>Anexos por aula</span><strong>20 MB</strong></div>
                        <div class="el-list-item"><span>Nota minima</span><strong>70%</strong></div>
                        <div class="el-list-item"><span>Capacidade global</span><strong>10.000 min</strong></div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>
