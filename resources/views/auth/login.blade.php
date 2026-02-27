<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>تسجيل الدخول</title>

<style>
/* ================= ROOT ================= */
:root{
  --prime:#51141b;
  --soft:#8d5450;
  --bg:#ae9382;
  --glass:rgba(255,255,255,.12);
}

/* ================= RESET ================= */
*{box-sizing:border-box;font-family:'Segoe UI',system-ui}
body{
  margin:0;
  min-height:100vh;
  background:
    radial-gradient(circle at 15% 20%, var(--soft), transparent 40%),
    radial-gradient(circle at 85% 80%, var(--prime), transparent 45%),
    var(--bg);
  display:flex;
  align-items:center;
  justify-content:center;
  overflow:hidden;
}

/* ================= FLOATING BACKGROUND ================= */
.bg-orb{
  position:absolute;
  width:380px;height:380px;
  border-radius:50%;
  filter:blur(90px);
  opacity:.45;
  animation: float 12s ease-in-out infinite alternate;
}
.bg-orb.one{background:var(--prime);top:-120px;right:-120px;}
.bg-orb.two{background:var(--soft);bottom:-140px;left:-120px;animation-delay:4s;}

@keyframes float{
  to{transform:translateY(-60px) translateX(40px);}
}

/* ================= CARD ================= */
.login-card{
  width:100%;
  max-width:420px;
  padding:40px 34px;
  background:linear-gradient(180deg,
      rgba(255,255,255,.18),
      rgba(255,255,255,.05));
  backdrop-filter: blur(22px);
  border-radius:26px;
  border:1px solid rgba(255,255,255,.2);
  box-shadow:
    0 30px 90px rgba(0,0,0,.45);
  animation: enter 1s cubic-bezier(.19,1,.22,1);
}

@keyframes enter{
  from{opacity:0;transform:translateY(40px) scale(.92);filter:blur(8px)}
  to{opacity:1;transform:none;filter:none}
}

/* ================= HEADER ================= */
.login-title{
  font-size:28px;
  font-weight:800;
  color:#fff;
  margin-bottom:6px;
}
.login-sub{
  color:rgba(255,255,255,.75);
  font-size:14px;
  margin-bottom:28px;
}

/* ================= INPUTS ================= */
.field{
  position:relative;
  margin-bottom:22px;
}

.field input{
  width:100%;
  padding:16px 14px;
  background:var(--glass);
  border:1px solid rgba(255,255,255,.25);
  border-radius:14px;
  color:#fff;
  outline:none;
  font-size:15px;
  transition:.35s;
}

.field label{
  position:absolute;
  top:50%;
  right:14px;
  transform:translateY(-50%);
  color:rgba(255,255,255,.7);
  pointer-events:none;
  transition:.3s;
  font-size:14px;
}

.field input:focus,
.field input:not(:placeholder-shown){
  background:rgba(255,255,255,.18);
  border-color:var(--soft);
}

.field input:focus + label,
.field input:not(:placeholder-shown) + label{
  top:-7px;
  background:linear-gradient(180deg,var(--soft),var(--prime));
  padding:2px 10px;
  border-radius:999px;
  font-size:12px;
  color:#fff;
}

/* ================= REMEMBER ================= */
.remember{
  display:flex;
  align-items:center;
  justify-content:space-between;
  margin-bottom:24px;
  font-size:13px;
  color:rgba(255,255,255,.8);
}

.remember a{
  color:#fff;
  text-decoration:none;
  opacity:.8;
}
.remember a:hover{opacity:1}

/* ================= BUTTON ================= */
.btn-login{
  width:100%;
  padding:15px;
  border:none;
  border-radius:16px;
  background:linear-gradient(135deg,var(--prime),var(--soft));
  color:#fff;
  font-size:16px;
  font-weight:700;
  cursor:pointer;
  position:relative;
  overflow:hidden;
  transition:transform .2s ease, box-shadow .2s;
}

.btn-login::after{
  content:'';
  position:absolute;
  inset:0;
  background:linear-gradient(90deg,
    transparent,
    rgba(255,255,255,.35),
    transparent);
  transform:translateX(-100%);
}

.btn-login:hover{
  transform:translateY(-2px);
  box-shadow:0 12px 30px rgba(0,0,0,.45);
}

.btn-login:hover::after{
  transform:translateX(100%);
  transition:1s;
}

/* ================= ERRORS ================= */
.error{
  font-size:12px;
  margin-top:6px;
  color:#ffdede;
}

/* ================= FOOTER ================= */
.footer{
  text-align:center;
  margin-top:20px;
  font-size:12px;
  color:rgba(255,255,255,.65);
}

/* ================= MOBILE ================= */
@media(max-width:480px){
  .login-card{padding:32px 24px}
  .login-title{font-size:24px}
}
</style>
</head>

<body>

<div class="bg-orb one"></div>
<div class="bg-orb two"></div>

<div class="login-card">

  <div class="login-title">مرحبًا بعودتك 👋</div>
  <div class="login-sub">سجل دخولك واستكمل رحلتك</div>

  <x-auth-session-status class="mb-4" :status="session('status')" />

  <form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="field">
      <input type="email" name="email" required placeholder=" " value="{{ old('email') }}">
      <label>البريد الإلكتروني</label>
      @error('email')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
      <input type="password" name="password" required placeholder=" ">
      <label>كلمة المرور</label>
      @error('password')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="remember">
      <label>
        <input type="checkbox" name="remember"> تذكرني
      </label>

      @if (Route::has('password.request'))
        <a href="{{ route('password.request') }}">نسيت كلمة السر؟</a>
      @endif
    </div>

    <button class="btn-login">تسجيل الدخول</button>
  </form>

  <div class="footer">
    تجربة آمنة • سريعة • أنيقة
  </div>

</div>

</body>
</html>
