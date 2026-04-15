<?php
$lesson = $data['lesson'] ?? [];
$attachments = $data['attachments'] ?? [];
$playlist = $data['playlist'] ?? [];
$progress = $data['progress'] ?? [];
$previousLessonId = $data['previous_lesson_id'] ?? null;
$nextLessonId = $data['next_lesson_id'] ?? null;
$lessonVideo = $lesson['video'] ?? null;
$progressPercent = min(100, max(0, (float) ($progress['video_progress_percent'] ?? 0)));
?>

<div class="el-ios">
    <div class="el-page el-stack">
        <header class="el-page-head">
            <div>
                <p class="el-eyebrow"><a href="/elearning/colaborador" style="color:inherit">Aluno</a> / <a href="/elearning/colaborador/cursos/<?= (int) ($lesson['course_id'] ?? 0) ?>" style="color:inherit"><?= e($lesson['course_title'] ?? 'Curso') ?></a></p>
                <h1 class="el-title"><?= e($lesson['title'] ?? 'Aula') ?></h1>
                <p class="el-subtitle"><?= e($lesson['description'] ?? 'Acompanhe a aula em video e use os materiais de apoio para consolidar o conteudo.') ?></p>
            </div>
            <div class="el-actions">
                <?php if ($previousLessonId): ?>
                    <a href="/elearning/colaborador/materiais/<?= (int) $previousLessonId ?>/assistir" class="el-btn el-btn-secondary">Aula anterior</a>
                <?php endif; ?>
                <?php if ($nextLessonId): ?>
                    <a href="/elearning/colaborador/materiais/<?= (int) $nextLessonId ?>/assistir" class="el-btn el-btn-primary">Proxima aula</a>
                <?php endif; ?>
                <a href="/elearning/colaborador/cursos/<?= (int) ($lesson['course_id'] ?? 0) ?>" class="el-btn el-btn-outline">Curso</a>
            </div>
        </header>

        <div class="el-grid">
            <main class="el-col-8 el-stack">
                <section class="el-video-frame">
                    <?php if ($lessonVideo): ?>
                        <?php if (($lessonVideo['provider'] ?? '') === 'bunny' && empty($lessonVideo['is_ready'])): ?>
                            <div class="el-video-empty" id="lessonProcessingPanel" data-lesson-id="<?= (int) ($lesson['id'] ?? 0) ?>">
                                <div class="el-panel" style="max-width:620px;text-align:center;background:rgba(255,255,255,.94);color:var(--el-text)">
                                    <span class="el-icon cyan"><i class="ph ph-clock-countdown"></i></span>
                                    <h2 class="el-section-title" style="margin-top:14px">Video em processamento</h2>
                                    <p class="el-section-copy" id="lessonProcessingMessage"><?= e($lessonVideo['processing_message'] ?? 'Atualize a pagina em alguns instantes para assistir ao conteudo.') ?></p>
                                    <button type="button" id="lessonProcessingButton" onclick="window.location.reload()" class="el-btn el-btn-primary" style="margin-top:14px">Atualizar pagina</button>
                                </div>
                            </div>
                        <?php elseif (($lessonVideo['provider'] ?? '') === 'bunny'): ?>
                            <iframe
                                id="lessonIframe"
                                src="<?= e(($lessonVideo['embed_url'] ?? '') . '?autoplay=false&t=' . time()) ?>"
                                loading="lazy"
                                allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;"
                                allowfullscreen
                            ></iframe>
                        <?php else: ?>
                            <video id="lessonPlayer" controls>
                                <source src="/elearning/colaborador/videos/<?= (int) $lesson['id'] ?>" type="<?= e($lesson['video_mime_type'] ?? 'video/mp4') ?>">
                                Seu navegador nao suporta reproducao de video.
                            </video>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="el-video-empty">Esta aula ainda nao possui video publicado.</div>
                    <?php endif; ?>
                </section>

                <section class="el-panel">
                    <div class="el-section-head">
                        <div>
                            <h2 class="el-section-title">Progresso da aula</h2>
                            <p class="el-section-copy">A conclusao tambem e registrada automaticamente ao atingir 90% do video.</p>
                        </div>
                        <button type="button" class="el-btn el-btn-primary" onclick="completeLesson()">Marcar como concluida</button>
                    </div>
                    <div class="el-progress">
                        <div class="el-progress-label"><span>Assistido</span><strong><?= number_format($progressPercent, 0) ?>%</strong></div>
                        <div class="el-progress-track"><div class="el-progress-fill" style="width: <?= $progressPercent ?>%"></div></div>
                    </div>
                </section>
            </main>

            <aside class="el-col-4 el-stack">
                <section class="el-panel">
                    <h2 class="el-section-title">Materiais</h2>
                    <div class="el-list" style="margin-top:14px">
                        <?php if (!$attachments): ?>
                            <div class="el-empty">Nenhum material extra foi anexado a esta aula.</div>
                        <?php endif; ?>
                        <?php foreach ($attachments as $attachment): ?>
                            <a href="/elearning/colaborador/anexos/<?= (int) $attachment['id'] ?>/download" class="el-list-item">
                                <span class="el-list-main">
                                    <strong class="el-list-title"><?= e($attachment['title'] ?: $attachment['file_name']) ?></strong>
                                    <span class="el-list-subtitle">Baixar material de apoio</span>
                                </span>
                                <i class="ph ph-download-simple"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="el-panel">
                    <h2 class="el-section-title">Trilha do curso</h2>
                    <div class="el-list" style="margin-top:14px">
                        <?php foreach ($playlist as $playlistLesson): ?>
                            <a href="/elearning/colaborador/materiais/<?= (int) $playlistLesson['id'] ?>/assistir" class="el-list-item" style="<?= (int) $playlistLesson['id'] === (int) ($lesson['id'] ?? 0) ? 'border-color:rgba(0,122,255,.45);background:var(--el-blue-soft)' : '' ?>">
                                <span class="el-list-main">
                                    <strong class="el-list-title"><?= e($playlistLesson['title']) ?></strong>
                                </span>
                                <?php if (!empty($playlistLesson['is_completed'])): ?>
                                    <i class="ph-fill ph-check-circle" style="color:var(--el-green)"></i>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>

