@extends('layouts.app_page')

@section('content')
    <div class="drafts-wrapper">

        {{-- التنبيهات --}}
        @if (session('success'))
            <script>
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true
                });
            </script>
        @endif

        <div class="page-header-section">
            <h2 class="page-title">إدارة مصاريف الموظفين <span class="title-badge">مسودات</span></h2>

            <div class="search-filter-bar">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="tableSearch" placeholder="ابحث بالاسم، الوصف، أو المبلغ..." class="filter-input">
                </div>
                <div class="filter-controls">
                    <input type="date" id="dateFilter" class="filter-input date-input">
                    <label class="select-all-btn">
                        <input type="checkbox" id="selectAll">
                        <span>تحديد الكل</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- شريط العمليات الجماعية العائم --}}
        <div class="bulk-actions-bar" id="bulk-actions">
            <div class="bulk-info">
                <span class="count-badge" id="count">0</span>
                <span>عناصر مختارة</span>
            </div>
            <div class="bulk-btns">
                <button id="bulk-convert-btn" class="btn-bulk btn-convert-all">
                    <i class="fas fa-exchange-alt"></i> تحويل المحدد
                </button>
                <button id="bulk-delete-btn" class="btn-bulk btn-delete-all">
                    <i class="fas fa-trash"></i> حذف
                </button>
            </div>
        </div>

        <div class="timeline-container">
            @forelse ($drafts->groupBy(fn($date) => \Carbon\Carbon::parse($date->created_at)->format('Y-m-d')) as $day => $dayDrafts)
                <div class="day-group" data-date="{{ $day }}">
                    <div class="day-label">
                        <span class="date-text">
                            <i class="far fa-calendar-check"></i>
                            {{ \Carbon\Carbon::parse($day)->translatedFormat('l, d F Y') }}
                        </span>
                        <span class="day-count">{{ $dayDrafts->count() }} عمليات</span>
                    </div>

                    @foreach ($dayDrafts as $draft)
                        <div class="expense-card"
                            data-search="{{ strtolower($draft->user->name . ' ' . $draft->note . ' ' . $draft->estimated_amount) }}">

                            <div class="card-selection">
                                <div class="custom-checkbox">
                                    <input type="checkbox" class="draft-checkbox" id="check-{{ $draft->id }}"
                                        value="{{ $draft->id }}">
                                    <label for="check-{{ $draft->id }}"></label>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="card-top-row">
                                    <div class="user-meta">
                                        <span class="employee-name">{{ $draft->user->name ?? 'غير معروف' }}</span>
                                        <span class="entry-time"><i class="far fa-clock"></i>
                                            {{ $draft->created_at->format('g:i A') }}</span>
                                    </div>
                                    <div class="amount-display">
                                        <span class="amount-number">{{ number_format($draft->estimated_amount, 2) }}</span>
                                        <span class="currency-label">ج.م</span>
                                    </div>
                                </div>

                                <p class="expense-note">{{ $draft->note }}</p>

                                <div class="card-footer-actions">
                                    <form action="{{ route('expense-drafts.convert', $draft->id) }}" method="POST"
                                        class="convert-form single-form">
                                        @csrf
                                        <div class="action-grid">
                                            <select name="expense_type_id" class="form-select-custom" required>
                                                <option value="">نوع المصروف</option>
                                                @foreach ($types as $type)
                                                    <option value="{{ $type->id }}"
                                                        {{ $draft->expense_type_id == $type->id ? 'selected' : '' }}>
                                                        {{ $type->name }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <input type="number" step="0.01" name="amount"
                                                value="{{ $draft->estimated_amount }}" class="form-input-custom" required
                                                title="المبلغ الفعلي">

                                            <button type="submit" class="btn-action-submit">
                                                <i class="fas fa-check-circle"></i> تحويل
                                            </button>
                                        </div>
                                    </form>

                                    <button class="btn-delete-item" onclick="deleteSingle('{{ $draft->id }}')"
                                        title="حذف المسودة">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="empty-state-container">
                    <div class="empty-icon"><i class="fas fa-feather-alt"></i></div>
                    <p>لا توجد مسودات حالياً، كل الأوراق نظيفة!</p>
                </div>
            @endforelse
        </div>
    </div>

    <style>
        :root {
            --bg-page: #f4f7f6;
            --card-white: #ffffff;
            --primary-accent: #D9B1AB;
            /* وردي مكتوم */
            --primary-dark: #6d5653;
            --text-dark: #2d3436;
            --text-gray: #636e72;
            --border-soft: #e0e0e0;
            --danger: #ff7675;
            --success: #55efc4;
            --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        body {
            background-color: var(--bg-page);
            color: var(--text-dark);
        }

        .drafts-wrapper {
            padding: 20px;
            max-width: 950px;
            margin: 0 auto 120px auto;
        }

        /* Header & Search */
        .page-header-section {
            margin-bottom: 30px;
        }

        .page-title {
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .title-badge {
            font-size: 0.5em;
            background: var(--primary-accent);
            padding: 4px 12px;
            border-radius: 50px;
            color: #fff;
        }

        .search-filter-bar {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .search-wrapper {
            flex: 2;
            position: relative;
            min-width: 280px;
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
        }

        .filter-controls {
            display: flex;
            gap: 10px;
            flex: 1;
            min-width: 250px;
        }

        .filter-input {
            width: 100%;
            background: var(--card-white);
            border: 2px solid var(--border-soft);
            padding: 10px 40px 10px 15px;
            border-radius: 12px;
            transition: 0.3s;
        }

        .filter-input:focus {
            border-color: var(--primary-accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(217, 177, 171, 0.2);
        }

        .select-all-btn {
            background: var(--card-white);
            border: 2px solid var(--border-soft);
            padding: 8px 15px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            white-space: nowrap;
        }

        /* Timeline Labels */
        .day-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 35px 0 15px 0;
            padding: 0 10px;
            border-right: 4px solid var(--primary-accent);
        }

        .date-text {
            font-weight: 700;
            color: var(--primary-dark);
        }

        .day-count {
            font-size: 0.85rem;
            color: var(--text-gray);
            background: #eee;
            padding: 2px 10px;
            border-radius: 10px;
        }

        /* Expense Card */
        .expense-card {
            background: var(--card-white);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            gap: 20px;
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid transparent;
        }

        .expense-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.08);
            border-color: var(--primary-accent);
        }

        .card-top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .employee-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-dark);
            display: block;
        }

        .entry-time {
            font-size: 0.8rem;
            color: var(--text-gray);
        }

        .amount-display {
            background: #f8f1f0;
            padding: 8px 15px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid rgba(217, 177, 171, 0.3);
        }

        .amount-number {
            font-weight: 900;
            font-size: 1.2rem;
            color: var(--primary-dark);
        }

        .currency-label {
            font-size: 0.7rem;
            color: var(--text-gray);
        }

        .expense-note {
            color: var(--text-gray);
            line-height: 1.5;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        /* Forms in Card */
        .card-footer-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            border-top: 1px solid #f0f0f0;
            padding-top: 15px;
        }

        .convert-form {
            flex: 1;
        }

        .action-grid {
            display: flex;
            gap: 10px;
        }

        .form-select-custom,
        .form-input-custom {
            flex: 1;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid var(--border-soft);
            background: #fafafa;
            font-size: 0.9rem;
        }

        .btn-action-submit {
            background: var(--primary-accent);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-action-submit:hover {
            background: var(--primary-dark);
        }

        .btn-delete-item {
            background: #fff0f0;
            color: var(--danger);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-delete-item:hover {
            background: var(--danger);
            color: white;
        }

        /* Floating Bulk Actions */
        .bulk-actions-bar {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--text-dark);
            color: white;
            padding: 15px 30px;
            border-radius: 100px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
            justify-content: space-between;
            align-items: center;
            width: 90%;
            max-width: 550px;
        }

        .count-badge {
            background: var(--primary-accent);
            color: var(--primary-dark);
            padding: 2px 12px;
            border-radius: 20px;
            font-weight: 800;
            margin-left: 10px;
        }

        .btn-bulk {
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: bold;
            cursor: pointer;
            margin-right: 10px;
        }

        .btn-convert-all {
            background: var(--primary-accent);
            color: white;
        }

        .btn-delete-all {
            background: transparent;
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        /* Empty State */
        .empty-state-container {
            text-align: center;
            padding: 60px;
            color: var(--text-gray);
        }

        .empty-icon {
            font-size: 4rem;
            opacity: 0.2;
            margin-bottom: 20px;
        }

        /* Custom Checkbox */
        .draft-checkbox {
            transform: scale(1.3);
            accent-color: var(--primary-accent);
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .expense-card {
                flex-direction: column;
            }

            .action-grid {
                flex-direction: column;
            }

            .filter-controls {
                flex-direction: column;
            }

            .bulk-actions-bar {
                flex-direction: column;
                border-radius: 20px;
                gap: 15px;
            }
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // بحث متقدم
            $("#tableSearch").on("keyup", function() {
                let value = $(this).val().toLowerCase();
                $(".expense-card").each(function() {
                    let text = $(this).data('search').toString().toLowerCase();
                    $(this).toggle(text.indexOf(value) > -1);
                });
                checkEmptyGroups();
            });

            // فلتر تاريخ
            $("#dateFilter").on("change", function() {
                let selected = $(this).val();
                if (!selected) {
                    $(".day-group").show();
                } else {
                    $(".day-group").hide();
                    $(`.day-group[data-date="${selected}"]`).show();
                }
            });

            // اختيار الكل
            $('#selectAll').on('change', function() {
                $('.draft-checkbox:visible').prop('checked', this.checked).trigger('change');
            });

            $(document).on('change', '.draft-checkbox', function() {
                let checkedCount = $('.draft-checkbox:checked').length;
                if (checkedCount > 0) {
                    $('#bulk-actions').css('display', 'flex').fadeIn();
                    $('#count').text(checkedCount);
                } else {
                    $('#bulk-actions').fadeOut();
                    $('#selectAll').prop('checked', false);
                }
            });

            // حذف جماعي
            $('#bulk-delete-btn').on('click', function() {
                let ids = $('.draft-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();
                confirmAction(`حذف ${ids.length} عناصر نهائياً؟`, '#ff7675', () => {
                    $.post("{{ route('expense-drafts.bulk-delete') }}", {
                        _token: "{{ csrf_token() }}",
                        ids: ids
                    }, () => location.reload());
                });
            });

            // تحويل جماعي
            $('#bulk-convert-btn').on('click', function() {
                let selected = $('.draft-checkbox:checked');
                confirmAction(`اعتماد ${selected.length} مصاريف بشكل نهائي؟`, '#D9B1AB', () => {
                    selected.each(function() {
                        $(this).closest('.expense-card').find('.single-form').submit();
                    });
                });
            });

            function confirmAction(title, color, callback) {
                Swal.fire({
                    title: title,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: color,
                    confirmButtonText: 'نعم، متأكد',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if (result.isConfirmed) callback();
                });
            }

            window.deleteSingle = function(id) {
                confirmAction('حذف هذه المسودة؟', '#ff7675', () => {
                    $.post("{{ route('expense-drafts.bulk-delete') }}", {
                        _token: "{{ csrf_token() }}",
                        ids: [id]
                    }, () => location.reload());
                });
            };

            function checkEmptyGroups() {
                $(".day-group").each(function() {
                    $(this).toggle($(this).find(".expense-card:visible").length > 0);
                });
            }
        });
    </script>
@endsection
