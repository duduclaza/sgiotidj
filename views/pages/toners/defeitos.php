<?php
/**
 * Toners com Defeito — View completa
 * Variáveis disponíveis: $toners_lista, $clientes_lista, $defeitos_historico
 */
?>

<section class="space-y-6">

  <!-- Cabeçalho -->
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
    <div>
      <h1 class="text-2xl font-semibold text-gray-900">Toners com Defeito</h1>
      <p class="text-sm text-gray-500 mt-0.5">Registre toners com defeito identificados e notifique os administradores automaticamente.</p>
    </div>
    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
      <?php echo count($defeitos_historico ?? []); ?> registros
    </span>
  </div>

  <!-- ======================= FORMULÁRIO ======================= -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 bg-red-50 flex items-center gap-2">
      <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
      <h2 class="text-base font-semibold text-red-800">Registrar Toner com Defeito</h2>
    </div>

    <?php if (!empty($canEdit)): ?>
    <form id="formDefeito" class="p-8 grid grid-cols-1 md:grid-cols-12 gap-6" novalidate>
      
      <!-- Linha 1: Pedido (3), Modelo (6), Qtd (3) -->
      
      <!-- Número do Pedido -->
      <div class="md:col-span-3 flex flex-col">
        <label for="numeroPedido" class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Número do Pedido <span class="text-red-500">*</span></label>
        <div class="relative group">
          <input type="text" id="numeroPedido" name="numero_pedido"
            placeholder="Ex: 54321"
            class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-800 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none placeholder-gray-400 group-hover:bg-white group-hover:border-gray-300">
        </div>
      </div>

      <!-- Nº OS -->
      <div class="md:col-span-3 flex flex-col">
        <label for="numeroOs" class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Nº OS <span class="text-gray-400 font-normal lowercase">opcional</span></label>
        <div class="relative group">
          <input type="text" id="numeroOs" name="numero_os"
            placeholder="Ex: 10045"
            class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-800 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none placeholder-gray-400 group-hover:bg-white group-hover:border-gray-300">
        </div>
      </div>

      <!-- Filial -->
      <div class="md:col-span-3 flex flex-col">
        <label for="filialSelect" class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Filial <span class="text-gray-400 font-normal lowercase">opcional</span></label>
        <select id="filialSelect" name="filial_id"
          class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-800 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none group-hover:bg-white group-hover:border-gray-300">
          <option value="">— Selecione —</option>
          <?php foreach ($filiais_lista ?? [] as $fil): ?>
          <option value="<?= (int)$fil['id'] ?>"><?= htmlspecialchars($fil['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Modelo do Toner (Busca) -->
      <div class="md:col-span-6 flex flex-col">
        <label for="buscaToner" class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Modelo do Toner <span class="text-red-500">*</span></label>
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400 group-focus-within:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
          <input type="text" id="buscaToner"
            placeholder="Pesquise o modelo..."
            class="w-full bg-gray-50 border border-gray-200 rounded-lg pl-10 pr-4 py-2.5 text-sm font-medium text-gray-800 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none placeholder-gray-400 group-hover:bg-white group-hover:border-gray-300"
            autocomplete="off">
            
          <!-- Select Oculto / Dropdown simulado -->
          <div id="dropdownToner" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-xl hidden max-h-60 overflow-y-auto">
             <select id="selectToner" name="toner_id" size="5" class="w-full text-sm border-none focus:ring-0 p-1">
                <?php foreach ($toners_lista as $t): ?>
                  <option value="<?php echo (int)$t['id']; ?>" data-label="<?php echo htmlspecialchars($t['modelo']); ?>" class="p-2 hover:bg-red-50 rounded cursor-pointer">
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
        <label for="quantidadeDefeito" class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Qtd. <span class="text-red-500">*</span></label>
        <div class="relative group">
           <input type="number" id="quantidadeDefeito" name="quantidade" value="1" min="1"
            class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 text-sm font-semibold text-gray-800 text-center focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none group-hover:bg-white group-hover:border-gray-300">
        </div>
      </div>

      <!-- Linha 2: Cliente (12) -->
      <div class="md:col-span-12 flex flex-col">
        <label for="buscaCliente" class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Cliente <span class="text-red-500">*</span></label>
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
             <svg class="h-5 w-5 text-gray-400 group-focus-within:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          </div>
          <input type="text" id="buscaCliente"
            placeholder="Pesquise por nome ou código do cliente..."
            class="w-full bg-gray-50 border border-gray-200 rounded-lg pl-10 pr-4 py-2.5 text-sm font-medium text-gray-800 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none placeholder-gray-400 group-hover:bg-white group-hover:border-gray-300"
            autocomplete="off">

           <!-- Select Oculto / Dropdown -->
           <div id="dropdownCliente" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-xl hidden max-h-60 overflow-y-auto">
              <select id="selectCliente" name="cliente_id" size="5" class="w-full text-sm border-none focus:ring-0 p-1">
                <?php foreach ($clientes_lista as $c): ?>
                  <option value="<?php echo (int)$c['id']; ?>"
                    data-label="<?php echo htmlspecialchars($c['codigo'] . ' – ' . $c['nome']); ?>"
                    data-nome="<?php echo htmlspecialchars($c['nome']); ?>"
                    class="p-2 hover:bg-red-50 rounded cursor-pointer">
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
        <label for="descricaoDefeito" class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Descrição do Defeito <span class="text-red-500">*</span></label>
        <textarea id="descricaoDefeito" name="descricao" rows="4"
          placeholder="Descreva detalhadamente o problema (ex: Manchas na lateral, ruído ao imprimir, etc)..."
          class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-800 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none resize-y placeholder-gray-400 hover:bg-white hover:border-gray-300"></textarea>
      </div>

      <!-- Linha 4: Fotos (12) -->
      <div class="md:col-span-12">
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3 block">Evidências Fotográficas <span class="text-gray-400 font-normal lowercase ml-1">(opcional, máx 3)</span></label>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <?php for ($i = 1; $i <= 3; $i++): ?>
          <div class="relative group">
            <label for="foto<?php echo $i; ?>"
              class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-gray-200 bg-gray-50 rounded-xl h-32 cursor-pointer hover:border-red-400 hover:bg-red-50/50 transition-all duration-200" id="labelFoto<?php echo $i; ?>">
              
              <span id="previewFoto<?php echo $i; ?>" class="w-full h-full hidden rounded-xl overflow-hidden relative">
                <img id="imgPreview<?php echo $i; ?>" src="" alt="Prévia" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <span class="text-white text-xs font-medium bg-black/50 px-2 py-1 rounded">Trocar</span>
                </div>
              </span>
              
              <span id="placeholderFoto<?php echo $i; ?>" class="flex flex-col items-center gap-1 text-gray-400 group-hover:text-red-400 transition-colors">
                <svg class="w-8 h-8 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="text-xs font-medium">Foto <?php echo $i; ?></span>
              </span>
            </label>
            <input type="file" id="foto<?php echo $i; ?>" name="foto<?php echo $i; ?>" accept="image/*" class="hidden" data-index="<?php echo $i; ?>">
            <button type="button" id="removerFoto<?php echo $i; ?>"
              class="hidden absolute -top-2 -right-2 bg-red-100 text-red-600 rounded-full p-1 shadow-md hover:bg-red-200 transition-colors"
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
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3 block">
          Notificar Setores
          <span class="text-gray-400 font-normal lowercase ml-1">(opcional â€” usuÃ¡rios dos setores selecionados receberÃ£o email)</span>
        </label>
        <div class="flex flex-wrap gap-2">
          <?php if (!empty($departamentos_lista)): ?>
            <?php foreach ($departamentos_lista as $dep): ?>
              <label class="inline-flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg cursor-pointer hover:bg-red-50 hover:border-red-300 transition-all has-[:checked]:bg-red-50 has-[:checked]:border-red-400 has-[:checked]:text-red-700 select-none">
                <input type="checkbox" name="notificar_setores[]" value="<?php echo htmlspecialchars($dep['nome']); ?>"
                  class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500 focus:ring-2">
                <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($dep['nome']); ?></span>
              </label>
            <?php endforeach; ?>
          <?php else: ?>
            <span class="text-sm text-gray-400 italic">Nenhum departamento cadastrado.</span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Botão -->
      <div class="md:col-span-12 flex justify-end pt-4 border-t border-gray-100 mt-2">
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
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
      <h2 class="text-base font-semibold text-gray-800">Histórico de Registros</h2>
      <span class="text-sm text-gray-400"><?php echo count($defeitos_historico ?? []); ?> registro(s)</span>
    </div>

    <!-- Barra de busca -->
    <div class="px-6 py-3 border-b border-gray-100 flex items-center gap-3">
      <div class="relative flex-1 max-w-md">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <input type="text" id="buscaHistorico" placeholder="Buscar por modelo, pedido, OS, cliente, filial, descrição..."
          class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-400 transition-all"
          oninput="filtrarHistorico()">
      </div>
      <span id="contadorResultados" class="text-xs text-gray-400"></span>
    </div>

    <?php if (empty($defeitos_historico)): ?>
    <div class="py-14 text-center">
      <p class="text-gray-400 text-sm">Nenhum toner com defeito registrado ainda.</p>
    </div>
    <?php
else: ?>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm" id="tabelaHistorico">
        <thead>
          <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
            <th class="px-4 py-3 text-left">Data</th>
            <th class="px-4 py-3 text-left">Modelo</th>
            <th class="px-4 py-3 text-left">Nº Pedido</th>
            <th class="px-4 py-3 text-left">Nº OS</th>
            <th class="px-4 py-3 text-left">Filial</th>
            <th class="px-4 py-3 text-left">Cliente</th>
            <th class="px-4 py-3 text-left">Descrição</th>
            <th class="px-4 py-3 text-center">Qtd</th>
            <th class="px-4 py-3 text-center">Evidências</th>
            <th class="px-4 py-3 text-left">Registrado por</th>
            <th class="px-4 py-3 text-center">Devolutiva</th>
            <th class="px-4 py-3 text-center">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($defeitos_historico as $d): ?>
          <tr class="hover:bg-gray-50 transition-colors defeito-row"
              data-busca="<?= strtolower(htmlspecialchars(
                  ($d['modelo_toner'] ?? '') . ' ' .
                  ($d['numero_pedido'] ?? '') . ' ' .
                  ($d['numero_os'] ?? '') . ' ' .
                  ($d['cliente_nome'] ?? '') . ' ' .
                  ($d['filial_nome'] ?? '') . ' ' .
                  ($d['descricao'] ?? '')
              )) ?>">
            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
              <?php echo date('d/m/Y H:i', strtotime($d['created_at'])); ?>
            </td>
            <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
              <?php echo htmlspecialchars($d['modelo_toner']); ?>
            </td>
            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
              <?php echo htmlspecialchars($d['numero_pedido']); ?>
            </td>
            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
              <?php echo $d['numero_os'] ? htmlspecialchars($d['numero_os']) : '<span class="text-gray-300">—</span>'; ?>
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
              <?php if (!empty($d['filial_nome'])): ?>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                  <?= htmlspecialchars($d['filial_nome']) ?>
                </span>
              <?php else: ?>
                <span class="text-gray-300">—</span>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
              <?php echo htmlspecialchars($d['cliente_nome']); ?>
            </td>
            <td class="px-4 py-3 text-gray-600 max-w-xs">
              <span title="<?php echo htmlspecialchars($d['descricao']); ?>">
                <?php echo htmlspecialchars(mb_strimwidth($d['descricao'], 0, 80, '…')); ?>
              </span>
            </td>
            <td class="px-4 py-3 text-center font-medium text-gray-700">
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
                     class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  </a>
                  <?php
      else: ?>
                  <span class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                  </span>
                  <?php
      endif; ?>
                <?php
    endfor; ?>
              </div>
            </td>
            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
              <?php echo htmlspecialchars($d['registrado_por_nome'] ?? '—'); ?>
            </td>
            <td class="px-4 py-3 text-center">
                <?php if (!empty($d['devolutiva_descricao'])): ?>
                <div class="flex flex-col items-center gap-1">
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Respondida
                  </span>
                  <?php
    $res = $d['devolutiva_resultado'] ?? '';
    if ($res === 'DEFEITO_PROCEDENTE'): ?>
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 border border-red-200">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    DEFEITO PROCEDENTE
                  </span>
                  <?php elseif ($res === 'TONER_SEM_DEFEITO'): ?>
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    TONER SEM DEFEITO
                  </span>
                  <?php endif; ?>
                  <span class="text-[10px] text-gray-400" title="<?php echo htmlspecialchars($d['devolutiva_descricao']); ?>">
                    <?php echo htmlspecialchars(mb_strimwidth($d['devolutiva_descricao'], 0, 40, '…')); ?>
                  </span>
                  <?php if (!empty($d['devolutiva_por_nome'])): ?>
                    <span class="text-[10px] text-gray-400">
                      por <?php echo htmlspecialchars($d['devolutiva_por_nome']); ?>
                      <?php if (!empty($d['devolutiva_at'])): ?>
                        em <?php echo date('d/m/Y', strtotime($d['devolutiva_at'])); ?>
                      <?php endif; ?>
                    </span>
                  <?php endif; ?>
                </div>
              <?php
    else: ?>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-400 border border-gray-200">
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
                            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg <?php echo $hasDevolutiva ? 'bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-800 border-blue-200' : 'bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 border-gray-200'; ?> transition-colors border" title="Ver Devolutiva">
                       <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                       <span class="text-xs font-medium">Ver</span>
                    </button>

                  <?php if ($hasDevolutiva && $canInsert): ?>
                    <!-- Editar Devolutiva (só Qualidade/Admin) -->
                    <button type="button" onclick="openDevolutiva(<?php echo $d['id']; ?>, 'edit', this)"
                            data-desc="<?php echo htmlspecialchars($d['devolutiva_descricao'] ?? ''); ?>"
                            data-resultado="<?php echo htmlspecialchars($d['devolutiva_resultado'] ?? ''); ?>"
                            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-yellow-50 text-yellow-600 hover:bg-yellow-100 hover:text-yellow-800 transition-colors border border-yellow-200" title="Editar Devolutiva">
                       <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                  <?php elseif (!$hasDevolutiva && $canInsert): ?>
                    <!-- Inserir Devolutiva (só Qualidade/Admin) -->
                    <button type="button" onclick="openDevolutiva(<?php echo $d['id']; ?>, 'create', this)"
                            data-resultado=""
                            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-800 transition-colors border border-green-200" title="Inserir Devolutiva">
                       <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                       <span class="text-xs font-medium">Devolutiva</span>
                    </button>
                  <?php endif; ?>

                  <button type="button" onclick="excluirDefeito(<?php echo $d['id']; ?>)"
                     class="text-red-500 hover:text-red-700 transition-colors" title="Excluir Registro">
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
  class="fixed bottom-6 right-6 z-50 hidden max-w-sm bg-white border rounded-xl shadow-lg px-5 py-4 flex items-start gap-3 transition-all duration-300">
  <span id="toastIconDefeito" class="mt-0.5 shrink-0 w-5 h-5"></span>
  <div>
    <p id="toastTituloDefeito" class="text-sm font-semibold text-gray-800"></p>
    <p id="toastMsgDefeito" class="text-sm text-gray-500 mt-0.5"></p>
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
      showToast('success', 'Registrado!', data.message ?? 'Toner com defeito registrado com sucesso.');
      e.target.reset();
      // Resetar previews
      [1, 2, 3].forEach(i => removerFoto(i));
      // Resetar selects e inputs visuais
      selectToner.selectedIndex  = -1; // Desmarcar
      selectCliente.selectedIndex = -1;
      document.getElementById('buscaToner').value = '';
      document.getElementById('buscaCliente').value = '';
      
      // Recarregar histórico após 1s
      setTimeout(() => location.reload(), 2000);
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
</script>

