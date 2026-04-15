<?php
$templates = $templates ?? [];
$storage = $storage ?? [];
$courses = $courses ?? [];
$schemaReady = (bool) ($schemaReady ?? true);
$preselectedCourseId = (int) ($_GET['course_id'] ?? 0);
?>

<div class="el-ios">
    <div class="el-page el-stack">
        <header class="el-page-head">
            <div>
                <p class="el-eyebrow">Professor</p>
                <h1 class="el-title">Certificados</h1>
                <p class="el-subtitle">Configure modelos por curso com texto, cor, logo, assinatura e imagem de fundo.</p>
            </div>
            <div class="el-actions">
                <a href="/elearning/gestor/cursos" class="el-btn el-btn-primary"><i class="ph ph-books"></i> Cursos</a>
                <a href="/elearning/gestor/armazenamento" class="el-btn el-btn-secondary"><i class="ph ph-hard-drives"></i> Armazenamento</a>
                <a href="/elearning/gestor" class="el-btn el-btn-outline"><i class="ph ph-squares-four"></i> Painel</a>
            </div>
        </header>

        <?php if (!$schemaReady): ?>
            <div class="el-alert">O SQL do modulo ainda nao foi aplicado. Os templates podem ser visualizados, mas a gravacao depende do schema MariaDB.</div>
        <?php endif; ?>

        <section class="el-metric-grid">
            <?php foreach ([
                ['label' => 'Templates', 'value' => count($templates), 'icon' => 'ph-layout', 'tone' => 'blue'],
                ['label' => 'Cursos', 'value' => count($courses), 'icon' => 'ph-books', 'tone' => 'green'],
                ['label' => 'Minutos usados', 'value' => $storage['used_human'] ?? '0 min', 'icon' => 'ph-play', 'tone' => 'orange'],
                ['label' => 'Disponiveis', 'value' => $storage['available_human'] ?? '10.000 min', 'icon' => 'ph-battery-high', 'tone' => 'cyan'],
            ] as $card): ?>
                <article class="el-metric">
                    <div class="el-metric-top">
                        <span class="el-icon <?= e($card['tone']) ?>"><i class="ph <?= e($card['icon']) ?>"></i></span>
                    </div>
                    <p class="el-metric-value"><?= e((string) $card['value']) ?></p>
                    <p class="el-metric-label"><?= e($card['label']) ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <div class="el-grid">
            <section class="el-col-5">
                <div class="el-section-head">
                    <div>
                        <h2 class="el-section-title">Modelos iniciais</h2>
                        <p class="el-section-copy">Galeria base para a identidade visual dos certificados.</p>
                    </div>
                </div>
                <div class="el-list">
                    <?php if (!$templates): ?>
                        <div class="el-empty">Nenhum template encontrado.</div>
                    <?php endif; ?>

                    <?php foreach ($templates as $template): ?>
                        <?php $defaults = is_array($template['default_settings'] ?? null) ? $template['default_settings'] : []; ?>
                        <article class="el-list-item">
                            <span class="el-icon" style="background: <?= e($defaults['accent_color'] ?? '#007aff') ?>18;color:<?= e($defaults['accent_color'] ?? '#007aff') ?>">
                                <i class="ph ph-certificate"></i>
                            </span>
                            <div class="el-list-main">
                                <h3 class="el-list-title"><?= e($template['name']) ?></h3>
                                <p class="el-list-subtitle"><?= e($template['description'] ?? ($template['layout_key'] ?? 'Template')) ?></p>
                            </div>
                            <span class="el-badge"><?= e($defaults['finish'] ?? 'glass') ?></span>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="el-col-7">
                <div class="el-section-head">
                    <div>
                        <h2 class="el-section-title">Configuracao por curso</h2>
                        <p class="el-section-copy">Escolha o curso e personalize o certificado emitido automaticamente.</p>
                    </div>
                </div>

                <div class="el-list">
                    <?php if (!$courses): ?>
                        <div class="el-empty">Nenhum curso encontrado para personalizacao.</div>
                    <?php endif; ?>

                    <?php foreach ($courses as $course): ?>
                        <?php
                        $settings = is_array($course['certificate_settings'] ?? null) ? $course['certificate_settings'] : [];
                        $highlight = $preselectedCourseId === (int) $course['id'];
                        $accent = $settings['accent_color'] ?? '#007aff';
                        ?>
                        <article class="el-list-item" style="<?= $highlight ? 'border-color:rgba(0,122,255,.45);background:var(--el-blue-soft)' : '' ?>">
                            <span class="el-icon" style="background: <?= e($accent) ?>18;color:<?= e($accent) ?>">
                                <i class="ph ph-seal-check"></i>
                            </span>
                            <div class="el-list-main">
                                <div class="el-badges">
                                    <span class="el-badge blue"><?= e($course['status_label'] ?? 'Rascunho') ?></span>
                                    <span class="el-badge green"><?= e($course['category'] ?? 'Geral') ?></span>
                                </div>
                                <h3 class="el-list-title" style="margin-top:8px"><?= e($course['title']) ?></h3>
                                <p class="el-list-subtitle"><?= (int) ($course['workload_hours'] ?? 0) ?>h | <?= (int) ($course['lessons_count'] ?? 0) ?> aulas | <?= e($course['teacher_name'] ?? 'A definir') ?></p>
                            </div>
                            <button type="button" class="el-btn el-btn-primary" data-course-id="<?= (int) $course['id'] ?>" data-course='<?= e(json_encode($course, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>' onclick="openCertificateModal(this)">Configurar</button>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>
