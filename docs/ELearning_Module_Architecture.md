# E-Learning SGI

## VisÃ£o Geral

O mÃ³dulo E-Learning foi estruturado em dois submÃ³dulos:

- `Professor`: criaÃ§Ã£o e gestÃ£o de cursos, aulas, provas, matrÃ­culas, certificados, relatÃ³rios e storage.
- `Aluno`: catÃ¡logo, matrÃ­cula, consumo das aulas, progresso, provas online, resultado e certificado.

Stack aplicada no projeto atual:

- Back-end: PHP com controllers dedicados e `App\Services\ELearningService`
- Front-end: views PHP responsivas seguindo o padrÃ£o visual do SGI
- Banco: MariaDB
- Arquivos: anexos, capas e certificados continuam locais no projeto/servidor
- Storage de videos: Bunny Stream com limite padrao de `10.000 minutos`

## Arquitetura Funcional

### DomÃ­nio principal

- `Curso`
  - possui vÃ¡rias `Aulas`
  - possui vÃ¡rias `Provas`
  - possui vÃ¡rias `MatrÃ­culas`
  - possui um `Template de Certificado` selecionado
- `Aula`
  - possui `1 vÃ­deo` no mÃ¡ximo
  - possui `N anexos`
- `MatrÃ­cula`
  - liga `Aluno` a `Curso`
  - consolida `status` e `progress_percent`
- `Prova`
  - possui `QuestÃµes`
  - cada questÃ£o possui `Alternativas`
  - gera `Tentativas` e `Respostas`
- `Certificado`
  - Ã© emitido quando regras acadÃªmicas forem atendidas
- `Storage`
  - contabiliza os minutos consumidos pelos videos das aulas no Bunny Stream

### Camadas implementadas

- `src/Services/ELearningService.php`
  - concentra regras de negÃ³cio, persistÃªncia, uploads, controle de storage, certificaÃ§Ã£o e dashboards
- `src/Controllers/ELearningGestorController.php`
  - expÃµe o fluxo do Professor
- `src/Controllers/ELearningColaboradorController.php`
  - expÃµe o fluxo do Aluno
- `views/pages/elearning/gestor/*`
  - dashboard, cursos, workspace do curso, relatÃ³rios, storage e biblioteca de certificados
- `views/pages/elearning/colaborador/*`
  - dashboard, curso, aula, prova, resultado, histÃ³rico e certificado

## Regras de NegÃ³cio ObrigatÃ³rias

- Cada curso pode ter vÃ¡rias aulas.
- Cada aula pode ter apenas `1 vÃ­deo`.
- Cada vÃ­deo deve ser obrigatoriamente `MP4`.
- Cada vÃ­deo pode ter no mÃ¡ximo `80 MB`.
- Cada anexo pode ter no mÃ¡ximo `20 MB`.
- O certificado exige `70%` ou mais de aproveitamento na prova obrigatÃ³ria.
- O certificado sÃ³ Ã© liberado apÃ³s conclusÃ£o do curso e aprovaÃ§Ã£o mÃ­nima.
- Devem existir no mÃ­nimo `5 templates` iniciais de certificado.
- O limite total padrao do modulo e `10.000 minutos`.
- Ao atingir `80%`, o sistema entra em estado de alerta.
- Ao atingir `100%`, novos uploads de vÃ­deo sÃ£o bloqueados.

## Fluxo do Professor

1. Criar ou editar um curso.
2. Definir tÃ­tulo, descriÃ§Ã£o, categoria, capa, carga horÃ¡ria, status e professor responsÃ¡vel.
3. Criar aulas e organizar a sequÃªncia.
4. Enviar `1 vÃ­deo MP4` por aula e anexos de apoio.
5. Criar prova com questÃµes objetivas e tentativas configurÃ¡veis.
6. Matricular alunos.
7. Escolher o template do certificado e personalizar curso a curso.
8. Acompanhar relatorios e consumo de minutos no Bunny Stream.

## Fluxo do Aluno

1. Visualizar cursos disponÃ­veis ou matriculados.
2. Entrar no curso e assistir Ã s aulas.
3. Baixar anexos de apoio.
4. Registrar progresso por aula.
5. Realizar prova online.
6. Consultar resultado.
7. Emitir certificado quando elegÃ­vel.

## APIs REST Internas

### Professor

