@extends('layouts.appp')

@section('page_title', 'لوحة التحكم الرئيسية')

@section('styles')
    <style>
        .dashboard-header {
            margin-bottom: 3rem;
            text-align: right;
            animation: fadeInDown 0.8s ease;
        }

        .dashboard-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: var(--text-main);
        }

        .dashboard-header h1 span {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .dashboard-header p {
            font-size: 1.1rem;
            color: var(--text-muted);
        }

        .modern-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .modern-card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 1.2rem;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .modern-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.05), transparent);
            transform: skewX(-20deg);
            transition: 0.5s;
        }

        .modern-card:hover {
            transform: translateY(-8px);
            border-color: rgba(0, 242, 254, 0.4);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4), 0 0 15px rgba(0, 242, 254, 0.1);
        }

        .modern-card:hover::before {
            left: 150%;
        }

        .modern-card.highlight {
            border-color: rgba(180, 101, 218, 0.4);
            box-shadow: 0 0 20px rgba(180, 101, 218, 0.1);
        }

        .modern-card.highlight:hover {
            border-color: var(--accent);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4), 0 0 20px rgba(180, 101, 218, 0.3);
        }

        .card-icon {
            width: 60px;
            height: 60px;
            flex-shrink: 0;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .card-icon img {
            width: 32px;
            height: 32px;
            filter: invert(1) opacity(0.8);
            transition: 0.3s;
        }

        .modern-card:hover .card-icon img {
            transform: scale(1.1);
            filter: invert(1) drop-shadow(0 0 8px var(--primary));
        }

        .modern-card.highlight:hover .card-icon img {
            filter: invert(1) drop-shadow(0 0 8px var(--accent));
        }

        .card-body h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.3rem;
            transition: 0.3s;
        }

        .card-body p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.4;
        }

        .modern-card:hover .card-body h3 {
            color: var(--primary);
        }

        .modern-card.highlight:hover .card-body h3 {
            color: var(--accent);
        }

        .status-dot {
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #ff4757;
            border: 3px solid #10141e;
            /* لون مقارب لخلفية الكارت */
            box-shadow: 0 0 10px #ff4757;
        }

        .status-dot.active {
            background: #2ed573;
            box-shadow: 0 0 10px #2ed573;
        }

        .modern-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--accent);
            color: #fff;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            box-shadow: 0 0 10px var(--accent);
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection

@section('content')
    <header class="dashboard-header">
        <h1>أهلاً بك، <span>{{ auth()->user()->name ?? 'مستخدم' }}</span></h1>
        <p>ماذا تود أن تفعل اليوم؟</p>
    </header>

    <section class="modern-grid">
        {{-- كرت: إدارة الجلسات --}}
        <a href="{{ route('session.index-manager') }}" class="modern-card">
            <div class="card-icon">
                <img src="https://img.icons8.com/fluency-systems-regular/64/user-group-man-man.png" alt="icon">
            </div>
            <div class="card-body">
                <h3>إدارة الجلسات</h3>
                <p>تنظيم ومتابعة الجلسات الحالية</p>
            </div>
        </a>

        {{-- كرت: بدء عملية بيع --}}
        <a href="{{ route('sale_proccess.create') }}" class="modern-card highlight">
            <div class="card-icon">
                <img src="https://img.icons8.com/fluency-systems-regular/64/sell.png" alt="icon">
            </div>
            <div class="card-body">
                <h3>بدء عملية بيع</h3>
                <p>تسجيل مبيعات جديدة فوراً</p>
            </div>
        </a>

        {{-- كرت: المشتركين --}}
        <a href="{{ route('subscriptions.index-manager') }}" class="modern-card">
            <div class="card-icon">
                <img src="https://img.icons8.com/fluency-systems-regular/64/conference.png" alt="icon">
            </div>
            <div class="card-body">
                <h3>المشتركين</h3>
                <p>قاعدة بيانات العملاء والاشتراكات</p>
            </div>
        </a>

        {{-- كرت: الفواتير --}}
        <a href="{{ route('invoice.index') }}" class="modern-card">
            <div class="card-icon">
                <img src="https://img.icons8.com/fluency-systems-regular/64/bill.png" alt="icon">
            </div>
            <div class="card-body">
                <h3>الفواتير</h3>
                <p>استعراض وطباعة فواتير النظام</p>
            </div>
        </a>

        @if (Auth::check() && Auth::user()->role === 'admin')
            {{-- كرت: حساب الموظفين --}}
            <a href="{{ route('admin.calendar') }}" class="modern-card">
                <div class="card-icon">
                    <img src="https://img.icons8.com/fluency-systems-regular/64/calendar-external.png" alt="icon">
                </div>
                <div class="card-body">
                    <h3>حساب الموظفين</h3>
                    <p>متابعة أداء الموظفين والتقويم</p>
                </div>
            </a>

            {{-- كرت: إضافة مصروف للادمن --}}
            <a href="{{ route('expenses.create') }}" class="modern-card">
                <div class="card-icon">
                    <img src="https://img.icons8.com/ios/50/money-transfer.png" alt="icon">
                </div>
                <div class="card-body">
                    <h3>إضافة مصروف</h3>
                    <p>تسجيل مصروفات المركز</p>
                </div>
            </a>
        @else
            @php
                $openShift = \App\Models\Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
            @endphp

            {{-- كرت الشفت (ديناميكي) --}}
            <a href="{{ $openShift ? route('shift.show', $openShift->id) : route('shift.create') }}"
                class="modern-card {{ $openShift ? 'shift-online' : 'shift-offline' }}">
                <div class="card-icon">
                    <img src="https://img.icons8.com/fluency-systems-regular/64/clock.png" alt="icon">
                    <span class="status-dot {{ $openShift ? 'active' : '' }}"></span>
                </div>
                <div class="card-body">
                    <h3>{{ $openShift ? 'الشفت مفتوح' : 'الشفت مغلق' }}</h3>
                    <p>{{ $openShift ? 'قيد العمل الآن - اضغط للتفاصيل' : 'ابدأ شفت جديد للعمل' }}</p>
                </div>
            </a>

            {{-- كرت المصروفات للموظف --}}
            <a href="{{ route('expenses.create') }}" class="modern-card">
                <div class="card-icon">
                    <img src="https://img.icons8.com/fluency-systems-regular/64/money-transfer.png" alt="icon">
                </div>
                <div class="card-body">
                    <h3>المصروفات</h3>
                    <p>تسجيل مصروفات نثرية أو عهدة</p>
                </div>
            </a>

            {{-- مسودات المصروفات --}}
            <a href="{{ route('expense-drafts.index') }}" class="modern-card">
                <div class="card-icon">
                    <img src="https://img.icons8.com/ios/50/money-transfer.png" alt="icon">
                </div>
                <div class="card-body">
                    <h3>مسودات المصروفات</h3>
                    <p>مراجعة المصروفات المعلقة</p>
                </div>
            </a>
        @endif

        {{-- كرت: جلسات لم تسجل --}}
        <a href="{{ route('new-session.index') }}" class="modern-card">
            <div class="card-icon">
                <img src="https://img.icons8.com/fluency-systems-regular/64/barcode.png" alt="icon">
            </div>
            <div class="card-body">
                <h3>جلسات غير مسجلة</h3>
                <p>مراجعة الجلسات المعلقة</p>
            </div>
            @if (isset($newSessions) && $newSessions->count())
                <span class="modern-badge">
                    {{ $newSessions->count() }}
                </span>
            @endif
        </a>
    </section>
@endsection
