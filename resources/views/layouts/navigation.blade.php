<style>
    .glass-navbar {
        width: 100%;
        max-width: 1200px;
        background: var(--card-bg);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
        padding: 0.5rem;
        margin-bottom: 2rem;
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        white-space: nowrap;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    }

    /* إخفاء شريط التمرير مع بقاء خاصية السحب */
    .glass-navbar::-webkit-scrollbar {
        display: none;
    }

    .glass-navbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .nav-link {
        padding: 0.8rem 1.5rem;
        border-radius: 12px;
        color: var(--text-muted);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .nav-link:hover {
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-main);
    }

    .nav-link.active {
        background: linear-gradient(135deg, rgba(0, 242, 254, 0.1), rgba(79, 172, 254, 0.1));
        color: var(--primary);
        border: 1px solid rgba(0, 242, 254, 0.2);
        font-weight: 700;
        box-shadow: 0 0 15px rgba(0, 242, 254, 0.1);
    }
</style>

<nav class="glass-navbar">
    <a href="{{ route('main.create') }}" class="nav-link {{ Request::is('/') || Request::is('main*') ? 'active' : '' }}">
        الرئيسية
    </a>

    <a href="{{ route('expenses.index') }}" class="nav-link {{ Request::is('expenses*') ? 'active' : '' }}">
        المصروفات
    </a>

    <a href="{{ route('subscriptions.index') }}" class="nav-link {{ Request::is('subscriptions*') ? 'active' : '' }}">
        الاشتراكات
    </a>

    <a href="{{ route('clients.index') }}" class="nav-link {{ Request::is('clients*') ? 'active' : '' }}">
        العملاء
    </a>

    @if (Auth::check() && Auth::user()->role === 'user')
        <a href="{{ route('shift.index') }}" class="nav-link {{ Request::is('shift*') ? 'active' : '' }}">
            الشفتات
        </a>
    @endif

    @if (Auth::check() && (Auth::user()->role === 'admin' || (method_exists(Auth::user(), 'isManager') && Auth::user()->isManager())))
        <a href="{{ route('analytics.comprehensive') }}" class="nav-link {{ Request::is('analytics*') ? 'active' : '' }}" style="background: linear-gradient(135deg, rgba(0, 242, 254, 0.15), rgba(180, 101, 218, 0.15)); border: 1px solid rgba(0, 242, 254, 0.35); color: #fff; font-weight: 700;">
            📊 التحليل الشامل
        </a>
        <a href="{{ route('products.index') }}" class="nav-link {{ Request::is('products*') ? 'active' : '' }}">
            المنتجات
        </a>
        <a href="{{ route('inventory.index') }}" class="nav-link {{ Request::is('inventory*') ? 'active' : '' }}">
            الخامات والمخزون
        </a>
        <a href="{{ route('recipes.index') }}" class="nav-link {{ Request::is('recipes*') ? 'active' : '' }}">
            معيار الوصفات
        </a>
        <a href="{{ route('purchase-invoices.index') }}" class="nav-link {{ Request::is('purchase-invoices*') ? 'active' : '' }}">
            فواتير الشراء
        </a>
        <a href="{{ route('managment.create') }}" class="nav-link {{ Request::is('managment*') ? 'active' : '' }}">
            الإدارة
        </a>
    @endif
</nav>
