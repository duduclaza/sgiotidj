<?php
// views/pages/elearning/gestor/aulas.php
?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <a href="/elearning/gestor/cursos" class="text-blue-600 hover:underline text-sm">← Cursos</a>
      <h1 class="text-2xl font-bold text-gray-900 mt-1">📖 Aulas — <?= htmlspecialchars($curso['titulo'] ?? '') ?></h1>
    </div>
    <?php if ($canEdit): ?>
    <div class="flex gap-2">
      <button onclick="document.getElementById('modalAula').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">+ Nueva Aula</button>
    </div>
    <?php endif; ?>
  </div>

  <?php if (empty($aulas)): ?>
  <div class="bg-white rounded-xl shadow p-8 text-center text-gray-400">Nenhuma aula cadastrada para este curso.</div>
  <?php else: ?>
  <div class="space-y-4">
    <?php foreach ($aulas as $a): ?>
    <div class="bg-white rounded-xl shadow p-5">
      <div class="flex items-start justify-between">
        <div>
          <div class="text-sm text-gray-400 mb-1">Aula <?= (int)($a['ordem'] + 1) ?></div>
          <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($a['titulo']) ?></h3>
          <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($a['descricao'] ?? '') ?></p>
          <div class="text-xs text-gray-400 mt-2"><?= (int)($a['total_materiais'] ?? 0) ?> material(is)</div>
        </div>
        <div class="flex flex-col gap-2 ml-4">
          <a href="#" onclick="abrirUpload(<?= (int)$a['id'] ?>); return false;" class="text-xs text-purple-600 hover:underline">+ Material</a>
          <?php if ($canDelete): ?>
          <button onclick="excluirAula(<?= (int)$a['id'] ?>, '<?= htmlspecialchars(addslashes($a['titulo'])) ?>')" class="text-xs text-red-600 hover:underline">Excluir</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Modal Nova Aula -->
<div id="modalAula" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
    <h2 class="text-lg font-semibold">Nova Aula</h2>
    <form id="formAula" class="space-y-3">
      <input type="hidden" name="id_curso" value="<?= (int)($curso['id'] ?? 0) ?>">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
        <input type="text" name="titulo" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
        <textarea name="descricao" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
        <input type="number" name="ordem" value="<?= count($aulas) ?>" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
      </div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="document.getElementById('modalAula').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Cancelar</button>
        <button type="button" onclick="salvarAula()" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">Salvar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Upload Material -->
<div id="modalUpload" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
    <h2 class="text-lg font-semibold">Enviar Material</h2>
    <form id="formUpload" class="space-y-3">
      <input type="hidden" name="id_aula" id="uploadAulaId">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Título do Material *</label>
        <input type="text" name="titulo" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
        <select name="tipo" id="tipoMaterial" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
          <option value="video">Vídeo (max 20MB)</option>
          <option value="pdf">PDF (max 20MB)</option>
          <option value="imagem">Imagem (max 10MB)</option>
          <option value="slide">Slide (max 20MB)</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Arquivo *</label>
        <input type="file" name="arquivo" id="inputArquivo" required class="w-full text-sm">
        <p id="dica" class="text-xs text-gray-400 mt-1"></p>
      </div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="document.getElementById('modalUpload').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Cancelar</button>
        <button type="button" id="btnEnviar" onclick="enviarMaterial()" class="px-4 py-2 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700">Enviar</button>
      </div>
    </form>
  </div>
</div>

<script>
function abrirUpload(aulaId) {
  document.getElementById('uploadAulaId').value = aulaId;
  document.getElementById('modalUpload').classList.remove('hidden');
}

document.getElementById('tipoMaterial').addEventListener('change', function() {
  const dicas = { video: 'MP4, AVI, MOV, WEBM', pdf: 'PDF', imagem: 'JPG, PNG, GIF, WEBP', slide: 'PPTX, PPT, PDF' };
  document.getElementById('dica').textContent = 'Formatos: ' + (dicas[this.value] || '');
});

async function salvarAula() {
  const fd = new FormData(document.getElementById('formAula'));
  const res = await fetch('/elearning/gestor/aulas/store', { method: 'POST', body: fd });
  const d = await res.json();
  if (d.success) { alert(d.message); location.reload(); } else alert('Erro: ' + d.message);
}

async function excluirAula(id, titulo) {
  if (!confirm(`Excluir a aula "${titulo}"?`)) return;
  const fd = new FormData(); fd.append('id', id);
  const res = await fetch('/elearning/gestor/aulas/delete', { method: 'POST', body: fd });
  const d = await res.json();
  if (d.success) { alert(d.message); location.reload(); } else alert('Erro: ' + d.message);
}

async function enviarMaterial() {
  const btn = document.getElementById('btnEnviar');
  btn.disabled = true; btn.textContent = 'Enviando...';
  const fd = new FormData(document.getElementById('formUpload'));
  try {
    const res = await fetch('/elearning/gestor/materiais/upload', { method: 'POST', body: fd });
    const d = await res.json();
    if (d.success) { alert('Material enviado com sucesso!'); location.reload(); }
    else alert('Erro: ' + d.message);
  } catch(e) { alert('Erro de conexão'); }
  finally { btn.disabled = false; btn.textContent = 'Enviar'; }
}
</script>
