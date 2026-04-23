{{-- resources/views/jugements/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Jugement du ' . $jugement->date_jugement->format('d/m/Y'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('jugements.index') }}">Jugements</a></li>
    <li class="breadcrumb-item active">Jugement #{{ $jugement->id }}</li>
@endsection

@section('content')

{{-- ══ EN-TÊTE ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
            <div>
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-hammer me-2 text-primary"></i>
                    Jugement du {{ $jugement->date_jugement->format('d/m/Y') }}
                </h5>
                <div class="text-muted small">
                    <i class="bi bi-bank me-1"></i>
                    {{ $jugement->dossierTribunal->tribunal->nom_tribunal ?? '—' }}
                    &nbsp;·&nbsp;
                    <a href="{{ route('dossiers.show', $jugement->dossierTribunal->dossier) }}" class="text-decoration-none">
                        {{ $jugement->dossierTribunal->dossier->numero_dossier_interne ?? '—' }}
                    </a>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                {{-- Badge statut recours --}}
                @if($jugement->est_definitif)
                    <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 fs-6 px-3 py-2">
                        <i class="bi bi-check-circle me-1"></i>Définitif
                    </span>
                @else
                    @php $delai = $jugement->delai_recours_restant; @endphp
                    <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-25 fs-6 px-3 py-2">
                        <i class="bi bi-clock me-1"></i>
                        {{ $jugement->statut_recours_label }}
                    </span>
                @endif

                {{-- Actions --}}
                <a href="{{ route('jugements.edit', $jugement) }}" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
            </div>
        </div>

        <hr class="my-3">
        <div class="row g-2 small text-muted">
            <div class="col-sm-3">
                <strong>Juge :</strong> {{ $jugement->juge->nom_complet ?? '—' }}
            </div>
            <div class="col-sm-3">
                <strong>Dossier :</strong>
                {{ $jugement->dossierTribunal->dossier->numero_dossier_interne ?? '—' }}
            </div>
            <div class="col-sm-3">
                <strong>Statut dossier :</strong>
                {{ $jugement->dossierTribunal->dossier->statut->statut_dossier ?? '—' }}
            </div>
            <div class="col-sm-3">
                <strong>Créé par :</strong> {{ $jugement->createdBy->name ?? '—' }}
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">

        {{-- Dispositif --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-file-text me-2 text-primary"></i>Dispositif</h6>
            </div>
            <div class="card-body">
                <div class="p-3 bg-light rounded border" style="white-space:pre-wrap; font-family: inherit; line-height:1.8">
                    {{ $jugement->contenu_dispositif ?? '—' }}
                </div>
            </div>
        </div>

        {{-- Parties --}}
        @if($jugement->parties->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-people me-2 text-primary"></i>Parties</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 small text-muted">Nom</th>
                            <th class="small text-muted">Identifiant</th>
                            <th class="small text-muted">Montant condamné</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jugement->parties as $partie)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $partie->nom_partie }}</td>
                            <td class="text-muted small font-monospace">{{ $partie->identifiant_unique }}</td>
                            <td>
                                @if($partie->pivot->montant_condamne)
                                    <strong>{{ number_format($partie->pivot->montant_condamne, 2) }} DH</strong>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Recours existants --}}
        @if($jugement->recours->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-arrow-repeat me-2 text-warning"></i>Recours déposés</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 small text-muted">Type</th>
                            <th class="small text-muted">Date</th>
                            <th class="small text-muted">Dans le délai</th>
                            <th class="small text-muted">Motifs</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jugement->recours as $recours)
                        <tr>
                            <td class="ps-3 fw-semibold">
                                <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-25">
                                    {{ $recours->typeRecours->type_recours ?? '—' }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $recours->date_recours->format('d/m/Y') }}</td>
                            <td>
                                @if($recours->est_dans_delais)
                                    <span class="badge bg-success bg-opacity-15 text-success">✓ Dans les délais</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-15 text-danger">✗ Hors délai</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ Str::limit($recours->motifs ?? '—', 60) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

    {{-- ── Colonne latérale : Actions recours ── --}}
    <div class="col-lg-4">

        {{-- Finance --}}
        @if($jugement->finance)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-cash-stack me-2 text-success"></i>Finance</h6>
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Condamné</span>
                    <strong>{{ number_format($jugement->finance->montant_condamne, 2) }} DH</strong>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Payé</span>
                    <strong class="text-success">{{ number_format($jugement->finance->montant_paye, 2) }} DH</strong>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Restant</span>
                    <strong class="text-danger">{{ number_format($jugement->finance->montant_restant, 2) }} DH</strong>
                </div>
            </div>
        </div>
        @endif


@php
    use App\Models\TypeRecours;

    $dr = TypeRecours::orderBy('delai_legal_jours')->first();
    dump([
        'date_jugement' => $jugement->date_jugement->toDateString(),
        'today' => today()->toDateString(),
        'delai_minimal' => $dr?->delai_legal_jours,
        'type_recours' => $dr?->type_recours,
        'date_limite' => $dr ? $jugement->date_jugement->copy()->addDays($dr->delai_legal_jours)->toDateString() : null,
        'peut_recours' => $jugement->peutFaireObjetRecours(),
        'delai_restant' => $jugement->delai_recours_restant,
        'est_definitif' => $jugement->est_definitif,
        'recours_existe' => $jugement->recours()->exists(),
    ]);
@endphp

        {{-- ══ BLOC RECOURS (cœur des règles métier) ══ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-arrow-repeat me-2 text-warning"></i>Actions recours
                </h6>
            </div>
            <div class="card-body">

                @if($jugement->est_definitif)
                    {{-- Jugement définitif --}}
                    <div class="alert alert-success py-2 small mb-0">
                        <i class="bi bi-check-circle me-1"></i>
                        Ce jugement est <strong>définitif</strong>. Aucun recours possible.
                    </div>

                @elseif($jugement->recours->isNotEmpty())
                    {{-- Recours déjà déposé --}}
                    <div class="alert alert-warning py-2 small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Un recours a déjà été déposé sur ce jugement.
                    </div>

                @elseif(!$jugement->peutFaireObjetRecours())
                    {{-- Délai expiré --}}
                    <div class="alert alert-secondary py-2 small mb-2">
                        <i class="bi bi-clock me-1"></i>
                        Le délai légal de recours est <strong>expiré</strong>.
                    </div>

                    {{-- Bouton clôture manuelle --}}
                    <form action="{{ route('jugements.cloture-sans-recours', $jugement) }}" method="POST"
                          onsubmit="return confirm('Marquer ce jugement comme définitif et clôturer le dossier ?')">
                        @csrf
                        <button class="btn btn-secondary btn-sm w-100">
                            <i class="bi bi-lock me-1"></i>Clôturer sans recours
                        </button>
                    </form>

                @else
                    {{-- Délai en cours → formulaire de dépôt --}}
                    @php $delaiRestant = $jugement->delai_recours_restant; @endphp

                    @if($delaiRestant !== null && $delaiRestant <= 5)
                        <div class="alert alert-danger py-2 small mb-3">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <strong>Attention :</strong> il reste <strong>{{ $delaiRestant }} jour(s)</strong> pour déposer un recours.
                        </div>
                    @else
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="bi bi-clock me-1"></i>
                            Délai restant : <strong>{{ $delaiRestant ?? '—' }} jour(s)</strong>
                        </div>
                    @endif

                    <form action="{{ route('jugements.recours.store', $jugement) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">
                                Type de recours <span class="text-danger">*</span>
                            </label>
                            <select name="id_type_recours" class="form-select form-select-sm @error('id_type_recours') is-invalid @enderror" required>
                                <option value="">— Sélectionner —</option>
                                @foreach(\App\Models\TypeRecours::orderBy('type_recours')->get() as $tr)
                                    <option value="{{ $tr->id }}">
                                        {{ $tr->type_recours }}
                                        ({{ $tr->delai_legal_jours }}j)
                                    </option>
                                @endforeach
                            </select>
                            @error('id_type_recours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">
                                Date du recours <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="date_recours"
                                   class="form-control form-control-sm @error('date_recours') is-invalid @enderror"
                                   value="{{ date('Y-m-d') }}"
                                   min="{{ $jugement->date_jugement->format('Y-m-d') }}"
                                   required>
                            @error('date_recours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Motifs</label>
                            <textarea name="motifs"
                                      class="form-control form-control-sm"
                                      rows="3"
                                      placeholder="Motifs du recours…"></textarea>
                        </div>

                        <button type="submit" class="btn btn-warning btn-sm w-100"
                                onclick="return confirm('Confirmer le dépôt du recours ? Le statut du dossier sera mis à jour.')">
                            <i class="bi bi-arrow-repeat me-1"></i>Déposer le recours
                        </button>
                    </form>

                    <hr class="my-3">

                    {{-- Clôturer manuellement si décision de ne pas faire appel --}}
                    <form action="{{ route('jugements.cloture-sans-recours', $jugement) }}" method="POST"
                          onsubmit="return confirm('Clôturer définitivement ce jugement sans recours ?')">
                        @csrf
                        <button class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-x-circle me-1"></i>Clôturer sans recours
                        </button>
                    </form>
                @endif

            </div>
        </div>

        {{-- Exécutions --}}
        @if($jugement->est_definitif && $jugement->executions->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <a href="{{ route('executions.create', ['jugement_id' => $jugement->id]) }}"
                   class="btn btn-primary btn-sm">
                    <i class="bi bi-shield-check me-1"></i>Lancer l'exécution
                </a>
            </div>
        </div>
        @endif

        @if($jugement->executions->isNotEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-shield-check me-2 text-info"></i>Exécutions</h6>
            </div>
            <div class="list-group list-group-flush">
                @foreach($jugement->executions as $exec)
                <div class="list-group-item small d-flex justify-content-between align-items-center">
                    <span class="font-monospace">{{ $exec->numero_dossier_execution }}</span>
                    <span class="badge bg-info bg-opacity-15 text-info">
                        {{ $exec->statut->statut_execution ?? '—' }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

<div class="mt-3">
    <a href="{{ route('jugements.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
    @if($jugement->dossierTribunal?->dossier)
    <a href="{{ route('dossiers.show', $jugement->dossierTribunal->dossier) }}#tab-jugements"
       class="btn btn-outline-primary btn-sm ms-2">
        <i class="bi bi-folder2-open me-1"></i>Voir le dossier
    </a>
    @endif
</div>

@endsection