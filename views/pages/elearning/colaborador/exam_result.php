<?php
$attempt = $data['attempt'] ?? [];
$answers = $data['answers'] ?? [];
$certificate = $data['certificate'] ?? null;
$approved = ($attempt['status'] ?? '') === 'approved';
$score = min(100, max(0, (float) ($attempt['score_percent'] ?? 0)));
?>

<div class="el-ios">
    <div class="el-page el-stack">
        <header class="el-page-head">
            <div>
                <p class="el-eyebrow">Resultado da avaliacao</p>
                <h1 class="el-title"><?= $approved ? 'Aprovado' : 'Reprovado' ?></h1>
                <p class="el-subtitle">Voce obteve <?= number_format($score, 0) ?>% na prova <?= e($attempt['exam_title'] ?? 'Avaliacao') ?>.</p>
            </div>
            <div class="el-actions">
                <a href="/elearning/colaborador/cursos/<?= (int) ($attempt['course_id'] ?? 0) ?>" class="el-btn el-btn-primary">Voltar ao curso</a>
                <a href="/elearning/colaborador/certificados" class="el-btn el-btn-secondary">Certificados</a>
            </div>
        </header>

        <section class="el-metric-grid">
            <article class="el-metric">
                <div class="el-metric-top"><span class="el-icon <?= $approved ? 'green' : 'pink' ?>"><i class="ph <?= $approved ? 'ph-check-circle' : 'ph-x-circle' ?>"></i></span></div>
                <p class="el-metric-value"><?= number_format($score, 0) ?>%</p>
                <p class="el-metric-label">Sua nota</p>
            </article>
            <article class="el-metric">
                <div class="el-metric-top"><span class="el-icon orange"><i class="ph ph-target"></i></span></div>
                <p class="el-metric-value"><?= number_format((float) ($attempt['passing_score'] ?? 70), 0) ?>%</p>
                <p class="el-metric-label">Nota minima</p>
            </article>
            <article class="el-metric">
                <div class="el-metric-top"><span class="el-icon blue"><i class="ph ph-list-checks"></i></span></div>
                <p class="el-metric-value"><?= count($answers) ?></p>
                <p class="el-metric-label">Questoes revisadas</p>
            </article>
            <article class="el-metric">
                <div class="el-metric-top"><span class="el-icon cyan"><i class="ph ph-certificate"></i></span></div>
                <p class="el-metric-value"><?= $approved && $certificate ? 'Sim' : 'Nao' ?></p>
                <p class="el-metric-label">Certificado</p>
            </article>
        </section>

        <div class="el-grid">
            <section class="el-col-8">
                <div class="el-section-head">
                    <div>
                        <h2 class="el-section-title">Revisao da prova</h2>
                        <p class="el-section-copy">Confira suas respostas e identifique pontos de melhoria.</p>
                    </div>
                </div>
                <div class="el-list">
                    <?php foreach ($answers as $index => $answer): ?>
                        <article class="el-list-item">
                            <span class="el-icon <?= !empty($answer['is_correct']) ? 'green' : 'pink' ?>">
                                <i class="ph <?= !empty($answer['is_correct']) ? 'ph-check' : 'ph-x' ?>"></i>
                            </span>
                            <div class="el-list-main">
                                <p class="el-eyebrow">Questao <?= $index + 1 ?></p>
                                <h3 class="el-list-title"><?= e($answer['statement']) ?></h3>
                                <p class="el-list-subtitle">Resposta enviada: <?= e($answer['option_text'] ?? 'Sem resposta') ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <aside class="el-col-4">
                <section class="el-panel">
                    <h2 class="el-section-title"><?= $approved ? 'Proximo passo' : 'Nova tentativa' ?></h2>
                    <?php if ($approved && $certificate): ?>
                        <p class="el-section-copy">Seu certificado ja esta disponivel para este curso.</p>
                        <div class="el-actions" style="margin-top:14px">
                            <a href="/elearning/colaborador/certificados/<?= e($certificate['validation_code']) ?>" class="el-btn el-btn-primary">Abrir certificado</a>
                        </div>
                    <?php else: ?>
                        <p class="el-section-copy">Revise o conteudo do curso e tente novamente quando estiver pronto, conforme a configuracao definida pelo professor.</p>
                    <?php endif; ?>
                </section>
            </aside>
        </div>
    </div>
</div>
