<?php
// views/pages/elearning/colaborador/departamento_cursos.php
?>
<div class="space-y-6">
  <div class="flex items-center gap-3 text-gray-600">
    <a href="/elearning/colaborador" class="text-sm hover:underline">← Voltar</a>
    <span class="text-gray-400">|</span>
    <h1 class="text-2xl font-bold text-gray-900">📂 <?= htmlspecialchars($departamento['nome'] ?? '') ?></h1>
  </div>

  <?php if (empty($cursos)): ?>
  <div class="bg-white rounded-xl shadow p-10 text-center">
    <div class="text-4xl mb-4">📚</div>
    <p class="text-gray-500">Nenhum curso ativo neste departamento.</p>
  </div>
  <?php else: ?>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($cursos as $c): ?>
    <div class="bg-white rounded-xl shadow overflow-hidden flex flex-col hover:shadow-lg transition">
      <?php if (!empty($c['has_thumbnail'])): ?>
      <img src="/elearning/gestor/cursos/thumbnail?id=<?= (int)$c['id'] ?>" alt="Thumbnail" class="w-full h-40 object-cover">
      <?php else: ?>
      <div class="w-full h-40 bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-5xl">🎓</div>
      <?php endif; ?>
      <div class="p-5 flex flex-col flex-1">
        <h3 class="font-bold text-gray-900 mb-1"><?= htmlspecialchars($c['titulo']) ?></h3>
        <p class="text-xs text-gray-500 mb-2">Por <?= htmlspecialchars($c['gestor_nome'] ?? '') ?> • <?= (int)$c['total_aulas'] ?> aulas</p>
        <p class="text-xs text-gray-600 mb-4 flex-1"><?= htmlspecialchars(mb_substr($c['descricao'] ?? '', 0, 100)) ?><?= strlen($c['descricao'] ?? '') > 100 ? '...' : '' ?></p>

        <!-- Status badge e botão -->
        <div class="flex items-center justify-between">
          <?php if (!empty($c['matricula_id'])): ?>
            <span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700 font-semibold">MATRICULADO</span>
            <a href="/elearning/colaborador/cursos/<?= (int)$c['id'] ?>" class="text-sm bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 transition">
              <?= ($c['progresso_pct'] ?? 0) > 0 ? 'Continuar' : 'Iniciar' ?> →
            </a>
          <?php else: ?>
            <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-semibold">NÃO MATRICULADO</span>
            <button onclick="matricular(<?= (int)$c['id'] ?>)" class="text-sm bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700 transition">
              Matricular-se →
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<script>
async function matricular(cursoId) {
  if (!confirm('Deseja se matricular neste curso?')) return;
  const fd = new FormData(); fd.append('curso_id', cursoId);
  try {
    const res = await fetch('/elearning/colaborador/matricular', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      alert(data.message);
      location.reload();
    } else {
      alert('Erro: ' + data.message);
    }
  } catch (e) {
    alert('Erro de conexão');
  }
}
</script>
