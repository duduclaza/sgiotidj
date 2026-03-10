<?php
// views/pages/elearning/colaborador/meus_certificados.php
?>
<div class="space-y-6">
  <h1 class="text-2xl font-bold text-gray-900">🏅 Meus Certificados</h1>

  <?php if (empty($certificados)): ?>
  <div class="bg-white rounded-xl shadow p-10 text-center">
    <div class="text-5xl mb-4">📜</div>
    <p class="text-gray-500">Você ainda não possui certificados. Conclua um curso e seja aprovado na prova para obter seu certificado!</p>
    <a href="/elearning/colaborador" class="mt-4 inline-block text-blue-600 hover:underline text-sm">← Ver meus cursos</a>
  </div>
  <?php else: ?>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <?php foreach ($certificados as $c): ?>
    <div class="bg-white rounded-xl shadow p-6 border-l-4 border-yellow-400">
      <div class="flex items-start justify-between">
        <div>
          <div class="text-yellow-500 text-2xl mb-2">🏆</div>
          <h3 class="font-bold text-gray-900"><?= htmlspecialchars($c['titulo_curso']) ?></h3>
          <div class="text-sm text-gray-500 mt-1">
            Emitido em <?= date('d/m/Y', strtotime($c['emitido_em'])) ?>
          </div>
          <div class="text-xs text-gray-400 mt-1">
            Carga horária: <?= (int)$c['carga_horaria'] ?> min
          </div>
          <div class="text-xs font-mono text-gray-400 mt-2 break-all">
            Código: <?= htmlspecialchars($c['codigo_validacao']) ?>
          </div>
        </div>
        <a href="/elearning/colaborador/certificados/<?= htmlspecialchars($c['codigo_validacao']) ?>" target="_blank" class="ml-4 flex-shrink-0 bg-yellow-400 text-yellow-900 px-3 py-2 rounded-lg text-xs font-semibold hover:bg-yellow-500 transition">📄 Baixar</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
