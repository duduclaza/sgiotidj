<?php
// views/pages/elearning/gestor/provas.php
?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <a href="/elearning/gestor/cursos" class="text-blue-600 hover:underline text-sm">← Cursos</a>
      <h1 class="text-2xl font-bold text-gray-900 mt-1">📝 Provas — <?= htmlspecialchars($curso['titulo'] ?? '') ?></h1>
    </div>
    <?php if ($canEdit): ?>
    <button onclick="document.getElementById('modalProva').classList.remove('hidden')" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm">+ Nova Prova</button>
    <?php endif; ?>
  </div>

  <?php if (empty($provas)): ?>
  <div class="bg-white rounded-xl shadow p-8 text-center text-gray-400">Nenhuma prova criada para este curso.</div>
  <?php else: ?>
  <div class="space-y-4">
    <?php foreach ($provas as $p): ?>
    <div class="bg-white rounded-xl shadow p-5">
      <div class="flex items-start justify-between">
        <div>
          <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($p['titulo']) ?></h3>
          <div class="flex gap-4 mt-2 text-sm text-gray-500">
            <span>Nota mínima: <strong><?= $p['nota_minima'] ?>%</strong></span>
            <span>Tentativas: <strong><?= $p['tentativas_max'] ?></strong></span>
            <span>Tempo: <strong><?= $p['tempo_min'] > 0 ? $p['tempo_min'].' min' : 'Sem limite' ?></strong></span>
            <span>Questões: <strong><?= (int)($p['total_questoes'] ?? 0) ?></strong></span>
          </div>
        </div>
        <button onclick="document.getElementById('modalQuestao_<?= (int)$p['id'] ?>').classList.remove('hidden')" class="text-xs text-purple-600 hover:underline ml-4">+ Questão</button>
      </div>
    </div>

    <!-- Modal Questão por prova -->
    <div id="modalQuestao_<?= (int)$p['id'] ?>" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 space-y-4 max-h-[90vh] overflow-y-auto">
        <h2 class="text-lg font-semibold">Nova Questão — <?= htmlspecialchars($p['titulo']) ?></h2>
        <form id="formQuestao_<?= (int)$p['id'] ?>" class="space-y-3">
          <input type="hidden" name="id_prova" value="<?= (int)$p['id'] ?>">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Enunciado *</label>
            <textarea name="enunciado" required rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
              <select name="tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="multipla">Múltipla escolha</option>
                <option value="verdadeiro_falso">Verdadeiro/Falso</option>
                <option value="dissertativa">Dissertativa</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Pontos</label>
              <input type="number" name="pontos" value="1" min="0.5" step="0.5" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Alternativas (marque a correta)</label>
            <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="flex items-center gap-2 mb-2">
              <input type="radio" name="correta" value="<?= $i ?>" class="flex-shrink-0">
              <input type="text" name="alternativas[]" placeholder="Alternativa <?= chr(65+$i) ?>" class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm">
            </div>
            <?php endfor; ?>
          </div>
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" onclick="document.getElementById('modalQuestao_<?= (int)$p['id'] ?>').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Cancelar</button>
            <button type="button" onclick="salvarQuestao(<?= (int)$p['id'] ?>)" class="px-4 py-2 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700">Salvar</button>
          </div>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Modal Nova Prova -->
<div id="modalProva" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
    <h2 class="text-lg font-semibold">Nova Prova</h2>
    <form id="formProva" class="space-y-3">
      <input type="hidden" name="id_curso" value="<?= (int)($curso['id'] ?? 0) ?>">
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
        <input type="text" name="titulo" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
      <div class="grid grid-cols-3 gap-3">
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Nota Mín. %</label>
          <input type="number" name="nota_minima" value="70" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Tentativas</label>
          <input type="number" name="tentativas_max" value="3" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Tempo (min)</label>
          <input type="number" name="tempo_min" value="0" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
      </div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="document.getElementById('modalProva').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Cancelar</button>
        <button type="button" onclick="salvarProva()" class="px-4 py-2 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700">Criar</button>
      </div>
    </form>
  </div>
</div>

<script>
async function salvarProva() {
  const fd = new FormData(document.getElementById('formProva'));
  const res = await fetch('/elearning/gestor/provas/store', { method: 'POST', body: fd });
  const d = await res.json();
  if (d.success) { alert(d.message); location.reload(); } else alert('Erro: ' + d.message);
}
async function salvarQuestao(provaId) {
  const fd = new FormData(document.getElementById('formQuestao_' + provaId));
  const res = await fetch('/elearning/gestor/questoes/store', { method: 'POST', body: fd });
  const d = await res.json();
  if (d.success) { alert('Questão salva!'); location.reload(); } else alert('Erro: ' + d.message);
}
</script>
