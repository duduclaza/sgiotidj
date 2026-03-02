<?php
// Forçar cabeçalhos de não-cache no PHP (mais forte que HTML meta tags)
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

$title = $title ?? 'SGQ OTI - DJ';
$viewFile = $viewFile ?? __DIR__ . '/../pages/home.php';
$sidebar = __DIR__ . '/../partials/sidebar.php';
// Versão dinâmica para evitar cache (time() força atualização a cada reload)
// Em produção, isso pode ser alterado para uma string fixa para performance
$assetVersion = time();
// Safe helper fallbacks in case global helpers are not loaded
if (!function_exists('e')) {
  function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('flash')) {
  function flash($key) { return null; }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Meta tags de cache (reforço) -->
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <link rel="icon" href="data:,">
  <title><?= e($title) ?></title>
  <script>if(window.console){const o=console.warn;console.warn=(...a)=>{if(a[0]&&String(a[0]).includes('cdn.tail'))return;o.apply(console,a)}}</script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <link rel="stylesheet" href="/src/Support/modal-styles.css?v=<?= urlencode($assetVersion) ?>">
  <script src="/src/Support/modal-utils.js?v=<?= urlencode($assetVersion) ?>"></script>
  <script>
    // ===== TOGGLE SUBMENU - GLOBAL FUNCTION =====
    // Definir PRIMEIRO, antes de qualquer outra coisa
    window.toggleSubmenu = function(button) {
      // console.log('toggleSubmenu global chamada!', button);
      const submenu = button.parentElement.querySelector('.submenu');
      const arrow = button.querySelector('.submenu-arrow');
      if (submenu && arrow) {
        submenu.classList.toggle('hidden');
        arrow.style.transform = submenu.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        // console.log('Submenu toggled - hidden:', submenu.classList.contains('hidden'));
      } else {
        // console.error('ERRO: Submenu ou arrow não encontrado!', {submenu, arrow, parent: button.parentElement});
      }
    }
    // console.log('[LAYOUT] toggleSubmenu definida:', typeof window.toggleSubmenu);
    
    // User permissions for frontend
    window.userPermissions = <?= json_encode($_SESSION['user_permissions'] ?? []) ?>;
  </script>
  <script>
    // Tailwind config with dark theme
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc', 400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca', 800: '#3730a3', 900: '#312e81'
            },
          }
        }
      }
    }
  </script>
  <style>
    /* Page transition styles */
    .page-transition {
      opacity: 0;
      transform: translateY(20px);
      transition: all 0.3s ease-in-out;
    }
    .page-transition.loaded {
      opacity: 1;
      transform: translateY(0);
    }
    
    /* Smooth scrolling */
    html {
      scroll-behavior: smooth;
    }
    
    /* Loading overlay removido - causava problemas globais */
  </style>
