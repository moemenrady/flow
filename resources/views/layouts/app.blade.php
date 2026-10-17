<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('page_title')</title>
    <style>
        /* drawer profile */
        .drawer {
            padding: 18px;
            width: 260px;
            box-sizing: border-box;
        }

        .drawer-profile {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 6px 4px;
        }

        .avatar-wrap {
            position: relative;
            width: 64px;
            height: 64px;
            flex: 0 0 64px;
        }

        .avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(180deg, #fff, #f6f6f6);
            border: 1px solid rgba(0, 0, 0, 0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 20px;
            color: #333;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        }

        /* الشارة الصغيرة (badge) فوق الدائرة */
        .role-badge {
            position: absolute;
            right: -6px;
            bottom: -6px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-grid;
            place-items: center;
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
            color: #fff;
        }

        /* ستايلات لكل دور */
        .role-admin {
            background: linear-gradient(180deg, #ffb86b, #ff8a6b);
            color: #fff;
        }

        .role-staff {
            background: linear-gradient(180deg, #7cc7ff, #4a9eff);
            color: #fff;
        }

        .profile-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .profile-name {
            font-weight: 800;
            font-size: 15px;
            color: #222;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .profile-role {
            font-size: 13px;
            color: #666;
        }

        /* فاصل وخيارات */
        .drawer-sep {
            margin: 12px 0;
            border: none;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.06), transparent);
        }

        .drawer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .drawer-links li {
            margin: 6px 0;
        }

        .drawer-links a {
            display: inline-block;
            color: #333;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 6px;
            border-radius: 8px;
            transition: background .18s ease, transform .12s ease;
        }

        /*
        .drawer-links a:hover {
            background: rgba(0, 0, 0, 0.04);
            transform: translateX(4px);
        } */
        .drawer-links a {
            will-change: transform;
        }

        /* responsive: أصغر avatar للموبايل */
        @media (max-width: 576px) {
            .avatar-wrap {
                width: 52px;
                height: 52px;
            }

            .avatar {
                width: 52px;
                height: 52px;
                font-size: 16px;
            }

            .role-badge {
                width: 26px;
                height: 26px;
                right: -6px;
                bottom: -6px;
            }

            .drawer {
                width: 220px;
                padding: 12px;
            }
        }

        .back-btn {
            position: fixed;
            /* 👈 بدل absolute */
            top: 20px;
            left: 20px;
            background: #e5c6c3;
            border-radius: 50%;
            padding: 13px;
            cursor: pointer;
            transition: transform 0.2s;
            z-index: 2000;
        }

        .back-btn:hover {
            transform: scale(1.1) rotate(-10deg);
        }

        /* Snackbar style */
        .snackbar {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #333;
            color: #fff;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            z-index: 9999;
            opacity: 0;
            transform: translateX(120%);
            transition: opacity 0.4s ease, transform 0.4s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }


        .snackbar.show {
            opacity: 1;
            transform: translateX(0);
            /* 👈 تتحرك للداخل */
        }

        .snackbar.success {
            background: #28a745;
        }

        .snackbar.error {
            background: #dc3545;
        }

        /* أيقونة صغيرة */
        .snackbar i {
            font-size: 16px;
        }

        .client-snackbar {
            position: fixed;
            top: 24px;
            right: 24px;
            background: #fff;
            color: #333;
            border-radius: 16px;
            padding: 16px 18px;
            width: 320px;
            box-shadow: 0 18px 40px rgba(0, 0, 0, .15);
            z-index: 9999;
            animation: slideIn .35s ease;
        }

        .client-snackbar h4 {
            margin: 0 0 6px;
            font-weight: 800;
            color: #e5c6c3;
        }

        .client-snackbar p {
            margin: 0;
            font-size: 14px;
        }

        .client-snackbar button {
            margin-top: 12px;
            width: 100%;
            padding: 10px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #e5c6c3, #d9b2ad);
            font-weight: 800;
            cursor: pointer;
        }

        @keyframes slideIn {
            from {
                transform: translateX(120%);
                opacity: 0
            }

            to {
                transform: translateX(0);
                opacity: 1
            }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

</head>

<body>

    @include('layouts.navigation')
    {{-- ✅ تنبيهات Toast باستخدام Snackbar --}}
    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                showSnackbar("{{ session('success') }}", "success");
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                showSnackbar("{{ session('error') }}", "error");
            });
        </script>
    @endif
    @yield('content')
    <!-- Bootstrap JS Bundle -->

    <!-- Drawer -->
    @auth
        <div class="drawer" id="drawer">
            <div class="drawer-profile">
                @php
                    $user = auth()->user();
                    // نحاول نقرأ الصلاحية بأكثر من طريقة (حقل role أو method hasRole)
                    $role = $user->role ?? null;
                    if (!$role && method_exists($user, 'hasRole')) {
                        $role = $user->hasRole('admin') ? 'ادمن' : 'موظف';
                    }
                    $role = $role
                        ? (strtolower($role) === 'admin' || strtolower($role) === 'أدمن'
                            ? 'ادمن'
                            : 'موظف')
                        : 'موظف';
                    $initials = trim(
                        collect(explode(' ', $user->name))
                            ->map(fn($w) => mb_substr($w, 0, 1))
                            ->take(2)
                            ->join(''),
                    );
                @endphp

                <div class="avatar-wrap" aria-hidden="true">
                    <div class="avatar" title="{{ $user->name }}">
                        {{-- الأحرف داخل الدائرة --}}
                        {{ $initials ?: mb_substr($user->name, 0, 1) }}
                    </div>

                    {{-- شارة الصلاحية --}}
                    <div class="role-badge role-{{ $role === 'ادمن' ? 'admin' : 'staff' }}"
                        title="الصلاحية: {{ $role }}">
                        @if ($role === 'ادمن')
                            {{-- تاج للأدمن --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 15l4-9 4 9 4-9 4 9 4-9v9H2z"></path>
                            </svg>
                        @else
                            {{-- حقيبة للموظف --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 7h20v13a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2z"></path>
                                <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"></path>
                            </svg>
                        @endif
                    </div>
                </div>

                <div class="profile-info">
                    <div class="profile-name">{{ $user->name }}</div>
                    <div class="profile-role">{{ $role === 'ادمن' ? 'أدمن' : 'موظف' }}</div>
                </div>
            </div>

            <hr class="drawer-sep">

            <ul class="drawer-links">
                <li>
                    <a href="{{ route('shifts.active') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" style="vertical-align:middle; margin-right:6px;">
                            <!-- أيقونة ساعة / جدول -->
                            <path d="M12 1v11l9 5-9 5v11l-9-5 9-5V1z"></path>
                        </svg>
                        شفتات الموظفين
                    </a>
                </li>
                <li>
                    <a href="#" id="logout-btn" role="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" style="vertical-align:middle; margin-right:6px;">
                            <!-- شكل الباب -->
                            <path d="M21 2H9a1 1 0 0 0-1 1v18a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"></path>
                            <!-- السهم للخروج ناحية الشمال -->
                            <path d="M10 12H3l3-3m-3 3l3 3"></path>
                        </svg>
                        تسجيل الخروج
                    </a>




                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>



                </li>
                {{-- تقدر تضيف روابط تانية هنا --}}
            </ul>
        </div>
    @endauth

    {{-- <!-- ✅ الفوتر -->
    <footer class="position-fixed bottom-0 start-50 translate-middle-x text-center py-2 shadow-lg"
        style="hight:10%; width: 100%;">
        <div class="footer-container">
            <p class="mb-1 text-light">© {{ date('Y') }} - نظام الاشتراكات</p> <small class=""> تم التطوير
                بواسطة <a href="https://example.com" target="_blank"
                    class="text-warning text-decoration-none">Moemen</a> </small>
        </div>
    </footer> --}}

    @include('session.modal.start-booking')

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logoutForm = document.getElementById('logout-form');
            const logoutBtn = document.getElementById('logout-btn');

            if (!logoutForm || !logoutBtn) return;

            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault(); // نمنع الإرسال التلقائي

                // نطلب من السيرفر إذا في شيفت مفتوح
                fetch("{{ route('shift.check') }}", {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json'
                    }
                }).then(async response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    const data = await response.json();

                    if (data.open) {
                        // رسالة تأكيد بسيطة:
                        const proceed = confirm(
                            "لديك شيفت مفتوح.\nهل تريد تسجيل الخروج دون غلق الشيفت؟\n[OK] لتسجيل الخروج دون إغلاق الشيفت، [Cancel] لإلغاء تسجيل الخروج"
                        );
                        if (proceed) {
                            logoutForm.submit();
                        } else {
                            // إلغاء تسجيل الخروج — يمكن إضافة إشعار للمستخدم هنا
                        }
                    } else {
                        // لا شيفت مفتوح -> نكمل تسجيل الخروج مباشرة
                        logoutForm.submit();
                    }
                }).catch(err => {
                    console.error('Error checking open shift:', err);
                    // لو فشل الفحص نعرض خيار للمستخدم بدلاً من منع الخروج نهائياً
                    const proceedAnyway = confirm(
                        "تعذر التحقق من حالة الشيفت.\nهل تود المتابعة وتسجيل الخروج؟");
                    if (proceedAnyway) {
                        logoutForm.submit();
                    }
                    // وإلا نلغي تسجيل الخروج
                });
            });
        });
    </script>

    <script>
        function toggleDrawer() {
            document.getElementById("drawer").classList.toggle("open");
        }
    </script>
    <script>
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000
            })
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: "{{ session('error') }}",
                showConfirmButton: false,
                timer: 2000
            })
        @endif
    </script>


    <script>
        // عرض Snackbar بسيط
        function showSnackbar(message) {
            const box = document.createElement('div');
            box.style.position = 'fixed';
            box.style.bottom = '20px';
            box.style.left = '50%';
            box.style.transform = 'translateX(-50%)';
            box.style.padding = '12px 16px';
            box.style.background = '#333';
            box.style.color = '#fff';
            box.style.borderRadius = '8px';
            box.style.zIndex = '9999';
            box.textContent = message;
            document.body.appendChild(box);
            setTimeout(() => box.remove(), 3500);
        }

        // إعداد Echo على Pusher
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env('PUSHER_APP_KEY') }}',
            cluster: '{{ env('PUSHER_APP_CLUSTER', 'mt1') }}',
            forceTLS: true
        });

        // الاستماع لقناة "bookings" والحدث "booking.status.updated"
        window.Echo.channel('bookings')
            .listen('.booking.status.updated', (e) => {
                // e جاي من broadcastWith
                if (e?.title) {
                    showSnackbar(`📣 الحجز "${e.title}" أصبح Due الآن`);
                } else {
                    showSnackbar('📣 تم تحديث حالة حجز إلى Due');
                }
            });
    </script>
    <script>
        let persons = 1;

        function updatePersonsUI() {
            const countEl = document.getElementById('persons-count');
            const inputEl = document.getElementById('persons-input');

            if (countEl) countEl.innerText = persons;
            if (inputEl) inputEl.value = persons;
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {




            /* ===============================
                Scanner State
            =============================== */
            let isSearching = false;
            let buffer = '';
            let scanTimer = null;

            const MIN_LENGTH = 4;
            const MAX_LENGTH = 5;
            const SCAN_TIMEOUT = 8000; // 8 ثانية

            /* ===============================
                Safe Audio Loader
            =============================== */
            function safeAudio(src) {
                const audio = new Audio(src);
                audio.onerror = () => console.warn('Audio not found:', src);
                return audio;
            }

            const sounds = {
                start: safeAudio('/sounds/start.mp3'),
                success: safeAudio('/sounds/success.mp3'),
                entry: safeAudio('/sounds/entry.mp3'),
                error: safeAudio('/sounds/error.mp3'),
                cancel: safeAudio('/sounds/cancel.mp3'),
            };

            /* ===============================
                Helpers
            =============================== */
            function resetScan(reason = '') {
                buffer = '';
                clearTimeout(scanTimer);
                scanTimer = null;
                isSearching = false;

                if (reason === 'timeout' || reason === 'invalid') {
                    sounds.cancel.play();
                }
            }


            function startTimeout() {
                clearTimeout(scanTimer);
                scanTimer = setTimeout(() => {
                    resetScan('timeout');
                }, SCAN_TIMEOUT);
            }

            function isTypingInField(e) {
                const el = e.target;

                return (
                    el.tagName === 'INPUT' ||
                    el.tagName === 'TEXTAREA' ||
                    el.isContentEditable ||
                    el.closest('.client-snackbar')
                );
            }

            function closeClientSnackbar() {
                document.querySelector('.client-snackbar')?.remove();
                persons = 1;
                updatePersonsUI();
            }

            /* ===============================
                Keyboard Listener (Scanner)
            =============================== */
            document.addEventListener('keydown', (e) => {
                if (e.repeat) return;
                // ❌ تجاهل أي كتابة أثناء البحث
                if (isSearching) return;

                if (isTypingInField(e)) return;

                if (e.key === 'Escape') {
                    closeClientSnackbar();
                    resetScan();
                    return;
                }

                // ⛔ أرقام فقط
                if (!/^[0-9]$/.test(e.key)) {
                    resetScan('invalid'); // ⛔ أي زر غير رقم = مسح فوري
                    return;
                }

                if (buffer.length === 0) {
                    startTimeout();
                    sounds.start.play();
                }

                buffer += e.key;

                if (buffer.length === MIN_LENGTH || buffer.length === MAX_LENGTH) {
                    isSearching = true; // 🔒 قفل الإدخال
                    searchClientById(buffer);
                    resetScan();
                    return;
                }

                if (buffer.length > MAX_LENGTH) {
                    resetScan('invalid');
                }
            });


            /* ===============================
                API Search
            =============================== */
            async function searchClientById(id) {
                try {
                    const res = await fetch(`{{ route('clients.search.id') }}?query=${id}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const data = await res.json();

                    // ❌ مفيش عميل
                    if (!data || !data.length) {
                        closeClientSnackbar(); // 🔥 اقفل أي Snackbar
                        resetScan('invalid'); // 🔥 امسح البفر فورًا
                        sounds.error.play();
                        showClientSnackbar(null, '❌ لا يوجد عميل بهذا الكود');
                        return;
                    }

                    const client = data[0];

                    // ✅ عنده جلسة
                    if (client.active_session_id) {
                        window.location.href = `{{ url('/sessions') }}/${client.active_session_id}`;
                        sounds.entry.play();
                        return;
                    }

                    // ✅ عميل طبيعي
                    persons = 1;
                    updatePersonsUI();
                    showClientSnackbar(client);
                    sounds.success.play();

                } catch (err) {
                    console.error(err);
                    resetScan('invalid');
                    sounds.error.play();
                } finally {
                    isSearching = false; // 🔓 فتح الإدخال تاني
                }
            }


            document.addEventListener('DOMContentLoaded', () => {
                const hallEl = document.getElementById('hallSelect');
                const startNowBtn = document.getElementById('startNowBtn');

                hallEl.addEventListener('change', async () => {
                    if (!hallEl.value) {
                        startNowBtn.disabled = true;
                        return;
                    }

                    // حساب التقدير والتحقق من الحجز
                    await fetchEstimate();
                });

            });

        });
    </script>

    <script>
        function showClientSnackbar(client, errorMsg = null) {
            persons = 1;
            updatePersonsUI();

            document.querySelector('.client-snackbar')?.remove();

            const box = document.createElement('div');
            box.className = 'client-snackbar';

            if (!client) {
                box.innerHTML = `<p>${errorMsg}</p>`;
                document.body.appendChild(box);
                setTimeout(() => box.remove(), 2500);
                return;
            }

            box.innerHTML = `
        <h4>👤 ${client.name}</h4>
        <p>📞 ${client.phone}</p>
        <p>ID: ${client.id}</p>

        <form method="POST" action="{{ route('session.justStart') }}">
            @csrf
            <input type="hidden" name="name" value="${client.name}">
            <input type="hidden" name="phone" value="${client.phone}">
            <div class="persons-counter">
                <button type="button" class="counter-btn" data-action="minus">➖</button>
                <span id="persons-count">1</span>
                <button type="button" class="counter-btn" data-action="plus">➕</button>
            </div>
            <input type="hidden" name="persons" id="persons-input" value="1">
            <input type="hidden" name="age" value="${client.age ?? ''}">
            <input type="hidden" name="specialization_id" value="${client.specialization_id ?? ''}">
            <input type="hidden" name="education_stage_id" value="${client.education_stage_id ?? ''}">
            
            <!-- الزر الرئيسي -->
            <button type="submit" autofocus>🚀 بدء الجلسة</button>

           
        </form>
    `;
            //    <div class="d-flex gap-2" style="flex-wrap:wrap;">

            //                         <!-- زر يفتح المودال — لا يرسل الفورم -->
            //                         <button type="button" id="openPrivateBtn" class="btn-submit"
            //                             style="background: linear-gradient(135deg,#7b61ff,#5e3bff); flex:1; min-width:140px;"
            //                             data-bs-toggle="modal" data-bs-target="#startBookingModal">
            //                             🔒 بدء جلسة خاصة
            //                         </button>
            //                     </div>
            document.body.appendChild(box);

            box.querySelector('button').focus();

            setTimeout(async () => {
                try {
                    const clientId = client.id;
                    const personsCount = persons;
                    const startTime = new Date().toISOString().slice(0, 19).replace('T',
                        ' ');

                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const response = await fetch("{{ route('new-session.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            client_id: clientId,
                            persons: personsCount,
                            start_time: startTime
                        })
                    });

                    if (!response.ok) {
                        const text = await response.text();
                        console.error('خطأ من السيرفر:', text);
                        return;
                    }

                    const data = await response.json();

                    if (data.success) {
                        console.log('تم تخزين الجلسة تلقائيًا بعد 80 ثانية');
                    } else {
                        console.error('خطأ في تخزين الجلسة:', data);
                    }

                } catch (err) {
                    console.error('حدث خطأ عند الاتصال بالسيرفر:', err);
                }
            }, 2000);


            // ==============================
            // التعامل مع العدادات داخل Snackbar
            // ==============================
            // ==============================
            // التعامل مع العدادات داخل Snackbar
            // ==============================
            const counterMinus = box.querySelector('[data-action="minus"]');
            const counterPlus = box.querySelector('[data-action="plus"]');
            const personsCountEl = box.querySelector('#persons-count');
            const personsInputEl = box.querySelector('#persons-input');

            function updateModalPersons() {
                const modalPersonsEl = document.getElementById('personsDisplayInModal');
                const modalPersonsInput = document.getElementById('modal_persons');
                modalPersonsEl.textContent = persons;
                modalPersonsInput.value = persons;
            }

            counterMinus.addEventListener('click', () => {
                if (persons > 1) persons--;
                personsCountEl.innerText = persons;
                personsInputEl.value = persons;

                updateModalPersons(); // 🔹 يحدث المودال مباشرة
            });

            counterPlus.addEventListener('click', () => {
                persons++;
                personsCountEl.innerText = persons;
                personsInputEl.value = persons;

                updateModalPersons(); // 🔹 يحدث المودال مباشرة
            });

        }

        // زر "بدء جلسة خاصة"         const privateBtn = box.querySelector('#openPrivateBtn');


        privateBtn.addEventListener('click', () => {
            modalPhone.value = client.phone || '';
            modalName.value = client.name || '';
            modalPersons.value = persons || '1';
            personsDisplayEl.textContent = persons || '1';

            // إعادة تعيين حالة UI للمودال
            estimateBanner.style.display = 'none';
            ongoingWarning.style.display = 'none';
            estimateAmount.textContent = '';
            estimatePerHour.textContent = '';
            estimateMessage.textContent = 'اختَر القاعة لاظهار التقدير (المدة: ساعة واحدة)';
            startNowBtn.disabled = true;

            // فتح المودال برمجياً
            const modalEl = document.getElementById('startBookingModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        });

        const personsDisplayEl = document.getElementById('personsDisplayInModal');
        const hallEl = document.getElementById('hallSelect');
        const estimateBanner = document.getElementById('estimateBanner');
        const ongoingWarning = document.getElementById('ongoingWarning');
        const estimateAmount = document.getElementById('estimateAmount');
        const estimatePerHour = document.getElementById('estimatePerHour');
        const estimateMessage = document.getElementById('estimateMessage');
        const startNowBtn = document.getElementById('startNowBtn');

        $('#startBookingModal').on('shown.bs.modal', function() {
            const personsDisplayEl = document.getElementById('personsDisplayInModal');
            personsDisplayEl.textContent = persons || '1';

            // إذا كانت القاعة محددة مسبقًا
            const hallEl = document.getElementById('hallSelect');
            if (hallEl && hallEl.value) {
                fetchEstimate?.();
            }
        });

        // ==============================
        // تحديث عداد الأشخاص في المودال أثناء تعديلهم في Snackbar
        // ==============================
        function updateModalPersons() {
            const modalPersonsEl = document.getElementById('personsDisplayInModal');
            const modalPersonsInput = document.getElementById('modal_persons');
            modalPersonsEl.textContent = persons;
            modalPersonsInput.value = persons;
        }

        async function fetchEstimate() {
            const hallId = hallEl.value;
            const attendees = Number(modalPersons.value || 1);

            if (!hallId || attendees < 1) {
                startNowBtn.disabled = true;
                estimateBanner.style.display = 'none';
                return;
            }

            // عرض Loading
            estimateBanner.style.display = 'block';
            estimateMessage.textContent = 'جارِ الحساب...';
            startNowBtn.disabled = true;

            // تحقق من الحجز الجاري
            const ongoingResp = await checkOngoing(hallId);
            if (ongoingResp && ongoingResp.ongoing) {
                ongoingWarning.style.display = 'block';
                ongoingText.textContent = ongoingResp.message || 'القاعة محجوزة حالياً.';
                startNowBtn.disabled = true;
                return;
            }

            // طلب التقدير من السيرفر
            try {
                const params = new URLSearchParams({
                    hall_id: hallId,
                    attendees: attendees,
                    duration_minutes: 60
                });
                const resp = await fetch("{{ route('bookings.estimate') }}?" + params.toString());
                const data = await resp.json();

                if (data && data.success) {
                    estimateMessage.textContent = `التقدير (المدة: ساعة واحدة)`;
                    estimateAmount.textContent = `${data.estimated_formatted} ${data.currency || ''}`;
                    estimatePerHour.textContent = `سعر الساعة: ${data.per_hour_formatted || ''} ${data.currency || ''}`;
                    startNowBtn.disabled = false; // ✅ تفعيل الزر
                } else {
                    estimateMessage.textContent = data.error || 'خطأ في الحساب';
                    startNowBtn.disabled = true;
                }
            } catch (err) {
                console.error(err);
                estimateMessage.textContent = 'خطأ في الاتصال';
                startNowBtn.disabled = true;
            }
        }

        counterMinus.addEventListener('click', () => {
            if (persons > 1) persons--;
            personsCountEl.innerText = persons;
            personsInputEl.value = persons;

            updateModalPersons(); // 🔹 يحدث المودال مباشرة
        });

        counterPlus.addEventListener('click', () => {
            persons++;
            personsCountEl.innerText = persons;
            personsInputEl.value = persons;

            updateModalPersons(); // 🔹 يحدث المودال مباشرة
        });


        startBookingForm.addEventListener('submit', function(e) {
            // تحقق من البيانات
            if (!modalPhone.value || !modalName.value) {
                e.preventDefault();
                alert('مطلوب: اسم العميل ورقم الهاتف قبل بدء الجلسة.');
                return false;
            }

            // copy values
            modalPersons.value = personsDisplayEl.textContent || '1';
            $('input[name="duration_minutes"]').val(60); // مدة ثابتة ساعة

            // الفورم سيرسل تلقائيًا إلى route bookings.start-now (POST)
        });
    </script>

    @if (session('show_start_shift_prompt'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (confirm("هل تريد بدء شيفت الآن؟")) {
                    // عمل POST لبدء الشيفت عبر fetch أو submited form
                    document.getElementById('start-shift-form').submit();
                }
            });
        </script>
    @endif

    <form id="start-shift-form" action="{{ route('shift.start') }}" method="POST" style="display:none;">
        @csrf
    </form>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Pusher JS -->
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
    <!-- Laravel Echo (IIFE) -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

</body>

</html>
