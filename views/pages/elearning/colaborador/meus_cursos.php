<?php
// views/pages/elearning/colaborador/meus_cursos.php
?>
<div class="space-y-6">
  <h1 class="text-2xl font-bold text-gray-900">🎒 Meus Cursos</h1>

  <?php if (empty($cursos)): ?>
  <div class="bg-white rounded-xl shadow p-10 text-center">
    <div class="text-4xl mb-4">📚</div>
    <p class="text-gray-500">Você ainda não está matriculado em nenhum curso.</p>
  </div>
  <?php else: ?>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($cursos as $c): ?>
    <?php $pct = (float)($c['progresso_pct'] ?? 0); ?>
    <div class="bg-white rounded-xl shadow overflow-hidden flex flex-col hover:shadow-lg transition">
      <?php if (!empty($c['thumbnail'])): ?>
      <img src="<?= htmlspecialchars($c['thumbnail']) ?>" alt="Thumbnail" class="w-full h-40 object-cover">
      <?php else: ?>
      <div class="w-full h-40 bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-5xl">🎓</div>
      <?php endif; ?>
      <div class="p-5 flex flex-col flex-1">
        <h3 class="font-bold text-gray-900 mb-1"><?= htmlspecialchars($c['titulo']) ?></h3>
        <p class="text-xs text-gray-500 mb-2">Por <?= htmlspecialchars($c['gestor_nome'] ?? '') ?> • <?= (int)$c['total_aulas'] ?> aulas</p>
        <p class="text-xs text-gray-600 mb-4 flex-1"><?= htmlspecialchars(mb_substr($c['descricao'] ?? '', 0, 100)) ?><?= strlen($c['descricao'] ?? '') > 100 ? '...' : '' ?></p>
        
        <!-- Progresso -->
        <div class="mb-4">
          <div class="flex justify-between text-xs text-gray-500 mb-1">
            <span>Progresso</span><span><?= number_format($pct,1) ?>%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="h-2 rounded-full <?= $pct >= 100 ? 'bg-green-500' : 'bg-blue-500' ?> transition-all" style="width: <?= $pct ?>%"></div>
          </div>
        </div>

        <!-- Status badge e botão -->
        <div class="flex items-center justify-between">
          <?php $sc = ['em_andamento'=>'blue','concluido'=>'green','reprovado'=>'red'][$c['matricula_status']] ?? 'gray'; ?>
          <span class="text-xs px-2 py-0.5 rounded bg-<?= $sc ?>-100 text-<?= $sc ?>-700 font-semibold"><?= strtoupper(str_replace('_',' ',$c['matricula_status'])) ?></span>
          <a href="/elearning/colaborador/cursos/<?= (int)$c['id'] ?>" class="text-sm bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 transition">
            <?= $pct > 0 ? 'Continuar' : 'Iniciar' ?> →
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
