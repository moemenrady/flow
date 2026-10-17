{{-- resources/views/auth/login-fancy.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>تسجيل الدخول | ابداع</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* استيراد خط Cairo الممتاز مع اللغة العربية */
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap');

        :root {
            --bg-color: #050505;
            /* أسود عميق */
            --glow-1: #6366f1;
            /* نيلي/بنفسجي */
            --glow-2: #ec4899;
            /* فوشيا/وردي */
            --glow-3: #06b6d4;
            /* سماوي */
            --glass-bg: rgba(20, 20, 25, 0.65);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Cairo', sans-serif;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* إضاءات النيون المتحركة في الخلفية */
        .bg-glow {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.5;
            animation: pulseGlow 10s infinite alternate;
            z-index: -1;
        }

        .glow-1 {
            width: 400px;
            height: 400px;
            background: var(--glow-1);
            top: -10%;
            left: -10%;
        }

        .glow-2 {
            width: 500px;
            height: 500px;
            background: var(--glow-2);
            bottom: -20%;
            right: -10%;
            animation-delay: -5s;
        }

        .glow-3 {
            width: 300px;
            height: 300px;
            background: var(--glow-3);
            top: 40%;
            left: 40%;
            opacity: 0.3;
            animation-duration: 15s;
        }

        @keyframes pulseGlow {
            0% {
                transform: scale(1) translate(0, 0);
            }

            100% {
                transform: scale(1.3) translate(50px, 50px);
            }
        }

        .login-container {
            width: 100%;
            max-width: 1050px;
            padding: 2rem;

            /* ✅ مهم للموبايل */
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* كارت الزجاج (الـ Glassmorphism) */
        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
            display: flex;
            flex-direction: row;
            box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.7), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            width: 100%;
            min-height: 600px;
        }


        /* القسم الأيمن (النصوص والشعار) */
        .brand-section {
            flex: 1.1;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(236, 72, 153, 0.05));
            border-left: 1px solid var(--glass-border);
            position: relative;
        }

        .brand-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(to left, var(--glow-2), var(--glow-1), var(--glow-3));
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
            animation: textShine 5s linear infinite;
        }

        @keyframes textShine {
            to {
                background-position: 200% center;
            }
        }

        .brand-subtitle {
            font-size: 1.2rem;
            color: var(--text-muted);
            line-height: 1.8;
            font-weight: 600;
        }

        /* أشكال هندسية تجميلية */
        .abstract-shape {
            position: absolute;
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            animation: floatShape 8s ease-in-out infinite;
            pointer-events: none;
        }

        .shape-1 {
            width: 120px;
            height: 120px;
            top: 15%;
            right: 10%;
            animation-delay: 0s;
            border-color: rgba(99, 102, 241, 0.3);
        }

        .shape-2 {
            width: 200px;
            height: 200px;
            bottom: -10%;
            left: -10%;
            animation-delay: -4s;
            border-radius: 30%;
            transform: rotate(45deg);
            border-color: rgba(236, 72, 153, 0.2);
        }

        @keyframes floatShape {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(15deg);
            }
        }

        /* القسم الأيسر (الفورم) */
        .form-section {
            flex: 1;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 2.5rem;
        }

        .form-header h2 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: #fff;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 1rem;
        }

        /* الحقول */
        .input-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        .input-group input[type="email"],
        .input-group input[type="password"] {
            width: 100%;
            padding: 1rem 1.2rem;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: #fff;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--glow-1);
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.2), inset 0 2px 4px rgba(0, 0, 0, 0.5);
            background: rgba(0, 0, 0, 0.6);
            transform: translateY(-2px);
        }

        /* رسائل الخطأ */
        .error-msg,
        .text-red-600 {
            color: #ef4444 !important;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: block;
        }

        /* خيارات التذكر وكلمة المرور */
        .options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            margin-top: 1rem;
            font-size: 0.95rem;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            color: var(--text-muted);
            transition: color 0.3s;
        }

        .checkbox-wrapper:hover {
            color: #fff;
        }

        .checkbox-wrapper input[type="checkbox"] {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            background: rgba(0, 0, 0, 0.3);
            cursor: pointer;
            position: relative;
            transition: all 0.3s;
        }

        .checkbox-wrapper input[type="checkbox"]:checked {
            background: var(--glow-1);
            border-color: var(--glow-1);
        }

        .checkbox-wrapper input[type="checkbox"]:checked::after {
            content: '✔';
            position: absolute;
            color: white;
            font-size: 14px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .forgot-link {
            color: var(--glow-3);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s, text-shadow 0.3s;
        }

        .forgot-link:hover {
            color: #fff;
            text-shadow: 0 0 10px var(--glow-3);
        }

        /* زر الدخول المتميز */
        .submit-btn {
            width: 100%;
            padding: 1.2rem;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--glow-1), var(--glow-2));
            color: #fff;
            font-family: inherit;
            font-size: 1.2rem;
            font-weight: 800;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(236, 72, 153, 0.4);
        }

        .submit-btn:active {
            transform: translateY(1px);
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.4), transparent);
            transform: skewX(-20deg);
            animation: btnShine 4s infinite;
        }

        @keyframes btnShine {
            0% {
                left: -100%;
            }

            20% {
                left: 200%;
            }

            100% {
                left: 200%;
            }
        }

        /* التجاوب مع الموبايل (Responsive) */
        @media (max-width: 900px) {
            body {
                align-items: flex-start;
            }

            .login-container {
                padding: 1rem;
                min-height: auto;
            }

            .glass-panel {
                flex-direction: column;
                border-radius: 20px;
            }

            .brand-section {
                padding: 2rem 1.5rem;
                text-align: center;
                border-left: none;
                border-bottom: 1px solid var(--glass-border);
            }

            .form-section {
                padding: 2rem 1.5rem;
                width: 100%;

            }

            .brand-title {
                font-size: 2rem;
            }

            .form-header h2 {
                font-size: 1.5rem;
            }

            .options-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            /* ❌ تقليل الإضاءات */
            .bg-glow {
                display: none;
            }
        }

        @media (max-width: 480px) {

            .input-group input {
                padding: 0.9rem 1rem;
                font-size: 0.95rem;
            }

            .submit-btn {
                font-size: 1rem;
                padding: 1rem;
            }

            .brand-subtitle {
                font-size: 0.95rem;
            }
        }
    </style>
