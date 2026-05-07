@extends('layouts.app')

@section('title', 'Modifier — ' . $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Utilisateurs</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')

@php
    $initials = collect(explode(' ', $user->name))
        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
        ->take(2)->implode('');
    $currentRole = $user->roles->first()?->name ?? '';
@endphp

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning me-2"></i>Modifier l'utilisateur
        </h4>
        <p class="text-muted small mb-0">
            Compte de <strong>{{ $user->name }}</strong>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour à la fiche
        </a>
    </div>
</div>

<form action="{{ route('admin.users.update', $user) }}" method="POST">
@csrf @method('PUT')

<div class="row g-4">

    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">

        {{-- Informations personnelles --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-fill me-2 text-warning"></i>Informations personnelles
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
                    </div>

                </div>
            </div>
        </div>

        {{-- Mot de passe (optionnel) --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-lock-fill me-2 text-secondary"></i>Changer le mot de passe
                    <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" style="font-size:.68rem">Optionnel</span>
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info border-0 small mb-3 py-2">
                    <i class="bi bi-info-circle me-1"></i>
                    Laissez ces champs vides pour conserver le mot de passe actuel.
                </div>
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Nouveau mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-key text-muted"></i>
                            </span>
                            <input type="password"
                                   name="password"
                                   id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Min. 8 caractères"
                                   oninput="checkStrength(this.value)">
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePwd('password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

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
                        <label class="form-label fw-semibold small">Confirmer le nouveau mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-key-fill text-muted"></i>
                            </span>
                            <input type="password"
                                   name="password_confirmation"
                                   id="password_confirmation"
                                   class="form-control"
                                   placeholder="Répétez le mot de passe">
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePwd('password_confirmation', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Rôle --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-shield-fill-check me-2 text-primary"></i>Rôle & Permissions
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach($roles as $role)
                    @php
                        $roleInfo = match($role->name) {
                            'admin'   => ['warning', 'shield-fill-check', 'Accès complet à toutes les fonctionnalités et à l\'administration.'],
                            'manager' => ['primary', 'person-gear', 'Gestion des dossiers, audiences, jugements et réclamations.'],
                            default   => ['secondary', 'person', 'Accès en lecture et opérations de base.'],
                        };
                        $selected = old('role', $currentRole) === $role->name;
                    @endphp
                    <div class="col-md-4">
                        <label class="card border h-100 p-3 {{ $selected ? 'border-primary' : '' }}"
                               style="cursor:pointer;transition:border-color .15s"
                               for="role_{{ $role->name }}">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <input type="radio"
                                       name="role"
                                       id="role_{{ $role->name }}"
                                       value="{{ $role->name }}"
                                       class="form-check-input mt-0"
                                       @checked($selected)
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
                    <div class="text-danger small mt-2"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                @enderror
            </div>
        </div>

    </div>

    {{-- ── Colonne latérale ── --}}
    <div class="col-lg-4">

        {{-- Résumé --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-muted"></i>Résumé du compte
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-15 d-flex align-items-center justify-content-center fw-bold text-white"
                         style="width:48px;height:48px;font-size:1rem;flex-shrink:0">
                        {{ $initials }}
                    </div>
                    <div class="small">
                        <div class="fw-semibold">{{ $user->name }}</div>
                        <div class="text-muted">{{ $user->email }}</div>
                    </div>
                </div>
                <dl class="row small mb-0">
                    <dt class="col-6 text-muted fw-normal">ID</dt>
                    <dd class="col-6 font-monospace">#{{ $user->id }}</dd>

                    <dt class="col-6 text-muted fw-normal">Créé le</dt>
                    <dd class="col-6">{{ $user->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">Rôle actuel</dt>
                    <dd class="col-6">
                        <span class="badge bg-{{ match($currentRole){ 'admin' => 'warning', 'manager' => 'primary', default => 'secondary' } }} {{ $currentRole === 'admin' ? 'text-dark' : 'text-white' }}">
                            {{ ucfirst($currentRole ?: '—') }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>

    </div>
</div>

{{-- Actions --}}
<div class="d-flex gap-2 justify-content-end mt-2">
    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>Annuler
    </a>
    <button type="submit" class="btn btn-warning px-4">
        <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
    </button>
</div>

</form>

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