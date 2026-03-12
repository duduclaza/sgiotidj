<?php
// views/pages/elearning/gestor/aulas.php
?>
<style>
  .el-fade-in { animation: elFadeIn .4s ease; }
  @keyframes elFadeIn { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
  .el-form-panel { max-height: 0; overflow: hidden; transition: max-height .5s cubic-bezier(.4,0,.2,1), opacity .3s; opacity: 0; }
  .el-form-panel.open { max-height: 600px; opacity: 1; }
  .el-upload-panel { max-height: 0; overflow: hidden; transition: max-height .4s ease, opacity .3s; opacity: 0; }
  .el-upload-panel.open { max-height: 2000px; opacity: 1; }
  .el-card { transition: transform .2s, box-shadow .2s; }
  .el-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.1); }
</style>

<div class="space-y-6 el-fade-in">

  <!-- Header -->
  <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
    <a href="/elearning/gestor/cursos" class="text-blue-200 hover:text-white text-sm transition">← Voltar aos Cursos</a>
    <h1 class="text-2xl font-bold mt-2 flex items-center gap-2">
      <span class="text-2xl">📖</span> Aulas — <?= htmlspecialchars($curso['titulo'] ?? '') ?>
    </h1>
    <p class="text-blue-100 text-sm mt-1"><?= count($aulas) ?> aula(s) cadastrada(s)</p>
  </div>

  <!-- Inline Form: Nova Aula -->
  <?php if ($canEdit): ?>
  <div>
    <button onclick="toggleAulaForm()" id="btnNovaAula"
      class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition shadow-md">
      <svg class="w-4 h-4 transition-transform" id="iconAula" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      <span id="btnAulaText">Nova Aula</span>
    </button>
    <div id="formAulaPanel" class="el-form-panel mt-3">
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <h3 class="font-bold text-gray-900 mb-4">Criar Nova Aula</h3>
        <form id="formAula" class="space-y-4">
          <input type="hidden" name="id_curso" value="<?= (int)($curso['id'] ?? 0) ?>">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
              <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Título *</label>
              <input type="text" name="titulo" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-gray-50/50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Ordem</label>
              <input type="number" name="ordem" value="<?= count($aulas) ?>" min="0" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-gray-50/50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </div>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Descrição</label>
            <textarea name="descricao" rows="2" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-gray-50/50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"></textarea>
          </div>
          <div class="flex justify-end gap-2">
            <button type="button" onclick="toggleAulaForm()" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">Cancelar</button>
            <button type="button" onclick="salvarAula()" class="px-5 py-2 text-sm font-bold bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 shadow transition">💾 Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Aulas List -->
  <?php if (empty($aulas)): ?>
  <div class="bg-white rounded-2xl shadow-md p-10 text-center">
    <div class="text-5xl mb-3">📖</div>
    <h3 class="text-lg font-semibold text-gray-600">Nenhuma aula cadastrada</h3>
    <p class="text-sm text-gray-400 mt-1">Adicione aulas para compor este curso</p>
  </div>
  <?php else: ?>
  <div class="space-y-4">
    <?php foreach ($aulas as $idx => $a): ?>
    <div class="el-card bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
      <div class="p-5">
        <div class="flex items-start justify-between">
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 font-bold text-sm flex-shrink-0">
              <?= $idx + 1 ?>
            </div>
            <div>
              <h3 class="font-bold text-gray-900"><?= htmlspecialchars($a['titulo']) ?></h3>
              <?php if ($a['descricao']): ?>
              <p class="text-sm text-gray-500 mt-0.5"><?= htmlspecialchars($a['descricao']) ?></p>
              <?php endif; ?>
              <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                <span>📎 <?= (int)($a['total_materiais'] ?? 0) ?> material(is)</span>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-2 ml-4 flex-shrink-0">
            <?php if ($canEdit): ?>
            <button onclick="toggleUpload(<?= (int)$a['id'] ?>)"
              class="text-xs font-medium text-purple-600 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-lg transition">
              📎 Material
            </button>
            <?php endif; ?>
            <?php if ($canDelete): ?>
            <button onclick="excluirAula(<?= (int)$a['id'] ?>, '<?= htmlspecialchars(addslashes($a['titulo'])) ?>')"
              class="text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition">
              🗑 Excluir
            </button>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Inline Upload Panel (materials list + add form) -->
      <div id="upload_<?= (int)$a['id'] ?>" class="el-upload-panel">
        <div class="border-t border-gray-100 bg-gray-50/70 p-5">

          <!-- Lista de materiais existentes -->
          <?php if (!empty($a['materiais'])): ?>
          <h4 class="text-sm font-bold text-gray-700 mb-3">📋 Materiais desta aula (<?= count($a['materiais']) ?>)</h4>
          <div class="space-y-2 mb-5 max-h-[300px] overflow-y-auto pr-1">
            <?php
              $tipoIcons = ['video'=>'🎬','pdf'=>'📄','imagem'=>'🖼️','slide'=>'📊','texto'=>'📝'];
              $tipoLabels = ['video'=>'Vídeo','pdf'=>'PDF','imagem'=>'Imagem','slide'=>'Slide','texto'=>'Texto'];
            ?>
            <?php foreach ($a['materiais'] as $mat): ?>
            <div class="flex items-center justify-between bg-white rounded-xl px-4 py-3 border border-gray-100 shadow-sm group hover:border-purple-200 transition">
              <div class="flex items-center gap-3 min-w-0 flex-1">
                <span class="text-lg flex-shrink-0"><?= $tipoIcons[$mat['tipo']] ?? '📎' ?></span>
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-semibold text-gray-800 truncate"><?= htmlspecialchars($mat['titulo']) ?></p>
                  <div class="flex items-center gap-2 text-[11px] text-gray-400 mt-0.5">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 font-medium"><?= $tipoLabels[$mat['tipo']] ?? $mat['tipo'] ?></span>
                    <?php if ($mat['tipo'] === 'texto'): ?>
                      <span class="truncate max-w-[200px]" title="<?= htmlspecialchars($mat['conteudo_texto'] ?? '') ?>"><?= htmlspecialchars(mb_substr($mat['conteudo_texto'] ?? '', 0, 60)) ?><?= mb_strlen($mat['conteudo_texto'] ?? '') > 60 ? '…' : '' ?></span>
                    <?php elseif ($mat['tamanho_bytes']): ?>
                      <span><?= number_format($mat['tamanho_bytes'] / 1024, 0) ?> KB</span>
                    <?php endif; ?>
                    <span><?= date('d/m/Y H:i', strtotime($mat['criado_em'])) ?></span>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                <?php if ($mat['tipo'] !== 'texto' && !empty($mat['arquivo_path'])): ?>
                <a href="<?= htmlspecialchars($mat['arquivo_path']) ?>" target="_blank"
                   class="text-xs text-blue-600 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded-lg transition opacity-0 group-hover:opacity-100">
                  👁 Ver
                </a>
                <?php endif; ?>
                <?php if ($canEdit): ?>
                <button onclick='editarMaterial(<?= (int)$a["id"] ?>, <?= json_encode(["id"=>(int)$mat["id"],"titulo"=>$mat["titulo"],"tipo"=>$mat["tipo"],"conteudo_texto"=>$mat["conteudo_texto"]??""]) ?>)'
                  class="text-xs text-amber-600 bg-amber-50 hover:bg-amber-100 px-2 py-1 rounded-lg transition opacity-0 group-hover:opacity-100">
                  ✏️
                </button>
                <?php endif; ?>
                <?php if ($canDelete): ?>
                <button onclick="excluirMaterial(<?= (int)$mat['id'] ?>, '<?= htmlspecialchars(addslashes($mat['titulo'])) ?>')"
                  class="text-xs text-red-600 bg-red-50 hover:bg-red-100 px-2 py-1 rounded-lg transition opacity-0 group-hover:opacity-100">
                  🗑
                </button>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <!-- Formulário de edição de material (hidden) -->
          <div id="editMatPanel_<?= (int)$a['id'] ?>" style="display:none;" class="mb-5 bg-amber-50/70 rounded-xl p-4 border border-amber-200">
            <h4 class="text-sm font-bold text-amber-800 mb-3">✏️ Editar Material</h4>
            <form id="formEditMat_<?= (int)$a['id'] ?>" class="space-y-3">
              <input type="hidden" name="id" id="editMatId_<?= (int)$a['id'] ?>">
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Título *</label>
                <input type="text" name="titulo" id="editMatTitulo_<?= (int)$a['id'] ?>" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-amber-400 transition">
              </div>
              <div id="editMatTextoField_<?= (int)$a['id'] ?>" style="display:none;">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Conteúdo do Texto *</label>
                <textarea name="conteudo_texto" id="editMatConteudo_<?= (int)$a['id'] ?>" rows="6" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-amber-400 transition resize-y"></textarea>
              </div>
              <div id="editMatFileField_<?= (int)$a['id'] ?>" style="display:none;">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Novo Arquivo (opcional — deixe vazio para manter o atual)</label>
                <input type="file" name="arquivo" class="w-full text-sm file:mr-2 file:rounded-lg file:border-0 file:bg-amber-500 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-amber-600 file:cursor-pointer">
              </div>
              <div class="flex justify-end gap-2">
                <button type="button" onclick="cancelarEdicao(<?= (int)$a['id'] ?>)" class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition">Cancelar</button>
                <button type="button" onclick="salvarEdicaoMaterial(<?= (int)$a['id'] ?>)" class="px-4 py-1.5 text-sm font-bold bg-amber-500 text-white rounded-lg hover:bg-amber-600 shadow transition">💾 Salvar</button>
              </div>
            </form>
          </div>

          <!-- Formulário para adicionar novo material -->
          <h4 class="text-sm font-bold text-gray-800 mb-3">➕ Adicionar Material — <?= htmlspecialchars($a['titulo']) ?></h4>
          <form id="formUpload_<?= (int)$a['id'] ?>" class="space-y-3">
            <input type="hidden" name="id_aula" value="<?= (int)$a['id'] ?>">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Título *</label>
                <input type="text" name="titulo" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-purple-500 transition">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Tipo *</label>
                <select name="tipo" onchange="toggleTipoMaterial(<?= (int)$a['id'] ?>, this.value)" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-purple-500 transition">
                  <option value="video">🎬 Vídeo (20MB)</option>
                  <option value="pdf">📄 PDF (20MB)</option>
                  <option value="imagem">🖼️ Imagem (10MB)</option>
                  <option value="slide">📊 Slide (20MB)</option>
                  <option value="texto">📝 Texto</option>
                </select>
              </div>
              <div id="fileField_<?= (int)$a['id'] ?>">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Arquivo *</label>
                <div id="pasteZone_<?= (int)$a['id'] ?>" class="paste-zone border-2 border-dashed border-gray-300 rounded-xl p-3 text-center cursor-pointer hover:border-purple-400 hover:bg-purple-50/30 transition relative"
                     onclick="document.getElementById('fileInput_<?= (int)$a['id'] ?>').click()"
                     tabindex="0">
                  <input type="file" name="arquivo" id="fileInput_<?= (int)$a['id'] ?>" class="hidden" onchange="onFileSelected(<?= (int)$a['id'] ?>, this)">
                  <div id="pasteHint_<?= (int)$a['id'] ?>" class="text-xs text-gray-400">
                    <span class="text-lg">📋</span><br>
                    <span class="font-medium text-gray-500">Ctrl+V</span> para colar · arrastar · ou <span class="text-purple-600 font-semibold underline">clique aqui</span>
                  </div>
                  <div id="pastePreview_<?= (int)$a['id'] ?>" style="display:none;" class="flex items-center gap-3">
                    <img id="pasteImg_<?= (int)$a['id'] ?>" class="w-12 h-12 object-cover rounded-lg border" src="" alt="">
                    <span id="pasteName_<?= (int)$a['id'] ?>" class="text-xs text-gray-600 truncate flex-1"></span>
                    <button type="button" onclick="event.stopPropagation(); clearPaste(<?= (int)$a['id'] ?>)" class="text-red-400 hover:text-red-600 text-sm">✕</button>
                  </div>
                </div>
              </div>
            </div>
            <!-- Textarea para tipo texto (hidden por padrão) -->
            <div id="textoField_<?= (int)$a['id'] ?>" style="display:none;" class="mt-3">
              <label class="block text-xs font-semibold text-gray-500 mb-1">Conteúdo do Texto *</label>
              <textarea name="conteudo_texto" rows="6" placeholder="Digite o conteúdo do material aqui... Suporta texto longo, instruções, orientações, etc."
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-purple-500 transition resize-y"></textarea>
            </div>
            <div class="flex justify-end gap-2 mt-3">
              <button type="button" onclick="toggleUpload(<?= (int)$a['id'] ?>)" class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition">Cancelar</button>
              <button type="button" onclick="enviarMaterial(<?= (int)$a['id'] ?>)" class="px-4 py-1.5 text-sm font-bold bg-purple-600 text-white rounded-lg hover:bg-purple-700 shadow transition">📤 Enviar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<script>
