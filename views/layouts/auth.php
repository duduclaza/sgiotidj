<?php
$title = $title ?? 'SGQ - Login';
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title><?= e($title) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    * { font-family: 'Inter', sans-serif; box-sizing: border-box; }

    html, body { margin: 0; padding: 0; width: 100%; height: 100%; }

    body {
      background-color: #0a0c12;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      position: relative;
    }

    /* ─── Neural Canvas ─── */
    #neural-canvas {
      position: fixed;
      inset: 0;
      z-index: 0;
      pointer-events: none;
    }

    /* Grade geométrica sutil */
    .grid-overlay {
      position: fixed;
      inset: 0;
      background-image:
        linear-gradient(rgba(255,255,255,0.018) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.018) 1px, transparent 1px);
      background-size: 56px 56px;
      pointer-events: none;
      z-index: 1;
    }

    /* ─── Auth Card ─── */
    .auth-card {
      position: relative;
      z-index: 10;
      width: 100%;
      background: rgba(255,255,255,0.04);
      backdrop-filter: blur(28px);
      -webkit-backdrop-filter: blur(28px);
      border: 1px solid rgba(255,255,255,0.07);
      border-radius: 24px;
      padding: 40px 36px;
      box-shadow:
        0 0 0 1px rgba(255,255,255,0.04) inset,
        0 32px 80px rgba(0,0,0,0.5);
    }

    /* Linha de brilho no topo */
    .auth-card::before {
      content: '';
      position: absolute;
      top: 0; left: 15%; right: 15%;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(99,120,255,0.3), transparent);
    }

    /* ─── Inputs ─── */
    .auth-input {
      width: 100%;
      padding: 11px 14px 11px 42px;
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 12px;
      color: #e2e8f0;
      font-size: 14px;
      font-weight: 500;
      outline: none;
      transition: all 0.2s ease;
    }
    .auth-input::placeholder { color: rgba(148,163,184,0.45); }
    .auth-input:focus {
      background: rgba(255,255,255,0.08);
      border-color: rgba(99,120,255,0.4);
      box-shadow: 0 0 0 3px rgba(99,120,255,0.08);
    }
    /* Selects */
    .auth-input option { background: #1a1d2e; color: #e2e8f0; }

    /* ─── Botão principal ─── */
    .btn-auth {
      width: 100%;
      padding: 12px;
      background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
      border: none;
      border-radius: 12px;
      color: white;
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 0.01em;
      cursor: pointer;
      transition: all 0.2s ease;
      box-shadow: 0 4px 20px rgba(99,102,241,0.3);
      position: relative;
      overflow: hidden;
    }
    .btn-auth::after {
      content: '';
      position: absolute;
      top: 0; left: -100%;
      width: 100%; height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
      transition: left 0.5s ease;
    }
    .btn-auth:hover { transform: translateY(-1px); box-shadow: 0 6px 28px rgba(99,102,241,0.45); }
    .btn-auth:hover::after { left: 100%; }
    .btn-auth:active { transform: translateY(0); }
    .btn-auth.loading { opacity: 0.7; cursor: not-allowed; transform: none !important; }

    /* ─── Labels ─── */
    .auth-label {
      display: block;
      font-size: 11px;
      font-weight: 700;
      color: rgba(148,163,184,0.75);
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 6px;
    }

    /* ─── Logo badge ─── */
    .logo-badge {
      width: 48px; height: 48px;
      background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
      border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 8px 24px rgba(59,130,246,0.3);
      margin: 0 auto 20px;
    }

    /* ─── Status dot ─── */
    .status-dot {
      display: inline-block;
      width: 6px; height: 6px;
      background: #22c55e;
      border-radius: 50%;
      animation: pulse-green 2s ease-in-out infinite;
    }
    @keyframes pulse-green {
      0%, 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0.4); transform: scale(1); }
      50% { box-shadow: 0 0 0 4px rgba(34,197,94,0); transform: scale(1.1); }
    }

    /* ─── Textarea ─── */
    .auth-input.textarea-auth { height: 90px; resize: none; padding-left: 14px; padding-top: 10px; }

    /* ─── Scroll thin ─── */
    .auth-card-scroll { max-height: 90vh; overflow-y: auto; scrollbar-width: thin; scrollbar-color: rgba(99,102,241,0.3) transparent; }
  </style>
