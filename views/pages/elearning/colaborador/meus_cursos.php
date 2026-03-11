<?php
// views/pages/elearning/colaborador/meus_cursos.php
?>
<style>
  .el-fade-in { animation: elFadeIn .4s ease; }
  @keyframes elFadeIn { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
  .el-card { transition: transform .25s, box-shadow .25s; }
  .el-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(0,0,0,.14); }
  .el-gradient-hero { background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 40%, #3b82f6 100%); }
  .el-thumb-default { background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%); }
  .el-search:focus { box-shadow: 0 0 0 3px rgba(59,130,246,.25); }
  .el-progress-ring { width: 48px; height: 48px; }
  .el-progress-ring circle { transition: stroke-dashoffset .6s ease; }
</style>

<div class="space-y-6 el-fade-in">

  <!-- Hero -->
  <div class="el-gradient-hero rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
    <div class="relative z-10">
      <h1 class="text-2xl sm:text-3xl font-bold tracking-tight flex items-center gap-3">
        <span class="text-3xl">🎓</span> Catálogo de Cursos
      </h1>
      <p class="text-blue-200 text-sm mt-2 max-w-lg">
        Explore nossos cursos disponíveis, matricule-se e desenvolva suas habilidades. Seu progresso é salvo automaticamente.
      </p>
    </div>
    <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
    <div class="absolute bottom-0 right-20 w-32 h-32 bg-white/5 rounded-full translate-y-1/2"></div>
  </div>

  <!-- Search & Filter -->
  <div class="flex flex-col sm:flex-row gap-3">
    <div class="flex-1 relative">
      <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      <input type="text" id="searchCursos" onkeyup="filtrarCursos()" placeholder="Buscar cursos..."
        class="el-search w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
    </div>
    <select id="filterMatricula" onchange="filtrarCursos()" class="border border-gray-200 rounded-xl px-4 py-3 text-sm bg-white focus:ring-2 focus:ring-blue-500 transition shadow-sm">
      <option value="">Todos</option>
      <option value="matriculado">✅ Matriculado</option>
      <option value="nao_matriculado">📋 Não matriculado</option>
    </select>
  </div>

  <!-- Course Grid -->
  <?php if (empty($cursos)): ?>
  <div class="bg-white rounded-2xl shadow-md p-12 text-center">
    <div class="text-6xl mb-4">📚</div>
    <h3 class="text-xl font-bold text-gray-700 mb-2">Nenhum curso disponível</h3>
    <p class="text-sm text-gray-400">Em breve novos cursos estarão disponíveis para você.</p>
  </div>
  <?php else: ?>
  <div id="cursosGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
    <?php foreach ($cursos as $c):
      $matriculado = !empty($c['matricula_id']);
      $pct = (float)($c['progresso_pct'] ?? 0);
      $concluido = ($c['matricula_status'] ?? '') === 'concluido';
    ?>
    <div class="el-card curso-item bg-white rounded-2xl shadow-md overflow-hidden flex flex-col border border-gray-100"
         data-titulo="<?= htmlspecialchars(mb_strtolower($c['titulo'])) ?>"
         data-matricula="<?= $matriculado ? 'matriculado' : 'nao_matriculado' ?>">

      <!-- Thumbnail -->
      <?php if (!empty($c['has_thumbnail'])): ?>
      <div class="h-44 overflow-hidden relative">
        <img src="/elearning/gestor/cursos/thumbnail?id=<?= (int)$c['id'] ?>" alt="<?= htmlspecialchars($c['titulo']) ?>"
          class="w-full h-full object-cover transition-transform duration-500 hover:scale-110">
        <?php if ($concluido): ?>
        <div class="absolute top-3 right-3 bg-green-500 text-white text-[10px] px-2 py-1 rounded-full font-bold shadow">✓ CONCLUÍDO</div>
        <?php elseif ($matriculado): ?>
        <div class="absolute top-3 right-3 bg-blue-500 text-white text-[10px] px-2 py-1 rounded-full font-bold shadow">EM ANDAMENTO</div>
        <?php endif; ?>
      </div>
      <?php else: ?>
      <div class="el-thumb-default h-44 flex items-center justify-center relative">
        <span class="text-6xl text-white/60">🎓</span>
        <?php if ($concluido): ?>
        <div class="absolute top-3 right-3 bg-green-500 text-white text-[10px] px-2 py-1 rounded-full font-bold shadow">✓ CONCLUÍDO</div>
        <?php elseif ($matriculado): ?>
        <div class="absolute top-3 right-3 bg-blue-500 text-white text-[10px] px-2 py-1 rounded-full font-bold shadow">EM ANDAMENTO</div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- Content -->
      <div class="p-5 flex flex-col flex-1">
        <h3 class="font-bold text-gray-900 text-sm leading-tight line-clamp-2 mb-1"><?= htmlspecialchars($c['titulo']) ?></h3>
        
        <p class="text-xs text-gray-400 mb-2">por <?= htmlspecialchars($c['gestor_nome'] ?? 'N/A') ?></p>

        <?php if ($c['descricao']): ?>
        <p class="text-xs text-gray-500 mb-3 line-clamp-2 flex-1"><?= htmlspecialchars($c['descricao']) ?></p>
        <?php else: ?>
        <div class="flex-1"></div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="flex items-center gap-3 text-xs text-gray-400 py-2 border-t border-gray-100">
          <span title="Carga Horária">⏱ <?= (int)$c['carga_horaria'] ?> min</span>
          <span title="Aulas">📖 <?= (int)($c['total_aulas'] ?? 0) ?> aulas</span>
        </div>

        <!-- Progress or Enroll -->
        <?php if ($matriculado): ?>
        <div class="mt-2">
          <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-semibold text-gray-600">Progresso</span>
            <span class="text-xs font-bold text-blue-600"><?= number_format($pct, 0) ?>%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="h-2 rounded-full transition-all duration-500 <?= $concluido ? 'bg-green-500' : 'bg-blue-500' ?>" style="width: <?= $pct ?>%"></div>
          </div>
          <a href="/elearning/colaborador/cursos/<?= (int)$c['id'] ?>"
            class="mt-3 block text-center text-sm font-bold py-2.5 rounded-xl transition
            <?= $concluido
              ? 'bg-green-50 text-green-700 hover:bg-green-100 border border-green-200'
              : 'bg-blue-600 text-white hover:bg-blue-700 shadow-md hover:shadow-lg'
            ?>">
            <?= $concluido ? '🏆 Revisar Curso' : ($pct > 0 ? '▶ Continuar' : '▶ Iniciar Curso') ?>
          </a>
        </div>
        <?php else: ?>
        <button onclick="matricular(<?= (int)$c['id'] ?>)"
          class="mt-3 w-full text-center text-sm font-bold py-2.5 rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 text-white hover:from-green-600 hover:to-emerald-700 shadow-md hover:shadow-lg transition">
          📋 Matricular-se
        </button>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Certificados Link -->
  <div class="text-center pt-4">
    <a href="/elearning/colaborador/certificados" class="text-sm text-blue-600 hover:text-blue-700 font-medium transition">
      🏆 Ver meus certificados →
    </a>
  </div>
