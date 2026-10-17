@extends('layouts.app_page_admin')

@section('page_title', 'إدارة المستخدمين المحترفة')

@section('content')
    <!-- استدعاء FontAwesome للأيقونات المبهرة -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* نظام الألوان الداكن المتطور - Futuristic Dark */
        :root {
            --bg-dark: #0f172a;
            --card-dark: #1e293b;
            --neon-blue: #38bdf8;
            --neon-green: #4ade80;
            --neon-purple: #a855f7;
            --neon-red: #f87171;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
        }

        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }

        .page-container {
            max-width: 1280px;
            margin: 30px auto;
            padding: 0 24px;
        }

        /* الـ Header وإحصائيات النيون */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 32px;
        }

        .title-area h2 {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(to right, #fff, var(--neon-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0 0 4px 0;
        }

        .stats-container {
            display: flex;
            gap: 16px;
        }

        .stat-card {
            background: var(--card-dark);
            border: 1px solid var(--border-color);
            padding: 16px 24px;
            border-radius: 16px;
            min-width: 160px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            left: 0;
            height: 3px;
        }

        .stat-card.total::after {
            background: var(--neon-blue);
        }

        .stat-card.admins::after {
            background: var(--neon-purple);
        }

        .stat-icon {
            font-size: 24px;
            color: var(--text-muted);
        }

        .stat-card.total .stat-icon {
            color: var(--neon-blue);
        }

        .stat-card.admins .stat-icon {
            color: var(--neon-purple);
        }

        .stat-info p {
            margin: 0;
            font-size: 13px;
            color: var(--text-muted);
        }

        .stat-info h3 {
            margin: 4px 0 0 0;
            font-size: 22px;
            font-weight: 700;
            color: #fff;
        }

        /* صندوق التحكم والبحث وعمليات الـ CRUD العلوية */
        .controls-card {
            background: var(--card-dark);
            border: 1px solid var(--border-color);
            padding: 20px;
            border-radius: 16px;
            display: flex;
            gap: 16px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .search-wrapper {
            position: relative;
            width: 100%;
            max-width: 450px;
        }

        .search-wrapper i {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .search-input {
            width: 100%;
            background: var(--bg-dark);
            border: 1px solid var(--border-color);
            padding: 12px 44px 12px 16px;
            border-radius: 12px;
            color: #fff;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--neon-blue);
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.15);
        }

        /* الأزرار الاحترافية */
        .btn-neon {
            background: linear-gradient(135deg, var(--neon-blue), #0284c7);
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(56, 189, 248, 0.3);
        }

        .btn-neon:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(56, 189, 248, 0.5);
            filter: brightness(1.1);
        }

        /* الجدول الرئيسي للديسكتوب */
        .table-container {
            background: var(--card-dark);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
        }

        th {
            background: rgba(15, 23, 42, 0.6);
            padding: 16px;
            color: var(--text-muted);
            font-size: 13px;
            text-transform: uppercase;
            font-weight: 600;
            border-bottom: 1px solid var(--border-color);
        }

        td {
            padding: 16px;
            border-bottom: 1px solid rgba(51, 65, 85, 0.5);
            font-size: 14px;
            color: #e2e8f0;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: rgba(51, 65, 85, 0.3);
        }

        /* الـ Badges الذكية والـ Select */
        .role-select-native {
            background: var(--bg-dark);
            color: #fff;
            border: 1px solid var(--border-color);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            outline: none;
            transition: all 0.3s;
        }

        .role-select-native.admin {
            color: var(--neon-purple);
            border-color: var(--neon-purple);
            background: rgba(168, 85, 247, 0.1);
        }

        .role-select-native.user {
            color: var(--neon-green);
            border-color: var(--neon-green);
            background: rgba(74, 222, 128, 0.1);
        }

        /* أزرار العمليات المصغرة */
        .action-actions-btn {
            background: none;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            margin-right: 4px;
        }

        .btn-edit {
            color: var(--neon-blue);
            background: rgba(56, 189, 248, 0.1);
        }

        .btn-edit:hover {
            background: var(--neon-blue);
            color: #fff;
        }

        .btn-delete {
            color: var(--neon-red);
            background: rgba(248, 113, 113, 0.1);
        }

        .btn-delete:hover {
            background: var(--neon-red);
            color: #fff;
        }

        /* تصميم المودال العائم والمبهر للـ CRUD */
        .neon-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .neon-modal.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-content {
            background: var(--card-dark);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            width: 100%;
            max-width: 480px;
            padding: 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transform: scale(0.9);
            transition: all 0.3s ease;
        }

        .neon-modal.active .modal-content {
            transform: scale(1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #fff;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 20px;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .form-control {
            width: 100%;
            background: var(--bg-dark);
            border: 1px solid var(--border-color);
            padding: 12px;
            border-radius: 10px;
            color: #fff;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--neon-blue);
        }

        /* الاستجابة الكاملة للموبايل - Responsive Grid Cards */
        @media (max-width: 840px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .stats-container {
                width: 100%;
                justify-content: space-between;
            }

            .controls-card {
                flex-direction: column;
                align-items: stretch;
            }

            .search-wrapper {
                max-width: 100%;
            }

            /* إخفاء الجدول وهيكلة البيانات كبطاقات نيون هجينة */
            thead {
                display: none;
            }

            table,
            tbody,
            tr,
            td {
                display: block;
                width: 100%;
            }

            tr {
                background: var(--card-dark);
                border: 1px solid var(--border-color);
                border-radius: 16px;
                padding: 16px;
                margin-bottom: 16px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            }

            td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px 0;
                border-bottom: 1px solid rgba(51, 65, 85, 0.3);
                text-align: left;
            }

            td:last-child {
                border-bottom: none;
                justify-content: flex-end;
            }

            td[data-label]::before {
                content: attr(data-label);
                color: var(--text-muted);
                font-weight: 600;
                font-size: 13px;
            }
        }
    </style>

    <div class="page-container">
        <!-- الهيدر العلوي الذكي -->
        <div class="dashboard-header">
            <div class="title-area">
                <h2>إدارة أعضاء النظام</h2>
                <p style="color: var(--text-muted); margin: 0; font-size: 14px;">التحكم الكامل بالصلاحيات والمستخدمين بكل
                    سهولة عبر الـ AJAX</p>
            </div>
            <div class="stats-container">
                <div class="stat-card total">
                    <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-info">
                        <p>إجمالي الحسابات</p>
                        <h3 id="totalUsersCount">{{ $totalUsers ?? '—' }}</h3>
                    </div>
                </div>
                <div class="stat-card admins">
                    <div class="stat-icon"><i class="fa-solid fa-user-shield"></i></div>
                    <div class="stat-info">
                        <p>المشرفين (Admins)</p>
                        <h3 id="adminsCount">{{ $adminsCount ?? '—' }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- لوحة التحكم والبحث -->
        <div class="controls-card">
            <div class="search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input class="search-input" type="text" id="searchBox"
                    placeholder="ابحث بالاسم، البريد الإلكتروني أو المعرّف (ID)...">
            </div>
            <!-- تم التعديل هنا: تحويل الزر إلى رابط يتوجه مباشرة إلى صفحة الـ Register -->
            <a href="{{ route('register') }}" class="btn-neon">
                <i class="fa-solid fa-user-plus"></i>
                <span>إضافة حساب جديد</span>
            </a>
        </div>

        <!-- حاوية الجدول الرئيسي / بطاقات الموبايل -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th># المعرف</th>
                        <th>الاسم الشخصي</th>
                        <th>البريد الإلكتروني</th>
                        <th>الصلاحية الحالية</th>
                        <th style="text-align: left; padding-left: 24px;">العمليات</th>
                    </tr>
                </thead>
                <tbody id="usersTable">
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);"><i
                                class="fa-solid fa-circle-notch fa-spin"></i> جاري جلب البيانات من الخادم...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================== نافذة تعديل بيانات مستخدم (Edit Modal) ================== -->
    <div class="neon-modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>تعديل بيانات الحساب</h3>
                <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form id="editUserForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_user_id">
                <div class="form-group">
                    <label>الاسم</label>
                    <input type="text" id="edit_name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>البريد الإلكتروني</label>
                    <input type="email" id="edit_email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>تغيير الصلاحية</label>
                    <select id="edit_role" name="role" class="form-control">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn-neon"
                    style="width: 100%; justify-content: center; margin-top: 10px;">تحديث البيانات</button>
            </form>
        </div>
    </div>

    <!-- ================== محرّك الـ JavaScript والـ AJAX الاحترافي ================== -->
    <script>
        // تحديد هوية المستخدم الحالي المسجل لحماية حسابه من التعديل الذاتي
        const currentAuthId = {{ auth()->id() }};

        document.addEventListener('DOMContentLoaded', function() {
            const searchBox = document.getElementById('searchBox');
            const tbody = document.getElementById('usersTable');

            // 1. دالة بناء وعرض الصفوف (متوافقة ديسكتوب وموبايل بشكل رائع)
            function renderRows(users) {
                tbody.innerHTML = '';
                if (users.length === 0) {
                    tbody.innerHTML =
                        `<tr><td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">❌ لم يتم العثور على أي حسابات مطابقة</td></tr>`;
                    return;
                }

                users.forEach(u => {
                    const tr = document.createElement('tr');

                    // حماية التعديل التلقائي أو الحذف للحساب الشخصي المفتوح حالياً
                    const isSelf = (u.id === currentAuthId);

                    // تخصيص الأكشنز والـ Select المنسدل بناءً على الصلاحيات والحساب الشخصي
                    let roleMarkup = '';
                    if (!isSelf) {
                        roleMarkup = `
                            <select class="role-select-native ${u.role}" onchange="updateUserRole(${u.id}, this.value)">
                                <option value="user" ${u.role === 'user' ? 'selected' : ''}>User</option>
                                <option value="admin" ${u.role === 'admin' ? 'selected' : ''}>Admin</option>
                            </select>
                        `;
                    } else {
                        roleMarkup =
                            `<span class="role-select-native admin" style="cursor: not-allowed;"><i class="fa-solid fa-user-lock"></i> حسابك الحالي</span>`;
                    }

                    let actionMarkup = '';
                    if (!isSelf) {
                        actionMarkup = `
                            <button class="action-actions-btn btn-edit" onclick="openEditModal(${u.id}, '${escapeHtml(u.name)}', '${escapeHtml(u.email)}', '${u.role}')" title="تعديل">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="action-actions-btn btn-delete" onclick="deleteUser(${u.id})" title="حذف">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        `;
                    } else {
                        actionMarkup =
                            `<span style="font-size:12px; color:var(--text-muted)">لا توجد عمليات</span>`;
                    }

                    tr.innerHTML = `
                        <td data-label="# المعرف"><span style="color: var(--neon-blue); font-weight: 700;">#${u.id}</span></td>
                        <td data-label="الاسم الشخصي" class="fw-semibold">${escapeHtml(u.name)}</td>
                        <td data-label="البريد الإلكتروني" style="color: var(--text-muted);">${escapeHtml(u.email)}</td>
                        <td data-label="الصلاحية الحالية">${roleMarkup}</td>
                        <td style="text-align: left; padding-left:24px;">${actionMarkup}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            // 2. دالة البحث المباشر وجلب البيانات من الخادم (Read)
            function fetchUsers(query = '') {
                const params = new URLSearchParams({
                    q: query
                });
                fetch("{{ route('admin.users.ajaxSearch') }}?" + params.toString(), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        renderRows(data);
                    })
                    .catch(err => {
                        console.error(err);
                        tbody.innerHTML =
                            `<tr><td colspan="5" style="text-align: center; padding: 20px; color: var(--neon-red);">⚠️ حدث خطأ غير متوقع أثناء تحميل البيانات.</td></tr>`;
                    });
            }

            // عمل Debounce للبحث لعدم إرهاق السيرفر
            let timer;
            searchBox.addEventListener('input', function() {
                clearTimeout(timer);
                timer = setTimeout(() => fetchUsers(this.value.trim()), 200);
            });

            // 3. التحديث السريع والذكي للصلاحية عبر الـ Dropdown (Update Role)
            window.updateUserRole = function(id, newRole) {
                let formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('_method', 'PUT');
                formData.append('role', newRole);

                fetch(`/admin/users/${id}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error();
                        fetchUsers(searchBox.value);
                    })
                    .catch(() => alert('⚠️ فشل تحديث الصلاحية، تأكد من الـ controller الخاص بك.'));
            }

            // 4. تعديل بيانات المستخدم العامة من المودال (Update General)
            document.getElementById('editUserForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const id = document.getElementById('edit_user_id').value;
                fetch(`/admin/users/${id}`, {
                        method: 'POST',
                        body: new FormData(this),
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        closeModal('editModal');
                        fetchUsers(searchBox.value);
                        alert('✅ تم تحديث بيانات الحساب بنجاح.');
                    })
                    .catch(() => alert('⚠️ فشل التحديث.'));
            });

            // 5. حذف حساب نهائياً (Delete)
            window.deleteUser = function(id) {
                if (confirm('هل أنت متأكد تماماً من رغبتك في حذف هذا الحساب نهائياً؟ لا يمكن التراجع.')) {
                    let formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('_method', 'DELETE');

                    fetch(`/admin/users/${id}`, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => {
                            fetchUsers(searchBox.value);
                            alert('🗑️ تم حذف الحساب من قاعدة البيانات بنجاح.');
                        });
                }
            }

            // تحميل البيانات لأول مرة تلقائياً
            fetchUsers();
        });

        // فتح وإغلاق النوافذ المنبثقة بحركات نيون ناعمة
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        function openEditModal(id, name, email, role) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            openModal('editModal');
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
        }
    </script>
@endsection
