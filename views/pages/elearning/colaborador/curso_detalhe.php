<?php
// views/pages/elearning/colaborador/curso_detalhe.php
?>
<div class="max-w-4xl mx-auto space-y-6">
  <div class="flex items-center gap-3">
    <a href="/elearning/colaborador" class="text-blue-600 hover:underline text-sm">← Meus Cursos</a>
  </div>

  <!-- Cabeçalho do curso -->
  <div class="bg-white rounded-xl shadow p-6">
    <div class="flex items-start justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($curso['titulo'] ?? '') ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($curso['descricao'] ?? '') ?></p>
        <div class="flex gap-4 mt-3 text-sm text-gray-600">
          <span>⏱ <?= (int)($curso['carga_horaria'] ?? 0) ?> min</span>
          <span>📖 <?= count($aulas) ?> aulas</span>
          <span>📝 <?= count($provas) ?> prova(s)</span>
        </div>
      </div>
      <?php $pct = (float)($matricula['progresso_pct'] ?? 0); ?>
      <div class="text-center ml-6">
        <div class="text-3xl font-bold text-blue-600"><?= number_format($pct,0) ?>%</div>
        <div class="text-xs text-gray-500">concluído</div>
      </div>
    </div>
    <div class="mt-4 bg-gray-200 rounded-full h-2">
      <div class="h-2 rounded-full bg-blue-500 transition-all" style="width: <?= $pct ?>%"></div>
    </div>
  </div>

  <!-- Aulas -->
  <div class="bg-white rounded-xl shadow">
    <div class="px-6 py-4 border-b border-gray-100"><h2 class="font-semibold text-gray-900">📖 Conteúdo do Curso</h2></div>
    <?php foreach ($aulas as $idx => $a): ?>
    <div class="px-6 py-4 border-b border-gray-50 last:border-0">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-xs text-gray-400">Aula <?= $idx + 1 ?></div>
          <h3 class="font-medium text-gray-900"><?= htmlspecialchars($a['titulo']) ?></h3>
          <?php if ($a['descricao']): ?>
          <p class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars($a['descricao']) ?></p>
          <?php endif; ?>
          <div class="text-xs text-gray-400 mt-1"><?= (int)($a['total_materiais'] ?? 0) ?> material(is)</div>
        </div>
        <div class="flex gap-2 ml-4 flex-wrap">
          <button onclick="carregarMateriais(<?= (int)$a['id'] ?>, this)" class="text-xs text-blue-600 border border-blue-200 rounded px-2 py-1 hover:bg-blue-50">Ver materiais</button>
        </div>
      </div>
      <div id="materiais_<?= (int)$a['id'] ?>" class="hidden mt-3 pl-4 border-l-2 border-blue-100 space-y-2"></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Provas e Certificado -->
  <?php if (!empty($provas)): ?>
  <div class="bg-white rounded-xl shadow p-6 space-y-4">
    <h2 class="font-semibold text-gray-900">📝 Avaliações</h2>
    <?php foreach ($provas as $p): ?>
    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
      <div>
        <div class="font-medium text-gray-900"><?= htmlspecialchars($p['titulo']) ?></div>
        <div class="text-xs text-gray-500 mt-1">Nota mínima: <?= $p['nota_minima'] ?>% • Tentativas: <?= $p['tentativas_feitas'] ?>/<?= $p['tentativas_max'] ?></div>
      </div>
      <?php if ((int)$p['tentativas_feitas'] < (int)$p['tentativas_max']): ?>
      <a href="/elearning/colaborador/provas/<?= (int)$p['id'] ?>/fazer" class="text-sm bg-purple-600 text-white px-3 py-1.5 rounded-lg hover:bg-purple-700">Fazer Prova</a>
      <?php else: ?>
      <span class="text-xs text-gray-400 bg-gray-200 rounded px-2 py-1">Tentativas esgotadas</span>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if ($certificado): ?>
  <div class="bg-green-50 border border-green-200 rounded-xl p-6 flex items-center justify-between">
    <div>
      <div class="text-green-800 font-semibold">🏆 Certificado Disponível!</div>
      <div class="text-green-600 text-sm mt-1">Código: <?= htmlspecialchars($certificado['codigo_validacao']) ?></div>
    </div>
    <a href="/elearning/colaborador/certificados/<?= htmlspecialchars($certificado['codigo_validacao']) ?>" target="_blank" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm">📄 Baixar Certificado</a>
  </div>
  <?php endif; ?>
</div>

<script>
async function carregarMateriais(aulaId, btn) {
  const div = document.getElementById('materiais_' + aulaId);
  if (!div.classList.contains('hidden')) { div.classList.add('hidden'); btn.textContent = 'Ver materiais'; return; }
  btn.textContent = 'Carregando...';
  const res = await fetch('/elearning/colaborador/materiais/' + aulaId + '/assistir');
  const d = await res.json();
  if (d.success) {
    const icones = { video: '🎬', pdf: '📄', imagem: '🖼️', slide: '📊' };
    div.innerHTML = `<div class="flex items-center gap-3 p-2 bg-white rounded-lg shadow-sm">
      <span>${icones[d.tipo] || '📁'}</span>
      <div class="flex-1">
        <div class="text-sm font-medium text-gray-900">${d.titulo}</div>
        <div class="text-xs text-gray-400">${d.tipo}</div>
      </div>
      <a href="${d.path}" target="_blank" class="text-xs text-blue-600 hover:underline" onclick="marcarVisto(${aulaId})">Abrir →</a>
    </div>`;
    div.classList.remove('hidden');
    btn.textContent = 'Ocultar';
  } else {
    btn.textContent = 'Ver materiais';
    alert('Não foi possível carregar os materiais.');
  }
}

function marcarVisto(materialId) {
  const fd = new FormData();
  fd.append('id_material', materialId);
  fd.append('pct', 100);
  fetch('/elearning/colaborador/progresso/registrar', { method: 'POST', body: fd });
}
</script>
