<?php
// views/pages/elearning/gestor/progresso.php
?>
<div class="space-y-6">
  <div>
    <a href="/elearning/gestor/cursos" class="text-blue-600 hover:underline text-sm">← Cursos</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-1">📊 Progresso — <?= htmlspecialchars($curso['titulo'] ?? '') ?></h1>
  </div>

  <div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Colaborador</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progresso</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Conclusão</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($progresso)): ?>
        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">Nenhum progresso registrado.</td></tr>
        <?php else: ?>
        <?php foreach ($progresso as $p): ?>
        <?php $pct = (float)$p['progresso_pct']; ?>
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3 font-medium text-gray-900"><?= htmlspecialchars($p['usuario_nome']) ?></td>
          <td class="px-4 py-3">
            <div class="flex items-center gap-3">
              <div class="flex-1 bg-gray-200 rounded-full h-2">
                <div class="h-2 rounded-full <?= $pct >= 100 ? 'bg-green-500' : ($pct > 50 ? 'bg-blue-500' : 'bg-yellow-400') ?>" style="width: <?= $pct ?>%"></div>
              </div>
              <span class="text-xs text-gray-600 w-12 text-right"><?= number_format($pct,1) ?>%</span>
            </div>
          </td>
          <td class="px-4 py-3">
            <?php $sc = ['em_andamento'=>'blue','concluido'=>'green','reprovado'=>'red'][$p['status']] ?? 'gray'; ?>
            <span class="px-2 py-0.5 rounded text-xs bg-<?= $sc ?>-100 text-<?= $sc ?>-800 font-semibold"><?= strtoupper(str_replace('_',' ',$p['status'])) ?></span>
          </td>
          <td class="px-4 py-3 text-gray-600 text-xs"><?= $p['concluido_em'] ? date('d/m/Y', strtotime($p['concluido_em'])) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
