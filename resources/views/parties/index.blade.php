@extends('layouts.app')

@section('title', 'Parties')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item active">Parties</li>
@endsection

@section('content')

{{-- ══ STATS ══ --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-people fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $parties->total() }}</div>
                    <div class="text-muted small">Total parties</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-person fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ \App\Models\Partie::where('type_personne', 'Physique')->count() }}
                    </div>
                    <div class="text-muted small">Personnes physiques</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-building fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ \App\Models\Partie::where('type_personne', 'Morale')->count() }}
                    </div>
                    <div class="text-muted small">Personnes morales</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ FILTRES ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('parties.index') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted fw-semibold">Recherche</label>
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text"
                           name="search"
                           class="form-control border-start-0"
                           placeholder="Nom, identifiant ou email…"
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted fw-semibold">Type de personne</label>
                <select name="type_personne" class="form-select">
                    <option value="">Tous les types</option>
                    <option value="Physique" @selected(request('type_personne') === 'Physique')>Physique</option>
                    <option value="Morale"   @selected(request('type_personne') === 'Morale')>Morale</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary flex-fill">
                    <i class="bi bi-funnel-fill me-1"></i>Filtrer
                </button>
                <a href="{{ route('parties.index') }}" class="btn btn-outline-secondary">
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
            <i class="bi bi-people me-2 text-primary"></i>Parties
            <span class="badge bg-primary ms-2">{{ $parties->total() }}</span>
        </h5>
        <a href="{{ route('parties.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouvelle partie
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3 text-muted small fw-semibold">Nom / Dénomination</th>
                    <th class="text-muted small fw-semibold">Identifiant</th>
                    <th class="text-muted small fw-semibold">Type</th>
                    <th class="text-muted small fw-semibold">Téléphone</th>
                    <th class="text-muted small fw-semibold">Email</th>
                    <th class="text-muted small fw-semibold">Dossiers</th>
                    <th class="text-end pe-3 text-muted small fw-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($parties as $partie)
                <tr>
                    <td class="ps-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                                        bg-{{ $partie->type_personne === 'Morale' ? 'warning' : 'success' }} bg-opacity-10"
                                 style="width:38px;height:38px">
                                <i class="bi bi-{{ $partie->type_personne === 'Morale' ? 'building' : 'person' }}
                                          text-{{ $partie->type_personne === 'Morale' ? 'warning' : 'success' }}"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $partie->nom_partie }}</div>
                                @if($partie->adresse)
                                    <div class="text-muted small text-truncate" style="max-width:200px">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $partie->adresse }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 font-monospace">
                            {{ $partie->identifiant_unique }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $partie->type_personne === 'Morale' ? 'warning' : 'success' }}
                                          bg-opacity-15 text-{{ $partie->type_personne === 'Morale' ? 'warning' : 'success' }}
                                          border border-{{ $partie->type_personne === 'Morale' ? 'warning' : 'success' }} border-opacity-25">
                            <i class="bi bi-{{ $partie->type_personne === 'Morale' ? 'building' : 'person' }} me-1"></i>
                            {{ $partie->type_personne ?? '—' }}
                        </span>
                    </td>
                    <td>
                        @if($partie->telephone)
                            <a href="tel:{{ $partie->telephone }}"
                               class="text-decoration-none text-muted small">
                                <i class="bi bi-telephone me-1"></i>{{ $partie->telephone }}
                            </a>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        @if($partie->email)
                            <a href="mailto:{{ $partie->email }}"
                               class="text-decoration-none text-muted small">
                                <i class="bi bi-envelope me-1"></i>{{ $partie->email }}
                            </a>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        @php $nb = $partie->dossiers()->count(); @endphp
                        @if($nb > 0)
                            <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-25">
                                <i class="bi bi-folder2 me-1"></i>{{ $nb }} dossier{{ $nb > 1 ? 's' : '' }}
                            </span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">Aucun</span>
                        @endif
                    </td>
                    <td class="text-end pe-3">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('parties.show', $partie) }}"
                               class="btn btn-sm btn-outline-primary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('parties.edit', $partie) }}"
                               class="btn btn-sm btn-outline-warning" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('parties.destroy', $partie) }}" method="POST"
                                  onsubmit="return confirm('Supprimer cette partie ?')">
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
                        <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>
                        Aucune partie trouvée
                        @if(request()->hasAny(['search', 'type_personne']))
                            — <a href="{{ route('parties.index') }}">Réinitialiser les filtres</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($parties->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
        <span class="text-muted small">
            Affichage {{ $parties->firstItem() }}–{{ $parties->lastItem() }}
            sur {{ $parties->total() }} parties
        </span>
        {{ $parties->links() }}
    </div>
    @endif
</div>

@endsection