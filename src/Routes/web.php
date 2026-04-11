<?php
/**
 * Rotas Diversas e Especiais
 * 
 * Registros, Suporte, Financeiro, Master, Área Técnica, Profile
 */

use App\Controllers\RegistrosController;
use App\Controllers\ClientesController;
use App\Controllers\SuporteController;
use App\Controllers\FinanceiroController;
use App\Controllers\MasterController;
use App\Controllers\AreaTecnicaController;
use App\Controllers\ProfileController;
use App\Controllers\TriagemTonersController;
use App\Controllers\CadastroDefeitosController;
use App\Controllers\PrecificacaoColetaDescartesController;
use App\Controllers\ELearningGestorController;
use App\Controllers\ELearningColaboradorController;

// ===== REGISTROS GERAIS =====

$router->get('/registros/filiais', [RegistrosController::class , 'filiais']);
$router->get('/registros/departamentos', [RegistrosController::class , 'departamentos']);
$router->get('/registros/fornecedores', [RegistrosController::class , 'fornecedores']);
$router->get('/registros/parametros', [RegistrosController::class , 'parametros']);

// Store
$router->post('/registros/filiais/store', [RegistrosController::class , 'storeFilial']);
$router->post('/registros/departamentos/store', [RegistrosController::class , 'storeDepartamento']);
$router->post('/registros/fornecedores/store', [RegistrosController::class , 'storeFornecedor']);
$router->post('/registros/parametros/store', [RegistrosController::class , 'storeParametro']);

// Update
$router->post('/registros/filiais/update', [RegistrosController::class , 'updateFilial']);
$router->post('/registros/departamentos/update', [RegistrosController::class , 'updateDepartamento']);
$router->post('/registros/fornecedores/update', [RegistrosController::class , 'updateFornecedor']);
$router->post('/registros/parametros/update', [RegistrosController::class , 'updateParametro']);

// Delete
$router->post('/registros/filiais/delete', [RegistrosController::class , 'deleteFilial']);
$router->post('/registros/departamentos/delete', [RegistrosController::class , 'deleteDepartamento']);
$router->post('/registros/fornecedores/delete', [RegistrosController::class , 'deleteFornecedor']);
$router->post('/registros/parametros/delete', [RegistrosController::class , 'deleteParametro']);

// ===== CADASTRO DE CLIENTES =====

$router->get('/cadastros/clientes', [ClientesController::class , 'index']);
$router->get('/cadastros/clientes/listar', [ClientesController::class , 'listar']);
$router->post('/cadastros/clientes/criar', [ClientesController::class , 'criar']);
$router->post('/cadastros/clientes/atualizar', [ClientesController::class , 'atualizar']);
$router->post('/cadastros/clientes/excluir', [ClientesController::class , 'excluir']);
$router->post('/cadastros/clientes/importar', [ClientesController::class , 'importar']);
$router->get('/cadastros/clientes/exportar', [ClientesController::class , 'exportar']);
$router->get('/cadastros/clientes/template', [ClientesController::class , 'template']);

// Contratos (placeholder)
$router->get('/cadastros/contratos', function () {
    $viewFile = __DIR__ . '/../views/pages/cadastros/contratos.php';
    include __DIR__ . '/../views/layouts/main.php';
});

// ===== SUPORTE =====

$router->get('/suporte', [SuporteController::class , 'index']);
$router->post('/suporte/store', [SuporteController::class , 'store']);
$router->post('/suporte/update-status', [SuporteController::class , 'updateStatus']);
$router->post('/suporte/delete', [SuporteController::class , 'delete']);
$router->get('/suporte/{id}/details', [SuporteController::class , 'details']);
$router->get('/suporte/anexo/{anexoId}', [SuporteController::class , 'downloadAnexo']);

// ===== FINANCEIRO =====

$router->get('/financeiro', [FinanceiroController::class , 'index']);
$router->post('/financeiro/anexar-comprovante', [FinanceiroController::class , 'anexarComprovante']);
$router->get('/financeiro/{id}/download-comprovante', [FinanceiroController::class , 'downloadComprovante']);

// ===== MASTER (Aprovação de Pagamentos) =====

$router->get('/master/login', [MasterController::class , 'loginPage']);
$router->post('/master/auth', [MasterController::class , 'authenticate']);
$router->get('/master/dashboard', [MasterController::class , 'dashboard']);
$router->post('/master/aprovar-pagamento', [MasterController::class , 'aprovarPagamento']);
$router->get('/master/logout', [MasterController::class , 'logout']);

// ===== ÁREA TÉCNICA =====

$router->get('/area-tecnica', [AreaTecnicaController::class , 'index']);
$router->post('/area-tecnica/ativar-trial', [AreaTecnicaController::class , 'ativarTrial']);
$router->get('/area-tecnica/trial-status', [AreaTecnicaController::class , 'getTrialStatus']);

