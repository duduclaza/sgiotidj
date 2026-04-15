<?php
/**
 * Toners com Defeito — View completa
 * Variáveis disponíveis: $toners_lista, $clientes_lista, $defeitos_historico
 */
?>

<style>
  @keyframes highlight-fade {
    0% { background-color: rgba(244, 63, 94, 0.2); }
    100% { background-color: transparent; }
  }
  .highlight-row {
    animation: highlight-fade 3s ease-in-out forwards;
    border-left: 4px solid #f43f5e !important;
  }
</style>

<section class="space-y-6">

  <!-- Cabeçalho -->
  <div class="mb-8 p-6 bg-white/50 dark:bg-slate-800/50 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-sm transition-all flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div class="flex items-center gap-4">
      <div class="p-3 bg-red-100 dark:bg-red-900/40 rounded-xl">
        <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
      </div>
      <div>
        <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">Toners com Defeito</h1>
        <p class="mt-1 text-slate-500 dark:text-slate-400 font-medium">Registre falhas identificadas e notifique os responsáveis automaticamente.</p>
      </div>
    </div>
    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold bg-white/80 dark:bg-slate-900/80 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900/30 shadow-sm transition-all">
      <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
      <?php echo count($defeitos_historico ?? []); ?> Registros Ativos
    </span>
  </div>

  <!-- Formulário -->
  <div class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-md rounded-2xl shadow-sm border border-slate-200/50 dark:border-slate-700/50 overflow-hidden transition-all mb-8">
    <div class="px-8 py-5 border-b border-slate-100 dark:border-slate-700/50 bg-red-50/50 dark:bg-red-900/10 flex items-center gap-3">
      <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
      <h2 class="text-lg font-extrabold text-red-800 dark:text-red-400 tracking-tight">Novo Registro de Ocorrência</h2>
    </div>

    <?php if (!empty($canEdit)): ?>
    <form id="formDefeito" class="p-8 grid grid-cols-1 md:grid-cols-12 gap-6" novalidate>
      
      <!-- Linha 1: Pedido (3), Modelo (6), Qtd (3) -->
      
      <!-- Número do Pedido -->
      <div class="md:col-span-3 flex flex-col">
        <label for="numeroPedido" class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Número do Pedido <span class="text-red-500">*</span></label>
        <div class="relative group">
          <input type="text" id="numeroPedido" name="numero_pedido"
            placeholder="Ex: 54321"
            class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-800 dark:text-gray-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none placeholder-gray-400 dark:placeholder-gray-600 group-hover:bg-white dark:group-hover:bg-slate-800 group-hover:border-gray-300 dark:group-hover:border-slate-600">
        </div>
      </div>

      <!-- Nº OS -->
      <div class="md:col-span-3 flex flex-col">
        <label for="numeroOs" class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Nº OS <span class="text-gray-400 font-normal lowercase">opcional</span></label>
        <div class="relative group">
          <input type="text" id="numeroOs" name="numero_os"
            placeholder="Ex: 10045"
            class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-800 dark:text-gray-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none placeholder-gray-400 dark:placeholder-gray-600 group-hover:bg-white dark:group-hover:bg-slate-800 group-hover:border-gray-300 dark:group-hover:border-slate-600">
        </div>
      </div>
