<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - WAF Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg: #1A1A1A;
            --bg-soft: #1E1E1E;
            --card: #1E1E1E;
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
        }

        * {
            box-sizing: border-box;
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
            background-image: radial-gradient(circle, rgba(157, 78, 221, 0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.3;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .login-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 48px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .login-logo img {
            max-height: 120px;
            width: auto;
            object-fit: contain;
            margin-bottom: 16px;
        }

        .login-logo-text {
            font-size: 32px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.5px;
        }

        .login-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 8px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--bg-soft);
            box-shadow: 0 0 0 3px rgba(157, 78, 221, 0.1);
        }

        .form-input::placeholder {
            color: var(--text-tertiary);
        }

        .remember-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .remember-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .remember-group label {
            font-size: 13px;
            color: var(--text-muted);
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-login:hover {
            background: var(--primary-soft);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(157, 78, 221, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert-error {
            padding: 12px 16px;
            background: rgba(248, 113, 113, 0.1);
            border: 1px solid rgba(248, 113, 113, 0.3);
            border-radius: 8px;
            color: var(--danger);
            font-size: 13px;
            margin-bottom: 24px;
        }

        .footer-text {
            text-align: center;
            margin-top: 32px;
            font-size: 11px;
            color: var(--text-tertiary);
        }
    </style>
</head>
<body>
    <div class="dots-pattern"></div>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <img src="{{ asset('images/Logo.png') }}" alt="WAF Gate Logo">
                </div>
            </div>

            @if ($errors->any())
                <div class="alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="admin@example.com"
                        value="{{ old('email') }}"
                        required 
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <div class="remember-group">
                    <input type="checkbox" id="remember" name="remember" value="1">
                    <label for="remember">Remember me</label>
                </div>

                <button type="submit" class="btn-login">
                    Sign In
                </button>
            </form>

            <div class="footer-text">
                WAF Management System
            </div>
        </div>
    </div>
</body>
</html>
