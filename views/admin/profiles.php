<style>
/* Custom scrollbar para o grid de permissões */
.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }

/* Toggle Switch Premium */
.toggle-switch-glass {
  @apply relative inline-flex items-center cursor-pointer select-none;
}
</style>

<?php if (isset($error)): ?>
  <div class="mb-6 p-4 bg-red-50/50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30 rounded-2xl flex items-center gap-3 text-red-700 dark:text-red-400 backdrop-blur-sm animate-shake">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span class="text-sm font-bold"><?= e($error) ?></span>
  </div>
<?php endif; ?>

<section class="space-y-8 animate-fade-in">
  <!-- Header Actions -->
  <div class="flex justify-end items-center">
    <button onclick="toggleProfileForm()" id="toggleFormBtn" class="px-8 py-4 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-2xl font-extrabold text-sm flex items-center gap-3 hover:scale-105 active:scale-95 transition-all shadow-xl shadow-slate-900/10 dark:shadow-white/5 border border-slate-800 dark:border-slate-200">
      <div class="bg-blue-500 rounded-lg p-1">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      </div>
      Projetar Novo Perfil
    </button>
  </div>

  <!-- Profile Configuration Form -->
  <div id="profileFormContainer" class="hidden opacity-0 scale-95 transform transition-all duration-500">
    <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-2xl rounded-[2.5rem] border border-slate-200/50 dark:border-slate-700/50 shadow-2xl overflow-hidden shadow-blue-500/5">
      <div class="px-10 py-8 border-b border-slate-100 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
        <div>
          <h3 id="formTitle" class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Arquitetura de Privilégios</h3>
          <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-1">Definição Técnica de Matriz de Acesso</p>
        </div>
        <button onclick="cancelProfileForm()" class="p-3 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-2xl transition-all">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      
      <form id="profileForm" class="p-10 space-y-10">
        <input type="hidden" id="profileId" name="id">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="md:col-span-2 space-y-2">
            <label class="px-2 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Nomenclatura Operacional</label>
            <input type="text" id="profileName" name="name" required placeholder="Ex: Gestor de Qualidade Sênior"
                   class="w-full bg-slate-50/50 dark:bg-slate-900/50 border-2 border-slate-100 dark:border-slate-700/50 rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white focus:border-blue-500 focus:ring-0 transition-all placeholder:text-slate-300 dark:placeholder:text-slate-600">
          </div>

          <div class="flex flex-col justify-end">
            <label class="group relative flex flex-col p-4 bg-slate-50/50 dark:bg-slate-900/50 border-2 border-slate-100 dark:border-slate-700/50 rounded-2xl cursor-pointer hover:border-emerald-500/50 transition-all">
              <div class="flex items-center justify-between mb-1">
                <span class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Padrão do Sistema</span>
                <input type="checkbox" id="isDefault" name="is_default" class="w-5 h-5 rounded-lg text-emerald-500 border-slate-300 dark:border-slate-600 focus:ring-emerald-500 bg-white dark:bg-slate-800 transition-all">
              </div>
              <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">Vinculação automática (Novos usuários)</span>
            </label>
          </div>
        </div>

        <div class="space-y-2">
          <label class="px-2 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Escopo e Responsabilidades</label>
          <textarea id="profileDescription" name="description" rows="2" placeholder="Descreva as atribuições e limites deste perfil..."
                    class="w-full bg-slate-50/50 dark:bg-slate-900/50 border-2 border-slate-100 dark:border-slate-700/50 rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 dark:text-white focus:border-blue-500 focus:ring-0 transition-all placeholder:text-slate-300 dark:placeholder:text-slate-600"></textarea>
        </div>

        <!-- Matrix Header -->
        <div class="space-y-4">
          <div class="flex items-center justify-between px-2">
            <h4 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-tighter">Matriz Modular de Acessos</h4>
            <div class="flex gap-2">
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Configuração rápida colunar:</span>
              <div class="flex gap-1">
                <button type="button" onclick="toggleColumn('view', true)" class="px-2 py-0.5 text-[9px] font-bold text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded transition-all">VIS</button>
                <button type="button" onclick="toggleColumn('edit', true)" class="px-2 py-0.5 text-[9px] font-bold text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded transition-all">EDI</button>
                <button type="button" onclick="toggleColumn('delete', true)" class="px-2 py-0.5 text-[9px] font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded transition-all">EXC</button>
              </div>
            </div>
          </div>
          
          <div class="bg-slate-50/50 dark:bg-slate-900/50 rounded-3xl border border-slate-100 dark:border-slate-700/50 overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
              <div class="min-w-[800px]">
                <!-- Table Header -->
                <div class="grid grid-cols-12 gap-4 px-8 py-4 bg-white/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-700 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                  <div class="col-span-4">Módulos do Ecossistema</div>
                  <div class="col-span-8 grid grid-cols-5 text-center">
                    <div>Visualizar</div>
                    <div>Editar</div>
                    <div>Excluir</div>
                    <div>Importar</div>
                    <div>Exportar</div>
                  </div>
                </div>
                
                <div id="permissionsGrid" class="divide-y divide-slate-100 dark:divide-slate-700/30">
                  <!-- Permissions Loaded via JS -->
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Dashboard Analytics Config -->
        <div class="space-y-4 pt-4">
          <div class="flex items-center gap-3 px-2">
            <div class="w-8 h-8 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <h4 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-tighter">Habilitação de Abas Analytics</h4>
          </div>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php 
              $tabs = [
                ['key' => 'retornados', 'name' => 'Retornados', 'icon' => '📦', 'color' => 'blue'],
                ['key' => 'amostragens', 'name' => 'Amostragens 2.0', 'icon' => '🧪', 'color' => 'indigo'],
                ['key' => 'fornecedores', 'name' => 'Fornecedores', 'icon' => '🏭', 'color' => 'amber'],
                ['key' => 'garantias', 'name' => 'Garantias', 'icon' => '🛡️', 'color' => 'emerald'],
                ['key' => 'melhorias', 'name' => 'Melhoria Contínua', 'icon' => '🚀', 'color' => 'blue'],
                ['key' => 'nao_conformidades', 'name' => 'Não Conformidades', 'icon' => '⚠️', 'color' => 'red']
              ];
              foreach ($tabs as $tab):
            ?>
            <label class="group flex items-center justify-between p-5 bg-slate-50/50 dark:bg-slate-900/50 border-2 border-slate-100 dark:border-slate-700/50 rounded-[2rem] cursor-pointer hover:bg-white dark:hover:bg-slate-800 hover:border-<?= $tab['color'] ?>-500/50 transition-all duration-300">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                  <?= $tab['icon'] ?>
                </div>
                <span class="text-xs font-extrabold text-slate-700 dark:text-slate-200 uppercase tracking-tight"><?= $tab['name'] ?></span>
              </div>
              <input type="checkbox" name="dashboard_tabs[<?= $tab['key'] ?>]" checked
                     class="w-6 h-6 rounded-lg text-<?= $tab['color'] ?>-500 border-slate-300 dark:border-slate-600 focus:ring-<?= $tab['color'] ?>-500 bg-white dark:bg-slate-800 transition-all">
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-700">
          <button type="button" onclick="cancelProfileForm()" class="px-8 py-4 text-sm font-bold text-slate-500 hover:text-red-500 transition-all">
            Descartar Alterações
          </button>
          <button type="button" onclick="submitProfile(this)" id="submitBtn" 
                  class="px-12 py-4 text-sm font-black text-white bg-slate-900 dark:bg-white dark:text-slate-900 rounded-2xl hover:scale-105 active:scale-95 shadow-xl transition-all">
            Ativar Configurações
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Profiles Table Card -->

  <div class="bg-white/50 dark:bg-slate-800/50 backdrop-blur-xl rounded-3xl shadow-sm border border-slate-200/50 dark:border-slate-700/50 overflow-hidden transition-all">
    <div class="px-10 py-8 border-b border-slate-100 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
      <div>
        <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Cofre de Identidades</h3>
        <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-1">Repositório de Perfis e Permissões Ativas</p>
      </div>
    </div>
    <div class="overflow-x-auto ring-1 ring-slate-100 dark:ring-slate-700/50 rounded-2xl mx-6 mb-8 mt-2">
      <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700/50">
        <thead class="bg-slate-50/80 dark:bg-slate-900/80 backdrop-blur-md">
          <tr>
            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Estrutura de Perfil</th>
            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Escopo Operacional</th>
            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest text-center">Abrangência</th>
            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Classificação</th>
            <th class="px-8 py-5 text-right text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Ações de Gestão</th>
          </tr>
        </thead>
        <tbody id="profilesTableBody" class="divide-y divide-slate-50 dark:divide-slate-700/30 bg-white/30 dark:bg-transparent">
          <!-- Profiles Loaded via JS -->
          <tr>
            <td colspan="5" class="px-8 py-20 text-center">
              <div class="flex flex-col items-center gap-3">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Sincronizando Banco de Dados...</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

