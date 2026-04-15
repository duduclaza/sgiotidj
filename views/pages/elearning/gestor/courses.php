<?php
$courses = $data['courses'] ?? [];
$teachers = $data['teachers'] ?? [];
$categories = $data['categories'] ?? [];
$storage = $data['storage'] ?? [];
$stats = $data['stats'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);
$storagePercent = min(100, max(0, (float) ($storage['percent_used'] ?? 0)));
?>

<div class="el-ios">
    <div class="el-page el-stack">
        <header class="el-page-head">
            <div>
                <p class="el-eyebrow">Professor</p>
                <h1 class="el-title">Cursos</h1>
                <p class="el-subtitle">Organize trilhas, status, capas, aulas, provas e alunos com uma leitura visual mais limpa.</p>
            </div>
            <div class="el-actions">
                <?php if ($canEdit): ?>
                    <button type="button" class="el-btn el-btn-primary" onclick="openCourseModal()"><i class="ph ph-plus-circle"></i> Novo curso</button>
                <?php endif; ?>
                <a href="/elearning/gestor/diploma/config" class="el-btn el-btn-secondary"><i class="ph ph-certificate"></i> Certificados</a>
                <a href="/elearning/gestor" class="el-btn el-btn-outline"><i class="ph ph-squares-four"></i> Painel</a>
            </div>
        </header>

        <?php if (!$schemaReady): ?>
            <div class="el-alert">O schema do e-learning ainda nao foi aplicado. Os formularios ficam prontos para uso assim que a base estiver disponivel.</div>
        <?php endif; ?>

        <section class="el-metric-grid">
            <?php foreach ([
                ['label' => 'Total de cursos', 'value' => count($courses), 'icon' => 'ph-books', 'tone' => 'blue'],
                ['label' => 'Publicados', 'value' => $stats['published_courses'] ?? 0, 'icon' => 'ph-broadcast', 'tone' => 'green'],
                ['label' => 'Aulas', 'value' => $stats['total_lessons'] ?? 0, 'icon' => 'ph-play-circle', 'tone' => 'cyan'],
                ['label' => 'Alunos', 'value' => $stats['total_students'] ?? 0, 'icon' => 'ph-users-three', 'tone' => 'orange'],
            ] as $card): ?>
                <article class="el-metric">
                    <div class="el-metric-top">
                        <span class="el-icon <?= e($card['tone']) ?>"><i class="ph <?= e($card['icon']) ?>"></i></span>
                        <span class="el-badge">Curso</span>
                    </div>
                    <p class="el-metric-value"><?= e((string) $card['value']) ?></p>
                    <p class="el-metric-label"><?= e($card['label']) ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <div class="el-grid">
            <section class="el-col-8">
                <div class="el-section-head">
                    <div>
                        <h2 class="el-section-title">Trilhas cadastradas</h2>
                        <p class="el-section-copy"><?= count($courses) ?> curso(s) no ambiente do professor.</p>
                    </div>
                    <?php if ($canDelete && $courses): ?>
                        <button type="button" class="el-btn el-btn-sm el-danger" onclick="deleteAllCourses()">Excluir todos</button>
                    <?php endif; ?>
                </div>

                <div class="el-course-grid">
                    <?php if (!$courses): ?>
                        <div class="el-empty" style="grid-column:1/-1">Nenhum curso cadastrado ate o momento.</div>
                    <?php endif; ?>

                    <?php foreach ($courses as $course): ?>
                        <?php $progress = min(100, max(0, (float) ($course['avg_progress'] ?? 0))); ?>
                        <article class="el-course">
                            <div class="el-cover">
                                <img src="<?= e($course['cover_url']) ?>" alt="<?= e($course['title']) ?>">
                            </div>
                            <div class="el-course-body">
                                <div class="el-badges">
                                    <span class="el-badge blue"><?= e($course['status_label'] ?? 'Rascunho') ?></span>
                                    <span class="el-badge green"><?= e($course['category'] ?? 'Geral') ?></span>
                                    <span class="el-badge"><?= (int) ($course['workload_hours'] ?? 0) ?>h</span>
                                </div>

                                <div>
                                    <h3 class="el-course-title"><?= e($course['title']) ?></h3>
                                    <p class="el-course-meta">Professor: <?= e($course['teacher_name'] ?? 'A definir') ?></p>
                                    <p class="el-course-meta"><?= (int) ($course['lessons_count'] ?? 0) ?> aulas | <?= (int) ($course['enrollments_count'] ?? 0) ?> alunos</p>
                                </div>

                                <div class="el-progress">
                                    <div class="el-progress-label">
                                        <span>Progresso medio</span>
                                        <strong><?= number_format($progress, 0) ?>%</strong>
                                    </div>
                                    <div class="el-progress-track"><div class="el-progress-fill" style="width: <?= $progress ?>%"></div></div>
                                </div>

                                <div class="el-course-actions">
                                    <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/aulas" class="el-btn el-btn-sm el-btn-primary">Abrir</a>
                                    <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/provas" class="el-btn el-btn-sm el-btn-secondary">Provas</a>
                                    <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/matriculas" class="el-btn el-btn-sm el-btn-secondary">Alunos</a>
                                    <?php if ($canEdit): ?>
                                        <button type="button" class="el-btn el-btn-sm el-btn-outline" data-course='<?= e(json_encode($course, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>' onclick="openCourseModal(this)">Editar</button>
                                    <?php endif; ?>
                                    <?php if ($canDelete): ?>
                                        <button type="button" class="el-btn el-btn-sm el-danger" onclick='deleteCourse(<?= (int) $course['id'] ?>, <?= json_encode((string) ($course['title'] ?? ''), JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Excluir</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <aside class="el-col-4 el-stack">
                <section class="el-panel">
                    <h2 class="el-section-title">Armazenamento</h2>
                    <p class="el-section-copy">Uso atual do SGI Stream para videos de aulas.</p>
                    <p class="el-metric-value"><?= e($storage['used_human'] ?? '0 min') ?></p>
                    <p class="el-metric-label">de <?= e($storage['contracted_human'] ?? '10.000 min') ?></p>
                    <div class="el-progress" style="margin-top:16px">
                        <div class="el-progress-label">
                            <span>Consumo</span>
                            <strong><?= number_format($storagePercent, 1, ',', '.') ?>%</strong>
                        </div>
                        <div class="el-progress-track">
                            <div class="el-progress-fill <?= $storagePercent >= 90 ? 'pink' : ($storagePercent >= 80 ? 'orange' : 'green') ?>" style="width: <?= $storagePercent ?>%"></div>
                        </div>
                    </div>
                    <div class="el-actions" style="margin-top:16px">
                        <a href="/elearning/gestor/armazenamento" class="el-btn el-btn-secondary">Detalhes</a>
                    </div>
                </section>

                <section class="el-panel">
                    <h2 class="el-section-title">Regras</h2>
                    <div class="el-list" style="margin-top:14px">
                        <div class="el-list-item"><span>Video por aula</span><strong>1 MP4</strong></div>
                        <div class="el-list-item"><span>Tamanho do video</span><strong>80 MB</strong></div>
                        <div class="el-list-item"><span>Anexo</span><strong>20 MB</strong></div>
                        <div class="el-list-item"><span>Certificado</span><strong>70%</strong></div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>

