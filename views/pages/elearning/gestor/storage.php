<?php
$summary = $data['summary'] ?? [];
$courses = $data['courses'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);
$barColor = ($summary['alert_level'] ?? 'healthy') === 'critical'
    ? 'bg-rose-500'
    : ((($summary['alert_level'] ?? 'healthy') === 'warning') ? 'bg-amber-500' : 'bg-emerald-500');
?>

<section class="space-y-8">
    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-[linear-gradient(135deg,_#020617,_#0f172a_35%,_#0f766e)] p-8 text-white shadow-2xl">
        <div class="grid gap-8 xl:grid-cols-[1.2fr,0.8fr]">
            <div class="space-y-5">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-100/70">Bunny Stream</p>
                <h1 class="text-4xl font-black tracking-tight sm:text-5xl">Controle de minutos de video para o modulo E-Learning.</h1>
                <p class="max-w-3xl text-base leading-relaxed text-emerald-50/80">O modulo envia os videos para o Bunny Stream, acompanha os minutos consumidos pelas aulas e bloqueia novos uploads ao atingir 100% do limite padrao de 10.000 minutos.</p>
                <div class="flex flex-wrap gap-3">
                    <a href="/elearning/gestor/cursos" class="rounded-full bg-white px-5 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Gerenciar cursos</a>
                    <a href="/elearning/gestor/relatorios" class="rounded-full border border-white/15 px-5 py-3 text-sm font-black text-white transition hover:bg-white/10">Relatorios gerais</a>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <?php foreach ([
                    ['label' => 'Contratado', 'value' => $summary['contracted_human'] ?? '10.000 min'],
                    ['label' => 'Usado', 'value' => $summary['used_human'] ?? '0 min'],
                    ['label' => 'Disponivel', 'value' => $summary['available_human'] ?? '10.000 min'],
                    ['label' => 'Consumo', 'value' => number_format((float) ($summary['percent_used'] ?? 0), 2, ',', '.') . '%'],
                ] as $card): ?>
                    <article class="rounded-[1.75rem] border border-white/10 bg-white/10 p-5 backdrop-blur-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-white/60"><?= e($card['label']) ?></p>
                        <p class="mt-4 text-3xl font-black text-white"><?= e($card['value']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php if (!$schemaReady): ?>
        <div class="rounded-[1.75rem] border border-amber-300 bg-amber-50 px-5 py-4 text-sm leading-relaxed text-amber-950">
            O painel ja esta pronto, mas o schema do modulo ainda nao foi aplicado neste ambiente.
        </div>
    <?php endif; ?>

    <div class="grid gap-6 xl:grid-cols-[0.9fr,1.1fr]">
        <aside class="space-y-6">
            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Capacidade global</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Uso atual</h2>
                <div class="mt-5 h-4 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full <?= $barColor ?>" style="width: <?= min(100, max(0, (float) ($summary['percent_used'] ?? 0))) ?>%"></div>
                </div>
                <p class="mt-4 text-sm font-semibold text-slate-600">Consumo de <?= number_format((float) ($summary['percent_used'] ?? 0), 2, ',', '.') ?>% sobre <?= e($summary['contracted_human'] ?? '10.000 min') ?>.</p>
                <?php if (($summary['alert_level'] ?? 'healthy') === 'warning'): ?>
                    <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        Alerta de 80% atingido. Planeje a ampliacao de minutos para nao interromper uploads.
                    </div>
                <?php endif; ?>
                <?php if (!empty($summary['is_upload_blocked'])): ?>
                    <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                        Limite de minutos de video atingido. Contrate mais capacidade para continuar enviando novos conteudos.
                    </div>
                <?php endif; ?>
            </section>

            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Politica do modulo</p>
                <ul class="mt-5 space-y-3 text-sm leading-relaxed text-slate-600">
                    <li>O consumo e recalculado sempre que um video e enviado, removido ou substituido.</li>
                    <li>Somente videos de aulas entram no calculo do limite global de minutos.</li>
                    <li>Ao atingir 100%, novos uploads de video sao bloqueados automaticamente.</li>
                </ul>
            </section>
        </aside>

        <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Por curso</p>
                    <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Distribuicao dos minutos</h2>
                </div>
                <span class="rounded-full border border-slate-200 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-slate-500"><?= count($courses) ?> curso(s)</span>
            </div>

            <div class="mt-6 space-y-4">
                <?php if (!$courses): ?>
                    <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 p-10 text-center text-slate-500">
                        Nenhum video registrado ainda.
                    </div>
                <?php endif; ?>

                <?php foreach ($courses as $course): ?>
                    <?php
                    $width = (int) ($summary['used_seconds'] ?? 0) > 0
                        ? ((int) ($course['used_seconds'] ?? 0) / max(1, (int) ($summary['used_seconds'] ?? 1))) * 100
                        : 0;
                    ?>
                    <article class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-5">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <h3 class="text-xl font-black tracking-tight text-slate-900"><?= e($course['title']) ?></h3>
                                <p class="mt-2 text-sm text-slate-500"><?= (int) ($course['videos_count'] ?? 0) ?> video(s) armazenado(s)</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-slate-900"><?= e($course['used_human'] ?? '0 min') ?></p>
                                <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/aulas" class="mt-2 inline-flex rounded-full border border-slate-200 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-slate-700 transition hover:bg-white">Abrir curso</a>
                            </div>
                        </div>
                        <div class="mt-4 h-3 overflow-hidden rounded-full bg-white">
                            <div class="h-full rounded-full bg-[linear-gradient(90deg,_#0f766e,_#10b981)]" style="width: <?= min(100, $width) ?>%"></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</section>