function toggleAulaForm() {
  const p = document.getElementById('formAulaPanel');
  const i = document.getElementById('iconAula');
  const t = document.getElementById('btnAulaText');
  if (p.classList.contains('open')) {
    p.classList.remove('open'); i.style.transform='rotate(0)'; t.textContent='Nova Aula';
  } else {
    p.classList.add('open'); i.style.transform='rotate(45deg)'; t.textContent='Fechar';
  }
}

function toggleUpload(id) {
  const p = document.getElementById('upload_' + id);
  p.classList.toggle('open');
}

function showToast(msg, type) {
  const e = document.getElementById('elToast'); if (e) e.remove();
  const c = { success:'bg-green-600', error:'bg-red-600' };
  const d = document.createElement('div'); d.id='elToast';
  d.className = `fixed bottom-6 right-6 z-[100] ${c[type]||'bg-indigo-600'} text-white px-5 py-3 rounded-xl shadow-2xl text-sm font-medium`;
  d.style.animation = 'elFadeIn .3s ease'; d.textContent = msg;
  document.body.appendChild(d); setTimeout(()=>d.remove(), 3500);
}

async function salvarAula() {
  const fd = new FormData(document.getElementById('formAula'));
  try {
    const res = await fetch('/elearning/gestor/aulas/store', { method:'POST', body:fd });
    const d = await res.json();
    if (d.success) { showToast(d.message,'success'); setTimeout(()=>location.reload(),800); }
    else showToast('Erro: '+d.message,'error');
  } catch(e) { showToast('Erro de conexão','error'); }
}

