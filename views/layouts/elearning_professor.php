<?php
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/elearning/gestor', PHP_URL_PATH) ?: '/elearning/gestor';
$navItems = [
    ['label' => 'Painel', 'href' => '/elearning/gestor', 'active' => ['/elearning/professor', '/elearning/gestor'], 'exact' => true],
    ['label' => 'Cursos', 'href' => '/elearning/gestor/cursos', 'active' => ['/elearning/gestor/cursos']],
    ['label' => 'Armazenamento', 'href' => '/elearning/gestor/armazenamento', 'active' => ['/elearning/gestor/armazenamento']],
    ['label' => 'Relatorios', 'href' => '/elearning/gestor/relatorios', 'active' => ['/elearning/gestor/relatorios']],
];

$isActiveNav = static function (array $item) use ($requestPath): bool {
    if (!empty($item['exact'])) {
        return in_array($requestPath, $item['active'], true);
    }

    foreach ($item['active'] as $activePath) {
        if ($requestPath === $activePath || str_starts_with($requestPath, $activePath . '/')) {
            return true;
        }
    }

    return false;
};
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'E-Learning Professor') ?></title>
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
                        soft: '0 24px 80px -38px rgba(15, 23, 42, 0.6)',
                    },
                },
            },
        };
    </script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .el-noise::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: .16;
            background-image:
                radial-gradient(circle at 12% 18%, rgba(56, 189, 248, .14), transparent 24%),
                radial-gradient(circle at 82% 14%, rgba(45, 212, 191, .12), transparent 22%),
                radial-gradient(circle at 50% 82%, rgba(148, 163, 184, .08), transparent 28%);
        }
    </style>
</head>
<body class="el-noise min-h-screen bg-[#020617] text-slate-100 antialiased">
    <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(15,23,42,0.96),_rgba(2,6,23,1)_42%),radial-gradient(circle_at_top_right,_rgba(8,47,73,0.45),_transparent_24%),linear-gradient(180deg,_#020617,_#020617)]">
        <header class="sticky top-0 z-40 border-b border-white/10 bg-slate-950/75 backdrop-blur-2xl">
            <div class="mx-auto flex max-w-[92rem] flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <a href="/elearning/gestor" class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_#dbeafe,_#67e8f9_55%,_#99f6e4)] text-slate-950 shadow-soft">
                        <i class="ph-fill ph-chalkboard-teacher text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.34em] text-slate-400">SGI</p>
                        <h1 class="text-lg font-black tracking-tight text-white">E-Learning Professor</h1>
                    </div>
                </a>

                <nav class="flex flex-wrap items-center gap-2 rounded-full border border-white/10 bg-white/[0.04] p-2">
                    <?php foreach ($navItems as $item): ?>
                        <?php $active = $isActiveNav($item); ?>
                        <a href="<?= e($item['href']) ?>" class="rounded-full px-4 py-2 text-sm font-semibold transition <?= $active ? 'bg-white text-slate-950 shadow-soft' : 'text-slate-200 hover:bg-white/10 hover:text-white' ?>">
                            <?= e($item['label']) ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="/inicio" class="rounded-full border border-white/10 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">Voltar ao SGI</a>
                </nav>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-200/70">Professor</p>
                        <p class="text-sm font-bold text-white"><?= e($_SESSION['user_name'] ?? 'Usuario') ?></p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-sm font-black text-white">
                        <?= strtoupper(substr($_SESSION['user_name'] ?? 'P', 0, 1)) ?>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-[92rem] px-4 py-8 sm:px-6 lg:px-8">
            <?php include $viewFile; ?>
        </main>
    </div>

    <div id="professor-toast" class="pointer-events-none fixed bottom-5 right-5 z-[90] translate-y-6 opacity-0 transition duration-300">
        <div id="professor-toast-box" class="rounded-2xl border border-white/10 bg-slate-900/95 px-5 py-4 text-sm font-semibold text-white shadow-soft backdrop-blur-xl"></div>
    </div>

    <script>
        window.showProfessorToast = function (message, type = 'info') {
            const toast = document.getElementById('professor-toast');
            const box = document.getElementById('professor-toast-box');
            const tone = {
                info: 'border-cyan-400/30 text-cyan-100',
                success: 'border-emerald-400/30 text-emerald-100',
                error: 'border-rose-400/30 text-rose-100',
            };

            if (!toast || !box) {
                return;
            }

            box.className = 'rounded-2xl border bg-slate-900/95 px-5 py-4 text-sm font-semibold shadow-soft backdrop-blur-xl ' + (tone[type] || tone.info);
            box.textContent = message;
            toast.classList.remove('translate-y-6', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
            clearTimeout(window.__professorToastTimeout);
            window.__professorToastTimeout = setTimeout(() => {
                toast.classList.add('translate-y-6', 'opacity-0');
                toast.classList.remove('translate-y-0', 'opacity-100');
            }, 3200);
        };
    </script>
</body>
</html>
