<?php
$weakSkills = array_values(array_filter($skills, static fn (array $skill): bool => !empty($skill['is_weak'])));
$sampleAnswers = array_sum(array_column($skills, 'answer_count'));
$correctAnswers = array_sum(array_column($skills, 'correct_count'));
$recentAccuracy = $sampleAnswers > 0 ? (int) round($correctAnswers / $sampleAnswers * 100) : null;
$score = $prediction ? max(0, min(100, (int) $prediction['score'])) : 0;
?>
<section class="student-page student-analysis space-y-6">
    <nav class="student-breadcrumbs" aria-label="مسار التنقل">
        <a href="<?= e(url('/')) ?>">الرئيسية</a><span aria-hidden="true">/</span><span>تحليل الأداء</span>
    </nav>

    <header class="student-page-hero student-page-hero--analysis">
        <div class="student-page-hero__copy">
            <span class="student-page-kicker">قرارات تدريب مبنية على أدائك</span>
            <h1>تحليل الأداء</h1>
            <p>رؤية واضحة لتقدمك، مهاراتك الأقوى، والخطوات التي تساعدك على التحسن.</p>
        </div>
        <a class="student-button student-button--solid" href="<?= e(url('/')) ?>">متابعة التدريب <span aria-hidden="true">←</span></a>
    </header>

    <section class="student-analysis-overview" aria-label="ملخص التحليل">
        <article class="student-analysis-score">
            <div class="student-analysis-score__copy">
                <span class="student-page-kicker">مؤشر الاستعداد</span>
                <h2>درجتك المتوقعة في STEP</h2>
                <?php if ($prediction): ?>
                    <p class="student-analysis-score__value"><?= e((string) $score) ?><small> / ١٠٠</small></p>
                    <p class="student-analysis-score__band"><?= e($prediction['band']) ?></p>
                    <p class="student-analysis-score__note">تبقّى <?= e((string) $prediction['gap']) ?> درجة للشريحة التالية.</p>
                <?php else: ?>
                    <p class="student-analysis-score__empty">—<small> / ١٠٠</small></p>
                    <p class="student-analysis-score__note">أكمل تدريباً واحداً على الأقل لاحتساب تقديرك.</p>
                <?php endif; ?>
            </div>
            <div class="student-analysis-ring" style="--score: <?= e((string) $score) ?>%" role="img" aria-label="<?= $prediction ? 'الدرجة المتوقعة ' . e((string) $score) . ' من ١٠٠' : 'لا توجد درجة متوقعة بعد' ?>">
                <div><strong><?= $prediction ? e((string) $score) : '—' ?></strong><span>من ١٠٠</span></div>
            </div>
        </article>
        <article class="student-analysis-stat">
            <span class="student-analysis-stat__icon">↗</span><p>دقة المهارات الأخيرة</p>
            <strong><?= $recentAccuracy === null ? '—' : e((string) $recentAccuracy) . '٪' ?></strong>
            <small><?= e((string) $sampleAnswers) ?> إجابة ضمن آخر ٥٠ إجابة لكل مهارة</small>
        </article>
        <article class="student-analysis-stat">
            <span class="student-analysis-stat__icon student-analysis-stat__icon--amber">◎</span><p>مهارات تحتاج تركيزاً</p>
            <strong><?= e((string) count($weakSkills)) ?></strong>
            <small>أقل من حد الإتقان <?= e((string) $weakSkillThreshold) ?>٪</small>
        </article>
    </section>

    <?php if ($prediction): ?>
        <section class="student-analysis-guidance">
            <span class="student-analysis-guidance__icon" aria-hidden="true">✦</span>
            <div>
                <h2>خطوتك نحو الدرجة التالية</h2>
                <p>ركّز على جلسات قصيرة ومنتظمة، وابدأ بالمهارة الأضعف. تحتاج إلى <?= e((string) $prediction['gap']) ?> درجة للوصول إلى الشريحة التالية.</p>
            </div>
            <a href="<?= e(url('/daily')) ?>">ابدأ جلسة اليوم <span aria-hidden="true">←</span></a>
        </section>
    <?php endif; ?>

    <section class="student-analysis-skills" aria-labelledby="student-skills-title">
        <div class="student-content-heading">
            <div>
                <span class="student-page-kicker">آخر ٥٠ إجابة لكل مهارة</span>
                <h2 id="student-skills-title">مستواك حسب المهارة</h2>
                <p>يُحسب المؤشر من إجاباتك الأحدث؛ استمر بالتدريب لتحديث الصورة.</p>
            </div>
            <span class="student-analysis-threshold">حد التركيز: <?= e((string) $weakSkillThreshold) ?>٪</span>
        </div>

        <?php if (!$skills): ?>
            <div class="student-empty-card">
                <span class="student-empty-card__icon" aria-hidden="true">↗</span>
                <h3>تحليلك يبدأ مع أول إجابة</h3>
                <p>أكمل تدريباً واحداً لتظهر هنا دقة إجاباتك حسب المهارة، ونقاط القوة التي يمكنك البناء عليها.</p>
                <a class="student-button student-button--solid" href="<?= e(url('/')) ?>">اختر تدريباً</a>
            </div>
        <?php else: ?>
            <div class="student-skill-list">
                <?php foreach ($skills as $index => $skill): ?>
                    <?php $accuracy = max(0, min(100, (int) $skill['accuracy'])); ?>
                    <article class="student-skill-card <?= $skill['is_weak'] ? 'is-weak' : '' ?>">
                        <div class="student-skill-card__rank"><?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></div>
                        <div class="student-skill-card__main">
                            <div class="student-skill-card__heading">
                                <div><h3><?= e($skill['name_ar']) ?></h3><span dir="ltr"><?= e($skill['name_en']) ?></span></div>
                                <span class="student-skill-card__badge <?= $skill['is_weak'] ? 'is-weak' : 'is-strong' ?>"><?= $skill['is_weak'] ? 'تحتاج تركيزاً' : 'تقدم جيد' ?></span>
                            </div>
                            <div class="student-skill-card__meter" role="progressbar" aria-label="دقة <?= e($skill['name_ar']) ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= e((string) $accuracy) ?>"><span style="width: <?= e((string) $accuracy) ?>%"></span></div>
                            <div class="student-skill-card__footer"><span><?= e((string) $skill['answer_count']) ?> إجابة حديثة</span><strong><?= e((string) $accuracy) ?>٪</strong></div>
                        </div>
                        <div class="student-skill-card__actions">
                            <?php if (!empty($skill['section_slug'])): ?><a href="<?= e(url('/section/' . $skill['section_slug'])) ?>">تدريبات القسم</a><?php endif; ?>
                            <?php if ($skill['is_weak']): ?><a href="<?= e(url('/analysis/custom/' . $skill['id'])) ?>">تدريب مخصص <span aria-hidden="true">←</span></a><?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <nav class="student-quick-links" aria-label="خطواتك التالية">
        <a href="<?= e(url('/mistakes')) ?>"><span class="student-quick-links__icon">↻</span><span><b>مراجعة الأخطاء</b><small>تعلم من إجاباتك السابقة</small></span><i aria-hidden="true">←</i></a>
        <a href="<?= e(url('/placement')) ?>"><span class="student-quick-links__icon">◎</span><span><b>اختبار المستوى</b><small>قِس تقدمك من جديد</small></span><i aria-hidden="true">←</i></a>
        <a href="<?= e(url('/vocab')) ?>"><span class="student-quick-links__icon">Aa</span><span><b>بنك المفردات</b><small>ادعم مهاراتك اللغوية</small></span><i aria-hidden="true">←</i></a>
        <a href="<?= e(url('/daily')) ?>"><span class="student-quick-links__icon">✦</span><span><b>التحدي اليومي</b><small>حافظ على استمراريتك</small></span><i aria-hidden="true">←</i></a>
    </nav>
</section>
