<!-- resources/views/invoice.blade.php -->
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta charset="UTF-8">
    <title>الفاتورة</title>
    <style>
        :root {
            --bg-start: #fdfdfd;
            --bg-end: #bdbdbd;
            --card-bg: #ffffff;
            --muted: #888;
            --accent1: #ff416c;
            --accent2: #ff4b2b;
            --pill-bg: #cfcfcf;
            --success: #2e8b57;
            --card-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: "Tahoma", sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background: linear-gradient(to bottom, var(--bg-start), var(--bg-end));
            color: #222;
        }

        /* صفحة كاملة */
        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 18px;
            box-sizing: border-box;
            gap: 18px;
            align-items: center;
        }

        h2.page-title {
            margin: 0;
            padding: 6px 12px;
            font-size: 20px;
            text-align: center;
        }

        /* زرّ الرجوع (كما عندك) */
        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #e5c6c3;
            border-radius: 50%;
            padding: 13px;
            cursor: pointer;
            transition: transform 0.2s;
            z-index: 2000;
        }

        /* الحاوية الرئيسية */
        .invoice-container {
            width: 100%;
            max-width: 1100px;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: visible;
            padding: 20px;
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* رأس الفاتورة: الإجمالي + عنوان */
        .invoice-header {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .summary {
            background: var(--pill-bg);
            padding: 10px 22px;
            border-radius: 25px;
            font-weight: bold;
            align-self: flex-start;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .invoice-meta {
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: flex-end;
            min-width: 160px;
        }

        .invoice-meta .small {
            color: var(--muted);
            font-size: 13px;
        }

        /* جدول الفاتورة (ديسكتوب) */
        .table-wrapper {
            width: 100%;
            box-sizing: border-box;
            overflow: hidden;
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        table.invoice-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            min-width: 520px;
        }

        table.invoice-table thead th {
            padding: 12px 10px;
            text-align: center;
            font-weight: 700;
            background: rgba(250, 250, 250, 0.98);
            position: sticky;
            top: 0;
            z-index: 3;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        table.invoice-table tbody td {
            padding: 12px 10px;
            text-align: center;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
            word-break: break-word;
        }

        td.total {
            color: var(--success);
            font-weight: 700;
        }

        /* Scrollable tbody technique */
        .invoice-table thead,
        .invoice-table tbody,
        .invoice-table tr {
            display: block;
        }

        .invoice-table tbody {
            max-height: 48vh;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .invoice-table tr {
            display: table;
            table-layout: fixed;
            width: 100%;
        }

        /* كروت الأصناف (مخفية افتراضياً، تظهر فقط على الموبايل) */
        .cards-wrapper {
            display: none;
            /* افتراضي - لا تظهر على الديسكتوب */
            width: 100%;
            box-sizing: border-box;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .item-card {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(180deg, #fff, #fafafa);
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.04);
        }

        .item-left {
            display: flex;
            gap: 10px;
            align-items: center;
            min-width: 0;
        }

        .item-badge {
            background: rgba(0, 0, 0, 0.06);
            border-radius: 10px;
            padding: 8px 10px;
            font-weight: 700;
            min-width: 48px;
            text-align: center;
            flex-shrink: 0;
        }

        .item-details {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .item-name {
            font-weight: 800;
            font-size: 15px;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            direction: rtl;
        }

        .item-meta {
            font-size: 13px;
            color: var(--muted);
        }

        .item-right {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
            min-width: 88px;
        }

        .item-price {
            font-weight: 700;
        }

        .item-total {
            color: var(--success);
            font-weight: 800;
        }

        /* تفاصيل الفوتر/أزرار */
        .action-bar {
            width: 100%;
            display: flex;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
            margin-top: 6px;
        }

        .action-left,
        .action-right {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        button {
            border: none;
            border-radius: 25px;
            padding: 12px 22px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease-in-out;
        }

        .btn-cancel {
            background: #e7b4b4;
            color: white;
        }

        .btn-print {
            background: #fcefb1;
            color: #333;
        }

        .btn-done {
            background: linear-gradient(135deg, var(--accent1), var(--accent2));
            width: 220px;
            color: white;
            font-size: 16px;
            font-weight: 800;
            border: none;
            border-radius: 50px;
            padding: 13px 26px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1.6px;
            transition: all 0.35s ease-in-out;
            position: relative;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(255, 65, 108, 0.6), 0 0 30px rgba(255, 75, 43, 0.35);
        }

        .btn-done:hover {
            transform: scale(1.06) rotate(-1deg);
            box-shadow: 0 0 25px rgba(255, 65, 108, 0.95), 0 0 50px rgba(255, 75, 43, 0.6);
        }

        .btn-done::before {
            content: "";
            position: absolute;
            top: 0;
            left: -75%;
            width: 50%;
            height: 100%;
            background: linear-gradient(120deg, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0.1) 100%);
            transform: skewX(-25deg);
        }

        .btn-done:hover::before {
            animation: shine 0.9s forwards;
        }

        @keyframes shine {
            0% {
                left: -75%;
            }

            100% {
                left: 125%;
            }
        }

        /* شريط أزرار ثابت في أسفل الموبايل */
        .sticky-actions-mobile {
            display: none;
        }

        /* طباعة */
        @media print {
            body {
                background: white;
            }

            .action-bar,
            .sticky-actions-mobile {
                display: none !important;
            }

            .invoice-container {
                box-shadow: none;
                border-radius: 0;
                padding: 6px;
            }

            table.invoice-table thead th {
                position: static;
            }

            .invoice-table tbody {
                max-height: none;
                overflow: visible;
                display: table-row-group;
            }

            .invoice-table thead,
            .invoice-table tbody,
            .invoice-table tr {
                display: table;
                width: 100%;
            }

            /* كروت لا تظهر في الطباعة */
            .cards-wrapper {
                display: none !important;
            }
        }

        /* --- Responsive behavior --- */
        @media (max-width: 900px) {
            .invoice-container {
                padding: 14px;
            }

            .invoice-table tbody {
                max-height: 40vh;
            }

            .btn-done {
                width: 180px;
                font-size: 15px;
            }

            .invoice-meta {
                min-width: 120px;
            }
        }

        /* MOBILE: show cards, hide table */
        @media (max-width: 640px) {
            .invoice-container {
                padding: 12px;
                border-radius: 10px;
            }

            h2.page-title {
                font-size: 18px;
            }

            .invoice-header {
                align-items: flex-start;
                gap: 8px;
            }

            .summary {
                position: static;
                align-self: stretch;
                display: inline-block;
                width: auto;
                margin-bottom: 6px;
            }

            /* على الموبايل: اخفي الجدول واظهر الكروت */
            table.invoice-table {
                display: none;
            }

            .table-wrapper {
                display: none;
            }

            /* هنا نظهر الكروت (فقط على الموبايل) */
            .cards-wrapper {
                display: grid;
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .action-bar {
                display: none;
            }

            /* show sticky bottom actions for mobile */
            .sticky-actions-mobile {
                display: flex;
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                padding: 10px;
                gap: 8px;
                justify-content: center;
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.98));
                box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.08);
                z-index: 60;
            }

            .sticky-actions-mobile .btn-done {
                width: calc(100% - 28px);
                max-width: 620px;
            }

            /* اجعل قائمة الكروت لا تتغطى بالشريط السفلي */
            .cards-wrapper {
                padding-bottom: 82px;
                /* مساحة للشريط الثابت */
            }

            .invoice-table tbody {
                max-height: calc(48vh - 56px);
            }
        }

        .back-btn {
            position: fixed;
            top: 60px;
            /* نزلناه شويه تحت */
            left: 20px;
            background: #e5c6c3;
            border-radius: 50%;
            padding: 13px;
            cursor: pointer;
            transition: transform 0.2s;
            z-index: 2000;
            border: none;
        }

        /* زرّ الرجوع */
        .back-btn2 {
            position: fixed;
            top: 12px;
            /* فوق شويه */
            left: 20px;
            background: #e5c6c3;
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 2001;
            /* أعلى من الرئيسية */
        }

        /* تأثير hover */
        .back-btn:hover,
        .back-btn2:hover {
            transform: scale(1.1);
            background: #d9b3b0;
        }

        /* أيقونة السهم */
        .back-btn2 i {
            color: #333;
            font-size: 16px;
        }

        @media print {
            @page {
                size: auto;
                margin: 10mm;
            }

            body {
                background: white !important;
                color: black !important;
            }

            /* إخفاء العناصر غير المرغوبة في الطباعة */
            .back-btn,
            .back-btn2,
            .action-bar,
            .sticky-actions-mobile,
            .page-title {
                display: none !important;
            }

            /* الحاوية الرئيسية */
            .page {
                padding: 0;
                display: block;
            }

            .invoice-container {
                box-shadow: none !important;
                border: 1px solid #eee;
                padding: 10px;
                width: 100%;
                max-width: 100%;
            }

            /* إظهار الجدول وإخفاء الكروت دائماً في الطباعة */
            table.invoice-table {
                display: table !important;
                width: 100% !important;
                border: 1px solid #000;
            }

            .table-wrapper {
                display: block !important;
                overflow: visible !important;
            }

            .invoice-table tbody {
                max-height: none !important;
                overflow: visible !important;
                display: table-row-group !important;
            }

            .invoice-table thead,
            .invoice-table tr {
                display: table-row !important;
                width: 100% !important;
            }

            .invoice-table th,
            .invoice-table td {
                display: table-cell !important;
                border: 1px solid #ddd !important;
                padding: 8px !important;
            }

            .cards-wrapper {
                display: none !important;
            }

            /* تنسيق الإجمالي في الطباعة */
            .summary {
                background: #f0f0f0 !important;
                border: 1px solid #ccc;
                color: black !important;
                -webkit-print-color-adjust: exact;
            }

            .total {
                color: black !important;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <h2 class="page-title">الفاتورة</h2>


        <button type="button" class="back-btn2" onclick="history.back()">
            ←

        </button>

        <div class="invoice-container" role="region" aria-label="Invoice container">
            <div class="invoice-header">
                <div class="header-left" style="gap:18px;">
                    <div class="invoice-meta">
                        <div>رقم الفاتورة: <strong id="invoiceNumber">--</strong></div>
                        <div class="small">التاريخ: <span id="invoiceDate">--</span></div>
                    </div>
                    <div class="summary" id="fixedSummary">الإجمالي:
                        @php $grandTotal = 0; @endphp
                        @foreach ($items as $item)
                            @php $grandTotal += $item['total']; @endphp
                        @endforeach
                        <span style="margin-left:10px;">{{ $grandTotal }} جينة</span>
                    </div>

                </div>


            </div>

            <!-- جدول الديسكتوب -->
            <div class="table-wrapper" aria-live="polite">
                <table class="invoice-table" role="table" aria-label="Invoice items">
                    <thead role="rowgroup">
                        <tr role="row">
                            <th role="columnheader">عدد</th>
                            <th role="columnheader">منتج</th>
                            <th role="columnheader">سعر</th>
                            <th role="columnheader">مجموع</th>
                        </tr>
                    </thead>
                    <tbody role="rowgroup">
                        @foreach ($items as $item)
                            <tr role="row">
                                <td role="cell">{{ $item['qty'] }}</td>
                                <td role="cell" style="text-align:right; padding-right:14px;">{{ $item['name'] }}
                                </td>
                                <td role="cell">{{ $item['price'] ?? '-' }}</td>
                                <td role="cell" class="total">{{ $item['total'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- كروت الموبايل (تظهر فقط على شاشات صغيرة) -->
            <div class="cards-wrapper" aria-live="polite">
                @foreach ($items as $item)
                    <div class="item-card" role="article" aria-label="صنف: {{ $item['name'] }}">
                        <div class="item-left">
                            <div class="item-badge">{{ $item['qty'] }}</div>
                            <div class="item-details">
                                <div class="item-name">{{ $item['name'] }}</div>
                            </div>
                        </div>

                        <div class="item-right">
                            <div class="item-price">سعر الوحدة: {{ $item['price'] ?? '-' }}</div>
                            <div class="item-total">مجموع: {{ $item['total'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
            <!-- Stock Error Box -->
            <div id="stockErrorBox"
                style="
    display:none;
    background:#ffe5e5;
    border:1px solid #ffb3b3;
    color:#8b0000;
    padding:16px;
    border-radius:12px;
    margin-bottom:12px;
    box-shadow:0 4px 12px rgba(0,0,0,.08);
">
                <strong id="stockErrorTitle"></strong>
                <ul id="stockErrorList" style="margin:12px 0 0; padding-right:18px;"></ul>
            </div>

            <!-- زر إتمام الفاتورة (الـ form الرئيسي) -->
            <form id="invoiceForm" style="margin-top:8px;">
                @csrf
                <input type="hidden" name="request_id" id="request_id">
                <input type="hidden" name="client_id" value="{{ $client->id ?? '' }}">
                <input type="hidden" name="notes" value="عملية بيع منفصله عن العمله">

                @foreach ($items as $index => $item)
                    <input type="hidden" name="items[{{ $index }}][item_type]" value="product">
                    <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item['id'] }}">
                    <input type="hidden" name="items[{{ $index }}][qty]" value="{{ $item['qty'] }}">
                @endforeach

                <div class="action-bar" aria-hidden="false">
                    <div class="action-left">
                        <button type="button" class="btn-cancel"
                            onclick="window.location.replace('{{ route('sale_proccess.create') }}')">إلغاء</button>
                       
                    </div>

                    <div class="action-right">
                        <button type="submit" class="btn-done">إتمام الفاتورة</button>
                    </div>
                </div>
            </form>

            <!-- فورم الطباعة: يبقى موجوداً منفصلاً ويُرسل target=_blank -->
            <form id="printForm" action="{{ route('invoices.print') }}" method="POST" target="_blank"
                style="display:none;">
                @csrf
                @foreach ($items as $index => $item)
                    <input type="hidden" name="items[{{ $index }}][qty]" value="{{ $item['qty'] }}">
                    <input type="hidden" name="items[{{ $index }}][name]" value="{{ $item['name'] }}">
                    <input type="hidden" name="items[{{ $index }}][price]"
                        value="{{ $item['price'] ?? '' }}">
                @endforeach
            </form>

            <!-- شريط أزرار ثابت للهواتف (يحتوي نفس الزرار الرئيسي) -->
            <div class="sticky-actions-mobile" aria-hidden="true">
                <button type="button" class="btn-cancel"
                    onclick="window.location.replace('{{ route('sale_proccess.create') }}')">إلغاء</button>
                <button type="submit" form="invoiceForm" class="btn-done">إتمام الفاتورة</button>
            </div>
        </div>
    </div>

    <script>
        // وظائف مساعدة
        function buildFormData(form) {
            return new FormData(form);
        }

        function postJsonForm(url, fd) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd,
                credentials: 'same-origin'
            });
        }

        // دالة الطباعة بالشكل الحراري الجديد مع إضافة رقم الدورة
        function printInvoice(invoiceNumber = "فاتورة مبدئية", cycleNumber = null, invoiceType = null) {
            // جلب التاريخ والوقت الحالي
            const dateObj = new Date();
            const invoiceDate = dateObj.toLocaleDateString('ar-EG') + ' ' + dateObj.toLocaleTimeString('ar-EG');

            // حساب الإجمالي مرة أخرى في الجافاسكريبت (من بيانات Blade)
            @php $jsTotal = 0; @endphp
            let itemsHtml = '';

            @foreach ($items as $item)
                @php $jsTotal += $item['total']; @endphp
                itemsHtml += `<tr>
                    <td style="width:15%">{{ $item['qty'] }}</td>
                    <td style="width:55%">{{ $item['name'] }}</td>
                    <td style="width:30%">{{ $item['total'] }} ج</td>
                </tr>`;
            @endforeach

            const totalAmount = "{{ $jsTotal }}";

            // فتح نافذة الطباعة وتطبيق التصميم المطلوب
            const printWindow = window.open('', '_blank', 'width=400,height=600');
            printWindow.document.write(`
            <!DOCTYPE html>
            <html lang="ar" dir="rtl">
            <head>
                <meta charset="UTF-8">
                <title>إيصال Flow - ${invoiceNumber}</title>
                <style>
                    @page { size: 80mm auto; margin: 5mm; }
                    body { font-family: "Tahoma", sans-serif; font-size: 13px; color: #000; direction: rtl; margin: 0; padding: 5px; }
                    .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 10px; }
                    .header h2 { margin: 0; font-size: 22px; letter-spacing: 1px; }
                    .header p { margin: 2px 0; font-size: 11px; }
                    .info { font-size: 11px; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
                    .items { width: 100%; border-collapse: collapse; margin: 10px 0; }
                    .items th, .items td { padding: 6px 2px; text-align: right; border-bottom: 1px dashed #eee; }
                    .items th { font-size: 12px; border-bottom: 1px solid #000; }
                    .items td:last-child, .items th:last-child { text-align: left; }
                    .total { border-top: 2px dashed #000; margin-top: 10px; padding-top: 10px; text-align: center; font-size: 16px; font-weight: bold; }
                    .footer { text-align: center; margin-top: 15px; font-size: 11px; border-top: 1px dashed #000; padding-top: 8px; }
                    .big-cycle {
                        text-align: center;
                        font-size: 70px;
                        font-weight: bold;
                        margin: 15px 0;
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h2>Flow</h2>
                    <p>مكانك المثالي للعمل والإبداع 💡</p>
                </div>
                
                ${invoiceType === 'product' && cycleNumber 
                    ? `<div class="big-cycle">${cycleNumber}</div>` 
                    : ''
                }

                <div class="info">
                    <strong>رقم الفاتورة:</strong> ${invoiceNumber}<br>
                    <strong>التاريخ:</strong> ${invoiceDate}<br>
                </div>
                <table class="items">
                    <thead>
                        <tr>
                            <th style="width:15%">العدد</th>
                            <th style="width:55%">المنتج</th>
                            <th style="width:30%">المجموع</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                </table>
                <div class="total">
                    الإجمالي: ${totalAmount} جينة
                </div>
                <div class="footer">
                    شكرًا لزيارتك Flow 💼<br>
                    نتطلع لرؤيتك مرة أخرى!
                </div>
            </body>
            </html>
            `);

            printWindow.document.close();
            printWindow.focus();

            // إعطاء وقت صغير لتحميل المحتوى قبل فتح نافذة الطباعة التلقائية
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 300);
        }

        // استبدال دالة الطباعة القديمة بالجديدة (في حالة الطباعة المبدئية قبل الحفظ)
        function openPrintForm() {
            printInvoice();
        }

        let isSubmitting = false;

        document.getElementById('invoiceForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            if (isSubmitting) return; // 🔥 يمنع التكرار
            isSubmitting = true;

            const form = e.target;
            const doneBtn = document.querySelector('.btn-done');

            if (doneBtn) {
                doneBtn.disabled = true; // 🔥 تعطيل الزر
                doneBtn.innerText = 'جاري الحفظ...';
            }

            try {
                document.getElementById('request_id').value = crypto.randomUUID();
                let response = await postJsonForm("{{ route('invoices.store') }}", buildFormData(form));
                let data = await response.json();

                if (response.ok) {
                    alert("✅ تم إنشاء الفاتورة رقم " + data.invoice_number);

                    printInvoice(
                        data.invoice_number,
                        data.cycle_number,
                        data.type
                    );

                    setTimeout(() => {
                        window.location.replace("{{ route('main.create') }}");
                    }, 500);
                } else {
                    isSubmitting = false;
                    if (doneBtn) {
                        doneBtn.disabled = false;
                        doneBtn.innerText = 'إتمام الفاتورة';
                    }
                }

            } catch (err) {
                isSubmitting = false;
                if (doneBtn) {
                    doneBtn.disabled = false;
                    doneBtn.innerText = 'إتمام الفاتورة';
                }
            }
        });

        // Submit on Enter logic
        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Enter') return;
            if (e.repeat) return;

            const active = document.activeElement;
            if (!active) return;

            const tag = active.tagName.toUpperCase();
            if (tag === 'TEXTAREA' || active.isContentEditable) return;
            if (active.hasAttribute && active.hasAttribute('data-ignore-enter')) return;

            if (tag === 'INPUT') {
                const t = (active.type || '').toLowerCase();
                const blocked = ['button', 'file', 'radio', 'checkbox', 'image'];
                if (blocked.includes(t)) return;
            }

            e.preventDefault();

            const doneBtn = document.querySelector('.btn-done[form="invoiceForm"]') || document.querySelector(
                '.btn-done');
            if (doneBtn) {
                doneBtn.click();
            }
        });
    </script>
</body>

</html>
