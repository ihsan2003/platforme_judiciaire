{{-- resources/views/jugements/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Jugements')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item active">Jugements</li>
@endsection

@section('content')

{{-- ══ STATS ══ --}}
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-journal-text fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['total'] }}</div>
                    <div class="text-muted small">Total</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-check-circle fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['definitifs'] }}</div>
                    <div class="text-muted small">Définitifs</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-arrow-repeat fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['en_appel'] }}</div>
                    <div class="text-muted small">En appel</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3">
                    <i class="bi bi-shield-check fs-4 text-info"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['executes'] }}</div>
                    <div class="text-muted small">Exécutés</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ FILTRES ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted fw-semibold">Juge</label>
                <select name="juge" class="form-select form-select-sm">
                    <option value="">Tous les juges</option>
                    @foreach($juges as $juge)
                        <option value="{{ $juge->id }}" @selected(request('juge') == $juge->id)>
                            {{ $juge->nom_complet }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted fw-semibold">Caractère</label>
                <select name="definitif" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="oui" @selected(request('definitif') === 'oui')>Définitifs</option>
                    <option value="non" @selected(request('definitif') === 'non')>Non définitifs</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-funnel-fill me-1"></i>Filtrer
                </button>
                <a href="{{ route('jugements.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ══ TABLE ══ --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <h5 class="mb-0 fw-semibold">
            <i class="bi bi-journal-text me-2 text-primary"></i>Jugements
            <span class="badge bg-primary ms-2">{{ $jugements->total() }}</span>
        </h5>
        <a href="{{ route('jugements.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouveau jugement
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3 text-muted small fw-semibold">Date</th>
                    <th class="text-muted small fw-semibold">Dossier</th>
                    <th class="text-muted small fw-semibold">Tribunal</th>
                    <th class="text-muted small fw-semibold">Juge</th>
                    <th class="text-muted small fw-semibold">Caractère</th>
                    <th class="text-muted small fw-semibold">Recours</th>
                    <th class="text-muted small fw-semibold">Exécution</th>
                    <th class="text-end pe-3 text-muted small fw-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jugements as $jugement)
                <tr>
                    <td class="ps-3 fw-semibold">
                        {{ $jugement->date_jugement->format('d/m/Y') }}
                    </td>
                    <td>
                        @if($jugement->dossierTribunal?->dossier)
                            <a href="{{ route('dossiers.show', $jugement->dossierTribunal->dossier) }}"
                               class="text-decoration-none fw-semibold text-primary">
                                {{ $jugement->dossierTribunal->dossier->numero_dossier_interne }}
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-muted small">
                        {{ $jugement->dossierTribunal?->tribunal?->nom_tribunal ?? '—' }}
                    </td>
                    <td class="text-muted small">
                        {{ $jugement->juge?->nom_complet ?? '—' }}
                    </td>
                    <td>
                        @if($jugement->est_definitif)
                            <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25">
                                <i class="bi bi-check-circle me-1"></i>Définitif
                            </span>
                        @else
                            @php
                                $delai = $jugement->delai_recours_restant;
                            @endphp
                            <span class="badge {{ $delai !== null && $delai <= 5 ? 'bg-danger' : 'bg-warning text-dark' }} bg-opacity-15 border border-opacity-25">
                                <i class="bi bi-clock me-1"></i>Non définitif
                                @if($delai !== null)
                                    ({{ $delai }}j)
                                @endif
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($jugement->recours->isNotEmpty())
                            <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-25">
                                <i class="bi bi-arrow-repeat me-1"></i>{{ $jugement->recours->count() }} recours
                            </span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        @if($jugement->executions->isNotEmpty())
                            <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-25">
                                <i class="bi bi-shield-check me-1"></i>{{ $jugement->executions->first()->statut?->statut_execution ?? 'En cours' }}
                            </span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-end pe-3">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('jugements.show', $jugement) }}"
                               class="btn btn-sm btn-outline-primary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('jugements.edit', $jugement) }}"
                               class="btn btn-sm btn-outline-warning" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('jugements.destroy', $jugement) }}" method="POST"
                                  onsubmit="return confirm('Supprimer ce jugement ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-25"></i>
                        Aucun jugement trouvé
                        @if(request()->hasAny(['juge','definitif']))
                            — <a href="{{ route('jugements.index') }}">Réinitialiser les filtres</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($jugements->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <span class="text-muted small">
            Affichage {{ $jugements->firstItem() }}–{{ $jugements->lastItem() }}
            sur {{ $jugements->total() }} jugements
        </span>
        {{ $jugements->links() }}
    </div>
    @endif
</div>

@endsection
