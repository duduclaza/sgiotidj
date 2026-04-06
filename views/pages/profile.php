<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Meu Perfil</h1>
  </div>

  <!-- Profile Card -->
  <div class="bg-white border rounded-lg p-6">
    <div class="flex items-center space-x-6 mb-6">
      <div class="relative">
        <div id="profilePhotoContainer" class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
          <img id="profilePhoto" src="" alt="Foto de Perfil" class="w-full h-full object-cover hidden">
          <svg id="defaultAvatar" class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
          </svg>
        </div>
        <button onclick="document.getElementById('photoInput').click()" class="absolute bottom-0 right-0 bg-blue-600 text-white rounded-full p-1 hover:bg-blue-700">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
        </button>
        <input type="file" id="photoInput" accept="image/*" class="hidden" onchange="uploadPhoto()">
      </div>
      <div>
        <h2 class="text-xl font-semibold text-gray-900" id="userName">Carregando...</h2>
        <p class="text-gray-600" id="userEmail">Carregando...</p>
        <p class="text-sm text-gray-500" id="userInfo">Carregando...</p>
      </div>
    </div>

    <!-- Notification Preferences -->
    <div class="border-t pt-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Preferências de Notificações</h3>
      
      <!-- Alert de Reload -->
      <div id="reloadAlert" class="hidden bg-blue-50 border-l-4 border-blue-500 p-4 mb-4 rounded">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
          </svg>
          <div>
            <p class="text-sm font-medium text-blue-800">
              ⏳ Aguarde... Página será recarregada para aplicar as mudanças!
            </p>
            <p class="text-xs text-blue-600 mt-1">
              O sino aparecerá ou desaparecerá automaticamente após o reload.
            </p>
          </div>
        </div>
      </div>
      
      <div class="bg-gradient-to-r from-yellow-50 to-amber-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-start space-x-3">
          <div class="flex items-center h-5">
            <input type="checkbox" id="notificacoesToggle" class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
          </div>
          <div class="flex-1">
            <label for="notificacoesToggle" class="font-medium text-gray-900 cursor-pointer">
              🔔 Receber Notificações do Sistema
            </label>
            <p class="text-sm text-gray-600 mt-1">
              Quando ativado, você verá o sino de notificações na barra lateral e receberá alertas visuais e sonoros sobre eventos importantes do sistema (aprovações, atualizações, etc).
            </p>
            <p class="text-xs text-gray-500 mt-2">
              <strong>Importante:</strong> Após alterar, a página recarregará automaticamente para aplicar as mudanças. O sino aparecerá ou desaparecerá após o reload.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Change Password Form -->
    <div class="border-t pt-6 mt-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Alterar Senha</h3>
      <form id="changePasswordForm" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Senha Atual *</label>
            <input type="password" name="current_password" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nova Senha *</label>
            <input type="password" name="new_password" required minlength="6" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nova Senha *</label>
            <input type="password" name="confirm_password" required minlength="6" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>
        </div>
        <div class="flex justify-end">
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
            Alterar Senha
          </button>
        </div>
      </form>
    </div>
  </div>
</section>

<script>
let currentUser = null;

// Load user profile on page load
document.addEventListener('DOMContentLoaded', function() {
  loadUserProfile();
});

function loadUserProfile() {
  fetch('/api/profile')
    .then(response => response.json())
    .then(user => {
      if (user.error) {
        alert('Erro ao carregar perfil: ' + user.error);
        return;
      }
      
      currentUser = user;
      document.getElementById('userName').textContent = user.name;
      document.getElementById('userEmail').textContent = user.email;
      document.getElementById('userInfo').textContent = 'Usuário do Sistema';
      
      // Load notification preference
      const notifToggle = document.getElementById('notificacoesToggle');
      if (notifToggle) {
        notifToggle.checked = user.notificacoes_ativadas == 1 || user.notificacoes_ativadas === true || user.notificacoes_ativadas === undefined;
        
        // Add change listener
        notifToggle.addEventListener('change', function() {
          updateNotificationPreference(this.checked);
        });
      }
      
      // Load profile photo if exists
      if (user.profile_photo) {
        const img = document.getElementById('profilePhoto');
        img.src = `data:${user.profile_photo_type};base64,${user.profile_photo}`;
        img.classList.remove('hidden');
        document.getElementById('defaultAvatar').classList.add('hidden');
        
        // Atualizar também a miniatura da sidebar
        const sidebarImg = document.getElementById('sidebarUserPhoto');
        const sidebarInitial = document.getElementById('sidebarUserInitial');
        if (sidebarImg && sidebarInitial) {
          sidebarImg.src = `data:${user.profile_photo_type};base64,${user.profile_photo}`;
          sidebarImg.classList.remove('hidden');
          sidebarInitial.classList.add('hidden');
        }
      }
    })
    .catch(error => {
      console.error('Erro ao carregar perfil:', error);
      alert('Erro ao carregar perfil do usuário');
    });
}

