<?php
/* ── Navigation model ─────────────────────────────────────────── */
$navGroups = [
    'عام' => [
        ['/admin',           'الرئيسية',          'a-grid'],
    ],
    'المحتوى' => [
        ['/admin/practices', 'التدريبات',         'a-target'],
        ['/admin/questions', 'الأسئلة',           'a-help'],
        ['/admin/passages',  'المقاطع والصوت',    'a-headset'],
        ['/admin/vocab',     'المفردات',          'a-book'],
        ['/admin/skills',    'المهارات والنصائح', 'a-bulb'],
        ['/admin/imports',   'مركز الاستيراد',     'a-upload'],
    ],
    'الإدارة' => [
        ['/admin/users',     'الطلاب',            'a-users'],
        ['/admin/settings',  'الإعدادات',         'a-sliders'],
    ],
];

$curPath  = rtrim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/') ?: '/';
$homePath = rtrim((string) parse_url(url('/admin'), PHP_URL_PATH), '/');

$isActive = function (string $p) use ($curPath, $homePath) {
    $target = rtrim((string) parse_url(url($p), PHP_URL_PATH), '/') ?: '/';
    if ($target === $homePath) {
        return $curPath === $target;
    }
    return $curPath === $target || strpos($curPath, $target . '/') === 0;
};

$activeIcon = 'a-grid';
foreach ($navGroups as $items) {
    foreach ($items as $it) {
        if ($isActive($it[0])) { $activeIcon = $it[2]; }
    }
}

