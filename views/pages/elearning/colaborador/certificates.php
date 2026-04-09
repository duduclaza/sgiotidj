<?php
$certificates = $data['certificates'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);
?>

<section class="space-y-8">
    <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-[linear-gradient(135deg,_rgba(250,204,21,0.18),_rgba(15,23,42,0.78))] p-8 shadow-soft">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-100/70">Conquistas</p>
        <h2 class="mt-4 text-4xl font-black tracking-tight text-white">Seus certificados digitais</h2>
        <p class="mt-3 max-w-2xl text-base leading-relaxed text-slate-100/80">Acesse certificados emitidos automaticamente após concluir os requisitos do curso, incluindo a prova obrigatória e a nota mínima de 70%.</p>
    </div>

    <?php if (!$schemaReady): ?>
        <div class="rounded-[1.75rem] border border-amber-400/30 bg-amber-500/10 px-5 py-4 text-sm leading-relaxed text-amber-50">
            O ambiente ainda está sem o schema do módulo. Assim que o SQL for aplicado, os certificados emitidos passarão a aparecer aqui.
        </div>
    <?php endif; ?>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        <?php if (!$certificates): ?>
            <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-10 text-center text-slate-300 md:col-span-2 xl:col-span-3">Nenhum certificado disponível até o momento.</div>
        <?php endif; ?>

        <?php foreach ($certificates as $certificate): ?>
            <article class="overflow-hidden rounded-[1.75rem] border border-white/10 bg-white/5 shadow-soft backdrop-blur-xl">
                <div class="h-32 bg-[linear-gradient(135deg,_rgba(250,204,21,0.32),_rgba(59,130,246,0.25),_rgba(15,23,42,0.95))]"></div>
                <div class="space-y-4 p-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-100/70"><?= e($certificate['template_name'] ?? 'Template do curso') ?></p>
                        <h3 class="mt-2 text-2xl font-black tracking-tight text-white"><?= e($certificate['course_title']) ?></h3>
                    </div>
                    <div class="grid gap-3 text-sm text-slate-300">
                        <div class="flex items-center justify-between">
                            <span>Nota</span>
                            <strong class="text-white"><?= number_format((float) ($certificate['score_percent'] ?? 70), 0) ?>%</strong>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Carga horária</span>
                            <strong class="text-white"><?= (int) ($certificate['workload_hours'] ?? 0) ?>h</strong>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Emitido em</span>
                            <strong class="text-white"><?= !empty($certificate['issued_at']) ? date('d/m/Y', strtotime((string) $certificate['issued_at'])) : '--' ?></strong>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3 pt-2">
                        <a href="/elearning/colaborador/certificados/<?= e($certificate['validation_code']) ?>" class="rounded-full bg-white px-4 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Abrir certificado</a>
                        <span class="rounded-full border border-white/10 px-4 py-3 text-sm font-black text-white"><?= e($certificate['validation_code']) ?></span>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
