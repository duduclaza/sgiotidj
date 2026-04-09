<?php
$courses = $data['courses'] ?? [];
?>

<section class="space-y-8">
    <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 p-8 shadow-soft backdrop-blur-xl">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-300/70">Linha do tempo</p>
        <h2 class="mt-4 text-4xl font-black tracking-tight text-white">Histórico de cursos</h2>
        <p class="mt-3 max-w-2xl text-base leading-relaxed text-slate-300">Acompanhe cursos concluídos, trilhas reprovadas e o progresso acumulado da sua jornada dentro do módulo E-Learning.</p>
    </div>

    <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-soft backdrop-blur-xl">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left">
                <thead class="border-b border-white/10 bg-slate-950/35">
                    <tr class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">
                        <th class="px-6 py-4">Curso</th>
                        <th class="px-6 py-4">Categoria</th>
                        <th class="px-6 py-4">Carga horária</th>
                        <th class="px-6 py-4">Progresso</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Conclusão</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-sm text-slate-200">
                    <?php if (!$courses): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-300">Seu histórico será preenchido conforme você avançar nos cursos do módulo.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($courses as $course): ?>
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4 font-bold text-white"><?= e($course['title']) ?></td>
                            <td class="px-6 py-4"><?= e($course['category'] ?? 'Geral') ?></td>
                            <td class="px-6 py-4"><?= (int) ($course['workload_hours'] ?? 0) ?>h</td>
                            <td class="px-6 py-4"><?= number_format((float) ($course['progress_percent'] ?? 0), 0) ?>%</td>
                            <td class="px-6 py-4"><?= e((string) ($course['status'] ?? 'in_progress')) ?></td>
                            <td class="px-6 py-4"><?= !empty($course['completed_at']) ? date('d/m/Y', strtotime((string) $course['completed_at'])) : '--' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
