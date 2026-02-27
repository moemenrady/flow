{{-- resources/views/admin/day_shifts.blade.php --}}
@extends('layouts.app_page')

@section('content')

    <div class="add-expense-wrapper" style="padding-bottom:40px;">

        <h2 class="page-title">{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}</h2>

        {{-- Alerts --}}
        @if (session('success'))
            <script>
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                    background: '#fff',
                    color: '#333',
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                });
            </script>
        @endif

        {{-- الملخص الكلي --}}
        <div class="form-container animate__animated animate__fadeInUp" style="max-width:1100px; margin: 0 auto 20px;">
            <div style="display:flex; gap:18px; flex-wrap:wrap; justify-content:space-between; align-items:center;">
                <div style="flex:1; min-width:200px;">
                    <h4 style="margin:0 0 8px;">💰 الإيراد الكلي</h4>

                    <div style="font-size:20px; font-weight:700;">
                        {{ number_format($total_income, 2) }} ج.م
                        <small style="font-size:14px; color:#555; font-weight:400;">
                            (كاش: {{ number_format($totalCash, 2) }} - محفظة: {{ number_format($totalDigital, 2) }})
                        </small>
                    </div>
                </div>

                <div style="flex:1; min-width:200px;">
                    <h4 style="margin:0 0 8px;">🧾 المصروف الكلي</h4>
                    <div style="font-size:20px; font-weight:700;">{{ number_format($total_expense, 2) }} ج.م</div>
                </div>
                <div style="flex:1; min-width:200px; text-align:right;">
                    <h4 style="margin:0 0 8px;">🏁 الصافي الكلي</h4>
                    <div style="font-size:20px; font-weight:700;">{{ number_format($total_net, 2) }} ج.م</div>
                </div>
            </div>
        </div>

        {{-- جدول الشيفتات --}}
        <div class="form-container animate__animated animate__fadeInUp" style="max-width:1100px; margin: 0 auto;">
            @if ($shifts->isEmpty())
                <div class="alert alert-warning">❌ لا توجد شيفتات في هذا اليوم</div>
            @else
                <table class="actions-table" style="width:100%; margin-bottom:0;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الموظف</th>
                            <th>بداية</th>
                            <th>نهاية</th>
                            <th>المدة</th>
                            <th>الإيراد</th>
                            <th>المصروف</th>
                            <th>الصافي</th>
                            <th>تفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($shifts as $shift)
                            @php
                                $net = $shift->total_amount - $shift->total_expense;

                                // نحصل على الدقائق كعدد صحيح (بدون كسور)
                                // نفرض أن $shift->duration معطاة بالدقائق (لو عندك بالثواني عدل السطر ليحول الثواني لمجموع دقائق)
                                if (!empty($shift->duration)) {
                                    // تحويل إلى عدد دقائق صحيح (نأخذ floor لإلغاء الكسور)
                                    $totalMinutes = (int) floor($shift->duration);

                                    // لو كانت القيمة الفعلية موجبة لكن floor أعاد 0 (مثلاً 0.3 دقيقة) نعرض دقيقة واحدة على الأقل
                                    if ($totalMinutes <= 0 && $shift->duration > 0) {
                                        $totalMinutes = 1;
                                    }

                                    if ($totalMinutes < 60) {
                                        $durationText = $totalMinutes . ' دقيقة';
                                    } else {
                                        $hours = intdiv($totalMinutes, 60);
                                        $minutes = $totalMinutes % 60;

                                        $durationText = $hours . ' ساعة';
                                        if ($minutes > 0) {
                                            $durationText .= ' ' . $minutes . ' دقيقة';
                                        }
                                    }
                                } else {
                                    $durationText = '—';
                                }
                            @endphp
                            @php
                                // تحديد هل المستخدم أدمن
                                $isAdmin = false;
                                $userRole = null;
                                if ($shift->user) {
                                    // لو فيه method hasRole استخدمها وإلا اعتمد على الحقل role
                                    if (method_exists($shift->user, 'hasRole')) {
                                        $isAdmin = $shift->user->hasRole('admin');
                                        $userRole = $shift->user->role ?? null;
                                    } else {
                                        $userRole = $shift->user->role ?? null;
                                        $isAdmin = $userRole === 'admin';
                                    }
                                }
                                // إختيار الأيقونة
                                $badgeEmoji = $isAdmin ? '👑' : '🧳';
                                $badgeTitle = $isAdmin ? 'إدارة' : 'موظف';
                            @endphp

                            <tr>
                                <td data-label="#"> {{ $shift->id }} </td>

                                {{-- الموظف: نعرض البادج ثم الاسم --}}
                                <td data-label="الموظف" class="shift-user-cell">
                                    {{-- البادج (سيُعرض فوق الكارت في الموبايل وبجانبه في الديسكتوب) --}}
                                    @if ($shift->user)
                                        <span class="shift-badge" title="{{ $badgeTitle }}"
                                            aria-hidden="false">{{ $badgeEmoji }}</span>
                                        <span class="shift-user-name">{{ $shift->user->name }}</span>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td data-label="بداية">
                                    {{ \Carbon\Carbon::parse($shift->created_at)->format('Y-m-d h:i A') }}
                                </td>
                                <td data-label="نهاية">
                                    {{ $shift->updated_at ? \Carbon\Carbon::parse($shift->updated_at)->format('Y-m-d h:i A') : '—' }}
                                </td>
                                <td data-label="المدة">{{ $durationText }}</td>
                                <td data-label="الإيراد">{{ number_format($shift->total_amount, 2) }}</td>
                                <td data-label="المصروف">
                                    @if (!$isAdmin)
                                        {{-- موظف → زرار outline أحمر --}}
                                        <a href="{{ route('admin_draft.create') }}"
                                            class="btn btn-sm fw-bold btn-outline-danger">
                                            {{ number_format($shift->total_expense, 2) }} ج.م
                                        </a>
                                    @else
                                        {{-- أدمن → نص فقط --}}
                                        {{ number_format($shift->total_expense, 2) }} ج.م
                                    @endif
                                </td>



                                <td data-label="الصافي">{{ number_format($net, 2) }}</td>
                                <td data-label="تفاصيل">
                                    <a href="{{ route('shift.show', $shift->id) }}" class="btn-details"
                                        style="text-decoration:none;padding:6px 10px;border-radius:8px;background:#D9B1AB;color:#fff;font-weight:700;">عرض</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    </div>

    {{-- Styles (مطابقة الستيل العام والأنيميشن) --}}
    <style>
        body {
            font-family: "Cairo", sans-serif;
            background: #F2F2F2;
            margin: 0;
            padding: 70px;
            color: #333;

        }


        /* نضع ال row بالنسبة النسبية ليعمل badge على الموبايل فوق الكارت */
        @media (max-width:768px) {
            tbody tr {
                position: relative;
            }

            /* البادج على الموبايل: فوق يمين الكارت (صفحة RTL) */
            .shift-badge {
                position: absolute;
                top: -10px;
                right: 12px;
                /* RTL: نعرضها على اليمين */
                background: #fff;
                padding: 6px 8px;
                border-radius: 12px;
                box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
                font-size: 16px;
                line-height: 1;
            }

            .shift-user-cell .shift-user-name {
                display: block;
                margin-top: 6px;
                /* لترك مسافة تحت البادج */
            }
        }

        /* على الديسكتوب: نجعل البادج inline بجانب الاسم */
        @media (min-width:769px) {
            .shift-badge {
                position: static;
                display: inline-block;
                margin-left: 8px;
                /* عند RTL هذا يضعها قبل الاسم بصريًا */
                margin-right: 0;
                background: transparent;
                padding: 0;
                box-shadow: none;
                font-size: 18px;
                vertical-align: middle;
            }

            /* نجعل اسم المستخدم يظهر عادي */
            .shift-user-cell {
                white-space: nowrap;
            }
        }


        .page-title {
            font-size: 28px;
            margin-bottom: 18px;
            color: #444;
            text-align: center;
        }

        .form-container {
            background: #fff;
            padding: 25px;
            border-radius: 18px;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .actions-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }

        .actions-table th,
        .actions-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .actions-table thead th {
            background: #f9f9f9;
            font-weight: 700;
            color: #444;
        }

        .btn-details {
            transition: transform .15s ease;
        }

        .btn-details:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08);
        }

        /* responsive cards on mobile */
        @media (max-width:768px) {

            .actions-table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead {
                display: none;
            }

            tbody tr {
                margin-bottom: 12px;
                border: 1px solid #eee;
                border-radius: 8px;
                padding: 10px;
                background: #fff;
            }

            tbody td {
                padding: 6px 10px;
                position: relative;
                text-align: right;
            }

            tbody td::before {
                content: attr(data-label);
                font-weight: 700;
                color: #666;
                position: absolute;
                left: 10px;
            }
        }
    </style>
@endsection
