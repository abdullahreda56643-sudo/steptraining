<?php if (($pagination['pages'] ?? 1) > 1): ?>
    <?php
        $paginationPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/admin';
        $appPrefix = rtrim(parse_url(APP_URL, PHP_URL_PATH) ?: '', '/');
        if ($appPrefix !== '' && strpos($paginationPath, $appPrefix . '/') === 0) {
            $paginationPath = substr($paginationPath, strlen($appPrefix));
        }
        parse_str((string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY) ?? ''), $paginationQuery);
    ?>
    <nav class="mt-2 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-border bg-white px-4 py-3 shadow-sm" aria-label="صفحات النتائج">
        <span class="text-sm text-muted">صفحة <?= e((string) $pagination['page']) ?> من <?= e((string) $pagination['pages']) ?> — <?= e((string) $pagination['total']) ?> نتيجة</span>
        <div class="flex flex-wrap gap-2">
            <?php foreach (array_unique([1, max(1, $pagination['page'] - 1), min($pagination['pages'], $pagination['page'] + 1), $pagination['pages']]) as $pageNumber): ?>
                <?php $paginationQuery['page'] = $pageNumber; ?>
                <a href="<?= e(url($paginationPath . '?' . http_build_query($paginationQuery))) ?>"
                   class="rounded-lg border px-3 py-2 text-sm <?= $pageNumber === $pagination['page'] ? 'border-primary bg-primary text-white' : 'border-border text-ink hover:border-primary' ?>">
                    <?= e((string) $pageNumber) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>
<?php endif; ?>
