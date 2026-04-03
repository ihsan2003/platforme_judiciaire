{{-- resources/views/audiences/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Audiences')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item active">Audiences</li>
@endsection

@section('content')

{{-- ══ STATS RAPIDES ══ --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-calendar-check fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['aujourd_hui'] }}</div>
                    <div class="text-muted small">Aujourd'hui</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-calendar-week fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['cette_semaine'] }}</div>
                    <div class="text-muted small">Cette semaine</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                    <i class="bi bi-exclamation-triangle fs-4 text-danger"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['passees_sans_suite'] }}</div>
                    <div class="text-muted small">Sans résultat</div>
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
                <label class="form-label small text-muted fw-semibold">Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tous les types</option>
                    @foreach($typesAudience as $type)
                        <option value="{{ $type->id }}" @selected(request('type') == $type->id)>
                            {{ $type->libelle }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted fw-semibold">Période</label>
                <select name="periode" class="form-select form-select-sm">
                    <option value="">Toutes</option>
                    <option value="today"   @selected(request('periode') == 'today')>Aujourd'hui</option>
                    <option value="semaine" @selected(request('periode') == 'semaine')>7 prochains jours</option>
                    <option value="futures" @selected(request('periode') == 'futures')>À venir</option>
                    <option value="passees" @selected(request('periode') == 'passees')>Passées</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-funnel-fill me-1"></i>Filtrer
                </button>
                <a href="{{ route('audiences.index') }}" class="btn btn-outline-secondary btn-sm">
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
            <i class="bi bi-gavel me-2 text-primary"></i>Audiences
            <span class="badge bg-primary ms-2">{{ $audiences->total() }}</span>
        </h5>
        <a href="{{ route('audiences.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouvelle audience
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3 text-muted small fw-semibold">Date</th>
                    <th class="text-muted small fw-semibold">Dossier</th>
                    <th class="text-muted small fw-semibold">Tribunal</th>
                    <th class="text-muted small fw-semibold">Type</th>
                    <th class="text-muted small fw-semibold">Juge</th>
                    <th class="text-muted small fw-semibold">Présences</th>
                    <th class="text-muted small fw-semibold">Prochaine audience</th>
                    <th class="text-end pe-3 text-muted small fw-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($audiences as $audience)
                <tr class="{{ $audience->est_today ? 'table-warning' : '' }}">
                    <td class="ps-3">
                        <span class="fw-semibold">{{ $audience->date_audience->format('d/m/Y') }}</span>
                        @if($audience->est_today)
                            <span class="badge bg-warning text-dark ms-1">Aujourd'hui</span>
                        @elseif($audience->est_passee)
                            <span class="badge bg-secondary ms-1">Passée</span>
                        @else
                            <span class="badge bg-success ms-1">À venir</span>
                        @endif
                    </td>
                    <td>
                        @if($audience->dossierTribunal?->dossier)
                            <a href="{{ route('dossiers.show', $audience->dossierTribunal->dossier) }}"
                               class="text-decoration-none fw-semibold text-primary">
                                {{ $audience->dossierTribunal->dossier->numero_dossier_interne }}
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-muted small">
                        {{ $audience->dossierTribunal?->tribunal?->nom_tribunal ?? '—' }}
                    </td>
                    <td>
                        <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-25">
                            {{ $audience->typeAudience?->libelle ?? '—' }}
                        </span>
                    </td>
                    <td class="text-muted small">
                        {{ $audience->juge?->nom_complet ?? '—' }}
                    </td>
                    <td>
                        <span class="me-2" title="Demandeur">
                            <i class="bi bi-person-fill {{ $audience->presence_demandeur ? 'text-success' : 'text-danger opacity-25' }}"></i>
                        </span>
                        <span title="Défendeur">
                            <i class="bi bi-person-fill {{ $audience->presence_defendeur ? 'text-success' : 'text-danger opacity-25' }}"></i>
                        </span>
                    </td>
                    <td class="text-muted small">
                        {{ $audience->date_prochaine_audience?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="text-end pe-3">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('audiences.show', $audience) }}"
                               class="btn btn-sm btn-outline-primary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('audiences.edit', $audience) }}"
                               class="btn btn-sm btn-outline-warning" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('audiences.destroy', $audience) }}" method="POST"
                                  onsubmit="return confirm('Supprimer cette audience ?')">
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
                        <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-25"></i>
                        Aucune audience trouvée
                        @if(request()->hasAny(['juge','type','periode']))
                            — <a href="{{ route('audiences.index') }}">Réinitialiser les filtres</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($audiences->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <span class="text-muted small">
            Affichage {{ $audiences->firstItem() }}–{{ $audiences->lastItem() }}
            sur {{ $audiences->total() }} audiences
        </span>
        {{ $audiences->links() }}
    </div>
    @endif
</div>

@endsection
