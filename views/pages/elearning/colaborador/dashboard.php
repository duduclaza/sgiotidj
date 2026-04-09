<?php
$stats = $data['stats'] ?? [];
$enrolledCourses = $data['enrolled_courses'] ?? [];
$availableCourses = $data['available_courses'] ?? [];
$nextLesson = $data['next_lesson'] ?? null;
$certificates = $data['certificates'] ?? [];
$history = $data['history'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);
?>

<section class="space-y-8">
    <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-[linear-gradient(135deg,_rgba(59,130,246,0.24),_rgba(14,165,233,0.14)_45%,_rgba(16,185,129,0.22))] p-8 shadow-soft">
        <div class="grid gap-8 lg:grid-cols-[1.4fr,0.9fr]">
            <div class="space-y-5">
                <p class="text-xs font-semibold uppercase tracking-[0.4em] text-sky-100/70">Experiência de aprendizagem</p>
                <h2 class="max-w-2xl text-4xl font-black tracking-tight text-white sm:text-5xl">Seu campus digital dentro do SGI, com foco em clareza, ritmo e conclusão.</h2>
                <p class="max-w-2xl text-base leading-relaxed text-slate-100/80">Acompanhe seus cursos em andamento, retome a próxima aula sem fricção e emita seus certificados assim que atingir os critérios do curso.</p>
                <div class="flex flex-wrap gap-3">
                    <?php if ($nextLesson): ?>
                        <a href="/elearning/colaborador/materiais/<?= (int) $nextLesson['id'] ?>/assistir" class="rounded-full bg-white px-5 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Continuar próxima aula</a>
                    <?php endif; ?>
                    <a href="/elearning/colaborador/historico" class="rounded-full border border-white/20 px-5 py-3 text-sm font-black text-white transition hover:bg-white/10">Ver histórico</a>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <?php foreach ([
                    ['label' => 'Cursos em andamento', 'value' => $stats['in_progress'] ?? 0, 'icon' => 'ph-book-open-text', 'tone' => 'text-sky-100'],
                    ['label' => 'Cursos concluídos', 'value' => $stats['completed'] ?? 0, 'icon' => 'ph-check-circle', 'tone' => 'text-emerald-100'],
                    ['label' => 'Provas pendentes', 'value' => $stats['pending_exams'] ?? 0, 'icon' => 'ph-clipboard-text', 'tone' => 'text-amber-100'],
                    ['label' => 'Progresso geral', 'value' => ($stats['overall_progress'] ?? 0) . '%', 'icon' => 'ph-chart-line-up', 'tone' => 'text-violet-100'],
                ] as $card): ?>
                    <article class="rounded-[1.75rem] border border-white/10 bg-slate-950/30 p-5 backdrop-blur-xl">
                        <div class="flex items-center justify-between">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 <?= $card['tone'] ?>">
                                <i class="ph <?= $card['icon'] ?> text-xl"></i>
                            </div>
                            <span class="text-xs font-semibold uppercase tracking-[0.25em] text-white/50">Aluno</span>
                        </div>
                        <p class="mt-5 text-3xl font-black text-white"><?= e((string) $card['value']) ?></p>
                        <p class="mt-2 text-sm text-slate-200/70"><?= e($card['label']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php if (!$schemaReady): ?>
        <div class="rounded-[1.75rem] border border-amber-400/30 bg-amber-500/10 px-5 py-4 text-sm leading-relaxed text-amber-50">
            O módulo está pronto visualmente, mas o schema MariaDB ainda não foi aplicado neste ambiente. Execute o SQL do módulo para habilitar dados reais, uploads e persistência.
        </div>
    <?php endif; ?>

    <div class="grid gap-8 xl:grid-cols-[1.4fr,0.9fr]">
        <div class="space-y-8">
            <section class="space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-sky-200/70">Meus cursos</p>
                        <h3 class="text-2xl font-black tracking-tight text-white">Cursos matriculados</h3>
                    </div>
                    <span class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-bold uppercase tracking-[0.25em] text-white/70"><?= count($enrolledCourses) ?> ativos</span>
                </div>

                <div class="grid gap-4">
                    <?php if (!$enrolledCourses): ?>
                        <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-8 text-center text-slate-300">Nenhum curso matriculado ainda.</div>
                    <?php endif; ?>

                    <?php foreach ($enrolledCourses as $course): ?>
                        <article class="overflow-hidden rounded-[1.75rem] border border-white/10 bg-white/5 shadow-soft backdrop-blur-xl">
                            <div class="grid gap-0 lg:grid-cols-[220px,1fr]">
                                <div class="min-h-[180px] bg-slate-900">
                                    <img src="<?= e($course['cover_url']) ?>" alt="<?= e($course['title']) ?>" class="h-full w-full object-cover">
                                </div>
                                <div class="flex flex-col justify-between gap-5 p-6">
                                    <div class="space-y-3">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <span class="rounded-full border border-sky-300/20 bg-sky-400/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-sky-100"><?= e($course['category'] ?? 'Geral') ?></span>
                                            <span class="rounded-full border border-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-slate-200"><?= e((string) ($course['enrollment_status'] ?? 'in_progress')) ?></span>
                                        </div>
                                        <h4 class="text-2xl font-black tracking-tight text-white"><?= e($course['title']) ?></h4>
                                        <div class="flex flex-wrap gap-5 text-sm text-slate-300">
                                            <span>Professor: <strong class="text-white"><?= e($course['teacher_name'] ?? 'A definir') ?></strong></span>
                                            <span>Carga horária: <strong class="text-white"><?= (int) ($course['workload_hours'] ?? 0) ?>h</strong></span>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between text-sm text-slate-300">
                                            <span>Progresso do curso</span>
                                            <span class="font-black text-white"><?= number_format((float) ($course['progress_percent'] ?? 0), 0) ?>%</span>
                                        </div>
                                        <div class="h-3 overflow-hidden rounded-full bg-white/10">
                                            <div class="h-full rounded-full bg-[linear-gradient(90deg,_#38bdf8,_#34d399)]" style="width: <?= min(100, max(0, (float) ($course['progress_percent'] ?? 0))) ?>%"></div>
                                        </div>
                                        <div class="flex flex-wrap gap-3 pt-1">
                                            <a href="/elearning/colaborador/cursos/<?= (int) $course['id'] ?>" class="rounded-full bg-white px-4 py-2 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Abrir curso</a>
                                            <a href="/elearning/colaborador/cursos/<?= (int) $course['id'] ?>/continuar" class="rounded-full border border-white/15 px-4 py-2 text-sm font-black text-white transition hover:bg-white/10">Continuar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="space-y-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-200/70">Disponíveis</p>
                    <h3 class="text-2xl font-black tracking-tight text-white">Catálogo para matrícula</h3>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <?php if (!$availableCourses): ?>
                        <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-8 text-center text-slate-300 md:col-span-2">Todos os cursos publicados já estão na sua trilha atual.</div>
                    <?php endif; ?>

                    <?php foreach ($availableCourses as $course): ?>
                        <article class="overflow-hidden rounded-[1.75rem] border border-white/10 bg-white/5 shadow-soft backdrop-blur-xl">
                            <div class="aspect-[16/9] bg-slate-900">
                                <img src="<?= e($course['cover_url']) ?>" alt="<?= e($course['title']) ?>" class="h-full w-full object-cover">
                            </div>
                            <div class="space-y-4 p-5">
                                <div class="space-y-2">
                                    <span class="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-emerald-100"><?= e($course['category'] ?? 'Geral') ?></span>
                                    <h4 class="text-xl font-black tracking-tight text-white"><?= e($course['title']) ?></h4>
                                    <p class="text-sm leading-relaxed text-slate-300"><?= e($course['description'] ?? 'Curso disponível para matrícula imediata.') ?></p>
                                </div>
                                <div class="flex items-center justify-between text-sm text-slate-300">
                                    <span><?= (int) ($course['workload_hours'] ?? 0) ?>h</span>
                                    <span><?= e($course['teacher_name'] ?? 'Professor') ?></span>
                                </div>
                                <button type="button" class="w-full rounded-full bg-sky-500 px-4 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.01]" onclick="enrollCourse(<?= (int) $course['id'] ?>)">Matricular agora</button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-soft backdrop-blur-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-violet-200/70">Próxima ação</p>
                <h3 class="mt-3 text-2xl font-black tracking-tight text-white">Sua próxima aula</h3>
                <?php if ($nextLesson): ?>
                    <p class="mt-3 text-sm leading-relaxed text-slate-300">Retome <strong class="text-white"><?= e($nextLesson['title']) ?></strong> do curso <strong class="text-white"><?= e($nextLesson['course_title']) ?></strong>.</p>
                    <a href="/elearning/colaborador/materiais/<?= (int) $nextLesson['id'] ?>/assistir" class="mt-5 inline-flex rounded-full bg-white px-4 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Abrir aula</a>
                <?php else: ?>
                    <p class="mt-3 text-sm leading-relaxed text-slate-300">Sem pendências imediatas. Seu dashboard será atualizado quando novos conteúdos forem liberados.</p>
                <?php endif; ?>
            </section>

            <section class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-soft backdrop-blur-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-200/70">Certificados</p>
                        <h3 class="mt-2 text-2xl font-black tracking-tight text-white">Disponíveis</h3>
                    </div>
                    <i class="ph-fill ph-seal-check text-3xl text-amber-200"></i>
                </div>
                <div class="mt-5 space-y-3">
                    <?php if (!$certificates): ?>
                        <p class="text-sm text-slate-300">Assim que você concluir um curso elegível, o certificado aparecerá aqui.</p>
                    <?php endif; ?>
                    <?php foreach (array_slice($certificates, 0, 3) as $certificate): ?>
                        <a href="/elearning/colaborador/certificados/<?= e($certificate['validation_code']) ?>" class="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 transition hover:bg-slate-900">
                            <div>
                                <p class="text-sm font-bold text-white"><?= e($certificate['course_title']) ?></p>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400"><?= date('d/m/Y', strtotime((string) $certificate['issued_at'])) ?></p>
                            </div>
                            <span class="rounded-full bg-amber-300 px-3 py-1 text-xs font-black uppercase tracking-[0.2em] text-slate-950">Abrir</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-soft backdrop-blur-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-300/70">Histórico recente</p>
                <div class="mt-4 space-y-3">
                    <?php if (!$history): ?>
                        <p class="text-sm text-slate-300">Seu histórico ficará disponível conforme você concluir suas primeiras trilhas.</p>
                    <?php endif; ?>
                    <?php foreach (array_slice($history, 0, 4) as $item): ?>
                        <div class="rounded-2xl border border-white/10 bg-slate-950/30 px-4 py-3">
                            <p class="text-sm font-bold text-white"><?= e($item['title']) ?></p>
                            <div class="mt-2 flex items-center justify-between text-xs uppercase tracking-[0.2em] text-slate-400">
                                <span><?= e((string) $item['status']) ?></span>
                                <span><?= !empty($item['completed_at']) ? date('d/m/Y', strtotime((string) $item['completed_at'])) : '--' ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </aside>
    </div>
</section>

<script>
async function enrollCourse(courseId) {
    const formData = new FormData();
    formData.append('course_id', courseId);

    const response = await fetch('/elearning/colaborador/matricular', { method: 'POST', body: formData });
    const result = await response.json();
    if (!result.success) {
        showELToast(result.message || 'Não foi possível concluir a matrícula.', 'error');
        return;
    }

    showELToast(result.message || 'Matrícula concluída com sucesso.', 'success');
    setTimeout(() => window.location.reload(), 700);
}
</script>