</head>
<body class="bg-gray-50 text-gray-900">
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header/Navbar -->
      <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="flex items-center justify-between px-6 py-3">
          <!-- Espaço vazio à esquerda -->
          <div></div>
          
          <div class="flex items-center gap-4">
            <!-- Ícone de Suporte (Admin e Super Admin) -->
            <?php if (isAdmin()): ?>
            <?php 
              // Contar solicitações pendentes APENAS para Super Admin
              $suportePendentes = 0;
              if (isSuperAdmin()) {
                $suportePendentes = \App\Controllers\SuporteController::contarPendentes();
              }
            ?>
            <a href="/suporte" class="relative group">
              <button class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-full transition-all duration-200" title="Suporte Técnico">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"></path>
                </svg>
                <!-- Badge com contador (APENAS Super Admin) -->
                <?php if (isSuperAdmin() && $suportePendentes > 0): ?>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full min-w-[20px] h-5 flex items-center justify-center px-1">
                  <?= $suportePendentes ?>
                </span>
                <?php endif; ?>
              </button>
              <div class="absolute right-0 mt-2 px-3 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                🆘 Suporte <?php if (isSuperAdmin() && $suportePendentes > 0): ?>(<?= $suportePendentes ?> pendente<?= $suportePendentes > 1 ? 's' : '' ?>)<?php endif; ?>
              </div>
            </a>
            <?php endif; ?>
            
            <!-- Ícone de Notificações -->
            <button class="relative p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-full transition-all duration-200" title="Notificações">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
              </svg>
              <!-- Badge de notificações -->
              <!-- <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span> -->
            </button>
            
            <!-- User Menu -->
            <div class="flex items-center gap-2 px-3 py-1 bg-gray-100 rounded-full">
              <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-semibold text-sm">
                <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
              </div>
              <span class="text-sm font-medium text-gray-700"><?= $_SESSION['user_name'] ?? 'Usuário' ?></span>
            </div>
          </div>
        </div>
      </header>
      
      <!-- Content -->
      <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
        <!-- Aviso de migração de email removido - Resend API ativo -->
        
        <?php if ($msg = flash('success')): ?>
          <div class="mb-4 rounded-md border border-green-200 bg-green-50 text-green-800 px-4 py-2 text-sm"><?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
          <div class="mb-4 rounded-md border border-red-200 bg-red-50 text-red-800 px-4 py-2 text-sm"><?= e($msg) ?></div>
        <?php endif; ?>
        <div class="page-transition">
          <?php include $viewFile; ?>
        </div>
      </main>
    </div>
  </div>

  <!-- Container para modais globais -->
  <div id="global-modals-container"></div>

  <!-- Chat virtual global -->
  <?php if (isset($_SESSION['user_id'])): ?>
  <div id="chat-widget" class="chat-widget">
    <button id="chat-toggle" class="chat-toggle" type="button">
      <span>Chat</span>
      <span id="chat-unread-badge" class="chat-unread-badge hidden">0</span>
    </button>

    <div id="chat-panel" class="chat-panel hidden">
      <div class="chat-header">
        <strong>Chat interno</strong>
        <span class="chat-subtitle">Usuários online/offline • chat em tempo real</span>
      </div>

      <div class="chat-retention-warning">
        As conversas deste chat são armazenadas em JSON e apagadas automaticamente a cada 30 dias.
      </div>

      <div class="chat-body">
        <aside class="chat-contacts">
          <input id="chat-search" type="text" placeholder="Buscar usuário..." class="chat-search-input">
          <div id="chat-contacts-list" class="chat-contacts-list"></div>
        </aside>

        <section class="chat-conversation">
          <div id="chat-empty" class="chat-empty">Selecione um usuário para conversar.</div>
          <div id="chat-conversation-header" class="chat-conversation-header hidden"></div>
          <div id="chat-messages" class="chat-messages hidden"></div>
          <form id="chat-form" class="chat-form hidden">
            <button id="chat-emoji-toggle" class="chat-emoji-toggle" type="button" title="Emojis">😊</button>
            <div id="chat-emoji-picker" class="chat-emoji-picker hidden">
              <button type="button" data-emoji="😀">😀</button>
              <button type="button" data-emoji="😂">😂</button>
              <button type="button" data-emoji="😉">😉</button>
              <button type="button" data-emoji="😍">😍</button>
              <button type="button" data-emoji="👍">👍</button>
              <button type="button" data-emoji="🙏">🙏</button>
              <button type="button" data-emoji="🎉">🎉</button>
              <button type="button" data-emoji="✅">✅</button>
              <button type="button" data-emoji="⚠️">⚠️</button>
              <button type="button" data-emoji="❤️">❤️</button>
            </div>
            <input id="chat-message-input" type="text" maxlength="2000" placeholder="Digite sua mensagem..." autocomplete="off">
            <button type="submit">Enviar</button>
          </form>
        </section>
      </div>
    </div>
  </div>

  <style>
    .chat-widget { position: fixed; right: 20px; bottom: 20px; z-index: 1200; font-family: inherit; }
    .chat-toggle { display: inline-flex; align-items: center; gap: 8px; border: 0; border-radius: 999px; padding: 8px 13px; background: #25d366; color: #073b1d; font-size: 13px; font-weight: 700; cursor: pointer; box-shadow: 0 8px 24px rgba(17, 24, 39, 0.28); }
    .chat-unread-badge { min-width: 18px; height: 18px; padding: 0 6px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #ef4444; display: inline-flex; align-items: center; justify-content: center; }
    .chat-unread-badge.hidden { display: none; }
    .chat-panel { width: min(760px, calc(100vw - 28px)); height: 500px; background: #efeae2; border: 1px solid #d1d5db; border-radius: 14px; margin-top: 10px; box-shadow: 0 20px 55px rgba(15, 23, 42, 0.2); overflow: hidden; display: flex; flex-direction: column; }
    .chat-panel.hidden { display: none; }
    .chat-header { padding: 12px 14px; border-bottom: 1px solid #1f7760; display: flex; flex-direction: column; background: #075e54; color: #ffffff; }
    .chat-subtitle { font-size: 12px; color: #c7efe4; margin-top: 2px; }
    .chat-retention-warning { padding: 8px 12px; border-bottom: 1px solid #fde68a; background: #fffbeb; color: #92400e; font-size: 12px; }
    .chat-body { display: grid; grid-template-columns: 260px 1fr; flex: 1; min-height: 0; }
    .chat-contacts { border-right: 1px solid #d4d4d8; display: flex; flex-direction: column; background: #f0f2f5; min-height: 0; }
    .chat-search-input { margin: 10px; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 13px; background: #ffffff; }
    .chat-contacts-list { flex: 1; min-height: 0; overflow-y: auto; overflow-x: hidden; padding: 0 8px 10px; }
    .chat-contacts-list::-webkit-scrollbar { width: 7px; }
    .chat-contacts-list::-webkit-scrollbar-thumb { background: #b6c0ce; border-radius: 999px; }
    .chat-contact-item { width: 100%; border: 0; text-align: left; padding: 10px; border-radius: 10px; margin-bottom: 6px; background: transparent; cursor: pointer; }
    .chat-contact-item:hover, .chat-contact-item.active { background: #d9fdd3; }
    .chat-contact-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .chat-contact-person { display: inline-flex; align-items: center; gap: 8px; min-width: 0; }
    .chat-contact-avatar { width: 26px; height: 26px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #fff; background: #334155; overflow: hidden; flex-shrink: 0; }
    .chat-contact-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .chat-contact-name { font-size: 13px; color: #111827; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .chat-contact-status { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; color: #64748b; }
    .chat-status-dot { width: 8px; height: 8px; border-radius: 999px; background: #94a3b8; }
    .chat-status-dot.online { background: #22c55e; }
    .chat-unread { background: #ef4444; color: #fff; border-radius: 999px; font-size: 10px; padding: 2px 6px; font-weight: 700; }
    .chat-conversation { display: flex; flex-direction: column; min-width: 0; min-height: 0; }
    .chat-empty { margin: auto; color: #6b7280; font-size: 13px; }
    .chat-conversation-header { padding: 10px 14px; border-bottom: 1px solid #d4d4d8; font-size: 13px; font-weight: 600; color: #1f2937; background: #f0f2f5; }
    .chat-conversation-header.hidden { display: none; }
    .chat-conversation-title { display: inline-flex; align-items: center; gap: 8px; }
    .chat-conversation-sub { color: #64748b; font-weight: 500; font-size: 11px; }
    .chat-messages { flex: 1; min-height: 0; overflow: auto; padding: 12px; background:
      radial-gradient(circle at 25px 25px, rgba(255,255,255,0.28) 2px, transparent 0) 0 0/50px 50px,
      linear-gradient(#e7ddd4, #efeae2); }
    .chat-messages.hidden { display: none; }
    .chat-message-row { display: flex; align-items: flex-end; gap: 6px; margin-bottom: 8px; }
    .chat-message-row.me { justify-content: flex-end; }
    .chat-message-row.other { justify-content: flex-start; }
    .chat-message-avatar { width: 24px; height: 24px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: #fff; background: #64748b; overflow: hidden; flex-shrink: 0; }
    .chat-message-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .chat-message { display: inline-block; width: auto; max-width: 40%; padding: 4px 7px; border-radius: 8px; font-size: 12px; line-height: 1.25; white-space: pre-wrap; word-break: break-word; box-shadow: 0 1px 0 rgba(0, 0, 0, 0.08); }
    .chat-message.me { background: #d9fdd3; color: #0f172a; }
    .chat-message.other { background: #ffffff; color: #111827; }
    .chat-typing { display: inline-flex; align-items: center; gap: 3px; min-height: 14px; }
    .chat-typing-dot { width: 5px; height: 5px; border-radius: 999px; background: #64748b; opacity: 0.28; animation: chatTypingBlink 1.1s infinite; }
    .chat-typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .chat-typing-dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes chatTypingBlink {
      0%, 80%, 100% { opacity: 0.25; transform: translateY(0); }
      40% { opacity: 1; transform: translateY(-1px); }
    }
    .chat-ai-panel { margin: 8px 0 12px 30px; max-width: 70%; background: #ffffff; border: 1px solid #dbe5ef; border-radius: 10px; padding: 8px; box-shadow: 0 1px 0 rgba(0, 0, 0, 0.08); }
    .chat-ai-panel-text { font-size: 12px; color: #334155; margin-bottom: 7px; line-height: 1.35; }
    .chat-ai-actions { display: flex; flex-wrap: wrap; gap: 6px; }
    .chat-ai-action-btn { border: 1px solid #cbd5e1; background: #f8fafc; color: #0f172a; border-radius: 999px; padding: 5px 10px; font-size: 11px; font-weight: 600; cursor: pointer; }
    .chat-ai-action-btn:hover { background: #e2e8f0; }
    .chat-triagem-report { white-space: pre-wrap; word-break: break-word; font-size: 11px; line-height: 1.45; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px; margin: 4px 0 6px; max-height: 260px; overflow-y: auto; font-family: 'Segoe UI', sans-serif; }
    .chat-print-btn { margin-top: 4px; }
    @media print {
      body * { visibility: hidden !important; }
      #chat-print-area, #chat-print-area * { visibility: visible !important; }
      #chat-print-area { position: fixed; left: 0; top: 0; width: 100%; padding: 20px; font-size: 12px; white-space: pre-wrap; font-family: 'Segoe UI', sans-serif; }
    }
    .chat-message-meta { margin-top: 4px; font-size: 10px; opacity: 0.8; display: inline-flex; align-items: center; gap: 4px; }
    .chat-read-status { font-size: 11px; letter-spacing: -1px; }
    .chat-read-status.sent { color: #64748b; }
    .chat-read-status.read { color: #2563eb; }
    .chat-form { position: relative; display: flex; flex-shrink: 0; gap: 8px; padding: 8px 10px; border-top: 1px solid #d4d4d8; background: #f0f2f5; }
    .chat-form.hidden { display: none; }
    .chat-emoji-toggle { border: 1px solid #d1d5db; background: #fff; color: #374151; border-radius: 10px; width: 36px; min-width: 36px; height: 36px; cursor: pointer; }
    .chat-emoji-picker { position: absolute; bottom: 54px; left: 10px; z-index: 10; background: #fff; border: 1px solid #d1d5db; border-radius: 10px; padding: 6px; display: grid; grid-template-columns: repeat(5, 1fr); gap: 4px; box-shadow: 0 8px 18px rgba(2, 6, 23, 0.15); }
    .chat-emoji-picker.hidden { display: none; }
    .chat-emoji-picker button { border: 0; background: #fff; font-size: 16px; line-height: 1; width: 28px; height: 28px; border-radius: 6px; cursor: pointer; }
    .chat-emoji-picker button:hover { background: #f1f5f9; }
    .chat-form input { flex: 1; border: 1px solid #d1d5db; border-radius: 999px; padding: 8px 12px; font-size: 13px; background: #ffffff; }
    .chat-form button[type="submit"] { border: 0; background: #25d366; color: #073b1d; border-radius: 999px; padding: 8px 14px; font-size: 13px; font-weight: 700; cursor: pointer; }
    .chat-form button[type="submit"]:disabled { opacity: 0.5; cursor: not-allowed; }
    @media (max-width: 900px) {
      .chat-panel { width: min(96vw, 560px); height: 78vh; }
      .chat-body { grid-template-columns: 1fr; }
      .chat-contacts { max-height: 40%; border-right: 0; border-bottom: 1px solid #e5e7eb; }
    }
  </style>

  <script>
    (function() {
      const meId = <?= (int)($_SESSION['user_id'] ?? 0) ?>;
      const meName = <?= json_encode((string)($_SESSION['user_name'] ?? 'Você')) ?>;
      if (!meId) return;

      let contacts = [];
      let activeMode = 'direct';
      let activeContactId = null;
      let lastGlobalSeenId = 0;
      const lastDirectSeenByUser = {};
      let pollTimer = null;
      let heartbeatTimer = null;
      let isChatOpen = false;
      let isAppVisible = true;
      let isAiTyping = false;
      let aiFlowStep = 'menu';
      let hasEduardoWelcomed = false;
      const aiTicketDraft = { module: '', problem: '' };
      const aiTriagemDraft = { clienteCodigo: '', dias: 0 };
      let pollBackoffLevel = 0;
      let lastUnreadTotal = 0;

      const POLL_INTERVALS_MS = [15000, 30000, 60000];
      const DEFAULT_INPUT_PLACEHOLDER = 'Digite sua mensagem...';

      const ui = {};

      function q(id) { return document.getElementById(id); }
      function getInitial(name) {
        return (String(name || '?').trim().charAt(0) || '?').toUpperCase();
      }

      function avatarHtml(userId, name, hasPhoto, className, avatarUrl) {
        const cls = className || 'chat-contact-avatar';
        if (avatarUrl) {
          return `<span class="${cls}"><img src="${escapeHtml(avatarUrl)}" alt="${escapeHtml(name)}"></span>`;
        }
        if (Number(hasPhoto) === 1) {
          return `<span class="${cls}"><img src="/profile/photo/${userId}" alt="${escapeHtml(name)}"></span>`;
        }
        return `<span class="${cls}">${escapeHtml(getInitial(name))}</span>`;
      }

      function playMessageSound(kind) {
        try {
          const AudioCtx = window.AudioContext || window.webkitAudioContext;
          if (!AudioCtx) return;
          const ctx = new AudioCtx();
          const osc = ctx.createOscillator();
          const gain = ctx.createGain();
          osc.connect(gain);
          gain.connect(ctx.destination);
          osc.type = 'sine';
          osc.frequency.value = kind === 'send' ? 760 : 560;
          gain.gain.setValueAtTime(0.0001, ctx.currentTime);
          gain.gain.exponentialRampToValueAtTime(0.06, ctx.currentTime + 0.01);
          gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.13);
          osc.start();
          osc.stop(ctx.currentTime + 0.14);
        } catch (_) {}
      }

      function escapeHtml(value) {
        return String(value)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/\"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }

      function getSelectedContact() {
        return contacts.find(c => String(c.id) === String(activeContactId)) || null;
      }

      function isAiConversation() {
        const selected = getSelectedContact();
        return !!(selected && Number(selected.is_ai) === 1);
      }

      function getEduardoWelcomeKey() {
        return `chat_eduardo_welcome_${meId}`;
      }

      function loadEduardoWelcomeState() {
        try {
          hasEduardoWelcomed = localStorage.getItem(getEduardoWelcomeKey()) === '1';
        } catch (_) {
          hasEduardoWelcomed = false;
        }
      }

      function markEduardoWelcomed() {
        hasEduardoWelcomed = true;
        try {
          localStorage.setItem(getEduardoWelcomeKey(), '1');
        } catch (_) {}
      }

      function resetAiFlow() {
        aiFlowStep = 'menu';
        aiTicketDraft.module = '';
        aiTicketDraft.problem = '';
        aiTriagemDraft.clienteCodigo = '';
        aiTriagemDraft.dias = 0;
      }

      function updateInputPlaceholder() {
        if (!ui.messageInput) return;
        if (isAiConversation() && aiFlowStep === 'ticket_problem' && aiTicketDraft.module) {
          ui.messageInput.placeholder = `Descreva o problema no módulo ${aiTicketDraft.module}...`;
        } else if (isAiConversation() && aiFlowStep === 'triagem_code') {
          ui.messageInput.placeholder = 'Digite o código ou nome do cliente...';
        } else if (isAiConversation() && aiFlowStep === 'qa') {
          ui.messageInput.placeholder = 'Digite sua pergunta para o Eduardo...';
        } else {
          ui.messageInput.placeholder = DEFAULT_INPUT_PLACEHOLDER;
        }
      }

      function aiQuickPanelHtml() {
        if (!isAiConversation()) return '';

        let text = '';
        let actions = [];

        if (aiFlowStep === 'ticket_module') {
          text = 'Qual módulo está com problema?';
          actions = [
            { action: 'ticket-module', label: 'Triagem de Toners', value: 'Triagem de Toners' },
            { action: 'ticket-module', label: 'Cadastro de Defeitos', value: 'Cadastro de Defeitos' },
            { action: 'ticket-module', label: 'Toners com Defeito', value: 'Toners com Defeito' },
            { action: 'ticket-module', label: 'Outro módulo', value: 'Outro módulo' },
            { action: 'back-menu', label: 'Voltar' }
          ];
        } else if (aiFlowStep === 'ticket_problem') {
          text = `Me conta o problema no módulo ${escapeHtml(aiTicketDraft.module)}. Vou abrir o chamado assim que você enviar.`;
          actions = [
            { action: 'cancel-ticket', label: 'Cancelar chamado' },
            { action: 'back-menu', label: 'Voltar ao menu' }
          ];
        } else if (aiFlowStep === 'triagem_code') {
          text = 'Digite o código ou nome do cliente para eu consultar a triagem.';
          actions = [
            { action: 'back-menu', label: 'Voltar' }
          ];
        } else if (aiFlowStep === 'triagem_period') {
          text = `Cliente: ${escapeHtml(aiTriagemDraft.clienteCodigo)}. Qual período deseja consultar?`;
          actions = [
            { action: 'triagem-days', label: '30 dias', value: '30' },
            { action: 'triagem-days', label: '60 dias', value: '60' },
            { action: 'triagem-days', label: '90 dias', value: '90' },
            { action: 'back-menu', label: 'Cancelar' }
          ];
        } else if (aiFlowStep === 'qa') {
          text = 'Manda sua pergunta que eu pesquiso e respondo de forma direta.';
          actions = [
            { action: 'back-menu', label: 'Menu principal' }
          ];
        } else {
          text = hasEduardoWelcomed
            ? 'Escolha uma opção:'
            : 'Olá, meu nome é Eduardo e estou aqui para ajudar com algumas coisas. Escolha uma opção:';
          actions = [
            { action: 'start-ticket', label: 'Abrir chamado' },
            { action: 'start-triagem', label: 'Consultar triagem de cliente' },
            { action: 'start-qa', label: 'Responder pergunta' }
          ];
        }

        const actionsHtml = actions.map(item => {
          const extra = item.value ? ` data-ai-value="${escapeHtml(item.value)}"` : '';
          return `<button type="button" class="chat-ai-action-btn" data-ai-action="${item.action}"${extra}>${escapeHtml(item.label)}</button>`;
        }).join('');

        return `
          <div class="chat-ai-panel">
            <div class="chat-ai-panel-text">${text}</div>
            <div class="chat-ai-actions">${actionsHtml}</div>
          </div>
        `;
      }

      function typingIndicatorHtml(selectedContact) {
        if (!selectedContact) return '';
        return `
          <div id="chat-typing-indicator" class="chat-message-row other">
            ${avatarHtml(selectedContact.id, selectedContact.name, selectedContact.has_photo, 'chat-message-avatar', selectedContact.avatar_url)}
            <div class="chat-message other">
              <div class="chat-typing" aria-label="Eduardo está digitando">
                <span class="chat-typing-dot"></span>
                <span class="chat-typing-dot"></span>
                <span class="chat-typing-dot"></span>
              </div>
            </div>
          </div>
        `;
      }

      function fmtDate(iso) {
        const d = new Date(iso.replace(' ', 'T'));
        if (Number.isNaN(d.getTime())) return '';
        return d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
      }

      async function fetchJson(url, options) {
        const response = await fetch(url, options || {});
        return response.json();
      }

      async function heartbeat() {
        try {
          await fetchJson('/api/chat/heartbeat', { method: 'POST' });
        } catch (_) {}
      }

      function stopChatPolling() {
        if (pollTimer) {
          clearTimeout(pollTimer);
          pollTimer = null;
        }
        if (heartbeatTimer) {
          clearInterval(heartbeatTimer);
          heartbeatTimer = null;
        }
      }

      function registerChatActivity() {
        pollBackoffLevel = 0;
      }

      function getPollIntervalMs() {
        const idx = Math.min(pollBackoffLevel, POLL_INTERVALS_MS.length - 1);
        return POLL_INTERVALS_MS[idx];
      }

      function scheduleNextPoll() {
        if (!isChatOpen || !isAppVisible) return;

        if (pollTimer) {
          clearTimeout(pollTimer);
          pollTimer = null;
        }

        pollTimer = setTimeout(runPollCycle, getPollIntervalMs());
      }

      async function runPollCycle() {
        if (!isChatOpen || !isAppVisible) return;

        await loadContacts();
        if (activeContactId) {
          await loadMessages();
        }

        if (pollBackoffLevel < POLL_INTERVALS_MS.length - 1) {
          pollBackoffLevel += 1;
        }

        scheduleNextPoll();
      }

      async function startChatPolling() {
        if (!isAppVisible) return;
        stopChatPolling();
        registerChatActivity();

        await heartbeat();
        await loadContacts();
        if (!activeContactId) {
          const daniel = contacts.find(c => Number(c.is_ai) === 1);
          const fallback = contacts[0] || null;
          const initial = daniel || fallback;
          if (initial) {
            activeContactId = String(initial.id);
          }
        }

        if (activeContactId) {
          await loadMessages();
        }

        scheduleNextPoll();

        heartbeatTimer = setInterval(function() {
          if (!isChatOpen || !isAppVisible) return;
          heartbeat();
        }, 120000);
      }

      function syncPollingWithVisibility() {
        if (isChatOpen && isAppVisible) {
          startChatPolling();
        } else {
          stopChatPolling();
        }
      }

      async function loadContacts() {
        const searchValue = (ui.search.value || '').trim().toLowerCase();
        try {
          const data = await fetchJson('/api/chat/contacts');
          if (!data.success) return;
          contacts = data.contacts || [];

          const filtered = contacts.filter(c =>
            c.name.toLowerCase().includes(searchValue) ||
            c.email.toLowerCase().includes(searchValue)
          );

          ui.contactsList.innerHTML = (filtered.map(c => {
            const isActive = String(c.id) === String(activeContactId);
            const online = Number(c.is_online) === 1;
            const unread = Number(c.unread_count || 0);
            return `
              <button class="chat-contact-item ${isActive ? 'active' : ''}" data-user-id="${c.id}">
                <div class="chat-contact-top">
                  <span class="chat-contact-person">
                    ${avatarHtml(c.id, c.name, c.has_photo, 'chat-contact-avatar', c.avatar_url)}
                    <span class="chat-contact-name">${escapeHtml(c.name)}</span>
                  </span>
                  ${unread > 0 ? `<span class="chat-unread">${unread}</span>` : ''}
                </div>
                <div class="chat-contact-status">
                  <span class="chat-status-dot ${online ? 'online' : ''}"></span>
                  ${online ? 'Online' : 'Offline'}
                </div>
              </button>
            `;
          }).join('') || '<div style="padding:10px;color:#64748b;font-size:12px;">Nenhum usuário encontrado.</div>');

          const totalUnread = contacts.reduce((sum, c) => sum + Number(c.unread_count || 0), 0);
          if (totalUnread !== lastUnreadTotal) {
            lastUnreadTotal = totalUnread;
            registerChatActivity();
          }
          if (totalUnread > 0) {
            ui.badge.textContent = totalUnread > 99 ? '99+' : String(totalUnread);
            ui.badge.classList.remove('hidden');
          } else {
            ui.badge.classList.add('hidden');
          }
        } catch (_) {}
      }

      async function loadMessages() {
        if (!activeContactId) return;
        try {
          const data = await fetchJson(`/api/chat/messages/${activeContactId}`);
          if (!data.success) return;
          const messages = data.messages || [];

          const prevSeen = Number(lastDirectSeenByUser[activeContactId] || 0);
          const maxId = messages.reduce((max, item) => Math.max(max, Number(item.id || 0)), 0);
          if (prevSeen > 0) {
            const hasIncomingNew = messages.some(m => Number(m.id) > prevSeen && Number(m.sender_id) !== meId);
            if (hasIncomingNew) {
              playMessageSound('receive');
              registerChatActivity();
            }
          }
          if (maxId > 0) lastDirectSeenByUser[activeContactId] = Math.max(prevSeen, maxId);

          const selectedContact = getSelectedContact();
          ui.empty.classList.add('hidden');
          ui.convHeader.classList.remove('hidden');
          if (selectedContact) {
            ui.convHeader.innerHTML = `<span class="chat-conversation-title">${avatarHtml(selectedContact.id, selectedContact.name, selectedContact.has_photo, 'chat-contact-avatar', selectedContact.avatar_url)}<span>${escapeHtml(selectedContact.name)}</span></span> <span class="chat-conversation-sub">(${Number(selectedContact.is_online) === 1 ? 'Online' : 'Offline'})</span>`;
          } else {
            ui.convHeader.textContent = 'Conversa';
          }
          ui.messages.classList.remove('hidden');
          ui.form.classList.remove('hidden');

          ui.messages.innerHTML = messages.map(m => {
            const mine = Number(m.sender_id) === meId;
            const readClass = m.read_at ? 'read' : 'sent';
            const readLabel = mine ? `<span class="chat-read-status ${readClass}">${m.read_at ? '✓✓' : '✓'}</span>` : '';
            const msgText = m.message || '';
            const isTriagemResult = !mine && msgText.startsWith('__TRIAGEM_RESULT__|');
            let renderedContent = '';
            let printBtn = '';
            if (isTriagemResult) {
              const trParts = msgText.split('|');
              const trBody = trParts.slice(2).join('|');
              renderedContent = `<pre class="chat-triagem-report">${escapeHtml(trBody)}</pre>`;
              printBtn = `<button type="button" class="chat-ai-action-btn chat-print-btn" onclick="printTriagemReport(this)">🖨️ Imprimir resultado</button>`;
            } else {
              renderedContent = `<div>${escapeHtml(msgText)}</div>`;
            }
            return `
              <div class="chat-message-row ${mine ? 'me' : 'other'}">
                ${mine ? '' : (selectedContact ? avatarHtml(selectedContact.id, selectedContact.name, selectedContact.has_photo, 'chat-message-avatar', selectedContact.avatar_url) : '')}
                <div class="chat-message ${mine ? 'me' : 'other'}">
                  ${renderedContent}
                  ${printBtn}
                  <div class="chat-message-meta">${fmtDate(m.created_at)} ${readLabel}</div>
                </div>
              </div>
            `;
          }).join('')
            + (isAiTyping && isAiConversation() ? typingIndicatorHtml(selectedContact) : '')
            + aiQuickPanelHtml();

          ui.messages.scrollTop = ui.messages.scrollHeight;
          updateInputPlaceholder();
        } catch (_) {}
      }

      async function sendMessage(event) {
        event.preventDefault();
        const text = ui.messageInput.value.trim();
        if (!text) return;

        const payload = new URLSearchParams();
        const endpoint = '/api/chat/send';
        if (!activeContactId) return;
        const isAiChat = isAiConversation();
        payload.set('receiver_id', activeContactId);

        let finalText = text;
        if (isAiChat && aiFlowStep === 'ticket_problem' && aiTicketDraft.module) {
          aiTicketDraft.problem = text;
          finalText = `__OPEN_TICKET__|${aiTicketDraft.module}|${aiTicketDraft.problem}`;
        } else if (isAiChat && aiFlowStep === 'triagem_code') {
          aiTriagemDraft.clienteCodigo = text;
          aiFlowStep = 'triagem_period';
          ui.messageInput.value = '';
          updateInputPlaceholder();
          await loadMessages();
          return;
        }

        payload.set('message', finalText);

        if (isAiChat) {
          markEduardoWelcomed();
          isAiTyping = true;
          await loadMessages();
        }

        try {
          const data = await fetchJson(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: payload.toString()
          });

          if (!data.success) {
            alert(data.message || 'Erro ao enviar mensagem');
            return;
          }

          ui.messageInput.value = '';
          playMessageSound('send');
          registerChatActivity();
          if (isAiChat && (aiFlowStep === 'ticket_problem' || aiFlowStep === 'triagem_waiting')) {
            resetAiFlow();
          }
          isAiTyping = false;
          await loadMessages();
          await loadContacts();
        } catch (_) {
          isAiTyping = false;
          await loadMessages();
          alert('Erro ao enviar mensagem');
        }
      }

      async function sendTriagemQuery(triagemMsg) {
        markEduardoWelcomed();
        isAiTyping = true;
        await loadMessages();

        const payload = new URLSearchParams();
        payload.set('receiver_id', activeContactId);
        payload.set('message', triagemMsg);

        try {
          const data = await fetchJson('/api/chat/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: payload.toString()
          });
          if (!data.success) {
            alert(data.message || 'Erro ao consultar triagem');
          } else {
            playMessageSound('send');
            registerChatActivity();
          }
        } catch (_) {
          alert('Erro ao consultar triagem');
        }
        resetAiFlow();
        isAiTyping = false;
        await loadMessages();
        await loadContacts();
      }

      function selectContact(contactId) {
        activeMode = 'direct';
        activeContactId = String(contactId);
        isAiTyping = false;
        if (isAiConversation()) {
          loadEduardoWelcomeState();
          resetAiFlow();
        } else {
          aiFlowStep = 'menu';
        }
        registerChatActivity();
        loadContacts().then(loadMessages);
      }

      function bindEvents() {
        document.addEventListener('visibilitychange', function() {
          isAppVisible = !document.hidden;
          syncPollingWithVisibility();
        });

        window.addEventListener('blur', function() {
          isAppVisible = false;
          syncPollingWithVisibility();
        });

        window.addEventListener('focus', function() {
          isAppVisible = !document.hidden;
          syncPollingWithVisibility();
        });

        ui.toggle.addEventListener('click', async function() {
          ui.panel.classList.toggle('hidden');
          if (!ui.panel.classList.contains('hidden')) {
            isChatOpen = true;
            await syncPollingWithVisibility();
          } else {
            isChatOpen = false;
            stopChatPolling();
          }
        });

        ui.search.addEventListener('input', function() {
          registerChatActivity();
          loadContacts();
        });
        ui.emojiToggle.addEventListener('click', function() {
          registerChatActivity();
          ui.emojiPicker.classList.toggle('hidden');
        });

        ui.emojiPicker.addEventListener('click', function(event) {
          const btn = event.target.closest('button[data-emoji]');
          if (!btn) return;
          registerChatActivity();
          ui.messageInput.value += btn.getAttribute('data-emoji');
          ui.messageInput.focus();
        });

        ui.messageInput.addEventListener('input', registerChatActivity);

        document.addEventListener('click', function(event) {
          if (!ui.form.contains(event.target) || event.target === ui.messageInput) {
            if (event.target !== ui.emojiToggle && !ui.emojiPicker.contains(event.target)) {
              ui.emojiPicker.classList.add('hidden');
            }
          }
        });

        ui.contactsList.addEventListener('click', function(event) {
          const btn = event.target.closest('.chat-contact-item');
          if (!btn) return;
          selectContact(btn.getAttribute('data-user-id'));
        });

        ui.messages.addEventListener('click', function(event) {
          const btn = event.target.closest('[data-ai-action]');
          if (!btn || !isAiConversation()) return;

          const action = btn.getAttribute('data-ai-action');
          const value = btn.getAttribute('data-ai-value') || '';

          markEduardoWelcomed();

          if (action === 'start-ticket') {
            aiFlowStep = 'ticket_module';
          } else if (action === 'start-triagem') {
            aiFlowStep = 'triagem_code';
            ui.messageInput.focus();
          } else if (action === 'start-qa') {
            aiFlowStep = 'qa';
            ui.messageInput.focus();
          } else if (action === 'ticket-module') {
            aiTicketDraft.module = value || 'Módulo não informado';
            aiFlowStep = 'ticket_problem';
            ui.messageInput.focus();
          } else if (action === 'triagem-days') {
            aiTriagemDraft.dias = parseInt(value) || 30;
            aiFlowStep = 'triagem_waiting';
            updateInputPlaceholder();
            loadMessages();
            const triagemMsg = `__TRIAGEM_QUERY__|${aiTriagemDraft.clienteCodigo}|${aiTriagemDraft.dias}`;
            sendTriagemQuery(triagemMsg);
            return;
          } else if (action === 'cancel-ticket') {
            resetAiFlow();
          } else if (action === 'back-menu') {
            resetAiFlow();
          }

          updateInputPlaceholder();
          loadMessages();
        });

        ui.form.addEventListener('submit', sendMessage);
      }

      async function init() {
        ui.toggle = q('chat-toggle');
        ui.panel = q('chat-panel');
        ui.badge = q('chat-unread-badge');
        ui.search = q('chat-search');
        ui.contactsList = q('chat-contacts-list');
        ui.empty = q('chat-empty');
        ui.convHeader = q('chat-conversation-header');
        ui.messages = q('chat-messages');
        ui.form = q('chat-form');
        ui.messageInput = q('chat-message-input');
        ui.emojiToggle = q('chat-emoji-toggle');
        ui.emojiPicker = q('chat-emoji-picker');

        if (!ui.toggle || !ui.panel) return;

        loadEduardoWelcomeState();
        updateInputPlaceholder();

        bindEvents();
        await loadContacts();
        if (!activeContactId) {
          const daniel = contacts.find(c => Number(c.is_ai) === 1);
          const fallback = contacts[0] || null;
          const initial = daniel || fallback;
          if (initial) {
            activeContactId = String(initial.id);
          }
        }

        window.addEventListener('beforeunload', function() {
          stopChatPolling();
        });
      }

      document.addEventListener('DOMContentLoaded', init);
    })();

    function printTriagemReport(btn) {
      const report = btn.closest('.chat-message').querySelector('.chat-triagem-report');
      if (!report) return;
      let printArea = document.getElementById('chat-print-area');
      if (!printArea) {
        printArea = document.createElement('div');
        printArea.id = 'chat-print-area';
        document.body.appendChild(printArea);
      }
      printArea.innerHTML = '<h2 style="margin-bottom:12px;font-size:16px;">Relatório de Triagem - Eduardo do Suporte</h2>'
        + '<pre style="white-space:pre-wrap;font-size:12px;line-height:1.5;font-family:Segoe UI,sans-serif;">'
        + report.textContent + '</pre>';
      window.print();
      setTimeout(function() { printArea.innerHTML = ''; }, 1000);
    }
  </script>
  <?php endif; ?>

  <!-- Loading overlay removido - causava problemas em todos os módulos -->

  <script>
    // Page transition and smooth navigation
    document.addEventListener('DOMContentLoaded', function() {
      // Add loaded class for initial page load
      const pageContent = document.querySelector('.page-transition');
      if (pageContent) {
        setTimeout(() => pageContent.classList.add('loaded'), 100);
      }

      // Navegação simples sem loading global (removido para evitar problemas)
      // Cada módulo pode implementar seu próprio loading se necessário
    });
  </script>
  
  <!-- Debug Panel (só se debug estiver ativo) -->
  <?php 
  $showDebug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true' || isset($_GET['debug']);
  if ($showDebug): 
      include __DIR__ . '/../partials/debug-panel.php'; 
  endif; 
  ?>
</body>
</html>
