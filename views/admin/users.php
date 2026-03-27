<?php if (isset($error)): ?>
  <div class="mb-6 p-4 bg-red-50/50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/50 text-red-700 dark:text-red-400 rounded-2xl backdrop-blur-sm transition-all animate-pulse flex items-center gap-3">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span class="font-bold text-sm"><?= e($error) ?></span>
  </div>
<?php endif; ?>

<section class="space-y-6">
  <!-- Cabeçalho Premium -->
  <div class="mb-8 p-6 bg-white/50 dark:bg-slate-800/50 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-sm transition-all flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div class="flex items-center gap-4">
      <div class="p-3 bg-blue-100 dark:bg-blue-900/40 rounded-xl">
        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
      </div>
      <div>
        <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">Gestão de Usuários</h1>
        <p class="mt-1 text-slate-500 dark:text-slate-400 font-medium">Controle central de acessos, permissões e status operacional.</p>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <span id="userCountBadge" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold bg-white/80 dark:bg-slate-900/80 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-900/30 shadow-sm">
        <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
        <span id="userTotal">...</span> Usuários
      </span>
      <button onclick="toggleUserForm()" id="toggleFormBtn" class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition-all shadow-lg shadow-blue-500/25 flex items-center justify-center gap-2 group">
        <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Novo Usuário
      </button>
    </div>
  </div>

  <!-- User Form (Floating Card Style) -->
  <div id="userFormContainer" class="hidden transform transition-all duration-300 scale-95 opacity-0 bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-2xl shadow-2xl border border-slate-200/50 dark:border-slate-700/50 p-8 mb-8 overflow-hidden sticky top-6 z-40">
    <div class="flex justify-between items-center mb-8">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-blue-600/10 flex items-center justify-center">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        </div>
        <h3 id="formTitle" class="text-xl font-extrabold text-slate-800 dark:text-white tracking-tight leading-none">Configurar Usuário</h3>
      </div>
      <button onclick="cancelUserForm()" class="p-2 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/40 rounded-xl transition-all">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <form id="userForm" class="space-y-8">
      <input type="hidden" id="userId" name="id">
      
      <!-- Dados Básicos -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-6 bg-slate-50/50 dark:bg-slate-900/40 rounded-2xl border border-slate-100 dark:border-slate-800/50">
        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Nome Completo</label>
          <input type="text" id="userName" name="name" required placeholder="Ex: João da Silva"
                 class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all placeholder-slate-400 dark:placeholder-slate-600 shadow-inner">
        </div>

        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Endereço de Email</label>
          <input type="email" id="userEmail" name="email" required placeholder="joao@tiuai.com.br"
                 class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all placeholder-slate-400 dark:placeholder-slate-600 shadow-inner">
        </div>

        <div id="passwordField" class="space-y-2">
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Senha de Acesso <span id="passwordRequired" class="text-red-500">*</span></label>
          <div class="relative group">
            <input type="password" id="userPassword" name="password" placeholder="••••••••"
                   class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 pr-12 text-sm font-bold text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all shadow-inner">
            <button type="button" onclick="toggleUserPassword()" 
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 hover:text-blue-500 dark:hover:text-blue-400 p-2 rounded-lg transition-colors">
              <svg id="userPasswordEyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
            </button>
          </div>
          <p id="passwordHelp" class="text-[10px] font-bold text-slate-400 uppercase tracking-tight mt-1 ml-1 hidden italic">Deixe em branco para manter a senha atual</p>
        </div>

        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Nível de Função</label>
          <select id="userRole" name="role" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all shadow-inner cursor-pointer appearance-none">
            <option value="user">Usuário Operacional</option>
            <option value="admin">Administrador do Sistema</option>
          </select>
        </div>
      </div>

      <!-- Atributos Profissionais -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 p-6 bg-slate-50/30 dark:bg-slate-900/20 rounded-2xl border border-slate-100/50 dark:border-slate-800/30">
        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Setor de Atuação</label>
          <select id="userSetor" name="setor" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all shadow-inner">
            <option value="">Selecione um setor</option>
          </select>
        </div>

        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Unidade / Filial</label>
          <select id="userFilial" name="filial" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all shadow-inner">
            <option value="">Selecione uma filial</option>
          </select>
        </div>

        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Status Ativo</label>
          <select id="userStatus" name="status" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all shadow-inner">
            <option value="active">Usuário Ativo</option>
            <option value="inactive">Suspenso / Inativo</option>
          </select>
        </div>

        <div class="md:col-span-3 space-y-2">
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Perfil de Acesso Modular *</label>
          <select id="userProfile" name="profile_id" required class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all shadow-inner">
            <option value="">Selecione um perfil de permissões pré-definido</option>
          </select>
        </div>
      </div>

      <!-- Configurações Adicionais (Grid Visual Premium) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Notificações -->
        <div class="p-4 bg-amber-50/50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-900/30 rounded-2xl group transition-all hover:bg-amber-50 dark:hover:bg-amber-900/20">
          <div class="flex items-start gap-4">
            <input type="checkbox" id="notificacoesAtivadas" name="notificacoes_ativadas" checked 
                   class="mt-1.5 h-6 w-6 text-amber-600 focus:ring-amber-500 bg-white dark:bg-slate-900 border-amber-300 dark:border-amber-800 rounded-lg shadow-sm cursor-pointer">
            <div>
              <label for="notificacoesAtivadas" class="block text-sm font-extrabold text-amber-900 dark:text-amber-200 cursor-pointer select-none">Notificações do Sistema</label>
              <p class="text-xs font-bold text-amber-700/60 dark:text-amber-400/60 mt-0.5 leading-relaxed tracking-tight">Ativa alertas visuais, sonoros e o sino de notificações no painel do usuário.</p>
            </div>
          </div>
        </div>

        <!-- Módulos de Aprovação (Somente Admin) -->
        <div id="permissoesAprovacaoContainer" class="hidden grid grid-cols-1 gap-4">
          <div class="p-4 bg-slate-100/50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-2xl flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Aprovações</span>
                <span class="block text-sm font-bold text-slate-700 dark:text-slate-200">Módulos de Auditoria & POP</span>
              </div>
            </div>
            <div class="flex gap-2">
              <label class="group relative flex items-center p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-blue-400 transition-all cursor-pointer shadow-sm" title="Aprovar POP/IT">
                <input type="checkbox" id="podeAprovarPopsIts" name="pode_aprovar_pops_its" class="h-4 w-4 text-blue-600 rounded">
                <span class="ml-2 text-[10px] font-bold text-slate-500 uppercase">POP</span>
              </label>
              <label class="group relative flex items-center p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-purple-400 transition-all cursor-pointer shadow-sm" title="Aprovar Fluxogramas">
                <input type="checkbox" id="podeAprovarFluxogramas" name="pode_aprovar_fluxogramas" class="h-4 w-4 text-purple-600 rounded">
                <span class="ml-2 text-[10px] font-bold text-slate-500 uppercase">FLUX</span>
              </label>
              <label class="group relative flex items-center p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-green-400 transition-all cursor-pointer shadow-sm" title="Aprovar Amostragens">
                <input type="checkbox" id="podeAprovarAmostragens" name="pode_aprovar_amostragens" class="h-4 w-4 text-green-600 rounded">
                <span class="ml-2 text-[10px] font-bold text-slate-500 uppercase">AMOST</span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-end space-x-4 pt-8 border-t border-slate-200 dark:border-slate-700/50">
        <button type="button" onclick="cancelUserForm()" 
                class="px-8 py-3 text-sm font-bold text-slate-600 dark:text-slate-400 hover:text-red-600 transition-all rounded-xl border border-transparent hover:border-red-500/30">
          Descartar
        </button>
        <button type="button" onclick="submitUser(this)" id="submitBtn" 
                class="px-10 py-3 text-sm font-extrabold text-white bg-blue-600 border border-transparent rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/25">
          Criar Usuário
        </button>
      </div>
    </form>
  </div>

  <!-- Users Table Card -->
  <div class="bg-white/50 dark:bg-slate-800/50 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/50 dark:border-slate-700/50 overflow-hidden transition-all">
    <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
      <h3 class="text-base font-extrabold text-slate-800 dark:text-white tracking-tight">Base Operacional de Usuários</h3>
      <div class="flex items-center gap-2">
        <span class="flex items-center gap-1 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest bg-white dark:bg-slate-800 px-3 py-1.5 rounded-full border border-slate-200 dark:border-slate-700">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
          Ordenado por Perfil
        </span>
      </div>
    </div>
    <div class="overflow-x-auto ring-1 ring-slate-100 dark:ring-slate-700/50 rounded-2xl">
      <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700/50">
        <thead class="bg-slate-50/80 dark:bg-slate-900/80 backdrop-blur-md">
          <tr>
            <th class="px-6 py-4 text-left text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Identidade Operacional</th>
            <th class="px-6 py-4 text-left text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Perfil & Acesso</th>
            <th class="px-6 py-4 text-left text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Status / Vigência</th>
            <th class="px-6 py-4 text-right text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-widest w-40">Ações de Gestão</th>
          </tr>
        </thead>
        <tbody id="usersTableBody" class="divide-y divide-slate-50 dark:divide-slate-700/30 bg-white/30 dark:bg-transparent">
          <!-- Users will be loaded here via JavaScript -->
        </tbody>
      </table>
    </div>
  </div>
