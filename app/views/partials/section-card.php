<?php
$statusLabels = [
    'not_started' => 'لم يبدأ بعد',
    'in_progress' => 'قيد التقدم',
    'completed' => 'مكتمل',
];
$status = $practice['student_status'] ?? 'not_started';
$progress = max(0, min(100, (int) ($practice['progress_percent'] ?? 0)));
$score = (int) ($practice['best_score'] ?? 0);
$practiceTitle = trim((string) ($practice['title_ar'] ?? ''));
?>
<article class="student-practice-card">
    <div class="student-practice-card__top">
        <span class="student-practice-card__type"><?= $status === 'completed' ? 'إنجاز محفوظ' : 'تدريب تفاعلي' ?></span>
        <span class="student-status student-status--<?= e($status) ?>"><?= e($statusLabels[$status] ?? $statusLabels['not_started']) ?></span>
    </div>
    <h3><?= e($practiceTitle !== '' ? $practiceTitle : $practice['title_en']) ?></h3>
    <p class="student-practice-card__english" dir="ltr"><?= e($practice['title_en']) ?></p>
    <div class="student-practice-card__score">
        <span>أفضل درجة متوقعة</span>
        <strong><?= $status === 'completed' ? e((string) $score) . '<small> / ١٠٠</small>' : '—' ?></strong>
    </div>
    <div class="student-practice-card__progress">
        <div class="student-practice-card__progress-label"><span>تقدمك في التدريب</span><strong><?= e((string) $progress) ?>٪</strong></div>
        <div role="progressbar" aria-label="تقدم <?= e($practiceTitle !== '' ? $practiceTitle : $practice['title_en']) ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= e((string) $progress) ?>">
            <span style="width: <?= e((string) $progress) ?>%"></span>
        </div>
    </div>
    <a class="student-practice-card__action" href="<?= e(url('/practice/' . $practice['id'])) ?>">
        <?= $status === 'in_progress' ? 'تابع من حيث توقفت' : ($status === 'completed' ? 'تدرّب مرة أخرى' : 'ابدأ هذا التدريب') ?>
        <span aria-hidden="true">←</span>
    </a>
</article>