Sync check.

      <!-- Filial -->
      <div class="md:col-span-3 flex flex-col">
        <label for="filialSelect" class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Filial <span class="text-gray-400 font-normal lowercase">opcional</span></label>
        <select id="filialSelect" name="filial_id"
          class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-800 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none group-hover:bg-white dark:group-hover:bg-slate-800 group-hover:border-gray-300 dark:group-hover:border-slate-600">
          <option value="">— Selecione —</option>
          <?php foreach ($filiais_lista ?? [] as $fil): ?>
          <option value="<?= (int)$fil['id'] ?>"><?= htmlspecialchars($fil['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Modelo do Toner (Busca) -->
      <div class="md:col-span-6 flex flex-col">
        <label for="buscaToner" class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Modelo do Toner <span class="text-red-500">*</span></label>
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400 group-focus-within:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
          <input type="text" id="buscaToner"
            placeholder="Pesquise o modelo..."
            class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg pl-10 pr-4 py-2.5 text-sm font-medium text-gray-800 dark:text-gray-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none placeholder-gray-400 dark:placeholder-gray-600 group-hover:bg-white dark:group-hover:bg-slate-800 group-hover:border-gray-300 dark:group-hover:border-slate-600"
            autocomplete="off">
            
          <!-- Select Oculto / Dropdown simulado -->
          <div id="dropdownToner" class="absolute z-10 w-full mt-1 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg shadow-xl hidden max-h-60 overflow-y-auto">
             <select id="selectToner" name="toner_id" size="5" class="w-full text-sm border-none focus:ring-0 p-1 bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-100">
                <?php foreach ($toners_lista as $t): ?>
                  <option value="<?php echo (int)$t['id']; ?>" data-label="<?php echo htmlspecialchars($t['modelo']); ?>" class="p-2 hover:bg-red-50 dark:hover:bg-red-900/10 rounded cursor-pointer">
                    <?php echo htmlspecialchars($t['modelo']); ?>
                  </option>
                <?php
endforeach; ?>
                <?php if (empty($toners_lista)): ?>
                  <option value="" disabled class="p-2 text-gray-400">Nenhum toner cadastrado</option>
                <?php
endif; ?>
             </select>
          </div>
        </div>
        <input type="hidden" id="modeloTonerHidden" name="modelo_toner">
      </div>

      <!-- Quantidade -->
      <div class="md:col-span-3 flex flex-col">
        <label for="quantidadeDefeito" class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Qtd. <span class="text-red-500">*</span></label>
        <div class="relative group">
           <input type="number" id="quantidadeDefeito" name="quantidade" value="1" min="1"
            class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm font-semibold text-gray-800 dark:text-gray-100 text-center focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none group-hover:bg-white dark:group-hover:bg-slate-800 group-hover:border-gray-300 dark:group-hover:border-slate-600">
        </div>
      </div>

      <!-- Linha 2: Cliente (12) -->
      <div class="md:col-span-12 flex flex-col">
        <label for="buscaCliente" class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Cliente <span class="text-red-500">*</span></label>
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
             <svg class="h-5 w-5 text-gray-400 group-focus-within:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          </div>
          <input type="text" id="buscaCliente"
            placeholder="Pesquise por nome ou código do cliente..."
            class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg pl-10 pr-4 py-2.5 text-sm font-medium text-gray-800 dark:text-gray-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none placeholder-gray-400 dark:placeholder-gray-600 group-hover:bg-white dark:group-hover:bg-slate-800 group-hover:border-gray-300 dark:group-hover:border-slate-600"
            autocomplete="off">

           <!-- Select Oculto / Dropdown -->
           <div id="dropdownCliente" class="absolute z-10 w-full mt-1 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg shadow-xl hidden max-h-60 overflow-y-auto transition-colors">
              <select id="selectCliente" name="cliente_id" size="5" class="w-full text-sm border-none focus:ring-0 p-1 bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-100">
                <?php foreach ($clientes_lista as $c): ?>
                  <option value="<?php echo (int)$c['id']; ?>"
                    data-label="<?php echo htmlspecialchars($c['codigo'] . ' – ' . $c['nome']); ?>"
                    data-nome="<?php echo htmlspecialchars($c['nome']); ?>"
                    class="p-2 hover:bg-red-50 dark:hover:bg-red-900/10 rounded cursor-pointer transition-colors">
                    <?php echo htmlspecialchars($c['codigo'] . ' – ' . $c['nome']); ?>
                  </option>
                <?php
endforeach; ?>
                <?php if (empty($clientes_lista)): ?>
                  <option value="" disabled class="p-2 text-gray-400">Nenhum cliente cadastrado</option>
                <?php
endif; ?>
              </select>
           </div>
        </div>
        <input type="hidden" id="clienteNomeHidden" name="cliente_nome">
      </div>

      <!-- Linha 3: Descrição (12) -->
      <div class="md:col-span-12 flex flex-col">
        <label for="descricaoDefeito" class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Descrição do Defeito <span class="text-red-500">*</span></label>
        <textarea id="descricaoDefeito" name="descricao" rows="4"
          placeholder="Descreva detalhadamente o problema (ex: Manchas na lateral, ruído ao imprimir, etc)..."
          class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg px-4 py-3 text-sm text-gray-800 dark:text-gray-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none resize-y placeholder-gray-400 dark:placeholder-gray-600 hover:bg-white dark:hover:bg-slate-800 hover:border-gray-300 dark:hover:border-slate-600"></textarea>
      </div>

      <!-- Linha 4: Fotos (12) -->
      <div class="md:col-span-12">
        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3 block">Evidências Fotográficas <span class="text-gray-400 font-normal lowercase ml-1">(opcional, máx 3)</span></label>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <?php for ($i = 1; $i <= 3; $i++): ?>
          <div class="relative group">
            <label for="foto<?php echo $i; ?>"
              class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/50 rounded-xl h-32 cursor-pointer hover:border-red-400 dark:hover:border-red-500 hover:bg-red-50/50 dark:hover:bg-red-900/10 transition-all duration-200" id="labelFoto<?php echo $i; ?>">
              
              <span id="previewFoto<?php echo $i; ?>" class="w-full h-full hidden rounded-xl overflow-hidden relative">
                <img id="imgPreview<?php echo $i; ?>" src="" alt="Prévia" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <span class="text-white text-xs font-medium bg-black/50 px-2 py-1 rounded">Trocar</span>
                </div>
              </span>
              
              <span id="placeholderFoto<?php echo $i; ?>" class="flex flex-col items-center gap-1 text-gray-400 group-hover:text-red-400 dark:group-hover:text-red-500 transition-colors">
                <svg class="w-8 h-8 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="text-xs font-medium">Foto <?php echo $i; ?></span>
              </span>
            </label>
            <input type="file" id="foto<?php echo $i; ?>" name="foto<?php echo $i; ?>" accept="image/*" class="hidden" data-index="<?php echo $i; ?>">
            <button type="button" id="removerFoto<?php echo $i; ?>"
              class="hidden absolute -top-2 -right-2 bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400 rounded-full p-1 shadow-md hover:bg-red-200 dark:hover:bg-red-900 transition-colors"
                title="Remover foto"
              onclick="removerFoto(<?php echo $i; ?>)">
               <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <?php
endfor; ?>
        </div>
      </div>

      <!-- Linha 5: Notificar Setores (12) -->
      <div class="md:col-span-12">
        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3 block">
          Notificar Setores
          <span class="text-gray-400 dark:text-gray-500 font-normal lowercase ml-1">(opcional — usuários dos setores selecionados receberão email)</span>
        </label>
        <div class="flex flex-wrap gap-2">
          <?php if (!empty($departamentos_lista)): ?>
            <?php foreach ($departamentos_lista as $dep): ?>
              <label class="inline-flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg cursor-pointer hover:bg-red-50 dark:hover:bg-red-900/10 hover:border-red-300 dark:hover:border-red-700 transition-all has-[:checked]:bg-red-50 dark:has-[:checked]:bg-red-900/20 has-[:checked]:border-red-400 dark:has-[:checked]:border-red-500 has-[:checked]:text-red-700 dark:has-[:checked]:text-red-400 select-none">
                <input type="checkbox" name="notificar_setores[]" value="<?php echo htmlspecialchars($dep['nome']); ?>"
                  class="w-4 h-4 text-red-600 bg-gray-100 dark:bg-slate-800 border-gray-300 dark:border-slate-600 rounded focus:ring-red-500 focus:ring-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($dep['nome']); ?></span>
              </label>
            <?php endforeach; ?>
          <?php else: ?>
            <span class="text-sm text-gray-400 dark:text-gray-500 italic">Nenhum departamento cadastrado.</span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Botão -->
      <div class="md:col-span-12 flex justify-end pt-4 border-t border-gray-100 dark:border-slate-700 mt-2 transition-colors">
        <button type="submit" id="btnRegistrar"
          class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white text-sm font-bold rounded-lg shadow-lg shadow-red-500/20 transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Registrar Defeito
        </button>
      </div>

    </form>
  <?php else: ?>
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center text-yellow-700 text-sm">
      <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
      Voce nao tem permissao para registrar defeitos.
    </div>
  <?php endif; ?>
  </div>

  <!-- ======================= HISTÓRICO ======================= -->
  <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden transition-colors">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
      <h2 class="text-base font-semibold text-gray-800 dark:text-white">Histórico de Registros</h2>
      <span class="text-sm text-gray-400 dark:text-gray-500"><?php echo count($defeitos_historico ?? []); ?> registro(s)</span>
    </div>

    <!-- Barra de busca -->
    <div class="px-8 py-4 border-b border-slate-100 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-900/30 flex items-center gap-4">
      <div class="relative flex-1 max-w-lg group">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-red-500 transition-colors">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <input type="text" id="buscaHistorico" placeholder="Buscar por modelo, pedido, cliente, filial..."
          class="w-full pl-11 pr-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none transition-all text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500"
          oninput="filtrarHistorico()">
      </div>
      <div id="contadorResultados" class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest bg-slate-100 dark:bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-200/50 dark:border-slate-700/50"></div>
    </div>

    <?php if (empty($defeitos_historico)): ?>
    <div class="py-14 text-center">
      <p class="text-gray-400 text-sm">Nenhum toner com defeito registrado ainda.</p>
    </div>
    <?php
else: ?>
    <div class="overflow-x-auto ring-1 ring-slate-100 dark:ring-slate-700/50 rounded-b-2xl mx-8 mb-8">
      <table class="min-w-full text-sm divide-y divide-slate-100 dark:divide-slate-700/50" id="tabelaHistorico">
        <thead>
          <tr class="bg-slate-50/80 dark:bg-slate-900/80 backdrop-blur-md">
            <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ocorrência</th>
            <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Identificação</th>
            <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cliente/Unidade</th>
            <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Descrição Falha</th>
            <th class="px-5 py-3 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Qtd</th>
            <th class="px-5 py-3 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Evidências</th>
            <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Autor</th>
            <th class="px-5 py-3 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider font-bold text-red-600">Devolutiva</th>
            <th class="px-5 py-3 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50 dark:divide-slate-700/30">
          <?php foreach ($defeitos_historico as $d): ?>
          <tr id="defeito-<?php echo $d['id']; ?>" 
              class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors defeito-row"
              data-busca="<?= strtolower(htmlspecialchars(
                  ($d['modelo_toner'] ?? '') . ' ' .
                  ($d['numero_pedido'] ?? '') . ' ' .
                  ($d['numero_os'] ?? '') . ' ' .
                  ($d['cliente_nome'] ?? '') . ' ' .
                  ($d['filial_nome'] ?? '') . ' ' .
                  ($d['descricao'] ?? '')
              )) ?>">
            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap transition-colors">
              <?php echo date('d/m/Y H:i', strtotime($d['created_at'])); ?>
            </td>
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap transition-colors">
              <?php echo htmlspecialchars($d['modelo_toner']); ?>
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap transition-colors">
              <?php echo htmlspecialchars($d['numero_pedido']); ?>
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap transition-colors">
              <?php echo $d['numero_os'] ? htmlspecialchars($d['numero_os']) : '<span class="text-gray-300 dark:text-gray-600">—</span>'; ?>
            </td>
            <td class="px-4 py-3 whitespace-nowrap transition-colors">
              <?php if (!empty($d['filial_nome'])): ?>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-900/50 transition-colors">
                  <?= htmlspecialchars($d['filial_nome']) ?>
                </span>
              <?php else: ?>
                <span class="text-gray-300 dark:text-gray-600">—</span>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap transition-colors">
              <?php echo htmlspecialchars($d['cliente_nome']); ?>
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 min-w-[200px] break-words transition-colors">
              <?php echo nl2br(htmlspecialchars($d['descricao'])); ?>
            </td>
            <td class="px-4 py-3 text-center font-medium text-gray-700 dark:text-gray-300 transition-colors">
               <?php echo (int)($d['quantidade'] ?? 1); ?>
            </td>
            <td class="px-4 py-3 text-center">
              <div class="flex items-center justify-center gap-1.5">
                <?php for ($n = 1; $n <= 3; $n++): ?>
                  <?php $fotoNome = $d["foto{$n}_nome"]; ?>
                  <?php if (!empty($fotoNome)): ?>
                  <a href="/toners/defeitos/<?php echo (int)$d['id']; ?>/foto/<?php echo $n; ?>"
                     target="_blank"
                     title="<?php echo htmlspecialchars($fotoNome); ?>"
                     class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 transition-colors border border-blue-100 dark:border-blue-900/50 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  </a>
                  <?php
      else: ?>
                  <span class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-slate-900 flex items-center justify-center text-gray-300 dark:text-gray-700 border border-gray-100 dark:border-slate-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                  </span>
                  <?php
      endif; ?>
                <?php
    endfor; ?>
              </div>
            </td>
            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap transition-colors">
              <?php echo htmlspecialchars($d['registrado_por_nome'] ?? '—'); ?>
            </td>
            <td class="px-4 py-3 text-center transition-colors">
                <?php if (!empty($d['devolutiva_descricao'])): ?>
                <div class="flex flex-col items-center gap-1">
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-900/50 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Respondida
                  </span>
                  <?php
    $res = $d['devolutiva_resultado'] ?? '';
    if ($res !== ''):
