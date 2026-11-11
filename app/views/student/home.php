<?php
$maxActivity = 0;
$weeklyTotal = 0;
foreach ($weeklyActivity as $day) {
    $maxActivity = max($maxActivity, (int) $day['count']);
    $weeklyTotal += (int) $day['count'];
}
$firstName = trim(explode(' ', trim((string) ($user['name'] ?? 'طالب')))[0]);
$sectionCount = count($sections);
$completedSections = count(array_filter($sections, static fn (array $section): bool => (int) $section['progress'] >= 100));
?>

<section class="student-dashboard space-y-7">
    <header class="student-hero">
        <div class="student-hero__content">
            <span class="student-eyebrow"><span class="student-live-dot"></span>مساحتك للتقدم والإنجاز</span>
            <h1>أهلاً <?= e($firstName) ?>، <span>جاهز لخطوتك القادمة؟</span></h1>
            <p>كل جلسة تدريب تقرّبك من هدفك. واصل التقدم، وسنساعدك على معرفة أين تركز اليوم.</p>
            <div class="student-hero__actions">
                <a class="student-button student-button--gold" href="<?= e(url('/daily')) ?>">
                    ابدأ التحدي اليومي
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                </a>
                <a class="student-button student-button--glass" href="<?= e(url('/analysis')) ?>">استكشف تحليلك</a>
            </div>
        </div>
        <div class="student-hero__score" aria-label="الدرجة المتوقعة في STEP">
            <div class="student-score-ring" style="--score: <?= $predictedScore === null ? 0 : max(0, min(100, (int) $predictedScore)) ?>%">
                <div class="student-score-ring__inner">
                    <span><?= $predictedScore === null ? '—' : e((string) $predictedScore) ?></span>
                    <small>من ١٠٠</small>
                </div>
            </div>
            <p>درجتك المتوقعة</p>
            <a href="<?= e(url('/analysis')) ?>">عرض تقرير الأداء <span aria-hidden="true">←</span></a>
        </div>
        <span class="student-hero__orb student-hero__orb--one" aria-hidden="true"></span>
        <span class="student-hero__orb student-hero__orb--two" aria-hidden="true"></span>
    </header>

    <section class="student-metrics" aria-label="ملخص أدائك">
        <article class="student-metric">
            <span class="student-metric__icon student-metric__icon--violet" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M4 19V5m0 14h16M8 15l3-4 3 2 5-7"/></svg>
            </span>
            <div><p>دقة إجاباتك</p><strong><?= e((string) $accuracy) ?><small>٪</small></strong><span>من إجاباتك المكتملة</span></div>
            <div class="student-metric__mini-meter" aria-hidden="true"><span style="width: <?= e((string) $accuracy) ?>%"></span></div>
        </article>
        <article class="student-metric">
            <span class="student-metric__icon student-metric__icon--blue" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h4"/></svg>
            </span>
            <div><p>أسئلة أجبت عنها</p><strong><?= e((string) $answeredQuestions) ?></strong><span>سؤال تدريبي مكتمل</span></div>
        </article>
        <article class="student-metric">
            <span class="student-metric__icon student-metric__icon--green" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M5 19V9h4v10m3 0V5h4v14m3 0v-7h4v7M3 19h19"/></svg>
            </span>
            <div><p>تدريبات مكتملة</p><strong><?= e((string) $completedAttempts) ?></strong><span>إنجازاتك حتى الآن</span></div>
        </article>
        <article class="student-metric student-metric--streak">
            <span class="student-metric__icon student-metric__icon--amber" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 22c4.4 0 7-3 7-7.1 0-2.8-1.6-5.3-4.8-7.9.2 2.4-.5 3.5-1.6 4.1C12.8 7.3 10.1 4.5 7 2c.4 4.6-2 6.8-2 11.5C5 18.3 7.9 22 12 22Z"/><path d="M12 22c-1.8-1.2-2.3-3-1.6-4.6.4-1 1.1-1.8 2-2.6.3 1.4 1.2 2 2 2.5.2 1.9-.6 3.7-2.4 4.7Z"/></svg>
            </span>
            <div><p>سلسلة الالتزام</p><strong><?= e((string) $streak) ?><small> أيام</small></strong><span><?= $streak > 0 ? 'استمر، أنت على المسار!' : 'ابدأ سلسلة جديدة اليوم' ?></span></div>
        </article>
    </section>

    <section class="student-main-grid">
        <article class="student-panel student-activity">
            <div class="student-panel__heading">
                <div>
                    <span class="student-section-kicker">الاستمرارية تصنع الفرق</span>
                    <h2>نشاطك خلال الأسبوع</h2>
                    <p>التدريبات التي أكملتها خلال آخر ٧ أيام</p>
                </div>
                <span class="student-week-total"><strong><?= e((string) $weeklyTotal) ?></strong><small>إجمالي هذا الأسبوع</small></span>
            </div>
            <div class="student-chart" role="img" aria-label="مخطط التدريبات المكتملة خلال الأيام السبعة الماضية">
                <?php foreach ($weeklyActivity as $day): ?>
                    <?php
                    $count = (int) $day['count'];
                    $height = $maxActivity > 0 ? max(10, (int) round($count / $maxActivity * 100)) : 10;
                    ?>
                    <div class="student-chart__day" title="<?= e($day['label'] . ': ' . (string) $count . ' تدريبات') ?>">
                        <span class="student-chart__value"><?= e((string) $count) ?></span>
                        <div class="student-chart__track"><span class="<?= $count > 0 && $count === $maxActivity ? 'is-peak' : '' ?>" style="height: <?= e((string) $height) ?>%"></span></div>
                        <small><?= e(mb_substr($day['label'], 0, 3, 'UTF-8')) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="student-chart__footer"><span><i></i> تدريبات مكتملة</span><span>خطوة صغيرة كل يوم تصنع نتيجة كبيرة</span></div>
        </article>

        <aside class="student-panel student-focus">
            <div class="student-focus__top">
                <span class="student-focus__icon" aria-hidden="true">✦</span>
                <span class="student-focus__label">خطوتك التالية</span>
            </div>
            <h2><?= $mistakesCount > 0 ? 'حوّل أخطاءك إلى نقاط قوة' : 'استكشف مستواك بذكاء' ?></h2>
            <p><?= $mistakesCount > 0
                ? 'لديك ' . (string) $mistakesCount . ' سؤالاً يحتاج مراجعة. جلسة قصيرة الآن تثبّت ما تعلمته.'
                : 'ابدأ اختبار تحديد المستوى لتحصل على نقطة انطلاق وخطة تدريب تناسبك.' ?></p>
            <a href="<?= e(url($mistakesCount > 0 ? '/mistakes' : '/placement')) ?>">
                <?= $mistakesCount > 0 ? 'راجع إجاباتي' : 'ابدأ تحديد المستوى' ?>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
            </a>
            <div class="student-focus__meta">
                <span><b><?= e((string) $mistakesCount) ?></b> سؤال للمراجعة</span>
                <a href="<?= e(url('/vocab')) ?>">مراجعة المفردات <span aria-hidden="true">←</span></a>
            </div>
        </aside>
    </section>

    <section class="student-sections">
        <div class="student-section-heading">
            <div>
                <span class="student-section-kicker">تعلّم حسب احتياجك</span>
                <h2>مسارات التدريب</h2>
                <p>اختر مهارة وابدأ ببناء تقدمك خطوة بخطوة</p>
            </div>
            <?php if ($sectionCount > 0): ?>
                <span class="student-section-count"><strong><?= e((string) $completedSections) ?></strong> من <?= e((string) $sectionCount) ?> مسارات مكتملة</span>
            <?php endif; ?>
        </div>

        <?php if ($sections): ?>
            <div class="student-section-grid">
                <?php foreach ($sections as $index => $section): ?>
                    <?php $progress = max(0, min(100, (int) $section['progress'])); ?>
                    <article class="student-path student-path--<?= e((string) ($index % 4)) ?>">
                        <div class="student-path__top">
                            <span class="student-path__number"><?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                            <span class="student-path__progress"><?= e((string) $progress) ?><small>٪</small></span>
                        </div>
                        <h3><?= e($section['name']) ?></h3>
                        <p><?= $progress === 100 ? 'أحسنت! أكملت تدريبات هذا المسار' : ($progress > 0 ? 'واصل من حيث توقفت ووسّع مهاراتك' : 'ابدأ رحلتك في هذا المسار') ?></p>
                        <div class="student-path__meter" role="progressbar" aria-label="التقدم في <?= e($section['name']) ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= e((string) $progress) ?>"><span style="width: <?= e((string) $progress) ?>%"></span></div>
                        <a href="<?= e(url('/section/' . $section['slug'])) ?>">
                            <?= $progress === 100 ? 'تدرب مجدداً' : ($progress > 0 ? 'تابع التدريب' : 'استكشف المسار') ?>
                            <span aria-hidden="true">←</span>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="student-empty-state">لا توجد مسارات تدريب متاحة حالياً. يرجى العودة لاحقاً.</div>
        <?php endif; ?>
    </section>

    <nav class="student-quick-links" aria-label="روابط سريعة">
        <a href="<?= e(url('/daily')) ?>"><span class="student-quick-links__icon">✦</span><span><b>التحدي اليومي</b><small><?= e((string) $dailyChallenge) ?> أسئلة متنوعة يومياً</small></span><i aria-hidden="true">←</i></a>
        <a href="<?= e(url('/placement')) ?>"><span class="student-quick-links__icon">◎</span><span><b>تحديد المستوى</b><small>اكتشف نقطة انطلاقك</small></span><i aria-hidden="true">←</i></a>
        <a href="<?= e(url('/analysis')) ?>"><span class="student-quick-links__icon">↗</span><span><b>تحليل الأداء</b><small>تعرف على نقاط قوتك</small></span><i aria-hidden="true">←</i></a>
        <a href="<?= e(url('/vocab')) ?>"><span class="student-quick-links__icon">Aa</span><span><b>مفردات STEP</b><small>وسّع حصيلتك اللغوية</small></span><i aria-hidden="true">←</i></a>
    </nav>
</section>
