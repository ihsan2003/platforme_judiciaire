@extends('layouts.app')

@section('title', 'Structure — ' . $structure->nom)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.structures.index') }}">Structures</a></li>
    <li class="breadcrumb-item active">{{ $structure->nom }}</li>
@endsection

@section('content')

{{-- ══ EN-TÊTE ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">

            {{-- Identité --}}
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                     style="width:64px;height:64px;flex-shrink:0">
                    <i class="bi bi-building-fill fs-3 text-primary"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1">{{ $structure->nom }}</h4>
                    <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                        <span class="badge bg-primary bg-opacity-15 text-white border border-primary border-opacity-25">
                            <i class="bi bi-tag me-1"></i>{{ $structure->typeStructure?->type_structure ?? '—' }}
                        </span>
                        @if($structure->parent)
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                <i class="bi bi-diagram-2 me-1"></i>Sous-structure de {{ $structure->parent->nom }}
                            </span>
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-building me-1"></i>Structure principale
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Compteurs --}}
            <div class="d-flex flex-wrap gap-4 small text-muted">
                <div class="text-center">
                    <div class="fw-semibold text-dark fs-6">{{ $structure->enfants->count() }}</div>
                    <div>Sous-structures</div>
                </div>
                <div class="text-center">
                    <div class="fw-semibold text-dark fs-6">{{ $structure->actionReclamations->count() }}</div>
                    <div>Actions liées</div>
                </div>
            </div>

            {{-- Boutons --}}
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.structures.edit', $structure) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
                <form action="{{ route('admin.structures.destroy', $structure) }}" method="POST"
                      onsubmit="return confirm('Supprimer « {{ $structure->nom }} » ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash me-1"></i>Supprimer
                    </button>
                </form>
                <a href="{{ route('admin.structures.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>
        </div>

        {{-- Méta-données --}}
        <hr class="my-3">
        <div class="row g-2 small text-muted">
            <div class="col-sm-3">
                <i class="bi bi-calendar-plus me-1"></i>
                <strong>Créée le :</strong> {{ $structure->created_at->format('d/m/Y à H:i') }}
            </div>
            <div class="col-sm-3">
                <i class="bi bi-pencil me-1"></i>
                <strong>Modifiée le :</strong> {{ $structure->updated_at->format('d/m/Y') }}
            </div>
            <div class="col-sm-3">
                <i class="bi bi-fingerprint me-1"></i>
                <strong>ID :</strong> <span class="font-monospace">{{ $structure->id }}</span>
            </div>
            @if($structure->parent)
            <div class="col-sm-3">
                <i class="bi bi-diagram-3 me-1"></i>
                <strong>Hiérarchie :</strong> {{ $structure->hierarchie }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ══ CONTENU ══ --}}
