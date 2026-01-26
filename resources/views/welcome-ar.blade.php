<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WAF Gate - جدار الحماية المتقدم لتطبيقات الويب</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap');
        
        :root {
            --bg-primary: #0a0a0a;
            --bg-secondary: #161615;
            --bg-card: #1e1e1d;
            --text-primary: #ffffff;
            --text-secondary: #a1a09a;
            --text-muted: #706f6c;
            --border: #2a2a29;
            --primary: #864ccb;
            --primary-hover: #9a5dd8;
            --primary-gradient: linear-gradient(135deg, #864ccb 0%, #a372e0 100%);
            --accent-blue: #3b82f6;
            --accent-purple: #8b5cf6;
            --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(134, 76, 203, 0.15) 0%, transparent 70%);
            top: -250px;
            right: -250px;
            animation: float 20s ease-in-out infinite;
        }

        .bg-animation::after {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            bottom: -300px;
            left: -300px;
            animation: float 25s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(-30px, -30px) rotate(120deg); }
            66% { transform: translate(20px, 20px) rotate(240deg); }
        }

        /* Header */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(10, 10, 10, 0.7);
            backdrop-filter: blur(30px) saturate(180%);
            -webkit-backdrop-filter: blur(30px) saturate(180%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            z-index: 1000;
            padding: 1rem 2rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .header.scrolled {
            background: rgba(10, 10, 10, 0.95);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            border-bottom: 1px solid rgba(134, 76, 203, 0.2);
            padding: 0.875rem 2rem;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo img {
            height: 56px;
            width: auto;
            filter: drop-shadow(0 2px 8px rgba(134, 76, 203, 0.3));
            transition: filter 0.3s ease;
        }

        .logo:hover img {
            filter: drop-shadow(0 4px 12px rgba(134, 76, 203, 0.5));
        }

        .nav-links {
            display: flex;
            gap: 2.5rem;
            align-items: center;
            flex: 1;
            justify-content: center;
        }

        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            padding: 0.5rem 0;
        }

        .nav-links a::before {
            content: '';
            position: absolute;
            bottom: 0;
            right: 50%;
            transform: translateX(50%);
            width: 0;
            height: 2px;
            background: var(--primary-gradient);
            border-radius: 2px;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-links a:hover {
            color: var(--text-primary);
            transform: translateY(-2px);
        }

        .nav-links a:hover::before {
            width: 100%;
        }

        .btn {
            padding: 0.75rem 1.75rem;
            border-radius: 10px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-block;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(134, 76, 203, 0.3);
            border: 1px solid rgba(134, 76, 203, 0.3);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            right: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: right 0.5s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(134, 76, 203, 0.5);
            border-color: rgba(134, 76, 203, 0.5);
        }

        .btn-primary:hover::before {
            right: 100%;
        }

        .btn-outline {
            border: 1.5px solid rgba(255, 255, 255, 0.2);
            color: var(--text-primary);
            background: transparent;
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(134, 76, 203, 0.1);
        }

        /* Language Switcher */
        .language-switcher {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            margin-left: 1rem;
        }

        .language-switcher button {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-secondary);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .language-switcher button:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .language-switcher button.active {
            background: var(--primary-gradient);
            border-color: var(--primary);
            color: white;
        }

        /* Header Mobile Menu */
        .header-cta {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .mobile-menu-toggle {
            display: none;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-primary);
            padding: 0.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .mobile-menu-toggle:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .mobile-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            left: 0;
            background: rgba(10, 10, 10, 0.98);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 1.5rem;
            flex-direction: column;
            gap: 1rem;
            z-index: 999;
        }

        .mobile-menu.active {
            display: flex;
        }

        .mobile-menu a {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            color: var(--text-primary) !important;
            text-decoration: none;
            transition: all 0.3s ease;
            text-align: right;
        }

        .mobile-menu a:hover {
            background: rgba(134, 76, 203, 0.1);
            color: var(--primary) !important;
        }

        .mobile-menu .btn {
            color: var(--text-primary) !important;
            width: 100%;
            text-align: center;
        }

        .mobile-menu .language-switcher {
            justify-content: center;
            margin: 0.5rem 0;
        }

        /* Hero Section */
        .hero {
            position: relative;
            padding: 120px 2rem 100px;
            text-align: center;
            max-width: 1200px;
            margin: 0 auto;
            z-index: 1;
        }

        .hero-content {
            text-align: center;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, 
                        rgba(134, 76, 203, 0.15) 0%, 
                        rgba(134, 76, 203, 0.08) 100%);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(134, 76, 203, 0.3);
            border-radius: 50px;
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.3px;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.6s ease, badgeGlow 3s ease-in-out infinite;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 20px rgba(134, 76, 203, 0.15),
                        0 0 0 1px rgba(134, 76, 203, 0.1) inset;
        }

        .hero-badge::before {
            content: '';
            position: absolute;
            top: 0;
            right: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                        transparent, 
                        rgba(255, 255, 255, 0.1), 
                        transparent);
            transition: right 0.6s ease;
        }

        .hero-badge:hover {
            transform: translateY(-2px) scale(1.02);
            background: linear-gradient(135deg, 
                        rgba(134, 76, 203, 0.2) 0%, 
                        rgba(134, 76, 203, 0.12) 100%);
            border-color: rgba(134, 76, 203, 0.5);
            box-shadow: 0 8px 30px rgba(134, 76, 203, 0.25),
                        0 0 0 1px rgba(134, 76, 203, 0.2) inset;
        }

        .hero-badge:hover::before {
            right: 100%;
        }

        .hero-badge span:first-child {
            font-size: 18px;
            filter: drop-shadow(0 2px 4px rgba(134, 76, 203, 0.3));
            animation: flagWave 2s ease-in-out infinite;
        }

        .hero-badge span:last-child {
            background: linear-gradient(135deg, #ffffff 0%, #e0d9f0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 600;
        }

        @keyframes badgeGlow {
            0%, 100% {
                box-shadow: 0 4px 20px rgba(134, 76, 203, 0.15),
                            0 0 0 1px rgba(134, 76, 203, 0.1) inset;
            }
            50% {
                box-shadow: 0 6px 25px rgba(134, 76, 203, 0.25),
                            0 0 0 1px rgba(134, 76, 203, 0.15) inset;
            }
        }

        @keyframes flagWave {
            0%, 100% {
                transform: rotate(0deg) scale(1);
            }
            25% {
                transform: rotate(-5deg) scale(1.05);
            }
            75% {
                transform: rotate(5deg) scale(1.05);
            }
        }

        .hero h1 {
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.1;
            background: linear-gradient(135deg, #ffffff 0%, #a1a09a 50%, #ffffff 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: fadeInUp 0.8s ease;
            animation-fill-mode: both;
        }

        .hero h1 .gradient-text {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: clamp(1rem, 2vw, 1.25rem);
            color: var(--text-secondary);
            max-width: 800px;
            margin: 0 auto 3rem;
            line-height: 1.8;
            animation: fadeInUp 1s ease;
            animation-fill-mode: both;
        }

        .hero-description {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            max-width: 800px;
            margin: 0 auto 1rem;
            animation: fadeInUp 1s ease;
            animation-fill-mode: both;
        }

        .hero-description-intro {
            font-size: clamp(1.125rem, 2.5vw, 1.375rem);
            color: var(--text-primary);
            font-weight: 500;
            line-height: 1.6;
        }

        .hero-description-intro .highlight {
            color: var(--primary);
            font-weight: 600;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1.2s ease;
            animation-fill-mode: both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Features Section */
        .features {
            position: relative;
            padding: 120px 2rem;
            max-width: 1400px;
            margin: 0 auto;
            z-index: 1;
        }

        .section-header {
            text-align: center;
            margin-bottom: 5rem;
        }

        .section-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ffffff 0%, #a1a09a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-subtitle {
            font-size: 1.125rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
        }

        .feature-item {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            margin-bottom: 8rem;
            animation: fadeInUp 0.8s ease;
            animation-fill-mode: both;
        }

        .feature-item:nth-child(even) {
            grid-template-columns: 1fr 1fr;
        }

        .feature-item:nth-child(even) .feature-image {
            order: 2;
        }

        .feature-item.feature-1 .feature-image,
        .feature-item.feature-2 .feature-image,
        .feature-item.feature-4 .feature-image {
            overflow: visible !important;
        }

        .feature-item.feature-1 .feature-image img,
        .feature-item.feature-2 .feature-image img,
        .feature-item.feature-4 .feature-image img {
            object-fit: contain !important;
            width: 100% !important;
            height: auto !important;
            max-height: none !important;
            display: block !important;
        }

        .feature-item.feature-3 .feature-image {
            aspect-ratio: 2048 / 1416 !important;
            overflow: visible !important;
            height: auto !important;
        }

        .feature-item.feature-3 .feature-image img {
            object-fit: contain !important;
            width: 100% !important;
            height: auto !important;
            max-height: none !important;
            display: block !important;
        }

        .feature-image {
            position: relative;
            width: 100%;
            aspect-ratio: 1792 / 824;
            border-radius: 30px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .feature-image:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(134, 76, 203, 0.2);
        }

        .feature-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 30px;
            transition: transform 0.4s ease;
        }

        .feature-image:hover img {
            transform: scale(1.02);
        }

        .feature-content {
            padding: 2rem 0;
            text-align: right;
        }

        .feature-content h3 {
            font-size: clamp(1.75rem, 3vw, 2.5rem);
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .feature-content p {
            color: var(--text-secondary);
            font-size: 1.125rem;
            line-height: 1.8;
            margin-bottom: 0;
        }

        /* Stats Section */
        .stats {
            position: relative;
            padding: 100px 2rem;
            background: linear-gradient(180deg, transparent 0%, rgba(134, 76, 203, 0.03) 50%, transparent 100%);
            z-index: 1;
        }

        .stats-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            text-align: center;
        }

        .stat-item {
            padding: 2rem;
            background: rgba(30, 30, 29, 0.5);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-5px);
            border-color: rgba(134, 76, 203, 0.2);
        }

        .stat-item h3 {
            font-size: 3.5rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .stat-item p {
            color: var(--text-secondary);
            font-size: 1.125rem;
            font-weight: 500;
        }

        /* CTA Section */
        .cta {
            position: relative;
            padding: 120px 2rem;
            text-align: center;
            max-width: 900px;
            margin: 0 auto;
            z-index: 1;
        }

        .cta-card {
            background: linear-gradient(135deg, rgba(30, 30, 29, 0.9) 0%, rgba(22, 22, 21, 0.9) 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 4rem 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .cta h2 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ffffff 0%, #a1a09a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .cta p {
            color: var(--text-secondary);
            font-size: 1.125rem;
            margin-bottom: 2.5rem;
            line-height: 1.7;
        }

        /* Contact Form */
        .contact-form {
            display: none;
            margin-top: 2rem;
            text-align: right;
            animation: fadeInUp 0.5s ease;
        }

        .contact-form.active {
            display: block;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: var(--text-primary);
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.875rem 1.25rem;
            background: rgba(30, 30, 29, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(30, 30, 29, 0.95);
            box-shadow: 0 0 0 3px rgba(134, 76, 203, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: var(--text-muted);
        }

        .form-submit-btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.5rem;
        }

        .form-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(134, 76, 203, 0.5);
        }

        .form-submit-btn:active {
            transform: translateY(0);
        }

        .form-close-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-secondary);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }

        .form-close-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Footer */
        .footer {
            position: relative;
            padding: 4rem 2rem 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            text-align: center;
            color: var(--text-secondary);
            font-size: 14px;
            z-index: 1;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                padding: 0.875rem 1rem;
            }

            .header.scrolled {
                padding: 0.75rem 1rem;
            }

            .header-content {
                gap: 1rem;
            }

            .nav-links {
                display: none;
            }

            .header-cta {
                display: none;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .logo img {
                height: 36px;
            }

            .hero {
                padding: 100px 1.5rem 60px;
            }

            .hero-badge {
                padding: 0.5rem 1rem;
                font-size: 12px;
                gap: 6px;
                margin-bottom: 1.5rem;
            }

            .hero-badge span:first-child {
                font-size: 14px;
            }

            .hero h1 {
                font-size: clamp(1.75rem, 6vw, 2.5rem);
                margin-bottom: 1.25rem;
            }

            .hero p {
                font-size: clamp(0.9rem, 2vw, 1rem);
                margin-bottom: 2rem;
            }

            .hero-buttons {
                gap: 0.75rem;
            }

            .hero-buttons .btn {
                width: 100%;
                text-align: center;
            }

            .features {
                padding: 60px 1.5rem;
            }

            .section-header {
                margin-bottom: 3rem;
            }

            .section-title {
                font-size: clamp(1.5rem, 5vw, 2rem);
                margin-bottom: 0.75rem;
            }

            .section-subtitle {
                font-size: 0.95rem;
            }

            .feature-item {
                grid-template-columns: 1fr !important;
                gap: 2rem;
                margin-bottom: 3rem;
            }

            .feature-item:nth-child(even) .feature-image {
                order: 1 !important;
            }

            .feature-item:nth-child(even) .feature-content {
                order: 2 !important;
            }

            .feature-content {
                text-align: center;
            }

            .feature-image {
                aspect-ratio: 16 / 9 !important;
                border-radius: 20px;
            }

            .feature-item.feature-1 .feature-image,
            .feature-item.feature-2 .feature-image,
            .feature-item.feature-3 .feature-image,
            .feature-item.feature-4 .feature-image {
                aspect-ratio: 16 / 9 !important;
            }

            .feature-image img {
                border-radius: 20px;
            }

            .feature-content h3 {
                font-size: clamp(1.25rem, 4vw, 1.75rem);
                margin-bottom: 1rem;
            }

            .feature-content p {
                font-size: 0.95rem;
                line-height: 1.6;
            }

            .stats {
                padding: 60px 1.5rem;
            }

            .stats-content {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }

            .stat-item {
                padding: 1.5rem 1rem;
            }

            .stat-item h3 {
                font-size: 2.5rem;
            }

            .stat-item p {
                font-size: 0.95rem;
            }

            .cta {
                padding: 60px 1.5rem;
            }

            .cta-card {
                padding: 2.5rem 1.5rem;
            }

            .cta h2 {
                font-size: clamp(1.5rem, 5vw, 2rem);
            }

            .cta p {
                font-size: 1rem;
            }

            .footer {
                padding: 3rem 1.5rem 1.5rem;
                font-size: 13px;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 0.75rem 1rem;
            }

            .logo img {
                height: 36px;
            }

            .hero {
                padding: 90px 1rem 50px;
            }

            .features {
                padding: 50px 1rem;
            }

            .feature-item {
                margin-bottom: 2.5rem;
            }

            .stats-content {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .cta-card {
                padding: 2rem 1rem;
            }
        }

        /* Smooth Scroll */
        html {
            scroll-behavior: smooth;
        }
    </style>
    </head>
<body>
    <div class="bg-animation"></div>

    <!-- Header -->
    <header class="header" id="header">
        <div class="header-content">
            <div class="logo">
                <img src="{{ asset('images/Logo.png') }}" alt="WAF Gate">
            </div>
            <nav class="nav-links">
                <a href="#features">المميزات</a>
                <a href="#stats">الإحصائيات</a>
                <a href="mailto:contact@wafgate.com">اتصل بنا</a>
            </nav>
            <div class="header-cta">
                <div class="language-switcher">
                    <button id="langAr" class="active">AR</button>
                    <button id="langEn">EN</button>
                </div>
                @auth
                    <a href="{{ url('/waf') }}" class="btn btn-outline">لوحة التحكم</a>
                @else
                    <a href="#" class="btn btn-outline" id="showFormBtnHeader">ابدأ الآن</a>
                @endauth
            </div>
            <button class="mobile-menu-toggle" id="mobileMenuToggle">☰</button>
        </div>
        <div class="mobile-menu" id="mobileMenu">
            <div class="language-switcher">
                <button id="langArMobile" class="active">AR</button>
                <button id="langEnMobile">EN</button>
            </div>
            <a href="#features">المميزات</a>
            <a href="#stats">الإحصائيات</a>
            <a href="mailto:contact@wafgate.com">اتصل بنا</a>
            @auth
                <a href="{{ url('/waf') }}" class="btn btn-outline" style="width: 100%; text-align: center;">لوحة التحكم</a>
            @else
                <a href="#" class="btn btn-outline" id="showFormBtnMobile" style="width: 100%; text-align: center;">ابدأ الآن</a>
            @endauth
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-badge">
            <span>🇸🇦</span>
            <span>شركة سعودية متخصصة في WAF</span>
        </div>
        <h1>
            احمِ تطبيقاتك على الويب<br>
            باستخدام <span class="gradient-text">WAF المتقدم</span>
        </h1>
        <div class="hero-description">
            <p class="hero-description-intro">
                شركة <span class="highlight">سعودية</span> تقدم حلول <span class="highlight">جدار الحماية لتطبيقات الويب (WAF)</span>. 
                حافظ على تطبيقاتك آمنة من الهجمات مع حماية على مستوى المؤسسات.
            </p>
        </div>
        <div class="hero-buttons">
            @auth
                <a href="{{ url('/waf') }}" class="btn btn-primary">الوصول إلى لوحة التحكم</a>
            @else
                <a href="#" class="btn btn-primary" id="showFormBtnHero">احصل على استشارة</a>
            @endauth
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="section-header">
            <h2 class="section-title">كل ما تحتاجه للبقاء آمناً</h2>
            <p class="section-subtitle">
                مميزات أمنية شاملة مصممة لحماية تطبيقات الويب الخاصة بك من جميع أنواع التهديدات
            </p>
        </div>
        
        <!-- Feature 1: Image Right, Content Left -->
        <div class="feature-item feature-1">
            <div class="feature-image">
                <img src="{{ asset('images/pictone.png') }}" alt="حماية فورية من الهجمات">
            </div>
            <div class="feature-content">
                <h3>حماية فورية من الهجمات</h3>
                <p>WAF Gate يحجب الهجمات الشائعة مثل حقن SQL وXSS ومحاولات القوة الغاشمة في الوقت الفعلي قبل وصولها إلى تطبيقك.</p>
            </div>
        </div>

        <!-- Feature 2: Image Left, Content Right -->
        <div class="feature-item feature-2">
            <div class="feature-image">
                <img src="{{ asset('images/picttwo.png') }}" alt="حظر ذكي حسب الدولة وعنوان IP">
            </div>
            <div class="feature-content">
                <h3>حظر ذكي حسب الدولة وعنوان IP</h3>
                <p>تحكم كامل للسماح أو حظر حركة المرور بناءً على الدولة أو عنوان IP، مما يقلل من المخاطر الأمنية غير الضرورية.</p>
            </div>
        </div>

        <!-- Feature 3: Image Right, Content Left -->
        <div class="feature-item feature-3">
            <div class="feature-image">
                <img src="{{ asset('images/picthree.png') }}" alt="مراقبة وتحليل التهديدات">
            </div>
            <div class="feature-content">
                <h3>مراقبة وتحليل التهديدات</h3>
                <p>لوحة تحكم واضحة تعرض جميع الأنشطة المشبوهة مع تفسيرات بسيطة لكل حدث أمني.</p>
            </div>
        </div>

        <!-- Feature 4: Image Left, Content Right -->
        <div class="feature-item feature-4">
            <div class="feature-image">
                <img src="{{ asset('images/picfour.png') }}" alt="إعداد سهل بدون تعقيد">
            </div>
            <div class="feature-content">
                <h3>إعداد سهل بدون تعقيد</h3>
                <p>نشر سريع بدون تغييرات كبيرة في البنية التحتية، مثالي للمواقع الصغيرة والمتوسطة.</p>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section id="stats" class="stats">
        <div class="stats-content">
            <div class="stat-item">
                <h3>99.9%</h3>
                <p>ضمان وقت التشغيل</p>
            </div>
            <div class="stat-item">
                <h3>24/7</h3>
                <p>مراقبة فورية</p>
            </div>
            <div class="stat-item">
                <h3>1000+</h3>
                <p>قواعد أمنية</p>
            </div>
            <div class="stat-item">
                <h3>عالمي</h3>
                <p>تغطية GeoIP</p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="cta-card">
            <h2>هل أنت مستعد لتأمين تطبيقاتك؟</h2>
            <p>ابدأ في حماية تطبيقات الويب الخاصة بك اليوم مع حل الأمان الشامل من WAF Gate. ابدأ في دقائق.</p>
            <div id="ctaButtons">
                @auth
                    <a href="{{ url('/waf') }}" class="btn btn-primary">الوصول إلى لوحة التحكم</a>
                @else
                    <a href="#" class="btn btn-primary" id="showFormBtn">ابدأ الآن</a>
                @endauth
            </div>
            
            <!-- Contact Form -->
            <form class="contact-form" id="contactForm">
                <div class="form-group">
                    <label for="name">الاسم الكامل *</label>
                    <input type="text" id="name" name="name" placeholder="أدخل اسمك الكامل" required>
                </div>
                
                <div class="form-group">
                    <label for="email">عنوان البريد الإلكتروني *</label>
                    <input type="email" id="email" name="email" placeholder="بريدك.الإلكتروني@example.com" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">رقم الهاتف</label>
                    <input type="tel" id="phone" name="phone" placeholder="+966 50 123 4567">
                </div>
                
                <div class="form-group">
                    <label for="message">الرسالة</label>
                    <textarea id="message" name="message" placeholder="أخبرنا عن متطلباتك..."></textarea>
                </div>
                
                <button type="submit" class="form-submit-btn">إرسال الطلب</button>
                <button type="button" class="form-close-btn" id="closeFormBtn">إلغاء</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p><strong>WAF Gate</strong> - شركة سعودية تقدم حلول جدار الحماية لتطبيقات الويب (WAF)</p>
        <p style="margin-top: 0.5rem; opacity: 0.7;">&copy; {{ date('Y') }} WAF Gate. جميع الحقوق محفوظة.</p>
    </footer>

    <script>
        // Language Switcher
        const langAr = document.getElementById('langAr');
        const langEn = document.getElementById('langEn');
        const langArMobile = document.getElementById('langArMobile');
        const langEnMobile = document.getElementById('langEnMobile');

        function switchToEnglish() {
            window.location.href = '/';
        }

        if (langEn) {
            langEn.addEventListener('click', switchToEnglish);
        }
        if (langEnMobile) {
            langEnMobile.addEventListener('click', switchToEnglish);
        }

        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        
        if (mobileMenuToggle && mobileMenu) {
            mobileMenuToggle.addEventListener('click', function() {
                mobileMenu.classList.toggle('active');
                this.textContent = mobileMenu.classList.contains('active') ? '✕' : '☰';
            });

            // Close menu when clicking on a link
            mobileMenu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function() {
                    mobileMenu.classList.remove('active');
                    mobileMenuToggle.textContent = '☰';
                });
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!mobileMenuToggle.contains(event.target) && !mobileMenu.contains(event.target)) {
                    mobileMenu.classList.remove('active');
                    mobileMenuToggle.textContent = '☰';
                }
            });
        }

        // Contact Form Toggle
        const showFormBtn = document.getElementById('showFormBtn');
        const showFormBtnHeader = document.getElementById('showFormBtnHeader');
        const showFormBtnHero = document.getElementById('showFormBtnHero');
        const showFormBtnMobile = document.getElementById('showFormBtnMobile');
        const closeFormBtn = document.getElementById('closeFormBtn');
        const contactForm = document.getElementById('contactForm');
        const ctaButtons = document.getElementById('ctaButtons');

        function showContactForm() {
            contactForm.classList.add('active');
            if (ctaButtons) ctaButtons.style.display = 'none';
            // Scroll to form
            contactForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function hideContactForm() {
            contactForm.classList.remove('active');
            if (ctaButtons) ctaButtons.style.display = 'block';
        }

        if (showFormBtn) {
            showFormBtn.addEventListener('click', function(e) {
                e.preventDefault();
                showContactForm();
            });
        }

        if (showFormBtnHeader) {
            showFormBtnHeader.addEventListener('click', function(e) {
                e.preventDefault();
                showContactForm();
            });
        }

        if (showFormBtnHero) {
            showFormBtnHero.addEventListener('click', function(e) {
                e.preventDefault();
                showContactForm();
            });
        }

        if (showFormBtnMobile) {
            showFormBtnMobile.addEventListener('click', function(e) {
                e.preventDefault();
                showContactForm();
                // Close mobile menu if open
                if (mobileMenu) {
                    mobileMenu.classList.remove('active');
                    if (mobileMenuToggle) mobileMenuToggle.textContent = '☰';
                }
            });
        }

        if (closeFormBtn) {
            closeFormBtn.addEventListener('click', function() {
                hideContactForm();
            });
        }

        // Form Submission (for future implementation)
        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form data
                const formData = {
                    name: document.getElementById('name').value,
                    email: document.getElementById('email').value,
                    phone: document.getElementById('phone').value,
                    message: document.getElementById('message').value
                };
                
                // For now, just show an alert (will be implemented later)
                alert('شكراً لاهتمامك! سنتواصل معك قريباً على ' + formData.email);
                
                // Reset form
                contactForm.reset();
                contactForm.classList.remove('active');
                ctaButtons.style.display = 'block';
            });
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

    </script>
    </body>
</html>
