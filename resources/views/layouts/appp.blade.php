<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('page_title', 'لوحة التحكم | البُعد الجديد')</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <style>
        /* ====== المتغيرات ====== */
        :root {
            --primary: #00f2fe;
            --secondary: #4facfe;
            --accent: #b465da;
            --bg-dark: #090a0f;
            --card-bg: rgba(16, 20, 30, 0.6);
            --glass-border: rgba(255, 255, 255, 0.12);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --drawer-width: 320px;
        }

        /* ===== تحسين التباين ووضوح النصوص في كامل النظام (Global High Contrast) ===== */
        select option {
            background-color: #111622 !important;
            color: #f8fafc !important;
        }

        label, .form-label, .modal label {
            color: #e2e8f0 !important;
            font-weight: 600;
        }

        .text-muted {
            color: #94a3b8 !important;
        }

        .form-control, .form-select, input, textarea {
            color: #ffffff !important;
        }

        .form-control::placeholder, input::placeholder, textarea::placeholder {
            color: rgba(226, 232, 240, 0.45) !important;
        }

        .modal-content, .glass-modal {
            background-color: #0d121d !important;
            color: #f8fafc !important;
            border: 1px solid rgba(0, 242, 254, 0.25) !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.8) !important;
        }

        .modal-header .modal-title {
            color: #ffffff !important;
            font-weight: 700;
        }

        .glass-input {
            background: rgba(255, 255, 255, 0.07) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
            border-radius: 10px;
            padding: 10px 14px;
        }

        .glass-input:focus {
            background: rgba(255, 255, 255, 0.12) !important;
            border-color: var(--primary) !important;
            color: #ffffff !important;
            box-shadow: 0 0 15px rgba(0, 242, 254, 0.25) !important;
        }

        table, .table, .glass-table, .table-glass {
            color: #f8fafc !important;
        }

        table th, .table th, .glass-table th, .table-glass th {
            color: #a5b4fc !important;
        }

        table td, .table td, .glass-table td, .table-glass td {
            color: #f8fafc !important;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-dark);
            font-family: 'Tajawal', sans-serif;
            color: var(--text-main);
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* ====== الخلفية المتحركة ====== */
        .bg-mesh {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -1;
            overflow: hidden;
            background: var(--bg-dark);
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.4;
            animation: floatOrb 20s ease-in-out infinite alternate;
        }

        .orb-1 {
            width: 400px;
            height: 400px;
            background: var(--secondary);
            top: -10%;
            right: -5%;
        }

        .orb-2 {
            width: 500px;
            height: 500px;
            background: var(--accent);
            bottom: -10%;
            left: -5%;
            animation-delay: -5s;
        }

        @keyframes floatOrb {
            0% {
                transform: translate(0, 0) scale(1);
            }

            100% {
                transform: translate(60px, 60px) scale(1.1);
            }
        }

        /* ====== الأزرار العلوية (يمين ويسار) ====== */
        .top-bar {
            position: fixed;
            top: 1.5rem;
            width: 100%;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1001;
            pointer-events: none;
            /* يسمح بالضغط على ما تحته إلا لو كان زر */
        }

        .menu-trigger,
        .nav-controls {
            pointer-events: auto;
        }

        .menu-trigger {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            width: 50px;
            height: 50px;
            border-radius: 12px;
            cursor: pointer;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            transition: 0.3s;
        }

        .menu-trigger:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-controls {
            display: flex;
            gap: 10px;
        }

        .btn-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 12px;
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            color: var(--text-main);
            text-decoration: none;
            font-size: 0.9rem;
            cursor: pointer;
            backdrop-filter: blur(10px);
            transition: 0.3s;
        }

        .btn-nav svg {
            width: 18px;
            height: 18px;
        }

        .btn-nav:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        /* ====== الـ Drawer (القائمة الجانبية) ====== */
        .drawer {
            position: fixed;
            top: 0;
            right: -100%;
            width: var(--drawer-width);
            height: 100vh;
            background: rgba(10, 12, 18, 0.7);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-left: 1px solid var(--glass-border);
            z-index: 1002;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            padding: 2rem;
            box-shadow: -20px 0 50px rgba(0, 0, 0, 0.5);
        }

        .drawer-open .drawer {
            right: 0;
        }

        .user-profile {
            text-align: center;
            margin-bottom: 3rem;
            transform: translateY(20px);
            opacity: 0;
            transition: 0.5s 0.2s;
        }

        .drawer-open .user-profile {
            transform: translateY(0);
            opacity: 1;
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            margin: 0 auto 1rem;
            padding: 3px;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            border-radius: 18px;
            object-fit: cover;
            border: 2px solid var(--bg-dark);
        }

        .user-info h3 {
            font-size: 1.2rem;
            color: #fff;
        }

        .user-info p {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .logout-section {
            margin-top: auto;
            transform: translateY(20px);
            opacity: 0;
            transition: 0.5s 0.3s;
        }

        .drawer-open .logout-section {
            transform: translateY(0);
            opacity: 1;
        }

        .btn-logout {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            background: rgba(255, 107, 107, 0.1);
            color: #ff6b6b;
            border: 1px solid rgba(255, 107, 107, 0.2);
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
            font-family: 'Tajawal', sans-serif;
        }

        .btn-logout:hover {
            background: #ff6b6b;
            color: white;
        }

        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(3px);
            z-index: 1001;
            display: none;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .drawer-open .overlay {
            display: block;
            opacity: 1;
        }

        /* ====== الهيكل الرئيسي ====== */
        .main-wrapper {
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
            padding: 6rem 2rem 2rem 2rem;
            /* مساحة علوية للبار */
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .drawer-open .main-wrapper {
            filter: blur(5px);
            transform: scale(0.98);
            opacity: 0.8;
            pointer-events: none;
        }

        /* ====== إشعار النظام (Snackbar) ====== */
        #snackbar {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary);
            color: #000;
            padding: 12px 24px;
            border-radius: 12px;
            display: none;
            z-index: 9999;
            font-weight: bold;
            box-shadow: 0 10px 30px rgba(0, 242, 254, 0.3);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .top-bar {
                padding: 0 1rem;
            }

            .main-wrapper {
                padding: 6rem 1rem 1rem 1rem;
            }
        }

        .drawer {
            position: fixed;
            top: 0;
            right: -100%;
            width: 320px;
            height: 100vh;
            background: rgba(10, 12, 18, 0.75);
            backdrop-filter: blur(25px);
            border-left: 1px solid rgba(255, 255, 255, 0.08);
            padding: 20px;
            transition: 0.4s ease;
            z-index: 9999;
        }

        .drawer-open .drawer {
            right: 0;
        }

        .drawer-close {
            background: none;
            border: none;
            color: #fff;
            font-size: 22px;
            cursor: pointer;
        }

        .user-profile {
            text-align: center;
            margin: 20px 0;
        }

        .avatar {
            width: 70px;
            height: 70px;
            margin: auto;
            border-radius: 16px;
            background: linear-gradient(135deg, #00f2fe, #4facfe);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #000;
        }

        .drawer-links {
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }

        .drawer-links li {
            margin-bottom: 12px;
        }

        /* ===== تحسين روابط الدروار ===== */
        .drawer-links a {
            display: flex;
            align-items: center;
            gap: 8px;

            padding: 12px 14px;
            border-radius: 12px;

            color: var(--text-main);
            /* خليها فاتحة وواضحة */
            font-weight: 600;
            font-size: 0.95rem;

            text-decoration: none;

            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);

            transition: 0.3s ease;
        }

        /* hover */
        .drawer-links a:hover {
            background: rgba(0, 242, 254, 0.12);
            border-color: rgba(0, 242, 254, 0.3);
            color: #ffffff;
            transform: translateX(-6px);
        }

        /* الأيقونة */
        .drawer-links a svg {
            color: var(--primary);
            flex-shrink: 0;
        }

        /* logout مميز */
        .logout-item a {
            color: #ff6b6b !important;
            background: rgba(255, 107, 107, 0.08);
            border: 1px solid rgba(255, 107, 107, 0.2);
        }

        .logout-item a:hover {
            background: rgba(255, 107, 107, 0.2);
            color: #fff !important;
        }

    </style>
    @yield('styles')
</head>

<body>
    <div class="bg-mesh">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
    </div>

    <!-- الأزرار العلوية الثابتة -->
    <div class="top-bar">
        <!-- زر القائمة الجانبية (يمين) -->
        <button class="menu-trigger" onclick="toggleDrawer()" title="القائمة">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7" />
            </svg>
        </button>

        <!-- أزرار التحكم (يسار) -->
        <div class="nav-controls">
            <a href="{{ route('main.create') }}" class="btn-nav">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>الرئيسية</span>
            </a>
            <button class="btn-nav" onclick="window.history.back()" title="رجوع">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>

    <div class="overlay" onclick="toggleDrawer()"></div>

    <!-- القائمة الجانبية -->
    <aside class="drawer" id="drawer">
        <button onclick="toggleDrawer()"
            style="background:none; border:none; color:white; cursor:pointer; align-self:flex-start; margin-bottom:1rem;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        @auth
            @php
                $user = auth()->user();
                $role = $user->role ?? null;
                if (!$role && method_exists($user, 'hasRole')) {
                    $role = $user->hasRole('admin') ? 'admin' : 'staff';
                }
                $isAdmin = strtolower($role) === 'admin' || strtolower($role) === 'ادمن';
                $roleLabel = $isAdmin ? 'أدمن' : 'موظف';
                $initials = trim(
                    collect(explode(' ', $user->name))
                        ->map(fn($w) => mb_substr($w, 0, 1))
                        ->take(2)
                        ->join(''),
                );
            @endphp

            <div class="user-profile">
                <div class="avatar">
                    {{ $initials ?: mb_substr($user->name, 0, 1) }}
                </div>
                <div class="user-info">
                    <h3>{{ $user->name }}</h3>
                    <p>{{ $roleLabel }}</p>
                </div>
            </div>

            <hr class="drawer-sep">

            <ul class="drawer-links">
                {{-- روابط الأدمن فقط --}}
                @if ($isAdmin)
                    <li>
                        <a href="{{ route('analytics.comprehensive') }}" style="background: linear-gradient(135deg, rgba(0, 242, 254, 0.15), rgba(180, 101, 218, 0.15)); border: 1px solid rgba(0, 242, 254, 0.3); color: #fff;">
                            📊 التحليل الشامل (Analytics)
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('products.index') }}">
                            🍔 المنتجات والمنيو
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('inventory.index') }}">
                            📦 مخزن الخامات
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('recipes.index') }}">
                            🧪 معيار الوصفات (Recipes)
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('purchase-invoices.index') }}">
                            🛒 فواتير الشراء والتوريد
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('inventory.movements') }}">
                            📜 دفتر حركات المخزون
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('shifts.active') }}">
                            ⏱️ شفتات الموظفين
                        </a>
                    </li>
                @endif

                {{-- تسجيل خروج --}}
                <li class="logout-item">
                    <a href="#" id="logout-btn">
                        🚪 تسجيل الخروج
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                        @csrf
                    </form>
                </li>
            </ul>
        @endauth
    </aside>

    <!-- المحتوى الرئيسي (يحتوي على الناف بار والمحتوى) -->
    <main class="main-wrapper">
        @include('layouts.navigation')

        <div id="snackbar"></div>

        <div style="width: 100%; max-width: 1200px;">
            @yield('content')
        </div>
    </main>

    <script>
        function toggleDrawer() {
            document.body.classList.toggle('drawer-open');
        }

        // تأثير حركة الخلفية مع الماوس
        document.addEventListener('mousemove', (e) => {
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            document.querySelector('.orb-1').style.transform = `translate(${x * 40}px, ${y * 40}px)`;
            document.querySelector('.orb-2').style.transform = `translate(${x * -50}px, ${y * -50}px)`;
        });

        function showSnackbar(message) {
            const bar = document.getElementById('snackbar');
            bar.innerText = message;
            bar.style.display = 'block';
            setTimeout(() => {
                bar.style.display = 'none';
            }, 3000);
        }
    </script>
     <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logoutForm = document.getElementById('logout-form');
            const logoutBtn = document.getElementById('logout-btn');

            if (!logoutForm || !logoutBtn) return;

            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault(); // نمنع الإرسال التلقائي

                // نطلب من السيرفر إذا في شيفت مفتوح
                fetch("{{ route('shift.check') }}", {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json'
                    }
                }).then(async response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    const data = await response.json();

                    if (data.open) {
                        // رسالة تأكيد بسيطة:
                        const proceed = confirm(
                            "لديك شيفت مفتوح.\nهل تريد تسجيل الخروج دون غلق الشيفت؟\n[OK] لتسجيل الخروج دون إغلاق الشيفت، [Cancel] لإلغاء تسجيل الخروج"
                        );
                        if (proceed) {
                            logoutForm.submit();
                        } else {
                            // إلغاء تسجيل الخروج — يمكن إضافة إشعار للمستخدم هنا
                        }
                    } else {
                        // لا شيفت مفتوح -> نكمل تسجيل الخروج مباشرة
                        logoutForm.submit();
                    }
                }).catch(err => {
                    console.error('Error checking open shift:', err);
                    // لو فشل الفحص نعرض خيار للمستخدم بدلاً من منع الخروج نهائياً
                    const proceedAnyway = confirm(
                        "تعذر التحقق من حالة الشيفت.\nهل تود المتابعة وتسجيل الخروج؟");
                    if (proceedAnyway) {
                        logoutForm.submit();
                    }
                    // وإلا نلغي تسجيل الخروج
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @yield('scripts')
    @stack('scripts')
</body>

</html>
