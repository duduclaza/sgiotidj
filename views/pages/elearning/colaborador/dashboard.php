<?php
$stats = $data['stats'] ?? [];
$enrolledCourses = $data['enrolled_courses'] ?? [];
$availableCourses = $data['available_courses'] ?? [];
$nextLesson = $data['next_lesson'] ?? null;
$certificates = $data['certificates'] ?? [];
$history = $data['history'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);

$statCards = [
    ['label' => 'Em andamento', 'value' => $stats['in_progress'] ?? 0, 'icon' => 'ph-book-open-text'],
    ['label' => 'Concluídos', 'value' => $stats['completed'] ?? 0, 'icon' => 'ph-check-circle'],
    ['label' => 'Provas pendentes', 'value' => $stats['pending_exams'] ?? 0, 'icon' => 'ph-clipboard-text'],
    ['label' => 'Progresso geral', 'value' => ($stats['overall_progress'] ?? 0) . '%', 'icon' => 'ph-chart-line-up'],
];
?>

<style>
/* Subtly brutalist / Highly structural design */
.aluno-container {
    background-color: #fcfcfc;
    color: #111;
    font-family: 'Inter', system-ui, sans-serif;
    padding: 2rem;
}
.aluno-hero {
    background: #000;
    color: #fff;
    border-radius: 0;
    border: 2px solid #000;
    box-shadow: 8px 8px 0px #000;
    margin-bottom: 3rem;
    position: relative;
    overflow: hidden;
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.aluno-hero:hover {
    transform: translate(-4px, -4px);
    box-shadow: 12px 12px 0px #000;
}
.aluno-card {
    background: #fff;
    border: 2px solid #000;
    box-shadow: 4px 4px 0px #000;
    border-radius: 0;
    transition: all 0.2s;
}
.aluno-card:hover {
    transform: translate(-2px, -2px);
    box-shadow: 6px 6px 0px #000;
}
.aluno-btn {
    background: #fff;
    color: #000;
    border: 2px solid #000;
    padding: 10px 20px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: -0.02em;
    cursor: pointer;
    box-shadow: 3px 3px 0px #000;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.aluno-btn:hover {
    transform: translate(2px, 2px);
    box-shadow: 1px 1px 0px #000;
    background: #000;
    color: #fff;
}
.aluno-btn-primary {
    background: #000;
    color: #fff;
    border: 2px solid #000;
    padding: 10px 20px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: -0.02em;
    cursor: pointer;
    box-shadow: 3px 3px 0px #000;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.aluno-btn-primary:hover {
    transform: translate(2px, 2px);
    box-shadow: 1px 1px 0px #000;
    background: #3b82f6; /* Blue hover for primary action */
    color: #fff;
    border-color: #3b82f6;
}
.aluno-progress-track {
    height: 12px;
    background: #f1f5f9;
    border: 2px solid #000;
    position: relative;
    overflow: hidden;
}
.aluno-progress-fill {
    height: 100%;
    background: #3b82f6;
    border-right: 2px solid #000;
    transition: width 1s cubic-bezier(0.16, 1, 0.3, 1);
}
.badge-solid {
    background: #000;
    color: #fff;
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    padding: 4px 8px;
    border: 1px solid #000;
}
.badge-outline {
    background: transparent;
    color: #000;
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    padding: 4px 8px;
    border: 1px solid #000;
}
</style>

<div class="aluno-container min-h-screen">
    
    <?php if (!$schemaReady): ?>
        <div class="mb-8 border-l-8 border-red-500 bg-white p-4 shadow-[4px_4px_0px_#000] border-2 border-r-black border-t-black border-b-black lg:col-span-12">
            <p class="font-black text-red-600 uppercase">Sistema em Manutenção</p>
            <p class="text-sm font-bold text-gray-800">O front do módulo já está disponível, mas o esquema do banco de dados ainda não foi aplicado.</p>
        </div>
    <?php endif; ?>

    <!-- HERO SECTION / NEXT LESSON GIGANTE -->
    <div class="aluno-hero grid grid-cols-1 lg:grid-cols-2 group">
        <div class="p-10 lg:p-16 flex flex-col justify-center relative z-10">
            <div class="inline-flex items-center gap-2 mb-6">
                <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                <span class="text-xs font-black uppercase tracking-widest text-gray-300">Campus Digital</span>
            </div>
            
            <?php if ($nextLesson): ?>
                <h2 class="text-xl font-bold text-gray-400 uppercase tracking-widest mb-2 border-b border-gray-800 pb-2 inline-block">Retomar Estudos</h2>
                <h1 class="text-4xl lg:text-6xl font-black mb-4 leading-none tracking-tighter text-white group-hover:text-amber-300 transition-colors">
                    <?= e($nextLesson['title']) ?>
                </h1>
                <p class="text-sm text-gray-400 mb-8 max-w-md">De <strong class="text-white"><?= e($nextLesson['course_title']) ?></strong></p>
                <div>
                    <a href="/elearning/colaborador/materiais/<?= (int) $nextLesson['id'] ?>/assistir" class="aluno-btn-primary">
                        <i class="ph-fill ph-play-circle text-xl"></i> Continuar Aula
                    </a>
                </div>
            <?php else: ?>
                <h1 class="text-4xl lg:text-6xl font-black mb-4 leading-none tracking-tighter text-white">
                    Minha<br>Jornada
                </h1>
                <p class="text-sm text-gray-400 mb-8 max-w-md">Sem pendências imediatas. Explore o catálogo para encontrar novos conhecimentos.</p>
            <?php endif; ?>
            
            <div class="mt-12 flex gap-4">
                <a href="/elearning/colaborador/historico" class="text-xs font-bold uppercase tracking-widest text-gray-500 hover:text-white transition-colors border-b border-transparent hover:border-white pb-1">Ver histórico completo &rarr;</a>
            </div>
        </div>
        
        <!-- Decoration / Abstract Image -->
        <div class="hidden lg:block relative bg-gray-900 border-l-2 border-gray-800">
            <div class="absolute inset-0 bg-[url('https://placehold.co/800x600?text=LEARN')] bg-cover bg-center mix-blend-overlay opacity-20 filter grayscale transition duration-700 group-hover:grayscale-0 group-hover:opacity-40"></div>
            <!-- Decorative overlay box -->
            <div class="absolute bottom-10 right-10 w-40 h-40 border-4 border-dashed border-gray-700 flex items-center justify-center p-4">
                <div class="text-center">
                    <div class="text-3xl font-black text-white"><?= $stats['overall_progress'] ?? 0 ?>%</div>
                    <div class="text-[10px] font-bold uppercase text-gray-500">Global</div>
                </div>
            </div>
        </div>
    </div>

    <!-- METRICS STRIP -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-12">
        <?php foreach ($statCards as $i => $card): ?>
            <div class="aluno-card p-4 <?= $i === 3 ? 'bg-amber-300' : 'bg-white' ?>">
                <div class="flex justify-between items-center mb-4">
                    <i class="ph <?= e($card['icon']) ?> text-2xl <?= $i === 3 ? 'text-black' : 'text-blue-600' ?>"></i>
                    <span class="text-[10px] font-black uppercase text-gray-400"><?= sprintf('%02d', $i+1) ?></span>
                </div>
                <div class="text-3xl font-black tracking-tighter mb-1"><?= e((string) $card['value']) ?></div>
                <div class="text-[11px] font-bold uppercase text-gray-800 tracking-wider"><?= e($card['label']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- MAIN CONTENT GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        <!-- LEFT: Trilhas Matriculadas -->
        <div class="lg:col-span-8 space-y-8">
            <div class="flex items-center justify-between border-b-4 border-black pb-4">
                <h3 class="text-2xl font-black uppercase tracking-tighter flex items-center gap-2">
                    <span class="w-4 h-4 bg-black inline-block"></span> Cursos Ativos
                </h3>
                <span class="badge-solid"><?= count($enrolledCourses) ?> Trilhas</span>
            </div>

            <div class="grid gap-6">
                <?php if (!$enrolledCourses): ?>
                    <div class="border-4 border-dashed border-gray-300 p-12 text-center bg-gray-50">
                        <i class="ph ph-empty text-4xl text-gray-400 mb-2"></i>
                        <p class="font-bold text-gray-500 uppercase tracking-widest">Nenhuma trilha matriculada ainda.</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($enrolledCourses as $course): ?>
                    <div class="aluno-card p-6 relative overflow-hidden group">
                        <div class="grid grid-cols-1 sm:grid-cols-[200px,1fr] gap-6 relative z-10">
                            <!-- Course Cover -->
                            <div class="aspect-video sm:aspect-square bg-gray-100 border-2 border-black relative overflow-hidden">
                                <img src="<?= e($course['cover_url']) ?>" alt="Capa" class="w-full h-full object-cover filter grayscale group-hover:grayscale-0 transition duration-500">
                                <div class="absolute top-2 right-2 badge-solid"><?= (int) ($course['workload_hours'] ?? 0) ?>H</div>
                            </div>
                            
                            <!-- Course Info -->
                            <div class="flex flex-col justify-between">
                                <div>
                                    <div class="flex gap-2 mb-3 max-w-full overflow-x-auto pb-2">
                                        <span class="badge-outline whitespace-nowrap"><?= e($course['category'] ?? 'Geral') ?></span>
                                        <span class="badge-solid whitespace-nowrap"><?= e((string) ($course['enrollment_status'] ?? 'in_progress')) ?></span>
                                    </div>
                                    <h4 class="text-xl sm:text-2xl font-black uppercase tracking-tight leading-none mb-1 group-hover:text-blue-600 transition-colors"><?= e($course['title']) ?></h4>
                                    <p class="text-xs font-bold text-gray-500 mt-2">Prof. <?= e($course['teacher_name'] ?? 'A definir') ?></p>
                                </div>
                                
                                <div class="mt-6">
                                    <div class="flex justify-between text-[10px] font-black uppercase tracking-widest mb-1">
                                        <span>Progresso</span>
                                        <span><?= number_format((float) ($course['progress_percent'] ?? 0), 0) ?>%</span>
                                    </div>
                                    <div class="aluno-progress-track mb-4">
                                        <div class="aluno-progress-fill" style="width: <?= min(100, max(0, (float) ($course['progress_percent'] ?? 0))) ?>%"></div>
                                    </div>
                                    
                                    <div class="flex gap-3">
                                        <a href="/elearning/colaborador/cursos/<?= (int) $course['id'] ?>/continuar" class="aluno-btn-primary flex-1 justify-center text-[11px]">Continuar</a>
                                        <a href="/elearning/colaborador/cursos/<?= (int) $course['id'] ?>" class="aluno-btn flex-1 justify-center text-[11px]">Visão Geral</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- DISPONÍVEIS (Catálogo) -->
            <div class="flex items-center justify-between border-b-4 border-black pb-4 mt-16 pt-8">
                <h3 class="text-2xl font-black uppercase tracking-tighter flex items-center gap-2">
                    <span class="w-4 h-4 bg-white border-2 border-black inline-block"></span> Novas Oportunidades
                </h3>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <?php if (!$availableCourses): ?>
                    <div class="border-4 border-dashed border-gray-300 p-8 text-center bg-gray-50 md:col-span-2">
                        <p class="font-bold text-gray-500 uppercase tracking-widest text-xs">Todos os cursos publicados já estão na sua trilha.</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($availableCourses as $course): ?>
                    <div class="aluno-card bg-white hover:bg-amber-50 group flex flex-col">
                        <div class="h-32 bg-gray-200 border-b-2 border-black overflow-hidden relative">
                            <img src="<?= e($course['cover_url']) ?>" alt="Capa" class="w-full h-full object-cover filter grayscale opacity-80 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-500">
                        </div>
                        <div class="p-5 flex flex-col flex-1">
                            <div class="flex justify-between items-start mb-2">
                                <span class="badge-outline"><?= e($course['category'] ?? 'Geral') ?></span>
                                <span class="text-xs font-black"><?= (int) ($course['workload_hours'] ?? 0) ?>h</span>
                            </div>
                            <h4 class="text-lg font-black uppercase tracking-tight leading-tight mb-2 flex-grow">
                                <?= e($course['title']) ?>
                            </h4>
                            <p class="text-[10px] font-bold text-gray-500 uppercase truncate mb-4">Prof. <?= e($course['teacher_name'] ?? 'Padrão') ?></p>
                            
                            <button type="button" class="aluno-btn w-full justify-center text-xs" onclick="enrollCourse(<?= (int) $course['id'] ?>)">
                                Matricular agora
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- RIGHT: Sidebars (Certificados, Histórico) -->
        <div class="lg:col-span-4 space-y-8">
            
            <!-- Certificados -->
            <div class="aluno-card p-6 bg-gray-900 border-black text-white relative overflow-hidden group">
                <div class="absolute -right-10 -top-10 w-40 h-40 border-8 border-gray-800 rounded-full opacity-50 pointer-events-none group-hover:scale-110 transition-transform duration-700"></div>
                
                <h3 class="text-xl font-black uppercase tracking-tighter mb-6 relative z-10 flex items-center gap-2">
                    <i class="ph-fill ph-seal-check text-yellow-400"></i> Certificados
                </h3>

                <div class="space-y-3 relative z-10">
                    <?php if (!$certificates): ?>
                        <p class="text-[11px] font-bold text-gray-500 uppercase tracking-widest border border-gray-800 p-4">Assista às aulas e passe nas provas para obter seus certificados.</p>
                    <?php endif; ?>

                    <?php foreach (array_slice($certificates, 0, 3) as $certificate): ?>
                        <a href="/elearning/colaborador/certificados/<?= e($certificate['validation_code']) ?>" class="block border border-gray-700 p-3 hover:border-yellow-400 transition-colors bg-black">
                            <p class="text-sm font-bold uppercase truncate mb-1" title="<?= e($certificate['course_title']) ?>"><?= e($certificate['course_title']) ?></p>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-[9px] font-bold text-gray-500 uppercase tracking-widest"><?= date('d/m/Y', strtotime((string) $certificate['issued_at'])) ?></span>
                                <span class="text-[10px] font-black text-yellow-400 uppercase">Imprimir &rarr;</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Histórico Compacto -->
            <div class="border-4 border-black p-6 bg-white shadow-[6px_6px_0px_#000]">
                <h3 class="font-black uppercase text-lg mb-4 flex items-center justify-between border-b-2 border-black pb-2">
                    Histórico <i class="ph ph-clock-counter-clockwise"></i>
                </h3>
                <div class="space-y-4">
                    <?php if (!$history): ?>
                        <p class="text-[11px] font-bold text-gray-400 uppercase">Nenhum evento registrado.</p>
                    <?php endif; ?>
                    <?php foreach (array_slice($history, 0, 4) as $item): ?>
                        <div class="flex gap-3 items-start group">
                            <div class="w-1.5 h-1.5 bg-black mt-1.5 group-hover:scale-150 transition-transform"></div>
                            <div>
                                <p class="text-xs font-black uppercase leading-tight group-hover:text-blue-600 transition-colors"><?= e($item['title']) ?></p>
                                <p class="text-[9px] font-bold text-gray-500 uppercase tracking-widest mt-1">
                                    <?= e((string) $item['status']) ?> &bull; <?= !empty($item['completed_at']) ? date('d/m/Y', strtotime((string) $item['completed_at'])) : '--' ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <a href="/inicio" class="block w-full border-2 border-black bg-gray-100 hover:bg-red-500 hover:text-white transition-colors p-4 text-center text-sm font-black uppercase tracking-widest cursor-pointer mt-8">
                Sair do Modulo Educacional &times;
            </a>
            
        </div>
    </div>
</div>

<script>
async function enrollCourse(courseId) {
    const formData = new FormData();
    formData.append('course_id', courseId);

    const response = await fetch('/elearning/colaborador/matricular', { method: 'POST', body: formData });
    const result = await response.json();
    if (!result.success) {
        if(typeof showELToast === 'function') showELToast(result.message || 'Não foi possível concluir a matricula.', 'error');
        else alert(result.message || 'Erro ao matricular');
        return;
    }

    if(typeof showELToast === 'function') showELToast(result.message || 'Matricula concluída com sucesso.', 'success');
    setTimeout(() => window.location.reload(), 700);
}
</script>
