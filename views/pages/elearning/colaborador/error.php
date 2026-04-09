<section class="mx-auto max-w-2xl rounded-[2rem] border border-white/10 bg-white/5 p-10 text-center shadow-soft backdrop-blur-xl">
    <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-rose-500/15 text-rose-200">
        <i class="ph-fill ph-warning-circle text-4xl"></i>
    </div>
    <h2 class="text-3xl font-black tracking-tight text-white">Não foi possível abrir este conteúdo</h2>
    <p class="mt-4 text-base leading-relaxed text-slate-300"><?= e($message ?? 'Tente novamente em alguns instantes.') ?></p>
    <div class="mt-8 flex flex-wrap justify-center gap-3">
        <a href="/elearning/colaborador" class="rounded-full bg-white px-5 py-3 text-sm font-bold text-slate-900 transition hover:scale-[1.02]">Voltar ao dashboard</a>
        <a href="/inicio" class="rounded-full border border-white/20 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">Ir para o SGI</a>
    </div>
</section>
