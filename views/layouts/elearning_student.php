<?php
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/elearning/colaborador', PHP_URL_PATH) ?: '/elearning/colaborador';
$navItems = [
    ['label' => 'Inicio', 'href' => '/elearning/colaborador', 'active' => ['/elearning/colaborador'], 'exact' => true],
    ['label' => 'Certificados', 'href' => '/elearning/colaborador/certificados', 'active' => ['/elearning/colaborador/certificados']],
    ['label' => 'Historico', 'href' => '/elearning/colaborador/historico', 'active' => ['/elearning/colaborador/historico']],
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

$elearningStylePath = __DIR__ . '/../../public/assets/elearning-modern.css';
$elearningAssetVersion = file_exists($elearningStylePath) ? filemtime($elearningStylePath) : time();
?>
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
    <link rel="stylesheet" href="/assets/elearning-modern.css?v=<?= urlencode($elearningAssetVersion) ?>">
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
        body { font-family: 'Outfit', sans-serif; background: #f5f5f7; }
    </style>
</head>
<body class="min-h-screen bg-[#f5f5f7] text-slate-900 antialiased">
    <div class="min-h-screen bg-[#f5f5f7]">
        <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/85 backdrop-blur-2xl">
            <div class="mx-auto flex max-w-[92rem] flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <a href="/elearning/colaborador" class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[#e8f2ff] text-[#007aff] shadow-sm">
                        <i class="ph-fill ph-graduation-cap text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500">SGI</p>
                        <h1 class="text-lg font-black text-slate-950">E-Learning Aluno</h1>
                    </div>
                </a>

                <nav class="flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 bg-slate-100 p-1.5">
                    <?php foreach ($navItems as $item): ?>
                        <?php $active = $isActiveNav($item); ?>
                        <a href="<?= e($item['href']) ?>" class="rounded-lg px-4 py-2 text-sm font-semibold transition <?= $active ? 'bg-white text-slate-950 shadow-sm' : 'text-slate-600 hover:bg-white hover:text-slate-950' ?>">
                            <?= e($item['label']) ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="/inicio" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:text-slate-950">Voltar ao SGI</a>
                </nav>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-xs font-semibold text-slate-500">Aluno</p>
                        <p class="text-sm font-bold text-slate-950"><?= e($_SESSION['user_name'] ?? 'Usuario') ?></p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg border border-slate-200 bg-white text-sm font-black text-slate-900 shadow-sm">
                        <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-[92rem] px-4 py-6 sm:px-6 lg:px-8">
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
                info: 'border-cyan-400/30 text-cyan-100',
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
