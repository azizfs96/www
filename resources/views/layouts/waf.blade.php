<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WAF Dashboard')</title>
    <style>
        :root {
            --app-bg: #000000;
            --content-text: #e5e7eb;
            --sidebar-bg: #000000;
            --sidebar-panel: #000000;
            --sidebar-border: rgba(255, 255, 255, 0.10);
            --sidebar-text: #edf2ff;
            --sidebar-muted: #b2b9cc;
            --sidebar-hover: rgba(255, 255, 255, 0.06);
            --sidebar-active-bg: #4b5265;
            --sidebar-active-text: #f8faff;
            --sidebar-active-icon: #f8faff;
            --badge-red: #dc3545;
            --sidebar-width: 256px;
            --sidebar-collapsed-width: 72px;
            --item-height: 36px;
            --radius-sm: 8px;
            --z-sidebar: 1000;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: "Inter", "Segoe UI", Roboto, Arial, sans-serif; }
        html, body { min-height: 100vh; background: var(--app-bg); color: var(--content-text); overflow-x: hidden; }
        .app-container { display: flex; min-height: 100vh; width: 100%; position: relative; background: #000000; isolation: isolate; }
        .app-container::before {
            content: "";
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: #000000;
            z-index: calc(var(--z-sidebar) - 1);
            pointer-events: none;
        }

        .gcs-sidebar {
            width: var(--sidebar-width); min-width: var(--sidebar-width); background: #000000;
            border: 1px solid var(--sidebar-border); border-left: none; border-radius: 0 14px 14px 0;
            box-shadow: 0 12px 34px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.02);
            height: 100vh; position: fixed; left: 0; top: 0;
            z-index: var(--z-sidebar); display: flex; flex-direction: column; transition: width .18s ease, min-width .18s ease, transform .2s ease;
            overflow: hidden;
            isolation: isolate;
        }
        .gcs-sidebar::before {
            content: "";
            position: absolute;
            top: -1px;
            right: -1px;
            width: 18px;
            height: 18px;
            background: #000000;
            border-bottom-left-radius: 14px;
            pointer-events: none;
            z-index: 2;
        }
        .gcs-sidebar::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 0 14px 14px 0;
            box-shadow: inset -1px 0 0 rgba(255, 255, 255, 0.05);
            pointer-events: none;
        }
        .gcs-sidebar.is-collapsed { width: var(--sidebar-collapsed-width); min-width: var(--sidebar-collapsed-width); }
        .gcs-sidebar__top { padding: 10px 12px 8px; }
        .gcs-top-row { height: 42px; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .gcs-brand { display: inline-flex; align-items: center; gap: 10px; min-width: 0; color: var(--sidebar-text); }
        .gcs-brand__icon { width: 18px; height: 18px; color: #8be38f; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .gcs-brand__logo { height: 55px; width: auto; max-width: 273px; display: block; object-fit: contain; }
        .gcs-brand__label { font-size: 15px; line-height: 20px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .gcs-star-btn, .gcs-collapse-btn { width: 28px; height: 28px; border: none; background: transparent; border-radius: var(--radius-sm); color: var(--sidebar-muted); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }
        .gcs-star-btn:hover, .gcs-collapse-btn:hover { background: var(--sidebar-hover); color: var(--sidebar-text); }

        .gcs-sidebar__quick { padding: 0 12px 10px; }
        .gcs-quick-search { height: 36px; border: 1px solid transparent; border-radius: 8px; display: flex; align-items: center; gap: 8px; padding: 0 10px; color: var(--sidebar-muted); font-size: 13px; line-height: 20px; background: rgba(255,255,255,.08); }

        .gcs-sidebar__nav { flex: 1; overflow: auto; padding: 4px 10px 8px; }
        .gcs-section { margin-bottom: 8px; }
        .gcs-section__title { font-size: 12px; line-height: 16px; color: #c6ccdc; padding: 4px 10px; }
        .gcs-group { margin-bottom: 2px; }
        .gcs-group__toggle { width: 100%; height: var(--item-height); border: none; border-radius: var(--radius-sm); background: transparent; color: var(--sidebar-text); display: flex; align-items: center; justify-content: space-between; padding: 0 10px; font-size: 14px; line-height: 20px; font-weight: 500; cursor: pointer; }
        .gcs-group__toggle:hover { background: var(--sidebar-hover); }
        .gcs-group__chevron { color: var(--sidebar-muted); display: inline-flex; align-items: center; justify-content: center; transition: transform .18s ease; }
        .gcs-group.is-open .gcs-group__chevron { transform: rotate(180deg); }
        .gcs-group__submenu { max-height: 0; overflow: hidden; transition: max-height .2s ease; padding-left: 0; }
        .gcs-group.is-open .gcs-group__submenu { max-height: 260px; }

        .gcs-item { height: var(--item-height); display: flex; align-items: center; gap: 12px; padding: 0 10px; border-radius: var(--radius-sm); color: var(--sidebar-text); text-decoration: none; font-size: 14px; line-height: 20px; font-weight: 400; white-space: nowrap; position: relative; transition: background-color .16s ease, color .16s ease; margin-bottom: 2px; }
        .gcs-item:hover { background: var(--sidebar-hover); }
        .gcs-item.is-active { background: var(--sidebar-active-bg); color: var(--sidebar-active-text); font-weight: 500; }
        .gcs-item__icon { width: 18px; height: 18px; color: var(--sidebar-muted); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .gcs-item.is-active .gcs-item__icon { color: var(--sidebar-active-icon); }
        .gcs-item__label, .gcs-group__title, .gcs-brand__label, .gcs-quick-search__label { transition: opacity .12s ease; }

        .gcs-sidebar__footer { border-top: 1px solid rgba(255, 255, 255, 0.08); padding: 8px 10px; }
        .gcs-item--footer { color: var(--sidebar-muted); }
        .gcs-footer-user { padding: 8px 10px 10px; color: var(--sidebar-muted); font-size: 12px; line-height: 16px; }
        .gcs-footer-user strong { display: block; color: var(--sidebar-text); font-size: 13px; line-height: 18px; margin-bottom: 2px; font-weight: 500; }
        .gcs-divider { height: 1px; background: rgba(255, 255, 255, 0.08); margin: 8px 0; }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left .18s ease;
            background: #000000 !important;
            background-color: #000000 !important;
        }
        .app-container.is-sidebar-collapsed .main-content { margin-left: var(--sidebar-collapsed-width); }
        .content-wrapper {
            padding: 24px 32px;
            max-width: 1600px;
            margin: 0 auto;
            color: var(--content-text);
            background: #000000 !important;
            background-color: #000000 !important;
        }
        .gcs-inline-tools { margin-bottom: 12px; }

        .gcs-sidebar.is-collapsed .gcs-brand__label, .gcs-sidebar.is-collapsed .gcs-group__title, .gcs-sidebar.is-collapsed .gcs-item__label, .gcs-sidebar.is-collapsed .gcs-section__title, .gcs-sidebar.is-collapsed .gcs-quick-search__label, .gcs-sidebar.is-collapsed .gcs-star-btn, .gcs-sidebar.is-collapsed .gcs-footer-user { opacity: 0; pointer-events: none; width: 0; overflow: hidden; }
        .gcs-sidebar.is-collapsed .gcs-group__toggle, .gcs-sidebar.is-collapsed .gcs-item { justify-content: center; padding: 0; gap: 0; }
        .gcs-sidebar.is-collapsed .gcs-group__chevron { display: none; }
        .gcs-sidebar.is-collapsed .gcs-group__submenu { display: none; }
        .gcs-sidebar.is-collapsed .gcs-quick-search { justify-content: center; padding: 0; }
        .gcs-sidebar.is-collapsed [data-tooltip] { position: relative; }
        .gcs-sidebar.is-collapsed [data-tooltip]:hover::after { content: attr(data-tooltip); position: absolute; left: calc(100% + 10px); top: 50%; transform: translateY(-50%); background: #1f2937; color: #fff; border-radius: 4px; padding: 4px 8px; font-size: 12px; line-height: 16px; white-space: nowrap; z-index: 1200; pointer-events: none; }

        .badge-red { margin-left: auto; min-width: 20px; height: 18px; border-radius: 999px; background: var(--badge-red); color: #fff; font-size: 11px; line-height: 18px; text-align: center; padding: 0 6px; font-weight: 600; }

        .mobile-menu-btn { display: none; position: fixed; top: 20px; left: 20px; z-index: 1001; background: #fff; border: 1px solid #d9dde5; border-radius: 8px; padding: 10px; color: #374151; cursor: pointer; font-size: 20px; }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.45); z-index: 999; }
        .sidebar-overlay.open { display: block; }

        @media (max-width: 1024px) {
            .gcs-sidebar { transform: translateX(-100%); }
            .gcs-sidebar.open { transform: translateX(0); }
            .main-content, .app-container.is-sidebar-collapsed .main-content { margin-left: 0; }
            .content-wrapper { padding: 16px; }
            .mobile-menu-btn { display: block; }
        }

        /* ===== Platform Topbar ===== */
        .gcs-topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            gap: 14px;
            height: 58px;
            padding: 0 24px;
            background: rgba(0, 0, 0, 0.72);
            border-bottom: 1px solid rgba(255, 255, 255, 0.10);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .gcs-topbar__toggle {
            width: 34px; height: 34px; flex: 0 0 auto;
            display: inline-flex; align-items: center; justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 8px;
            background: transparent; color: var(--sidebar-muted); cursor: pointer;
            transition: background .15s, color .15s, border-color .15s;
        }
        .gcs-topbar__toggle:hover { background: rgba(255,255,255,.06); color: #fff; border-color: rgba(255,255,255,.22); }

        .gcs-topbar__search {
            position: relative; flex: 1; max-width: 440px;
        }
        .gcs-topbar__search svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--sidebar-muted); pointer-events: none; }
        .gcs-topbar__search input {
            width: 100%; height: 38px; padding: 0 12px 0 38px;
            background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.12);
            border-radius: 9px; color: #edf2ff; font-size: 13px; transition: all .15s;
        }
        .gcs-topbar__search input::placeholder { color: #6b7280; }
        .gcs-topbar__search input:focus { outline: none; background: rgba(255,255,255,.07); border-color: rgba(157,78,221,.55); box-shadow: 0 0 0 3px rgba(157,78,221,.15); }

        .gcs-topbar__right { margin-left: auto; display: flex; align-items: center; gap: 10px; }
        .gcs-env-badge {
            display: inline-flex; align-items: center; gap: 7px; height: 34px; padding: 0 12px;
            border: 1px solid rgba(255,255,255,.12); border-radius: 8px; background: rgba(255,255,255,.04);
            color: #d1d5db; font-size: 12px; font-weight: 600; white-space: nowrap;
        }
        .gcs-env-badge .dot { width: 7px; height: 7px; border-radius: 999px; background: #4ade80; box-shadow: 0 0 8px rgba(74,222,128,.7); }

        .gcs-market-btn {
            display: inline-flex; align-items: center; gap: 7px; height: 34px; padding: 0 13px;
            border: 1px solid rgba(157,78,221,.4); border-radius: 8px;
            background: rgba(157,78,221,.12); color: #d8b4fe;
            font-size: 12px; font-weight: 600; white-space: nowrap; text-decoration: none;
            transition: all .15s;
        }
        .gcs-market-btn:hover { background: rgba(157,78,221,.22); border-color: rgba(157,78,221,.6); color: #ede9fe; }
        .gcs-market-btn svg { flex: 0 0 auto; }

        .gcs-topbar__icon-btn {
            position: relative; width: 36px; height: 36px;
            display: inline-flex; align-items: center; justify-content: center;
            border: none; background: transparent; border-radius: 8px;
            color: var(--sidebar-muted); cursor: pointer; transition: background .15s, color .15s;
        }
        .gcs-topbar__icon-btn:hover { background: rgba(255,255,255,.06); color: #fff; }
        .gcs-topbar__icon-btn .ping { position: absolute; top: 7px; right: 8px; width: 7px; height: 7px; border-radius: 999px; background: #ef4444; box-shadow: 0 0 0 2px #000; }
        .gcs-topbar__icon-btn .ping::after { content: ""; position: absolute; inset: 0; border-radius: 999px; background: #ef4444; animation: gcsPing 1.6s cubic-bezier(0,0,.2,1) infinite; }
        @keyframes gcsPing { 75%, 100% { transform: scale(2.4); opacity: 0; } }

        .gcs-topbar__divider { width: 1px; height: 24px; background: rgba(255,255,255,.12); }

        .gcs-topbar__user { display: inline-flex; align-items: center; gap: 10px; padding: 4px 6px; border-radius: 9px; text-decoration: none; transition: background .15s; }
        .gcs-topbar__user:hover { background: rgba(255,255,255,.06); }
        .gcs-topbar__avatar {
            width: 32px; height: 32px; flex: 0 0 auto; border-radius: 999px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: #fff;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
        }
        .gcs-topbar__user-meta { line-height: 1.2; }
        .gcs-topbar__user-name { font-size: 12.5px; font-weight: 600; color: #edf2ff; }
        .gcs-topbar__user-role { font-size: 11px; color: var(--sidebar-muted); }
        .gcs-logout-btn { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,.12); background: transparent; border-radius: 8px; color: var(--sidebar-muted); cursor: pointer; transition: all .15s; }
        .gcs-logout-btn:hover { color: #fca5a5; border-color: rgba(248,113,113,.4); background: rgba(248,113,113,.1); }

        @media (max-width: 768px) {
            .gcs-topbar { padding: 0 14px; gap: 10px; }
            .gcs-topbar__search { max-width: none; }
            .gcs-env-badge, .gcs-topbar__user-meta { display: none; }
        }

        @yield('styles')
    </style>
    <style>
        /* Global white-ish card borders across all WAF pages */
        .content-wrapper .stat-card,
        .content-wrapper .panel,
        .content-wrapper .chart-panel,
        .content-wrapper .card,
        .content-wrapper .events-list,
        .content-wrapper .filters-container,
        .content-wrapper .table-container,
        .content-wrapper .form-container,
        .content-wrapper .site-card,
        .content-wrapper .rule-card {
            background: #0e0e0e !important;
            border: 1px solid rgba(255, 255, 255, 0.26) !important;
            outline: none !important;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08) !important;
        }
    </style>
</head>
<body>
@php
    $icons = [
        'leaf' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M19 5c-6 0-10 4-10 10 6 0 10-4 10-10z"></path><path d="M9 15c0-3 2-6 5-8"></path></svg>',
        'home' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 10l9-7 9 7"></path><path d="M5 10v10h14V10"></path></svg>',
        'task' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="16" height="16" rx="2"></rect><path d="M8 12l2 2 6-6"></path></svg>',
        'activity' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12h4l2-4 4 8 2-4h6"></path></svg>',
        'shield' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3l7 3v6c0 4.6-2.8 7.7-7 9-4.2-1.3-7-4.4-7-9V6l7-3z"></path></svg>',
        'users' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"></circle><circle cx="17" cy="9" r="2"></circle><path d="M3 19c1.4-3 4-5 6-5s4.6 2 6 5"></path></svg>',
        'bell' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9a6 6 0 1112 0v5l2 2H4l2-2V9z"></path><path d="M10 19a2 2 0 004 0"></path></svg>',
        'settings' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-1.8-.3 1.6 1.6 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.2a1.6 1.6 0 0 0-1-1.5 1.6 1.6 0 0 0-1.8.3l-.1.1a2 2 0 0 1-2.8-2.8l.1-.1a1.6 1.6 0 0 0 .3-1.8 1.6 1.6 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.2a1.6 1.6 0 0 0 1.5-1 1.6 1.6 0 0 0-.3-1.8l-.1-.1a2 2 0 0 1 2.8-2.8l.1.1a1.6 1.6 0 0 0 1.8.3h0a1.6 1.6 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.2a1.6 1.6 0 0 0 1 1.5 1.6 1.6 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0-.3 1.8v0a1.6 1.6 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.2a1.6 1.6 0 0 0-1.4 1z"></path></svg>',
        'info' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>',
        'chevron' => '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 10l4 4 4-4"></path></svg>',
        'logout' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><path d="M16 17l5-5-5-5"></path><path d="M21 12H9"></path></svg>',
        'search' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"></circle><path d="M20 20l-3.5-3.5"></path></svg>',
        'sun' => '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"></path></svg>',
        'moon' => '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.8A9 9 0 1111.2 3 7 7 0 0021 12.8z"></path></svg>',
    ];

    $menuGroups = [
        [
            'id' => 'waf',
            'title' => 'WAF',
            'items' => [
                ['label' => 'Dashboard', 'href' => '/waf', 'icon' => $icons['home'], 'active' => request()->is('waf')],
                ['label' => 'Event Log', 'href' => '/waf/events', 'icon' => $icons['task'], 'active' => request()->is('waf/events*')],
                ['label' => 'Firewall Rules', 'href' => '/waf/firewall', 'icon' => $icons['activity'], 'active' => request()->is('waf/firewall*')],
            ],
        ],
        [
            'id' => 'soc',
            'title' => 'Security Center (SOC)',
            'items' => [
                ['label' => 'Dashboard', 'href' => '/waf/soc', 'icon' => $icons['home'], 'active' => request()->is('waf/soc')],
                ['label' => 'Alerts', 'href' => '/waf/soc/alerts', 'icon' => $icons['bell'], 'active' => request()->is('waf/soc/alerts*')],
                ['label' => 'Incidents', 'href' => '/waf/soc/incidents', 'icon' => $icons['task'], 'active' => request()->is('waf/soc/incidents*')],
                ['label' => 'Attack Analysis', 'href' => '/waf/soc/attack-analysis', 'icon' => $icons['activity'], 'active' => request()->is('waf/soc/attack-analysis*')],
                ['label' => 'Assets', 'href' => '/waf/soc/assets', 'icon' => $icons['shield'], 'active' => request()->is('waf/soc/assets*')],
            ],
        ],
        [
            'id' => 'sites',
            'title' => 'Sites',
            'items' => [
                ['label' => 'Site Management', 'href' => '/waf/sites', 'icon' => $icons['users'], 'active' => request()->is('waf/sites*')],
            ],
        ],
    ];
@endphp

<div class="app-container" id="appContainer">
    <x-sidebar :collapsed="false">
        <x-slot:top>
            <div class="gcs-top-row">
                <div class="gcs-brand" data-tooltip="WAF Gate" title="WAF Gate">
                    <img src="{{ asset('images/Logo.png') }}" alt="WAF Gate Logo" class="gcs-brand__logo">
                </div>
                <button type="button" class="gcs-star-btn" aria-label="Favorite" title="Favorite">●</button>
            </div>
        </x-slot:top>

        <x-slot:quick>
            <div class="gcs-quick-search" data-tooltip="Search" title="Search">
                <span class="gcs-item__icon" aria-hidden="true">{!! $icons['search'] !!}</span>
                <span class="gcs-quick-search__label">Search...</span>
            </div>
        </x-slot:quick>

        @foreach($menuGroups as $group)
            @php $groupActive = collect($group['items'])->contains(fn($item) => $item['active']); @endphp
            <x-sidebar.group :title="$group['title']" :group-id="$group['id']" :open="$groupActive" :active="$groupActive">
                @foreach($group['items'] as $item)
                    <x-sidebar.item :href="$item['href']" :label="$item['label']" :icon="$item['icon']" :active="$item['active']" />
                @endforeach
            </x-sidebar.group>
        @endforeach

        <div class="gcs-divider"></div>

        @auth
            @if(auth()->user()->isSuperAdmin())
                <x-sidebar.section title="Administration">
                    <x-sidebar.item href="/tenants" label="Tenants Management" :icon="$icons['settings']" :active="request()->is('tenants*')" />
                </x-sidebar.section>
            @endif
        @endauth

        <x-slot:footer>
            @auth
                <div class="gcs-footer-user" data-tooltip="{{ auth()->user()->name }}" title="{{ auth()->user()->name }}">
                    <strong>{{ auth()->user()->name }}</strong>
                    {{ auth()->user()->isSuperAdmin() ? 'Super Admin' : (auth()->user()->isTenantAdmin() ? 'Tenant Admin' : 'User') }}
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-sidebar.footer-item :icon="$icons['logout']" label="Logout" :is-button="true" type="submit" />
                </form>
            @endauth
        </x-slot:footer>
    </x-sidebar>

    <div class="main-content">
        <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        {{-- Platform Topbar --}}
        <header class="gcs-topbar">
            <button type="button" class="gcs-topbar__toggle" id="sidebarCollapseBtn" aria-label="Toggle sidebar" title="Toggle sidebar">
                {!! $icons['chevron'] !!}
            </button>

            <div class="gcs-topbar__search">
                {!! $icons['search'] !!}
                <input type="text" placeholder="Search domains, rules, IPs…" aria-label="Search">
            </div>

            <div class="gcs-topbar__right">
                <span class="gcs-env-badge"><span class="dot"></span> Production</span>

                <a href="#" class="gcs-market-btn" title="Marketplace">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l1-5h16l1 5"></path><path d="M4 9v10a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V9"></path><path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"></path></svg>
                    Marketplace
                </a>

                <button type="button" class="gcs-topbar__icon-btn" aria-label="Notifications" title="Notifications">
                    {!! $icons['bell'] !!}
                    <span class="ping"></span>
                </button>

                <span class="gcs-topbar__divider"></span>

                @auth
                    @php
                        $u = auth()->user();
                        $initials = collect(explode(' ', trim($u->name)))->filter()->take(2)->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
                        $roleLabel = $u->isSuperAdmin() ? 'Super Admin' : ($u->isTenantAdmin() ? 'Tenant Admin' : 'User');
                    @endphp
                    <div class="gcs-topbar__user">
                        <span class="gcs-topbar__avatar">{{ $initials ?: 'U' }}</span>
                        <span class="gcs-topbar__user-meta">
                            <span class="gcs-topbar__user-name">{{ $u->name }}</span><br>
                            <span class="gcs-topbar__user-role">{{ $roleLabel }}</span>
                        </span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="gcs-logout-btn" aria-label="Logout" title="Logout">{!! $icons['logout'] !!}</button>
                    </form>
                @endauth
            </div>
        </header>

        <div class="content-wrapper">
            @yield('content')
        </div>
    </div>
</div>

<script>
    const sidebar = document.getElementById('gcsSidebar');
    const appContainer = document.getElementById('appContainer');
    const collapseBtn = document.getElementById('sidebarCollapseBtn');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        if (window.innerWidth <= 1024) {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
            return;
        }
        const collapsed = sidebar.classList.toggle('is-collapsed');
        appContainer.classList.toggle('is-sidebar-collapsed', collapsed);
    }

    if (collapseBtn) collapseBtn.addEventListener('click', toggleSidebar);

    document.querySelectorAll('[data-group-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const parentGroup = button.closest('.gcs-group');
            if (!parentGroup) return;
            document.querySelectorAll('.gcs-group.is-open').forEach((group) => {
                if (group !== parentGroup) group.classList.remove('is-open');
            });
            parentGroup.classList.toggle('is-open');
        });
    });

    document.addEventListener('click', function (event) {
        if (window.innerWidth > 1024) return;
        const menuBtn = document.querySelector('.mobile-menu-btn');
        if (!sidebar.contains(event.target) && !menuBtn.contains(event.target) && sidebar.classList.contains('open')) {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        }
    });
</script>

@yield('scripts')
</body>
</html>