// Checklist Virtual (rota pública - sem login)
$router->get('/area-tecnica/checklist', [AreaTecnicaController::class , 'checklistPublico']);
$router->post('/area-tecnica/checklist/salvar', [AreaTecnicaController::class , 'salvarChecklist']);

// Consulta de Checklists
$router->get('/area-tecnica/consulta', [AreaTecnicaController::class , 'consultaChecklists']);
$router->get('/area-tecnica/checklists/buscar', [AreaTecnicaController::class , 'buscarChecklists']);
$router->get('/area-tecnica/checklists/listar', [AreaTecnicaController::class , 'listarTodosChecklists']);
$router->get('/area-tecnica/checklists/{id}', [AreaTecnicaController::class , 'verChecklist']);


// ===== PROFILE (Perfil do Usuário) =====

$router->get('/profile', [ProfileController::class , 'index']);

// Dashboard 2.0
$router->get('/dashboard-2', [App\Controllers\AdminController::class, 'dashboard2']);
$router->get('/dashboard-2/triagem', [App\Controllers\AdminController::class, 'dashboard2Triagem']);
$router->get('/dashboard-2/triagem/data', [App\Controllers\AdminController::class, 'dashboard2TriagemData']);
$router->get('/dashboard-2/triagem/reprovados', [App\Controllers\AdminController::class, 'dashboard2TriagemReprovados']);

// Cadastro de Defeitos (fallback)
$router->get('/cadastro-defeitos', [CadastroDefeitosController::class, 'index']);
$router->post('/cadastro-defeitos/store', [CadastroDefeitosController::class, 'store']);
$router->post('/cadastro-defeitos/update', [CadastroDefeitosController::class, 'update']);
$router->post('/cadastro-defeitos/delete', [CadastroDefeitosController::class, 'delete']);
$router->get('/cadastro-defeitos/{id}/imagem', [CadastroDefeitosController::class, 'imagem']);

// ===== TONERS COM DEFEITO =====
use App\Controllers\TonersController;
$router->get('/toners/defeitos', [TonersController::class , 'defeitos']);
$router->post('/toners/defeitos/store', [TonersController::class , 'storeDefeito']);
$router->get('/toners/defeitos/{id}/foto/{n}', [TonersController::class , 'downloadFotoDefeito']);
$router->post('/toners/defeitos/delete', [TonersController::class , 'deleteDefeito']);

// ===== TRIAGEM DE TONERS (fallback) =====
$router->get('/triagem-toners', [TriagemTonersController::class, 'index']);
$router->get('/triagem-toners/list', [TriagemTonersController::class, 'list']);
$router->get('/triagem-toners/template', [TriagemTonersController::class, 'downloadTemplate']);
$router->post('/triagem-toners/importar', [TriagemTonersController::class, 'importar']);
$router->post('/triagem-toners/calcular', [TriagemTonersController::class, 'calcular']);
$router->post('/triagem-toners/store', [TriagemTonersController::class, 'store']);
$router->post('/triagem-toners/update', [TriagemTonersController::class, 'update']);
$router->post('/triagem-toners/duplicate', [TriagemTonersController::class, 'duplicate']);
$router->post('/triagem-toners/delete', [TriagemTonersController::class, 'delete']);
$router->get('/triagem-toners/parametros', [TriagemTonersController::class, 'getParametrosApi']);
$router->post('/triagem-toners/parametros/save', [TriagemTonersController::class, 'saveParametros']);

// ===== PRECIFICAÇÃO DE COLETA DE DESCARTES =====
$router->get('/precificacao-coleta-descartes', [PrecificacaoColetaDescartesController::class, 'index']);
$router->get('/precificacao-coleta-descartes/list', [PrecificacaoColetaDescartesController::class, 'list']);
$router->post('/precificacao-coleta-descartes/create', [PrecificacaoColetaDescartesController::class, 'create']);
$router->post('/precificacao-coleta-descartes/update', [PrecificacaoColetaDescartesController::class, 'update']);
$router->post('/precificacao-coleta-descartes/delete', [PrecificacaoColetaDescartesController::class, 'delete']);

