<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Certificado — <?= htmlspecialchars($cert['titulo_curso'] ?? '') ?></title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;600&display=swap');
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { background: #f5f0e8; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
    .cert {
      background: white;
      width: 800px;
      padding: 60px;
      border: 8px solid #d4af37;
      border-radius: 12px;
      position: relative;
      box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }
    .cert::before {
      content: '';
      position: absolute;
      inset: 12px;
      border: 2px solid #d4af37;
      border-radius: 6px;
      opacity: 0.4;
      pointer-events: none;
    }
    .header { text-align: center; margin-bottom: 40px; }
    .logo { font-size: 48px; margin-bottom: 12px; }
    .org { font-family: 'Inter', sans-serif; font-size: 13px; letter-spacing: 4px; text-transform: uppercase; color: #8b7355; font-weight: 600; }
    .title { font-family: 'Playfair Display', serif; font-size: 14px; letter-spacing: 6px; text-transform: uppercase; color: #8b7355; margin: 30px 0 10px; }
    .certifica { font-family: 'Playfair Display', serif; font-size: 42px; color: #1a1a2e; margin-bottom: 16px; }
    .recipient { font-family: 'Playfair Display', serif; font-size: 32px; color: #d4af37; font-weight: 700; border-bottom: 2px solid #d4af37; padding-bottom: 8px; display: inline-block; margin-bottom: 16px; }
    .description { font-size: 14px; color: #555; line-height: 1.8; margin: 20px 0; text-align: center; max-width: 580px; margin-left: auto; margin-right: auto; }
    .course { font-weight: 700; color: #1a1a2e; font-size: 16px; }
    .details { display: flex; gap: 40px; justify-content: center; margin: 30px 0; }
    .detail-item { text-align: center; }
    .detail-label { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: #8b7355; margin-bottom: 4px; }
    .detail-value { font-size: 14px; font-weight: 600; color: #1a1a2e; }
    .footer { display: flex; justify-content: space-between; margin-top: 40px; align-items: flex-end; }
    .signature { text-align: center; }
    .sig-line { width: 180px; border-bottom: 1px solid #333; margin-bottom: 8px; }
    .sig-name { font-size: 12px; font-weight: 600; color: #333; }
    .sig-role { font-size: 10px; color: #888; }
    .validation { text-align: right; font-size: 10px; color: #aaa; }
    .print-btn { position: fixed; bottom: 20px; right: 20px; background: #d4af37; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; box-shadow: 0 4px 12px rgba(212,175,55,0.4); }
    @media print {
      body { background: white; padding: 0; }
      .cert { box-shadow: none; width: 100%; }
      .print-btn { display: none; }
    }
  </style>
</head>
<body>
  <div class="cert">
    <div class="header">
      <div class="logo">🎓</div>
      <div class="org">OTI — Organização Tecnológica Integrada</div>
    </div>

    <div style="text-align:center">
      <div class="title">Certificado de Conclusão</div>
      <div class="certifica">Certifica que</div>
      <div class="recipient"><?= htmlspecialchars($cert['nome_usuario'] ?? '') ?></div>
      <div class="description">
        concluiu com êxito o curso<br>
        <span class="course"><?= htmlspecialchars($cert['titulo_curso'] ?? '') ?></span><br>
        cumprindo todos os requisitos e aprovação na avaliação.
      </div>

      <div class="details">
        <div class="detail-item">
          <div class="detail-label">Carga Horária</div>
          <div class="detail-value"><?= (int)($cert['carga_horaria'] ?? 0) ?> min</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Data de Conclusão</div>
          <div class="detail-value"><?= isset($cert['emitido_em']) ? date('d/m/Y', strtotime($cert['emitido_em'])) : date('d/m/Y') ?></div>
        </div>
      </div>
    </div>

    <div class="footer">
      <div class="signature">
        <div class="sig-line" style="margin:0 auto 8px"></div>
        <div class="sig-name"><?= htmlspecialchars($cert['gestor_nome'] ?? 'Gestor') ?></div>
        <div class="sig-role">Responsável pelo Curso</div>
      </div>
      <div style="text-align:center">
        <div style="font-size:36px">⭐</div>
      </div>
      <div class="validation">
        Código de Validação:<br>
        <strong style="font-family:monospace; font-size:9px"><?= htmlspecialchars($cert['codigo_validacao'] ?? '') ?></strong>
      </div>
    </div>
  </div>

  <button class="print-btn" onclick="window.print()">🖨️ Imprimir / Salvar PDF</button>
</body>
</html>