</head>

<body>

    <div class="bg-glow glow-1"></div>
    <div class="bg-glow glow-2"></div>
    <div class="bg-glow glow-3"></div>

    <div class="login-container">
        <div class="glass-panel">

            {{-- القسم الأيمن (البراند) --}}
            <div class="brand-section">
                <div class="abstract-shape shape-1"></div>
                <div class="abstract-shape shape-2"></div>

                <div>
                    <h1 class="brand-title">Flow</h1>
                    <p class="brand-subtitle">انطلق نحو المستقبل بتجربة استخدام فائقة التطور، تصميم عصري، وأداء
                        استثنائي.</p>
                </div>
            </div>

            {{-- القسم الأيسر (الفورم) --}}
            <div class="form-section">
                <div class="form-header">
                    <h2>مرحباً بعودتك 👋</h2>
                    <p>سجل دخولك للمتابعةالنظام </p>
                </div>

                {{-- حالة الجلسة --}}
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="input-group">
                        <label for="email">البريد الإلكتروني</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            autofocus autocomplete="username" placeholder="example@mail.com">
                        <x-input-error :messages="$errors->get('email')" class="error-msg mt-2" />
                    </div>

                    <div class="input-group">
                        <label for="password">كلمة المرور</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            placeholder="••••••••">
                        <x-input-error :messages="$errors->get('password')" class="error-msg mt-2" />
                    </div>

                    <div class="options-row">
                        <label class="checkbox-wrapper" for="remember_me">
                            <input id="remember_me" type="checkbox" name="remember">
                            <span>تذكرني</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="forgot-link" href="{{ route('password.request') }}">نسيت كلمة السر؟</a>
                        @endif
                    </div>

                    <button type="submit" class="submit-btn">
                        تسجيل الدخول
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </button>
                </form>

            </div>
        </div>
    </div>

    {{-- إضافة تفاعل بسيط للديسكتوب لتأثير المنظور (Parallax) --}}
    <script>
        (function() {
            const panel = document.querySelector('.glass-panel');
            if (!panel) return;

            window.addEventListener('mousemove', (e) => {
                if (window.innerWidth < 900) return; // تعطيل في الموبايل
                const xAxis = (window.innerWidth / 2 - e.pageX) / 50;
                const yAxis = (window.innerHeight / 2 - e.pageY) / 50;
                panel.style.transform = `rotateY(${xAxis}deg) rotateX(${yAxis}deg)`;
                panel.style.transition = 'none'; // لإزالة التأخير أثناء الحركة
            });

            window.addEventListener('mouseleave', () => {
                panel.style.transform = `rotateY(0deg) rotateX(0deg)`;
                panel.style.transition = 'transform 0.5s ease';
            });

            window.addEventListener('mouseenter', () => {
                panel.style.transition = 'none';
            });
        })();
    </script>
</body>

</html>
