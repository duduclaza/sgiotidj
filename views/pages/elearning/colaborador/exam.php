<?php
$exam = $data['exam'] ?? [];
$questions = $data['questions'] ?? [];
$remainingAttempts = $data['remaining_attempts'] ?? 0;
?>

<section class="space-y-8">
    <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 p-8 shadow-soft backdrop-blur-xl">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-4">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-200/70">Avaliação online</p>
                <h2 class="text-4xl font-black tracking-tight text-white"><?= e($exam['title'] ?? 'Prova') ?></h2>
                <p class="max-w-3xl text-base leading-relaxed text-slate-300"><?= e($exam['instructions'] ?? 'Leia com atenção e selecione a melhor alternativa em cada questão.') ?></p>
            </div>
            <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/35 px-5 py-4">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Tentativas restantes</p>
                <p class="mt-3 text-3xl font-black text-white"><?= (int) $remainingAttempts ?></p>
            </div>
        </div>
    </div>

    <form id="examForm" class="space-y-5">
        <input type="hidden" name="exam_id" value="<?= (int) ($exam['id'] ?? 0) ?>">
        <?php foreach ($questions as $index => $question): ?>
            <article class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-soft backdrop-blur-xl">
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 text-sm font-black text-white"><?= $index + 1 ?></span>
                    <div class="flex-1 space-y-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Questão <?= $index + 1 ?></p>
                            <h3 class="mt-2 text-2xl font-black tracking-tight text-white"><?= e($question['statement']) ?></h3>
                        </div>
                        <div class="grid gap-3">
                            <?php foreach ($question['options'] as $option): ?>
                                <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-white/10 bg-slate-950/35 px-4 py-4 text-sm text-slate-200 transition hover:bg-slate-900">
                                    <input type="radio" name="answers[<?= (int) $question['id'] ?>]" value="<?= (int) $option['id'] ?>" class="h-5 w-5 border-white/20 bg-transparent text-sky-400 focus:ring-sky-400">
                                    <span><?= e($option['option_text']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>

        <div class="flex flex-wrap justify-end gap-3">
            <a href="/elearning/colaborador/cursos/<?= (int) ($exam['course_id'] ?? 0) ?>" class="rounded-full border border-white/15 px-5 py-3 text-sm font-black text-white transition hover:bg-white/10">Voltar ao curso</a>
            <button type="submit" class="rounded-full bg-white px-5 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Enviar prova</button>
        </div>
    </form>
</section>

<script>
document.getElementById('examForm')?.addEventListener('submit', async function (event) {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    const response = await fetch('/elearning/colaborador/provas/submeter', { method: 'POST', body: formData });
    const result = await response.json();

    if (!result.success) {
        showELToast(result.message || 'Não foi possível enviar a prova.', 'error');
        return;
    }

    showELToast(result.message || 'Prova enviada com sucesso.', 'success');
    window.location.href = '/elearning/colaborador/provas/resultado/' + result.attempt_id;
});
</script>
