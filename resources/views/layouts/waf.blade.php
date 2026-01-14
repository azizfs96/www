<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WAF Dashboard')</title>
    <style>
        /* Force Dark Background with Dots Pattern */
        html { 
            background: #1A1A1A !important; 
            background-color: #1A1A1A !important;
        }
        
        
        body { 
            background: #1A1A1A !important; 
            background-color: #1A1A1A !important;
        }
        * { box-sizing: border-box; }
        
        :root {
            --bg: #1A1A1A;
            --bg-soft: #1E1E1E;
            --card: #1E1E1E;
            --sidebar-bg: #1A1A1A;
            --sidebar-active: rgba(157, 78, 221, 0.15);
            --primary: #9D4EDD;
            --primary-soft: #B06FE8;
            --primary-dark: #7C2DD8;
            --danger: #F87171;
            --warning: #FBBF24;
            --success: #4ADE80;
            --text: #E5E5E5;
            --text-muted: #B3B3B3;
            --text-tertiary: #808080;
            --border: #333333;
            --sidebar-width: 260px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        html {
            background: #050810 !important;
        }
        
        html, body {
            background: #1A1A1A !important;
            background-color: #1A1A1A !important;
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .app-container {
            background: #1A1A1A !important;
            position: relative;
            z-index: 1;
        }
        
        .main-content {
            background: transparent !important;
            background-color: transparent !important;
            position: relative;
            z-index: 1;
        }
        
        .content-wrapper {
            background: transparent !important;
            background-color: transparent !important;
            position: relative;
            z-index: 1;
        }
        
        .sidebar {
            background: #1A1A1A !important;
            border-right-color: #333333 !important;
            position: relative;
            z-index: 1;
        }

        .dots-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: #1A1A1A;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }
        
        .dots-pattern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                url("data:image/svg+xml,%3Csvg width='24' height='24' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='12' cy='12' r='2' fill='rgba(255,255,255,0.15)'/%3E%3C/svg%3E");
            background-size: 24px 24px;
            background-position: 0 0;
            background-repeat: repeat;
        }
        
        .app-container {
            display: flex;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border);
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: var(--sidebar-bg);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }

        .sidebar-header {
            padding: 18px 16px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 4px;
        }

        .sidebar-logo-icon {
            width: 32px;
            height: 32px;
            background: transparent;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            color: var(--text);
            border: 1px solid var(--border);
        }

        .sidebar-logo-text {
            font-size: 16px;
            font-weight: 600;
            color: var(--text);
            letter-spacing: -0.3px;
        }

        .sidebar-subtitle {
            font-size: 10px;
            color: var(--text-muted);
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sidebar-nav {
            padding: 20px 12px;
            flex: 1;
            overflow-y: auto;
        }

        .nav-section {
            margin-bottom: 28px;
        }

        .nav-section:last-child {
            margin-bottom: 0;
        }

        .nav-section-title {
            font-size: 10px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 12px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 4px;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 13px;
            transition: all 0.15s ease;
            margin-bottom: 2px;
        }

        .nav-item:hover {
            background: var(--sidebar-active);
            color: var(--text);
        }

        .nav-item.active {
            background: #1E1E1E;
            color: var(--text);
            border-left: 2px solid var(--text);
            font-weight: 500;
        }
        
        .nav-item.active .nav-item-icon {
            color: var(--text);
        }
        
        .nav-item:hover {
            background: #1E1E1E;
            color: var(--text);
        }

        .nav-item-icon {
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: var(--text-muted);
            transition: color 0.15s ease;
            font-weight: normal;
            flex-shrink: 0;
        }
        
        .nav-item:hover .nav-item-icon,
        .nav-item.active .nav-item-icon {
            color: var(--text);
        }

        .nav-item-badge {
            margin-left: auto;
            background: var(--danger);
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .content-wrapper {
            padding: 32px 40px;
            max-width: 1600px;
            margin: 0 auto;
        }

        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .content-wrapper {
                padding: 16px;
            }

            .mobile-menu-btn {
                display: block !important;
            }
        }

        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--sidebar-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px;
            color: var(--text);
            cursor: pointer;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            transition: all 0.2s ease;
        }

        .mobile-menu-btn:hover {
            background: var(--sidebar-active);
            transform: scale(1.05);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 999;
        }

        .sidebar-overlay.open {
            display: block;
        }

        @media (max-width: 1024px) {
            .sidebar-overlay.open {
                display: block;
            }
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(157, 78, 221, 0.15);
            color: var(--primary-soft);
            border: 1px solid rgba(157, 78, 221, 0.3);
            border-radius: 12px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--primary);
            box-shadow: 0 0 8px rgba(157, 78, 221, 0.6);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Content Styles */
        @yield('styles')
    </style>
</head>
<body>
    <div class="dots-pattern"></div>
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <div class="sidebar-logo-icon">W</div>
                    <div class="sidebar-logo-text">WAF Monitor</div>
                </div>
                <div class="sidebar-subtitle">Web Application Firewall Monitor</div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="/waf" class="nav-item {{ request()->is('waf') && !request()->is('waf/events') && !request()->is('waf/ip-rules') ? 'active' : '' }}">
                        <span class="nav-item-icon">—</span>
                        <span>Dashboard</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Events & Attacks</div>
                    <a href="/waf/events" class="nav-item {{ request()->is('waf/events*') ? 'active' : '' }}">
                        <span class="nav-item-icon">▪</span>
                        <span>Event Log</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Settings</div>
                    <a href="/waf/ip-rules" class="nav-item {{ request()->is('waf/ip-rules*') ? 'active' : '' }}">
                        <span class="nav-item-icon">▫</span>
                        <span>IP Rules</span>
                    </a>
                    <a href="/waf/url-rules" class="nav-item {{ request()->is('waf/url-rules*') ? 'active' : '' }}">
                        <span class="nav-item-icon">▫</span>
                        <span>URL Rules</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
            <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

            <div class="content-wrapper">
                @yield('content')
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 1024) {
                if (!sidebar.contains(event.target) && 
                    !menuBtn.contains(event.target) && 
                    sidebar.classList.contains('open')) {
                    toggleSidebar();
                }
            }
        });
        
        // Create dots pattern dynamically
        document.addEventListener('DOMContentLoaded', function() {
            const dotsPattern = document.querySelector('.dots-pattern');
            if (dotsPattern) {
                // Force the pattern to be visible
                dotsPattern.style.display = 'block';
                dotsPattern.style.visibility = 'visible';
                dotsPattern.style.opacity = '1';
            }
        });
    </script>

    @yield('scripts')
</body>
</html>
