@extends('layouts.appp')

@section('page_title', 'مخزن المواد الخام')

@section('styles')
<style>
    .inventory-container {
        padding: 10px 0 40px 0;
    }

    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 25px;
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
        transition: transform 0.25s ease, border-color 0.25s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        border-color: var(--primary);
    }

    .stat-info p {
        margin: 0;
        font-size: 0.82rem;
        color: var(--text-muted);
    }

    .stat-info h3 {
        margin: 5px 0 0 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #fff;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
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
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .filter-bar input, .filter-bar select {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--glass-border);
        color: var(--text-main);
        padding: 10px 16px;
        border-radius: 10px;
        font-size: 0.9rem;
    }

    .filter-bar input:focus, .filter-bar select:focus {
        outline: none;
        border-color: var(--primary);
    }

    .btn-neon {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: #000;
        font-weight: 700;
        padding: 10px 20px;
        border-radius: 12px;
        text-decoration: none;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 0 15px rgba(0, 242, 254, 0.3);
        transition: 0.3s;
    }

    .btn-neon:hover {
        transform: translateY(-2px);
        box-shadow: 0 0 25px rgba(0, 242, 254, 0.5);
        color: #000;
    }

    .btn-glass {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid var(--glass-border);
        color: var(--text-main);
        padding: 9px 16px;
        border-radius: 10px;
        text-decoration: none;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: 0.25s;
    }

    .btn-glass:hover {
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
    }

    .table-glass {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        color: var(--text-main);
    }

    .table-glass th {
        background: rgba(255, 255, 255, 0.03);
        padding: 14px 16px;
        font-size: 0.85rem;
        color: var(--text-muted);
        text-align: right;
        border-bottom: 1px solid var(--glass-border);
    }

    .table-glass td {
        padding: 14px 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        font-size: 0.9rem;
        vertical-align: middle;
    }

    .table-glass tr:hover td {
        background: rgba(255, 255, 255, 0.03);
    }

    .badge-status {
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        display: inline-block;
    }

    .badge-success { background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
    .badge-warning { background: rgba(234, 179, 8, 0.15); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.3); }
    .badge-danger { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
</style>
@endsection

@section('content')
<div class="inventory-container">

    {{-- رسائل التنبيه --}}
    @if(session('success'))
        <div class="alert alert-success bg-success bg-opacity-25 text-white border-0 rounded-4 mb-4">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger bg-danger bg-opacity-25 text-white border-0 rounded-4 mb-4">
            ❌ {{ session('error') }}
        </div>
    @endif

    {{-- رأس الصفحة والأزرار السريعة --}}
    <div class="header-actions">
        <div>
            <h2 class="fw-bold mb-1" style="color: #fff;">مخزن المواد الخام والتشغيل</h2>
            <p class="text-muted mb-0 small">إدارة الخامات، حدود النواقص، والتسويات التلقائية مع فواتير الشراء ووصفات المنتجات.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('purchase-invoices.index') }}" class="btn-glass">
                🛒 فواتير الشراء والتوريد
            </a>
            <a href="{{ route('recipes.index') }}" class="btn-glass">
                ☕ معيار الوصفات (Recipes)
            </a>
            <a href="{{ route('inventory.daily_tracking') }}" class="btn-glass">
                📊 تقرير متابعة التكاليف
            </a>
            <a href="{{ route('inventory.movements') }}" class="btn-glass">
                📜 سجل الحركات
            </a>
            <button type="button" class="btn-neon" data-bs-toggle="modal" data-bs-target="#quickAddModal">
                + إضافة خامة سريعة
            </button>
        </div>
    </div>

    {{-- كروت الإحصائيات السريعة --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <p>إجمالي الأصناف</p>
                <h3>{{ $totalItemsCount }} <small style="font-size:0.8rem; font-weight:normal;">خامة</small></h3>
            </div>
            <div class="stat-icon" style="background: rgba(0, 242, 254, 0.1); color: var(--primary);">📦</div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <p>القيمة الإجمالية للمخزون</p>
                <h3 style="color: #4ade80;">{{ number_format($totalValuation, 2) }} <small style="font-size:0.8rem; font-weight:normal;">ج.م</small></h3>
            </div>
            <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #4ade80;">💰</div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <p>أصناف أوشكت على النفاد</p>
                <h3 style="color: #facc15;">{{ $lowStockCount }} <small style="font-size:0.8rem; font-weight:normal;">خامة</small></h3>
            </div>
            <div class="stat-icon" style="background: rgba(234, 179, 8, 0.1); color: #facc15;">⚠️</div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <p>أصناف نفدت بالكامل</p>
                <h3 style="color: #f87171;">{{ $outOfStockCount }} <small style="font-size:0.8rem; font-weight:normal;">خامة</small></h3>
            </div>
            <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #f87171;">🚫</div>
        </div>
    </div>

    {{-- الفلاتر والجدول الرئيسي --}}
    <div class="glass-card">
        <form method="GET" action="{{ route('inventory.index') }}" class="filter-bar">
            <input type="text" name="search" placeholder="🔍 ابحث باسم الخامة أو الكود..." value="{{ request('search') }}" style="flex: 2; min-width: 200px;">
            <select name="category" onchange="this.form.submit()" style="flex: 1; min-width: 150px;">
                <option value="all">كل التصنيفات</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-glass">فلترة</button>
            @if(request()->hasAny(['search', 'category']))
                <a href="{{ route('inventory.index') }}" class="btn-glass text-muted">إلغاء الفلتر</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>الكود</th>
                        <th>اسم الخامة</th>
                        <th>التصنيف</th>
                        <th>الرصيد المتوفر</th>
                        <th>سعر الوحدة</th>
                        <th>القيمة الكلية</th>
                        <th>حد النواقص</th>
                        <th>الحالة</th>
                        <th class="text-center">تسوية سريعة</th>
                        <th class="text-center">العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td class="text-muted small">{{ $item->item_code }}</td>
                            <td class="fw-bold text-white">{{ $item->name }}</td>
                            <td><span class="badge bg-secondary bg-opacity-25 text-light px-2 py-1 rounded-pill small">{{ $item->category ?: 'عام' }}</span></td>
                            <td class="fw-bold" style="color: var(--primary);">
                                {{ floatval($item->quantity) }} {{ $item->unit }}
                            </td>
                            <td>{{ number_format($item->unit_price, 2) }} ج</td>
                            <td>{{ number_format($item->total_value, 2) }} ج</td>
                            <td class="text-muted">{{ floatval($item->reorder_level) }} {{ $item->unit }}</td>
                            <td>
                                @if($item->status === 'متوفر')
                                    <span class="badge-status badge-success">متوفر</span>
                                @elseif($item->status === 'منخفض')
                                    <span class="badge-status badge-warning">منخفض</span>
                                @else
                                    <span class="badge-status badge-danger">نفد</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1" onclick="openAdjustModal({{ $item->id }}, '{{ $item->name }}', '{{ $item->unit }}', {{ $item->quantity }})">
                                    ⚡ تسوية
                                </button>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('inventory.edit', $item->id) }}" class="btn btn-sm btn-outline-light rounded-pill px-2" title="تعديل">
                                        ✏️
                                    </a>
                                    <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الخامة؟ لن يمكن الحذف إذا كانت مستخدمة في وصفات منتجات.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="حذف">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                لا توجد خامات مسجلة في المخزن حالياً. اضغط على "+ إضافة خامة جديدة" للبدء.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- نافذة منبثقة Modal للتسوية السريعة --}}
