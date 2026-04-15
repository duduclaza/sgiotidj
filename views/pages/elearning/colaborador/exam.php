<?php
$exam = $data['exam'] ?? [];
$questions = $data['questions'] ?? [];
$remainingAttempts = $data['remaining_attempts'] ?? 0;
?>

<div class="el-ios">
    <div class="el-page el-stack">
        <header class="el-page-head">
            <div>
                <p class="el-eyebrow">Avaliacao online</p>
                <h1 class="el-title"><?= e($exam['title'] ?? 'Prova') ?></h1>
                <p class="el-subtitle"><?= e($exam['instructions'] ?? 'Leia com atencao e selecione a melhor alternativa em cada questao.') ?></p>
            </div>
            <div class="el-actions">
                <a href="/elearning/colaborador/cursos/<?= (int) ($exam['course_id'] ?? 0) ?>" class="el-btn el-btn-outline">Voltar ao curso</a>
            </div>
        </header>

        <section class="el-metric-grid">
            <article class="el-metric">
                <div class="el-metric-top"><span class="el-icon orange"><i class="ph ph-repeat"></i></span></div>
                <p class="el-metric-value"><?= (int) $remainingAttempts ?></p>
                <p class="el-metric-label">Tentativas restantes</p>
            </article>
            <article class="el-metric">
                <div class="el-metric-top"><span class="el-icon green"><i class="ph ph-target"></i></span></div>
                <p class="el-metric-value"><?= number_format((float) ($exam['passing_score'] ?? 70), 0) ?>%</p>
                <p class="el-metric-label">Nota minima</p>
            </article>
            <article class="el-metric">
                <div class="el-metric-top"><span class="el-icon blue"><i class="ph ph-list-checks"></i></span></div>
                <p class="el-metric-value"><?= count($questions) ?></p>
                <p class="el-metric-label">Questoes</p>
            </article>
            <article class="el-metric">
                <div class="el-metric-top"><span class="el-icon cyan"><i class="ph ph-graduation-cap"></i></span></div>
                <p class="el-metric-value"><?= !empty($exam['is_mandatory']) ? 'Sim' : 'Nao' ?></p>
                <p class="el-metric-label">Obrigatoria</p>
            </article>
        </section>

        <form id="examForm" class="el-stack">
            <input type="hidden" name="exam_id" value="<?= (int) ($exam['id'] ?? 0) ?>">
            <?php foreach ($questions as $index => $question): ?>
                <article class="el-panel">
                    <div class="el-section-head">
                        <div>
                            <p class="el-eyebrow">Questao <?= $index + 1 ?></p>
                            <h2 class="el-section-title"><?= e($question['statement']) ?></h2>
                        </div>
                        <span class="el-icon"><?= $index + 1 ?></span>
                    </div>
                    <div class="el-list">
                        <?php foreach ($question['options'] as $option): ?>
                            <label class="el-answer">
                                <input type="radio" name="answers[<?= (int) $question['id'] ?>]" value="<?= (int) $option['id'] ?>">
                                <span><?= e($option['option_text']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endforeach; ?>

            <div class="el-actions" style="justify-content:flex-end">
                <a href="/elearning/colaborador/cursos/<?= (int) ($exam['course_id'] ?? 0) ?>" class="el-btn el-btn-outline">Voltar ao curso</a>
                <button type="submit" class="el-btn el-btn-primary">Enviar prova</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('examForm')?.addEventListener('submit', async function (event) {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    const response = await fetch('/elearning/colaborador/provas/submeter', { method: 'POST', body: formData });
    const result = await response.json();

    if (!result.success) {
        if (typeof showELToast === 'function') showELToast(result.message || 'Nao foi possivel enviar a prova.', 'error');
        else alert(result.message || 'Nao foi possivel enviar a prova.');
        return;
    }

    if (typeof showELToast === 'function') showELToast(result.message || 'Prova enviada com sucesso.', 'success');
    window.location.href = '/elearning/colaborador/provas/resultado/' + result.attempt_id;
});
</script>