<script>
let currentProfileId = null;
const modulesByCategory = {
  'Ecossistema Geral': [
    { key: 'dashboard', name: 'Painel Geral Analítico 📊' },
    { key: 'profile', name: 'Configurações de Identidade 👤' }
  ],
  'Gestão de Cadastros': [
    { key: 'cadastros_2', name: 'Módulo Cadastros 2.0 📦' },
    { key: 'toners_cadastro', name: 'Registro Global de Toners 💧' },
    { key: 'cadastro_maquinas', name: 'Parque de Máquinas 🖨️' },
    { key: 'cadastro_pecas', name: 'Inventário de Peças 🔧' },
    { key: 'cadastro_defeitos', name: 'Matriz de Defeitos 🧩' },
    { key: 'registros_fornecedores', name: 'Cadeia de Fornecedores 🏭' },
    { key: 'cadastro_contratos', name: 'Gestão de Contratos 📄' },
    { key: 'cadastro_clientes', name: 'Base de Clientes 👥' },
    { key: 'registros_filiais', name: 'Unidades / Filiais 🏢' },
    { key: 'registros_departamentos', name: 'Setores / Departamentos 🏛️' }
  ],
  'Controle de Qualidade': [
    { key: 'triagem_toners', name: 'Central de Triagem 🔍' },
    { key: 'toners_retornados', name: 'Base de Retornados 📋' },
    { key: 'toners_defeitos', name: 'Toners com Não Conformidade 🔴' },
    { key: 'amostragens_2', name: 'Módulo Amostragens 2.0 🔬' },
    { key: 'garantias', name: 'Gestão de Garantias 🛡️' },
    { key: 'controle_descartes', name: 'Operações de Descarte ♻️' },
    { key: 'precificacao_coleta_descartes', name: 'Fluxo Financeiro de Coleta 💰' },
    { key: 'homologacoes', name: 'Matriz de Homologação ✅' },
    { key: 'certificados', name: 'Cofre de Certificados 📜' },
    { key: 'fmea', name: 'Análise FMEA 📈' },
    { key: 'pops_its', name: 'Arquitetura de POPs e ITs 📚' },
    { key: 'pops_its_cadastro_titulos', name: '→ Cadastro de Títulos' },
    { key: 'pops_its_meus_registros', name: '→ Meus Registros' },
    { key: 'pops_its_pendente_aprovacao', name: '→ Gestão de Aprovações' },
    { key: 'pops_its_visualizacao', name: '→ Visualização Master' },
    { key: 'pops_its_solicitacoes', name: '→ Solicitações de Expurgo' },
    { key: 'fluxogramas', name: 'Modelagem de Fluxogramas 🔀' },
    { key: 'auditorias', name: 'Ciclos de Auditoria 🔍' },
    { key: 'nao_conformidades', name: 'Gestão de RNCs ⚠️' },
    { key: 'melhoria_continua_2', name: 'Ciclo de Melhoria 2.0 🚀' },
    { key: 'controle_rc', name: 'Controle de RCs 🗂️' },
    { key: 'nps', name: 'Analytics de NPS 🎯' }
  ],
  'Engenharia & Implantação': [
    { key: 'implantacao_dpo', name: 'Engenharia DPO' },
    { key: 'implantacao_ordem_servicos', name: 'Ordens de Serviço' },
    { key: 'implantacao_fluxo', name: 'Workflow de Implantação' },
    { key: 'implantacao_relatorios', name: 'Dashboards de Implantação' }
  ],
  'CRM & Inteligência': [
    { key: 'crm_prospeccao', name: 'Pipeline Prospecção' },
    { key: 'crm_vendas', name: 'Gestão Comercial' },
    { key: 'crm_relacionamento', name: 'Loyalty & Relacionamento' },
    { key: 'crm_marketing', name: 'Marketing Hub' },
    { key: 'crm_relatorios', name: 'Reports Estratégicos' },
    { key: 'crm_dashboards', name: 'CRM Analytics' }
  ],
  'Supply Chain & Logística': [
    { key: 'logistica_entrada_estoque', name: 'Inbound Logística' },
    { key: 'logistica_entrada_almoxarifados', name: 'Gestão de Insumos' },
    { key: 'logistica_inventarios', name: 'Auditoria de Estoque' },
    { key: 'logistica_consulta_estoque', name: 'Consulta Disponibilidade' },
    { key: 'logistica_consulta_almoxarifado', name: 'Almoxarifado Geral' },
    { key: 'logistica_transferencias_internas', name: 'Movimentação Interna' },
    { key: 'logistica_transferencias_externas', name: 'Expedição Externa' },
    { key: 'logistica_estoque_tecnico', name: 'Estoque Avançado Técnico' }
  ],
  'Suporte Técnico': [
    { key: 'area_tecnica', name: 'Painel Operacional Técnico' },
    { key: 'area_tecnica_checklist', name: 'Checklist Inteligente' },
    { key: 'area_tecnica_consulta', name: 'Repositório de Checklists' },
    { key: 'calculadora_toners', name: 'Calculadora de Dimensionamento 🧮' }
  ],
  'Educação Corporativa': [
    { key: 'elearning_gestor',      name: 'E-Learning: Professor 👨‍🏫' },
    { key: 'elearning_colaborador', name: 'E-Learning: Aluno 🎓' },
    { key: 'elearning_relatorios',  name: 'E-Learning: Relatórios 📊' }
  ],
  'Administração Central': [
    { key: 'admin_usuarios', name: 'Gestão de Profissionais 👥' },
    { key: 'admin_perfis', name: 'Arquitetura de Perfis 🎭' },
    { key: 'admin_convites', name: 'Convites de Acesso 📧' },
    { key: 'admin_painel', name: 'Console Administrativo ⚙️' },
    { key: 'registros_parametros', name: 'Variáveis de Retorno 📊' },
    { key: 'configuracoes_gerais', name: 'DNA do Sistema' },
    { key: 'email_config', name: 'Servidores de Notificação' }
  ]
};

