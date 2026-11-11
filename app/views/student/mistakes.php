<section class="student-page student-mistakes space-y-6">
    <nav class="student-breadcrumbs" aria-label="مسار التنقل">
        <a href="<?= e(url('/')) ?>">الرئيسية</a><span aria-hidden="true">/</span><span>مراجعة الأخطاء</span>
    </nav>
    <header class="student-page-hero student-page-hero--mistakes">
        <div class="student-page-hero__copy">
            <span class="student-page-kicker">كل خطأ فرصة للتقدم</span>
            <h1>مراجعة إجاباتك</h1>
            <p>راجع الأسئلة التي تحتاج إلى تثبيت، وافهم الإجابة الصحيحة قبل أن تعود إليها.</p>
        </div>
        <span class="student-mistakes-count"><strong><?= e((string) $mistakesCount) ?></strong><small>سؤال للمراجعة</small></span>
    </header>

    <section class="student-empty-card student-mistakes-empty">
        <span class="student-empty-card__icon" aria-hidden="true">✓</span>
        <span class="student-page-kicker">أداء رائع</span>
        <h2>لا توجد أخطاء حديثة للمراجعة</h2>
        <p>لا توجد أسئلة كانت آخر إجابة لك فيها خاطئة. هذا يعني أنك أتقنت ما راجعته حتى الآن؛ واصل التدريب، وستظهر هنا أي أسئلة جديدة تحتاج إلى تثبيت.</p>
        <div class="student-mistakes-empty__actions">
            <a class="student-button student-button--solid" href="<?= e(url('/daily')) ?>">ابدأ التحدي اليومي</a>
            <a class="student-button student-button--outline" href="<?= e(url('/analysis')) ?>">استكشف تحليلك</a>
        </div>
    </section>

    <nav class="student-quick-links" aria-label="خطواتك التالية">
        <a href="<?= e(url('/')) ?>"><span class="student-quick-links__icon">⌂</span><span><b>مسارات التدريب</b><small>واصل التدريب على مهاراتك</small></span><i aria-hidden="true">←</i></a>
        <a href="<?= e(url('/analysis')) ?>"><span class="student-quick-links__icon">↗</span><span><b>تحليل الأداء</b><small>اعرف المهارات التي تحتاج دعماً</small></span><i aria-hidden="true">←</i></a>
        <a href="<?= e(url('/placement')) ?>"><span class="student-quick-links__icon">◎</span><span><b>تحديد المستوى</b><small>قِس تقدمك من جديد</small></span><i aria-hidden="true">←</i></a>
        <a href="<?= e(url('/vocab')) ?>"><span class="student-quick-links__icon">Aa</span><span><b>بنك المفردات</b><small>استمر في توسيع حصيلتك</small></span><i aria-hidden="true">←</i></a>
    </nav>
</section>
