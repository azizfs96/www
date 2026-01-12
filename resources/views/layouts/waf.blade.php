<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>WAF Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f6fa;
            font-family: 'Tajawal', sans-serif;
        }
        .sidebar {
            width: 220px;
            height: 100vh;
            background: #1b1f3b;
            color: white;
            position: fixed;
            padding-top: 20px;
        }
        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: #ddd;
            text-decoration: none;
        }
        .sidebar a:hover, .sidebar .active {
            background: #4f5bd5;
            color: white;
        }
        .content {
            margin-right: 240px;
            padding: 25px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h5 class="text-center mb-3">🔥 WAF Panel</h5>
    <a href="/waf" class="{{ request()->is('waf') ? 'active' : '' }}">📊 Dashboard</a>
    <a href="/waf/events" class="{{ request()->is('waf/events') ? 'active' : '' }}">🚨 Events</a>
    <a href="/waf/ip-rules" class="{{ request()->is('waf/ip-rules') ? 'active' : '' }}">🛑 IP Rules</a>
</div>

<div class="content">
    @yield('content')
</div>

</body>
</html>
