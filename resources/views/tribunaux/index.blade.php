@extends('layouts.app')

@section('title', 'Tribunaux')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item active">Tribunaux</li>
@endsection

@section('content')

{{-- ══ STATS ══ --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-building fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $tribunaux->total() }}</div>
                    <div class="text-muted small">Total tribunaux</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-geo-alt fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ \App\Models\Province::has('tribunaux')->count() }}
                    </div>
                    <div class="text-muted small">Provinces couvertes</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-person-workspace fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ \App\Models\Juge::count() }}
                    </div>
                    <div class="text-muted small">Juges enregistrés</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ FILTRES ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted fw-semibold">Recherche</label>
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text"
                           name="search"
                           class="form-control border-start-0"
                           placeholder="Nom du tribunal…"
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted fw-semibold">Type</label>
                <select name="type" class="form-select">
                    <option value="">Tous les types</option>
                    @foreach(\App\Models\TypeTribunal::orderBy('tribunal')->get() as $type)
                        <option value="{{ $type->id }}" @selected(request('type') == $type->id)>
                            {{ $type->tribunal }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary flex-fill">
                    <i class="bi bi-funnel-fill me-1"></i>Filtrer
                </button>
                <a href="{{ route('tribunaux.index') }}" class="btn btn-outline-secondary">
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
            <i class="bi bi-building me-2 text-primary"></i>Tribunaux
            <span class="badge bg-primary ms-2">{{ $tribunaux->total() }}</span>
        </h5>
        <a href="{{ route('tribunaux.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouveau tribunal
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3 text-muted small fw-semibold">Tribunal</th>
                    <th class="text-muted small fw-semibold">Type</th>
                    <th class="text-muted small fw-semibold">Province</th>
                    <th class="text-muted small fw-semibold">Région</th>
                    <th class="text-muted small fw-semibold">Juges</th>
                    <th class="text-end pe-3 text-muted small fw-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tribunaux as $tribunal)
                <tr>
                    <td class="ps-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:38px;height:38px">
                                <i class="bi bi-building text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $tribunal->nom_tribunal }}</div>
                                <div class="text-muted small">Tribunal</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-25">
                            {{ $tribunal->typeTribunal->tribunal ?? '—' }}
                        </span>
                    </td>
                    <td class="text-muted small">
                        <i class="bi bi-geo-alt me-1"></i>{{ $tribunal->province->province ?? '—' }}
                    </td>
                    <td class="text-muted small">
                        {{ $tribunal->province->region->region ?? '—' }}
                    </td>
                    <td>
                        @php $nbJuges = $tribunal->juges()->count(); @endphp
                        @if($nbJuges > 0)
                            <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25">
                                <i class="bi bi-person-workspace me-1"></i>{{ $nbJuges }} juge{{ $nbJuges > 1 ? 's' : '' }}
                            </span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">Aucun juge</span>
                        @endif
                    </td>
                    <td class="text-end pe-3">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('tribunaux.show', $tribunal) }}"
                               class="btn btn-sm btn-outline-primary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('tribunaux.edit', $tribunal) }}"
                               class="btn btn-sm btn-outline-warning" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('tribunaux.destroy', $tribunal) }}" method="POST"
                                  onsubmit="return confirm('Supprimer ce tribunal ?')">
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
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-building-x fs-1 d-block mb-2 opacity-25"></i>
                        Aucun tribunal trouvé
                        @if(request()->hasAny(['search', 'type']))
                            — <a href="{{ route('tribunaux.index') }}">Réinitialiser les filtres</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tribunaux->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
        <span class="text-muted small">
            Affichage {{ $tribunaux->firstItem() }}–{{ $tribunaux->lastItem() }}
            sur {{ $tribunaux->total() }} tribunaux
        </span>
        {{ $tribunaux->links() }}
    </div>
    @endif
</div>

@endsection