@extends('layouts.app_page')

@section('title', 'كشف حساب: ' . $employee->name)

@section('content')
<div class="container mx-auto p-2 md:p-4 max-w-5xl">
    
    <div class="row g-3 mb-4 animate__animated animate__fadeInDown">
        <div class="col-md-4">
            <div class="card p-4 h-100 border-0" style="background: linear-gradient(145deg, #1a1a1d, #25252a);">
                <h4 class="text-warning mb-1">{{ $employee->name }}</h4>
                <p class="text-dim small mb-3">موظف | {{ $employee->phone }}</p>
                <div class="d-flex gap-2">
                    <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-outline-secondary">تعديل</a>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card p-4 h-100">
                <div class="row text-center g-3">
                    <div class="col-4">
                        <div class="text-dim small mb-1">الراتب الأساسي</div>
                        <div class="h5 text-white">{{ number_format($employee->salary) }}</div>
                    </div>
                    <div class="col-4 border-start border-end border-secondary">
                        <div class="text-danger small mb-1">إجمالي الخصومات</div>
                        <div class="h5 text-danger">- {{ number_format($totalAdvances + $totalPurchases) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-success small mb-1">الصافي (هذا الشهر)</div>
                        <div class="h5 text-success">{{ number_format($netSalary) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="title mt-5 mb-3 animate__animated animate__fadeInLeft" style="font-size: 1.2rem;">
        <i class="fas fa-calendar-check me-2 text-accent"></i> تفاصيل شهر {{ now()->translatedFormat('F Y') }}
    </div>

    @php
        $groupedByDay = $currentTransactions->groupBy(fn($date) => $date->created_at->format('Y-m-d'));
    @endphp

    <div class="space-y-4 mb-5">
        @forelse($groupedByDay as $day => $txs)
            <div class="card p-0 overflow-hidden animate__animated animate__fadeInUp">
                <div class="bg-dark p-2 px-3 border-bottom border-secondary d-flex justify-content-between align-items-center">
                    <span class="text-accent small"><i class="far fa-clock me-1"></i> {{ $day }}</span>
                    <span class="badge bg-secondary">{{ $txs->count() }} عملية</span>
                </div>
                <table class="table table-dark table-hover mb-0 small">
                    <tbody>
                        @foreach($txs as $tx)
                        <tr>
                            <td style="width: 50px">
                                @if($tx->type == 'advance')
                                    <i class="fas fa-hand-holding-usd text-warning"></i>
                                @else
                                    <i class="fas fa-shopping-cart text-info"></i>
                                @endif
                            </td>
                            <td>{{ $tx->type == 'advance' ? 'سلفة مالية' : 'مشتريات منتجات' }}</td>
                            <td class="text-dim">{{ $tx->notes ?? '-' }}</td>
                            <td class="text-end text-white font-monospace">{{ number_format($tx->amount, 2) }} ج.م</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <div class="text-center p-5 card text-dim">لا توجد مسحوبات لهذا الشهر حتى الآن</div>
        @endforelse
    </div>

    <div class="title mt-5 mb-3" style="font-size: 1.2rem;">
        <i class="fas fa-history me-2 text-dim"></i> السجل التاريخي (الشهور السابقة)
    </div>
    
    <div class="accordion border-0" id="historyAccordion">
        @foreach($history as $month => $txs)
            @if($month != now()->format('Y-m'))
            <div class="accordion-item bg-transparent border-0 mb-2">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed card py-3 shadow-none text-white" type="button" 
                            data-bs-toggle="collapse" data-bs-target="#month-{{ $month }}" 
                            style="background: #1a1a1d !important;">
                        شهر {{ $month }} 
                        <span class="ms-auto me-3 badge bg-dark border border-secondary">خصومات: {{ $txs->sum('amount') }} ج.م</span>
                    </button>
                </h2>
                <div id="month-{{ $month }}" class="accordion-collapse collapse" data-bs-parent="#historyAccordion">
                    <div class="accordion-body card mt-1 p-0 overflow-hidden">
                        <table class="table table-dark table-sm mb-0">
                            <thead class="small text-dim">
                                <tr>
                                    <th>التاريخ</th>
                                    <th>النوع</th>
                                    <th class="text-end">المبلغ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($txs as $tx)
                                <tr class="small">
                                    <td>{{ $tx->created_at->format('d/m') }}</td>
                                    <td>{{ $tx->type }}</td>
                                    <td class="text-end font-monospace">{{ $tx->amount }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    </div>
</div>
@endsection