<!-- Logo e cabeçalho -->
<div class="text-center mb-8">
  <div class="logo-badge" style="background:transparent; box-shadow:none; padding:0; width:auto; height:auto; border-radius:0;">
    <img src="/assets/otilogo.png" alt="OTI" class="h-12 object-contain mx-auto" style="filter: brightness(0) invert(1);">
  </div>
  <h1 class="text-xl font-bold text-white tracking-tight mt-4">Bem-vindo ao SGI OTI</h1>
  <p class="text-slate-400 text-xs mt-1 font-medium leading-relaxed px-2">
    Sistema de Gestão Integrada para empresas de Outsourcing de TI
  </p>
  <p class="text-slate-600 text-[11px] mt-2 font-medium flex items-center justify-center gap-1.5">
    <span class="status-dot"></span>
    Sistema operacional
  </p>
</div>

<!-- Form -->
<form id="loginForm" class="space-y-4">
  <div>
    <label class="auth-label">E-mail</label>
    <div class="relative">
      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
      </span>
      <input type="email" name="email" required
             class="auth-input"
             placeholder="seu@email.com"
             autocomplete="email">
    </div>
  </div>

  <div>
    <label class="auth-label">Senha</label>
    <div class="relative">
      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
      </span>
      <input type="password" name="password" id="loginPassword" required
             class="auth-input pr-10"
             placeholder="••••••••"
             autocomplete="current-password">
      <button type="button" onclick="toggleLoginPassword()"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors p-1">
        <svg id="loginEyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        </svg>
      </button>
    </div>
  </div>

  <div class="pt-1">
    <button type="submit" id="btnLogin" class="btn-auth">
      <span class="btn-text">Entrar</span>
    </button>
  </div>
</form>

<!-- Links inferiores -->
<div class="mt-6 flex items-center justify-between text-xs">
  <a href="/password-reset/request"
     class="text-slate-500 hover:text-slate-300 transition-colors font-medium">
    Esqueci minha senha
  </a>
  <a href="/request-access"
     class="text-slate-500 hover:text-slate-300 transition-colors font-medium">
    Solicitar acesso
  </a>
</div>

<script>
function toggleLoginPassword() {
  const input = document.getElementById('loginPassword');
  const icon = document.getElementById('loginEyeIcon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>`;
  } else {
    input.type = 'password';
    icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>`;
  }
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const btn = document.getElementById('btnLogin');
  setButtonLoading(btn, true);

  fetch('/auth/login', { method: 'POST', body: new FormData(this) })
    .then(r => r.json())
    .then(result => {
      if (result.success) {
        showToast(result.message || 'Login realizado!', 'success');
        setTimeout(() => { window.location.href = result.redirect || '/'; }, 800);
      } else {
        setButtonLoading(btn, false);
        showToast(result.message || 'Credenciais inválidas', 'error');
      }
    })
    .catch(() => {
      setButtonLoading(btn, false);
      showToast('Erro de conexão. Tente novamente.', 'error');
    });
});
</script>
