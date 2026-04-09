// Modal Utilities - Sistema SGQ
// Funções utilitárias para modais padronizados

class SGQModal {
  constructor(modalId, options = {}) {
    this.modalId = modalId;
    this.options = {
      closeOnOverlayClick: true,
      closeOnEscape: true,
      animationDuration: 300,
      ...options
    };
    
    this.modal = null;
    this.isOpen = false;
    
    this.init();
  }
  
  init() {
    // Encontrar ou criar o modal
    this.modal = document.getElementById(this.modalId);
    if (!this.modal) {
      console.error(`Modal ${this.modalId} não encontrado`);
      return;
    }
    
    // Aplicar classes padrão
    this.applyStandardClasses();
    
    // Configurar event listeners
    this.setupEventListeners();
  }
  
  applyStandardClasses() {
    // Aplicar classes do overlay
    this.modal.className = 'modal-overlay';
    
    // Encontrar ou criar container
    let container = this.modal.querySelector('.modal-container');
    if (!container) {
      // Se não existe container, envolver o conteúdo existente
      const content = this.modal.innerHTML;
      this.modal.innerHTML = `<div class="modal-container">${content}</div>`;
      container = this.modal.querySelector('.modal-container');
    } else {
      container.className = 'modal-container';
    }
    
    // Aplicar classes aos elementos internos
    const header = container.querySelector('.modal-header, .px-6.py-4.border-b');
    if (header) {
      header.className = 'modal-header';
    }
    
    const body = container.querySelector('.modal-body, .px-6.py-6');
    if (body) {
      body.className = 'modal-body';
    }
    
    const footer = container.querySelector('.modal-footer, .px-6.py-4.bg-gray-50');
    if (footer) {
      footer.className = 'modal-footer';
    }
  }
  
  setupEventListeners() {
    // Fechar ao clicar no overlay
    if (this.options.closeOnOverlayClick) {
      this.modal.addEventListener('click', (e) => {
        if (e.target === this.modal) {
          this.close();
        }
      });
    }
    
    // Fechar com ESC
    if (this.options.closeOnEscape) {
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && this.isOpen) {
          this.close();
        }
      });
    }
    
    // Botões de fechar
    const closeButtons = this.modal.querySelectorAll('[data-modal-close], .modal-close');
    closeButtons.forEach(btn => {
      btn.addEventListener('click', () => this.close());
    });
  }
  
  open() {
    if (this.isOpen) return;
    
    this.modal.style.display = 'flex';
    
    // Force reflow
    this.modal.offsetHeight;
    
    this.modal.classList.add('active');
    this.isOpen = true;
    
    // Prevenir scroll do body
    document.body.style.overflow = 'hidden';
    
    // Disparar evento
    this.modal.dispatchEvent(new CustomEvent('modal:opened', { detail: { modalId: this.modalId } }));
  }
  
  close() {
    if (!this.isOpen) return;
    
    this.modal.classList.remove('active');
    
    setTimeout(() => {
      this.modal.style.display = 'none';
      this.isOpen = false;
      
      // Restaurar scroll do body
      document.body.style.overflow = '';
      
      // Disparar evento
      this.modal.dispatchEvent(new CustomEvent('modal:closed', { detail: { modalId: this.modalId } }));
    }, this.options.animationDuration);
  }
  
  toggle() {
    if (this.isOpen) {
      this.close();
    } else {
      this.open();
    }
  }
}

// Função global para inicializar todos os modais
function initializeModals() {
  const modals = document.querySelectorAll('[id$="Modal"], .modal-overlay');
  
  modals.forEach(modal => {
    if (!modal.sgqModal) {
      modal.sgqModal = new SGQModal(modal.id);
    }
  });
}

// Funções globais de conveniência
window.openModal = function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    // Try to append modal to document body to break out of iframe
    if (window.parent && window.parent.document && window.parent.document.body) {
      try {
        // Clone modal to parent document
        const parentModal = modal.cloneNode(true);
        parentModal.id = modalId + '_parent';
        window.parent.document.body.appendChild(parentModal);
        
        // Add event listeners to parent modal
        const closeBtn = parentModal.querySelector('[data-modal-close]');
        if (closeBtn) {
          closeBtn.addEventListener('click', () => {
            parentModal.remove();
            window.parent.document.body.style.overflow = '';
          });
        }
        
        // Close on overlay click
        parentModal.addEventListener('click', (e) => {
          if (e.target === parentModal) {
            parentModal.remove();
            window.parent.document.body.style.overflow = '';
          }
        });
        
        // Show parent modal
        parentModal.classList.add('active');
        window.parent.document.body.style.overflow = 'hidden';
        return;
      } catch (e) {
        console.log('Could not open modal in parent window, using iframe modal');
      }
    }
    
    // Fallback to iframe modal
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  } else {
    // Fallback para modais não inicializados
    const newModal = new SGQModal(modalId);
    newModal.open();
  }
};

window.closeModal = function(modalId) {
  // Try to close parent modal first
  const parentModal = window.parent && window.parent.document ? 
    window.parent.document.getElementById(modalId + '_parent') : null;
  
  if (parentModal) {
    parentModal.remove();
    window.parent.document.body.style.overflow = '';
    return;
  }
  
  // Fallback to iframe modal
  const modal = document.getElementById(modalId);
  if (modal && modal.sgqModal) {
    modal.sgqModal.close();
  } else {
    // Fallback manual close
    if (modal) {
      modal.classList.remove('active');
      document.body.style.overflow = '';
    }
  }
};

window.toggleModal = function(modalId) {
  const modal = document.getElementById(modalId);
  if (modal && modal.sgqModal) {
    modal.sgqModal.toggle();
  }
};

// Auto-inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', initializeModals);

// Reinicializar quando novos modais forem adicionados dinamicamente
const observer = new MutationObserver(function(mutations) {
  mutations.forEach(function(mutation) {
    mutation.addedNodes.forEach(function(node) {
      if (node.nodeType === 1) { // Element node
        if (node.id && node.id.includes('Modal')) {
          if (!node.sgqModal) {
            node.sgqModal = new SGQModal(node.id);
          }
        }
        
        // Verificar filhos também
        const childModals = node.querySelectorAll && node.querySelectorAll('[id$="Modal"]');
        if (childModals) {
          childModals.forEach(childModal => {
            if (!childModal.sgqModal) {
              childModal.sgqModal = new SGQModal(childModal.id);
            }
          });
        }
      }
    });
  });
});

// Verificar se document.body existe antes de observar
if (document.body) {
  observer.observe(document.body, {
    childList: true,
    subtree: true
  });
} else {
  // Se document.body ainda não existe, aguardar o DOM
  document.addEventListener('DOMContentLoaded', function() {
    if (document.body) {
      observer.observe(document.body, {
        childList: true,
        subtree: true
      });
    }
  });
}

// Exportar para uso em módulos
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { SGQModal, initializeModals };
}
