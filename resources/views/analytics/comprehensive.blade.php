@extends('layouts.appp')

@section('page_title', 'التحليل الشامل للمشروع')

@section('styles')
<style>
    .comprehensive-container {
        padding: 10px 0 50px 0;
    }
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 25px;
    }
    .kpi-card {
        background: var(--card-bg);
        border: 1px solid var(--glass-border);
        border-radius: 18px;
        padding: 20px;
        backdrop-filter: blur(15px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        transition: transform 0.25s ease, border-color 0.25s ease;
        position: relative;
        overflow: hidden;
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        border-color: var(--primary);
    }
    .kpi-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        opacity: 0.8;
    }
    .kpi-title {
        font-size: 0.82rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-bottom: 6px;
    }
    .kpi-value {
        font-size: 1.65rem;
        font-weight: 800;
        color: #fff;
        margin-bottom: 4px;
        line-height: 1.2;
    }
    .kpi-sub {
        font-size: 0.78rem;
        color: var(--text-muted);
    }
    .growth-badge {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }
    .growth-up {
        background: rgba(34, 197, 94, 0.15);
        color: #4ade80;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }
    .growth-down {
        background: rgba(239, 68, 68, 0.15);
        color: #f87171;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
    .glass-section {
        background: var(--card-bg);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        padding: 24px;
        backdrop-filter: blur(15px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        margin-bottom: 25px;
    }
    .filter-btn {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--glass-border);
        color: var(--text-muted);
        padding: 7px 16px;
        border-radius: 12px;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-block;
    }
    .filter-btn:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
    }
    .filter-btn.active {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: #000;
        font-weight: 700;
        border-color: transparent;
        box-shadow: 0 0 15px rgba(0, 242, 254, 0.3);
    }
    .table-glass {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        color: var(--text-main);
    }
    .table-glass th {
        background: rgba(255, 255, 255, 0.03);
        padding: 12px 14px;
        font-size: 0.82rem;
        color: var(--text-muted);
        text-align: right;
        border-bottom: 1px solid var(--glass-border);
    }
    .table-glass td {
        padding: 12px 14px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        font-size: 0.88rem;
        vertical-align: middle;
    }
    .table-glass tr:hover td {
        background: rgba(255, 255, 255, 0.02);
    }
    .stream-card {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--glass-border);
        border-radius: 14px;
        padding: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    @media print {
        .navbar, .glass-navbar, .drawer, .filter-bar-wrapper, .btn-print {
            display: none !important;
        }
        body {
            background: #fff !important;
            color: #000 !important;
        }
        .kpi-card, .glass-section {
            background: #fff !important;
            border: 1px solid #ddd !important;
            box-shadow: none !important;
            color: #000 !important;
        }
        .kpi-value, h2, h3, h4, h5, strong {
            color: #000 !important;
        }
    }
</style>
@endsection

@section('content')
<div class="comprehensive-container">
    {{-- رأس الصفحة --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="fs-4">📊</span>
                <h2 class="fw-bold mb-0 text-white">التحليل الشامل التنفيذي (Flow Analytics)</h2>
                <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-2 py-1 rounded-pill small">
                    {{ $periodLabel }}
                </span>
            </div>
            <p class="text-muted small mb-0">لوحة تحكم تنفيذية متكاملة لتحليل الإيرادات، المصروفات، فواتير الشراء، هوامش الربحية وساعات الذروة.</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-light rounded-pill px-3 py-2 small btn-print">
                🖨️ طباعة التقرير
            </button>
            <a href="{{ route('analytics.all') }}" class="btn btn-outline-info rounded-pill px-3 py-2 small">
                📈 الإحصائيات العامة
            </a>
        </div>
    </div>

    {{-- شريط فلاتر الفترات الزمنية --}}
    <div class="glass-section filter-bar-wrapper py-3 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="text-muted small fw-bold">الفترة:</span>
                <a href="{{ route('analytics.comprehensive', ['period' => 'today']) }}" class="filter-btn {{ $period === 'today' ? 'active' : '' }}">اليوم</a>
                <a href="{{ route('analytics.comprehensive', ['period' => 'yesterday']) }}" class="filter-btn {{ $period === 'yesterday' ? 'active' : '' }}">أمس</a>
                <a href="{{ route('analytics.comprehensive', ['period' => '7days']) }}" class="filter-btn {{ $period === '7days' ? 'active' : '' }}">آخر 7 أيام</a>
                <a href="{{ route('analytics.comprehensive', ['period' => 'this_month']) }}" class="filter-btn {{ $period === 'this_month' ? 'active' : '' }}">هذا الشهر</a>
                <a href="{{ route('analytics.comprehensive', ['period' => 'last_month']) }}" class="filter-btn {{ $period === 'last_month' ? 'active' : '' }}">الشهر السابق</a>
            </div>

            {{-- نموذج فترة مخصصة --}}
            <form method="GET" action="{{ route('analytics.comprehensive') }}" class="d-flex align-items-center gap-2 flex-wrap">
                <input type="hidden" name="period" value="custom">
                <input type="date" name="from" value="{{ request('from', $startDate->format('Y-m-d')) }}" class="form-control form-control-sm text-white" style="background: rgba(255,255,255,0.06); border-color: var(--glass-border); border-radius: 10px; width: auto;" required>
                <span class="text-muted small">إلى</span>
                <input type="date" name="to" value="{{ request('to', $endDate->format('Y-m-d')) }}" class="form-control form-control-sm text-white" style="background: rgba(255,255,255,0.06); border-color: var(--glass-border); border-radius: 10px; width: auto;" required>
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">عرض</button>
            </form>
        </div>
    </div>

    {{-- كروت المؤشرات المالية الرئيسية (KPIs) --}}
    <div class="kpi-grid">
        {{-- إجمالي الإيرادات --}}
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-title">إجمالي الإيرادات (Revenue)</div>
                <span class="fs-5">💵</span>
            </div>
            <div class="kpi-value" style="color: #4ade80;">
                {{ number_format($totalRevenue, 2) }} <small style="font-size: 0.9rem;">ج.م</small>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <span class="kpi-sub">{{ $invoicesCount }} فاتورة مدفوعة</span>
                @if(!is_null($revenueGrowth))
                    <span class="growth-badge {{ $revenueGrowth >= 0 ? 'growth-up' : 'growth-down' }}">
                        {{ $revenueGrowth >= 0 ? '▲ +' : '▼ ' }}{{ $revenueGrowth }}%
                    </span>
                @endif
            </div>
        </div>

        {{-- المصروفات التشغيلية --}}
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-title">المصروفات العامة (Expenses)</div>
                <span class="fs-5">📉</span>
            </div>
            <div class="kpi-value" style="color: #f87171;">
                {{ number_format($operatingExpenses, 2) }} <small style="font-size: 0.9rem;">ج.م</small>
            </div>
            <div class="kpi-sub">مصاريف التشغيل والصيانة والنثريات</div>
        </div>

        {{-- فواتير الشراء وتوريد المخزن --}}
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-title">مشتريات وتوريد المخزن</div>
                <span class="fs-5">🛒</span>
            </div>
            <div class="kpi-value" style="color: #fb923c;">
                {{ number_format($purchasesTotal, 2) }} <small style="font-size: 0.9rem;">ج.م</small>
            </div>
            <div class="kpi-sub">تكلفة توريدات الخامات من الموردين</div>
        </div>

        {{-- صافي الربح الفعلي --}}
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-title">صافي الربح الفعلي (Net Profit)</div>
                <span class="fs-5">🏆</span>
            </div>
            <div class="kpi-value" style="color: {{ $netProfit >= 0 ? 'var(--primary)' : '#f87171' }};">
                {{ number_format($netProfit, 2) }} <small style="font-size: 0.9rem;">ج.م</small>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <span class="kpi-sub">هامش الربح:</span>
                <span class="growth-badge {{ $profitMargin >= 30 ? 'growth-up' : ($profitMargin > 0 ? 'bg-warning bg-opacity-25 text-warning' : 'growth-down') }}">
                    {{ $profitMargin }}%
                </span>
            </div>
        </div>

        {{-- متوسط قيمة الفاتورة وحركة العملاء --}}
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-title">متوسط الفاتورة & الزيارات</div>
                <span class="fs-5">👥</span>
            </div>
            <div class="kpi-value" style="color: #38bdf8;">
                {{ number_format($averageOrderValue, 2) }} <small style="font-size: 0.9rem;">ج.م</small>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <span class="kpi-sub">{{ $totalVisits }} زيارة مسجلة</span>
                <span class="badge bg-info bg-opacity-25 text-info px-2 py-1 rounded-pill small">+{{ $newClients }} عملاء جدد</span>
            </div>
        </div>
    </div>

    {{-- صف الرسوم البيانية التفاعلية --}}
    <div class="row g-4 mb-4">
        {{-- مخطط الإيرادات والمصروفات --}}
        <div class="col-lg-8">
            <div class="glass-section h-100 mb-0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-white mb-1">📈 المنحنى المالي للإيرادات مقابل المصروفات</h5>
                        <small class="text-muted">مقارنة التدفق المالي عبر الزمن ({{ in_array($period, ['today', 'yesterday']) ? 'بالساعات' : 'بالأيام' }})</small>
                    </div>
                </div>
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="timelineChart"></canvas>
                </div>
            </div>
        </div>

        {{-- دونات شارت: مصادر الإيرادات --}}
        <div class="col-lg-4">
            <div class="glass-section h-100 mb-0">
                <div class="mb-3">
                    <h5 class="fw-bold text-white mb-1">🍩 توزيع مصادر الإيرادات</h5>
                    <small class="text-muted">حجم مساهمة كل قسم في دخل المكان</small>
                </div>
                <div style="position: relative; height: 210px; width: 100%; margin-bottom: 15px;">
                    <canvas id="streamsDoughnutChart"></canvas>
                </div>
                <div>
                    <div class="stream-card">
                        <div class="d-flex align-items-center gap-2">
                            <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#00f2fe;"></span>
                            <span class="text-light small">الجلسات المفتوحة</span>
                        </div>
                        <strong class="text-white small">{{ number_format($sessionsRevenue, 2) }} ج</strong>
                    </div>
                    <div class="stream-card">
                        <div class="d-flex align-items-center gap-2">
                            <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#b465da;"></span>
                            <span class="text-light small">الحجوزات والغرف</span>
                        </div>
                        <strong class="text-white small">{{ number_format($bookingsRevenue, 2) }} ج</strong>
                    </div>
                    <div class="stream-card">
                        <div class="d-flex align-items-center gap-2">
                            <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#22c55e;"></span>
                            <span class="text-light small">الكافيه والمنتجات</span>
                        </div>
                        <strong class="text-white small">{{ number_format($allProductsItemsRevenue ?: $directProductsRevenue, 2) }} ج</strong>
                    </div>
                    <div class="stream-card">
                        <div class="d-flex align-items-center gap-2">
                            <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#f59e0b;"></span>
                            <span class="text-light small">الاشتراكات والباقات</span>
                        </div>
                        <strong class="text-white small">{{ number_format($subscriptionsRevenue, 2) }} ج</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- صف ساعات الذروة وأفضل المنتجات مبيعاً --}}
    <div class="row g-4 mb-4">
        {{-- جدول أفضل المنتجات مبيعاً وربحية --}}
        <div class="col-lg-6">
            <div class="glass-section h-100 mb-0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-white mb-1">🍔 المنتجات الأكثر مبيعاً وتحقيقاً للأرباح</h5>
                        <small class="text-muted">الكمية المباعة، التكلفة بالريسبي، وصافي الربح المحقق</small>
                    </div>
                    <a href="{{ route('recipes.index') }}" class="btn btn-outline-info btn-sm rounded-pill px-3">
                        🧪 الوصفات
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table-glass">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th class="text-center">الكمية</th>
                                <th>الإيراد</th>
                                <th>التكلفة</th>
                                <th>الربح</th>
                                <th>الهامش</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $item)
                                <tr>
                                    <td class="fw-bold text-white">{{ $item->name }}</td>
                                    <td class="text-center"><span class="badge bg-secondary bg-opacity-25 text-light px-2 py-1 rounded-pill">{{ (int)$item->total_qty }}</span></td>
                                    <td class="text-success">{{ number_format($item->total_revenue, 2) }} ج</td>
                                    <td class="text-muted">{{ number_format($item->total_cost, 2) }} ج</td>
                                    <td class="fw-bold" style="color: var(--primary);">{{ number_format($item->profit, 2) }} ج</td>
                                    <td>
                                        <span class="growth-badge {{ $item->margin >= 40 ? 'growth-up' : 'growth-down' }}">
                                            {{ $item->margin }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        لا توجد مبيعات منتجات مسجلة في هذه الفترة.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- الخامات الأكثر استهلاكاً من سجل الحركات --}}
        <div class="col-lg-6">
            <div class="glass-section h-100 mb-0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-white mb-1">📦 الخامات الأكثر استهلاكاً في المخزن</h5>
                        <small class="text-muted">المواد الخام المنصرفة آلياً عبر ريسبي المبيعات</small>
                    </div>
                    <a href="{{ route('inventory.movements') }}" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        📜 سجل الحركات
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table-glass">
                        <thead>
                            <tr>
                                <th>اسم الخامة</th>
                                <th>الوحدة</th>
                                <th>إجمالي الاستهلاك</th>
                                <th>سعر الوحدة</th>
                                <th>إجمالي تكلفة المنصرف</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topInventoryConsumed as $mov)
                                <tr>
                                    <td class="fw-bold text-white">{{ $mov->inventoryItem->name ?? 'خامة غير معروفة' }}</td>
                                    <td><span class="badge bg-info bg-opacity-25 text-info px-2 py-1 rounded-pill small">{{ $mov->inventoryItem->unit ?? '-' }}</span></td>
                                    <td class="fw-bold text-warning" dir="ltr">{{ floatval($mov->total_consumed) }}</td>
                                    <td class="text-muted">{{ number_format($mov->inventoryItem->unit_price ?? 0, 2) }} ج</td>
                                    <td class="text-danger fw-bold">
                                        {{ number_format(($mov->inventoryItem->unit_price ?? 0) * $mov->total_consumed, 2) }} ج
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        لا توجد حركات استهلاك خامات مسجلة في هذه الفترة.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- صف ساعات الذروة وأداء الشيفتات --}}
    <div class="row g-4">
        {{-- ساعات الذروة --}}
        <div class="col-lg-7">
            <div class="glass-section h-100 mb-0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-white mb-1">⏱️ تحليل ساعات الذروة والنشاط (Peak Hours)</h5>
                        <small class="text-muted">توزيع الفواتير وحجم العمل على مدار ساعات اليوم</small>
                    </div>
                    <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 px-3 py-1 rounded-pill small">
                        أعلى ذروة: {{ $peakHourFormatted }}
                    </span>
                </div>
                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="peakHoursChart"></canvas>
                </div>
            </div>
        </div>

        {{-- أداء الشيفتات والموظفين --}}
        <div class="col-lg-5">
            <div class="glass-section h-100 mb-0">
                <div class="mb-3">
                    <h5 class="fw-bold text-white mb-1">💼 أداء الكاشير والشيفتات</h5>
                    <small class="text-muted">الموظفون الأكثر تسجيلاً للفواتير والتحصيل</small>
                </div>
                <div class="table-responsive">
                    <table class="table-glass">
                        <thead>
                            <tr>
                                <th>الموظف</th>
                                <th class="text-center">الفواتير</th>
                                <th class="text-end">إجمالي التحصيل</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffPerformance as $staff)
                                <tr>
                                    <td class="fw-bold text-white">{{ $staff->user->name ?? 'غير محدد' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary bg-opacity-25 text-light px-2 py-1 rounded-pill">{{ $staff->invoices_count }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-success">{{ number_format($staff->total_collected, 2) }} ج</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        لا توجد بيانات شيفتات في هذه الفترة.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. مخطط الإيرادات والمصروفات الزمني
        const timelineCtx = document.getElementById('timelineChart')?.getContext('2d');
        if (timelineCtx) {
            new Chart(timelineCtx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [
                        {
                            label: 'الإيرادات (ج.م)',
                            data: @json($chartRevenueData),
                            borderColor: '#00f2fe',
                            backgroundColor: 'rgba(0, 242, 254, 0.1)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBackgroundColor: '#00f2fe'
                        },
                        {
                            label: 'المصروفات (ج.م)',
                            data: @json($chartExpensesData),
                            borderColor: '#f87171',
                            backgroundColor: 'rgba(239, 68, 68, 0.05)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBackgroundColor: '#f87171'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: '#e2e8f0', font: { family: 'Tajawal, sans-serif' } }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#94a3b8' },
                            grid: { color: 'rgba(255, 255, 255, 0.05)' }
                        },
                        y: {
                            ticks: { color: '#94a3b8' },
                            grid: { color: 'rgba(255, 255, 255, 0.05)' }
                        }
                    }
                }
            });
        }

        // 2. دونات شارت: مصادر الإيرادات
        const doughnutCtx = document.getElementById('streamsDoughnutChart')?.getContext('2d');
        if (doughnutCtx) {
            new Chart(doughnutCtx, {
                type: 'doughnut',
                data: {
                    labels: ['الجلسات', 'الحجوزات', 'المنتجات/الكافيه', 'الاشتراكات'],
                    datasets: [{
                        data: [
                            {{ $sessionsRevenue }},
                            {{ $bookingsRevenue }},
                            {{ $allProductsItemsRevenue ?: $directProductsRevenue }},
                            {{ $subscriptionsRevenue }}
                        ],
                        backgroundColor: ['#00f2fe', '#b465da', '#22c55e', '#f59e0b'],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    cutout: '72%'
                }
            });
        }

        // 3. مخطط ساعات الذروة
        const peakCtx = document.getElementById('peakHoursChart')?.getContext('2d');
        if (peakCtx) {
            const rawPeak = @json($peakHoursRaw);
            const hours = [];
            const counts = [];
            for (let i = 0; i < 24; i++) {
                hours.push(String(i).padStart(2, '0') + ':00');
                const found = rawPeak.find(p => p.hour == i);
                counts.push(found ? found.count : 0);
            }

            new Chart(peakCtx, {
                type: 'bar',
                data: {
                    labels: hours,
                    datasets: [{
                        label: 'عدد الفواتير المنفذة',
                        data: counts,
                        backgroundColor: 'rgba(180, 101, 218, 0.4)',
                        borderColor: '#b465da',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#e2e8f0' } }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#94a3b8', maxRotation: 45, minRotation: 45 },
                            grid: { display: false }
                        },
                        y: {
                            ticks: { color: '#94a3b8', stepSize: 1 },
                            grid: { color: 'rgba(255, 255, 255, 0.05)' }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
