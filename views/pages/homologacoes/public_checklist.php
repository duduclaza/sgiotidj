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
<body class="bg-slate-100 text-slate-800 min-h-screen p-4 sm:p-8">
<div class="max-w-xl mx-auto">
    <div class="text-center mb-6 mt-4">
        <div class="w-16 h-16 bg-primary-100 text-primary-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
            <i class="ph-fill ph-clipboard-text text-3xl"></i>
        </div>
        <h1 class="text-2xl font-bold">Avaliação Técnica em Campo</h1>
        <p class="text-slate-500 text-sm mt-1">Preencha o checklist para o equipamento <br><strong><?= htmlspecialchars($homologacao['codigo']) ?> - <?= htmlspecialchars($homologacao['modelo']) ?></strong></p>
    </div>

    <?php if (isset($_GET['sucesso']) || $concluido): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center mt-8">
            <div class="w-20 h-20 bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="ph-bold ph-check text-4xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-800 mb-2">Checklist Concluído!</h2>
            <p class="text-slate-500 mb-6">Suas respostas foram contabilizadas e a homologação foi atualizada no SGQ.</p>
            <p class="text-sm text-slate-400">Pode fechar esta janela.</p>
        </div>
    <?php else: ?>
        <form method="POST" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 sm:p-8 border-b border-slate-200">
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Seu Nome Completo</label>
                    <input type="text" name="nome_avaliador" required placeholder="Ex: João da Silva" class="bg-slate-50 border border-slate-300 text-slate-900 rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                </div>

                <h3 class="font-bold text-slate-800 text-lg mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                    <i class="ph-fill ph-list-checks text-primary-500"></i>
                    Critérios de Homologação
                </h3>

                <div class="space-y-4">
                    <?php foreach ($checklistItems as $key => $label): ?>
                        <div class="bg-slate-50 border border-slate-200 p-4 rounded-xl">
                            <span class="block text-sm font-medium text-slate-800 mb-3"><?= htmlspecialchars($label) ?></span>
                            <div class="flex gap-2 w-full">
                                <input type="radio" class="hidden peer/ok" name="checklist[<?= htmlspecialchars($key) ?>]" id="<?= htmlspecialchars($key) ?>_ok" value="1" required>
                                <label for="<?= htmlspecialchars($key) ?>_ok" class="flex-1 text-center py-2.5 px-2 text-sm font-bold bg-white text-emerald-600 border border-emerald-200 rounded-lg cursor-pointer hover:bg-emerald-50 peer-checked/ok:bg-emerald-600 peer-checked/ok:text-white peer-checked/ok:border-emerald-600">
                                    <i class="ph-bold ph-check mr-1"></i> PASS
                                </label>

                                <input type="radio" class="hidden peer/fail" name="checklist[<?= htmlspecialchars($key) ?>]" id="<?= htmlspecialchars($key) ?>_fail" value="0" required>
                                <label for="<?= htmlspecialchars($key) ?>_fail" class="flex-1 text-center py-2.5 px-2 text-sm font-bold bg-white text-rose-600 border border-rose-200 rounded-lg cursor-pointer hover:bg-rose-50 peer-checked/fail:bg-rose-600 peer-checked/fail:text-white peer-checked/fail:border-rose-600">
                                    <i class="ph-bold ph-x mr-1"></i> FAIL
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-slate-50">
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Observações</label>
                    <textarea name="observacoes_checklist" rows="3" placeholder="Comentários sobre os testes..." class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-3"></textarea>
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
