<?php
$course = $data['course'] ?? [];
$enrollment = $data['enrollment'] ?? [];
$lessons = $data['lessons'] ?? [];
$exams = $data['exams'] ?? [];
$certificate = $data['certificate'] ?? null;
$canIssueCertificate = (bool) ($data['can_issue_certificate'] ?? false);
?>

<section class="space-y-8">
    <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-soft backdrop-blur-xl">
        <div class="grid gap-0 lg:grid-cols-[320px,1fr]">
            <div class="min-h-[260px] bg-slate-900">
                <img src="<?= e($course['cover_url'] ?? '/public/assets/logo.png') ?>" alt="<?= e($course['title'] ?? 'Curso') ?>" class="h-full w-full object-cover">
            </div>
            <div class="space-y-6 p-8">
                <div class="flex flex-wrap items-center gap-3">
                    <a href="/elearning/colaborador" class="text-sm font-semibold text-sky-200 transition hover:text-white">Dashboard</a>
                    <span class="text-slate-500">/</span>
                    <span class="text-sm font-semibold text-white"><?= e($course['title'] ?? 'Curso') ?></span>
                </div>
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full border border-sky-300/20 bg-sky-400/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-sky-100"><?= e($course['category'] ?? 'Geral') ?></span>
                        <span class="rounded-full border border-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-slate-200"><?= e($course['status_label'] ?? 'Curso') ?></span>
                    </div>
                    <h2 class="text-4xl font-black tracking-tight text-white"><?= e($course['title'] ?? 'Curso') ?></h2>
                    <p class="max-w-3xl text-base leading-relaxed text-slate-300"><?= e($course['description'] ?? 'Curso disponível no ambiente de aprendizagem do SGI.') ?></p>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/40 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Professor</p>
                        <p class="mt-3 text-lg font-black text-white"><?= e($course['teacher_name'] ?? 'A definir') ?></p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/40 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Carga horária</p>
                        <p class="mt-3 text-lg font-black text-white"><?= (int) ($course['workload_hours'] ?? 0) ?>h</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/40 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Status</p>
                        <p class="mt-3 text-lg font-black text-white"><?= e((string) ($enrollment['status'] ?? 'in_progress')) ?></p>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm text-slate-300">
                        <span>Progresso do curso</span>
                        <span class="font-black text-white"><?= number_format((float) ($enrollment['progress_percent'] ?? 0), 0) ?>%</span>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-white/10">
                        <div class="h-full rounded-full bg-[linear-gradient(90deg,_#38bdf8,_#34d399)]" style="width: <?= min(100, max(0, (float) ($enrollment['progress_percent'] ?? 0))) ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-8 xl:grid-cols-[1.45fr,0.85fr]">
        <section class="space-y-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-sky-200/70">Conteúdo</p>
                <h3 class="text-3xl font-black tracking-tight text-white">Aulas e materiais</h3>
            </div>

            <div class="space-y-4">
                <?php foreach ($lessons as $index => $lesson): ?>
                    <article class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5 shadow-soft backdrop-blur-xl">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="space-y-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 text-sm font-black text-white"><?= $index + 1 ?></span>
                                    <?php if (!empty($lesson['is_completed'])): ?>
                                        <span class="rounded-full bg-emerald-400/15 px-3 py-1 text-[11px] font-black uppercase tracking-[0.22em] text-emerald-100">Concluída</span>
                                    <?php endif; ?>
                                </div>
                                <h4 class="text-2xl font-black tracking-tight text-white"><?= e($lesson['title']) ?></h4>
                                <p class="text-sm leading-relaxed text-slate-300"><?= e($lesson['description'] ?? 'Sem descrição informada.') ?></p>
                                <div class="flex flex-wrap gap-4 text-sm text-slate-300">
                                    <span><?= (int) ($lesson['estimated_minutes'] ?? 0) ?> min</span>
                                    <span><?= count($lesson['attachments'] ?? []) ?> anexos</span>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <a href="/elearning/colaborador/materiais/<?= (int) $lesson['id'] ?>/assistir" class="rounded-full bg-white px-4 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Assistir aula</a>
                            </div>
                        </div>

                        <?php if (!empty($lesson['attachments'])): ?>
                            <div class="mt-5 grid gap-3 md:grid-cols-2">
                                <?php foreach ($lesson['attachments'] as $attachment): ?>
                                    <a href="/elearning/colaborador/anexos/<?= (int) $attachment['id'] ?>/download" class="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/30 px-4 py-3 text-sm text-slate-200 transition hover:bg-slate-900">
                                        <span class="truncate"><?= e($attachment['title'] ?: $attachment['file_name']) ?></span>
                                        <i class="ph ph-download-simple"></i>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-soft backdrop-blur-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-200/70">Avaliações</p>
                <h3 class="mt-2 text-2xl font-black tracking-tight text-white">Provas do curso</h3>
                <div class="mt-5 space-y-4">
                    <?php if (!$exams): ?>
                        <p class="text-sm text-slate-300">Este curso ainda não possui prova publicada.</p>
                    <?php endif; ?>
                    <?php foreach ($exams as $exam): ?>
                        <article class="rounded-[1.5rem] border border-white/10 bg-slate-950/35 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-lg font-black text-white"><?= e($exam['title']) ?></p>
                                    <p class="mt-2 text-sm text-slate-300">Aproveitamento mínimo: <?= number_format((float) ($exam['passing_score'] ?? 70), 0) ?>%</p>
                                </div>
                                <span class="rounded-full border border-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.22em] text-white"><?= (int) ($exam['attempts_count'] ?? 0) ?>/<?= (int) ($exam['attempts_allowed'] ?? 1) ?></span>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-3">
                                <a href="/elearning/colaborador/provas/<?= (int) $exam['id'] ?>/fazer" class="rounded-full bg-sky-500 px-4 py-2 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Fazer prova</a>
                                <?php if (!empty($exam['approved'])): ?>
                                    <span class="rounded-full bg-emerald-400/15 px-4 py-2 text-sm font-black text-emerald-100">Aprovado</span>
                                <?php elseif (!empty($exam['best_score'])): ?>
                                    <span class="rounded-full border border-white/10 px-4 py-2 text-sm font-black text-white">Melhor nota: <?= number_format((float) $exam['best_score'], 0) ?>%</span>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-soft backdrop-blur-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-200/70">Certificação</p>
                <h3 class="mt-2 text-2xl font-black tracking-tight text-white">Status do certificado</h3>
                <?php if ($certificate): ?>
                    <p class="mt-4 text-sm text-slate-300">Seu certificado já está liberado para este curso.</p>
                    <a href="/elearning/colaborador/certificados/<?= e($certificate['validation_code']) ?>" class="mt-5 inline-flex rounded-full bg-white px-4 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Abrir certificado</a>
                <?php elseif ($canIssueCertificate): ?>
                    <p class="mt-4 text-sm text-slate-300">Os critérios foram cumpridos e a emissão automática já está disponível.</p>
                    <a href="/elearning/colaborador/certificados" class="mt-5 inline-flex rounded-full bg-emerald-400 px-4 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Ir para meus certificados</a>
                <?php else: ?>
                    <p class="mt-4 text-sm text-slate-300">Finalize todas as aulas e conclua a prova obrigatória com no mínimo 70% para liberar o certificado.</p>
                <?php endif; ?>
            </section>
        </aside>
    </div>
</section>
