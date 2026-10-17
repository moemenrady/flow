@extends('layouts.appp')

@section('page_title', 'قائمة المصروفات')

@section('styles')
    <!-- استدعاء الثيم المظلم لـ Flatpickr ليتناسب مع تصميم النظام -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">

    <style>
        /* ====== الهيدر وزر الإضافة ====== */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        /* زر الإضافة بتأثير اللمعة والنيون */
        .btn-glow-add {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #000;
            font-weight: bold;
            font-size: 1.1rem;
            text-decoration: none;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0, 242, 254, 0.4);
            transition: all 0.3s ease;
            border: none;
        }

        .btn-glow-add::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
            transform: skewX(-20deg);
            transition: 0.5s;
        }

        .btn-glow-add:hover {
            transform: translateY(-3px);
            box-shadow: 0 0 25px rgba(0, 242, 254, 0.6);
            color: #000;
        }

        .btn-glow-add:hover::before {
            left: 150%;
        }

        /* ====== صندوق الفلاتر (زجاجي) ====== */
        .glass-panel {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .filters-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* ستايل حقول الإدخال */
        .glass-input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            color: var(--text-main);
            font-family: inherit;
            transition: all 0.3s;
            outline: none;
        }

        .glass-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(0, 242, 254, 0.2);
            background: rgba(0, 0, 0, 0.4);
        }

        .glass-input option {
            background: #10141e;
            color: #fff;
        }

        /* ====== الجدول الزجاجي ====== */
        .table-responsive {
            overflow-x: auto;
            border-radius: 16px;
            border: 1px solid var(--glass-border);
            background: var(--card-bg);
            backdrop-filter: blur(15px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }

        thead {
            background: rgba(0, 242, 254, 0.1);
        }

        thead th {
            padding: 16px;
            color: var(--primary);
            font-weight: 700;
            font-size: 0.95rem;
            border-bottom: 1px solid var(--glass-border);
        }

        tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        tbody td {
            padding: 14px 16px;
            color: var(--text-main);
            font-size: 0.95rem;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-edit {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            background: rgba(180, 101, 218, 0.1);
            color: var(--accent);
            border: 1px solid rgba(180, 101, 218, 0.3);
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: 0.3s;
        }

        .btn-edit:hover {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 0 10px rgba(180, 101, 218, 0.4);
        }

        /* انيميشن ظهور الصفوف */
        .row-animate {
            animation: fadeUp 0.4s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* حالة عدم وجود بيانات */
        .no-results {
            padding: 30px !important;
            color: var(--text-muted) !important;
            font-size: 1.1rem;
        }

        /* ====== ريسبونسيف الموبايل (نظام الكروت) ====== */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-glow-add {
                width: 100%;
                justify-content: center;
            }

            .filter-group {
                min-width: 100%;
            }

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
                width: 100%;
            }

            thead {
                display: none;
            }

            tbody tr {
                margin-bottom: 15px;
                border: 1px solid var(--glass-border);
                border-radius: 12px;
                background: rgba(0, 0, 0, 0.2);
                padding: 10px;
            }

            tbody td {
                text-align: right;
                padding: 10px;
                position: relative;
                padding-right: 40%;
                /* مساحة للعنوان */
                border-bottom: 1px dashed rgba(255, 255, 255, 0.05);
            }

            tbody td:last-child {
                border-bottom: none;
                text-align: center;
                padding-right: 10px;
            }

            tbody td::before {
                content: attr(data-label);
                position: absolute;
                right: 15px;
                top: 10px;
                color: var(--primary);
                font-weight: bold;
                font-size: 0.85rem;
            }
        }
    </style>
@endsection

@section('content')
    <!-- الهيدر -->
    <div class="page-header">
        <h1 class="page-title">إدارة المصروفات</h1>
        <a href="{{ route('expenses.create') }}" class="btn-glow-add">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
            </svg>
            إضافة مصروف جديد
        </a>
    </div>

    <!-- الفلاتر -->
    <div class="glass-panel">
        <div class="filters-container">
            <div class="filter-group">
                <label>نوع المصروف</label>
                <select id="expenseTypeFilter" class="glass-input">
                    <option value="">كل الأنواع</option>
                    @foreach ($expenseTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>تحديد الفترة (من - إلى)</label>
                <input type="text" id="dateRange" class="glass-input" placeholder="اضغط لاختيار الفترة">
                <input type="hidden" id="fromDateHidden">
                <input type="hidden" id="toDateHidden">
            </div>
        </div>
    </div>

    <!-- الجدول -->
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>النوع</th>
                    <th>المبلغ</th>
                    <th>الملاحظة</th>
                    <th>أضيف بواسطة</th>
                    <th>التاريخ</th>
                    <th>إجراء</th>
                </tr>
            </thead>
            <tbody id="expenseTable">
                <tr>
                    <td colspan="6" class="text-center p-4" style="color: var(--primary);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" class="spin-anim" style="vertical-align: middle;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        جاري التحميل...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        const tableBody = document.getElementById("expenseTable");
        const expenseTypeFilter = document.getElementById("expenseTypeFilter");
        const fromInput = document.getElementById("fromDateHidden");
        const toInput = document.getElementById("toDateHidden");

        let params = new URLSearchParams();

        // دالة رسم الصفوف
        function renderRows(data) {
            tableBody.innerHTML = '';

            if (!data || data.length === 0) {
                tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="no-results">
                        <svg width="40" height="40" fill="none" stroke="var(--text-muted)" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:10px; display:block; margin: 0 auto;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        لا توجد مصروفات مسجلة لهذه الفلاتر
                    </td>
                </tr>`;
                return;
            }

            data.forEach((exp, index) => {
                const tr = document.createElement("tr");
                tr.classList.add("row-animate");
                tr.style.animationDelay = `${index * 50}ms`;

                const dateObj = new Date(exp.created_at);
                const datePart = dateObj.toLocaleDateString("en-CA", {
                    year: "numeric",
                    month: "2-digit",
                    day: "2-digit"
                });
                const timePart = dateObj.toLocaleTimeString("en-US", {
                    hour: "2-digit",
                    minute: "2-digit",
                    hour12: true
                });

                const createdAt =
                    `${datePart}<br><small style="color:var(--text-muted); font-size:0.8rem;">${timePart}</small>`;

                tr.innerHTML = `
                    <td data-label="النوع"><strong>${exp.expense_type_name}</strong></td>
                    <td data-label="المبلغ" style="color: #2ed573; font-weight: bold;">${exp.amount} ج.م</td>
                    <td data-label="الملاحظة">${exp.note ?? '-'}</td>
                    <td data-label="أضيف بواسطة">${exp.added_by}</td>
                    <td data-label="التاريخ">${createdAt}</td>
                    <td data-label="إجراء">
                        <a href="/expenses/${exp.id}/edit" class="btn-edit">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            تعديل
                        </a>
                    </td>
                `;

                tableBody.appendChild(tr);
            });
        }

        // دالة جلب البيانات بالأجاكس
        function fetchExpenses() {
            params = new URLSearchParams();

            if (expenseTypeFilter.value) params.append("expense_type_id", expenseTypeFilter.value);
            if (fromInput.value) params.append("from", fromInput.value);
            if (toInput.value) params.append("to", toInput.value);

            tableBody.innerHTML =
                `<tr><td colspan="6" class="text-center p-4" style="color: var(--primary);">⏳ جاري جلب البيانات...</td></tr>`;

            fetch("{{ route('expense.ajaxSearch') }}?" + params.toString())
                .then(res => res.json())
                .then(data => renderRows(data))
                .catch(err => {
                    console.error(err);
                    tableBody.innerHTML =
                        `<tr><td colspan="6" class="text-center p-4" style="color: #ff4757;">⚠️ حدث خطأ في الاتصال، يرجى المحاولة مرة أخرى</td></tr>`;
                });
        }

        // تفعيل الفلتر عند تغيير النوع
        expenseTypeFilter.addEventListener("change", fetchExpenses);

        // إعداد التقويم (Flatpickr)
        flatpickr("#dateRange", {
            mode: "range",
            dateFormat: "Y-m-d",
            disableMobile: "true", // عشان يفتح بشكل مريح عالموبايل
            onChange: function(selectedDates) {
                if (selectedDates.length === 2) {
                    // تصحيح التوقيت المحلي
                    fromInput.value = selectedDates[0].toLocaleDateString('en-CA');
                    toInput.value = selectedDates[1].toLocaleDateString('en-CA');
                    fetchExpenses();
                } else if (selectedDates.length === 0) {
                    fromInput.value = "";
                    toInput.value = "";
                    fetchExpenses();
                }
            }
        });

        // تشغيل الجلب عند فتح الصفحة
        window.addEventListener("DOMContentLoaded", fetchExpenses);
    </script>
    <style>
        .spin-anim {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush
