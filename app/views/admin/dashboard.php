<?php
$attemptTypes = [
    'practice' => 'تدريب',
    'mistakes' => 'مراجعة الأخطاء',
    'custom' => 'تدريب مخصص',
    'placement' => 'تحديد المستوى',
    'daily' => 'التحدي اليومي',
];

$maxActivity = 0;
foreach ($weeklyActivity as $day) {
    $maxActivity = max($maxActivity, (int) $day['count']);
}
$weekTotal = (int) array_sum(array_column($weeklyActivity, 'count'));
$weekAvg   = round($weekTotal / max(1, count($weeklyActivity)), 1);

$pct = function ($part, $whole) {
    return (int) $whole > 0 ? max(0, min(100, (int) round(((int) $part / (int) $whole) * 100))) : 0;
};
$avgScore = $stats['average_score'] !== null ? max(0, min(100, (float) $stats['average_score'])) : 0;

$kpis = [
    ['label' => 'الطلاب',              'value' => (string) $stats['students'],           'note' => $stats['active_students'] . ' حساب نشط',                'icon' => 'a-users'],
    ['label' => 'التدريبات المكتملة',  'value' => (string) $stats['completed_attempts'], 'note' => $stats['completed_today'] . ' مكتمل اليوم',              'icon' => 'a-check-circle'],
    ['label' => 'الأسئلة النشطة',      'value' => (string) $stats['active_questions'],   'note' => 'من أصل ' . $stats['questions'] . ' سؤال',               'icon' => 'a-help'],
    ['label' => 'متوسط الدرجة المتوقعة', 'value' => $stats['average_score'] !== null ? (string) $stats['average_score'] : '—', 'suffix' => '/100', 'note' => $stats['active_today'] . ' طالباً بدأوا تدريباً اليوم', 'icon' => 'd-trend', 'progress' => $avgScore],
];

$content = [
    ['/admin/practices', 'التدريبات',      'a-target',  (string) $stats['active_practices'],   'نشط',  'من أصل ' . $stats['practices'],    $pct($stats['active_practices'], $stats['practices'])],
    ['/admin/passages',  'المقاطع والصوت', 'a-headset', (string) $stats['passages'],           '',     'مادة تعليمية',                      null],
    ['/admin/vocab',     'المفردات',       'a-book',    (string) $stats['active_vocabulary'],  'نشطة', 'من أصل ' . $stats['vocabulary'],    $pct($stats['active_vocabulary'], $stats['vocabulary'])],
    ['/admin/skills',    'المهارات',       'a-bulb',    (string) $stats['skills'],             '',     'مهارة مسجلة',                       null],
];

$quick = [
    ['/admin/questions', 'الأسئلة',   'a-help',    (string) $stats['questions']],
    ['/admin/users',     'الطلاب',    'a-users',   (string) $stats['students']],
    ['/admin/practices', 'التدريبات', 'a-target',  (string) $stats['practices']],
    ['/admin/settings',  'الإعدادات', 'a-sliders', null],
];
?>

<!-- Extra duotone icons for the dashboard (the rest come from the layout sprite) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <symbol id="d-trend" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 19.5h17M5 15l4.5-4.5 3.2 3.2L19 7"/><path d="M15 7h4v4" style="stroke:var(--ic-accent)"/></g>
        </symbol>
        <symbol id="d-arrow" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></g>
        </symbol>
        <symbol id="d-clock" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9" style="fill:var(--ic-accent);opacity:.25"/>
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></g>
        </symbol>
        <symbol id="d-bars" viewBox="0 0 24 24">
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4.5V20h16"/><rect x="7.5" y="12.5" width="2.8" height="4.5" rx=".9"/><rect x="12" y="9" width="2.8" height="8" rx=".9"/></g>
            <rect x="16.5" y="5.5" width="2.8" height="11.5" rx=".9" style="fill:var(--ic-accent)"/>
        </symbol>
    </defs>
</svg>

