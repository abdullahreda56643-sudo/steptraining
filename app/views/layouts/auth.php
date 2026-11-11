<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#262262">
    <title><?= e($title ?? 'STEP Smart Practice') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #262262;
            --navy-deep: #1C194B;
            --navy-soft: #302D78;
            --violet: #817DCE;
            --orange: #efb144;
            --white: #FFFFFF;
            --ink: #211F35;
            --muted: #706E83;
            --line: #E4E3EF;
            --tint: #F1F0FA;
            --error: #C2414B;
            --seam: clamp(56px, 6vw, 96px);
            --pad: clamp(28px, 5vw, 68px);
        }

        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; }

        body {
            min-width: 320px;
            min-height: 100vh;
            color: var(--ink);
            background: var(--white);
            font-family: "IBM Plex Sans Arabic", "Segoe UI", Tahoma, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* ───────── Shell ───────── */
        .auth-page {
            display: grid;
            min-height: 100vh;
            grid-template-columns: minmax(0, 1fr) minmax(400px, 1fr);
        }

        /* ───────── Visual side ───────── */
        .auth-visual {
            position: relative;
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            padding: var(--pad);
            padding-left: calc(var(--pad) + var(--seam) * .6);
            color: var(--white);
            background:
                radial-gradient(circle at 100% 0%, rgba(129,125,206,.26), transparent 45%),
                linear-gradient(160deg, var(--navy-soft) 0%, var(--navy) 55%, var(--navy-deep) 100%);
        }
        .auth-visual > * { position: relative; z-index: 2; }

        /* Divider seam: one clean static curve, three layers */
        .auth-seam {
            position: absolute !important;
            z-index: 1 !important;
            pointer-events: none;
        }
        .auth-seam svg { display: block; width: 100%; height: 100%; }
        .auth-seam-v { top: 0; bottom: 0; left: -1px; width: var(--seam); }
        .auth-seam-h { display: none; right: 0; bottom: -1px; left: 0; height: 40px; }

        /* Brand */
        .auth-brand { display: inline-flex; width: fit-content; align-items: center; gap: 12px; color: inherit; text-decoration: none; }
        .auth-brand-mark { display: grid; width: 46px; height: 46px; place-items: center; border-radius: 14px; background: var(--orange); color: var(--navy); }
        .auth-brand-name { font-size: 17px; font-weight: 700; line-height: 1.35; }
        .auth-brand-name small { display: block; margin-top: 2px; color: rgba(255,255,255,.65); font-size: 12px; font-weight: 500; }

        /* Hero */
        .auth-hero { max-width: 460px; }
        .auth-hero h1 { margin: 0; font-size: clamp(32px, 3.6vw, 48px); font-weight: 700; line-height: 1.3; letter-spacing: -.01em; }
        .auth-hero h1 span { color: #FFD58E; }
        .auth-hero > p { margin: 16px 0 0; color: rgba(255,255,255,.74); font-size: 15.5px; line-height: 2; }

        /* Features: clean rows with bold icons, no boxes */
        .auth-features { margin: 36px 0 0; padding: 0; list-style: none; }
        .auth-features li { display: flex; align-items: center; gap: 16px; padding: 16px 0; border-top: 1px solid rgba(255,255,255,.12); }
        .auth-features li:last-child { border-bottom: 1px solid rgba(255,255,255,.12); }
        .auth-feature-icon {
            display: grid;
            width: 46px;
            height: 46px;
            flex: none;
            place-items: center;
            border: 1px solid rgba(239,177,68,.35);
            border-radius: 13px;
            background: rgba(239,177,68,.12);
            color: var(--orange);
        }
        .auth-feature-icon svg { stroke-width: 2.2; }
        .auth-feature-title { font-size: 15px; font-weight: 700; }
        .auth-feature-desc { margin-top: 3px; color: rgba(255,255,255,.64); font-size: 13px; line-height: 1.7; }

        .auth-footer { color: rgba(255,255,255,.5); font-size: 12px; }

        /* ───────── Form side ───────── */
        .auth-form-panel {
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: var(--pad);
            padding-right: calc(var(--pad) * .8);
            background: var(--white);
        }
        .auth-form-wrap { width: min(100%, 400px); }
        .auth-card { width: 100%; }

        .auth-tabs { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; padding: 4px; border-radius: 13px; background: var(--tint); }
        .auth-tab {
            display: flex;
            min-height: 44px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 10px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: background .15s ease, color .15s ease;
        }
        .auth-tab-icon { width: 18px; height: 18px; flex: none; stroke-width: 2.2; }
        .auth-tab:hover { color: var(--navy); }
        .auth-tab[aria-current="page"] { background: var(--white); color: var(--navy); font-weight: 700; box-shadow: 0 1px 6px rgba(38,34,98,.12); }

        .auth-heading { margin-top: 32px; }
        .auth-heading h2 { margin: 0; color: var(--navy); font-size: 28px; font-weight: 700; line-height: 1.4; }
        .auth-heading p { margin: 8px 0 0; color: var(--muted); font-size: 14px; line-height: 1.9; }

        .auth-alert { display: flex; align-items: flex-start; gap: 10px; margin-top: 20px; padding: 12px 14px; border: 1px solid; border-radius: 11px; font-size: 13px; line-height: 1.7; }
        .auth-alert svg { flex: none; margin-top: 2px; stroke-width: 2.2; }
        .auth-alert--error { border-color: #F2C6CA; background: #FFF6F6; color: #A12F3A; }
        .auth-alert--success { border-color: #B7DCCD; background: #F4FBF7; color: #236747; }

        .auth-form { display: grid; gap: 18px; margin-top: 26px; }
        .auth-field label { display: block; margin-bottom: 8px; color: #2E2C45; font-size: 13px; font-weight: 700; }
        .auth-input-wrap { position: relative; }
        .auth-input-wrap input {
            width: 100%;
            height: 52px;
            padding: 0 44px 0 15px;
            border: 1px solid var(--line);
            border-radius: 12px;
            outline: none;
            background: var(--white);
            color: var(--ink);
            font: inherit;
            font-size: 14px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .auth-input-wrap input::placeholder { color: #A5A3B2; }
        .auth-input-wrap input:hover { border-color: #CFCDDF; }
        .auth-input-wrap input:focus { border-color: var(--navy); box-shadow: 0 0 0 4px rgba(38,34,98,.08); }
        .auth-input-wrap input.auth-ltr { direction: ltr; padding: 0 44px; text-align: left; }
        .auth-input-wrap input.auth-password { padding-right: 72px; }

        .auth-icon { position: absolute; top: 50%; right: 15px; display: grid; color: #8F8DA3; pointer-events: none; transform: translateY(-50%); transition: color .15s ease; }
        .auth-icon svg { stroke-width: 2.1; }
        .auth-input-wrap.is-ltr .auth-icon { right: auto; left: 15px; }
        .auth-input-wrap:focus-within .auth-icon { color: var(--navy); }

        .auth-password-toggle {
            position: absolute;
            top: 50%;
            right: 8px;
            min-width: 52px;
            height: 36px;
            padding: 0 9px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: var(--navy);
            cursor: pointer;
            font: inherit;
            font-size: 12px;
            font-weight: 700;
            transform: translateY(-50%);
        }
        .auth-password-toggle:hover { background: var(--tint); }

        /* Password strength */
        .auth-strength { display: grid; grid-template-columns: repeat(4, 1fr); gap: 5px; margin-top: 10px; }
        .auth-strength span { height: 4px; border-radius: 4px; background: #E9E8F2; transition: background .2s ease; }
        .auth-strength[data-level="1"] span:nth-child(-n+1) { background: var(--error); }
        .auth-strength[data-level="2"] span:nth-child(-n+2) { background: var(--orange); }
        .auth-strength[data-level="3"] span:nth-child(-n+3) { background: var(--orange); }
        .auth-strength[data-level="4"] span:nth-child(-n+4) { background: var(--navy); }
        .auth-strength-text { min-height: 18px; margin: 6px 0 0; color: var(--muted); font-size: 12px; }

        /* Submit: bold label + arrow icon */
        .auth-submit {
            position: relative;
            display: flex;
            width: 100%;
            min-height: 54px;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 4px;
            padding: 12px 18px;
            border: 0;
            border-radius: 12px;
            background: var(--navy);
            box-shadow: 0 8px 18px rgba(38,34,98,.18);
            color: var(--white);
            cursor: pointer;
            font: inherit;
            font-size: 15px;
            font-weight: 700;
            transition: background .15s ease, box-shadow .15s ease;
        }
        .auth-submit::after {
            width: 18px;
            height: 18px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2.6' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M19 12H5M11 6l-6 6 6 6'/%3E%3C/svg%3E") center / contain no-repeat;
            content: "";
        }
        .auth-submit:hover { background: var(--navy-soft); box-shadow: 0 10px 22px rgba(38,34,98,.24); }
        .auth-submit:disabled { cursor: wait; opacity: .85; }
        .auth-submit.is-loading { color: transparent; }
        .auth-submit.is-loading::after {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 22px;
            height: 22px;
            border: 2px solid rgba(255,255,255,.35);
            border-top-color: var(--white);
            border-radius: 50%;
            background: none;
            animation: auth-spin .7s linear infinite;
        }
        @keyframes auth-spin { to { transform: translate(-50%, -50%) rotate(360deg); } }

        .auth-switch { margin: 20px 0 0; color: var(--muted); font-size: 13px; text-align: center; }
        .auth-switch a { color: var(--navy); font-weight: 700; text-decoration: none; }
        .auth-switch a:hover { text-decoration: underline; }

        .auth-page a:focus-visible, .auth-page button:focus-visible { outline: 2px solid var(--orange); outline-offset: 3px; }

        /* ───────── Responsive ───────── */
        @media (max-width: 1000px) {
            .auth-page { grid-template-columns: minmax(0, .9fr) minmax(380px, 1fr); }
            .auth-feature-desc { display: none; }
        }

        @media (max-width: 800px) {
            .auth-page { display: flex; flex-direction: column; }
            .auth-visual { min-height: auto; padding: 22px 22px 64px; }
            .auth-seam-v { display: none; }
            .auth-seam-h { display: block; }
            .auth-brand-mark { width: 42px; height: 42px; border-radius: 13px; }
            .auth-hero { margin-top: 24px; }
            .auth-hero h1 { font-size: 27px; }
            .auth-hero h1 br { display: none; }
            .auth-hero > p { margin-top: 8px; font-size: 13.5px; line-height: 1.9; }
            .auth-features { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 20px; }
            .auth-features li, .auth-features li:last-child { flex-direction: column; gap: 8px; padding: 0; border: 0; text-align: center; }
            .auth-feature-icon { width: 40px; height: 40px; }
            .auth-feature-title { font-size: 12px; }
            .auth-footer { display: none; }
            .auth-form-panel { min-height: auto; flex: 1; align-items: flex-start; padding: 22px 22px 40px; }
            .auth-heading { margin-top: 26px; }
            .auth-heading h2 { font-size: 25px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <div class="auth-page">

        <!-- ═════════ Visual side ═════════ -->
        <aside class="auth-visual" aria-label="مميزات منصة STEP">

            <!-- Vertical seam (desktop): violet depth → orange line → white edge -->
            <div class="auth-seam auth-seam-v" aria-hidden="true">
                <svg viewBox="0 0 100 1000" preserveAspectRatio="none">
                    <path fill="#817DCE" fill-opacity=".22" d="M82 0C112 170 52 340 82 500S112 840 82 1000H0V0Z"/>
                    <path fill="#FFFFFF" d="M62 0C92 170 32 340 62 500S92 840 62 1000H0V0Z"/>
                    <path fill="none" stroke="#efb144" stroke-width="2" stroke-linecap="round" vector-effect="non-scaling-stroke" d="M62 0C92 170 32 340 62 500S92 840 62 1000"/>
                </svg>
            </div>

            <!-- Horizontal seam (mobile) -->
            <div class="auth-seam auth-seam-h" aria-hidden="true">
                <svg viewBox="0 0 1000 40" preserveAspectRatio="none">
                    <path fill="#817DCE" fill-opacity=".22" d="M0 16C250 -4 400 36 600 16S900 -2 1000 12V40H0Z"/>
                    <path fill="#FFFFFF" d="M0 26C250 6 400 46 600 26S900 8 1000 22V40H0Z"/>
                    <path fill="none" stroke="#efb144" stroke-width="2" stroke-linecap="round" vector-effect="non-scaling-stroke" d="M0 26C250 6 400 46 600 26S900 8 1000 22"/>
                </svg>
            </div>

            <a class="auth-brand" href="<?= e(url('/')) ?>">
                <span class="auth-brand-mark" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5 9.5 17 19 7.5"/></svg>
                </span>
                <span class="auth-brand-name">STEP Smart Practice<small>منصة التدريب الذكي</small></span>
            </a>

            <div class="auth-hero">
                <h1>خطوتك القادمة<br><span>تبدأ من هنا.</span></h1>
                <p>تدرّب بذكاء، افهم أخطاءك، وتابع تقدمك في تجربة تعليمية هادئة وواضحة.</p>

                <ul class="auth-features">
                    <li>
                        <span class="auth-feature-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3 8-8"/><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9"/></svg></span>
                        <div>
                            <div class="auth-feature-title">تدريب تفاعلي</div>
                            <div class="auth-feature-desc">أسئلة على نمط الاختبار مع تصحيح فوري.</div>
                        </div>
                    </li>
                    <li>
                        <span class="auth-feature-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19V5m0 14h17"/><path d="M8 15l3-4 3 2 5-7"/></svg></span>
                        <div>
                            <div class="auth-feature-title">تحليل تقدمك</div>
                            <div class="auth-feature-desc">اعرف نقاط قوتك وما يحتاج تركيزاً أكثر.</div>
                        </div>
                    </li>
                    <li>
                        <span class="auth-feature-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 7v5c0 5 3.4 8 8 9 4.6-1 8-4 8-9V7l-8-4Z"/><path d="m9 12 2 2 4-4"/></svg></span>
                        <div>
                            <div class="auth-feature-title">تعلم خطوة بخطوة</div>
                            <div class="auth-feature-desc">مسار واضح يتدرج معك حتى الجاهزية.</div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="auth-footer">© <?= e(date('Y')) ?> STEP Smart Practice · صُمم بواسطة <strong>أ. خديجة الشهري</strong></div>
        </aside>

        <!-- ═════════ Form side ═════════ -->
        <main class="auth-form-panel">
            <div class="auth-form-wrap">
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toggle   = document.getElementById('authPasswordToggle');
            var password = document.getElementById('auth-password');
            var form     = document.querySelector('[data-auth-form]');
            var submit   = form ? form.querySelector('[type="submit"]') : null;
            var meter    = document.getElementById('authStrength');
            var meterTxt = document.getElementById('authStrengthText');

            if (toggle && password) {
                toggle.addEventListener('click', function () {
                    var show = password.type === 'password';
                    password.type = show ? 'text' : 'password';
                    toggle.textContent = show ? 'إخفاء' : 'إظهار';
                    toggle.setAttribute('aria-label', show ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
                });
            }

            if (meter && password) {
                var labels = ['', 'ضعيفة', 'مقبولة', 'جيدة', 'قوية'];
                password.addEventListener('input', function () {
                    var v = password.value, score = 0;
                    if (v.length >= 8) score++;
                    if (/[a-z]/.test(v) && /[A-Z]/.test(v)) score++;
                    if (/\d/.test(v)) score++;
                    if (/[^A-Za-z0-9]/.test(v) || v.length >= 12) score++;
                    if (!v) score = 0;
                    meter.setAttribute('data-level', score);
                    meter.setAttribute('aria-valuenow', String(score));
                    if (meterTxt) meterTxt.textContent = score ? 'قوة كلمة المرور: ' + labels[score] : '';
                });
            }

            if (form && submit) {
                form.addEventListener('submit', function () {
                    if (!form.reportValidity()) return;
                    submit.disabled = true;
                    submit.classList.add('is-loading');
                    submit.setAttribute('aria-busy', 'true');
                });
                window.addEventListener('pageshow', function (e) {
                    if (e.persisted) {
                        submit.disabled = false;
                        submit.classList.remove('is-loading');
                        submit.removeAttribute('aria-busy');
                    }
                });
            }
        });
    </script>
</body>
</html>