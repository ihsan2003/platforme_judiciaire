@extends('layouts.app')

@section('title', 'Utilisateur — ' . $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Utilisateurs</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@section('content')

@php
    $initials = collect(explode(' ', $user->name))
        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
        ->take(2)->implode('');

    $isAdmin = $user->hasRole('admin');
    $isSelf  = $user->id === auth()->id();
@endphp

{{-- ══ EN-TÊTE ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">

            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-15 d-flex align-items-center justify-content-center fw-bold text-primary"
                     style="width:64px;height:64px;font-size:1.4rem;flex-shrink:0">
                    {{ $initials }}
                </div>
                <div>
                    <h4 class="fw-bold mb-0">{{ $user->name }}</h4>
                    <div class="text-muted small mt-1">
                        <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                    </div>
                    <div class="mt-2 d-flex flex-wrap gap-2">
                        @foreach($user->roles as $role)
                        @php
                            $rc = match($role->name) {
                                'admin'   => ['warning', 'shield-fill-check'],
                                'manager' => ['primary', 'person-gear'],
                                default   => ['secondary', 'person'],
                            };
                        @endphp
                        <span class="badge bg-{{ $rc[0] }} {{ $rc[0] === 'warning' ? 'text-dark' : 'text-white' }} border border-{{ $rc[0] }} border-opacity-25">
                            <i class="bi bi-{{ $rc[1] }} me-1"></i>{{ ucfirst($role->name) }}
                        </span>
                        @endforeach

                        @if($isSelf)
                        <span class="badge bg-info bg-opacity-15 text-white border border-info border-opacity-25">
                            <i class="bi bi-person-check me-1"></i>Vous
                        </span>
                        @endif

                        @if($user->email_verified_at)
                        <span class="badge bg-success bg-opacity-15 text-white border border-success border-opacity-25">
                            <i class="bi bi-patch-check me-1"></i>E-mail vérifié
                        </span>
                        @else
                        <span class="badge bg-danger bg-opacity-15 text-white border border-danger border-opacity-25">
                            <i class="bi bi-patch-exclamation me-1"></i>E-mail non vérifié
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
                @if(!$isSelf)
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                      onsubmit="return confirm('Supprimer l\'utilisateur « {{ $user->name }} » ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash me-1"></i>Supprimer
                    </button>
                </form>
                @endif
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>
        </div>

        <hr class="my-3">
        <div class="row g-2 small text-muted">
            <div class="col-sm-3">
                <i class="bi bi-calendar-plus me-1"></i>
                <strong>Créé le :</strong> {{ $user->created_at->format('d/m/Y à H:i') }}
            </div>
            <div class="col-sm-3">
                <i class="bi bi-pencil me-1"></i>
                <strong>Modifié le :</strong> {{ $user->updated_at->format('d/m/Y') }}
            </div>
            <div class="col-sm-3">
                <i class="bi bi-fingerprint me-1"></i>
                <strong>ID :</strong> <span class="font-monospace">{{ $user->id }}</span>
            </div>
            <div class="col-sm-3">
                <i class="bi bi-clock me-1"></i>
                <strong>Membre depuis :</strong> {{ $user->created_at->diffForHumans() }}
            </div>
        </div>
    </div>
</div>

{{-- ══ CONTENU ══ --}}
<div class="row g-4">

    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">

        {{-- Informations du compte --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-fill me-2 text-primary"></i>Informations du compte
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 rounded border h-100">
                            <div class="text-muted small fw-semibold mb-1">Nom complet</div>
                            <div class="fw-semibold">{{ $user->name }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded border h-100">
                            <div class="text-muted small fw-semibold mb-1">Adresse e-mail</div>
                            <div class="fw-semibold">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded border h-100">
                            <div class="text-muted small fw-semibold mb-1">Vérification e-mail</div>
                            @if($user->email_verified_at)
                                <span class="badge bg-success bg-opacity-15 text-white border border-success border-opacity-25">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Vérifié le {{ $user->email_verified_at->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-15 text-white border border-danger border-opacity-25">
                                    <i class="bi bi-x-circle me-1"></i>Non vérifié
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded border h-100">
                            <div class="text-muted small fw-semibold mb-1">Rôle(s) attribué(s)</div>
                            @foreach($user->roles as $role)
                            @php
                                $rc = match($role->name) {
                                    'admin'   => ['warning', 'shield-fill-check'],
                                    'manager' => ['primary', 'person-gear'],
                                    default   => ['secondary', 'person'],
                                };
                            @endphp
                            <span class="badge bg-{{ $rc[0] }} {{ $rc[0] === 'warning' ? 'text-dark' : 'text-white' }} me-1">
                                <i class="bi bi-{{ $rc[1] }} me-1"></i>{{ ucfirst($role->name) }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Activité --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-activity me-2 text-success"></i>Activité
                </h6>
            </div>
            <div class="list-group list-group-flush small">
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-folder2-open me-2 text-primary"></i>Dossiers créés</span>
                    <span class="badge bg-primary rounded-pill">{{ $user->dossiersCrees->count() }}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-hammer me-2 text-secondary"></i>Jugements saisis</span>
                    <span class="badge bg-secondary rounded-pill">{{ $user->jugementsCrees->count() }}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-shield-check me-2 text-info"></i>Exécutions responsable</span>
                    <span class="badge bg-info rounded-pill">{{ $user->executionsResponsable->count() }}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-chat-left-text me-2 text-warning"></i>Actions sur réclamations</span>
                    <span class="badge bg-warning text-dark rounded-pill">{{ $user->actionsReclamations->count() }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Colonne latérale ── --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-muted"></i>Résumé
                </h6>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-6 text-muted fw-normal">Identifiant</dt>
                    <dd class="col-6 font-monospace">#{{ $user->id }}</dd>

                    <dt class="col-6 text-muted fw-normal">Compte créé</dt>
                    <dd class="col-6">{{ $user->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">Dernière MAJ</dt>
                    <dd class="col-6">{{ $user->updated_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">E-mail vérifié</dt>
                    <dd class="col-6">
                        @if($user->email_verified_at)
                            <span class="badge bg-success bg-opacity-15 text-white">Oui</span>
                        @else
                            <span class="badge bg-danger bg-opacity-15 text-white">Non</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>

        {{-- Actions rapides --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 d-flex flex-column gap-2">
                <a href="{{ route('admin.users.edit', $user) }}"
                   class="btn btn-warning w-100 btn-sm">
                    <i class="bi bi-pencil me-1"></i>Modifier cet utilisateur
                </a>
                @if(!$isSelf)
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                      onsubmit="return confirm('Supprimer l\'utilisateur « {{ $user->name }} » ? Cette action est irréversible.')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger w-100 btn-sm">
                        <i class="bi bi-trash me-1"></i>Supprimer ce compte
                    </button>
                </form>
                @else
                <div class="alert alert-info border-0 small py-2 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Vous ne pouvez pas supprimer votre propre compte.
                </div>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection