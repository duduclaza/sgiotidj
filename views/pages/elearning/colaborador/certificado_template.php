<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Certificado — <?= htmlspecialchars($cert['titulo_curso'] ?? '') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;600;800&family=Great+Vibes&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 40px; }
    
    .diploma-container { 
      width: 1123px; /* A4 Landscape at 96dpi approx */
      height: 794px;
      background: white; 
      box-shadow: 0 40px 80px rgba(0,0,0,0.1); 
      position: relative; 
      overflow: hidden; 
    }
    
    .diploma-content {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 80px;
      text-align: center;
      z-index: 10;
    }

    .logo-img {
      position: absolute;
      z-index: 50;
      transform: translate(-50%, 0);
    }

    /* PREMIUM LAYOUTS (Same as config) */
    .tpl-1 { background: #fffcf0; color: #1a1a1a; border: 30px solid #c5a059; }
    .tpl-1::before { content: ''; position: absolute; inset: 15px; border: 3px solid #c5a059; pointer-events: none; }
    .tpl-1 .title { font-family: 'Playfair Display', serif; font-size: 64px; font-weight: 900; color: #8a6d3b; margin-bottom: 20px; }
    .tpl-1 .name { font-family: 'Great Vibes', cursive; font-size: 72px; color: #1a1a1a; margin: 30px 0; }
    .tpl-1 .label { color: #8a6d3b; letter-spacing: 5px; text-transform: uppercase; font-size: 14px; }

    .tpl-2 { background: #ffffff; color: #0f172a; }
    .tpl-2::after { content: ''; position: absolute; top:0; right:0; width:45%; height:100%; background: linear-gradient(135deg, rgba(99,102,241,0.15) 0%, rgba(99,102,241,0) 100%); clip-path: polygon(100% 0, 0 0, 100% 100%); }
    .tpl-2 .title { font-family: 'Montserrat', sans-serif; font-size: 56px; font-weight: 800; text-transform: uppercase; letter-spacing: 6px; color: #4338ca; }
    .tpl-2 .name { font-family: 'Montserrat', sans-serif; font-size: 48px; font-weight: 600; color: #1e1b4b; border-bottom: 6px solid #4338ca; padding-bottom: 10px; }

    .tpl-3 { background: #0f172a; color: #f8fafc; border: 4px solid #334155; }
    .tpl-3::before { content: ''; position: absolute; top:0; left:0; width:100%; height:120px; background: #1e293b; }
    .tpl-3 .title { font-family: 'Playfair Display', serif; font-size: 52px; font-weight: 700; color: #c5a059; text-transform: uppercase; margin-top: 60px; }
    .tpl-3 .name { font-family: 'Montserrat', sans-serif; font-size: 44px; font-weight: 800; color: #ffffff; }

    .tpl-4 { background: #fdf6e3; color: #5d4037; padding: 60px; }
    .tpl-4::before { content: ''; position: absolute; inset: 40px; border: 12px double #8d6e63; border-radius: 6px; }
    .tpl-4 .title { font-family: 'Playfair Display', serif; font-size: 58px; font-style: italic; }
    .tpl-4 .name { font-family: 'Playfair Display', serif; font-size: 52px; font-weight: 700; text-decoration: underline; }

    .tpl-5 { background: linear-gradient(45deg, #f3f4f6 0%, #ffffff 100%); }
    .tpl-5::before { content: ''; position: absolute; bottom:-50px; left:-50px; width:300px; height:300px; background: #fbbf24; opacity: 0.1; border-radius: 50%; }
    .tpl-5 .title { font-family: 'Montserrat', sans-serif; font-size: 48px; font-weight: 300; color: #1f2937; }
    .tpl-5 .name { font-family: 'Montserrat', sans-serif; font-size: 42px; color: #d97706; background: rgba(251, 191, 36, 0.1); padding: 10px 40px; border-radius: 999px; }

    .footer { width: 100%; margin-top: auto; display: flex; justify-content: space-between; align-items: flex-end; padding: 0 40px 20px; }
    .sig-block { text-align: center; }
    .sig-line { width: 220px; border-bottom: 2px solid currentColor; margin-bottom: 10px; opacity: 0.6; }
    .sig-text { font-size: 14px; font-weight: 700; }
    
    .print-btn { position: fixed; bottom: 30px; right: 30px; background: #6366f1; color: white; padding: 15px 30px; border-radius: 12px; font-weight: 800; border: none; cursor: pointer; box-shadow: 0 10px 20px rgba(99,102,241,0.3); z-index: 100; transition: all 0.2s; }
    .print-btn:hover { transform: scale(1.05); background: #4f46e5; }

    @media print {
      body { background: white; padding: 0; }
      .diploma-container { box-shadow: none; width: 100%; height: 100vh; }
      .print-btn { display: none; }
    }
  </style>
</head>
<body>

  <div class="diploma-container tpl-<?= $tplConfig['layout_ativo'] ?? 1 ?>">
    
    <!-- Logo -->
    <?php if ($tplConfig['logo_diploma']): ?>
      <img class="logo-img" 
           src="data:<?= $tplConfig['logo_tipo'] ?>;base64,<?= base64_encode($tplConfig['logo_diploma']) ?>" 
           style="left: <?= $tplConfig['logo_x'] ?? 50 ?>%; top: <?= $tplConfig['logo_y'] ?? 10 ?>%; width: <?= $tplConfig['logo_width'] ?? 150 ?>px;">
    <?php endif; ?>

    <div class="diploma-content">
      <div class="title">Certificado de Conclusão</div>
      <div class="label" style="margin: 30px 0;">Certificamos com honra que</div>
      <div class="name"><?= htmlspecialchars($cert['nome_usuario'] ?? 'NOME DO ALUNO') ?></div>
      
      <p style="font-size: 18px; max-width: 700px; line-height: 1.8; margin-top: 40px;">
        concluiu com aproveitamento excepcional o treinamento de<br>
        <b style="font-size: 24px; color: inherit;"><?= htmlspecialchars($cert['titulo_curso'] ?? 'O CURSO') ?></b><br>
        com carga horária de <?= round(($cert['carga_horaria'] ?? 0) / 60, 1) ?> horas de conteúdo programático.
      </p>

      <div class="footer">
        <div style="text-align: left;">
          <p style="font-size: 10px; opacity: 0.6; text-transform: uppercase; font-weight: 800;">Data de Emissão</p>
          <p style="font-size: 16px; font-weight: 700;"><?= date('d/m/Y', strtotime($cert['emitido_em'])) ?></p>
        </div>

        <div class="sig-block">
          <div class="sig-line"></div>
          <p class="sig-text"><?= htmlspecialchars($tplConfig['assinatura_texto'] ?? 'Diretoria') ?></p>
        </div>

        <div style="text-align: right;">
          <p style="font-size: 10px; opacity: 0.6; text-transform: uppercase; font-weight: 800;">Validação</p>
          <p style="font-size: 10px; font-family: monospace; font-weight: bold;"><?= $cert['codigo_validacao'] ?></p>
        </div>
      </div>
    </div>
  </div>

  <button class="print-btn" onclick="window.print()">🖨️ Imprimir Certificado</button>

</body>
</html>
