<?php
// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
$userName = $_SESSION['user_name'] ?? $_SESSION['name'] ?? '';
?>

<section class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Requisição de Garantias</h1>
            <p class="text-gray-600 mt-1">Abra uma nova requisição de garantia para um produto com defeito</p>
        </div>
        <div class="flex space-x-3">
            <a href="/garantias" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Voltar</span>
            </a>
        </div>
    </div>

    <!-- Formulário de Requisição -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-6">
            <h2 class="text-xl font-bold text-white flex items-center gap-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Nova Requisição de Garantia
            </h2>
            <p class="text-blue-100 mt-1">Preencha os dados abaixo para abrir uma nova requisição</p>
        </div>
        
        <form id="formRequisicao" class="p-8 space-y-6">
            <!-- Nome do Requisitante -->
            <div>
                <label for="nome_requisitante" class="block text-sm font-semibold text-gray-700 mb-2">
                    <span class="text-red-500">*</span> Nome do Requisitante
                </label>
                <input type="text" id="nome_requisitante" name="nome_requisitante" 
                       value="<?= htmlspecialchars($userName) ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                       placeholder="Digite seu nome completo" required>
            </div>
            
            <!-- Produto com Problema -->
            <div>
                <label for="produto" class="block text-sm font-semibold text-gray-700 mb-2">
                    <span class="text-red-500">*</span> Produto com Problema
                </label>
                <input type="text" id="produto" name="produto" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                       placeholder="Ex: Toner HP CF258A, Impressora Lexmark MS610..." required>
            </div>
            
            <!-- Descrição do Defeito -->
            <div>
                <label for="descricao_defeito" class="block text-sm font-semibold text-gray-700 mb-2">
                    <span class="text-red-500">*</span> Descrição do Defeito
                </label>
                <textarea id="descricao_defeito" name="descricao_defeito" rows="4"
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none"
                          placeholder="Descreva detalhadamente o defeito encontrado no produto..." required></textarea>
            </div>
            
            <!-- Upload de Imagens -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Anexar Imagens de Evidência (opcional)
                </label>
                <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-500 transition-colors cursor-pointer">
                    <input type="file" id="imagens" name="imagens[]" multiple accept="image/*" class="hidden">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-gray-600 font-medium">Clique aqui ou arraste imagens</p>
                    <p class="text-sm text-gray-400 mt-1">PNG, JPG, JPEG até 5MB cada</p>
                </div>
                <div id="previewImagens" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
            </div>
            
            <!-- Notificar Setores (obrigatório) -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <span class="text-red-500">*</span> Notificar Setores
                    <span class="text-gray-400 font-normal text-xs ml-1">(selecione ao menos um — o sistema enviará email aos usuários do setor)</span>
                </label>
                <div class="flex flex-wrap gap-2" id="setoresContainer">
                    <?php if (!empty($departamentos_lista)): ?>
                        <?php foreach ($departamentos_lista as $dep): ?>
                            <label class="inline-flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition-all has-[:checked]:bg-blue-50 has-[:checked]:border-blue-400 has-[:checked]:text-blue-700 select-none">
                                <input type="checkbox" name="notificar_setores[]" value="<?= htmlspecialchars($dep['nome']) ?>"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($dep['nome']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="text-sm text-gray-400 italic">Nenhum departamento cadastrado.</span>
                    <?php endif; ?>
                </div>
                <p id="setoresErro" class="hidden text-red-600 text-sm mt-2 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    Selecione ao menos um setor para notificar.
                </p>
            </div>

            <!-- Botão de Envio -->
            <div class="pt-4">
                <button type="submit" id="btnEnviar"
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-4 px-8 rounded-xl transition-all transform hover:scale-[1.02] flex items-center justify-center gap-3 shadow-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    <span>Enviar Requisição de Garantia</span>
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Modal de Ticket Gerado (para impressão) -->
<div id="modalTicket" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" style="z-index: 999999;">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto relative" id="ticketContent">
        
        <!-- Botão X para fechar -->
        <button onclick="fecharModal()" class="absolute top-4 right-4 z-10 bg-white/20 hover:bg-white/40 rounded-full p-2 transition-all print:hidden">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        
        <!-- Cabeçalho do Ticket -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-6 text-center">
            <!-- Mensagem de Sucesso -->
            <div class="bg-green-500/20 border border-green-400/50 rounded-xl px-4 py-3 mb-4 print:hidden">
                <div class="flex items-center justify-center gap-2 text-green-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-bold text-lg">Requisição aberta com sucesso!</span>
                </div>
            </div>
            
            <div class="flex justify-center mb-3">
                <img src="/assets/img/logo-oti.png" alt="OTI Logo" class="h-12" onerror="this.style.display='none'">
            </div>
            <h2 class="text-2xl font-bold text-white">REQUISIÇÃO DE GARANTIA</h2>
            <p class="text-blue-100 mt-1">Sistema de Gestão da Qualidade - OTI</p>
        </div>
        
        <!-- Número do Ticket -->
        <div class="bg-gray-100 px-8 py-6 text-center border-b-2 border-dashed border-gray-300">
            <p class="text-gray-600 font-medium mb-2">Número do Ticket</p>
            <p id="ticketNumero" class="text-4xl font-bold text-blue-600 font-mono tracking-wider">-</p>
            <p class="text-sm text-gray-500 mt-2">Guarde este número para acompanhamento</p>
        </div>
        
        <!-- Dados da Requisição -->
        <div class="px-8 py-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Data de Abertura</p>
                    <p id="ticketData" class="text-lg font-semibold text-gray-800">-</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Status</p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                        Aguardando Recebimento
                    </span>
                </div>
            </div>
            
            <div class="border-t pt-4">
                <p class="text-sm text-gray-500 font-medium mb-1">Requisitante</p>
                <p id="ticketRequisitante" class="text-lg font-semibold text-gray-800">-</p>
            </div>
            
            <div class="border-t pt-4">
                <p class="text-sm text-gray-500 font-medium mb-1">Produto</p>
                <p id="ticketProduto" class="text-lg font-semibold text-gray-800">-</p>
            </div>
            
            <div class="border-t pt-4">
                <p class="text-sm text-gray-500 font-medium mb-1">Descrição do Defeito</p>
                <p id="ticketDescricao" class="text-gray-700 bg-gray-50 p-3 rounded-lg">-</p>
            </div>
            
            <div id="ticketImagensContainer" class="border-t pt-4 hidden">
                <p class="text-sm text-gray-500 font-medium mb-2">Imagens Anexadas</p>
                <p class="text-sm text-gray-600"><span id="ticketQtdImagens">0</span> imagem(ns) anexada(s)</p>
            </div>
        </div>
        
        <!-- Rodapé com Instruções -->
        <div class="bg-yellow-50 border-t-2 border-yellow-200 px-8 py-6">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <p class="font-bold text-yellow-800">INSTRUÇÕES IMPORTANTES</p>
                    <p class="text-yellow-700 mt-1">
                        Imprima esta requisição e envie <strong>junto com o produto/peça</strong> para o departamento responsável pela análise de garantias.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Botões de Ação -->
        <div class="px-8 py-6 bg-gray-50 flex flex-col sm:flex-row gap-3 print:hidden">
            <button onclick="window.print()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl flex items-center justify-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Imprimir Requisição
            </button>
            <button onclick="fecharModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-xl flex items-center justify-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nova Requisição
            </button>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #modalTicket, #modalTicket * {
        visibility: visible;
    }
    #modalTicket {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background: white !important;
    }
    #ticketContent {
        max-height: none !important;
        overflow: visible !important;
        box-shadow: none !important;
    }
    .print\\:hidden {
        display: none !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mover modal para o body para ficar fullscreen
    const modal = document.getElementById('modalTicket');
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    
    const form = document.getElementById('formRequisicao');
    const dropZone = document.getElementById('dropZone');
    const inputImagens = document.getElementById('imagens');
    const previewContainer = document.getElementById('previewImagens');
    let arquivosSelecionados = [];
    
    // Drag and drop
    dropZone.addEventListener('click', () => inputImagens.click());
    
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-blue-500', 'bg-blue-50');
    });
    
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        handleFiles(e.dataTransfer.files);
    });
    
    inputImagens.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });
    
    function handleFiles(files) {
        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/') && file.size <= 5 * 1024 * 1024) {
                arquivosSelecionados.push(file);
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'relative group';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-24 object-cover rounded-lg border">
                        <button type="button" onclick="removerImagem(${arquivosSelecionados.length - 1})" 
                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            ×
                        </button>
                        <p class="text-xs text-gray-500 truncate mt-1">${file.name}</p>
                    `;
                    previewContainer.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    window.removerImagem = function(index) {
        arquivosSelecionados.splice(index, 1);
        previewContainer.innerHTML = '';
        arquivosSelecionados.forEach((file, i) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const div = document.createElement('div');
                div.className = 'relative group';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-24 object-cover rounded-lg border">
                    <button type="button" onclick="removerImagem(${i})" 
                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        ×
                    </button>
                    <p class="text-xs text-gray-500 truncate mt-1">${file.name}</p>
                `;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    };
    
    // Submit form
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Validar setores
        const setoresMarcados = form.querySelectorAll('input[name="notificar_setores[]"]');
        const algumMarcado = Array.from(setoresMarcados).some(cb => cb.checked);
        const erroSetores = document.getElementById('setoresErro');
        if (!algumMarcado) {
            if (erroSetores) erroSetores.classList.remove('hidden');
            document.getElementById('setoresContainer')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        if (erroSetores) erroSetores.classList.add('hidden');
        
        const btnEnviar = document.getElementById('btnEnviar');
        btnEnviar.disabled = true;
        btnEnviar.innerHTML = `
            <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Enviando...</span>
        `;
        
        try {
            const formData = new FormData();
            formData.append('nome_requisitante', document.getElementById('nome_requisitante').value);
            formData.append('produto', document.getElementById('produto').value);
            formData.append('descricao_defeito', document.getElementById('descricao_defeito').value);

            // Adicionar setores selecionados
            form.querySelectorAll('input[name="notificar_setores[]"]:checked').forEach(cb => {
                formData.append('notificar_setores[]', cb.value);
            });
            
            arquivosSelecionados.forEach((file, i) => {
                formData.append(`imagens[${i}]`, file);
            });
            
            const response = await fetch('/garantias/requisicao/criar', {
                method: 'POST',
                body: formData
            });
            
            const responseText = await response.text();
            let result = null;

            try {
                result = responseText ? JSON.parse(responseText) : null;
            } catch (parseError) {
                throw new Error(`Resposta invalida do servidor (HTTP ${response.status}).`);
            }

            if (!result) {
                throw new Error(`Servidor retornou uma resposta vazia (HTTP ${response.status}).`);
            }
            
            if (result.success) {
                // Preencher modal do ticket
                document.getElementById('ticketNumero').textContent = result.data.ticket;
                document.getElementById('ticketData').textContent = result.data.data;
                document.getElementById('ticketRequisitante').textContent = result.data.nome_requisitante;
                document.getElementById('ticketProduto').textContent = result.data.produto;
                document.getElementById('ticketDescricao').textContent = result.data.descricao_defeito;
                
                if (result.data.qtd_imagens > 0) {
                    document.getElementById('ticketImagensContainer').classList.remove('hidden');
                    document.getElementById('ticketQtdImagens').textContent = result.data.qtd_imagens;
                }
                
                // Mostrar modal
                document.getElementById('modalTicket').classList.remove('hidden');
                
                // Limpar formulário
                form.reset();
                previewContainer.innerHTML = '';
                arquivosSelecionados = [];
                
            } else {
                alert('Erro: ' + result.message);
            }
            
        } catch (error) {
            alert('Erro ao enviar requisição: ' + error.message);
        } finally {
            btnEnviar.disabled = false;
            btnEnviar.innerHTML = `
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
                <span>Enviar Requisição de Garantia</span>
            `;
        }
    });
});

function fecharModal() {
    document.getElementById('modalTicket').classList.add('hidden');
}
</script>
