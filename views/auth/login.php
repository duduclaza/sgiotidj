<!-- Card Glassmorphism -->
<div class="glass-card p-10 relative overflow-hidden">
  <!-- Brilho decorativo -->
  <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/20 rounded-full blur-3xl"></div>
  <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-indigo-500/20 rounded-full blur-3xl"></div>

  <!-- Logo DJ no topo -->
  <div class="text-center mb-10 relative z-10">
    <div class="bg-white/80 backdrop-blur-md p-4 rounded-2xl inline-block shadow-lg border border-white/50 mb-4">
      <img src="/assets/logodj.png" alt="DJ Logo" class="h-10 object-contain">
    </div>
    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Bem-vindo ao SGQ</h2>
    <p class="text-slate-500 text-sm mt-1">Acesse sua conta para continuar</p>
  </div>

  <!-- Login Form -->
  <form id="loginForm" class="space-y-5 relative z-10">
    <div>
      <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 ml-1">Email Corporativo</label>
      <div class="relative group">
        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
        </span>
        <input type="email" name="email" required 
               class="w-full pl-12 pr-4 py-3.5 bg-white/50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
               placeholder="nome@empresa.com.br">
      </div>
    </div>

    <div>
      <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 ml-1">Senha de Acesso</label>
      <div class="relative group">
        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </span>
        <input type="password" name="password" id="loginPassword" required 
               class="w-full pl-12 pr-12 py-3.5 bg-white/50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
               placeholder="••••••••">
        <button type="button" onclick="toggleLoginPassword()" 
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600 transition-colors p-2">
          <svg id="loginEyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
          </svg>
        </button>
      </div>
    </div>

    <button type="submit" id="btnLogin"
            class="w-full btn-primary text-white font-bold py-4 px-6 rounded-xl text-lg flex items-center justify-center gap-2 mt-2">
      <span class="btn-text">Entrar no Sistema</span>
    </button>
  </form>

  <!-- Links -->
  <div class="mt-8 text-center space-y-4 pt-6 border-t border-slate-200/50">
    <a href="/password-reset/request" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors inline-flex items-center gap-2">
      Esqueci minha senha?
    </a>
    <div class="flex items-center justify-center gap-3">
      <span class="h-px w-8 bg-slate-200"></span>
      <span class="text-xs text-slate-400 uppercase font-bold tracking-widest">ou</span>
      <span class="h-px w-8 bg-slate-200"></span>
    </div>
    <a href="/request-access" class="w-full py-3 px-4 rounded-xl border-2 border-slate-200 text-slate-700 font-bold hover:bg-slate-50 hover:border-slate-300 transition-all inline-block">
      Solicitar Acesso
    </a>
  </div>
</div>

<script>
function toggleLoginPassword() {
  const passwordInput = document.getElementById('loginPassword');
  const eyeIcon = document.getElementById('loginEyeIcon');
  
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

document.getElementById('loginForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const btn = document.getElementById('btnLogin');
  const formData = new FormData(this);
  
  setButtonLoading(btn, true);
  
  fetch('/auth/login', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      showToast(result.message || 'Login realizado!', 'success');
      setTimeout(() => {
        window.location.href = result.redirect || '/';
      }, 800);
    } else {
      setButtonLoading(btn, false);
      showToast(result.message || 'Erro ao fazer login', 'error');
    }
  })
  .catch(error => {
    setButtonLoading(btn, false);
    showToast('Erro de conexão. Tente novamente.', 'error');
  });
});
</script>