?>
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-900/50 transition-colors">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <?php echo htmlspecialchars($res); ?>
                  </span>
                  <?php endif; ?>
 
                  <span class="text-[10px] text-gray-400 dark:text-gray-500 transition-colors" title="<?php echo htmlspecialchars($d['devolutiva_descricao']); ?>">
                    <?php echo htmlspecialchars(mb_strimwidth($d['devolutiva_descricao'], 0, 40, '…')); ?>
                  </span>
                  <?php if (!empty($d['devolutiva_por_nome'])): ?>
                    <span class="text-[10px] text-gray-400 dark:text-gray-500 transition-colors">
                      por <?php echo htmlspecialchars($d['devolutiva_por_nome']); ?>
                      <?php if (!empty($d['devolutiva_at'])): ?>
                        em <?php echo date('d/m/Y', strtotime($d['devolutiva_at'])); ?>
                      <?php endif; ?>
                    </span>
                  <?php endif; ?>
                </div>
              <?php
    else: ?>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-gray-400 dark:text-gray-500 transition-colors">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  Pendente
                </span>
              <?php
    endif; ?>
            </td>
            <td class="px-4 py-3 text-center whitespace-nowrap">
              <div class="flex items-center justify-center gap-2">
                  <?php
    $hasDevolutiva = !empty($d['devolutiva_at']) || !empty($d['devolutiva_descricao']);
    $userSetor = $_SESSION['user_setor'] ?? '';
    $userRole = $_SESSION['user_role'] ?? '';
    $isQualidade = stripos($userSetor, 'Qualidade') !== false;
    $isAdmin = in_array($userRole, ['admin', 'super_admin']);
    $canInsert = ($isQualidade || $isAdmin);
