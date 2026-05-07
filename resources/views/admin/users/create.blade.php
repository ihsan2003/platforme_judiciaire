@extends('layouts.app')

@section('title', 'Nouvel utilisateur')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Utilisateurs</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-person-plus text-primary me-2"></i>Nouvel utilisateur
        </h4>
        <p class="text-muted small mb-0">Créez un compte et assignez un rôle.</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
</div>

<div class="row g-4">

    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">

        <form action="{{ route('admin.users.store') }}" method="POST" id="formCreateUser">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-fill me-2 text-primary"></i>Informations personnelles
                </h6>
            </div>
            <div class="card-body">
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
                                   value="{{ old('name') }}"
                                   placeholder="Prénom Nom"
                                   required autofocus>
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
                                   value="{{ old('email') }}"
                                   placeholder="utilisateur@exemple.ma"
                                   required>
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-lock-fill me-2 text-warning"></i>Sécurité & Rôle
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            Mot de passe <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-key text-muted"></i>
                            </span>
                            <input type="password"
                                   name="password"
                                   id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Min. 8 caractères"
                                   required
                                   oninput="checkStrength(this.value)">
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePwd('password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        {{-- Indicateur de force --}}
                        <div class="mt-2" id="strength-wrap" style="display:none">
                            <div class="d-flex gap-1 mb-1">
                                @for($i = 1; $i <= 4; $i++)
                                    <div class="flex-fill" style="height:4px;border-radius:2px;background:#e2e8f0" id="bar{{ $i }}"></div>
                                @endfor
                            </div>
                            <div class="small" id="strength-label" style="font-size:.75rem;color:#94a3b8"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            Confirmer le mot de passe <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-key-fill text-muted"></i>
                            </span>
                            <input type="password"
                                   name="password_confirmation"
                                   id="password_confirmation"
                                   class="form-control"
                                   placeholder="Répétez le mot de passe"
                                   required>
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePwd('password_confirmation', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            Rôle <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2 mt-1">
                            @foreach($roles as $role)
                            @php
                                $roleInfo = match($role->name) {
                                    'admin'   => ['warning', 'shield-fill-check', 'Accès complet à toutes les fonctionnalités et à l\'administration.'],
                                    'manager' => ['primary', 'person-gear', 'Gestion des dossiers, audiences, jugements et réclamations.'],
                                    default   => ['secondary', 'person', 'Accès en lecture et opérations de base.'],
                                };
                            @endphp
                            <div class="col-md-4">
                                <label class="card border cursor-pointer h-100 p-3 {{ old('role') === $role->name ? 'border-primary' : '' }}"
                                       style="cursor:pointer;transition:border-color .15s"
                                       for="role_{{ $role->name }}">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <input type="radio"
                                               name="role"
                                               id="role_{{ $role->name }}"
                                               value="{{ $role->name }}"
                                               class="form-check-input mt-0"
                                               @checked(old('role') === $role->name)
                                               required>
                                        <i class="bi bi-{{ $roleInfo[1] }} text-{{ $roleInfo[0] }}"></i>
                                        <span class="fw-semibold small">{{ ucfirst($role->name) }}</span>
                                    </div>
                                    <p class="text-muted mb-0" style="font-size:.75rem;line-height:1.4">{{ $roleInfo[2] }}</p>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @error('role')
                            <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i>Annuler
            </a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i>Créer l'utilisateur
            </button>
        </div>

        </form>
    </div>

    {{-- ── Colonne latérale ── --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
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
                    <li>Ne réutilisez pas d'anciens mots de passe</li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-info"></i>À propos des rôles
                </h6>
            </div>
            <div class="card-body small text-muted">
                <p class="mb-2">Le rôle détermine ce que l'utilisateur peut voir et faire dans l'application.</p>
                <p class="mb-0">Il peut être modifié à tout moment depuis la fiche de l'utilisateur.</p>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

function checkStrength(val) {
    const wrap  = document.getElementById('strength-wrap');
    const label = document.getElementById('strength-label');
    const bars  = [1,2,3,4].map(i => document.getElementById('bar' + i));
    if (!val) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';
    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    score = Math.min(4, score);
    const colors = ['#ef4444','#f97316','#eab308','#22c55e'];
    const labels = ['Très faible','Faible','Moyen','Fort'];
    bars.forEach((b, i) => { b.style.background = i < score ? colors[score - 1] : '#e2e8f0'; });
    label.textContent = labels[score - 1] || 'Trop court';
    label.style.color = score > 0 ? colors[score - 1] : '#94a3b8';
}

// Highlight role card on selection
document.querySelectorAll('input[name="role"]').forEach(radio => {
    radio.addEventListener('change', function () {
        document.querySelectorAll('input[name="role"]').forEach(r => {
            r.closest('label').classList.remove('border-primary');
        });
        this.closest('label').classList.add('border-primary');
    });
});
</script>
@endpush