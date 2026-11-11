<?php
/* ── Data prep (view-only) ───────────────────────────────────── */
$groups = [];
$groupTitles = [];
$totalPassages = 0;
$withAudio = 0;
$withText = 0;
foreach ($items as $passage) {
    $pid = (int) $passage['practice_id'];
    $groups[$pid][] = $passage;
    $groupTitles[$pid] = $passage['practice_title'];
    $totalPassages++;
    if ($passage['audio_path']) { $withAudio++; }
    if (trim((string) ($passage['body'] ?? '')) !== '') { $withText++; }
}

$audioAccept = '.mp3,.wav,.m4a,.ogg,audio/mpeg,audio/wav,audio/mp4,audio/ogg';
?>

<style>
    .ps { --navy: #262262; --navy-deep: #1C194B; --orange: #FFA51A; --ink: #211F35; --muted: #706E83; --line: #E8E7F0; --tint: #F1F0FA; --ic-accent: #FFA51A; display: grid; gap: 22px; color: var(--ink); }
    .ps *, .ps *::before, .ps *::after { box-sizing: border-box; }

    /* Icons */
    .ps-ic { display: block; width: 20px; height: 20px; flex: none; }
    .ps-ic--sm { width: 16px; height: 16px; }
    .ps-ic--lg { width: 24px; height: 24px; }

    /* Surfaces */
    .ps-card { background: #fff; border: 1px solid var(--line); border-radius: 18px; box-shadow: 0 1px 2px rgba(38,34,98,.04); }
    .ps-chip { display: grid; width: 44px; height: 44px; flex: none; place-items: center; border-radius: 13px; background: var(--tint); color: var(--navy); }

    /* Header */
    .ps-head { display: flex; align-items: center; gap: 14px; }
    .ps-head h2 { margin: 0; font-size: 24px; font-weight: 700; line-height: 1.35; }
    .ps-head p { max-width: 62ch; margin: 3px 0 0; color: var(--muted); font-size: 14px; line-height: 1.8; }

    /* Fields */
    .ps-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
    .ps-span { grid-column: 1 / -1; }
    .ps-label { display: block; margin-bottom: 7px; color: #37354B; font-size: 13px; font-weight: 600; }
    .ps-hint { margin: 7px 0 0; color: var(--muted); font-size: 12px; }
    .ps-input { width: 100%; height: 46px; padding: 0 14px; border: 1px solid var(--line); border-radius: 12px; outline: none; background-color: #FCFCFE; color: var(--ink); font: inherit; font-size: 14px; transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease; }
    textarea.ps-input { height: auto; min-height: 180px; padding: 12px 14px; line-height: 1.85; resize: vertical; }
    .ps-input::placeholder { color: #A5A3B2; }
    .ps-input:hover { border-color: #C9C7D9; }
    .ps-input:focus { border-color: var(--navy); background-color: #fff; box-shadow: 0 0 0 4px rgba(38,34,98,.08); }
    select.ps-input { padding-left: 38px; cursor: pointer; appearance: none; -webkit-appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23706E83' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: left 13px center; }

    /* File picker */
    .ps-file { display: flex; align-items: center; gap: 14px; padding: 12px 14px; border: 1.5px dashed #CFCDE0; border-radius: 14px; background: #FCFCFE; transition: border-color .15s ease, background .15s ease; }
    .ps-file:hover, .ps-file:focus-within { border-color: var(--navy); background: #fff; }
    .ps-file input[type="file"] { min-width: 0; flex: 1; color: var(--muted); font: inherit; font-size: 13px; cursor: pointer; }
    .ps-file input[type="file"]::file-selector-button { margin-inline-end: 12px; padding: 8px 14px; border: 0; border-radius: 10px; background: var(--tint); color: var(--navy); font: inherit; font-size: 13px; font-weight: 600; cursor: pointer; transition: background .15s ease; }
    .ps-file input[type="file"]::file-selector-button:hover { background: #E4E2F6; }

    /* Current audio */
    .ps-audio { display: flex; flex-wrap: wrap; align-items: center; gap: 14px; padding: 14px 16px; border: 1px solid var(--line); border-radius: 14px; background: #FAFAFD; }
    .ps-audio-info { min-width: 150px; }
    .ps-audio-info strong { display: block; font-size: 14px; }
    .ps-audio-info span { display: block; margin-top: 2px; color: var(--muted); font-size: 12px; }
    .ps-audio audio { height: 40px; min-width: 220px; flex: 1; }
    .ps-link { display: inline-flex; align-items: center; gap: 6px; color: var(--navy); font-size: 13px; font-weight: 600; text-decoration: none; }
    .ps-link:hover { text-decoration: underline; }

    /* Buttons */
    .ps-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 10px; margin-top: 22px; }
    .ps-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 18px; border: 1px solid transparent; border-radius: 12px; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: background .15s ease, border-color .15s ease; }
    .ps-btn--sm { padding: 9px 14px; font-size: 13px; }
    .ps-btn--primary { background: var(--navy); color: #fff; }
    .ps-btn--primary:hover { background: var(--navy-deep); }
    .ps-btn--danger { --ic-accent: #C2414B; border-color: #F2C6CA; background: #fff; color: #B03541; }
    .ps-btn--danger:hover { background: #FFF4F4; }

    /* Pills */
    .ps-pill { display: inline-flex; max-width: 100%; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: var(--tint); color: var(--navy); font-size: 12px; font-weight: 600; white-space: nowrap; }
    .ps-pill > span { overflow: hidden; text-overflow: ellipsis; }
    .ps-pill--ok { background: #ECF8F2; color: #1F7A52; }
    .ps-pill--off { background: #F3F2F8; color: var(--muted); }

    /* Toolbar */
    .ps-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: -6px; }
    .ps-bar p { margin: 0; color: var(--muted); font-size: 13px; }
    .ps-bar-stats { display: flex; flex-wrap: wrap; gap: 8px; }

    /* Collapsible blocks */
    .ps-fold > summary { display: flex; align-items: center; gap: 14px; list-style: none; cursor: pointer; }
    .ps-fold > summary::-webkit-details-marker { display: none; }
    .ps-caret { margin-inline-start: 4px; color: var(--muted); transition: transform .18s ease; }
    .ps-fold[open] > summary .ps-caret { transform: rotate(180deg); }

    .ps-add { padding: 18px 22px; }
    .ps-add > summary { font-size: 16px; font-weight: 700; }
    .ps-add > summary .ps-caret { margin-inline-start: auto; }
    .ps-body { margin-top: 18px; padding-top: 22px; border-top: 1px solid var(--line); }

    /* Groups + rows */
    .ps-list { display: grid; gap: 26px; }
    .ps-group-head { display: flex; align-items: center; gap: 10px; margin: 0 0 12px; }
    .ps-group-head h3 { margin: 0; font-size: 16px; font-weight: 700; }
    .ps-rows { display: grid; gap: 10px; }

    .ps-item { transition: border-color .15s ease; }
    .ps-item:hover, .ps-item[open] { border-color: #D8D6EF; }
    .ps-item > summary { padding: 14px 18px; }
    .ps-item-title { min-width: 0; flex: 1; }
    .ps-item-title strong { display: block; overflow: hidden; font-size: 15px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
    .ps-item-meta { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 8px; }
    .ps-item-body { margin: 0 18px; padding: 20px 0 18px; border-top: 1px solid var(--line); }

    /* Empty */
    .ps-empty { display: grid; place-items: center; gap: 10px; padding: 48px 20px; color: var(--muted); font-size: 14px; text-align: center; }
    .ps-empty .ps-chip { width: 52px; height: 52px; border-radius: 16px; }

    .ps a:focus-visible, .ps button:focus-visible, .ps summary:focus-visible { outline: 3px solid rgba(255,165,26,.85); outline-offset: 2px; }

    @media (max-width: 700px) {
        .ps-grid { grid-template-columns: 1fr; }
        .ps-item > summary { flex-wrap: wrap; }
        .ps-item-title { flex-basis: calc(100% - 120px); }
        .ps-item-meta { order: 3; width: 100%; justify-content: flex-start; }
        .ps-actions { justify-content: stretch; }
        .ps-actions .ps-btn { flex: 1; }
        .ps-audio audio { min-width: 100%; }
    }
    @media (prefers-reduced-motion: reduce) { .ps * { transition-duration: .01ms !important; } }
</style>

<!-- Icons (self-contained, duotone) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <symbol id="ps-headset" viewBox="0 0 24 24">
            <rect x="3.5" y="13" width="4" height="6.5" rx="1.8" style="fill:var(--ic-accent);opacity:.4"/>
            <rect x="16.5" y="13" width="4" height="6.5" rx="1.8" style="fill:var(--ic-accent);opacity:.4"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="3.5" y="13" width="4" height="6.5" rx="1.8"/><rect x="16.5" y="13" width="4" height="6.5" rx="1.8"/><path d="M18.5 19.5c0 1.3-1.6 2-4 2h-1"/></g>
        </symbol>
        <symbol id="ps-book" viewBox="0 0 24 24">
            <path d="M12 6.5C10.3 5.2 8 4.7 4.5 5v12.5c3.5-.3 5.8.2 7.5 1.5Z" style="fill:var(--ic-accent);opacity:.35"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6.5C10.3 5.2 8 4.7 4.5 5v12.5c3.5-.3 5.8.2 7.5 1.5 1.7-1.3 4-1.8 7.5-1.5V5C16 4.7 13.7 5.2 12 6.5ZM12 6.5V19"/></g>
        </symbol>
        <symbol id="ps-target" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5.2"/></g>
            <circle cx="12" cy="12" r="2.2" style="fill:var(--ic-accent)"/>
        </symbol>
        <symbol id="ps-plus" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></g>
        </symbol>
        <symbol id="ps-check" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></g>
        </symbol>
        <symbol id="ps-trash" viewBox="0 0 24 24">
            <path d="M6.5 7h11l-.8 11.2a2 2 0 0 1-2 1.8H9.3a2 2 0 0 1-2-1.8Z" style="fill:var(--ic-accent);opacity:.25"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 7h15M9.5 7V5a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v2M6.5 7l.8 11.2a2 2 0 0 0 2 1.8h5.4a2 2 0 0 0 2-1.8L17.5 7M10 11v5M14 11v5"/></g>
        </symbol>
        <symbol id="ps-caret" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></g>
        </symbol>
        <symbol id="ps-upload" viewBox="0 0 24 24">
            <path d="M5 15v3a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3Z" style="fill:var(--ic-accent);opacity:.3"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15V4M7.5 8.5 12 4l4.5 4.5M5 15v3a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3"/></g>
        </symbol>
        <symbol id="ps-external" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h6v6M20 4l-8 8M18 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4"/></g>
        </symbol>
    </defs>
</svg>

<section class="ps">

    <!-- ═════════ Header ═════════ -->
    <header class="ps-head">
        <span class="ps-chip" aria-hidden="true"><svg class="ps-ic ps-ic--lg"><use href="#ps-headset"/></svg></span>
        <div>
            <h2>المقاطع الصوتية والنصوص</h2>
            <p>اربط المقطع بتدريب القراءة أو الاستماع. الحد الأقصى للملف الصوتي 10 MB.</p>
        </div>
    </header>

    <!-- ═════════ Add ═════════ -->
    <details class="ps-card ps-fold ps-add">
        <summary>
            <span class="ps-chip" aria-hidden="true"><svg class="ps-ic"><use href="#ps-plus"/></svg></span>
            <span>إضافة مقطع</span>
            <svg class="ps-ic ps-caret" aria-hidden="true"><use href="#ps-caret"/></svg>
        </summary>

        <form method="post" enctype="multipart/form-data" action="<?= e(url('/admin/passages')) ?>" class="ps-body">
            <?= csrfField() ?><input type="hidden" name="action" value="save">

            <div class="ps-grid">
                <label>
                    <span class="ps-label">التدريب</span>
                    <select name="practice_id" required class="ps-input"><?php foreach ($practices as $practice): ?><option value="<?= e((string) $practice['id']) ?>"><?= e($practice['title_ar']) ?></option><?php endforeach; ?></select>
                </label>
                <label>
                    <span class="ps-label">عنوان المقطع</span>
                    <input name="title" required maxlength="255" class="ps-input">
                </label>
                <label class="ps-span">
                    <span class="ps-label">نص القراءة (اختياري)</span>
                    <textarea name="body" rows="7" dir="ltr" class="ps-input"></textarea>
                </label>
                <div class="ps-span">
                    <span class="ps-label">ملف الصوت (اختياري)</span>
                    <label class="ps-file">
                        <svg class="ps-ic" aria-hidden="true" style="color:var(--navy)"><use href="#ps-upload"/></svg>
                        <input name="audio" type="file" accept="<?= e($audioAccept) ?>">
                    </label>
                    <p class="ps-hint">الصيغ المدعومة: MP3 / WAV / M4A / OGG</p>
                </div>
            </div>

            <div class="ps-actions">
                <button type="submit" class="ps-btn ps-btn--primary"><svg class="ps-ic ps-ic--sm" aria-hidden="true"><use href="#ps-plus"/></svg>إضافة المقطع</button>
            </div>
        </form>
    </details>

    <!-- ═════════ Summary bar ═════════ -->
    <?php if ($items): ?>
        <div class="ps-bar">
            <p>المقاطع المعروضة في هذه الصفحة</p>
            <div class="ps-bar-stats">
                <span class="ps-pill"><span><?= e((string) $totalPassages) ?> مقطع</span></span>
                <span class="ps-pill"><svg class="ps-ic ps-ic--sm" aria-hidden="true"><use href="#ps-headset"/></svg><span><?= e((string) $withAudio) ?> بصوت</span></span>
                <span class="ps-pill"><svg class="ps-ic ps-ic--sm" aria-hidden="true"><use href="#ps-book"/></svg><span><?= e((string) $withText) ?> بنص</span></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- ═════════ List (grouped by practice) ═════════ -->
    <div class="ps-list">
        <?php foreach ($groups as $practiceId => $groupItems): ?>
            <section aria-label="<?= e($groupTitles[$practiceId]) ?>">
                <div class="ps-group-head">
                    <span class="ps-ic" aria-hidden="true" style="color:var(--navy)"><svg class="ps-ic"><use href="#ps-target"/></svg></span>
                    <h3><?= e($groupTitles[$practiceId]) ?></h3>
                    <span class="ps-pill"><span><?= e((string) count($groupItems)) ?></span></span>
                </div>

                <div class="ps-rows">
                    <?php foreach ($groupItems as $passage): ?>
                        <?php
                            $id = (string) $passage['id'];
                            $hasAudio = !empty($passage['audio_path']);
                            $hasText = trim((string) ($passage['body'] ?? '')) !== '';
                        ?>
                        <details class="ps-card ps-fold ps-item">
                            <summary>
                                <span class="ps-chip" aria-hidden="true"><svg class="ps-ic"><use href="#<?= $hasAudio ? 'ps-headset' : 'ps-book' ?>"/></svg></span>
                                <div class="ps-item-title"><strong dir="auto"><?= e($passage['title']) ?></strong></div>
                                <div class="ps-item-meta">
                                    <?php if ($hasAudio): ?><span class="ps-pill ps-pill--ok"><svg class="ps-ic ps-ic--sm" aria-hidden="true"><use href="#ps-headset"/></svg><span>يوجد ملف صوت</span></span><?php endif; ?>
                                    <?php if ($hasText): ?><span class="ps-pill"><svg class="ps-ic ps-ic--sm" aria-hidden="true"><use href="#ps-book"/></svg><span>نص قراءة</span></span><?php endif; ?>
                                    <?php if (!$hasAudio && !$hasText): ?><span class="ps-pill ps-pill--off"><span>بدون محتوى</span></span><?php endif; ?>
                                </div>
                                <svg class="ps-ic ps-caret" aria-hidden="true"><use href="#ps-caret"/></svg>
                            </summary>

                            <div class="ps-item-body">
                                <form method="post" enctype="multipart/form-data" action="<?= e(url('/admin/passages')) ?>">
                                    <?= csrfField() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e($id) ?>">

                                    <div class="ps-grid">
                                        <label>
                                            <span class="ps-label">التدريب</span>
                                            <select name="practice_id" required class="ps-input"><?php foreach ($practices as $practice): ?><option value="<?= e((string) $practice['id']) ?>" <?= (int) $passage['practice_id'] === (int) $practice['id'] ? 'selected' : '' ?>><?= e($practice['title_ar']) ?></option><?php endforeach; ?></select>
                                        </label>
                                        <label>
                                            <span class="ps-label">العنوان</span>
                                            <input name="title" required maxlength="255" value="<?= e($passage['title']) ?>" class="ps-input">
                                        </label>
                                        <label class="ps-span">
                                            <span class="ps-label">نص القراءة</span>
                                            <textarea name="body" rows="7" dir="ltr" class="ps-input"><?= e($passage['body'] ?? '') ?></textarea>
                                        </label>

                                        <?php if ($hasAudio): ?>
                                            <div class="ps-span ps-audio">
                                                <span class="ps-chip" aria-hidden="true"><svg class="ps-ic"><use href="#ps-headset"/></svg></span>
                                                <div class="ps-audio-info"><strong>الصوت الحالي</strong><span>ارفع ملفاً جديداً لاستبداله.</span></div>
                                                <audio controls preload="none" src="<?= e(asset($passage['audio_path'])) ?>"></audio>
                                                <a class="ps-link" href="<?= e(asset($passage['audio_path'])) ?>" target="_blank" rel="noopener"><svg class="ps-ic ps-ic--sm" aria-hidden="true"><use href="#ps-external"/></svg>تشغيل الملف</a>
                                            </div>
                                        <?php endif; ?>

                                        <div class="ps-span">
                                            <span class="ps-label">استبدال الصوت</span>
                                            <label class="ps-file">
                                                <svg class="ps-ic" aria-hidden="true" style="color:var(--navy)"><use href="#ps-upload"/></svg>
                                                <input name="audio" type="file" accept="<?= e($audioAccept) ?>">
                                            </label>
                                        </div>
                                    </div>

                                    <div class="ps-actions">
                                        <button type="submit" form="delete-passage-<?= e($id) ?>" class="ps-btn ps-btn--danger ps-btn--sm"><svg class="ps-ic ps-ic--sm" aria-hidden="true"><use href="#ps-trash"/></svg>حذف المقطع</button>
                                        <button type="submit" class="ps-btn ps-btn--primary ps-btn--sm"><svg class="ps-ic ps-ic--sm" aria-hidden="true"><use href="#ps-check"/></svg>حفظ التعديلات</button>
                                    </div>
                                </form>

                                <form id="delete-passage-<?= e($id) ?>" method="post" action="<?= e(url('/admin/passages')) ?>" hidden onsubmit="return confirm('حذف هذا المقطع؟');">
                                    <?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($id) ?>">
                                </form>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>

        <?php if (!$items): ?>
            <div class="ps-card ps-empty">
                <span class="ps-chip" aria-hidden="true"><svg class="ps-ic ps-ic--lg"><use href="#ps-headset"/></svg></span>
                <p>لا توجد مقاطع بعد.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/_pagination.php'; ?>
</section>