<?php if ($lessonVideo && ($lessonVideo['provider'] ?? '') === 'bunny' && !empty($lessonVideo['is_ready'])): ?>
    <script src="https://assets.mediadelivery.net/playerjs/playerjs-latest.min.js"></script>
<?php endif; ?>

<script>
let lastSentProgress = <?= (int) round((float) ($progress['video_progress_percent'] ?? 0)) ?>;
let completionTriggered = false;

async function parseStudentJsonResponse(response) {
    const text = await response.text();
    let result = {};

    try {
        result = text ? JSON.parse(text) : {};
    } catch (error) {
        throw new Error('Resposta invalida do servidor.');
    }

    if (!response.ok || !result.success) {
        throw new Error(result.message || 'Nao foi possivel concluir a operacao.');
    }

    return result;
}

async function registerProgress(progressPercent, silent = false) {
    const formData = new FormData();
    formData.append('lesson_id', <?= (int) ($lesson['id'] ?? 0) ?>);
    formData.append('progress_percent', progressPercent);

    const response = await fetch('/elearning/colaborador/progresso/registrar', { method: 'POST', body: formData });
    const result = await response.json();
    if (!result.success) {
        if (!silent && typeof showELToast === 'function') {
            showELToast(result.message || 'Nao foi possivel registrar o progresso.', 'error');
        }
        return;
    }

    lastSentProgress = Math.max(lastSentProgress, Number(progressPercent || 0));
    if (!silent && typeof showELToast === 'function') {
        showELToast('Progresso atualizado.', 'success');
    }
}

function syncProgressIfNeeded(progressPercent) {
    const normalized = Math.max(0, Math.min(100, Math.round(progressPercent)));
    if (normalized < 5) return;
    if (normalized < 90 && normalized < lastSentProgress + 10) return;
    registerProgress(normalized, true);
}

async function completeLesson(silent = false) {
    if (completionTriggered) return;
    completionTriggered = true;
    await registerProgress(100, silent);
    setTimeout(() => window.location.reload(), 600);
}

const player = document.getElementById('lessonPlayer');
if (player) {
    player.addEventListener('timeupdate', () => {
        const duration = Number(player.duration || 0);
        const currentTime = Number(player.currentTime || 0);
        if (duration <= 0) return;

        const percent = (currentTime / duration) * 100;
        if (percent >= 90) {
            completeLesson(true);
            return;
        }

        syncProgressIfNeeded(percent);
    });

    player.addEventListener('ended', () => completeLesson(true));
}

const bunnyIframe = document.getElementById('lessonIframe');
if (bunnyIframe && window.playerjs) {
    const bunnyPlayer = new playerjs.Player(bunnyIframe);
    bunnyPlayer.on('timeupdate', (data) => {
        const seconds = Number(data?.seconds || 0);
        const duration = Number(data?.duration || 0);
        if (duration <= 0) return;

        const percent = (seconds / duration) * 100;
        if (percent >= 90) {
            completeLesson(true);
            return;
        }

        syncProgressIfNeeded(percent);
    });

    bunnyPlayer.on('ended', () => completeLesson(true));
}

const lessonProcessingPanel = document.getElementById('lessonProcessingPanel');
if (lessonProcessingPanel) {
    const lessonProcessingMessage = document.getElementById('lessonProcessingMessage');
    const lessonProcessingButton = document.getElementById('lessonProcessingButton');
    const processingLessonId = Number(lessonProcessingPanel.dataset.lessonId || 0);
    let processingAttempts = 0;

    const pollProcessingStatus = async () => {
        if (processingLessonId <= 0) return;

        try {
            const response = await fetch(`/elearning/colaborador/aulas/${processingLessonId}/video-status`, {
                headers: { Accept: 'application/json' },
            });
            const result = await parseStudentJsonResponse(response);

            if (result.has_video && result.is_ready) {
                if (typeof showELToast === 'function') {
                    showELToast('Video pronto. Abrindo a aula...', 'success');
                }
                window.setTimeout(() => window.location.reload(), 700);
                return;
            }

            if (lessonProcessingMessage) {
                lessonProcessingMessage.textContent = result.processing_message || 'O SGI STREAM ainda esta preparando o video desta aula.';
            }

            if (lessonProcessingButton) {
                lessonProcessingButton.textContent = 'Atualizando automaticamente...';
            }
        } catch (error) {
            if (lessonProcessingMessage) {
                lessonProcessingMessage.textContent = 'Ainda preparando o video. Vamos tentar atualizar novamente em instantes.';
            }
        }

        processingAttempts += 1;
        if (processingAttempts < 30) {
            window.setTimeout(pollProcessingStatus, 6000);
        } else if (lessonProcessingButton) {
            lessonProcessingButton.textContent = 'Atualizar pagina';
        }
    };

    window.setTimeout(pollProcessingStatus, 5000);
}
</script>
