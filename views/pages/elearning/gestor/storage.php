<?php
$summary = $data['summary'] ?? [];
$courses = $data['courses'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);
$barColor = ($summary['alert_level'] ?? 'healthy') === 'critical'
    ? 'bg-rose-500'
    : ((($summary['alert_level'] ?? 'healthy') === 'warning') ? 'bg-amber-500' : 'bg-emerald-500');
$summaryRows = [
    ['label' => 'Contratado', 'value' => $summary['contracted_human'] ?? '10.000 min'],
    ['label' => 'Em uso', 'value' => $summary['used_human'] ?? '0 min'],
    ['label' => 'Disponivel', 'value' => $summary['available_human'] ?? '10.000 min'],
    ['label' => 'Consumo', 'value' => number_format((float) ($summary['percent_used'] ?? 0), 2, ',', '.') . '%'],
];
?>

<section class="space-y-8">
    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-[linear-gradient(135deg,_#07111f,_#0f3d4d_48%,_#0f766e)] p-8 text-white shadow-2xl">
        <div class="grid gap-8 xl:grid-cols-[1.05fr,0.95fr]">
            <div class="space-y-5">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-100/75">SGI STREAM</p>
                <h1 class="max-w-2xl text-3xl font-black tracking-tight sm:text-4xl">
                    Controle de capacidade de video
                </h1>
                <p class="max-w-2xl text-sm leading-relaxed text-emerald-50/80 sm:text-base">
                    Uma leitura simples do consumo do modulo para acompanhar novos envios com tranquilidade e sem poluir a tela com blocos desnecessarios.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="/elearning/gestor/cursos" class="rounded-full bg-white px-5 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">
                        Gerenciar cursos
                    </a>
                    <a href="/elearning/gestor/relatorios" class="rounded-full border border-white/15 px-5 py-3 text-sm font-black text-white transition hover:bg-white/10">
                        Relatorios
                    </a>
                </div>
            </div>

            <div class="rounded-[1.75rem] border border-white/12 bg-white/10 p-5 backdrop-blur-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-emerald-100/65">Resumo rapido</p>
                <div class="mt-4 space-y-3">
                    <?php foreach ($summaryRows as $row): ?>
                        <div class="flex items-start justify-between gap-4 rounded-2xl border border-white/10 bg-black/10 px-4 py-3">
                            <span class="pt-1 text-sm font-semibold text-emerald-50/75"><?= e($row['label']) ?></span>
                            <strong class="max-w-[13rem] text-right text-base font-black leading-tight text-white [overflow-wrap:anywhere] sm:text-lg">
                                <?= e($row['value']) ?>
                            </strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$schemaReady): ?>
        <div class="rounded-[1.75rem] border border-amber-300 bg-amber-50 px-5 py-4 text-sm leading-relaxed text-amber-950">
            O painel ja esta pronto, mas o schema do modulo ainda nao foi aplicado neste ambiente.
        </div>
    <?php endif; ?>

    <div class="grid gap-6 xl:grid-cols-[0.85fr,1.15fr]">
        <aside class="space-y-6">
            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Capacidade global</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Uso atual do modulo</h2>

                <div class="mt-5 h-4 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full <?= $barColor ?>" style="width: <?= min(100, max(0, (float) ($summary['percent_used'] ?? 0))) ?>%"></div>
                </div>

                <div class="mt-5 space-y-3">
                    <div class="flex items-start justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3">
                        <span class="text-sm font-semibold text-slate-600">Minutos usados</span>
                        <strong class="max-w-[12rem] text-right text-sm font-black text-slate-900 [overflow-wrap:anywhere]">
                            <?= e($summary['used_human'] ?? '0 min') ?>
                        </strong>
                    </div>
                    <div class="flex items-start justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3">
                        <span class="text-sm font-semibold text-slate-600">Minutos livres</span>
                        <strong class="max-w-[12rem] text-right text-sm font-black text-slate-900 [overflow-wrap:anywhere]">
                            <?= e($summary['available_human'] ?? '10.000 min') ?>
                        </strong>
                    </div>
                </div>

                <p class="mt-5 text-sm leading-relaxed text-slate-600">
                    Consumo de <?= number_format((float) ($summary['percent_used'] ?? 0), 2, ',', '.') ?>% sobre <?= e($summary['contracted_human'] ?? '10.000 min') ?>.
                </p>

                <?php if (($summary['alert_level'] ?? 'healthy') === 'warning'): ?>
                    <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        Alerta de 80% atingido. Vale planejar a ampliacao antes de novos envios em massa.
                    </div>
                <?php endif; ?>

                <?php if (!empty($summary['is_upload_blocked'])): ?>
                    <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                        O limite foi atingido. Novos uploads ficam bloqueados ate a liberacao de mais capacidade.
                    </div>
                <?php endif; ?>
            </section>

            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Politica do modulo</p>
                <ul class="mt-5 space-y-3 text-sm leading-relaxed text-slate-600">
                    <li>O consumo e recalculado sempre que um video e enviado, removido ou substituido.</li>
                    <li>Somente videos de aulas entram no calculo da capacidade global.</li>
                    <li>Ao atingir 100%, novos envios ficam bloqueados automaticamente.</li>
                </ul>
            </section>
        </aside>

        <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Por curso</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Distribuicao dos minutos</h2>
                </div>
                <span class="rounded-full border border-slate-200 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-slate-500">
                    <?= count($courses) ?> curso(s)
                </span>
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
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="text-lg font-black tracking-tight text-slate-900"><?= e($course['title']) ?></h3>
                                <p class="mt-2 text-sm text-slate-500"><?= (int) ($course['videos_count'] ?? 0) ?> video(s) armazenado(s)</p>
                            </div>
                            <div class="text-right">
                                <p class="max-w-[12rem] text-base font-black leading-tight text-slate-900 [overflow-wrap:anywhere]">
                                    <?= e($course['used_human'] ?? '0 min') ?>
                                </p>
                                <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/aulas" class="mt-2 inline-flex rounded-full border border-slate-200 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-slate-700 transition hover:bg-white">
                                    Abrir curso
                                </a>
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
