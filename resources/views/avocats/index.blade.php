@extends('layouts.app')

@section('title', 'Avocats')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item active">Avocats</li>
@endsection

@section('content')

{{-- ══ STATS ══ --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-person-badge fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $avocats->total() }}</div>
                    <div class="text-muted small">Total avocats</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-briefcase fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ \App\Models\Avocat::has('dossierParties')->count() }}
                    </div>
                    <div class="text-muted small">Avec dossiers actifs</div>
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
                    <div class="fs-2 fw-bold lh-1">
                        {{ \App\Models\Avocat::whereMonth('created_at', now()->month)->count() }}
                    </div>
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
            <div class="col-md-6">
                <label class="form-label small text-muted fw-semibold">Recherche</label>
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text"
                           name="search"
                           class="form-control border-start-0"
                           placeholder="Nom, email ou téléphone…"
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted fw-semibold">Trier par</label>
                <select name="sort" class="form-select">
                    <option value="nom_avocat" @selected(request('sort','nom_avocat') === 'nom_avocat')>Nom (A→Z)</option>
                    <option value="created_at" @selected(request('sort') === 'created_at')>Plus récents</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill" title="Filtrer">
                    <i class="bi bi-funnel-fill me-1"></i>Filtrer
                </button>
                <a href="{{ route('avocats.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
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
            <i class="bi bi-person-badge me-2 text-primary"></i>Avocats
            <span class="badge bg-primary ms-2">{{ $avocats->total() }}</span>
        </h5>
        <a href="{{ route('avocats.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouvel avocat
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3 text-muted small fw-semibold">Avocat</th>
                    <th class="text-muted small fw-semibold">Téléphone</th>
                    <th class="text-muted small fw-semibold">Email</th>
                    <th class="text-muted small fw-semibold">Dossiers</th>
                    <th class="text-end pe-3 text-muted small fw-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($avocats as $avocat)
                <tr>
                    <td class="ps-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:38px;height:38px">
                                <span class="fw-semibold text-primary small">
                                    {{ strtoupper(substr($avocat->nom_avocat, 0, 2)) }}
                                </span>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $avocat->nom_avocat }}</div>
                                <div class="text-muted small">Avocat</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($avocat->telephone)
                            <a href="tel:{{ $avocat->telephone }}"
                               class="text-decoration-none text-muted small">
                                <i class="bi bi-telephone me-1"></i>{{ $avocat->telephone }}
                            </a>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        @if($avocat->email)
                            <a href="mailto:{{ $avocat->email }}"
                               class="text-decoration-none text-muted small">
                                <i class="bi bi-envelope me-1"></i>{{ $avocat->email }}
                            </a>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        @php $nb = $avocat->dossierParties()->count(); @endphp
                        @if($nb > 0)
                            <span class="badge bg-info bg-opacity-15 text-white border border-info border-opacity-25">
                                <i class="bi bi-folder2 me-1"></i>{{ $nb }} dossier{{ $nb > 1 ? 's' : '' }}
                            </span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                Aucun dossier
                            </span>
                        @endif
                    </td>
                    <td class="text-end pe-3">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('avocats.show', $avocat) }}"
                               class="btn btn-sm btn-outline-primary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('avocats.edit', $avocat) }}"
                               class="btn btn-sm btn-outline-warning" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('avocats.destroy', $avocat) }}" method="POST"
                                  onsubmit="return confirm('Supprimer cet avocat ?')">
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
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-person-x fs-1 d-block mb-2 opacity-25"></i>
                        Aucun avocat trouvé
                        @if(request('search'))
                            pour « {{ request('search') }} »
                            — <a href="{{ route('avocats.index') }}">Réinitialiser</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($avocats->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
        <span class="text-muted small">
            Affichage {{ $avocats->firstItem() }}–{{ $avocats->lastItem() }}
            sur {{ $avocats->total() }} avocats
        </span>
        {{ $avocats->links() }}
    </div>
    @endif
</div>

@endsection