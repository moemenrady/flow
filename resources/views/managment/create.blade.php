@extends('layouts.app_admin')
@section('content')
    <!-- Main content -->
    <main class="container py-5">

        <div class="row g-4 justify-content-center">

            <div class="col-12">
                <form action="{{ route('analytics.money') }}" method="GET">
                    <button type="submit" class="custom-card">
                        التحليل
                    </button>
                </form>
            </div>

            <div class="col-12">
                <form action="{{ route('managment-system-edit.create') }}" method="GET">
                    <button type="submit" class="custom-card">
                      تعديلات في النظام
                    </button>
                </form>
            </div>
            <div class="col-12">
                <form action="{{ route('system-actions.index') }}" method="GET">
                    <button type="submit" class="custom-card">
                      برج المراقبه
                    </button>
                </form>
            <div class="col-12">
                <form action="{{ route('inventory.index') }}" method="GET">
                    <button type="submit" class="custom-card">
                        📦 مخزن المواد الخام والتوريد
                    </button>
                </form>
            </div>
            <div class="col-12">
                <form action="{{ route('recipes.index') }}" method="GET">
                    <button type="submit" class="custom-card">
                        🧪 معيار الوصفات وتكاليف المنتجات
                    </button>
                </form>
            </div>
            <div class="col-12">
                <form action="{{ route('purchase-invoices.index') }}" method="GET">
                    <button type="submit" class="custom-card">
                        🛒 فواتير الشراء والموردين
                    </button>
                </form>
            </div>
        </div>

    </main>
@endsection
