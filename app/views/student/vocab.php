<?php
$itemCount = count($items);
$totalCount = (int) ($summary['total_count'] ?? $itemCount);
$knownCount = (int) ($summary['known_count'] ?? count(array_filter($items, static fn (array $item): bool => ($item['progress_status'] ?? '') === 'known')));
$reviewCount = (int) ($summary['review_count'] ?? count(array_filter($items, static fn (array $item): bool => ($item['progress_status'] ?? '') === 'review')));
$newCount = (int) ($summary['new_count'] ?? ($itemCount - $knownCount - $reviewCount));
$pageNewCount = count(array_filter($items, static fn (array $item): bool => empty($item['progress_status'])));
$pageReviewCount = count(array_filter($items, static fn (array $item): bool => ($item['progress_status'] ?? '') === 'review'));
$pageKnownCount = count(array_filter($items, static fn (array $item): bool => ($item['progress_status'] ?? '') === 'known'));
$page = (int) ($page ?? 1);
$pageCount = max(1, (int) ($pageCount ?? 1));
$pageSize = max(1, (int) ($pageSize ?? 48));
$firstItem = $totalCount > 0 ? (($page - 1) * $pageSize + 1) : 0;
$lastItem = min($totalCount, ($page - 1) * $pageSize + $itemCount);
$pageWindowStart = max(1, $page - 2);
$pageWindowEnd = min($pageCount, $page + 2);
?>
<section class="student-page student-vocab space-y-6">
    <nav class="student-breadcrumbs" aria-label="مسار التنقل">
        <a href="<?= e(url('/')) ?>">الرئيسية</a><span aria-hidden="true">/</span><span>بنك المفردات</span>
    </nav>

    <header class="student-page-hero student-page-hero--vocab">
        <div class="student-page-hero__copy">
            <span class="student-page-kicker">كلمة جديدة، خطوة أقرب</span>
            <h1>بنك مفردات STEP</h1>
            <p>اختبر ذاكرتك، اكشف المعنى بعد المحاولة، وسجّل الكلمات التي تحتاج إلى مراجعتها.</p>
        </div>
        <div class="student-vocab-total"><strong><?= e((string) $totalCount) ?></strong><span>كلمة في بنك المفردات</span></div>
    </header>

    <section class="student-vocab-summary" aria-label="ملخص المفردات">
        <div><span class="student-vocab-summary__dot is-new"></span><span>جديدة</span><strong data-vocab-count="new"><?= e((string) $newCount) ?></strong></div>
        <div><span class="student-vocab-summary__dot is-review"></span><span>للمراجعة</span><strong data-vocab-count="review"><?= e((string) $reviewCount) ?></strong></div>
        <div><span class="student-vocab-summary__dot is-known"></span><span>أعرفها</span><strong data-vocab-count="known"><?= e((string) $knownCount) ?></strong></div>
    </section>

    <?php if (!$items): ?>
        <div class="student-empty-card">
            <span class="student-empty-card__icon" aria-hidden="true">Aa</span>
            <h2>بنك الكلمات قيد الإعداد</h2>
            <p>ستظهر المفردات هنا عند إضافتها إلى المنصة. يمكنك البدء بتدريبات الأقسام حتى ذلك الحين.</p>
            <a class="student-button student-button--solid" href="<?= e(url('/')) ?>">استكشاف التدريبات</a>
        </div>
    <?php else: ?>
        <section class="student-vocab-tools" aria-label="البحث والتصفية">
            <label class="student-vocab-search">
                <span aria-hidden="true">⌕</span>
                <input type="search" data-vocab-search aria-label="ابحث في مفردات هذه الصفحة" placeholder="ابحث عن كلمة أو معنى..." autocomplete="off">
            </label>
            <div class="student-vocab-filters" role="group" aria-label="تصفية الكلمات">
                <button type="button" data-vocab-filter="all" aria-pressed="true">الكل <span><?= e((string) $itemCount) ?></span></button>
                <button type="button" data-vocab-filter="review" aria-pressed="false">للمراجعة <span><?= e((string) $pageReviewCount) ?></span></button>
                <button type="button" data-vocab-filter="new" aria-pressed="false">جديدة <span><?= e((string) $pageNewCount) ?></span></button>
                <button type="button" data-vocab-filter="known" aria-pressed="false">أعرفها <span><?= e((string) $pageKnownCount) ?></span></button>
            </div>
        </section>

        <p class="student-vocab-result-count" aria-live="polite"><span data-vocab-visible-count><?= e((string) $itemCount) ?></span> كلمة معروضة · <?= e((string) $firstItem) ?>–<?= e((string) $lastItem) ?> من <?= e((string) $totalCount) ?> (البحث في هذه الصفحة)</p>
        <div id="vocab-app" class="student-vocab-grid" data-csrf-token="<?= e($csrfToken) ?>" data-save-url="<?= e(url('/api/vocab-progress')) ?>">
            <?php foreach ($items as $index => $item): ?>
                <?php
                $status = $item['progress_status'] ?? '';
                $searchText = implode(' ', array_filter([
                    $item['word'],
                    $item['meaning_ar'],
                    $item['synonym'] ?? '',
                    $item['antonym'] ?? '',
                    $item['example'] ?? '',
                ]));
                $statusLabel = $status === 'known' ? 'أعرفها' : ($status === 'review' ? 'للمراجعة' : 'جديدة');
                ?>
                <article class="student-vocab-card" data-vocab-id="<?= e((string) $item['id']) ?>" data-vocab-card data-flashcard data-status="<?= e($status !== '' ? $status : 'new') ?>" data-search="<?= e($searchText) ?>">
                    <div class="student-vocab-card__top">
                        <span class="student-vocab-card__status" data-vocab-status><?= e($statusLabel) ?></span>
                        <span class="student-vocab-card__index" aria-hidden="true"><?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                    </div>
                    <h2 dir="ltr"><?= e($item['word']) ?></h2>
                    <?php if (!empty($item['synonym'])): ?><p class="student-vocab-card__synonym" dir="ltr">Similar: <?= e($item['synonym']) ?></p><?php endif; ?>
                    <button type="button" class="student-vocab-card__reveal" data-flashcard-toggle aria-expanded="false">
                        <span data-flashcard-label>إظهار المعنى</span><span aria-hidden="true">⌄</span>
                    </button>
                    <div class="student-vocab-card__answer" data-flashcard-answer hidden>
                        <p class="student-vocab-card__meaning"><?= e($item['meaning_ar']) ?></p>
                        <?php if (!empty($item['antonym'])): ?><p class="student-vocab-card__detail" dir="ltr"><b>Opposite:</b> <?= e($item['antonym']) ?></p><?php endif; ?>
                        <?php if (!empty($item['example'])): ?><blockquote class="student-vocab-card__example" dir="ltr"><?= e($item['example']) ?></blockquote><?php endif; ?>
                    </div>
                    <div class="student-vocab-card__actions">
                        <button type="button" class="vocab-progress <?= $status === 'review' ? 'is-selected' : '' ?>" data-status="review">أحتاج مراجعتها</button>
                        <button type="button" class="vocab-progress <?= $status === 'known' ? 'is-selected' : '' ?>" data-status="known">أعرفها ✓</button>
                    </div>
                    <p class="vocab-error student-vocab-card__error hidden" role="alert"></p>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="student-vocab-no-results" data-vocab-empty hidden>
            <h2>لا توجد كلمات مطابقة</h2>
            <p>جرّب كلمة أخرى أو غيّر خيارات التصفية.</p>
        </div>
        <?php if ($pageCount > 1): ?>
            <nav class="student-pagination" aria-label="صفحات بنك المفردات">
                <?php if ($page > 1): ?><a href="<?= e(url('/vocab?page=' . ($page - 1))) ?>" aria-label="الصفحة السابقة">السابق</a><?php endif; ?>
                <?php for ($pageNumber = $pageWindowStart; $pageNumber <= $pageWindowEnd; $pageNumber++): ?>
                    <a href="<?= e(url('/vocab?page=' . $pageNumber)) ?>" <?= $pageNumber === $page ? 'aria-current="page"' : '' ?>><?= e((string) $pageNumber) ?></a>
                <?php endfor; ?>
                <?php if ($page < $pageCount): ?><a href="<?= e(url('/vocab?page=' . ($page + 1))) ?>" aria-label="الصفحة التالية">التالي</a><?php endif; ?>
            </nav>
        <?php endif; ?>
        <script src="<?= e(asset('js/flashcards.js')) ?>" defer></script>
        <script src="<?= e(asset('js/vocab.js')) ?>" defer></script>
    <?php endif; ?>
</section>
