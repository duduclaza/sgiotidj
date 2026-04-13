<?php
/**
 * Rotas Diversas e Especiais
 * 
 * Registros, Suporte, Financeiro, Master, Área Técnica, CRM, Implantação, Logística, Profile
 */

use App\Controllers\RegistrosController;
use App\Controllers\ClientesController;
use App\Controllers\SuporteController;
use App\Controllers\FinanceiroController;
use App\Controllers\MasterController;
use App\Controllers\AreaTecnicaController;
use App\Controllers\ProfileController;
use App\Controllers\PageController;

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

// ===== TONERS COM DEFEITO =====
use App\Controllers\TonersController;
$router->get('/toners/defeitos', [TonersController::class , 'defeitos']);
$router->post('/toners/defeitos/store', [TonersController::class , 'storeDefeito']);
$router->get('/toners/defeitos/{id}/foto/{n}', [TonersController::class , 'downloadFotoDefeito']);
$router->post('/toners/defeitos/delete', [TonersController::class , 'deleteDefeito']);

// ===== eLEARNING GESTOR =====
$router->get('/elearning/professor', [App\Controllers\ELearningGestorController::class, 'dashboard']);
$router->get('/elearning/gestor', [App\Controllers\ELearningGestorController::class, 'dashboard']);
$router->get('/elearning/gestor/cursos', [App\Controllers\ELearningGestorController::class, 'cursos']);
$router->get('/elearning/gestor/armazenamento', [App\Controllers\ELearningGestorController::class, 'armazenamento']);
$router->get('/elearning/gestor/relatorios', [App\Controllers\ELearningGestorController::class, 'relatorios']);
$router->post('/elearning/gestor/cursos/store', [App\Controllers\ELearningGestorController::class, 'storeCurso']);
$router->post('/elearning/gestor/cursos/update', [App\Controllers\ELearningGestorController::class, 'updateCurso']);
$router->post('/elearning/gestor/cursos/delete', [App\Controllers\ELearningGestorController::class, 'deleteCurso']);
$router->post('/elearning/gestor/cursos/delete-all', [App\Controllers\ELearningGestorController::class, 'deleteTodosCursos']);
$router->get('/elearning/gestor/cursos/thumbnail', [App\Controllers\ELearningGestorController::class, 'thumbnailCurso']);
$router->get('/elearning/gestor/cursos/{id}/aulas', [App\Controllers\ELearningGestorController::class, 'aulas']);
$router->post('/elearning/gestor/aulas/store', [App\Controllers\ELearningGestorController::class, 'storeAula']);
$router->post('/elearning/gestor/aulas/reorder', [App\Controllers\ELearningGestorController::class, 'reorderAula']);
$router->post('/elearning/gestor/aulas/delete', [App\Controllers\ELearningGestorController::class, 'deleteAula']);
$router->get('/elearning/gestor/aulas/{id}/video-status', [App\Controllers\ELearningGestorController::class, 'videoStatusAula']);
$router->get('/elearning/gestor/videos/{id}', [App\Controllers\ELearningGestorController::class, 'streamLessonVideo']);
$router->get('/elearning/gestor/anexos/{id}/download', [App\Controllers\ELearningGestorController::class, 'downloadAttachment']);
$router->post('/elearning/gestor/provas/delete', [App\Controllers\ELearningGestorController::class, 'deleteProva']);

$router->get('/elearning/gestor/diploma/config', [App\Controllers\ELearningGestorController::class, 'diplomaConfig']);
$router->post('/elearning/gestor/diploma/save', [App\Controllers\ELearningGestorController::class, 'saveDiplomaConfig']);
$router->get('/elearning/gestor/diploma/logo', [App\Controllers\ELearningGestorController::class, 'diplomaLogo']);
$router->post('/elearning/gestor/materiais/upload', [App\Controllers\ELearningGestorController::class, 'uploadMaterial']);
$router->post('/elearning/gestor/materiais/delete', [App\Controllers\ELearningGestorController::class, 'deleteMaterial']);
$router->post('/elearning/gestor/materiais/update', [App\Controllers\ELearningGestorController::class, 'updateMaterial']);
$router->get('/elearning/gestor/cursos/{id}/provas', [App\Controllers\ELearningGestorController::class, 'provas']);
$router->post('/elearning/gestor/provas/store', [App\Controllers\ELearningGestorController::class, 'storeProva']);
$router->post('/elearning/gestor/questoes/store', [App\Controllers\ELearningGestorController::class, 'storeQuestao']);
$router->get('/elearning/gestor/cursos/{id}/matriculas', [App\Controllers\ELearningGestorController::class, 'matriculas']);
$router->post('/elearning/gestor/matriculas/store', [App\Controllers\ELearningGestorController::class, 'matricularColaborador']);
$router->get('/elearning/gestor/cursos/{id}/progresso', [App\Controllers\ELearningGestorController::class, 'progressoDashboard']);
$router->post('/elearning/gestor/certificados/emitir', [App\Controllers\ELearningGestorController::class, 'emitirCertificado']);

// ===== eLEARNING COLABORADOR =====
$router->get('/elearning/aluno', [App\Controllers\ELearningColaboradorController::class, 'meusCursos']);
$router->get('/elearning/colaborador', [App\Controllers\ELearningColaboradorController::class, 'meusCursos']);
$router->post('/elearning/colaborador/matricular', [App\Controllers\ELearningColaboradorController::class, 'matricularSe']);
$router->get('/elearning/colaborador/cursos/{id}', [App\Controllers\ELearningColaboradorController::class, 'verCurso']);
$router->get('/elearning/colaborador/cursos/{id}/continuar', [App\Controllers\ELearningColaboradorController::class, 'continuar']);
$router->get('/elearning/colaborador/materiais/{id}/assistir', [App\Controllers\ELearningColaboradorController::class, 'assistirAula']);
$router->get('/elearning/colaborador/aulas/{id}/video-status', [App\Controllers\ELearningColaboradorController::class, 'videoStatusAula']);
$router->get('/elearning/colaborador/videos/{id}', [App\Controllers\ELearningColaboradorController::class, 'streamLessonVideo']);
$router->get('/elearning/colaborador/anexos/{id}/download', [App\Controllers\ELearningColaboradorController::class, 'downloadAttachment']);
$router->post('/elearning/colaborador/progresso/registrar', [App\Controllers\ELearningColaboradorController::class, 'registrarProgresso']);
$router->get('/elearning/colaborador/provas/{id}/fazer', [App\Controllers\ELearningColaboradorController::class, 'fazerProva']);
$router->post('/elearning/colaborador/provas/submeter', [App\Controllers\ELearningColaboradorController::class, 'submeterProva']);
$router->get('/elearning/colaborador/provas/resultado/{id}', [App\Controllers\ELearningColaboradorController::class, 'resultadoProva']);
$router->get('/elearning/colaborador/certificados', [App\Controllers\ELearningColaboradorController::class, 'meusCertificados']);
$router->get('/elearning/colaborador/historico', [App\Controllers\ELearningColaboradorController::class, 'historico']);
$router->get('/elearning/colaborador/certificados/{codigo}', [App\Controllers\ELearningColaboradorController::class, 'downloadCertificado']);
