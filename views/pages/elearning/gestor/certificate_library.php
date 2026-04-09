<?php
$templates = $templates ?? [];
$storage = $storage ?? [];
$courses = $courses ?? [];
$schemaReady = (bool) ($schemaReady ?? true);
$preselectedCourseId = (int) ($_GET['course_id'] ?? 0);
?>

<section class="space-y-8">
    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-[linear-gradient(135deg,_#111827,_#1d4ed8_58%,_#0284c7)] p-8 text-white shadow-2xl">
        <div class="grid gap-8 xl:grid-cols-[1.15fr,0.85fr]">
            <div class="space-y-5">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-sky-100/70">Biblioteca de certificados</p>
                <h1 class="text-4xl font-black tracking-tight sm:text-5xl">Cinco modelos iniciais para personalizacao por curso.</h1>
                <p class="max-w-3xl text-base leading-relaxed text-sky-50/80">Escolha o template do curso, ajuste cor, texto, acabamento, logo, assinatura e imagem de fundo. A emissao e automatica quando o aluno cumpre progresso e aprovacao minima.</p>
                <div class="flex flex-wrap gap-3">
                    <a href="/elearning/gestor/cursos" class="rounded-full bg-white px-5 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Voltar aos cursos</a>
                    <a href="/elearning/gestor/armazenamento" class="rounded-full border border-white/15 px-5 py-3 text-sm font-black text-white transition hover:bg-white/10">Minutos de video</a>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <?php foreach ([
                    ['label' => 'Templates ativos', 'value' => count($templates)],
                    ['label' => 'Cursos do professor', 'value' => count($courses)],
                    ['label' => 'Minutos usados', 'value' => $storage['used_human'] ?? '0 min'],
                    ['label' => 'Minutos disponiveis', 'value' => $storage['available_human'] ?? '10.000 min'],
                ] as $card): ?>
                    <article class="rounded-[1.75rem] border border-white/10 bg-white/10 p-5 backdrop-blur-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-white/60"><?= e($card['label']) ?></p>
                        <p class="mt-4 text-3xl font-black text-white"><?= e((string) $card['value']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php if (!$schemaReady): ?>
        <div class="rounded-[1.75rem] border border-amber-300 bg-amber-50 px-5 py-4 text-sm leading-relaxed text-amber-950">
            O SQL do modulo ainda nao foi aplicado. Os templates ja podem ser visualizados, mas a gravacao das configuracoes depende do schema MariaDB.
        </div>
    <?php endif; ?>

    <div class="grid gap-6 xl:grid-cols-[1.05fr,0.95fr]">
        <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Modelos iniciais</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Gallery</h2>
            </div>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <?php foreach ($templates as $template): ?>
                    <?php $defaults = is_array($template['default_settings'] ?? null) ? $template['default_settings'] : []; ?>
                    <article class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-slate-50 shadow-sm">
                        <div class="h-40 p-5" style="background:
                            radial-gradient(circle at top left, <?= e($defaults['accent_color'] ?? '#1d4ed8') ?>55, transparent 44%),
                            linear-gradient(135deg, #ffffff, #eff6ff 55%, #e2e8f0);">
                            <div class="rounded-[1.5rem] border border-white/60 bg-white/70 p-4 backdrop-blur">
                                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400"><?= e($template['preview_palette'] ?? 'Preview') ?></p>
                                <h3 class="mt-3 text-2xl font-black tracking-tight text-slate-900"><?= e($template['name']) ?></h3>
                                <p class="mt-2 text-sm text-slate-600"><?= e($template['layout_key'] ?? 'layout') ?></p>
                            </div>
                        </div>
                        <div class="space-y-3 p-5">
                            <p class="text-sm leading-relaxed text-slate-600"><?= e($template['description'] ?? '') ?></p>
                            <div class="flex items-center justify-between text-sm text-slate-500">
                                <span>Acabamento padrao</span>
                                <strong class="text-slate-900"><?= e($defaults['finish'] ?? 'glass') ?></strong>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="space-y-6">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Configuracao por curso</p>
                        <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Escolha o destino</h2>
                    </div>
                    <span class="rounded-full border border-slate-200 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-slate-500"><?= count($courses) ?> curso(s)</span>
                </div>

                <div class="mt-6 space-y-4">
                    <?php if (!$courses): ?>
                        <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-500">
                            Nenhum curso encontrado para personalizacao.
                        </div>
                    <?php endif; ?>
                    <?php foreach ($courses as $course): ?>
                        <?php
                        $settings = is_array($course['certificate_settings'] ?? null) ? $course['certificate_settings'] : [];
                        $highlight = $preselectedCourseId === (int) $course['id'];
                        ?>
                        <article class="rounded-[1.75rem] border <?= $highlight ? 'border-sky-300 bg-sky-50' : 'border-slate-200 bg-slate-50' ?> p-5">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <div class="flex flex-wrap gap-3">
                                        <span class="rounded-full bg-slate-900 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-white"><?= e($course['status_label'] ?? 'Rascunho') ?></span>
                                        <span class="rounded-full bg-sky-100 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-sky-700"><?= e($course['category'] ?? 'Geral') ?></span>
                                    </div>
                                    <h3 class="mt-3 text-2xl font-black tracking-tight text-slate-900"><?= e($course['title']) ?></h3>
                                    <p class="mt-2 text-sm text-slate-600"><?= (int) ($course['workload_hours'] ?? 0) ?>h | <?= (int) ($course['lessons_count'] ?? 0) ?> aulas | professor <?= e($course['teacher_name'] ?? 'A definir') ?></p>
                                </div>
                                <button type="button" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:scale-[1.02]" data-course-id="<?= (int) $course['id'] ?>" data-course='<?= e(json_encode($course, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>' onclick="openCertificateModal(this)">Configurar</button>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-slate-500">
                                <span>Cor: <strong class="text-slate-900"><?= e($settings['accent_color'] ?? '#1d4ed8') ?></strong></span>
                                <span>Acabamento: <strong class="text-slate-900"><?= e($settings['finish'] ?? 'glass') ?></strong></span>
                                <span>Template atual: <strong class="text-slate-900"><?= (int) ($course['certificate_template_id'] ?? 1) ?></strong></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </div>
</section>

<div id="certificate-modal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
    <div class="w-full max-w-5xl rounded-[2rem] bg-white p-8 shadow-2xl">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Configuracao</p>
                <h2 id="certificate-modal-title" class="mt-2 text-3xl font-black tracking-tight text-slate-900">Personalizar certificado</h2>
            </div>
            <button type="button" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50" onclick="closeCertificateModal()">Fechar</button>
        </div>

        <form id="certificate-form" class="mt-8 grid gap-5 md:grid-cols-2">
            <input type="hidden" name="course_id" id="certificate_course_id">
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">Template</label>
                <select name="certificate_template_id" id="certificate_template_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900">
                    <?php foreach ($templates as $template): ?>
                        <option value="<?= (int) $template['id'] ?>"><?= e($template['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">Cor de destaque</label>
                <input type="color" name="accent_color" id="certificate_accent_color" value="#1d4ed8" class="h-[52px] w-full rounded-2xl border border-slate-200 bg-white px-3 py-2">
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">Acabamento visual</label>
                <input type="text" name="finish" id="certificate_finish" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900" placeholder="glass, premium, sleek...">
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">Logo</label>
                <input type="file" name="logo" id="certificate_logo" accept=".png,.jpg,.jpeg,.webp,image/*" class="block w-full rounded-2xl border border-dashed border-slate-300 px-4 py-4 text-sm text-slate-700">
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">Assinatura</label>
                <input type="file" name="signature" id="certificate_signature" accept=".png,.jpg,.jpeg,.webp,image/*" class="block w-full rounded-2xl border border-dashed border-slate-300 px-4 py-4 text-sm text-slate-700">
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">Imagem de fundo opcional</label>
                <input type="file" name="background" id="certificate_background" accept=".png,.jpg,.jpeg,.webp,image/*" class="block w-full rounded-2xl border border-dashed border-slate-300 px-4 py-4 text-sm text-slate-700">
            </div>
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-bold text-slate-700">Texto personalizado</label>
                <textarea name="custom_text" id="certificate_custom_text" rows="5" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-slate-900"></textarea>
            </div>
            <div class="md:col-span-2 rounded-[1.75rem] border border-slate-200 bg-slate-50 p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Previa conceitual</p>
                <div id="certificate-preview" class="mt-4 rounded-[1.5rem] border border-white bg-white p-6 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">SGI E-Learning</p>
                    <h3 class="mt-3 text-3xl font-black tracking-tight text-slate-900">Certificado de Conclusao</h3>
                    <p class="mt-4 text-sm leading-relaxed text-slate-600">A cor, o acabamento e o texto serao atualizados em tempo real nesta previa.</p>
                </div>
            </div>
            <div class="md:col-span-2 flex justify-end gap-3">
                <button type="button" class="rounded-full border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50" onclick="closeCertificateModal()">Cancelar</button>
                <button type="submit" class="rounded-full bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:scale-[1.02]">Salvar configuracao</button>
            </div>
        </form>
    </div>
</div>

<div id="certificate-toast" class="pointer-events-none fixed right-4 top-4 z-[90] hidden rounded-2xl bg-slate-900 px-4 py-3 text-sm font-bold text-white shadow-2xl"></div>

<script>
const certificateModal = document.getElementById('certificate-modal');
const certificateForm = document.getElementById('certificate-form');
const certificatePreview = document.getElementById('certificate-preview');

function showCertificateToast(message, type = 'success') {
    const toast = document.getElementById('certificate-toast');
    toast.textContent = message;
    toast.className = 'pointer-events-none fixed right-4 top-4 z-[90] rounded-2xl px-4 py-3 text-sm font-bold text-white shadow-2xl ' + (type === 'error' ? 'bg-rose-600' : 'bg-slate-900');
    toast.classList.remove('hidden');
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => toast.classList.add('hidden'), 2600);
}

function syncCertificatePreview() {
    const accent = document.getElementById('certificate_accent_color').value || '#1d4ed8';
    const finish = document.getElementById('certificate_finish').value || 'glass';
    const customText = document.getElementById('certificate_custom_text').value || 'Certificamos que o aluno concluiu o curso com exito e atingiu os criterios minimos de aproveitamento.';
    certificatePreview.style.background = `linear-gradient(135deg, ${accent}18, #ffffff 45%, #f8fafc)`;
    certificatePreview.querySelector('h3').style.color = accent;
    certificatePreview.querySelector('p').textContent = `${customText} | acabamento ${finish}.`;
}

function validateAssetInput(input) {
    const file = input?.files?.[0];
    if (!file) return true;
    if (file.size > 5 * 1024 * 1024) {
        input.value = '';
        showCertificateToast('Cada imagem do certificado deve ter no maximo 5 MB.', 'error');
        return false;
    }
    return true;
}

function openCertificateModal(button) {
    const course = JSON.parse(button.dataset.course || '{}');
    const settings = course.certificate_settings || {};
    document.getElementById('certificate-modal-title').textContent = `Personalizar: ${course.title || 'Curso'}`;
    document.getElementById('certificate_course_id').value = course.id || '';
    document.getElementById('certificate_template_id').value = course.certificate_template_id || 1;
    document.getElementById('certificate_accent_color').value = settings.accent_color || '#1d4ed8';
    document.getElementById('certificate_finish').value = settings.finish || 'glass';
    document.getElementById('certificate_custom_text').value = settings.custom_text || 'Certificamos que o aluno concluiu o curso com exito e atingiu os criterios minimos de aproveitamento.';
    syncCertificatePreview();
    certificateModal.classList.remove('hidden');
    certificateModal.classList.add('flex');
}

function closeCertificateModal() {
    certificateModal.classList.add('hidden');
    certificateModal.classList.remove('flex');
}

['certificate_accent_color', 'certificate_finish', 'certificate_custom_text'].forEach((id) => {
    document.getElementById(id)?.addEventListener('input', syncCertificatePreview);
});

['certificate_logo', 'certificate_signature', 'certificate_background'].forEach((id) => {
    document.getElementById(id)?.addEventListener('change', function () {
        validateAssetInput(this);
    });
});

certificateForm?.addEventListener('submit', async function (event) {
    event.preventDefault();
    if (!validateAssetInput(document.getElementById('certificate_logo')) || !validateAssetInput(document.getElementById('certificate_signature')) || !validateAssetInput(document.getElementById('certificate_background'))) {
        return;
    }
    try {
        const response = await fetch('/elearning/gestor/diploma/save', { method: 'POST', body: new FormData(event.currentTarget) });
        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || 'Nao foi possivel salvar a configuracao.');
        }
        showCertificateToast(result.message || 'Configuracao salva com sucesso.');
        setTimeout(() => window.location.reload(), 700);
    } catch (error) {
        showCertificateToast(error.message, 'error');
    }
});

window.addEventListener('DOMContentLoaded', () => {
    const preselected = <?= $preselectedCourseId ?>;
    if (preselected > 0) {
        const button = document.querySelector(`[data-course-id="${preselected}"]`);
        if (button) {
            openCertificateModal(button);
        }
    }
    syncCertificatePreview();
});
</script>
