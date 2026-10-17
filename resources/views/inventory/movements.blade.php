@extends('layouts.appp')

@section('page_title', 'سجل حركات المخزون')

@section('styles')
<style>
    .movements-container {
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
    .badge-type {
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        display: inline-block;
    }
</style>
@endsection

@section('content')
<div class="movements-container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #fff;">سجل دفتر حركات المخزون</h2>
            <p class="text-muted small mb-0">التتبع التاريخي الكامل لكل جرام ومل تم صرفه أو توريده أو استرجاعه.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-light rounded-pill px-3 py-2 small">
                ← العودة للمخزن
            </a>
            <a href="{{ route('inventory.daily_tracking') }}" class="btn btn-outline-info rounded-pill px-3 py-2 small">
                📊 تقرير التكاليف
            </a>
        </div>
    </div>

    {{-- كروت اليوم السريعة --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div>
                <p class="text-muted small mb-1">حركات البيع اليوم</p>
                <h3 class="fw-bold mb-0 text-white">{{ $todaySales }} <small class="text-muted" style="font-size:0.8rem;">حركة</small></h3>
            </div>
            <div style="font-size:1.8rem;">🛒</div>
        </div>
        <div class="stat-card">
            <div>
                <p class="text-muted small mb-1">حركات التوريد اليوم</p>
                <h3 class="fw-bold mb-0" style="color: var(--primary);">{{ $todayRestock }} <small class="text-muted" style="font-size:0.8rem;">حركة</small></h3>
            </div>
            <div style="font-size:1.8rem;">📥</div>
        </div>
        <div class="stat-card">
            <div>
                <p class="text-muted small mb-1">حركات التالف / الهالك اليوم</p>
                <h3 class="fw-bold mb-0 text-warning">{{ $todayWaste }} <small class="text-muted" style="font-size:0.8rem;">حركة</small></h3>
            </div>
            <div style="font-size:1.8rem;">⚠️</div>
        </div>
        <div class="stat-card">
            <div>
                <p class="text-muted small mb-1">الأكثر استهلاكاً اليوم</p>
                <h4 class="fw-bold mb-0 text-info text-truncate" style="max-width: 170px;">{{ $mostConsumedName }}</h4>
            </div>
            <div style="font-size:1.8rem;">🔥</div>
        </div>
    </div>

    <div class="glass-card">
        {{-- الفلتر --}}
        <form method="GET" action="{{ route('inventory.movements') }}" class="filter-bar">
            <input type="text" name="inventory" placeholder="اسم الخامة..." value="{{ request('inventory') }}" style="flex: 1; min-width: 140px;">
            <select name="type" style="min-width: 130px;">
                <option value="all">كل أنواع الحركات</option>
                <option value="sale" {{ request('type') === 'sale' ? 'selected' : '' }}>خصم بيع (Sale)</option>
                <option value="sale_cancel" {{ request('type') === 'sale_cancel' ? 'selected' : '' }}>استرجاع بيع (Refund)</option>
                <option value="restock" {{ request('type') === 'restock' ? 'selected' : '' }}>توريد / شراء (Restock)</option>
                <option value="waste" {{ request('type') === 'waste' ? 'selected' : '' }}>هالك / تالف (Waste)</option>
                <option value="sale_update" {{ request('type') === 'sale_update' ? 'selected' : '' }}>تسوية تعديل</option>
            </select>
            <input type="date" name="from" value="{{ request('from') }}" title="من تاريخ">
            <input type="date" name="to" value="{{ request('to') }}" title="إلى تاريخ">
            <button type="submit" class="btn btn-sm btn-outline-info rounded-pill px-3">فلترة</button>
            @if(request()->hasAny(['inventory', 'type', 'from', 'to']))
                <a href="{{ route('inventory.movements') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">إلغاء</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>التاريخ والوقت</th>
                        <th>المادة الخام</th>
                        <th>نوع الحركة</th>
                        <th>الكمية</th>
                        <th>الرصيد بعد الحركة</th>
                        <th>الفاتورة المرتبطة</th>
                        <th>المستخدم / الموظف</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                        <tr>
                            <td class="text-muted small">{{ $m->id }}</td>
                            <td class="small">{{ $m->created_at->format('Y-m-d h:i A') }}</td>
                            <td class="fw-bold text-white">{{ $m->inventoryItem->name ?? 'خامة محذوفة' }}</td>
                            <td>
                                @if($m->type === 'sale')
                                    <span class="badge-type" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);">خصم بيع</span>
                                @elseif($m->type === 'sale_cancel')
                                    <span class="badge-type" style="background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3);">استرجاع بيع</span>
                                @elseif($m->type === 'restock')
                                    <span class="badge-type" style="background: rgba(0, 242, 254, 0.15); color: var(--primary); border: 1px solid rgba(0, 242, 254, 0.3);">توريد مخزني</span>
                                @elseif($m->type === 'waste')
                                    <span class="badge-type" style="background: rgba(234, 179, 8, 0.15); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.3);">هالك / تالف</span>
                                @else
                                    <span class="badge-type" style="background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3);">تسوية تعديل</span>
                                @endif
                            </td>
                            <td class="fw-bold {{ (float) $m->quantity > 0 ? 'text-success' : 'text-danger' }}" dir="ltr">
                                {{ (float) $m->quantity > 0 ? '+' : '' }}{{ floatval($m->quantity) }} {{ $m->inventoryItem->unit ?? '' }}
                            </td>
                            <td class="fw-bold text-info">
                                {{ floatval($m->balance_after) }} {{ $m->inventoryItem->unit ?? '' }}
                            </td>
                            <td>
                                @if($m->invoice)
                                    <a href="{{ route('invoices.client.show', $m->invoice->id) }}" class="text-decoration-none text-light small">
                                        #{{ $m->invoice->invoice_number }}
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $m->user->name ?? 'النظام' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                لا توجد حركات مسجلة تطابق معايير البحث.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $movements->links() }}
        </div>
    </div>
</div>
@endsection
