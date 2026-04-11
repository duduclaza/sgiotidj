<?php
$data = $data ?? [];
$stats = $data['stats'] ?? [];
$courses = $data['courses'] ?? [];
$storage = $data['storage'] ?? [];
$templates = $data['templates'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);

$statCards = [
    [
        'label' => 'Cursos',
        'value' => $stats['total_courses'] ?? 0,
        'detail' => ($stats['published_courses'] ?? 0) . ' publicados',
        'icon' => 'ph-books',
    ],
    [
        'label' => 'Aulas',
        'value' => $stats['total_lessons'] ?? 0,
        'detail' => 'conteudos no ar',
        'icon' => 'ph-play-circle',
    ],
    [
        'label' => 'Alunos',
        'value' => $stats['total_students'] ?? 0,
        'detail' => 'matriculas unicas',
        'icon' => 'ph-users-three',
    ],
    [
        'label' => 'Aprovacao',
        'value' => number_format((float) ($stats['approval_rate'] ?? 0), 0) . '%',
        'detail' => 'nas provas enviadas',
        'icon' => 'ph-check-circle',
    ],
];
?>

<section class="space-y-8">
    <div class="overflow-hidden rounded-[2.25rem] border border-white/10 bg-[linear-gradient(135deg,_rgba(15,23,42,0.94),_rgba(8,47,73,0.74)_58%,_rgba(13,148,136,0.52))] p-8 shadow-soft sm:p-10">
        <div class="grid gap-8 xl:grid-cols-[1.15fr,0.85fr]">
            <div class="space-y-6">
                <div class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-[11px] font-black uppercase tracking-[0.32em] text-cyan-50/80">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-300"></span>
                    Professor
                </div>

                <div class="space-y-4">
                    <h1 class="max-w-4xl text-4xl font-black tracking-tight text-white sm:text-5xl">
                        E-Learning Professor
                    </h1>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="/elearning/gestor/cursos" class="rounded-full bg-white px-5 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">
                        Abrir cursos
                    </a>
                    <a href="/elearning/gestor/relatorios" class="rounded-full border border-white/20 px-5 py-3 text-sm font-black text-white transition hover:bg-white/10">
                        Ver relatorios
                    </a>
                    <a href="/inicio" class="rounded-full border border-white/20 px-5 py-3 text-sm font-black text-white transition hover:bg-white/10">
                        Retornar ao SGI
                    </a>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <?php foreach ($statCards as $card): ?>
                    <article class="rounded-[1.75rem] border border-white/10 bg-slate-950/30 p-5 backdrop-blur-xl">
                        <div class="flex items-center justify-between">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 text-cyan-100">
                                <i class="ph <?= e($card['icon']) ?> text-xl"></i>
                            </div>
                            <span class="text-xs font-semibold uppercase tracking-[0.24em] text-white/40">SGI</span>
                        </div>
                        <p class="mt-5 text-3xl font-black text-white"><?= e((string) $card['value']) ?></p>
                        <p class="mt-1 text-sm font-semibold text-slate-200/70"><?= e($card['label']) ?></p>
                        <p class="mt-2 text-xs uppercase tracking-[0.2em] text-slate-400"><?= e($card['detail']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php if (!$schemaReady): ?>
        <div class="rounded-[1.75rem] border border-amber-300/30 bg-amber-400/10 px-5 py-4 text-sm leading-relaxed text-amber-50">
            O front do modulo ja esta disponivel, mas o schema MariaDB ainda nao foi aplicado neste ambiente.
        </div>
    <?php endif; ?>

    <div class="grid gap-6 xl:grid-cols-[1.25fr,0.75fr]">
        <section class="rounded-[2rem] border border-white/10 bg-white/[0.045] p-6 shadow-soft backdrop-blur-xl">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-100/60">Cursos recentes</p>
                    <h2 class="mt-2 text-3xl font-black tracking-tight text-white">Trilhas do professor</h2>
                </div>
                <a href="/elearning/gestor/cursos" class="rounded-full border border-white/10 px-4 py-2 text-sm font-black text-white transition hover:bg-white/10">
                    Ver todos
                </a>
            </div>

            <div class="mt-6 grid gap-4">
                <?php if (!$courses): ?>
                    <div class="rounded-[1.75rem] border border-dashed border-white/10 bg-slate-950/30 p-10 text-center text-slate-300">
                        Nenhum curso cadastrado ainda.
                    </div>
                <?php endif; ?>

                <?php foreach (array_slice($courses, 0, 4) as $course): ?>
                    <article class="overflow-hidden rounded-[1.75rem] border border-white/10 bg-slate-950/35 shadow-soft">
                        <div class="grid gap-0 lg:grid-cols-[220px,1fr]">
                            <div class="min-h-[160px] bg-slate-900">
                                <img src="<?= e($course['cover_url']) ?>" alt="<?= e($course['title']) ?>" class="h-full w-full object-cover">
                            </div>
                            <div class="flex flex-col justify-between gap-5 p-5">
                                <div class="space-y-3">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="rounded-full border border-white/10 bg-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.22em] text-slate-100"><?= e($course['status_label'] ?? 'Rascunho') ?></span>
                                        <span class="rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.22em] text-cyan-100"><?= e($course['category'] ?? 'Geral') ?></span>
                                    </div>
                                    <h3 class="text-2xl font-black tracking-tight text-white"><?= e($course['title']) ?></h3>
                                    <p class="text-sm text-slate-300">
                                        <?= (int) ($course['lessons_count'] ?? 0) ?> aulas | <?= (int) ($course['enrollments_count'] ?? 0) ?> alunos | <?= (int) ($course['workload_hours'] ?? 0) ?>h
                                    </p>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between text-sm text-slate-300">
                                        <span>Progresso medio</span>
                                        <strong class="text-white"><?= number_format((float) ($course['avg_progress'] ?? 0), 0) ?>%</strong>
                                    </div>
                                    <div class="h-2.5 overflow-hidden rounded-full bg-white/10">
                                        <div class="h-full rounded-full bg-[linear-gradient(90deg,_#67e8f9,_#99f6e4)]" style="width: <?= min(100, max(0, (float) ($course['avg_progress'] ?? 0))) ?>%"></div>
                                    </div>
                                    <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/aulas" class="inline-flex rounded-full bg-white px-4 py-2 text-sm font-black text-slate-950 transition hover:scale-[1.02]">
                                        Abrir curso
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="rounded-[2rem] border border-white/10 bg-white/[0.045] p-6 shadow-soft backdrop-blur-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-100/60">Armazenamento</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight text-white"><?= e($storage['used_human'] ?? '0 min') ?></h2>
                <p class="mt-3 text-sm leading-relaxed text-slate-300">
                    Consumidos de <?= e($storage['contracted_human'] ?? '10.000 min') ?> no SGI STREAM.
                </p>
                <div class="mt-5 h-3 overflow-hidden rounded-full bg-white/10">
                    <div class="h-full rounded-full bg-[linear-gradient(90deg,_#2dd4bf,_#67e8f9)]" style="width: <?= min(100, max(0, (float) ($storage['percent_used'] ?? 0))) ?>%"></div>
                </div>
                <a href="/elearning/gestor/armazenamento" class="mt-5 inline-flex rounded-full border border-white/10 px-4 py-2 text-sm font-black text-white transition hover:bg-white/10">
                    Abrir capacidade
                </a>
            </section>

            <section class="rounded-[2rem] border border-white/10 bg-white/[0.045] p-6 shadow-soft backdrop-blur-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-300/60">Operacao</p>
                <div class="mt-5 space-y-3">
                    <a href="/elearning/gestor/cursos" class="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/30 px-4 py-3 text-sm font-bold text-slate-100 transition hover:bg-white/10">
                        Gerenciar cursos
                        <i class="ph ph-arrow-up-right"></i>
                    </a>
                    <a href="/elearning/gestor/diploma/config" class="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/30 px-4 py-3 text-sm font-bold text-slate-100 transition hover:bg-white/10">
                        Biblioteca de certificados
                        <span><?= count($templates) ?></span>
                    </a>
                    <a href="/inicio" class="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/30 px-4 py-3 text-sm font-bold text-slate-100 transition hover:bg-white/10">
                        Retornar ao SGI
                        <i class="ph ph-sign-out"></i>
                    </a>
                </div>
            </section>
        </aside>
    </div>
</section>
