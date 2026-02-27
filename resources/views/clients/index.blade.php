@extends('layouts.app')

@section('page_title', 'العملاء')

<style>
    body {
        font-family: "Tahoma", sans-serif;
        background: linear-gradient(to bottom, #fff, #fce9d9);
        margin: 0;
        padding: 0;
        color: #333;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 20px;
    }
.filters-box {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    background-color: #f9f9f9; /* خلفية هادئة */
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    margin-bottom: 25px;
}

.filter-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 150px;
}

.filter-item label {
    font-weight: 600;
    color: #555;
    font-size: 14px;
}

.filter-item select,
.filter-item input[type="number"] {
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
    transition: 0.2s;
    outline: none;
    background-color: #fff;
}

.filter-item select:focus,
.filter-item input[type="number"]:focus {
    border-color: #7aa7f9;
    box-shadow: 0 0 0 3px rgba(122, 167, 249, 0.2);
}

.filter-item .separator {
    margin: 0 5px;
    color: #888;
    font-weight: 600;
}

/* responsive */
@media (max-width: 600px) {
    .filters-box {
        flex-direction: column;
    }
}

    /* العداد */
    .stats-box {
        background: #fdf6f0;
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        width: 220px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        font-size: 15px;
        margin: 10px;
        flex-shrink: 0;
    }

    .stats-box p:first-child {
        margin: 0;
        font-weight: bold;
        color: #444;
        font-size: 16px;
    }

    .stats-box p:last-child {
        margin: 10px 0 0;
        font-size: 22px;
        color: #333;
    }

    /* الصف الأول */
    .header-row {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 40px;
    }

    /* مربع البحث */
    .search-box {
        margin: 20px auto;
        text-align: center;
    }

    .search-box input {
        padding: 14px 20px;
        width: 450px;
        max-width: 100%;
        border-radius: 25px;
        border: 1px solid #ddd;
        font-size: 15px;
        outline: none;
        transition: 0.2s;
        background: #fff;
    }

    .search-box input:focus {
        border-color: #ffcb9a;
        box-shadow: 0 0 6px rgba(255, 170, 80, 0.5);
    }

    /* الجدول */
    table {
        width: 100%;
        border-collapse: collapse;
        background: transparent;
        border-radius: 12px;
        overflow: hidden;
        margin-top: 20px;
    }

    thead {
        background: rgba(255, 224, 178, 0.8);
    }

    thead th {
        padding: 16px 20px;
        text-align: center;
        font-size: 15px;
        font-weight: bold;
        color: #444;
    }

    tbody tr {
        border-bottom: 1px solid #eee;
        text-align: center;
        transition: background 0.2s;
    }

    tbody tr:hover {
        background: rgba(255, 247, 240, 0.7);
    }

    tbody td {
        padding: 14px 18px;
        font-size: 15px;
        color: #333;
    }

    /* الموبايل */
    @media (max-width: 768px) {
        .header-row {
            flex-direction: column;
        }

        .search-box input {
            width: 100%;
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
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.9);
        }

        tbody td {
            text-align: right;
            padding: 8px 10px;
            position: relative;
            font-size: 14px;
        }

        tbody td::before {
            content: attr(data-label);
            position: absolute;
            left: 10px;
            font-weight: bold;
            color: #666;
        }
    }
</style>