async function excluirAula(id, titulo) {
  if (!confirm(`Excluir a aula "${titulo}"?`)) return;
  const fd = new FormData(); fd.append('id', id);
  try {
    const res = await fetch('/elearning/gestor/aulas/delete', { method:'POST', body:fd });
    const d = await res.json();
    if (d.success) { showToast(d.message,'success'); setTimeout(()=>location.reload(),800); }
    else showToast('Erro: '+d.message,'error');
  } catch(e) { showToast('Erro de conexão','error'); }
}

async function excluirMaterial(id, titulo) {
  if (!confirm(`Excluir o material "${titulo}"?`)) return;
  const fd = new FormData(); fd.append('id', id);
  try {
    const res = await fetch('/elearning/gestor/materiais/delete', { method:'POST', body:fd });
    const d = await res.json();
    if (d.success) { showToast(d.message,'success'); setTimeout(()=>location.reload(),800); }
    else showToast('Erro: '+d.message,'error');
  } catch(e) { showToast('Erro de conexão','error'); }
}

function editarMaterial(aulaId, mat) {
  // Abrir o painel da aula se não estiver aberto
  const panel = document.getElementById('upload_' + aulaId);
  if (!panel.classList.contains('open')) panel.classList.add('open');
  // Mostrar formulário de edição
  const editPanel = document.getElementById('editMatPanel_' + aulaId);
  editPanel.style.display = '';
  // Preencher campos
  document.getElementById('editMatId_' + aulaId).value = mat.id;
  document.getElementById('editMatTitulo_' + aulaId).value = mat.titulo;
  // Mostrar campo correto: texto ou arquivo
  const textoField = document.getElementById('editMatTextoField_' + aulaId);
  const fileField = document.getElementById('editMatFileField_' + aulaId);
  if (mat.tipo === 'texto') {
    textoField.style.display = '';
    fileField.style.display = 'none';
    document.getElementById('editMatConteudo_' + aulaId).value = mat.conteudo_texto || '';
  } else {
    textoField.style.display = 'none';
    fileField.style.display = '';
  }
  // Scroll suave até o edit panel
  editPanel.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function cancelarEdicao(aulaId) {
  document.getElementById('editMatPanel_' + aulaId).style.display = 'none';
}

async function salvarEdicaoMaterial(aulaId) {
  const fd = new FormData(document.getElementById('formEditMat_' + aulaId));
  try {
    const res = await fetch('/elearning/gestor/materiais/update', { method:'POST', body:fd });
    const d = await res.json();
    if (d.success) { showToast(d.message,'success'); setTimeout(()=>location.reload(),800); }
    else showToast('Erro: '+d.message,'error');
  } catch(e) { showToast('Erro de conexão','error'); }
}

async function enviarMaterial(aulaId) {
  const fd = new FormData(document.getElementById('formUpload_' + aulaId));
  try {
    const res = await fetch('/elearning/gestor/materiais/upload', { method:'POST', body:fd });
    const d = await res.json();
    if (d.success) { showToast('Material enviado!','success'); setTimeout(()=>location.reload(),800); }
    else showToast('Erro: '+d.message,'error');
  } catch(e) { showToast('Erro de conexão','error'); }
}

function toggleTipoMaterial(aulaId, tipo) {
  const fileField = document.getElementById('fileField_' + aulaId);
  const textoField = document.getElementById('textoField_' + aulaId);
  if (tipo === 'texto') {
    fileField.style.display = 'none';
    textoField.style.display = '';
    clearPaste(aulaId);
  } else {
    fileField.style.display = '';
    textoField.style.display = 'none';
    const ta = textoField.querySelector('textarea');
    if (ta) ta.value = '';
  }
}

// === PASTE / DROP / FILE HANDLING ===

function showPastePreview(aulaId, file) {
  const hint = document.getElementById('pasteHint_' + aulaId);
  const preview = document.getElementById('pastePreview_' + aulaId);
  const img = document.getElementById('pasteImg_' + aulaId);
  const name = document.getElementById('pasteName_' + aulaId);
  if (!hint || !preview) return;
  // Se for imagem, mostrar thumbnail
  if (file.type.startsWith('image/')) {
    const reader = new FileReader();
    reader.onload = e => { img.src = e.target.result; img.style.display = ''; };
    reader.readAsDataURL(file);
  } else {
    img.style.display = 'none';
  }
  const sizeKB = (file.size / 1024).toFixed(0);
  name.textContent = `${file.name} (${sizeKB} KB)`;
  hint.style.display = 'none';
  preview.style.display = 'flex';
}

function clearPaste(aulaId) {
  const hint = document.getElementById('pasteHint_' + aulaId);
  const preview = document.getElementById('pastePreview_' + aulaId);
  const fi = document.getElementById('fileInput_' + aulaId);
  if (hint) hint.style.display = '';
  if (preview) preview.style.display = 'none';
  if (fi) fi.value = '';
}

function onFileSelected(aulaId, input) {
  if (input.files && input.files[0]) {
    showPastePreview(aulaId, input.files[0]);
  }
}

function setFileOnInput(aulaId, file) {
  const fi = document.getElementById('fileInput_' + aulaId);
  if (!fi) return;
  const dt = new DataTransfer();
  dt.items.add(file);
  fi.files = dt.files;
  showPastePreview(aulaId, file);
}

// Encontrar o aulaId do painel de upload aberto
function findOpenUploadAulaId() {
  const panels = document.querySelectorAll('.el-upload-panel.open');
  for (const p of panels) {
    const id = p.id.replace('upload_', '');
    if (id) return parseInt(id);
  }
  return null;
}

// Global paste listener
document.addEventListener('paste', function(e) {
  const aulaId = findOpenUploadAulaId();
  if (!aulaId) return;
  // Verificar se tipo é imagem ou arquivo
  const fileField = document.getElementById('fileField_' + aulaId);
  if (!fileField || fileField.style.display === 'none') return;
  const items = e.clipboardData?.items;
  if (!items) return;
  for (const item of items) {
    if (item.kind === 'file') {
      e.preventDefault();
      const file = item.getAsFile();
      if (file) {
        // Renomear se não tem nome decente
        const ext = file.type.split('/')[1] || 'png';
        const named = new File([file], `colado_${Date.now()}.${ext}`, { type: file.type });
        setFileOnInput(aulaId, named);
        showToast('Imagem colada! ✅', 'success');
      }
      return;
    }
  }
});

// Drag and drop nos paste zones
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.paste-zone').forEach(zone => {
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('border-purple-500','bg-purple-50'); });
    zone.addEventListener('dragleave', e => { zone.classList.remove('border-purple-500','bg-purple-50'); });
    zone.addEventListener('drop', e => {
      e.preventDefault();
      zone.classList.remove('border-purple-500','bg-purple-50');
      const aulaId = zone.id.replace('pasteZone_', '');
      if (e.dataTransfer.files && e.dataTransfer.files[0]) {
        setFileOnInput(parseInt(aulaId), e.dataTransfer.files[0]);
      }
    });
  });
});
</script>
