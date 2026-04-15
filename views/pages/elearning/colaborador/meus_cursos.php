<?php
// views/pages/elearning/colaborador/meus_cursos.php
$emAndamento = array_filter($cursos ?? [], fn($c) => !empty($c['matricula_id']) && $c['matricula_status'] === 'em_andamento');
$concluidos = array_filter($cursos ?? [], fn($c) => !empty($c['matricula_id']) && $c['matricula_status'] === 'concluido');
$disponiveis = array_filter($cursos ?? [], fn($c) => empty($c['matricula_id']));
?>
<link rel="stylesheet" href="/assets/elearning-modern.css?v=<?= time() ?>">
<style>
    .el-continue-card {
        background: white;
        border: 1px solid var(--slate-200);
        border-radius: 1.25rem;
        overflow: hidden;
        display: flex;
        transition: all 0.25s ease-in-out;
    }
    .el-continue-card:hover {
        border-color: var(--primary-300);
        box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.15);
        transform: translateY(-2px);
    }
    .el-continue-image {
        width: 150px;
        height: auto;
        min-height: 100%;
        background: linear-gradient(135deg, var(--primary-200), var(--primary-300));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        flex-shrink: 0;
        overflow: hidden;
    }
    .el-continue-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .el-continue-content {
        padding: 1.25rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
</style>

<div class="space-y-8">

    <!-- HERO SECTION -->
    <div class="el-hero">
        <span class="el-stat-badge mb-4">Bem-vindo de volta!</span>
        <h1>O que você quer aprender hoje?</h1>
        <p>Explore nossa biblioteca de treinamentos gratuitos e potencialize sua carreira profissional.</p>
    </div>

    <!-- CONTINUE WATCHING -->
    <?php if (!empty($emAndamento)): ?>
        <section>
            <div class="flex items-center justify-between mb-6">
                <h2 class="el-h2">Continuar Aprendendo</h2>
                <span class="el-text-sm" style="color: var(--primary-600); font-weight: 600;">
                    <?= count($emAndamento) ?> em progresso
                </span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($emAndamento as $c): 
                    $pct = (float)($c['progresso_pct'] ?? 0);
                ?>
                <a href="/elearning/colaborador/cursos/<?= (int)$c['id'] ?>/continuar" class="el-continue-card group">
                    <div class="el-continue-image">
                        <?php if ($c['has_thumbnail']): ?>
                            <img src="/elearning/gestor/cursos/thumbnail?id=<?= (int)$c['id'] ?>" alt="<?= e($c['titulo']) ?>">
                        <?php else: ?>
                            📖
                        <?php endif; ?>
                    </div>
                    <div class="el-continue-content">
                        <div>
                            <h3 class="font-bold text-slate-900 line-clamp-2 group-hover:text-primary-600 transition-colors">
                                <?= e($c['titulo']) ?>
                            </h3>
                            <p class="el-text-sm text-muted mt-1">
                                Por <?= e($c['gestor_nome']) ?>
                            </p>
                        </div>
                        <div class="mt-3">
                            <div class="flex justify-between items-center mb-2">
                                <span class="el-text-xs">Seu progresso</span>
                                <span class="font-bold text-primary-600"><?= number_format($pct, 0) ?>%</span>
                            </div>
                            <div class="el-progress-container">
                                <div class="el-progress-bar" style="width: <?= $pct ?>%;"></div>
                            </div>
         FILTER TABS -->
    <div class="flex gap-2 mb-6 flex-wrap">
        <button onclick="setFilter('all')" class="filter-btn el-btn el-btn-sm el-btn-primary active">
            Todos (<?= count($cursos ?? []) ?>)
        </button>
        <button onclick="setFilter('not_enrolled')" class="filter-btn el-btn el-btn-sm el-btn-secondary">
            Disponíveis (<?= count($disponiveis) ?>)
        </button>
        <button onclick="setFilter('enrolled')" class="filter-btn el-btn el-btn-sm el-btn-secondary">
            Em Progresso (<?= count($emAndamento) ?>)
        </button>
        <button onclick="setFilter('completed')" class="filter-btn el-btn el-btn-sm el-btn-secondary">
            Concluídos (<?= count($concluidos) ?>)
        </button>
    </div>

    <!-- COURSE CATALOG -->
    <section>
        <div id="catalogGrid" class="el-grid">
            <?php if (empty($cursos)): ?>
                <div class="el-card md:col-span-3 text-center py-16">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📚</div>
                    <h3 class="el-h3">Nenhum curso disponível</h3>
                    <p class="el-text-sm text-muted mt-2">Novos cursos serão adicionados em breve!</p>
                </div>
            <?php else: ?>
                <?php foreach ($cursos ?? [] as $c): 
                    $matriculado = !empty($c['matricula_id']);
                    $concluido = ($c['matricula_status'] ?? '') === 'concluido';
                    $emProgresso = $matriculado && !$concluido;
                    $statusClass = $concluido ? 'completed' : ($matriculado ? 'enrolled' : 'not_enrolled');
                ?>
                <article class="curso-card el-course-card" 
                         data-status="<?= $statusClass ?>"
                         data-title="<?= strtolower(e($c['titulo'] ?? '')) ?>">
                    
                    <div class="el-course-image group">
                        <?php if ($c['has_thumbnail']): ?>
                            <img src="/elearning/gestor/cursos/thumbnail?id=<?= (int)$c['id'] ?>" alt="<?= e($c['titulo']) ?>" class="group-hover:scale-110 transition-transform duration-500">
                        <?php else: ?>
                            <div style="background: linear-gradient(135deg, #0ea5e9, #7c3aed);">📖</div>
                        <?php endif; ?>
                        
                        <?php if ($concluido): ?>
                            <div class="absolute inset-0 bg-success-500 bg-opacity-90 flex items-center justify-center">
                                <div class="text-center text-white">
                                    <div style="font-size: 2.5rem;">✓</div>
                                    <p class="el-text-xs mt-2">CONCLUÍDO</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="el-course-content">
                        <div class="flex gap-2 flex-wrap mb-2">
<script>
    function setFilter(status) {
        const allCards = document.querySelectorAll('.el-course-card');
        const filterBtns = document.querySelectorAll('.filter-btn');
        
        // Update button styles
        filterBtns.forEach(btn => {
            btn.classList.remove('el-btn-primary');
            btn.classList.add('el-btn-secondary');
        });
        event.target.classList.remove('el-btn-secondary');
        event.target.classList.add('el-btn-primary');

        // Filter cards
        allCards.forEach(card => {
            if (status === 'all') {
                card.style.display = 'flex';
            } else if (status === 'enrolled') {
                card.style.display = card.dataset.status === 'enrolled' ? 'flex' : 'none';
            } else if (status === 'not_enrolled') {
                card.style.display = card.dataset.status === 'not_enrolled' ? 'flex' : 'none';
            } else if (status === 'completed') {
                card.style.display = card.dataset.status === 'completed' ? 'flex' : 'none';
            }
        });
    }

    window.filtrarCursos = function(query) {
        query = query.toLowerCase();
        document.querySelectorAll('.curso-card').forEach(card => {
            const title = card.dataset.title;
            card.style.display = title.includes(query) ? 'flex' : 'none';
        });
    };

    async function matricular(cursoId) {
        const fd = new FormData();
        fd.append('curso_id', cursoId);
        fd.append('redirect', '1');

        try {
            const res = await fetch('/elearning/colaborador/matricular', { method: 'POST', body: fd });
            const data = await res.json();
            
            if (data.success) {
                if (typeof showToast === 'function') {
                    showToast('Inscrição realizada! Bons estudos.', 'success');
                }
                if (data.redirect_url) {
                    setTimeout(() => window.location.href = data.redirect_url + '/continuar', 1000);
                } else {
                    setTimeout(() => location.reload(), 1000);
                }
            } else {
                if (typeof showToast === 'function') {
                    showToast(data.message || 'Erro ao inscrever', 'error');
                }
            }
        } catch(e) {
            if (typeof showToast === 'function') {
                showToast('Erro de conexão', 'error');
            }
        }
    }
</script>
