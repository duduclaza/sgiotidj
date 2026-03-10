<?php
// views/pages/elearning/gestor/cursos.php
?>
<div class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900">📚 Cursos — eLearning Gestor</h1>
    <?php if ($canEdit): ?>
    <button onclick="document.getElementById('modalCurso').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">+ Novo Curso</button>
    <?php endif; ?>
  </div>

  <div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Curso</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">CH (min)</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aulas</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Matrículas</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (empty($cursos)): ?>
          <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">Nenhum curso cadastrado.</td></tr>
          <?php else: ?>
          <?php foreach ($cursos as $c): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-900"><?= htmlspecialchars($c['titulo']) ?></td>
            <td class="px-4 py-3">
              <?php $sc = ['ativo'=>'green','rascunho'=>'yellow','inativo'=>'gray'][$c['status']] ?? 'gray'; ?>
              <span class="px-2 py-0.5 rounded text-xs bg-<?= $sc ?>-100 text-<?= $sc ?>-800 font-semibold"><?= strtoupper($c['status']) ?></span>
            </td>
            <td class="px-4 py-3 text-gray-600"><?= (int)$c['carga_horaria'] ?></td>
            <td class="px-4 py-3 text-gray-600"><?= (int)($c['total_aulas'] ?? 0) ?></td>
            <td class="px-4 py-3 text-gray-600"><?= (int)($c['total_matriculas'] ?? 0) ?></td>
            <td class="px-4 py-3 flex gap-2 flex-wrap">
              <a href="/elearning/gestor/cursos/<?= (int)$c['id'] ?>/aulas" class="text-xs text-blue-600 hover:underline">Aulas</a>
              <a href="/elearning/gestor/cursos/<?= (int)$c['id'] ?>/provas" class="text-xs text-purple-600 hover:underline">Provas</a>
              <a href="/elearning/gestor/cursos/<?= (int)$c['id'] ?>/matriculas" class="text-xs text-green-600 hover:underline">Matrículas</a>
              <a href="/elearning/gestor/cursos/<?= (int)$c['id'] ?>/progresso" class="text-xs text-teal-600 hover:underline">Progresso</a>
              <?php if ($canEdit): ?>
              <button onclick='editarCurso(<?= json_encode($c) ?>)' class="text-xs text-orange-600 hover:underline">Editar</button>
              <?php endif; ?>
              <?php if ($canDelete): ?>
              <button onclick='excluirCurso(<?= (int)$c['id'] ?>, "<?= htmlspecialchars(addslashes($c['titulo'])) ?>")' class="text-xs text-red-600 hover:underline">Excluir</button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Curso -->
<div id="modalCurso" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 space-y-4">
    <h2 id="modalCursoTitle" class="text-lg font-semibold text-gray-900">Novo Curso</h2>
    <form id="formCurso" class="space-y-3">
      <input type="hidden" name="id" id="cursoId">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
        <input type="text" name="titulo" id="cursoTitulo" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
        <textarea name="descricao" id="cursoDescricao" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"></textarea>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Carga Horária (min)</label>
          <input type="number" name="carga_horaria" id="cursoCarga" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select name="status" id="cursoStatus" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="rascunho">Rascunho</option>
            <option value="ativo">Ativo</option>
            <option value="inativo">Inativo</option>
          </select>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Thumbnail (opcional, max 10MB)</label>
        <input type="file" name="thumbnail" accept="image/*" class="w-full text-sm">
      </div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="document.getElementById('modalCurso').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</button>
        <button type="button" onclick="salvarCurso()" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">Salvar</button>
      </div>
    </form>
  </div>
</div>

<script>
function editarCurso(c) {
  document.getElementById('modalCursoTitle').textContent = 'Editar Curso';
  document.getElementById('cursoId').value = c.id;
  document.getElementById('cursoTitulo').value = c.titulo;
  document.getElementById('cursoDescricao').value = c.descricao || '';
  document.getElementById('cursoCarga').value = c.carga_horaria || 0;
  document.getElementById('cursoStatus').value = c.status;
  document.getElementById('modalCurso').classList.remove('hidden');
}

async function salvarCurso() {
  const form = document.getElementById('formCurso');
  const formData = new FormData(form);
  const id = document.getElementById('cursoId').value;
  const url = id ? '/elearning/gestor/cursos/update' : '/elearning/gestor/cursos/store';
  try {
    const res = await fetch(url, { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) { alert(data.message); location.reload(); }
    else alert('Erro: ' + data.message);
  } catch(e) { alert('Erro de conexão'); }
}

async function excluirCurso(id, titulo) {
  if (!confirm(`Excluir o curso "${titulo}"? Esta ação não pode ser desfeita.`)) return;
  const fd = new FormData(); fd.append('id', id);
  const res = await fetch('/elearning/gestor/cursos/delete', { method: 'POST', body: fd });
  const d = await res.json();
  if (d.success) { alert(d.message); location.reload(); } else alert('Erro: ' + d.message);
}
</script>