</section>


<!-- Permissions Modal -->
<div id="permissionsModal" class="modal-overlay transition-all duration-300 opacity-0 pointer-events-none fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
  <div class="modal-container w-full max-w-4xl bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 shadow-2xl rounded-3xl overflow-hidden transform transition-all duration-300 scale-95">
    <div class="modal-header border-b border-slate-100 dark:border-slate-700/50 p-8 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/50">
      <div>
        <h3 class="text-2xl font-extrabold text-slate-800 dark:text-white tracking-tight">Arquitetura de Permissões</h3>
        <p class="text-xs font-bold text-blue-600 dark:text-blue-400 mt-1 uppercase tracking-widest">Usuário: <span id="permissionsUserName" class="text-slate-500 dark:text-slate-400"></span></p>
      </div>
      <button class="p-2 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/40 rounded-xl transition-all" data-modal-close>
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <div class="modal-body p-8 max-h-[60vh] overflow-y-auto custom-scrollbar">
      <div id="permissionsContent" class="space-y-6">
        <!-- Permissions will be loaded here -->
        <div class="flex items-center justify-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>
      </div>
    </div>

    <div class="modal-footer p-8 border-t border-slate-100 dark:border-slate-700/50 flex flex-col sm:flex-row justify-end gap-3 bg-slate-50/30 dark:bg-slate-900/30">
      <button onclick="closePermissionsModal()" class="px-8 py-3 text-sm font-bold text-slate-600 dark:text-slate-400 hover:text-red-600 transition-all rounded-xl border border-transparent">
        Cancelar
      </button>
      <button onclick="savePermissions(this)" class="px-10 py-3 text-sm font-extrabold text-white bg-blue-600 border border-transparent rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/25">
        Salvar Matriz de Acesso
      </button>
    </div>
  </div>
