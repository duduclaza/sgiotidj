<?php
$stats = $data['stats'] ?? [];
$enrolledCourses = $data['enrolled_courses'] ?? [];
$availableCourses = $data['available_courses'] ?? [];
$nextLesson = $data['next_lesson'] ?? null;
$certificates = $data['certificates'] ?? [];
$history = $data['history'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);

$statCards = [
    ['label' => 'Em andamento', 'value' => $stats['in_progress'] ?? 0, 'icon' => 'ph-book-open-text'],
    ['label' => 'Concluidos', 'value' => $stats['completed'] ?? 0, 'icon' => 'ph-check-circle'],
    ['label' => 'Provas pendentes', 'value' => $stats['pending_exams'] ?? 0, 'icon' => 'ph-clipboard-text'],
    ['label' => 'Progresso geral', 'value' => ($stats['overall_progress'] ?? 0) . '%', 'icon' => 'ph-chart-line-up'],
];
?>

<section class="space-y-8">
    <div class="overflow-hidden rounded-[2.25rem] border border-white/10 bg-[linear-gradient(135deg,_rgba(15,23,42,0.94),_rgba(8,47,73,0.64)_58%,_rgba(13,148,136,0.38))] p-8 shadow-soft sm:p-10">
        <div class="grid gap-8 xl:grid-cols-[1.15fr,0.85fr]">
            <div class="space-y-6">
                <div class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-[11px] font-black uppercase tracking-[0.32em] text-cyan-50/80">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-300"></span>
                    Campus digital
                </div>
                <div class="space-y-4">
                    <h2 class="max-w-4xl text-4xl font-black tracking-tight text-white sm:text-5xl">
                        Aprenda com foco, acompanhe seu ritmo e volte ao SGI quando precisar.
                    </h2>
                    <p class="max-w-3xl text-base leading-relaxed text-slate-200/75">
                        Um painel mais direto para continuar aulas, ver cursos ativos e acompanhar certificados sem excesso de informacao na tela.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <?php if ($nextLesson): ?>
                        <a href="/elearning/colaborador/materiais/<?= (int) $nextLesson['id'] ?>/assistir" class="rounded-full bg-white px-5 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Continuar aula</a>
                    <?php endif; ?>
                    <a href="/elearning/colaborador/historico" class="rounded-full border border-white/20 px-5 py-3 text-sm font-black text-white transition hover:bg-white/10">Ver historico</a>
                    <a href="/inicio" class="rounded-full border border-white/20 px-5 py-3 text-sm font-black text-white transition hover:bg-white/10">Retornar ao SGI</a>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <?php foreach ($statCards as $card): ?>
                    <article class="rounded-[1.75rem] border border-white/10 bg-slate-950/30 p-5 backdrop-blur-xl">
                        <div class="flex items-center justify-between">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 text-cyan-100">
                                <i class="ph <?= e($card['icon']) ?> text-xl"></i>
                            </div>
                            <span class="text-xs font-semibold uppercase tracking-[0.24em] text-white/40">Aluno</span>
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
            O modulo esta pronto visualmente, mas o schema MariaDB ainda nao foi aplicado neste ambiente.
        </div>
    <?php endif; ?>

    <div class="grid gap-8 xl:grid-cols-[1.4fr,0.9fr]">
        <div class="space-y-8">
            <section class="space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-100/60">Meus cursos</p>
                        <h3 class="text-3xl font-black tracking-tight text-white">Cursos matriculados</h3>
                    </div>
                    <span class="rounded-full border border-white/10 bg-white/[0.04] px-4 py-2 text-xs font-bold uppercase tracking-[0.25em] text-white/70"><?= count($enrolledCourses) ?> ativos</span>
                </div>

                <div class="grid gap-4">
                    <?php if (!$enrolledCourses): ?>
                        <div class="rounded-[1.75rem] border border-dashed border-white/10 bg-white/[0.045] p-8 text-center text-slate-300">Nenhum curso matriculado ainda.</div>
                    <?php endif; ?>

                    <?php foreach ($enrolledCourses as $course): ?>
                        <article class="overflow-hidden rounded-[1.9rem] border border-white/10 bg-white/[0.045] shadow-soft backdrop-blur-xl">
                            <div class="grid gap-0 lg:grid-cols-[220px,1fr]">
                                <div class="min-h-[180px] bg-slate-900">
                                    <img src="<?= e($course['cover_url']) ?>" alt="<?= e($course['title']) ?>" class="h-full w-full object-cover">
                                </div>
                                <div class="flex flex-col justify-between gap-5 p-6">
                                    <div class="space-y-3">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <span class="rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-cyan-100"><?= e($course['category'] ?? 'Geral') ?></span>
                                            <span class="rounded-full border border-white/10 bg-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-slate-200"><?= e((string) ($course['enrollment_status'] ?? 'in_progress')) ?></span>
                                        </div>
                                        <h4 class="text-2xl font-black tracking-tight text-white"><?= e($course['title']) ?></h4>
                                        <div class="flex flex-wrap gap-5 text-sm text-slate-300">
                                            <span>Professor: <strong class="text-white"><?= e($course['teacher_name'] ?? 'A definir') ?></strong></span>
                                            <span>Carga horaria: <strong class="text-white"><?= (int) ($course['workload_hours'] ?? 0) ?>h</strong></span>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between text-sm text-slate-300">
                                            <span>Progresso do curso</span>
                                            <span class="font-black text-white"><?= number_format((float) ($course['progress_percent'] ?? 0), 0) ?>%</span>
                                        </div>
                                        <div class="h-2.5 overflow-hidden rounded-full bg-white/10">
                                            <div class="h-full rounded-full bg-[linear-gradient(90deg,_#67e8f9,_#99f6e4)]" style="width: <?= min(100, max(0, (float) ($course['progress_percent'] ?? 0))) ?>%"></div>
                                        </div>
                                        <div class="flex flex-wrap gap-3 pt-1">
                                            <a href="/elearning/colaborador/cursos/<?= (int) $course['id'] ?>" class="rounded-full bg-white px-4 py-2 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Abrir curso</a>
                                            <a href="/elearning/colaborador/cursos/<?= (int) $course['id'] ?>/continuar" class="rounded-full border border-white/10 px-4 py-2 text-sm font-black text-white transition hover:bg-white/10">Continuar</a>
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
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-100/60">Disponiveis</p>
                    <h3 class="text-3xl font-black tracking-tight text-white">Catalogo para matricula</h3>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <?php if (!$availableCourses): ?>
                        <div class="rounded-[1.75rem] border border-dashed border-white/10 bg-white/[0.045] p-8 text-center text-slate-300 md:col-span-2">Todos os cursos publicados ja estao na sua trilha atual.</div>
                    <?php endif; ?>

                    <?php foreach ($availableCourses as $course): ?>
                        <article class="overflow-hidden rounded-[1.9rem] border border-white/10 bg-white/[0.045] shadow-soft backdrop-blur-xl">
                            <div class="aspect-[16/9] bg-slate-900">
                                <img src="<?= e($course['cover_url']) ?>" alt="<?= e($course['title']) ?>" class="h-full w-full object-cover">
                            </div>
                            <div class="space-y-4 p-5">
                                <div class="space-y-2">
                                    <span class="rounded-full border border-emerald-300/20 bg-emerald-300/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-emerald-100"><?= e($course['category'] ?? 'Geral') ?></span>
                                    <h4 class="text-xl font-black tracking-tight text-white"><?= e($course['title']) ?></h4>
                                    <p class="text-sm leading-relaxed text-slate-300"><?= e($course['description'] ?? 'Curso disponivel para matricula imediata.') ?></p>
                                </div>
                                <div class="flex items-center justify-between text-sm text-slate-300">
                                    <span><?= (int) ($course['workload_hours'] ?? 0) ?>h</span>
                                    <span><?= e($course['teacher_name'] ?? 'Professor') ?></span>
                                </div>
                                <button type="button" class="w-full rounded-full bg-white px-4 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.01]" onclick="enrollCourse(<?= (int) $course['id'] ?>)">Matricular agora</button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-[1.9rem] border border-white/10 bg-white/[0.045] p-6 shadow-soft backdrop-blur-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-100/60">Proxima acao</p>
                <h3 class="mt-3 text-2xl font-black tracking-tight text-white">Sua proxima aula</h3>
                <?php if ($nextLesson): ?>
                    <p class="mt-3 text-sm leading-relaxed text-slate-300">Retome <strong class="text-white"><?= e($nextLesson['title']) ?></strong> do curso <strong class="text-white"><?= e($nextLesson['course_title']) ?></strong>.</p>
                    <a href="/elearning/colaborador/materiais/<?= (int) $nextLesson['id'] ?>/assistir" class="mt-5 inline-flex rounded-full bg-white px-4 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Abrir aula</a>
                <?php else: ?>
                    <p class="mt-3 text-sm leading-relaxed text-slate-300">Sem pendencias imediatas. O painel sera atualizado quando novos conteudos forem liberados.</p>
                <?php endif; ?>
            </section>

            <section class="rounded-[1.9rem] border border-white/10 bg-white/[0.045] p-6 shadow-soft backdrop-blur-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-100/60">Certificados</p>
                        <h3 class="mt-2 text-2xl font-black tracking-tight text-white">Disponiveis</h3>
                    </div>
                    <i class="ph-fill ph-seal-check text-3xl text-amber-200"></i>
                </div>
                <div class="mt-5 space-y-3">
                    <?php if (!$certificates): ?>
                        <p class="text-sm text-slate-300">Assim que voce concluir um curso elegivel, o certificado aparecera aqui.</p>
                    <?php endif; ?>
                    <?php foreach (array_slice($certificates, 0, 3) as $certificate): ?>
                        <a href="/elearning/colaborador/certificados/<?= e($certificate['validation_code']) ?>" class="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/35 px-4 py-3 transition hover:bg-white/10">
                            <div>
                                <p class="text-sm font-bold text-white"><?= e($certificate['course_title']) ?></p>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400"><?= date('d/m/Y', strtotime((string) $certificate['issued_at'])) ?></p>
                            </div>
                            <span class="rounded-full bg-amber-200 px-3 py-1 text-xs font-black uppercase tracking-[0.2em] text-slate-950">Abrir</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="rounded-[1.9rem] border border-white/10 bg-white/[0.045] p-6 shadow-soft backdrop-blur-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-300/60">Historico recente</p>
                <div class="mt-4 space-y-3">
                    <?php if (!$history): ?>
                        <p class="text-sm text-slate-300">Seu historico ficara disponivel conforme voce concluir suas primeiras trilhas.</p>
                    <?php endif; ?>
                    <?php foreach (array_slice($history, 0, 4) as $item): ?>
                        <div class="rounded-2xl border border-white/10 bg-slate-950/35 px-4 py-3">
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
        showELToast(result.message || 'Nao foi possivel concluir a matricula.', 'error');
        return;
    }

    showELToast(result.message || 'Matricula concluida com sucesso.', 'success');
    setTimeout(() => window.location.reload(), 700);
}
</script>
