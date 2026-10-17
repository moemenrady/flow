@extends('layouts.appp')

@section('page_title', 'إدارة العملاء')

@section('styles')
    <style>
        /* الحاوية الأساسية */
        .clients-container {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* كارت الإحصائيات */
        .stats-header {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        .glass-stats-card {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            padding: 20px 50px;
            border-radius: 20px;
            text-align: center;
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
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .glass-stats-card .value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
            text-shadow: 0 0 15px rgba(0, 242, 254, 0.4);
        }

        /* قسم البحث والفلاتر */
        .filter-section {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .glass-input {
            width: 100%;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: var(--text-main);
            transition: all 0.3s ease;
            outline: none;
        }

        .glass-input:focus {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 10px rgba(0, 242, 254, 0.2);
        }

        .filter-label {
            display: block;
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        /* تصميم الجدول */
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
            font-weight: 700;
            border-bottom: 1px solid var(--glass-border);
        }

        .glass-table td {
            padding: 15px;
            text-align: center;
            color: var(--text-main);
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        }

        .glass-table tbody tr {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .glass-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .badge-count {
            background: rgba(0, 242, 254, 0.15);
            color: var(--primary);
            padding: 4px 10px;
            border-radius: 8px;
            font-weight: bold;
        }

        /* ريسبونسيف الموبايل */
        @media (max-width: 992px) {
            .glass-table thead {
                display: none;
            }

            .glass-table tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid var(--glass-border);
                border-radius: 15px;
                padding: 10px;
            }

            .glass-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 15px;
                text-align: right;
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
    <div class="clients-container">

        {{-- الإشعارات --}}
        @if (session('success'))
            <div class="alert alert-glass-success animate__animated animate__fadeInDown">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        {{-- العداد --}}
        <div class="stats-header animate__animated animate__fadeIn">
            <div class="glass-stats-card">
                <span class="label">إجمالي عدد العملاء</span>
                <span class="value">{{ $count_client }}</span>
            </div>
        </div>

        {{-- البحث والفلاتر --}}
        <div class="filter-section animate__animated animate__fadeIn" style="animation-delay: 0.1s;">
            <div class="row g-3">
                <div class="col-lg-12">
                    <label class="filter-label">🔍 بحث سريع</label>
                    <input type="text" id="searchBox" class="glass-input"
                        placeholder="بحث باسم العميل، رقم الهاتف، أو المعرف الرقمي...">
                </div>

                <div class="col-md-4">
                    <label class="filter-label">🎓 المرحلة التعليمية</label>
                    <select id="educationStageFilter" class="glass-input">
                        <option value="">كل المراحل</option>
                        @foreach ($educationStages as $stage)
                            <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="filter-label">🧬 التخصص</label>
                    <select id="specializationFilter" class="glass-input">
                        <option value="">كل التخصصات</option>
                        @foreach ($specializations as $spec)
                            <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="filter-label">🎂 تصفية بالسن</label>
                    <div class="d-flex align-items: center; gap: 10px;">
                        <input type="number" id="ageFrom" class="glass-input" placeholder="من">
                        <span class="text-muted align-self-center">-</span>
                        <input type="number" id="ageTo" class="glass-input" placeholder="إلى">
                    </div>
                </div>
            </div>
        </div>

        {{-- الجدول --}}
        <div class="table-container animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
            <div class="table-responsive">
                <table class="glass-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>اسم العميل</th>
                            <th>الهاتف</th>
                            <th>العمر</th>
                            <th>التخصص</th>
                            <th>المرحلة</th>
                            <th>الزيارات</th>
                            <th>الفواتير</th>
                        </tr>
                    </thead>
                    <tbody id="clientTable">
                        <tr>
                            <td colspan="8" class="text-center p-5">
                                <div class="spinner-border text-info" role="status"></div>
                                <p class="mt-2">جاري تحميل بيانات العملاء...</p>
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
        document.addEventListener('DOMContentLoaded', function() {
            const showRouteTemplate = @json(route('clients.show', ['client' => ':id']));
            const tbody = document.getElementById('clientTable');
            const inputs = ['searchBox', 'specializationFilter', 'educationStageFilter', 'ageFrom', 'ageTo'];

            function renderRows(data) {
                tbody.innerHTML = '';

                if (!data || data.length === 0) {
                    tbody.innerHTML =
                        `<tr><td colspan="8" class="text-center p-5 text-muted">❌ لا توجد نتائج مطابقة لبحثك</td></tr>`;
                    return;
                }

                data.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.className = 'animate__animated animate__fadeIn';

                    tr.innerHTML = `
                <td data-label="المعرف"><span class="text-primary fw-bold">#${item.id}</span></td>
                <td data-label="اسم العميل"><strong>${item.name}</strong></td>
                <td data-label="رقم الهاتف">${item.phone ?? '-'}</td>
                <td data-label="العمر">${item.age ?? '-'}</td>
                <td data-label="التخصص">${item.specialization}</td>
                <td data-label="المرحلة">${item.education_stage}</td>
                <td data-label="الزيارات"><span class="badge-count">${item.invoices_count}</span></td>
                <td data-label="الفواتير"><span class="badge-count">${item.invoices_count}</span></td>
            `;

                    tr.addEventListener('click', () => {
                        window.location.href = showRouteTemplate.replace(':id', encodeURIComponent(
                            item.id));
                    });

                    tbody.appendChild(tr);
                });
            }

            function fetchClients() {
                const params = new URLSearchParams();
                const query = document.getElementById('searchBox').value.trim();
                const spec = document.getElementById('specializationFilter').value;
                const stage = document.getElementById('educationStageFilter').value;
                const from = document.getElementById('ageFrom').value;
                const to = document.getElementById('ageTo').value;

                if (query) params.append('query', query);
                if (spec) params.append('specialization_id', spec);
                if (stage) params.append('education_stage_id', stage);
                if (from) params.append('age_from', from);
                if (to) params.append('age_to', to);

                fetch("{{ route('clients.search') }}?" + params.toString())
                    .then(res => res.json())
                    .then(data => renderRows(data))
                    .catch(err => {
                        tbody.innerHTML =
                            `<tr><td colspan="8" class="text-center p-4 text-danger">⚠️ حدث خطأ أثناء جلب البيانات</td></tr>`;
                    });
            }

            let debounceTimer;
            inputs.forEach(id => {
                document.getElementById(id).addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(fetchClients, 300);
                });
            });

            fetchClients(); // التحميل الأولي
        });
    </script>
@endpush