</div>

<script>
const isMasterUser = <?= json_encode($isMasterUser ?? false) ?>;
let currentUserId = null;
let setoresList = [];
let filiaisList = [];
let profilesList = [];

// Função para mostrar/ocultar senha
function toggleUserPassword() {
  const passwordInput = document.getElementById('userPassword');
  const eyeIcon = document.getElementById('userPasswordEyeIcon');
  
  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    eyeIcon.innerHTML = `
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
    `;
  } else {
    passwordInput.type = 'password';
    eyeIcon.innerHTML = `
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
    `;
  }
}

// Load users on page load
document.addEventListener('DOMContentLoaded', function() {
  loadUsers();
});

function loadUsers() {
  fetch('/admin/users', {
    method: 'GET',
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      displayUsers(result.users);
      setoresList = result.setores || [];
      filiaisList = result.filiais || [];
      profilesList = result.profiles || [];
      
      const totalSpan = document.getElementById('userTotal');
      if (totalSpan) {
        totalSpan.textContent = result.users.length;
        document.getElementById('userCountBadge').classList.remove('hidden');
      }
      
      populateDropdowns();
    } else {
      showToast('Erro ao carregar usuários: ' + result.message, 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('Erro de conexão ao carregar usuários', 'error');
  });
}

function populateDropdowns() {
  // Popula dropdowns existentes (lógica mantida)
  const setorSelect = document.getElementById('userSetor');
  if (setorSelect) {
    setorSelect.innerHTML = '<option value="">Selecione um setor</option>';
    setoresList.forEach(setor => {
      const option = document.createElement('option');
      option.value = setor; option.textContent = setor;
      setorSelect.appendChild(option);
    });
  }
  
  const filialSelect = document.getElementById('userFilial');
  if (filialSelect) {
    filialSelect.innerHTML = '<option value="">Selecione uma filial</option>';
    filiaisList.forEach(filial => {
      const option = document.createElement('option');
      option.value = filial; option.textContent = filial;
      filialSelect.appendChild(option);
    });
  }
  
  const profileSelect = document.getElementById('userProfile');
  if (profileSelect) {
    profileSelect.innerHTML = '<option value="">Selecione um perfil de permissões</option>';
    profilesList.forEach(profile => {
      const option = document.createElement('option');
      option.value = profile.id; option.textContent = profile.name;
      if (profile.description) option.title = profile.description;
      profileSelect.appendChild(option);
    });
  }
}

function displayUsers(users) {
  const tbody = document.getElementById('usersTableBody');
  tbody.innerHTML = '';
  
  users.forEach(user => {
    const row = document.createElement('tr');
    row.className = 'hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors group';
    
    // Identidade
    const avatarColor = user.role === 'admin' ? 'bg-purple-100 text-purple-600 dark:bg-purple-900/40 dark:text-purple-400' : 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400';
    
    row.innerHTML = `
      <td class="px-6 py-4">
        <div class="flex items-center gap-4">
          <div class="w-10 h-10 rounded-xl ${avatarColor} flex items-center justify-center font-bold text-sm shadow-sm border border-white/50 dark:border-slate-700/50">
            ${user.name.charAt(0).toUpperCase()}
          </div>
          <div>
            <div class="text-sm font-extrabold text-slate-800 dark:text-white leading-tight uppercase tracking-tight">${user.name}</div>
            <div class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase flex items-center gap-1.5 mt-0.5">
              ${user.email}
            </div>
          </div>
        </div>
      </td>
      <td class="px-6 py-4">
        <div>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold ${user.role === 'admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400'} border border-transparent dark:border-slate-800 shadow-sm" title="${user.profile_description || ''}">
            <div class="w-1.5 h-1.5 rounded-full ${user.role === 'admin' ? 'bg-purple-500' : 'bg-blue-500'}"></div>
            ${user.profile_name || 'Sem perfil'}
          </span>
          <div class="text-[10px] font-bold text-slate-400 uppercase mt-1.5 px-1">${user.setor || 'Sem setor'} • ${user.filial || 'Sem filial'}</div>
        </div>
      </td>
      <td class="px-6 py-4">
        <div class="flex items-center gap-3">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-widest ${user.status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400'}">
            ${user.status === 'active' ? 'Ativo' : 'Suspenso'}
          </span>
          <span class="text-[10px] font-bold text-slate-400 dark:text-slate-600 uppercase tracking-tighter">
            Desde ${new Date(user.created_at).toLocaleDateString('pt-BR')}
          </span>
        </div>
      </td>
      <td class="px-6 py-4 text-right">
        <div class="flex items-center justify-end gap-1 px-2 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
          <!-- Ações Master (Impersonate/Pass) -->
          ${isMasterUser ? `
            <button onclick="viewUserPassword(${user.id}, '${user.password_plain || ''}')" 
                    class="p-2 text-amber-500 hover:bg-amber-100 dark:hover:bg-amber-900/30 rounded-xl transition-all" title="Ver Identidade Logada">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </button>
            <button onclick="impersonateUser(${user.id}, '${user.name}')" 
                    class="p-2 text-emerald-500 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 rounded-xl transition-all" title="Simular Acesso">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            </button>
          ` : ''}
          
          <button onclick="managePermissions(${user.id}, '${user.name}')" 
                  class="p-2 text-indigo-500 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 rounded-xl transition-all" title="Matriz de Permissões">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
          </button>
          
          <div class="w-px h-6 bg-slate-200 dark:bg-slate-700/50 mx-1"></div>

          <button onclick="editUser(${user.id})" 
                  class="p-2 text-blue-500 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-xl transition-all" title="Ficha Técnica">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          </button>
          <button onclick="deleteUser(${user.id}, '${user.name}')" 
                  class="p-2 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-xl transition-all" title="Eliminar Acesso">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
          </button>
        </div>
      </td>
    `;
    tbody.appendChild(row);
  });
}

// Função para mostrar/esconder permissões de aprovação
function togglePopsItsPermission() {
  const role = document.getElementById('userRole').value;
  const container = document.getElementById('permissoesAprovacaoContainer');
  
  if (role === 'admin') {
    container.classList.remove('hidden');
  } else {
    container.classList.add('hidden');
    document.getElementById('podeAprovarPopsIts').checked = false;
    document.getElementById('podeAprovarFluxogramas').checked = false;
    document.getElementById('podeAprovarAmostragens').checked = false;
  }
}

// Event listener para mudança de role
document.addEventListener('DOMContentLoaded', function() {
  const userRoleSelect = document.getElementById('userRole');
  if (userRoleSelect) userRoleSelect.addEventListener('change', togglePopsItsPermission);
});

function toggleUserForm() {
  const container = document.getElementById('userFormContainer');
  const btn = document.getElementById('toggleFormBtn');
  
  if (container.classList.contains('hidden')) {
    container.classList.remove('hidden');
    // Forçar reflow para animação
    container.offsetHeight;
    container.classList.remove('scale-95', 'opacity-0');
    container.classList.add('scale-100', 'opacity-100');
    
    document.getElementById('formTitle').textContent = 'Configurar Novo Acesso';
    document.getElementById('submitBtn').textContent = 'Ativar Usuário';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('passwordField').classList.remove('hidden');
    document.getElementById('userPassword').required = true;
    document.getElementById('passwordRequired').classList.remove('hidden');
    document.getElementById('passwordHelp').classList.add('hidden');
    
    populateDropdowns();
    document.getElementById('userName').focus();
    
    btn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg><span>Cancelar</span>`;
  } else {
    cancelUserForm();
  }
}

function editUser(userId) {
  fetch('/admin/users', {
    method: 'GET',
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      const user = result.users.find(u => u.id == userId);
      if (user) {
        const container = document.getElementById('userFormContainer');
        container.classList.remove('hidden');
        container.offsetHeight;
        container.classList.remove('scale-95', 'opacity-0');
        container.classList.add('scale-100', 'opacity-100');
        
        populateDropdowns();
        
        setTimeout(() => {
          document.getElementById('formTitle').textContent = 'Editar Ficha Técnica';
          document.getElementById('submitBtn').textContent = 'Atualizar Cadastro';
          document.getElementById('userId').value = user.id;
          document.getElementById('userName').value = user.name;
          document.getElementById('userEmail').value = user.email;
          document.getElementById('userSetor').value = user.setor || '';
          document.getElementById('userFilial').value = user.filial || '';
          document.getElementById('userRole').value = user.role;
          document.getElementById('userStatus').value = user.status;
          document.getElementById('userProfile').value = user.profile_id || '';
          
          document.getElementById('podeAprovarPopsIts').checked = Boolean(Number(user.pode_aprovar_pops_its));
          document.getElementById('podeAprovarFluxogramas').checked = Boolean(Number(user.pode_aprovar_fluxogramas));
          document.getElementById('podeAprovarAmostragens').checked = Boolean(Number(user.pode_aprovar_amostragens));
          document.getElementById('notificacoesAtivadas').checked = user.notificacoes_ativadas === undefined || Boolean(Number(user.notificacoes_ativadas));
          
          togglePopsItsPermission();
          
          document.getElementById('passwordField').classList.remove('hidden');
          document.getElementById('userPassword').required = false;
          document.getElementById('userPassword').value = '';
          document.getElementById('passwordRequired').classList.add('hidden');
          document.getElementById('passwordHelp').classList.remove('hidden');
          
          container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 50);
        
        document.getElementById('toggleFormBtn').innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg><span>Cancelar</span>`;
      }
    }
  });
}

