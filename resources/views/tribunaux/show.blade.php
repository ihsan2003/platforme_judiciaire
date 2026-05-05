@extends('layouts.app')

@section('title', $tribunal->nom_tribunal)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tribunaux.index') }}">Tribunaux</a></li>
    <li class="breadcrumb-item active">{{ $tribunal->nom_tribunal }}</li>
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
                    <i class="bi bi-building fs-3 text-primary"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0">{{ $tribunal->nom_tribunal }}</h4>
                    <div class="mt-1 d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-25">
                            <i class="bi bi-tag me-1"></i>{{ $tribunal->typeTribunal->tribunal ?? '—' }}
                        </span>
                        @if($tribunal->province)
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                <i class="bi bi-geo-alt me-1"></i>{{ $tribunal->province->province }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('tribunaux.edit', $tribunal) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
                <form action="{{ route('tribunaux.destroy', $tribunal) }}" method="POST"
                      onsubmit="return confirm('Supprimer ce tribunal ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash me-1"></i>Supprimer
                    </button>
                </form>
                <a href="{{ route('tribunaux.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>
        </div>

        <hr class="my-3">

        {{-- Coordonnées rapides --}}
        <div class="row g-2 small text-muted">
            <div class="col-sm-4">
                <i class="bi bi-tag me-1"></i>
                <strong>Type :</strong>
                <span class="ms-1">{{ $tribunal->typeTribunal->tribunal ?? '—' }}</span>
            </div>
            <div class="col-sm-4">
                <i class="bi bi-geo-alt me-1"></i>
                <strong>Province :</strong>
                <span class="ms-1">{{ $tribunal->province->province ?? '—' }}</span>
            </div>
            <div class="col-sm-4">
                <i class="bi bi-map me-1"></i>
                <strong>Région :</strong>
                <span class="ms-1">{{ $tribunal->province->region->region ?? '—' }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ── Colonne principale : juges ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-workspace me-2 text-primary"></i>Juges rattachés
                    <span class="badge bg-primary ms-1">{{ $tribunal->juges->count() }}</span>
                </h6>
                <a href="{{ route('juges.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Ajouter un juge
                </a>
            </div>

            @if($tribunal->juges->isEmpty())
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-person-x fs-1 d-block mb-2 opacity-25"></i>
                    Aucun juge rattaché à ce tribunal.
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 small text-muted fw-semibold">Nom complet</th>
                            <th class="small text-muted fw-semibold">Grade</th>
                            <th class="small text-muted fw-semibold">Spécialisation</th>
                            <th class="text-end pe-3 small text-muted fw-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tribunal->juges as $juge)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:32px;height:32px">
                                        <span class="fw-semibold text-primary" style="font-size:.72rem">
                                            {{ strtoupper(substr($juge->nom_complet, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div class="fw-semibold">{{ $juge->nom_complet }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                    {{ $juge->grade ?? '—' }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $juge->specialisation ?? '—' }}</td>
                            <td class="text-end pe-3">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('juges.show', $juge) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('juges.edit', $juge) }}"
                                       class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
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

        {{-- Informations --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-muted"></i>Informations
                </h6>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-6 text-muted fw-normal">Type</dt>
                    <dd class="col-6">{{ $tribunal->typeTribunal->tribunal ?? '—' }}</dd>

                    <dt class="col-6 text-muted fw-normal">Province</dt>
                    <dd class="col-6">{{ $tribunal->province->province ?? '—' }}</dd>

                    <dt class="col-6 text-muted fw-normal">Région</dt>
                    <dd class="col-6">{{ $tribunal->province->region->region ?? '—' }}</dd>

                    <dt class="col-6 text-muted fw-normal">Juges</dt>
                    <dd class="col-6">
                        <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-25">
                            {{ $tribunal->juges->count() }} juge(s)
                        </span>
                    </dd>

                    <dt class="col-6 text-muted fw-normal">Créé le</dt>
                    <dd class="col-6">{{ $tribunal->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">Modifié le</dt>
                    <dd class="col-6">{{ $tribunal->updated_at->format('d/m/Y') }}</dd>
                </dl>
            </div>
        </div>

        {{-- Actions rapides --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 d-flex flex-column gap-2">
                <a href="{{ route('tribunaux.edit', $tribunal) }}"
                   class="btn btn-warning w-100 btn-sm">
                    <i class="bi bi-pencil me-1"></i>Modifier ce tribunal
                </a>
                <a href="{{ route('juges.create') }}"
                   class="btn btn-outline-primary w-100 btn-sm">
                    <i class="bi bi-person-plus me-1"></i>Ajouter un juge
                </a>
                <a href="{{ route('tribunaux.index') }}"
                   class="btn btn-outline-secondary w-100 btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Retour à la liste
                </a>
            </div>
        </div>

    </div>

</div>

@endsection