</div>

<script>
function filtrarCursos() {
  const search = document.getElementById('searchCursos').value.toLowerCase();
  const filtro = document.getElementById('filterMatricula').value;
  document.querySelectorAll('.curso-item').forEach(c => {
    const matchT = c.dataset.titulo.includes(search);
    const matchM = !filtro || c.dataset.matricula === filtro;
    c.style.display = (matchT && matchM) ? '' : 'none';
  });
}

function showToast(msg, type) {
  const e = document.getElementById('elToast'); if (e) e.remove();
  const c = { success:'bg-green-600', error:'bg-red-600' };
  const d = document.createElement('div'); d.id='elToast';
  d.className = `fixed bottom-6 right-6 z-[100] ${c[type]||'bg-blue-600'} text-white px-5 py-3 rounded-xl shadow-2xl text-sm font-medium`;
  d.style.animation = 'elFadeIn .3s ease'; d.textContent = msg;
  document.body.appendChild(d); setTimeout(()=>d.remove(), 3500);
}

async function matricular(cursoId) {
  if (!confirm('Deseja se matricular neste curso?')) return;
  const fd = new FormData(); fd.append('curso_id', cursoId);
  try {
    const res = await fetch('/elearning/colaborador/matricular', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) { showToast(data.message, 'success'); setTimeout(() => location.reload(), 800); }
    else showToast('Erro: ' + data.message, 'error');
  } catch(e) { showToast('Erro de conexão', 'error'); }
}
</script>
