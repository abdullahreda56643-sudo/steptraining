<?php
/* ── Data prep (view-only) ───────────────────────────────────── */
$totalStudents = count($items);
$activeStudents = 0;
foreach ($items as $row) {
    if ($row['is_active']) { $activeStudents++; }
}
$stoppedStudents = $totalStudents - $activeStudents;
$hasSearch = ($search !== '');
?>

<style>
    .us { --navy: #262262; --navy-deep: #1C194B; --orange: #FFA51A; --ink: #211F35; --muted: #706E83; --line: #E8E7F0; --tint: #F1F0FA; --ic-accent: #FFA51A; display: grid; gap: 22px; color: var(--ink); }
    .us *, .us *::before, .us *::after { box-sizing: border-box; }

    /* Icons */
    .us-ic { display: block; width: 20px; height: 20px; flex: none; }
    .us-ic--sm { width: 16px; height: 16px; }
    .us-ic--lg { width: 24px; height: 24px; }

    /* Surfaces */
    .us-card { background: #fff; border: 1px solid var(--line); border-radius: 18px; box-shadow: 0 1px 2px rgba(38,34,98,.04); }
    .us-chip { display: grid; width: 44px; height: 44px; flex: none; place-items: center; border-radius: 13px; background: var(--tint); color: var(--navy); }

    /* Header */
    .us-head { display: flex; align-items: center; gap: 14px; }
    .us-head h2 { margin: 0; font-size: 24px; font-weight: 700; line-height: 1.35; }
    .us-head p { max-width: 62ch; margin: 3px 0 0; color: var(--muted); font-size: 14px; line-height: 1.8; }

    /* Search */
    .us-search { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 16px; }
    .us-search-field { position: relative; min-width: 220px; flex: 1; }
    .us-search-field .us-ic { position: absolute; top: 50%; right: 14px; color: #9A98AD; pointer-events: none; transform: translateY(-50%); }

    /* Inputs */
    .us-input { width: 100%; height: 46px; padding: 0 14px; border: 1px solid var(--line); border-radius: 12px; outline: none; background-color: #FCFCFE; color: var(--ink); font: inherit; font-size: 14px; transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease; }
    .us-search-field .us-input { padding-right: 44px; }
    .us-input::placeholder { color: #A5A3B2; }
    .us-input:hover { border-color: #C9C7D9; }
    .us-input:focus { border-color: var(--navy); background-color: #fff; box-shadow: 0 0 0 4px rgba(38,34,98,.08); }

    /* Buttons */
    .us-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 18px; border: 1px solid transparent; border-radius: 12px; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; white-space: nowrap; cursor: pointer; transition: background .15s ease, border-color .15s ease; }
    .us-btn--sm { padding: 9px 14px; font-size: 13px; }
    .us-btn--primary { background: var(--navy); color: #fff; }
    .us-btn--primary:hover { background: var(--navy-deep); }
    .us-btn--line { border-color: var(--line); background: #fff; color: var(--navy); }
    .us-btn--line:hover { border-color: #C9C7D9; background: #F7F7FB; }
    .us-btn--soft { background: var(--tint); color: var(--navy); }
    .us-btn--soft:hover { background: #E4E2F6; }
    .us-btn--danger { --ic-accent: #C2414B; border-color: #F2C6CA; background: #fff; color: #B03541; }
    .us-btn--danger:hover { background: #FFF4F4; }

    /* Pills */
    .us-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: var(--tint); color: var(--navy); font-size: 12px; font-weight: 600; white-space: nowrap; }
    .us-pill--ok::before, .us-pill--off::before { width: 6px; height: 6px; flex: none; border-radius: 50%; background: currentColor; content: ""; }
    .us-pill--ok { background: #ECF8F2; color: #1F7A52; }
    .us-pill--off { background: #FFF0F1; color: #B03541; }

    /* Toolbar */
    .us-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: -6px; }
    .us-bar p { margin: 0; color: var(--muted); font-size: 13px; }
    .us-bar-stats { display: flex; flex-wrap: wrap; gap: 8px; }

    /* Students grid */
    .us-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; gap: 14px; }
    .us-student { overflow: hidden; transition: border-color .15s ease; }
    .us-student:hover { border-color: #D8D6EF; }
    .us-student-head { display: flex; align-items: center; gap: 12px; padding: 18px 18px 14px; }
    .us-avatar { display: grid; width: 46px; height: 46px; flex: none; place-items: center; border-radius: 14px; background: var(--tint); color: var(--navy); font-size: 17px; font-weight: 700; }
    .us-avatar.is-off { background: #F3F2F8; color: var(--muted); }
    .us-who { min-width: 0; flex: 1; }
    .us-who strong { display: block; overflow: hidden; font-size: 15px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
    .us-who span { display: block; overflow: hidden; margin-top: 2px; color: var(--muted); font-size: 13px; text-overflow: ellipsis; white-space: nowrap; }

    .us-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 0 18px; }
    .us-stat { padding: 12px 14px; border-radius: 14px; background: #FAFAFD; border: 1px solid var(--line); }
    .us-stat-label { display: flex; align-items: center; gap: 6px; margin: 0; color: var(--muted); font-size: 12px; }
    .us-stat-num { margin: 6px 0 0; color: var(--navy); font-size: 22px; font-weight: 700; line-height: 1.1; }
    .us-stat-num small { color: var(--muted); font-size: 12px; font-weight: 600; }
    .us-meter { height: 5px; margin-top: 9px; overflow: hidden; border-radius: 5px; background: var(--tint); }
    .us-meter > span { display: block; height: 100%; border-radius: 5px; background: var(--navy); }

    .us-toggle-row { display: flex; padding: 14px 18px 18px; }
    .us-toggle-row form { width: 100%; }
    .us-toggle-row .us-btn { width: 100%; }

    /* Reset password accordion */
    .us-fold { border-top: 1px solid var(--line); }
    .us-fold > summary { display: flex; align-items: center; gap: 10px; padding: 13px 18px; color: var(--navy); font-size: 14px; font-weight: 600; list-style: none; cursor: pointer; transition: background .15s ease; }
    .us-fold > summary::-webkit-details-marker { display: none; }
    .us-fold > summary:hover { background: #FAFAFD; }
    .us-caret { margin-inline-start: auto; color: var(--muted); transition: transform .18s ease; }
    .us-fold[open] > summary .us-caret { transform: rotate(180deg); }
    .us-fold-body { padding: 4px 18px 18px; }
    .us-pw { display: flex; flex-wrap: wrap; gap: 10px; }
    .us-pw-field { position: relative; min-width: 180px; flex: 1; }
    .us-pw-field .us-input { padding-left: 46px; }
    .us-eye { position: absolute; top: 50%; left: 6px; display: grid; width: 34px; height: 34px; place-items: center; border: 0; border-radius: 10px; background: transparent; color: var(--muted); cursor: pointer; transform: translateY(-50%); transition: background .15s ease, color .15s ease; }
    .us-eye:hover { background: var(--tint); color: var(--navy); }
    .us-eye .us-ic-hide { display: none; }
    .us-eye.is-visible .us-ic-show { display: none; }
    .us-eye.is-visible .us-ic-hide { display: block; }

    /* Empty */
    .us-empty { display: grid; grid-column: 1 / -1; place-items: center; gap: 10px; padding: 48px 20px; color: var(--muted); font-size: 14px; text-align: center; }
    .us-empty .us-chip { width: 52px; height: 52px; border-radius: 16px; }

    .us a:focus-visible, .us button:focus-visible, .us summary:focus-visible { outline: 3px solid rgba(255,165,26,.85); outline-offset: 2px; }

    @media (max-width: 1100px) { .us-list { grid-template-columns: 1fr; } }
    @media (max-width: 560px) {
        .us-search .us-btn { flex: 1; }
        .us-student-head { flex-wrap: wrap; }
        .us-who { flex-basis: calc(100% - 60px); }
        .us-pw .us-btn { width: 100%; }
    }
    @media (prefers-reduced-motion: reduce) { .us * { transition-duration: .01ms !important; } }
</style>

<!-- Icons (self-contained, duotone) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <symbol id="us-users" viewBox="0 0 24 24">
            <circle cx="9" cy="8.5" r="3.3" style="fill:var(--ic-accent);opacity:.4"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8.5" r="3.3"/><path d="M2.8 19.5c.6-3.2 3.1-4.9 6.2-4.9s5.6 1.7 6.2 4.9"/><circle cx="17" cy="9.5" r="2.6"/><path d="M16.6 14.8c2.4.1 4.1 1.6 4.7 4.2"/></g>
        </symbol>
        <symbol id="us-search" viewBox="0 0 24 24">
            <circle cx="10.5" cy="10.5" r="6.5" style="fill:var(--ic-accent);opacity:.25"/>
            <g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 4.5 4.5"/></g>
        </symbol>
        <symbol id="us-reset" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 12a7.5 7.5 0 1 0 2.4-5.5M4.5 4.5v4h4"/></g>
        </symbol>
        <symbol id="us-key" viewBox="0 0 24 24">
            <circle cx="8" cy="15" r="3.8" style="fill:var(--ic-accent);opacity:.35"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="15" r="3.8"/><path d="m10.8 12.2 8.2-8.2M16 7l2.5 2.5M13.5 9.5l2 2"/></g>
        </symbol>
        <symbol id="us-pause" viewBox="0 0 24 24">
            <g style="fill:var(--ic-accent);opacity:.3"><rect x="6" y="5" width="4" height="14" rx="1.2"/><rect x="14" y="5" width="4" height="14" rx="1.2"/></g>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"><rect x="6" y="5" width="4" height="14" rx="1.2"/><rect x="14" y="5" width="4" height="14" rx="1.2"/></g>
        </symbol>
        <symbol id="us-check" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></g>
        </symbol>
        <symbol id="us-done" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9" style="fill:var(--ic-accent);opacity:.25"/>
            <g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m8 12.5 2.7 2.7 5.3-5.4"/></g>
        </symbol>
        <symbol id="us-trend" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 19.5h17M5 15l4.5-4.5 3.2 3.2L19 7"/><path d="M15 7h4v4" style="stroke:var(--ic-accent)"/></g>
        </symbol>
        <symbol id="us-caret" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></g>
        </symbol>
        <symbol id="us-eye" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="1.6" style="fill:var(--ic-accent)"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3.2"/></g>
        </symbol>
        <symbol id="us-eye-off" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 3.5l17 17"/><path d="M6.5 7.2C4 8.9 2.5 12 2.5 12S6 18.5 12 18.5c1.5 0 2.8-.4 4-.9"/><path d="M9.9 5.7c.7-.1 1.4-.2 2.1-.2 6 0 9.5 6.5 9.5 6.5s-.8 1.5-2.2 3"/><path d="M9.9 9.9a3.2 3.2 0 0 0 4.2 4.2"/></g>
        </symbol>
    </defs>
</svg>

<section class="us">

    <!-- ═════════ Header ═════════ -->
    <header class="us-head">
        <span class="us-chip" aria-hidden="true"><svg class="us-ic us-ic--lg"><use href="#us-users"/></svg></span>
        <div>
            <h2>إدارة الطلاب</h2>
            <p>ابحث عن حساب طالب، غيّر حالة الدخول أو عيّن كلمة مرور جديدة.</p>
        </div>
    </header>

    <!-- ═════════ Search ═════════ -->
    <form method="get" action="<?= e(url('/admin/users')) ?>" class="us-card us-search">
        <div class="us-search-field">
            <svg class="us-ic" aria-hidden="true"><use href="#us-search"/></svg>
            <input type="search" name="q" value="<?= e($search) ?>" placeholder="الاسم أو البريد الإلكتروني" class="us-input" aria-label="بحث عن طالب">
        </div>
        <?php if ($hasSearch): ?>
            <a href="<?= e(url('/admin/users')) ?>" class="us-btn us-btn--line"><svg class="us-ic us-ic--sm" aria-hidden="true"><use href="#us-reset"/></svg>إعادة ضبط</a>
        <?php endif; ?>
        <button type="submit" class="us-btn us-btn--primary"><svg class="us-ic us-ic--sm" aria-hidden="true"><use href="#us-search"/></svg>بحث</button>
    </form>

    <!-- ═════════ Summary bar ═════════ -->
    <?php if ($items): ?>
        <div class="us-bar">
            <p>الطلاب المعروضون في هذه الصفحة<?= $hasSearch ? ' (نتائج البحث)' : '' ?></p>
            <div class="us-bar-stats">
                <span class="us-pill"><?= e((string) $totalStudents) ?> طالب</span>
                <span class="us-pill us-pill--ok"><?= e((string) $activeStudents) ?> نشط</span>
                <?php if ($stoppedStudents > 0): ?><span class="us-pill us-pill--off"><?= e((string) $stoppedStudents) ?> موقوف</span><?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- ═════════ Students ═════════ -->
    <div class="us-list">
        <?php foreach ($items as $student): ?>
            <?php
                $active = (bool) $student['is_active'];
                $avg = $student['average_score'] !== null ? max(0, min(100, (float) $student['average_score'])) : null;
            ?>
            <article class="us-card us-student">
                <div class="us-student-head">
                    <span class="us-avatar<?= $active ? '' : ' is-off' ?>" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($student['name'], 0, 1, 'UTF-8'), 'UTF-8')) ?></span>
                    <div class="us-who">
                        <strong><?= e($student['name']) ?></strong>
                        <span dir="ltr"><?= e($student['email']) ?></span>
                    </div>
                    <span class="us-pill <?= $active ? 'us-pill--ok' : 'us-pill--off' ?>"><?= $active ? 'نشط' : 'موقوف' ?></span>
                </div>

                <div class="us-stats">
                    <div class="us-stat">
                        <p class="us-stat-label"><svg class="us-ic us-ic--sm" aria-hidden="true" style="color:var(--navy)"><use href="#us-done"/></svg>التدريبات المكتملة</p>
                        <p class="us-stat-num"><?= e((string) $student['completed_attempts']) ?></p>
                    </div>
                    <div class="us-stat">
                        <p class="us-stat-label"><svg class="us-ic us-ic--sm" aria-hidden="true" style="color:var(--navy)"><use href="#us-trend"/></svg>متوسط الدرجة المتوقعة</p>
                        <?php if ($avg !== null): ?>
                            <p class="us-stat-num"><?= e((string) $student['average_score']) ?><small>/100</small></p>
                            <div class="us-meter" aria-hidden="true"><span style="width: <?= e((string) round($avg)) ?>%"></span></div>
                        <?php else: ?>
                            <p class="us-stat-num">—</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="us-toggle-row">
                    <form method="post" action="<?= e(url('/admin/users')) ?>">
                        <?= csrfField() ?><input type="hidden" name="id" value="<?= e((string) $student['id']) ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="is_active" value="<?= e((string) $student['is_active']) ?>">
                        <?php if ($active): ?>
                            <button type="submit" class="us-btn us-btn--danger us-btn--sm"><svg class="us-ic us-ic--sm" aria-hidden="true"><use href="#us-pause"/></svg>إيقاف الحساب</button>
                        <?php else: ?>
                            <button type="submit" class="us-btn us-btn--soft us-btn--sm"><svg class="us-ic us-ic--sm" aria-hidden="true"><use href="#us-check"/></svg>تفعيل الحساب</button>
                        <?php endif; ?>
                    </form>
                </div>

                <details class="us-fold">
                    <summary>
                        <svg class="us-ic us-ic--sm" aria-hidden="true"><use href="#us-key"/></svg>
                        <span>تعيين كلمة مرور جديدة</span>
                        <svg class="us-ic us-ic--sm us-caret" aria-hidden="true"><use href="#us-caret"/></svg>
                    </summary>
                    <div class="us-fold-body">
                        <form method="post" action="<?= e(url('/admin/users')) ?>" class="us-pw">
                            <?= csrfField() ?><input type="hidden" name="id" value="<?= e((string) $student['id']) ?>"><input type="hidden" name="action" value="reset_password">
                            <div class="us-pw-field">
                                <input type="password" name="password" minlength="8" maxlength="200" required autocomplete="new-password" placeholder="8 أحرف على الأقل" class="us-input" aria-label="كلمة المرور الجديدة">
                                <button type="button" class="us-eye" data-pw-toggle aria-label="إظهار كلمة المرور" aria-pressed="false">
                                    <svg class="us-ic us-ic-show" aria-hidden="true"><use href="#us-eye"/></svg>
                                    <svg class="us-ic us-ic-hide" aria-hidden="true"><use href="#us-eye-off"/></svg>
                                </button>
                            </div>
                            <button type="submit" class="us-btn us-btn--primary"><svg class="us-ic us-ic--sm" aria-hidden="true"><use href="#us-check"/></svg>حفظ كلمة المرور</button>
                        </form>
                    </div>
                </details>
            </article>
        <?php endforeach; ?>

        <?php if (!$items): ?>
            <div class="us-card us-empty">
                <span class="us-chip" aria-hidden="true"><svg class="us-ic us-ic--lg"><use href="#us-users"/></svg></span>
                <p>لا يوجد طلاب مطابقون للبحث.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/_pagination.php'; ?>
</section>

<script>
    document.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = btn.parentNode.querySelector('input');
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.classList.toggle('is-visible', show);
            btn.setAttribute('aria-label', show ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
        });
    });
</script>