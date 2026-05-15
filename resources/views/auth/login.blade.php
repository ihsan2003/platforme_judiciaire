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

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --navy:   #1a3a5c;
            --navy-d: #112944;
            --navy-l: #234d77;
            --gold:   #c8a84b;
            --gold-l: #e0c26e;
            --gold-d: #a8882e;
            --cream:  #faf8f3;
        }

        html, body {
            height: 100%;
            font-family: 'Cairo', sans-serif;
            background: #f0f2f5;
            overflow: hidden;
        }

        .page {
            display: flex;
            height: 100vh;
        }

        /* LEFT PANEL */
        .panel-left {
            flex: 1.1;
            background: var(--navy);
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            padding: 3rem;
        }

        .panel-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                repeating-linear-gradient(
                    -55deg,
                    transparent,
                    transparent 60px,
                    rgba(200,168,75,.04) 60px,
                    rgba(200,168,75,.04) 61px
                );
        }

        .deco-ring {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(200,168,75,.12);
        }

        .deco-ring-1 {
            width: 600px;
            height: 600px;
        }

        .deco-ring-2 {
            width: 420px;
            height: 420px;
        }

        .deco-ring-3 {
            width: 240px;
            height: 240px;
        }

        .brand-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
        }

        .brand-icon {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            background: rgba(200,168,75,.15);
            border: 1px solid rgba(200,168,75,.35);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
        }

        .brand-icon i {
            font-size: 2rem;
            color: var(--gold);
        }

        .brand-title {
            font-size: 2.3rem;
            font-weight: 700;
            margin-bottom: .8rem;
            line-height: 1.5;
        }

        .brand-sub {
            font-size: .9rem;
            color: rgba(255,255,255,.6);
            letter-spacing: .1em;
            margin-bottom: 2rem;
        }

        .divider-gold {
            width: 50px;
            height: 2px;
            background: var(--gold);
            margin: 0 auto 2rem;
        }

        .brand-quote {
            font-size: 1rem;
            color: rgba(255,255,255,.6);
            line-height: 1.8;
        }

        /* RIGHT PANEL */
        .panel-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem;
            background: #f0f2f5;
        }

        .form-card {
            width: 100%;
            max-width: 420px;
        }

        .top-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 4rem;
        }

        .top-logo img {
            height: 140px;
        }

        .alert-session {
            padding: .8rem 1rem;
            background: rgba(200,168,75,.12);
            border-right: 3px solid var(--gold);
            border-radius: 6px;
            margin-bottom: 1.5rem;
            color: var(--navy);
            font-size: .9rem;
        }

        .field {
            margin-bottom: 1.5rem;
        }

        .field label {
            display: block;
            margin-bottom: .5rem;
            color: var(--navy-l);
            font-size: .8rem;
            font-weight: 600;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap i {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #aab4c0;
        }

        .input-wrap input {
            width: 100%;
            padding: .85rem 2.8rem .85rem 1rem;
            border: 1.5px solid #dde3eb;
            border-radius: 8px;
            font-family: 'Cairo', sans-serif;
            font-size: .9rem;
            outline: none;
            transition: .2s;
        }

        .input-wrap input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(200,168,75,.12);
        }

        .field-error {
            margin-top: .4rem;
            font-size: .75rem;
            color: #c0392b;
        }

        .row-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.8rem;
            font-size: .85rem;
        }

        .check-label {
            display: flex;
            align-items: center;
            gap: .5rem;
            color: #6b7a8d;
        }

        .forgot-link {
            color: var(--gold-d);
            text-decoration: none;
            font-weight: 600;
        }

        .btn-submit {
            width: 100%;
            padding: .95rem;
            border: none;
            border-radius: 8px;
            background: var(--navy);
            color: white;
            font-family: 'Cairo', sans-serif;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            transition: .25s;
        }

        .btn-submit:hover {
            background: var(--navy-d);
        }

        .form-footer {
            margin-top: 2rem;
            text-align: center;
            color: #95a0ac;
            font-size: .8rem;
        }

        .form-footer strong {
            color: var(--navy);
        }

        @media (max-width: 768px) {
            .panel-left {
                display: none;
            }

            .panel-right {
                background: var(--cream);
            }
        }
    </style>
</head>
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
                الإدارة القانونية<br>
                والشكايات
            </h1>

            <div class="divider-gold"></div>

            <p class="brand-quote">
                « منصة موحدة لتتبع الملفات والشكايات القانونية »
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

                    <div class="input-wrap">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="••••••••"
                            required
                        >
                        <i class="bi bi-lock"></i>
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

</body>
</html>