@extends('layouts.app')

@section('title', 'Dossiers Judiciaires')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item active">Dossiers</li>
@endsection

@section('content')

{{-- ══ EN-TÊTE : stats rapides ══ --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-folder2-open fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['total'] }}</div>
                    <div class="text-muted small">Total dossiers</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-activity fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['actifs'] }}</div>
                    <div class="text-muted small">Dossiers actifs</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-calendar-plus fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['ce_mois'] }}</div>
                    <div class="text-muted small">Ce mois-ci</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ FILTRES ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted fw-semibold">Recherche</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0"
                           placeholder="N° dossier…" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">Type d'affaire</label>
                <select name="type" class="form-select">
                    <option value="">Tous les types</option>
                    @foreach($typesAffaire as $type)
                        <option value="{{ $type->id }}" @selected(request('type') == $type->id)>
                            {{ $type->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    @foreach($statutDossiers as $statut)
                        <option value="{{ $statut->id }}" @selected(request('statut') == $statut->id)>
                            {{ $statut->statut_dossier }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">Du</label>
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">Au</label>
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button class="btn btn-primary flex-fill" title="Filtrer">
                    <i class="bi bi-funnel-fill"></i>
                </button>
                <a href="{{ route('dossiers.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
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
            <i class="bi bi-folder2 me-2 text-primary"></i>Dossiers judiciaires
            <span class="badge bg-primary ms-2">{{ $dossiers->total() }}</span>
        </h5>
        @can('create', App\Models\DossierJudiciaire::class)
            <a href="{{ route('dossiers.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Nouveau dossier
            </a>
        @endcan
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-muted small fw-semibold ps-3">N° Interne</th>
                    <th class="text-muted small fw-semibold">N° Tribunal</th>
                    <th class="text-muted small fw-semibold">Type d'affaire</th>
                    <th class="text-muted small fw-semibold">Tribunal(x)</th>
                    <th class="text-muted small fw-semibold">Statut</th>
                    <th class="text-muted small fw-semibold">Ouverture</th>
                    <th class="text-muted small fw-semibold">Créé par</th>
                    <th class="text-muted small fw-semibold text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dossiers as $dossier)
                <tr>
                    <td class="ps-3">
                        <span class="fw-semibold">{{ $dossier->numero_dossier_interne }}</span>
                    </td>
                    <td class="text-muted small">{{ $dossier->numero_dossier_tribunal ?? '—' }}</td>
                    <td>
                        <span class="badge bg-info bg-opacity-15 text-white border border-info border-opacity-25">
                            {{ $dossier->typeAffaire->affaire ?? '—' }}
                        </span>
                    </td>
                    <td>
                        @forelse($dossier->dossierTribunaux as $dt)
                            <span class="badge bg-secondary bg-opacity-10 text-secondary me-1 mb-1">
                                <i class="bi bi-bank me-1"></i>{{ $dt->tribunal->nom_tribunal ?? '?' }}
                            </span>
                        @empty
                            <span class="text-muted small">—</span>
                        @endforelse
                    </td>
                    <td>
                        @php
                            $statut = $dossier->statutDossier->statut_dossier ?? '—';
                            $color  = match(true) {
                                str_contains($statut, 'Actif')    => 'success',
                                str_contains($statut, 'Clôturé')  => 'secondary',
                                str_contains($statut, 'Suspendu') => 'warning',
                                default                           => 'primary',
                            };
                        @endphp
                        <span class="badge bg-{{ $color }} bg-opacity-15 text-{{ $color }} border border-{{ $color }} border-opacity-25">
                            {{ $statut }}
                        </span>
                    </td>
                    <td class="text-muted small">
                        {{ $dossier->date_ouverture?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="text-muted small">{{ $dossier->createdBy->name ?? '—' }}</td>
                    <td class="text-end pe-3">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('dossiers.show', $dossier) }}"
                               class="btn btn-sm btn-outline-primary" title="Voir le détail">
                                <i class="bi bi-eye"></i>
                            </a>
                            @can('update', $dossier)
                            <a href="{{ route('dossiers.edit', $dossier) }}"
                               class="btn btn-sm btn-outline-warning" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            @can('delete', $dossier)
                            <form action="{{ route('dossiers.destroy', $dossier) }}" method="POST"
                                  onsubmit="return confirm('Archiver ce dossier ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Archiver">
                                    <i class="bi bi-archive"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-folder-x fs-1 d-block mb-2 opacity-25"></i>
                        Aucun dossier trouvé
                        @if(request()->hasAny(['search','type','statut','date_debut','date_fin']))
                            — <a href="{{ route('dossiers.index') }}">Réinitialiser les filtres</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($dossiers->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <span class="text-muted small">
            Affichage {{ $dossiers->firstItem() }}–{{ $dossiers->lastItem() }}
            sur {{ $dossiers->total() }} dossiers
        </span>
        {{ $dossiers->links() }}
    </div>
    @endif
</div>

@endsection