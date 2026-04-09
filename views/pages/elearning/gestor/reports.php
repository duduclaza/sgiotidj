<?php
$courses = $data['courses'] ?? [];
$storage = $data['storage'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);
?>

<section class="space-y-8">
    <div class="flex flex-col gap-5 rounded-[2rem] border border-slate-200 bg-white p-8 shadow-xl xl:flex-row xl:items-end xl:justify-between">
        <div class="space-y-4">
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Relatorios do Professor</p>
            <h1 class="text-4xl font-black tracking-tight text-slate-900">Matriculas, conclusao, aprovacao e certificados por curso.</h1>
            <p class="max-w-3xl text-base leading-relaxed text-slate-600">Use este painel para acompanhar desempenho academico, adesao dos alunos e impacto da operacao de E-Learning por curso publicado ou em preparacao.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="/elearning/gestor/cursos" class="rounded-full bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:scale-[1.02]">Gerenciar cursos</a>
            <a href="/elearning/gestor/armazenamento" class="rounded-full border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50">Painel de armazenamento</a>
        </div>
    </div>

    <?php if (!$schemaReady): ?>
        <div class="rounded-[1.75rem] border border-amber-300 bg-amber-50 px-5 py-4 text-sm leading-relaxed text-amber-950">
            O schema do modulo ainda nao foi aplicado neste ambiente, entao os indicadores reais aparecerao apos a execucao do SQL do E-Learning.
        </div>
    <?php endif; ?>

    <div class="grid gap-6 xl:grid-cols-[1.2fr,0.8fr]">
        <section class="space-y-5">
            <?php if (!$courses): ?>
                <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                    Nenhum curso encontrado para este professor.
                </div>
            <?php endif; ?>

            <?php foreach ($courses as $course): ?>
                <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-lg">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="space-y-2">
                            <div class="flex flex-wrap gap-3">
                                <span class="rounded-full bg-slate-900 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-white"><?= e($course['status_label'] ?? 'Rascunho') ?></span>
                                <span class="rounded-full bg-sky-100 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-sky-700"><?= e($course['category'] ?? 'Geral') ?></span>
                            </div>
                            <h2 class="text-2xl font-black tracking-tight text-slate-900"><?= e($course['title']) ?></h2>
                            <p class="text-sm text-slate-500"><?= (int) ($course['enrollments_count'] ?? 0) ?> matricula(s) | <?= (int) ($course['certificates_count'] ?? 0) ?> certificado(s) | <?= (int) ($course['completed_count'] ?? 0) ?> concluido(s)</p>
                        </div>
                        <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/progresso" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50">Abrir curso</a>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Progresso medio</p>
                            <p class="mt-3 text-3xl font-black text-slate-900"><?= number_format((float) ($course['avg_progress'] ?? 0), 0) ?>%</p>
                        </div>
                        <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Taxa de aprovacao</p>
                            <p class="mt-3 text-3xl font-black text-slate-900"><?= number_format((float) ($course['approval_rate'] ?? 0), 0) ?>%</p>
                        </div>
                        <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Reprovados</p>
                            <p class="mt-3 text-3xl font-black text-slate-900"><?= (int) ($course['failed_count'] ?? 0) ?></p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <div class="flex items-center justify-between text-sm text-slate-500">
                            <span>Andamento medio da turma</span>
                            <strong class="text-slate-900"><?= number_format((float) ($course['avg_progress'] ?? 0), 0) ?>%</strong>
                        </div>
                        <div class="mt-3 h-3 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-[linear-gradient(90deg,_#2563eb,_#06b6d4)]" style="width: <?= min(100, max(0, (float) ($course['avg_progress'] ?? 0))) ?>%"></div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <aside class="space-y-6">
            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Minutos globais</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900"><?= e($storage['used_human'] ?? '0 min') ?></h2>
                <p class="mt-3 text-sm leading-relaxed text-slate-600">Consumidos de <?= e($storage['contracted_human'] ?? '10.000 min') ?> contratados para videos do modulo.</p>
                <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full <?= ($storage['alert_level'] ?? 'healthy') === 'critical' ? 'bg-rose-500' : ((($storage['alert_level'] ?? 'healthy') === 'warning') ? 'bg-amber-500' : 'bg-emerald-500') ?>" style="width: <?= min(100, max(0, (float) ($storage['percent_used'] ?? 0))) ?>%"></div>
                </div>
                <p class="mt-3 text-sm font-semibold text-slate-600">Disponivel: <strong class="text-slate-900"><?= e($storage['available_human'] ?? '0 min') ?></strong></p>
            </section>

            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Foco operacional</p>
                <ul class="mt-5 space-y-3 text-sm leading-relaxed text-slate-600">
                    <li>Priorize cursos com alto volume de matriculas e baixa aprovacao.</li>
                    <li>Monitore provas obrigatorias para evitar bloqueio de certificados.</li>
                    <li>Use os minutos globais como indicador de necessidade de expansao.</li>
                </ul>
            </section>
        </aside>
    </div>
</section>
