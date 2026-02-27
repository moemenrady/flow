@extends('layouts.app_page')


@section('content')
<div class="subscription-container">

    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", () =>
                showSnackbar("{{ session('success') }}", "success")
            );
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", () =>
                showSnackbar("{{ session('error') }}", "error")
            );
        </script>
    @endif

    <div class="card">
        <div class="card-header">
            <h2>✏️ تعديل مصروف</h2>
            <span class="badge">#{{ $expense->id }}</span>
        </div>

        <form action="{{ route('expenses.update', $expense->id) }}" method="POST" autocomplete="off">
            @csrf
            @method('PUT')

            {{-- بيانات المصروف --}}
            <div class="section">
                <h3>💰 بيانات المصروف</h3>

                <div class="box">

                    {{-- نوع المصروف --}}
                    <div class="mb-3">
                        <label class="form-label">نوع المصروف</label>
                        <select name="expense_type_id" class="form-control" required>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}"
                                    {{ $expense->expense_type_id == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- المبلغ --}}
                    <div class="mb-3">
                        <label class="form-label">المبلغ</label>
                        <input type="number" step="0.01"
                               name="amount"
                               class="form-control"
                               value="{{ old('amount', $expense->amount) }}"
                               required>
                    </div>

                    {{-- الملاحظة --}}
                    <div class="mb-3">
                        <label class="form-label">ملاحظة</label>
                        <textarea name="note"
                                  class="form-control"
                                  rows="3"
                                  placeholder="ملاحظة اختيارية">{{ old('note', $expense->note) }}</textarea>
                    </div>

                    {{-- تاريخ ووقت المصروف --}}
                    <div class="mb-3">
                        <label class="form-label">📅 تاريخ ووقت المصروف</label>
                        <input type="datetime-local"
                               name="expense_time"
                               class="form-control"
                               value="{{ old('expense_time', $expense->created_at->format('Y-m-d\TH:i')) }}">
                    </div>

                </div>
            </div>

            {{-- أزرار التحكم --}}
            <div class="actions">
                <button type="submit" class="btn yellow">
                    💾 حفظ التعديلات
                </button>

                <a href="{{ route('expenses.index') }}" class="btn">
                    إلغاء
                </a>
            </div>

        </form>
    </div>
</div>
@endsection


@section('style')
<style>
    body {
        background: #fafafa;
        font-family: "Tahoma", sans-serif;
    }

    .subscription-container {
        max-width: 720px;
        margin: 40px auto;
        padding: 20px;
    }

    .card {
        background: #fff;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        animation: fadeInUp .6s ease;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #f1f1f1;
        margin-bottom: 20px;
        padding-bottom: 10px;
    }

    .card-header h2 {
        font-size: 26px;
        margin: 0;
    }

    .badge {
        background: #D9B1AB;
        color: #fff;
        padding: 6px 15px;
        border-radius: 30px;
        font-weight: bold;
    }

    .section h3 {
        color: #a86f68;
        font-size: 20px;
        margin-bottom: 10px;
    }

    .box {
        background: #fafafa;
        padding: 18px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
    }

    .form-control {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #ddd;
        box-sizing: border-box;
        font-size: 15px;
    }

    .form-label {
        margin-bottom: 6px;
        font-weight: bold;
        display: block;
    }

    .actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .btn {
        border: none;
        padding: 12px 18px;
        border-radius: 12px;
        font-weight: bold;
        cursor: pointer;
        transition: .3s;
        font-size: 15px;
        background: #eee;
    }

    .btn.yellow {
        background: #ffe483;
        border: 1px solid #f2d35e;
    }

    .btn:hover {
        opacity: .9;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection
