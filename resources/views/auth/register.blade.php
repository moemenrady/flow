@extends('layouts.app_page_admin')

@section('page_title', 'إنشاء حساب جديد')

@section('content')
    <!-- استدعاء FontAwesome للأيقونات المتوافقة مع النظام -->
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
            max-width: 680px; /* مقاس متناسق مخصص لصفحات النماذج */
            margin: 40px auto;
            padding: 0 24px;
        }

        /* الهيدر العلوي */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        /* كارت النموذج المصمم بطراز النيون */
        .form-card {
            background: var(--card-dark);
            border: 1px solid var(--border-color);
            padding: 34px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            position: relative;
            overflow: hidden;
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--neon-blue), var(--neon-purple));
        }

        /* تنسيق الحقول والمجموعات */
        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 600;
        }

        /* حاوية الإدخال المدعومة بالأيقونات */
        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
            transition: color 0.3s ease;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            background: var(--bg-dark);
            border: 1px solid var(--border-color);
            padding: 14px 46px 14px 16px; /* مسافة إضافية من اليمين للأيقونة */
            border-radius: 12px;
            color: #fff;
            font-size: 14px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--neon-blue);
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.15);
        }

        /* تغيير لون الأيقونة عند التفاعل */
        .form-control:focus + i {
            color: var(--neon-blue);
        }

        select.form-control {
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
        }

        /* تخصيص رسائل الخطأ */
        .error-message {
            color: var(--neon-red);
            font-size: 13px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* الأزرار الاحترافية */
        .btn-neon {
            background: linear-gradient(135deg, var(--neon-blue), #0284c7);
            color: #fff;
            border: none;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(56, 189, 248, 0.3);
            margin-top: 10px;
        }

        .btn-neon:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(56, 189, 248, 0.5);
            filter: brightness(1.1);
        }

        .btn-secondary-neon {
            background: rgba(51, 65, 85, 0.5);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            box-shadow: none;
        }
        
        .btn-secondary-neon:hover {
            background: rgba(51, 65, 85, 0.8);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .form-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .form-footer a {
            color: var(--neon-blue);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .form-footer a:hover {
            color: #7dd3fc;
            text-decoration: underline;
        }

        /* دعم الشاشات الصغيرة للموبايل */
        @media (max-width: 480px) {
            .form-card {
                padding: 24px 16px;
            }
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            .btn-secondary-neon {
                width: 100%;
            }
        }
    </style>

    <div class="page-container">
        <!-- الهيدر العلوي الذكي -->
        <div class="dashboard-header">
            <div class="title-area">
                <h2>إضافة مستخدم جديد</h2>
                <p style="color: var(--text-muted); margin: 0; font-size: 14px;">قم بإنشاء حساب جديد وتعيين الصلاحيات مباشرة للنظام</p>
            </div>
            <a href="{{ url()->previous() }}" class="btn-neon btn-secondary-neon" style="width: auto;">
                <i class="fa-solid fa-arrow-right"></i>
                <span>رجوع</span>
            </a>
        </div>

        <!-- كارت التسجيل الرئيسي -->
        <div class="form-card">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- حقل الاسم بالكامل -->
                <div class="form-group">
                    <label for="name">الاسم بالكامل</label>
                    <div class="input-wrapper">
                        <input id="name" class="form-control" type="text" name="name" required value="{{ old('name') }}" placeholder="أدخل اسم المستخدم الثلاثي...">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    @error('name')
                        <div class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <!-- حقل البريد الإلكتروني -->
                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <div class="input-wrapper">
                        <input id="email" class="form-control" type="email" name="email" required value="{{ old('email') }}" placeholder="example@domain.com">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    @error('email')
                        <div class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <!-- حقل كلمة المرور -->
                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <div class="input-wrapper">
                        <input id="password" class="form-control" type="password" name="password" required placeholder="••••••••">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    @error('password')
                        <div class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <!-- حقل تأكيد كلمة المرور -->
                <div class="form-group">
                    <label for="password_confirmation">تأكيد كلمة المرور</label>
                    <div class="input-wrapper">
                        <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required placeholder="••••••••">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    @error('password_confirmation')
                        <div class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <!-- حقل نوع الحساب / الصلاحية -->
                <div class="form-group">
                    <label for="role">نوع صلاحية الحساب</label>
                    <div class="input-wrapper">
                        <select id="role" name="role" class="form-control" required>
                            <option value="" disabled selected style="background: var(--card-dark);">اختر نوع الحساب وتصنيفه</option>
                            <option value="user" {{ old('role') == 'user' ? 'selected' : '' }} style="background: var(--card-dark);">مستخدم عادي (User)</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }} style="background: var(--card-dark);">مدير نظام (Admin)</option>
                        </select>
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    @error('role')
                        <div class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <!-- زر الإرسال -->
                <button type="submit" class="btn-neon">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>تأكيد وإنشاء الحساب الآن</span>
                </button>
            </form>

            <!-- الفوتر الداخلي للكارت -->
            <div class="form-footer">
                لديك حساب بالفعل؟ 
                <a href="{{ route('login') }}">تسجيل الدخول المباشر</a>
            </div>
        </div>
    </div>
@endsection