// Flatten modules for legacy support
const modules = Object.values(modulesByCategory).flat();

// Master User Detection (God Mode)
const currentUserEmail = '<?= $_SESSION['user_email'] ?? '' ?>';
const isMasterUser = currentUserEmail.toLowerCase() === 'du.claza@gmail.com' || <?= (isset($_SESSION['user_role']) && in_array(strtolower($_SESSION['user_role']), ['super_admin', 'superadmin'])) ? 'true' : 'false' ?>;

// Load profiles on page load
document.addEventListener('DOMContentLoaded', function() {
  loadProfiles();
  generatePermissionsGrid();
});

function loadProfiles() {
  fetch('/admin/profiles', {
    method: 'GET',
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
  })
  .then(response => {
    if (!response.ok) throw new Error('Network response was not ok');
    return response.json();
  })
  .then(result => {
    if (result.success) {
      const profilesList = Array.isArray(result.data) ? result.data : (Array.isArray(result.profiles) ? result.profiles : []);
      const profiles = profilesList.filter(p => !(String(p.name).toLowerCase() === 'super administrador' && !isMasterUser));
      displayProfiles(profiles);
      
      const totalSpan = document.getElementById('profilesTotal');
      if (totalSpan) totalSpan.textContent = profiles.length;
    } else {
      showToast('Erro ao carregar perfis: ' + result.message, 'error');
    }
  })
  .catch(error => {
    console.error('Error fetching profiles:', error);
    showToast('Erro de conexão ao carregar perfis, verifique se o Endpoint existe', 'error');
    document.getElementById('profilesTableBody').innerHTML = `<tr><td colspan="5" class="px-8 py-20 text-center text-red-500 font-bold">Falha ao carregar (Endpoint retornou HTML/Erro)</td></tr>`;
  });
}