</head>
<body>

  <!-- Neural Particle Canvas -->
  <canvas id="neural-canvas"></canvas>
  <div class="grid-overlay"></div>

  <!-- Auth Card -->
  <div class="auth-card mx-4" style="max-width:420px; width:100%;">
    <div class="auth-card-scroll flex flex-col min-h-full">
      <div class="flex-grow">
        <?php include $viewFile; ?>
      </div>
    </div>
  </div>

  <!-- Branding inferior externo -->
  <div class="text-center mt-3 relative z-10 w-full max-w-[420px] mx-auto px-4 flex justify-center">
    <a href="https://www.tiuai.com.br" target="_blank" rel="noopener noreferrer"
       class="inline-flex items-center justify-center gap-1.5 text-[11px] text-slate-500 hover:text-slate-300 transition-colors group">
      <span class="text-slate-600 group-hover:text-slate-400 transition-colors">Desenvolvido por</span>
      <span class="font-bold text-slate-500 group-hover:text-slate-300 tracking-wide transition-colors">TI UAI</span>
      <svg class="w-3 h-3 text-slate-600 group-hover:text-slate-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
    </a>
  </div>


  <?php include __DIR__ . '/../partials/ui-feedback.php'; ?>
  <?php include __DIR__ . '/../partials/ui-scripts.php'; ?>

  <script>
  // ═══════════════════════════════════════════════════════
  //  NEURAL PARTICLE SYSTEM — Mouse Interactive
  // ═══════════════════════════════════════════════════════
  (function() {
    const canvas = document.getElementById('neural-canvas');
    const ctx = canvas.getContext('2d');

    let W, H, mouse = { x: -9999, y: -9999 };
    let particles = [];
    let animFrame;

    const CONFIG = {
      count: 80,
      connectDistance: 140,
      mouseRadius: 180,
      mouseForce: 0.06,
      speed: 0.35,
      nodeSize: { min: 1.5, max: 3.5 },
      colors: ['#6366f1','#3b82f6','#22d3ee','#818cf8','#a78bfa'],
    };

    function resize() {
      W = canvas.width  = window.innerWidth;
      H = canvas.height = window.innerHeight;
    }

    class Particle {
      constructor() { this.reset(true); }
      reset(init = false) {
        this.x  = Math.random() * W;
        this.y  = init ? Math.random() * H : (Math.random() < 0.5 ? -10 : H + 10);
        this.vx = (Math.random() - 0.5) * CONFIG.speed;
        this.vy = (Math.random() - 0.5) * CONFIG.speed;
        this.r  = CONFIG.nodeSize.min + Math.random() * (CONFIG.nodeSize.max - CONFIG.nodeSize.min);
        this.color = CONFIG.colors[Math.floor(Math.random() * CONFIG.colors.length)];
        this.alpha = 0.4 + Math.random() * 0.5;
        this.pulseOffset = Math.random() * Math.PI * 2;
      }
      update(t) {
        // Mouse attraction / repulsion
        const dx = mouse.x - this.x, dy = mouse.y - this.y;
        const dist = Math.sqrt(dx*dx + dy*dy);
        if (dist < CONFIG.mouseRadius && dist > 1) {
          const force = (1 - dist / CONFIG.mouseRadius) * CONFIG.mouseForce;
          this.vx += (dx / dist) * force;
          this.vy += (dy / dist) * force;
        }

        // Damping
        this.vx *= 0.985;
        this.vy *= 0.985;

        this.x += this.vx;
        this.y += this.vy;

        // Wrap edges
        if (this.x < -20) this.x = W + 20;
        if (this.x > W + 20) this.x = -20;
        if (this.y < -20) this.y = H + 20;
        if (this.y > H + 20) this.y = -20;

        // Pulse alpha
        this.currentAlpha = this.alpha * (0.7 + 0.3 * Math.sin(t * 0.002 + this.pulseOffset));
      }
      draw() {
        ctx.save();
        ctx.globalAlpha = this.currentAlpha;
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
        ctx.fillStyle = this.color;
        ctx.shadowColor = this.color;
        ctx.shadowBlur = 8;
        ctx.fill();
        ctx.restore();
      }
    }

    function init() {
      particles = Array.from({ length: CONFIG.count }, () => new Particle());
    }

    function drawConnections() {
      for (let i = 0; i < particles.length; i++) {
        for (let j = i + 1; j < particles.length; j++) {
          const a = particles[i], b = particles[j];
          const dx = a.x - b.x, dy = a.y - b.y;
          const dist = Math.sqrt(dx*dx + dy*dy);
          if (dist < CONFIG.connectDistance) {
            const opacity = (1 - dist / CONFIG.connectDistance) * 0.25;
            // Highlight connections near mouse
            const mx = (a.x + b.x) / 2, my = (a.y + b.y) / 2;
            const md = Math.sqrt((mx - mouse.x)**2 + (my - mouse.y)**2);
            const boost = md < CONFIG.mouseRadius ? (1 - md / CONFIG.mouseRadius) * 0.5 : 0;

            ctx.beginPath();
            ctx.moveTo(a.x, a.y);
            ctx.lineTo(b.x, b.y);
            const grad = ctx.createLinearGradient(a.x, a.y, b.x, b.y);
            grad.addColorStop(0, a.color);
            grad.addColorStop(1, b.color);
            ctx.strokeStyle = grad;
            ctx.globalAlpha = opacity + boost;
            ctx.lineWidth = 0.6 + boost;
            ctx.stroke();
          }
        }
      }
      ctx.globalAlpha = 1;
    }

    let t = 0;
    function loop() {
      t++;
      ctx.clearRect(0, 0, W, H);
      particles.forEach(p => p.update(t));
      drawConnections();
      particles.forEach(p => p.draw());
      animFrame = requestAnimationFrame(loop);
    }

    window.addEventListener('resize', () => { resize(); });
    window.addEventListener('mousemove', e => { mouse.x = e.clientX; mouse.y = e.clientY; });
    window.addEventListener('touchmove', e => {
      mouse.x = e.touches[0].clientX;
      mouse.y = e.touches[0].clientY;
    }, { passive: true });
    window.addEventListener('mouseleave', () => { mouse.x = -9999; mouse.y = -9999; });

    resize();
    init();
    loop();
  })();
  </script>

</body>
</html>
