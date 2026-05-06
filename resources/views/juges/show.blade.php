@extends('layouts.app')

@section('title', $juge->nom_complet)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('juges.index') }}">Juges</a></li>
    <li class="breadcrumb-item active">{{ $juge->nom_complet }}</li>
@endsection

@section('content')

{{-- ══ EN-TÊTE ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">

            {{-- Identité --}}
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:56px;height:56px">
                    <span class="fw-bold text-primary fs-5">
                        {{ strtoupper(substr($juge->nom_complet, 0, 2)) }}
                    </span>
                </div>
                <div>
                    <h4 class="fw-bold mb-0">{{ $juge->nom_complet }}</h4>
                    <div class="mt-1 d-flex flex-wrap gap-2 align-items-center">
                        @if($juge->grade)
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                <i class="bi bi-award me-1"></i>{{ $juge->grade }}
                            </span>
                        @endif
                        @if($juge->tribunal)
                            <span class="badge bg-info bg-opacity-15 text-white border border-info border-opacity-25">
                                <i class="bi bi-building me-1"></i>{{ $juge->tribunal->nom_tribunal }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('juges.edit', $juge) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
                <form action="{{ route('juges.destroy', $juge) }}" method="POST"
                      onsubmit="return confirm('Supprimer ce juge ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash me-1"></i>Supprimer
                    </button>
                </form>
                <a href="{{ route('juges.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>
        </div>

        <hr class="my-3">

        <div class="row g-2 small text-muted">
            <div class="col-sm-3">
                <i class="bi bi-award me-1"></i>
                <strong>Grade :</strong>
                <span class="ms-1">{{ $juge->grade ?? '—' }}</span>
            </div>
            <div class="col-sm-3">
                <i class="bi bi-bookmark me-1"></i>
                <strong>Spécialisation :</strong>
                <span class="ms-1">{{ $juge->specialisation ?? '—' }}</span>
            </div>
            <div class="col-sm-3">
                <i class="bi bi-building me-1"></i>
                <strong>Tribunal :</strong>
                @if($juge->tribunal)
                    <a href="{{ route('tribunaux.show', $juge->tribunal) }}" class="text-decoration-none ms-1">
                        {{ $juge->tribunal->nom_tribunal }}
                    </a>
                @else
                    <span class="ms-1">—</span>
                @endif
            </div>
            <div class="col-sm-3">
                <i class="bi bi-clock me-1"></i>
                <strong>Mis à jour :</strong>
                <span class="ms-1">{{ $juge->updated_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">

        {{-- Audiences à venir --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-calendar-event me-2 text-warning"></i>Audiences à venir
                    <span class="badge bg-warning text-dark ms-1">
                        {{ $juge->audiences->where('date_audience', '>=', today())->count() }}
                    </span>
                </h6>
                <a href="{{ route('audiences.index', ['juge' => $juge->id]) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-list me-1"></i>Toutes les audiences
                </a>
            </div>

            @php $audiencesAVenir = $juge->audiences->where('date_audience', '>=', today())->sortBy('date_audience')->take(5); @endphp

            @if($audiencesAVenir->isEmpty())
                <div class="card-body text-center py-4 text-muted">
                    <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-25"></i>
                    Aucune audience à venir pour ce juge.
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 small text-muted fw-semibold">Date</th>
                            <th class="small text-muted fw-semibold">Dossier</th>
                            <th class="small text-muted fw-semibold">Type</th>
                            <th class="text-end pe-3 small text-muted fw-semibold">Voir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($audiencesAVenir as $audience)
                        <tr>
                            <td class="ps-3">
                                <span class="fw-semibold">{{ $audience->date_audience->format('d/m/Y') }}</span>
                                @if($audience->date_audience->isToday())
                                    <span class="badge bg-danger ms-1">Aujourd'hui</span>
                                @endif
                            </td>
                            <td>
                                @if($audience->dossierTribunal?->dossier)
                                    <a href="{{ route('dossiers.show', $audience->dossierTribunal->dossier) }}"
                                       class="text-decoration-none fw-semibold text-primary small">
                                        {{ $audience->dossierTribunal->dossier->numero_dossier_interne }}
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-15 text-white border border-info border-opacity-25 small" dir="rtl">
                                    {{ $audience->typeAudience?->type_audience ?? '—' }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('audiences.show', $audience) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Jugements rendus --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-hammer me-2 text-primary"></i>Jugements rendus
                    <span class="badge bg-primary ms-1">{{ $juge->jugements->count() }}</span>
                </h6>
            </div>

            @if($juge->jugements->isEmpty())
                <div class="card-body text-center py-4 text-muted">
                    <i class="bi bi-hammer fs-1 d-block mb-2 opacity-25"></i>
                    Aucun jugement enregistré pour ce juge.
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 small text-muted fw-semibold">Date</th>
                            <th class="small text-muted fw-semibold">Dossier</th>
                            <th class="small text-muted fw-semibold">Caractère</th>
                            <th class="text-end pe-3 small text-muted fw-semibold">Voir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($juge->jugements->sortByDesc('date_jugement')->take(10) as $jugement)
                        <tr>
                            <td class="ps-3 fw-semibold small">
                                {{ $jugement->date_jugement->format('d/m/Y') }}
                            </td>
                            <td>
                                @if($jugement->dossierTribunal?->dossier)
                                    <a href="{{ route('dossiers.show', $jugement->dossierTribunal->dossier) }}"
                                       class="text-decoration-none fw-semibold text-primary small">
                                        {{ $jugement->dossierTribunal->dossier->numero_dossier_interne }}
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                @if($jugement->est_definitif)
                                    <span class="badge bg-success bg-opacity-15 text-white border border-success border-opacity-25">
                                        <i class="bi bi-check-circle me-1"></i>Définitif
                                    </span>
                                @else
                                    <span class="badge bg-warning bg-opacity-15 text-black border border-warning border-opacity-25">
                                        <i class="bi bi-clock me-1"></i>En cours
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('jugements.show', $jugement) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

    </div>

    {{-- ── Colonne latérale ── --}}
    <div class="col-lg-4">

        {{-- Informations --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-muted"></i>Informations
                </h6>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-6 text-muted fw-normal">Grade</dt>
                    <dd class="col-6">{{ $juge->grade ?? '—' }}</dd>

                    <dt class="col-6 text-muted fw-normal">Spécialisation</dt>
                    <dd class="col-6">{{ $juge->specialisation ?? '—' }}</dd>

                    <dt class="col-6 text-muted fw-normal">Tribunal</dt>
                    <dd class="col-6">
                        @if($juge->tribunal)
                            <a href="{{ route('tribunaux.show', $juge->tribunal) }}" class="text-decoration-none">
                                {{ $juge->tribunal->nom_tribunal }}
                            </a>
                        @else
                            <span class="text-muted fst-italic">Non assigné</span>
                        @endif
                    </dd>

                    <dt class="col-6 text-muted fw-normal">Audiences</dt>
                    <dd class="col-6">
                        <span class="badge bg-info bg-opacity-15 text-white border border-info border-opacity-25">
                            {{ $juge->audiences->count() }} au total
                        </span>
                    </dd>

                    <dt class="col-6 text-muted fw-normal">Jugements</dt>
                    <dd class="col-6">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                            {{ $juge->jugements->count() }}
                        </span>
                    </dd>

                    <dt class="col-6 text-muted fw-normal">Créé le</dt>
                    <dd class="col-6">{{ $juge->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">Modifié le</dt>
                    <dd class="col-6">{{ $juge->updated_at->format('d/m/Y') }}</dd>
                </dl>
            </div>
        </div>

        {{-- Actions rapides --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 d-flex flex-column gap-2">
                <a href="{{ route('juges.edit', $juge) }}"
                   class="btn btn-warning w-100 btn-sm">
                    <i class="bi bi-pencil me-1"></i>Modifier ce juge
                </a>
                @if($juge->tribunal)
                <a href="{{ route('tribunaux.show', $juge->tribunal) }}"
                   class="btn btn-outline-info w-100 btn-sm">
                    <i class="bi bi-building me-1"></i>Voir le tribunal
                </a>
                @endif
                <a href="{{ route('audiences.index', ['juge' => $juge->id]) }}"
                   class="btn btn-outline-primary w-100 btn-sm">
                    <i class="bi bi-calendar-event me-1"></i>Toutes ses audiences
                </a>
                <a href="{{ route('juges.index') }}"
                   class="btn btn-outline-secondary w-100 btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Retour à la liste
                </a>
            </div>
        </div>

    </div>

</div>

@endsection