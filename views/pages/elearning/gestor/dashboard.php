<?php
$stats = $data['stats'] ?? [];
$courses = $data['courses'] ?? [];
$storage = $data['storage'] ?? [];
$charts = $data['charts'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);
?>

<section class="space-y-8">
    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-[linear-gradient(135deg,_#0f172a,_#1d4ed8_55%,_#0f766e)] p-8 text-white shadow-2xl">
        <div class="grid gap-8 xl:grid-cols-[1.35fr,0.95fr]">
            <div class="space-y-5">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-sky-100/70">Submodulo Professor</p>
                <h1 class="max-w-3xl text-4xl font-black tracking-tight sm:text-5xl">Orquestre cursos, video-aulas, avaliacoes e certificados com o padrao visual do SGI.</h1>
                <p class="max-w-3xl text-base leading-relaxed text-sky-50/80">Este painel resume publicacao de cursos, adesao dos alunos, taxa de aprovacao e o consumo dos minutos de video enviados ao Bunny Stream.</p>
                <div class="flex flex-wrap gap-3">
                    <a href="/elearning/gestor/cursos" class="rounded-full bg-white px-5 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Gerenciar cursos</a>
                    <a href="/elearning/gestor/armazenamento" class="rounded-full border border-white/20 px-5 py-3 text-sm font-black text-white transition hover:bg-white/10">Painel de armazenamento</a>
                    <a href="/elearning/gestor/relatorios" class="rounded-full border border-white/20 px-5 py-3 text-sm font-black text-white transition hover:bg-white/10">Relatorios</a>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <?php foreach ([
                    ['label' => 'Cursos', 'value' => $stats['total_courses'] ?? 0, 'icon' => 'ph-books'],
                    ['label' => 'Aulas', 'value' => $stats['total_lessons'] ?? 0, 'icon' => 'ph-play-circle'],
                    ['label' => 'Alunos matriculados', 'value' => $stats['total_students'] ?? 0, 'icon' => 'ph-users-three'],
                    ['label' => 'Taxa media de aprovacao', 'value' => number_format((float) ($stats['approval_rate'] ?? 0), 0) . '%', 'icon' => 'ph-chart-line-up'],
                ] as $card): ?>
                    <article class="rounded-[1.75rem] border border-white/10 bg-white/10 p-5 backdrop-blur-xl">
                        <div class="flex items-center justify-between">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 text-white">
                                <i class="ph <?= $card['icon'] ?> text-xl"></i>
                            </div>
                            <span class="text-xs font-semibold uppercase tracking-[0.25em] text-white/60">Professor</span>
                        </div>
                        <p class="mt-5 text-3xl font-black text-white"><?= e((string) $card['value']) ?></p>
                        <p class="mt-2 text-sm text-sky-50/80"><?= e($card['label']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php if (!$schemaReady): ?>
        <div class="rounded-[1.75rem] border border-amber-300 bg-amber-50 px-5 py-4 text-sm leading-relaxed text-amber-950">
            O front-end do modulo foi implantado, mas o schema MariaDB ainda nao existe neste ambiente. Execute o SQL do E-Learning para habilitar persistencia, uploads e relatorios reais.
        </div>
    <?php endif; ?>

    <div class="grid gap-6 xl:grid-cols-[1.35fr,0.95fr]">
        <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Cursos ativos</p>
                    <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Radar de operacao</h2>
                </div>
                <a href="/elearning/gestor/cursos" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50">Ver todos</a>
            </div>
            <div class="mt-6 grid gap-4 lg:grid-cols-2">
                <?php if (!$courses): ?>
                    <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-500 lg:col-span-2">Nenhum curso cadastrado ainda.</div>
                <?php endif; ?>
                <?php foreach (array_slice($courses, 0, 4) as $course): ?>
                    <article class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-slate-50 shadow-sm">
                        <div class="aspect-[16/9] bg-slate-900">
                            <img src="<?= e($course['cover_url']) ?>" alt="<?= e($course['title']) ?>" class="h-full w-full object-cover">
                        </div>
                        <div class="space-y-4 p-5">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="rounded-full bg-slate-900 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-white"><?= e($course['status_label'] ?? 'Rascunho') ?></span>
                                <span class="rounded-full bg-sky-100 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-sky-700"><?= e($course['category'] ?? 'Geral') ?></span>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black tracking-tight text-slate-900"><?= e($course['title']) ?></h3>
                                <p class="mt-2 text-sm text-slate-600"><?= (int) ($course['lessons_count'] ?? 0) ?> aulas, <?= (int) ($course['enrollments_count'] ?? 0) ?> alunos, progresso medio de <?= number_format((float) ($course['avg_progress'] ?? 0), 0) ?>%.</p>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/aulas" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:scale-[1.02]">Abrir curso</a>
                                <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/provas" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-white">Provas</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Armazenamento</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Capacidade de video</h2>
                <div class="mt-5 space-y-3">
                    <div class="flex items-center justify-between text-sm text-slate-600">
                        <span>Uso atual</span>
                        <strong class="text-slate-900"><?= e($storage['used_human'] ?? '0 min') ?></strong>
                    </div>
                    <div class="flex items-center justify-between text-sm text-slate-600">
                        <span>Disponivel</span>
                        <strong class="text-slate-900"><?= e($storage['available_human'] ?? '10.000 min') ?></strong>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full <?= ($storage['alert_level'] ?? 'healthy') === 'critical' ? 'bg-rose-500' : ((($storage['alert_level'] ?? 'healthy') === 'warning') ? 'bg-amber-500' : 'bg-emerald-500') ?>" style="width: <?= min(100, max(0, (float) ($storage['percent_used'] ?? 0))) ?>%"></div>
                    </div>
                    <p class="text-sm font-semibold text-slate-600">Consumo de <?= number_format((float) ($storage['percent_used'] ?? 0), 2, ',', '.') ?>% sobre os minutos contratados.</p>
                    <?php if (($storage['alert_level'] ?? 'healthy') !== 'healthy'): ?>
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            <?= ($storage['is_upload_blocked'] ?? false)
                                ? 'Limite de minutos de video atingido. Contrate mais capacidade para continuar enviando novos conteudos.'
                                : 'Alerta de 80% atingido. Planeje a ampliacao de minutos para nao interromper uploads.' ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Graficos</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Matriculas por curso</h2>
                <div class="mt-5 space-y-4">
                    <?php if (empty($charts['enrollments'])): ?>
                        <p class="text-sm text-slate-500">Os graficos aparecerao assim que houver cursos e matriculas registradas.</p>
                    <?php endif; ?>
                    <?php foreach ($charts['enrollments'] as $bar): ?>
                        <?php $width = ($stats['total_students'] ?? 0) > 0 ? ((int) $bar['value'] / max(1, (int) $stats['total_students'])) * 100 : 0; ?>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-slate-700"><?= e($bar['label']) ?></span>
                                <strong class="text-slate-900"><?= (int) $bar['value'] ?></strong>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-[linear-gradient(90deg,_#2563eb,_#0ea5e9)]" style="width: <?= min(100, $width) ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </aside>
    </div>
</section>
