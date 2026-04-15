<?php
$data = $data ?? [];
$stats = $data['stats'] ?? [];
$courses = $data['courses'] ?? [];
$storage = $data['storage'] ?? [];
$templates = $data['templates'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);

$statCards = [
    [
        'label' => 'Cursos Cadastrados',
        'value' => $stats['total_courses'] ?? 0,
        'detail' => ($stats['published_courses'] ?? 0) . ' publicados',
        'icon' => 'ph-books',
    ],
    [
        'label' => 'Aulas Ativas',
        'value' => $stats['total_lessons'] ?? 0,
        'detail' => 'conteudos no ar',
        'icon' => 'ph-play-circle',
    ],
    [
        'label' => 'Alunos Matriculados',
        'value' => $stats['total_students'] ?? 0,
        'detail' => 'matriculas unicas',
        'icon' => 'ph-users-three',
    ],
    [
        'label' => 'Taxa de Aprovacao',
        'value' => number_format((float) ($stats['approval_rate'] ?? 0), 0) . '%',
        'detail' => 'nas provas enviadas',
        'icon' => 'ph-check-circle',
    ],
];
?>

<style>
/* Brutalist Premium Overrides */
.el-brut-container {
    background-color: #f7f7f7;
    color: #111;
    font-family: 'Inter', system-ui, sans-serif;
    padding: 2rem;
}
.el-brut-card {
    background: #fff;
    border: 2px solid #000;
    box-shadow: 6px 6px 0px #000;
    border-radius: 0;
    transition: all 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.el-brut-card:hover {
    transform: translate(-3px, -3px);
    box-shadow: 9px 9px 0px #000;
}
.el-brut-btn {
    background: #000;
    color: #fff;
    border: 2px solid #000;
    padding: 12px 24px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: -0.02em;
    cursor: pointer;
    position: relative;
    z-index: 1;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background 0.2s, color 0.2s;
}
.el-brut-btn:hover {
    background: #fff;
    color: #000;
}
.el-brut-btn-outline {
    background: transparent;
    color: #000;
    border: 2px solid #000;
    padding: 12px 24px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: -0.02em;
    cursor: pointer;
    box-shadow: 4px 4px 0px #000;
    transition: all 0.2s;
}
.el-brut-btn-outline:hover {
    transform: translate(2px, 2px);
    box-shadow: 2px 2px 0px #000;
}
.el-brut-title {
    font-size: 3.5rem;
    font-weight: 900;
    line-height: 1;
    letter-spacing: -0.04em;
    text-transform: uppercase;
    mix-blend-mode: exclusion;
    color: #000;
}
.el-grid-asym {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 2rem;
}
.el-badge {
    background: #eef2ff;
    border: 1px solid #000;
    font-weight: 800;
    font-size: 0.70rem;
    padding: 4px 8px;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}
</style>

<div class="el-brut-container min-h-screen relative overflow-hidden">
    
    <!-- Background abstract geometric shapes for depth -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-yellow-300 rounded-full mix-blend-multiply opacity-50 blur-2xl pointer-events-none"></div>
    <div class="absolute top-40 -left-20 w-72 h-72 bg-blue-300 rounded-full mix-blend-multiply opacity-50 blur-2xl pointer-events-none"></div>

    <?php if (!$schemaReady): ?>
        <div class="mb-8 border-l-8 border-red-500 bg-red-50 p-4 shadow-[4px_4px_0px_#000]">
            <p class="font-black text-red-900 uppercase">Atenção Crítica</p>
            <p class="text-sm text-red-800">O front do módulo já está disponível, mas o schema MariaDB ainda não foi aplicado neste ambiente. Funcionalidades podem falhar.</p>
        </div>
    <?php endif; ?>

    <!-- HERO SECTION -->
    <div class="el-grid-asym mb-12">
        <div class="col-span-12 lg:col-span-8 flex flex-col justify-center">
            <div class="inline-flex items-center gap-2 mb-4">
                <span class="w-3 h-3 bg-red-500 border border-black"></span>
                <span class="text-xs font-black uppercase tracking-widest">Painel de Liderança</span>
            </div>
            <h1 class="el-brut-title mb-6">Educação<br>Corporativa</h1>
            <div class="flex flex-wrap gap-4 mt-4">
                <a href="/elearning/gestor/cursos" class="el-brut-btn">
                    <i class="ph ph-books"></i> Gerenciar Cursos
                </a>
                <a href="/elearning/gestor/relatorios" class="el-brut-btn-outline">
                    <i class="ph ph-chart-line-up"></i> People Analytics
                </a>
            </div>
        </div>
        
        <div class="col-span-12 lg:col-span-4 relative flex items-center justify-center p-8">
            <!-- Decorative Element -->
            <div class="w-full aspect-square border-4 border-black border-dashed rounded-full flex items-center justify-center relative bg-gradient-to-tr from-gray-100 to-white spin-slow">
                <div class="absolute inset-4 bg-black rounded-full flex items-center justify-center text-white">
                    <div class="text-center">
                        <div class="text-5xl font-black mb-1"><?= $stats['total_courses'] ?? 0 ?></div>
                        <div class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Trilhas Inseridas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- METRICS STRIP -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-16 relative z-10">
        <?php foreach ($statCards as $i => $card): ?>
            <div class="el-brut-card p-6 flex flex-col justify-between <?= $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' ?>">
                <div class="flex justify-between items-start mb-8">
                    <div class="w-10 h-10 border-2 border-black flex items-center justify-center bg-yellow-300 text-black">
                        <i class="ph <?= e($card['icon']) ?> text-xl"></i>
                    </div>
                    <span class="el-badge">Métrica</span>
                </div>
                <div>
                    <div class="text-4xl font-black tracking-tighter mb-1"><?= e((string) $card['value']) ?></div>
                    <div class="font-bold text-sm text-gray-900 border-b-2 border-black inline-block pb-1 mb-1 uppercase"><?= e($card['label']) ?></div>
                    <div class="text-[10px] text-gray-500 uppercase font-bold tracking-widest"><?= e($card['detail']) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- MAIN CONTENT SPLIT -->
    <div class="el-grid-asym">
        <!-- RECENT COURSES -->
        <div class="col-span-12 lg:col-span-8 space-y-6">
            <div class="flex justify-between items-end mb-6 border-b-4 border-black pb-4">
                <h2 class="text-3xl font-black uppercase tracking-tighter">Trilhas Recentes</h2>
                <a href="/elearning/gestor/cursos" class="text-sm font-black hover:bg-black hover:text-white px-2 py-1 transition-colors border-2 border-transparent hover:border-black uppercase">Ver Todas &rarr;</a>
            </div>

            <?php if (!$courses): ?>
                <div class="border-4 border-dashed border-gray-300 p-12 text-center bg-gray-50">
                    <i class="ph ph-empty text-4xl text-gray-400 mb-2"></i>
                    <p class="font-bold text-gray-500 uppercase tracking-widest">Nenhuma trilha encontrada</p>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 gap-6">
                <?php foreach (array_slice($courses, 0, 4) as $course): ?>
                    <div class="el-brut-card flex gap-0 group relative overflow-hidden">
                        <div class="w-48 bg-black shrink-0 relative overflow-hidden border-r-2 border-black">
                            <img src="<?= e($course['cover_url']) ?>" alt="Capa" class="w-full h-full object-cover filter grayscale group-hover:grayscale-0 transition duration-500">
                        </div>
                        <div class="p-6 flex flex-col justify-between flex-grow bg-white relative">
                            <!-- Background decoration on hover -->
                            <div class="absolute inset-0 bg-blue-50 transform scale-x-0 origin-right transition-transform group-hover:scale-x-100 z-0 ease-out duration-300"></div>
                            
                            <div class="relative z-10">
                                <div class="flex gap-2 mb-3">
                                    <span class="el-badge bg-white"><?= e($course['status_label'] ?? 'Rascunho') ?></span>
                                    <span class="el-badge bg-black text-white"><?= e($course['category'] ?? 'Geral') ?></span>
                                </div>
                                <h3 class="text-2xl font-black uppercase tracking-tight leading-tight mb-2 group-hover:text-blue-600 transition-colors"><?= e($course['title']) ?></h3>
                                <p class="text-xs font-bold text-gray-600 uppercase tracking-widest">
                                    <?= (int) ($course['lessons_count'] ?? 0) ?> Aulas &bull; <?= (int) ($course['workload_hours'] ?? 0) ?>H &bull; <?= (int) ($course['enrollments_count'] ?? 0) ?> Alunos
                                </p>
                            </div>

                            <div class="relative z-10 mt-6 grid grid-cols-[1fr,auto] gap-4 items-end">
                                <div>
                                    <div class="flex justify-between text-[10px] font-black uppercase mb-1">
                                        <span>Aproveitamento Global</span>
                                        <span><?= number_format((float) ($course['avg_progress'] ?? 0), 0) ?>%</span>
                                    </div>
                                    <div class="h-4 border-2 border-black w-full bg-gray-100 relative overflow-hidden">
                                        <div class="h-full bg-blue-500 border-r-2 border-black transition-all duration-1000" style="width: <?= min(100, max(0, (float) ($course['avg_progress'] ?? 0))) ?>%"></div>
                                    </div>
                                </div>
                                <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/aulas" class="w-10 h-10 border-2 border-black rounded-full flex items-center justify-center bg-yellow-300 hover:bg-black hover:text-yellow-300 transition-colors tooltip cursor-pointer" title="Gerenciar Conteúdo">
                                    <i class="ph ph-arrow-right font-bold"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- SIDEBAR WIDGETS -->
        <div class="col-span-12 lg:col-span-4 space-y-8">
            
            <!-- Storage Widget -->
            <div class="el-brut-card p-6 bg-yellow-300 relative overflow-hidden">
                <div class="absolute -right-6 -bottom-6 w-32 h-32 border-4 border-black rounded-full opacity-20 pointer-events-none"></div>
                <div class="flex items-center gap-2 mb-4 relative z-10">
                    <i class="ph ph-hard-drives text-xl"></i>
                    <h3 class="font-black uppercase tracking-widest text-sm border-b-2 border-black pb-1">Storage Network</h3>
                </div>
                
                <div class="text-5xl font-black tracking-tighter mb-2 relative z-10"><?= e($storage['used_human'] ?? '0 min') ?></div>
                <p class="text-xs font-bold uppercase mb-4 relative z-10 pb-4 border-b border-black">De <?= e($storage['contracted_human'] ?? '10.000 min') ?> Contratados</p>
                
                <div class="relative z-10">
                    <div class="h-6 border-2 border-black bg-white w-full relative overflow-hidden">
                        <?php $warnClass = ($storage['percent_used'] ?? 0) > 80 ? 'bg-red-500' : 'bg-black'; ?>
                        <div class="h-full <?= $warnClass ?> border-r-2 border-black" style="width: <?= min(100, max(0, (float) ($storage['percent_used'] ?? 0))) ?>%"></div>
                    </div>
                </div>
                <button class="el-brut-btn bg-white text-black mt-4 w-full justify-center text-xs">Aumentar Limite</button>
            </div>

            <!-- Quick Links -->
            <div class="border-4 border-black p-6 bg-white shadow-[8px_8px_0px_#000]">
                <h3 class="font-black uppercase text-xl mb-6 flex items-center gap-2">
                    <span class="w-4 h-4 bg-black inline-block"></span> Hub de Controle
                </h3>
                
                <nav class="flex flex-col gap-3">
                    <a href="/elearning/gestor/cursos" class="group flex items-center justify-between p-3 border-2 border-gray-200 hover:border-black transition-colors font-bold uppercase text-xs">
                        <span class="flex items-center gap-2"><i class="ph ph-books group-hover:animate-bounce"></i> Catálogo Base</span>
                        <i class="ph ph-arrow-up-right opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </a>
                    
                    <a href="/elearning/gestor/diploma/config" class="group flex items-center justify-between p-3 border-2 border-gray-200 hover:border-black transition-colors font-bold uppercase text-xs">
                        <span class="flex items-center gap-2"><i class="ph ph-certificate group-hover:animate-bounce"></i> Gestor de Certificados</span>
                        <span class="bg-black text-white px-2 py-0.5 text-[10px]"><?= count($templates) ?> Ativos</span>
                    </a>
                    
                    <a href="/elearning/gestor/relatorios" class="group flex items-center justify-between p-3 border-2 border-gray-200 hover:border-black transition-colors font-bold uppercase text-xs bg-gray-50">
                        <span class="flex items-center gap-2 text-blue-600"><i class="ph ph-chart-polar group-hover:animate-bounce"></i> People Analytics</span>
                        <i class="ph ph-arrow-right text-blue-600"></i>
                    </a>
                </nav>
            </div>
            
            <a href="/inicio" class="block w-full border-2 border-black bg-gray-100 hover:bg-red-500 hover:text-white transition-colors p-4 text-center text-sm font-black uppercase tracking-widest cursor-pointer">
                Sair do Modulo Educacional &times;
            </a>

        </div>
    </div>
</div>
