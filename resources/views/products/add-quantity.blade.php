@extends('layouts.app')

@section('page_title', 'إضافة كمية لمنتج')

@section('content')
<div class="container py-5">
    <div class="card shadow-lg border-0 rounded-4 animate__animated animate__fadeInUp">
        <div class="card-header bg-success text-white text-center fw-bold fs-4 rounded-top-4">
            📦 إضافة كمية لمنتج موجود
        </div>
        <div class="card-body p-4">
            <div class="mb-4">
                <input type="text" id="searchProduct" class="form-control form-control-lg" placeholder="🔍 ابحث عن المنتج بالاسم أو المعرف">
                <ul id="searchResults" class="list-group mt-2"></ul>
            </div>

            <form id="addQuantityForm" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="product_id" id="product_id">

                <div class="mb-3">
                    <label class="form-label">الكمية المراد إضافتها</label>
                    <input type="number" name="quantity" class="form-control form-control-lg" required>
                </div>

                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-success btn-lg px-5 fw-bold">
                        ✅ إضافة الكمية
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-dark btn-lg px-5 ms-3">
                        ⬅️ رجوع
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
