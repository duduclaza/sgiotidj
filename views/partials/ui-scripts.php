<script>
  // ========== GLOBAL TOAST ==========
  window.showToast = function(message, type = 'success', duration = 4000) {
    const stack = document.getElementById('global-toast-stack');
    if (!stack) return;

    const colors = {
      success: { bg: 'bg-emerald-600', icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>` },
      error:   { bg: 'bg-red-600',     icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>` },
      warning: { bg: 'bg-amber-500',   icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>` },
      info:    { bg: 'bg-blue-600',    icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>` },
    };
    const c = colors[type] || colors.info;

    const el = document.createElement('div');
    el.className = `toast-item flex items-center gap-3 px-4 py-3 rounded-xl text-white text-sm font-medium shadow-2xl ${c.bg}`;
    el.innerHTML = `
      <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><${c.icon}</svg>
      <span class="flex-1">${message}</span>
      <button onclick="this.parentElement.classList.add('removing');setTimeout(()=>this.parentElement.remove(),250)" class="opacity-60 hover:opacity-100 flex-shrink-0 transition-opacity">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>`;
    stack.appendChild(el);

    setTimeout(() => {
      if (el.parentElement) {
        el.classList.add('removing');
        setTimeout(() => el.remove(), 250);
      }
    }, duration);
  };

  // ========== GLOBAL MODAL HELPERS ==========
  window.showConfirm = function(message, onConfirm, options = {}) {
    const overlay = document.getElementById('global-confirm-overlay');
    const box     = document.getElementById('global-confirm-box');
    const msgEl   = document.getElementById('global-confirm-msg');
    const titleEl = document.getElementById('global-confirm-title');
    const okBtn   = document.getElementById('global-confirm-ok');
    const cancelBtn = document.getElementById('global-confirm-cancel');

    msgEl.textContent   = message;
    titleEl.textContent = options.title   || 'Confirmar ação';
    
    // Configurar botão de confirmação
    const isDanger = options.danger !== false;
    okBtn.textContent = options.okText || 'Confirmar';
    okBtn.className = `flex-1 px-4 py-3 text-sm font-bold text-white rounded-2xl transition-all shadow-lg ${isDanger ? 'bg-red-600 hover:bg-red-700 shadow-red-500/20' : 'bg-blue-600 hover:bg-blue-700 shadow-blue-500/20'}`;

    overlay.classList.remove('hidden');
    // Forçar reflow para animação
    overlay.offsetHeight;
    box.style.opacity = '1';
    box.style.transform = 'scale(1)';

    const close = () => {
      box.style.opacity = '0';
      box.style.transform = 'scale(0.95)';
      setTimeout(() => overlay.classList.add('hidden'), 200);
    };

    const confirmAction = () => {
      close();
      if (typeof onConfirm === 'function') onConfirm();
    };

    // Remover listeners antigos (clone)
    const newOk = okBtn.cloneNode(true);
    const newCancel = cancelBtn.cloneNode(true);
    okBtn.replaceWith(newOk);
    cancelBtn.replaceWith(newCancel);

    document.getElementById('global-confirm-ok').addEventListener('click', confirmAction);
    document.getElementById('global-confirm-cancel').addEventListener('click', close);
    overlay.addEventListener('click', e => { if (e.target === overlay) close(); }, { once: true });
  };

  // ========== BUTTON LOADING HELPER ==========
  window.setButtonLoading = function(btn, loading, originalContent) {
    if (!btn) return;
    if (loading) {
      btn.dataset.originalHtml = btn.innerHTML;
      btn.classList.add('btn-loading');
      btn.disabled = true;
    } else {
      btn.classList.remove('btn-loading');
      btn.disabled = false;
      if (btn.dataset.originalHtml) {
        btn.innerHTML = btn.dataset.originalHtml;
      }
    }
  };

  // ========== GLOBAL ESCAPE KEY ==========
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      // 1. Fechar confirm overlay
      const confirmOverlay = document.getElementById('global-confirm-overlay');
      if (confirmOverlay && !confirmOverlay.classList.contains('hidden')) {
        document.getElementById('global-confirm-cancel').click();
      }
      
      // 2. Fechar modais de Tailwind (padrão .fixed.inset-0)
      const visibleModals = Array.from(document.querySelectorAll('.fixed.inset-0:not(.hidden)'));
      if (visibleModals.length > 0) {
        // Tentar encontrar botão fechar ou apenas ocultar o último
        const lastModal = visibleModals[visibleModals.length - 1];
        const closeBtn = lastModal.querySelector('button[onclick*="close"], button[onclick*="fechar"], .close-modal');
        if (closeBtn) closeBtn.click();
        else lastModal.classList.add('hidden');
      }
    }
  });
</script>