<div class="row g-4">

    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">

        {{-- Informations générales --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-building-fill me-2 text-primary"></i>Informations de la structure
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 rounded border h-100">
                            <div class="text-muted small fw-semibold mb-1">Nom de la structure</div>
                            <div class="fw-semibold">{{ $structure->nom }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded border h-100">
                            <div class="text-muted small fw-semibold mb-1">Type de structure</div>
                            <span class="badge bg-primary bg-opacity-15 text-white border border-primary border-opacity-25">
                                {{ $structure->typeStructure?->type_structure ?? '—' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded border h-100">
                            <div class="text-muted small fw-semibold mb-1">Structure parente</div>
                            @if($structure->parent)
                                <a href="{{ route('admin.structures.show', $structure->parent) }}"
                                   class="text-decoration-none fw-semibold">
                                    <i class="bi bi-building me-1 text-muted"></i>
                                    {{ $structure->parent->nom }}
                                </a>
                            @else
                                <span class="text-muted fst-italic">Aucune (structure principale)</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded border h-100">
                            <div class="text-muted small fw-semibold mb-1">Niveau hiérarchique</div>
                            @if($structure->parent)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                    <i class="bi bi-diagram-2 me-1"></i>Sous-structure
                                </span>
                            @else
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                    <i class="bi bi-building me-1"></i>Niveau principal
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sous-structures --}}
        @if($structure->enfants->count() > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-diagram-2 me-2 text-success"></i>Sous-structures
                    <span class="badge bg-success bg-opacity-10 text-success ms-1">{{ $structure->enfants->count() }}</span>
                </h6>
                <a href="{{ route('admin.structures.create') }}?parent={{ $structure->id }}"
                   class="btn btn-success btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Ajouter
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 text-muted small fw-semibold">Nom</th>
                            <th class="text-muted small fw-semibold">Type</th>
                            <th class="text-end pe-3 text-muted small fw-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($structure->enfants as $enfant)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center text-secondary"
                                         style="width:32px;height:32px;flex-shrink:0">
                                        <i class="bi bi-building fs-6"></i>
                                    </div>
                                    <span class="fw-semibold small">{{ $enfant->nom }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25" style="font-size:.7rem">
                                    {{ $enfant->typeStructure?->type_structure ?? '—' }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('admin.structures.show', $enfant) }}"
                                       class="btn btn-sm btn-outline-primary" title="Voir">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.structures.edit', $enfant) }}"
                                       class="btn btn-sm btn-outline-warning" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.structures.destroy', $enfant) }}" method="POST"
                                          onsubmit="return confirm('Supprimer « {{ $enfant->nom }} » ?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Actions de réclamation liées --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-activity me-2 text-warning"></i>Actions de suivi liées
                    <span class="badge bg-warning text-dark ms-1">{{ $structure->actionReclamations->count() }}</span>
                </h6>
            </div>
            @if($structure->actionReclamations->count() > 0)
            <div class="list-group list-group-flush small">
                @foreach($structure->actionReclamations->take(5) as $action)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-right-circle text-warning"></i>
                        <div>
                            <div class="fw-semibold">{{ $action->typeAction?->type_action ?? '—' }}</div>
                            <div class="text-muted" style="font-size:.72rem">
                                {{ $action->date_action?->format('d/m/Y') ?? '—' }}
                                @if($action->reclamation)
                                    · Réclamation #{{ $action->reclamation->id }}
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($action->reclamation)
                    <a href="{{ route('reclamations.show', $action->id_reclamation) }}"
                       class="btn btn-sm btn-outline-primary" title="Voir la réclamation">
                        <i class="bi bi-eye"></i>
                    </a>
                    @endif
                </div>
                @endforeach
                @if($structure->actionReclamations->count() > 5)
                <div class="list-group-item text-center text-muted small fst-italic py-2">
                    … et {{ $structure->actionReclamations->count() - 5 }} action(s) de plus
                </div>
                @endif
            </div>
            @else
            <div class="card-body text-center py-4 text-muted">
                <i class="bi bi-list-check fs-2 d-block mb-2 opacity-25"></i>
                <span class="small">Aucune action de suivi liée à cette structure.</span>
            </div>
            @endif
        </div>

    </div>

    {{-- ── Colonne latérale ── --}}
    <div class="col-lg-4">


        {{-- Hiérarchie visuelle --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-diagram-3 me-2 text-primary"></i>Hiérarchie
                </h6>
            </div>
            <div class="card-body small">
                @if($structure->parent)
                <div class="d-flex align-items-center gap-2 mb-2 text-muted">
                    <i class="bi bi-building-fill text-primary"></i>
                    <a href="{{ route('admin.structures.show', $structure->parent) }}"
                       class="text-decoration-none text-muted fw-semibold">
                        {{ $structure->parent->nom }}
                    </a>
                </div>
                <div class="d-flex align-items-center gap-2 ms-3 mb-2">
                    <i class="bi bi-arrow-return-right text-muted"></i>
                    <i class="bi bi-building text-primary"></i>
                    <span class="fw-bold text-primary">{{ $structure->nom }}</span>
                    <span class="badge bg-primary bg-opacity-15 text-white" style="font-size:.65rem">actuel</span>
                </div>
                @else
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-building-fill text-primary"></i>
                    <span class="fw-bold text-primary">{{ $structure->nom }}</span>
                    <span class="badge bg-primary bg-opacity-15 text-white" style="font-size:.65rem">actuel</span>
                </div>
                @if($structure->enfants->count() > 0)
                    @foreach($structure->enfants as $enfant)
                    <div class="d-flex align-items-center gap-2 ms-3 mt-1 text-muted">
                        <i class="bi bi-arrow-return-right text-muted"></i>
                        <i class="bi bi-building text-secondary"></i>
                        <a href="{{ route('admin.structures.show', $enfant) }}"
                           class="text-decoration-none text-muted small">
                            {{ $enfant->nom }}
                        </a>
                    </div>
                    @endforeach
                @endif
                @endif
            </div>
        </div>

        {{-- Actions rapides --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 d-flex flex-column gap-2">
                <a href="{{ route('admin.structures.edit', $structure) }}"
                   class="btn btn-warning w-100 btn-sm">
                    <i class="bi bi-pencil me-1"></i>Modifier cette structure
                </a>
                <a href="{{ route('admin.structures.create') }}"
                   class="btn btn-outline-primary w-100 btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Nouvelle structure
                </a>
                <form action="{{ route('admin.structures.destroy', $structure) }}" method="POST"
                      onsubmit="return confirm('Supprimer « {{ $structure->nom }} » ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger w-100 btn-sm">
                        <i class="bi bi-trash me-1"></i>Supprimer
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection