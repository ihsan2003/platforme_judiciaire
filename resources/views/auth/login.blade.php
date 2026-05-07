<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion — Plateforme Juridique</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:   #1a3a5c;
            --navy-d: #112944;
            --navy-l: #234d77;
            --gold:   #c8a84b;
            --gold-l: #e0c26e;
            --gold-d: #a8882e;
            --cream:  #faf8f3;
            --smoke:  #f0ede6;
        }

        html, body {
            height: 100%;
            font-family: 'Jost', sans-serif;
            background: #f0f2f5;
            overflow: hidden;
        }

        /* ── Layout ─────────────────────────────────────── */
        .page {
            display: flex;
            height: 100vh;
        }

        /* ── Left panel ─────────────────────────────────── */
        .panel-left {
            flex: 1.1;
            background: var(--navy);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            padding: 3rem;
        }

        /* Geometric decorative lines */
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

        /* Large decorative circle */
        .deco-ring {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(200,168,75,.12);
        }
        .deco-ring-1 {
            width: 600px; height: 600px;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
        }
        .deco-ring-2 {
            width: 420px; height: 420px;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            border-color: rgba(200,168,75,.08);
        }
        .deco-ring-3 {
            width: 240px; height: 240px;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            border-color: rgba(200,168,75,.15);
        }

        /* Corner ornaments */
        .ornament {
            position: absolute;
            width: 80px; height: 80px;
            border-color: rgba(200,168,75,.25);
            border-style: solid;
        }
        .ornament-tl { top: 28px; left: 28px; border-width: 2px 0 0 2px; }
        .ornament-br { bottom: 28px; right: 28px; border-width: 0 2px 2px 0; }

        /* Brand content */
        .brand-content {
            position: relative;
            z-index: 2;
            text-align: center;
            animation: fadeUp .8s ease both;
        }

        .brand-icon {
            width: 72px; height: 72px;
            border-radius: 18px;
            background: rgba(200,168,75,.15);
            border: 1px solid rgba(200,168,75,.35);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 2rem;
            backdrop-filter: blur(4px);
        }
        .brand-icon i { font-size: 2rem; color: var(--gold); }

        .brand-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.4rem;
            font-weight: 600;
            color: #fff;
            line-height: 1.2;
            letter-spacing: .01em;
            margin-bottom: .6rem;
        }

        .brand-sub {
            font-size: .8rem;
            font-weight: 300;
            color: rgba(255,255,255,.45);
            letter-spacing: .18em;
            text-transform: uppercase;
            margin-bottom: 2.5rem;
        }

        .divider-gold {
            width: 40px; height: 2px;
            background: var(--gold);
            margin: 0 auto 2.5rem;
            opacity: .7;
        }

        .brand-quote {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.05rem;
            color: rgba(255,255,255,.5);
            font-style: italic;
            max-width: 280px;
            line-height: 1.7;
        }


        /* ── Right panel ─────────────────────────────────── */
        .panel-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem;
            background: #f0f2f5;
            position: relative;
        }

        /* Subtle texture */
        .panel-right::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 80% 20%, rgba(200,168,75,.15) 0%, transparent 60%);
            pointer-events: none;
        }

        .top-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 5rem;
        }

        .top-logo img {
            height: 150px;
            width: auto;
            object-fit: contain;
        }

        /* ── Form card ───────────────────────────────────── */
        .form-card {
            width: 100%;
            max-width: 400px;
            animation: fadeUp .6s .1s ease both;
        }

        
        /* ── Alert ───────────────────────────────────────── */
        .alert-session {
            padding: .7rem 1rem;
            background: rgba(200,168,75,.12);
            border-left: 3px solid var(--gold);
            border-radius: 4px;
            font-size: .82rem;
            color: var(--navy);
            margin-bottom: 1.5rem;
        }

        /* ── Field ───────────────────────────────────────── */
        .field {
            margin-bottom: 1.4rem;
        }

        .field label {
            display: block;
            font-size: .73rem;
            font-weight: 500;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--navy-l);
            margin-bottom: .5rem;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #b0bbc8;
            font-size: .9rem;
            pointer-events: none;
            transition: color .2s;
        }

        .input-wrap input {
            width: 100%;
            padding: .75rem 1rem .75rem 2.6rem;
            border: 1.5px solid #dde3eb;
            border-radius: 8px;
            background: #fff;
            font-family: 'Jost', sans-serif;
            font-size: .88rem;
            color: var(--navy);
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }

        .input-wrap input::placeholder { color: #b8c2ce; }

        .input-wrap input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(200,168,75,.12);
        }

        .input-wrap input:focus + i,
        .input-wrap:focus-within i { color: var(--gold); }

        /* Reorder icon after input in DOM but visually position left */
        .input-icon-left { order: -1; pointer-events: none; }

        .field-error {
            margin-top: .35rem;
            font-size: .75rem;
            color: #c0392b;
        }

        /* ── Row options ─────────────────────────────────── */
        .row-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.8rem;
            font-size: .8rem;
        }

        .check-label {
            display: flex;
            align-items: center;
            gap: .5rem;
            cursor: pointer;
            color: #6b7a8d;
            user-select: none;
        }

        .check-label input[type="checkbox"] {
            accent-color: var(--gold);
            width: 15px; height: 15px;
            cursor: pointer;
        }

        .forgot-link {
            color: var(--gold-d);
            text-decoration: none;
            font-weight: 500;
            font-size: .78rem;
            letter-spacing: .03em;
            transition: color .2s;
        }
        .forgot-link:hover { color: var(--navy); }

        /* ── Submit button ───────────────────────────────── */
        .btn-submit {
            width: 100%;
            padding: .85rem;
            background: var(--navy);
            color: #fff;
            font-family: 'Jost', sans-serif;
            font-size: .85rem;
            font-weight: 500;
            letter-spacing: .12em;
            text-transform: uppercase;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: background .25s, transform .15s, box-shadow .25s;
        }

        .btn-submit::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(200,168,75,.18) 50%, transparent 100%);
            transform: translateX(-100%);
            transition: transform .4s ease;
        }

        .btn-submit:hover {
            background: var(--navy-d);
            box-shadow: 0 6px 20px rgba(26,58,92,.3);
            transform: translateY(-1px);
        }

        .btn-submit:hover::after {
            transform: translateX(100%);
        }

        .btn-submit:active { transform: translateY(0); }

        /* ── Footer ──────────────────────────────────────── */
        .form-footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e8ecf0;
            text-align: center;
        }

        .footer-copy {
            font-size: .72rem;
            color: #9ea9b5;
            letter-spacing: .05em;
        }

        .footer-copy strong {
            color: var(--navy-l);
            font-weight: 500;
        }

        /* ── Animations ──────────────────────────────────── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Responsive ──────────────────────────────────── */
        @media (max-width: 768px) {
            .panel-left { display: none; }
            .panel-right { background: var(--cream); }
        }
    </style>
