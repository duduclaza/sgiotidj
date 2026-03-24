<?php
// Garantir que as variáveis existam
$amostragens = $amostragens ?? [];
$usuarios = $usuarios ?? [];
$filiais = $filiais ?? [];
$fornecedores = $fornecedores ?? [];
$toners = $toners ?? [];

/**
 * Função para construir URL de paginação mantendo os filtros
 */
function construirUrlPaginacao($pagina) {
    $params = $_GET;
    $params['pagina'] = $pagina;
    return '/amostragens-2?' . http_build_query($params);
}
?>

<section class="mb-8">
  <!-- Header -->
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">🔬 Amostragens 2.0</h1>
    <button onclick="novaAmostragem()" 
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
      <span>➕</span>
      <span>Nova Amostragem</span>
    </button>
  </div>

  <!-- Formulário Inline (Hidden por padrão) -->
  <div id="amostragemFormContainer" class="hidden bg-gray-800 border border-gray-600 rounded-lg p-6 mb-6">
    <div class="flex justify-between items-center mb-6">
      <h2 id="formTitle" class="text-lg font-semibold text-gray-100">🔬 Nova Amostragem</h2>
      <button onclick="closeAmostragemModal()" class="text-gray-400 hover:text-gray-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    <form id="amostragemForm" action="/amostragens-2/store" method="POST" enctype="multipart/form-data" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Número da NF -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Número da NF *</label>
          <input type="text" name="numero_nf" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Anexo da NF -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Anexo da NF (PDF ou Foto - Máx 10MB)</label>
          <input type="file" name="anexo_nf" accept=".pdf,image/*" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200">
          <div id="anexoNfExistente" class="hidden mt-2">
            <p class="text-xs text-gray-400">Anexo atual: <span id="anexoNfNome" class="text-blue-400"></span></p>
            <p class="text-xs text-gray-500">Envie um novo arquivo para substituir</p>
          </div>
        </div>

        <!-- Tipo de Produto -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Tipo de Produto *</label>
          <select name="tipo_produto" id="tipoProduto" required onchange="carregarProdutos()" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
            <option value="">Selecione...</option>
            <option value="Toner">Toner</option>
            <option value="Peça">Peça</option>
            <option value="Máquina">Máquina</option>
          </select>
        </div>

        <!-- Código do Produto -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Código do Produto *</label>
          <div class="relative">
            <input type="text" id="buscaProduto" placeholder="Digite para buscar..." onkeyup="filtrarProdutos()" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
            <select name="produto_id" id="produtoSelect" required size="5" class="hidden w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 mt-2 max-h-40 overflow-y-auto">
              <option value="">Selecione o tipo de produto primeiro</option>
            </select>
          </div>
          <input type="hidden" name="codigo_produto" id="codigoProduto">
          <input type="hidden" name="nome_produto" id="nomeProduto">
        </div>

        <!-- Quantidade Recebida -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Quantidade Recebida *</label>
          <input type="number" name="quantidade_recebida" id="quantidadeRecebida" min="1" required 
                 onchange="atualizarValidacoes()"
                 class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Fornecedor -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Fornecedor *</label>
          <select name="fornecedor_id" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
            <option value="">Selecione...</option>
            <?php foreach ($fornecedores as $forn): ?>
              <option value="<?= $forn['id'] ?>"><?= e($forn['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Responsáveis -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Responsáveis pelo Teste *</label>
          <select name="responsaveis[]" multiple required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500" size="4">
            <?php foreach ($usuarios as $user): ?>
              <option value="<?= $user['id'] ?>"><?= e($user['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="text-xs text-gray-400 mt-1">Segure Ctrl/Cmd para selecionar múltiplos</p>
        </div>

        <!-- Descrição do Defeito ou Observações -->
        <div class="md:col-span-2" id="observacoesContainer">
          <label id="observacoesLabel" class="block text-sm font-medium text-gray-200 mb-1">Descrição do Defeito ou Observações <span id="observacoesOptional" class="text-gray-400 text-xs">(Opcional)</span><span id="observacoesRequired" class="text-red-400 text-xs hidden">* Obrigatório</span></label>
          <textarea name="observacoes" id="observacoesInput" rows="3" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500" placeholder="Informações adicionais sobre a amostragem..."></textarea>
          <p id="observacoesError" class="text-red-400 text-sm mt-1 hidden">⚠️ Por favor, preencha a Descrição do Defeito ou Observações.</p>
        </div>

        <!-- Evidências (Fotos) -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-200 mb-1">Evidências (Fotos - Máx 5 arquivos de 10MB cada)</label>
          <input type="file" name="evidencias[]" multiple accept="image/*" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200">
          <p class="text-xs text-gray-400 mt-1">Opcional - Máximo 5 fotos</p>
          <div id="evidenciasExistentes" class="hidden mt-3">
            <p class="text-sm font-medium text-gray-200 mb-2">Evidências atuais:</p>
            <div id="listaEvidencias" class="grid grid-cols-2 md:grid-cols-3 gap-2"></div>
            <p class="text-xs text-gray-500 mt-2">Novas evidências serão adicionadas às existentes</p>
          </div>
        </div>

        <!-- ========== STATUS FINAL - 4 BOTÕES ========== -->
        <div class="md:col-span-2 mt-6 pt-6 border-t-2 border-gray-500">
          <label class="block text-xl font-bold text-white mb-4">🎯 Status Final *</label>
          
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <!-- Botão: Pendente -->
            <button type="button" id="btnPendente" onclick="selecionarStatus('pendente')"
                    class="p-4 rounded-xl border-3 border-gray-500 bg-gray-700/50 hover:bg-gray-600/50 transition-all text-center">
              <div class="text-3xl mb-2">⏳</div>
              <div class="text-sm font-bold text-gray-300">Pendente</div>
              <div class="text-xs text-gray-400 mt-1">Aguardando análise</div>
            </button>

            <!-- Botão: Aprovado -->
            <button type="button" id="btnAprovado" onclick="selecionarStatus('aprovado')"
                    class="p-4 rounded-xl border-3 border-green-500/50 bg-green-900/30 hover:bg-green-800/40 transition-all text-center">
              <div class="text-3xl mb-2">✅</div>
              <div class="text-sm font-bold text-green-300">Aprovado</div>
              <div class="text-xs text-green-400 mt-1">Lote 100% OK</div>
            </button>

            <!-- Botão: Aprovado Parcialmente -->
            <button type="button" id="btnParcial" onclick="selecionarStatus('parcial')"
                    class="p-4 rounded-xl border-3 border-yellow-500/50 bg-yellow-900/30 hover:bg-yellow-800/40 transition-all text-center">
              <div class="text-3xl mb-2">🔶</div>
              <div class="text-sm font-bold text-yellow-300">Parcial</div>
              <div class="text-xs text-yellow-400 mt-1">Parte reprovada</div>
            </button>

            <!-- Botão: Reprovado Total -->
            <button type="button" id="btnReprovado" onclick="selecionarStatus('reprovado')"
                    class="p-4 rounded-xl border-3 border-red-500/50 bg-red-900/30 hover:bg-red-800/40 transition-all text-center">
              <div class="text-3xl mb-2">❌</div>
              <div class="text-sm font-bold text-red-300">Reprovado</div>
              <div class="text-xs text-red-400 mt-1">Lote 100% reprovado</div>
            </button>
          </div>

          <!-- Campo hidden para status selecionado -->
          <input type="hidden" name="status_selecionado" id="statusSelecionado" value="">

          <!-- Mensagem quando Pendente -->
          <div id="msgPendente" class="hidden p-4 bg-gray-700/50 border border-gray-500 rounded-lg text-center">
            <p class="text-gray-300">⏳ Este lote ficará como <strong>PENDENTE</strong> aguardando análise.</p>
            <p class="text-xs text-gray-400 mt-2">A linha aparecerá em amarelo na listagem.</p>
          </div>

          <!-- Campos quando Aprovado -->
          <div id="camposAprovado" class="hidden p-4 bg-green-900/20 border border-green-500/30 rounded-lg">
            <h4 class="text-green-300 font-semibold mb-3">✅ Lote Aprovado - Informe a quantidade testada:</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-200 mb-1">Qtd Testada <span class="text-red-400">*</span></label>
                <input type="number" id="qtdTestadaAprovado" min="0" 
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-green-500">
                <p class="text-xs text-gray-400 mt-1">Quantas unidades foram testadas?</p>
              </div>
              <div class="flex items-center">
                <div class="p-3 bg-green-800/30 rounded-lg">
                  <p class="text-green-300 text-sm">📊 <strong>Resultado:</strong></p>
                  <p class="text-green-200 text-lg font-bold">Aprovadas: <span id="resumoAprovado">0</span></p>
                  <p class="text-xs text-gray-400">= Quantidade Recebida</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Campos quando Aprovado Parcialmente -->
          <div id="camposParcial" class="hidden p-4 bg-yellow-900/20 border border-yellow-500/30 rounded-lg">
            <h4 class="text-yellow-300 font-semibold mb-3">🔶 Aprovado Parcialmente - Informe os detalhes:</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-200 mb-1">Qtd Testada <span class="text-red-400">*</span></label>
                <input type="number" id="qtdTestadaParcial" min="0" onchange="calcularParcial()" oninput="calcularParcial()"
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-yellow-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-200 mb-1">Qtd Aprovada <span class="text-red-400">*</span></label>
                <input type="number" id="qtdAprovadaParcial" min="0" onchange="calcularParcial()" oninput="calcularParcial()"
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-green-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-200 mb-1">Qtd Reprovada <span class="text-gray-400 text-xs">(auto)</span></label>
                <input type="number" id="qtdReprovadaParcial" readonly
                       class="w-full bg-gray-600 border border-gray-500 rounded-lg px-3 py-2 text-red-300 cursor-not-allowed font-bold">
                <p class="text-xs text-gray-400 mt-1">= Testada - Aprovada</p>
              </div>
            </div>
            <div class="mt-3 p-2 bg-gray-800/50 rounded-lg">
              <p class="text-gray-300 text-sm">📊 <strong>Não Testadas:</strong> <span id="naoTestadasParcial" class="text-blue-300 font-bold">0</span> <span class="text-xs text-gray-400">(consideradas aprovadas)</span></p>
              <p class="text-gray-300 text-sm mt-1">📋 <strong>Total Aprovadas:</strong> <span id="totalAprovadasParcial" class="text-green-300 font-bold">0</span> | <strong>Total Reprovadas:</strong> <span id="totalReprovadaParcial" class="text-red-300 font-bold">0</span></p>
            </div>
          </div>

          <!-- Campos quando Reprovado Total -->
          <div id="camposReprovado" class="hidden p-4 bg-red-900/20 border border-red-500/30 rounded-lg">
            <h4 class="text-red-300 font-semibold mb-3">❌ Lote Reprovado - Informe a quantidade testada:</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-200 mb-1">Qtd Testada <span class="text-red-400">*</span></label>
                <input type="number" id="qtdTestadaReprovado" min="0" 
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-red-500">
                <p class="text-xs text-gray-400 mt-1">Quantas unidades foram testadas?</p>
              </div>
              <div class="flex items-center">
                <div class="p-3 bg-red-800/30 rounded-lg">
                  <p class="text-red-300 text-sm">📊 <strong>Resultado:</strong></p>
                  <p class="text-red-200 text-lg font-bold">Reprovadas: <span id="resumoReprovado">0</span></p>
                  <p class="text-xs text-gray-400">= Quantidade Recebida</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Campos hidden para enviar ao servidor -->
          <input type="hidden" name="quantidade_testada" id="hiddenTestada" value="">
          <input type="hidden" name="quantidade_aprovada" id="hiddenAprovada" value="">
          <input type="hidden" name="quantidade_reprovada" id="hiddenReprovada" value="">
        </div>
      </div>

      <div class="flex justify-end space-x-3 pt-4">
        <button type="button" onclick="cancelarEdicao()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
          Cancelar
        </button>
        <button type="submit" id="submitButton" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          💾 Salvar Amostragem
        </button>
      </div>
    </form>
  </div>

  <!-- Filtros -->
  <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700/50 p-4 mb-6 transition-colors">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
      <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
        <span class="p-1.5 bg-blue-100 dark:bg-blue-900/40 rounded-lg text-blue-600 dark:text-blue-400">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </span>
        Filtros de Busca
      </h3>
      
      <!-- Controle de Zoom do Grid (Removido daqui e movido para o Grid abaixo) -->
    </div>
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4">
      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Número da NF</label>
        <input type="text" name="numero_nf" value="<?= $_GET['numero_nf'] ?? '' ?>" placeholder="Digite a NF..." class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Código do Produto</label>
        <input type="text" name="codigo_produto" value="<?= $_GET['codigo_produto'] ?? '' ?>" placeholder="Digite o código..." class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Usuário</label>
        <select name="user_id" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
          <option value="">Todos</option>
          <?php foreach ($usuarios as $user): ?>
            <option value="<?= $user['id'] ?>" <?= ($_GET['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
              <?= e($user['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Filial</label>
        <select name="filial_id" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
          <option value="">Todas</option>
          <?php foreach ($filiais as $filial): ?>
            <option value="<?= $filial['id'] ?>" <?= ($_GET['filial_id'] ?? '') == $filial['id'] ? 'selected' : '' ?>>
              <?= e($filial['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Fornecedor</label>
        <select name="fornecedor_id" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
          <option value="">Todos</option>
          <?php foreach ($fornecedores as $forn): ?>
            <option value="<?= $forn['id'] ?>" <?= ($_GET['fornecedor_id'] ?? '') == $forn['id'] ? 'selected' : '' ?>>
              <?= e($forn['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
        <select name="status" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
          <option value="">Todos</option>
          <option value="Pendente" <?= ($_GET['status'] ?? '') == 'Pendente' ? 'selected' : '' ?>>Pendente</option>
          <option value="Aprovado" <?= ($_GET['status'] ?? '') == 'Aprovado' ? 'selected' : '' ?>>Aprovado</option>
          <option value="Aprovado Parcialmente" <?= ($_GET['status'] ?? '') == 'Aprovado Parcialmente' ? 'selected' : '' ?>>Aprovado Parcialmente</option>
          <option value="Reprovado" <?= ($_GET['status'] ?? '') == 'Reprovado' ? 'selected' : '' ?>>Reprovado</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Data Início</label>
        <input type="date" name="data_inicio" value="<?= $_GET['data_inicio'] ?? '' ?>" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Data Fim</label>
        <input type="date" name="data_fim" value="<?= $_GET['data_fim'] ?? '' ?>" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
      </div>

      <div class="flex items-end gap-1.5 col-span-1">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg transition-colors whitespace-nowrap font-medium shadow-md text-sm">
          Filtrar
        </button>
        <button type="button" onclick="abrirModalColunas()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-lg transition-colors whitespace-nowrap font-medium shadow-md text-sm">
          Colunas
        </button>
        <a href="/amostragens-2" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg text-center transition-colors whitespace-nowrap font-medium shadow-md text-sm">
          Limpar
        </a>
        <button type="button" onclick="exportarExcel()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg transition-colors whitespace-nowrap font-medium shadow-md text-sm">
          📊 Exportar
        </button>
      </div>
    </form>
  </div>

  <!-- Controles de Paginação -->
  <?php if (isset($paginacao)): ?>
  <div class="bg-white dark:bg-slate-800 border dark:border-slate-700 rounded-lg p-4 mb-4 transition-colors">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
      <!-- Seletor de registros por página -->
      <div class="flex items-center gap-2">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Mostrar:</label>
        <select onchange="alterarPorPagina(this.value)" 
                class="border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-colors">
          <option value="10" <?= $paginacao['por_pagina'] == 10 ? 'selected' : '' ?>>10</option>
          <option value="50" <?= $paginacao['por_pagina'] == 50 ? 'selected' : '' ?>>50</option>
          <option value="100" <?= $paginacao['por_pagina'] == 100 ? 'selected' : '' ?>>100</option>
        </select>
        <span class="text-sm text-gray-600 dark:text-gray-400">registros por página</span>
      </div>

      <!-- Informação de registros -->
      <div class="text-sm text-gray-700 dark:text-gray-300">
        Mostrando 
        <span class="font-semibold text-gray-900 dark:text-white"><?= $paginacao['offset'] + 1 ?></span>
        até 
        <span class="font-semibold text-gray-900 dark:text-white"><?= min($paginacao['offset'] + $paginacao['por_pagina'], $paginacao['total_registros']) ?></span>
        de 
        <span class="font-semibold text-gray-900 dark:text-white"><?= $paginacao['total_registros'] ?></span>
        registros
      </div>

      <!-- Navegação de páginas -->
      <?php if ($paginacao['total_paginas'] > 1): ?>
      <div class="flex items-center gap-1">
        <!-- Botão Primeira -->
        <?php if ($paginacao['pagina_atual'] > 1): ?>
          <a href="<?= construirUrlPaginacao(1) ?>" 
             class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
            « Primeira
          </a>
        <?php endif; ?>

        <!-- Botão Anterior -->
        <?php if ($paginacao['pagina_atual'] > 1): ?>
          <a href="<?= construirUrlPaginacao($paginacao['pagina_atual'] - 1) ?>" 
             class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
            ‹ Anterior
          </a>
        <?php endif; ?>

        <!-- Números de página -->
        <?php
        $inicio = max(1, $paginacao['pagina_atual'] - 2);
        $fim = min($paginacao['total_paginas'], $paginacao['pagina_atual'] + 2);
        
        for ($i = $inicio; $i <= $fim; $i++):
        ?>
          <a href="<?= construirUrlPaginacao($i) ?>" 
             class="px-3 py-2 border rounded-lg text-sm font-medium transition-colors
                    <?= $i == $paginacao['pagina_atual'] 
                        ? 'bg-blue-600 text-white border-blue-600' 
                        : 'border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>

        <!-- Botão Próxima -->
        <?php if ($paginacao['pagina_atual'] < $paginacao['total_paginas']): ?>
          <a href="<?= construirUrlPaginacao($paginacao['pagina_atual'] + 1) ?>" 
             class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
            Próxima ›
          </a>
        <?php endif; ?>

        <!-- Botão Última -->
        <?php if ($paginacao['pagina_atual'] < $paginacao['total_paginas']): ?>
          <a href="<?= construirUrlPaginacao($paginacao['total_paginas']) ?>" 
             class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
            Última »
          </a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Grid de Amostragens -->
  <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700/50 overflow-hidden transition-colors">
    <!-- Zoom Control no Cabeçalho do Grid -->
    <div class="px-4 py-2 bg-gray-50 dark:bg-slate-900/30 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="flex items-center gap-2">
          <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">🔍 Zoom</span>
          <input type="range" id="gridZoom" min="50" max="130" value="100" oninput="updateZoom(this.value)" 
                 class="w-32 h-1.5 bg-gray-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-blue-600">
          <span id="zoomValue" class="text-xs font-mono font-bold text-blue-600 dark:text-blue-400 min-w-[36px]">100%</span>
        </div>
        <div class="w-px h-4 bg-gray-300 dark:bg-slate-700"></div>
        <button onclick="resetZoom()" class="text-gray-400 hover:text-blue-500 transition-colors" title="Resetar Zoom">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </button>
      </div>
      <div id="grid-top-scroll" class="flex-1 overflow-x-auto" style="overflow-y:hidden;height:12px;">
        <div id="grid-top-scroll-inner" style="height:1px;"></div>
      </div>
    </div>
    <div id="grid-scroll" class="overflow-x-auto">
      <table id="amostragensTable" class="min-w-full text-sm">
        <thead class="bg-gray-50 dark:bg-slate-900/50">
          <tr>
            <th class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Data <div class="resizer" data-col="0"></div>
            </th>
            <th class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              NF <div class="resizer" data-col="1"></div>
            </th>
            <th class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Usuário <div class="resizer" data-col="2"></div>
            </th>
            <th class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Filial <div class="resizer" data-col="3"></div>
            </th>
            <th class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Tipo <div class="resizer" data-col="4"></div>
            </th>
            <th class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Código <div class="resizer" data-col="5"></div>
            </th>
            <th class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Fornecedor <div class="resizer" data-col="6"></div>
            </th>
            <th class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Qtd Recebida <div class="resizer" data-col="7"></div>
            </th>
            <th class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Qtd Testada <div class="resizer" data-col="8"></div>
            </th>
            <th class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Aprovada <div class="resizer" data-col="9"></div>
            </th>
            <th class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Reprovada <div class="resizer" data-col="10"></div>
            </th>
            <th class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Status <div class="resizer" data-col="11"></div>
            </th>
            <th class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Aprovado Por <div class="resizer" data-col="12"></div>
            </th>
            <th class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Anexo NF <div class="resizer" data-col="13"></div>
            </th>
            <th class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Evidências <div class="resizer" data-col="14"></div>
            </th>
            <th class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Ações <div class="resizer" data-col="15"></div>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
          <?php foreach ($amostragens as $amostra): ?>
          <?php 
            // Linha amarela se não tem quantidade testada preenchida (precisa atualizar)
            $precisaAtualizar = empty($amostra['quantidade_testada']) || $amostra['quantidade_testada'] == 0;
            $classeLinhaAmarela = $precisaAtualizar ? 'bg-yellow-50 hover:bg-yellow-100' : 'hover:bg-gray-50';
          ?>
          <tr class="<?= $classeLinhaAmarela ?> dark:border-slate-700" <?php if($precisaAtualizar): ?>title="⚠️ Quantidade testada não informada - Precisa atualizar"<?php endif; ?>>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
              <?= date('d/m/Y', strtotime($amostra['created_at'])) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
              <?= e($amostra['numero_nf']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400" title="<?= e($amostra['usuario_nome']) ?>">
              <?= e($amostra['usuario_nome']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400" title="<?= e($amostra['filial_nome']) ?>">
              <?= e($amostra['filial_nome']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?= e($amostra['tipo_produto']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?= e($amostra['codigo_produto']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?= e($amostra['fornecedor_nome']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
              <?= $amostra['quantidade_recebida'] ?? 0 ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
              <?= $amostra['quantidade_testada'] ?? 0 ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-green-600 font-semibold">
              <?= $amostra['quantidade_aprovada'] ?? 0 ?>
              <?php if (($amostra['quantidade_aprovada'] ?? 0) == ($amostra['quantidade_recebida'] ?? 0) && ($amostra['quantidade_reprovada'] ?? 0) == 0 && ($amostra['quantidade_aprovada'] ?? 0) > 0): ?>
                <span class="ml-1 bg-green-100 text-green-800 text-xs px-1.5 py-0.5 rounded-full" title="Lote 100% aprovado">✓</span>
              <?php endif; ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-red-600 font-semibold">
              <?= $amostra['quantidade_reprovada'] ?? 0 ?>
              <?php if (($amostra['quantidade_reprovada'] ?? 0) == ($amostra['quantidade_recebida'] ?? 0) && ($amostra['quantidade_aprovada'] ?? 0) == 0 && ($amostra['quantidade_reprovada'] ?? 0) > 0): ?>
                <span class="ml-1 bg-red-100 text-red-800 text-xs px-1.5 py-0.5 rounded-full" title="Lote 100% reprovado">LOTE</span>
              <?php elseif (($amostra['quantidade_reprovada'] ?? 0) > 0 && ($amostra['quantidade_aprovada'] ?? 0) > 0): ?>
                <span class="ml-1 bg-yellow-100 text-yellow-800 text-xs px-1.5 py-0.5 rounded-full" title="Aprovação parcial">PARCIAL</span>
              <?php endif; ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <select onchange="alterarStatus(<?= $amostra['id'] ?>, this.value)" 
                      data-old-value="<?= e($amostra['status_final']) ?>"
                      class="px-2 py-1 text-xs font-semibold rounded-md border-0 cursor-pointer
                        <?php
                          switch($amostra['status_final']) {
                            case 'Aprovado': echo 'bg-green-100 text-green-800'; break;
                            case 'Aprovado Parcialmente': echo 'bg-yellow-100 text-yellow-800'; break;
                            case 'Reprovado': echo 'bg-red-100 text-red-800'; break;
                            default: echo 'bg-gray-100 text-gray-800';
                          }
                        ?>">
                <option value="Pendente" <?= $amostra['status_final'] == 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                <option value="Aprovado" <?= $amostra['status_final'] == 'Aprovado' ? 'selected' : '' ?>>Aprovado</option>
                <option value="Aprovado Parcialmente" <?= $amostra['status_final'] == 'Aprovado Parcialmente' ? 'selected' : '' ?>>Aprovado Parcialmente</option>
                <option value="Reprovado" <?= $amostra['status_final'] == 'Reprovado' ? 'selected' : '' ?>>Reprovado</option>
              </select>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?php if (!empty($amostra['aprovado_por_nome'])): ?>
                <div class="flex flex-col">
                  <span class="text-gray-900 dark:text-white font-medium"><?= e($amostra['aprovado_por_nome']) ?></span>
                  <?php if (!empty($amostra['aprovado_em'])): ?>
                    <?php
                    // Converter para timezone do Brasil (América/São_Paulo = UTC-3)
                    $dt = new DateTime($amostra['aprovado_em'], new DateTimeZone('UTC'));
                    $dt->setTimezone(new DateTimeZone('America/Sao_Paulo'));
                    ?>
                    <span class="text-xs text-gray-500 dark:text-gray-400"><?= $dt->format('d/m/Y H:i') ?></span>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <span class="text-gray-400 dark:text-gray-500 text-xs">-</span>
              <?php endif; ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
              <?php if (!empty($amostra['anexo_nf_nome'])): ?>
                <a href="/amostragens-2/<?= $amostra['id'] ?>/download-nf" 
                   class="text-blue-600 hover:text-blue-800" 
                   title="<?= e($amostra['anexo_nf_nome']) ?>">
                  📄 Baixar
                </a>
              <?php else: ?>
                <span class="text-gray-400">-</span>
              <?php endif; ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
              <?php if ($amostra['total_evidencias'] > 0): ?>
                <button onclick="baixarEvidencias(<?= $amostra['id'] ?>)" 
                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs">
                  📥 Baixar (<?= $amostra['total_evidencias'] ?>)
                </button>
              <?php else: ?>
                <span class="text-gray-400">Sem evidências</span>
              <?php endif; ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
              <div class="flex items-center gap-2">
                <button onclick="editarAmostragem(<?= $amostra['id'] ?>)" 
                        class="p-1.5 bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/60 transition-colors"
                        title="Editar">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button onclick="excluirAmostragem(<?= $amostra['id'] ?>)" 
                        class="p-1.5 bg-red-50 dark:bg-red-900/40 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/60 transition-colors"
                        title="Excluir">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          
          <?php if (empty($amostragens)): ?>
          <tr>
            <td colspan="16" class="px-6 py-8 text-center text-gray-500">
              <div class="flex flex-col items-center">
                <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-lg font-semibold mb-2">Nenhuma amostragem encontrada</p>
                <p class="text-sm">Tente ajustar os filtros ou crie uma nova amostragem</p>
              </div>
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Controles de Paginação (Rodapé) -->
  <?php if (isset($paginacao) && $paginacao['total_paginas'] > 1): ?>
  <div class="bg-white dark:bg-slate-800 border dark:border-slate-700 rounded-lg p-4 mt-4 transition-colors">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
      <!-- Informação de registros -->
      <div class="text-sm text-gray-700 dark:text-gray-300">
        Mostrando 
        <span class="font-semibold text-gray-900 dark:text-white"><?= $paginacao['offset'] + 1 ?></span>
        até 
        <span class="font-semibold text-gray-900 dark:text-white"><?= min($paginacao['offset'] + $paginacao['por_pagina'], $paginacao['total_registros']) ?></span>
        de 
        <span class="font-semibold text-gray-900 dark:text-white"><?= $paginacao['total_registros'] ?></span>
        registros
      </div>

      <!-- Navegação de páginas -->
      <div class="flex items-center gap-1">
        <!-- Botão Primeira -->
        <?php if ($paginacao['pagina_atual'] > 1): ?>
          <a href="<?= construirUrlPaginacao(1) ?>" 
             class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
            « Primeira
          </a>
        <?php endif; ?>

        <!-- Botão Anterior -->
        <?php if ($paginacao['pagina_atual'] > 1): ?>
          <a href="<?= construirUrlPaginacao($paginacao['pagina_atual'] - 1) ?>" 
             class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
            ‹ Anterior
          </a>
        <?php endif; ?>

        <!-- Números de página -->
        <?php
        $inicio = max(1, $paginacao['pagina_atual'] - 2);
        $fim = min($paginacao['total_paginas'], $paginacao['pagina_atual'] + 2);
        
        for ($i = $inicio; $i <= $fim; $i++):
        ?>
          <a href="<?= construirUrlPaginacao($i) ?>" 
             class="px-3 py-2 border rounded-lg text-sm font-medium transition-colors
                    <?= $i == $paginacao['pagina_atual'] 
                        ? 'bg-blue-600 text-white border-blue-600' 
                        : 'border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>

        <!-- Botão Próxima -->
        <?php if ($paginacao['pagina_atual'] < $paginacao['total_paginas']): ?>
          <a href="<?= construirUrlPaginacao($paginacao['pagina_atual'] + 1) ?>" 
             class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
            Próxima ›
          </a>
        <?php endif; ?>

        <!-- Botão Última -->
        <?php if ($paginacao['pagina_atual'] < $paginacao['total_paginas']): ?>
          <a href="<?= construirUrlPaginacao($paginacao['total_paginas']) ?>" 
             class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
            Última »
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</section>

</div>

<!-- ========== MODAL: PERSONALIZAR COLUNAS ========== -->
<div id="modal-colunas" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-xl transition-colors">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-slate-700">
      <div>
        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Personalizar colunas</h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Escolha quais colunas exibir no grid.</p>
      </div>
      <button onclick="fecharModalColunas()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="px-6 py-4">
      <div id="colunas-lista" class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-80 overflow-y-auto pr-2">
        <!-- JS irá popular isso -->
      </div>
    </div>
    <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/50">
      <button onclick="resetColunasPadrao()" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700">Padrão</button>
      <button onclick="salvarPreferenciasColunas()" class="px-4 py-2 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-lg">Salvar</button>
    </div>
  </div>
</div>

<!-- Modal de Loading para Downloads -->
<div id="loadingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
    <div class="text-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
      <h3 class="text-lg font-semibold mb-2">Preparando Download...</h3>
      <p class="text-gray-600">Aguarde enquanto preparamos as evidências para download.</p>
    </div>
  </div>
</div>

<!-- Toast/Notificação Customizada -->
<div id="toastContainer" class="fixed top-4 right-4 z-[9999] flex flex-col gap-3 pointer-events-none"></div>

<style>
.beta-badge {
  background: linear-gradient(45deg, #ff6b6b, #feca57);
  color: white;
  font-size: 0.7rem;
  font-weight: bold;
  padding: 4px 8px;
  border-radius: 12px;
  text-shadow: 0 1px 2px rgba(0,0,0,0.3);
  animation: pulse 2s infinite;
}

/* --- Melhorias do Grid (Zoom e Resize) --- */
:root {
  --grid-zoom: 100%;
}

.grid-wrapper table {
  zoom: var(--grid-zoom);
  transform-origin: top left;
  /* Removido fixed para permitir que as colunas respirem melhor */
  width: 100%;
}

/* Forçar larguras mínimas e evitar quebra de linha/sobreposição */
.grid-wrapper th, .grid-wrapper td {
  min-width: 120px; 
  white-space: nowrap !important; /* Garantir que não quebre linha nunca */
  overflow: hidden;
  text-overflow: ellipsis;
  padding-left: 1rem;
  padding-right: 1rem;
}

/* Colunas específicas podem ser menores */
.grid-wrapper th:nth-child(1), .grid-wrapper td:nth-child(1) { min-width: 100px; } /* Data */
.grid-wrapper th:nth-child(2), .grid-wrapper td:nth-child(2) { min-width: 90px; }  /* NF */

/* Estilos do Resizer (Drag Handle) */
.resizer {
    position: absolute;
    top: 0;
    right: 0;
    width: 4px;
    cursor: col-resize;
    user-select: none;
    height: 100%;
    z-index: 10;
    transition: background-color 0.2s;
}

.resizer:hover, .resizing {
    background-color: #3b82f6; /* blue-500 */
    width: 4px;
}

/* Ajustes finos para selects de status no modo escuro */
.dark select option {
  background-color: #1e293b; /* slate-800 */
  color: #f8fafc; /* slate-50 */
}

.dark .bg-green-100 { background-color: rgba(20, 83, 45, 0.3) !important; color: #4ade80 !important; }
.dark .bg-yellow-100 { background-color: rgba(113, 63, 18, 0.3) !important; color: #facc15 !important; }
.dark .bg-red-100 { background-color: rgba(127, 29, 29, 0.3) !important; color: #f87171 !important; }
.dark .bg-blue-100 { background-color: rgba(30, 58, 138, 0.3) !important; color: #60a5fa !important; }
.dark .bg-gray-100 { background-color: rgba(30, 41, 59, 0.5) !important; color: #cbd5e1 !important; }

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.8; }
}

/* Estilos para campo obrigatório */
.observacoes-required {
  border-color: #f87171 !important;
  background-color: rgba(239, 68, 68, 0.1) !important;
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}

/* Toast/Notificação Styles */
.toast {
  pointer-events: auto;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 16px 20px;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  animation: toastSlideIn 0.4s ease-out forwards;
  max-width: 420px;
  min-width: 320px;
}

.toast.toast-exit {
  animation: toastSlideOut 0.3s ease-in forwards;
}

.toast-warning {
  background: linear-gradient(135deg, #ff8c00 0%, #ff6600 100%);
  border-left: 4px solid #fff;
}

.toast-error {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  border-left: 4px solid #fff;
}

.toast-success {
  background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
  border-left: 4px solid #fff;
}

.toast-info {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  border-left: 4px solid #fff;
}

.toast-icon {
  font-size: 24px;
  flex-shrink: 0;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
}

.toast-content {
  flex: 1;
}

.toast-title {
  font-weight: 700;
  font-size: 15px;
  color: white;
  margin-bottom: 4px;
  text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.toast-message {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.9);
  line-height: 1.4;
}

.toast-close {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.toast-close:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.1);
}

.toast-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  background: rgba(255, 255, 255, 0.5);
  border-radius: 0 0 0 12px;
  animation: toastProgress 5s linear forwards;
}

@keyframes toastSlideIn {
  from {
    transform: translateX(100%) scale(0.8);
    opacity: 0;
  }
  to {
    transform: translateX(0) scale(1);
    opacity: 1;
  }
}

@keyframes toastSlideOut {
  from {
    transform: translateX(0) scale(1);
    opacity: 1;
  }
  to {
    transform: translateX(100%) scale(0.8);
    opacity: 0;
  }
}

@keyframes toastProgress {
  from { width: 100%; }
  to { width: 0%; }
}
</style>

<script>
const produtosData = {
  toners: <?= json_encode($toners ?? []) ?>,
  pecas: <?= json_encode($pecas ?? []) ?>,
  maquinas: <?= json_encode($maquinas ?? []) ?>
};

// ========== Sistema de Toast/Notificação ==========
function showToast(type, title, message, duration = 5000) {
  const container = document.getElementById('toastContainer');
  
  const icons = {
    warning: '⚠️',
    error: '❌',
    success: '✅',
    info: 'ℹ️'
  };
  
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `
    <span class="toast-icon">${icons[type] || '📢'}</span>
    <div class="toast-content">
      <div class="toast-title">${title}</div>
      <div class="toast-message">${message}</div>
    </div>
    <button class="toast-close" onclick="closeToast(this.parentElement)">✕</button>
    <div class="toast-progress"></div>
  `;
  
  container.appendChild(toast);
  
  // Auto-remover após duração
  setTimeout(() => {
    closeToast(toast);
  }, duration);
  
  return toast;
}

function closeToast(toast) {
  if (!toast || toast.classList.contains('toast-exit')) return;
  
  toast.classList.add('toast-exit');
  setTimeout(() => {
    toast.remove();
  }, 300);
}

function openAmostragemModal() {
  document.getElementById('amostragemFormContainer').classList.remove('hidden');
  document.getElementById('amostragemFormContainer').scrollIntoView({ behavior: 'smooth' });
}

function closeAmostragemModal() {
  document.getElementById('amostragemFormContainer').classList.add('hidden');
  document.getElementById('amostragemForm').reset();
  
  // Voltar para modo criar
  document.getElementById('amostragemForm').action = '/amostragens-2/store';
  
  // Remover campo hidden de ID se existir
  const hiddenId = document.querySelector('input[name="amostragem_id"]');
  if (hiddenId) {
    hiddenId.remove();
  }
}

function carregarProdutos() {
  const tipo = document.getElementById('tipoProduto').value;
  const select = document.getElementById('produtoSelect');
  
  select.innerHTML = '<option value="">Selecione...</option>';
  
  let produtos = [];
  
  if (tipo === 'Toner') {
    produtos = produtosData.toners;
  } else if (tipo === 'Peça') {
    produtos = produtosData.pecas;
  } else if (tipo === 'Máquina') {
    produtos = produtosData.maquinas;
  }
  
  produtos.forEach(p => {
    const option = document.createElement('option');
    option.value = p.id;
    // Todos os tipos: mostrar apenas código de referência
    option.textContent = p.codigo;
    option.dataset.codigo = p.codigo;
    option.dataset.nome = p.nome;
    select.appendChild(option);
  });
  
  select.classList.remove('hidden');
  
  select.onchange = function() {
    const selected = this.options[this.selectedIndex];
    document.getElementById('codigoProduto').value = selected.dataset.codigo || '';
    document.getElementById('nomeProduto').value = selected.dataset.nome || '';
  };
}

function filtrarProdutos() {
  const busca = document.getElementById('buscaProduto').value.toLowerCase();
  const select = document.getElementById('produtoSelect');
  const options = select.options;
  
  for (let i = 0; i < options.length; i++) {
    const text = options[i].textContent.toLowerCase();
    options[i].style.display = text.includes(busca) ? '' : 'none';
  }
}

// ===== NOVA LÓGICA DE AMOSTRAGENS - 4 BOTÕES =====
const qtdRecebidaInput = document.getElementById('quantidadeRecebida');
let statusSelecionado = null;

// Função para selecionar o status
function selecionarStatus(status) {
  statusSelecionado = status;
  document.getElementById('statusSelecionado').value = status;
  
  // Resetar visual de todos os botões
  ['btnPendente', 'btnAprovado', 'btnParcial', 'btnReprovado'].forEach(id => {
    const btn = document.getElementById(id);
    if (btn) {
      btn.classList.remove('ring-4', 'ring-gray-400', 'ring-green-400', 'ring-yellow-400', 'ring-red-400', 'scale-105');
      btn.style.transform = '';
    }
  });
  
  // Esconder todos os painéis
  ['msgPendente', 'camposAprovado', 'camposParcial', 'camposReprovado'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.classList.add('hidden');
  });
  
  const qtdRecebida = parseInt(qtdRecebidaInput?.value) || 0;
  
  // Atualizar obrigatoriedade do campo de observações
  atualizarObrigatoriedadeObservacoes(status);
  
  // Ativar botão e mostrar painel correspondente
  if (status === 'pendente') {
    document.getElementById('btnPendente').classList.add('ring-4', 'ring-gray-400', 'scale-105');
    document.getElementById('msgPendente').classList.remove('hidden');
    
  } else if (status === 'aprovado') {
    document.getElementById('btnAprovado').classList.add('ring-4', 'ring-green-400', 'scale-105');
    document.getElementById('camposAprovado').classList.remove('hidden');
    document.getElementById('resumoAprovado').textContent = qtdRecebida;
    
  } else if (status === 'parcial') {
    document.getElementById('btnParcial').classList.add('ring-4', 'ring-yellow-400', 'scale-105');
    document.getElementById('camposParcial').classList.remove('hidden');
    // Limpar campos
    document.getElementById('qtdTestadaParcial').value = '';
    document.getElementById('qtdAprovadaParcial').value = '';
    document.getElementById('qtdReprovadaParcial').value = '';
    calcularParcial();
    
  } else if (status === 'reprovado') {
    document.getElementById('btnReprovado').classList.add('ring-4', 'ring-red-400', 'scale-105');
    document.getElementById('camposReprovado').classList.remove('hidden');
    document.getElementById('resumoReprovado').textContent = qtdRecebida;
  }
}

// Função para atualizar a obrigatoriedade do campo de observações
function atualizarObrigatoriedadeObservacoes(status) {
  const observacoesInput = document.getElementById('observacoesInput');
  const observacoesOptional = document.getElementById('observacoesOptional');
  const observacoesRequired = document.getElementById('observacoesRequired');
  const observacoesError = document.getElementById('observacoesError');
  
  if (status === 'aprovado') {
    // Status aprovado: campo opcional
    observacoesOptional.classList.remove('hidden');
    observacoesRequired.classList.add('hidden');
    observacoesInput.classList.remove('observacoes-required');
    observacoesError.classList.add('hidden');
  } else {
    // Outros status: campo obrigatório
    observacoesOptional.classList.add('hidden');
    observacoesRequired.classList.remove('hidden');
  }
}

// Função para validar o campo de observações
function validarObservacoes() {
  const observacoesInput = document.getElementById('observacoesInput');
  const observacoesError = document.getElementById('observacoesError');
  
  // Se status não é aprovado, o campo é obrigatório
  if (statusSelecionado && statusSelecionado !== 'aprovado') {
    const valor = observacoesInput.value.trim();
    
    if (!valor) {
      // Campo vazio - mostrar erro
      observacoesInput.classList.add('observacoes-required');
      observacoesError.classList.remove('hidden');
      observacoesInput.focus();
      observacoesInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return false;
    }
  }
  
  // Campo válido - remover erro
  observacoesInput.classList.remove('observacoes-required');
  observacoesError.classList.add('hidden');
  return true;
}

// Remover erro quando o usuário digitar no campo
document.getElementById('observacoesInput')?.addEventListener('input', function() {
  if (this.value.trim()) {
    this.classList.remove('observacoes-required');
    document.getElementById('observacoesError').classList.add('hidden');
  }
});

// Calcular valores para Aprovação Parcial
function calcularParcial() {
  const qtdRecebida = parseInt(qtdRecebidaInput?.value) || 0;
  const qtdTestada = parseInt(document.getElementById('qtdTestadaParcial')?.value) || 0;
  const qtdAprovada = parseInt(document.getElementById('qtdAprovadaParcial')?.value) || 0;
  
  // Validar testada <= recebida
  if (qtdTestada > qtdRecebida) {
    document.getElementById('qtdTestadaParcial').value = qtdRecebida;
  }
  
  const testadaAtual = parseInt(document.getElementById('qtdTestadaParcial')?.value) || 0;
  
  // Validar aprovada <= testada
  if (qtdAprovada > testadaAtual) {
    document.getElementById('qtdAprovadaParcial').value = testadaAtual;
  }
  
  const aprovadaAtual = parseInt(document.getElementById('qtdAprovadaParcial')?.value) || 0;
  
  // Reprovada = Testada - Aprovada
  const reprovada = testadaAtual - aprovadaAtual;
  document.getElementById('qtdReprovadaParcial').value = reprovada >= 0 ? reprovada : 0;
  
  // Não testadas = Recebida - Testada
  const naoTestadas = qtdRecebida - testadaAtual;
  document.getElementById('naoTestadasParcial').textContent = naoTestadas >= 0 ? naoTestadas : 0;
  
  // Total Aprovadas = Aprovada + Não Testadas
  const totalAprovadas = aprovadaAtual + (naoTestadas >= 0 ? naoTestadas : 0);
  document.getElementById('totalAprovadasParcial').textContent = totalAprovadas;
  
  // Total Reprovadas = Reprovada do teste
  document.getElementById('totalReprovadaParcial').textContent = reprovada >= 0 ? reprovada : 0;
}

// Atualizar quando quantidade recebida muda
function atualizarValidacoes() {
  const qtdRecebida = parseInt(qtdRecebidaInput?.value) || 0;
  
  if (statusSelecionado === 'aprovado') {
    document.getElementById('resumoAprovado').textContent = qtdRecebida;
  } else if (statusSelecionado === 'reprovado') {
    document.getElementById('resumoReprovado').textContent = qtdRecebida;
  } else if (statusSelecionado === 'parcial') {
    calcularParcial();
  }
}

// Submit do formulário
document.getElementById('amostragemForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const qtdRecebida = parseInt(qtdRecebidaInput?.value) || 0;
  
  if (qtdRecebida <= 0) {
    alert('⚠️ Informe a quantidade recebida.');
    return;
  }
  
  // Validar se status foi selecionado
  if (!statusSelecionado) {
    alert('⚠️ Selecione o Status Final (Pendente, Aprovado, Parcial ou Reprovado).');
    return;
  }
  
  // Validar campo de observações (obrigatório se não for aprovado)
  if (!validarObservacoes()) {
    showToast(
      'warning', 
      'Campo Obrigatório', 
      'Por favor, preencha o campo "Descrição do Defeito ou Observações". Este campo é obrigatório quando o status não é "Aprovado".',
      6000
    );
    return;
  }
  
  // Preencher campos hidden baseado no status
  let testada = 0, aprovada = 0, reprovada = 0;
  
  if (statusSelecionado === 'pendente') {
    testada = 0;
    aprovada = 0;
    reprovada = 0;
    
  } else if (statusSelecionado === 'aprovado') {
    testada = parseInt(document.getElementById('qtdTestadaAprovado')?.value) || qtdRecebida;
    aprovada = qtdRecebida;
    reprovada = 0;
    
    if (testada <= 0) {
      alert('⚠️ Informe a quantidade testada.');
      document.getElementById('qtdTestadaAprovado')?.focus();
      return;
    }
    
  } else if (statusSelecionado === 'parcial') {
    testada = parseInt(document.getElementById('qtdTestadaParcial')?.value) || 0;
    const aprovadaTeste = parseInt(document.getElementById('qtdAprovadaParcial')?.value) || 0;
    const naoTestadas = qtdRecebida - testada;
    aprovada = aprovadaTeste + (naoTestadas > 0 ? naoTestadas : 0);
    reprovada = testada - aprovadaTeste;
    
    if (testada <= 0) {
      alert('⚠️ Informe a quantidade testada.');
      document.getElementById('qtdTestadaParcial')?.focus();
      return;
    }
    
    if (aprovadaTeste > testada) {
      alert('⚠️ Quantidade aprovada não pode ser maior que a testada.');
      return;
    }
    
  } else if (statusSelecionado === 'reprovado') {
    testada = parseInt(document.getElementById('qtdTestadaReprovado')?.value) || qtdRecebida;
    aprovada = 0;
    reprovada = qtdRecebida;
    
    if (testada <= 0) {
      alert('⚠️ Informe a quantidade testada.');
      document.getElementById('qtdTestadaReprovado')?.focus();
      return;
    }
  }
  
  // Preencher campos hidden
  document.getElementById('hiddenTestada').value = testada;
  document.getElementById('hiddenAprovada').value = aprovada;
  document.getElementById('hiddenReprovada').value = reprovada;
  
  const formData = new FormData(this);
  formData.set('status_selecionado', statusSelecionado);
  
  try {
    const response = await fetch(this.action, {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      alert(result.message);
      if (result.redirect) {
        window.location.href = result.redirect;
      } else {
        window.location.reload();
      }
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    alert('Erro ao enviar formulário');
  }
});

// Baixar evidências
async function baixarEvidencias(amostragemId) {
  console.log('Baixando evidências para amostragem:', amostragemId);
  
  // Mostrar modal de loading
  document.getElementById('loadingModal').classList.remove('hidden');
  
  try {
    const response = await fetch(`/amostragens-2/${amostragemId}/evidencias`);
    const data = await response.json();
    
    console.log('Resposta do servidor:', data);
    
    if (data.success && data.evidencias && data.evidencias.length > 0) {
      console.log('Evidências encontradas:', data.evidencias.length);
      
      // Baixar cada evidência individualmente
      for (let i = 0; i < data.evidencias.length; i++) {
        const ev = data.evidencias[i];
        console.log(`Baixando evidência ${i + 1}/${data.evidencias.length}: ${ev.nome}`);
        
        // Criar link temporário para download
        const link = document.createElement('a');
        link.href = `/amostragens-2/${amostragemId}/download-evidencia/${ev.id}`;
        link.download = ev.nome;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Pequeno delay entre downloads para não sobrecarregar
        if (i < data.evidencias.length - 1) {
          await new Promise(resolve => setTimeout(resolve, 500));
        }
      }
      
      // Fechar modal e mostrar sucesso
      document.getElementById('loadingModal').classList.add('hidden');
      alert(`✅ ${data.evidencias.length} evidência(s) baixada(s) com sucesso!`);
      
    } else {
      document.getElementById('loadingModal').classList.add('hidden');
      alert('⚠️ Nenhuma evidência encontrada para esta amostragem');
    }
    
  } catch (error) {
    console.error('Erro ao baixar evidências:', error);
    document.getElementById('loadingModal').classList.add('hidden');
    alert('❌ Erro ao baixar evidências: ' + error.message);
  }
}

// Função de email removida - mantendo apenas notificações visuais

// Editar amostragem
async function editarAmostragem(id) {
  try {
    console.log('Carregando amostragem para edição:', id);
    
    // Buscar dados da amostragem via API JSON
    const response = await fetch(`/amostragens-2/${id}/details-json`);
    
    // Verificar se a resposta é válida
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    // Verificar se tem conteúdo antes de fazer parse
    const text = await response.text();
    console.log('Resposta do servidor:', text);
    
    if (!text) {
      throw new Error('Resposta vazia do servidor');
    }
    
    const result = JSON.parse(text);
    
    if (!result.success) {
      alert('Erro ao carregar amostragem: ' + result.message);
      return;
    }
    
    const amostra = result.amostragem;
    console.log('Dados carregados:', amostra);
    
    // Alterar título do formulário
    document.getElementById('formTitle').textContent = '✏️ Editar Amostragem';
    
    // Alterar action do formulário para update
    document.getElementById('amostragemForm').action = '/amostragens-2/update';
    
    // Adicionar campo hidden com ID da amostragem
    let hiddenId = document.querySelector('input[name="amostragem_id"]');
    if (!hiddenId) {
      hiddenId = document.createElement('input');
      hiddenId.type = 'hidden';
      hiddenId.name = 'amostragem_id';
      document.getElementById('amostragemForm').appendChild(hiddenId);
    }
    hiddenId.value = id;
    
    // Preencher campos do formulário
    document.querySelector('input[name="numero_nf"]').value = amostra.numero_nf || '';
    
    // Tipo de produto
    document.getElementById('tipoProduto').value = amostra.tipo_produto || '';
    carregarProdutos(); // Carregar lista de produtos
    
    // Aguardar produtos carregarem e selecionar o correto
    setTimeout(() => {
      const produtoSelect = document.getElementById('produtoSelect');
      produtoSelect.value = amostra.produto_id || '';
      
      // Disparar evento change para preencher campos hidden
      const event = new Event('change');
      produtoSelect.dispatchEvent(event);
    }, 100);
    
    // Quantidades
    document.querySelector('input[name="quantidade_recebida"]').value = amostra.quantidade_recebida || '';
    document.getElementById('hiddenTestada').value = amostra.quantidade_testada || '';
    document.getElementById('hiddenAprovada').value = amostra.quantidade_aprovada || '';
    document.getElementById('hiddenReprovada').value = amostra.quantidade_reprovada || '';
    
    // Fornecedor
    document.querySelector('select[name="fornecedor_id"]').value = amostra.fornecedor_id || '';
    
    // Responsáveis (múltipla seleção)
    if (amostra.responsaveis) {
      const responsaveisIds = amostra.responsaveis.split(',').map(id => id.trim());
      const responsaveisSelect = document.querySelector('select[name="responsaveis[]"]');
      
      for (let option of responsaveisSelect.options) {
        option.selected = responsaveisIds.includes(option.value);
      }
    }
    
    // Status - Selecionar o botão correspondente ao status salvo
    if (amostra.status_final) {
      const statusMap = {
        'Pendente': 'pendente',
        'Aprovado': 'aprovado',
        'Aprovado Parcialmente': 'parcial',
        'Reprovado': 'reprovado'
      };
      const statusKey = statusMap[amostra.status_final] || 'pendente';
      selecionarStatus(statusKey);
      
      // Preencher os campos de quantidade baseado no status
      if (statusKey === 'aprovado') {
        document.getElementById('qtdTestadaAprovado').value = amostra.quantidade_testada || '';
      } else if (statusKey === 'parcial') {
        document.getElementById('qtdTestadaParcial').value = amostra.quantidade_testada || '';
        document.getElementById('qtdAprovadaParcial').value = amostra.quantidade_aprovada || '';
        calcularParcial();
      } else if (statusKey === 'reprovado') {
        document.getElementById('qtdTestadaReprovado').value = amostra.quantidade_testada || '';
      }
    }
    
    // Descrição do Defeito ou Observações
    document.getElementById('observacoesInput').value = amostra.observacoes || '';
    
    // Mostrar anexo NF existente se houver
    if (amostra.anexo_nf_nome) {
      document.getElementById('anexoNfExistente').classList.remove('hidden');
      document.getElementById('anexoNfNome').textContent = amostra.anexo_nf_nome;
    } else {
      document.getElementById('anexoNfExistente').classList.add('hidden');
    }
    
    // Buscar e mostrar evidências existentes
    const evidResponse = await fetch(`/amostragens-2/${id}/evidencias`);
    const evidResult = await evidResponse.json();
    
    if (evidResult.success && evidResult.evidencias && evidResult.evidencias.length > 0) {
      const listaEvidencias = document.getElementById('listaEvidencias');
      listaEvidencias.innerHTML = '';
      
      evidResult.evidencias.forEach(ev => {
        const div = document.createElement('div');
        div.className = 'bg-gray-700 p-2 rounded text-xs';
        div.innerHTML = `
          <p class="text-gray-300 truncate" title="${ev.nome}">${ev.nome}</p>
          <p class="text-gray-500">${(ev.tamanho / 1024).toFixed(1)} KB</p>
        `;
        listaEvidencias.appendChild(div);
      });
      
      document.getElementById('evidenciasExistentes').classList.remove('hidden');
    } else {
      document.getElementById('evidenciasExistentes').classList.add('hidden');
    }
    
    // Alterar texto do botão
    document.getElementById('submitButton').innerHTML = '💾 Atualizar Amostragem';
    
    // Mostrar formulário
    document.getElementById('amostragemFormContainer').classList.remove('hidden');
    
    // Scroll para o formulário
    document.getElementById('amostragemFormContainer').scrollIntoView({ behavior: 'smooth' });
    
    console.log('Formulário preenchido e pronto para edição');
    
  } catch (error) {
    console.error('Erro ao carregar amostragem:', error);
    alert('Erro ao carregar dados da amostragem: ' + error.message);
  }
}

// Nova amostragem
function novaAmostragem() {
  // Limpar formulário
  document.getElementById('amostragemForm').reset();
  
  // Remover campo hidden de ID se existir
  const hiddenId = document.querySelector('input[name="amostragem_id"]');
  if (hiddenId) {
    hiddenId.remove();
  }
  
  // Restaurar título do formulário
  document.getElementById('formTitle').textContent = '🔬 Nova Amostragem';
  
  // Restaurar action original e texto do botão
  document.getElementById('amostragemForm').action = '/amostragens-2/store';
  document.getElementById('submitButton').innerHTML = '💾 Salvar Amostragem';
  
  // Esconder seções de anexos existentes
  document.getElementById('anexoNfExistente').classList.add('hidden');
  document.getElementById('evidenciasExistentes').classList.add('hidden');
  
  // Mostrar formulário
  document.getElementById('amostragemFormContainer').classList.remove('hidden');
  
  // Scroll para o formulário
  document.getElementById('amostragemFormContainer').scrollIntoView({ behavior: 'smooth' });
  
  console.log('Formulário preparado para nova amostragem');
}

// Cancelar edição
function cancelarEdicao() {
  // Limpar formulário
  document.getElementById('amostragemForm').reset();
  
  // Remover campo hidden de ID se existir
  const hiddenId = document.querySelector('input[name="amostragem_id"]');
  if (hiddenId) {
    hiddenId.remove();
  }
  
  // Restaurar título do formulário
  document.getElementById('formTitle').textContent = '🔬 Nova Amostragem';
  
  // Restaurar action original e texto do botão
  document.getElementById('amostragemForm').action = '/amostragens-2/store';
  document.getElementById('submitButton').innerHTML = '💾 Salvar Amostragem';
  
  // Esconder seções de anexos existentes
  document.getElementById('anexoNfExistente').classList.add('hidden');
  document.getElementById('evidenciasExistentes').classList.add('hidden');
  
  // Esconder formulário
  document.getElementById('amostragemFormContainer').classList.add('hidden');
  
  console.log('Edição cancelada, formulário limpo');
}

// Excluir amostragem
async function excluirAmostragem(id) {
  if (!confirm('Tem certeza que deseja excluir esta amostragem?')) return;
  
  try {
    const response = await fetch('/amostragens-2/delete', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `id=${id}`
    });
    
    const result = await response.json();
    alert(result.message);
    
    if (result.success) {
      window.location.reload();
    }
  } catch (error) {
    alert('Erro ao excluir amostragem');
  }
}

// Alterar quantidade de registros por página
function alterarPorPagina(porPagina) {
  const urlParams = new URLSearchParams(window.location.search);
  urlParams.set('por_pagina', porPagina);
  urlParams.set('pagina', '1'); // Resetar para primeira página
  window.location.href = '/amostragens-2?' + urlParams.toString();
}

// Exportar para Excel
function exportarExcel() {
  // Coletar filtros ativos
  const params = new URLSearchParams();
  
  const codigoProduto = document.getElementById('filtroCodigo')?.value;
  if (codigoProduto) params.append('codigo_produto', codigoProduto);
  
  const userId = document.getElementById('filtroUsuario')?.value;
  if (userId) params.append('user_id', userId);
  
  const filialId = document.getElementById('filtroFilial')?.value;
  if (filialId) params.append('filial_id', filialId);
  
  const fornecedorId = document.getElementById('filtroFornecedor')?.value;
  if (fornecedorId) params.append('fornecedor_id', fornecedorId);
  
  const statusFinal = document.getElementById('filtroStatus')?.value;
  if (statusFinal) params.append('status_final', statusFinal);
  
  const dataInicio = document.getElementById('filtroDataInicio')?.value;
  if (dataInicio) params.append('data_inicio', dataInicio);
  
  const dataFim = document.getElementById('filtroDataFim')?.value;
  if (dataFim) params.append('data_fim', dataFim);
  
  // Redirecionar para exportação
  const url = `/amostragens-2/export?${params.toString()}`;
  window.location.href = url;
}

// Alterar status da amostragem
async function alterarStatus(id, novoStatus) {
  // Salvar o select que foi alterado para poder reverter se cancelar
  const selectElement = event.target;
  const oldValue = selectElement.getAttribute('data-old-value') || selectElement.value;
  
  if (!confirm(`Tem certeza que deseja alterar o status para "${novoStatus}"?\n\nUm email será enviado aos responsáveis.`)) {
    // Reverter select ao valor anterior
    selectElement.value = oldValue;
    return;
  }
  
  // Desabilitar select durante o processamento
  selectElement.disabled = true;
  
  try {
    console.log(`🔄 Alterando status da amostragem ${id} para: ${novoStatus}`);
    
    const response = await fetch('/amostragens-2/update-status', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `id=${id}&status=${encodeURIComponent(novoStatus)}`
    });
    
    const result = await response.json();
    console.log('📡 Resposta do servidor:', result);
    
    if (result.success) {
      console.log('✅ Status atualizado com sucesso!');
      alert('✅ ' + result.message + '\n\n📧 Email enviado aos responsáveis!');
      
      // Recarregar página para mostrar mudanças
      console.log('🔄 Recarregando página...');
      window.location.reload();
    } else {
      alert('❌ Erro: ' + result.message);
      // Reverter select ao valor anterior
      selectElement.value = oldValue;
      selectElement.disabled = false;
    }
  } catch (error) {
    console.error('❌ Erro ao alterar status:', error);
    alert('❌ Erro ao alterar status: ' + error.message);
    // Reverter select ao valor anterior
    selectElement.value = oldValue;
    selectElement.disabled = false;
  }
}
// --- Funcionalidades Avançadas do Grid (Zoom e Resize) ---

const ZOOM_KEY = 'amostragens2_zoom';
const COL_WIDTHS_KEY = 'amostragens2_col_widths';
const COL_VISIBILITY_KEY = 'amostragens2_col_visibility';

const ALL_COLUMNS = [
  { id: 0, name: 'Data' },
  { id: 1, name: 'NF' },
  { id: 2, name: 'Usuário' },
  { id: 3, name: 'Filial' },
  { id: 4, name: 'Tipo' },
  { id: 5, name: 'Código' },
  { id: 6, name: 'Fornecedor' },
  { id: 7, name: 'Qtd Recebida' },
  { id: 8, name: 'Qtd Testada' },
  { id: 9, name: 'Aprovada' },
  { id: 10, name: 'Reprovada' },
  { id: 11, name: 'Status' },
  { id: 12, name: 'Aprovado Por' },
  { id: 13, name: 'Anexo NF' },
  { id: 14, name: 'Evidências' },
  { id: 15, name: 'Ações' }
];

// Inicializar Zoom e Colunas
document.addEventListener('DOMContentLoaded', function() {
  // Carregar Zoom
  const savedZoom = localStorage.getItem(ZOOM_KEY);
  if (savedZoom) {
    updateZoom(savedZoom, false);
    document.getElementById('gridZoom').value = savedZoom;
  }

  // Carregar Larguras das Colunas
  const savedWidths = JSON.parse(localStorage.getItem(COL_WIDTHS_KEY) || '{}');
  const table = document.getElementById('amostragensTable');
  const headers = table.querySelectorAll('th');
  
  headers.forEach((th, index) => {
    if (savedWidths[index]) {
      th.style.width = savedWidths[index] + 'px';
    }
    
    const resizer = th.querySelector('.resizer');
    if (resizer) {
      initResizer(resizer, th, index);
    }
  });

  // Carregar Visibilidade das Colunas
  const savedVisibility = JSON.parse(localStorage.getItem(COL_VISIBILITY_KEY) || '[]');
  if (savedVisibility.length > 0) {
    applyColumnVisibility(savedVisibility);
  }

  // Sincronização de Scroll
  setupScrollSync();
});

// Lógica de Scroll Sincronizado
function setupScrollSync() {
  const topScroll = document.getElementById('grid-top-scroll');
  const bottomScroll = document.getElementById('grid-scroll');
  const inner = document.getElementById('grid-top-scroll-inner');
  const table = document.getElementById('amostragensTable');

  if (!topScroll || !bottomScroll || !inner || !table) return;

  // Ajustar largura do conteúdo do scroll superior
  const resizeObserver = new ResizeObserver(() => {
    inner.style.width = table.offsetWidth + 'px';
  });
  resizeObserver.observe(table);

  topScroll.addEventListener('scroll', () => {
    bottomScroll.scrollLeft = topScroll.scrollLeft;
  });

  bottomScroll.addEventListener('scroll', () => {
    topScroll.scrollLeft = bottomScroll.scrollLeft;
  });
}

// Lógica de Personalização de Colunas
function abrirModalColunas() {
  const container = document.getElementById('colunas-lista');
  const savedVisibility = JSON.parse(localStorage.getItem(COL_VISIBILITY_KEY) || '[]');
  
  container.innerHTML = '';
  
  ALL_COLUMNS.forEach(col => {
    const isHidden = savedVisibility.includes(col.id);
    const div = document.createElement('div');
    div.className = 'flex items-center gap-3 p-3 bg-gray-50 dark:bg-slate-900 shadow-sm rounded-xl border border-gray-100 dark:border-slate-700/50 hover:border-blue-300 transition-all';
    div.innerHTML = `
      <label class="flex items-center gap-3 w-full cursor-pointer group">
        <div class="relative inline-flex items-center cursor-pointer">
          <input type="checkbox" value="${col.id}" ${!isHidden ? 'checked' : ''} class="sr-only peer">
          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
        </div>
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 group-hover:text-blue-500 transition-colors uppercase tracking-wider">${col.name}</span>
      </label>
    `;
    container.appendChild(div);
  });
  
  document.getElementById('modal-colunas').classList.remove('hidden');
}

function fecharModalColunas() {
  document.getElementById('modal-colunas').classList.add('hidden');
}

function salvarPreferenciasColunas() {
  const checkboxes = document.getElementById('colunas-lista').querySelectorAll('input[type="checkbox"]');
  const hiddenColumns = [];
  
  checkboxes.forEach(cb => {
    if (!cb.checked) {
      hiddenColumns.push(parseInt(cb.value));
    }
  });
  
  localStorage.setItem(COL_VISIBILITY_KEY, JSON.stringify(hiddenColumns));
  applyColumnVisibility(hiddenColumns);
  fecharModalColunas();
}

function resetColunasPadrao() {
  localStorage.removeItem(COL_VISIBILITY_KEY);
  applyColumnVisibility([]);
  fecharModalColunas();
}

function applyColumnVisibility(hiddenIds) {
  const table = document.getElementById('amostragensTable');
  const rows = table.rows;
  
  for (let i = 0; i < rows.length; i++) {
    const cells = rows[i].cells;
    for (let j = 0; j < cells.length; j++) {
      if (hiddenIds.includes(j)) {
        cells[j].style.display = 'none';
      } else {
        cells[j].style.display = '';
      }
    }
  }
}

// Lógica de Zoom
function updateZoom(value, save = true) {
  document.documentElement.style.setProperty('--grid-zoom', value + '%');
  document.getElementById('zoomValue').textContent = value + '%';
  if (save) localStorage.setItem(ZOOM_KEY, value);
}

function resetZoom() {
  document.getElementById('gridZoom').value = 100;
  updateZoom(100);
}

// Lógica de Redimensionamento de Colunas
function initResizer(resizer, th, colIndex) {
  let x = 0;
  let w = 0;

  const mouseDownHandler = function(e) {
    x = e.clientX;
    const styles = window.getComputedStyle(th);
    w = parseInt(styles.width, 10);

    document.addEventListener('mousemove', mouseMoveHandler);
    document.addEventListener('mouseup', mouseUpHandler);
    resizer.classList.add('resizing');
  };

  const mouseMoveHandler = function(e) {
    const dx = e.clientX - x;
    const newWidth = Math.max(80, w + dx);
    th.style.width = `${newWidth}px`;
  };

  const mouseUpHandler = function() {
    document.removeEventListener('mousemove', mouseMoveHandler);
    document.removeEventListener('mouseup', mouseUpHandler);
    resizer.classList.remove('resizing');
    
    // Salvar no localStorage
    saveColumnWidths();
  };

  resizer.addEventListener('mousedown', mouseDownHandler);
}

function saveColumnWidths() {
  const table = document.getElementById('amostragensTable');
  const headers = table.querySelectorAll('th');
  const widths = {};
  
  headers.forEach((th, index) => {
    widths[index] = th.offsetWidth;
  });
  
  localStorage.setItem(COL_WIDTHS_KEY, JSON.stringify(widths));
}
</script>