?>
                    <!-- Ver Devolutiva - SEMPRE VISÍVEL para todos -->
                    <button type="button" onclick="openDevolutiva(<?php echo $d['id']; ?>, 'view', this)"
                            data-desc="<?php echo htmlspecialchars($d['devolutiva_descricao'] ?? ''); ?>"
                            data-resultado="<?php echo htmlspecialchars($d['devolutiva_resultado'] ?? ''); ?>"
                            data-devolutiva-por="<?php echo htmlspecialchars($d['devolutiva_por_nome'] ?? ''); ?>"
                            data-devolutiva-at="<?php echo !empty($d['devolutiva_at']) ? date('d/m/Y H:i', strtotime($d['devolutiva_at'])) : ''; ?>"
                            data-has-devolutiva="<?php echo $hasDevolutiva ? '1' : '0'; ?>"
                            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg <?php echo $hasDevolutiva ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50 hover:text-blue-800 dark:hover:text-blue-200 border border-blue-200 dark:border-blue-800 transition-colors shadow-sm' : 'bg-gray-50 dark:bg-slate-900 text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700 hover:text-gray-600 dark:hover:text-gray-300 border border-gray-200 dark:border-slate-700 transition-colors shadow-none'; ?>" title="Ver Devolutiva">
                       <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                       <span class="text-xs font-medium">Ver</span>
                    </button>

                  <?php if ($hasDevolutiva && $canInsert): ?>
                    <!-- Editar Devolutiva (só Qualidade/Admin) -->
                    <button type="button" onclick="openDevolutiva(<?php echo $d['id']; ?>, 'edit', this)"
                            data-desc="<?php echo htmlspecialchars($d['devolutiva_descricao'] ?? ''); ?>"
                            data-resultado="<?php echo htmlspecialchars($d['devolutiva_resultado'] ?? ''); ?>"
                            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 hover:bg-yellow-100 dark:hover:bg-yellow-900/50 hover:text-yellow-800 dark:hover:text-yellow-200 transition-colors border border-yellow-200 dark:border-yellow-800 shadow-sm" title="Editar Devolutiva">
                       <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                  <?php elseif (!$hasDevolutiva && $canInsert): ?>
                    <!-- Inserir Devolutiva (só Qualidade/Admin) -->
                    <button type="button" onclick="openDevolutiva(<?php echo $d['id']; ?>, 'create', this)"
                            data-resultado=""
                            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/50 hover:text-green-800 dark:hover:text-green-200 transition-colors border border-green-200 dark:border-green-800 shadow-sm" title="Inserir Devolutiva">
                       <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                       <span class="text-xs font-medium">Devolutiva</span>
                    </button>
                  <?php endif; ?>

                  <button type="button" onclick="excluirDefeito(<?php echo $d['id']; ?>)"
                     class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors" title="Excluir Registro">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
              </div>
            </td>
          </tr>
          <?php
  endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php
endif; ?>
  </div>

</section>

<!-- Toast de feedback -->
<div id="toastDefeito"
  class="fixed bottom-6 right-6 z-50 hidden max-w-sm bg-white dark:bg-slate-800 border dark:border-slate-700 rounded-xl shadow-lg px-5 py-4 flex items-start gap-3 transition-all duration-300">
  <span id="toastIconDefeito" class="mt-0.5 shrink-0 w-5 h-5 transition-colors"></span>
  <div>
    <p id="toastTituloDefeito" class="text-sm font-semibold text-gray-800 dark:text-white transition-colors"></p>
    <p id="toastMsgDefeito" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 transition-colors"></p>
  </div>
</div>

<script>

// =====================================================
// Autocomplete / Dropdown Logic (Toner & Cliente)
// =====================================================