function generatePermissionsGrid() {
  const grid = document.getElementById('permissionsGrid');
  let html = '';
  
  Object.keys(modulesByCategory).forEach(category => {
    html += `
      <div class="px-8 py-3 bg-slate-50/80 dark:bg-slate-900/80 backdrop-blur-md flex justify-between items-center border-y border-slate-100 dark:border-slate-700/30">
        <h5 class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest">${category}</h5>
        <div class="flex gap-2">
          <button type="button" onclick="toggleCategory('${category}', true)" class="px-2 py-1 text-[8px] font-bold bg-blue-50 dark:bg-blue-900/30 text-blue-600 rounded-md hover:bg-blue-100 transition-all">Sincronizar Tudo</button>
          <button type="button" onclick="toggleCategory('${category}', false)" class="px-2 py-1 text-[8px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-md hover:bg-slate-200 transition-all">Limpar</button>
        </div>
      </div>
    `;
    
    modulesByCategory[category].forEach(module => {
      html += `
        <div class="grid grid-cols-12 gap-4 px-8 py-4 items-center hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-all group" data-category="${category}">
          <div class="col-span-4 flex items-center gap-3">
            <div class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600 group-hover:bg-blue-500 transition-colors"></div>
            <span class="text-xs font-bold text-slate-600 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white transition-colors">${module.name}</span>
          </div>
          <div class="col-span-8 grid grid-cols-5 gap-4">
            ${['view', 'edit', 'delete', 'import', 'export'].map(type => `
              <div class="flex justify-center">
                <input type="checkbox" name="permissions[${module.key}][${type}]" data-module="${module.key}" data-type="${type}"
                       class="w-5 h-5 rounded-lg border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-slate-900 transition-all cursor-pointer">
              </div>
            `).join('')}
          </div>
        </div>
      `;
    });
  });
  
  grid.innerHTML = html;
}

