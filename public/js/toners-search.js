// ===== BUSCA INTELIGENTE NO GRID DE TONERS =====
// Arquivo separado para evitar conflitos e SyntaxErrors

// Variáveis de paginação globais
window.currentPage = 1;
window.itemsPerPage = 50;
window.currentMatchedRows = [];

// Função para limpar a busca
window.clearSearch = function() {
    console.log('🧽 Limpando busca...');
    const input = document.getElementById('searchToners');
    const select = document.getElementById('searchColumn');
    
    if (input) input.value = '';
    if (select) select.value = 'all';
    
    window.searchToners();
};

// Função de busca por coluna específica
window.searchToners = function() {
    console.log('🔍 Executando busca...');
    const input = document.getElementById('searchToners');
    const searchColumn = document.getElementById('searchColumn')?.value || 'all';
    
    if (!input) {
        console.error('❌ Campo de busca não encontrado!');
        return;
    }
    
    let tbody = document.getElementById('tonersTbody');
    if (!tbody) {
        tbody = document.querySelector('table tbody');
        console.warn('⚠️ Usando fallback para tbody');
    }
    
    if (!tbody) {
        console.error('❌ Tbody não encontrado!');
        return;
    }
    
    const rows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.cells.length >= 2);
    let visibleCount = 0;
    
    console.log(`📊 Total de linhas: ${rows.length}`);

    const raw = (input.value || '').trim().toLowerCase();
    const normalized = raw.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    const tokens = normalized.split(/\s+/).filter(Boolean);
    
    console.log(`🔑 Termos de busca: ${tokens.length > 0 ? tokens.join(', ') : '(vazio)'} | Coluna: ${searchColumn}`);

    // Remover possível linha de mensagem antiga
    const emptyMsg = tbody.querySelector('.no-results-row');
    if (emptyMsg) emptyMsg.remove();
    
    window.currentMatchedRows = [];

    rows.forEach((row, index) => {
      let isMatch = false;

      // Se não há termos de busca, mostrar tudo
      if (tokens.length === 0) {
        isMatch = true;
      } else {
        let haystack = '';
        if (searchColumn === 'all') {
          haystack = Array.from(row.cells).map(td => td.textContent || '').join(' ');
        } else {
          const columnIndex = parseInt(searchColumn);
          haystack = row.cells[columnIndex]?.textContent || '';
        }

        let norm = haystack.toLowerCase();
        norm = norm.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        isMatch = tokens.every(tok => norm.includes(tok));
      }

      if (isMatch) {
         window.currentMatchedRows.push(row);
      }
    });

    // Reset pagination to page 1 on search
    window.currentPage = 1;
    window.renderPage();
};

window.renderPage = function() {
    const tbody = document.getElementById('tonersTbody');
    const allRows = Array.from(tbody.querySelectorAll('tr:not(.no-results-row)'));
    
    // Esconder tudo primeiro
    allRows.forEach(row => row.style.display = 'none');
    
    const total = window.currentMatchedRows.length;
    
    // Remover mensagem vazia
    const emptyMsg = tbody.querySelector('.no-results-row');
    if (emptyMsg) emptyMsg.remove();
    
    if (total === 0) {
      if (allRows.length > 0) {
          const tr = document.createElement('tr');
          tr.className = 'no-results-row';
          const td = document.createElement('td');
          td.colSpan = 12;
          td.className = 'px-4 py-8 text-center text-gray-500';
          td.innerHTML = '<div class="flex flex-col items-center gap-2"><svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="font-medium">🔍 Nenhum resultado encontrado</span><span class="text-xs">Tente buscar com outros termos</span></div>';
          tr.appendChild(td);
          tbody.appendChild(tr);
      }
      window.updatePaginationUI(0, 0);
      window.updateResultsCount(0, allRows.length);
      return;
    }
    
    const totalPages = Math.ceil(total / window.itemsPerPage);
    if (window.currentPage > totalPages) window.currentPage = totalPages;
    
    const startIndex = (window.currentPage - 1) * window.itemsPerPage;
    const endIndex = Math.min(startIndex + window.itemsPerPage, total);
    
    // Mostrar as da página atual
    const pageRows = window.currentMatchedRows.slice(startIndex, endIndex);
    pageRows.forEach(row => row.style.display = '');
    
    window.updatePaginationUI(startIndex, endIndex, total, totalPages);
    window.updateResultsCount(total, allRows.length);
};

window.goToPage = function(page) {
    const totalPages = Math.ceil(window.currentMatchedRows.length / window.itemsPerPage);
    if (page >= 1 && page <= totalPages) {
        window.currentPage = page;
        window.renderPage();
    }
};

window.changeItemsPerPage = function(perPage) {
    window.itemsPerPage = parseInt(perPage, 10);
    window.currentPage = 1;
    // Dispara a busca toda vez que mudar a paginação para reconstruir as linhas visíveis previnindo Array vazio
    window.searchToners();
};

