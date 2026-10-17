@extends('layouts.appp')

@section('page_title', 'فواتير الشراء والتوريد')

@section('styles')
<style>
    .purchases-container {
        padding: 10px 0 40px 0;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }
    .stat-card {
        background: var(--card-bg);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
        padding: 18px 20px;
        backdrop-filter: blur(15px);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .glass-card {
        background: var(--card-bg);
        border: 1px solid var(--glass-border);
        border-radius: 18px;
        padding: 24px;
        backdrop-filter: blur(15px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }
    .filter-bar {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .filter-bar input, .filter-bar select {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--glass-border);
        color: var(--text-main);
        padding: 9px 14px;
        border-radius: 10px;
        font-size: 0.85rem;
    }
    .date-group-header {
        background: rgba(255, 255, 255, 0.04);
        border-right: 4px solid var(--primary);
        padding: 10px 16px;
        border-radius: 10px;
        margin: 20px 0 12px 0;
        font-weight: 700;
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
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
    .badge-status {
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        display: inline-block;
    }
</style>
@endsection

@section('content')
<div class="purchases-container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #fff;">فواتير الشراء والتوريد المخزني</h2>
            <p class="text-muted small mb-0">سجل فواتير المشتريات من الموردين، حساب الآجل والمتبقي، وتحديث المخزون التلقائي.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-info rounded-pill px-3 py-2 small">
                📦 مخزن الخامات
            </a>
            <a href="{{ route('purchase-invoices.create') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); border:none; color:#000;">
                + فاتورة شراء جديدة
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success bg-opacity-25 text-white border-0 rounded-4 mb-4">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- كروت الإحصائيات --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div>
                <p class="text-muted small mb-1">إجمالي المشتريات</p>
                <h3 class="fw-bold mb-0 text-white">{{ number_format($totalPurchases, 2) }} <small class="text-muted" style="font-size:0.75rem;">ج</small></h3>
            </div>
            <div style="font-size:1.8rem;">💰</div>
        </div>
        <div class="stat-card">
            <div>
                <p class="text-muted small mb-1">مشتريات اليوم</p>
                <h3 class="fw-bold mb-0" style="color: var(--primary);">{{ number_format($todayPurchases, 2) }} <small class="text-muted" style="font-size:0.75rem;">ج</small></h3>
            </div>
            <div style="font-size:1.8rem;">📅</div>
        </div>
        <div class="stat-card">
            <div>
                <p class="text-muted small mb-1">المتبقي (آجل غير مدفوع)</p>
                <h3 class="fw-bold mb-0 text-danger">{{ number_format($totalUnpaid, 2) }} <small class="text-muted" style="font-size:0.75rem;">ج</small></h3>
            </div>
            <div style="font-size:1.8rem;">⏳</div>
        </div>
        <div class="stat-card">
            <div>
                <p class="text-muted small mb-1">عدد فواتير الشراء</p>
                <h3 class="fw-bold mb-0 text-info">{{ $invoicesCount }}</h3>
            </div>
            <div style="font-size:1.8rem;">📑</div>
        </div>
    </div>

    <div class="glass-card">
        {{-- الفلترة --}}
        <form method="GET" action="{{ route('purchase-invoices.index') }}" class="filter-bar">
            <input type="text" name="search" placeholder="بحث برقم الفاتورة، المورد، أو الصنف..." value="{{ request('search') }}" style="flex: 2; min-width: 200px;">
            <select name="date_filter" onchange="this.form.submit()" style="flex: 1; min-width: 140px;">
                <option value="all">كل التواريخ</option>
                <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>اليوم</option>
                <option value="yesterday" {{ request('date_filter') === 'yesterday' ? 'selected' : '' }}>أمس</option>
                <option value="week" {{ request('date_filter') === 'week' ? 'selected' : '' }}>هذا الأسبوع</option>
                <option value="month" {{ request('date_filter') === 'month' ? 'selected' : '' }}>هذا الشهر</option>
            </select>
            <select name="payment_status" onchange="this.form.submit()" style="flex: 1; min-width: 140px;">
                <option value="all">كل حالات السداد</option>
                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>مدفوعة بالكامل</option>
                <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>مدفوعة جزئياً</option>
                <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>آجلة (غير مدفوعة)</option>
            </select>
            <button type="submit" class="btn btn-sm btn-outline-info rounded-pill px-3">فلترة</button>
            @if(request()->hasAny(['search', 'date_filter', 'payment_status']))
                <a href="{{ route('purchase-invoices.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">إلغاء</a>
            @endif
        </form>

        @forelse($groupedInvoices as $date => $dayInvoices)
            <div class="date-group-header">
                <span>📅 {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</span>
                <span class="small text-muted">إجمالي اليوم: {{ number_format($dayInvoices->sum('net_amount'), 2) }} ج</span>
            </div>

            <div class="table-responsive mb-4">
                <table class="table-glass">
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>المورد</th>
                            <th>الأصناف المشتراة</th>
                            <th>طريقة الدفع</th>
                            <th>الصافي</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>حالة السداد</th>
                            <th class="text-center">العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dayInvoices as $inv)
                            <tr>
                                <td class="fw-bold" style="color: var(--primary);">
                                    <a href="{{ route('purchase-invoices.show', $inv->id) }}" class="text-decoration-none" style="color: inherit;">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td class="fw-bold text-white">{{ $inv->supplier_name }}</td>
                                <td class="small text-muted" style="max-width: 200px;">
                                    {{ $inv->items->pluck('item_name')->take(3)->join('، ') }}
                                    @if($inv->items->count() > 3) <span class="text-info">+{{ $inv->items->count() - 3 }}</span> @endif
                                </td>
                                <td>{{ $inv->payment_method_label }}</td>
                                <td class="fw-bold text-white">{{ number_format($inv->net_amount, 2) }} ج</td>
                                <td class="text-success">{{ number_format($inv->paid_amount, 2) }} ج</td>
                                <td class="{{ $inv->remaining_amount > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                    {{ number_format($inv->remaining_amount, 2) }} ج
                                </td>
                                <td>
                                    @php $statusInfo = $inv->payment_status_info; @endphp
                                    <span class="badge {{ $statusInfo['badge'] }} rounded-pill px-2 py-1 small">
                                        {{ $statusInfo['label'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('purchase-invoices.show', $inv->id) }}" class="btn btn-sm btn-outline-info rounded-pill px-2" title="عرض وطباعة">
                                            👁️
                                        </a>
                                        <a href="{{ route('purchase-invoices.edit', $inv->id) }}" class="btn btn-sm btn-outline-light rounded-pill px-2" title="تعديل">
                                            ✏️
                                        </a>
                                        <form action="{{ route('purchase-invoices.destroy', $inv->id) }}" method="POST" onsubmit="return confirm('تحذير: حذف فاتورة الشراء سيقوم تلقائياً بالتراجع عن الكميات المضافة للمخزن! هل أنت متأكد؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="حذف">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                لا توجد فواتير شراء مسجلة حتى الآن. اضغط على "+ فاتورة شراء جديدة" للإضافة.
            </div>
        @endforelse
    </div>
</div>
@endsection