@section('content')
    <div class="container">

        {{-- الإشعارات --}}
        @if (session('success'))
            <div style="background: #d4edda; padding: 12px; margin-bottom: 20px; border-radius: 8px; color:#155724;">
                {{ session('success') }}
            </div>
        @endif
        {{-- العداد --}}
        <div class="header-row" style="display: flex; gap: 20px;">
            <div class="stats-box">
                <p>عدد العملاء</p>
                <p>{{ $count_client }}</p> <!-- مش محتاج id -->
            </div>
            {{-- <div class="stats-box">
                <p>العملاء النشطين</p>
                <p>{{ $active_clients_count }}</p> <!-- مش محتاج id -->
            </div> --}}

        </div>

        <div class="search-box">
            <input type="text" id="searchBox" placeholder="🔍 بحث عن عميل (اسم أو هاتف أو ID)">
        </div><div class="filters-box">
    <div class="filter-item">
        <label>التخصص</label>
        <select id="specializationFilter">
            <option value="">كل التخصصات</option>
            @foreach($specializations as $spec)
                <option value="{{ $spec->id }}">{{ $spec->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="filter-item">
        <label>المرحلة التعليمية</label>
        <select id="educationStageFilter">
            <option value="">كل المراحل</option>
            @foreach($educationStages as $stage)
                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="filter-item">
        <label>السن</label>
        <input type="number" id="ageFrom" placeholder="من" min="10" max="100">
        <span class="separator">-</span>
        <input type="number" id="ageTo" placeholder="إلى" min="10" max="100">
    </div>
</div>


        <table>
            <thead>
                <tr>
                    <th>المعرف</th>
                    <th>اسم العميل</th>
                    <th>رقم الهاتف</th>
                    <th>العمر</th>
                    <th>التخصص</th>
                    <th>المرحلة التعليمية</th>
                    <th>عدد الزيارات</th>
                    <th>عدد الفواتير</th>
                </tr>
            </thead>
            <tbody id="clientTable">
                <tr>
                    <td colspan="9" class="text-center p-3">⏳ جاري التحميل...</td>
                </tr>
            </tbody>
        </table>

    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const showRouteTemplate = @json(route('clients.show', ['client' => ':id']));
    const tbody = document.getElementById('clientTable');

    const searchBox = document.getElementById('searchBox');
    const specializationFilter = document.getElementById('specializationFilter');
    const educationStageFilter = document.getElementById('educationStageFilter');
    const ageFromInput = document.getElementById('ageFrom');
    const ageToInput = document.getElementById('ageTo');

    // دالة عرض الصفوف
    function renderRows(data) {
        tbody.innerHTML = '';

        if (!data || data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="text-center p-3">❌ لا توجد نتائج</td></tr>`;
            return;
        }

        data.forEach(item => {
            const tr = document.createElement('tr');
            tr.style.cursor = 'pointer';
            tr.dataset.id = item.id;

            tr.innerHTML = `
                <td data-label="المعرف">${item.id}</td>
                <td data-label="اسم العميل">${item.name}</td>
                <td data-label="رقم الهاتف">${item.phone ?? '-'}</td>
                <td data-label="العمر">${item.age ?? '-'}</td>
                <td data-label="التخصص">${item.specialization}</td>
                <td data-label="المرحلة التعليمية">${item.education_stage}</td>
                <td data-label="عدد الزيارات">${item.invoices_count}</td>
                <td data-label="عدد الفواتير">${item.invoices_count}</td>
            `;

            tr.addEventListener('click', () => {
                const url = showRouteTemplate.replace(':id', encodeURIComponent(item.id));
                window.location.href = url;
            });

            tbody.appendChild(tr);
        });
    }

    // دالة جلب البيانات من السيرفر مع جميع الفلاتر
    function fetchClients() {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center p-3">⏳ جاري التحميل...</td></tr>`;

        const params = new URLSearchParams();
        if(searchBox.value) params.append('query', searchBox.value.trim());
        if(specializationFilter.value) params.append('specialization_id', specializationFilter.value);
        if(educationStageFilter.value) params.append('education_stage_id', educationStageFilter.value);
        if(ageFromInput.value) params.append('age_from', ageFromInput.value);
        if(ageToInput.value) params.append('age_to', ageToInput.value);

        fetch("{{ route('clients.search') }}?" + params.toString())
            .then(res => res.json())
            .then(data => renderRows(data))
            .catch(err => {
                console.error(err);
                tbody.innerHTML = `<tr><td colspan="9" class="text-center p-3">⚠️ حدث خطأ، حاول مرة أخرى</td></tr>`;
            });
    }

    // Debounce لكل الحقول
    let debounceTimer;
    [searchBox, specializationFilter, educationStageFilter, ageFromInput, ageToInput].forEach(el => {
        el.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fetchClients, 250);
        });
    });

    // تحميل أولي
    fetchClients();
});
</script>


@endsection
