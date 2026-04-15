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
        'icon' => '📚',
        'color' => 'primary',
    ],
    [
        'label' => 'Aulas Ativas',
        'value' => $stats['total_lessons'] ?? 0,
        'detail' => 'conteudos no ar',
        'icon' => '▶️',
        'color' => 'secondary',
    ],
    [
        'label' => 'Alunos Matriculados',
        'value' => $stats['total_students'] ?? 0,
        'detail' => 'matriculas unicas',
        'icon' => '👥',
        'color' => 'success',
    ],
    [
        'label' => 'Taxa de Aprovacao',
        'value' => number_format((float) ($stats['approval_rate'] ?? 0), 0) . '%',
        'detail' => 'nas provas enviadas',
        'icon' => '✓',
        'color' => 'primary',
    ],
];
?>

<link rel="stylesheet" href="/assets/elearning-modern.css?v=<?= time() ?>">
<style>
.el-dashboard {}
.el-dashboard-hero {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #7c3aed 100%);
    border-radius: 1.25rem;
    padding: 2.5rem;
    color: white;
    margin-bottom: 2.5rem;
    position: relative;
    overflow: hidden;
}
.el-dashboard-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(255,255,255,0.1), transparent);
    border-radius: 50%;
}
.el-dashboard-hero > * {
    position: relative;
    z-index: 1;
}
.el-dashboard-hero h1 {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
}
.el-dashboard-hero p {
    font-size: 1.125rem;
    opacity: 0.95;
    margin-bottom: 1.5rem;
}
.el-stat-badge {
    display: inline-block;
    background: rgba(255,255,255, 0.15);
    border: 1px solid rgba(255,255,255, 0.3);
    color: white;
    padding: 0.375rem 1rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.el-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}
