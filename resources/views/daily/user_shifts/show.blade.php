@extends('layouts.app_page')

@section('content')
    <div class="shift-details-wrapper" style="padding:30px 18px;">

        <div class="form-container animate__animated animate__fadeInUp" style="max-width:1200px; margin:0 auto;">
            <div class="top-row">
                <div class="top-left">
                    <h2 class="shift-title">🧾 تفاصيل الشيفت #{{ $shift->id }}</h2>
                    <div class="shift-meta">
                        الموظف: <strong>{{ $shift->user?->name ?? '—' }}</strong>
                        • بداية:
                        <strong>{{ $shift->start_time ? \Carbon\Carbon::parse($shift->start_time)->format('Y-m-d h:i A') : \Carbon\Carbon::parse($shift->created_at)->format('Y-m-d h:i A') }}</strong>
                        • نهاية:
                        <strong>{{ $shift->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('Y-m-d h:i A') : '—' }}</strong>
                    </div>
                </div>

                <div class="top-right">
                    <div class="summary-row">
                        <div class="summary-pill">
                            <div class="small-title">الإيراد</div>
                            <div class="big-num">{{ number_format($totalIncome, 2) }} ج.م</div>
                        </div>

                        <div class="summary-pill">
                            <div class="small-title">المصروف</div>
                            <div class="big-num">{{ number_format($totalExpense, 2) }} ج.م</div>
                        </div>

                        <div class="summary-pill summary-net">
                            <div class="small-title">الصافي</div>
                            <div class="big-num">{{ number_format($totalNet, 2) }} ج.م</div>
                        </div>

                        <div class="summary-pill actions-pill">
                            <button id="exportCsv" class="btn export-btn" title="تصدير CSV">تصدير CSV</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Controls: بحث وفلتر --}}
        <div class="form-container animate__animated animate__fadeInUp" style="max-width:1200px; margin:16px auto;">
            <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                <input id="searchInput" type="text" placeholder="ابحث عن نوع الحركة، رقم الفاتورة، ملاحظة..."
                    class="input-search">
                <select id="filterType" class="input-select">
                    <option value="">كل الأنواع</option>
                    <option value="new_subscription">new_subscription</option>
                    <option value="renew_subscription">renew_subscription</option>
                    <option value="end_session">end_session</option>
                    <option value="separate_sale">separate_sale</option>
                    <option value="add_booking">add_booking</option>
                    <option value="end_booking">end_booking</option>
                    <option value="expense_note">expense_note</option>
                </select>

                <div style="margin-left:auto; display:flex; gap:8px;">
                    <button id="toggleCollapse" class="btn btn-outline">طي/توسيع كل الحركات</button>
                </div>
            </div>
        </div>

        {{-- قائمة الحركات --}}
        <div class="form-container animate__animated animate__fadeInUp" style="max-width:1200px; margin:0 auto 40px;">
            @if ($actions->isEmpty())
                <div class="alert alert-warning">لا توجد حركات لهذا الشيفت</div>
            @else
                <ul id="actionsList" class="actions-list">
                    @foreach ($actions as $action)
                        <li class="action-item" data-id="{{ $action->id }}" data-action-type="{{ $action->action_type }}"
                            data-search="{{ implode(' ', [$action->action_type, $action->invoice?->id, $action->expenseDraft?->id, $action->notes]) }}">
                            <div class="action-head">
                                <div class="left">
                                    <div class="action-icon">{!! actionIcon($action->action_type) !!}</div>
                                    <div class="action-meta">
                                        <div class="action-type">{{ humanizeAction($action->action_type) }}</div>
                                        <div class="action-time">
                                            {{ \Carbon\Carbon::parse($action->created_at)->format('Y-m-d h:i A') }}</div>
                                    </div>
                                </div>

                                <div class="right">
                                    <div class="amounts">
                                        <div class="amount">+ {{ number_format($action->amount ?? 0, 2) }} ج.م</div>
                                        <div class="expense">- {{ number_format($action->expense_amount ?? 0, 2) }} ج.م
                                        </div>
                                    </div>
                                    <button class="btn-toggle-details" aria-expanded="false">تفاصيل ▾</button>
                                </div>
                            </div>

                            <div class="action-body" style="display:none;">
                                <div class="grid-two">
                                    <div>
                                        <strong>نوع الحركة:</strong> {{ $action->action_type }}
                                    </div>
                                    <div>
                                        <strong>فاتورة مرتبطة:</strong>
                                        @if ($action->invoice)
                                            <a
                                                href="{{ route('invoices.client.show', $action->invoice->id) }}">#{{ $action->invoice->id }}</a>
                                        @else
                                            —
                                        @endif
                                    </div>

                                    <div>
                                        <strong>المبلغ:</strong> {{ number_format($action->amount ?? 0, 2) }} ج.م
                                    </div>

                                    <div style="grid-column: 1 / -1;">
                                        <strong>ملاحظات:</strong>
                                        <div class="notes">{!! $action->notes ? nl2br(e($action->notes)) : '—' !!}</div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>

    {{-- CSS محلي للصفحة (responsive tweaks) --}}
    <style>
        .top-row {
            display: flex;
            gap: 16px;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .top-left {
            flex: 1 1 320px;
            min-width: 230px;
        }

        .top-right {
            flex: 0 1 640px;
            min-width: 260px;
        }

        .shift-title {
            margin: 0;
            font-size: 22px;
        }

        .shift-meta {
            color: #666;
            margin-top: 6px;
            font-size: 14px;
        }

        .summary-row {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .summary-pill {
            background: #fff;
            border-radius: 12px;
            padding: 10px 14px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
            text-align: center;
            min-width: 130px;
            flex: 0 1 180px;
        }

        .summary-pill .small-title {
            font-size: 12px;
            color: #666;
        }

        .summary-pill .big-num {
            font-weight: 800;
            font-size: 16px;
            margin-top: 6px;
        }

        .summary-net {
            background: linear-gradient(90deg, #8FD3A6, #66C2A2);
            color: #fff;
        }

        .actions-pill {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .export-btn {
            background: #D9B1AB;
            border: none;
            color: #fff;
            padding: 8px 12px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
        }

        .input-search {
            flex: 1;
            min-width: 220px;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #e6e6e6;
        }

        .input-select {
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #e6e6e6;
        }

        .actions-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .action-item {
            background: #fff;
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .action-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .action-head .left {
            display: flex;
            gap: 12px;
            align-items: center;
            min-width: 0;
        }

        .action-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F7F7F7;
            font-size: 18px;
            flex: 0 0 44px;
        }

        .action-meta {
            min-width: 0;
            overflow: hidden;
        }

        .action-type {
            font-weight: 700;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            max-width: 200px;
        }

        .action-time {
            color: #888;
            font-size: 13px;
        }

        .amounts {
            text-align: right;
            min-width: 140px;
        }

        .amount {
            color: #2b8a3e;
            font-weight: 800;
        }

        .expense {
            color: #c0392b;
            font-weight: 700;
            font-size: 13px;
        }

        .btn-toggle-details {
            background: transparent;
            border: none;
            color: #666;
            cursor: pointer;
            font-weight: 700;
        }

        .action-body {
            margin-top: 10px;
            border-top: 1px dashed #eee;
            padding-top: 10px;
            animation: fadeIn .18s ease;
        }

        .grid-two {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            align-items: start;
        }

        .notes {
            margin-top: 6px;
            color: #333;
            white-space: pre-wrap;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @media (max-width: 900px) {
            .top-right {
                flex-basis: 100%;
                order: 2;
            }

            .top-left {
                flex-basis: 100%;
                order: 1;
                margin-bottom: 10px;
            }

            .summary-row {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
                align-items: stretch;
            }

            .summary-pill {
                flex: none;
                min-width: 0;
            }

            .actions-pill {
                align-self: center;
            }
        }

        @media (max-width: 480px) {
            .summary-row {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }

            .shift-title {
                font-size: 18px;
            }

            .shift-meta {
                font-size: 13px;
            }

            .action-type {
                max-width: 120px;
            }

            .amounts {
                min-width: 90px;
                text-align: left;
            }

            .grid-two {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 420px) {
            .form-container {
                padding-left: 8px;
                padding-right: 8px;
            }
        }
    </style>

    {{-- helpers (موجودين في الأصل) --}}
    @php
        function humanizeAction($type)
        {
            return match ($type) {
                'new_subscription' => 'اشتراك جديد',
                'renew_subscription' => 'تجديد اشتراك',
                'end_session' => 'انهاء جلسة',
                'separate_sale' => 'بيع منفصل',
                'add_booking' => 'اضافة حجز',
                'end_booking' => 'انهاء حجز',
                'expense_note' => 'مذكرة مصروف',
                default => $type,
            };
        }

        function actionIcon($type)
        {
            return match ($type) {
                'new_subscription'
                    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v6"></path><path d="M20 12H4"></path><path d="M12 22v-6"></path></svg>',
                'renew_subscription'
                    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 12a9 9 0 1 0-3 6.7"></path><path d="M21 12v6h-6"></path></svg>',
                'end_session'
                    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><path d="M16 17l5-5-5-5"></path></svg>',
                'separate_sale'
                    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 6h18"></path><path d="M8 6v14"></path></svg>',
                'add_booking'
                    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4"></path></svg>',
                'end_booking'
                    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 15v4a2 2 0 0 1-2 2H5"></path><path d="M7 10l5 5 5-5"></path></svg>',
                'expense_note'
                    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 10v6a2 2 0 0 1-2 2H5"></path><path d="M7 10V6a2 2 0 0 1 2-2h6"></path></svg>',
                default
                    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="10"/></svg>',
            };
        }
    @endphp

    {{-- JS: فلترة، طي/توسيع، تصدير CSV --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const actionsList = document.getElementById('actionsList');
            if (!actionsList) return;

            const searchInput = document.getElementById('searchInput');
            const filterType = document.getElementById('filterType');
            const toggleCollapse = document.getElementById('toggleCollapse');
            const exportCsv = document.getElementById('exportCsv');

            document.querySelectorAll('.btn-toggle-details').forEach(btn => {
                btn.addEventListener('click', () => {
                    const item = btn.closest('.action-item');
                    const body = item.querySelector('.action-body');
                    const expanded = btn.getAttribute('aria-expanded') === 'true';
                    if (expanded) {
                        body.style.display = 'none';
                        btn.setAttribute('aria-expanded', 'false');
                        btn.textContent = 'تفاصيل ▾';
                    } else {
                        body.style.display = 'block';
                        btn.setAttribute('aria-expanded', 'true');
                        btn.textContent = 'تفاصيل ▴';
                    }
                });
            });

            let allExpanded = false;
            toggleCollapse.addEventListener('click', () => {
                allExpanded = !allExpanded;
                document.querySelectorAll('.action-item .action-body').forEach(b => {
                    b.style.display = allExpanded ? 'block' : 'none';
                });
                document.querySelectorAll('.btn-toggle-details').forEach(btn => {
                    btn.setAttribute('aria-expanded', allExpanded ? 'true' : 'false');
                    btn.textContent = allExpanded ? 'تفاصيل ▴' : 'تفاصيل ▾';
                });
            });

            function applyFilter() {
                const q = (searchInput.value || '').trim().toLowerCase();
                const t = (filterType.value || '').trim();

                document.querySelectorAll('.action-item').forEach(item => {
                    const text = (item.dataset.search || '').toLowerCase();
                    const type = (item.dataset.actionType || '');
                    const matchesSearch = q === '' || text.includes(q);
                    const matchesType = t === '' || type === t;
                    item.style.display = (matchesSearch && matchesType) ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', applyFilter);
            filterType.addEventListener('change', applyFilter);

            exportCsv.addEventListener('click', () => {
                const rows = [];
                rows.push(['id', 'type', 'time', 'amount', 'expense_amount', 'invoice_id',
                    'expense_draft_id', 'notes'
                ]);

                document.querySelectorAll('.action-item').forEach(item => {
                    if (item.style.display === 'none') return;
                    const id = item.dataset.id ?? '';
                    const type = item.dataset.actionType || '';
                    const time = item.querySelector('.action-time') ? item.querySelector(
                        '.action-time').textContent.trim() : '';
                    const amount = item.querySelector('.amount') ? item.querySelector('.amount')
                        .textContent.replace(/[^\d\.\-]/g, '').trim() : '';
                    const expense = item.querySelector('.expense') ? item.querySelector('.expense')
                        .textContent.replace(/[^\d\.\-]/g, '').trim() : '';
                    const invoiceLink = item.querySelector('a[href*="/invoices/"]');
                    const invoiceId = invoiceLink ? invoiceLink.textContent.replace('#', '')
                    .trim() : '';
                    const expenseLink = item.querySelector('a[href*="/expense_drafts/"]');
                    const expenseDraftId = expenseLink ? expenseLink.textContent.replace('#', '')
                        .trim() : '';
                    const notesEl = item.querySelector('.notes');
                    const notes = notesEl ? notesEl.textContent.replace(/\s+/g, ' ').trim() : '';

                    rows.push([id, type, time, amount, expense, invoiceId, expenseDraftId, notes]);
                });

                const csvContent = rows.map(r => r.map(c => `"${String(c).replace(/"/g,'""')}"`).join(','))
                    .join('\n');
                const blob = new Blob([csvContent], {
                    type: 'text/csv;charset=utf-8;'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `shift_{{ $shift->id }}_actions.csv`;
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);
            });
        });
    </script>

@endsection
