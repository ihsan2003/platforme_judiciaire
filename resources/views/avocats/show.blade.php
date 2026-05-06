@extends('layouts.app')

@section('title', $avocat->nom_avocat)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('avocats.index') }}">Avocats</a></li>
    <li class="breadcrumb-item active">{{ $avocat->nom_avocat }}</li>
@endsection

@section('content')

{{-- ══ EN-TÊTE ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">

            {{-- Identité --}}
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:56px;height:56px">
                    <span class="fw-bold text-primary fs-5">
                        {{ strtoupper(substr($avocat->nom_avocat, 0, 2)) }}
                    </span>
                </div>
                <div>
                    <h4 class="fw-bold mb-0">{{ $avocat->nom_avocat }}</h4>
                    <div class="text-muted small mt-1">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                            <i class="bi bi-person-badge me-1"></i>Avocat
                        </span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('avocats.edit', $avocat) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
                <form action="{{ route('avocats.destroy', $avocat) }}" method="POST"
                      onsubmit="return confirm('Supprimer cet avocat ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm"
                            @if($avocat->dossierParties()->count() > 0) disabled title="Impossible : dossiers liés" @endif>
                        <i class="bi bi-trash me-1"></i>Supprimer
                    </button>
                </form>
                <a href="{{ route('avocats.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>
        </div>

        <hr class="my-3">

        {{-- Coordonnées rapides --}}
        <div class="row g-2 small text-muted">
            <div class="col-sm-4">
                <i class="bi bi-telephone me-1"></i>
                <strong>Téléphone :</strong>
                @if($avocat->telephone)
                    <a href="tel:{{ $avocat->telephone }}" class="text-decoration-none ms-1">
                        {{ $avocat->telephone }}
                    </a>
                @else
                    <span class="ms-1">—</span>
                @endif
            </div>
            <div class="col-sm-4">
                <i class="bi bi-envelope me-1"></i>
                <strong>Email :</strong>
                @if($avocat->email)
                    <a href="mailto:{{ $avocat->email }}" class="text-decoration-none ms-1">
                        {{ $avocat->email }}
                    </a>
                @else
                    <span class="ms-1">—</span>
                @endif
            </div>
            <div class="col-sm-4">
                <i class="bi bi-clock me-1"></i>
                <strong>Mis à jour :</strong>
                <span class="ms-1">{{ $avocat->updated_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ── Colonne principale : dossiers ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-folder2-open me-2 text-primary"></i>Dossiers associés
                    <span class="badge bg-primary ms-1">{{ $avocat->dossierParties->count() }}</span>
                </h6>
            </div>

            @if($avocat->dossierParties->isEmpty())
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-folder-x fs-1 d-block mb-2 opacity-25"></i>
                    Aucun dossier associé à cet avocat.
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 small text-muted fw-semibold">Dossier</th>
                            <th class="small text-muted fw-semibold">Rôle de la partie</th>
                            <th class="small text-muted fw-semibold">Partie représentée</th>
                            <th class="small text-muted fw-semibold">Date d'entrée</th>
                            <th class="text-end pe-3 small text-muted fw-semibold">Voir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($avocat->dossierParties as $dp)
                        <tr>
                            <td class="ps-3">
                                @if($dp->dossier)
                                    <a href="{{ route('dossiers.show', $dp->dossier) }}"
                                       class="text-decoration-none fw-semibold text-primary">
                                        {{ $dp->dossier->numero_dossier_interne ?? '—' }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                    {{ $dp->typePartie->type_partie ?? '—' }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ $dp->partie->nom_partie ?? '—' }}
                            </td>
                            <td class="text-muted small">
                                {{ $dp->date_entree?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="text-end pe-3">
                                @if($dp->dossier)
                                <a href="{{ route('dossiers.show', $dp->dossier) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Colonne latérale ── --}}
    <div class="col-lg-4">

        {{-- Infos ── --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-muted"></i>Informations
                </h6>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-6 text-muted fw-normal">Créé le</dt>
                    <dd class="col-6">{{ $avocat->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">Modifié le</dt>
                    <dd class="col-6">{{ $avocat->updated_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">Dossiers</dt>
                    <dd class="col-6">
                        <span class="badge bg-info bg-opacity-15 text-white border border-info border-opacity-25">
                            {{ $avocat->dossierParties->count() }} dossier(s)
                        </span>
                    </dd>
                </dl>
            </div>
        </div>

        {{-- Alerte suppression ── --}}
        @if($avocat->dossierParties->count() > 0)
        <div class="card border-0 shadow-sm" style="border-left: 3px solid #f59e0b !important;">
            <div class="card-body small">
                <div class="fw-semibold mb-1">
                    <i class="bi bi-exclamation-triangle text-warning me-1"></i>Suppression impossible
                </div>
                <div class="text-muted">
                    Cet avocat est lié à
                    <strong>{{ $avocat->dossierParties->count() }}</strong> dossier(s).
                    Retirez ces liens avant de le supprimer.
                </div>
            </div>
        </div>
        @else
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 text-center">
                <form action="{{ route('avocats.destroy', $avocat) }}" method="POST"
                      onsubmit="return confirm('Supprimer définitivement cet avocat ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm w-100">
                        <i class="bi bi-trash me-1"></i>Supprimer cet avocat
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>

</div>

@endsection