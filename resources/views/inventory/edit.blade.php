@extends('layouts.appp')

@section('page_title', 'تعديل مادة خام')

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
                <h3 class="fw-bold mb-1" style="color: #fff;">تعديل مادة خام: {{ $inventoryItem->name }}</h3>
                <p class="text-muted small mb-0">أي تغيير في كمية الرصيد سيتم تسجيله تلقائياً كحركة تسوية في دفتر الحركات.</p>
            </div>
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-light rounded-pill px-3 py-1 small">
                ← رجوع للمخزن
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

        <form action="{{ route('inventory.update', $inventoryItem->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">اسم المادة الخام <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $inventoryItem->name) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">كود الصنف</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code', $inventoryItem->code) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">التصنيف</label>
                    <input type="text" name="category" list="categoriesList" class="form-control" value="{{ old('category', $inventoryItem->category) }}">
                    <datalist id="categoriesList">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                </div>

                <div class="col-md-6">
                    <label class="form-label">وحدة القياس <span class="text-danger">*</span></label>
                    <select name="unit" class="form-select" required>
                        @foreach($units as $u)
                            <option value="{{ $u }}" {{ old('unit', $inventoryItem->unit) === $u ? 'selected' : '' }}>{{ $u }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">الكمية المتوفرة بالمخزن <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="quantity" class="form-control" value="{{ old('quantity', $inventoryItem->quantity) }}" required>
                    <small class="text-muted" style="font-size:0.75rem;">الرصيد السابق المسجل: {{ $inventoryItem->quantity }} {{ $inventoryItem->unit }}</small>
                </div>

                <div class="col-md-4">
                    <label class="form-label">سعر تكلفة الوحدة (ج.م) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="unit_price" class="form-control" value="{{ old('unit_price', $inventoryItem->unit_price) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">حد النواقص (Reorder Level)</label>
                    <input type="number" step="0.01" min="0" name="reorder_level" class="form-control" value="{{ old('reorder_level', $inventoryItem->reorder_level) }}">
                </div>
            </div>

            <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-end gap-2">
                <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary rounded-pill px-4">إلغاء</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); border:none; color:#000;">
                    💾 حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
