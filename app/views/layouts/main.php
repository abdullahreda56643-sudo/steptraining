<?php
$success = flash('success');
$error   = flash('error');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#262262">
    <title><?= e($title ?? 'STEP Smart Practice') ?></title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#262262',
                        primaryDark: '#1C194B',
                        primaryLight: '#F1F0FA',
                        primaryMid: '#D8D6EF',
                        accent: '#efb144',
                        ink: '#211F35',
                        muted: '#706E83',
                        border: '#E8E7F0',
                        warning: '#efb144',
                        error: '#C2414B'
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <style>
        :root {
            --navy: #262262;
            --navy-soft: #302D78;
            --navy-deep: #1C194B;
            --orange: #efb144;
            --line: #E8E7F0;
            --tint: #F1F0FA;
            --page: #F7F7FB;
            --ic-accent: #efb144;
        }

        html { scroll-behavior: smooth; }
        body { position: relative; isolation: isolate; font-family: 'IBM Plex Sans Arabic', 'Segoe UI', Tahoma, sans-serif; -webkit-font-smoothing: antialiased; }
        ::selection { background: rgba(239,177,68,.35); color: var(--navy-deep); }

        /* ───────── Shared component classes (kept + refined) ───────── */
        .card { background: #fff; border: 1px solid var(--line); border-radius: 20px; box-shadow: 0 1px 2px rgba(38,34,98,.04), 0 12px 28px -18px rgba(38,34,98,.18); }

        .btn-primary { display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: var(--navy); color: #fff; border-radius: 12px; font-weight: 600; box-shadow: 0 10px 20px -8px rgba(38,34,98,.5); transition: transform .16s ease, background .16s ease, box-shadow .16s ease; }
        .btn-primary:hover { background: var(--navy-deep); transform: translateY(-1px); box-shadow: 0 14px 24px -8px rgba(38,34,98,.55); }
        .btn-primary:active { transform: translateY(0); }
        .btn-primary .ic { color: var(--orange); }

        .btn-secondary { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: 1px solid var(--navy); color: var(--navy); background: #fff; border-radius: 12px; font-weight: 600; transition: background .16s ease, transform .16s ease; }
        .btn-secondary:hover { background: var(--tint); transform: translateY(-1px); }

        .btn-accent { display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: var(--orange); color: var(--navy-deep); border-radius: 12px; font-weight: 700; box-shadow: 0 10px 20px -8px rgba(239,177,68,.65); transition: transform .16s ease, filter .16s ease; }
        .btn-accent:hover { filter: brightness(1.05); transform: translateY(-1px); }

        .text-muted { color: #706E83; }

        /* ───────── Icons (duotone sprite below) ───────── */
        .ic { display: block; width: 20px; height: 20px; flex: none; }
        .ic-sm { width: 17px; height: 17px; }
        .ic-lg { width: 26px; height: 26px; }

        /* ───────── Page decoration ───────── */
        .site-art { position: absolute; top: 0; right: 0; left: 0; z-index: -1; display: block; width: 100%; height: 300px; pointer-events: none; }

        /* ───────── Skip link ───────── */
        .skip-link { position: absolute; top: -60px; right: 16px; z-index: 100; padding: 10px 16px; border-radius: 10px; background: var(--navy); color: #fff; font-size: 14px; font-weight: 600; text-decoration: none; transition: top .16s ease; }
        .skip-link:focus { top: 12px; }

        /* ───────── Flash ───────── */
        .flash { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 20px; padding: 14px 16px; border: 1px solid; border-radius: 14px; font-size: 14px; line-height: 1.7; }
        .flash .ic { margin-top: 1px; }
        .flash p { flex: 1; margin: 0; }
        .flash-close { display: grid; width: 28px; height: 28px; flex: none; place-items: center; border: 0; border-radius: 8px; background: transparent; color: inherit; cursor: pointer; opacity: .65; }
        .flash-close:hover { background: rgba(0,0,0,.06); opacity: 1; }
        .flash--success { --ic-accent: #2E9D6B; border-color: #B7DCCD; background: #F2FBF6; color: #236747; }
        .flash--error { --ic-accent: #C2414B; border-color: #F2C6CA; background: #FFF4F4; color: #A12F3A; }

        /* ───────── Footer ───────── */
        .site-footer { position: relative; margin-top: 56px; padding: 74px 20px 26px; color: #fff; background: linear-gradient(160deg, var(--navy-soft) 0%, var(--navy) 55%, var(--navy-deep) 100%); }
        .site-footer-wave { position: absolute; top: -1px; right: 0; left: 0; display: block; width: 100%; height: 52px; pointer-events: none; }
        .site-footer-inner { display: flex; max-width: 80rem; margin-inline: auto; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 22px; }
        .site-brand { display: inline-flex; align-items: center; gap: 12px; color: #fff; text-decoration: none; }
        .site-brand-mark { display: grid; width: 44px; height: 44px; place-items: center; border-radius: 14px; background: var(--orange); color: var(--navy); --ic-accent: var(--navy); box-shadow: 0 10px 22px rgba(13,11,52,.3); }
        .site-brand-name { font-size: 15.5px; font-weight: 700; line-height: 1.3; }
        .site-brand-name small { display: block; margin-top: 2px; color: rgba(255,255,255,.7); font-size: 11.5px; font-weight: 500; }
        .site-points { display: flex; flex-wrap: wrap; gap: 10px 22px; margin: 0; padding: 0; list-style: none; }
        .site-points li { display: flex; align-items: center; gap: 9px; color: rgba(255,255,255,.82); font-size: 13px; }
        .site-points .dot { display: grid; width: 30px; height: 30px; place-items: center; border-radius: 10px; background: rgba(255,255,255,.1); color: #fff; }
        .site-copy { width: 100%; max-width: 80rem; margin: 22px auto 0; padding-top: 18px; border-top: 1px solid rgba(255,255,255,.14); color: rgba(255,255,255,.58); font-size: 12px; text-align: center; }

        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible { outline: 3px solid rgba(239,177,68,.8); outline-offset: 2px; }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after { transition-duration: .01ms !important; }
        }
    </style>
</head>
<body class="bg-[#F7F7FB] text-ink min-h-screen flex flex-col">

    <!-- Duotone icon sprite: <svg class="ic"><use href="#i-target"/></svg> -->
    <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
        <defs>
            <symbol id="i-stairs" viewBox="0 0 24 24">
                <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 20h5v-5h5v-5h4"/></g>
                <circle cx="18.5" cy="6" r="2.6" style="fill:var(--ic-accent)"/>
            </symbol>
            <symbol id="i-home" viewBox="0 0 24 24">
                <path d="M4 11 12 4l8 7v8a1 1 0 0 1-1 1h-4.5v-5h-5v5H5a1 1 0 0 1-1-1Z" style="fill:var(--ic-accent);opacity:.3"/>
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11 12 4l8 7v8a1 1 0 0 1-1 1h-4.5v-5h-5v5H5a1 1 0 0 1-1-1Z"/></g>
            </symbol>
            <symbol id="i-target" viewBox="0 0 24 24">
                <g fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5.2"/></g>
                <circle cx="12" cy="12" r="2.2" style="fill:var(--ic-accent)"/>
            </symbol>
            <symbol id="i-help" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" style="fill:var(--ic-accent);opacity:.25"/>
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M9.6 9.6a2.5 2.5 0 1 1 3.8 2.1c-.9.5-1.4 1.1-1.4 2M12 16.9v.1"/></g>
            </symbol>
            <symbol id="i-headset" viewBox="0 0 24 24">
                <rect x="3.5" y="13" width="4" height="6.5" rx="1.8" style="fill:var(--ic-accent);opacity:.4"/>
                <rect x="16.5" y="13" width="4" height="6.5" rx="1.8" style="fill:var(--ic-accent);opacity:.4"/>
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="3.5" y="13" width="4" height="6.5" rx="1.8"/><rect x="16.5" y="13" width="4" height="6.5" rx="1.8"/><path d="M18.5 19.5c0 1.3-1.6 2-4 2h-1"/></g>
            </symbol>
            <symbol id="i-book" viewBox="0 0 24 24">
                <path d="M12 6.5C10.3 5.2 8 4.7 4.5 5v12.5c3.5-.3 5.8.2 7.5 1.5Z" style="fill:var(--ic-accent);opacity:.35"/>
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6.5C10.3 5.2 8 4.7 4.5 5v12.5c3.5-.3 5.8.2 7.5 1.5 1.7-1.3 4-1.8 7.5-1.5V5C16 4.7 13.7 5.2 12 6.5ZM12 6.5V19"/></g>
            </symbol>
            <symbol id="i-bulb" viewBox="0 0 24 24">
                <path d="M12 3a6 6 0 0 0-3.6 10.8c.6.5 1.1 1.2 1.1 2V16h5v-.2c0-.8.5-1.5 1.1-2A6 6 0 0 0 12 3Z" style="fill:var(--ic-accent);opacity:.4"/>
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0-3.6 10.8c.6.5 1.1 1.2 1.1 2V16h5v-.2c0-.8.5-1.5 1.1-2A6 6 0 0 0 12 3Z"/><path d="M9.5 18.5h5M10.5 21h3"/></g>
            </symbol>
            <symbol id="i-chart" viewBox="0 0 24 24">
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4.5V20h16"/><rect x="7.5" y="12.5" width="2.8" height="4.5" rx=".9"/><rect x="12" y="9" width="2.8" height="8" rx=".9"/></g>
                <rect x="16.5" y="5.5" width="2.8" height="11.5" rx=".9" style="fill:var(--ic-accent)"/>
            </symbol>
            <symbol id="i-clock" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" style="fill:var(--ic-accent);opacity:.22"/>
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></g>
            </symbol>
            <symbol id="i-trophy" viewBox="0 0 24 24">
                <path d="M8 4h8v5a4 4 0 0 1-8 0Z" style="fill:var(--ic-accent);opacity:.4"/>
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M8 4h8v5a4 4 0 0 1-8 0Z"/><path d="M8 6H5.2a2.4 2.4 0 0 0 2.6 4.4M16 6h2.8a2.4 2.4 0 0 1-2.6 4.4M12 13v4M8.5 20h7M10 17h4"/></g>
            </symbol>
            <symbol id="i-user" viewBox="0 0 24 24">
                <circle cx="12" cy="8" r="3.8" style="fill:var(--ic-accent);opacity:.35"/>
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.8"/><path d="M4.8 20c.8-3.5 3.5-5.3 7.2-5.3s6.4 1.8 7.2 5.3"/></g>
            </symbol>
            <symbol id="i-login" viewBox="0 0 24 24">
                <path d="M14 4.5h3.5a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H14Z" style="fill:var(--ic-accent);opacity:.3"/>
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4.5h3.5a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H14"/><path d="M4.5 12h9M10 8l4 4-4 4"/></g>
            </symbol>
            <symbol id="i-logout" viewBox="0 0 24 24">
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M10 4.5H6.5a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2H10"/><path d="m14 8-4 4 4 4M10 12h9.5" style="stroke:var(--ic-accent)"/></g>
            </symbol>
            <symbol id="i-shield" viewBox="0 0 24 24">
                <path d="M12 3 4.5 6v5.5c0 4.6 3.1 7.7 7.5 9.5 4.4-1.8 7.5-4.9 7.5-9.5V6Z" style="fill:var(--ic-accent);opacity:.28"/>
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4.5 6v5.5c0 4.6 3.1 7.7 7.5 9.5 4.4-1.8 7.5-4.9 7.5-9.5V6Z"/><path d="m9 12 2.2 2.2L15.5 10"/></g>
            </symbol>
            <symbol id="i-arrow" viewBox="0 0 24 24">
                <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12H5M11 6l-6 6 6 6"/></g>
            </symbol>
            <symbol id="i-menu" viewBox="0 0 24 24">
                <g fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><path d="M4 7h16M4 12h16"/><path d="M4 17h9" style="stroke:var(--ic-accent)"/></g>
            </symbol>
            <symbol id="i-close" viewBox="0 0 24 24">
                <g fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></g>
            </symbol>
            <symbol id="i-check-circle" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" style="fill:var(--ic-accent);opacity:.25"/>
                <g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m8 12.5 2.7 2.7 5.3-5.4"/></g>
            </symbol>
            <symbol id="i-alert" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" style="fill:var(--ic-accent);opacity:.22"/>
                <g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7.8v5M12 16.4v.1"/></g>
            </symbol>
        </defs>
    </svg>

    <a class="skip-link" href="#main-content">تخطَّ إلى المحتوى</a>

    <!-- Soft wave behind the top of every page -->
    <svg class="site-art" viewBox="0 0 1440 300" preserveAspectRatio="none" aria-hidden="true">
        <defs>
            <linearGradient id="siteFade" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#262262" stop-opacity=".07"/><stop offset="1" stop-color="#262262" stop-opacity="0"/></linearGradient>
        </defs>
        <path d="M0 0H1440V150C1230 210 1090 120 880 160S520 230 300 186S80 150 0 172Z" fill="url(#siteFade)"/>
        <path d="M0 172C80 150 160 186 300 186S520 230 880 160S1230 210 1440 150" fill="none" stroke="#efb144" stroke-opacity=".5" stroke-width="1.6" vector-effect="non-scaling-stroke"/>
        <path d="M0 196C90 176 170 212 310 212S530 254 890 186S1240 232 1440 176" fill="none" stroke="#817DCE" stroke-opacity=".28" stroke-width="1.4" vector-effect="non-scaling-stroke"/>
    </svg>

    <?php include ROOT_PATH . '/app/views/partials/navbar.php'; ?>

    <main id="main-content" class="relative w-full flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if ($success): ?>
            <div class="flash flash--success" role="status">
                <svg class="ic" aria-hidden="true"><use href="#i-check-circle"/></svg>
                <p><?= e($success) ?></p>
                <button type="button" class="flash-close" data-dismiss aria-label="إغلاق"><svg class="ic ic-sm" aria-hidden="true"><use href="#i-close"/></svg></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="flash flash--error" role="alert">
                <svg class="ic" aria-hidden="true"><use href="#i-alert"/></svg>
                <p><?= e($error) ?></p>
                <button type="button" class="flash-close" data-dismiss aria-label="إغلاق"><svg class="ic ic-sm" aria-hidden="true"><use href="#i-close"/></svg></button>
            </div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </main>

    <footer class="site-footer">
        <svg class="site-footer-wave" viewBox="0 0 1440 52" preserveAspectRatio="none" aria-hidden="true">
            <path d="M0 0H1440V22C1250 50 1130 4 940 26S560 52 340 22S100 6 0 28Z" fill="#F7F7FB"/>
            <path d="M0 28C100 6 200 24 340 22S560 52 940 26S1250 50 1440 22" fill="none" stroke="#efb144" stroke-width="2" vector-effect="non-scaling-stroke"/>
        </svg>
        <div class="site-footer-inner">
            <a class="site-brand" href="<?= e(url('/')) ?>">
                <span class="site-brand-mark" aria-hidden="true"><svg class="ic ic-lg"><use href="#i-stairs"/></svg></span>
                <span class="site-brand-name">STEP Smart Practice<small>منصة التدريب الذكي</small></span>
            </a>
            <ul class="site-points">
                <li><span class="dot" aria-hidden="true"><svg class="ic ic-sm"><use href="#i-target"/></svg></span>تدريب تفاعلي</li>
                <li><span class="dot" aria-hidden="true"><svg class="ic ic-sm"><use href="#i-chart"/></svg></span>تحليل تقدمك</li>
                <li><span class="dot" aria-hidden="true"><svg class="ic ic-sm"><use href="#i-shield"/></svg></span>تعلم آمن</li>
            </ul>
        </div>
        <p class="site-copy">© <?= e(date('Y')) ?> STEP Smart Practice · تعلّم بثقة · صُمم بواسطة <strong>أ. خديجة الشهري</strong></p>
    </footer>

    <script>
        document.querySelectorAll('[data-dismiss]').forEach(function (b) {
            b.addEventListener('click', function () { var a = b.closest('.flash'); if (a) a.remove(); });
        });
    </script>
</body>
</html>