$pageTitle = $title ?? 'لوحة الإدارة';
$success = flash('success');
$error   = flash('error');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#262262">
    <title><?= e($pageTitle) ?></title>
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
            --ink: #211F35;
            --muted: #706E83;
            --line: #E8E7F0;
            --tint: #F1F0FA;
            --ic-accent: #efb144;
            --side-w: 272px;
        }

        body { font-family: 'IBM Plex Sans Arabic', 'Segoe UI', Tahoma, sans-serif; -webkit-font-smoothing: antialiased; }
        html.adm-lock { overflow: hidden; }

        .ic { display: block; width: 20px; height: 20px; flex: none; }
        .ic-sm { width: 17px; height: 17px; }
        .ic-lg { width: 24px; height: 24px; }

        /* ───────── Shell ───────── */
        .adm { display: grid; min-height: 100vh; grid-template-columns: var(--side-w) minmax(0, 1fr); }

        /* ───────── Sidebar ───────── */
        .adm-side {
            position: sticky;
            top: 0;
            z-index: 40;
            display: flex;
            height: 100vh;
            flex-direction: column;
            overflow: hidden;
            color: #fff;
            background:
                radial-gradient(ellipse at 100% 0%, rgba(129,125,206,.32), transparent 45%),
                linear-gradient(170deg, var(--navy-soft) 0%, var(--navy) 55%, var(--navy-deep) 100%);
        }
        .adm-side-art { position: absolute; right: 0; bottom: 0; left: 0; z-index: 0; display: block; width: 100%; height: 190px; pointer-events: none; }
        .adm-side > *:not(.adm-side-art) { position: relative; z-index: 1; }

        .adm-brand { display: flex; align-items: center; gap: 12px; padding: 22px 20px 18px; color: #fff; text-decoration: none; }
        .adm-brand-mark { display: grid; width: 44px; height: 44px; flex: none; place-items: center; border-radius: 14px; background: var(--orange); color: var(--navy); --ic-accent: var(--navy); box-shadow: 0 10px 22px rgba(13,11,52,.3); }
        .adm-brand-name { font-size: 15.5px; font-weight: 700; line-height: 1.3; }
        .adm-brand-name small { display: block; margin-top: 2px; color: rgba(255,255,255,.7); font-size: 11.5px; font-weight: 500; }

        .adm-nav { flex: 1; overflow-y: auto; padding: 4px 14px 24px; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.2) transparent; }
        .adm-group + .adm-group { margin-top: 18px; }
        .adm-group-title { display: flex; align-items: center; gap: 10px; margin: 0 8px 8px; color: rgba(255,255,255,.52); font-size: 12px; font-weight: 600; }
        .adm-group-title::after { height: 1px; flex: 1; background: rgba(255,255,255,.12); content: ""; }

        .adm-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 3px;
            padding: 11px 14px;
            border-radius: 13px;
            color: rgba(255,255,255,.8);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: background .16s ease, color .16s ease;
        }
        .adm-link:hover { background: rgba(255,255,255,.09); color: #fff; }
        .adm-link[aria-current="page"] { background: rgba(255,255,255,.14); color: #fff; font-weight: 600; }
        .adm-link[aria-current="page"]::before { position: absolute; top: 10px; right: -14px; bottom: 10px; width: 4px; border-radius: 4px 0 0 4px; background: var(--orange); content: ""; }

        /* ───────── Main column ───────── */
        .adm-main { position: relative; display: flex; min-width: 0; flex-direction: column; }
        .adm-main-art { position: absolute; top: 0; right: 0; left: 0; z-index: 0; display: block; width: 100%; height: 240px; pointer-events: none; }
        .adm-main > *:not(.adm-main-art) { position: relative; z-index: 1; }

        .adm-top { position: sticky; top: 0; z-index: 30; border-bottom: 1px solid var(--line); background: rgba(255,255,255,.88); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); }
        .adm-top-inner { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 12px clamp(16px, 3vw, 32px); }
        .adm-top-start { display: flex; min-width: 0; align-items: center; gap: 12px; }
        .adm-top-chip { display: grid; width: 42px; height: 42px; flex: none; place-items: center; border: 1px solid var(--line); border-radius: 13px; background: var(--tint); color: var(--navy); }
        .adm-top-title { overflow: hidden; margin: 0; color: var(--navy); font-size: 19px; font-weight: 700; line-height: 1.35; text-overflow: ellipsis; white-space: nowrap; }
        .adm-top-sub { margin: 1px 0 0; color: var(--muted); font-size: 12px; }
        .adm-top-end { display: flex; align-items: center; gap: 10px; }

        .adm-btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 15px; border: 1px solid var(--line); border-radius: 12px; background: #fff; color: var(--navy); font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; transition: background .16s ease, border-color .16s ease, box-shadow .16s ease; }
        .adm-btn:hover { border-color: #C9C7D9; background: var(--tint); }
        .adm-btn--solid { border-color: var(--navy); background: var(--navy); color: #fff; box-shadow: 0 8px 18px -6px rgba(38,34,98,.45); }
        .adm-btn--solid:hover { background: var(--navy-soft); border-color: var(--navy-soft); }
        .adm-btn--solid .ic { color: var(--orange); }

        .adm-burger { display: none; width: 42px; height: 42px; flex: none; place-items: center; border: 1px solid var(--line); border-radius: 13px; background: #fff; color: var(--navy); cursor: pointer; }
        .adm-overlay { position: fixed; inset: 0; z-index: 35; display: none; background: rgba(28,25,75,.5); backdrop-filter: blur(2px); }

        .adm-content { flex: 1; width: 100%; max-width: 1280px; margin-inline: auto; padding: clamp(18px, 3vw, 32px); }
        .adm-page { display: grid; gap: 24px; }
        .adm-page-header {
            position: relative;
            overflow: hidden;
            padding: clamp(20px, 3vw, 30px);
            border: 1px solid var(--line);
            border-radius: 22px;
            background:
                radial-gradient(ellipse at 0% 100%, rgba(239,177,68,.10), transparent 34%),
                linear-gradient(120deg, #fff 0%, #FCFCFE 100%);
            box-shadow: 0 12px 30px -24px rgba(38,34,98,.3);
        }
        .adm-page-header::before { position: absolute; top: 22px; right: 0; bottom: 22px; width: 4px; border-radius: 0 4px 4px 0; background: var(--orange); content: ""; }
        .adm-page-header h2 { margin: 0; color: var(--navy); font-size: clamp(21px, 2.4vw, 28px); font-weight: 700; line-height: 1.4; }
        .adm-page-header p { max-width: 760px; margin: 7px 0 0; color: var(--muted); font-size: 14px; line-height: 1.9; }
        .adm-content .card { box-shadow: 0 1px 2px rgba(38,34,98,.035), 0 14px 32px -26px rgba(38,34,98,.28); }
        .adm-content input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]),
        .adm-content select,
        .adm-content textarea {
            border-color: var(--line);
            border-radius: 12px;
            background-color: #FCFCFE;
            color: var(--ink);
            transition: border-color .16s ease, box-shadow .16s ease, background-color .16s ease;
        }
        .adm-content input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):hover,
        .adm-content select:hover,
        .adm-content textarea:hover { border-color: #C9C7D9; }
        .adm-content input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):focus,
        .adm-content select:focus,
        .adm-content textarea:focus { border-color: var(--navy); outline: none; background-color: #fff; box-shadow: 0 0 0 4px rgba(38,34,98,.08); }
        .adm-content input[type="checkbox"],
        .adm-content input[type="radio"] { accent-color: var(--orange); }
        .adm-content input[type="file"] { max-width: 100%; font-size: 13px; }
        .adm-content details > summary { position: relative; list-style: none; }
        .adm-content details > summary::-webkit-details-marker { display: none; }
        .adm-content details > summary::after { display: inline-grid; width: 25px; height: 25px; flex: none; place-items: center; margin-inline-start: 10px; border-radius: 8px; background: var(--tint); color: var(--navy); content: "+"; font-size: 17px; font-weight: 500; line-height: 1; transition: transform .16s ease, background .16s ease; }
        .adm-content details[open] > summary::after { background: rgba(239,177,68,.2); transform: rotate(45deg); }
        .adm-content button { transition: transform .16s ease, box-shadow .16s ease, background-color .16s ease, border-color .16s ease; }
        .adm-content button:active { transform: translateY(1px); }
        .adm-empty { display: grid; place-items: center; gap: 8px; padding: 32px 20px; border: 1px dashed #D8D6EF; border-radius: 18px; background: rgba(255,255,255,.82); color: var(--muted); text-align: center; }
        @media (prefers-reduced-motion: reduce) {
            .adm-content input, .adm-content select, .adm-content textarea, .adm-content button,
            .adm-content details > summary::after { transition-duration: .01ms !important; }
        }
        .adm-foot { padding: 16px clamp(16px, 3vw, 32px) 22px; color: var(--muted); font-size: 12px; text-align: center; }

        /* ───────── Flash messages ───────── */
        .adm-alert { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 20px; padding: 14px 16px; border: 1px solid; border-radius: 14px; font-size: 14px; line-height: 1.7; }
        .adm-alert .ic { margin-top: 1px; }
        .adm-alert p { flex: 1; margin: 0; }
        .adm-alert-close { display: grid; width: 28px; height: 28px; flex: none; place-items: center; border: 0; border-radius: 8px; background: transparent; color: inherit; cursor: pointer; opacity: .65; }
        .adm-alert-close:hover { background: rgba(0,0,0,.06); opacity: 1; }
        .adm-alert--success { --ic-accent: #2E9D6B; border-color: #B7DCCD; background: #F2FBF6; color: #236747; }
        .adm-alert--error { --ic-accent: #C2414B; border-color: #F2C6CA; background: #FFF4F4; color: #A12F3A; }

        .adm-page-head { display:flex; align-items:flex-end; justify-content:space-between; gap:20px; margin-bottom:22px; }
        .adm-page-head h1 { margin:3px 0 4px; color:var(--navy); font-size:clamp(23px,3vw,30px); }
        .adm-page-head p { margin:0; color:var(--muted); line-height:1.9; }
        .adm-eyebrow,.adm-panel-kicker { color:var(--orange); font-size:12px; font-weight:700; letter-spacing:.04em; }
        .adm-grid-2 { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:18px; }
        .adm-panel { margin-bottom:18px; padding:22px; border:1px solid var(--line); border-radius:20px; background:#fff; box-shadow:0 12px 34px -28px rgba(38,34,98,.38); }
        .adm-panel-head { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:18px; }
        .adm-panel-head h2 { margin:2px 0 0; color:var(--navy); font-size:19px; }
        .adm-actions { display:flex; gap:10px; margin-top:18px; }
        .adm-actions--end { justify-content:flex-end; }
        .adm-btn--accent { border-color:var(--orange); background:var(--orange); color:var(--navy-deep); }
        .adm-btn--accent:hover { border-color:#D99A31; background:#F4BF63; }
        .adm-field { display:grid; gap:7px; color:var(--ink); font-size:13px; font-weight:600; }
        .adm-table-wrap { overflow-x:auto; }
        .adm-table { width:100%; border-collapse:collapse; }
        .adm-table th,.adm-table td { padding:12px 14px; border-bottom:1px solid var(--line); text-align:right; }
        .adm-table th { color:var(--muted); font-size:12px; }
        .status-pill { display:inline-flex; padding:5px 10px; border-radius:999px; font-size:12px; font-weight:700; }
        .status-pill--neutral { background:var(--tint); color:var(--navy); }
        .import-drop { display:grid; min-height:210px; place-items:center; align-content:center; gap:8px; border:1px dashed #CAC8DD; border-radius:18px; background:linear-gradient(145deg,#FCFCFE,var(--tint)); cursor:pointer; text-align:center; }
        .import-drop input { position:absolute; opacity:0; }
        .import-drop strong { color:var(--navy); font-size:16px; }
        .import-drop small { color:var(--muted); }
        .import-drop-icon { display:grid; width:64px; height:64px; place-items:center; border-radius:18px; background:var(--orange); color:var(--navy); font-size:17px; font-weight:800; box-shadow:0 12px 24px rgba(239,177,68,.25); }
        .import-page { display:grid; min-width:0; gap:20px; }
        .import-page > * { min-width:0; }
        .import-hero { position:relative; display:flex; align-items:center; gap:18px; overflow:hidden; padding:clamp(22px,4vw,36px); border:1px solid var(--line); border-radius:24px; background:radial-gradient(ellipse at 0% 100%,rgba(239,177,68,.15),transparent 38%),linear-gradient(125deg,#fff 0%,#F9F8FE 100%); box-shadow:0 16px 38px -32px rgba(38,34,98,.4); }
        .import-hero-icon { display:grid; width:62px; height:62px; flex:none; place-items:center; border:1px solid rgba(38,34,98,.08); border-radius:19px; background:var(--navy); color:var(--orange); box-shadow:0 12px 24px rgba(38,34,98,.18); }
        .import-hero-icon .ic { width:30px; height:30px; }
        .import-hero h2 { margin:3px 0 5px; color:var(--navy); font-size:clamp(23px,3vw,31px); }
        .import-hero p { margin:0; color:var(--muted); line-height:1.8; }
        .import-hero-count { display:flex; flex:none; align-items:center; gap:8px; margin-inline-start:auto; padding:9px 13px; border:1px solid var(--line); border-radius:999px; background:#fff; color:var(--navy); font-size:12px; font-weight:700; }
        .import-hero-count i { width:7px; height:7px; border-radius:50%; background:#35A876; box-shadow:0 0 0 4px rgba(53,168,118,.12); }
        .import-type-grid { display:grid; min-width:0; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; align-items:stretch; }
        .import-type-card { display:flex; min-width:0; flex-direction:column; padding:clamp(18px,2.4vw,25px); border:1px solid var(--line); border-radius:21px; background:#fff; box-shadow:0 12px 34px -28px rgba(38,34,98,.38); }
        .import-type-head { display:flex; align-items:flex-start; gap:13px; margin-bottom:18px; }
        .import-type-icon { display:grid; width:46px; height:46px; flex:none; place-items:center; border-radius:14px; background:var(--tint); color:var(--navy); }
        .import-type-icon .ic { width:23px; height:23px; }
        .import-type-head > div { min-width:0; }
        .import-type-head h2 { margin:1px 0 3px; color:var(--navy); font-size:18px; }
        .import-type-head p { margin:0; color:var(--muted); font-size:13px; line-height:1.7; }
        .import-type-badge { margin-inline-start:auto; padding:5px 9px; border-radius:8px; background:#F5F4FA; color:var(--muted); font-size:11px; font-weight:700; }
        .import-type-card form { display:flex; flex:1; flex-direction:column; }
        .import-drop { position:relative; min-height:160px; padding:18px; transition:border-color .18s ease,background .18s ease,box-shadow .18s ease; }
        .import-drop:hover,.import-drop:focus-within { border-color:var(--navy); background:#F8F7FE; box-shadow:0 0 0 4px rgba(38,34,98,.055); }
        .import-drop:focus-within { outline:2px solid rgba(38,34,98,.3); outline-offset:2px; }
        .import-drop input { inset:0; width:100%; height:100%; cursor:pointer; }
        .import-drop-icon { width:52px; height:52px; border-radius:15px; font-size:14px; }
        .import-drop strong { font-size:15px; }
        .import-drop small { max-width:330px; line-height:1.7; }
        .import-drop--audio { min-height:98px; grid-template-columns:48px minmax(0,1fr); grid-template-rows:auto auto; justify-items:start; align-content:center; margin-top:11px; text-align:right; }
        .import-drop--audio .import-drop-icon { grid-row:1 / 3; width:42px; height:42px; border-radius:13px; font-size:11px; }
        .import-drop--audio strong { align-self:end; }
        .import-drop--audio small { align-self:start; }
        .import-type-card .adm-actions { margin-top:auto; padding-top:16px; }
        .import-type-card .adm-btn { min-height:44px; justify-content:center; }
        .import-csv-columns { display:flex; flex-wrap:wrap; gap:7px; margin:0 0 14px; padding:0; list-style:none; }
        .import-csv-columns li { padding:6px 9px; border:1px solid var(--line); border-radius:9px; background:#FAFAFD; color:#4B4962; direction:ltr; font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; font-size:11px; }
        .import-csv-note { margin:0 0 14px; color:var(--muted); font-size:12px; line-height:1.8; }
        .import-csv-drop { min-height:160px; }
        .import-workflow { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; padding:18px; border:1px solid var(--line); border-radius:18px; background:rgba(255,255,255,.82); }
        .import-workflow-step { display:flex; align-items:center; gap:11px; min-width:0; }
        .import-workflow-step b { display:grid; width:33px; height:33px; flex:none; place-items:center; border-radius:11px; background:var(--tint); color:var(--navy); font-size:13px; }
        .import-workflow-step span { display:grid; min-width:0; }
        .import-workflow-step strong { color:var(--ink); font-size:13px; }
        .import-workflow-step small { color:var(--muted); font-size:11px; line-height:1.6; }
        .import-preview { scroll-margin-top:90px; }
        .import-audio-row { display:grid; grid-template-columns:minmax(0,1fr) minmax(180px,.8fr) minmax(180px,.8fr); align-items:center; gap:14px; padding:12px 0; border-top:1px solid var(--line); }
        .import-audio-row > div { display:grid; min-width:0; }
        .import-audio-row strong { overflow-wrap:anywhere; color:var(--ink); }
        .import-audio-row small { color:var(--muted); font-size:12px; }
        .import-audio-row audio { width:100%; max-width:100%; }
        .import-media-links { display:grid; gap:7px; margin:0 0 18px; padding:14px 16px; border-radius:14px; background:#fff9ed; color:#704d16; }
        .import-media-links a { overflow-wrap:anywhere; color:var(--navy); }
        .import-media-links small { color:var(--muted); }
        .import-history-title { display:flex; align-items:center; gap:10px; }
        .import-history-count { display:inline-grid; min-width:28px; height:28px; place-items:center; padding:0 7px; border-radius:9px; background:var(--tint); color:var(--navy); font-size:12px; font-weight:700; }
        .import-policy { display:grid; gap:14px; }
        .import-policy > div { display:flex; align-items:center; gap:12px; }
        .import-policy > div > b { display:grid; width:34px; height:34px; place-items:center; border-radius:11px; background:var(--tint); color:var(--navy); }
        .import-policy span { display:grid; }
        .import-policy small { margin-top:2px; color:var(--muted); line-height:1.6; }
        .import-destinations { margin-bottom:18px; }
        .import-items { display:grid; gap:9px; }
        .import-item { display:grid; grid-template-columns:auto 1fr auto; align-items:center; gap:12px; padding:13px; border:1px solid var(--line); border-radius:14px; cursor:pointer; }
        .import-item span { display:grid; min-width:0; }
        .import-item strong { overflow:hidden; color:var(--ink); text-overflow:ellipsis; white-space:nowrap; }
        .import-item small { color:var(--muted); }
        .import-item em { color:#2E9D6B; font-size:12px; font-style:normal; }
        .import-item.is-duplicate { background:#FFF8F0; }
        .import-item.is-duplicate em { color:#A26A17; }
        @media (max-width:760px) { .adm-grid-2 { grid-template-columns:1fr; } .import-item { grid-template-columns:auto 1fr; } .import-item em { grid-column:2; } }
        @media (max-width:800px) { .import-type-grid { grid-template-columns:1fr; } .import-workflow { grid-template-columns:1fr; } .import-audio-row { grid-template-columns:1fr; } }
        @media (max-width:560px) { .import-hero { align-items:flex-start; flex-wrap:wrap; } .import-hero-count { margin-inline-start:0; } .import-type-badge { font-size:10px; } .import-type-card { padding:17px; } .import-drop { min-height:145px; } }

        .adm a:focus-visible, .adm button:focus-visible { outline: 3px solid rgba(239,177,68,.85); outline-offset: 2px; }

        /* ───────── Responsive ───────── */
        @media (max-width: 1024px) {
            .adm { display: block; overflow-x: clip; }
            .adm-side { position: fixed; top: 0; right: 0; bottom: 0; width: min(86vw, 308px); height: 100%; box-shadow: -24px 0 60px rgba(13,11,52,.35); transform: translateX(105%); transition: transform .26s ease; visibility: hidden; }
            .adm-side.is-open { transform: translateX(0); visibility: visible; }
            .adm-overlay.is-open { display: block; }
            .adm-burger { display: grid; }
            .adm-top-chip { display: none; }
        }
        @media (max-width: 560px) {
            .adm-btn-label { display: none; }
            .adm-btn { padding: 10px; }
            .adm-top-title { font-size: 17px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: .01ms !important; }
        }
    </style>
</head>
<body class="bg-[#F7F7FB] text-ink min-h-screen">

    <!-- Duotone icon sprite -->
    <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
        <defs>
            <symbol id="a-stairs" viewBox="0 0 24 24">
                <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 20h5v-5h5v-5h4"/></g>
                <circle cx="18.5" cy="6" r="2.6" style="fill:var(--ic-accent)"/>
            </symbol>
            <symbol id="a-grid" viewBox="0 0 24 24">
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"><rect x="3.5" y="3.5" width="7" height="7" rx="2"/><rect x="13.5" y="3.5" width="7" height="7" rx="2"/><rect x="3.5" y="13.5" width="7" height="7" rx="2"/></g>
                <rect x="13.5" y="13.5" width="7" height="7" rx="2" style="fill:var(--ic-accent)"/>
            </symbol>
            <symbol id="a-target" viewBox="0 0 24 24">
                <g fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5.2"/></g>
                <circle cx="12" cy="12" r="2.2" style="fill:var(--ic-accent)"/>
            </symbol>
            <symbol id="a-help" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" style="fill:var(--ic-accent);opacity:.25"/>
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M9.6 9.6a2.5 2.5 0 1 1 3.8 2.1c-.9.5-1.4 1.1-1.4 2M12 16.9v.1"/></g>
            </symbol>
            <symbol id="a-upload" viewBox="0 0 24 24">
                <path d="M12 14V4m0 0L8.5 7.5M12 4l3.5 3.5M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 4 8.5 7.5h7Z" style="fill:var(--ic-accent);opacity:.5"/>
            </symbol>
            <symbol id="a-file" viewBox="0 0 24 24">
                <path d="M6 3.5h8l4 4V20a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 6 20Z" style="fill:var(--ic-accent);opacity:.2"/>
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8V5a1.5 1.5 0 0 1 1.5-1.5h6.5l4 4V19a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 6 19v-7"/><path d="M14 3.8V8h4.2M12 12.5H8M15 16H8"/></g>
            </symbol>
            <symbol id="a-headset" viewBox="0 0 24 24">
                <rect x="3.5" y="13" width="4" height="6.5" rx="1.8" style="fill:var(--ic-accent);opacity:.4"/>
                <rect x="16.5" y="13" width="4" height="6.5" rx="1.8" style="fill:var(--ic-accent);opacity:.4"/>
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="3.5" y="13" width="4" height="6.5" rx="1.8"/><rect x="16.5" y="13" width="4" height="6.5" rx="1.8"/><path d="M18.5 19.5c0 1.3-1.6 2-4 2h-1"/></g>
            </symbol>
            <symbol id="a-book" viewBox="0 0 24 24">
                <path d="M12 6.5C10.3 5.2 8 4.7 4.5 5v12.5c3.5-.3 5.8.2 7.5 1.5Z" style="fill:var(--ic-accent);opacity:.35"/>
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6.5C10.3 5.2 8 4.7 4.5 5v12.5c3.5-.3 5.8.2 7.5 1.5 1.7-1.3 4-1.8 7.5-1.5V5C16 4.7 13.7 5.2 12 6.5ZM12 6.5V19"/></g>
            </symbol>
            <symbol id="a-bulb" viewBox="0 0 24 24">
                <path d="M12 3a6 6 0 0 0-3.6 10.8c.6.5 1.1 1.2 1.1 2V16h5v-.2c0-.8.5-1.5 1.1-2A6 6 0 0 0 12 3Z" style="fill:var(--ic-accent);opacity:.4"/>
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0-3.6 10.8c.6.5 1.1 1.2 1.1 2V16h5v-.2c0-.8.5-1.5 1.1-2A6 6 0 0 0 12 3Z"/><path d="M9.5 18.5h5M10.5 21h3"/></g>
            </symbol>
            <symbol id="a-users" viewBox="0 0 24 24">
                <circle cx="9" cy="8.5" r="3.3" style="fill:var(--ic-accent);opacity:.4"/>
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8.5" r="3.3"/><path d="M2.8 19.5c.6-3.2 3.1-4.9 6.2-4.9s5.6 1.7 6.2 4.9"/><circle cx="17" cy="9.5" r="2.6"/><path d="M16.6 14.8c2.4.1 4.1 1.6 4.7 4.2"/></g>
            </symbol>
            <symbol id="a-sliders" viewBox="0 0 24 24">
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M4 7h8.8M17.2 7H20M4 17h2.8M11.2 17H20"/></g>
                <circle cx="15" cy="7" r="2.3" style="fill:var(--ic-accent)"/>
                <circle cx="9" cy="17" r="2.3" style="fill:var(--ic-accent)"/>
            </symbol>
            <symbol id="a-globe" viewBox="0 0 24 24">
                <path d="M12 3c2.6 2.7 3.9 5.7 3.9 9S14.6 18.3 12 21c-2.6-2.7-3.9-5.7-3.9-9S9.4 5.7 12 3Z" style="fill:var(--ic-accent);opacity:.3"/>
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.6 2.7 3.9 5.7 3.9 9S14.6 18.3 12 21c-2.6-2.7-3.9-5.7-3.9-9S9.4 5.7 12 3Z"/></g>
            </symbol>
            <symbol id="a-logout" viewBox="0 0 24 24">
                <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M10 4.5H6.5a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2H10"/><path d="m14 8-4 4 4 4M10 12h9.5" style="stroke:var(--ic-accent)"/></g>
            </symbol>
            <symbol id="a-menu" viewBox="0 0 24 24">
                <g fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><path d="M4 7h16M4 12h16"/><path d="M4 17h9" style="stroke:var(--ic-accent)"/></g>
            </symbol>
            <symbol id="a-close" viewBox="0 0 24 24">
                <g fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></g>
            </symbol>
            <symbol id="a-check-circle" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" style="fill:var(--ic-accent);opacity:.25"/>
                <g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m8 12.5 2.7 2.7 5.3-5.4"/></g>
            </symbol>
            <symbol id="a-alert" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" style="fill:var(--ic-accent);opacity:.22"/>
                <g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7.8v5M12 16.4v.1"/></g>
            </symbol>
        </defs>
    </svg>

    <div class="adm">

        <!-- ═════════ Sidebar ═════════ -->
        <aside class="adm-side" id="admSide" aria-label="قائمة الإدارة">
            <svg class="adm-side-art" viewBox="0 0 272 190" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0 80C60 50 110 110 170 82S250 56 272 70V190H0Z" fill="#1C194B" fill-opacity=".5"/>
                <g fill="none" stroke-linecap="round">
                    <path d="M0 62C60 32 110 92 170 64S250 38 272 52" stroke="#efb144" stroke-opacity=".45" stroke-width="1.6" vector-effect="non-scaling-stroke"/>
                    <path d="M0 96C60 66 110 126 170 98S250 72 272 86" stroke="#fff" stroke-opacity=".12" stroke-width="1.4" vector-effect="non-scaling-stroke"/>
                    <path d="M0 130C60 100 110 160 170 132S250 106 272 120" stroke="#817DCE" stroke-opacity=".4" stroke-width="1.4" vector-effect="non-scaling-stroke"/>
                </g>
            </svg>

            <a class="adm-brand" href="<?= e(url('/admin')) ?>">
                <span class="adm-brand-mark" aria-hidden="true"><svg class="ic ic-lg"><use href="#a-stairs"/></svg></span>
                <span class="adm-brand-name">STEP Smart Practice<small>لوحة الإدارة</small></span>
            </a>

            <nav class="adm-nav" aria-label="التنقل الرئيسي">
                <?php foreach ($navGroups as $groupLabel => $items): ?>
                    <div class="adm-group">
                        <p class="adm-group-title"><?= e($groupLabel) ?></p>
                        <?php foreach ($items as [$path, $label, $icon]): ?>
                            <a class="adm-link" href="<?= e(url($path)) ?>"<?= $isActive($path) ? ' aria-current="page"' : '' ?>>
                                <svg class="ic" aria-hidden="true"><use href="#<?= e($icon) ?>"/></svg>
                                <span><?= e($label) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </nav>
        </aside>
        <div class="adm-overlay" id="admOverlay"></div>

        <!-- ═════════ Main ═════════ -->
        <div class="adm-main">
            <svg class="adm-main-art" viewBox="0 0 1200 240" preserveAspectRatio="none" aria-hidden="true">
                <defs>
                    <linearGradient id="admFade" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#262262" stop-opacity=".07"/><stop offset="1" stop-color="#262262" stop-opacity="0"/></linearGradient>
                </defs>
                <path d="M0 0H1200V120C1020 168 900 96 720 132S420 190 240 150S60 120 0 140Z" fill="url(#admFade)"/>
                <path d="M0 140C60 120 140 150 240 150S420 190 720 132S1020 168 1200 120" fill="none" stroke="#efb144" stroke-opacity=".5" stroke-width="1.6" vector-effect="non-scaling-stroke"/>
            </svg>

            <header class="adm-top">
                <div class="adm-top-inner">
                    <div class="adm-top-start">
                        <button type="button" class="adm-burger" id="admBurger" aria-label="فتح القائمة" aria-controls="admSide" aria-expanded="false">
                            <svg class="ic" aria-hidden="true"><use href="#a-menu"/></svg>
                        </button>
                        <span class="adm-top-chip" aria-hidden="true"><svg class="ic"><use href="#<?= e($activeIcon) ?>"/></svg></span>
                        <div style="min-width:0">
                            <h1 class="adm-top-title"><?= e($pageTitle) ?></h1>
                            <p class="adm-top-sub">لوحة الإدارة</p>
                        </div>
                    </div>
                    <div class="adm-top-end">
                        <a class="adm-btn" href="<?= e(url('/')) ?>" aria-label="عرض المنصة"><svg class="ic ic-sm" aria-hidden="true"><use href="#a-globe"/></svg><span class="adm-btn-label">عرض المنصة</span></a>
                        <a class="adm-btn adm-btn--solid" href="<?= e(url('/logout')) ?>" aria-label="تسجيل الخروج"><svg class="ic ic-sm" aria-hidden="true"><use href="#a-logout"/></svg><span class="adm-btn-label">تسجيل الخروج</span></a>
                    </div>
                </div>
            </header>

            <main class="adm-content">
                <?php if ($success): ?>
                    <div class="adm-alert adm-alert--success" role="status">
                        <svg class="ic" aria-hidden="true"><use href="#a-check-circle"/></svg>
                        <p><?= e($success) ?></p>
                        <button type="button" class="adm-alert-close" data-dismiss aria-label="إغلاق"><svg class="ic ic-sm" aria-hidden="true"><use href="#a-close"/></svg></button>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="adm-alert adm-alert--error" role="alert">
                        <svg class="ic" aria-hidden="true"><use href="#a-alert"/></svg>
                        <p><?= e($error) ?></p>
                        <button type="button" class="adm-alert-close" data-dismiss aria-label="إغلاق"><svg class="ic ic-sm" aria-hidden="true"><use href="#a-close"/></svg></button>
                    </div>
                <?php endif; ?>

                <?= $content ?? '' ?>
            </main>

            <footer class="adm-foot">© <?= e(date('Y')) ?> STEP Smart Practice · لوحة الإدارة · صُمم بواسطة <strong>أ. خديجة الشهري</strong></footer>
        </div>
    </div>

    <script>
        (function () {
            var side = document.getElementById('admSide');
            var overlay = document.getElementById('admOverlay');
            var burger = document.getElementById('admBurger');
            var root = document.documentElement;

            function setOpen(open) {
                side.classList.toggle('is-open', open);
                overlay.classList.toggle('is-open', open);
                root.classList.toggle('adm-lock', open);
                burger.setAttribute('aria-expanded', open ? 'true' : 'false');
                burger.setAttribute('aria-label', open ? 'إغلاق القائمة' : 'فتح القائمة');
                if (open) { var first = side.querySelector('.adm-link'); if (first) first.focus(); } else { burger.focus({ preventScroll: true }); }
            }

            burger.addEventListener('click', function () { setOpen(!side.classList.contains('is-open')); });
            overlay.addEventListener('click', function () { setOpen(false); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && side.classList.contains('is-open')) setOpen(false); });
            window.matchMedia('(min-width: 1025px)').addEventListener('change', function (m) {
                if (m.matches) { side.classList.remove('is-open'); overlay.classList.remove('is-open'); root.classList.remove('adm-lock'); burger.setAttribute('aria-expanded', 'false'); }
            });

            document.querySelectorAll('[data-dismiss]').forEach(function (b) {
                b.addEventListener('click', function () { var a = b.closest('.adm-alert'); if (a) a.remove(); });
            });
        })();
    </script>
    <script src="<?= e(asset('js/admin.js') . '?v=' . filemtime(ROOT_PATH . '/public/js/admin.js')) ?>" defer></script>
</body>
</html>