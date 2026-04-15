<?php
$certificates = $data['certificates'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);
?>

<div class="el-ios">
    <div class="el-page el-stack">
        <header class="el-page-head">
            <div>
                <p class="el-eyebrow">Aluno</p>
                <h1 class="el-title">Certificados</h1>
                <p class="el-subtitle">Acesse seus certificados digitais emitidos apos concluir os requisitos dos cursos.</p>
            </div>
            <div class="el-actions">
                <a href="/elearning/colaborador" class="el-btn el-btn-primary"><i class="ph ph-house"></i> Inicio</a>
                <a href="/elearning/colaborador/historico" class="el-btn el-btn-secondary"><i class="ph ph-clock-counter-clockwise"></i> Historico</a>
            </div>
        </header>

        <?php if (!$schemaReady): ?>
            <div class="el-alert">O ambiente ainda esta sem o schema do modulo. Assim que o SQL for aplicado, os certificados emitidos aparecerao aqui.</div>
        <?php endif; ?>

        <section class="el-metric-grid">
            <article class="el-metric">
                <div class="el-metric-top"><span class="el-icon orange"><i class="ph ph-certificate"></i></span></div>
                <p class="el-metric-value"><?= count($certificates) ?></p>
                <p class="el-metric-label">Certificados emitidos</p>
            </article>
            <article class="el-metric">
                <div class="el-metric-top"><span class="el-icon green"><i class="ph ph-seal-check"></i></span></div>
                <p class="el-metric-value"><?= count(array_filter($certificates, fn($item) => !empty($item['validation_code']))) ?></p>
                <p class="el-metric-label">Validaveis</p>
            </article>
        </section>

        <section>
            <div class="el-section-head">
                <div>
                    <h2 class="el-section-title">Biblioteca pessoal</h2>
                    <p class="el-section-copy">Abra, imprima ou confira o codigo de validacao.</p>
                </div>
            </div>

            <div class="el-course-grid">
                <?php if (!$certificates): ?>
                    <div class="el-empty" style="grid-column:1/-1">Nenhum certificado disponivel ate o momento.</div>
                <?php endif; ?>

                <?php foreach ($certificates as $certificate): ?>
                    <article class="el-card">
                        <div class="el-badges">
                            <span class="el-badge orange"><?= e($certificate['template_name'] ?? 'Template do curso') ?></span>
                            <span class="el-badge green"><?= number_format((float) ($certificate['score_percent'] ?? 70), 0) ?>%</span>
                        </div>
                        <h3 class="el-course-title" style="margin-top:14px"><?= e($certificate['course_title']) ?></h3>
                        <div class="el-list" style="margin-top:14px">
                            <div class="el-list-item"><span>Carga horaria</span><strong><?= (int) ($certificate['workload_hours'] ?? 0) ?>h</strong></div>
                            <div class="el-list-item"><span>Emitido em</span><strong><?= !empty($certificate['issued_at']) ? date('d/m/Y', strtotime((string) $certificate['issued_at'])) : '--' ?></strong></div>
                            <div class="el-list-item"><span>Codigo</span><strong><?= e($certificate['validation_code']) ?></strong></div>
                        </div>
                        <div class="el-actions" style="margin-top:14px">
                            <a href="/elearning/colaborador/certificados/<?= e($certificate['validation_code']) ?>" class="el-btn el-btn-primary">Abrir certificado</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>