window.updatePaginationUI = function(startIndex, endIndex, total = 0, totalPages = 0) {
    const infoSpan = document.getElementById('paginationInfo');
    const controls = document.getElementById('paginationControls');
    
    if (infoSpan) {
        if (total === 0) {
            infoSpan.innerHTML = 'Nenhum resultado';
        } else {
            infoSpan.innerHTML = `Mostrando <span class="font-semibold text-slate-900 dark:text-white">${startIndex + 1}</span> até <span class="font-semibold text-slate-900 dark:text-white">${endIndex}</span> de <span class="font-semibold text-slate-900 dark:text-white">${total}</span> registros`;
        }
    }
    
    if (controls) {
        controls.innerHTML = '';
        if (totalPages > 1) {
            const btnClassNormal = "px-3 py-2 flex items-center justify-center border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer shadow-sm";
            const btnClassActive = "px-3 py-2 flex items-center justify-center border rounded-lg text-sm font-medium transition-colors bg-blue-600 text-white border-blue-600 shadow-md";

            // Primeira
            if (window.currentPage > 1) {
                const firstBtn = document.createElement('button');
                firstBtn.className = btnClassNormal;
                firstBtn.textContent = '« Primeira';
                firstBtn.onclick = () => window.goToPage(1);
                controls.appendChild(firstBtn);
            }

            // Anterior
            if (window.currentPage > 1) {
                const prevBtn = document.createElement('button');
                prevBtn.className = btnClassNormal;
                prevBtn.textContent = '‹ Anterior';
                prevBtn.onclick = () => window.goToPage(window.currentPage - 1);
                controls.appendChild(prevBtn);
            }
            
            // Números
            const limit = 2; // quantos de cada lado
            const inicio = Math.max(1, window.currentPage - limit);
            const fim = Math.min(totalPages, window.currentPage + limit);

            for (let p = inicio; p <= fim; p++) {
                const btn = document.createElement('button');
                btn.className = (p === window.currentPage) ? btnClassActive : btnClassNormal;
                btn.textContent = p;
                if (p !== window.currentPage) btn.onclick = () => window.goToPage(p);
                controls.appendChild(btn);
            }
            
            // Próxima
            if (window.currentPage < totalPages) {
                const nextBtn = document.createElement('button');
                nextBtn.className = btnClassNormal;
                nextBtn.textContent = 'Próxima ›';
                nextBtn.onclick = () => window.goToPage(window.currentPage + 1);
                controls.appendChild(nextBtn);
            }

            // Última
            if (window.currentPage < totalPages) {
                const lastBtn = document.createElement('button');
                lastBtn.className = btnClassNormal;
                lastBtn.textContent = 'Última »';
                lastBtn.onclick = () => window.goToPage(totalPages);
                controls.appendChild(lastBtn);
            }
        }
    }
};

// Função para atualizar contador de resultados
window.updateResultsCount = function(visibleCount, totalCount) {
    const resultsCount = document.getElementById('resultsCount');
    if (resultsCount) {
      const resultText = visibleCount === totalCount 
        ? `${totalCount} toner(s) cadastrado(s)` 
        : `Mostrando ${visibleCount} de ${totalCount} toner(s)`;
      resultsCount.textContent = resultText;
    }
};

// Debounce helper
function debounce(fn, delay = 200) {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(null, args), delay);
    };
}

// Inicialização quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
  console.log('🚀 Inicializando busca de toners...');
  
  // Bind live events
  const searchInput = document.getElementById('searchToners');
  const searchSelect = document.getElementById('searchColumn');
  const runSearch = debounce(() => window.searchToners(), 150);
  
  if (searchInput) {
    searchInput.addEventListener('input', runSearch);
    searchInput.addEventListener('keyup', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        window.searchToners();
      } else {
        runSearch();
      }
    });
  }
  
  if (searchSelect) {
    searchSelect.addEventListener('change', () => {
      window.searchToners();
      if (searchInput) searchInput.focus();
    });
  }
  
  const runBtn = document.getElementById('runSearchBtn');
  if (runBtn) {
    runBtn.addEventListener('click', () => {
      window.searchToners();
    });
  }
  
  const searchActionBtn = document.getElementById('searchActionBtn');
  if (searchActionBtn) {
    searchActionBtn.addEventListener('click', () => {
      window.searchToners();
    });
  }
  
  const clearBtn = document.getElementById('clearSearchBtn');
  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      window.clearSearch();
    });
  }

  // Evitar submit de qualquer formulário ao pressionar Enter no campo de busca
  document.addEventListener('keydown', (e) => {
    const active = document.activeElement;
    if (e.key === 'Enter' && active && active.id === 'searchToners') {
      e.preventDefault();
      window.searchToners();
    }
  }, true);

  // Primeiro cálculo do contador e estado inicial
  const initialRows = document.querySelectorAll('#tonersTbody tr').length;
  window.updateResultsCount(initialRows, initialRows);
  window.searchToners();
  
  console.log('✅ Busca de toners inicializada com sucesso!');
});
