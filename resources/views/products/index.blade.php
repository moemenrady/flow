@extends('layouts.appp')

@section('page_title', 'إدارة المخزن')

@section('styles')
    <style>
        /* الحاوية الرئيسية */
        .inventory-container {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* قسم الإحصائيات والهيدر */
        .header-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .glass-stats-card {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            padding: 20px 40px;
            border-radius: 20px;
            text-align: center;
            min-width: 200px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }

        .glass-stats-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .glass-stats-card .label {
            display: block;
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .glass-stats-card .value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            text-shadow: 0 0 10px rgba(0, 242, 254, 0.3);
        }

        /* زر الإضافة النيوني */
        .btn-add-neon {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            color: #000;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 0 20px rgba(0, 242, 254, 0.4);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .btn-add-neon:hover {
            transform: scale(1.1) rotate(90deg);
            box-shadow: 0 0 35px rgba(0, 242, 254, 0.7);
        }

        /* صندوق البحث */
        .search-wrapper {
            position: relative;
            max-width: 600px;
            margin: 0 auto 40px;
        }

        .glass-search {
            width: 100%;
            padding: 15px 50px 15px 25px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            color: var(--text-main);
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .glass-search:focus {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 15px rgba(0, 242, 254, 0.2);
        }

        .search-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 1.2rem;
        }

        /* الجدول الزجاجي */
        .table-container {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .glass-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }

        .glass-table thead {
            background: rgba(0, 242, 254, 0.1);
        }

        .glass-table th {
            padding: 20px;
            color: var(--primary);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        .glass-table tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .glass-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .glass-table td {
            padding: 18px;
            color: var(--text-main);
            font-size: 0.95rem;
        }

        /* شارات التميز */
        .badge-qty {
            background: rgba(0, 242, 254, 0.15);
            color: var(--primary);
            padding: 5px 12px;
            border-radius: 8px;
            font-weight: bold;
        }

        /* 🗑️ زر الحذف الزجاجي الجديد داخل الجدول */
        .btn-delete-table {
            background: rgba(230, 57, 70, 0.15);
            border: 1px solid rgba(230, 57, 70, 0.3);
            color: #e63946;
            padding: 6px 14px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.2s ease;
            backdrop-filter: blur(5px);
        }

        .btn-delete-table:hover {
            background: #e63946;
            color: #fff;
            box-shadow: 0 0 15px rgba(230, 57, 70, 0.5);
            transform: scale(1.05);
        }

        /* ريسبونسيف الموبايل */
        @media (max-width: 768px) {
            .header-row {
                flex-direction: column;
                gap: 15px;
            }

            .btn-add-neon {
                order: -1;
                width: 70px;
                height: 70px;
                font-size: 30px;
            }

            .glass-table thead {
                display: none;
            }

            .glass-table tr {
                display: block;
                margin-bottom: 15px;
                padding: 10px;
                background: rgba(255, 255, 255, 0.02);
            }

            .glass-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px 15px;
                text-align: right;
                border: none;
            }

            .glass-table td::before {
                content: attr(data-label);
                color: var(--primary);
                font-weight: bold;
                font-size: 0.8rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="inventory-container">

        {{-- التنبيهات الزجاجية --}}
        @if (session('success'))
            <div class="alert alert-glass-success animate__animated animate__fadeInDown">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger animate__animated animate__fadeInDown"
                style="background: rgba(230, 57, 70, 0.2); border: 1px solid #e63946; color: #fff; border-radius: 12px; padding: 15px;">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- الإحصائيات وزر الإضافة --}}
        <div class="header-row animate__animated animate__fadeIn">
            <div class="glass-stats-card">
                <span class="label">إجمالي القطع</span>
                <span class="value">{{ $countItems }}</span>
            </div>

            <button class="btn-add-neon" data-bs-toggle="modal" data-bs-target="#chooseActionModal"
                title="إضافة صنف أو كمية">
                <i class="fas fa-plus">+</i>
            </button>

            <div class="glass-stats-card">
                <span class="label">عدد الأصناف</span>
                <span class="value">{{ $countProducts }}</span>
        </div>

        <div class="d-flex justify-content-center gap-2 mb-4 flex-wrap">
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-info rounded-pill px-4 py-2 small fw-bold">
                📦 مخزن الخامات والمكونات
            </a>
            <a href="{{ route('recipes.index') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 small fw-bold">
                🧪 معيار الوصفات وتكاليف المنتجات
            </a>
            <a href="{{ route('purchase-invoices.index') }}" class="btn btn-outline-success rounded-pill px-4 py-2 small fw-bold">
                🛒 فواتير الشراء والتوريد
            </a>
        </div>

        {{-- البحث --}}
        <div class="search-wrapper animate__animated animate__fadeIn" style="animation-delay: 0.2s;">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchBox" class="glass-search" placeholder="ابحث عن منتج بالاسم أو الكود...">
        </div>

        {{-- الجدول --}}
        <div class="table-container animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>المعرف</th>
                        <th>اسم المنتج</th>
                        <th>الصنف</th>
                        <th>السعر</th>
                        <th>التكلفة</th>
                        <th>النوع / الوصفة</th>
                        <th>الكمية</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="productTable">
                    @foreach ($products as $product)
                        <tr data-href="{{ route('products.show', $product->id) }}">
                            <td data-label="المعرف">#{{ $product->id }}</td>
                            <td data-label="اسم المنتج" class="fw-bold">{{ $product->name }}</td>
                            <td data-label="الصنف">{{ $product->category?->name ?? '---' }}</td>
                            <td data-label="السعر" class="text-success fw-bold">{{ number_format($product->price, 2) }} ج</td>
                            <td data-label="التكلفة" class="text-info">{{ number_format($product->cost, 2) }} ج</td>
                            <td data-label="الوصفة">
                                @if($product->recipes->isNotEmpty())
                                    <a href="{{ route('recipes.index') }}" class="badge rounded-pill text-decoration-none" style="background: rgba(0, 242, 254, 0.15); color: #00f2fe; border: 1px solid rgba(0, 242, 254, 0.3); font-size: 0.8rem; padding: 6px 10px;">
                                        🧪 بوصفة ({{ $product->recipes->count() }} خامات)
                                    </a>
                                @elseif($product->is_produced)
                                    <a href="{{ route('recipes.index') }}" class="badge rounded-pill text-decoration-none" style="background: rgba(234, 179, 8, 0.15); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.3); font-size: 0.8rem; padding: 6px 10px;">
                                        ⚠️ بدون مقادير (أضف ريسبي)
                                    </a>
                                @else
                                    <span class="badge rounded-pill" style="background: rgba(255, 255, 255, 0.08); color: #94a3b8; font-size: 0.8rem; padding: 6px 10px;">
                                        🥫 مباشر جاهز
                                    </span>
                                @endif
                            </td>
                            <td data-label="الكمية">
                                @if($product->recipes->isNotEmpty() || $product->is_produced)
                                    <span class="badge rounded-pill" style="background: rgba(180, 101, 218, 0.15); color: #d8b4fe; border: 1px solid rgba(180, 101, 218, 0.3);">تحضير فوري</span>
                                @else
                                    <span class="badge-qty">{{ $product->quantity }}</span>
                                @endif
                            </td>
                            <td data-label="الإجراءات">
                                <div class="d-flex gap-2 justify-content-end align-items-center">
                                    <a href="{{ route('recipes.index') }}" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 text-decoration-none" title="تعديل مكونات الوصفة">
                                        🧪 ريسبي
                                    </a>
                                    {{-- نموذج حذف المنتج المباشر --}}
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                        class="delete-form m-0"
                                        onsubmit="return confirm('⚠️ هل أنت متأكد من حذف المنتج ({{ $product->name }}) نهائياً؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete-table" title="حذف المنتج">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- المودالات --}}
    @include('products.modals.choose-action')
    @include('products.modals.add-product')
    @include('products.modals.add-quantity')
    @include('products.modals.add-new-category')

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tbody = document.getElementById('productTable');
            const searchBox = document.getElementById('searchBox');

            // النقر على الصفوف الانتقالية (تم تعديله لمنع التداخل مع زر الحذف)
            tbody.addEventListener('click', function(e) {
                // إذا تم الضغط على زر الحذف أو الفورم الخاص به لا تفعل شيئاً واترك عملية الحذف تتم
                if (e.target.closest('.delete-form') || e.target.classList.contains('btn-delete-table')) {
                    return;
                }

                const row = e.target.closest('tr');
                if (!row) return;
                const url = row.dataset.href;
                if (url) window.location.href = url;
            });

            // البحث AJAX المطور ليشمل زر الحذف التلقائي للمخرجات الجديدة
            searchBox.addEventListener('keyup', function() {
                let query = this.value;

                fetch("{{ route('products.search') }}?query=" + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        tbody.innerHTML = "";
                        if (data.length > 0) {
                            data.forEach(item => {
                                tbody.innerHTML += `
                                <tr data-href="/products/${item.id}" class="animate__animated animate__fadeIn">
                                    <td data-label="المعرف">#${item.id}</td>
                                    <td data-label="اسم المنتج" class="fw-bold">${item.name}</td>
                                    <td data-label="الصنف">${item.category ? item.category.name : '---'}</td>
                                    <td data-label="السعر" class="text-success">${item.price}</td>
                                    <td data-label="التكلفة" class="text-muted">${item.cost}</td>
                                    <td data-label="الكمية"><span class="badge-qty">${item.quantity}</span></td>
                                    <td data-label="الإجراءات">
                                        <form action="/products/${item.id}" method="POST" class="delete-form m-0" onsubmit="return confirm('⚠️ هل أنت متأكد من حذف المنتج (${item.name}) نهائياً؟');">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn-delete-table" title="حذف المنتج">🗑️ حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            `;
                            });
                        } else {
                            tbody.innerHTML =
                                `<tr><td colspan="7" class="text-center p-5 text-muted">لا توجد نتائج مطابقة</td></tr>`;
                        }
                    });
            });
        });
    </script>
    <script>
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                let action = this.dataset.action;

                let chooseModal = bootstrap.Modal.getInstance(document.getElementById('chooseActionModal'));
                chooseModal.hide();

                setTimeout(() => {
                    if (action === 'product') {
                        new bootstrap.Modal(document.getElementById('addProductModal')).show();
                    }
                    if (action === 'category') {
                        new bootstrap.Modal(document.getElementById('addCategoryModal')).show();
                    }
                    if (action === 'quantity') {
                        new bootstrap.Modal(document.getElementById('addQuantityModal')).show();
                    }
                }, 400);
            });
        });
    </script>
@endpush
