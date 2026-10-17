@extends('layouts.appp')

@section('page_title', 'معيار الوصفات وتكاليف المنتجات')

@section('styles')
<style>
    .recipes-container {
        padding: 10px 0 40px 0;
    }
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .product-recipe-card {
        background: var(--card-bg);
        border: 1px solid var(--glass-border);
        border-radius: 18px;
        padding: 22px;
        backdrop-filter: blur(15px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        cursor: pointer;
        transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .product-recipe-card:hover {
        transform: translateY(-4px);
        border-color: var(--primary);
        box-shadow: 0 12px 40px rgba(0, 242, 254, 0.2);
    }
    .recipe-row {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 10px;
        padding: 8px 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }
    .cost-box {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.25);
        border-radius: 12px;
        padding: 10px;
        text-align: center;
    }
    .price-box {
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.25);
        border-radius: 12px;
        padding: 10px;
        text-align: center;
    }
    .margin-badge {
        padding: 4px 10px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.8rem;
    }
    .filter-pill {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid var(--glass-border);
        color: var(--text-muted);
        border-radius: 20px;
        padding: 6px 16px;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .filter-pill:hover, .filter-pill.active {
        background: var(--primary);
        color: #000;
        font-weight: 700;
        border-color: var(--primary);
        box-shadow: 0 0 12px rgba(0, 242, 254, 0.3);
    }
</style>
@endsection

@section('content')
<div class="recipes-container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #fff;">معيار الوصفات وتكاليف المنتجات (Recipes)</h2>
            <p class="text-muted small mb-0">اضغط على أي منتج لإضافة أو تعديل مكونات الوصفة الخام وحساب التكلفة التلقائية وهامش الربح.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-info rounded-pill px-3 py-2 small">
                📦 مخزن الخامات
            </a>
            <a href="{{ route('products.index') }}" class="btn btn-outline-light rounded-pill px-3 py-2 small">
                🍔 قائمة المنتجات
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success bg-opacity-25 text-white border-0 rounded-4 mb-4">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- شريط البحث والفلترة السريعة --}}
    <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mb-3 p-3 rounded-4" style="background: var(--card-bg); border: 1px solid var(--glass-border);">
        <div style="flex: 1; min-width: 260px;">
            <input type="text" id="recipeSearchInput" class="form-control text-white" placeholder="🔍 ابحث عن منتج أو قسم..." style="background: rgba(255,255,255,0.06); border-color: var(--glass-border); border-radius: 12px;" oninput="filterProductCards()">
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="filter-pill active" onclick="setFilter('all', this)">الكل ({{ $products->count() }})</button>
            <button type="button" class="filter-pill" onclick="setFilter('with-recipe', this)">لها وصفة ({{ $products->filter(fn($p) => $p->recipes->count() > 0)->count() }})</button>
            <button type="button" class="filter-pill" onclick="setFilter('without-recipe', this)">بدون وصفة ({{ $products->filter(fn($p) => $p->recipes->count() === 0)->count() }})</button>
        </div>
    </div>

    {{-- شبكة المنتجات --}}
    <div class="products-grid" id="productsGrid">
        @forelse($products as $product)
            <div class="product-recipe-card" 
                 data-name="{{ mb_strtolower($product->name) }}"
                 data-category="{{ mb_strtolower($product->category->name ?? '') }}"
                 data-has-recipe="{{ $product->recipes->count() > 0 ? 'true' : 'false' }}"
                 onclick="openRecipeModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ (float)$product->price }}, {{ json_encode($product->recipes) }})">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="fw-bold mb-1 text-white">{{ $product->name }}</h4>
                            <span class="badge bg-secondary bg-opacity-25 text-light px-2 py-1 rounded-pill small">
                                {{ $product->category->name ?? 'بدون قسم' }}
                            </span>
                        </div>
                        <div>
                            @if($product->recipes->count() > 0)
                                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-2 py-1 rounded-pill small">
                                    ✓ وصفة مسجلة ({{ $product->recipes->count() }})
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 px-2 py-1 rounded-pill small">
                                    + بدون وصفة
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- عرض عينة من المكونات --}}
                    <div class="mb-3">
                        @if($product->recipes->count() > 0)
                            <p class="text-muted small mb-2 fw-bold">المكونات الخام:</p>
                            @foreach($product->recipes->take(3) as $r)
                                <div class="recipe-row">
                                    <span class="small text-light">{{ $r->inventoryItem->name ?? 'خامة محذوفة' }}</span>
                                    <span class="badge bg-info bg-opacity-25 text-info small" dir="ltr">
                                        {{ floatval($r->quantity_used) }} {{ $r->inventoryItem->unit ?? '' }}
                                    </span>
                                </div>
                            @endforeach
                            @if($product->recipes->count() > 3)
                                <p class="text-center text-muted small mb-0">+ {{ $product->recipes->count() - 3 }} مكونات إضافية...</p>
                            @endif
                        @else
                            <div class="text-center py-4 border border-secondary border-dashed border-opacity-25 rounded-3">
                                <span class="fs-4 d-block mb-1">🧪</span>
                                <span class="text-muted small">اضغط هنا لإضافة مكونات الوصفة</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- الصناديق المالية (سعر البيع، التكلفة، الهامش) --}}
                <div class="pt-3 border-top border-secondary border-opacity-25">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <div class="price-box">
                                <small class="text-muted d-block" style="font-size:0.75rem;">سعر البيع</small>
                                <strong class="text-success">{{ number_format($product->price, 2) }} ج</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="cost-box">
                                <small class="text-muted d-block" style="font-size:0.75rem;">تكلفة الوصفة</small>
                                <strong class="text-danger">{{ number_format($product->recipe_cost, 2) }} ج</strong>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">هامش الربح:</span>
                        <span class="margin-badge {{ $product->profit_margin >= 40 ? 'bg-success bg-opacity-25 text-success' : ($product->profit_margin > 0 ? 'bg-warning bg-opacity-25 text-warning' : 'bg-danger bg-opacity-25 text-danger') }}">
                            {{ $product->profit_margin }}%
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                لا توجد منتجات مسجلة حتى الآن.
            </div>
        @endforelse
    </div>
