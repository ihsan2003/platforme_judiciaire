@extends('layouts.app')

@section('title', 'Mon profil')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item active">Mon profil</li>
@endsection

@push('styles')
<style>
.profile-header {
    background: #1a3a5c;
    border-radius: 12px;
    padding: 28px 32px;
    color: #fff;
    margin-bottom: 24px;
}
.avatar-circle {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: rgba(200,168,75,.2);
    border: 2px solid rgba(200,168,75,.4);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem;
    font-weight: 700;
    color: #c8a84b;
    flex-shrink: 0;
}
.profile-tab .nav-link {
    font-weight: 600;
    font-size: .85rem;
    color: #64748b;
    border: none;
    padding: .6rem 1.1rem;
    border-bottom: 3px solid transparent;
    border-radius: 0;
    transition: all .15s;
}
.profile-tab .nav-link.active {
    color: #1a3a5c;
    border-bottom-color: #1a3a5c;
    background: none;
}
.profile-tab .nav-link:hover:not(.active) {
    color: #1a3a5c;
    border-bottom-color: #e2e8f0;
    background: none;
}
.section-icon {
    width: 36px; height: 36px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.danger-zone {
    border: 1.5px solid #fee2e2;
    border-radius: 12px;
    background: #fff;
}
.danger-zone .card-header {
    background: #fff5f5;
    border-bottom: 1px solid #fee2e2;
    border-radius: 12px 12px 0 0 !important;
}
</style>
@endpush

@section('content')

@php
    $initials = collect(explode(' ', auth()->user()->name))
        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
        ->take(2)
        ->implode('');
    $roles = auth()->user()->getRoleNames();
@endphp

{{-- ══ EN-TÊTE PROFIL ══ --}}
<div class="profile-header mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-circle">{{ $initials }}</div>
            <div>
                <h4 class="fw-bold mb-0 text-white">{{ auth()->user()->name }}</h4>
                <div class="small mt-1" style="opacity:.7">
                    <i class="bi bi-envelope me-1"></i>{{ auth()->user()->email }}
                </div>
                <div class="mt-2 d-flex flex-wrap gap-2">
                    @foreach($roles as $role)
                        <span class="badge" style="background:rgba(200,168,75,.2);color:#c8a84b;border:1px solid rgba(200,168,75,.3);font-size:.72rem">
                            <i class="bi bi-shield-check me-1"></i>{{ ucfirst($role) }}
                        </span>
                    @endforeach
                    <span class="badge" style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.7);font-size:.72rem">
                        <i class="bi bi-clock me-1"></i>Membre depuis {{ auth()->user()->created_at->format('M Y') }}
                    </span>
                </div>
            </div>
        </div>
        <div class="text-end small" style="opacity:.6">
            <div><i class="bi bi-calendar-check me-1"></i>Dernière connexion : {{ now()->format('d/m/Y à H:i') }}</div>
        </div>
    </div>
</div>

{{-- ══ ONGLETS ══ --}}
<ul class="nav profile-tab border-bottom mb-0" id="profileTabs">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-infos">
            <i class="bi bi-person me-1"></i>Informations
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-password">
            <i class="bi bi-lock me-1"></i>Mot de passe
        </button>
    </li>
</ul>

<div class="tab-content border border-top-0 rounded-bottom bg-white shadow-sm p-4" id="profileTabContent">

    {{-- ── ONGLET 1 : INFORMATIONS ── --}}
    <div class="tab-pane fade show active" id="tab-infos">

        <div class="row g-4">
            <div class="col-lg-8">

                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="section-icon bg-primary bg-opacity-10">
                        <i class="bi bi-person-fill text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-semibold mb-0">Informations personnelles</h6>
                        <div class="small text-muted">Mettez à jour votre nom et votre adresse e-mail.</div>
                    </div>
                </div>

                @if(session('status') === 'profile-updated')
                    <div class="alert alert-success alert-dismissible fade show small">
                        <i class="bi bi-check-circle me-2"></i>Profil mis à jour avec succès.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PATCH')

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">
                                Nom complet <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-person text-muted"></i>
                                </span>
                                <input type="text"
                                       name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}"
                                       required>
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">
                                Adresse e-mail <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-envelope text-muted"></i>
                                </span>
                                <input type="email"
                                       name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}"
                                       required>
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                <div class="alert alert-warning small mt-2 py-2">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Votre adresse e-mail n'est pas vérifiée.
                                    <form id="send-verification" method="POST" action="{{ route('verification.send') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-link btn-sm p-0 ms-1">
                                            Renvoyer le lien de vérification
                                        </button>
                                    </form>
                                </div>
                                @if (session('status') === 'verification-link-sent')
                                    <div class="alert alert-success small mt-2 py-2">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Un nouveau lien de vérification a été envoyé.
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>

            {{-- Colonne droite : résumé compte --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-info-circle me-2 text-muted"></i>Résumé du compte
                        </h6>
                    </div>
                    <div class="card-body small">
                        <dl class="row mb-0">
                            <dt class="col-5 text-muted fw-normal">Identifiant</dt>
                            <dd class="col-7 font-monospace">#{{ auth()->user()->id }}</dd>

                            <dt class="col-5 text-muted fw-normal">Rôle(s)</dt>
                            <dd class="col-7">
                                @foreach($roles as $role)
                                    <span class="badge bg-primary bg-opacity-10 text-primary d-block mb-1">{{ ucfirst($role) }}</span>
                                @endforeach
                            </dd>

                            <dt class="col-5 text-muted fw-normal">Membre depuis</dt>
                            <dd class="col-7">{{ auth()->user()->created_at->format('d/m/Y') }}</dd>

                            <dt class="col-5 text-muted fw-normal">Mis à jour le</dt>
                            <dd class="col-7">{{ auth()->user()->updated_at->format('d/m/Y') }}</dd>

                            <dt class="col-5 text-muted fw-normal">E-mail vérifié</dt>
                            <dd class="col-7">
                                @if(auth()->user()->hasVerifiedEmail())
                                    <span class="badge bg-success bg-opacity-15 text-white border border-success border-opacity-25">
                                        <i class="bi bi-check-circle me-1"></i>Oui
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">Non</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── ONGLET 2 : MOT DE PASSE ── --}}
    <div class="tab-pane fade" id="tab-password">

        <div class="row g-4">
            <div class="col-lg-8">

                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="section-icon bg-warning bg-opacity-10">
                        <i class="bi bi-lock-fill text-warning"></i>
                    </div>
                    <div>
                        <h6 class="fw-semibold mb-0">Changer le mot de passe</h6>
                        <div class="small text-muted">Utilisez un mot de passe long et aléatoire pour sécuriser votre compte.</div>
                    </div>
                </div>

                @if(session('status') === 'password-updated')
                    <div class="alert alert-success alert-dismissible fade show small">
                        <i class="bi bi-check-circle me-2"></i>Mot de passe mis à jour avec succès.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ url('password') }}">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">
                                Mot de passe actuel <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                                <input type="password"
                                       name="current_password"
                                       id="current_password"
                                       class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                       autocomplete="current-password">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePwd('current_password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('current_password', 'updatePassword')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">
                                Nouveau mot de passe <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-key text-muted"></i>
                                </span>
                                <input type="password"
                                       name="password"
                                       id="new_password"
                                       class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                       autocomplete="new-password"
                                       oninput="checkStrength(this.value)">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePwd('new_password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password', 'updatePassword')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            {{-- Indicateur de force --}}
                            <div class="mt-2" id="strength-wrap" style="display:none">
                                <div class="d-flex gap-1 mb-1">
                                    <div class="flex-fill" style="height:4px;border-radius:2px;background:#e2e8f0" id="bar1"></div>
                                    <div class="flex-fill" style="height:4px;border-radius:2px;background:#e2e8f0" id="bar2"></div>
                                    <div class="flex-fill" style="height:4px;border-radius:2px;background:#e2e8f0" id="bar3"></div>
                                    <div class="flex-fill" style="height:4px;border-radius:2px;background:#e2e8f0" id="bar4"></div>
                                </div>
                                <div class="small" id="strength-label" style="color:#94a3b8;font-size:.75rem"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">
                                Confirmer le nouveau mot de passe <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-key-fill text-muted"></i>
                                </span>
                                <input type="password"
                                       name="password_confirmation"
                                       id="confirm_password"
                                       class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                                       autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePwd('confirm_password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password_confirmation', 'updatePassword')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-shield-lock me-1"></i>Mettre à jour le mot de passe
                        </button>
                    </div>
                </form>
            </div>

            {{-- Conseils sécurité --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-shield-check me-2 text-success"></i>Conseils de sécurité
                        </h6>
                    </div>
                    <div class="card-body small">
                        <ul class="ps-3 mb-0" style="line-height:2">
                            <li>Au moins <strong>8 caractères</strong></li>
                            <li>Mélangez <strong>majuscules</strong> et <strong>minuscules</strong></li>
                            <li>Ajoutez des <strong>chiffres</strong> et des <strong>symboles</strong></li>
                            <li>N'utilisez pas votre nom ou date de naissance</li>
                            <li>Ne réutilisez pas d'anciens mots de passe</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>


@endsection

@push('scripts')
<script>
{{-- Ouvrir le bon onglet selon le statut de session --}}
(function () {
    @if(session('status') === 'password-updated')
        const tab = document.querySelector('[data-bs-target="#tab-password"]');
        if (tab) new bootstrap.Tab(tab).show();
    @endif
    @if($errors->updatePassword->any())
        const tab = document.querySelector('[data-bs-target="#tab-password"]');
        if (tab) new bootstrap.Tab(tab).show();
    @endif
    
})();

{{-- Afficher/masquer mot de passe --}}
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

{{-- Indicateur de force du mot de passe --}}
function checkStrength(val) {
    const wrap = document.getElementById('strength-wrap');
    const label = document.getElementById('strength-label');
    const bars = [document.getElementById('bar1'), document.getElementById('bar2'),
                  document.getElementById('bar3'), document.getElementById('bar4')];

    if (!val) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';

    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    score = Math.min(4, score);

    const colors = ['#ef4444', '#f97316', '#eab308', '#22c55e'];
    const labels = ['Très faible', 'Faible', 'Moyen', 'Fort'];

    bars.forEach((b, i) => {
        b.style.background = i < score ? colors[score - 1] : '#e2e8f0';
    });
    label.textContent = labels[score - 1] || 'Trop court';
    label.style.color = score > 0 ? colors[score - 1] : '#94a3b8';
}
</script>
@endpush