// ===== eLEARNING GESTOR =====
$router->get('/elearning/professor',                            [ELearningGestorController::class, 'dashboard']);
$router->get('/elearning/gestor',                            [ELearningGestorController::class, 'dashboard']);
$router->get('/elearning/gestor/cursos',                     [ELearningGestorController::class, 'cursos']);
$router->get('/elearning/gestor/armazenamento',              [ELearningGestorController::class, 'armazenamento']);
$router->get('/elearning/gestor/relatorios',                 [ELearningGestorController::class, 'relatorios']);
$router->post('/elearning/gestor/cursos/store',              [ELearningGestorController::class, 'storeCurso']);
$router->post('/elearning/gestor/cursos/update',             [ELearningGestorController::class, 'updateCurso']);
$router->post('/elearning/gestor/cursos/delete',             [ELearningGestorController::class, 'deleteCurso']);
$router->post('/elearning/gestor/cursos/delete-all',         [ELearningGestorController::class, 'deleteTodosCursos']);
$router->get('/elearning/gestor/cursos/thumbnail',           [ELearningGestorController::class, 'thumbnailCurso']);
$router->get('/elearning/gestor/cursos/{id}/aulas',          [ELearningGestorController::class, 'aulas']);
$router->post('/elearning/gestor/aulas/store',               [ELearningGestorController::class, 'storeAula']);
$router->post('/elearning/gestor/aulas/reorder',             [ELearningGestorController::class, 'reorderAula']);
$router->get('/elearning/gestor/aulas/{id}/video-status',    [ELearningGestorController::class, 'videoStatusAula']);
$router->get('/elearning/gestor/videos/{id}',                [ELearningGestorController::class, 'streamLessonVideo']);
$router->get('/elearning/gestor/anexos/{id}/download',       [ELearningGestorController::class, 'downloadAttachment']);
$router->post('/elearning/gestor/provas/delete',              [ELearningGestorController::class, 'deleteProva']);

$router->get('/elearning/gestor/diploma/config',            [ELearningGestorController::class, 'diplomaConfig']);
$router->post('/elearning/gestor/diploma/save',             [ELearningGestorController::class, 'saveDiplomaConfig']);
$router->get('/elearning/gestor/diploma/logo',              [ELearningGestorController::class, 'diplomaLogo']);
$router->post('/elearning/gestor/materiais/upload',          [ELearningGestorController::class, 'uploadMaterial']);
$router->post('/elearning/gestor/materiais/delete',          [ELearningGestorController::class, 'deleteMaterial']);
$router->post('/elearning/gestor/materiais/update',          [ELearningGestorController::class, 'updateMaterial']);
$router->get('/elearning/gestor/cursos/{id}/provas',         [ELearningGestorController::class, 'provas']);
$router->post('/elearning/gestor/provas/store',              [ELearningGestorController::class, 'storeProva']);
$router->post('/elearning/gestor/questoes/store',            [ELearningGestorController::class, 'storeQuestao']);
$router->get('/elearning/gestor/cursos/{id}/matriculas',     [ELearningGestorController::class, 'matriculas']);
$router->post('/elearning/gestor/matriculas/store',          [ELearningGestorController::class, 'matricularColaborador']);
$router->get('/elearning/gestor/cursos/{id}/progresso',      [ELearningGestorController::class, 'progressoDashboard']);
$router->post('/elearning/gestor/certificados/emitir',       [ELearningGestorController::class, 'emitirCertificado']);

// ===== eLEARNING COLABORADOR =====
$router->get('/elearning/aluno',                                  [ELearningColaboradorController::class, 'meusCursos']);
$router->get('/elearning/colaborador',                           [ELearningColaboradorController::class, 'meusCursos']);
$router->post('/elearning/colaborador/matricular',               [ELearningColaboradorController::class, 'matricularSe']);
$router->get('/elearning/colaborador/cursos/{id}',               [ELearningColaboradorController::class, 'verCurso']);
$router->get('/elearning/colaborador/cursos/{id}/continuar',     [ELearningColaboradorController::class, 'continuar']);
$router->get('/elearning/colaborador/materiais/{id}/assistir',   [ELearningColaboradorController::class, 'assistirAula']);
$router->get('/elearning/colaborador/aulas/{id}/video-status',   [ELearningColaboradorController::class, 'videoStatusAula']);
$router->get('/elearning/colaborador/videos/{id}',               [ELearningColaboradorController::class, 'streamLessonVideo']);
$router->get('/elearning/colaborador/anexos/{id}/download',      [ELearningColaboradorController::class, 'downloadAttachment']);
$router->post('/elearning/colaborador/progresso/registrar',      [ELearningColaboradorController::class, 'registrarProgresso']);
$router->get('/elearning/colaborador/provas/{id}/fazer',         [ELearningColaboradorController::class, 'fazerProva']);
$router->post('/elearning/colaborador/provas/submeter',          [ELearningColaboradorController::class, 'submeterProva']);
$router->get('/elearning/colaborador/provas/resultado/{id}',     [ELearningColaboradorController::class, 'resultadoProva']);
$router->get('/elearning/colaborador/certificados',              [ELearningColaboradorController::class, 'meusCertificados']);
$router->get('/elearning/colaborador/historico',                 [ELearningColaboradorController::class, 'historico']);
$router->get('/elearning/colaborador/certificados/{codigo}',     [ELearningColaboradorController::class, 'downloadCertificado']);
