<?php
// views/pages/elearning/colaborador/resultado_prova.php
?>
<div class="max-w-2xl mx-auto space-y-6">
  <!-- Header de resultado -->
  <?php $aprovado = (bool)($tentativa['aprovado'] ?? false); ?>
  <div class="bg-white rounded-xl shadow p-8 text-center">
    <div class="text-6xl mb-4"><?= $aprovado ? '🏆' : '😕' ?></div>
    <h1 class="text-2xl font-bold <?= $aprovado ? 'text-green-600' : 'text-red-500' ?>">
      <?= $aprovado ? 'Parabéns! Você foi aprovado!' : 'Não foi desta vez...' ?>
    </h1>
    <div class="mt-4 text-5xl font-bold text-gray-800"><?= number_format((float)($tentativa['nota_obtida'] ?? 0), 1) ?>%</div>
    <div class="text-gray-500 text-sm mt-1">Nota mínima exigida: <?= number_format((float)($tentativa['nota_minima'] ?? 70), 1) ?>%</div>

    <div class="mt-6 flex gap-4 justify-center">
      <a href="/elearning/colaborador" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">← Meus Cursos</a>
      <?php if (!$aprovado): ?>
      <a href="javascript:history.back()" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700">Tentar Novamente</a>
      <?php else: ?>
      <a href="/elearning/colaborador/certificados" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">Ver Certificado 🏅</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Gabarito -->
  <?php if (!empty($respostas)): ?>
  <div class="bg-white rounded-xl shadow p-6 space-y-4">
    <h2 class="font-semibold text-gray-900">📋 Gabarito</h2>
    <?php foreach ($respostas as $i => $r): ?>
    <div class="flex items-start gap-3 p-4 rounded-lg <?= $r['correta'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' ?>">
      <span class="<?= $r['correta'] ? 'text-green-600' : 'text-red-500' ?> font-bold text-lg"><?= $r['correta'] ? '✓' : '✗' ?></span>
      <div>
        <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($r['enunciado'] ?? '') ?></p>
        <?php if (!empty($r['alt_texto'])): ?>
        <p class="text-xs text-gray-500 mt-1">Resposta: <?= htmlspecialchars($r['alt_texto']) ?></p>
        <?php endif; ?>
        <p class="text-xs <?= $r['correta'] ? 'text-green-600' : 'text-red-500' ?> mt-0.5"><?= number_format((float)($r['pontos'] ?? 0),1) ?> ponto(s)</p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
