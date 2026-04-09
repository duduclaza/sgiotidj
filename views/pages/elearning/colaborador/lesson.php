<?php
$lesson = $data['lesson'] ?? [];
$attachments = $data['attachments'] ?? [];
$playlist = $data['playlist'] ?? [];
$progress = $data['progress'] ?? [];
$previousLessonId = $data['previous_lesson_id'] ?? null;
$nextLessonId = $data['next_lesson_id'] ?? null;
$lessonVideo = $lesson['video'] ?? null;
?>

<section class="space-y-6">
    <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 p-8 shadow-soft backdrop-blur-xl">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-4">
                <div class="flex flex-wrap items-center gap-3 text-sm text-slate-300">
                    <a href="/elearning/colaborador" class="font-semibold text-sky-200 transition hover:text-white">Dashboard</a>
                    <span>/</span>
                    <a href="/elearning/colaborador/cursos/<?= (int) ($lesson['course_id'] ?? 0) ?>" class="font-semibold text-sky-200 transition hover:text-white"><?= e($lesson['course_title'] ?? 'Curso') ?></a>
                </div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-sky-200/70">Aula em video</p>
                <h2 class="text-4xl font-black tracking-tight text-white"><?= e($lesson['title'] ?? 'Aula') ?></h2>
                <p class="max-w-3xl text-base leading-relaxed text-slate-300"><?= e($lesson['description'] ?? 'Acompanhe a aula em video e use os materiais de apoio para consolidar o conteudo.') ?></p>
            </div>
            <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/35 px-5 py-4">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Progresso da aula</p>
                <p class="mt-3 text-3xl font-black text-white"><?= number_format((float) ($progress['video_progress_percent'] ?? 0), 0) ?>%</p>
            </div>
        </div>
    </div>

    <div class="grid gap-8 xl:grid-cols-[1.5fr,0.85fr]">
        <div class="space-y-6">
            <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-black shadow-soft">
                <?php if ($lessonVideo): ?>
                    <?php if (($lessonVideo['provider'] ?? '') === 'bunny' && empty($lessonVideo['is_ready'])): ?>
                        <div class="flex aspect-video flex-col items-center justify-center gap-4 bg-slate-950 px-8 text-center text-slate-300" id="lessonProcessingPanel" data-lesson-id="<?= (int) ($lesson['id'] ?? 0) ?>">
                            <div class="space-y-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-300/80">Processando video</p>
                                <h3 class="text-3xl font-black text-white">O Bunny Stream ainda esta preparando esta aula</h3>
                                <p class="max-w-2xl text-sm leading-relaxed text-slate-300" id="lessonProcessingMessage"><?= e($lessonVideo['processing_message'] ?? 'Atualize a pagina em alguns instantes para assistir ao conteudo.') ?></p>
                            </div>
                            <button type="button" id="lessonProcessingButton" onclick="window.location.reload()" class="rounded-full bg-white px-5 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Atualizar pagina</button>
                        </div>
                    <?php elseif (($lessonVideo['provider'] ?? '') === 'bunny'): ?>
                        <iframe
                            id="lessonIframe"
                            src="<?= e(($lessonVideo['embed_url'] ?? '') . '?autoplay=false&t=' . time()) ?>"
                            class="aspect-video w-full bg-black"
                            loading="lazy"
                            allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;"
                            allowfullscreen
                        ></iframe>
                    <?php else: ?>
                        <video id="lessonPlayer" controls class="aspect-video w-full bg-black">
                            <source src="/elearning/colaborador/videos/<?= (int) $lesson['id'] ?>" type="<?= e($lesson['video_mime_type'] ?? 'video/mp4') ?>">
                            Seu navegador nao suporta reproducao de video.
                        </video>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="flex aspect-video items-center justify-center bg-slate-900 text-center text-slate-400">
                        Esta aula ainda nao possui video publicado.
                    </div>
                <?php endif; ?>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-soft backdrop-blur-xl">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-200/70">Conclusao</p>
                        <h3 class="mt-2 text-2xl font-black tracking-tight text-white">Marcar progresso</h3>
                    </div>
                    <button type="button" class="rounded-full bg-white px-5 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]" onclick="completeLesson()">Marcar aula como concluida</button>
                </div>
                <p class="mt-4 text-sm leading-relaxed text-slate-300">A aula e considerada concluida automaticamente ao atingir 90% de progresso no player, mas voce tambem pode registrar a conclusao manualmente apos revisar o conteudo.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <?php if ($previousLessonId): ?>
                        <a href="/elearning/colaborador/materiais/<?= (int) $previousLessonId ?>/assistir" class="rounded-full border border-white/15 px-4 py-3 text-sm font-black text-white transition hover:bg-white/10">Aula anterior</a>
                    <?php endif; ?>
                    <?php if ($nextLessonId): ?>
                        <a href="/elearning/colaborador/materiais/<?= (int) $nextLessonId ?>/assistir" class="rounded-full bg-sky-500 px-4 py-3 text-sm font-black text-slate-950 transition hover:scale-[1.02]">Proxima aula</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <section class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-soft backdrop-blur-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-200/70">Materiais</p>
                <h3 class="mt-2 text-2xl font-black tracking-tight text-white">Anexos de apoio</h3>
                <div class="mt-5 space-y-3">
                    <?php if (!$attachments): ?>
                        <p class="text-sm text-slate-300">Nenhum material extra foi anexado a esta aula.</p>
                    <?php endif; ?>
                    <?php foreach ($attachments as $attachment): ?>
                        <a href="/elearning/colaborador/anexos/<?= (int) $attachment['id'] ?>/download" class="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/35 px-4 py-3 text-sm text-slate-200 transition hover:bg-slate-900">
                            <span class="truncate"><?= e($attachment['title'] ?: $attachment['file_name']) ?></span>
                            <i class="ph ph-download-simple"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-soft backdrop-blur-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-violet-200/70">Playlist</p>
                <h3 class="mt-2 text-2xl font-black tracking-tight text-white">Trilha do curso</h3>
                <div class="mt-5 space-y-3">
                    <?php foreach ($playlist as $playlistLesson): ?>
                        <a href="/elearning/colaborador/materiais/<?= (int) $playlistLesson['id'] ?>/assistir" class="flex items-center justify-between rounded-2xl border px-4 py-3 text-sm transition <?= (int) $playlistLesson['id'] === (int) ($lesson['id'] ?? 0) ? 'border-sky-300/30 bg-sky-400/10 text-white' : 'border-white/10 bg-slate-950/35 text-slate-200 hover:bg-slate-900' ?>">
                            <span><?= e($playlistLesson['title']) ?></span>
                            <?php if (!empty($playlistLesson['is_completed'])): ?>
                                <i class="ph-fill ph-check-circle text-emerald-300"></i>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        </aside>
    </div>