function cancelUserForm() {
  const container = document.getElementById('userFormContainer');
  container.classList.add('scale-95', 'opacity-0');
  setTimeout(() => {
    container.classList.add('hidden');
    document.getElementById('userForm').reset();
    populateDropdowns();
  }, 300);
  
  document.getElementById('toggleFormBtn').innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg><span>Novo Usuário</span>`;
}

async function submitUser(btn) {
  const form = document.getElementById('userForm');
  const formData = new FormData(form);
  const userId = document.getElementById('userId').value;
  
  // Handlers para checkboxes
  ['notificacoes_ativadas', 'pode_aprovar_pops_its', 'pode_aprovar_fluxogramas', 'pode_aprovar_amostragens'].forEach(field => {
    const cb = document.querySelector(`[name="${field}"]`);
    if (cb) {
      formData.delete(field);
      formData.append(field, cb.checked ? '1' : '0');
    }
  });
  
  setButtonLoading(btn);
  const url = userId ? '/admin/users/update' : '/admin/users/create';
  
  try {
    const response = await fetch(url, { method: 'POST', body: formData });
    const result = await response.json();
    
    if (result.success) {
      showToast(result.message, 'success');
      cancelUserForm();
      loadUsers();
    } else {
      showToast(result.message, 'error');
    }
  } catch (error) {
    showToast('Erro de comunicação com o servidor', 'error');
  } finally {
    setButtonLoading(btn, false);
  }
}

function viewUserPassword(userId, password) {
  if (!password || password === '') {
    showToast('Este usuário não possui senha em texto simples vinculada.', 'warning');
    return;
  }
  showConfirm('Credenciais de Acesso', `A senha gerada para este usuário é: <strong>${password}</strong><br><br>Garante que esta informação seja tratada com sigilo.`, 'Fechar', '', 'bg-amber-600');
}

async function impersonateUser(userId, userName) {
  const confirm = await showConfirm('Simular Acesso', `Deseja realmente iniciar uma sessão como <strong>"${userName}"</strong>?<br>Sua sessão atual será encerrada para fins de auditoria.`);
  if (!confirm) return;
  
  try {
    const formData = new FormData();
    formData.append('user_id', userId);
    
    const response = await fetch('/admin/users/impersonate', { method: 'POST', body: formData });
    const result = await response.json();
    
    if (result.success) {
      showToast(result.message, 'success');
      setTimeout(() => window.location.href = result.redirect || '/dashboard', 1000);
    } else {
      showToast(result.message, 'error');
    }
  } catch (error) {
    showToast('Erro de conexão ao simular acesso', 'error');
  }
}

async function deleteUser(userId, userName) {
  const confirm = await showConfirm('Remover Acesso', `Tem certeza que deseja eliminar o usuário <strong>"${userName}"</strong>?<br>Esta operação desativa permanentemente o acesso deste profissional.`, 'Confirmar Exclusão', 'Manter Usuário', 'bg-red-600');
  if (!confirm) return;
  
  try {
    const formData = new FormData();
    formData.append('user_id', userId);
    
    const response = await fetch('/admin/users/delete', { method: 'POST', body: formData });
    const result = await response.json();
    
    if (result.success) {
      showToast(result.message, 'success');
      loadUsers();
    } else {
      showToast(result.message, 'error');
    }
  } catch (error) {
    showToast('Erro de conexão ao remover usuário', 'error');
  }
}

function managePermissions(userId, userName) {
  currentUserId = userId;
  document.getElementById('permissionsUserName').textContent = userName;
  const content = document.getElementById('permissionsContent');
  
  content.innerHTML = `
    <div class="flex items-center justify-center py-20">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>
  `;
  
  openModal('permissionsModal');
  
  fetch(`/admin/users/${userId}/permissions`, {
    method: 'GET',
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      displayPermissions(result.permissions);
    } else {
      showToast('Erro ao carregar matriz: ' + result.message, 'error');
      closePermissionsModal();
    }
  })
  .catch(error => {
    showToast('Erro de conexão com a matriz de acessos', 'error');
    closePermissionsModal();
  });
}

function displayPermissions(permissions) {
  const content = document.getElementById('permissionsContent');
  const modules = [
    { key: 'dashboard', name: 'Painel Geral (Dashboard)', icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z' },
    { key: 'toners', name: 'Controle & Triagem de Toners', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
    { key: 'homologacoes', name: 'Gestão de Homologações (Fitas)', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
    { key: 'amostragens', name: 'Amostragens de Produção', icon: 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.675.337a4 4 0 01-2.547.547l-2.434-.487a2 2 0 00-1.022.547l-2.146 2.146a2 2 0 01-2.828 0l-.586-.586a2 2 0 010-2.828l2.146-2.146a2 2 0 00.547-1.022l.487-2.434a4 4 0 01.547-2.547l.337-.675a6 6 0 00.517-3.86l-.477-2.387a2 2 0 00-.547-1.022l-2.146-2.146a2 2 0 010-2.828l.586-.586a2 2 0 012.828 0l2.146 2.146z' },
    { key: 'auditorias', name: 'Auditorias de Processo', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { key: 'garantias', name: 'Garantias & RMAs', icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z' }
  ];
  
  let html = `
    <div class="grid grid-cols-1 gap-4">
      <div class="grid grid-cols-12 gap-4 px-6 py-3 bg-slate-100 dark:bg-slate-900/50 rounded-xl text-[10px] font-extrabold text-slate-500 uppercase tracking-widest hidden sm:grid">
        <div class="col-span-6">Módulo Operacional</div>
        <div class="col-span-2 text-center">Ver</div>
        <div class="col-span-2 text-center">Editar</div>
        <div class="col-span-2 text-center">Excluir</div>
      </div>
  `;
  
  modules.forEach(module => {
    const perm = permissions[module.key] || {};
    html += `
      <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 p-6 bg-white dark:bg-slate-800/40 border border-slate-100 dark:border-slate-700/30 rounded-2xl items-center group transition-all hover:bg-slate-50 dark:hover:bg-slate-700/30">
        <div class="col-span-1 sm:col-span-6 flex items-center gap-4">
          <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-slate-400 group-hover:text-blue-500 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${module.icon}"/></svg>
          </div>
          <span class="text-sm font-bold text-slate-700 dark:text-slate-200">${module.name}</span>
        </div>
        <div class="col-span-1 sm:col-span-2 flex justify-center">
          <label class="relative flex items-center cursor-pointer">
            <input type="checkbox" name="permissions[${module.key}][view]" ${perm.can_view ? 'checked' : ''} 
                   class="w-6 h-6 rounded-lg text-blue-600 border-slate-300 dark:border-slate-600 focus:ring-blue-500 bg-white dark:bg-slate-900">
          </label>
        </div>
        <div class="col-span-1 sm:col-span-2 flex justify-center">
          <label class="relative flex items-center cursor-pointer">
            <input type="checkbox" name="permissions[${module.key}][edit]" ${perm.can_edit ? 'checked' : ''} 
                   class="w-6 h-6 rounded-lg text-emerald-600 border-slate-300 dark:border-slate-600 focus:ring-emerald-500 bg-white dark:bg-slate-900">
          </label>
        </div>
        <div class="col-span-1 sm:col-span-2 flex justify-center">
          <label class="relative flex items-center cursor-pointer">
            <input type="checkbox" name="permissions[${module.key}][delete]" ${perm.can_delete ? 'checked' : ''} 
                   class="w-6 h-6 rounded-lg text-red-600 border-slate-300 dark:border-slate-600 focus:ring-red-500 bg-white dark:bg-slate-900">
          </label>
        </div>
      </div>
    `;
  });
  
  html += `</div>`;
  content.innerHTML = html;
}

function closePermissionsModal() {
  closeModal('permissionsModal');
  currentUserId = null;
}

async function savePermissions(btn) {
  const form = document.getElementById('permissionsContent');
  const formData = new FormData();
  formData.append('user_id', currentUserId);
  
  const checkboxes = form.querySelectorAll('input[type="checkbox"]:checked');
  checkboxes.forEach(checkbox => formData.append(checkbox.name, '1'));
  
  setButtonLoading(btn);
  
  try {
    const response = await fetch('/admin/permissions/update', { method: 'POST', body: formData });
    const result = await response.json();
    
    if (result.success) {
      showToast(result.message, 'success');
      closePermissionsModal();
    } else {
      showToast(result.message, 'error');
    }
  } catch (error) {
    showToast('Erro ao salvar matriz de acessos', 'error');
  } finally {
    setButtonLoading(btn, false);
  }
}

// Modal functions
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('pointer-events-none');
    modal.classList.add('opacity-100');
    const container = modal.querySelector('.modal-container');
    if (container) {
      container.classList.remove('scale-95');
      container.classList.add('scale-100');
    }
    document.body.style.overflow = 'hidden';
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add('pointer-events-none');
    modal.classList.remove('opacity-100');
    const container = modal.querySelector('.modal-container');
    if (container) {
      container.classList.add('scale-95');
      container.classList.remove('scale-100');
    }
    document.body.style.overflow = '';
  }
}

// Global modal handlers
document.addEventListener('click', (e) => { if (e.target.classList.contains('modal-overlay')) closeModal(e.target.id); });
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { const m = document.querySelector('.modal-overlay.opacity-100'); if (m) closeModal(m.id); } });
document.addEventListener('click', (e) => { 
  if (e.target.matches('[data-modal-close]') || e.target.closest('[data-modal-close]')) {
    const m = e.target.closest('.modal-overlay'); if (m) closeModal(m.id);
  }
});
</script>
