@extends('layouts.app')

@section('title', 'Juges')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item active">Juges</li>
@endsection

@section('content')

{{-- ══ STATS ══ --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-person-workspace fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $juges->total() }}</div>
                    <div class="text-muted small">Total juges</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-building fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ \App\Models\Tribunal::has('juges')->count() }}
                    </div>
                    <div class="text-muted small">Tribunaux couverts</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-calendar-event fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ \App\Models\Juge::whereMonth('created_at', now()->month)->count() }}
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
            <div class="col-md-5">
                <label class="form-label small text-muted fw-semibold">Recherche</label>
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text"
                           name="search"
                           class="form-control border-start-0"
                           placeholder="Nom, grade ou spécialisation…"
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted fw-semibold">Tribunal</label>
                <select name="tribunal" class="form-select">
                    <option value="">Tous les tribunaux</option>
                    @foreach(\App\Models\Tribunal::orderBy('nom_tribunal')->get() as $t)
                        <option value="{{ $t->id }}" @selected(request('tribunal') == $t->id)>
                            {{ $t->nom_tribunal }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary flex-fill">
                    <i class="bi bi-funnel-fill me-1"></i>Filtrer
                </button>
                <a href="{{ route('juges.index') }}" class="btn btn-outline-secondary">
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
            <i class="bi bi-person-workspace me-2 text-primary"></i>Juges
            <span class="badge bg-primary ms-2">{{ $juges->total() }}</span>
        </h5>
        <a href="{{ route('juges.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouveau juge
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3 text-muted small fw-semibold">Juge</th>
                    <th class="text-muted small fw-semibold">Grade</th>
                    <th class="text-muted small fw-semibold">Spécialisation</th>
                    <th class="text-muted small fw-semibold">Tribunal</th>
                    <th class="text-muted small fw-semibold">Audiences à venir</th>
                    <th class="text-end pe-3 text-muted small fw-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($juges as $juge)
                <tr>
                    <td class="ps-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:38px;height:38px">
                                <span class="fw-semibold text-primary small">
                                    {{ strtoupper(substr($juge->nom_complet, 0, 2)) }}
                                </span>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $juge->nom_complet }}</div>
                                <div class="text-muted small">Juge</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                            {{ $juge->grade ?? '—' }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $juge->specialisation ?? '—' }}</td>
                    <td>
                        @if($juge->tribunal)
                            <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-25">
                                <i class="bi bi-building me-1"></i>{{ $juge->tribunal->nom_tribunal }}
                            </span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        @php $nbAud = $juge->audiences()->whereDate('date_audience', '>=', today())->count(); @endphp
                        @if($nbAud > 0)
                            <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-25">
                                <i class="bi bi-calendar-event me-1"></i>{{ $nbAud }} audience{{ $nbAud > 1 ? 's' : '' }}
                            </span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">Aucune</span>
                        @endif
                    </td>
                    <td class="text-end pe-3">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('juges.show', $juge) }}"
                               class="btn btn-sm btn-outline-primary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('juges.edit', $juge) }}"
                               class="btn btn-sm btn-outline-warning" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('juges.destroy', $juge) }}" method="POST"
                                  onsubmit="return confirm('Supprimer ce juge ?')">
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
                        <i class="bi bi-person-x fs-1 d-block mb-2 opacity-25"></i>
                        Aucun juge trouvé
                        @if(request()->hasAny(['search', 'tribunal']))
                            — <a href="{{ route('juges.index') }}">Réinitialiser les filtres</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($juges->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
        <span class="text-muted small">
            Affichage {{ $juges->firstItem() }}–{{ $juges->lastItem() }}
            sur {{ $juges->total() }} juges
        </span>
        {{ $juges->links() }}
    </div>
    @endif
</div>

@endsection