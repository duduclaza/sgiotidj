<?php
$courses = $data['courses'] ?? [];
$storage = $data['storage'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);
?>

<style>
.analytics-container {
    background-color: #f7f7f7;
    color: #000;
    font-family: 'Inter', system-ui, sans-serif;
    padding: 2rem;
}
.analytics-card {
    background: #fff;
    border: 3px solid #000;
    box-shadow: 6px 6px 0px #000;
    padding: 1.5rem;
    transition: transform 0.2s;
}
.analytics-card:hover {
    transform: translate(-2px, -2px);
    box-shadow: 8px 8px 0px #000;
}
.analytics-badge {
    background: #fff;
    color: #000;
    border: 2px solid #000;
    font-weight: 900;
    text-transform: uppercase;
    font-size: 10px;
    padding: 4px 8px;
    display: inline-block;
    box-shadow: 2px 2px 0px #000;
}
.analytics-badge.dark {
    background: #000;
    color: #fff;
}
.data-block {
    border-bottom: 2px solid #000;
    padding-bottom: 1rem;
    margin-bottom: 1rem;
}
.data-block:last-child {
    border-bottom: none;
    padding-bottom: 0;
    margin-bottom: 0;
}
.progress-bar-container {
    border: 2px solid #000;
    height: 1.5rem;
    background: #fff;
    width: 100%;
    position: relative;
    overflow: hidden;
}
.progress-bar-fill {
    height: 100%;
    background: #000;
    border-right: 2px solid #000;
}
</style>