</div>

{{-- نافذة Modal إعداد الوصفة --}}
<div class="modal fade" id="recipeModal" tabindex="-1" aria-hidden="true" dir="rtl">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content text-white" style="background: #111522; border: 1px solid var(--glass-border); border-radius: 20px;">
            <div class="modal-header border-secondary border-opacity-25">
                <div>
                    <h5 class="modal-title fw-bold" id="modalProductName">وصفة المنتج</h5>
                    <small class="text-muted">حدد المواد الخام والكمية المستهلكة بدقة لكل قطعة مباعة</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="recipeForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    {{-- شريط مالي حي للمودال --}}
                    <div class="row g-2 mb-3 p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border);">
                        <div class="col-4 text-center border-end border-secondary border-opacity-25">
                            <small class="text-muted d-block">سعر البيع</small>
                            <span id="modalSellingPrice" class="fw-bold text-success fs-6">0.00 ج</span>
                        </div>
                        <div class="col-4 text-center border-end border-secondary border-opacity-25">
                            <small class="text-muted d-block">تكلفة الخامات</small>
                            <span id="liveTotalCost" class="fw-bold text-danger fs-6">0.00 ج</span>
                        </div>
                        <div class="col-4 text-center">
                            <small class="text-muted d-block">هامش الربح المتوقع</small>
                            <span id="modalProfitMargin" class="fw-bold text-info fs-6">0%</span>
                        </div>
                    </div>

                    <div id="recipeRowsContainer">
                        {{-- تملأ ديناميكياً بالجافاسكريبت --}}
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary border-opacity-25">
                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="addRecipeRow()">
                            + إضافة مادة خام أخرى للوصفة
                        </button>
                        <a href="{{ route('inventory.index') }}" target="_blank" class="text-muted small text-decoration-none">
                            + إضافة خامة جديدة للمخزن ↗
                        </a>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); border:none; color:#000;">
                        💾 حفظ الوصفة وتحديث التكلفة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const allInventories = @json($inventories);
    let currentProductPrice = 0;
    let currentFilter = 'all';

    function setFilter(type, btn) {
        currentFilter = type;
        document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        filterProductCards();
    }

    function filterProductCards() {
        const query = (document.getElementById('recipeSearchInput').value || '').trim().toLowerCase();
        const cards = document.querySelectorAll('.product-recipe-card');

        cards.forEach(card => {
            const name = card.dataset.name || '';
            const category = card.dataset.category || '';
            const hasRecipe = card.dataset.hasRecipe === 'true';

            const matchesSearch = !query || name.includes(query) || category.includes(query);
            let matchesFilter = true;
            if (currentFilter === 'with-recipe') matchesFilter = hasRecipe;
            if (currentFilter === 'without-recipe') matchesFilter = !hasRecipe;

            card.style.display = (matchesSearch && matchesFilter) ? 'flex' : 'none';
        });
    }

    function calculateLiveCost() {
        let total = 0;
        const rows = document.querySelectorAll('.recipe-row-item');
        rows.forEach(row => {
            const select = row.querySelector('.inventory-select');
            const qtyInput = row.querySelector('.qty-input');
            const unitPrice = parseFloat(select.selectedOptions[0]?.dataset?.price || 0);
            const qty = parseFloat(qtyInput.value || 0);
            total += unitPrice * qty;
        });

        document.getElementById('liveTotalCost').innerText = total.toFixed(2) + ' ج';

        if (currentProductPrice > 0) {
            const profit = currentProductPrice - total;
            const margin = Math.round((profit / currentProductPrice) * 100);
            const marginEl = document.getElementById('modalProfitMargin');
            marginEl.innerText = margin + '% (' + profit.toFixed(2) + ' ج)';
            if (margin >= 40) {
                marginEl.className = 'fw-bold text-success fs-6';
            } else if (margin > 0) {
                marginEl.className = 'fw-bold text-warning fs-6';
            } else {
                marginEl.className = 'fw-bold text-danger fs-6';
            }
        }
    }

    function createRowHtml(invId = '', qty = '') {
        let optionsHtml = '<option value="">-- اختر المادة الخام --</option>';
        allInventories.forEach(inv => {
            const isSelected = inv.id == invId ? 'selected' : '';
            optionsHtml += `<option value="${inv.id}" data-unit="${inv.unit}" data-price="${inv.unit_price}" ${isSelected}>
                ${inv.name} (${inv.unit_price} ج/${inv.unit}) [المتوفر: ${parseFloat(inv.quantity)} ${inv.unit}]
            </option>`;
        });

        const selectedInv = allInventories.find(i => i.id == invId);
        const unitName = selectedInv ? selectedInv.unit : 'الوحدة';

        return `
            <div class="recipe-row-item row g-2 align-items-center mb-2">
                <div class="col-md-6">
                    <select name="inventory_ids[]" class="form-select inventory-select text-white" style="background: rgba(255,255,255,0.08); border-color: var(--glass-border);" onchange="handleSelectChange(this)" required>
                        ${optionsHtml}
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="number" step="0.001" min="0.001" name="quantities[]" value="${qty}" class="form-control qty-input text-white" style="background: rgba(255,255,255,0.08); border-color: var(--glass-border);" placeholder="الكمية المستخدمة" oninput="calculateLiveCost()" required>
                        <span class="input-group-text unit-badge text-muted" style="background: rgba(255,255,255,0.04); border-color: var(--glass-border);">${unitName}</span>
                    </div>
                </div>
                <div class="col-md-2 text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill w-100" onclick="removeRecipeRow(this)">
                        🗑️ إزالة
                    </button>
                </div>
            </div>
        `;
    }

    function handleSelectChange(selectElem) {
        const row = selectElem.closest('.recipe-row-item');
        const unitBadge = row.querySelector('.unit-badge');
        const opt = selectElem.selectedOptions[0];
        unitBadge.innerText = opt ? (opt.dataset.unit || 'الوحدة') : 'الوحدة';
        calculateLiveCost();
    }

    function addRecipeRow(invId = '', qty = '') {
        const container = document.getElementById('recipeRowsContainer');
        container.insertAdjacentHTML('beforeend', createRowHtml(invId, qty));
        calculateLiveCost();
    }

    function removeRecipeRow(btn) {
        btn.closest('.recipe-row-item').remove();
        calculateLiveCost();
    }

    function openRecipeModal(productId, productName, productPrice, existingRecipes) {
        currentProductPrice = parseFloat(productPrice || 0);
        document.getElementById('modalProductName').innerText = 'وصفة: ' + productName;
        document.getElementById('modalSellingPrice').innerText = currentProductPrice.toFixed(2) + ' ج';
        document.getElementById('recipeForm').action = '/recipes/' + productId;

        const container = document.getElementById('recipeRowsContainer');
        container.innerHTML = '';

        if (existingRecipes && existingRecipes.length > 0) {
            existingRecipes.forEach(r => {
                addRecipeRow(r.inventory_item_id, r.quantity_used);
            });
        } else {
            addRecipeRow();
        }

        calculateLiveCost();
        const myModal = new bootstrap.Modal(document.getElementById('recipeModal'));
        myModal.show();
    }
</script>
@endpush
