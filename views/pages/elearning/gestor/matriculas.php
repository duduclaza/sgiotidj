<?php
// views/pages/elearning/gestor/matriculas.php
?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <a href="/elearning/gestor/cursos" class="text-blue-600 hover:underline text-sm">← Cursos</a>
      <h1 class="text-2xl font-bold text-gray-900 mt-1">👥 Matrículas — <?= htmlspecialchars($curso['titulo'] ?? '') ?></h1>
    </div>
    <?php if ($canEdit): ?>
    <button onclick="document.getElementById('modalMatricula').classList.remove('hidden')" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm">+ Matricular</button>
    <?php endif; ?>
  </div>

  <div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Colaborador</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data Matrícula</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progresso</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Conclusão</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($matriculas)): ?>
        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Nenhuma matrícula registrada.</td></tr>
        <?php else: ?>
        <?php foreach ($matriculas as $m): ?>
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3">
            <div class="font-medium text-gray-900"><?= htmlspecialchars($m['usuario_nome']) ?></div>
            <div class="text-xs text-gray-400"><?= htmlspecialchars($m['usuario_email'] ?? '') ?></div>
          </td>
          <td class="px-4 py-3 text-gray-600 text-xs"><?= date('d/m/Y H:i', strtotime($m['data_matricula'])) ?></td>
          <td class="px-4 py-3">
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="bg-blue-500 h-2 rounded-full" style="width: <?= (float)$m['progresso_pct'] ?>%"></div>
            </div>
            <div class="text-xs text-gray-500 mt-1"><?= number_format((float)$m['progresso_pct'], 1) ?>%</div>
          </td>
          <td class="px-4 py-3">
            <?php $sc = ['em_andamento'=>'blue','concluido'=>'green','reprovado'=>'red'][$m['status']] ?? 'gray'; ?>
            <span class="px-2 py-0.5 rounded text-xs bg-<?= $sc ?>-100 text-<?= $sc ?>-800 font-semibold"><?= strtoupper(str_replace('_',' ',$m['status'])) ?></span>
          </td>
          <td class="px-4 py-3 text-gray-600 text-xs"><?= $m['concluido_em'] ? date('d/m/Y', strtotime($m['concluido_em'])) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div id="modalMatricula" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6 space-y-4">
    <h2 class="text-lg font-semibold">Matricular Colaborador</h2>
    <form id="formMatricula" class="space-y-3">
      <input type="hidden" name="id_curso" value="<?= (int)($curso['id'] ?? 0) ?>">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Colaborador *</label>
        <select name="id_usuario" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
          <option value="">Selecione...</option>
          <?php foreach ($usuarios as $u): ?>
          <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['name']) ?> — <?= htmlspecialchars($u['email']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="document.getElementById('modalMatricula').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Cancelar</button>
        <button type="button" onclick="matricular()" class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">Matricular</button>
      </div>
    </form>
  </div>
</div>
<script>
async function matricular() {
  const fd = new FormData(document.getElementById('formMatricula'));
  const res = await fetch('/elearning/gestor/matriculas/store', { method: 'POST', body: fd });
  const d = await res.json();
  if (d.success) { alert(d.message); location.reload(); } else alert('Erro: ' + d.message);
}
</script>
