@extends('layouts.appp')

@section('page_title', 'إدارة الاشتراكات')

@section('styles')
    <style>
        /* الفلاتر والبحث في صندوق زجاجي واحد */
        .filter-section {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        /* تصميم زر الإضافة العائم */
        .fab-add {
            position: fixed;
            bottom: 30px;
            left: 30px;
            width: 65px;
            height: 65px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000;
            font-size: 30px;
            text-decoration: none;
            box-shadow: 0 5px 20px rgba(0, 242, 254, 0.4);
            transition: all 0.3s ease;
            z-index: 1000;
            border: none;
        }

        .fab-add:hover {
            transform: scale(1.1) rotate(90deg);
            color: #000;
            box-shadow: 0 8px 25px rgba(0, 242, 254, 0.6);
        }

        /* حقل البحث */
        .glass-search-container {
            position: relative;
            margin-bottom: 25px;
        }

        .glass-input {
            width: 100%;
            padding: 15px 45px 15px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: var(--text-main);
            font-size: 1rem;
            transition: all 0.3s;
        }

        .glass-input:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(0, 242, 254, 0.2);
            outline: none;
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 1.2rem;
        }

        /* تصميم الفلاتر (الـ Checkboxes) */
        .filter-label {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 12px;
            display: block;
            font-size: 0.9rem;
        }

        .custom-check-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .custom-check {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.03);
            padding: 8px 16px;
            border-radius: 10px;
            border: 1px solid var(--glass-border);
            transition: 0.3s;
            user-select: none;
        }

        .custom-check:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .custom-check input:checked+span {
            color: var(--primary);
            font-weight: bold;
        }

        .custom-check input {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        /* الجدول الزجاجي */
        .table-container {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            overflow: hidden;
        }

        .glass-table {
            width: 100%;
            border-collapse: collapse;
        }

        .glass-table thead {
            background: rgba(0, 242, 254, 0.1);
        }

        .glass-table th {
            padding: 18px;
            text-align: center;
            color: var(--primary);
            font-weight: 600;
            border-bottom: 1px solid var(--glass-border);
        }

        .glass-table td {
            padding: 16px;
            text-align: center;
            color: var(--text-main);
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        }

        .subscription-row {
            transition: all 0.3s;
            cursor: pointer;
        }

        .subscription-row:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: scale(1.002);
        }

        /* الموبايل - تحويل الجدول لكروت */
        @media (max-width: 992px) {
            .glass-table thead {
                display: none;
            }

            .glass-table,
            .glass-table tbody,
            .glass-table tr,
            .glass-table td {
                display: block;
                width: 100%;
            }

            .glass-table tr {
                margin-bottom: 15px;
                border-bottom: 2px solid var(--glass-border);
                padding: 10px;
            }

            .glass-table td {
                text-align: right;
                padding: 8px 15px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border: none;
            }

            .glass-table td::before {
                content: attr(data-label);
                color: var(--primary);
                font-weight: bold;
                font-size: 0.85rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">

        <!-- زر الإضافة العائم -->
        <a href="{{ route('subscriptions.create') }}" class="fab-add" title="إضافة اشتراك">
            <i class="fas fa-plus">+</i>
        </a>

        <!-- قسم البحث والفلاتر -->
        <div class="filter-section animate__animated animate__fadeIn">
            <div class="row">
                <!-- البحث -->
                <div class="col-lg-12">
                    <div class="glass-search-container">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="searchBox" class="glass-input"
                            placeholder="بحث باسم العميل، رقم الهاتف، أو كود الاشتراك...">
                    </div>
                </div>

                <!-- فلتر الحالة -->
                <div class="col-md-4 mb-4 mb-md-0">
                    <span class="filter-label">⚡ حالة الاشتراك</span>
                    <div class="custom-check-group">
                        <label class="custom-check">
                            <input type="checkbox" class="status-filter" value="1" id="statusActive">
                            <span>فعال</span>
                        </label>
                        <label class="custom-check">
                            <input type="checkbox" class="status-filter" value="0" id="statusEnded">
                            <span>منتهي</span>
                        </label>
                    </div>
                </div>

                <!-- فلتر الخطط -->
                <div class="col-md-8">
                    <span class="filter-label">📦 تصفية حسب الخطة</span>
                    <div class="custom-check-group">
                        @foreach ($plans as $plan)
                            <label class="custom-check">
                                <input type="checkbox" class="plan-filter" value="{{ $plan->id }}"
                                    id="plan{{ $plan->id }}">
                                <span>{{ $plan->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- جدول البيانات -->
        <div class="table-container animate__animated animate__fadeInUp">
            <div class="table-responsive">
                <table class="glass-table">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>العميل</th>
                            <th>الهاتف</th>
                            <th>الخطة</th>
                            <th>بداية الاشتراك</th>
                            <th>نهاية الاشتراك</th>
                            <th>المتبقي</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody id="subsTable">
                        <tr>
                            <td colspan="8" class="text-center p-5">
                                <div class="spinner-border text-info" role="status"></div>
                                <p class="mt-2">جاري تحميل البيانات...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchBox = document.getElementById("searchBox");
            const statusFilters = document.querySelectorAll(".status-filter");
            const planFilters = document.querySelectorAll(".plan-filter");
            const tableBody = document.getElementById("subsTable");
            const showRoute = @json(route('subscriptions.show', ['id' => ':id']));

            function formatDateTime(dateStr) {
                if (!dateStr) return '-';
                let d = new Date(dateStr);
                return d.toLocaleDateString("ar-EG", {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }

            function fetchSubs() {
                let q = searchBox.value;
                let statuses = Array.from(statusFilters).filter(c => c.checked).map(c => c.value);
                let plans = Array.from(planFilters).filter(c => c.checked).map(c => c.value);

                let params = new URLSearchParams({
                    q
                });
                statuses.forEach(s => params.append("statuses[]", s));
                plans.forEach(p => params.append("plans[]", p));

                fetch("{{ route('subscriptions.ajaxSearch') }}?" + params.toString())
                    .then(res => res.json())
                    .then(data => {
                        tableBody.innerHTML = "";
                        if (data.length === 0) {
                            tableBody.innerHTML =
                                `<tr><td colspan="8" class="text-center p-5 text-muted">❌ لم يتم العثور على اشتراكات مطابقة</td></tr>`;
                        } else {
                            data.forEach(s => {
                                const badgeClass = s.is_active === "فعال" ? "badge-success-glass" :
                                    "badge-danger-glass";
                                tableBody.innerHTML += `
                                <tr class="subscription-row" onclick="window.location.href='${showRoute.replace(':id', s.id)}'">
                                    <td data-label="#ID"><span class="text-primary fw-bold">#${s.id}</span></td>
                                    <td data-label="العميل"><strong>${s.client_name}</strong></td>
                                    <td data-label="الهاتف">${s.client_phone}</td>
                                    <td data-label="الخطة">${s.plan_name}</td>
                                    <td data-label="من">${formatDateTime(s.start_date)}</td>
                                    <td data-label="إلى">${formatDateTime(s.end_date)}</td>
                                    <td data-label="المتبقي"><span class="text-accent">${s.remaining_visits} حصة</span></td>
                                    <td data-label="الحالة">
                                        <span class="badge ${s.is_active === "فعال" ? 'bg-success' : 'bg-danger'} shadow-sm">
                                            ${s.is_active}
                                        </span>
                                    </td>
                                </tr>`;
                            });
                        }
                    });
            }

            searchBox.addEventListener("input", debounce(fetchSubs, 500));
            statusFilters.forEach(cb => cb.addEventListener("change", fetchSubs));
            planFilters.forEach(cb => cb.addEventListener("change", fetchSubs));

            function debounce(func, wait) {
                let timeout;
                return function() {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, arguments), wait);
                };
            }

            fetchSubs(); // تحميل أولي
        });
    </script>
@endpush
