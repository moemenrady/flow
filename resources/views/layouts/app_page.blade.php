<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <meta charset="UTF-8">
    <title>

        @yield('title')

    </title>
    @yield('style')

    <style>
        /* مساحة افتراضية أعلى الصفحة (يتم تحديثها ديناميكياً بالـ JS) */
        :root {
            --theme-primary: #d9b2ad;
            --accent-2: #ffe8ee;
            --top-offset: 72px;
            --theme-color: #e5c6c3;
            --theme-color-active: #e5c6c3;
            --nav-text: #333;
            --nav-active-bg: #fff;
        }

        /* نستخدم المتغير لتعويد المحتوى على وجود أزرار ثابتة أعلاه */
        body {
            /* نحفظ المسافة الفارغة أعلى المحتوى لتفادي تداخل العناصر الثابتة */
            padding-top: calc(var(--top-offset) + 12px);
            transition: padding-top .18s ease;
        }

        /* ✅ Navbar */
        .navbar-custom {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            padding: 14px 24px;
            backdrop-filter: blur(10px);
            z-index: 1500;
            border-radius: 0 0 16px 16px;
        }

        .navbar-custom a {
            text-decoration: none;
            color: var(--nav-text);
            font-weight: 600;
            padding: 10px 18px;
            border-radius: 8px;
            transition: all 0.25s ease;
        }

        .navbar-custom a:hover {
            background: #e5c6c33a;
            transform: translateY(-2px);
        }

        .navbar-custom a.active {
            background: var(--theme-color-active);
            color: #000;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            transform: scale(1.05);
        }

        .back-btn {
            position: fixed;
            top: 60px;
            /* نزلناه شويه تحت */
            left: 20px;
            background: #e5c6c3;
            border-radius: 50%;
            padding: 13px;
            cursor: pointer;
            transition: transform 0.2s;
            z-index: 2000;
            border: none;
        }

        /* زرّ الرجوع */
        .back-btn2 {
            position: fixed;
            top: 12px;
            /* فوق شويه */
            left: 20px;
            background: #e5c6c3;
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 2001;
            /* أعلى من الرئيسية */
        }

        /* تأثير hover */
        .back-btn:hover,
        .back-btn2:hover {
            transform: scale(1.1);
            background: #d9b3b0;
        }

        /* أيقونة السهم */
        .back-btn2 i {
            color: #333;
            font-size: 16px;
        }

        /* زرّات الإجراءات في أعلى اليمين (لو عندك) يجب أن يكون لها هذه الصفة */
        .page-actions {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 2000;
        }

        /* Snackbar: لا نضعه عند top:20px مباشرة، بل نستعمل المتغير بحيث يكون أسفل الأزرار الثابتة */
        .snackbar {
            position: fixed;
            top: calc(var(--top-offset) + 8px);
            /* أسفل الأزرار الثابتة */
            right: 20px;
            background: #333;
            color: #fff;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            z-index: 1990;
            /* أقل من الأزرار الثابتة حتى لا يغطيها */
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
        }

        .snackbar.success {
            background: #28a745;
        }

        .snackbar.error {
            background: #dc3545;
        }

        /* لو في أكثر من snackbar نتباعد بينهم */
        .snackbar-stack+.snackbar-stack {
            margin-top: 8px;
        }

        /* حماية على الشاشات الصغيرة: نقّص المسافة لو تحتاج */
        @media (max-width: 520px) {
            :root {
                --top-offset: 64px;
            }

            body {
                padding-top: calc(var(--top-offset) + 8px);
            }

            .snackbar {
                right: 12px;
                left: 12px;
                top: calc(var(--top-offset) + 8px);
            }
        }

        .title {
            text-align: center;
            margin: 8px 0 18px;
            color: var(--theme-primary);
            font-size: 22px;
            font-weight: 800;
            padding: 12px 18px;
            border-radius: 12px;
            background: linear-gradient(180deg, rgba(217, 178, 173, 0.06), rgba(217, 178, 173, 0.02));
            border: 1px solid rgba(217, 178, 173, 0.10);
            box-shadow: 0 8px 22px rgba(217, 178, 173, 0.06);
        }

        @media (max-width:720px) {
            .title {
                font-size: 18px;
                padding: 10px 14px;
            }
        }

        @media (max-width: 768px) {
            .desktop-nav {
                display: none !important;
            }
        }

        /* إظهارها على الديسكتوب */
        @media (min-width: 769px) {
            .desktop-nav {
                display: block;
            }
        }

        .edit-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(90deg, #ffd966cd 0%, #ffb803db 100%);
            color: #2b2b2b;
            padding: 10px 14px;
            border-radius: 12px;
            font-weight: 800;
            box-shadow: 0 8px 22px rgba(255, 183, 3, 0.18), 0 2px 6px rgba(0, 0, 0, 0.06);
            text-decoration: none;
            transform: translateY(0);
            transition: transform .18s cubic-bezier(.2, .9, .3, 1), box-shadow .18s, filter .18s;
            border: 1px solid rgba(0, 0, 0, 0.06);
            position: relative;
            overflow: visible;
        }

        .edit-btn .edit-ico {
            font-size: 18px;
            transform-origin: center;
            display: inline-block;
            transition: transform .24s ease;
        }

        .edit-btn .edit-txt {
            font-size: 14px;
            letter-spacing: .2px;
        }

        .edit-btn:focus {
            outline: none;
            box-shadow: 0 12px 30px rgba(255, 179, 3, 0.18);
        }

        .edit-btn:hover {
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 18px 40px rgba(255, 170, 3, 0.22);
            filter: saturate(1.05);
        }

        .edit-btn:hover .edit-ico {
            transform: translateY(-2px) rotate(-12deg) scale(1.05);
        }

        /* نبض خفيف حول الزر (pseudo) */
        .edit-btn::after {
            content: '';
            position: absolute;
            left: -6px;
            right: -6px;
            top: -6px;
            bottom: -6px;
            border-radius: 16px;
            z-index: -1;
            opacity: 0;
            transition: opacity .25s, transform .25s;
            background: radial-gradient(closest-side, rgba(255, 190, 60, 0.12), transparent 40%);
            transform: scale(.95);
            pointer-events: none;
        }

        /* نشغل النبضة مرة عند تحميل الصفحة */
        .edit-btn {
            animation: btnPulse 1.1s ease 0s 1;
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



    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>

<body>
    <div class="desktop-nav">
        @include('layouts.page_navigation')
    </div>
    @yield('page_title')
    @if (session('shift_required'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: '⚠️ لم تفتح شيفت بعد',
                    text: 'هل تريد فتح شيفت جديد الآن؟',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم، افتح شيفت',
                    cancelButtonText: 'لاحقًا'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch("{{ route('shift.start') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            }
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            });
        </script>
    @endif

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
    @if (session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif

    {{-- زر الرجوع للرئيسيه --}}
    <form action="{{ route('main.create') }}">
        <button class="back-btn">الرئيسية</button>
    </form>

    <button type="button" class="back-btn2" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>

    @yield('content')



    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Pusher JS -->
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
    <!-- Laravel Echo (IIFE) -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

    <script>
        function showSnackbar(message, type = "success") {
            const box = document.createElement('div');
            box.className = `snackbar ${type}`;
            box.textContent = message;
            document.body.appendChild(box);

            setTimeout(() => box.classList.add("show"), 50);

            setTimeout(() => {
                box.classList.remove("show");
                setTimeout(() => box.remove(), 300);
            }, 4000);
        }
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

            document.body.appendChild(box);

            //   <div class="d-flex gap-2" style="flex-wrap:wrap;">

            //             <!-- زر يفتح المودال — لا يرسل الفورم -->
            //             <button type="button" id="openPrivateBtn" class="btn-submit"
            //                 style="background: linear-gradient(135deg,#7b61ff,#5e3bff); flex:1; min-width:140px;"
            //                 data-bs-toggle="modal" data-bs-target="#startBookingModal">
            //                 🔒 بدء جلسة خاصة
            //             </button>
            //         </div>
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

        // زر "بدء جلسة خاصة"
        const privateBtn = box.querySelector('#openPrivateBtn');

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
</body>

</html>
