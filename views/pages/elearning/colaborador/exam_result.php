<?php
$attempt = $data['attempt'] ?? [];
$answers = $data['answers'] ?? [];
$certificate = $data['certificate'] ?? null;
$approved = ($attempt['status'] ?? '') === 'approved';
?>

<section class="space-y-8">
    <div class="overflow-hidden rounded-[2rem] border border-white/10 p-8 shadow-soft backdrop-blur-xl <?= $approved ? 'bg-[linear-gradient(135deg,_rgba(16,185,129,0.24),_rgba(15,23,42,0.7))]' : 'bg-[linear-gradient(135deg,_rgba(244,63,94,0.24),_rgba(15,23,42,0.7))]' ?>">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-4">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-white/70">Resultado da avaliação</p>
                <h2 class="text-4xl font-black tracking-tight text-white"><?= $approved ? 'Aprovado' : 'Reprovado' ?></h2>
                <p class="max-w-2xl text-base leading-relaxed text-slate-100/80">Você obteve <strong class="text-white"><?= number_format((float) ($attempt['score_percent'] ?? 0), 0) ?>%</strong> na prova <strong class="text-white"><?= e($attempt['exam_title'] ?? 'Avaliação') ?></strong>.</p>
            </div>
            <div class="rounded-[1.5rem] border border-white/20 bg-slate-950/30 px-5 py-4">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-300">Nota mínima</p>
                <p class="mt-3 text-3xl font-black text-white"><?= number_format((float) ($attempt['passing_score'] ?? 70), 0) ?>%</p>
            </div>
        </div>
    </div>

    <div class="grid gap-8 xl:grid-cols-[1.45fr,0.85fr]">
        <section class="space-y-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-sky-200/70">Questões</p>
                <h3 class="text-3xl font-black tracking-tight text-white">Revisão da prova</h3>
            </div>
            <?php foreach ($answers as $index => $answer): ?>
                <article class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5 shadow-soft backdrop-blur-xl">
                    <div class="flex items-start gap-4">
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl <?= !empty($answer['is_correct']) ? 'bg-emerald-400/15 text-emerald-100' : 'bg-rose-400/15 text-rose-100' ?>">
                            <i class="ph-fill <?= !empty($answer['is_correct']) ? 'ph-check-circle' : 'ph-x-circle' ?> text-xl"></i>
                        </span>
                        <div class="space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Questão <?= $index + 1 ?></p>
                            <h4 class="text-xl font-black tracking-tight text-white"><?= e($answer['statement']) ?></h4>
                            <p class="text-sm text-slate-300">Resposta enviada: <strong class="text-white"><?= e($answer['option_text'] ?? 'Sem resposta') ?></strong></p>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <aside class="space-y-6">
            <section class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-soft backdrop-blur-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-200/70">Próximo passo</p>
                <h3 class="mt-2 text-2xl font-black tracking-tight text-white"><?= $approved ? 'Certificado liberado' : 'Nova tentativa' ?></h3>
                <?php if ($approved && $certificate): ?>
                    <p class="mt-4 text-sm text-slate-300">Seu certificado já está disponível para este curso.</p>
                    <a href="/elearning/colaborador/certificados/<?= e($certificate['validation_code']) ?>" class="mt-5 inline-flex rounded-full bg-white px-5 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Abrir certificado</a>
                <?php else: ?>
                    <p class="mt-4 text-sm text-slate-300">Revise o conteúdo do curso e tente novamente quando estiver pronto, conforme a configuração definida pelo professor.</p>
                <?php endif; ?>
            </section>

            <section class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-soft backdrop-blur-xl">
                <div class="flex flex-wrap gap-3">
                    <a href="/elearning/colaborador/cursos/<?= (int) ($attempt['course_id'] ?? 0) ?>" class="rounded-full border border-white/20 px-4 py-3 text-sm font-black text-white transition hover:bg-white/10">Voltar ao curso</a>
                    <a href="/elearning/colaborador/certificados" class="rounded-full bg-sky-500 px-4 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Meus certificados</a>
                </div>
            </section>
        </aside>
    </div>
</section>