function setupAutocomplete(inputId, dropdownId, selectId, hiddenInputId, onSelectCallback) {
  const input    = document.getElementById(inputId);
  const dropdown = document.getElementById(dropdownId);
  const select   = document.getElementById(selectId);
  const hidden   = document.getElementById(hiddenInputId);
  
  if (!input || !dropdown || !select) return;

  function filterOptions() {
    const q = input.value.toLowerCase();
    let visibleCount = 0;
    
    Array.from(select.options).forEach(opt => {
      const text  = (opt.dataset.label || opt.text).toLowerCase();
      // Ocultar se não der match E não for a opção padrão disabled
      const match = text.includes(q);
      
      if (opt.disabled) {
          opt.style.display = match ? '' : 'none'; // Mostrar disabled message se for relevante? Geralmente não.
      } else {
          opt.style.display = match ? '' : 'none';
          if (match) visibleCount++;
      }
    });
    
    // Se digitou algo e tem resultados, mostrar
    if (visibleCount > 0) {
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
  }

  // Mostrar ao focar
  input.addEventListener('focus', () => {
      filterOptions();
      dropdown.classList.remove('hidden');
  });

  // Filtrar ao digitar
  input.addEventListener('input', () => {
      filterOptions();
  });

  // Selecionar ao clicar na option (ou change no select)
  // O evento 'change' dispara quando clica numa option em size > 1
  select.addEventListener('change', () => {
      selectOption();
  });
  
  // Garantir clique direto (desktop)
  select.addEventListener('click', (e) => {
      if (e.target.tagName === 'OPTION' && !e.target.disabled) {
          selectOption();
      }
  });

  function selectOption() {
      const opt = select.options[select.selectedIndex];
      if (opt && !opt.disabled) {
          input.value = opt.dataset.label || opt.text;
          // Se for select de ID (value=int), salvar no hidden se necessário ou confiar no submit do select
          // Mas cuidado: se o user digitar algo que não existe, o select não muda.
          // O input visual é só busca. O que vale é o select.
          
          if (hidden) hidden.value = opt.dataset.label || opt.dataset.nome || '';
          
          if (onSelectCallback) onSelectCallback(opt);
          
          dropdown.classList.add('hidden');
      }
  }

  // Ocultar ao sair (com delay para permitir clique)
  input.addEventListener('blur', () => {
      setTimeout(() => {
          dropdown.classList.add('hidden');
      }, 200);
  });
}

// Configurar Toner
setupAutocomplete('buscaToner', 'dropdownToner', 'selectToner', 'modeloTonerHidden', (opt) => {
    // Ao selecionar toner, atualizar o campo hidden com o NOME do modelo (redundância)
    document.getElementById('modeloTonerHidden').value = opt.dataset.label;
});

// Configurar Cliente
setupAutocomplete('buscaCliente', 'dropdownCliente', 'selectCliente', 'clienteNomeHidden', (opt) => {
    // Ao selecionar cliente, atualizar nome hidden
    document.getElementById('clienteNomeHidden').value = opt.dataset.nome;
});


// =====================================================
// Preview de fotos
// =====================================================
[1, 2, 3].forEach(i => {
  const input   = document.getElementById('foto' + i);
  const preview = document.getElementById('previewFoto' + i);
  const img     = document.getElementById('imgPreview' + i);
  const holder  = document.getElementById('placeholderFoto' + i);
  const btnRem  = document.getElementById('removerFoto' + i);

  input.addEventListener('change', () => {
    const file = input.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) {
      showToast('error', 'Arquivo inválido', 'Apenas imagens são permitidas.');
      input.value = '';
      return;
    }
    if (file.size > 16 * 1024 * 1024) {
      showToast('error', 'Arquivo muito grande', 'Máximo 16 MB por foto.');
      input.value = '';
      return;
    }
    const reader = new FileReader();
    reader.onload = e => {
      img.src = e.target.result;
      preview.classList.remove('hidden');
      holder.classList.add('hidden');
      btnRem.classList.remove('hidden');
      // Remover borda tracejada ao ter foto? Opcional
    };
    reader.readAsDataURL(file);
  });
});

function removerFoto(i) {
  const input   = document.getElementById('foto' + i);
  const preview = document.getElementById('previewFoto' + i);
  const img     = document.getElementById('imgPreview' + i);
  const holder  = document.getElementById('placeholderFoto' + i);
  const btnRem  = document.getElementById('removerFoto' + i);
  input.value   = '';
  img.src       = '';
  preview.classList.add('hidden');
  holder.classList.remove('hidden');
  btnRem.classList.add('hidden');
}

// =====================================================
// Envio do formulário
// =====================================================
// Permissoes injetadas pelo PHP
const canDelete = <?php echo json_encode(!empty($canDelete)); ?>;

// Ocultar botoes de exclusao se sem permissao
if (!canDelete) {
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[onclick*="excluirDefeito"]').forEach(btn => {
      btn.closest('.group, td, div')?.querySelector('[onclick*="excluirDefeito"]')?.remove() || btn.remove();
    });
  });
}
document.getElementById('formDefeito').addEventListener('submit', async (e) => {
  e.preventDefault();

  const btn = document.getElementById('btnRegistrar');
  const selectToner = document.getElementById('selectToner');
  const selectCliente = document.getElementById('selectCliente');
  
  // Validação: Verificar se um item foi realmente selecionado no SELECT
  // Se o usuário só digitou e não selecionou, o selectValue pode estar vazio ou errado.
  // Entretanto, se o usuário não selecionou nada, o select.value será '' (se tiver option disabled selected) ou o primeiro.
  // Vamos forçar a validação: o texto do input busca deve bater com o selecionado? Não necessariamente.
  // Vamos confiar no select.value.

  if (!selectToner.value) {
    showToast('error', 'Campo obrigatório', 'Selecione um modelo de toner na lista.'); return;
  }
  if (!document.getElementById('numeroPedido').value.trim()) {
    showToast('error', 'Campo obrigatório', 'Informe o número do pedido.'); return;
  }
  if (!selectCliente.value) {
    showToast('error', 'Campo obrigatório', 'Selecione um cliente na lista.'); return;
  }
  if (!document.getElementById('descricaoDefeito').value.trim()) {
    showToast('error', 'Campo obrigatório', 'Informe a descrição do defeito.'); return;
  }

  btn.disabled = true;
  btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Registrando...';

  try {
    const fd = new FormData(e.target);
    // Garantir valores hidden corretos
    fd.set('modelo_toner', document.getElementById('modeloTonerHidden').value);
    fd.set('cliente_nome', document.getElementById('clienteNomeHidden').value);

    const resp = await fetch('/toners/defeitos/store', { method: 'POST', body: fd });
    const data = await resp.json();

    if (data.success) {
      // Usar o novo overlay bonito no centro
      showSuccessOverlay(data.message);
      
      e.target.reset();
      // Resetar previews
      [1, 2, 3].forEach(i => removerFoto(i));
      // Resetar selects e inputs visuais
      selectToner.selectedIndex  = -1; // Desmarcar
      selectCliente.selectedIndex = -1;
      document.getElementById('buscaToner').value = '';
      document.getElementById('buscaCliente').value = '';
    } else {
      showToast('error', 'Erro ao registrar', data.message ?? 'Tente novamente.');
    }
  } catch (err) {
    showToast('error', 'Erro de conexão', 'Não foi possível enviar o formulário.' + err);
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Registrar Toner com Defeito';
  }
});

