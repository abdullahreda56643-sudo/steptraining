<?php
/* ── Data prep (view-only) ───────────────────────────────────── */
$totalWords = count($items);
$activeWords = 0;
foreach ($items as $row) {
    if ($row['is_active']) { $activeWords++; }
}
$stoppedWords = $totalWords - $activeWords;

?>

<style>
    .vc { --navy: #262262; --navy-deep: #1C194B; --orange: #FFA51A; --ink: #211F35; --muted: #706E83; --line: #E8E7F0; --tint: #F1F0FA; --ic-accent: #FFA51A; display: grid; gap: 22px; color: var(--ink); }
    .vc *, .vc *::before, .vc *::after { box-sizing: border-box; }

    /* Icons */
    .vc-ic { display: block; width: 20px; height: 20px; flex: none; }
    .vc-ic--sm { width: 16px; height: 16px; }
    .vc-ic--lg { width: 24px; height: 24px; }

    /* Surfaces */
    .vc-card { background: #fff; border: 1px solid var(--line); border-radius: 18px; box-shadow: 0 1px 2px rgba(38,34,98,.04); }
    .vc-chip { display: grid; width: 44px; height: 44px; flex: none; place-items: center; border-radius: 13px; background: var(--tint); color: var(--navy); }

    /* Header */
    .vc-head { display: flex; align-items: center; gap: 14px; }
    .vc-head h2 { margin: 0; font-size: 24px; font-weight: 700; line-height: 1.35; }
    .vc-head p { margin: 3px 0 0; color: var(--muted); font-size: 14px; }

    /* Fields */
    .vc-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
    .vc-span { grid-column: 1 / -1; }
    .vc-label { display: block; margin-bottom: 7px; color: #37354B; font-size: 13px; font-weight: 600; }
    .vc-input { width: 100%; height: 46px; padding: 0 14px; border: 1px solid var(--line); border-radius: 12px; outline: none; background-color: #FCFCFE; color: var(--ink); font: inherit; font-size: 14px; transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease; }
    textarea.vc-input { height: auto; min-height: 92px; padding: 12px 14px; line-height: 1.8; resize: vertical; }
    .vc-input::placeholder { color: #A5A3B2; }
    .vc-input:hover { border-color: #C9C7D9; }
    .vc-input:focus { border-color: var(--navy); background-color: #fff; box-shadow: 0 0 0 4px rgba(38,34,98,.08); }

    /* Buttons */
    .vc-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; margin-top: 22px; }
    .vc-actions-end { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
    .vc-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 18px; border: 1px solid transparent; border-radius: 12px; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: background .15s ease, border-color .15s ease; }
    .vc-btn--sm { padding: 9px 14px; font-size: 13px; }
    .vc-btn--primary { background: var(--navy); color: #fff; }
    .vc-btn--primary:hover { background: var(--navy-deep); }
    .vc-btn--line { border-color: var(--navy); background: #fff; color: var(--navy); }
    .vc-btn--line:hover { background: var(--tint); }
    .vc-btn--danger { --ic-accent: #C2414B; border-color: #F2C6CA; background: #fff; color: #B03541; }
    .vc-btn--danger:hover { background: #FFF4F4; }

    /* Switch */
    .vc-switch-row { display: inline-flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 500; cursor: pointer; }
    .vc-switch { position: relative; width: 42px; height: 24px; flex: none; margin: 0; border-radius: 999px; outline: none; background: #D8D6EF; cursor: pointer; appearance: none; -webkit-appearance: none; transition: background .18s ease; }
    .vc-switch::after { position: absolute; top: 3px; right: 3px; width: 18px; height: 18px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(33,31,53,.3); content: ""; transition: transform .18s ease; }
    .vc-switch:checked { background: var(--navy); }
    .vc-switch:checked::after { transform: translateX(-18px); }
    .vc-switch:focus-visible { outline: 3px solid rgba(255,165,26,.85); outline-offset: 2px; }

    /* Pills */
    .vc-pill { display: inline-flex; max-width: 100%; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: var(--tint); color: var(--navy); font-size: 12px; font-weight: 600; white-space: nowrap; }
    .vc-pill > span { overflow: hidden; text-overflow: ellipsis; }
    .vc-pill--ok::before, .vc-pill--off::before { width: 6px; height: 6px; flex: none; border-radius: 50%; background: currentColor; content: ""; }
    .vc-pill--ok { background: #ECF8F2; color: #1F7A52; }
    .vc-pill--off { background: #F3F2F8; color: var(--muted); }
    .vc-pill--rel { background: #fff; border: 1px solid var(--line); color: #4B4962; font-weight: 500; }
    .vc-pill--rel em { color: var(--muted); font-style: normal; }

    /* Toolbar */
    .vc-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: -6px; }
    .vc-bar p { margin: 0; color: var(--muted); font-size: 13px; }
    .vc-bar-stats { display: flex; flex-wrap: wrap; gap: 8px; }
    .vc-bulk { display:flex; flex-wrap:wrap; align-items:center; gap:10px; margin-bottom:18px; padding:12px 14px; border:1px solid var(--line); border-radius:14px; background:#fff; }
    .vc-bulk-count { margin-inline-end:auto; color:var(--muted); font-size:13px; }
    .vc-check { width:18px; height:18px; flex:none; accent-color:var(--orange); }
    .vc-row-check { display:grid; width:32px; height:32px; place-items:center; border-radius:10px; background:#fff; border:1px solid var(--line); }

    /* Collapsible blocks */
    .vc-fold > summary { display: flex; align-items: center; gap: 14px; list-style: none; cursor: pointer; }
    .vc-fold > summary::-webkit-details-marker { display: none; }
    .vc-caret { margin-inline-start: 4px; color: var(--muted); transition: transform .18s ease; }
    .vc-fold[open] > summary .vc-caret { transform: rotate(180deg); }

    .vc-panel { padding: 18px 22px; }
    .vc-panel > summary { font-size: 16px; font-weight: 700; }
    .vc-panel > summary .vc-caret { margin-inline-start: auto; }
    .vc-body { margin-top: 18px; padding-top: 22px; border-top: 1px solid var(--line); }
    .vc-note { margin: 0; color: var(--muted); font-size: 14px; line-height: 1.8; }

    /* Rows */
    .vc-list { display: grid; gap: 10px; }
    .vc-item { transition: border-color .15s ease; }
    .vc-item:hover, .vc-item[open] { border-color: #D8D6EF; }
    .vc-item > summary { padding: 14px 18px; }
    .vc-letter { font-size: 17px; font-weight: 700; }
    .vc-item-title { min-width: 0; flex: 1; }
    .vc-word { display: block; width: fit-content; max-width: 100%; overflow: hidden; font-size: 16px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
    .vc-meaning { display: block; overflow: hidden; margin-top: 2px; color: var(--muted); font-size: 13px; text-overflow: ellipsis; white-space: nowrap; }
    .vc-item-meta { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 8px; }
    .vc-item-body { margin: 0 18px; padding: 20px 0 18px; border-top: 1px solid var(--line); }

    /* Empty */
    .vc-empty { display: grid; place-items: center; gap: 10px; padding: 48px 20px; color: var(--muted); font-size: 14px; text-align: center; }
    .vc-empty .vc-chip { width: 52px; height: 52px; border-radius: 16px; }

    .vc a:focus-visible, .vc button:focus-visible, .vc summary:focus-visible { outline: 3px solid rgba(255,165,26,.85); outline-offset: 2px; }

    @media (max-width: 700px) {
        .vc-grid { grid-template-columns: 1fr; }
        .vc-item > summary { flex-wrap: wrap; }
        .vc-item-title { flex-basis: calc(100% - 120px); }
        .vc-item-meta { order: 3; width: 100%; justify-content: flex-start; }
        .vc-actions, .vc-actions-end { width: 100%; }
        .vc-actions-end .vc-btn { flex: 1; }
    }
    @media (prefers-reduced-motion: reduce) { .vc * { transition-duration: .01ms !important; } }
</style>

<!-- Icons (self-contained, duotone) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <symbol id="vc-book" viewBox="0 0 24 24">
            <path d="M12 6.5C10.3 5.2 8 4.7 4.5 5v12.5c3.5-.3 5.8.2 7.5 1.5Z" style="fill:var(--ic-accent);opacity:.35"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6.5C10.3 5.2 8 4.7 4.5 5v12.5c3.5-.3 5.8.2 7.5 1.5 1.7-1.3 4-1.8 7.5-1.5V5C16 4.7 13.7 5.2 12 6.5ZM12 6.5V19"/></g>
        </symbol>
        <symbol id="vc-plus" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></g>
        </symbol>
        <symbol id="vc-check" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></g>
        </symbol>
        <symbol id="vc-trash" viewBox="0 0 24 24">
            <path d="M6.5 7h11l-.8 11.2a2 2 0 0 1-2 1.8H9.3a2 2 0 0 1-2-1.8Z" style="fill:var(--ic-accent);opacity:.25"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 7h15M9.5 7V5a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v2M6.5 7l.8 11.2a2 2 0 0 0 2 1.8h5.4a2 2 0 0 0 2-1.8L17.5 7M10 11v5M14 11v5"/></g>
        </symbol>
        <symbol id="vc-caret" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></g>
        </symbol>
    </defs>
</svg>

<section class="vc">

    <!-- ═════════ Header ═════════ -->
    <header class="vc-head">
        <span class="vc-chip" aria-hidden="true"><svg class="vc-ic vc-ic--lg"><use href="#vc-book"/></svg></span>
        <div>
            <h2>بنك المفردات</h2>
            <p>أضف الكلمة ومعناها والمرادف والضد ومثال الاستخدام.</p>
        </div>
    </header>

    <!-- ═════════ Add ═════════ -->
    <details class="vc-card vc-fold vc-panel">
        <summary>
            <span class="vc-chip" aria-hidden="true"><svg class="vc-ic"><use href="#vc-plus"/></svg></span>
            <span>إضافة مفردة</span>
            <svg class="vc-ic vc-caret" aria-hidden="true"><use href="#vc-caret"/></svg>
        </summary>

        <form method="post" action="<?= e(url('/admin/vocab')) ?>" class="vc-body">
            <?= csrfField() ?><input type="hidden" name="action" value="save">

            <div class="vc-grid">
                <label>
                    <span class="vc-label">الكلمة (English)</span>
                    <input name="word" required maxlength="255" dir="ltr" class="vc-input">
                </label>
                <label>
                    <span class="vc-label">المعنى بالعربية</span>
                    <input name="meaning_ar" required class="vc-input">
                </label>
                <label>
                    <span class="vc-label">مرادف</span>
                    <input name="synonym" maxlength="255" dir="ltr" class="vc-input">
                </label>
                <label>
                    <span class="vc-label">ضد</span>
                    <input name="antonym" maxlength="255" dir="ltr" class="vc-input">
                </label>
                <label class="vc-span">
                    <span class="vc-label">مثال</span>
                    <textarea name="example" rows="2" dir="ltr" class="vc-input"></textarea>
                </label>
            </div>

            <div class="vc-actions">
                <label class="vc-switch-row"><input type="checkbox" name="is_active" checked class="vc-switch"><span>نشطة للطلاب</span></label>
                <div class="vc-actions-end">
                    <button type="submit" class="vc-btn vc-btn--primary"><svg class="vc-ic vc-ic--sm" aria-hidden="true"><use href="#vc-plus"/></svg>إضافة</button>
                </div>
            </div>
        </form>
    </details>

    <!-- ═════════ Summary bar ═════════ -->
    <?php if ($items): ?>
        <div class="vc-bar">
            <p>المفردات المعروضة في هذه الصفحة</p>
            <div class="vc-bar-stats">
                <span class="vc-pill"><span><?= e((string) $totalWords) ?> مفردة</span></span>
                <span class="vc-pill vc-pill--ok"><span><?= e((string) $activeWords) ?> نشطة</span></span>
                <?php if ($stoppedWords > 0): ?><span class="vc-pill vc-pill--off"><span><?= e((string) $stoppedWords) ?> متوقفة</span></span><?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <form class="vc-bulk" method="post" action="<?= e(url('/admin/vocab')) ?>" id="vocab-bulk-form" onsubmit="return vocabConfirmBulk(this)">
        <?= csrfField() ?>
        <label class="vc-switch-row"><input type="checkbox" id="vocab-select-all" class="vc-check"><span>تحديد الكل في الصفحة</span></label>
        <span class="vc-bulk-count"><b id="vocab-selected-count">0</b> محددة</span>
        <button class="vc-btn vc-btn--sm vc-btn--line" type="submit" name="action" value="bulk_activate">تفعيل</button>
        <button class="vc-btn vc-btn--sm vc-btn--line" type="submit" name="action" value="bulk_deactivate">إيقاف</button>
        <button class="vc-btn vc-btn--sm vc-btn--danger" type="submit" name="action" value="bulk_delete">حذف المحدد</button>
    </form>

    <!-- ═════════ List ═════════ -->
    <div class="vc-list">
        <?php foreach ($items as $word): ?>
            <?php
                $wid = (string) $word['id'];
                $syn = trim((string) ($word['synonym'] ?? ''));
                $ant = trim((string) ($word['antonym'] ?? ''));
            ?>
            <details class="vc-card vc-fold vc-item">
                <summary>
                    <span class="vc-row-check" onclick="event.stopPropagation()" onmousedown="event.stopPropagation()">
                        <input class="vc-check vocab-row-check" type="checkbox" form="vocab-bulk-form" name="ids[]" value="<?= e($wid) ?>" aria-label="تحديد <?= e($word['word']) ?>" onclick="event.stopPropagation()">
                    </span>
                    <span class="vc-chip vc-letter" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($word['word'], 0, 1, 'UTF-8'), 'UTF-8')) ?></span>
                    <div class="vc-item-title">
                        <strong class="vc-word" dir="ltr"><?= e($word['word']) ?></strong>
                        <span class="vc-meaning"><?= e($word['meaning_ar']) ?></span>
                    </div>
                    <div class="vc-item-meta">
                        <?php if ($syn !== ''): ?><span class="vc-pill vc-pill--rel"><em>مرادف</em><span dir="ltr"><?= e($syn) ?></span></span><?php endif; ?>
                        <?php if ($ant !== ''): ?><span class="vc-pill vc-pill--rel"><em>ضد</em><span dir="ltr"><?= e($ant) ?></span></span><?php endif; ?>
                        <span class="vc-pill <?= $word['is_active'] ? 'vc-pill--ok' : 'vc-pill--off' ?>"><span><?= $word['is_active'] ? 'نشطة' : 'متوقفة' ?></span></span>
                    </div>
                    <svg class="vc-ic vc-caret" aria-hidden="true"><use href="#vc-caret"/></svg>
                </summary>

                <div class="vc-item-body">
                    <form method="post" action="<?= e(url('/admin/vocab')) ?>">
                        <?= csrfField() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e($wid) ?>">

                        <div class="vc-grid">
                            <label>
                                <span class="vc-label">الكلمة</span>
                                <input name="word" required maxlength="255" dir="ltr" value="<?= e($word['word']) ?>" class="vc-input">
                            </label>
                            <label>
                                <span class="vc-label">المعنى العربي</span>
                                <input name="meaning_ar" value="<?= e($word['meaning_ar']) ?>" class="vc-input" placeholder="يُستخرج تلقائيًا عند الحفظ إذا كان فارغًا">
                            </label>
                            <label>
                                <span class="vc-label">مرادف</span>
                                <input name="synonym" maxlength="255" dir="ltr" value="<?= e($word['synonym'] ?? '') ?>" class="vc-input">
                            </label>
                            <label>
                                <span class="vc-label">ضد</span>
                                <input name="antonym" maxlength="255" dir="ltr" value="<?= e($word['antonym'] ?? '') ?>" class="vc-input">
                            </label>
                            <label class="vc-span">
                                <span class="vc-label">مثال</span>
                                <textarea name="example" rows="2" dir="ltr" class="vc-input"><?= e($word['example'] ?? '') ?></textarea>
                            </label>
                        </div>

                        <div class="vc-actions">
                            <label class="vc-switch-row"><input type="checkbox" name="is_active" <?= $word['is_active'] ? 'checked' : '' ?> class="vc-switch"><span>نشطة للطلاب</span></label>
                            <div class="vc-actions-end">
                                <button type="submit" form="delete-word-<?= e($wid) ?>" class="vc-btn vc-btn--danger vc-btn--sm"><svg class="vc-ic vc-ic--sm" aria-hidden="true"><use href="#vc-trash"/></svg>حذف</button>
                                <button type="submit" class="vc-btn vc-btn--primary vc-btn--sm"><svg class="vc-ic vc-ic--sm" aria-hidden="true"><use href="#vc-check"/></svg>حفظ التعديلات</button>
                            </div>
                        </div>
                    </form>

                    <form id="delete-word-<?= e($wid) ?>" method="post" action="<?= e(url('/admin/vocab')) ?>" hidden onsubmit="return confirm('حذف المفردة؟');">
                        <?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($wid) ?>">
                    </form>
                </div>
            </details>
        <?php endforeach; ?>

        <?php if (!$items): ?>
            <div class="vc-card vc-empty">
                <span class="vc-chip" aria-hidden="true"><svg class="vc-ic vc-ic--lg"><use href="#vc-book"/></svg></span>
                <p>بنك المفردات فارغ حالياً.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/_pagination.php'; ?>
</section>

<script>
(() => {
    const form = document.getElementById('vocab-bulk-form');
    const selectAll = document.getElementById('vocab-select-all');
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    form.appendChild(actionInput);
    const rows = () => Array.from(document.querySelectorAll('.vocab-row-check'));
    const update = () => {
        const selected = rows().filter((checkbox) => checkbox.checked).length;
        document.getElementById('vocab-selected-count').textContent = String(selected);
        selectAll.checked = rows().length > 0 && selected === rows().length;
        selectAll.indeterminate = selected > 0 && selected < rows().length;
    };
    selectAll.addEventListener('change', () => {
        const shouldSelect = selectAll.checked;
        rows().forEach((checkbox) => {
            checkbox.checked = shouldSelect;
        });
        update();
    });
    rows().forEach((checkbox) => checkbox.addEventListener('change', update));
    form.addEventListener('submit', (event) => {
        const submitter = event.submitter;
        actionInput.value = submitter?.value || '';
        form.querySelectorAll('input[name="ids[]"]').forEach((input) => input.remove());
        rows().filter((checkbox) => checkbox.checked).forEach((checkbox) => {
            const hiddenId = document.createElement('input');
            hiddenId.type = 'hidden';
            hiddenId.name = 'ids[]';
            hiddenId.value = checkbox.value;
            form.appendChild(hiddenId);
        });
    });
    update();
})();
function vocabConfirmBulk(form) {
    const count = document.querySelectorAll('.vocab-row-check:checked').length;
    if (!count) { alert('اختر مفردة واحدة على الأقل.'); return false; }
    const action = document.activeElement?.value;
    return action !== 'bulk_delete' || confirm(`حذف ${count} مفردة محددة نهائياً؟`);
}
</script>