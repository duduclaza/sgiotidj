<?php
// views/pages/elearning/gestor/cursos.php
?>
<div class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900">📚 Cursos — eLearning Gestor</h1>
    <?php if ($canEdit): ?>
    <button onclick="abrirNovoCurso()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">+ Novo Curso</button>
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
<div id="modalCurso" class="hidden fixed inset-0 bg-black/50 z-50 p-4 sm:p-6 overflow-y-auto" onclick="if(event.target===this) fecharModalCurso()">
  <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-gray-100 w-full max-w-3xl mx-auto my-4 sm:my-10 max-h-[90vh] flex flex-col overflow-hidden">
    <div class="px-5 sm:px-7 pt-5 sm:pt-6 pb-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 id="modalCursoTitle" class="text-xl font-semibold tracking-tight text-gray-900">Novo Curso</h2>
          <p class="text-sm text-gray-500 mt-1">Preencha os campos abaixo para publicar o curso com qualidade.</p>
        </div>
        <button type="button" onclick="fecharModalCurso()" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition" aria-label="Fechar modal">
          <span class="text-lg leading-none">&times;</span>
        </button>
      </div>
    </div>
    <form id="formCurso" class="px-5 sm:px-7 py-5 space-y-5 overflow-y-auto">
      <input type="hidden" name="id" id="cursoId">
      <div class="space-y-4">
        <div>
          <label class="block text-xs font-semibold tracking-wide text-gray-500 uppercase mb-2">Título do curso *</label>
          <input type="text" name="titulo" id="cursoTitulo" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Ex.: Treinamento de Segurança Operacional">
        </div>
        <div>
          <label class="block text-xs font-semibold tracking-wide text-gray-500 uppercase mb-2">Descrição</label>
          <textarea name="descricao" id="cursoDescricao" rows="4" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Resumo objetivo do conteúdo, público-alvo e resultado esperado."></textarea>
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold tracking-wide text-gray-500 uppercase mb-2">Carga horária (min)</label>
          <input type="number" name="carga_horaria" id="cursoCarga" min="0" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="0">
        </div>
        <div>
          <label class="block text-xs font-semibold tracking-wide text-gray-500 uppercase mb-2">Status</label>
          <select name="status" id="cursoStatus" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            <option value="rascunho">Rascunho</option>
            <option value="ativo">Ativo</option>
            <option value="inativo">Inativo</option>
          </select>
        </div>
      </div>
      <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50/70 p-4">
        <label class="block text-xs font-semibold tracking-wide text-gray-500 uppercase mb-2">Thumbnail (opcional, max 10MB)</label>
        <input type="file" name="thumbnail" accept="image/*" class="w-full text-sm block file:mr-4 file:rounded-lg file:border-0 file:bg-blue-600 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-blue-700">
        <p class="text-xs text-gray-500 mt-2">Formato recomendado: 1280x720 px para melhor visualização.</p>
      </div>
      <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 pt-4 border-t border-gray-100 sticky bottom-0 bg-white/95 backdrop-blur pb-1">
        <button type="button" onclick="fecharModalCurso()" class="w-full sm:w-auto px-4 py-2.5 text-sm font-medium border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition">Cancelar</button>
        <button type="button" onclick="salvarCurso()" class="w-full sm:w-auto px-5 py-2.5 text-sm font-semibold bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-sm hover:shadow-md transition">Salvar Curso</button>
      </div>
    </form>
  </div>
</div>

<script>
function handleCursoModalEsc(event) {
  if (event.key === 'Escape') {
    fecharModalCurso();
  }
}

function abrirNovoCurso() {
  document.getElementById('modalCursoTitle').textContent = 'Novo Curso';
  document.getElementById('formCurso').reset();
  document.getElementById('cursoId').value = '';
  document.getElementById('modalCurso').classList.remove('hidden');
  document.body.classList.add('overflow-hidden');
  document.addEventListener('keydown', handleCursoModalEsc);
}

function fecharModalCurso() {
  document.getElementById('modalCurso').classList.add('hidden');
  document.body.classList.remove('overflow-hidden');
  document.removeEventListener('keydown', handleCursoModalEsc);
}

function editarCurso(c) {
  document.getElementById('modalCursoTitle').textContent = 'Editar Curso';
  document.getElementById('cursoId').value = c.id;
  document.getElementById('cursoTitulo').value = c.titulo;
  document.getElementById('cursoDescricao').value = c.descricao || '';
  document.getElementById('cursoCarga').value = c.carga_horaria || 0;
  document.getElementById('cursoStatus').value = c.status;
  document.getElementById('modalCurso').classList.remove('hidden');
  document.body.classList.add('overflow-hidden');
  document.addEventListener('keydown', handleCursoModalEsc);
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