<div class="analytics-container min-h-screen">

    <!-- HERO HEADER -->
    <div class="border-4 border-black bg-white p-8 mb-12 relative overflow-hidden shadow-[12px_12px_0px_#000]">
        <!-- Abstract Decoration -->
        <div class="absolute -top-20 -right-20 w-64 h-64 rounded-full border-8 border-yellow-300 opacity-50"></div>
        <div class="absolute -bottom-10 right-20 w-32 h-32 bg-blue-500 transform rotate-45 border-4 border-black"></div>
        
        <div class="relative z-10 w-full lg:w-2/3">
            <div class="analytics-badge mb-4">Módulo Independente</div>
            <h1 class="text-5xl lg:text-7xl font-black uppercase tracking-tighter leading-none mb-4">People<br>Analytics</h1>
            <p class="text-lg font-bold text-gray-700 max-w-2xl border-l-4 border-black pl-4">Acompanhamento avançado de desempenho acadêmico, retenção e impacto das jornadas educacionais sobre os colaboradores corporativos.</p>
        </div>
    </div>

    <?php if (!$schemaReady): ?>
        <div class="mb-8 border-4 border-red-500 bg-red-100 p-6 shadow-[6px_6px_0px_#ef4444]">
            <p class="font-black text-red-900 border-b-2 border-red-500 pb-2 mb-2 uppercase text-xl">Integração Pendente</p>
            <p class="text-red-900 font-bold">Os indicadores abaixo são estruturais. O banco de dados (schema E-Learning) ainda requer atualização no ambiente atual para exibir dados vivos.</p>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-10">
        
        <!-- MAIN DATA COLUMN -->
        <div class="xl:col-span-8 space-y-10">
            
            <div class="flex items-center justify-between border-b-4 border-black pb-2">
                <h2 class="text-2xl font-black uppercase tracking-tighter">Performance por Trilha</h2>
                <div class="analytics-badge dark"><?= count($courses) ?> Mapeadas</div>
            </div>

            <?php if (!$courses): ?>
                <div class="analytics-card text-center py-16 bg-gray-100">
                    <i class="ph ph-database text-6xl mb-4"></i>
                    <p class="font-black text-xl uppercase tracking-widest">Sem base de dados geométrica</p>
                    <p class="font-bold text-gray-500 mt-2">Nenhum curso ou aluno associado disponível para extração de kpi's.</p>
                </div>
            <?php endif; ?>

            <div class="grid gap-6">
                <?php foreach ($courses as $course): ?>
                    <div class="analytics-card flex flex-col md:flex-row gap-6">
                        
                        <!-- Left: Info -->
                        <div class="flex-1 border-r-0 md:border-r-2 border-black md:pr-6">
                            <div class="flex gap-2 mb-4">
                                <span class="analytics-badge"><?= e($course['status_label'] ?? 'Rascunho') ?></span>
                                <span class="analytics-badge bg-yellow-300 text-black border-none"><?= e($course['category'] ?? 'Geral') ?></span>
                            </div>
                            <h3 class="text-2xl font-black uppercase tracking-tight leading-tight mb-2"><?= e($course['title']) ?></h3>
                            
                            <div class="grid grid-cols-3 gap-2 mt-6">
                                <div class="bg-gray-100 border-2 border-black p-2 text-center">
                                    <div class="text-xl font-black"><?= (int) ($course['enrollments_count'] ?? 0) ?></div>
                                    <div class="text-[9px] font-bold uppercase">Matrículas</div>
                                </div>
                                <div class="bg-gray-100 border-2 border-black p-2 text-center">
                                    <div class="text-xl font-black"><?= (int) ($course['completed_count'] ?? 0) ?></div>
                                    <div class="text-[9px] font-bold uppercase">Concluídos</div>
                                </div>
                                <div class="bg-yellow-100 border-2 border-black p-2 text-center">
                                    <div class="text-xl font-black"><?= (int) ($course['certificates_count'] ?? 0) ?></div>
                                    <div class="text-[9px] font-bold uppercase">Certificados</div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: KPIs -->
                        <div class="w-full md:w-64 flex flex-col justify-center">
                            
                            <div class="data-block">
                                <div class="flex justify-between items-end mb-1">
                                    <span class="text-[10px] font-black uppercase tracking-widest">Andamento</span>
                                    <span class="font-black text-xl"><?= number_format((float) ($course['avg_progress'] ?? 0), 0) ?>%</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar-fill bg-blue-500" style="width: <?= min(100, max(0, (float) ($course['avg_progress'] ?? 0))) ?>%"></div>
                                </div>
                            </div>

                            <div class="data-block border-none !mb-0 !pb-0">
                                <div class="flex justify-between items-end mb-1">
                                    <span class="text-[10px] font-black uppercase tracking-widest">Aprovação</span>
                                    <span class="font-black text-xl text-green-600"><?= number_format((float) ($course['approval_rate'] ?? 0), 0) ?>%</span>
                                </div>
                                <div class="progress-bar-container bg-red-100">
                                    <div class="progress-bar-fill bg-green-500" style="width: <?= min(100, max(0, (float) ($course['approval_rate'] ?? 0))) ?>%"></div>
                                </div>
                                <div class="flex justify-between text-[10px] uppercase font-bold mt-2 text-gray-500">
                                    <span>GAPs Críticos</span>
                                    <span class="text-red-500"><?= (int) ($course['failed_count'] ?? 0) ?> reprovas</span>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- RIGHT SIDEBAR (STORAGE & ALERTS) -->
        <div class="xl:col-span-4 space-y-8">
            
            <div class="analytics-card bg-black text-white relative">
                <i class="ph-fill ph-hard-drives text-6xl absolute -right-4 -bottom-4 text-gray-800 pointer-events-none"></i>
                <div class="border-b-2 border-gray-700 pb-2 mb-6">
                    <h3 class="font-black uppercase tracking-widest">Server Load</h3>
                </div>
                
                <div class="text-6xl font-black tracking-tighter mb-2"><?= e($storage['used_human'] ?? '0 m') ?></div>
                <p class="font-bold text-gray-400 mb-8 border-b-2 border-gray-800 pb-4">De <?= e($storage['contracted_human'] ?? '10k min') ?> / Vídeo Storage</p>

                <?php 
                    $lvl = $storage['alert_level'] ?? 'healthy';
                    $barColor = $lvl === 'critical' ? 'bg-red-500' : ($lvl === 'warning' ? 'bg-yellow-500' : 'bg-green-500');
                ?>
                <div class="progress-bar-container border-gray-700 bg-gray-900 mb-2">
                    <div class="progress-bar-fill border-gray-700 <?= $barColor ?>" style="width: <?= min(100, max(0, (float) ($storage['percent_used'] ?? 0))) ?>%"></div>
                </div>
                <div class="text-right text-[10px] font-black uppercase text-gray-500">Disponível: <?= e($storage['available_human'] ?? '0 min') ?></div>
            </div>

            <div class="analytics-card bg-yellow-300">
                <h3 class="font-black uppercase text-xl border-b-4 border-black pb-2 mb-4">Decisões RH</h3>
                <ul class="space-y-4 font-bold text-sm">
                    <li class="flex gap-2 items-start"><i class="ph-fill ph-warning-circle text-red-500 mt-0.5"></i> <span class="leading-tight">Atenção imediata para trilhas com taxa de reprovação acima de 30%.</span></li>
                    <li class="flex gap-2 items-start"><i class="ph-fill ph-check-circle text-green-600 mt-0.5"></i> <span class="leading-tight">Valide se os módulos obrigatórios estão bloqueando progressão de cargos.</span></li>
                    <li class="flex gap-2 items-start"><i class="ph-fill ph-info text-blue-600 mt-0.5"></i> <span class="leading-tight">Storage: Avalie purgar turmas arquivadas se o consumo ultrapassar 80%.</span></li>
                </ul>
            </div>

            <a href="/inicio" class="block w-full border-4 border-black bg-white hover:bg-black hover:text-white transition-colors p-4 text-center font-black uppercase tracking-widest cursor-pointer shadow-[6px_6px_0px_#000]">
                Retroceder ao HUB Geral
            </a>

        </div>
    </div>
</div>