function uploadPhoto() {
  const fileInput = document.getElementById('photoInput');
  const file = fileInput.files[0];
  
  if (!file) return;
  
  // Validate file type
  if (!file.type.startsWith('image/')) {
    alert('Por favor, selecione apenas arquivos de imagem.');
    return;
  }
  
  // Validate file size (max 5MB)
  if (file.size > 5 * 1024 * 1024) {
    alert('A imagem deve ter no máximo 5MB.');
    return;
  }
  
  const formData = new FormData();
  formData.append('profile_photo', file);
  
  fetch('/api/profile/photo', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      // Update photo display
      const img = document.getElementById('profilePhoto');
      const reader = new FileReader();
      reader.onload = function(e) {
        img.src = e.target.result;
        img.classList.remove('hidden');
        document.getElementById('defaultAvatar').classList.add('hidden');
        
        // Atualizar também a miniatura da sidebar
        const sidebarImg = document.getElementById('sidebarUserPhoto');
        const sidebarInitial = document.getElementById('sidebarUserInitial');
        if (sidebarImg && sidebarInitial) {
          sidebarImg.src = e.target.result;
          sidebarImg.classList.remove('hidden');
          sidebarInitial.classList.add('hidden');
        }
      };
      reader.readAsDataURL(file);
      
      alert('Foto de perfil atualizada com sucesso!');
    } else {
      alert('Erro ao atualizar foto: ' + result.message);
    }
  })
  .catch(error => {
    console.error('Erro ao fazer upload da foto:', error);
    alert('Erro ao fazer upload da foto');
  });
}

// Update notification preference
function updateNotificationPreference(enabled) {
  const formData = new FormData();
  formData.append('notificacoes_ativadas', enabled ? '1' : '0');
  
  console.log('Atualizando notificações para:', enabled ? 'ATIVADO' : 'DESATIVADO');
  
  // Mostrar alert de reload
  const reloadAlert = document.getElementById('reloadAlert');
  if (reloadAlert) {
    reloadAlert.classList.remove('hidden');
  }
  
  fetch('/api/profile/notifications', {
    method: 'POST',
    body: formData
  })
  .then(response => {
    console.log('Response status:', response.status);
    return response.json();
  })
  .then(result => {
    console.log('Resultado da API:', result);
    
    if (result.success) {
      // Show success message
      showNotification(result.message, 'success');
      
      // Mostrar debug info se disponível
      if (result.debug) {
        console.log('Debug info:', result.debug);
        console.log('✅ Banco atualizado para:', result.debug.novo_valor);
        console.log('✅ Sessão atualizada:', result.debug.session_updated);
      }
      
      // Reload imediato e forçado com cache clear
      console.log('⏳ Recarregando página em 1.5 segundos...');
      console.log('🔔 Sino', enabled ? 'APARECERÁ' : 'DESAPARECERÁ', 'após o reload');
      
      setTimeout(() => {
        // Força reload completo sem cache
        console.log('🔄 Executando reload...');
        window.location.href = window.location.pathname + '?reload=' + Date.now();
      }, 1500);
    } else {
      // Revert checkbox on error
      document.getElementById('notificacoesToggle').checked = !enabled;
      showNotification('Erro: ' + result.message, 'error');
      console.error('Erro ao salvar:', result.message);
      
      // Esconder alert de reload
      if (reloadAlert) {
        reloadAlert.classList.add('hidden');
      }
    }
  })
  .catch(error => {
    console.error('Erro ao atualizar notificações:', error);
    document.getElementById('notificacoesToggle').checked = !enabled;
    showNotification('Erro ao atualizar preferências de notificação', 'error');
    
    // Esconder alert de reload
    if (reloadAlert) {
      reloadAlert.classList.add('hidden');
    }
  });
}

// Show notification toast
function showNotification(message, type = 'info') {
  // Create toast element
  const toast = document.createElement('div');
  toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300 ${
    type === 'success' ? 'bg-green-500 text-white' : 
    type === 'error' ? 'bg-red-500 text-white' : 
    'bg-blue-500 text-white'
  }`;
  toast.textContent = message;
  
  document.body.appendChild(toast);
  
  // Fade out and remove after 3 seconds
  setTimeout(() => {
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// Handle password change form
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const newPassword = formData.get('new_password');
  const confirmPassword = formData.get('confirm_password');
  
  if (newPassword !== confirmPassword) {
    showNotification('A nova senha e a confirmação não coincidem.', 'error');
    return;
  }
  
  fetch('/api/profile/password', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      showNotification('Senha alterada com sucesso!', 'success');
      this.reset();
    } else {
      showNotification('Erro ao alterar senha: ' + result.message, 'error');
    }
  })
  .catch(error => {
    console.error('Erro ao alterar senha:', error);
    showNotification('Erro ao alterar senha', 'error');
  });
});
</script>