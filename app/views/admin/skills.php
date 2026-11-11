<?php
/* ── Data prep (view-only) ───────────────────────────────────── */
$sectionNames = [];
foreach ($sections as $section) {
    $sectionNames[(int) $section['id']] = $section['name_ar'];
}

$groups = [];
$generalCount = 0;
foreach ($items as $skill) {
    $key = !empty($skill['section_id']) ? (int) $skill['section_id'] : 0;
    $groups[$key][] = $skill;
    if ($key === 0) { $generalCount++; }
}
$totalSkills = count($items);
?>

<style>
    .sk { --navy: #262262; --navy-deep: #1C194B; --orange: #FFA51A; --ink: #211F35; --muted: #706E83; --line: #E8E7F0; --tint: #F1F0FA; --ic-accent: #FFA51A; display: grid; gap: 24px; color: var(--ink); }
    .sk *, .sk *::before, .sk *::after { box-sizing: border-box; }

    /* Icons */
    .sk-ic { display: block; width: 20px; height: 20px; flex: none; }
    .sk-ic--sm { width: 16px; height: 16px; }
    .sk-ic--lg { width: 24px; height: 24px; }

    /* Surfaces */
    .sk-card { background: #fff; border: 1px solid var(--line); border-radius: 18px; box-shadow: 0 1px 2px rgba(38,34,98,.04); }
    .sk-chip { display: grid; width: 44px; height: 44px; flex: none; place-items: center; border-radius: 13px; background: var(--tint); color: var(--navy); }

    /* Page header + section headings */
    .sk-head { display: flex; align-items: center; gap: 14px; }
    .sk-head h2 { margin: 0; font-size: 24px; font-weight: 700; line-height: 1.35; }
    .sk-head p { max-width: 62ch; margin: 3px 0 0; color: var(--muted); font-size: 14px; line-height: 1.8; }
    .sk-block-head { display: flex; flex-wrap: wrap; align-items: baseline; gap: 4px 12px; margin: 0 0 14px; }
    .sk-block-head h3 { margin: 0; font-size: 18px; font-weight: 700; }
    .sk-block-head p { margin: 0; color: var(--muted); font-size: 13px; }

    /* Fields */
    .sk-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
    .sk-label { display: block; margin-bottom: 7px; color: #37354B; font-size: 13px; font-weight: 600; }
    .sk-input { width: 100%; height: 46px; padding: 0 14px; border: 1px solid var(--line); border-radius: 12px; outline: none; background-color: #FCFCFE; color: var(--ink); font: inherit; font-size: 14px; transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease; }
    textarea.sk-input { height: auto; min-height: 112px; padding: 12px 14px; line-height: 1.85; resize: vertical; }
    .sk-input::placeholder { color: #A5A3B2; }
    .sk-input:hover { border-color: #C9C7D9; }
    .sk-input:focus { border-color: var(--navy); background-color: #fff; box-shadow: 0 0 0 4px rgba(38,34,98,.08); }
    select.sk-input { padding-left: 38px; cursor: pointer; appearance: none; -webkit-appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23706E83' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: left 13px center; }

    /* Buttons */
    .sk-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 10px; margin-top: 20px; }
    .sk-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 18px; border: 1px solid transparent; border-radius: 12px; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: background .15s ease, border-color .15s ease; }
    .sk-btn--sm { padding: 9px 14px; font-size: 13px; }
    .sk-btn--primary { background: var(--navy); color: #fff; }
    .sk-btn--primary:hover { background: var(--navy-deep); }
    .sk-btn--danger { --ic-accent: #C2414B; border-color: #F2C6CA; background: #fff; color: #B03541; }
    .sk-btn--danger:hover { background: #FFF4F4; }

    /* Pills */
    .sk-pill { display: inline-flex; max-width: 100%; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: var(--tint); color: var(--navy); font-size: 12px; font-weight: 600; white-space: nowrap; }
    .sk-pill > span { overflow: hidden; text-overflow: ellipsis; }
    .sk-pill--off { background: #F3F2F8; color: var(--muted); }

    /* Section tips */
    .sk-tips { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .sk-tip { display: flex; flex-direction: column; padding: 20px; }
    .sk-tip-head { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .sk-tip-head h4 { margin: 0; font-size: 16px; font-weight: 700; line-height: 1.4; }
    .sk-tip-head small { display: block; margin-top: 2px; color: var(--muted); font-size: 12px; }
    .sk-tip-head .sk-pill { margin-inline-start: auto; }
    .sk-tip form { display: flex; flex: 1; flex-direction: column; }
    .sk-tip .sk-actions { margin-top: auto; padding-top: 16px; }

    /* Toolbar */
    .sk-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: -6px; }
    .sk-bar p { margin: 0; color: var(--muted); font-size: 13px; }
    .sk-bar-stats { display: flex; flex-wrap: wrap; gap: 8px; }

    /* Collapsible blocks */
    .sk-fold > summary { display: flex; align-items: center; gap: 14px; list-style: none; cursor: pointer; }
    .sk-fold > summary::-webkit-details-marker { display: none; }
    .sk-caret { margin-inline-start: 4px; color: var(--muted); transition: transform .18s ease; }
    .sk-fold[open] > summary .sk-caret { transform: rotate(180deg); }

    .sk-add { padding: 18px 22px; }
    .sk-add > summary { font-size: 16px; font-weight: 700; }
    .sk-add > summary .sk-caret { margin-inline-start: auto; }
    .sk-body { margin-top: 18px; padding-top: 22px; border-top: 1px solid var(--line); }

    /* Groups + rows */
    .sk-list { display: grid; gap: 26px; }
    .sk-group-head { display: flex; align-items: center; gap: 10px; margin: 0 0 12px; }
    .sk-group-head h4 { margin: 0; font-size: 16px; font-weight: 700; }
    .sk-rows { display: grid; gap: 10px; }

    .sk-item { transition: border-color .15s ease; }
    .sk-item:hover, .sk-item[open] { border-color: #D8D6EF; }
    .sk-item > summary { padding: 14px 18px; }
    .sk-item-title { min-width: 0; flex: 1; }
    .sk-item-title strong { display: block; overflow: hidden; font-size: 15px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
    .sk-item-title span { display: block; width: fit-content; max-width: 100%; overflow: hidden; margin-top: 2px; color: var(--muted); font-size: 12px; text-overflow: ellipsis; white-space: nowrap; }
    .sk-item-body { margin: 0 18px; padding: 20px 0 18px; border-top: 1px solid var(--line); }

    /* Empty */
    .sk-empty { display: grid; place-items: center; gap: 10px; padding: 48px 20px; color: var(--muted); font-size: 14px; text-align: center; }
    .sk-empty .sk-chip { width: 52px; height: 52px; border-radius: 16px; }

    .sk a:focus-visible, .sk button:focus-visible, .sk summary:focus-visible { outline: 3px solid rgba(255,165,26,.85); outline-offset: 2px; }

    @media (max-width: 900px) {
        .sk-tips { grid-template-columns: 1fr; }
    }
    @media (max-width: 760px) {
        .sk-grid { grid-template-columns: 1fr; }
        .sk-actions { justify-content: stretch; }
        .sk-actions .sk-btn { flex: 1; }
    }
    @media (prefers-reduced-motion: reduce) { .sk * { transition-duration: .01ms !important; } }
</style>

<!-- Icons (self-contained, duotone) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <symbol id="sk-bulb" viewBox="0 0 24 24">
            <path d="M12 3a6 6 0 0 0-3.6 10.8c.6.5 1.1 1.2 1.1 2V16h5v-.2c0-.8.5-1.5 1.1-2A6 6 0 0 0 12 3Z" style="fill:var(--ic-accent);opacity:.4"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0-3.6 10.8c.6.5 1.1 1.2 1.1 2V16h5v-.2c0-.8.5-1.5 1.1-2A6 6 0 0 0 12 3Z"/><path d="M9.5 18.5h5M10.5 21h3"/></g>
        </symbol>
        <symbol id="sk-layers" viewBox="0 0 24 24">
            <path d="m12 4 8.5 4.5L12 13 3.5 8.5Z" style="fill:var(--ic-accent);opacity:.35"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m12 4 8.5 4.5L12 13 3.5 8.5Z"/><path d="m3.5 12.5 8.5 4.5 8.5-4.5M3.5 16.5l8.5 4.5 8.5-4.5"/></g>
        </symbol>
        <symbol id="sk-globe" viewBox="0 0 24 24">
            <path d="M12 3c2.6 2.7 3.9 5.7 3.9 9S14.6 18.3 12 21c-2.6-2.7-3.9-5.7-3.9-9S9.4 5.7 12 3Z" style="fill:var(--ic-accent);opacity:.3"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.6 2.7 3.9 5.7 3.9 9S14.6 18.3 12 21c-2.6-2.7-3.9-5.7-3.9-9S9.4 5.7 12 3Z"/></g>
        </symbol>
        <symbol id="sk-plus" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></g>
        </symbol>
        <symbol id="sk-check" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></g>
        </symbol>
        <symbol id="sk-trash" viewBox="0 0 24 24">
            <path d="M6.5 7h11l-.8 11.2a2 2 0 0 1-2 1.8H9.3a2 2 0 0 1-2-1.8Z" style="fill:var(--ic-accent);opacity:.25"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 7h15M9.5 7V5a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v2M6.5 7l.8 11.2a2 2 0 0 0 2 1.8h5.4a2 2 0 0 0 2-1.8L17.5 7M10 11v5M14 11v5"/></g>
        </symbol>
        <symbol id="sk-caret" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></g>
        </symbol>
    </defs>
</svg>

<section class="sk">

    <!-- ═════════ Header ═════════ -->
    <header class="sk-head">
        <span class="sk-chip" aria-hidden="true"><svg class="sk-ic sk-ic--lg"><use href="#sk-bulb"/></svg></span>
        <div>
            <h2>المهارات ونصائح الأقسام</h2>
            <p>اربط كل سؤال بمهارة، واكتب إرشادات استراتيجية تظهر أعلى صفحة القسم.</p>
        </div>
    </header>

    <!-- ═════════ Section tips ═════════ -->
    <section aria-labelledby="sk-tips-title">
        <div class="sk-block-head">
            <h3 id="sk-tips-title">نصائح الأقسام</h3>
            <p>تظهر النصيحة أعلى صفحة القسم للطالب.</p>
        </div>

        <div class="sk-tips">
            <?php foreach ($sections as $section): ?>
                <?php $tip = trim((string) ($section['tip_text'] ?? '')); $sid = (string) $section['id']; ?>
                <article class="sk-card sk-tip">
                    <div class="sk-tip-head">
                        <span class="sk-chip" aria-hidden="true"><svg class="sk-ic"><use href="#sk-layers"/></svg></span>
                        <h4><?= e($section['name_ar']) ?><small dir="ltr"><?= e($section['name_en']) ?></small></h4>
                        <?php if ($tip === ''): ?><span class="sk-pill sk-pill--off"><span>بدون نصيحة</span></span><?php endif; ?>
                    </div>

                    <form method="post" action="<?= e(url('/admin/skills')) ?>">
                        <?= csrfField() ?><input type="hidden" name="action" value="tip"><input type="hidden" name="section_id" value="<?= e($sid) ?>">
                        <label>
                            <span class="sk-label">نصيحة الاستراتيجية</span>
                            <textarea name="tip_text" required rows="3" class="sk-input"><?= e($section['tip_text'] ?? '') ?></textarea>
                        </label>
                        <div class="sk-actions">
                            <button type="submit" class="sk-btn sk-btn--primary sk-btn--sm"><svg class="sk-ic sk-ic--sm" aria-hidden="true"><use href="#sk-check"/></svg>حفظ النصيحة</button>
                        </div>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ═════════ Skills ═════════ -->
    <section aria-labelledby="sk-skills-title" style="display:grid;gap:22px">
        <div class="sk-block-head" style="margin:0">
            <h3 id="sk-skills-title">المهارات</h3>
            <p>كل سؤال يرتبط بمهارة واحدة.</p>
        </div>

        <details class="sk-card sk-fold sk-add">
            <summary>
                <span class="sk-chip" aria-hidden="true"><svg class="sk-ic"><use href="#sk-plus"/></svg></span>
                <span>إضافة مهارة</span>
                <svg class="sk-ic sk-caret" aria-hidden="true"><use href="#sk-caret"/></svg>
            </summary>

            <form method="post" action="<?= e(url('/admin/skills')) ?>" class="sk-body">
                <?= csrfField() ?><input type="hidden" name="action" value="save">

                <div class="sk-grid">
                    <label>
                        <span class="sk-label">الاسم بالعربية</span>
                        <input name="name_ar" required maxlength="255" class="sk-input">
                    </label>
                    <label>
                        <span class="sk-label">الاسم بالإنجليزية</span>
                        <input name="name_en" required maxlength="255" dir="ltr" class="sk-input">
                    </label>
                    <label>
                        <span class="sk-label">القسم</span>
                        <select name="section_id" class="sk-input"><option value="">عامة</option><?php foreach ($sections as $section): ?><option value="<?= e((string) $section['id']) ?>"><?= e($section['name_ar']) ?></option><?php endforeach; ?></select>
                    </label>
                </div>

                <div class="sk-actions">
                    <button type="submit" class="sk-btn sk-btn--primary"><svg class="sk-ic sk-ic--sm" aria-hidden="true"><use href="#sk-plus"/></svg>إضافة المهارة</button>
                </div>
            </form>
        </details>

        <?php if ($items): ?>
            <div class="sk-bar" style="margin-bottom:-8px">
                <p>المهارات المعروضة في هذه الصفحة</p>
                <div class="sk-bar-stats">
                    <span class="sk-pill"><span><?= e((string) $totalSkills) ?> مهارة</span></span>
                    <?php if ($generalCount > 0): ?><span class="sk-pill"><svg class="sk-ic sk-ic--sm" aria-hidden="true"><use href="#sk-globe"/></svg><span><?= e((string) $generalCount) ?> عامة</span></span><?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Grouped by section -->
        <div class="sk-list">
            <?php foreach ($groups as $sectionId => $groupItems): ?>
                <?php $groupName = $sectionId === 0 ? 'مهارات عامة' : ($sectionNames[$sectionId] ?? 'بدون قسم'); ?>
                <section aria-label="<?= e($groupName) ?>">
                    <div class="sk-group-head">
                        <span class="sk-ic" aria-hidden="true" style="color:var(--navy)"><svg class="sk-ic"><use href="#<?= $sectionId === 0 ? 'sk-globe' : 'sk-layers' ?>"/></svg></span>
                        <h4><?= e($groupName) ?></h4>
                        <span class="sk-pill"><span><?= e((string) count($groupItems)) ?></span></span>
                    </div>

                    <div class="sk-rows">
                        <?php foreach ($groupItems as $skill): ?>
                            <?php $kid = (string) $skill['id']; ?>
                            <details class="sk-card sk-fold sk-item">
                                <summary>
                                    <span class="sk-chip" aria-hidden="true"><svg class="sk-ic"><use href="#sk-bulb"/></svg></span>
                                    <div class="sk-item-title">
                                        <strong><?= e($skill['name_ar']) ?></strong>
                                        <span dir="ltr"><?= e($skill['name_en']) ?></span>
                                    </div>
                                    <svg class="sk-ic sk-caret" aria-hidden="true"><use href="#sk-caret"/></svg>
                                </summary>

                                <div class="sk-item-body">
                                    <form method="post" action="<?= e(url('/admin/skills')) ?>">
                                        <?= csrfField() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e($kid) ?>">

                                        <div class="sk-grid">
                                            <label>
                                                <span class="sk-label">بالعربية</span>
                                                <input name="name_ar" required maxlength="255" value="<?= e($skill['name_ar']) ?>" class="sk-input">
                                            </label>
                                            <label>
                                                <span class="sk-label">بالإنجليزية</span>
                                                <input name="name_en" required maxlength="255" dir="ltr" value="<?= e($skill['name_en']) ?>" class="sk-input">
                                            </label>
                                            <label>
                                                <span class="sk-label">القسم</span>
                                                <select name="section_id" class="sk-input"><option value="">عامة</option><?php foreach ($sections as $section): ?><option value="<?= e((string) $section['id']) ?>" <?= (string) ($skill['section_id'] ?? '') === (string) $section['id'] ? 'selected' : '' ?>><?= e($section['name_ar']) ?></option><?php endforeach; ?></select>
                                            </label>
                                        </div>

                                        <div class="sk-actions">
                                            <button type="submit" form="delete-skill-<?= e($kid) ?>" class="sk-btn sk-btn--danger sk-btn--sm"><svg class="sk-ic sk-ic--sm" aria-hidden="true"><use href="#sk-trash"/></svg>حذف</button>
                                            <button type="submit" class="sk-btn sk-btn--primary sk-btn--sm"><svg class="sk-ic sk-ic--sm" aria-hidden="true"><use href="#sk-check"/></svg>حفظ المهارة</button>
                                        </div>
                                    </form>

                                    <form id="delete-skill-<?= e($kid) ?>" method="post" action="<?= e(url('/admin/skills')) ?>" hidden onsubmit="return confirm('حذف المهارة؟ لا يمكن حذفها إذا كانت مرتبطة بأسئلة.');">
                                        <?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($kid) ?>">
                                    </form>
                                </div>
                            </details>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>

            <?php if (!$items): ?>
                <div class="sk-card sk-empty">
                    <span class="sk-chip" aria-hidden="true"><svg class="sk-ic sk-ic--lg"><use href="#sk-bulb"/></svg></span>
                    <p>لا توجد مهارات بعد.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include __DIR__ . '/_pagination.php'; ?>
</section>