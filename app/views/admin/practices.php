<?php
/* ── Data prep (view-only) ───────────────────────────────────── */
$sectionNames = [];
foreach ($sections as $section) {
    $sectionNames[(int) $section['id']] = $section['name_ar'];
}

$groups = [];
$totalPractices = 0;
$activePractices = 0;
$totalQuestions = 0;
foreach ($items as $practice) {
    $groups[(int) $practice['section_id']][] = $practice;
    $totalPractices++;
    if ($practice['is_active']) { $activePractices++; }
    $totalQuestions += (int) $practice['question_count'];
}
$inactivePractices = $totalPractices - $activePractices;
?>

<style>
    .pr { --navy: #262262; --navy-deep: #1C194B; --orange: #FFA51A; --ink: #211F35; --muted: #706E83; --line: #E8E7F0; --tint: #F1F0FA; --ic-accent: #FFA51A; display: grid; gap: 24px; color: var(--ink); }
    .pr *, .pr *::before, .pr *::after { box-sizing: border-box; }

    /* Icons */
    .pr-ic { display: block; width: 20px; height: 20px; flex: none; }
    .pr-ic--sm { width: 17px; height: 17px; }
    .pr-ic--lg { width: 24px; height: 24px; }

    /* Surfaces */
    .pr-card { background: #fff; border: 1px solid var(--line); border-radius: 18px; box-shadow: 0 1px 2px rgba(38,34,98,.04); }
    .pr-chip { display: grid; width: 44px; height: 44px; flex: none; place-items: center; border-radius: 13px; background: var(--tint); color: var(--navy); }

    /* Page header */
    .pr-head { display: flex; align-items: center; gap: 14px; }
    .pr-head h2 { margin: 0; font-size: 24px; font-weight: 700; line-height: 1.35; }
    .pr-head p { margin: 3px 0 0; color: var(--muted); font-size: 14px; }

    /* Summary */
    .pr-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
    .pr-stat { display: flex; align-items: center; gap: 14px; padding: 16px 18px; }
    .pr-stat-num { margin: 0; color: var(--navy); font-size: 26px; font-weight: 700; line-height: 1.1; }
    .pr-stat-label { margin: 4px 0 0; color: var(--muted); font-size: 13px; }
    .pr-note { margin: -10px 0 0; color: var(--muted); font-size: 12px; }

    /* Form fields */
    .pr-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
    .pr-label { display: block; margin-bottom: 7px; color: #37354B; font-size: 13px; font-weight: 600; }
    .pr-input { width: 100%; height: 46px; padding: 0 14px; border: 1px solid var(--line); border-radius: 12px; outline: none; background-color: #FCFCFE; color: var(--ink); font: inherit; font-size: 14px; transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease; }
    .pr-input::placeholder { color: #A5A3B2; }
    .pr-input:hover { border-color: #C9C7D9; }
    .pr-input:focus { border-color: var(--navy); background-color: #fff; box-shadow: 0 0 0 4px rgba(38,34,98,.08); }
    select.pr-input { padding-left: 38px; cursor: pointer; appearance: none; -webkit-appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23706E83' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: left 13px center; }
    .pr-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; margin-top: 22px; }
    .pr-actions-end { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }

    /* Buttons */
    .pr-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 18px; border: 1px solid transparent; border-radius: 12px; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: background .15s ease, border-color .15s ease; }
    .pr-btn--sm { padding: 9px 14px; font-size: 13px; }
    .pr-btn--primary { background: var(--navy); color: #fff; }
    .pr-btn--primary:hover { background: var(--navy-deep); }
    .pr-btn--danger { --ic-accent: #C2414B; border-color: #F2C6CA; background: #fff; color: #B03541; }
    .pr-btn--danger:hover { background: #FFF4F4; }

    /* Switch */
    .pr-switch-row { display: inline-flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 500; cursor: pointer; }
    .pr-switch { position: relative; width: 42px; height: 24px; flex: none; margin: 0; border-radius: 999px; outline: none; background: #D8D6EF; cursor: pointer; appearance: none; -webkit-appearance: none; transition: background .18s ease; }
    .pr-switch::after { position: absolute; top: 3px; right: 3px; width: 18px; height: 18px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(33,31,53,.3); content: ""; transition: transform .18s ease; }
    .pr-switch:checked { background: var(--navy); }
    .pr-switch:checked::after { transform: translateX(-18px); }

    /* Pills */
    .pr-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: var(--tint); color: var(--navy); font-size: 12px; font-weight: 600; white-space: nowrap; }
    .pr-pill--ok, .pr-pill--off { }
    .pr-pill--ok::before, .pr-pill--off::before { width: 6px; height: 6px; border-radius: 50%; background: currentColor; content: ""; }
    .pr-pill--ok { background: #ECF8F2; color: #1F7A52; }
    .pr-pill--off { background: #F3F2F8; color: var(--muted); }

    /* Collapsible blocks */
    .pr-fold > summary { display: flex; align-items: center; gap: 14px; list-style: none; cursor: pointer; }
    .pr-fold > summary::-webkit-details-marker { display: none; }
    .pr-caret { margin-inline-start: 4px; color: var(--muted); transition: transform .18s ease; }
    .pr-fold[open] > summary .pr-caret { transform: rotate(180deg); }

    .pr-add { padding: 20px 22px; }
    .pr-add > summary { font-size: 16px; font-weight: 700; }
    .pr-add > summary .pr-caret { margin-inline-start: auto; }
    .pr-add-body { margin-top: 20px; padding-top: 22px; border-top: 1px solid var(--line); }

    /* Groups + rows */
    .pr-list { display: grid; gap: 28px; }
    .pr-group-head { display: flex; align-items: center; gap: 10px; margin: 0 0 12px; }
    .pr-group-head h3 { margin: 0; font-size: 16px; font-weight: 700; }
    .pr-group-head .pr-pill { margin-inline-start: 2px; }
    .pr-rows { display: grid; gap: 10px; }

    .pr-item { transition: border-color .15s ease; }
    .pr-item:hover, .pr-item[open] { border-color: #D8D6EF; }
    .pr-item > summary { padding: 14px 18px; }
    .pr-item-title { min-width: 0; flex: 1; }
    .pr-item-title strong { display: block; overflow: hidden; font-size: 15px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
    .pr-item-title span { display: block; overflow: hidden; margin-top: 2px; color: var(--muted); font-size: 12px; text-overflow: ellipsis; white-space: nowrap; }
    .pr-item-meta { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 8px; }
    .pr-item-body { margin: 0 18px; padding: 20px 0 18px; border-top: 1px solid var(--line); }

    /* Empty */
    .pr-empty { display: grid; place-items: center; gap: 10px; padding: 48px 20px; color: var(--muted); font-size: 14px; text-align: center; }
    .pr-empty .pr-chip { width: 52px; height: 52px; border-radius: 16px; }

    .pr a:focus-visible, .pr button:focus-visible, .pr summary:focus-visible, .pr .pr-switch:focus-visible { outline: 3px solid rgba(255,165,26,.85); outline-offset: 2px; }

    @media (max-width: 1100px) {
        .pr-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 900px) {
        .pr-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 600px) {
        .pr-grid { grid-template-columns: 1fr; }
        .pr-stats { gap: 10px; }
        .pr-stat { padding: 14px; }
        .pr-item > summary { flex-wrap: wrap; }
        .pr-item-title { flex-basis: calc(100% - 100px); }
        .pr-item-meta { order: 3; width: 100%; justify-content: flex-start; }
        .pr-actions, .pr-actions-end { width: 100%; }
        .pr-actions-end .pr-btn { flex: 1; }
    }
    @media (prefers-reduced-motion: reduce) { .pr * { transition-duration: .01ms !important; } }
</style>

<!-- Icons (self-contained, duotone) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <symbol id="pr-target" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5.2"/></g>
            <circle cx="12" cy="12" r="2.2" style="fill:var(--ic-accent)"/>
        </symbol>
        <symbol id="pr-plus" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></g>
        </symbol>
        <symbol id="pr-check" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></g>
        </symbol>
        <symbol id="pr-trash" viewBox="0 0 24 24">
            <path d="M6.5 7h11l-.8 11.2a2 2 0 0 1-2 1.8H9.3a2 2 0 0 1-2-1.8Z" style="fill:var(--ic-accent);opacity:.25"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 7h15M9.5 7V5a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v2M6.5 7l.8 11.2a2 2 0 0 0 2 1.8h5.4a2 2 0 0 0 2-1.8L17.5 7M10 11v5M14 11v5"/></g>
        </symbol>
        <symbol id="pr-caret" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></g>
        </symbol>
        <symbol id="pr-layers" viewBox="0 0 24 24">
            <path d="m12 4 8.5 4.5L12 13 3.5 8.5Z" style="fill:var(--ic-accent);opacity:.35"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m12 4 8.5 4.5L12 13 3.5 8.5Z"/><path d="m3.5 12.5 8.5 4.5 8.5-4.5M3.5 16.5l8.5 4.5 8.5-4.5"/></g>
        </symbol>
        <symbol id="pr-eye" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="1.6" style="fill:var(--ic-accent)"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3.2"/></g>
        </symbol>
        <symbol id="pr-eye-off" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 3.5l17 17"/><path d="M6.5 7.2C4 8.9 2.5 12 2.5 12S6 18.5 12 18.5c1.5 0 2.8-.4 4-.9"/><path d="M9.9 5.7c.7-.1 1.4-.2 2.1-.2 6 0 9.5 6.5 9.5 6.5s-.8 1.5-2.2 3"/><path d="M9.9 9.9a3.2 3.2 0 0 0 4.2 4.2"/></g>
        </symbol>
        <symbol id="pr-help" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9" style="fill:var(--ic-accent);opacity:.25"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M9.6 9.6a2.5 2.5 0 1 1 3.8 2.1c-.9.5-1.4 1.1-1.4 2M12 16.9v.1"/></g>
        </symbol>
    </defs>
</svg>

<section class="pr">

    <!-- ═════════ Header ═════════ -->
    <header class="pr-head">
        <span class="pr-chip" aria-hidden="true"><svg class="pr-ic pr-ic--lg"><use href="#pr-target"/></svg></span>
        <div>
            <h2>التدريبات</h2>
            <p>أنشئ التدريبات ورتبها وفعّل أو أوقف ظهورها للطلاب.</p>
        </div>
    </header>

    <!-- ═════════ Summary ═════════ -->
    <div class="pr-stats">
        <div class="pr-card pr-stat">
            <span class="pr-chip" aria-hidden="true"><svg class="pr-ic"><use href="#pr-layers"/></svg></span>
            <div><p class="pr-stat-num"><?= e((string) $totalPractices) ?></p><p class="pr-stat-label">إجمالي التدريبات</p></div>
        </div>
        <div class="pr-card pr-stat">
            <span class="pr-chip" aria-hidden="true"><svg class="pr-ic"><use href="#pr-eye"/></svg></span>
            <div><p class="pr-stat-num"><?= e((string) $activePractices) ?></p><p class="pr-stat-label">ظاهرة للطلاب</p></div>
        </div>
        <div class="pr-card pr-stat">
            <span class="pr-chip" aria-hidden="true"><svg class="pr-ic"><use href="#pr-eye-off"/></svg></span>
            <div><p class="pr-stat-num"><?= e((string) $inactivePractices) ?></p><p class="pr-stat-label">موقوفة</p></div>
        </div>
        <div class="pr-card pr-stat">
            <span class="pr-chip" aria-hidden="true"><svg class="pr-ic"><use href="#pr-help"/></svg></span>
            <div><p class="pr-stat-num"><?= e((string) $totalQuestions) ?></p><p class="pr-stat-label">إجمالي الأسئلة</p></div>
        </div>
    </div>
    <p class="pr-note">الأرقام أعلاه تخص التدريبات المعروضة في هذه الصفحة.</p>

    <!-- ═════════ Add ═════════ -->
    <details class="pr-card pr-fold pr-add" open>
        <summary>
            <span class="pr-chip" aria-hidden="true"><svg class="pr-ic"><use href="#pr-plus"/></svg></span>
            <span>إضافة تدريب جديد</span>
            <svg class="pr-ic pr-caret" aria-hidden="true"><use href="#pr-caret"/></svg>
        </summary>

        <form method="post" action="<?= e(url('/admin/practices')) ?>" class="pr-add-body">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="save">

            <div class="pr-grid">
                <label>
                    <span class="pr-label">القسم</span>
                    <select name="section_id" required class="pr-input">
                        <?php foreach ($sections as $section): ?><option value="<?= e((string) $section['id']) ?>"><?= e($section['name_ar']) ?></option><?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span class="pr-label">الاسم بالعربية</span>
                    <input name="title_ar" required maxlength="255" class="pr-input" placeholder="تدريب 1">
                </label>
                <label>
                    <span class="pr-label">الاسم بالإنجليزية</span>
                    <input name="title_en" required maxlength="255" dir="ltr" class="pr-input" placeholder="Model 1">
                </label>
                <label>
                    <span class="pr-label">ترتيب العرض</span>
                    <input name="sort_order" type="number" min="0" value="0" class="pr-input">
                </label>
                <label>
                    <span class="pr-label">المدة بالدقائق (اختياري)</span>
                    <input name="time_limit_min" type="number" min="1" max="600" class="pr-input">
                </label>
            </div>

            <div class="pr-actions">
                <label class="pr-switch-row"><input type="checkbox" name="is_active" checked class="pr-switch"><span>ظاهر للطلاب</span></label>
                <div class="pr-actions-end">
                    <button type="submit" class="pr-btn pr-btn--primary"><svg class="pr-ic pr-ic--sm" aria-hidden="true"><use href="#pr-plus"/></svg>إضافة التدريب</button>
                </div>
            </div>
        </form>
    </details>

    <!-- ═════════ List (grouped by section) ═════════ -->
    <div class="pr-list">
        <?php foreach ($groups as $sectionId => $groupItems): ?>
            <section aria-label="<?= e($sectionNames[$sectionId] ?? 'بدون قسم') ?>">
                <div class="pr-group-head">
                    <h3><?= e($sectionNames[$sectionId] ?? 'بدون قسم') ?></h3>
                    <span class="pr-pill"><?= e((string) count($groupItems)) ?></span>
                </div>

                <div class="pr-rows">
                    <?php foreach ($groupItems as $practice): ?>
                        <?php $pid = (string) $practice['id']; ?>
                        <details class="pr-card pr-fold pr-item">
                            <summary>
                                <span class="pr-chip" aria-hidden="true"><svg class="pr-ic"><use href="#pr-target"/></svg></span>
                                <div class="pr-item-title">
                                    <strong><?= e($practice['title_ar']) ?></strong>
                                    <span dir="ltr"><?= e($practice['title_en']) ?></span>
                                </div>
                                <div class="pr-item-meta">
                                    <?php if ($practice['time_limit_min']): ?><span class="pr-pill"><?= e((string) $practice['time_limit_min']) ?> دقيقة</span><?php endif; ?>
                                    <span class="pr-pill"><?= e((string) $practice['question_count']) ?> سؤال</span>
                                    <span class="pr-pill <?= $practice['is_active'] ? 'pr-pill--ok' : 'pr-pill--off' ?>"><?= $practice['is_active'] ? 'ظاهر للطلاب' : 'موقوف' ?></span>
                                </div>
                                <svg class="pr-ic pr-caret" aria-hidden="true"><use href="#pr-caret"/></svg>
                            </summary>

                            <div class="pr-item-body">
                                <form method="post" action="<?= e(url('/admin/practices')) ?>">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e($pid) ?>">

                                    <div class="pr-grid">
                                        <label>
                                            <span class="pr-label">القسم</span>
                                            <select name="section_id" class="pr-input"><?php foreach ($sections as $section): ?><option value="<?= e((string) $section['id']) ?>" <?= (int) $practice['section_id'] === (int) $section['id'] ? 'selected' : '' ?>><?= e($section['name_ar']) ?></option><?php endforeach; ?></select>
                                        </label>
                                        <label>
                                            <span class="pr-label">العنوان العربي</span>
                                            <input name="title_ar" required maxlength="255" value="<?= e($practice['title_ar']) ?>" class="pr-input">
                                        </label>
                                        <label>
                                            <span class="pr-label">English title</span>
                                            <input name="title_en" required maxlength="255" value="<?= e($practice['title_en']) ?>" dir="ltr" class="pr-input">
                                        </label>
                                        <label>
                                            <span class="pr-label">الترتيب</span>
                                            <input name="sort_order" type="number" min="0" value="<?= e((string) $practice['sort_order']) ?>" class="pr-input">
                                        </label>
                                        <label>
                                            <span class="pr-label">المدة (دقيقة)</span>
                                            <input name="time_limit_min" type="number" min="1" max="600" value="<?= e((string) ($practice['time_limit_min'] ?? '')) ?>" class="pr-input">
                                        </label>
                                    </div>

                                    <div class="pr-actions">
                                        <label class="pr-switch-row"><input type="checkbox" name="is_active" <?= $practice['is_active'] ? 'checked' : '' ?> class="pr-switch"><span>نشط</span></label>
                                        <div class="pr-actions-end">
                                            <button type="submit" form="delete-practice-<?= e($pid) ?>" class="pr-btn pr-btn--danger pr-btn--sm"><svg class="pr-ic pr-ic--sm" aria-hidden="true"><use href="#pr-trash"/></svg>حذف التدريب</button>
                                            <button type="submit" class="pr-btn pr-btn--primary pr-btn--sm"><svg class="pr-ic pr-ic--sm" aria-hidden="true"><use href="#pr-check"/></svg>حفظ التغييرات</button>
                                        </div>
                                    </div>
                                </form>

                                <form id="delete-practice-<?= e($pid) ?>" method="post" action="<?= e(url('/admin/practices')) ?>" hidden onsubmit="return confirm('حذف التدريب؟ يجب حذف أسئلته ومحاولاته المرتبطة أولاً.');">
                                    <?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($pid) ?>">
                                </form>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>

        <?php if (!$items): ?>
            <div class="pr-card pr-empty">
                <span class="pr-chip" aria-hidden="true"><svg class="pr-ic pr-ic--lg"><use href="#pr-target"/></svg></span>
                <p>لم تتم إضافة تدريبات بعد.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/_pagination.php'; ?>
</section>