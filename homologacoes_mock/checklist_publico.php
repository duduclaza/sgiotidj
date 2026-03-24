<?php
require_once __DIR__ . '/init.php';

$token = $_GET['token'] ?? '';
if (!$token) die("Token inválido.");

$data = getMockData();
$homologacao = null;
foreach ($data['homologacoes'] as $h) {
    if (md5($h['id'] . 'token_seguro') === $token) {
        $homologacao = $h;
        break;
    }
}

if (!$homologacao) {
    die("Homologação não encontrada ou token inválido.");
}

$tipo = $homologacao['tipo_equipamento'];
$checklistItems = $data['checklists'][$tipo] ?? [];

if ($homologacao['status'] === 'concluida') {
    $concluido = true;
} else {
    $concluido = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$concluido) {
    $respostas = $_POST['checklist'] ?? [];
    $nome = trim($_POST['nome_avaliador'] ?? 'Avaliador Externo');
    $obs = trim($_POST['observacoes_checklist'] ?? '');
    
    // Auto-calculate verdict
    $hasPass = false;
    $hasFail = false;
    foreach ($checklistItems as $k => $v) {
        if (isset($respostas[$k])) {
            if ($respostas[$k] === "1") $hasPass = true;
            if ($respostas[$k] === "0") $hasFail = true;
        }
    }
    
    $resultado = '';
    if ($hasPass && !$hasFail) {
        $resultado = 'aprovado';
    } elseif (!$hasPass && $hasFail) {
        $resultado = 'reprovado';
    } elseif ($hasPass && $hasFail) {
        $resultado = 'aprovado com ressalvas';
    } else {
        $resultado = 'aprovado com ressalvas'; // fallback Se nao marcou nada, estranho vir vazio.
    }
    
    $laudo = "Avaliação externa realizada via Celular.\nAvaliador: {$nome}\nObservações: " . ($obs ?: "Nenhuma.") . "\n\nO sistema calculou automaticamente o veredicto com base nas respostas e fechou a homologação.";

    atualizarHomologacaoMock($homologacao['id'], [
        'checklist_respostas' => $respostas,
        'observacoes_checklist' => $obs,
        'status' => 'concluida',
        'resultado' => $resultado,
        'parecer_final' => $laudo,
        'data_fim_homologacao' => date('Y-m-d')
    ]);
    
    header("Location: checklist_publico.php?token={$token}&sucesso=1");
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliação de Homologação</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: { primary: {"50":"#eff6ff","100":"#dbeafe","200":"#bfdbfe","300":"#93c5fd","400":"#60a5fa","500":"#3b82f6","600":"#2563eb","700":"#1d4ed8","800":"#1e40af","900":"#1e3a8a","950":"#172554"} }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-200 min-h-screen p-4 sm:p-8">

<div class="max-w-xl mx-auto">
    <div class="text-center mb-6 mt-4">
        <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-400 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
            <i class="ph-fill ph-clipboard-text text-3xl"></i>
        </div>
        <h1 class="text-2xl font-bold">Avaliação Técnica em Campo</h1>
        <p class="text-slate-500 text-sm mt-1">Preencha o checklist para o equipamento <br><strong><?= $homologacao['codigo'] ?> - <?= $homologacao['modelo'] ?></strong></p>
    </div>

    <?php if (isset($_GET['sucesso']) || $concluido): ?>
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-8 text-center mt-8">
            <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="ph-bold ph-check text-4xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">Checklist Concluído!</h2>
            <p class="text-slate-500 dark:text-slate-400 mb-6">Suas respostas foram contabilizadas e a homologação foi automaticamente fechada no sistema SGQ.</p>
            <p class="text-sm text-slate-400">Pode fechar esta janela no seu celular.</p>
        </div>
    <?php else: ?>
        <form method="POST" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-6 sm:p-8 border-b border-slate-200 dark:border-slate-700">
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Seu Nome Completo</label>
                    <input type="text" name="nome_avaliador" required placeholder="Ex: João da Silva" class="bg-slate-50 border border-slate-300 text-slate-900 rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-3 dark:bg-slate-900 dark:border-slate-600 dark:text-white">
                </div>
                
                <h3 class="font-bold text-slate-800 dark:text-white text-lg mb-4 flex items-center gap-2 border-b border-slate-100 dark:border-slate-700 pb-2">
                    <i class="ph-fill ph-list-checks text-primary-500"></i>
                    Critérios de Homologação
                </h3>
                
                <div class="space-y-4">
                    <?php foreach ($checklistItems as $key => $label): ?>
                        <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 p-4 rounded-xl">
                            <span class="block text-sm font-medium text-slate-800 dark:text-slate-200 mb-3"><?= $label ?></span>
                            
                            <div class="flex gap-2 w-full">
                                <!-- Passed -->
                                <input type="radio" class="hidden peer/ok" name="checklist[<?= $key ?>]" id="<?= $key ?>_ok" value="1" required>
                                <label for="<?= $key ?>_ok" class="flex-1 text-center py-2.5 px-2 text-sm font-bold bg-white text-emerald-600 border border-emerald-200 rounded-lg cursor-pointer hover:bg-emerald-50 peer-checked/ok:bg-emerald-600 peer-checked/ok:text-white peer-checked/ok:border-emerald-600 dark:bg-slate-800 dark:border-emerald-900/50 dark:hover:bg-slate-700 transition-colors shadow-sm">
                                    <i class="ph-bold ph-check mr-1"></i> PASS
                                </label>

                                <!-- Failed -->
                                <input type="radio" class="hidden peer/fail" name="checklist[<?= $key ?>]" id="<?= $key ?>_fail" value="0" required>
                                <label for="<?= $key ?>_fail" class="flex-1 text-center py-2.5 px-2 text-sm font-bold bg-white text-rose-600 border border-rose-200 rounded-lg cursor-pointer hover:bg-rose-50 peer-checked/fail:bg-rose-600 peer-checked/fail:text-white peer-checked/fail:border-rose-600 dark:bg-slate-800 dark:border-rose-900/50 dark:hover:bg-slate-700 transition-colors shadow-sm">
                                    <i class="ph-bold ph-x mr-1"></i> FAIL
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="p-6 sm:p-8 bg-slate-50 dark:bg-slate-800/50">
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Observações (Opcional)</label>
                    <textarea name="observacoes_checklist" rows="3" placeholder="Comentários sobre os testes..." class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-3 dark:bg-slate-900 dark:border-slate-600 dark:text-white"></textarea>
                </div>
                
                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-lg px-6 py-4 shadow-md flex justify-center items-center gap-2">
                    <i class="ph-bold ph-paper-plane-right"></i>
                    Finalizar e Enviar
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