<div id="course-modal" class="el-modal hidden">
    <div class="el-modal-card">
        <div class="el-modal-head">
            <div>
                <p class="el-eyebrow">Curso</p>
                <h2 id="course-modal-title" class="el-title el-title-sm">Novo curso</h2>
            </div>
            <button type="button" class="el-btn el-btn-outline" onclick="closeCourseModal()">Fechar</button>
        </div>

        <form id="course-form" class="el-form-grid">
            <input type="hidden" name="id" id="course-id">
            <div class="el-field el-form-full">
                <label for="course-title">Titulo do curso</label>
                <input type="text" name="title" id="course-title" required>
            </div>
            <div class="el-field el-form-full">
                <label for="course-description">Descricao</label>
                <textarea name="description" id="course-description" rows="4"></textarea>
            </div>
            <div class="el-field">
                <label for="course-category">Categoria</label>
                <input list="course-categories" name="category" id="course-category">
                <datalist id="course-categories">
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="el-field">
                <label for="course-teacher">Professor responsavel</label>
                <select name="teacher_id" id="course-teacher">
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?= (int) $teacher['id'] ?>"><?= e($teacher['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="el-field">
                <label for="course-workload">Carga horaria (h)</label>
                <input type="number" min="0" name="workload_hours" id="course-workload">
            </div>
            <div class="el-field">
                <label for="course-status">Status</label>
                <select name="status" id="course-status">
                    <option value="draft">Rascunho</option>
                    <option value="published">Publicado</option>
                    <option value="archived">Arquivado</option>
                </select>
            </div>
            <div class="el-field el-form-full">
                <label for="course-cover">Capa / imagem</label>
                <input type="file" name="cover" id="course-cover" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            </div>
            <div class="el-actions el-form-full" style="justify-content:flex-end">
                <button type="button" class="el-btn el-btn-outline" onclick="closeCourseModal()">Cancelar</button>
                <button type="submit" class="el-btn el-btn-primary">Salvar curso</button>
            </div>
        </form>
    </div>