- `GET /elearning/gestor`
- `GET /elearning/gestor/cursos`
- `POST /elearning/gestor/cursos/store`
- `POST /elearning/gestor/cursos/update`
- `POST /elearning/gestor/cursos/delete`
- `GET /elearning/gestor/cursos/{id}/aulas`
- `POST /elearning/gestor/aulas/store`
- `POST /elearning/gestor/aulas/reorder`
- `POST /elearning/gestor/aulas/delete`
- `GET /elearning/gestor/cursos/{id}/provas`
- `POST /elearning/gestor/provas/store`
- `POST /elearning/gestor/provas/delete`
- `GET /elearning/gestor/cursos/{id}/matriculas`
- `POST /elearning/gestor/matriculas/store`
- `GET /elearning/gestor/cursos/{id}/progresso`
- `POST /elearning/gestor/certificados/emitir`
- `GET /elearning/gestor/diploma/config`
- `POST /elearning/gestor/diploma/save`
- `GET /elearning/gestor/armazenamento`
- `GET /elearning/gestor/relatorios`
- `GET /elearning/gestor/videos/{lessonId}`
- `GET /elearning/gestor/anexos/{attachmentId}/download`

### Aluno

- `GET /elearning/colaborador`
- `POST /elearning/colaborador/matricular`
- `GET /elearning/colaborador/cursos/{id}`
- `GET /elearning/colaborador/cursos/{id}/continuar`
- `GET /elearning/colaborador/materiais/{lessonId}/assistir`
- `POST /elearning/colaborador/progresso/registrar`
- `GET /elearning/colaborador/provas/{examId}/fazer`
- `POST /elearning/colaborador/provas/submeter`
- `GET /elearning/colaborador/provas/resultado/{attemptId}`
- `GET /elearning/colaborador/certificados`
- `GET /elearning/colaborador/certificados/{codigo}`
- `GET /elearning/colaborador/historico`
- `GET /elearning/colaborador/videos/{lessonId}`
- `GET /elearning/colaborador/anexos/{attachmentId}/download`

## Modelagem MariaDB

Arquivo principal:

- [elearning_module.sql](/c:/Users/Eduardo%20Martins/Desktop/sgqdj/database/elearning_module.sql)

Tabelas previstas:

- `elearning_courses`
- `elearning_lessons`
- `elearning_lesson_videos`
- `elearning_lesson_attachments`
- `elearning_enrollments`
- `elearning_student_progress`
- `elearning_exams`
- `elearning_exam_questions`
- `elearning_exam_options`
- `elearning_exam_attempts`
- `elearning_exam_answers`
- `elearning_certificate_templates`
- `elearning_certificates`
- `elearning_storage_control`
- `elearning_activity_logs`

## Uploads e DiretÃ³rios

PadrÃ£o de persistÃªncia fÃ­sica:

- capas: `storage/elearning/covers`
- vÃ­deos: `storage/elearning/courses/{courseId}/lessons/{lessonId}/video`
- anexos: `storage/elearning/courses/{courseId}/lessons/{lessonId}/attachments`
- assets do certificado: `storage/elearning/courses/{courseId}/certificate`

Requisitos operacionais:

- garantir permissÃ£o de escrita para a pasta `storage/elearning`
- manter backup dos arquivos e do MariaDB em conjunto
- monitorar crescimento dos vÃ­deos localmente

## CertificaÃ§Ã£o

LÃ³gica aplicada no service:

- progresso do curso Ã© calculado pelo percentual de aulas concluÃ­das
- prova obrigatÃ³ria aprovada move a matrÃ­cula para `approved`
- curso sem prova obrigatÃ³ria pode concluir como `completed`
- certificado Ã© emitido automaticamente quando a matrÃ­cula se torna elegÃ­vel
- logo, assinatura e fundo do certificado sÃ£o incorporados em `data URL` na renderizaÃ§Ã£o para funcionar mesmo fora da pasta pÃºblica

## PermissÃµes por Perfil

Chaves de permissÃ£o usadas pelo mÃ³dulo:

- `elearning_gestor`
- `elearning_colaborador`

SugestÃ£o operacional:

- `Professor`: view/edit/delete em `elearning_gestor`
- `Aluno`: view em `elearning_colaborador`
- `Admin`: acesso aos dois mÃ³dulos

## Massa Mock

O SQL entrega seed opcional para:

- `1 curso`: InformÃ¡tica BÃ¡sica
- `3 aulas`
- `1 prova obrigatÃ³ria`
- `2 questÃµes objetivas`
- `1 matrÃ­cula de exemplo`, se houver ao menos dois usuÃ¡rios

## Checklist de ImplantaÃ§Ã£o

1. Executar [elearning_module.sql](/c:/Users/Eduardo%20Martins/Desktop/sgqdj/database/elearning_module.sql).
2. Garantir escrita em `storage/elearning`.
3. Validar perfis em `Admin > Perfis`.
4. Testar upload de vÃ­deo MP4 abaixo e acima de 80 MB.
5. Testar anexos abaixo e acima de 20 MB.
6. Testar curso concluÃ­do com e sem prova obrigatÃ³ria.
7. Testar emissÃ£o e abertura do certificado.


