<?php
$schemaReady = (bool) ($data['schema_ready'] ?? false);
$navigationItems = [
    [
        'title' => 'Gerenciar cursos',
        'description' => 'Cadastre cursos, organize capas, aulas e publicacoes.',
        'href' => '/elearning/gestor/cursos',
        'icon' => 'ph-books',
    ],
    [
        'title' => 'Painel de armazenamento',
        'description' => 'Acompanhe capacidade, consumo e distribuicao dos videos.',
        'href' => '/elearning/gestor/armazenamento',
        'icon' => 'ph-hard-drives',
    ],
    [
        'title' => 'Relatorios',
        'description' => 'Veja progresso, aprovacoes e leitura geral dos cursos.',
        'href' => '/elearning/gestor/relatorios',
        'icon' => 'ph-chart-line-up',
    ],
    [
        'title' => 'Biblioteca de certificados',
        'description' => 'Ajuste modelos e configuracoes de emissao por curso.',
        'href' => '/elearning/gestor/diploma/config',
        'icon' => 'ph-certificate',
    ],
];
?>

<section class="space-y-6">
    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-[linear-gradient(135deg,_#0f172a,_#1e3a8a_58%,_#0f766e)] text-white shadow-2xl">
        <div class="relative p-8 sm:p-10">
            <div class="absolute inset-y-0 right-0 hidden w-1/3 bg-[radial-gradient(circle_at_center,_rgba(255,255,255,0.18),_transparent_65%)] lg:block"></div>

            <div class="relative max-w-5xl space-y-6">
                <div class="inline-flex items-center gap-3 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-[11px] font-black uppercase tracking-[0.32em] text-sky-50/85">
                    <span class="flex h-2.5 w-2.5 rounded-full bg-emerald-300"></span>
                    E-Learning Professor
                </div>

                <div class="space-y-3">
                    <h1 class="max-w-3xl text-3xl font-black tracking-tight text-white sm:text-4xl xl:text-[3.25rem]">
                        Central do professor
                    </h1>
                    <p class="max-w-2xl text-sm leading-relaxed text-sky-50/80 sm:text-base">
                        Um painel mais direto para navegar pelo modulo. Escolha a area que deseja abrir e siga o fluxo sem excesso de informacao na tela inicial.
                    </p>
                </div>

                <nav class="grid gap-3 lg:grid-cols-2">
                    <?php foreach ($navigationItems as $item): ?>
                        <a
                            href="<?= e($item['href']) ?>"
                            class="group flex items-start gap-4 rounded-[1.5rem] border border-white/12 bg-white/10 px-5 py-5 transition duration-200 hover:bg-white/14"
                        >
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/12 text-white">
                                <i class="ph <?= e($item['icon']) ?> text-2xl"></i>
                            </span>
                            <span class="min-w-0 space-y-1">
                                <span class="block text-base font-black text-white"><?= e($item['title']) ?></span>
                                <span class="block text-sm leading-relaxed text-sky-50/75"><?= e($item['description']) ?></span>
                            </span>
                            <span class="ml-auto hidden pt-1 text-sky-100/60 transition group-hover:text-white sm:block">
                                <i class="ph ph-arrow-up-right text-xl"></i>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <div class="flex flex-wrap gap-3 text-xs font-semibold uppercase tracking-[0.24em] text-sky-100/70">
                    <span class="rounded-full border border-white/12 bg-white/8 px-4 py-2">Cursos</span>
                    <span class="rounded-full border border-white/12 bg-white/8 px-4 py-2">Aulas</span>
                    <span class="rounded-full border border-white/12 bg-white/8 px-4 py-2">Relatorios</span>
                    <span class="rounded-full border border-white/12 bg-white/8 px-4 py-2">Certificados</span>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$schemaReady): ?>
        <div class="rounded-[1.75rem] border border-amber-300 bg-amber-50 px-5 py-4 text-sm leading-relaxed text-amber-950">
            O front do modulo ja esta disponivel, mas o schema MariaDB ainda nao foi aplicado neste ambiente.
        </div>
    <?php endif; ?>
</section>
