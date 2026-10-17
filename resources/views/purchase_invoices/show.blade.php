@extends('layouts.appp')

@section('page_title', 'فاتورة شراء #' . $purchaseInvoice->invoice_number)

@section('styles')
<style>
    .invoice-view-container {
        max-width: 900px;
        margin: 20px auto 40px auto;
    }
    .glass-invoice-paper {
        background: var(--card-bg);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        padding: 40px;
        backdrop-filter: blur(20px);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
    }
    .invoice-header-box {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 2px dashed rgba(255, 255, 255, 0.1);
        padding-bottom: 25px;
        margin-bottom: 25px;
    }
    .info-block p {
        margin: 0;
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    .info-block strong {
        font-size: 1rem;
        color: #fff;
    }
    .table-invoice {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }
    .table-invoice th {
        background: rgba(255, 255, 255, 0.04);
        padding: 12px 14px;
        font-size: 0.85rem;
        color: var(--text-muted);
        text-align: right;
        border-bottom: 1px solid var(--glass-border);
    }
    .table-invoice td {
        padding: 12px 14px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        font-size: 0.9rem;
        color: var(--text-main);
    }
    @media print {
        body {
            background: #fff !important;
            color: #000 !important;
        }
        .top-bar, .navbar-custom, .glass-navbar, .no-print {
            display: none !important;
        }
        .glass-invoice-paper {
            background: #fff !important;
            color: #000 !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
        .table-invoice th, .table-invoice td {
            color: #000 !important;
            border-color: #ddd !important;
        }
    }
</style>
@endsection

@section('content')
<div class="invoice-view-container">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="{{ route('purchase-invoices.index') }}" class="btn btn-outline-light rounded-pill px-3 py-1 small">
            ← العودة لفواتير الشراء
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-info rounded-pill px-4 fw-bold">
                🖨️ طباعة الفاتورة
            </button>
            <a href="{{ route('purchase-invoices.edit', $purchaseInvoice->id) }}" class="btn btn-outline-warning rounded-pill px-4 fw-bold">
                ✏️ تعديل
            </a>
        </div>
    </div>

    <div class="glass-invoice-paper">
        <div class="invoice-header-box">
            <div>
                <span class="badge bg-primary bg-opacity-25 text-info px-3 py-1 rounded-pill small mb-2 d-inline-block">
                    فاتورة توريد وشراء مخزني
                </span>
                <h2 class="fw-bold text-white mb-1">فاتورة رقم: {{ $purchaseInvoice->invoice_number }}</h2>
                <p class="text-muted small mb-0">المورد: <strong class="text-white">{{ $purchaseInvoice->supplier_name }}</strong></p>
            </div>
            <div class="text-start">
                <p class="text-muted small mb-1">التاريخ: <strong class="text-white">{{ $purchaseInvoice->invoice_date->format('Y-m-d') }}</strong></p>
                <p class="text-muted small mb-1">المحرر: <strong class="text-white">{{ $purchaseInvoice->user->name ?? 'النظام' }}</strong></p>
                @php $statusInfo = $purchaseInvoice->payment_status_info; @endphp
                <span class="badge {{ $statusInfo['badge'] }} px-3 py-1 rounded-pill small mt-1">
                    {{ $statusInfo['label'] }}
                </span>
            </div>
        </div>

        {{-- تفاصيل الأصناف --}}
        <div class="table-responsive">
            <table class="table-invoice">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الصنف / المادة الخام</th>
                        <th>الوحدة</th>
                        <th>الكمية المستلمة</th>
                        <th>سعر الوحدة</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseInvoice->items as $idx => $item)
                        <tr>
                            <td class="text-muted small">{{ $idx + 1 }}</td>
                            <td class="fw-bold text-white">{{ $item->item_name }}</td>
                            <td class="text-muted small">{{ $item->unit }}</td>
                            <td class="fw-bold" style="color: var(--primary);">{{ floatval($item->quantity) }}</td>
                            <td>{{ number_format($item->unit_price, 2) }} ج</td>
                            <td class="fw-bold text-white">{{ number_format($item->subtotal, 2) }} ج</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- الملخص المالي --}}
        <div class="row g-3 justify-content-end mt-3 pt-3 border-top border-secondary border-opacity-25">
            <div class="col-md-5">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">المجموع الفرعي:</span>
                    <strong class="text-white">{{ number_format($purchaseInvoice->total_amount, 2) }} ج</strong>
                </div>
                @if($purchaseInvoice->discount > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">الخصم:</span>
                        <strong class="text-warning">-{{ number_format($purchaseInvoice->discount, 2) }} ج</strong>
                    </div>
                @endif
                @if($purchaseInvoice->tax > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">الضريبة:</span>
                        <strong class="text-info">+{{ number_format($purchaseInvoice->tax, 2) }} ج</strong>
                    </div>
                @endif
                <hr class="border-secondary border-opacity-25 my-2">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold text-white">الصافي الكلي:</span>
                    <strong class="fs-5 text-success">{{ number_format($purchaseInvoice->net_amount, 2) }} ج</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">المدفوع:</span>
                    <strong class="text-info">{{ number_format($purchaseInvoice->paid_amount, 2) }} ج</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">المتبقي:</span>
                    <strong class="{{ $purchaseInvoice->remaining_amount > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                        {{ number_format($purchaseInvoice->remaining_amount, 2) }} ج
                    </strong>
                </div>
            </div>
        </div>

        @if($purchaseInvoice->notes)
            <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
                <p class="text-muted small mb-1 fw-bold">ملاحظات:</p>
                <p class="text-white small mb-0">{{ $purchaseInvoice->notes }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
