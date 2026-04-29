{{-- resources/views/reclamations/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Réclamations')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item active">Réclamations</li>
@endsection

@section('content')

{{-- ══ STATS ══ --}}
<div class="row g-3 mb-4">
    @foreach([
        ['label' => 'Total',       'value' => $stats['total'],      'icon' => 'chat-left-text',  'color' => 'primary'],
        ['label' => 'Reçues',      'value' => $stats['recues'],     'icon' => 'inbox',            'color' => 'info'],
        ['label' => 'En cours',    'value' => $stats['en_cours'],   'icon' => 'arrow-repeat',     'color' => 'warning'],
        ['label' => 'Clôturées',   'value' => $stats['cloturees'],  'icon' => 'check-circle',     'color' => 'success'],
        ['label' => 'En attente',  'value' => $stats['en_attente'], 'icon' => 'hourglass-split',  'color' => 'danger'],
        ['label' => 'Ce mois',     'value' => $stats['ce_mois'],    'icon' => 'calendar-plus',    'color' => 'secondary'],
    ] as $stat)
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="rounded-circle bg-{{ $stat['color'] }} bg-opacity-10 p-2 d-inline-flex mb-2">
                    <i class="bi bi-{{ $stat['icon'] }} fs-5 text-{{ $stat['color'] }}"></i>
                </div>
                <div class="fs-3 fw-bold lh-1 mb-1">{{ $stat['value'] }}</div>
                <div class="text-muted small">{{ $stat['label'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
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
                           placeholder="Objet ou nom réclamant…" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    @foreach($statuts as $statut)
                        <option value="{{ $statut->id }}" @selected(request('statut') == $statut->id)>
                            {{ $statut->statut_reclamation }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">Type réclamant</label>
                <select name="type_reclamant" class="form-select">
                    <option value="">Tous les types</option>
                    @foreach($typesReclamant as $type)
                        <option value="{{ $type->id }}" @selected(request('type_reclamant') == $type->id)>
                            {{ $type->type_reclamant }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">Période</label>
                <select name="periode" class="form-select">
                    <option value="">Toutes les périodes</option>
                    <option value="ce_mois"      @selected(request('periode') == 'ce_mois')>Ce mois</option>
                    <option value="ce_trimestre" @selected(request('periode') == 'ce_trimestre')>Ce trimestre</option>
                    <option value="cette_annee"  @selected(request('periode') == 'cette_annee')>Cette année</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">Du</label>
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button class="btn btn-primary flex-fill" title="Filtrer">
                    <i class="bi bi-funnel-fill"></i>
                </button>
                <a href="{{ route('reclamations.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
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
            <i class="bi bi-chat-left-text me-2 text-primary"></i>Réclamations
            <span class="badge bg-primary ms-2">{{ $reclamations->total() }}</span>
        </h5>
        <a href="{{ route('reclamations.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouvelle réclamation
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3 text-muted small fw-semibold">Réclamant</th>
                    <th class="text-muted small fw-semibold">Type</th>
                    <th class="text-muted small fw-semibold">Objet</th>
                    <th class="text-muted small fw-semibold">Réception</th>
                    <th class="text-muted small fw-semibold">Statut</th>
                    <th class="text-muted small fw-semibold">Dernière action</th>
                    <th class="text-muted small fw-semibold text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reclamations as $reclamation)
                @php
                    $statut = $reclamation->statut?->statut_reclamation ?? '—';
                    $color  = match(true) {
                        $statut === 'Reçue'     => 'info',
                        $statut === 'En cours'  => 'warning',
                        $statut === 'Clôturée'  => 'success',
                        default                 => 'secondary',
                    };
                    $derniereAction = $reclamation->actions->first();
                @endphp
                <tr>
                    <td class="ps-3">
                        <div class="fw-semibold">{{ $reclamation->reclamant?->nom ?? '—' }}</div>
                        @if($reclamation->reclamant?->email)
                            <div class="text-muted small">{{ $reclamation->reclamant->email }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                            {{ $reclamation->reclamant?->typeReclamant?->type_reclamant ?? '—' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('reclamations.show', $reclamation) }}"
                           class="text-decoration-none fw-semibold text-dark">
                            {{ Str::limit($reclamation->objet, 55) }}
                        </a>
                    </td>
                    <td class="text-muted small">
                        {{ $reclamation->date_reception?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td>
                        <span class="badge bg-{{ $color }} bg-opacity-15 text-{{ $color }} border border-{{ $color }} border-opacity-25">
                            <i class="bi bi-circle-fill me-1" style="font-size:.45rem;vertical-align:middle"></i>
                            {{ $statut }}
                        </span>
                    </td>
                    <td class="text-muted small">
                        @if($derniereAction)
                            <div>{{ $derniereAction->typeAction?->type_action ?? '—' }}</div>
                            <div class="text-muted" style="font-size:.72rem">
                                {{ $derniereAction->date_action?->format('d/m/Y') }}
                            </div>
                        @else
                            <span class="text-muted fst-italic">Aucune action</span>
                        @endif
                    </td>
                    <td class="text-end pe-3">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('reclamations.show', $reclamation) }}"
                               class="btn btn-sm btn-outline-primary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('reclamations.edit', $reclamation) }}"
                               class="btn btn-sm btn-outline-warning" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('reclamations.destroy', $reclamation) }}" method="POST"
                                  onsubmit="return confirm('Supprimer cette réclamation ?')">
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
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-chat-left-x fs-1 d-block mb-2 opacity-25"></i>
                        Aucune réclamation trouvée
                        @if(request()->hasAny(['search','statut','type_reclamant','periode','date_debut','date_fin']))
                            — <a href="{{ route('reclamations.index') }}">Réinitialiser les filtres</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($reclamations->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
        <span class="text-muted small">
            Affichage {{ $reclamations->firstItem() }}–{{ $reclamations->lastItem() }}
            sur {{ $reclamations->total() }} réclamations
        </span>
        {{ $reclamations->links() }}
    </div>
    @endif
</div>

@endsection