function toggleCategory(category, checked) {
  const rows = document.querySelectorAll(`div[data-category="${category}"]`);
  rows.forEach(row => {
    const checkboxes = row.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = checked);
  });
}

function toggleColumn(type, checked) {
  const checkboxes = document.querySelectorAll(`input[data-type="${type}"]`);
  checkboxes.forEach(cb => cb.checked = checked);
}

function displayProfiles(profiles) {
  const tbody = document.getElementById('profilesTableBody');
  tbody.innerHTML = '';
  
  profiles.forEach(profile => {
    const row = document.createElement('tr');
    row.className = 'hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors group';
    
    const statusBadge = profile.is_admin == 1 
      ? '<span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-xl bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-400 border border-purple-200/50 dark:border-purple-700/50 shadow-sm shadow-purple-500/10">Full Admin</span>'
      : profile.is_default == 1 
        ? '<span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-700/50 shadow-sm shadow-emerald-500/10">Default</span>'
        : '<span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-xl bg-slate-100 text-slate-700 dark:bg-slate-700/50 dark:text-slate-300 border border-slate-200/50 dark:border-slate-600/50 shadow-sm">Operacional</span>';
    
    row.innerHTML = `
      <td class="px-8 py-5">
        <div class="flex items-center gap-4">
          <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center font-bold text-blue-600 dark:text-blue-400 border border-blue-200/50 dark:border-blue-700/50">
            ${profile.name.charAt(0).toUpperCase()}
          </div>
          <div>
            <div class="text-sm font-extrabold text-slate-800 dark:text-white leading-tight uppercase tracking-tight">${profile.name}</div>
            <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-tighter mt-0.5">ID: ARCH-00${profile.id}</div>
          </div>
        </div>
      </td>
      <td class="px-8 py-5">
        <div class="text-xs font-bold text-slate-500 dark:text-slate-400 truncate max-w-[200px]">${profile.description || 'Sem documentação técnica'}</div>
      </td>
      <td class="px-8 py-5 text-center">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-black bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
          ${profile.users_count}
        </span>
      </td>
      <td class="px-8 py-5">
        ${statusBadge}
      </td>
      <td class="px-8 py-5 text-right">
        <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
          ${(profile.is_admin == 1 && !isMasterUser) ? `
            <span class="text-[10px] font-bold text-slate-400 uppercase italic">Protegido</span>
          ` : `
            <button onclick="editProfile(${profile.id})" class="p-2 text-blue-500 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-xl transition-all" title="Editar Ficha">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </button>
            <button onclick="deleteProfile(${profile.id}, '${profile.name}')" class="p-2 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-xl transition-all" title="Eliminar Estrutura">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
          `}
        </div>
      </td>
    `;
    tbody.appendChild(row);
  });
}

function toggleProfileForm() {
  const container = document.getElementById('profileFormContainer');
  const btn = document.getElementById('toggleFormBtn');
  
  if (container.classList.contains('hidden')) {
    container.classList.remove('hidden');
    // Force reflow
    container.offsetHeight;
    container.classList.remove('opacity-0', 'scale-95');
    container.classList.add('opacity-100', 'scale-100');
    
    document.getElementById('formTitle').textContent = 'Projeto de Nova Arquitetura';
    document.getElementById('submitBtn').textContent = 'Ativar Perfil';
    document.getElementById('profileForm').reset();
    document.getElementById('profileId').value = '';
    generatePermissionsGrid();
    
    btn.innerHTML = `<div class="bg-red-500 rounded-lg p-1 text-white"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div>Descartar`;
  } else {
    cancelProfileForm();
  }
}

