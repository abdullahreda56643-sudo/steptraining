<section class="student-page student-daily space-y-6">
    <?php $displayQuestionCount = $attempt ? (int) $attempt['total'] : (int) $questionCount; ?>
    <nav class="student-breadcrumbs" aria-label="مسار التنقل">
        <a href="<?= e(url('/')) ?>">الرئيسية</a><span aria-hidden="true">/</span><span>التحدي اليومي</span>
    </nav>
    <header class="student-daily-hero">
        <div class="student-daily-hero__copy">
            <span class="student-page-kicker">عادة صغيرة، أثر كبير</span>
            <h1>تحدّي اليوم</h1>
            <p><?= e($date) ?> <span aria-hidden="true">·</span> <?= e((string) $displayQuestionCount) ?> أسئلة متنوعة</p>
        </div>
        <div class="student-daily-emblem" aria-hidden="true"><span>✦</span><i></i><i></i></div>
        <div class="student-daily-hero__streak"><span>سلسلتك الحالية</span><strong>🔥 <?= e((string) $streak['current_streak']) ?></strong><small>يوماً من الاستمرارية</small></div>
    </header>

    <?php if ($completed): ?>
        <section class="student-daily-complete">
            <div class="student-daily-complete__check" aria-hidden="true">✓</div>
            <span class="student-page-kicker">تم تسجيل إنجاز اليوم</span>
            <h2>أحسنت، أنهيت تحديك!</h2>
            <?php $total = (int) $attempt['total']; $correct = (int) $attempt['correct_count']; $accuracy = $total > 0 ? (int) round($correct / $total * 100) : 0; ?>
            <div class="student-daily-result">
                <div><strong><?= e((string) $correct) ?><small> / <?= e((string) $total) ?></small></strong><span>إجابات صحيحة</span></div>
                <div><strong><?= e((string) $accuracy) ?>٪</strong><span>دقة اليوم</span></div>
                <div><strong>🔥 <?= e((string) $streak['current_streak']) ?></strong><span>سلسلة الالتزام</span></div>
            </div>
            <p>عد غداً لتحدٍ جديد، أو استغل حماسك لمراجعة إجاباتك ومهاراتك.</p>
            <div class="student-daily-actions">
                <a class="student-button student-button--solid" href="<?= e(url('/analysis')) ?>">عرض تحليل أدائي</a>
                <a class="student-button student-button--outline" href="<?= e(url('/')) ?>">العودة للرئيسية</a>
            </div>
        </section>
    <?php elseif ($attemptId !== null): ?>
        <section class="student-daily-start">
            <div class="student-daily-start__details">
                <span class="student-daily-status"><i></i><?= $hasAnswers ? 'محاولة محفوظة' : 'جاهز للبدء' ?></span>
                <h2><?= $hasAnswers ? 'تابع تحدي اليوم من حيث توقفت' : 'امنح نفسك دقائق من التدريب المركّز' ?></h2>
                <p><?= $hasAnswers
                    ? 'إجاباتك السابقة محفوظة. أكمل الأسئلة المتبقية ثم أنهِ التحدي لتسجيل إنجاز اليوم.'
                    : 'أجب عن أسئلة اليوم لتراجع مهارات متنوعة وتحافظ على وتيرة تقدمك.' ?></p>
                <a class="student-button student-button--solid" href="<?= e(url('/practice/attempt/' . $attemptId)) ?>">
                    <?= $hasAnswers ? 'متابعة التحدي' : 'ابدأ تحدي اليوم' ?> <span aria-hidden="true">←</span>
                </a>
                <small>يمكنك مغادرة الصفحة والعودة لاحقاً؛ ستبقى إجاباتك محفوظة.</small>
            </div>
            <div class="student-daily-start__visual" aria-hidden="true"><span>STEP</span><strong><?= e((string) $displayQuestionCount) ?></strong><small>سؤال اليوم</small><i>✦</i></div>
        </section>
    <?php else: ?>
        <section class="student-empty-card student-daily-empty">
            <span class="student-empty-card__icon" aria-hidden="true">✦</span>
            <h2>تحدي اليوم قيد الإعداد</h2>
            <p>لا توجد أسئلة مفعّلة للتحدي حالياً. لا تدع يومك يمر بلا تدريب؛ اختر أحد المسارات المتاحة.</p>
            <a class="student-button student-button--solid" href="<?= e(url('/')) ?>">استكشاف التدريبات</a>
        </section>
    <?php endif; ?>

    <nav class="student-quick-links" aria-label="متابعة التدريب">
        <a href="<?= e(url('/')) ?>"><span class="student-quick-links__icon">⌂</span><span><b>مسارات التدريب</b><small>تدرب حسب القسم</small></span><i aria-hidden="true">←</i></a>
        <a href="<?= e(url('/mistakes')) ?>"><span class="student-quick-links__icon">↻</span><span><b>مراجعة الأخطاء</b><small>ثبّت ما تعلمته</small></span><i aria-hidden="true">←</i></a>
        <a href="<?= e(url('/vocab')) ?>"><span class="student-quick-links__icon">Aa</span><span><b>المفردات</b><small>مراجعة سريعة للكلمات</small></span><i aria-hidden="true">←</i></a>
        <a href="<?= e(url('/analysis')) ?>"><span class="student-quick-links__icon">↗</span><span><b>تحليل الأداء</b><small>تابع نقاط قوتك</small></span><i aria-hidden="true">←</i></a>
    </nav>
</section>