</div>

<script>
const courseModal = document.getElementById('course-modal');
const courseForm = document.getElementById('course-form');

function notifyCourse(message, type = 'info') {
    if (typeof showProfessorToast === 'function') {
        showProfessorToast(message, type);
        return;
    }
    alert(message);
}

function openCourseModal(button = null) {
    courseForm.reset();
    document.getElementById('course-id').value = '';
    document.getElementById('course-modal-title').textContent = 'Novo curso';

    if (button?.dataset.course) {
        const course = JSON.parse(button.dataset.course);
        document.getElementById('course-modal-title').textContent = 'Editar curso';
        document.getElementById('course-id').value = course.id || '';
        document.getElementById('course-title').value = course.title || '';
        document.getElementById('course-description').value = course.description || '';
        document.getElementById('course-category').value = course.category || '';
        document.getElementById('course-teacher').value = course.teacher_id || '';
        document.getElementById('course-workload').value = course.workload_hours || '';
        document.getElementById('course-status').value = course.status || 'draft';
    }

    courseModal.classList.remove('hidden');
    courseModal.classList.add('flex');
}

function closeCourseModal() {
    courseModal.classList.add('hidden');
    courseModal.classList.remove('flex');
}

async function readCourseResponse(response) {
    const result = await response.json();
    if (!response.ok || !result.success) {
        throw new Error(result.message || 'Nao foi possivel concluir a operacao.');
    }

    return result;
}

courseForm?.addEventListener('submit', async function (event) {
    event.preventDefault();

    const formData = new FormData(event.currentTarget);
    const isUpdate = !!document.getElementById('course-id').value;
    const endpoint = isUpdate ? '/elearning/gestor/cursos/update' : '/elearning/gestor/cursos/store';

    try {
        const response = await fetch(endpoint, { method: 'POST', body: formData });
        const result = await readCourseResponse(response);
        notifyCourse(result.message || 'Curso salvo com sucesso.', 'success');
        setTimeout(() => window.location.reload(), 700);
    } catch (error) {
        notifyCourse(error.message || 'Nao foi possivel salvar o curso.', 'error');
    }
});

async function deleteCourse(courseId, courseTitle = '') {
    const courseLabel = courseTitle ? `"${courseTitle}"` : 'este curso';
    if (!confirm(`Deseja excluir ${courseLabel}?\n\nIsso remove aulas, provas, matriculas, certificados, anexos e os videos hospedados do curso.`)) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('id', courseId);
        const response = await fetch('/elearning/gestor/cursos/delete', { method: 'POST', body: formData });
        const result = await readCourseResponse(response);
        notifyCourse(result.message || 'Curso excluido com sucesso.', 'success');
        setTimeout(() => window.location.reload(), 700);
    } catch (error) {
        notifyCourse(error.message || 'Nao foi possivel excluir o curso.', 'error');
    }
}

async function deleteAllCourses() {
    if (!confirm('Deseja excluir todos os cursos deste professor?\n\nIsso remove aulas, provas, matriculas, certificados, anexos e videos vinculados.')) {
        return;
    }

    try {
        const response = await fetch('/elearning/gestor/cursos/delete-all', { method: 'POST', body: new FormData() });
        const result = await readCourseResponse(response);
        notifyCourse(result.message || 'Cursos excluidos com sucesso.', 'success');
        setTimeout(() => window.location.reload(), 900);
    } catch (error) {
        notifyCourse(error.message || 'Nao foi possivel excluir todos os cursos.', 'error');
    }
}
</script>
