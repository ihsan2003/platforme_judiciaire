<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل الدخول — المنصة القانونية</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    @vite('resources/css/login.css')</head>
<body>

<div class="page">

    <!-- LEFT PANEL -->
    <div class="panel-left">

        <div class="deco-ring deco-ring-1"></div>
        <div class="deco-ring deco-ring-2"></div>
        <div class="deco-ring deco-ring-3"></div>

        <div class="brand-content">
            <div class="brand-icon">
                <i class="bi bi-bank2"></i>
            </div>

            <p class="brand-sub">التعاون الوطني</p>

            <h1 class="brand-title">
                تتبع الملفات القضائية 
                <br>
                والشكايات
            </h1>

            <div class="divider-gold"></div>

            <p class="brand-quote">
                « منصة تتبع الملفات القضائية والشكايات القانونية 
                 الخاصة بمصلحة المنازعات والشؤون القانونية»
            </p>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="panel-right">

        <div class="form-card">

            <div class="top-logo">
                <img src="{{ asset('images/logo.png') }}" alt="الشعار">
            </div>

            @if(session('status'))
                <div class="alert-session">
                    <i class="bi bi-info-circle"></i>
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field">
                    <label for="email">البريد الإلكتروني</label>

                    <div class="input-wrap">
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="example@email.com"
                            required
                            autofocus
                        >
                        <i class="bi bi-envelope"></i>
                    </div>

                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">كلمة المرور</label>

                    <div class="input-wrap has-toggle">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="••••••••"
                            required
                        >
                        <i class="bi bi-lock"></i>
                        <button type="button" class="toggle-password" id="togglePassword" aria-label="إظهار/إخفاء كلمة المرور" tabindex="-1">
                            <i class="bi bi-eye-slash" id="togglePasswordIcon"></i>
                        </button>
                    </div>

                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="row-options">

                    <label class="check-label">
                        <input type="checkbox" name="remember">
                        تذكرني
                    </label>

                </div>

                <button type="submit" class="btn-submit">
                    <i class="bi bi-arrow-left-circle"></i>
                    تسجيل الدخول
                </button>
            </form>

            <div class="form-footer">
                <strong> التعاون الوطني © {{ date('Y') }}</strong>
                — جميع الحقوق محفوظة
            </div>

        </div>
    </div>

</div>

<script>
    (function () {
        const toggleBtn  = document.getElementById('togglePassword');
        const toggleIcon = document.getElementById('togglePasswordIcon');
        const passwordInput = document.getElementById('password');

        if (!toggleBtn || !passwordInput) return;

        toggleBtn.addEventListener('click', function () {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';

            toggleIcon.classList.toggle('bi-eye-slash', !isHidden);
            toggleIcon.classList.toggle('bi-eye', isHidden);
        });
    })();
</script>

</body>
</html>