</section>

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
        if (!silent) {
            showELToast(result.message || 'Nao foi possivel registrar o progresso.', 'error');
        }
        return;
    }

    lastSentProgress = Math.max(lastSentProgress, Number(progressPercent || 0));
    if (!silent) {
        showELToast('Progresso atualizado.', 'success');
    }
}

function syncProgressIfNeeded(progressPercent) {
    const normalized = Math.max(0, Math.min(100, Math.round(progressPercent)));
    if (normalized < 5) {
        return;
    }
    if (normalized < 90 && normalized < lastSentProgress + 10) {
        return;
    }

    registerProgress(normalized, true);
}

async function completeLesson(silent = false) {
    if (completionTriggered) {
        return;
    }

    completionTriggered = true;
    await registerProgress(100, silent);
    setTimeout(() => window.location.reload(), 600);
}

const player = document.getElementById('lessonPlayer');
if (player) {
    player.addEventListener('timeupdate', () => {
        const duration = Number(player.duration || 0);
        const currentTime = Number(player.currentTime || 0);
        if (duration <= 0) {
            return;
        }

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
        if (duration <= 0) {
            return;
        }

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
        if (processingLessonId <= 0) {
            return;
        }

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
                lessonProcessingMessage.textContent = result.processing_message || 'O Bunny Stream ainda esta preparando o video desta aula.';
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
