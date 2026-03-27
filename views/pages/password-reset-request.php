<!-- Cabeçalho -->
<div class="text-center mb-8">
  <h1 class="text-xl font-bold text-white tracking-tight">Recuperar Senha</h1>
  <p class="text-slate-500 text-xs mt-1 font-medium">Informe seu e-mail para receber o código</p>
</div>

<!-- Form -->
<form id="formRequestReset" class="space-y-4">
  <div>
    <label class="auth-label">E-mail corporativo</label>
    <div class="relative">
      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
      </span>
      <input type="email" id="email" name="email" required
             class="auth-input"
             placeholder="nome@empresa.com.br"
             autocomplete="email">
    </div>
  </div>

  <!-- Feedback inline -->
  <div id="message" class="hidden text-xs font-semibold px-3 py-2.5 rounded-xl border"></div>

  <div class="pt-1">
    <button type="submit" id="btnSubmit" class="btn-auth">
      <span class="btn-text">Enviar código de verificação</span>
    </button>
  </div>
</form>

<!-- Rodapé -->
<div class="mt-6 text-center">
  <a href="/login" class="text-slate-500 hover:text-slate-300 text-xs font-medium transition-colors inline-flex items-center gap-1.5">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    Voltar para o login
  </a>
</div>

<!-- Dica -->
<div class="mt-5 p-3 rounded-xl bg-white/[0.03] border border-white/[0.05] text-center space-y-1">
  <p class="text-[11px] text-slate-500 font-medium">O código expira em <span class="text-slate-400 font-bold">2 minutos</span></p>
  <p class="text-[11px] text-slate-600">Verifique sua caixa de entrada e spam</p>
</div>

<script>
document.getElementById('formRequestReset').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('btnSubmit');
  const msg = document.getElementById('message');
  const email = document.getElementById('email').value;

  setButtonLoading(btn, true);
  msg.className = 'hidden';

  try {
    const response = await fetch('/password-reset/request', { method: 'POST', body: new FormData(this) });
    const result = await response.json();

    if (result.success) {
      msg.className = 'text-xs font-semibold px-3 py-2.5 rounded-xl border text-emerald-400 bg-emerald-400/10 border-emerald-400/20';
      msg.textContent = '✓ ' + result.message;
      setTimeout(() => {
        window.location.href = '/password-reset/verify?email=' + encodeURIComponent(email);
      }, 1800);
    } else {
      msg.className = 'text-xs font-semibold px-3 py-2.5 rounded-xl border text-red-400 bg-red-400/10 border-red-400/20';
      msg.textContent = '✕ ' + result.message;
      setButtonLoading(btn, false);
    }
  } catch {
    msg.className = 'text-xs font-semibold px-3 py-2.5 rounded-xl border text-red-400 bg-red-400/10 border-red-400/20';
    msg.textContent = '✕ Erro de conexão. Tente novamente.';
    setButtonLoading(btn, false);
  }
});
</script>
