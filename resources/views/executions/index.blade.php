{{-- resources/views/executions/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Exécutions')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item active">Exécutions</li>
@endsection

@section('content')

{{-- ══ STATS ══ --}}
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-shield fs-4 text-primary"></i>
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
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['en_cours'] }}</div>
                    <div class="text-muted small">En cours</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-shield-check fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['terminees'] }}</div>
                    <div class="text-muted small">Terminées</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3">
                    <i class="bi bi-calendar-plus fs-4 text-info"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['ce_mois'] }}</div>
                    <div class="text-muted small">Ce mois</div>
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
                <label class="form-label small text-muted fw-semibold">Statut</label>
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous les statuts</option>
                    @foreach($statuts as $statut)
                        <option value="{{ $statut->id }}" @selected(request('statut') == $statut->id)>
                            {{ $statut->statut_execution }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted fw-semibold">Responsable</label>
                <select name="responsable" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($responsables as $user)
                        <option value="{{ $user->id }}" @selected(request('responsable') == $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-funnel-fill me-1"></i>Filtrer
                </button>
                <a href="{{ route('executions.index') }}" class="btn btn-outline-secondary btn-sm">
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
            <i class="bi bi-shield me-2 text-primary"></i>Exécutions de jugements
            <span class="badge bg-primary ms-2">{{ $executions->total() }}</span>
        </h5>
        <a href="{{ route('executions.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouvelle exécution
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3 text-muted small fw-semibold">N° Exécution</th>
                    <th class="text-muted small fw-semibold">Jugement / Dossier</th>
                    <th class="text-muted small fw-semibold">Tribunal</th>
                    <th class="text-muted small fw-semibold">Statut</th>
                    <th class="text-muted small fw-semibold">Responsable</th>
                    <th class="text-muted small fw-semibold">Notification</th>
                    <th class="text-muted small fw-semibold">Exécuté le</th>
                    <th class="text-end pe-3 text-muted small fw-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($executions as $execution)
                <tr>
                    <td class="ps-3">
                        <span class="fw-semibold font-monospace">{{ $execution->numero_dossier_execution }}</span>
                    </td>
                    <td>
                        @if($execution->jugement?->dossierTribunal?->dossier)
                            <a href="{{ route('dossiers.show', $execution->jugement->dossierTribunal->dossier) }}"
                               class="text-decoration-none fw-semibold text-primary d-block">
                                {{ $execution->jugement->dossierTribunal->dossier->numero_dossier_interne }}
                            </a>
                            <small class="text-muted">
                                Jugement du {{ $execution->jugement->date_jugement->format('d/m/Y') }}
                            </small>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-muted small">
                        {{ $execution->jugement?->dossierTribunal?->tribunal?->nom_tribunal ?? '—' }}
                    </td>
                    <td>
                        @php
                            $statutLabel = $execution->statut?->statut_execution ?? '—';
                            $color = match(true) {
                                str_contains($statutLabel, 'Terminé') => 'success',
                                str_contains($statutLabel, 'cours')   => 'warning',
                                str_contains($statutLabel, 'Suspendu')=> 'danger',
                                default                               => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $color }} bg-opacity-15 text-{{ $color }} border border-{{ $color }} border-opacity-25">
                            {{ $statutLabel }}
                        </span>
                    </td>
                    <td class="text-muted small">
                        {{ $execution->responsable?->name ?? '—' }}
                    </td>
                    <td class="text-muted small">
                        {{ $execution->date_notification?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="text-muted small">
                        @if($execution->date_execution)
                            <span class="text-success fw-semibold">
                                {{ $execution->date_execution->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="badge bg-warning text-dark bg-opacity-20">En attente</span>
                        @endif
                    </td>
                    <td class="text-end pe-3">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('executions.show', $execution) }}"
                               class="btn btn-sm btn-outline-primary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('executions.edit', $execution) }}"
                               class="btn btn-sm btn-outline-warning" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('executions.destroy', $execution) }}" method="POST"
                                  onsubmit="return confirm('Supprimer cette exécution ?')">
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
                        <i class="bi bi-shield-x fs-1 d-block mb-2 opacity-25"></i>
                        Aucune exécution trouvée
                        @if(request()->hasAny(['statut','responsable']))
                            — <a href="{{ route('executions.index') }}">Réinitialiser les filtres</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($executions->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <span class="text-muted small">
            Affichage {{ $executions->firstItem() }}–{{ $executions->lastItem() }}
            sur {{ $executions->total() }} exécutions
        </span>
        {{ $executions->links() }}
    </div>
    @endif
</div>

@endsection
