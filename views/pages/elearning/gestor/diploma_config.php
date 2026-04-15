<?php
// views/pages/elearning/gestor/diploma_config.php
?>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;800&family=Great+Vibes&display=swap" rel="stylesheet">

<style>
  :root {
    --diploma-width: 800px;
    --diploma-ratio: 1.414; /* A4 Ratio */
  }
  .el-fade-in { animation: elFadeIn .4s ease; }
  @keyframes elFadeIn { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
  
  /* Layout Selector */
  .layout-card { cursor: pointer; border: 2px solid transparent; transition: all .3s; border-radius: 12px; overflow: hidden; background: white; }
  .layout-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,.1); }
  .layout-card.selected { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99,102,241,.1); }
  .layout-preview-small { aspect-ratio: 1.414/1; background: #f8fafc; position: relative; overflow: hidden; }

  /* DIPLOMA CONTAINER */
  .diploma-container { 
    width: 100%; 
    max-width: var(--diploma-width); 
    aspect-ratio: var(--diploma-ratio) / 1; 
    margin: 0 auto; 
    background: white; 
    box-shadow: 0 30px 60px rgba(0,0,0,0.2); 
    position: relative; 
    overflow: hidden; 
    user-select: none;
  }
  
  .diploma-content {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px;
    text-align: center;
    z-index: 10;
  }

  /* DRAGGABLE LOGO */
  #prevLogo {
    position: absolute;
    cursor: move;
    z-index: 50;
    transition: outline 0.2s;
    user-drag: none;
    -webkit-user-drag: none;
  }
  #prevLogo:hover { outline: 2px dashed #6366f1; }
  #prevLogo.dragging { opacity: 0.8; outline: 2px solid #6366f1; }

  /* PREMIUM LAYOUTS */
  
  /* Template 1: Classic Imperial */
  .tpl-1 { background: #fffcf0; color: #1a1a1a; border: 20px solid #c5a059; }
  .tpl-1::before { content: ''; position: absolute; inset: 10px; border: 2px solid #c5a059; pointer-events: none; }
  .tpl-1 .title { font-family: 'Playfair Display', serif; font-size: 48px; font-weight: 900; color: #8a6d3b; margin-bottom: 10px; }
  .tpl-1 .name { font-family: 'Great Vibes', cursive; font-size: 52px; color: #1a1a1a; margin: 20px 0; }
  .tpl-1 .line { width: 80%; height: 1px; background: #c5a059; margin: 20px 0; }

  /* Template 2: Modern Azure */
  .tpl-2 { background: #ffffff; color: #0f172a; }
  .tpl-2::after { content: ''; position: absolute; top: 0; right: 0; width: 40%; height: 100%; background: linear-gradient(135deg, rgba(99,102,241,0.1) 0%, rgba(99,102,241,0) 100%); clip-path: polygon(100% 0, 0 0, 100% 100%); pointer-events: none; }
  .tpl-2 .title { font-family: 'Montserrat', sans-serif; font-size: 42px; font-weight: 800; text-transform: uppercase; letter-spacing: 4px; color: #4338ca; }
  .tpl-2 .name { font-family: 'Montserrat', sans-serif; font-size: 38px; font-weight: 600; color: #1e1b4b; border-bottom: 4px solid #4338ca; padding-bottom: 5px; }

  /* Template 3: Royal Knight */
  .tpl-3 { background: #0f172a; color: #f8fafc; border: 2px solid #334155; }
  .tpl-3::before { content: ''; position: absolute; top:0; left:0; width:100%; height:80px; background: #1e293b; }
  .tpl-3 .title { font-family: 'Playfair Display', serif; font-size: 40px; font-weight: 700; color: #c5a059; text-transform: uppercase; margin-top: 40px; }
  .tpl-3 .name { font-family: 'Montserrat', sans-serif; font-size: 34px; font-weight: 800; color: #ffffff; text-shadow: 0 4px 10px rgba(0,0,0,0.5); }
  .tpl-3 .footer-line { border-top: 1px solid #334155; width: 100%; margin-top: 40px; padding-top: 20px; }

  /* Template 4: Vintage Scroll */
  .tpl-4 { background: #fdf6e3; color: #5d4037; padding: 40px; }
  .tpl-4::before { content: ''; position: absolute; inset: 30px; border: 8px double #8d6e63; border-radius: 4px; pointer-events: none; }
  .tpl-4 .title { font-family: 'Playfair Display', serif; font-size: 44px; font-style: italic; }
  .tpl-4 .name { font-family: 'Playfair Display', serif; font-size: 40px; font-weight: 700; text-decoration: underline; }

  /* Template 5: Creative Flow */
  .tpl-5 { background: linear-gradient(45deg, #f3f4f6 0%, #ffffff 100%); }
  .tpl-5::before { content: ''; position: absolute; bottom: 0; left: 0; width: 200px; height: 200px; background: #fbbf24; opacity: 0.1; border-radius: 50%; transform: translate(-50%, 50%); }
  .tpl-5 .title { font-family: 'Montserrat', sans-serif; font-size: 36px; font-weight: 300; color: #1f2937; }
  .tpl-5 b { font-weight: 800; color: #d97706; }
  .tpl-5 .name { font-family: 'Montserrat', sans-serif; font-size: 32px; color: #d97706; background: rgba(251, 191, 36, 0.1); padding: 5px 30px; border-radius: 999px; }

  /* Controls - Brutalist */
  .upload-logo-zone { border: 4px dashed #000; background: #fff; padding: 20px; transition: all .2s; cursor: pointer; box-shadow: 4px 4px 0px #000; }
  .upload-logo-zone:hover { transform: translate(-2px, -2px); box-shadow: 6px 6px 0px #000; }
  
  .brut-input { border: 2px solid #000; background: #fff; border-radius: 0; box-shadow: 2px 2px 0px #000; font-family: 'Inter', sans-serif; }
  .brut-input:focus { outline: none; box-shadow: 4px 4px 0px #000; transform: translate(-2px, -2px); }
  
  .brut-btn { border: 2px solid #000; background: #000; color: #fff; font-weight: 900; text-transform: uppercase; box-shadow: 4px 4px 0px #000; transition: all 0.2s; border-radius: 0; }
  .brut-btn:hover { background: #fff; color: #000; transform: translate(-2px, -2px); box-shadow: 6px 6px 0px #000; }
  
  .brut-pill { border: 2px solid #000; background: #fff; font-weight: 900; text-transform: uppercase; border-radius: 0; transition: all 0.2s; box-shadow: 2px 2px 0px #000; }
  .brut-pill.active { background: #000; color: #fff; transform: translate(2px, 2px); box-shadow: 0px 0px 0px #000; }
</style>

<div class="p-8 el-fade-in pb-20 bg-[#f7f7f7] min-h-screen">

  <!-- Header -->
  <div class="border-4 border-black bg-white p-6 shadow-[8px_8px_0px_#000] flex items-center justify-between mb-10">
    <div>
      <a href="/elearning/gestor/cursos" class="text-xs font-black uppercase inline-block border-b-2 border-black pb-1 hover:bg-black hover:text-white transition-colors mb-4">&larr; Voltar aos Cursos</a>
      <h1 class="text-4xl font-black uppercase tracking-tighter flex items-center gap-2">
        <i class="ph-fill ph-certificate"></i> Motor de Diplomas
      </h1>
      <p class="font-bold text-gray-500 mt-1 uppercase tracking-widest text-[10px]">Editor Espacial Master / Arraste para posicionar a marca</p>
    </div>
    <div class="flex gap-3">
      <button onclick="salvarConfig()" id="btnSalvarGeral" class="brut-btn px-6 py-3 flex items-center gap-2">
        <span id="saveLabel">Salvar Configuração &crarr;</span>
      </button>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

    <!-- LEFT: Settings (Col 1) -->
    <div class="space-y-8">
      
      <!-- Logo Upload & Size -->
      <div class="bg-white border-2 border-black p-5 shadow-[4px_4px_0px_#000]">
        <h3 class="text-sm font-black uppercase tracking-widest border-b-2 border-black pb-2 mb-4">Branding</h3>
        <input type="file" id="logoInput" class="hidden" onchange="handleLogoSelect(this)" accept="image/*">
        
        <div class="upload-logo-zone text-center mb-6" onclick="document.getElementById('logoInput').click()">
          <div id="logoSelectPrompt">
            <i class="ph ph-upload-simple text-4xl mb-2"></i>
            <p class="text-[10px] font-black uppercase">Subir Logo (PNG/JPG)</p>
          </div>
        </div>

        <div class="space-y-2">
          <div class="flex justify-between">
            <label class="block text-[10px] font-black uppercase">Escala</label>
            <div class="text-[10px] font-black uppercase"><span id="widthValue"><?= $config['logo_width'] ?? 150 ?></span>px</div>
          </div>
          <input type="range" id="logoWidthRange" min="50" max="400" value="<?= $config['logo_width'] ?? 150 ?>" 
                 class="w-full h-2 bg-gray-200 border border-black cursor-pointer accent-black"
                 oninput="updateLogoSize(this.value)">
        </div>
      </div>

      <!-- Signature -->
      <div class="bg-white border-2 border-black p-5 shadow-[4px_4px_0px_#000]">
        <h3 class="text-sm font-black uppercase tracking-widest border-b-2 border-black pb-2 mb-4">Validação</h3>
        <input type="text" id="assinaturaTexto" value="<?= htmlspecialchars($config['assinatura_texto'] ?? 'Diretoria SGQDJ') ?>" 
               class="w-full brut-input px-4 py-2 font-bold transition"
               oninput="updatePreviewText()">
      </div>

      <!-- Positioning Info -->
      <div class="bg-yellow-300 border-2 border-black p-4 shadow-[4px_4px_0px_#000]">
          <p class="text-[10px] text-black font-bold uppercase leading-relaxed">
            <i class="ph-fill ph-target"></i> No canvas ao lado, você pode <b>clicar e arrastar</b> o logo para posicionar. As coordenadas serão salvas no banco.
          </p>
      </div>

    </div>

    <!-- MAIN: Editor (Col 2-4) -->
    <div class="lg:col-span-3 space-y-8">
      
      <!-- Layout Selector Pills -->
      <div class="bg-white border-2 border-black p-4 shadow-[4px_4px_0px_#000] overflow-x-auto">
        <div class="flex gap-4 min-w-max">
          <?php 
          $layouts = [
            '1' => 'Imperial',
            '2' => 'Modern',
            '3' => 'Knight',
            '4' => 'Vintage',
            '5' => 'Creative'
          ];
          foreach($layouts as $id => $name): ?>
          <button onclick="selectLayout(<?= $id ?>)" id="layoutBtn_<?= $id ?>" 
                  class="brut-pill px-6 py-2 text-xs <?= ($config['layout_ativo'] ?? 1) == $id ? 'active' : '' ?>">
            <?= $name ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- DIPLOMA PREVIEW CANVAS -->
      <div class="flex items-center justify-center p-8 bg-gray-200 border-4 border-dashed border-gray-400">
        <div id="diplomaPreview" class="diploma-container tpl-<?= $config['layout_ativo'] ?? 1 ?>">
          
          <!-- Draggable Logo -->
          <img id="prevLogo" 
               src="<?= ($config['logo_diploma'] ?? false) ? '/elearning/gestor/diploma/logo' : 'https://placehold.co/400x200?text=Sua+Logo' ?>" 
               style="left: <?= $config['logo_x'] ?? 50 ?>%; top: <?= $config['logo_y'] ?? 10 ?>%; width: <?= $config['logo_width'] ?? 150 ?>px; transform: translate(-50%, 0);">
          
          <div class="diploma-content">
            <div class="title">Certificado de Conclusão</div>
            <div class="label text-gray-500 text-xs uppercase tracking-[0.3em] my-4">Certificamos com honra que</div>
            <div class="name">Nome do Aluno Exemplo</div>
            
            <p class="text-sm max-w-lg mx-auto leading-relaxed mt-6">
              concluiu com aproveitamento excepcional o treinamento de<br>
              <b class="text-lg">GESTÃO DE QUALIDADE E PROCESSOS</b><br>
              com carga horária de 80 horas de conteúdo programático.
            </p>

            <div class="w-full mt-auto flex justify-between items-end px-10 pb-4">
              <div class="text-left">
                <p class="text-[9px] text-gray-400 uppercase font-bold">Data de Emissão</p>
                <p class="text-xs font-bold"><?= date('d/m/Y') ?></p>
              </div>
              
              <div class="text-center group">
                <div class="w-40 border-b border-gray-400 mb-1 group-hover:w-48 transition-all"></div>
                <p id="prevAssinatura" class="text-xs font-bold"><?= htmlspecialchars($config['assinatura_texto'] ?? 'Diretoria SGQDJ') ?></p>
              </div>

              <div class="text-right">
                <p class="text-[9px] text-gray-400 uppercase font-bold">Autenticidade</p>
                <p class="text-[8px] font-mono">CODE: PREMIUM-PLATINUM-V3</p>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>

<script>
let currentLayout = <?= $config['layout_ativo'] ?? 1 ?>;
let logoX = <?= $config['logo_x'] ?? 50 ?>;
let logoY = <?= $config['logo_y'] ?? 10 ?>;
let logoWidth = <?= $config['logo_width'] ?? 150 ?>;

// --- Drag & Drop Logic ---
const logo = document.getElementById('prevLogo');
const container = document.getElementById('diplomaPreview');
let isDragging = false;

logo.addEventListener('mousedown', startDrag);
window.addEventListener('mousemove', drag);
window.addEventListener('mouseup', stopDrag);

function startDrag(e) {
  isDragging = true;
  logo.classList.add('dragging');
  e.preventDefault();
}

function drag(e) {
  if (!isDragging) return;
  
  const rect = container.getBoundingClientRect();
  let x = ((e.clientX - rect.left) / rect.width) * 100;
  let y = ((e.clientY - rect.top) / rect.height) * 100;
  
  // Constrain
  x = Math.max(0, Math.min(100, x));
  y = Math.max(0, Math.min(100, y));
  
  logoX = x.toFixed(2);
  logoY = y.toFixed(2);
  
  logo.style.left = logoX + '%';
  logo.style.top = logoY + '%';
}

function stopDrag() {
  isDragging = false;
  logo.classList.remove('dragging');
}

// --- Layout Selection ---
function selectLayout(id) {
  currentLayout = id;
  const pills = document.querySelectorAll('.brut-pill');
  pills.forEach(p => p.classList.remove('active'));
  
  const active = document.getElementById('layoutBtn_' + id);
  active.classList.add('active');
  
  document.getElementById('diplomaPreview').className = 'diploma-container tpl-' + id;
}

// --- Logo Handling ---
function handleLogoSelect(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('prevLogo').src = e.target.result;
      document.getElementById('logoSelectPrompt').innerHTML = '<div class="text-green-500 text-3xl">✓</div><p class="text-[10px] font-bold">Logo Trocado</p>';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function updateLogoSize(val) {
  logoWidth = val;
  document.getElementById('widthValue').textContent = val;
  logo.style.width = val + 'px';
}

function updatePreviewText() {
  document.getElementById('prevAssinatura').textContent = document.getElementById('assinaturaTexto').value || 'Diretoria';
}

// --- Save ---
async function salvarConfig() {
  const btn = document.getElementById('btnSalvarGeral');
  const label = document.getElementById('saveLabel');
  btn.disabled = true; label.textContent = '⏳ Salvando...';

  const fd = new FormData();
  fd.append('layout_ativo', currentLayout);
  fd.append('assinatura_texto', document.getElementById('assinaturaTexto').value);
  fd.append('logo_x', logoX);
  fd.append('logo_y', logoY);
  fd.append('logo_width', logoWidth);
  
  const logoInput = document.getElementById('logoInput');
  if (logoInput.files && logoInput.files[0]) {
    fd.append('logo', logoInput.files[0]);
  }

  try {
    const res = await fetch('/elearning/gestor/diploma/save', { method: 'POST', body: fd });
    const d = await res.json();
    if (d.success) {
      showToast('Design salvo com sucesso! 💎', 'success');
      setTimeout(() => location.href = '/elearning/gestor/cursos', 1500);
    } else {
      showToast('Erro: ' + d.message, 'error');
    }
  } catch(e) { showToast('Erro de conexão', 'error'); } 
  finally { btn.disabled = false; label.textContent = '💾 Salvar Alterações'; }
}

function showToast(msg, type) {
  const div = document.createElement('div');
  div.className = `fixed bottom-6 right-6 z-[100] px-6 py-4 shadow-[4px_4px_0px_#000] border-2 border-black text-black font-black uppercase text-xs tracking-widest el-fade-in ${type==='success'?'bg-yellow-300':'bg-red-500 text-white'}`;
  div.textContent = msg;
  document.body.appendChild(div);
  setTimeout(() => div.remove(), 3500);
}
</script>