.el-alert-premium {
    background: linear-gradient(135deg, #fef08a 0%, #fde047 100%);
    border: 1px solid #facc15;
    border-radius: 1rem;
    padding: 1.25rem;
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    margin-bottom: 2rem;
}
.el-alert-premium-icon {
    font-size: 1.75rem;
    flex-shrink: 0;
}
.el-alert-premium-content {
    flex: 1;
}
.el-alert-premium-title {
    font-weight: 700;
    color: #78350f;
    margin-bottom: 0.25rem;
}
.el-alert-premium-text {
    font-size: 0.875rem;
    color: #92400e;
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
    <div class="el-stat-grid">
        <?php foreach ($statCards as $card): ?>
            <div class="el-stat-card">
                <div class="el-stat-icon">
                    <?= $card['icon'] ?>
                </div>
                <div class="el-stat-value"><?= e((string) $card['value']) ?></div>
                <div class="el-stat-label"><?= e($card['label']) ?></div>
                <div class="el-stat-detail"><?= e($card['detail']) ?></div>
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

            <div class-->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- PRIMARY SECTION: RECENT COURSES -->
        <div class="lg:col-span-2">
            <div class="flex items-center justify-between mb-6">
                <h2 class="el-h2">Cursos Recentes</h2>
                <a href="/elearning/gestor/cursos" class="el-text-sm text-primary-600 hover:text-primary-700 font-semibold">Ver todos →</a>
            </div>

            <?php if (!$courses): ?>
                <div class="el-card text-center py-12">
                    <div class="text-4xl mb-3">📚</div>
                    <p class="el-text-sm text-muted">Nenhum curso cadastrado ainda</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach (array_slice($courses, 0, 6) as $course): ?>
                        <div class="el-course-card">
                            <div class="el-course-image">
                                <?php if ($course['cover_url'] && file_exists(str_replace('http://', '', str_replace('https://', '', $course['cover_url'])))): ?>
                                    <img src="<?= e($course['cover_url']) ?>" alt="<?= e($course['title']) ?>">
                                <?php else: ?>
                                    <div style="font-size: 3rem; background: linear-gradient(135deg, #0ea5e9, #7c3aed); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: white;">📖</div>
                                <?php endif; ?>
                            </div>
                            <div class="el-course-content">
                                <div class="flex gap-2 mb-2">
                                    <span class="el-badge el-badge-primary" style="font-size: 0.7rem;">
                                        <?= e($course['status_label'] ?? 'Rascunho') ?>
                                    </span>
                                    <span class="el-badge el-badge-success" style="font-size: 0.7rem;">
                                        <?= e($course['category'] ?? 'Geral') ?>
                                    </span>
                                </div>
                                <h3 class="el-course-title"><?= e($course['title']) ?></h3>
                                <p class="el-course-teacher">
                                    📌 <?= e($course['teacher_name'] ?? 'A definir') %>
                                </p>
                                <div class="el-course-stats">
                                    <div class="el-course-stat">🎓 <?= (int) ($course['lessons_count'] ?? 0) ?> aulas</div>
                                    <div class="el-course-stat">⏱ <?= (int) ($course['workload_hours'] ?? 0) ?>h</div>
                                    <div class="el-course-stat">👥 <?= (int) ($course['enrollments_count'] ?? 0) ?> alunos</div>
                                </div>
                                <div class="el-course-progress">
                                    <div class="el-course-progress-label">
                                        <span>Progresso médio</span>
                                        <span><?= number_format((float) ($course['avg_progress'] ?? 0), 0) ?>%</span>
                                    </div>
                                    <div class="el-progress-container">
                                        <div class="el-progress-bar" style="width: <?= min(100, max(0, (float) ($course['avg_progress'] ?? 0))) ?>%;"></div>
                                    </div>
                                </div>
                                <div class="el-course-actions">
                                    <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/aulas" class="el-btn el-btn-sm el-btn-primary">Aulas</a>
                                    <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/provas" class="el-btn el-btn-sm el-btn-secondary">Provas</a>
                                    <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/matriculas" class="el-btn el-btn-sm el-btn-secondary">Alunos</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- SIDEBAR: WIDGETS -->
        <div class="space-y-6">
            <!-- Storage Widget -->
            <div class="el-card">
                <div class="flex items-center gap-3 mb-4">
                    <span style="font-size: 1.5rem;">💾</span>
                    <div>
                        <p class="el-text-xs">ARMAZENAMENTO</p>
                        <h3 class="el-h4">SGI Stream</h3>
                    </div>
                </div>
                <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--slate-200);">
                    <div class="el-stat-value" style="font-size: 2rem; color: var(--primary-600);"><?= e($storage['used_human'] ?? '0 min') ?></div>
                    <p class="el-text-sm">de <?= e($storage['contracted_human'] ?? '10.000 min') ?></p>
                </div>
                <div class="el-progress-container" style="margin-bottom: 1rem;">
                    <?php 
                        $percent = (float) ($storage['percent_used'] ?? 0);
                        $barClass = $percent > 90 ? 'error' : ($percent > 80 ? 'warning' : 'success');
                    ?>
                    <div class="el-progress-bar <?= $barClass ?>" style="width: <?= min(100, $percent) ?>%;"></div>
                </div>
                <p class="el-text-sm">
                    <strong><?= e($storage['available_human'] ?? '0 min') ?></strong> disponíveis
                </p>
            </div>

            <!-- Quick Actions -->
            <div class="el-card">
                <p class="el-text-xs mb-3">ações rápidas</p>
                <h3 class="el-h4 mb-4">Hub de Controle</h3>
                <nav class="space-y-2 flex flex-col">
                    <a href="/elearning/gestor/cursos" class="el-btn el-btn-secondary el-btn-block justify-center">
                        📚 Meus Cursos
                    </a>
                    <a href="/elearning/gestor/diploma/config" class="el-btn el-btn-secondary el-btn-block justify-center">
                        🎓 Certificados (<?= count($templates) ?>)
                    </a>
                    <a href="/elearning/gestor/relatorios" class="el-btn el-btn-secondary el-btn-block justify-center">
                        📊 Relatórios
                    </a>
                    <a href="/elearning/gestor/armazenamento" class="el-btn el-btn-secondary el-btn-block justify-center">
                        🔧 Armazenamento
                    </a>
                </nav>
            </div>

            <!-- Governance Card -->
            <div class="el-card" style="background: linear-gradient(135deg, var(--primary-50) 0%, rgba(124, 58, 237, 0.05) 100%); border-color: var(--primary-200);">
                <p class="el-text-xs mb-2">regras</p>
                <h3 class="el-h4 mb-3">Governança</h3>
                <ul class="el-text-sm space-y-2" style="color: var(--slate-600);">
                    <li>✓ 1 vídeo MP4 por aula (80 MB)</li>
                    <li>✓ Anexos até 20 MB</li>
                    <li>✓ Nota mínima: 70%</li>
                    <li>✓ Limite global: 10.000 min</li>
                </ul>
            </div>

            <a href="/inicio" class="el-btn el-btn-danger el-btn-block justify-center">
                ✕ Sair do Módulo
            </a>