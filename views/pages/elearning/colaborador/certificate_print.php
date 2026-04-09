<?php
$certificateData = $certificate ?? [];
$templateMeta = $template['template'] ?? [];
$templateSettings = $template['settings'] ?? [];
$accent = $templateSettings['accent_color'] ?? '#1d4ed8';
$customText = $templateSettings['custom_text'] ?? 'Certificamos a conclusão desta trilha de aprendizagem.';
$logoPath = $templateSettings['logo_path'] ?? null;
$signaturePath = $templateSettings['signature_path'] ?? null;
$backgroundPath = $templateSettings['background_path'] ?? null;
$backgroundUrl = $templateSettings['background_data_url'] ?? ($backgroundPath ? '/' . ltrim($backgroundPath, '/') : null);
$logoUrl = $templateSettings['logo_data_url'] ?? ($logoPath ? '/' . ltrim($logoPath, '/') : null);
$signatureUrl = $templateSettings['signature_data_url'] ?? ($signaturePath ? '/' . ltrim($signaturePath, '/') : null);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($certificateData['course_title'] ?? 'Certificado') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; font-family: 'Outfit', sans-serif; background: #0f172a; color: #fff; }
        .sheet {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(circle at top left, rgba(37,99,235,.18), transparent 32%),
                radial-gradient(circle at top right, rgba(16,185,129,.12), transparent 28%),
                #0f172a;
        }
        .certificate {
            position: relative;
            width: min(1200px, 100%);
            min-height: 760px;
            border-radius: 32px;
            overflow: hidden;
            background:
                linear-gradient(135deg, rgba(255,255,255,.96), rgba(248,250,252,.98));
            color: #0f172a;
            box-shadow: 0 40px 120px -36px rgba(15,23,42,.55);
            border: 1px solid rgba(15,23,42,.08);
        }
        .certificate::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(135deg, <?= e($accent) ?> 0%, transparent 24%),
                linear-gradient(320deg, rgba(15,23,42,.08) 0%, transparent 30%);
            opacity: .12;
            pointer-events: none;
        }
        .certificate::after {
            content: '';
            position: absolute;
            inset: 16px;
            border-radius: 24px;
            border: 2px solid rgba(15,23,42,.08);
            pointer-events: none;
        }
        .watermark {
            position: absolute;
            inset: 0;
            background-image: <?= $backgroundUrl ? "url('" . e($backgroundUrl) . "')" : 'none' ?>;
            background-position: center;
            background-size: cover;
            opacity: .08;
        }
        @media print {
            .sheet { padding: 0; background: white; }
            .certificate { box-shadow: none; width: 100%; border-radius: 0; }
            .actions { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="actions" style="position:fixed;top:18px;right:18px;display:flex;gap:12px;z-index:10;">
        <button onclick="window.print()" style="border:none;border-radius:999px;padding:12px 18px;background:#fff;color:#0f172a;font-weight:800;cursor:pointer;">Imprimir</button>
        <a href="/elearning/colaborador/certificados" style="border-radius:999px;padding:12px 18px;background:rgba(255,255,255,.16);color:#fff;font-weight:800;text-decoration:none;">Voltar</a>
    </div>

    <div class="sheet">
        <article class="certificate">
            <div class="watermark"></div>
            <div style="position:relative;display:flex;min-height:760px;flex-direction:column;justify-content:space-between;padding:64px 72px;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:32px;">
                    <div>
                        <p style="margin:0;font-size:12px;font-weight:700;letter-spacing:.45em;text-transform:uppercase;color:<?= e($accent) ?>;">SGI E-Learning</p>
                        <h1 style="margin:18px 0 0;font-size:56px;line-height:1;font-weight:900;">Certificado de Conclusão</h1>
                        <p style="margin:18px 0 0;max-width:560px;font-size:18px;line-height:1.7;color:#334155;"><?= e($customText) ?></p>
                    </div>
                    <?php if ($logoUrl): ?>
                        <img src="<?= e($logoUrl) ?>" alt="Logo" style="max-width:220px;max-height:90px;object-fit:contain;">
                    <?php else: ?>
                        <div style="padding:18px 24px;border-radius:24px;background:rgba(15,23,42,.04);font-size:13px;font-weight:800;letter-spacing:.2em;text-transform:uppercase;color:#334155;"><?= e($templateMeta['name'] ?? 'Template') ?></div>
                    <?php endif; ?>
                </div>

                <div style="display:grid;gap:24px;">
                    <p style="margin:0;font-size:18px;text-transform:uppercase;letter-spacing:.3em;color:#64748b;">Conferido a</p>
                    <h2 style="margin:0;font-size:72px;line-height:1;font-weight:900;color:#0f172a;"><?= e($certificateData['student_name'] ?? 'Aluno') ?></h2>
                    <p style="margin:0;max-width:920px;font-size:28px;line-height:1.5;color:#1e293b;">
                        Pela conclusão do curso <strong style="color:<?= e($accent) ?>;"><?= e($certificateData['course_title'] ?? 'Curso') ?></strong>,
                        com carga horária de <strong><?= (int) ($certificateData['workload_hours'] ?? 0) ?> horas</strong> e aproveitamento final de
                        <strong><?= number_format((float) ($certificateData['score_percent'] ?? 70), 0) ?>%</strong>.
                    </p>
                </div>

                <div style="display:grid;gap:40px;grid-template-columns:1.1fr .9fr;">
                    <div style="display:grid;gap:14px;">
                        <div style="display:flex;gap:28px;flex-wrap:wrap;font-size:15px;color:#475569;">
                            <span>Professor responsável: <strong style="color:#0f172a;"><?= e($certificateData['teacher_name'] ?? 'Professor') ?></strong></span>
                            <span>Emitido em: <strong style="color:#0f172a;"><?= !empty($certificateData['issued_at']) ? date('d/m/Y', strtotime((string) $certificateData['issued_at'])) : date('d/m/Y') ?></strong></span>
                        </div>
                        <div style="display:flex;gap:28px;flex-wrap:wrap;font-size:15px;color:#475569;">
                            <span>Código de validação: <strong style="color:#0f172a;"><?= e($certificateData['validation_code'] ?? '') ?></strong></span>
                            <span>Modelo: <strong style="color:#0f172a;"><?= e($templateMeta['name'] ?? 'Template') ?></strong></span>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;justify-content:flex-end;gap:12px;">
                        <?php if ($signatureUrl): ?>
                            <img src="<?= e($signatureUrl) ?>" alt="Assinatura" style="max-width:220px;max-height:70px;object-fit:contain;">
                        <?php endif; ?>
                        <div style="width:260px;border-top:1px solid rgba(15,23,42,.24);padding-top:12px;text-align:right;">
                            <p style="margin:0;font-size:16px;font-weight:800;color:#0f172a;"><?= e($certificateData['teacher_name'] ?? 'Professor') ?></p>
                            <p style="margin:4px 0 0;font-size:12px;letter-spacing:.3em;text-transform:uppercase;color:#64748b;">Assinatura</p>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    </div>
</body>
</html>
