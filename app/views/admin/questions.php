<?php
/* ── Data prep (view-only) ───────────────────────────────────── */
$resultCount = count($items);
$activeCount = 0;
foreach ($items as $row) {
    if ($row['is_active']) { $activeCount++; }
}
$stoppedCount = $resultCount - $activeCount;

$hasFilters = ($search !== '')
    || !empty($filters['section_id'])
    || !empty($filters['practice_id'])
    || !empty($filters['skill_id']);
?>

<style>
    .qs { --navy: #262262; --navy-deep: #1C194B; --orange: #FFA51A; --ink: #211F35; --muted: #706E83; --line: #E8E7F0; --tint: #F1F0FA; --ic-accent: #FFA51A; display: grid; gap: 22px; color: var(--ink); }
    .qs *, .qs *::before, .qs *::after { box-sizing: border-box; }

    /* Icons */
    .qs-ic { display: block; width: 20px; height: 20px; flex: none; }
    .qs-ic--sm { width: 16px; height: 16px; }
    .qs-ic--lg { width: 24px; height: 24px; }

    /* Surfaces */
    .qs-card { background: #fff; border: 1px solid var(--line); border-radius: 18px; box-shadow: 0 1px 2px rgba(38,34,98,.04); }
    .qs-chip { display: grid; width: 44px; height: 44px; flex: none; place-items: center; border-radius: 13px; background: var(--tint); color: var(--navy); }

    /* Header */
    .qs-head { display: flex; align-items: center; gap: 14px; }
    .qs-head h2 { margin: 0; font-size: 24px; font-weight: 700; line-height: 1.35; }
    .qs-head p { max-width: 62ch; margin: 3px 0 0; color: var(--muted); font-size: 14px; line-height: 1.8; }

    /* Filters */
    .qs-filters { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; padding: 18px; }
    .qs-search { position: relative; grid-column: 1 / -1; }
    .qs-search .qs-ic { position: absolute; top: 50%; right: 14px; color: #9A98AD; pointer-events: none; transform: translateY(-50%); }
    .qs-search input { padding-right: 44px; }
    .qs-filter-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 10px; grid-column: 1 / -1; }

    /* Controls (used by filters and, scoped, by the shared question fields partial) */
    .qs-input, .qs-form input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([type="file"]), .qs-form select, .qs-form textarea {
        width: 100%; padding: 11px 14px; border: 1px solid var(--line); border-radius: 12px; outline: none; background-color: #FCFCFE; color: var(--ink); font: inherit; font-size: 14px;
        transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
    }
    .qs-input { height: 46px; padding-block: 0; }
    .qs-form textarea { line-height: 1.8; }
    .qs-input::placeholder, .qs-form input::placeholder, .qs-form textarea::placeholder { color: #A5A3B2; }
    .qs-input:hover, .qs-form input:hover, .qs-form select:hover, .qs-form textarea:hover { border-color: #C9C7D9; }
    .qs-input:focus, .qs-form input:focus, .qs-form select:focus, .qs-form textarea:focus { border-color: var(--navy); background-color: #fff; box-shadow: 0 0 0 4px rgba(38,34,98,.08); }
    select.qs-input, .qs-form select { padding-left: 38px; cursor: pointer; appearance: none; -webkit-appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23706E83' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: left 13px center; }
    .qs-form label { color: #37354B; font-size: 13px; font-weight: 600; }
    .qs-form input[type="checkbox"], .qs-form input[type="radio"] { accent-color: var(--navy); }

    /* Buttons */
    .qs-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 18px; border: 1px solid transparent; border-radius: 12px; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; transition: background .15s ease, border-color .15s ease; }
    .qs-btn--sm { padding: 9px 14px; font-size: 13px; }
    .qs-btn--primary { background: var(--navy); color: #fff; }
    .qs-btn--primary:hover { background: var(--navy-deep); }
    .qs-btn--line { border-color: var(--line); background: #fff; color: var(--navy); }
    .qs-btn--line:hover { border-color: #C9C7D9; background: #F7F7FB; }
    .qs-btn--danger { --ic-accent: #C2414B; border-color: #F2C6CA; background: #fff; color: #B03541; }
    .qs-btn--danger:hover { background: #FFF4F4; }

    /* Pills */
    .qs-pill { display: inline-flex; max-width: 100%; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: var(--tint); color: var(--navy); font-size: 12px; font-weight: 600; white-space: nowrap; }
    .qs-pill > span { overflow: hidden; text-overflow: ellipsis; }
    .qs-pill--ok, .qs-pill--off { gap: 6px; }
    .qs-pill--ok::before, .qs-pill--off::before { width: 6px; height: 6px; flex: none; border-radius: 50%; background: currentColor; content: ""; }
    .qs-pill--ok { background: #ECF8F2; color: #1F7A52; }
    .qs-pill--off { background: #F3F2F8; color: var(--muted); }

    /* Toolbar */
    .qs-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: -6px; }
    .qs-bar p { margin: 0; color: var(--muted); font-size: 13px; }
    .qs-bar-stats { display: flex; flex-wrap: wrap; gap: 8px; }

    /* Collapsible blocks */
    .qs-fold > summary { display: flex; align-items: center; gap: 14px; list-style: none; cursor: pointer; }
    .qs-fold > summary::-webkit-details-marker { display: none; }
    .qs-caret { margin-inline-start: 4px; color: var(--muted); transition: transform .18s ease; }
    .qs-fold[open] > summary .qs-caret { transform: rotate(180deg); }

    .qs-add { padding: 18px 22px; }
    .qs-add > summary { font-size: 16px; font-weight: 700; }
    .qs-add > summary .qs-caret { margin-inline-start: auto; }
    .qs-body { margin-top: 18px; padding-top: 22px; border-top: 1px solid var(--line); }
    .qs-form > * + * { margin-top: 16px; }
    .qs-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 10px; padding-top: 4px; }

    /* Rows */
    .qs-list { display: grid; gap: 10px; }
    .qs-item { transition: border-color .15s ease; }
    .qs-item:hover, .qs-item[open] { border-color: #D8D6EF; }
    .qs-item > summary { align-items: flex-start; padding: 14px 18px; }
    .qs-item-main { min-width: 0; flex: 1; }
    .qs-item-text { display: -webkit-box; margin: 0; overflow: hidden; font-size: 15px; font-weight: 600; line-height: 1.8; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
    .qs-item-meta { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .qs-item-status { padding-top: 2px; }
    .qs-item .qs-caret { margin-top: 10px; }
    .qs-item-body { margin: 0 18px; padding: 20px 0 18px; border-top: 1px solid var(--line); }

    /* Empty */
    .qs-empty { display: grid; place-items: center; gap: 10px; padding: 48px 20px; color: var(--muted); font-size: 14px; text-align: center; }
    .qs-empty .qs-chip { width: 52px; height: 52px; border-radius: 16px; }

    .qs a:focus-visible, .qs button:focus-visible, .qs summary:focus-visible { outline: 3px solid rgba(255,165,26,.85); outline-offset: 2px; }

    @media (max-width: 760px) {
        .qs-filters { grid-template-columns: 1fr; padding: 14px; }
        .qs-filter-actions { justify-content: stretch; }
        .qs-filter-actions .qs-btn { flex: 1; }
        .qs-item > summary { flex-wrap: wrap; }
        .qs-item-main { flex-basis: calc(100% - 64px); }
        .qs-item-status { order: 3; padding-top: 0; }
        .qs-actions { justify-content: stretch; }
        .qs-actions .qs-btn { flex: 1; }
    }
    @media (prefers-reduced-motion: reduce) { .qs * { transition-duration: .01ms !important; } }
</style>

<!-- Icons (self-contained, duotone) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <symbol id="qs-help" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9" style="fill:var(--ic-accent);opacity:.25"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M9.6 9.6a2.5 2.5 0 1 1 3.8 2.1c-.9.5-1.4 1.1-1.4 2M12 16.9v.1"/></g>
        </symbol>
        <symbol id="qs-search" viewBox="0 0 24 24">
            <circle cx="10.5" cy="10.5" r="6.5" style="fill:var(--ic-accent);opacity:.25"/>
            <g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 4.5 4.5"/></g>
        </symbol>
        <symbol id="qs-plus" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></g>
        </symbol>
        <symbol id="qs-check" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></g>
        </symbol>
        <symbol id="qs-trash" viewBox="0 0 24 24">
            <path d="M6.5 7h11l-.8 11.2a2 2 0 0 1-2 1.8H9.3a2 2 0 0 1-2-1.8Z" style="fill:var(--ic-accent);opacity:.25"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 7h15M9.5 7V5a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v2M6.5 7l.8 11.2a2 2 0 0 0 2 1.8h5.4a2 2 0 0 0 2-1.8L17.5 7M10 11v5M14 11v5"/></g>
        </symbol>
        <symbol id="qs-caret" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></g>
        </symbol>
        <symbol id="qs-filter" viewBox="0 0 24 24">
            <path d="M4.5 5.5h15L14 12.5V19l-4-2v-4.5Z" style="fill:var(--ic-accent);opacity:.3"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 5.5h15L14 12.5V19l-4-2v-4.5Z"/></g>
        </symbol>
        <symbol id="qs-reset" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 12a7.5 7.5 0 1 0 2.4-5.5M4.5 4.5v4h4"/></g>
        </symbol>
        <symbol id="qs-layers" viewBox="0 0 24 24">
            <path d="m12 4 8.5 4.5L12 13 3.5 8.5Z" style="fill:var(--ic-accent);opacity:.35"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m12 4 8.5 4.5L12 13 3.5 8.5Z"/><path d="m3.5 12.5 8.5 4.5 8.5-4.5M3.5 16.5l8.5 4.5 8.5-4.5"/></g>
        </symbol>
        <symbol id="qs-target" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5.2"/></g>
            <circle cx="12" cy="12" r="2.2" style="fill:var(--ic-accent)"/>
        </symbol>
        <symbol id="qs-bulb" viewBox="0 0 24 24">
            <path d="M12 3a6 6 0 0 0-3.6 10.8c.6.5 1.1 1.2 1.1 2V16h5v-.2c0-.8.5-1.5 1.1-2A6 6 0 0 0 12 3Z" style="fill:var(--ic-accent);opacity:.4"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0-3.6 10.8c.6.5 1.1 1.2 1.1 2V16h5v-.2c0-.8.5-1.5 1.1-2A6 6 0 0 0 12 3Z"/><path d="M9.5 18.5h5M10.5 21h3"/></g>
        </symbol>
    </defs>
</svg>

<section class="qs">

    <!-- ═════════ Header ═════════ -->
    <header class="qs-head">
        <span class="qs-chip" aria-hidden="true"><svg class="qs-ic qs-ic--lg"><use href="#qs-help"/></svg></span>
        <div>
            <h2>إدارة الأسئلة</h2>
            <p>لكل سؤال مهارة واحدة وإجابة صحيحة وشرح عربي؛ أكمل أسباب الخيارات الخاطئة قبل التفعيل.</p>
        </div>
    </header>

    <!-- ═════════ Filters ═════════ -->
    <form method="get" action="<?= e(url('/admin/questions')) ?>" class="qs-card qs-filters">
        <div class="qs-search">
            <svg class="qs-ic" aria-hidden="true"><use href="#qs-search"/></svg>
            <input type="search" name="q" value="<?= e($search) ?>" placeholder="ابحث في نص السؤال" class="qs-input" aria-label="بحث في نص السؤال">
        </div>
        <select name="section_id" class="qs-input" aria-label="القسم"><option value="">كل الأقسام</option><?php foreach ($sections as $section): ?><option value="<?= e((string) $section['id']) ?>" <?= (string) ($filters['section_id'] ?? '') === (string) $section['id'] ? 'selected' : '' ?>><?= e($section['name_ar']) ?></option><?php endforeach; ?></select>
        <select name="practice_id" class="qs-input" aria-label="التدريب"><option value="">كل التدريبات</option><?php foreach ($practices as $practice): ?><option value="<?= e((string) $practice['id']) ?>" <?= (string) ($filters['practice_id'] ?? '') === (string) $practice['id'] ? 'selected' : '' ?>><?= e($practice['title_ar']) ?></option><?php endforeach; ?></select>
        <select name="skill_id" class="qs-input" aria-label="المهارة"><option value="">كل المهارات</option><?php foreach ($skills as $skill): ?><option value="<?= e((string) $skill['id']) ?>" <?= (string) ($filters['skill_id'] ?? '') === (string) $skill['id'] ? 'selected' : '' ?>><?= e($skill['name_ar']) ?></option><?php endforeach; ?></select>
        <div class="qs-filter-actions">
            <?php if ($hasFilters): ?>
                <a href="<?= e(url('/admin/questions')) ?>" class="qs-btn qs-btn--line"><svg class="qs-ic qs-ic--sm" aria-hidden="true"><use href="#qs-reset"/></svg>إعادة ضبط</a>
            <?php endif; ?>
            <button type="submit" class="qs-btn qs-btn--primary"><svg class="qs-ic qs-ic--sm" aria-hidden="true"><use href="#qs-filter"/></svg>تطبيق المرشحات</button>
        </div>
    </form>

    <!-- ═════════ Add ═════════ -->
    <details class="qs-card qs-fold qs-add">
        <summary>
            <span class="qs-chip" aria-hidden="true"><svg class="qs-ic"><use href="#qs-plus"/></svg></span>
            <span>إضافة سؤال جديد</span>
            <svg class="qs-ic qs-caret" aria-hidden="true"><use href="#qs-caret"/></svg>
        </summary>
        <form method="post" action="<?= e(url('/admin/questions')) ?>" class="qs-body qs-form">
            <?= csrfField() ?><input type="hidden" name="action" value="save">
            <?php $question = []; include __DIR__ . '/_question-fields.php'; ?>
            <div class="qs-actions">
                <button type="submit" class="qs-btn qs-btn--primary"><svg class="qs-ic qs-ic--sm" aria-hidden="true"><use href="#qs-check"/></svg>حفظ السؤال</button>
            </div>
        </form>
    </details>

    <!-- ═════════ Results ═════════ -->
    <?php if ($items): ?>
        <div class="qs-bar">
            <p>النتائج المعروضة في هذه الصفحة<?= $hasFilters ? ' (مع المرشحات المطبقة)' : '' ?></p>
            <div class="qs-bar-stats">
                <span class="qs-pill"><span><?= e((string) $resultCount) ?> سؤال</span></span>
                <span class="qs-pill qs-pill--ok"><span><?= e((string) $activeCount) ?> نشط</span></span>
                <?php if ($stoppedCount > 0): ?><span class="qs-pill qs-pill--off"><span><?= e((string) $stoppedCount) ?> متوقف</span></span><?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="qs-list">
        <?php foreach ($items as $question): ?>
            <?php $qid = (string) $question['id']; ?>
            <details class="qs-card qs-fold qs-item">
                <summary>
                    <span class="qs-chip" aria-hidden="true"><svg class="qs-ic"><use href="#qs-help"/></svg></span>
                    <div class="qs-item-main">
                        <p class="qs-item-text" dir="auto" title="<?= e($question['text']) ?>"><?= e(mb_strimwidth($question['text'], 0, 100, '…', 'UTF-8')) ?></p>
                        <div class="qs-item-meta">
                            <span class="qs-pill"><svg class="qs-ic qs-ic--sm" aria-hidden="true"><use href="#qs-layers"/></svg><span><?= e($question['section_name_ar']) ?></span></span>
                            <span class="qs-pill"><svg class="qs-ic qs-ic--sm" aria-hidden="true"><use href="#qs-target"/></svg><span><?= e($question['practice_title']) ?></span></span>
                            <span class="qs-pill"><svg class="qs-ic qs-ic--sm" aria-hidden="true"><use href="#qs-bulb"/></svg><span><?= e($question['skill_name_ar']) ?></span></span>
                        </div>
                    </div>
                    <div class="qs-item-status"><span class="qs-pill <?= $question['is_active'] ? 'qs-pill--ok' : 'qs-pill--off' ?>"><span><?= $question['is_active'] ? 'نشط' : 'متوقف' ?></span></span></div>
                    <svg class="qs-ic qs-caret" aria-hidden="true"><use href="#qs-caret"/></svg>
                </summary>

                <div class="qs-item-body">
                    <form method="post" action="<?= e(url('/admin/questions')) ?>" class="qs-form">
                        <?= csrfField() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e($qid) ?>">
                        <?php include __DIR__ . '/_question-fields.php'; ?>
                        <div class="qs-actions">
                            <button type="submit" form="delete-question-<?= e($qid) ?>" class="qs-btn qs-btn--danger qs-btn--sm"><svg class="qs-ic qs-ic--sm" aria-hidden="true"><use href="#qs-trash"/></svg>حذف السؤال</button>
                            <button type="submit" class="qs-btn qs-btn--primary qs-btn--sm"><svg class="qs-ic qs-ic--sm" aria-hidden="true"><use href="#qs-check"/></svg>حفظ التعديلات</button>
                        </div>
                    </form>

                    <form id="delete-question-<?= e($qid) ?>" method="post" action="<?= e(url('/admin/questions')) ?>" hidden onsubmit="return confirm('حذف السؤال؟ لا يمكن حذف سؤال مرتبط بإجابات مسجلة.');">
                        <?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($qid) ?>">
                    </form>
                </div>
            </details>
        <?php endforeach; ?>

        <?php if (!$items): ?>
            <div class="qs-card qs-empty">
                <span class="qs-chip" aria-hidden="true"><svg class="qs-ic qs-ic--lg"><use href="#qs-search"/></svg></span>
                <p>لا توجد أسئلة مطابقة للبحث.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/_pagination.php'; ?>
</section>