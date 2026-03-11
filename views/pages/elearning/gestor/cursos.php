<?php
// views/pages/elearning/gestor/cursos.php
?>
<style>
  .el-fade-in { animation: elFadeIn .4s ease; }
  @keyframes elFadeIn { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
  .el-card { transition: transform .2s, box-shadow .2s; }
  .el-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,.12); }
  .el-gradient-header { background: linear-gradient(135deg, #1e40af 0%, #6366f1 50%, #8b5cf6 100%); }
  .el-form-panel { max-height: 0; overflow: hidden; transition: max-height .5s cubic-bezier(.4,0,.2,1), opacity .3s; opacity: 0; }
  .el-form-panel.open { max-height: 2400px; opacity: 1; }
  .el-badge { font-size: .65rem; letter-spacing: .05em; }
  .el-stat-card { backdrop-filter: blur(10px); background: rgba(255,255,255,.85); border: 1px solid rgba(255,255,255,.5); }
  .el-thumb { background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%); }
  .el-search:focus { box-shadow: 0 0 0 3px rgba(99,102,241,.2); }
  /* Thumbnail Gallery */
  .thumb-gallery { max-height: 360px; overflow-y: auto; scrollbar-width: thin; }
  .thumb-gallery::-webkit-scrollbar { width: 6px; }
  .thumb-gallery::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }
  .thumb-gallery::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
  .thumb-gallery::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  .thumb-item { position: relative; cursor: pointer; border-radius: 10px; overflow: hidden; aspect-ratio: 16/9; border: 2px solid transparent; transition: all .2s; }
  .thumb-item:hover { border-color: rgba(99,102,241,.4); transform: scale(1.03); z-index: 1; }
  .thumb-item.selected { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,.3); }
  .thumb-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .thumb-item .label { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,.7)); color: white; font-size: 9px; font-weight: 700; padding: 12px 6px 4px; }
  .thumb-item .check { position: absolute; top: 4px; right: 4px; width: 22px; height: 22px; border-radius: 50%; background: #6366f1; color: white; font-size: 11px; display: none; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,.3); }
  .thumb-item.selected .check { display: flex; }
  /* Section card */
  .form-section { background: white; border-radius: 16px; border: 1px solid #e5e7eb; overflow: hidden; }
  .form-section-header { padding: 14px 20px; border-bottom: 1px solid #f3f4f6; background: #fafbfc; display: flex; align-items: center; gap: 10px; }
  .form-section-header h3 { font-weight: 700; font-size: 14px; color: #111827; }
  .form-section-body { padding: 20px; }
  /* Upload drag zone */
  .upload-zone { border: 2px dashed #d1d5db; border-radius: 12px; padding: 24px; text-align: center; transition: all .3s; cursor: pointer; background: #fafbfc; }
  .upload-zone:hover, .upload-zone.dragover { border-color: #6366f1; background: #eef2ff; }
  .upload-preview { display: none; }
  .upload-preview.active { display: block; }
</style>

<div class="space-y-6 el-fade-in">

  <!-- Header -->
  <div class="el-gradient-header rounded-2xl p-6 sm:p-8 text-white shadow-lg">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight flex items-center gap-2">
          <span class="text-3xl">📚</span> Cursos — eLearning
        </h1>
        <p class="text-blue-100 text-sm mt-1">Gerencie todos os cursos da plataforma</p>
      </div>
      <?php if ($canEdit): ?>
      <button onclick="toggleFormPanel()" id="btnNovoCurso" class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 backdrop-blur text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition border border-white/30 shadow-sm">
        <svg class="w-5 h-5 transition-transform" id="iconPlus" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span id="btnNovoCursoText">Novo Curso</span>
      </button>
      <?php endif; ?>
    </div>
  </div>

  <!-- ===== INLINE FORM PANEL ===== -->
  <?php if ($canEdit): ?>
  <div id="formPanel" class="el-form-panel">
    <div class="bg-gradient-to-br from-indigo-50 via-white to-blue-50 rounded-2xl shadow-xl border border-indigo-100 p-5 sm:p-6 space-y-5">

      <!-- Form Title -->
      <div class="flex items-center justify-between">
        <div>
          <h2 id="formPanelTitle" class="text-xl font-bold text-gray-900 flex items-center gap-2">🎓 Criar Novo Curso</h2>
          <p class="text-xs text-gray-500 mt-0.5">Preencha as informações e escolha uma imagem de capa</p>
        </div>
        <button type="button" onclick="cancelarForm()" class="text-gray-400 hover:text-gray-600 transition p-1">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <form id="formCurso" enctype="multipart/form-data">
        <input type="hidden" name="id" id="cursoId">
        <input type="hidden" name="thumbnail_url" id="thumbUrlInput" value="">

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

          <!-- LEFT: Form Fields (3/5) -->
          <div class="lg:col-span-3 space-y-5">

            <!-- Section: Informações Básicas -->
            <div class="form-section">
              <div class="form-section-header">
                <span class="w-7 h-7 bg-indigo-100 rounded-lg flex items-center justify-center text-sm">📝</span>
                <h3>Informações Básicas</h3>
              </div>
              <div class="form-section-body space-y-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Título do Curso *</label>
                  <input type="text" name="titulo" id="cursoTitulo" required
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white"
                    placeholder="Ex.: Eletrocardiograma — Fundamentos">
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Descrição</label>
                  <textarea name="descricao" id="cursoDescricao" rows="3"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white"
                    placeholder="Resumo do curso, público-alvo e resultado esperado..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Carga Horária (min)</label>
                    <input type="number" name="carga_horaria" id="cursoCarga" min="0"
                      class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white"
                      placeholder="120">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                    <select name="status" id="cursoStatus"
                      class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white">
                      <option value="rascunho">📝 Rascunho</option>
                      <option value="ativo">✅ Ativo</option>
                      <option value="inativo">⏸ Inativo</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section: Upload Personalizado -->
            <div class="form-section">
              <div class="form-section-header">
                <span class="w-7 h-7 bg-purple-100 rounded-lg flex items-center justify-center text-sm">📤</span>
                <h3>Upload Personalizado</h3>
                <span class="text-[10px] text-gray-400 ml-auto">Ou selecione da biblioteca →</span>
              </div>
              <div class="form-section-body">
                <div id="uploadZone" class="upload-zone" onclick="document.getElementById('fileInput').click()"
                     ondragover="event.preventDefault(); this.classList.add('dragover')"
                     ondragleave="this.classList.remove('dragover')"
                     ondrop="event.preventDefault(); this.classList.remove('dragover'); handleFileDrop(event)">
                  <div id="uploadPrompt">
                    <div class="text-4xl mb-2">☁️</div>
                    <p class="text-sm font-semibold text-gray-600">Arraste uma imagem aqui ou clique para selecionar</p>
                    <p class="text-[10px] text-gray-400 mt-1">JPG, PNG, WebP • Até 10MB • Recomendado: 1280×720px</p>
                  </div>
                  <div id="uploadPreview" class="upload-preview">
                    <img id="uploadPreviewImg" src="" alt="" class="w-full h-32 object-cover rounded-lg mb-2">
                    <p id="uploadFileName" class="text-xs font-semibold text-gray-700"></p>
                    <button type="button" onclick="event.stopPropagation(); clearUpload()" class="mt-1 text-xs text-red-500 hover:text-red-700 transition font-medium">✕ Remover</button>
                  </div>
                </div>
                <input type="file" name="thumbnail" id="fileInput" accept="image/*" class="hidden" onchange="handleFileSelect(this)">
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3">
              <button type="button" onclick="salvarCurso()" id="btnSalvar"
                class="flex-1 sm:flex-none px-8 py-3 text-sm font-bold bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-xl hover:from-indigo-700 hover:to-blue-700 shadow-lg hover:shadow-xl transition-all">
                💾 Salvar Curso
              </button>
              <button type="button" onclick="cancelarForm()"
                class="px-5 py-3 text-sm font-medium border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-100 transition">
                Cancelar
              </button>
            </div>
          </div>

          <!-- RIGHT: Image Library (2/5) -->
          <div class="lg:col-span-2">
            <div class="form-section sticky top-4">
              <div class="form-section-header">
                <span class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center text-sm">🖼️</span>
                <h3>Biblioteca de Imagens</h3>
                <span id="libCount" class="ml-auto text-[10px] text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full font-medium"></span>
              </div>
              <div class="p-3">
                <!-- Selected Preview -->
                <div id="thumbPreview" class="hidden mb-3">
                  <div class="relative rounded-xl overflow-hidden shadow-md border-2 border-indigo-400">
                    <img id="thumbPreviewImg" src="" alt="" class="w-full h-28 object-cover">
                    <button type="button" onclick="clearThumb()" class="absolute top-2 right-2 bg-white/90 hover:bg-white text-gray-700 w-7 h-7 rounded-full text-xs font-bold shadow-md transition flex items-center justify-center">✕</button>
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-indigo-600/90 to-transparent p-2 pt-6">
                      <span class="text-white text-[10px] font-bold">✓ IMAGEM SELECIONADA</span>
                    </div>
                  </div>
                </div>

                <!-- Categories -->
                <div class="flex flex-wrap gap-1 mb-2">
                  <button type="button" onclick="filterLibrary('all')" class="lib-cat active text-[9px] font-bold px-2 py-1 rounded-full bg-indigo-600 text-white transition" data-cat="all">Todas</button>
                  <button type="button" onclick="filterLibrary('saude')" class="lib-cat text-[9px] font-bold px-2 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition" data-cat="saude">🏥</button>
                  <button type="button" onclick="filterLibrary('tech')" class="lib-cat text-[9px] font-bold px-2 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition" data-cat="tech">💻</button>
                  <button type="button" onclick="filterLibrary('negocios')" class="lib-cat text-[9px] font-bold px-2 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition" data-cat="negocios">📊</button>
                  <button type="button" onclick="filterLibrary('industria')" class="lib-cat text-[9px] font-bold px-2 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition" data-cat="industria">🏭</button>
                  <button type="button" onclick="filterLibrary('educacao')" class="lib-cat text-[9px] font-bold px-2 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition" data-cat="educacao">📚</button>
                  <button type="button" onclick="filterLibrary('seguranca')" class="lib-cat text-[9px] font-bold px-2 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition" data-cat="seguranca">🛡️</button>
                  <button type="button" onclick="filterLibrary('qualidade')" class="lib-cat text-[9px] font-bold px-2 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition" data-cat="qualidade">✅</button>
                  <button type="button" onclick="filterLibrary('lideranca')" class="lib-cat text-[9px] font-bold px-2 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition" data-cat="lideranca">👔</button>
                  <button type="button" onclick="filterLibrary('ambiente')" class="lib-cat text-[9px] font-bold px-2 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition" data-cat="ambiente">🌱</button>
                  <button type="button" onclick="filterLibrary('criativo')" class="lib-cat text-[9px] font-bold px-2 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition" data-cat="criativo">🎨</button>
                </div>

                <!-- Image Grid -->
                <div id="libGrid" class="thumb-gallery grid grid-cols-3 gap-1.5">
                  <!-- Images injected by JS -->
                </div>
                <p class="text-[9px] text-gray-400 mt-2 text-center">📸 Imagens Unsplash • Licença livre</p>
              </div>
            </div>
          </div>

        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- Search Bar -->
  <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">
    <div class="flex-1 relative">
      <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      <input type="text" id="searchCursos" onkeyup="filtrarCursos()" placeholder="Buscar cursos..."
        class="el-search w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
    </div>
    <select id="filterStatus" onchange="filtrarCursos()" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-indigo-500 transition">
      <option value="">Todos os status</option>
      <option value="ativo">✅ Ativo</option>
      <option value="rascunho">📝 Rascunho</option>
      <option value="inativo">⏸ Inativo</option>
    </select>
  </div>

  <!-- Stats -->
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    <?php
      $totalAtivos = 0; $totalRascunho = 0; $totalInativos = 0;
      foreach ($cursos as $c) {
        if ($c['status'] === 'ativo') $totalAtivos++;
        elseif ($c['status'] === 'rascunho') $totalRascunho++;
        else $totalInativos++;
      }
    ?>
    <div class="el-stat-card rounded-xl p-4 text-center">
      <div class="text-2xl font-bold text-indigo-600"><?= count($cursos) ?></div>
      <div class="text-xs text-gray-500 mt-0.5">Total</div>
    </div>
    <div class="el-stat-card rounded-xl p-4 text-center">
      <div class="text-2xl font-bold text-green-600"><?= $totalAtivos ?></div>
      <div class="text-xs text-gray-500 mt-0.5">Ativos</div>
    </div>
    <div class="el-stat-card rounded-xl p-4 text-center">
      <div class="text-2xl font-bold text-yellow-600"><?= $totalRascunho ?></div>
      <div class="text-xs text-gray-500 mt-0.5">Rascunhos</div>
    </div>
    <div class="el-stat-card rounded-xl p-4 text-center">
      <div class="text-2xl font-bold text-gray-500"><?= $totalInativos ?></div>
      <div class="text-xs text-gray-500 mt-0.5">Inativos</div>
    </div>
  </div>

  <!-- Course Grid -->
  <?php if (empty($cursos)): ?>
  <div class="bg-white rounded-2xl shadow p-12 text-center">
    <div class="text-5xl mb-4">📚</div>
    <h3 class="text-lg font-semibold text-gray-700 mb-1">Nenhum curso cadastrado</h3>
    <p class="text-sm text-gray-400">Clique em "Novo Curso" para começar!</p>
  </div>
  <?php else: ?>
  <div id="cursosGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php foreach ($cursos as $c): ?>
    <?php
      $statusConfig = [
        'ativo'    => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'icon' => '✅'],
        'rascunho' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'icon' => '📝'],
        'inativo'  => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'icon' => '⏸'],
      ];
      $sc = $statusConfig[$c['status']] ?? $statusConfig['inativo'];
    ?>
    <div class="el-card curso-card bg-white rounded-2xl shadow-md overflow-hidden flex flex-col border border-gray-100"
         data-titulo="<?= htmlspecialchars(mb_strtolower($c['titulo'])) ?>"
         data-status="<?= htmlspecialchars($c['status']) ?>">
      <?php if (!empty($c['has_thumbnail'])): ?>
      <div class="h-40 overflow-hidden">
        <img src="/elearning/gestor/cursos/thumbnail?id=<?= (int)$c['id'] ?>" alt="<?= htmlspecialchars($c['titulo']) ?>"
          class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
      </div>
      <?php else: ?>
      <div class="el-thumb h-40 flex items-center justify-center">
        <span class="text-5xl text-white/80">🎓</span>
      </div>
      <?php endif; ?>
      <div class="p-5 flex flex-col flex-1">
        <div class="flex items-start justify-between gap-2 mb-2">
          <h3 class="font-bold text-gray-900 text-sm leading-tight line-clamp-2"><?= htmlspecialchars($c['titulo']) ?></h3>
          <span class="el-badge flex-shrink-0 px-2 py-0.5 rounded-full font-bold <?= $sc['bg'] ?> <?= $sc['text'] ?>">
            <?= $sc['icon'] ?> <?= strtoupper($c['status']) ?>
          </span>
        </div>
        <?php if ($c['descricao']): ?>
        <p class="text-xs text-gray-500 mb-3 line-clamp-2"><?= htmlspecialchars($c['descricao']) ?></p>
        <?php endif; ?>
        <div class="flex items-center gap-3 text-xs text-gray-400 mt-auto pt-3 border-t border-gray-100">
          <span title="Carga Horária">⏱ <?= (int)$c['carga_horaria'] ?> min</span>
          <span title="Aulas">📖 <?= (int)($c['total_aulas'] ?? 0) ?></span>
          <span title="Matrículas">👥 <?= (int)($c['total_matriculas'] ?? 0) ?></span>
        </div>
        <div class="text-xs text-gray-400 mt-2">
          por <span class="font-medium text-gray-500"><?= htmlspecialchars($c['gestor_nome'] ?? 'N/A') ?></span>
        </div>
        <div class="flex flex-wrap items-center gap-2 mt-4 pt-3 border-t border-gray-100">
          <a href="/elearning/gestor/cursos/<?= (int)$c['id'] ?>/aulas" class="text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded-lg transition">📖 Aulas</a>
          <a href="/elearning/gestor/cursos/<?= (int)$c['id'] ?>/provas" class="text-xs font-medium text-purple-600 bg-purple-50 hover:bg-purple-100 px-2.5 py-1 rounded-lg transition">📝 Provas</a>
          <a href="/elearning/gestor/cursos/<?= (int)$c['id'] ?>/matriculas" class="text-xs font-medium text-green-600 bg-green-50 hover:bg-green-100 px-2.5 py-1 rounded-lg transition">👥 Matrículas</a>
          <a href="/elearning/gestor/cursos/<?= (int)$c['id'] ?>/progresso" class="text-xs font-medium text-teal-600 bg-teal-50 hover:bg-teal-100 px-2.5 py-1 rounded-lg transition">📊 Progresso</a>
          <?php if ($canEdit): ?>
          <button onclick='editarCurso(<?= json_encode($c) ?>)' class="text-xs font-medium text-orange-600 bg-orange-50 hover:bg-orange-100 px-2.5 py-1 rounded-lg transition ml-auto">✏️ Editar</button>
          <?php endif; ?>
          <?php if ($canDelete): ?>
          <button onclick='excluirCurso(<?= (int)$c["id"] ?>, "<?= htmlspecialchars(addslashes($c["titulo"])) ?>")' class="text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 px-2.5 py-1 rounded-lg transition">🗑 Excluir</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<script>
// ===== IMAGE LIBRARY DATA (80+ images) =====
const THUMB_LIBRARY = [
  // 🏥 Saúde (12)
  {url:'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=640&h=360&fit=crop',cat:'saude',label:'ECG / Monitor Cardíaco'},
  {url:'https://images.unsplash.com/photo-1579684385127-1ef15d508118?w=640&h=360&fit=crop',cat:'saude',label:'Laboratório Clínico'},
  {url:'https://images.unsplash.com/photo-1551190822-a9ce113ac100?w=640&h=360&fit=crop',cat:'saude',label:'Saúde Digital'},
  {url:'https://images.unsplash.com/photo-1559757175-5700dde675bc?w=640&h=360&fit=crop',cat:'saude',label:'Primeiro Socorro'},
  {url:'https://images.unsplash.com/photo-1530497610245-94d3c16cda28?w=640&h=360&fit=crop',cat:'saude',label:'Estetoscópio'},
  {url:'https://images.unsplash.com/photo-1631815588090-d4bfec5b1ccb?w=640&h=360&fit=crop',cat:'saude',label:'Coração / ECG'},
  {url:'https://images.unsplash.com/photo-1584820927498-cfe5211fd8bf?w=640&h=360&fit=crop',cat:'saude',label:'Bem-Estar'},
  {url:'https://images.unsplash.com/photo-1526256262350-7da7584cf5eb?w=640&h=360&fit=crop',cat:'saude',label:'Farmácia'},
  {url:'https://images.unsplash.com/photo-1538108149393-fbbd81895907?w=640&h=360&fit=crop',cat:'saude',label:'Hospital'},
  {url:'https://images.unsplash.com/photo-1516549655169-df83a0774514?w=640&h=360&fit=crop',cat:'saude',label:'Enfermeira'},
  {url:'https://images.unsplash.com/photo-1581595220892-b0739db3ba8c?w=640&h=360&fit=crop',cat:'saude',label:'Médico / Consulta'},
  {url:'https://images.unsplash.com/photo-1532938911079-1b06ac7ceec7?w=640&h=360&fit=crop',cat:'saude',label:'Paciente'},

  // 💻 Tecnologia (10)
  {url:'https://images.unsplash.com/photo-1518770660439-4636190af475?w=640&h=360&fit=crop',cat:'tech',label:'Circuito Eletrônico'},
  {url:'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=640&h=360&fit=crop',cat:'tech',label:'Programação'},
  {url:'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=640&h=360&fit=crop',cat:'tech',label:'Cybersecurity'},
  {url:'https://images.unsplash.com/photo-1488590528505-98d2b5aba04b?w=640&h=360&fit=crop',cat:'tech',label:'Computador / Tela'},
  {url:'https://images.unsplash.com/photo-1573164713988-8665fc963095?w=640&h=360&fit=crop',cat:'tech',label:'Data Science'},
  {url:'https://images.unsplash.com/photo-1555949963-ff9fe0c870eb?w=640&h=360&fit=crop',cat:'tech',label:'Código / Dev'},
  {url:'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=640&h=360&fit=crop',cat:'tech',label:'Rede Global'},
  {url:'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=640&h=360&fit=crop',cat:'tech',label:'Matriz / IA'},
  {url:'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=640&h=360&fit=crop',cat:'tech',label:'Robô / IA'},
  {url:'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=640&h=360&fit=crop',cat:'tech',label:'Servidor / Cloud'},

  // 📊 Negócios (10)
  {url:'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=640&h=360&fit=crop',cat:'negocios',label:'Analytics / Dashboard'},
  {url:'https://images.unsplash.com/photo-1553877522-43269d4ea984?w=640&h=360&fit=crop',cat:'negocios',label:'Reunião de Equipe'},
  {url:'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=640&h=360&fit=crop',cat:'negocios',label:'Apresentação'},
  {url:'https://images.unsplash.com/photo-1590650153855-d9e808231d41?w=640&h=360&fit=crop',cat:'negocios',label:'Gráficos Financeiros'},
  {url:'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=640&h=360&fit=crop',cat:'negocios',label:'Equipe / Team'},
  {url:'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=640&h=360&fit=crop',cat:'negocios',label:'Executivo'},
  {url:'https://images.unsplash.com/photo-1664575602554-2087b04935a5?w=640&h=360&fit=crop',cat:'negocios',label:'Mulher Líder'},
  {url:'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=640&h=360&fit=crop',cat:'negocios',label:'Escritório Moderno'},
  {url:'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=640&h=360&fit=crop',cat:'negocios',label:'Planejamento'},
  {url:'https://images.unsplash.com/photo-1591696205602-2f950c417cb9?w=640&h=360&fit=crop',cat:'negocios',label:'Contrato'},

  // 🏭 Indústria (8)
  {url:'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=640&h=360&fit=crop',cat:'industria',label:'Microscópio / Lab'},
  {url:'https://images.unsplash.com/photo-1565043666747-69f6646db940?w=640&h=360&fit=crop',cat:'industria',label:'Fábrica'},
  {url:'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?w=640&h=360&fit=crop',cat:'industria',label:'Engenharia'},
  {url:'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=640&h=360&fit=crop',cat:'industria',label:'Robótica Industrial'},
  {url:'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=640&h=360&fit=crop',cat:'industria',label:'Impressora 3D'},
  {url:'https://images.unsplash.com/photo-1567789884554-0b308d79bc62?w=640&h=360&fit=crop',cat:'industria',label:'Produção'},
  {url:'https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?w=640&h=360&fit=crop',cat:'industria',label:'Soldagem'},
  {url:'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=640&h=360&fit=crop',cat:'industria',label:'Logística'},

  // 📚 Educação (8)
  {url:'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=640&h=360&fit=crop',cat:'educacao',label:'Sala de Aula'},
  {url:'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=640&h=360&fit=crop',cat:'educacao',label:'Aprendizado'},
  {url:'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=640&h=360&fit=crop',cat:'educacao',label:'Estudando'},
  {url:'https://images.unsplash.com/photo-1513258496099-48168024aec0?w=640&h=360&fit=crop',cat:'educacao',label:'Laptop / Estudo'},
  {url:'https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?w=640&h=360&fit=crop',cat:'educacao',label:'Universidade'},
  {url:'https://images.unsplash.com/photo-1509869175650-a1d97972541a?w=640&h=360&fit=crop',cat:'educacao',label:'Biblioteca'},
  {url:'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=640&h=360&fit=crop',cat:'educacao',label:'Livros'},
  {url:'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=640&h=360&fit=crop',cat:'educacao',label:'Grupo de Estudo'},

  // 🛡️ Segurança (6)
  {url:'https://images.unsplash.com/photo-1558002038-1055907df827?w=640&h=360&fit=crop',cat:'seguranca',label:'Capacete EPI'},
  {url:'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=640&h=360&fit=crop',cat:'seguranca',label:'Cadeado Digital'},
  {url:'https://images.unsplash.com/photo-1614064641938-3bbee52942c7?w=640&h=360&fit=crop',cat:'seguranca',label:'Firewall'},
  {url:'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=640&h=360&fit=crop',cat:'seguranca',label:'Segurança TI'},
  {url:'https://images.unsplash.com/photo-1621252179027-94459d278660?w=640&h=360&fit=crop',cat:'seguranca',label:'Colete Segurança'},
  {url:'https://images.unsplash.com/photo-1590859808308-3d2d9c515b1a?w=640&h=360&fit=crop',cat:'seguranca',label:'Extintor'},

  // ✅ Qualidade (6)
  {url:'https://images.unsplash.com/photo-1434626881859-194d67b2b86f?w=640&h=360&fit=crop',cat:'qualidade',label:'Checklist'},
  {url:'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=640&h=360&fit=crop',cat:'qualidade',label:'Dashboard KPI'},
  {url:'https://images.unsplash.com/photo-1606857521015-7f9fcf423740?w=640&h=360&fit=crop',cat:'qualidade',label:'Certificação ISO'},
  {url:'https://images.unsplash.com/photo-1586953208270-767889fa9b6f?w=640&h=360&fit=crop',cat:'qualidade',label:'Inspeção Lupa'},
  {url:'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=640&h=360&fit=crop',cat:'qualidade',label:'Auditoria'},
  {url:'https://images.unsplash.com/photo-1552581234-26160f608093?w=640&h=360&fit=crop',cat:'qualidade',label:'Whiteboard / Lean'},

  // 👔 Liderança (8)
  {url:'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=640&h=360&fit=crop',cat:'lideranca',label:'Colaboração'},
  {url:'https://images.unsplash.com/photo-1552664730-d307ca884978?w=640&h=360&fit=crop',cat:'lideranca',label:'Workshop'},
  {url:'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=640&h=360&fit=crop',cat:'lideranca',label:'Mentoria'},
  {url:'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=640&h=360&fit=crop',cat:'lideranca',label:'Motivação'},
  {url:'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=640&h=360&fit=crop',cat:'lideranca',label:'Treinamento'},
  {url:'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=640&h=360&fit=crop',cat:'lideranca',label:'Palestra'},
  {url:'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=640&h=360&fit=crop',cat:'lideranca',label:'Trabalho em Equipe'},
  {url:'https://images.unsplash.com/photo-1557804506-669a67965ba0?w=640&h=360&fit=crop',cat:'lideranca',label:'Reunião Estratégica'},

  // 🌱 Meio Ambiente (6)
  {url:'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=640&h=360&fit=crop',cat:'ambiente',label:'Floresta'},
  {url:'https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?w=640&h=360&fit=crop',cat:'ambiente',label:'Reciclagem'},
  {url:'https://images.unsplash.com/photo-1473448912268-2022ce9509d8?w=640&h=360&fit=crop',cat:'ambiente',label:'Natureza'},
  {url:'https://images.unsplash.com/photo-1466611653911-95081537e5b7?w=640&h=360&fit=crop',cat:'ambiente',label:'Energia Solar'},
  {url:'https://images.unsplash.com/photo-1611273426858-450d8e3c9fce?w=640&h=360&fit=crop',cat:'ambiente',label:'Sustentabilidade'},
  {url:'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=640&h=360&fit=crop',cat:'ambiente',label:'Planta / Mão'},

  // 🎨 Criativo / Geral (6)
  {url:'https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=640&h=360&fit=crop',cat:'criativo',label:'Arte / Pintura'},
  {url:'https://images.unsplash.com/photo-1558655146-9f40138edfeb?w=640&h=360&fit=crop',cat:'criativo',label:'Design'},
  {url:'https://images.unsplash.com/photo-1542621334-a254cf47733d?w=640&h=360&fit=crop',cat:'criativo',label:'Fotografia'},
  {url:'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=640&h=360&fit=crop',cat:'criativo',label:'Retrato Profissional'},
  {url:'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?w=640&h=360&fit=crop',cat:'criativo',label:'Colorido / Criativo'},
  {url:'https://images.unsplash.com/photo-1501594907352-04cda38ebc29?w=640&h=360&fit=crop',cat:'criativo',label:'Paisagem'},
];

// Build library grid on load
(function() {
  const grid = document.getElementById('libGrid');
  if (!grid) return;
  const count = document.getElementById('libCount');
  if (count) count.textContent = THUMB_LIBRARY.length + ' imagens';
  
  THUMB_LIBRARY.forEach((img, idx) => {
    const div = document.createElement('div');
    div.className = 'thumb-item';
    div.dataset.cat = img.cat;
    div.dataset.idx = idx;
    div.onclick = function(e) {
      e.preventDefault();
      e.stopPropagation();
      selectThumb(img.url, div);
    };
    div.innerHTML = `
      <img src="${img.url}" alt="${img.label}" loading="lazy">
      <div class="label">${img.label}</div>
      <div class="check">✓</div>
    `;
    grid.appendChild(div);
  });
})();

// ===== LIBRARY FUNCTIONS =====
function selectThumb(url, el) {
  document.querySelectorAll('.thumb-item').forEach(i => i.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('thumbUrlInput').value = url;
  const prev = document.getElementById('thumbPreview');
  document.getElementById('thumbPreviewImg').src = url;
  prev.classList.remove('hidden');
  // Clear file upload if library is selected
  const fi = document.getElementById('fileInput');
  if(fi) fi.value = '';
  document.getElementById('uploadPreview').classList.remove('active');
  document.getElementById('uploadPrompt').style.display = '';
}

function clearThumb() {
  document.getElementById('thumbUrlInput').value = '';
  document.getElementById('thumbPreview').classList.add('hidden');
  document.querySelectorAll('.thumb-item').forEach(i => i.classList.remove('selected'));
}

function filterLibrary(cat) {
  document.querySelectorAll('.lib-cat').forEach(btn => {
    btn.className = btn.dataset.cat === cat
      ? 'lib-cat active text-[9px] font-bold px-2 py-1 rounded-full bg-indigo-600 text-white transition'
      : 'lib-cat text-[9px] font-bold px-2 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition';
  });
  document.querySelectorAll('.thumb-item').forEach(i => {
    i.style.display = (cat === 'all' || i.dataset.cat === cat) ? '' : 'none';
  });
  const visible = document.querySelectorAll('.thumb-item:not([style*="display: none"])').length;
  const count = document.getElementById('libCount');
  if (count) count.textContent = visible + ' imagens';
}

// ===== FILE UPLOAD =====
function handleFileSelect(input) {
  if (!input.files || !input.files[0]) return;
  showFilePreview(input.files[0]);
}

function handleFileDrop(e) {
  const dt = e.dataTransfer;
  if (dt.files && dt.files[0]) {
    const fi = document.getElementById('fileInput');
    fi.files = dt.files;
    showFilePreview(dt.files[0]);
  }
}

function showFilePreview(file) {
  if (!file.type.startsWith('image/')) { showToast('Selecione um arquivo de imagem', 'error'); return; }
  if (file.size > 10*1024*1024) { showToast('Arquivo muito grande (max 10MB)', 'error'); return; }
  // Clear library selection
  clearThumb();
  const reader = new FileReader();
  reader.onload = function(e) {
    document.getElementById('uploadPreviewImg').src = e.target.result;
    document.getElementById('uploadFileName').textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(1) + ' MB)';
    document.getElementById('uploadPreview').classList.add('active');
    document.getElementById('uploadPrompt').style.display = 'none';
  };
  reader.readAsDataURL(file);
}

function clearUpload() {
  document.getElementById('fileInput').value = '';
  document.getElementById('uploadPreview').classList.remove('active');
  document.getElementById('uploadPrompt').style.display = '';
}

// ===== FORM FUNCTIONS =====
function toggleFormPanel() {
  const panel = document.getElementById('formPanel');
  const icon = document.getElementById('iconPlus');
  const txt = document.getElementById('btnNovoCursoText');
  if (panel.classList.contains('open')) {
    cancelarForm();
  } else {
    panel.classList.add('open');
    icon.style.transform = 'rotate(45deg)';
    txt.textContent = 'Fechar';
    document.getElementById('formPanelTitle').innerHTML = '🎓 Criar Novo Curso';
    document.getElementById('formCurso').reset();
    document.getElementById('cursoId').value = '';
    clearThumb();
    clearUpload();
  }
}

function cancelarForm() {
  const panel = document.getElementById('formPanel');
  const icon = document.getElementById('iconPlus');
  const txt = document.getElementById('btnNovoCursoText');
  panel.classList.remove('open');
  icon.style.transform = 'rotate(0)';
  txt.textContent = 'Novo Curso';
  document.getElementById('formCurso').reset();
  document.getElementById('cursoId').value = '';
  clearThumb();
  clearUpload();
}

function editarCurso(c) {
  const panel = document.getElementById('formPanel');
  const icon = document.getElementById('iconPlus');
  const txt = document.getElementById('btnNovoCursoText');
  document.getElementById('formPanelTitle').innerHTML = '✏️ Editar Curso: ' + c.titulo;
  document.getElementById('cursoId').value = c.id;
  document.getElementById('cursoTitulo').value = c.titulo;
  document.getElementById('cursoDescricao').value = c.descricao || '';
  document.getElementById('cursoCarga').value = c.carga_horaria || 0;
  document.getElementById('cursoStatus').value = c.status;
  clearThumb();
  clearUpload();
  // Se o curso já tem thumbnail da biblioteca, mostrar
  if (c.thumbnail && c.thumbnail.startsWith('http')) {
    document.getElementById('thumbUrlInput').value = c.thumbnail;
    document.getElementById('thumbPreviewImg').src = c.thumbnail;
    document.getElementById('thumbPreview').classList.remove('hidden');
  }
  panel.classList.add('open');
  icon.style.transform = 'rotate(45deg)';
  txt.textContent = 'Fechar';
  panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function salvarCurso() {
  const btn = document.getElementById('btnSalvar');
  btn.disabled = true; btn.innerHTML = '⏳ Salvando...';
  const form = document.getElementById('formCurso');
  const formData = new FormData(form);
  const id = document.getElementById('cursoId').value;
  const url = id ? '/elearning/gestor/cursos/update' : '/elearning/gestor/cursos/store';
  try {
    const res = await fetch(url, { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) { showToast(data.message, 'success'); setTimeout(() => location.reload(), 800); }
    else showToast('Erro: ' + data.message, 'error');
  } catch(e) { showToast('Erro de conexão', 'error'); }
  finally { btn.disabled = false; btn.innerHTML = '💾 Salvar Curso'; }
}

async function excluirCurso(id, titulo) {
  if (!confirm(`Excluir o curso "${titulo}"?\nEsta ação não pode ser desfeita.`)) return;
  const fd = new FormData(); fd.append('id', id);
  try {
    const res = await fetch('/elearning/gestor/cursos/delete', { method: 'POST', body: fd });
    const d = await res.json();
    if (d.success) { showToast(d.message, 'success'); setTimeout(() => location.reload(), 800); }
    else showToast('Erro: ' + d.message, 'error');
  } catch(e) { showToast('Erro de conexão', 'error'); }
}

function filtrarCursos() {
  const search = document.getElementById('searchCursos').value.toLowerCase();
  const status = document.getElementById('filterStatus').value;
  document.querySelectorAll('.curso-card').forEach(card => {
    const matchTitulo = card.dataset.titulo.includes(search);
    const matchStatus = !status || card.dataset.status === status;
    card.style.display = (matchTitulo && matchStatus) ? '' : 'none';
  });
}

function showToast(msg, type) {
  const existing = document.getElementById('elToast');
  if (existing) existing.remove();
  const colors = { success: 'bg-green-600', error: 'bg-red-600', info: 'bg-indigo-600' };
  const div = document.createElement('div');
  div.id = 'elToast';
  div.className = `fixed bottom-6 right-6 z-[100] ${colors[type] || colors.info} text-white px-5 py-3 rounded-xl shadow-2xl text-sm font-medium el-fade-in`;
  div.textContent = msg;
  document.body.appendChild(div);
  setTimeout(() => div.remove(), 3500);
}
</script>