<!-- Modal Devolutiva -->
<!-- Overlay + Container fixo -->
<div id="modalDevolutiva" class="fixed inset-0 z-[99999] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="z-index: 99999;">
  
  <!-- Overlay Backdrop -->
  <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm pointer-events-auto" aria-hidden="true" onclick="closeDevolutiva()"></div>

  <!-- Centralização -->
  <div class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6 pointer-events-none">
      
      <!-- Modal Card -->
      <div class="pointer-events-auto relative w-full max-w-lg bg-white rounded-xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden transform transition-all ring-1 ring-black ring-opacity-5">
        
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50 shrink-0">
            <h3 class="text-lg font-semibold text-gray-900" id="modal-title">Devolutiva (Qualidade)</h3>
            <button type="button" onclick="closeDevolutiva()" class="text-gray-400 hover:text-gray-500 transition-colors bg-white hover:bg-gray-100 rounded-lg p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 border border-transparent hover:border-gray-200">
                <span class="sr-only">Fechar</span>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-6 scrollbar-thin scrollbar-thumb-gray-200">
            <form id="formDevolutiva">
                <input type="hidden" name="defeito_id" id="devolutivaDefeitoId">
                
                <div class="mb-5 p-3.5 bg-blue-50 border border-blue-100 rounded-lg flex gap-3 text-sm text-blue-700" id="devolutivaModeText">
                    <svg class="w-5 h-5 shrink-0 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Insira a análise técnica e evidências.</span>
                </div>

                <div class="space-y-6">
                    <!-- Classificação do Resultado -->
                    <div id="devolutivaResultadoDiv">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Classificação <span class="text-red-500" id="devolutivaResultadoObr">*</span></label>
                        <div class="flex gap-3" id="devolutivaResultadoBtns">
                            <button type="button" data-valor="DEFEITO_PROCEDENTE"
                                onclick="selectResultado('DEFEITO_PROCEDENTE')"
                                class="resultado-btn flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-lg border-2 border-gray-200 bg-gray-50 text-gray-600 font-semibold text-sm transition-all hover:border-red-400 hover:bg-red-50 hover:text-red-700">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                DEFEITO PROCEDENTE
                            </button>
                            <button type="button" data-valor="TONER_SEM_DEFEITO"
                                onclick="selectResultado('TONER_SEM_DEFEITO')"
                                class="resultado-btn flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-lg border-2 border-gray-200 bg-gray-50 text-gray-600 font-semibold text-sm transition-all hover:border-emerald-400 hover:bg-emerald-50 hover:text-emerald-700">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                TONER SEM DEFEITO
                            </button>
                        </div>
                        <input type="hidden" name="devolutiva_resultado" id="devolutivaResultadoInput" value="">
                        <!-- Modo view: badge só leitura -->
                        <div id="devolutivaResultadoView" class="hidden mt-2"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Descrição Técnica</label>
                        <textarea name="devolutiva_descricao" id="devolutivaDesc" rows="5" 
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm placeholder-gray-400 resize-none transition-shadow"
                            placeholder="Descreva detalhadamente a análise realizada..."
                            required></textarea>
                    </div>
                    
                    <div id="devolutivaUploads">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Evidências (Até 3 fotos)</label>
                        <div class="grid grid-cols-3 gap-3">
                            <?php for ($i = 1; $i <= 3; $i++): ?>
                            <div class="relative group aspect-square">
                                <label class="block w-full h-full border-2 border-dashed border-gray-300 rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-red-500 hover:bg-red-50/30 transition-all overflow-hidden bg-gray-50/50">
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
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3 shrink-0 rounded-b-xl">
              <button type="button" onclick="closeDevolutiva()" class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 shadow-sm transition-all">
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
    const resultadoBtns = document.getElementById('devolutivaResultadoBtns');
    const resultadoView = document.getElementById('devolutivaResultadoView');
    const resultadoInput = document.getElementById('devolutivaResultadoInput');
    
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
    
    // Reset resultado
    resultadoInput.value = '';
    selectResultado(resultado, true); // pre-select silently
    
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
        resultadoBtns.classList.add('hidden');
        resultadoView.classList.remove('hidden');
        // Render resultado badge
        if (resultado === 'DEFEITO_PROCEDENTE') {
            resultadoView.innerHTML = '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-bold bg-red-100 text-red-700 border border-red-200"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>DEFEITO PROCEDENTE</span>';
        } else if (resultado === 'TONER_SEM_DEFEITO') {
            resultadoView.innerHTML = '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-bold bg-emerald-100 text-emerald-700 border border-emerald-200"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>TONER SEM DEFEITO</span>';
        } else {
            resultadoView.innerHTML = '<span class="text-xs text-gray-400 italic">Não classificado</span>';
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
        resultadoBtns.classList.remove('hidden');
        resultadoView.classList.add('hidden');
        
        if (mode === 'edit') {
             modeText.querySelector('span').innerText = 'Editando devolutiva existente. Faça upload de novas fotos para substituir as antigas.';
        } else {
             modeText.querySelector('span').innerText = 'Insira a análise técnica e evidências.';
        }
    }
}

function selectResultado(valor, silent) {
    const resultadoInput = document.getElementById('devolutivaResultadoInput');
    if (resultadoInput) resultadoInput.value = valor || '';
    document.querySelectorAll('.resultado-btn').forEach(btn => {
        const isActive = btn.getAttribute('data-valor') === valor;
        btn.classList.toggle('border-red-500', isActive && valor === 'DEFEITO_PROCEDENTE');
        btn.classList.toggle('bg-red-100', isActive && valor === 'DEFEITO_PROCEDENTE');
        btn.classList.toggle('text-red-700', isActive && valor === 'DEFEITO_PROCEDENTE');
        btn.classList.toggle('border-emerald-500', isActive && valor === 'TONER_SEM_DEFEITO');
        btn.classList.toggle('bg-emerald-100', isActive && valor === 'TONER_SEM_DEFEITO');
        btn.classList.toggle('text-emerald-700', isActive && valor === 'TONER_SEM_DEFEITO');
        btn.classList.toggle('border-gray-200', !isActive);
        btn.classList.toggle('bg-gray-50', !isActive);
        btn.classList.toggle('text-gray-600', !isActive);
    });
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
</script>
