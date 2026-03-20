<?php
$title = $title ?? 'OTI - Login';
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
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      margin: 0;
      padding: 0;
      overflow-y: auto;
    }
    .btn-primary {
      background: #2563eb;
      transition: all 0.3s ease;
    }
    .btn-primary:hover {
      background: #1d4ed8;
      transform: scale(1.02);
    }
    .typing-effect {
      border-right: 2px solid #6b7280;
      animation: blink 0.7s step-end infinite;
      display: inline-block;
      min-height: 1.5rem;
    }
    @keyframes blink {
      from, to { border-color: transparent; }
      50% { border-color: #6b7280; }
    }
    @media (max-width: 768px) {
      .right-panel {
        display: none;
      }
      .left-panel {
        min-height: 100vh;
      }
    }
    
    /* ===== TEMA DE NATAL ===== */
    
    /* Container da neve */
    .snow-container {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 9999;
      overflow: hidden;
    }
    
    /* Flocos de neve */
    .snowflake {
      position: absolute;
      top: -10px;
      color: white;
      font-size: 1em;
      text-shadow: 0 0 5px rgba(255,255,255,0.7);
      animation: fall linear infinite;
      opacity: 0.8;
    }
    
    @keyframes fall {
      0% {
        transform: translateY(-10px) rotate(0deg);
        opacity: 1;
      }
      100% {
        transform: translateY(100vh) rotate(360deg);
        opacity: 0.3;
      }
    }
    
    /* Papai Noel voando (da Direita para Esquerda) */
    .santa-container {
      position: fixed;
      top: 12%;
      z-index: 100;
      pointer-events: none;
      animation: fly-santa 25s linear infinite; /* Mais lento para ser mais majestoso */
    }
    
    @keyframes fly-santa {
      0% {
        left: 110%; /* Começa fora da tela na direita */
        top: 12%;
      }
      50% {
        top: 8%;
      }
      100% {
        left: -300px; /* Termina fora da tela na esquerda */
        top: 15%;
      }
    }
    
    .santa-sleigh {
      width: 200px; /* Aumentei um pouco pois PNG costuma ter margem */
      height: auto;
      filter: drop-shadow(0 5px 15px rgba(0,0,0,0.3));
    }
    
    /* Montanhas de fundo */
    .mountains-container {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 30%;
      z-index: 1;
      pointer-events: none;
    }
    
    .mountain {
      position: absolute;
      bottom: 0;
      width: 100%;
      height: 100%;
    }
    
    .mountain-back {
      fill: rgba(100, 116, 139, 0.3);
    }
    
    .mountain-front {
      fill: rgba(71, 85, 105, 0.4);
    }
    
    .mountain-snow {
      fill: rgba(255, 255, 255, 0.6);
    }
  </style>
</head>
<body class="min-h-screen">
  <div class="flex min-h-screen">
    <!-- Painel Esquerdo - Azul com efeito roxo/lilás -->
    <div class="left-panel w-full md:w-1/2 bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-800 flex items-center justify-center p-4 md:p-8" style="background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #6366f1 100%);">
      <div class="w-full max-w-md my-8">
        <?php include $viewFile; ?>
      </div>
    </div>
    
    <!-- Painel Direito - Clean Design -->
    <div class="right-panel hidden md:flex md:w-1/2 items-center justify-center p-12" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #f1f5f9 100%);">
      <div class="text-center flex flex-col items-center">
        <!-- Logo OTI com efeitos premium -->
        <div class="oti-logo-container">
          <h1 class="oti-logo">
            <span class="oti-letter" data-letter="S">S</span>
            <span class="oti-letter" data-letter="G">G</span>
            <span class="oti-letter" data-letter="I">I</span>
          </h1>
          <div class="oti-glow"></div>
          <div class="oti-reflection"></div>
        </div>
        
        <!-- Frase abaixo do logo -->
        <p class="text-lg text-gray-500 font-light tracking-wide typing-effect mt-8 block w-full" id="typingText"></p>
        
        <!-- Elemento decorativo animado -->
        <div class="mt-12 flex justify-center gap-3">
          <div class="dot-pulse" style="animation-delay: 0s;"></div>
          <div class="dot-pulse" style="animation-delay: 0.3s;"></div>
          <div class="dot-pulse" style="animation-delay: 0.6s;"></div>
        </div>
      </div>
      
      <style>
        /* ===== LOGO OTI - EFEITOS PREMIUM ===== */
        .oti-logo-container {
          position: relative;
          display: block;
          margin-bottom: 2rem;
          width: 100%;
        }
        
        .oti-logo {
          font-size: 8rem;
          font-weight: 900;
          letter-spacing: -0.02em;
          margin: 0;
          position: relative;
          z-index: 2;
          display: flex;
          justify-content: center;
          gap: 0.1em;
        }
        
        .oti-letter {
          display: inline-block;
          background: linear-gradient(135deg, #1e40af 0%, #3b82f6 25%, #60a5fa 50%, #3b82f6 75%, #1e40af 100%);
          background-size: 200% 200%;
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          background-clip: text;
          animation: gradient-shift 4s ease-in-out infinite;
          position: relative;
          text-shadow: none;
          filter: drop-shadow(0 4px 8px rgba(59, 130, 246, 0.3));
          transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .oti-letter:nth-child(1) { animation-delay: 0s; }
        .oti-letter:nth-child(2) { animation-delay: 0.15s; }
        .oti-letter:nth-child(3) { animation-delay: 0.3s; }
        
        .oti-letter:hover {
          transform: translateY(-10px) scale(1.1);
          filter: drop-shadow(0 8px 20px rgba(59, 130, 246, 0.5));
        }
        
        @keyframes gradient-shift {
          0%, 100% { background-position: 0% 50%; }
          50% { background-position: 100% 50%; }
        }
        
        /* Brilho pulsante atrás */
        .oti-glow {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          width: 300px;
          height: 150px;
          background: radial-gradient(ellipse, rgba(59, 130, 246, 0.25) 0%, transparent 70%);
          border-radius: 50%;
          z-index: 1;
          animation: glow-pulse 3s ease-in-out infinite;
        }
        
        @keyframes glow-pulse {
          0%, 100% { 
            opacity: 0.6; 
            transform: translate(-50%, -50%) scale(1);
          }
          50% { 
            opacity: 1; 
            transform: translate(-50%, -50%) scale(1.2);
          }
        }
        
        /* Reflexo suave abaixo */
        .oti-reflection {
          position: absolute;
          bottom: -30px;
          left: 50%;
          transform: translateX(-50%);
          width: 200px;
          height: 20px;
          background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.15), transparent);
          border-radius: 50%;
          filter: blur(8px);
          animation: reflection-pulse 3s ease-in-out infinite;
        }
        
        @keyframes reflection-pulse {
          0%, 100% { opacity: 0.5; width: 200px; }
          50% { opacity: 0.8; width: 250px; }
        }
        
        /* Pontos decorativos pulsantes */
        .dot-pulse {
          width: 10px;
          height: 10px;
          border-radius: 50%;
          background: linear-gradient(135deg, #3b82f6, #1d4ed8);
          animation: dot-bounce 1.5s ease-in-out infinite;
          box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
        }
        
        @keyframes dot-bounce {
          0%, 100% { 
            transform: translateY(0) scale(1); 
            opacity: 0.6;
          }
          50% { 
            transform: translateY(-8px) scale(1.2); 
            opacity: 1;
          }
        }
        
        /* Efeito de borda luminosa ao redor das letras */
        .oti-logo-container::before {
          content: '';
          position: absolute;
          top: -20px;
          left: -40px;
          right: -40px;
          bottom: -20px;
          border-radius: 20px;
          background: linear-gradient(45deg, transparent, rgba(59, 130, 246, 0.05), transparent);
          z-index: 0;
        }
      </style>
    </div>
  </div>
  
  <?php 
  // ===== TEMA DE NATAL - Apenas em Dezembro =====
  $isDecember = (int)date('n') === 12;
  if ($isDecember): 
  ?>
  
  <!-- Neve caindo -->
  <div class="snow-container" id="snowContainer"></div>
  
  <!-- Papai Noel voando -->
  <div class="santa-container">
    <img src="/assets/papai.png" alt="Papai Noel" class="santa-sleigh">
  </div>
  
  <!-- Montanhas de fundo -->
  <div class="mountains-container">
    <svg class="mountain" viewBox="0 0 1440 320" preserveAspectRatio="none">
      <!-- Montanha traseira -->
      <path class="mountain-back" d="M0,320 L0,200 L200,100 L350,180 L500,80 L650,160 L800,60 L950,150 L1100,50 L1250,140 L1440,70 L1440,320 Z"/>
      
      <!-- Neve no topo traseira -->
      <path class="mountain-snow" d="M200,100 L180,115 L200,110 L220,115 Z M500,80 L475,100 L500,92 L525,100 Z M800,60 L770,85 L800,75 L830,85 Z M1100,50 L1065,80 L1100,68 L1135,80 Z"/>
      
      <!-- Montanha frontal -->
      <path class="mountain-front" d="M0,320 L0,220 L150,140 L300,200 L450,120 L600,190 L750,100 L900,180 L1050,90 L1200,170 L1350,110 L1440,160 L1440,320 Z"/>
      
      <!-- Neve no topo frontal -->
      <path class="mountain-snow" d="M150,140 L125,160 L150,150 L175,160 Z M450,120 L420,145 L450,132 L480,145 Z M750,100 L715,130 L750,115 L785,130 Z M1050,90 L1010,125 L1050,108 L1090,125 Z M1350,110 L1315,140 L1350,125 L1385,140 Z"/>
    </svg>
  </div>
  
  <script>
    // Criar flocos de neve
    function createSnowflakes() {
      const container = document.getElementById('snowContainer');
      const snowflakes = ['❄', '❅', '❆', '•'];
      
      for (let i = 0; i < 50; i++) {
        const snowflake = document.createElement('div');
        snowflake.className = 'snowflake';
        snowflake.innerHTML = snowflakes[Math.floor(Math.random() * snowflakes.length)];
        snowflake.style.left = Math.random() * 100 + '%';
        snowflake.style.fontSize = (Math.random() * 10 + 8) + 'px';
        snowflake.style.opacity = Math.random() * 0.6 + 0.4;
        snowflake.style.animationDuration = (Math.random() * 5 + 8) + 's';
        snowflake.style.animationDelay = (Math.random() * 10) + 's';
        container.appendChild(snowflake);
      }
    }
    createSnowflakes();
  </script>
  
  <?php endif; // Fim do tema de Natal ?>
  
  <script>
    // Efeito de digitação
    const text = 'Sistema de Gestão Integrada';
    const typingElement = document.getElementById('typingText');
    let index = 0;
    let isDeleting = false;
    
    function typeWriter() {
      if (!isDeleting && index <= text.length) {
        typingElement.textContent = text.substring(0, index);
        index++;
        setTimeout(typeWriter, 100);
      } else if (isDeleting && index >= 0) {
        typingElement.textContent = text.substring(0, index);
        index--;
        setTimeout(typeWriter, 50);
      } else if (index > text.length) {
        setTimeout(() => {
          isDeleting = true;
          typeWriter();
        }, 2000);
      } else {
        isDeleting = false;
        index = 0;
        setTimeout(typeWriter, 500);
      }
    }
    
    typeWriter();
  </script>
</body>
</html>
