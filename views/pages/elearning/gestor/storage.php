<?php
$summary = $data['summary'] ?? [];
$courses = $data['courses'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);
$percent = min(100, max(0, (float) ($summary['percent_used'] ?? 0)));
$tone = ($summary['alert_level'] ?? 'healthy') === 'critical'
    ? 'pink'
    : ((($summary['alert_level'] ?? 'healthy') === 'warning') ? 'orange' : 'green');
$summaryRows = [
    ['label' => 'Contratado', 'value' => $summary['contracted_human'] ?? '10.000 min'],
    ['label' => 'Em uso', 'value' => $summary['used_human'] ?? '0 min'],
    ['label' => 'Disponivel', 'value' => $summary['available_human'] ?? '10.000 min'],
    ['label' => 'Consumo', 'value' => number_format((float) ($summary['percent_used'] ?? 0), 2, ',', '.') . '%'],
];
?>

<div class="el-ios">
    <div class="el-page el-stack">
        <header class="el-page-head">
            <div>
                <p class="el-eyebrow">Professor</p>
                <h1 class="el-title">Armazenamento</h1>
                <p class="el-subtitle">Controle a capacidade de video do SGI Stream e veja como os minutos estao distribuidos por curso.</p>
            </div>
            <div class="el-actions">
                <a href="/elearning/gestor/cursos" class="el-btn el-btn-primary"><i class="ph ph-books"></i> Cursos</a>
                <a href="/elearning/gestor/relatorios" class="el-btn el-btn-secondary"><i class="ph ph-chart-line-up"></i> Relatorios</a>
                <a href="/elearning/gestor" class="el-btn el-btn-outline"><i class="ph ph-squares-four"></i> Painel</a>
            </div>
        </header>

        <?php if (!$schemaReady): ?>
            <div class="el-alert">O painel ja esta pronto, mas o schema do modulo ainda nao foi aplicado neste ambiente.</div>
        <?php endif; ?>

        <section class="el-hero">
            <div class="el-hero-inner">
                <div>
                    <p class="el-eyebrow">SGI Stream</p>
                    <h2 class="el-title">Capacidade clara antes de novos envios.</h2>
                    <p class="el-subtitle">Acompanhe o consumo global e identifique quais cursos concentram mais minutos de video.</p>
                    <?php if (!empty($summary['is_upload_blocked'])): ?>
                        <div class="el-alert" style="margin-top:16px;background:var(--el-pink-soft);color:#9f1239">
                            O limite foi atingido. Novos uploads ficam bloqueados ate a liberacao de mais capacidade.
                        </div>
                    <?php elseif (($summary['alert_level'] ?? 'healthy') === 'warning'): ?>
                        <div class="el-alert" style="margin-top:16px">
                            Alerta de 80% atingido. Planeje a ampliacao antes de novos envios em massa.
                        </div>
                    <?php endif; ?>
                </div>
                <aside class="el-hero-panel">
                    <span class="el-icon <?= e($tone) ?>"><i class="ph ph-hard-drives"></i></span>
                    <p class="el-metric-value"><?= e($summary['used_human'] ?? '0 min') ?></p>
                    <p class="el-metric-label">consumidos de <?= e($summary['contracted_human'] ?? '10.000 min') ?></p>
                    <div class="el-progress" style="margin-top:16px">
                        <div class="el-progress-label">
                            <span>Uso atual</span>
                            <strong><?= number_format($percent, 2, ',', '.') ?>%</strong>
                        </div>
                        <div class="el-progress-track"><div class="el-progress-fill <?= e($tone) ?>" style="width: <?= $percent ?>%"></div></div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="el-metric-grid">
            <?php foreach ($summaryRows as $index => $row): ?>
                <article class="el-metric">
                    <div class="el-metric-top">
                        <span class="el-icon <?= ['blue', 'orange', 'green', 'cyan'][$index] ?? 'blue' ?>"><i class="ph <?= ['ph-stack', 'ph-play', 'ph-battery-high', 'ph-gauge'][$index] ?? 'ph-info' ?>"></i></span>
                    </div>
                    <p class="el-metric-value"><?= e($row['value']) ?></p>
                    <p class="el-metric-label"><?= e($row['label']) ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <div class="el-grid">
            <aside class="el-col-4 el-stack">
                <section class="el-panel">
                    <h2 class="el-section-title">Politica do modulo</h2>
                    <div class="el-list" style="margin-top:14px">
                        <div class="el-list-item"><span>Recalculo</span><strong>automatico</strong></div>
                        <div class="el-list-item"><span>Conta no limite</span><strong>videos</strong></div>
                        <div class="el-list-item"><span>Bloqueio</span><strong>100%</strong></div>
                    </div>
                    <p class="el-section-copy" style="margin-top:14px">O consumo e recalculado sempre que um video e enviado, removido ou substituido.</p>
                </section>
            </aside>

            <section class="el-col-8">
                <div class="el-section-head">
                    <div>
                        <h2 class="el-section-title">Distribuicao por curso</h2>
                        <p class="el-section-copy"><?= count($courses) ?> curso(s) com videos armazenados.</p>
                    </div>
                </div>

                <div class="el-list">
                    <?php if (!$courses): ?>
                        <div class="el-empty">Nenhum video registrado ainda.</div>
                    <?php endif; ?>

                    <?php foreach ($courses as $course): ?>
                        <?php
                        $width = (int) ($summary['used_seconds'] ?? 0) > 0
                            ? ((int) ($course['used_seconds'] ?? 0) / max(1, (int) ($summary['used_seconds'] ?? 1))) * 100
                            : 0;
                        $width = min(100, max(0, $width));
                        ?>
                        <article class="el-list-item">
                            <div class="el-list-main">
                                <h3 class="el-list-title"><?= e($course['title']) ?></h3>
                                <p class="el-list-subtitle"><?= (int) ($course['videos_count'] ?? 0) ?> video(s) armazenado(s)</p>
                                <div class="el-progress" style="margin-top:12px">
                                    <div class="el-progress-track"><div class="el-progress-fill green" style="width: <?= $width ?>%"></div></div>
                                </div>
                            </div>
                            <div style="text-align:right">
                                <strong><?= e($course['used_human'] ?? '0 min') ?></strong>
                                <div style="margin-top:8px">
                                    <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/aulas" class="el-btn el-btn-sm el-btn-outline">Abrir</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>
</div>
