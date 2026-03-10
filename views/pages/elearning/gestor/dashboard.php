<?php
// views/pages/elearning/gestor/dashboard.php
?>
<div class="space-y-6">
  <div class="flex justify-between items-center">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">🎓 eLearning Gestor</h1>
      <p class="text-sm text-gray-500 mt-1">Gerencie cursos, aulas, provas e certificados</p>
    </div>
    <?php if ($canEdit): ?>
    <a href="/elearning/gestor/cursos" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
      + Novo Curso
    </a>
    <?php endif; ?>
  </div>

  <!-- Cards de resumo -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow p-6 border-l-4 border-blue-500">
      <div class="text-3xl font-bold text-blue-600"><?= (int)$totalCursos ?></div>
      <div class="text-sm text-gray-500 mt-1">Cursos Ativos</div>
    </div>
    <div class="bg-white rounded-xl shadow p-6 border-l-4 border-green-500">
      <div class="text-3xl font-bold text-green-600"><?= (int)$totalMatriculas ?></div>
      <div class="text-sm text-gray-500 mt-1">Matrículas Totais</div>
    </div>
    <div class="bg-white rounded-xl shadow p-6 border-l-4 border-purple-500">
      <div class="text-3xl font-bold text-purple-600"><?= (int)$totalConcluidos ?></div>
      <div class="text-sm text-gray-500 mt-1">Cursos Concluídos</div>
    </div>
  </div>

  <!-- Tabela de Cursos -->
  <div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
      <h2 class="text-lg font-semibold text-gray-900">Cursos Cadastrados</h2>
      <a href="/elearning/gestor/cursos" class="text-blue-600 hover:underline text-sm">Ver todos →</a>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase text-xs">Curso</th>
            <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase text-xs">Status</th>
            <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase text-xs">Gestor</th>
            <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase text-xs">Matrículas</th>
            <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase text-xs">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (empty($cursos)): ?>
          <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Nenhum curso cadastrado.</td></tr>
          <?php else: ?>
          <?php foreach ($cursos as $c): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($c['titulo']) ?></td>
            <td class="px-6 py-4">
              <?php $statusColors = ['ativo'=>'green','rascunho'=>'yellow','inativo'=>'gray']; $sc = $statusColors[$c['status']] ?? 'gray'; ?>
              <span class="px-2 py-0.5 rounded text-xs font-semibold bg-<?= $sc ?>-100 text-<?= $sc ?>-800"><?= strtoupper($c['status']) ?></span>
            </td>
            <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($c['gestor_nome'] ?? '-') ?></td>
            <td class="px-6 py-4 text-gray-600"><?= (int)($c['total_matriculas'] ?? 0) ?></td>
            <td class="px-6 py-4">
              <a href="/elearning/gestor/cursos/<?= (int)$c['id'] ?>/aulas" class="text-blue-600 hover:underline mr-3 text-xs">Aulas</a>
              <a href="/elearning/gestor/cursos/<?= (int)$c['id'] ?>/provas" class="text-purple-600 hover:underline mr-3 text-xs">Provas</a>
              <a href="/elearning/gestor/cursos/<?= (int)$c['id'] ?>/progresso" class="text-green-600 hover:underline text-xs">Progresso</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
