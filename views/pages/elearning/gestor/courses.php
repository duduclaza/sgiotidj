<?php
$courses = $data['courses'] ?? [];
$teachers = $data['teachers'] ?? [];
$categories = $data['categories'] ?? [];
$storage = $data['storage'] ?? [];
$stats = $data['stats'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);
?>

<section class="space-y-8">
    <div class="overflow-hidden rounded-[2.25rem] border border-white/10 bg-[linear-gradient(135deg,_rgba(15,23,42,0.95),_rgba(8,47,73,0.72)_58%,_rgba(15,118,110,0.54))] p-8 shadow-soft sm:p-10">
        <div class="grid gap-8 xl:grid-cols-[1.1fr,0.9fr]">
            <div class="space-y-5">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-100/70">Estrutura de cursos</p>
                <h1 class="max-w-4xl text-4xl font-black tracking-tight text-white sm:text-5xl">
                    Cursos
                </h1>
                <div class="flex flex-wrap gap-3">
                    <?php if ($canEdit): ?>
                        <button type="button" class="rounded-full bg-white px-5 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]" onclick="openCourseModal()">
                            Novo curso
                        </button>
                    <?php endif; ?>
                    <a href="/elearning/gestor/diploma/config" class="rounded-full border border-white/20 px-5 py-3 text-sm font-black text-white transition hover:bg-white/10">
                        Certificados
                    </a>
                    <?php if ($canDelete && $courses): ?>
                        <button type="button" class="rounded-full border border-rose-300/30 bg-rose-400/10 px-5 py-3 text-sm font-black text-rose-100 transition hover:bg-rose-400/20" onclick="deleteAllCourses()">
                            Excluir todos os cursos
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <?php foreach ([
                    ['label' => 'Total de cursos', 'value' => count($courses), 'icon' => 'ph-books'],
                    ['label' => 'Publicados', 'value' => $stats['published_courses'] ?? 0, 'icon' => 'ph-broadcast'],
                    ['label' => 'Aulas', 'value' => $stats['total_lessons'] ?? 0, 'icon' => 'ph-play-circle'],
                    ['label' => 'Alunos', 'value' => $stats['total_students'] ?? 0, 'icon' => 'ph-users-three'],
                ] as $card): ?>
                    <article class="rounded-[1.75rem] border border-white/10 bg-slate-950/30 p-5 backdrop-blur-xl">
                        <div class="flex items-center justify-between">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 text-cyan-100">
                                <i class="ph <?= e($card['icon']) ?> text-xl"></i>
                            </div>
                            <span class="text-xs font-semibold uppercase tracking-[0.24em] text-white/40">Curso</span>
                        </div>
                        <p class="mt-5 text-3xl font-black text-white"><?= e((string) $card['value']) ?></p>
                        <p class="mt-2 text-sm text-slate-200/70"><?= e($card['label']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php if (!$schemaReady): ?>
        <div class="rounded-[1.75rem] border border-amber-300/30 bg-amber-400/10 px-5 py-4 text-sm leading-relaxed text-amber-50">
            O schema do modulo ainda nao foi aplicado neste ambiente. Os formularios vao orientar a execucao do SQL do E-Learning.
        </div>
    <?php endif; ?>

    <div class="grid gap-6 xl:grid-cols-[1.28fr,0.72fr]">
        <section class="space-y-5">
            <div class="grid gap-4 md:grid-cols-2">
                <?php if (!$courses): ?>
                    <div class="rounded-[1.75rem] border border-dashed border-white/10 bg-white/[0.045] p-10 text-center text-slate-300 md:col-span-2">
                        Nenhum curso cadastrado ate o momento.
                    </div>
                <?php endif; ?>

                <?php foreach ($courses as $course): ?>
                    <article class="overflow-hidden rounded-[1.9rem] border border-white/10 bg-white/[0.045] shadow-soft backdrop-blur-xl">
                        <div class="aspect-[16/9] bg-slate-900">
                            <img src="<?= e($course['cover_url']) ?>" alt="<?= e($course['title']) ?>" class="h-full w-full object-cover">
                        </div>
                        <div class="space-y-5 p-5">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="rounded-full border border-white/10 bg-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.22em] text-slate-100"><?= e($course['status_label'] ?? 'Rascunho') ?></span>
                                <span class="rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.22em] text-cyan-100"><?= e($course['category'] ?? 'Geral') ?></span>
                            </div>

                            <div class="space-y-2">
                                <h3 class="text-2xl font-black tracking-tight text-white"><?= e($course['title']) ?></h3>
                                <p class="text-sm text-slate-300"><?= (int) ($course['workload_hours'] ?? 0) ?>h | <?= (int) ($course['lessons_count'] ?? 0) ?> aulas | <?= (int) ($course['enrollments_count'] ?? 0) ?> alunos</p>
                                <p class="text-sm text-slate-300">Professor: <strong class="text-white"><?= e($course['teacher_name'] ?? 'A definir') ?></strong></p>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-sm text-slate-300">
                                    <span>Progresso medio</span>
                                    <strong class="text-white"><?= number_format((float) ($course['avg_progress'] ?? 0), 0) ?>%</strong>
                                </div>
                                <div class="h-2.5 overflow-hidden rounded-full bg-white/10">
                                    <div class="h-full rounded-full bg-[linear-gradient(90deg,_#67e8f9,_#99f6e4)]" style="width: <?= min(100, max(0, (float) ($course['avg_progress'] ?? 0))) ?>%"></div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/aulas" class="rounded-full bg-white px-4 py-2 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Abrir</a>
                                <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/provas" class="rounded-full border border-white/10 px-4 py-2 text-sm font-black text-white transition hover:bg-white/10">Provas</a>
                                <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/matriculas" class="rounded-full border border-white/10 px-4 py-2 text-sm font-black text-white transition hover:bg-white/10">Alunos</a>
                                <?php if ($canEdit): ?>
                                    <button type="button" class="rounded-full border border-white/10 px-4 py-2 text-sm font-black text-white transition hover:bg-white/10" data-course='<?= e(json_encode($course, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>' onclick="openCourseModal(this)">Editar</button>
                                <?php endif; ?>
                                <?php if ($canDelete): ?>
                                    <button type="button" class="rounded-full border border-rose-300/30 px-4 py-2 text-sm font-black text-rose-100 transition hover:bg-rose-400/10" onclick='deleteCourse(<?= (int) $course['id'] ?>, <?= json_encode((string) ($course['title'] ?? ''), JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Excluir</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="rounded-[2rem] border border-white/10 bg-white/[0.045] p-6 shadow-soft backdrop-blur-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-300/60">Governanca</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight text-white">Regras enxutas</h2>
                <ul class="mt-5 space-y-3 text-sm leading-relaxed text-slate-300">
                    <li>Cada aula aceita 1 video MP4 de ate 80 MB.</li>
                    <li>Anexos aceitam ate 20 MB por arquivo.</li>
                    <li>Certificados exigem progresso completo e nota minima de 70%.</li>
                    <li>Uploads param automaticamente ao atingir a capacidade global do SGI STREAM.</li>
                </ul>
            </section>

            <section class="rounded-[2rem] border border-white/10 bg-white/[0.045] p-6 shadow-soft backdrop-blur-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-100/60">Armazenamento</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight text-white"><?= e($storage['used_human'] ?? '0 min') ?></h2>
                <p class="mt-2 text-sm text-slate-300">Consumidos de <?= e($storage['contracted_human'] ?? '10.000 min') ?> contratados.</p>
                <div class="mt-4 h-3 overflow-hidden rounded-full bg-white/10">
                    <div class="h-full rounded-full <?= ($storage['alert_level'] ?? 'healthy') === 'critical' ? 'bg-rose-400' : ((($storage['alert_level'] ?? 'healthy') === 'warning') ? 'bg-amber-300' : 'bg-emerald-300') ?>" style="width: <?= min(100, max(0, (float) ($storage['percent_used'] ?? 0))) ?>%"></div>
                </div>
                <p class="mt-3 text-sm text-slate-300">Disponivel: <strong class="text-white"><?= e($storage['available_human'] ?? '0 min') ?></strong></p>
            </section>
        </aside>
    </div>
</section>

<div id="course-modal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
    <div class="w-full max-w-3xl rounded-[2rem] border border-white/10 bg-slate-950 p-8 text-white shadow-2xl">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Curso</p>
                <h2 id="course-modal-title" class="mt-2 text-3xl font-black tracking-tight text-white">Novo curso</h2>
            </div>
            <button type="button" class="rounded-full border border-white/10 px-4 py-2 text-sm font-black text-white transition hover:bg-white/10" onclick="closeCourseModal()">Fechar</button>
        </div>

        <form id="course-form" class="mt-8 grid gap-5 md:grid-cols-2">
            <input type="hidden" name="id" id="course-id">
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-bold text-slate-200">Titulo do curso</label>
                <input type="text" name="title" id="course-title" class="w-full rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-sm font-medium text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-200" required>
            </div>
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-bold text-slate-200">Descricao</label>
                <textarea name="description" id="course-description" rows="4" class="w-full rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-sm font-medium text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-200"></textarea>
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-200">Categoria</label>
                <input list="course-categories" name="category" id="course-category" class="w-full rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-sm font-medium text-white outline-none transition focus:border-cyan-200">
                <datalist id="course-categories">
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-200">Professor responsavel</label>
                <select name="teacher_id" id="course-teacher" class="w-full rounded-2xl border border-white/10 bg-slate-900 px-4 py-3 text-sm font-medium text-white outline-none transition focus:border-cyan-200">
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?= (int) $teacher['id'] ?>"><?= e($teacher['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-200">Carga horaria (h)</label>
                <input type="number" min="0" name="workload_hours" id="course-workload" class="w-full rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-sm font-medium text-white outline-none transition focus:border-cyan-200">
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-200">Status</label>
                <select name="status" id="course-status" class="w-full rounded-2xl border border-white/10 bg-slate-900 px-4 py-3 text-sm font-medium text-white outline-none transition focus:border-cyan-200">
                    <option value="draft">Rascunho</option>
                    <option value="published">Publicado</option>
                    <option value="archived">Arquivado</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-bold text-slate-200">Capa / imagem</label>
                <input type="file" name="cover" id="course-cover" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="block w-full rounded-2xl border border-dashed border-white/20 px-4 py-4 text-sm text-slate-200">
            </div>
            <div class="md:col-span-2 flex justify-end gap-3 pt-3">
                <button type="button" class="rounded-full border border-white/10 px-5 py-3 text-sm font-black text-white transition hover:bg-white/10" onclick="closeCourseModal()">Cancelar</button>
                <button type="submit" class="rounded-full bg-white px-5 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Salvar curso</button>
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
