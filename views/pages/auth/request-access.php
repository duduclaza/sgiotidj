<!-- Cabeçalho -->
<div class="text-center mb-7">
  <div class="logo-badge">
    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
    </svg>
  </div>
  <h1 class="text-xl font-bold text-white tracking-tight">Solicitar Acesso</h1>
  <p class="text-slate-500 text-xs mt-1 font-medium">Preencha os dados abaixo para solicitar seu acesso</p>
</div>

<!-- Form -->
<form id="requestForm" class="space-y-4">

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-5">
    <!-- Nome -->
    <div>
      <label class="auth-label">Nome completo</label>
      <div class="relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </span>
        <input id="name" name="name" type="text" required
               class="auth-input" placeholder="Seu nome completo">
      </div>
    </div>

    <!-- Email -->
    <div>
      <label class="auth-label">E-mail</label>
      <div class="relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
        </span>
        <input id="email" name="email" type="email" required
               class="auth-input" placeholder="seu@email.com">
      </div>
    </div>

    <!-- Senha -->
    <div>
      <label class="auth-label">Senha</label>
      <div class="relative">
        <input id="password" name="password" type="password" required minlength="6"
               class="auth-input" placeholder="Min. 6 caracteres" style="padding-left:14px; padding-right:36px;">
        <button type="button" onclick="togglePwd('password','eyePwd')"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 p-1 transition-colors">
          <svg id="eyePwd" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Confirmar -->
    <div>
      <label class="auth-label flex justify-between">
        Confirmar
        <span id="pwdMatch" class="hidden text-[10px] font-bold px-1 ml-2 mt-0.5"></span>
      </label>
      <div class="relative">
        <input id="password_confirm" name="password_confirm" type="password" required minlength="6"
               class="auth-input" placeholder="Repita a senha" style="padding-left:14px; padding-right:36px;">
        <button type="button" onclick="togglePwd('password_confirm','eyeConf')"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 p-1 transition-colors">
          <svg id="eyeConf" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Departamento -->
    <div>
      <label class="auth-label">Departamento</label>
      <select id="setor" name="setor" class="auth-input" style="padding-left:14px;">
        <option value="">Selecione...</option>
        <?php foreach ($departamentos as $dept): ?>
          <option value="<?= htmlspecialchars($dept['nome']) ?>"><?= htmlspecialchars($dept['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Filial -->
    <div>
      <label class="auth-label">Filial</label>
      <select id="filial" name="filial" class="auth-input" style="padding-left:14px;">
        <option value="">Selecione...</option>
        <?php foreach ($filiais as $filial): ?>
          <option value="<?= htmlspecialchars($filial['nome']) ?>"><?= htmlspecialchars($filial['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Justificativa -->
    <div class="sm:col-span-2">
      <label class="auth-label">Justificativa</label>
      <textarea id="justificativa" name="justificativa" required rows="2"
                class="auth-input" style="height: 60px; resize: none; padding-left: 14px; padding-top: 10px;"
                placeholder="Por que você precisa de acesso ao sistema?"></textarea>
    </div>
  </div>

  <!-- Feedback -->
  <div id="message" class="hidden text-xs font-semibold px-3 py-2.5 rounded-xl border"></div>

  <div class="pt-1">
    <button type="submit" id="submitBtn" class="btn-auth">
      <span id="submitText">Enviar Solicitação</span>
    </button>
  </div>
</form>

<!-- Rodapé -->
<div class="mt-5 flex items-center justify-center">
  <a href="/login" class="text-slate-500 hover:text-slate-300 text-xs font-medium transition-colors inline-flex items-center gap-1.5">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    Já tenho acesso — fazer login
  </a>
</div>

<script>
function togglePwd(inputId, iconId) {
  const inp = document.getElementById(inputId);
  const ico = document.getElementById(iconId);
  const isHidden = inp.type === 'password';
  inp.type = isHidden ? 'text' : 'password';
  ico.innerHTML = isHidden
    ? `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`
    : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
}

// Live password match
const pwd = document.getElementById('password');
const pwdC = document.getElementById('password_confirm');
const pwdMatch = document.getElementById('pwdMatch');
[pwd, pwdC].forEach(el => el.addEventListener('input', () => {
  if (!pwdC.value) { pwdMatch.className = 'hidden'; return; }
  pwdMatch.classList.remove('hidden');
  const ok = pwd.value === pwdC.value;
  pwdMatch.className = 'text-[11px] font-bold px-1 ' + (ok ? 'text-emerald-400' : 'text-red-400');
  pwdMatch.textContent  = ok ? '✓ Senhas coincidem' : '✕ Senhas não coincidem';
}));

document.getElementById('requestForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  if (pwd.value !== pwdC.value) {
    const msg = document.getElementById('message');
    msg.className = 'text-xs font-semibold px-3 py-2.5 rounded-xl border text-red-400 bg-red-400/10 border-red-400/20';
    msg.textContent = '✕ As senhas não coincidem';
    return;
  }
  const btn = document.getElementById('submitBtn');
  const msg = document.getElementById('message');
  setButtonLoading(btn, true);
  msg.className = 'hidden';

  try {
    const res = await fetch('/access-request/process', { method: 'POST', body: new FormData(this) });
    const result = await res.json();
    if (result.success) {
      msg.className = 'text-xs font-semibold px-3 py-2.5 rounded-xl border text-emerald-400 bg-emerald-400/10 border-emerald-400/20';
      msg.textContent = '✓ ' + result.message;
      this.reset();
      pwdMatch.className = 'hidden';
    } else {
      msg.className = 'text-xs font-semibold px-3 py-2.5 rounded-xl border text-red-400 bg-red-400/10 border-red-400/20';
      msg.textContent = '✕ ' + result.message;
    }
  } catch {
    msg.className = 'text-xs font-semibold px-3 py-2.5 rounded-xl border text-red-400 bg-red-400/10 border-red-400/20';
    msg.textContent = '✕ Erro de conexão. Tente novamente.';
  } finally {
    setButtonLoading(btn, false);
  }
});
</script>