</div>

<div id="certificate-modal" class="el-modal hidden">
    <div class="el-modal-card" style="width:min(1040px,100%)">
        <div class="el-modal-head">
            <div>
                <p class="el-eyebrow">Configuracao</p>
                <h2 id="certificate-modal-title" class="el-title el-title-sm">Personalizar certificado</h2>
            </div>
            <button type="button" class="el-btn el-btn-outline" onclick="closeCertificateModal()">Fechar</button>
        </div>

        <form id="certificate-form" class="el-form-grid">
            <input type="hidden" name="course_id" id="certificate_course_id">
            <div class="el-field">
                <label for="certificate_template_id">Template</label>
                <select name="certificate_template_id" id="certificate_template_id">
                    <?php foreach ($templates as $template): ?>
                        <option value="<?= (int) $template['id'] ?>"><?= e($template['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="el-field">
                <label for="certificate_accent_color">Cor de destaque</label>
                <input type="color" name="accent_color" id="certificate_accent_color" value="#007aff" style="height:46px">
            </div>
            <div class="el-field">
                <label for="certificate_finish">Acabamento visual</label>
                <input type="text" name="finish" id="certificate_finish" placeholder="glass, premium, sleek...">
            </div>
            <div class="el-field">
                <label for="certificate_logo">Logo</label>
                <input type="file" name="logo" id="certificate_logo" accept=".png,.jpg,.jpeg,.webp,image/*">
            </div>
            <div class="el-field">
                <label for="certificate_signature">Assinatura</label>
                <input type="file" name="signature" id="certificate_signature" accept=".png,.jpg,.jpeg,.webp,image/*">
            </div>
            <div class="el-field">
                <label for="certificate_background">Imagem de fundo opcional</label>
                <input type="file" name="background" id="certificate_background" accept=".png,.jpg,.jpeg,.webp,image/*">
            </div>
            <div class="el-field el-form-full">
                <label for="certificate_custom_text">Texto personalizado</label>
                <textarea name="custom_text" id="certificate_custom_text" rows="5"></textarea>
            </div>
            <div class="el-panel el-form-full" id="certificate-preview">
                <p class="el-eyebrow">SGI E-Learning</p>
                <h3 class="el-title el-title-sm">Certificado de Conclusao</h3>
                <p class="el-subtitle">A cor, o acabamento e o texto serao atualizados em tempo real nesta previa.</p>
            </div>
            <div class="el-actions el-form-full" style="justify-content:flex-end">
                <button type="button" class="el-btn el-btn-outline" onclick="closeCertificateModal()">Cancelar</button>
                <button type="submit" class="el-btn el-btn-primary">Salvar configuracao</button>
            </div>
        </form>
    </div>
</div>

<div id="certificate-toast" class="pointer-events-none fixed right-4 top-4 z-[90] hidden px-4 py-3 text-sm font-bold text-white"></div>

<script>
const certificateModal = document.getElementById('certificate-modal');
const certificateForm = document.getElementById('certificate-form');
const certificatePreview = document.getElementById('certificate-preview');

function showCertificateToast(message, type = 'success') {
    const toast = document.getElementById('certificate-toast');
    toast.textContent = message;
    toast.className = 'pointer-events-none fixed right-4 top-4 z-[90] px-4 py-3 text-sm font-bold text-white ' + (type === 'error' ? 'bg-rose-600' : 'bg-slate-900');
    toast.classList.remove('hidden');
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => toast.classList.add('hidden'), 2600);
}

function syncCertificatePreview() {
    const accent = document.getElementById('certificate_accent_color').value || '#007aff';
    const finish = document.getElementById('certificate_finish').value || 'glass';
    const customText = document.getElementById('certificate_custom_text').value || 'Certificamos que o aluno concluiu o curso com exito e atingiu os criterios minimos de aproveitamento.';
    certificatePreview.style.background = `linear-gradient(135deg, ${accent}12, #ffffff 46%, #f5f5f7)`;
    certificatePreview.querySelector('h3').style.color = accent;
    certificatePreview.querySelector('p.el-subtitle').textContent = `${customText} Acabamento: ${finish}.`;
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
    document.getElementById('certificate_accent_color').value = settings.accent_color || '#007aff';
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
