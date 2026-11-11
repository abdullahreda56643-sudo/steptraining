<?php
$passage = null;
foreach ($questions as $question) {
    if ($question['passage_body'] !== null || $question['audio_path'] !== null) {
        $passage = $question;
        break;
    }
}
$answeredCount = count($answers);
$questionTotal = count($questions);
$canToggleExplanation = $canToggleExplanation ?? false;
$requiresAllAnswers = $requiresAllAnswers ?? false;
$practiceName = $practice['title_ar'] ?? 'التدريب';
$audioMimeTypes = [
    'mp3' => 'audio/mpeg',
    'wav' => 'audio/wav',
    'm4a' => 'audio/mp4',
    'ogg' => 'audio/ogg',
];
?>

<section id="practice-app"
         class="student-practice space-y-5"
         data-attempt-id="<?= e((string) ($attemptId ?? '')) ?>"
         data-csrf-token="<?= e($csrfToken) ?>"
         data-total="<?= e((string) $questionTotal) ?>"
         data-requires-all="<?= $requiresAllAnswers ? 'true' : 'false' ?>"
         data-show-explanation="<?= $showExplanation ? 'true' : 'false' ?>"
         data-submit-url="<?= e(url('/api/submit-answer')) ?>"
         data-finish-url="<?= e(url('/api/finish-practice')) ?>"
         data-toggle-explanation-url="<?= e(url('/api/toggle-explanation')) ?>"
         data-placement-url="<?= e(url('/placement')) ?>"
         data-daily-url="<?= e(url('/daily')) ?>">
    <header class="student-practice-header">
        <div class="student-practice-header__top">
            <div class="student-practice-header__title">
                <a href="<?= e(url($backUrl ?? ('/section/' . $practice['section_slug']))) ?>" class="student-back-link"><span aria-hidden="true">→</span> العودة إلى <?= e($practice['section_name_ar']) ?></a>
                <span class="student-page-kicker">جلسة تدريب مركّزة</span>
                <h1><?= e($practiceName) ?></h1>
                <p dir="ltr"><?= e($practice['title_en']) ?></p>
            </div>
            <?php if ($attemptId !== null): ?>
                <div class="student-practice-counters">
                    <div class="student-practice-counter"><span>الأسئلة المجابة</span><strong><span id="practice-progress-count"><?= e((string) $answeredCount) ?></span><small> / <?= e((string) $questionTotal) ?></small></strong></div>
                    <div class="student-practice-counter student-practice-counter--timer"><span>الوقت المنقضي</span><strong id="practice-timer" aria-live="off">00:00</strong></div>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($attemptId !== null && $canToggleExplanation): ?>
            <div class="student-practice-settings">
                <label for="explanation-toggle"><input id="explanation-toggle" type="checkbox" <?= $showExplanation ? 'checked' : '' ?>> إظهار الشرح التفصيلي بعد الإجابة</label>
                <span id="explanation-toggle-status" aria-live="polite"></span>
            </div>
        <?php endif; ?>
        <?php if ($questions): ?>
            <div class="student-practice-progress" role="progressbar" aria-label="تقدم التدريب" aria-valuemin="0" aria-valuemax="<?= e((string) $questionTotal) ?>" aria-valuenow="<?= e((string) $answeredCount) ?>">
                <span id="practice-progress-bar" style="width: <?= e((string) round($answeredCount / $questionTotal * 100)) ?>%"></span>
            </div>
            <div class="student-question-map" aria-label="الانتقال إلى السؤال">
                <span>انتقل إلى سؤال</span>
                <?php foreach ($questions as $index => $question): ?>
                    <?php $savedAnswer = $answers[(int) $question['id']] ?? null; ?>
                    <a class="<?= $savedAnswer ? 'is-answered' : '' ?>" href="#question-<?= e((string) $question['id']) ?>" aria-label="السؤال <?= e((string) ($index + 1)) ?><?= $savedAnswer ? '، تمت الإجابة' : '' ?>"><?= e((string) ($index + 1)) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </header>

    <?php if (!$questions): ?>
        <div class="student-empty-card">
            <span class="student-empty-card__icon" aria-hidden="true">◎</span>
            <h2>التدريب قيد الإعداد</h2>
            <p>لا توجد أسئلة مفعّلة لهذا التدريب حالياً. اختر تدريباً آخر أو عد لاحقاً.</p>
            <a class="student-button student-button--solid" href="<?= e(url('/section/' . $practice['section_slug'])) ?>">العودة إلى التدريبات</a>
        </div>
    <?php else: ?>
        <div class="<?= $passage ? 'student-practice-layout student-practice-layout--passage' : 'student-practice-layout' ?>">
            <?php if ($passage): ?>
                <aside class="student-passage" dir="ltr" aria-label="المقطع المرتبط بالأسئلة">
                    <div class="student-passage__eyebrow"><span aria-hidden="true">▤</span> مادة الأسئلة</div>
                    <?php if (!empty($passage['passage_title'])): ?><h2><?= e($passage['passage_title']) ?></h2><?php endif; ?>
                    <?php if (!empty($passage['audio_path'])): ?>
                        <div class="student-passage__audio">
                            <span>استمع إلى المقطع</span>
                            <audio controls preload="none" aria-label="المقطع الصوتي المرتبط بالتدريب">
                                <?php $audioMimeType = $audioMimeTypes[strtolower(pathinfo($passage['audio_path'], PATHINFO_EXTENSION))] ?? ''; ?>
                                <source src="<?= e(asset($passage['audio_path'])) ?>"<?= $audioMimeType !== '' ? ' type="' . e($audioMimeType) . '"' : '' ?>>
                                متصفحك لا يدعم تشغيل الصوت.
                            </audio>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($passage['passage_body'])): ?>
                        <div class="student-passage__body"><?= e($passage['passage_body']) ?></div>
                    <?php endif; ?>
                    <p class="student-passage__note">يمكنك الرجوع إلى هذا المقطع أثناء الإجابة عن الأسئلة.</p>
                </aside>
            <?php endif; ?>

            <div class="student-question-list">
                <?php foreach ($questions as $index => $question): ?>
                    <?php
                    $savedAnswer = $answers[(int) $question['id']] ?? null;
                    $questionIndex = $index;
                    include ROOT_PATH . '/app/views/partials/question-card.php';
                    ?>
                <?php endforeach; ?>
                <p id="practice-error" class="student-practice-error hidden" role="alert"></p>
                <div class="student-practice-finish">
                    <p>يمكنك إنهاء التدريب بعد الإجابة، وستُحفظ نتيجتك ضمن تقرير الأداء.</p>
                    <button id="finish-practice" type="button" <?= $requiresAllAnswers && $answeredCount < $questionTotal ? 'disabled' : '' ?>>
                        <?= $requiresAllAnswers && $answeredCount < $questionTotal ? 'أجب عن جميع الأسئلة لإكمال الاختبار' : 'إنهاء التدريب وعرض النتيجة' ?>
                        <span aria-hidden="true">←</span>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php if ($questions): ?>
    <?php include ROOT_PATH . '/app/views/partials/result-modal.php'; ?>
    <script src="<?= e(asset('js/timer.js')) ?>" defer></script>
    <script src="<?= e(asset('js/practice.js')) ?>" defer></script>
<?php endif; ?>
