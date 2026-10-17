@extends('layouts.app_page')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

@section('title', 'عرض الفاتورة')

@section('content')
    <div class="subscription-container">
        <div class="card">
            {{-- رأس الفاتورة --}}
            <div class="card-header">
                <div>
                    <h2>فاتورة رقم: {{ $invoice->invoice_number }}</h2>
                    <span class="badge">{{ $invoice->created_at->format('d/m/Y') }}</span>
                    @if ($invoice->isRefunded())
                        <span class="badge" style="background: #dc3545; color: #fff; margin-right: 8px;">مرتجعة (Refunded)</span>
                    @else
                        <span class="badge" style="background: #28a745; color: #fff; margin-right: 8px;">مدفوعة</span>
                    @endif
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                    <button onclick="printInvoice()" class="btn-print">
                        🖨 طباعة
                    </button>

                    @if (isset($canRefund) && $canRefund['allowed'] && !$invoice->isRefunded())
                        <button type="button" onclick="openRefundModal()" class="btn-refund" style="background:#dc3545; color:#fff; border:none; padding:8px 16px; border-radius:10px; font-weight:bold; cursor:pointer;">
                            ↩️ استرجاع الفاتورة (Refund)
                        </button>
                    @endif
                </div>
            </div>

            @if ($invoice->isRefunded())
                <div class="alert alert-danger" style="background: #ffebe9; border: 1px solid #f5c2c7; color: #842029; padding: 14px 18px; border-radius: 12px; margin: 15px 0; text-align: right;">
                    <strong style="font-size: 1.05rem;">⚠️ تم استرجاع هذه الفاتورة بالكامل (Refunded)</strong>
                    <div style="margin-top: 6px; font-size: 0.9rem;">
                        <span>تاريخ الاسترجاع: {{ $invoice->refunded_at ? $invoice->refunded_at->format('Y-m-d h:i A') : '—' }}</span> &nbsp;•&nbsp;
                        <span>بواسطة: {{ $invoice->refunder->name ?? 'الإدارة' }}</span>
                    </div>
                    @if ($invoice->refund_reason)
                        <div style="margin-top: 6px; font-size: 0.9rem; background: rgba(0,0,0,0.03); padding: 8px 12px; border-radius: 8px;">
                            <strong>سبب الاسترجاع:</strong> {{ $invoice->refund_reason }}
                        </div>
                    @endif
                </div>
            @endif
            @if ($invoiceType === 'product')
                <div class="section">
                    <div class="box">
                        <p>رقم الفاتورة
                            <strong>{{ $invoice->cycle_number }}</strong>
                        </p>
                    </div>
                </div>
            @endif
            {{-- بيانات العميل إن لم تكن مشتريات فقط --}}
            @if (!in_array($invoiceType, ['product']))
                <div class="section">
                    <div class="box">
                        <p><strong>العميل:</strong> {{ $invoice->client->name ?? 'غير معروف' }}
                            {{ $invoice->client->id ?? '-' }}</p>
                    </div>
                </div>
            @endif
            {{-- حسب نوع الفاتورة --}}

            @if (in_array($invoiceType, ['subscription', 'mixed']))
                <div class="section">
                    <h3>فاتورة اشتراك</h3>
                    @foreach ($groupedItems['subscription'] as $item)
                        <div class="box">
                            <p>اسم الخطة: {{ $item->name }}</p>
                            <p>السعر: {{ $item->price }} ج</p>
                        </div>
                    @endforeach
                </div>
            @endif

            @if (in_array($invoiceType, ['booking', 'mixed']))
                <div class="section">
                    <h3>جلسة خاصة / حجز</h3>
                    @foreach ($groupedItems['booking'] as $item)
                        <div class="box">
                            <p>القاعة: {{ $bookingData->hall->name }} </p>
                            @if ($bookingData->real_start_at)
                                <p><strong>بداية :</strong>
                                    {{ \Carbon\Carbon::parse($bookingData->real_start_at)->format('Y-m-d h:i A') }}
                                </p>
                            @endif

                            @if ($bookingData->real_end_at)
                                <p><strong>نهاية :</strong>
                                    {{ \Carbon\Carbon::parse($bookingData->real_end_at)->format('Y-m-d h:i A') }}
                                </p>
                            @endif

                            <p>سعر الساعة: {{ $hourlyRate }} ج</p>
                            <p id="duration-text-{{ $loop->index }}">مدة الجلسة: ...</p>
                            <p>الإجمالي: {{ $item->total }} ج</p>
                        </div>

                        <script>
                            (function() {
                                const minutes = {{ $actualDurationMinutes ?? 0 }};

                                function formatDurationArabic(minutes) {
                                    if (minutes < 60) {
                                        return `${minutes} دقيقة`;
                                    }
                                    const hours = Math.floor(minutes / 60);
                                    const remainingMinutes = minutes % 60;
                                    let hourText = '';
                                    if (hours === 1) hourText = 'ساعة';
                                    else if (hours === 2) hourText = 'ساعتين';
                                    else hourText = `${hours} ساعات`;

                                    let minuteText = '';
                                    if (remainingMinutes === 15) {
                                        minuteText = 'وربع';
                                    } else if (remainingMinutes === 30) {
                                        minuteText = 'ونصف';
                                    } else if (remainingMinutes === 45) {
                                        minuteText = 'إلا ربع';
                                        // نزيد ساعة لأن "إلا ربع" يعني قبل الساعة التالية بربع
                                        if (hours === 1) hourText = 'ساعتين إلا ربع';
                                        else hourText = `${hours + 1} ساعات إلا ربع`;
                                        return hourText;
                                    } else if (remainingMinutes > 0) {
                                        minuteText = `و${remainingMinutes} دقيقة`;
                                    }
                                    return `${hourText} ${minuteText}`.trim();
                                }

                                const formatted = formatDurationArabic(minutes);
                                document.getElementById("duration-text-{{ $loop->index }}").textContent =
                                    `مدة الجلسة: ${formatted}`;
                            })
                            ();
                        </script>
                    @endforeach
                </div>
                @if ($groupedItems['deposit']->isNotEmpty())
                    <div class="section">
                        <h3>💰 الدفعات المقدمة</h3>

                        @foreach ($groupedItems['deposit'] as $item)
                            <div class="box deposit-box">
                                <p>المبلغ المدفوع مقدمًا: <strong>{{ number_format($item->total, 2) }} ج</strong></p>
                                @if (!empty($item->notes))
                                    <p>ملاحظات: {{ $item->notes }}</p>
                                @endif
                                <p>تاريخ الدفع: {{ $item->created_at->format('Y-m-d h:i A') }}</p>

                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- @if ($isHasPurchase == true)
                    <div class="section">
                        <h3>🛒 المشتريات</h3>
                        <div class="purchase-list">
                            <div class="purchase-header">
                                <span>المنتج</span>
                                <span>الكمية</span>
                                <span>السعر</span>
                                <span>الإجمالي</span>
                            </div>
                            @foreach ($groupedItems['product'] as $item)
                                <div class="purchase-row">
                                    <span>{{ $item->name }}</span>
                                    <span>{{ $item->qty }}</span>
                                    <span>{{ number_format($item->price, 2) }} ج</span>
                                    <span>{{ number_format($item->total, 2) }} ج</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif --}}

            @endif

            @if (in_array($invoiceType, ['session', 'mixed']))
                <div class="section">
                    <h3>جلسة</h3>
                    @if ($invoiceType === 'session' && $sessionData)
                        <div class="section">
                            <h3>🧘‍♀️ تفاصيل الجلسة</h3>
                            <div class="box">
                                <p><strong>🕒 وقت البداية:</strong>
                                    {{ $sessionData->start_time ? \Carbon\Carbon::parse($sessionData->start_time)->format('Y-m-d h:i A') : '-' }}
                                </p>
                                <p><strong>🏁 وقت النهاية:</strong>
                                    {{ $sessionData->end_time ? \Carbon\Carbon::parse($sessionData->end_time)->format('Y-m-d h:i A') : '-' }}
                                </p>
                                <p><strong>👥 عدد الأفراد:</strong> {{ $sessionData->persons ?? '-' }}</p>

                            </div>
                        </div>
                    @endif

                    @foreach ($groupedItems['session'] as $item)
                        <div class="box">
                            <p>سعر الساعة: {{ $item->price }} ج</p>
                            <p>عدد الساعات: {{ $item->qty }}</p>
                            <p>الإجمالي: {{ $item->total }} ج</p>
                        </div>
                    @endforeach



            @endif
            {{-- 🛒 قسم المشتريات الموحد --}}
            @if ($purchaseItems->isNotEmpty())
                <div class="section">
                    <h3>🛒 المشتريات</h3>
                    <div class="purchase-list">
                        <div class="purchase-header">
                            <span>المنتج</span>
                            <span>الكمية</span>
                            <span>السعر</span>
                            <span>الإجمالي</span>
                        </div>
                        @foreach ($purchaseItems as $item)
                            <div class="purchase-row">
                                <span>{{ $item->name }}</span>
                                <span>{{ $item->qty }}</span>
                                <span>{{ number_format($item->price, 2) }} ج</span>
                                <span>{{ number_format($item->total, 2) }} ج</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($invoiceType === 'deposit')
                <div class="section">
                    <h3>مقدم الحجز</h3>
                    <div class="box">
                        <p>اسم العميل: {{ $extraData['client_name'] }}</p>
                        <p>موعد الحجز: {{ $extraData['booking_date'] }}</p>
                        @foreach ($groupedItems['deposit'] as $item)
                            <p>المبلغ المدفوع: {{ $item->total }} ج</p>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- السعر الكلي --}}
            <div class="section">
                <h3>الإجمالي الكلي</h3>
                <div class="box price">{{ $totalAmount }} ج</div>
            </div>

            <div class="section">
                <p style="text-align:center; margin-top:20px;">خلينا نشوفك تاني 😊</p>
            </div>
        </div>
    </div>
    <script>
        function printInvoice() {
            // 1. جمع البيانات من الصفحة الحالية
            const invoiceNumber = "{{ $invoice->invoice_number }}";
            const invoiceDate = "{{ $invoice->created_at->format('d/m/Y') }}";
            const clientName = "{{ $invoice->client->name ?? ($extraData['client_name'] ?? 'عميل نقدي') }}";
            const totalAmount = "{{ $totalAmount }}";
            const invoiceType = "{{ $invoice->type }}";
            const cycleNumber = "{{ $invoice->cycle_number }}";

            // 2. بناء جدول المنتجات/الخدمات ديناميكياً
            let itemsHtml = '';

            // إضافة الاشتراكات
            @if (isset($groupedItems['subscription']))
                @foreach ($groupedItems['subscription'] as $item)
                    itemsHtml += `<tr><td>1</td><td>اشتراك: {{ $item->name }}</td><td>{{ $item->price }}</td></tr>`;
                @endforeach
            @endif

            // إضافة الجلسات الخاصة/الحجز
            @if (isset($groupedItems['booking']))
                @foreach ($groupedItems['booking'] as $item)
                    itemsHtml +=
                        `<tr><td>1</td><td>حجز قاعة ({{ $bookingData->hall->name ?? '' }})</td><td>{{ $item->total }}</td></tr>`;
                @endforeach
            @endif

            // إضافة الجلسات العادية
            @if (isset($groupedItems['session']))
                @foreach ($groupedItems['session'] as $item)
                    itemsHtml += `<tr><td>{{ $item->qty }}</td><td>جلسة (ساعة)</td><td>{{ $item->total }}</td></tr>`;
                @endforeach
            @endif

            // إضافة المشتريات
            @if ($purchaseItems->isNotEmpty())
                @foreach ($purchaseItems as $item)
                    itemsHtml +=
                        `<tr><td>{{ $item->qty }}</td><td>{{ $item->name }}</td><td>{{ $item->total }}</td></tr>`;
                @endforeach
            @endif

            // إضافة دفعات المقدم إن وجدت
            @if (isset($groupedItems['deposit']))
                @foreach ($groupedItems['deposit'] as $item)
                    itemsHtml += `<tr><td>1</td><td>مقدم حجز</td><td>{{ $item->total }}</td></tr>`;
                @endforeach
            @endif

            // 3. فتح نافذة الطباعة وتطبيق التصميم الاحترافي لورق الكاشير
            const printWindow = window.open('', '', 'width=400,height=800');

            printWindow.document.write(`
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>فاتورة ${invoiceNumber}</title>
<style>
    @page { margin: 0; }
    body { font-family: "Tahoma", sans-serif; direction: rtl; margin: 0; padding: 0; color: #000; display: flex; justify-content: center; }
    * { font-weight: 900 !important; }
.invoice {
    width: 110mm; /* عرض قياسي لطباعة الفواتير الحرارية */
    max-width: 110%; /* لتصبح responsive لو الورقة أعرض أو على الشاشة */
    margin: 0 auto; /* لضمان التوسيط */
    padding: 5px 10px;
    box-sizing: border-box; /* مهم للتأكد أن padding لا يخرج عن حدود الفاتورة */
}.header { text-align: center; margin-bottom: 15px; border-bottom: 2px dashed #000; padding-bottom: 15px; }
    .logo { width: 120px; margin-bottom: 10px; }
    .header h2 { margin: 5px 0; font-size: 24px; }
    .header p { margin: 5px 0 0 0; font-size: 16px; }
    .big-cycle { text-align: center; font-size: 45px; border: 3px solid #000; border-radius: 8px; margin: 15px 0; padding: 5px; }
    .info { margin-bottom: 15px; font-size: 17px; line-height: 1.8; }
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-size: 16px; /* تقليل الحجم قليلًا */
}

th, td {
    padding: 5px 4px; /* أقل padding لتقليل المساحة */
    border-bottom: 1px dashed #000;
    text-align: center;
}

th:nth-child(1), td:nth-child(1) { width: 15%; }  /* الكمية */
th:nth-child(2), td:nth-child(2) { width: 55%; text-align: right; } /* البيان */
th:nth-child(3), td:nth-child(3) { width: 30%; }  /* المبلغ */ .total { margin-top: 20px; text-align: center; font-size: 24px; border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 15px 0; background-color: #f9f9f9; -webkit-print-color-adjust: exact; }
    .footer { text-align: center; margin-top: 20px; font-size: 16px; line-height: 1.6; }
</style>
</head>
<body>
<div class="invoice">

    <div class="header">
        <img src="/images/flow_logo.png" class="logo" alt="Logo" onerror="this.style.display='none'" />
        <h2>Flow Workspace</h2>
        <p>مكانك المثالي للعمل والإبداع 💡</p>
    </div>

    ${invoiceType === 'product' && cycleNumber ? `<div class="big-cycle">${cycleNumber}</div>` : ''}

    <div class="info">
        رقم الفاتورة: ${invoiceNumber}<br>
        التاريخ: ${invoiceDate}<br>
        العميل: ${clientName}
    </div>

    <table>
        <thead>
            <tr>
                <th>الكمية</th>
                <th>البيان</th>
                <th>المبلغ</th>
            </tr>
        </thead>
        <tbody>
            ${itemsHtml}
        </tbody>
    </table>

    <div class="total">
        الإجمالي: ${totalAmount} ج
    </div>

    <div class="footer">
        شكراً لزيارتك ❤️<br>
        نتمنى رؤيتك مرة أخرى
    </div>

</div>

<script>
window.onload = function() {
    window.print();
    setTimeout(() => window.close(), 600);
}
<\/script>

</body>
</html>
`);
            printWindow.document.close();
        }
    </script>

    {{-- نافذة الاسترجاع المنبثقة (Refund Modal) --}}
    <div id="refundModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.65); z-index:99999; justify-content:center; align-items:center; backdrop-filter:blur(5px);">
        <div style="background:#fff; border-radius:18px; padding:28px; width:92%; max-width:480px; box-shadow:0 15px 40px rgba(0,0,0,0.3); text-align:right; color:#222; animation:fadeInUp 0.3s ease;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid #eee; padding-bottom:10px;">
                <h3 style="margin:0; color:#dc3545; font-size:1.25rem; font-weight:bold;">↩️ استرجاع الفاتورة (Refund)</h3>
                <button type="button" onclick="closeRefundModal()" style="background:none; border:none; font-size:1.3rem; cursor:pointer; color:#888;">✕</button>
            </div>
            <p style="color:#555; font-size:0.88rem; line-height:1.6; margin-bottom:16px;">
                ⚠️ <strong>تنبيه هام:</strong> سيؤدي الاسترجاع إلى إعادة جميع المواد الخام والمنتجات المباعة إلى المخزن تلقائياً، وتحديث الأرصدة، وتسجيل قيد استرجاع في دفتر الحركات وسجل الشيفت.
            </p>
            <form action="{{ route('invoices.refund', $invoice->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد تماماً من تأكيد استرجاع الفاتورة؟');">
                @csrf
                <div style="margin-bottom:18px;">
                    <label style="display:block; margin-bottom:6px; font-weight:bold; font-size:0.88rem; color:#333;">سبب الاسترجاع <span style="color:red;">*</span></label>
                    <textarea name="reason" rows="3" required style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid #ccc; font-family:inherit; font-size:0.9rem;" placeholder="يرجى كتابة سبب الاسترجاع بالتفصيل (3 أحرف على الأقل).."></textarea>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="closeRefundModal()" style="background:#f0f0f0; border:none; padding:10px 18px; border-radius:10px; cursor:pointer; font-weight:600; color:#555;">إلغاء</button>
                    <button type="submit" style="background:#dc3545; color:#fff; border:none; padding:10px 22px; border-radius:10px; font-weight:bold; cursor:pointer; box-shadow:0 4px 12px rgba(220,53,69,0.3);">تأكيد الاسترجاع</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openRefundModal() {
        document.getElementById('refundModal').style.display = 'flex';
    }
    function closeRefundModal() {
        document.getElementById('refundModal').style.display = 'none';
    }
    </script>
