<!-- Global UI Feedback Components -->
<div id="global-toast-stack" class="fixed top-4 right-4 z-[9999] flex flex-col gap-3 pointer-events-none w-full max-w-xs"></div>

<!-- Global Confirm Overlay -->
<div id="global-confirm-overlay" class="fixed inset-0 bg-black/50 z-[9998] flex items-center justify-center hidden p-4 animate-fade-in backdrop-blur-sm">
  <div id="global-confirm-box" class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all scale-95 opacity-0">
    <div class="p-8">
      <div class="w-16 h-16 bg-blue-50 dark:bg-blue-900/40 rounded-2xl flex items-center justify-center mb-6 mx-auto">
        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      <h3 id="global-confirm-title" class="text-xl font-bold text-gray-900 dark:text-white mb-2 text-center">Confirmar</h3>
      <p id="global-confirm-msg" class="text-gray-600 dark:text-gray-400 text-center mb-8"></p>
      
      <div class="flex gap-3">
        <button id="global-confirm-cancel" class="flex-1 px-4 py-3 text-sm font-bold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 rounded-2xl transition-all">Cancelar</button>
        <button id="global-confirm-ok" class="flex-1 px-4 py-3 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-2xl transition-all shadow-lg shadow-blue-500/20">Confirmar</button>
      </div>
    </div>
  </div>
</div>

<style>
  /* Toast animations */
  @keyframes toastIn {
    from { opacity:0; transform:translateX(100%); }
    to   { opacity:1; transform:translateX(0); }
  }
  @keyframes toastOut {
    from { opacity:1; transform:translateX(0); }
    to   { opacity:0; transform:translateX(110%); }
  }
  @keyframes fade-in {
    from { opacity:0; }
    to   { opacity:1; }
  }
  .toast-item { animation: toastIn .3s cubic-bezier(.16,1,.3,1) forwards; pointer-events:all; }
  .toast-item.removing { animation: toastOut .25s ease-in forwards; }
  .animate-fade-in { animation: fade-in 0.2s ease-out; }

  /* Button loading state */
  .btn-loading { position:relative; pointer-events:none; opacity:.85; }
  .btn-loading > * { opacity:0; }
  .btn-loading::after {
    content:'';
    position:absolute;
    width:20px; height:20px;
    top:50%; left:50%;
    margin:-10px 0 0 -10px;
    border:2.5px solid rgba(255,255,255,.3);
    border-top-color:#fff;
    border-radius:50%;
    animation:spin .8s linear infinite;
  }
  @keyframes spin { to { transform:rotate(360deg); } }
</style>
