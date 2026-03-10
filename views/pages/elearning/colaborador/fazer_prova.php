<?php
// views/pages/elearning/colaborador/fazer_prova.php
$totalQuestoes = count($questoes);
?>
<div class="max-w-3xl mx-auto space-y-6">
  <div>
    <a href="/elearning/colaborador" class="text-blue-600 hover:underline text-sm">← Meus Cursos</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-1">📝 <?= htmlspecialchars($prova['titulo'] ?? '') ?></h1>
    <div class="flex gap-4 mt-2 text-sm text-gray-500">
      <span>Nota mínima: <?= $prova['nota_minima'] ?>%</span>
      <?php if (($prova['tempo_min'] ?? 0) > 0): ?>
      <span>⏱ Tempo: <strong id="timer"><?= (int)$prova['tempo_min'] ?>:00</strong></span>
      <?php endif; ?>
      <span><?= $totalQuestoes ?> questões</span>
    </div>
  </div>

  <?php if (empty($questoes)): ?>
  <div class="bg-white rounded-xl shadow p-8 text-center text-gray-400">Esta prova ainda não tem questões.</div>
  <?php else: ?>
  <form id="formProva" class="space-y-6">
    <input type="hidden" name="tentativa_id" value="<?= (int)($tentativaId ?? 0) ?>">

    <?php foreach ($questoes as $qi => $q): ?>
    <?php
      $alts = [];
      if (!empty($q['alternativas_raw'])) {
        foreach (explode(';;', $q['alternativas_raw']) as $alt) {
          $parts = explode('|', $alt, 3);
          if (count($parts) === 3) $alts[] = ['id' => $parts[0], 'texto' => $parts[1]];
        }
      }
    ?>
    <div class="bg-white rounded-xl shadow p-6">
      <div class="flex items-start gap-3 mb-4">
        <span class="flex-shrink-0 w-8 h-8 bg-purple-100 text-purple-700 rounded-full flex items-center justify-center text-sm font-bold"><?= $qi + 1 ?></span>
        <p class="text-gray-900 font-medium"><?= htmlspecialchars($q['enunciado']) ?></p>
      </div>

      <?php if ($q['tipo'] === 'dissertativa'): ?>
      <textarea name="respostas[<?= (int)$q['id'] ?>]" rows="4" placeholder="Digite sua resposta..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500"></textarea>
      <?php elseif ($q['tipo'] === 'verdadeiro_falso'): ?>
      <div class="space-y-2">
        <?php foreach ([['id'=> 'v', 'texto'=>'Verdadeiro'], ['id'=>'f', 'texto'=>'Falso']] as $opt): ?>
        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-purple-50">
          <input type="radio" name="respostas[<?= (int)$q['id'] ?>]" value="<?= $opt['id'] ?>" class="text-purple-600">
          <span class="text-sm text-gray-800"><?= $opt['texto'] ?></span>
        </label>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="space-y-2">
        <?php foreach ($alts as $alt): ?>
        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-purple-50">
          <input type="radio" name="respostas[<?= (int)$q['id'] ?>]" value="<?= (int)$alt['id'] ?>" class="text-purple-600">
          <span class="text-sm text-gray-800"><?= htmlspecialchars($alt['texto']) ?></span>
        </label>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <div class="flex justify-end">
      <button type="button" id="btnSubmeter" onclick="submeterProva()" class="bg-purple-600 text-white px-6 py-3 rounded-xl hover:bg-purple-700 transition font-medium">
        Finalizar e Enviar Prova →
      </button>
    </div>
  </form>
  <?php endif; ?>
</div>

<script>
<?php if (($prova['tempo_min'] ?? 0) > 0): ?>
let segundos = <?= (int)$prova['tempo_min'] ?> * 60;
const timerEl = document.getElementById('timer');
const interval = setInterval(function() {
  segundos--;
  const m = Math.floor(segundos / 60);
  const s = segundos % 60;
  timerEl.textContent = m + ':' + (s < 10 ? '0' : '') + s;
  timerEl.parentElement.classList.toggle('text-red-600', segundos < 60);
  if (segundos <= 0) { clearInterval(interval); submeterProva(); }
}, 1000);
<?php endif; ?>

async function submeterProva() {
  if (!confirm('Deseja finalizar e enviar a prova?')) return;
  const btn = document.getElementById('btnSubmeter');
  btn.disabled = true; btn.textContent = 'Enviando...';
  const fd = new FormData(document.getElementById('formProva'));
  try {
    const res = await fetch('/elearning/colaborador/provas/submeter', { method: 'POST', body: fd });
    const d = await res.json();
    if (d.success) {
      window.location.href = '/elearning/colaborador/provas/resultado/' + d.tentativa_id;
    } else {
      alert('Erro: ' + d.message);
      btn.disabled = false; btn.textContent = 'Finalizar e Enviar Prova →';
    }
  } catch(e) {
    alert('Erro de conexão');
    btn.disabled = false; btn.textContent = 'Finalizar e Enviar Prova →';
  }
}
</script>