@endsection



@section('style')


    <style>
        body {
            font-family: "Tahoma", sans-serif;
            direction: rtl;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .space {
            height: 30px;
        }

        .subscription-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }

        .card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            animation: fadeInUp 0.6s ease;
        }


        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f1f1f1;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .card-header h2 {
            font-size: 26px;
            color: #2b2b2b;
            margin: 0;
        }

        .badge {
            background: #D9B1AB;
            color: #fff;
            padding: 6px 15px;
            border-radius: 30px;
            font-weight: bold;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }

        .section h3 {
            color: #a86f68;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .box {
            background: #fafafa;
            padding: 5px 10px;
            border-radius: 12px;
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 5px;
            font-size: 12px;
            line-height: 1.7;
        }

        .price {
            font-weight: bold;
            font-size: 18px;
            color: #2b2b2b;
        }

        .remaining {
            font-weight: bold;
            font-size: 22px;
            color: #008000;
        }

        /* Progress Bar */
        .progress-section {
            margin: 25px 0;
        }

        .progress-bar {
            background: #eaeaea;
            border-radius: 12px;
            height: 20px;
            overflow: hidden;
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .progress-fill {
            background: linear-gradient(90deg, #D9B1AB, #a86f68);
            height: 100%;
            width: 0;
            border-radius: 12px;
            transition: width 0.6s ease-in-out;
        }

        .form-btn {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            background: #D9B1AB;
            color: #fff;
            padding: 12px 30px;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            cursor: pointer;
            transition: 0.3s;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            background: #a86f68;
            transform: scale(1.05);
        }

        /* ====== تصميم ذكي لقائمة المشتريات ====== */
        .purchase-list {
            background: #fafafa;
            border-radius: 12px;
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.05);
            padding: 10px;
            font-size: 13px;
        }

        .purchase-header,
        .purchase-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            align-items: center;
            text-align: center;
            padding: 8px 0;
        }

        .purchase-header {
            font-weight: bold;
            border-bottom: 1px solid #e0e0e0;
            color: #a86f68;
        }

        .purchase-row {
            border-bottom: 1px dashed #e5e5e5;
            color: #333;
        }

        .purchase-row:last-child {
            border-bottom: none;
        }

        .purchase-row span {
            padding: 3px 5px;
        }

        /* استجابة ممتازة للموبايل */
        @media (max-width: 600px) {

            .purchase-header,
            .purchase-row {
                grid-template-columns: 1.5fr 0.8fr 0.8fr 0.8fr;
                font-size: 12px;
            }
        }

        /* Snackbar style */
        .snackbar {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #333;
            color: #fff;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            z-index: 9999;
            opacity: 0;
            transform: translateX(120%);
            transition: opacity 0.4s ease, transform 0.4s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }


        .snackbar.show {
            opacity: 1;
            transform: translateX(0);
            /* 👈 تتحرك للداخل */
        }

        .snackbar.success {
            background: #28a745;
        }

        .snackbar.error {
            background: #dc3545;
        }

        /* أيقونة صغيرة */
        .snackbar i {
            font-size: 16px;
        }

        .used {
            font-weight: bold;
            font-size: 22px;
            color: #c40000;
            /* أحمر قوي */
            transition: transform 0.25s ease, color 0.25s ease;
        }

        /* تأثير بسيط عند التحديث */
        .used.updated {
            transform: scale(1.12);
        }


        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-print {
            background: linear-gradient(90deg, #28a745, #20c997);
            color: #fff;
            padding: 8px 18px;
            border-radius: 10px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(40, 167, 69, 0.4);
        }

        @media print {

            body {
                background: white !important;
            }

            .subscription-container {
                max-width: 280px;
                margin: 0 auto;
                padding: 0;
            }

            .card {
                box-shadow: none;
                border: none;
                padding: 10px;
            }

            .btn-edit,
            .btn-print {
                display: none;
            }

            .box {
                font-size: 12px;
            }

            h2 {
                font-size: 16px;
            }

            h3 {
                font-size: 14px;
            }

        }

        .big-cycle {
            text-align: center;
            font-size: 60px;
            font-weight: bold;
            margin: 10px 0;
            letter-spacing: 2px;
        }
    </style>
@endsection