function editProfile(profileId) {
  fetch(`/admin/profiles/${profileId}/permissions`, {
    method: 'GET',
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      const profile = result.profile;
      const permissions = result.permissions;
      
      const container = document.getElementById('profileFormContainer');
      container.classList.remove('hidden');
      container.offsetHeight;
      container.classList.remove('opacity-0', 'scale-95');
      container.classList.add('opacity-100', 'scale-100');
      
      document.getElementById('formTitle').textContent = 'Revisar Arquitetura Operacional';
      document.getElementById('submitBtn').textContent = 'Aplicar Mudanças';
      document.getElementById('profileId').value = profile.id;
      document.getElementById('profileName').value = profile.name;
      document.getElementById('profileDescription').value = profile.description || '';
      document.getElementById('isDefault').checked = profile.is_default == 1;
      
      generatePermissionsGrid();
      
      Object.keys(permissions).forEach(module => {
        const perm = permissions[module];
        ['view', 'edit', 'delete', 'import', 'export'].forEach(type => {
          const cb = document.querySelector(`input[name="permissions[${module}][${type}]"]`);
          if (cb) cb.checked = perm['can_' + type] == 1;
        });
      });
      
      loadDashboardTabPermissions(profile.id);
      
      document.getElementById('toggleFormBtn').innerHTML = `<div class="bg-red-500 rounded-lg p-1 text-white"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div>Descartar`;
      container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
      showToast(result.message, 'error');
    }
  });
}

function cancelProfileForm() {
  const container = document.getElementById('profileFormContainer');
  container.classList.remove('opacity-100', 'scale-100');
  container.classList.add('opacity-0', 'scale-95');
  
  setTimeout(() => {
    container.classList.add('hidden');
    document.getElementById('profileForm').reset();
    document.getElementById('toggleFormBtn').innerHTML = `<div class="bg-blue-500 rounded-lg p-1"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></div>Projetar Novo Perfil`;
  }, 500);
}

async function submitProfile(btn) {
  const form = document.getElementById('profileForm');
  const formData = new FormData(form);
  const profileId = document.getElementById('profileId').value;
  
  setButtonLoading(btn);
  const url = profileId ? '/admin/profiles/update' : '/admin/profiles/create';
  
  try {
    const response = await fetch(url, { method: 'POST', body: formData });
    const result = await response.json();
    
    if (result.success) {
      showToast(result.message, 'success');
      cancelProfileForm();
      loadProfiles();
    } else {
      showToast(result.message, 'error');
    }
  } catch (error) {
    showToast('Erro crítico na comunicação com o servidor', 'error');
  } finally {
    setButtonLoading(btn, false);
  }
}

async function deleteProfile(profileId, profileName) {
  const confirm = await showConfirm('Eliminar Perfil', `Deseja realmente remover a estrutura <strong>"${profileName}"</strong>?<br>Esta ação é irreversível e afetará as permissões vinculadas.`, 'Confirmar', 'Cancelar', 'bg-red-600');
  if (!confirm) return;
  
  try {
    const formData = new FormData();
    formData.append('profile_id', profileId);
    
    const response = await fetch('/admin/profiles/delete', { method: 'POST', body: formData });
    const result = await response.json();
    
    if (result.success) {
      showToast(result.message, 'success');
      loadProfiles();
    } else {
      showToast(result.message, 'error');
    }
  } catch (error) {
    showToast('Erro de conexão ao remover perfil', 'error');
  }
}

function loadDashboardTabPermissions(profileId) {
  fetch(`/admin/profiles/${profileId}/dashboard-tabs`, {
    method: 'GET',
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(response => response.json())
  .then(result => {
    if (result.success && result.dashboard_tabs) {
      const tabs = result.dashboard_tabs;
      Object.keys(tabs).forEach(tab => {
        const cb = document.querySelector(`input[name="dashboard_tabs[${tab}]"]`);
        if (cb) cb.checked = tabs[tab] === true || tabs[tab] === 1;
      });
    }
  });
}
</script>
</script>
