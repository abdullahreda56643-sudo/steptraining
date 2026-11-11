<section class="student-page student-placement space-y-6">
    <nav class="student-breadcrumbs" aria-label="مسار التنقل">
        <a href="<?= e(url('/')) ?>">الرئيسية</a><span aria-hidden="true">/</span><span>تحديد المستوى</span>
    </nav>
    <?php if ($error = flash('error')): ?>
        <p class="student-inline-alert" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <header class="student-page-hero student-page-hero--placement">
        <div class="student-page-hero__copy">
            <span class="student-page-kicker">ابدأ من مستواك الحقيقي</span>
            <h1>اختبار تحديد المستوى</h1>
            <p>اختبار قصير يرسم نقطة انطلاقك، ويقترح خطوات عملية تناسب أداءك الحالي.</p>
        </div>
        <div class="student-placement-hero-icon" aria-hidden="true"><span>◎</span><i></i><i></i></div>
    </header>

    <section class="student-placement-grid">
        <article class="student-placement-start">
            <div class="student-placement-start__top"><span>اختبار تشخيصي</span><span class="student-placement-start__availability"><?= e((string) $questionCount) ?> سؤال متاح</span></div>
            <h2><?= $openAttemptId !== null ? 'لديك اختبار غير مكتمل' : ($attempt ? 'هل أنت مستعد لقياس تقدمك؟' : 'اكتشف نقطة البداية المناسبة لك') ?></h2>
            <p><?= $openAttemptId !== null
                ? 'تابع من حيث توقفت، وستحافظ على إجاباتك السابقة.'
                : 'أجب عن الأسئلة بهدوء ودون تخمين متسرع. تظهر لك نتيجة مبدئية وخطة مقترحة بعد الإنهاء.' ?></p>
            <ul class="student-placement-facts">
                <li><span>✓</span> أسئلة متنوعة من أقسام STEP</li>
                <li><span>✓</span> نتيجة مبدئية واضحة بعد الاختبار</li>
                <li><span>✓</span> خطة تدريب مقترحة حسب المهارات</li>
            </ul>
            <?php if ($questionCount === 0 && $openAttemptId === null): ?>
                <p class="student-inline-alert student-inline-alert--soft" role="status">لا توجد أسئلة مفعّلة لهذا الاختبار حالياً. يمكنك البدء بتدريبات الأقسام ثم العودة لاحقاً.</p>
                <a class="student-button student-button--outline" href="<?= e(url('/')) ?>">تصفح التدريبات</a>
            <?php else: ?>
                <form method="post" action="<?= e(url('/placement')) ?>">
                    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                    <button class="student-button student-button--solid" type="submit">
                        <?= $openAttemptId !== null ? 'متابعة الاختبار' : ($attempt ? 'إعادة الاختبار' : 'ابدأ اختبار المستوى') ?>
                        <span aria-hidden="true">←</span>
                    </button>
                </form>
            <?php endif; ?>
        </article>

        <aside class="student-placement-result">
            <?php if ($attempt): ?>
                <span class="student-page-kicker">آخر نتيجة مسجلة</span>
                <h2>مستواك الحالي</h2>
                <span class="student-placement-level"><?= e($attempt['level']) ?></span>
                <div class="student-placement-result__score"><strong><?= e((string) $attempt['accuracy']) ?>٪</strong><span>دقة الإجابات</span></div>
                <p class="student-placement-result__total"><?= e((string) $attempt['correct_count']) ?> إجابة صحيحة من <?= e((string) $attempt['total']) ?></p>
                <div class="student-placement-plan">
                    <h3>خطة مقترحة للخطوة القادمة</h3>
                    <ol>
                        <?php foreach ($attempt['plan'] as $tip): ?>
                            <li><?= e($tip) ?></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                <a class="student-inline-link" href="<?= e(url('/analysis')) ?>">استكشف تحليل المهارات <span aria-hidden="true">←</span></a>
            <?php else: ?>
                <span class="student-page-kicker">كيف يساعدك الاختبار؟</span>
                <h2>صورة أوضح. خطة أذكى.</h2>
                <p>لا يوجد نجاح أو رسوب هنا؛ الهدف هو معرفة المهارات التي تتقنها وما يستحق اهتمامك أولاً.</p>
                <div class="student-placement-steps"><span>١</span><p><b>أجب عن الأسئلة</b><small>بناءً على معرفتك الحالية</small></p></div>
                <div class="student-placement-steps"><span>٢</span><p><b>راجع النتيجة</b><small>مستوى مبدئي ودقة أدائك</small></p></div>
                <div class="student-placement-steps"><span>٣</span><p><b>ابدأ خطتك</b><small>تدريب موجه نحو احتياجك</small></p></div>
            <?php endif; ?>
        </aside>
    </section>

    <?php if ($history): ?>
        <section class="student-history">
            <div class="student-content-heading">
                <div><span class="student-page-kicker">رحلتك عبر الوقت</span><h2>سجل اختبارات المستوى</h2><p>قارن نتائجك السابقة وتابع تطورك.</p></div>
                <span class="student-analysis-threshold"><?= e((string) count($history)) ?> محاولة</span>
            </div>
            <div class="student-history-table-wrap">
                <table class="student-history-table">
                    <thead><tr><th scope="col">التاريخ</th><th scope="col">النتيجة</th><th scope="col">الدقة</th><th scope="col">المستوى</th></tr></thead>
                    <tbody>
                    <?php foreach ($history as $row): ?>
                        <?php $result = PlacementService::level((int) $row['correct_count'], (int) $row['total']); ?>
                        <tr>
                            <td><?= e(date('d/m/Y', strtotime($row['finished_at']))) ?></td>
                            <td><?= e((string) $row['correct_count']) ?> / <?= e((string) $row['total']) ?></td>
                            <td><span class="student-history-accuracy"><?= e((string) $result['accuracy']) ?>٪</span></td>
                            <td><span class="student-placement-history-level"><?= e($result['level']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
</section>