</head>
<body>

<div class="page">

    {{-- ── Left panel ──────────────────────────────────────── --}}
    <div class="panel-left">
        <div class="deco-ring deco-ring-1"></div>
        <div class="deco-ring deco-ring-2"></div>
        <div class="deco-ring deco-ring-3"></div>
        <div class="ornament ornament-tl"></div>
        <div class="ornament ornament-br"></div>

        <div class="brand-content">
            <div class="brand-icon">
                <i class="bi bi-bank2"></i>
            </div>
            <p class="brand-sub">Entraide Nationale</p>
            <h1 class="brand-title">Gestion Juridique<br>& Réclamations</h1>
            <div class="divider-gold"></div>
            <p class="brand-quote">
            « Plateforme centralisée pour le suivi des dossiers et des réclamations. »           </p>
        </div>

    </div>

    {{-- ── Right panel ─────────────────────────────────────── --}}
    <div class="panel-right">
    
        <div class="form-card">

            <div class="top-logo">
                <img src="{{ asset('images/logo.png') }}" alt="Logo entraide">
            </div>

            {{-- Session Status --}}
            @if(session('status'))
                <div class="alert-session">
                    <i class="bi bi-info-circle me-1"></i> {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field">
                    <label for="email">Adresse e-mail</label>
                    <div class="input-wrap">
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="votre@email.ma"
                            required
                            autofocus
                            autocomplete="username"
                        >
                        <i class="bi bi-envelope"></i>
                    </div>
                    @error('email')
                        <p class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">Mot de passe</label>
                    <div class="input-wrap">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                        >
                        <i class="bi bi-lock"></i>
                    </div>
                    @error('password')
                        <p class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <div class="row-options">
                    <label class="check-label">
                        <input type="checkbox" name="remember">
                        Se souvenir de moi
                    </label>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            Mot de passe oublié ?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn-submit">
                    <i class="bi bi-arrow-right-circle me-2"></i>
                    Se connecter
                </button>
            </form>

            <div class="form-footer">
                <p class="footer-copy">
                    © {{ date('Y') }} <strong>Entraide Nationale</strong> — Tous droits réservés
                </p>
            </div>

        </div>
    </div>

</div>

</body>
</html>