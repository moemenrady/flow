@extends('layouts.app_page')

@section('title', 'زيارات الاشتراك #' . $subscription->id)

@section('content')
    <div class="subscription-container simple-padding">
        <div class="card compact-card">

            {{-- عنوان بسيط --}}
            <div class="card-header simple-header">
                <h2>📑 قائمة زيارات الاشتراك العميل <strong>{{ $subscription->client->name }}</strong></h2>
            </div>

            {{-- القائمة فقط --}}
            <div class="section">
                @if ($visits->count() === 0)
                    <div class="box empty-box">
                        لا توجد زيارات لهذا الاشتراك.
                    </div>
                @else
                    <div class="visits-grid">
                        @foreach ($visits as $v)
                          <div class="visit-mini-card">

    {{-- دائرة الأيقونة --}}
    <div class="visit-icon">
        🏷️
    </div>

    {{-- تفاصيل مختصرة --}}
    <div class="visit-info">
        @php
            $formatted = $v->checked_in_at?->format('Y-m-d g:i a') ?? '-';
        @endphp

        <div class="visit-time">{{ $formatted }}</div>
        <div class="visit-by">سجل بواسطة: {{ $v->creator?->name ?? '—' }}</div>
    </div>

    {{-- زر حذف --}}
    <form action="{{ route('sub.delete-visit', $v->id) }}" method="POST"
          onsubmit="return confirm('هل أنت متأكد من حذف الزيارة؟');">
        @csrf
        @method('DELETE')

        <button class="delete-btn" title="حذف الزيارة">✖</button>
    </form>

</div>

                        @endforeach
                    </div>

                  
                @endif
            </div>

            <div id="visit-toast" class="snackbar" aria-hidden="true"><i>ℹ️</i><span id="visit-toast-text"></span></div>
        </div>
    </div>
    @parent
    <script>
        (function() {
            const checkoutBase = "{{ url('subscription-visits') }}";
            const toast = document.getElementById('visit-toast');
            const toastText = document.getElementById('visit-toast-text');

            function showTempToast(msg, type = 'info') {
                toastText.innerText = msg;
                toast.classList.add('show');
                if (type === 'error') toast.classList.add('error');
                else toast.classList.remove('error');
                setTimeout(() => toast.classList.remove('show'), 1600);
            }

            document.querySelectorAll('.checkout-btn').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const id = this.dataset.id;
                    this.disabled = true;
                    const orig = this.innerHTML;
                    this.innerHTML = 'جاري الختم...';

                    try {
                        const resp = await fetch(`${checkoutBase}/${id}/checkout`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });
                        const data = await resp.json();
                        if (resp.ok && data.success) {
                            showTempToast(data.message || 'تم الختم');
                            setTimeout(() => location.reload(), 400);
                        } else {
                            showTempToast(data.message || 'خطأ', 'error');
                            this.disabled = false;
                            this.innerHTML = orig;
                        }
                    } catch (err) {
                        console.error(err);
                        showTempToast('خطأ في الاتصال', 'error');
                        this.disabled = false;
                        this.innerHTML = orig;
                    }
                });
            });

            // expose for copy button
            window.showTempToast = showTempToast;
        })();
    </script>
@endsection

@section('style')
    @parent
    <style>
        /* اكبر بادنج على الأطراف ليشعر بالراحة */
        .subscription-container.simple-padding {
            max-width: 1100px;
            margin: 28px auto;
            padding: 0 40px;
            /* مساحة على الجانبين */
        }

        @media (max-width:900px) {
            .subscription-container.simple-padding {
                padding: 0 20px;
                margin: 18px auto;
            }
        }

        /* بطاقة أصغر وبسيطة */
        .compact-card {
            padding: 18px;
            border-radius: 14px;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.06);
            background: #fff;
        }

        .simple-header {
            display: flex;
            align-items: center;
            border-bottom: none;
            margin-bottom: 14px;
            padding-bottom: 0;
        }

        .simple-header h2 {
            font-size: 18px;
            margin: 0;
            color: #2b2b2b;
        }

        .empty-box {
            text-align: center;
            color: #666;
            padding: 26px;
            border-radius: 12px;
            background: #fbfbfb;
        }

        /* قائمة الزيارات: grid بسيط */
        .visits-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .visit-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 12px;
            border-radius: 10px;
            background: linear-gradient(180deg, #fff, #fcfcfc);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
            align-items: center;
            transition: transform .14s ease, box-shadow .14s ease;
        }
/* كارت الزيارة الصغير */
.visit-mini-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    transition: 0.2s ease;
}

.visit-mini-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
}

/* الدائرة */
.visit-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #f3f3f3;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 20px;
}

/* المحتوى */
.visit-info {
    flex: 1;
}

.visit-time {
    font-weight: 700;
    color: #222;
    font-size: 14px;
}

.visit-by {
    font-size: 12px;
    color: #777;
}

/* زر الحذف */
.delete-btn {
    background: #ffdedf;
    color: #8b0000;
    border: none;
    font-size: 14px;
    padding: 6px 8px;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.15s ease;
}

.delete-btn:hover {
    background: #ffbfc2;
}

        .visit-row:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 26px rgba(0, 0, 0, 0.06);
        }

        .visit-left {
            flex: 1;
            min-width: 0;
        }

        .visit-title {
            font-size: 14px;
            color: #222;
            margin-bottom: 6px;
        }

        .visit-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .chip {
            padding: 6px 10px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 13px;
            background: #f1f1f1;
        }

        .chip.green {
            background: #e8f6ea;
            color: #0a8a3a;
        }

        .small {
            font-size: 13px;
            color: #666;
        }

        /* أزرار مبسطة */
        .btn {
            background: #D9B1AB;
            color: #fff;
            padding: 8px 10px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 700;
            font-size: 13px;
        }

        .btn:hover {
            transform: scale(1.02);
        }

        /* paginate wrapper */
        .pagination-wrap {
            margin-top: 14px;
            display: flex;
            justify-content: center;
        }

        /* responsive: عمود واحد للموبايل */
        @media (max-width:900px) {
            .visits-grid {
                grid-template-columns: 1fr;
            }

            .compact-card {
                padding: 16px;
            }

            .subscription-container.simple-padding {
                padding: 0 16px;
            }
        }

        /* snackbar */
        .snackbar {
            position: fixed;
            top: 18px;
            right: 18px;
            background: #333;
            color: #fff;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 14px;
            z-index: 9999;
            opacity: 0;
            transform: translateX(120%);
            transition: opacity 0.32s ease, transform 0.32s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .snackbar.show {
            opacity: 1;
            transform: translateX(0);
        }

        .snackbar.error {
            background: #dc3545;
        }
    </style>

@endsection
