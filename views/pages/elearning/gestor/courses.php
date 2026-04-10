<?php
$courses = $data['courses'] ?? [];
$teachers = $data['teachers'] ?? [];
$categories = $data['categories'] ?? [];
$storage = $data['storage'] ?? [];
$schemaReady = (bool) ($data['schema_ready'] ?? false);
?>

<section class="space-y-8">
    <div class="flex flex-col gap-5 rounded-[2rem] border border-slate-200 bg-white p-8 shadow-xl xl:flex-row xl:items-end xl:justify-between">
        <div class="space-y-4">
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Estrutura de cursos</p>
            <h1 class="text-4xl font-black tracking-tight text-slate-900">Cadastro, edicao e publicacao de cursos online</h1>
            <p class="max-w-3xl text-base leading-relaxed text-slate-600">Crie novos cursos, organize a jornada de aulas, defina o professor responsavel e publique trilhas prontas para consumo no submodulo do aluno.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="button" class="rounded-full bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:scale-[1.02]" onclick="openCourseModal()">Novo curso</button>
            <a href="/elearning/gestor/diploma/config" class="rounded-full border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50">Biblioteca de certificados</a>
        </div>
    </div>

    <?php if (!$schemaReady): ?>
        <div class="rounded-[1.75rem] border border-amber-300 bg-amber-50 px-5 py-4 text-sm leading-relaxed text-amber-950">
            O schema do modulo ainda nao foi aplicado neste ambiente. Os formularios de gravacao retornam uma mensagem orientando a execucao do SQL do E-Learning.
        </div>
    <?php endif; ?>

    <div class="grid gap-6 xl:grid-cols-[1.3fr,0.7fr]">
        <section class="space-y-5">
            <div class="grid gap-4 md:grid-cols-2">
                <?php if (!$courses): ?>
                    <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 md:col-span-2">Nenhum curso cadastrado ate o momento.</div>
                <?php endif; ?>
                <?php foreach ($courses as $course): ?>
                    <article class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-lg">
                        <div class="aspect-[16/9] bg-slate-900">
                            <img src="<?= e($course['cover_url']) ?>" alt="<?= e($course['title']) ?>" class="h-full w-full object-cover">
                        </div>
                        <div class="space-y-4 p-5">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="rounded-full bg-slate-900 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-white"><?= e($course['status_label'] ?? 'Rascunho') ?></span>
                                <span class="rounded-full bg-sky-100 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-sky-700"><?= e($course['category'] ?? 'Geral') ?></span>
                            </div>
                            <div class="space-y-2">
                                <h3 class="text-2xl font-black tracking-tight text-slate-900"><?= e($course['title']) ?></h3>
                                <p class="text-sm text-slate-600"><?= (int) ($course['workload_hours'] ?? 0) ?>h | <?= (int) ($course['lessons_count'] ?? 0) ?> aulas | <?= (int) ($course['enrollments_count'] ?? 0) ?> alunos</p>
                                <p class="text-sm text-slate-600">Professor: <strong class="text-slate-900"><?= e($course['teacher_name'] ?? 'A definir') ?></strong></p>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-sm text-slate-500">
                                    <span>Progresso medio dos alunos</span>
                                    <strong class="text-slate-900"><?= number_format((float) ($course['avg_progress'] ?? 0), 0) ?>%</strong>
                                </div>
                                <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-[linear-gradient(90deg,_#2563eb,_#0ea5e9)]" style="width: <?= min(100, max(0, (float) ($course['avg_progress'] ?? 0))) ?>%"></div>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/aulas" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:scale-[1.02]">Aulas</a>
                                <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/provas" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50">Provas</a>
                                <a href="/elearning/gestor/cursos/<?= (int) $course['id'] ?>/matriculas" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50">Alunos</a>
                                <button type="button" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50" data-course='<?= e(json_encode($course, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>' onclick="openCourseModal(this)">Editar</button>
                                <?php if ($canDelete): ?>
                                    <button type="button" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-black text-rose-700 transition hover:bg-rose-50" onclick='deleteCourse(<?= (int) $course['id'] ?>, <?= json_encode((string) ($course['title'] ?? ''), JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Excluir curso</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Governanca</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Regras de negocio</h2>
                <ul class="mt-5 space-y-3 text-sm leading-relaxed text-slate-600">
                    <li>Cada aula aceita apenas 1 video em MP4, com maximo de 80 MB.</li>
                    <li>Cada anexo aceita ate 20 MB e pode ser baixado pelo aluno.</li>
                    <li>O certificado exige progresso completo e minimo de 70% na prova obrigatoria.</li>
                    <li>O limite global de video no SGI STREAM e bloqueado ao atingir 10.000 minutos.</li>
                </ul>
            </section>

            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Armazenamento</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900"><?= e($storage['used_human'] ?? '0 min') ?></h2>
                <p class="mt-2 text-sm text-slate-600">Consumidos de <?= e($storage['contracted_human'] ?? '10.000 min') ?> contratados.</p>
                <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full <?= ($storage['alert_level'] ?? 'healthy') === 'critical' ? 'bg-rose-500' : ((($storage['alert_level'] ?? 'healthy') === 'warning') ? 'bg-amber-500' : 'bg-emerald-500') ?>" style="width: <?= min(100, max(0, (float) ($storage['percent_used'] ?? 0))) ?>%"></div>
                </div>
                <p class="mt-3 text-sm text-slate-600">Disponivel: <strong class="text-slate-900"><?= e($storage['available_human'] ?? '0 min') ?></strong></p>
            </section>
        </aside>
    </div>
</section>

<div id="course-modal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
    <div class="w-full max-w-3xl rounded-[2rem] bg-white p-8 shadow-2xl">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Curso</p>
                <h2 id="course-modal-title" class="mt-2 text-3xl font-black tracking-tight text-slate-900">Novo curso</h2>
            </div>
            <button type="button" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50" onclick="closeCourseModal()">Fechar</button>
        </div>

        <form id="course-form" class="mt-8 grid gap-5 md:grid-cols-2">
            <input type="hidden" name="id" id="course-id">
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-bold text-slate-700">Titulo do curso</label>
                <input type="text" name="title" id="course-title" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900" required>
            </div>
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-bold text-slate-700">Descricao</label>
                <textarea name="description" id="course-description" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900"></textarea>
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">Categoria</label>
                <input list="course-categories" name="category" id="course-category" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900">
                <datalist id="course-categories">
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">Professor responsavel</label>
                <select name="teacher_id" id="course-teacher" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900">
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?= (int) $teacher['id'] ?>"><?= e($teacher['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">Carga horaria (h)</label>
                <input type="number" min="0" name="workload_hours" id="course-workload" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900">
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">Status</label>
                <select name="status" id="course-status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900">
                    <option value="draft">Rascunho</option>
                    <option value="published">Publicado</option>
                    <option value="archived">Arquivado</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-bold text-slate-700">Capa / imagem</label>
                <input type="file" name="cover" id="course-cover" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="block w-full rounded-2xl border border-dashed border-slate-300 px-4 py-4 text-sm text-slate-700">
            </div>
            <div class="md:col-span-2 flex justify-end gap-3 pt-3">
                <button type="button" class="rounded-full border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50" onclick="closeCourseModal()">Cancelar</button>
                <button type="submit" class="rounded-full bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:scale-[1.02]">Salvar curso</button>
            </div>
        </form>
    </div>
</div>

<script>
const courseModal = document.getElementById('course-modal');
const courseForm = document.getElementById('course-form');

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

courseForm?.addEventListener('submit', async function (event) {
    event.preventDefault();

    const formData = new FormData(event.currentTarget);
    const isUpdate = !!document.getElementById('course-id').value;
    const endpoint = isUpdate ? '/elearning/gestor/cursos/update' : '/elearning/gestor/cursos/store';

    const response = await fetch(endpoint, { method: 'POST', body: formData });
    const result = await response.json();
    if (!result.success) {
        alert(result.message || 'Nao foi possivel salvar o curso.');
        return;
    }

    window.location.reload();
});

async function deleteCourse(courseId, courseTitle = '') {
    const courseLabel = courseTitle ? `\"${courseTitle}\"` : 'este curso';
    if (!confirm(`Deseja excluir ${courseLabel}?\n\nIsso remove aulas, provas, matriculas, certificados, anexos e os videos hospedados do curso.`)) {
        return;
    }

    const formData = new FormData();
    formData.append('id', courseId);

    const response = await fetch('/elearning/gestor/cursos/delete', { method: 'POST', body: formData });
    const result = await response.json();
    if (!result.success) {
        alert(result.message || 'Nao foi possivel excluir o curso.');
        return;
    }

    alert(result.message || 'Curso excluido com sucesso.');
    window.location.reload();
}
</script>