<style>
    .dash-card { background: #fff; border: 1px solid #E8E7F0; border-radius: 18px; box-shadow: 0 1px 2px rgba(38,34,98,.04); }

    .dash-chip { display: grid; width: 46px; height: 46px; flex: none; place-items: center; border-radius: 14px; background: #F1F0FA; color: #262262; }
    .dash-chip--lg { width: 56px; height: 56px; border-radius: 17px; background: #262262; color: #fff; }

    .dash-title { margin: 0; color: #211F35; font-size: 17px; font-weight: 700; }

    .dash-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border: 1px solid transparent; border-radius: 12px; font-size: 14px; font-weight: 600; text-decoration: none; transition: background .15s ease, border-color .15s ease; }
    .dash-btn--primary { background: #262262; color: #fff; }
    .dash-btn--primary:hover { background: #1C194B; }
    .dash-btn--line { border-color: #E8E7F0; background: #fff; color: #262262; }
    .dash-btn--line:hover { border-color: #C9C7D9; background: #F7F7FB; }

    .dash-kpi { padding: 20px; transition: border-color .15s ease; }
    .dash-kpi:hover { border-color: #D8D6EF; }
    .dash-meter { height: 6px; margin-top: 12px; overflow: hidden; border-radius: 6px; background: #F1F0FA; }
    .dash-meter > span { display: block; height: 100%; border-radius: 6px; background: #262262; }

    .dash-tile { display: block; padding: 18px; color: inherit; text-decoration: none; transition: border-color .15s ease, background .15s ease; }
    .dash-tile:hover { border-color: #D8D6EF; background: #FCFCFE; }
    .dash-tile .dash-go { transition: background .15s ease, color .15s ease; }
    .dash-tile:hover .dash-go { background: #262262; color: #fff; }

    .dash-track { display: flex; align-items: flex-end; width: 100%; max-width: 36px; height: 128px; margin-inline: auto; overflow: hidden; border-radius: 10px; background: #F1F0FA; }
    .dash-bar { width: 100%; border-radius: 10px; background: #262262; transition: height .35s ease; }
    .dash-bar.is-peak { background: #efb144; }
    .dash-val { display: inline-grid; min-width: 26px; height: 24px; place-items: center; padding: 0 7px; border-radius: 8px; background: #F1F0FA; color: #262262; font-size: 12px; font-weight: 700; }
    .dash-val.is-peak { background: #FFF4E0; color: #262262; }

    .dash-quick { display: flex; align-items: center; gap: 12px; min-height: 62px; padding: 11px 12px; border: 1px solid #E8E7F0; border-radius: 14px; color: #211F35; font-size: 14px; font-weight: 600; text-decoration: none; transition: border-color .15s ease, background .15s ease; }
    .dash-quick:hover { border-color: #D8D6EF; background: #F7F7FB; }
    .dash-quick .dash-chip { width: 38px; height: 38px; border-radius: 12px; }

    .dash-status { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
    .dash-status::before { width: 6px; height: 6px; border-radius: 50%; background: currentColor; content: ""; }
    .dash-status--ok { background: #ECF8F2; color: #1F7A52; }
    .dash-status--wait { background: #FFF4E0; color: #262262; }
    .dash-status--off { background: #FFF0F1; color: #B03541; }
    .dash-avatar { display: grid; width: 40px; height: 40px; flex: none; place-items: center; border-radius: 12px; background: #F1F0FA; color: #262262; font-size: 15px; font-weight: 700; }
    .dash-empty { display: grid; place-items: center; gap: 10px; padding: 40px 20px; color: #706E83; font-size: 14px; text-align: center; }
    .dash-empty .dash-chip { width: 52px; height: 52px; border-radius: 16px; }

    @media (prefers-reduced-motion: reduce) { .dash-bar { transition: none; } }
</style>

<section class="space-y-8">

    <!-- ═════════ Welcome ═════════ -->
    <header class="dash-card p-5 sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-5">
            <div class="flex min-w-0 items-center gap-4">
                <span class="dash-chip dash-chip--lg" aria-hidden="true"><svg class="ic ic-lg"><use href="#a-stairs"/></svg></span>
                <div class="min-w-0">
                    <p class="text-sm text-muted">مركز إدارة STEP Smart Practice</p>
                    <h2 class="mt-1 text-2xl font-bold text-ink">مرحباً، <?= e($adminName) ?></h2>
                    <p class="mt-1 text-sm leading-7 text-muted">تابع نشاط الطلاب ومحتوى المنصة، وانتقل مباشرة إلى أدوات الإدارة.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-2 text-xs text-muted"><svg class="ic ic-sm" aria-hidden="true"><use href="#d-clock"/></svg>آخر تحديث: <?= e(date('Y-m-d H:i')) ?></span>
                <a href="<?= e(url('/')) ?>" class="dash-btn dash-btn--line"><svg class="ic ic-sm" aria-hidden="true"><use href="#a-globe"/></svg>عرض المنصة</a>
                <a href="<?= e(url('/admin/settings')) ?>" class="dash-btn dash-btn--primary"><svg class="ic ic-sm" aria-hidden="true"><use href="#a-sliders"/></svg>إعدادات المنصة</a>
            </div>
        </div>
    </header>

    <!-- ═════════ Overview ═════════ -->
    <section aria-labelledby="dashboard-overview-title">
        <div class="mb-4">
            <h3 id="dashboard-overview-title" class="dash-title">نظرة عامة</h3>
            <p class="mt-1 text-sm text-muted">أرقام محدثة من بيانات الطلاب ومحتوى المنصة.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <?php foreach ($kpis as $k): ?>
                <article class="dash-card dash-kpi">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-muted"><?= e($k['label']) ?></p>
                        <span class="dash-chip" aria-hidden="true"><svg class="ic ic-lg"><use href="#<?= e($k['icon']) ?>"/></svg></span>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-ink"><?= e($k['value']) ?><?php if (!empty($k['suffix'])): ?><span class="text-base font-semibold text-muted"><?= e($k['suffix']) ?></span><?php endif; ?></p>
                    <p class="mt-1 text-xs text-muted"><?= e($k['note']) ?></p>
                    <?php if (isset($k['progress'])): ?>
                        <div class="dash-meter" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= e((string) round($k['progress'])) ?>" aria-label="متوسط الدرجة المتوقعة"><span style="width: <?= e((string) round($k['progress'])) ?>%"></span></div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ═════════ Platform content ═════════ -->
    <section aria-labelledby="content-overview-title">
        <div class="mb-4">
            <h3 id="content-overview-title" class="dash-title">محتوى المنصة</h3>
            <p class="mt-1 text-sm text-muted">حالة مكتبة التدريبات والموارد التعليمية.</p>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ($content as [$path, $label, $icon, $value, $unit, $sub, $ratio]): ?>
                <a href="<?= e(url($path)) ?>" class="dash-card dash-tile">
                    <div class="flex items-center justify-between gap-3">
                        <span class="dash-chip" aria-hidden="true"><svg class="ic ic-lg"><use href="#<?= e($icon) ?>"/></svg></span>
                        <span class="dash-go grid h-9 w-9 place-items-center rounded-xl bg-primaryLight text-primary" aria-hidden="true"><svg class="ic ic-sm"><use href="#d-arrow"/></svg></span>
                    </div>
                    <p class="mt-4 text-sm font-medium text-muted"><?= e($label) ?></p>
                    <p class="mt-1 text-2xl font-bold text-ink"><?= e($value) ?><?php if ($unit !== ''): ?><span class="mr-1 text-xs font-medium text-muted"><?= e($unit) ?></span><?php endif; ?></p>
                    <p class="mt-1 text-xs text-muted"><?= e($sub) ?></p>
                    <?php if ($ratio !== null): ?>
                        <div class="dash-meter" aria-hidden="true"><span style="width: <?= e((string) $ratio) ?>%"></span></div>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ═════════ Activity + quick links ═════════ -->
    <section class="grid grid-cols-1 gap-6 xl:grid-cols-5">
        <article class="dash-card p-5 sm:p-6 xl:col-span-3" aria-labelledby="activity-title">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <span class="dash-chip" aria-hidden="true"><svg class="ic ic-lg"><use href="#d-bars"/></svg></span>
                    <div>
                        <h3 id="activity-title" class="text-lg font-bold text-ink">نشاط إكمال التدريبات</h3>
                        <p class="mt-1 text-sm text-muted">عدد المحاولات المكتملة خلال الأيام السبعة الماضية.</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full bg-primaryLight px-3 py-1 text-xs font-semibold text-primary"><?= e((string) $weekTotal) ?> إجمالي</span>
                    <span class="rounded-full bg-[#FFF4E0] px-3 py-1 text-xs font-semibold text-primary">متوسط <?= e((string) $weekAvg) ?> يومياً</span>
                </div>
            </div>

            <div class="mt-7 grid grid-cols-7 items-end gap-2 sm:gap-4" role="img" aria-label="مخطط إكمال التدريبات خلال آخر سبعة أيام">
                <?php foreach ($weeklyActivity as $day): ?>
                    <?php
                        $count = (int) $day['count'];
                        $barHeight = $maxActivity > 0 ? max(8, (int) round(($count / $maxActivity) * 100)) : 8;
                        $isPeak = $maxActivity > 0 && $count === $maxActivity;
                    ?>
                    <div class="flex min-w-0 flex-col items-center justify-end gap-2" title="<?= e($day['label'] . ': ' . (string) $count . ' تدريبات مكتملة') ?>">
                        <span class="dash-val<?= $isPeak ? ' is-peak' : '' ?>"><?= e((string) $count) ?></span>
                        <div class="dash-track"><div class="dash-bar<?= $isPeak ? ' is-peak' : '' ?>" style="height: <?= e((string) $barHeight) ?>%"></div></div>
                        <span class="w-full truncate text-center text-[10px] text-muted sm:text-xs"><?= e($day['label']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="dash-card p-5 sm:p-6 xl:col-span-2" aria-labelledby="quick-links-title">
            <div>
                <h3 id="quick-links-title" class="text-lg font-bold text-ink">إدارة المنصة</h3>
                <p class="mt-1 text-sm text-muted">اختصارات إلى أدوات الإدارة.</p>
            </div>
            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <?php foreach ($quick as [$path, $label, $icon, $badge]): ?>
                    <a href="<?= e(url($path)) ?>" class="dash-quick">
                        <span class="dash-chip" aria-hidden="true"><svg class="ic"><use href="#<?= e($icon) ?>"/></svg></span>
                        <span class="flex-1"><?= e($label) ?></span>
                        <?php if ($badge !== null): ?>
                            <span class="rounded-full bg-primaryLight px-2.5 py-0.5 text-xs font-semibold text-primary"><?= e($badge) ?></span>
                        <?php else: ?>
                            <svg class="ic ic-sm text-primary" aria-hidden="true"><use href="#d-arrow"/></svg>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </article>
    </section>

    <!-- ═════════ Recent ═════════ -->
    <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <article class="dash-card overflow-hidden" aria-labelledby="recent-attempts-title">
            <div class="flex items-center justify-between gap-3 border-b border-border p-5">
                <div>
                    <h3 id="recent-attempts-title" class="text-lg font-bold text-ink">أحدث المحاولات</h3>
                    <p class="mt-1 text-sm text-muted">آخر أنشطة الطلاب المسجلة.</p>
                </div>
                <a href="<?= e(url('/admin/users')) ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:underline">إدارة الطلاب<svg class="ic ic-sm" aria-hidden="true"><use href="#d-arrow"/></svg></a>
            </div>
            <?php if ($recentAttempts): ?>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px] text-right text-sm">
                        <thead class="bg-primaryLight/60 text-xs text-muted">
                            <tr><th class="px-5 py-3 font-semibold">الطالب</th><th class="px-4 py-3 font-semibold">نوع النشاط</th><th class="px-4 py-3 font-semibold">النتيجة</th><th class="px-5 py-3 font-semibold">الحالة</th></tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <?php foreach ($recentAttempts as $attempt): ?>
                                <?php
                                    $done  = $attempt['finished_at'] !== null;
                                    $tot   = (int) $attempt['total'];
                                    $cor   = (int) $attempt['correct_count'];
                                    $ratio = $tot > 0 ? max(0, min(100, (int) round(($cor / $tot) * 100))) : 0;
                                ?>
                                <tr class="transition hover:bg-primaryLight/40">
                                    <td class="px-5 py-4">
                                        <span class="block font-semibold text-ink"><?= e($attempt['student_name']) ?></span>
                                        <span class="mt-1 block text-xs text-muted"><?= e($attempt['practice_title'] ?: 'نشاط تدريبي') ?></span>
                                    </td>
                                    <td class="px-4 py-4"><span class="rounded-lg bg-primaryLight px-2.5 py-1 text-xs font-semibold text-primary"><?= e($attemptTypes[$attempt['type']] ?? 'تدريب') ?></span></td>
                                    <td class="px-4 py-4 text-ink">
                                        <?php if ($done): ?>
                                            <span class="font-semibold"><?= e((string) $attempt['correct_count']) ?>/<?= e((string) $attempt['total']) ?></span>
                                            <div class="dash-meter" style="width: 72px; margin-top: 6px" aria-hidden="true"><span style="width: <?= e((string) $ratio) ?>%"></span></div>
                                            <?php if ($attempt['predicted_score'] !== null): ?><span class="mt-1 block text-xs text-muted">متوقع <?= e((string) $attempt['predicted_score']) ?>/100</span><?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="dash-status <?= $done ? 'dash-status--ok' : 'dash-status--wait' ?>"><?= $done ? 'مكتمل' : 'قيد التقدم' ?></span>
                                        <span class="mt-1 block text-xs text-muted"><?= e(date('Y-m-d H:i', strtotime($attempt['started_at']))) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="dash-empty">
                    <span class="dash-chip" aria-hidden="true"><svg class="ic ic-lg"><use href="#a-target"/></svg></span>
                    <p>لا توجد محاولات مسجلة حتى الآن.</p>
                </div>
            <?php endif; ?>
        </article>

        <article class="dash-card overflow-hidden" aria-labelledby="recent-students-title">
            <div class="flex items-center justify-between gap-3 border-b border-border p-5">
                <div>
                    <h3 id="recent-students-title" class="text-lg font-bold text-ink">أحدث الطلاب</h3>
                    <p class="mt-1 text-sm text-muted">آخر الحسابات التي انضمت إلى المنصة.</p>
                </div>
                <a href="<?= e(url('/admin/users')) ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:underline">عرض الكل<svg class="ic ic-sm" aria-hidden="true"><use href="#d-arrow"/></svg></a>
            </div>
            <?php if ($recentStudents): ?>
                <ul class="divide-y divide-border">
                    <?php foreach ($recentStudents as $student): ?>
                        <li class="flex items-center justify-between gap-3 p-4 transition hover:bg-primaryLight/40 sm:px-5">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="dash-avatar" aria-hidden="true"><?= e(mb_substr($student['name'], 0, 1, 'UTF-8')) ?></span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-ink"><?= e($student['name']) ?></p>
                                    <p class="mt-1 truncate text-xs text-muted" dir="ltr"><?= e($student['email']) ?></p>
                                </div>
                            </div>
                            <div class="flex-none text-left">
                                <span class="dash-status <?= $student['is_active'] ? 'dash-status--ok' : 'dash-status--off' ?>"><?= $student['is_active'] ? 'نشط' : 'موقوف' ?></span>
                                <p class="mt-1 text-xs text-muted"><?= e((string) $student['completed_attempts']) ?> مكتمل</p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="dash-empty">
                    <span class="dash-chip" aria-hidden="true"><svg class="ic ic-lg"><use href="#a-users"/></svg></span>
                    <p>لا يوجد طلاب مسجلون بعد.</p>
                </div>
            <?php endif; ?>
        </article>
    </section>
</section>