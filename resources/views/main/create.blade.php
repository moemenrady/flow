@extends('layouts.app')

@section('page_title', 'الصفحة الرئيسية')

@section('content')
<main class="main-dashboard">

  <section class="dashboard-grid">

    <form action="{{ route('session.index-manager') }}">
      <button class="dashboard-card w-100">
        <img src="https://img.icons8.com/ios/64/user-group-man-man.png">
        <span>إدارة الجلسات</span>
      </button>
    </form>

    {{-- <form action="{{ route('bookings.index-manager') }}">
      <button class="dashboard-card w-100">
        <img src="https://img.icons8.com/ios/64/calendar.png">
        <span>إدارة الحجوزات</span>
      </button>
    </form> --}}

    <form action="{{ route('subscriptions.index-manager') }}">
      <button class="dashboard-card w-100">
        <img src="https://img.icons8.com/ios/64/conference.png">
        <span>المشتركين</span>
      </button>
    </form>

    <form action="{{ route('sale_proccess.create') }}">
      <button class="dashboard-card w-100">
        <img src="https://img.icons8.com/ios/64/sell.png">
        <span>بدء عملية بيع</span>
      </button>
    </form>

    <form action="{{ route('invoice.index') }}">
      <button class="dashboard-card w-100 position-relative">
        <img src="https://img.icons8.com/ios/64/bill.png">
        <span>الفواتير</span>
      </button>
    </form>

    <form action="{{ route('admin.calendar') }}">
      <button class="dashboard-card w-100 position-relative">
        <img src="https://img.icons8.com/ios/64/bill.png">
        <span>حساب الموظفين</span>
      </button>
    </form>
    {{-- جلسات لم تسجل --}}
    <form action="{{ route('new-session.index') }}">
      <button class="dashboard-card w-100 position-relative">
        <img src="https://img.icons8.com/ios/64/barcode.png">
        <span>جلسات لم تُسجل</span>

        @if($newSessions?->count())
          <span class="badge-new-sessions">
            {{ $newSessions->count() }}
          </span>
        @endif
      </button>
    </form>

  </section>

</main>
@endsection