// =====================================================
// Busca inteligente no Histórico
// =====================================================
function filtrarHistorico() {
  const q = (document.getElementById('buscaHistorico')?.value || '').toLowerCase().trim();
  const rows = document.querySelectorAll('.defeito-row');
  let visivel = 0;

  rows.forEach(row => {
    const texto = (row.getAttribute('data-busca') || '').toLowerCase();
    const mostrar = !q || texto.includes(q);
    row.style.display = mostrar ? '' : 'none';
    if (mostrar) visivel++;
  });

  const contador = document.getElementById('contadorResultados');
  if (contador) {
    contador.textContent = q ? `${visivel} resultado(s) encontrado(s)` : '';
  }
}

// =====================================================
// Excluir Defeito
// =====================================================
async function excluirDefeito(id) {
  if (!confirm('Tem certeza que deseja excluir este registro? A ação não poderá ser desfeita.')) return;

  try {
    const resp = await fetch('/toners/defeitos/delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    });
    const data = await resp.json();

    if (data.success) {
      showToast('success', 'Excluído', data.message);
      setTimeout(() => location.reload(), 1000);
    } else {
      showToast('error', 'Erro', data.message);
    }
  } catch (err) {
    showToast('error', 'Erro', 'Falha na conexão ao tentar excluir.');
  }
}

// =====================================================
// Toast helper
// =====================================================
function showToast(type, titulo, msg) {
  const toast = document.getElementById('toastDefeito');
  const icon  = document.getElementById('toastIconDefeito');
  const tit   = document.getElementById('toastTituloDefeito');
  const txt   = document.getElementById('toastMsgDefeito');

  tit.textContent = titulo;
  txt.textContent = msg;

  if (type === 'success') {
    toast.className = toast.className.replace(/border-\S+/, '');
    toast.classList.add('border-green-200');
    icon.innerHTML  = '<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
  } else {
    toast.classList.remove('border-green-200');
    toast.classList.add('border-red-200');
    icon.innerHTML  = '<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>';
  }

  toast.classList.remove('hidden');
  setTimeout(() => toast.classList.add('hidden'), 5000);
}

// =====================================================
// Success Overlay (New Registration)
// =====================================================
function showSuccessOverlay(msg) {
  const text = msg || 'Registro de toner com defeito realizado com sucesso!';

  // Criar overlay dinamicamente no document.body para escapar do page-transition
  const overlay = document.createElement('div');
  overlay.id = 'successOverlayDynamic';
  overlay.style.cssText = 'position:fixed;inset:0;z-index:1000000;display:flex;align-items:center;justify-content:center;background:rgba(15,23,42,0.6);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);opacity:0;transition:opacity 0.4s ease;';

  overlay.innerHTML = `
    <div style="background:#fff;border-radius:1.5rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);padding:2.5rem;display:flex;flex-direction:column;align-items:center;gap:1.5rem;max-width:22rem;width:calc(100% - 2rem);border:1px solid #dcfce7;transform:scale(0.9);transition:transform 0.4s cubic-bezier(0.34,1.56,0.64,1);">
      <div style="width:5rem;height:5rem;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;position:relative;">
        <div style="position:absolute;inset:0;background:rgba(34,197,94,0.2);border-radius:50%;animation:pulse 1.5s ease-in-out infinite;"></div>
        <svg width="48" height="48" fill="none" stroke="#16a34a" viewBox="0 0 24 24" style="position:relative;z-index:1;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
      </div>
      <div style="text-align:center;">
        <h3 style="font-size:1.5rem;font-weight:900;color:#1e293b;margin:0 0 0.5rem 0;line-height:1.2;">Sucesso!</h3>
        <p style="font-size:1.125rem;font-weight:700;color:#16a34a;margin:0;">${text}</p>
        <p style="color:#94a3b8;font-size:0.875rem;margin-top:1rem;font-style:italic;">O hist\u00f3rico est\u00e1 sendo atualizado...</p>
      </div>
      <div style="width:100%;height:4px;background:#f1f5f9;border-radius:2px;overflow:hidden;">
        <div id="successProgressBar" style="height:100%;width:0%;background:linear-gradient(90deg,#22c55e,#16a34a);border-radius:2px;transition:width 2.5s linear;"></div>
      </div>
    </div>
  `;

  document.body.appendChild(overlay);
  document.body.style.overflow = 'hidden';

  // Animar entrada
  requestAnimationFrame(() => {
    overlay.style.opacity = '1';
    const card = overlay.querySelector('div');
    if (card) card.style.transform = 'scale(1)';
    const bar = document.getElementById('successProgressBar');
    if (bar) setTimeout(() => { bar.style.width = '100%'; }, 80);
  });

  // Fechar e recarregar
  setTimeout(() => {
    overlay.style.opacity = '0';
    setTimeout(() => location.reload(), 500);
  }, 2700);
}
</script>

<!-- Modal Devolutiva -->
<!-- Overlay + Container fixo -->
<div id="modalDevolutiva" class="fixed inset-0 z-[99999] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="z-index: 99999;">
  
  <!-- Overlay Backdrop -->
  <div class="fixed inset-0 bg-gray-900 bg-opacity-75 dark:bg-opacity-90 transition-opacity backdrop-blur-sm pointer-events-auto" aria-hidden="true" onclick="closeDevolutiva()"></div>

  <!-- Centralização -->
  <div class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6 pointer-events-none">
      
      <!-- Modal Card -->
      <div class="pointer-events-auto relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden transform transition-all ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 transition-colors">
        
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-800 shrink-0 transition-colors">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="modal-title">Devolutiva (Qualidade)</h3>
            <button type="button" onclick="closeDevolutiva()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors bg-white dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 border border-transparent hover:border-gray-200 dark:hover:border-slate-600">
                <span class="sr-only">Fechar</span>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-6 scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-slate-700 bg-white dark:bg-slate-900 transition-colors">
            <form id="formDevolutiva">
                <input type="hidden" name="defeito_id" id="devolutivaDefeitoId">
                
                <div class="mb-5 p-3.5 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-900/50 rounded-lg flex gap-3 text-sm text-blue-700 dark:text-blue-400" id="devolutivaModeText">
                    <svg class="w-5 h-5 shrink-0 text-blue-400 dark:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Insira a análise técnica e evidências.</span>
                </div>

                <div class="space-y-6">
                    <!-- Classificação do Resultado -->
                    <div id="devolutivaResultadoDiv">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 transition-colors" for="devolutivaResultadoInput">Classificação (Defeito) <span class="text-red-500" id="devolutivaResultadoObr">*</span></label>
                        <select name="devolutiva_resultado" id="devolutivaResultadoInput"
                            class="w-full bg-gray-50 dark:bg-slate-950 border border-gray-200 dark:border-slate-800 rounded-lg px-4 py-2 text-sm font-medium text-gray-800 dark:text-gray-100 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                            <option value="">— Selecione a classificação —</option>
                            <!-- Hardcoded fallback defaults from before, so old ones work logically -->
                            <option value="TONER_SEM_DEFEITO">TONER SEM DEFEITO / USO INTERNO</option>
                            <!-- List from DB -->
                            <?php foreach ($defeitos_lista ?? [] as $def): ?>
                                <option value="<?php echo htmlspecialchars($def['nome_defeito']); ?>"><?php echo htmlspecialchars($def['nome_defeito']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span id="devolutivaResultadoBadge" style="display:none;" class="inline-flex items-center gap-1 px-3 py-1 rounded-md text-xs font-bold border"></span>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5 transition-colors">Descrição Técnica</label>
                        <textarea name="devolutiva_descricao" id="devolutivaDesc" rows="5" 
                            class="block w-full rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-950 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600 resize-none transition-all"
                            placeholder="Descreva detalhadamente a análise realizada..."
                            required></textarea>
                    </div>
                    
                    <div id="devolutivaUploads">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 transition-colors">Evidências (Até 3 fotos)</label>
                        <div class="grid grid-cols-3 gap-3">
                            <?php for ($i = 1; $i <= 3; $i++): ?>
                            <div class="relative group aspect-square">
                                <label class="block w-full h-full border-2 border-dashed border-gray-300 dark:border-slate-800 rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-red-500 dark:hover:border-red-500 hover:bg-red-50/30 dark:hover:bg-red-900/10 transition-all overflow-hidden bg-gray-50/50 dark:bg-slate-950/50">
                                    <input type="file" name="devolutiva_foto<?php echo $i; ?>" id="devFoto<?php echo $i; ?>" accept="image/*" class="hidden" onchange="previewDevFoto(this, <?php echo $i; ?>)">
                                    
                                    <div id="devPlaceholder<?php echo $i; ?>" class="text-center p-2 transition-transform group-hover:scale-105">
                                        <div class="w-8 h-8 mx-auto mb-1.5 rounded-full bg-gray-100 flex items-center justify-center group-hover:bg-white group-hover:shadow-sm transition-all">
                                            <svg class="h-4 w-4 text-gray-400 group-hover:text-red-500" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        </div>
                                        <span class="block text-xs font-medium text-gray-500 group-hover:text-gray-700">Foto <?php echo $i; ?></span>
                                    </div>
                                    
                                    <img id="devPreview<?php echo $i; ?>" class="hidden w-full h-full object-cover">
                                    
                                    <button type="button" onclick="event.preventDefault(); removerPreview(<?php echo $i; ?>)" id="btnRemoveDev<?php echo $i; ?>" class="hidden absolute top-1 right-1 bg-white rounded-full p-1 shadow-md hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors z-10">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </label>
                            </div>
                            <?php
endfor; ?>
                        </div>
                    </div>
                    
                    <div id="devolutivaLinks" class="hidden grid grid-cols-3 gap-3">
                       <!-- Links injetados via JS -->
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-slate-800/50 border-t border-gray-100 dark:border-slate-800 flex justify-end gap-3 shrink-0 rounded-b-xl transition-colors">
              <button type="button" onclick="closeDevolutiva()" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-gray-900 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 dark:focus:ring-slate-700 shadow-sm transition-all">
                Cancelar
              </button>
              <button type="submit" form="formDevolutiva" id="btnSaveDevolutiva" class="px-6 py-2.5 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg text-sm font-semibold hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-lg shadow-red-500/20 transition-all transform hover:-translate-y-0.5 active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed">
                Salvar Devolutiva
              </button>
        </div>

      </div>
  </div>
</div>

<script>
function openDevolutiva(id, mode, btn) {
    const modal = document.getElementById('modalDevolutiva');
    const descInput = document.getElementById('devolutivaDesc');
    const idInput = document.getElementById('devolutivaDefeitoId');
    const uploadDiv = document.getElementById('devolutivaUploads');
    const linksDiv = document.getElementById('devolutivaLinks');
    const btnSave = document.getElementById('btnSaveDevolutiva');
    const modeText = document.getElementById('devolutivaModeText');
    const resultadoInput = document.getElementById('devolutivaResultadoInput');
    const resultadoBadge = document.getElementById('devolutivaResultadoBadge');
    
    // Move modal to body to escape sidebar stacking context
    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    
    idInput.value = id;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Get Data
    const desc = btn.getAttribute('data-desc') || '';
    const resultado = btn.getAttribute('data-resultado') || '';
    descInput.value = desc;
    
    // Reset Photos
    for(let i=1; i<=3; i++) {
        const img = document.getElementById('devPreview'+i);
        const holder = document.getElementById('devPlaceholder'+i);
        const input = document.getElementById('devFoto'+i);
        img.classList.add('hidden');
        img.src = '';
        holder.classList.remove('hidden');
        input.value = '';
    }
    
    if (mode === 'view') {
        descInput.disabled = true;
        uploadDiv.classList.add('hidden');
        btnSave.classList.add('hidden');
        
        // Hide select, show badge
        resultadoInput.style.display = 'none';
        resultadoInput.disabled = true;
        resultadoBadge.style.display = 'inline-flex';
        
        // Render resultado badge with dynamic text
        if (resultado) {
            resultadoBadge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 transition-colors shadow-sm';
            resultadoBadge.innerHTML = `<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>${resultado}`;
        } else {
            resultadoBadge.className = 'text-xs text-gray-400 dark:text-gray-500 italic transition-colors';
            resultadoBadge.innerHTML = 'Não classificado';
        }
        
        const hasDev = btn.getAttribute('data-has-devolutiva') === '1';
        const devPor = btn.getAttribute('data-devolutiva-por') || '';
        const devAt = btn.getAttribute('data-devolutiva-at') || '';
        
        if (hasDev && desc) {
            linksDiv.classList.remove('hidden');
            let metaInfo = 'Visualizando devolutiva registrada.';
            if (devPor) {
                metaInfo += ' Registrada por ' + devPor;
                if (devAt) metaInfo += ' em ' + devAt;
                metaInfo += '.';
            }
            modeText.querySelector('span').innerText = metaInfo;
        
            let linksHtml = '';
            for(let i=1; i<=3; i++) {
                linksHtml += `<a href="/toners/defeitos/${id}/devolutiva-foto/${i}" target="_blank" class="block w-full aspect-square bg-gray-50 rounded flex flex-col items-center justify-center text-xs text-blue-500 hover:bg-blue-50 border border-gray-200 hover:border-blue-300 transition-colors">
                                <svg class="w-6 h-6 mb-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Ver Foto ${i}
                              </a>`;
            }
            linksDiv.innerHTML = linksHtml;
        } else {
            linksDiv.classList.add('hidden');
            descInput.value = 'Nenhuma devolutiva registrada para este defeito.';
            modeText.querySelector('span').innerText = 'Este defeito ainda não possui devolutiva registrada.';
        }
        
    } else {
        descInput.disabled = false;
        uploadDiv.classList.remove('hidden');
        linksDiv.classList.add('hidden');
        btnSave.classList.remove('hidden');
        
        // Show select, hide badge
        resultadoInput.style.display = 'block';
        resultadoInput.disabled = false;
        
        // Find exact or nearest match in the options
        let matched = false;
        for (let idx = 0; idx < resultadoInput.options.length; idx++) {
            if (resultadoInput.options[idx].value === resultado) {
                resultadoInput.selectedIndex = idx;
                matched = true;
                break;
            }
        }
        if (!matched && resultado !== '') {
            // fallback if it's an old string no longer in DB
            const opt = document.createElement('option');
            opt.value = resultado;
            opt.textContent = resultado;
            resultadoInput.appendChild(opt);
            resultadoInput.value = resultado;
        } else if (!matched) {
            resultadoInput.value = '';
        }
        
        resultadoBadge.style.display = 'none';
        
        if (mode === 'edit') {
             modeText.querySelector('span').innerText = 'Editando devolutiva existente. Faça upload de novas fotos para substituir as antigas.';
        } else {
             modeText.querySelector('span').innerText = 'Insira a análise técnica e evidências.';
        }
    }
}

function closeDevolutiva() {
    document.getElementById('modalDevolutiva').classList.add('hidden');
    document.body.style.overflow = '';
}

function previewDevFoto(input, i) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById('devPreview'+i);
            const placeholder = document.getElementById('devPlaceholder'+i);
            const btn = document.getElementById('btnRemoveDev'+i);
            
            img.src = e.target.result;
            img.classList.remove('hidden');
            placeholder.classList.add('hidden');
            
            if(btn) btn.classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function removerPreview(i) {
    const input = document.getElementById('devFoto'+i);
    const img = document.getElementById('devPreview'+i);
    const placeholder = document.getElementById('devPlaceholder'+i);
    const btn = document.getElementById('btnRemoveDev'+i);
    
    input.value = ''; // Reset file input
    img.src = '';
    img.classList.add('hidden');
    placeholder.classList.remove('hidden');
    
    if(btn) btn.classList.add('hidden');
}

document.getElementById('formDevolutiva').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('btnSaveDevolutiva');
    const originalText = btn.innerText;
    
    btn.disabled = true;
    btn.innerText = 'Salvando...';
    
    try {
        const fd = new FormData(e.target);
        const resp = await fetch('/toners/defeitos/devolutiva/store', { method: 'POST', body: fd });
        const data = await resp.json();
        
        if (data.success) {
            showToast('success', 'Sucesso', data.message);
            closeDevolutiva();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', 'Erro', data.message);
        }
    } catch (err) {
        showToast('error', 'Erro', 'Falha na conexão.');
    } finally {
        btn.disabled = false;
        btn.innerText = originalText;
    }
});

// Highlight and Scroll to record if ID is provided in URL
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const targetId = urlParams.get('id');
    
    if (targetId) {
        const row = document.getElementById('defeito-' + targetId);
        if (row) {
            setTimeout(() => {
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                row.classList.add('highlight-row');
            }, 500);
        }
    }
});
</script>

