<?php
// views/pages/elearning/gestor/dashboard.php
?>
<style>
  .el-fade-in { animation: elFadeIn .4s ease; }
  @keyframes elFadeIn { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
  .el-card-hover { transition: transform .2s, box-shadow .2s; }
  .el-card-hover:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(0,0,0,.12); }
  .el-gradient-header { background: linear-gradient(135deg, #1e40af 0%, #6366f1 50%, #8b5cf6 100%); }
  .el-thumb { background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%); }
</style>

<div class="space-y-6 el-fade-in">

  <!-- Hero Header -->
  <div class="el-gradient-header rounded-2xl p-6 sm:p-8 text-white shadow-lg">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight flex items-center gap-2">
          <span class="text-3xl">🎓</span> eLearning Gestor
        </h1>
        <p class="text-blue-100 text-sm mt-1">Gerencie cursos, aulas, provas e certificados</p>
      </div>
      <?php if ($canEdit): ?>
      <a href="/elearning/gestor/cursos"
        class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 backdrop-blur text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition border border-white/30 shadow-sm">
        ➕ Gerenciar Cursos
      </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-2xl shadow-md p-6 border-l-4 border-indigo-500 el-card-hover">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-3xl font-bold text-indigo-600"><?= (int)$totalCursos ?></div>
          <div class="text-sm text-gray-500 mt-1 font-medium">Cursos Cadastrados</div>
        </div>
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center text-2xl">📚</div>
      </div>
    </div>
    <div class="bg-white rounded-2xl shadow-md p-6 border-l-4 border-green-500 el-card-hover">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-3xl font-bold text-green-600"><?= (int)$totalMatriculas ?></div>
          <div class="text-sm text-gray-500 mt-1 font-medium">Matrículas Totais</div>
        </div>
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-2xl">👥</div>
      </div>
    </div>
    <div class="bg-white rounded-2xl shadow-md p-6 border-l-4 border-purple-500 el-card-hover">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-3xl font-bold text-purple-600"><?= (int)$totalConcluidos ?></div>
          <div class="text-sm text-gray-500 mt-1 font-medium">Cursos Concluídos</div>
        </div>
        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-2xl">🏆</div>
      </div>
    </div>
  </div>

  <!-- Courses Grid -->
  <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-gray-50 to-white">
      <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">📚 Cursos Recentes</h2>
      <a href="/elearning/gestor/cursos" class="text-indigo-600 hover:text-indigo-700 text-sm font-semibold transition">Ver todos →</a>
    </div>

    <?php if (empty($cursos)): ?>
    <div class="p-12 text-center">
      <div class="text-5xl mb-4">📚</div>
      <h3 class="text-lg font-semibold text-gray-600 mb-1">Nenhum curso cadastrado</h3>
      <p class="text-sm text-gray-400 mb-4">Comece criando seu primeiro curso</p>
      <a href="/elearning/gestor/cursos" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition">
        ➕ Criar Curso
      </a>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-5">
      <?php foreach ($cursos as $c): ?>
      <?php
        $sc = ['ativo'=>'green','rascunho'=>'yellow','inativo'=>'gray'][$c['status']] ?? 'gray';
      ?>
      <div class="el-card-hover bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
        <?php if (!empty($c['has_thumbnail'])): ?>
        <img src="/elearning/gestor/cursos/thumbnail?id=<?= (int)$c['id'] ?>" class="w-full h-32 object-cover" alt="">
        <?php else: ?>
        <div class="el-thumb h-32 flex items-center justify-center"><span class="text-4xl text-white/80">🎓</span></div>
        <?php endif; ?>
        <div class="p-4 flex flex-col flex-1">
          <div class="flex items-start justify-between gap-2 mb-2">
            <h3 class="font-bold text-gray-900 text-sm leading-tight"><?= htmlspecialchars($c['titulo']) ?></h3>
            <span class="flex-shrink-0 px-1.5 py-0.5 rounded text-[10px] font-bold bg-<?= $sc ?>-100 text-<?= $sc ?>-700">
              <?= strtoupper($c['status']) ?>
            </span>
          </div>
          <div class="text-xs text-gray-400 mb-2"><?= htmlspecialchars($c['gestor_nome'] ?? '-') ?></div>
          <div class="flex items-center gap-3 text-xs text-gray-400 mt-auto pt-2 border-t border-gray-50">
            <span>👥 <?= (int)($c['total_matriculas'] ?? 0) ?></span>
          </div>
          <div class="flex flex-wrap gap-1.5 mt-3">
            <a href="/elearning/gestor/cursos/<?= (int)$c['id'] ?>/aulas" class="text-[10px] font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 px-2 py-0.5 rounded-md transition">Aulas</a>
            <a href="/elearning/gestor/cursos/<?= (int)$c['id'] ?>/provas" class="text-[10px] font-semibold text-purple-600 bg-purple-50 hover:bg-purple-100 px-2 py-0.5 rounded-md transition">Provas</a>
            <a href="/elearning/gestor/cursos/<?= (int)$c['id'] ?>/matriculas" class="text-[10px] font-semibold text-green-600 bg-green-50 hover:bg-green-100 px-2 py-0.5 rounded-md transition">Matrículas</a>
            <a href="/elearning/gestor/cursos/<?= (int)$c['id'] ?>/progresso" class="text-[10px] font-semibold text-teal-600 bg-teal-50 hover:bg-teal-100 px-2 py-0.5 rounded-md transition">Progresso</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
