<?php 
require __DIR__ . '/_subnav.php'; 

if ($u['perfil'] !== 'compras') {
    echo "<div class='bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-4 mb-6 shadow-sm dark:bg-rose-900/20 dark:border-rose-800 dark:text-rose-300 flex items-center gap-3'><i class='ph-fill ph-warning-circle text-xl'></i> Acesso restrito. Apenas o setor de Compras pode abrir homologações. Mude o simulador de Perfil acima!</div>";
    return;
}
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-1">Nova Homologação de TI</h2>
    <p class="text-slate-500 dark:text-slate-400 text-sm">Preencha os dados do equipamento para iniciar a esteira.</p>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <form method="POST" action="" class="p-6">
        <input type="hidden" name="criar_homologacao" value="1">
        
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            <div class="col-span-12 md:col-span-8">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Título da Homologação</label>
                <input type="text" name="titulo" required placeholder="Ex: Avaliação Novo Lote Monitores Dell" 
                    class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:placeholder-slate-400 dark:text-white">
            </div>
            
            <div class="col-span-12 md:col-span-4">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Tipo de Equipamento</label>
                <select name="tipo_equipamento" required class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white">
                    <option value="">Selecione...</option>
                    <?php foreach ($tiposReais as $tr): ?>
                        <option value="<?= htmlspecialchars($tr['nome']) ?>"><?= htmlspecialchars($tr['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-span-12">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Descrição do Contexto</label>
                <textarea name="descricao" rows="3" required placeholder="Descreva por que este item está sendo homologado..."
                    class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:placeholder-slate-400 dark:text-white"></textarea>
            </div>
            
            <div class="col-span-12 mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                <h3 class="text-md font-bold flex items-center gap-2 text-slate-700 dark:text-slate-300 mb-4"><i class="ph-fill ph-cpu text-primary-500 text-lg"></i> Detalhes Técnicos</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-2">Fornecedor</label>
                        <select name="fornecedor" required class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white">
                            <option value="">Selecione...</option>
                            <?php foreach ($fornecedoresReais as $fr): ?>
                                <option value="<?= htmlspecialchars($fr) ?>"><?= htmlspecialchars($fr) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-2">Modelo</label>
                        <input type="text" name="modelo" required class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-2">Nº Série / Lote</label>
                        <input type="text" name="numero_serie" placeholder="Opcional" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white dark:placeholder-slate-500">
                    </div>
                </div>
            </div>
            
            <div class="col-span-12 mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                <h3 class="text-md font-bold flex items-center gap-2 text-slate-700 dark:text-slate-300 mb-4"><i class="ph-fill ph-truck text-amber-500 text-lg"></i> Logística e SLA</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-2">Previsão de Chegada Físico</label>
                        <input type="date" name="data_prevista_chegada" required class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-2">Informar X dias antes do vencimento</label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="dias_antecedencia_notif" value="3" min="1" max="15" required class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-24 p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white">
                            <span class="text-sm text-slate-500 dark:text-slate-400">dias antes da data prevista</span>
                        </div>
                    </div>
                    <div class="flex items-end pb-2.5">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="notificar_envolvidos" value="1" checked class="w-5 h-5 border border-slate-300 rounded bg-slate-50 text-primary-600 focus:ring-primary-500 dark:bg-slate-700 dark:border-slate-600">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300 group-hover:text-primary-600 transition-colors">Enviar notificações para todos os envolvidos</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="col-span-12 mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                <h3 class="text-md font-bold flex items-center gap-2 text-slate-700 dark:text-slate-300 mb-2"><i class="ph-fill ph-users text-cyan-500 text-lg"></i> Equipe Técnica Designada</h3>
                <p class="text-slate-500 dark:text-slate-400 text-xs mb-4">Selecione quem fará o recebimento e execução do checklist técnico (mínimo 1).</p>
                <div class="flex flex-wrap gap-4">
                    <?php foreach ($responsaveis as $resp): ?>
                        <label class="cursor-pointer relative flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="responsaveis[]" value="<?= $resp['id'] ?>" class="w-4 h-4 border border-slate-300 rounded bg-slate-50 focus:ring-3 focus:ring-primary-300 dark:bg-slate-700 dark:border-slate-600 dark:focus:ring-primary-600 dark:ring-offset-slate-800">
                            </div>
                            <div class="ml-3 text-sm">
                                <span class="font-medium text-slate-900 dark:text-slate-300"><?= $resp['nome'] ?></span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="col-span-12 mt-8 flex items-center gap-3">
                <button type="submit" class="flex items-center gap-2 text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-6 py-3 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 transition-colors shadow-sm">
                    <i class="ph-bold ph-check"></i> Abrir Processo Oficial
                </button>
                <a href="index.php" class="text-slate-500 bg-white hover:bg-slate-100 focus:ring-4 focus:outline-none focus:ring-slate-200 rounded-lg border border-slate-200 text-sm font-medium px-6 py-3 hover:text-slate-900 focus:z-10 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:text-white dark:hover:bg-slate-700 dark:focus:ring-slate-700 transition-colors shadow-sm">
                    Cancelar
                </a>
            </div>
        </div>
    </form>
</div>
