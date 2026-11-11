<?php
$practiceCount = count($practices);
$completedCount = count(array_filter($practices, static fn (array $practice): bool => ($practice['student_status'] ?? '') === 'completed'));
$activeCount = count(array_filter($practices, static fn (array $practice): bool => ($practice['student_status'] ?? '') === 'in_progress'));
$sectionProgress = $practiceCount > 0 ? (int) round($completedCount / $practiceCount * 100) : 0;
?>
<section class="student-page space-y-6">
    <nav class="student-breadcrumbs" aria-label="مسار التنقل">
        <a href="<?= e(url('/')) ?>">الرئيسية</a><span aria-hidden="true">/</span><span><?= e($section['name_ar']) ?></span>
    </nav>

    <header class="student-page-hero student-page-hero--section">
        <div class="student-page-hero__copy">
            <span class="student-page-kicker">مسار تدريبي</span>
            <h1><?= e($section['name_ar']) ?></h1>
            <p><?= e($section['name_en']) ?> · تدريبات منظمة تساعدك على بناء المهارة بثقة.</p>
        </div>
        <div class="student-page-hero__stats">
            <div><strong><?= e((string) $practiceCount) ?></strong><span>تدريب متاح</span></div>
            <div><strong><?= e((string) $completedCount) ?></strong><span>تدريب مكتمل</span></div>
            <div><strong><?= e((string) $sectionProgress) ?>٪</strong><span>إنجاز المسار</span></div>
        </div>
    </header>

    <?php
    $tipTitle = 'استراتيجية هذا القسم';
    $tipText = $section['tip_text'] ?? '';
    include ROOT_PATH . '/app/views/partials/tip-card.php';
    ?>

    <section aria-labelledby="section-practices-title">
        <div class="student-content-heading">
            <div>
                <span class="student-page-kicker">تدرّب على مهارة واحدة في كل مرة</span>
                <h2 id="section-practices-title">التدريبات المتاحة</h2>
                <p><?= e((string) $activeCount) ?> تدريباً قيد التقدم · <?= e((string) $completedCount) ?> مكتملاً</p>
            </div>
            <a class="student-inline-link" href="<?= e(url('/analysis')) ?>">تحليل مهاراتي <span aria-hidden="true">←</span></a>
        </div>

        <?php if (!$practices): ?>
            <div class="student-empty-card">
                <span class="student-empty-card__icon" aria-hidden="true">◎</span>
                <h3>التدريبات قيد الإعداد</h3>
                <p>لا توجد تدريبات مفعّلة في هذا القسم حالياً. يمكنك استكشاف الأقسام الأخرى أو مراجعة مفردات STEP أثناء ذلك.</p>
                <div>
                    <a class="student-button student-button--solid" href="<?= e(url('/')) ?>">استكشاف الأقسام</a>
                    <a class="student-button student-button--outline" href="<?= e(url('/vocab')) ?>">مراجعة المفردات</a>
                </div>
            </div>
        <?php else: ?>
            <div class="student-practice-grid">
                <?php foreach ($practices as $practice): ?>
                    <?php include ROOT_PATH . '/app/views/partials/section-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</section>
