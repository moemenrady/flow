@extends('layouts.appp')

@section('page_title', 'تعديل فاتورة شراء #' . $purchaseInvoice->invoice_number)

@section('styles')
<style>
    .invoice-builder-container {
        max-width: 1100px;
        margin: 15px auto 40px auto;
    }
    .glass-builder-card {
        background: var(--card-bg);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        padding: 30px;
        backdrop-filter: blur(15px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    }
    .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 6px;
    }
    .form-control, .form-select {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--glass-border);
        color: #fff;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 0.9rem;
    }
    .form-control:focus, .form-select:focus {
        background: rgba(255, 255, 255, 0.08);
        border-color: var(--primary);
        color: #fff;
        box-shadow: 0 0 15px rgba(0, 242, 254, 0.2);
    }
    .item-row {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 10px;
    }
    .summary-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--glass-border);
        border-radius: 14px;
        padding: 16px;
    }
</style>
@endsection

@section('content')
<div class="invoice-builder-container">
    <div class="glass-builder-card">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
            <div>
                <h3 class="fw-bold mb-1" style="color: #fff;">تعديل فاتورة الشراء رقم: {{ $purchaseInvoice->invoice_number }}</h3>
                <p class="text-muted small mb-0">تعديل الفاتورة سيقوم بإعادة حساب كميات المخزن تلقائياً وتسجيل حركات التوريد الصحيحة.</p>
            </div>
            <a href="{{ route('purchase-invoices.show', $purchaseInvoice->id) }}" class="btn btn-outline-light rounded-pill px-3 py-1 small">
                ← تفاصيل الفاتورة
            </a>
        </div>

        @if (isset($errors) && $errors->any())
            <div class="alert alert-danger bg-danger bg-opacity-25 text-white border-0 rounded-4 mb-4">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('purchase-invoices.update', $purchaseInvoice->id) }}" method="POST" id="purchaseInvoiceForm">
            @csrf
            @method('PUT')

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">رقم الفاتورة</label>
                    <input type="text" class="form-control text-muted" value="{{ $purchaseInvoice->invoice_number }}" readonly>
                </div>

                <div class="col-md-3">
                    <label class="form-label">اسم المورد <span class="text-danger">*</span></label>
                    <input type="text" name="supplier_name" class="form-control" value="{{ old('supplier_name', $purchaseInvoice->supplier_name) }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">تاريخ الفاتورة <span class="text-danger">*</span></label>
                    <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', $purchaseInvoice->invoice_date->format('Y-m-d')) }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">طريقة الدفع</label>
                    <select name="payment_method" class="form-select">
                        <option value="cash" {{ $purchaseInvoice->payment_method === 'cash' ? 'selected' : '' }}>كاش نقدي</option>
                        <option value="instapay" {{ $purchaseInvoice->payment_method === 'instapay' ? 'selected' : '' }}>إنستا باي</option>
                        <option value="card" {{ in_array($purchaseInvoice->payment_method, ['card', 'visa']) ? 'selected' : '' }}>فيزا / بطاقة</option>
                        <option value="credit" {{ $purchaseInvoice->payment_method === 'credit' ? 'selected' : '' }}>آجل (حساب مورد)</option>
                        <option value="bank_transfer" {{ $purchaseInvoice->payment_method === 'bank_transfer' ? 'selected' : '' }}>تحويل بنكي</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-bold text-white mb-0">بنود وأصناف الفاتورة</h5>
                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="addRow()">
                        + إضافة صنف آخر
                    </button>
                </div>

                <div id="itemsContainer">
                    {{-- تملأ ديناميكياً --}}
                </div>
            </div>

            <div class="row g-3 justify-content-end mb-4">
                <div class="col-md-6 col-lg-5">
                    <div class="summary-card">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">إجمالي الأصناف:</span>
                            <strong id="displaySubtotal" class="text-white">0.00 ج</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">الخصم (إن وجد):</span>
                            <input type="number" step="0.01" min="0" name="discount" id="inputDiscount" class="form-control text-end py-1 px-2" style="width: 120px;" value="{{ old('discount', $purchaseInvoice->discount) }}" oninput="calculateTotals()">
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">الضريبة / الرسوم:</span>
                            <input type="number" step="0.01" min="0" name="tax" id="inputTax" class="form-control text-end py-1 px-2" style="width: 120px;" value="{{ old('tax', $purchaseInvoice->tax) }}" oninput="calculateTotals()">
                        </div>
                        <hr class="border-secondary border-opacity-25 my-2">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-white">الصافي الإجمالي:</span>
                            <strong id="displayNetAmount" class="fs-5 text-success">0.00 ج</strong>
                        </div>

                        <div class="mt-3 pt-2 border-top border-secondary border-opacity-25">
                            <div class="mb-2">
                                <label class="form-label">حالة السداد</label>
                                <select name="payment_status" id="paymentStatusSelect" class="form-select py-1" onchange="handlePaymentStatusChange()">
                                    <option value="paid" {{ $purchaseInvoice->payment_status === 'paid' ? 'selected' : '' }}>مدفوعة بالكامل</option>
                                    <option value="partial" {{ $purchaseInvoice->payment_status === 'partial' ? 'selected' : '' }}>مدفوعة جزئياً</option>
                                    <option value="unpaid" {{ $purchaseInvoice->payment_status === 'unpaid' ? 'selected' : '' }}>آجلة (غير مدفوعة)</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2" id="paidAmountRow" style="display:none;">
                                <span class="text-muted small">المبلغ المدفوع:</span>
                                <input type="number" step="0.01" min="0" name="paid_amount" id="inputPaidAmount" class="form-control text-end py-1 px-2" style="width: 120px;" value="{{ old('paid_amount', $purchaseInvoice->paid_amount) }}" oninput="calculateTotals()">
                            </div>

                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">المبلغ المتبقي:</span>
                                <strong id="displayRemainingAmount" class="text-danger">0.00 ج</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">ملاحظات الفاتورة</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $purchaseInvoice->notes) }}</textarea>
            </div>

            <div class="pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-end gap-2">
                <a href="{{ route('purchase-invoices.show', $purchaseInvoice->id) }}" class="btn btn-outline-secondary rounded-pill px-4">إلغاء</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); border:none; color:#000;">
                    💾 حفظ التعديلات وتحديث المخزن
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const inventoryList = @json($allInventoryItems);
    const defaultUnits = @json($units);
    const initialItems = @json($initialItems);

    let rowCount = 0;

    function addRow(name = '', unit = 'قطعة', qty = 1, price = 0, invId = '') {
        rowCount++;
        const container = document.getElementById('itemsContainer');

        let selectOptions = '<option value="">-- خامة جديدة أو اختر من المخزن --</option>';
        inventoryList.forEach(inv => {
            const isSelected = inv.id == invId ? 'selected' : '';
            selectOptions += `<option value="${inv.id}" data-name="${inv.name}" data-unit="${inv.unit}" data-price="${inv.unit_price}" ${isSelected}>
                ${inv.name} (${inv.unit_price} ج/${inv.unit})
            </option>`;
        });

        const rowHtml = `
            <div class="item-row" id="row_${rowCount}">
                <div class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <label class="form-label d-md-none">اختر من المخزن</label>
                        <select class="form-select select-inventory" onchange="onInventorySelected(this, ${rowCount})">
                            ${selectOptions}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label d-md-none">اسم الصنف</label>
                        <input type="text" name="items[${rowCount}][name]" class="form-control input-name" value="${name}" placeholder="اسم الصنف في المخزن" required>
                        <input type="hidden" name="items[${rowCount}][inventory_item_id]" class="input-inv-id" value="${invId}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-md-none">الوحدة</label>
                        <select name="items[${rowCount}][unit]" class="form-select input-unit">
                            ${defaultUnits.map(u => `<option value="${u}" ${u === unit ? 'selected' : ''}>${u}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-md-none">الكمية</label>
                        <input type="number" step="0.01" min="0.01" name="items[${rowCount}][quantity]" class="form-control input-qty text-center" value="${qty}" oninput="calculateTotals()" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-md-none">سعر الوحدة</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" name="items[${rowCount}][unit_price]" class="form-control input-price text-center" value="${price}" oninput="calculateTotals()" required>
                            <button type="button" class="btn btn-outline-danger" onclick="removeRow(${rowCount})">✕</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);
        calculateTotals();
    }

    function removeRow(id) {
        const row = document.getElementById('row_' + id);
        if (row) {
            row.remove();
            calculateTotals();
        }
    }

    function onInventorySelected(selectElem, id) {
        const row = document.getElementById('row_' + id);
        const selected = selectElem.selectedOptions[0];
        if (selected && selected.value) {
            row.querySelector('.input-name').value = selected.dataset.name;
            row.querySelector('.input-unit').value = selected.dataset.unit;
            row.querySelector('.input-price').value = selected.dataset.price;
            row.querySelector('.input-inv-id').value = selected.value;
        } else {
            row.querySelector('.input-inv-id').value = '';
        }
        calculateTotals();
    }

    function handlePaymentStatusChange() {
        const status = document.getElementById('paymentStatusSelect').value;
        const paidRow = document.getElementById('paidAmountRow');
        if (status === 'partial') {
            paidRow.style.display = 'flex';
        } else {
            paidRow.style.display = 'none';
        }
        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        const rows = document.querySelectorAll('.item-row');
        rows.forEach(r => {
            const qty = parseFloat(r.querySelector('.input-qty').value || 0);
            const price = parseFloat(r.querySelector('.input-price').value || 0);
            subtotal += (qty * price);
        });

        const discount = parseFloat(document.getElementById('inputDiscount').value || 0);
        const tax = parseFloat(document.getElementById('inputTax').value || 0);
        const net = Math.max(0, subtotal - discount + tax);

        document.getElementById('displaySubtotal').innerText = subtotal.toFixed(2) + ' ج';
        document.getElementById('displayNetAmount').innerText = net.toFixed(2) + ' ج';

        const paymentStatus = document.getElementById('paymentStatusSelect').value;
        let paid = 0;
        let remaining = 0;

        if (paymentStatus === 'paid') {
            paid = net;
            remaining = 0;
        } else if (paymentStatus === 'unpaid') {
            paid = 0;
            remaining = net;
        } else {
            paid = parseFloat(document.getElementById('inputPaidAmount').value || 0);
            if (paid > net) paid = net;
            remaining = Math.max(0, net - paid);
        }

        document.getElementById('displayRemainingAmount').innerText = remaining.toFixed(2) + ' ج';
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (initialItems && initialItems.length > 0) {
            initialItems.forEach(it => {
                addRow(it.name, it.unit, it.quantity, it.unit_price, it.inventory_item_id);
            });
        } else {
            addRow();
        }
        handlePaymentStatusChange();
    });
</script>
@endpush