<div class="modal fade" id="adjustModal" tabindex="-1" aria-hidden="true" dir="rtl">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-white" style="background: #111522; border: 1px solid var(--glass-border); border-radius: 20px;">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold" id="adjustModalTitle">تسوية سريعة للخامة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="adjustForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3">الرصيد الحالي بالمخزن: <strong id="currentStockDisplay" class="text-info"></strong></p>

                    <div class="mb-3">
                        <label class="form-label text-muted small">نوع التسوية</label>
                        <select name="type" class="form-select text-white" style="background: rgba(255,255,255,0.08); border-color: var(--glass-border);" required>
                            <option value="restock">توريد زيادة للمخزن (+)</option>
                            <option value="waste">إهلاك / تلف / عجز (-)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">الكمية (<span id="unitSpan"></span>)</label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control text-white" style="background: rgba(255,255,255,0.08); border-color: var(--glass-border);" required placeholder="أدخل الكمية...">
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">حفظ التسوية</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- نافذة Modal لإضافة خامة جديدة سريعة --}}
<div class="modal fade" id="quickAddModal" tabindex="-1" aria-hidden="true" dir="rtl">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-white" style="background: #111522; border: 1px solid var(--glass-border); border-radius: 20px;">
            <div class="modal-header border-secondary border-opacity-25">
                <div>
                    <h5 class="modal-title fw-bold">✨ إضافة خامة جديدة سريعة للمخزن</h5>
                    <small class="text-muted">أدخل تفاصيل المادة الخام ورصيدها وسعر تكلفتها</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('inventory.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">اسم الخامة <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control text-white" style="background: rgba(255,255,255,0.08); border-color: var(--glass-border);" placeholder="مثال: حبوب بن إسبريسو، حليب كامل الدسم، سيرب فانيليا..." required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small">التصنيف</label>
                            <input type="text" name="category" class="form-control text-white" style="background: rgba(255,255,255,0.08); border-color: var(--glass-border);" placeholder="مثال: قهوة، ألبان، سيرب">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">وحدة القياس <span class="text-danger">*</span></label>
                            <select name="unit" class="form-select text-white" style="background: rgba(255,255,255,0.08); border-color: var(--glass-border);" required>
                                <option value="جرام">جرام</option>
                                <option value="مل">مل</option>
                                <option value="قطعة">قطعة</option>
                                <option value="كجم">كجم</option>
                                <option value="لتر">لتر</option>
                                <option value="علبة">علبة</option>
                                <option value="باكت">باكت</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small">سعر الوحدة (ج.م) <span class="text-danger">*</span></label>
                            <input type="number" step="0.0001" min="0" name="unit_price" class="form-control text-white" style="background: rgba(255,255,255,0.08); border-color: var(--glass-border);" placeholder="0.00" required>
                            <small class="text-muted" style="font-size:0.75rem;">سعر الجرام أو المل أو القطعة</small>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">الرصيد الافتتاحي <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="quantity" class="form-control text-white" style="background: rgba(255,255,255,0.08); border-color: var(--glass-border);" value="0" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">حد النواقص (تنبيه عند انخفاض الرصيد) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="reorder_level" class="form-control text-white" style="background: rgba(255,255,255,0.08); border-color: var(--glass-border);" value="10" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); border:none; color:#000;">
                        💾 حفظ الخامة في المخزن
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAdjustModal(id, name, unit, currentQty) {
        document.getElementById('adjustModalTitle').innerText = 'تسوية رصيد خامة: ' + name;
        document.getElementById('currentStockDisplay').innerText = currentQty + ' ' + unit;
        document.getElementById('unitSpan').innerText = unit;
        document.getElementById('adjustForm').action = '/inventory/' + id + '/adjust';
        
        var myModal = new bootstrap.Modal(document.getElementById('adjustModal'));
        myModal.show();
    }
</script>
@endpush
