<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'E-Learning Aluno') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    boxShadow: {
                        soft: '0 20px 60px -28px rgba(15, 23, 42, 0.35)',
                    },
                },
            },
        };
    </script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .grain-bg::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: .12;
            background-image:
                radial-gradient(circle at 15% 25%, rgba(37,99,235,.18) 0, transparent 28%),
                radial-gradient(circle at 85% 15%, rgba(16,185,129,.16) 0, transparent 22%),
                radial-gradient(circle at 50% 80%, rgba(14,165,233,.12) 0, transparent 24%);
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 grain-bg antialiased min-h-screen">
    <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(37,99,235,0.18),_transparent_40%),linear-gradient(180deg,_rgba(15,23,42,1)_0%,_rgba(2,6,23,1)_100%)]">
        <header class="sticky top-0 z-40 border-b border-white/10 bg-slate-950/80 backdrop-blur-xl">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <a href="/elearning/colaborador" class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 via-sky-400 to-emerald-400 text-slate-950 shadow-soft">
                        <i class="ph-fill ph-graduation-cap text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-sky-200/70">SGI</p>
                        <h1 class="text-lg font-black tracking-tight text-white">E-Learning Aluno</h1>
                    </div>
                </a>

                <nav class="hidden items-center gap-2 rounded-full border border-white/10 bg-white/5 px-2 py-2 md:flex">
                    <a href="/elearning/colaborador" class="rounded-full px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/10">Dashboard</a>
                    <a href="/elearning/colaborador/certificados" class="rounded-full px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/10">Certificados</a>
                    <a href="/elearning/colaborador/historico" class="rounded-full px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/10">Histórico</a>
                    <a href="/inicio" class="rounded-full px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/10">Voltar ao SGI</a>
                </nav>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-emerald-200/70">Aluno</p>
                        <p class="text-sm font-bold text-white"><?= e($_SESSION['user_name'] ?? 'Usuário') ?></p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-sm font-black text-white">
                        <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <?php include $viewFile; ?>
        </main>
    </div>

    <div id="el-toast" class="pointer-events-none fixed bottom-5 right-5 z-[80] translate-y-6 opacity-0 transition duration-300">
        <div id="el-toast-box" class="rounded-2xl border border-white/10 bg-slate-900/95 px-5 py-4 text-sm font-semibold text-white shadow-soft backdrop-blur-xl"></div>
    </div>

    <script>
        window.showELToast = function (message, type = 'info') {
            const toast = document.getElementById('el-toast');
            const box = document.getElementById('el-toast-box');
            const tone = {
                info: 'border-sky-400/30 text-sky-100',
                success: 'border-emerald-400/30 text-emerald-100',
                error: 'border-rose-400/30 text-rose-100',
            };

            box.className = 'rounded-2xl border bg-slate-900/95 px-5 py-4 text-sm font-semibold shadow-soft backdrop-blur-xl ' + (tone[type] || tone.info);
            box.textContent = message;
            toast.classList.remove('translate-y-6', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
            clearTimeout(window.__elToastTimeout);
            window.__elToastTimeout = setTimeout(() => {
                toast.classList.add('translate-y-6', 'opacity-0');
                toast.classList.remove('translate-y-0', 'opacity-100');
            }, 3200);
        };
    </script>
</body>
</html>
