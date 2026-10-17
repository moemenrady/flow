@extends('layouts.appp')

@section('page_title', 'إضافة وصفة جديدة')

@section('styles')
<style>
    .form-container {
        max-width: 800px;
        margin: 20px auto 40px auto;
    }
    .glass-form-card {
        background: var(--card-bg);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        padding: 35px;
        backdrop-filter: blur(15px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    }
    .form-label {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 8px;
    }
    .form-control, .form-select {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--glass-border);
        color: #fff;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 0.95rem;
    }
    .form-control:focus, .form-select:focus {
        background: rgba(255, 255, 255, 0.08);
        border-color: var(--primary);
        color: #fff;
        box-shadow: 0 0 15px rgba(0, 242, 254, 0.2);
    }
</style>
@endsection

@section('content')
<div class="form-container">
    <div class="glass-form-card">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
            <div>
                <h3 class="fw-bold mb-1" style="color: #fff;">إضافة معيار وصفة لمنتج</h3>
                <p class="text-muted small mb-0">اختر المنتج والمواد الخام لحساب تكلفته وخصمها التلقائي عند البيع.</p>
            </div>
            <a href="{{ route('recipes.index') }}" class="btn btn-outline-light rounded-pill px-3 py-1 small">
                ← قائمة الوصفات
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger bg-danger bg-opacity-25 text-white border-0 rounded-4 mb-4">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('recipes.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="form-label">المنتج المراد تعيين الوصفة له <span class="text-danger">*</span></label>
                <select name="product_id" class="form-select" required>
                    <option value="">-- اختر المنتج من القائمة --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} ({{ number_format($product->price, 2) }} ج)</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">المكونات الخام</label>
                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="addRow()">
                        + إضافة خامة
                    </button>
                </div>

                <div id="ingredientsContainer">
                    {{-- صفوف المكونات تضاف هنا --}}
                </div>
            </div>

            <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-end gap-2">
                <a href="{{ route('recipes.index') }}" class="btn btn-outline-secondary rounded-pill px-4">إلغاء</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); border:none; color:#000;">
                    💾 حفظ الوصفة
                </button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
    const inventories = @json($inventories);

    function addRow() {
        const container = document.getElementById('ingredientsContainer');
        let options = '<option value="">-- اختر الخامة --</option>';
        inventories.forEach(i => {
            options += `<option value="${i.id}">${i.name} (${i.unit})</option>`;
        });

        const html = `
            <div class="row g-2 align-items-center mb-2 ingredient-row">
                <div class="col-7">
                    <select name="inventory_ids[]" class="form-select" required>
                        ${options}
                    </select>
                </div>
                <div class="col-3">
                    <input type="number" step="0.001" min="0.001" name="quantities[]" class="form-control" placeholder="الكمية" required>
                </div>
                <div class="col-2 text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill w-100" onclick="this.closest('.ingredient-row').remove()">✕</button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    document.addEventListener('DOMContentLoaded', () => {
        addRow();
    });
</script>
@endsection
@endsection
