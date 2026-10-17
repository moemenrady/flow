@extends('layouts.appp')

@section('page_title', 'تقرير متابعة المخزون والتكاليف')

@section('styles')
<style>
    .tracking-container {
        padding: 10px 0 40px 0;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
</style>
@endsection

@section('content')
<div class="tracking-container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #fff;">تقرير متابعة المخزون والتقييم المالي</h2>
            <p class="text-muted small mb-0">جرد تفصيلي لكافة الخامات بالمخزن وقيمتها المالية الإجمالية وحدود الطلب والتنبيهات.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.export_tracking') }}" class="btn btn-outline-success rounded-pill px-3 py-2 small fw-bold">
                📥 تصدير كملف Excel / CSV
            </a>
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-light rounded-pill px-3 py-2 small">
                ← العودة للمخزن
            </a>
        </div>
    </div>

    {{-- بطاقات الإحصائيات المالية والتنبيهية --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div>
                <p class="text-muted small mb-1">إجمالي تقييم المخزن</p>
                <h3 class="fw-bold mb-0 text-success">{{ number_format($totalValuation, 2) }} <small class="text-muted" style="font-size:0.75rem;">ج</small></h3>
            </div>
            <div style="font-size:1.8rem;">💵</div>
        </div>
        <div class="stat-card">
            <div>
                <p class="text-muted small mb-1">إجمالي الوحدات بالمخزن</p>
                <h3 class="fw-bold mb-0" style="color: var(--primary);">{{ number_format($totalAvailableUnits, 1) }}</h3>
            </div>
            <div style="font-size:1.8rem;">📦</div>
        </div>
        <div class="stat-card">
            <div>
                <p class="text-muted small mb-1">أصناف تحتاج إعادة طلب</p>
                <h3 class="fw-bold mb-0 text-warning">{{ $reorderItemsCount }}</h3>
            </div>
            <div style="font-size:1.8rem;">⚠️</div>
        </div>
        <div class="stat-card">
            <div>
                <p class="text-muted small mb-1">أصناف منتهية تماماً</p>
                <h3 class="fw-bold mb-0 text-danger">{{ $outOfStockItems->count() }}</h3>
            </div>
            <div style="font-size:1.8rem;">🚫</div>
        </div>
    </div>

    <div class="glass-card">
        {{-- الفلاتر --}}
        <form method="GET" action="{{ route('inventory.daily_tracking') }}" class="filter-bar">
            <input type="text" name="search" placeholder="بحث بالاسم أو الكود..." value="{{ request('search') }}" style="flex: 2; min-width: 180px;">
            <select name="category" onchange="this.form.submit()" style="flex: 1; min-width: 140px;">
                <option value="all">كل التصنيفات</option>
                @foreach($categoriesList as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            <select name="status" onchange="this.form.submit()" style="flex: 1; min-width: 130px;">
                <option value="all">كل الحالات</option>
                <option value="متوفر" {{ request('status') === 'متوفر' ? 'selected' : '' }}>متوفر</option>
                <option value="منخفض" {{ request('status') === 'منخفض' ? 'selected' : '' }}>منخفض</option>
                <option value="نفد" {{ request('status') === 'نفد' ? 'selected' : '' }}>نفد</option>
            </select>
            <button type="submit" class="btn btn-sm btn-outline-info rounded-pill px-3">فلترة</button>
            @if(request()->hasAny(['search', 'category', 'status']))
                <a href="{{ route('inventory.daily_tracking') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">إلغاء</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>الكود</th>
                        <th>اسم الخامة</th>
                        <th>التصنيف</th>
                        <th>الرصيد الفعلي</th>
                        <th>الوحدة</th>
                        <th>تكلفة الوحدة</th>
                        <th>القيمة الإجمالية</th>
                        <th>حد النواقص</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sumValuation = 0; @endphp
                    @forelse($items as $item)
                        @php $sumValuation += $item->total_value; @endphp
                        <tr>
                            <td class="text-muted small">{{ $item->item_code }}</td>
                            <td class="fw-bold text-white">{{ $item->name }}</td>
                            <td><span class="badge bg-secondary bg-opacity-25 text-light px-2 py-1 rounded-pill small">{{ $item->category ?: 'عام' }}</span></td>
                            <td class="fw-bold" style="color: var(--primary);">{{ floatval($item->quantity) }}</td>
                            <td class="text-muted small">{{ $item->unit }}</td>
                            <td>{{ number_format($item->unit_price, 2) }} ج</td>
                            <td class="fw-bold text-success">{{ number_format($item->total_value, 2) }} ج</td>
                            <td class="text-muted small">{{ floatval($item->reorder_level) }}</td>
                            <td>
                                @if($item->status === 'متوفر')
                                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-2 py-1 rounded-pill small">متوفر</span>
                                @elseif($item->status === 'منخفض')
                                    <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 px-2 py-1 rounded-pill small">منخفض</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 px-2 py-1 rounded-pill small">نفد</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                لا توجد عناصر تطابق الفلاتر المحددة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($items->isNotEmpty())
                    <tfoot>
                        <tr style="border-top: 2px solid var(--glass-border); font-weight: bold;">
                            <td colspan="6" class="text-start py-3 pe-4 text-white">إجمالي قيمة الأصناف المعروضة:</td>
                            <td class="text-success py-3">{{ number_format($sumValuation, 2) }} ج</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
