@extends('layouts.app')

@section('title', 'Dossier ' . $dossier->numero_dossier_interne)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dossiers.index') }}">Dossiers</a></li>
    <li class="breadcrumb-item active">{{ $dossier->numero_dossier_interne }}</li>
@endsection

@section('content')

{{-- ══════════════════════════════════════════════════════════
     EN-TÊTE DU DOSSIER
══════════════════════════════════════════════════════════ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">

            {{-- Identité --}}
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                     style="width:56px;height:56px">
                    <i class="bi bi-folder2-open fs-3 text-primary"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0">{{ $dossier->numero_dossier_interne }}</h4>
                    @if($dossier->numero_dossier_tribunal)
                        <span class="text-muted small">
                            <i class="bi bi-bank me-1"></i>Tribunal : {{ $dossier->numero_dossier_tribunal }}
                        </span>
                    @endif
                    <div class="mt-1">
                        @php
                            $statut = $dossier->statutDossier->statut_dossier ?? '—';
                            $color  = match(true) {
                                str_contains($statut, 'Actif')    => 'success',
                                str_contains($statut, 'Clôturé')  => 'secondary',
                                str_contains($statut, 'Suspendu') => 'warning',
                                default => 'primary',
                            };
                        @endphp
                        <span class="badge bg-{{ $color }} bg-opacity-15 text-{{ $color }} border border-{{ $color }} border-opacity-25">
                            <i class="bi bi-circle-fill me-1" style="font-size:.5rem;vertical-align:middle"></i>
                            {{ $statut }}
                        </span>
                        <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-25 ms-1">
                            {{ $dossier->typeAffaire->nom ?? '—' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Métadonnées --}}
            <div class="d-flex flex-wrap gap-4 small text-muted">
                <div class="text-center">
                    <div class="fw-semibold text-dark fs-6">{{ $dossierParties->count() }}</div>
                    <div>Parties</div>
                </div>
                <div class="text-center">
                    <div class="fw-semibold text-dark fs-6">{{ $dossier->dossierTribunaux->count() }}</div>
                    <div>Tribunaux</div>
                </div>
                <div class="text-center">
                    <div class="fw-semibold text-dark fs-6">
                        {{ $dossier->dossierTribunaux->flatMap->audiences->count() }}
                    </div>
                    <div>Audiences</div>
                </div>
                <div class="text-center">
                    <div class="fw-semibold text-dark fs-6">
                        {{ $dossier->dossierTribunaux->flatMap->jugements->count() }}
                    </div>
                    <div>Jugements</div>
                </div>
                <div class="text-center">
                    <div class="fw-semibold text-dark fs-6">
                        {{ number_format($stats['total_finances'], 2) }} DH
                    </div>
                    <div>Montant Condamné</div>
                </div>
                <div class="text-center">
                    <div class="fw-semibold text-dark fs-6">
                        {{ number_format($stats['total_paye'], 2) }} DH
                    </div>
                    <div>Montant Payé</div>
                </div>
                <div class="text-center">
                    <div class="fw-semibold text-dark fs-6">{{ $dossier->documents->count() }}</div>
                    <div>Documents</div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex gap-2">
                @can('update', $dossier)
                    <a href="{{ route('dossiers.edit', $dossier) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil me-1"></i>Modifier
                    </a>
                @endcan
                @can('delete', $dossier)
                    <form action="{{ route('dossiers.destroy', $dossier) }}" method="POST"
                          onsubmit="return confirm('Archiver ce dossier ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-archive me-1"></i>Archiver
                        </button>
                    </form>
                @endcan
            </div>

        </div>

        {{-- Métadonnées ligne 2 --}}
        <hr class="my-3">
        <div class="row g-2 small text-muted">
            <div class="col-sm-3">
                <i class="bi bi-calendar-event me-1"></i>
                <strong>Ouverture :</strong> {{ $dossier->date_ouverture?->format('d/m/Y') ?? '—' }}
            </div>
            <div class="col-sm-3">
                <i class="bi bi-calendar-check me-1"></i>
                <strong>Clôture :</strong> {{ $dossier->date_cloture?->format('d/m/Y') ?? 'En cours' }}
            </div>
            <div class="col-sm-3">
                <i class="bi bi-person me-1"></i>
                <strong>Créé par :</strong> {{ $dossier->createdBy->name ?? '—' }}
            </div>
            <div class="col-sm-3">
                <i class="bi bi-clock me-1"></i>
                <strong>Mis à jour :</strong> {{ $dossier->updated_at->diffForHumans() }}
            </div>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════
     ONGLETS PRINCIPAUX
══════════════════════════════════════════════════════════ --}}
<ul class="nav nav-tabs mb-0" id="dossierTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-parties">
            <i class="bi bi-people me-1"></i>Parties
            <span class="badge bg-primary ms-1">{{ $dossierParties->count() }}</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-tribunaux">
            <i class="bi bi-bank me-1"></i>Tribunaux
            <span class="badge bg-secondary ms-1">{{ $dossier->dossierTribunaux->count() }}</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-audiences">
            <i class="bi bi-calendar3 me-1"></i>Audiences
            <span class="badge bg-info ms-1">
                {{ $dossier->dossierTribunaux->flatMap->audiences->count() }}
            </span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-jugements">
            <i class="bi bi-hammer me-1"></i>Jugements
            <span class="badge bg-dark ms-1">
                {{ $dossier->dossierTribunaux->flatMap->jugements->count() }}
            </span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-finances">
            <i class="bi bi-cash-stack me-1"></i>Finances
            <span class="badge bg-success ms-1">
                {{ $dossier->dossierTribunaux->flatMap->jugements->pluck('finance')->filter()->count() }}
            </span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-documents">
            <i class="bi bi-paperclip me-1"></i>Documents
            <span class="badge bg-warning text-dark ms-1">{{ $dossier->documents->count() }}</span>
        </button>
    </li>
</ul>

<div class="tab-content border border-top-0 rounded-bottom bg-white shadow-sm p-4" id="dossierTabContent">

    {{-- ══════════════════════════════════════════════════════
         ONGLET 1 : PARTIES
    ══════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade show active" id="tab-parties">

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-semibold mb-0"><i class="bi bi-people me-2 text-primary"></i>Parties impliquées</h6>
            @can('update', $dossier)
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterPartie">
                    <i class="bi bi-person-plus me-1"></i>Ajouter une partie
                </button>
            @endcan
        </div>

        @if($dossierParties->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>
                Aucune partie enregistrée pour ce dossier.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="small text-muted fw-semibold">Identifiant</th>
                        <th class="small text-muted fw-semibold">Nom / Dénomination</th>
                        <th class="small text-muted fw-semibold">Type de personne</th>
                        <th class="small text-muted fw-semibold">Rôle dans le dossier</th>
                        <th class="small text-muted fw-semibold">Avocat</th>
                        <th class="small text-muted fw-semibold">Institution</th>
                        <th class="small text-muted fw-semibold">Date d'entrée</th>
                        <th class="small text-muted fw-semibold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dossierParties as $dp)
                    <tr>
                        <td class="text-muted small font-monospace">
                            {{ $dp->partie->identifiant_unique ?? '—' }}
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $dp->partie->nom_partie ?? '—' }}</div>
                            @if($dp->partie?->email)
                                <div class="text-muted small">{{ $dp->partie->email }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $dp->partie->type_personne === 'Morale' ? 'warning' : 'success' }} bg-opacity-15 text-{{ $dp->partie->type_personne === 'Morale' ? 'warning' : 'success' }}">
                                <i class="bi bi-{{ $dp->partie->type_personne === 'Morale' ? 'building' : 'person' }} me-1"></i>
                                {{ $dp->partie->type_personne ?? '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                {{ $dp->typePartie->type_partie ?? '—' }}
                            </span>
                        </td>
                        <td class="text-muted small">
                            @if($dp->avocat)
                                <i class="bi bi-briefcase me-1"></i>{{ $dp->avocat->nom_avocat }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($dp->est_institution)
                                <span class="badge bg-info bg-opacity-15 text-info">
                                    <i class="bi bi-building me-1"></i>Oui
                                </span>
                            @else
                                <span class="text-muted small">Non</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ $dp->date_entree?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="text-end">
                            @can('update', $dossier)
                            <div class="d-flex gap-1 justify-content-end">
                                <button class="btn btn-sm btn-outline-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditPartie{{ $dp->id }}"
                                        title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('dossiers.parties.destroy', [$dossier, $dp]) }}" method="POST"
                                      onsubmit="return confirm('Retirer cette partie du dossier ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Retirer">
                                        <i class="bi bi-person-dash"></i>
                                    </button>
                                </form>
                            </div>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>{{-- /tab-parties --}}


    {{-- ══════════════════════════════════════════════════════
         ONGLET 2 : TRIBUNAUX
    ══════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-tribunaux">

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-semibold mb-0"><i class="bi bi-bank me-2 text-primary"></i>Tribunaux assignés</h6>
            @can('update', $dossier)
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterTribunal">
                    <i class="bi bi-plus-lg me-1"></i>Assigner un tribunal
                </button>
            @endcan
        </div>

        @if($dossier->dossierTribunaux->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-bank fs-1 d-block mb-2 opacity-25"></i>
                Aucun tribunal assigné à ce dossier.
            </div>
        @else
        <div class="row g-3">
            @foreach($dossier->dossierTribunaux as $dt)
            <div class="col-md-6">
                <div class="card border h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="fw-semibold">
                                    <i class="bi bi-bank me-2 text-primary"></i>
                                    {{ $dt->tribunal->nom_tribunal ?? '—' }}
                                </div>
                                @if($dt->tribunal?->typeTribunal)
                                    <span class="badge bg-light text-secondary border small mt-1">
                                        {{ $dt->tribunal->typeTribunal->nom }}
                                    </span>
                                @endif
                            </div>
                            @can('update', $dossier)
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditTribunal{{ $dt->id }}"
                                        title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('dossiers.tribunaux.destroy', [$dossier, $dt]) }}" method="POST"
                                      onsubmit="return confirm('Retirer ce tribunal ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Retirer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                            @endcan
                        </div>

                        <hr class="my-2">
                        <div class="row small text-muted">
                            <div class="col-6">
                                <i class="bi bi-layers me-1"></i>
                                <strong>Degré :</strong> {{ $dt->degre->degre_juridiction ?? '—' }}
                            </div>
                            <div class="col-6">
                                <i class="bi bi-calendar-event me-1"></i>
                                <strong>Début :</strong> {{ $dt->date_debut?->format('d/m/Y') ?? '—' }}
                            </div>
                            <div class="col-6 mt-1">
                                <i class="bi bi-calendar-check me-1"></i>
                                <strong>Fin :</strong> {{ $dt->date_fin?->format('d/m/Y') ?? 'En cours' }}
                            </div>
                            <div class="col-6 mt-1">
                                <i class="bi bi-calendar3 me-1"></i>
                                <strong>Audiences :</strong> {{ $dt->audiences->count() }}
                            </div>
                        </div>

                        @if($dt->audiences->isNotEmpty())
                        <div class="mt-2 pt-2 border-top">
                            <div class="small text-muted fw-semibold mb-1">Prochaines audiences :</div>
                            @foreach($dt->audiences->take(3) as $aud)
                                <div class="small d-flex align-items-center gap-2 mb-1">
                                    <i class="bi bi-dot text-primary fs-5 lh-1"></i>
                                    <span>{{ $aud->date_audience?->format('d/m/Y') ?? '—' }}</span>
                                    @if($aud->typeAudience)
                                        <span class="badge bg-light text-secondary border">
                                            {{ $aud->typeAudience->nom }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>{{-- /tab-tribunaux --}}


 {{-- ══════════════════════════════════════════════════════
     ONGLET 3 : AUDIENCES
══════════════════════════════════════════════════════ --}}
<div class="tab-pane fade" id="tab-audiences">

    @php
        $toutesAudiences = $dossier->dossierTribunaux->flatMap(function($dt) {
            return $dt->audiences->map(fn($a) => $a->setRelation('dossierTribunal', $dt));
        })->sortByDesc('date_audience');

        $derniereAudience = $toutesAudiences->sortByDesc('date_audience')->first();

        $prochaineDate = $derniereAudience?->date_prochaine_audience;

        $audienceFutureExiste = $toutesAudiences->contains(function($aud) {
            return $aud->date_audience && $aud->date_audience->isFuture();
        });
    @endphp

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h6 class="fw-semibold mb-0">
                <i class="bi bi-calendar3 me-2 text-primary"></i>Audiences
            </h6>

            @if($prochaineDate)
                <small class="text-muted">
                    Prochaine audience prévue :
                    <strong>{{ $prochaineDate->format('d/m/Y') }}</strong>
                </small>
            @endif
        </div>

        @if($dossier->peutAvoirAudience() && $dossier->dossierTribunaux->isNotEmpty())
            <a href="{{ route('audiences.create', ['dossier_id' => $dossier->id]) }}"
                class="btn btn-primary">
                    Planifier une audience
                </a>
        @else
                <button class="btn btn-secondary" disabled>
                    Ajoutez au moins 2 parties
                </button>
            
        @endif
    </div>

    @if($toutesAudiences->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-25"></i>
            Aucune audience enregistrée.
            @if($dossier->dossierTribunaux->isEmpty())
                <div class="small mt-1">
                    Assignez d'abord un tribunal au dossier.
                </div>
            @endif
        </div>
    @else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th class="small text-muted fw-semibold">Date</th>
                    <th class="small text-muted fw-semibold">Type</th>
                    <th class="small text-muted fw-semibold">Tribunal</th>
                    <th class="small text-muted fw-semibold">Résultat / Renvoi</th>
                    <th class="small text-muted fw-semibold text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($toutesAudiences as $aud)
                <tr>
                    <td>
                        <strong>{{ $aud->date_audience?->format('d/m/Y') ?? '—' }}</strong>

                        @if($aud->date_audience && $aud->date_audience->isToday())
                            <span class="badge bg-danger ms-1">Aujourd'hui</span>
                        @elseif($aud->date_audience && $aud->date_audience->isFuture())
                            <span class="badge bg-primary bg-opacity-15 text-primary ms-1">À venir</span>
                        @endif

                        @if($aud->date_prochaine_audience)
                            <div class="small text-muted mt-1">
                                Renvoi :
                                <strong>{{ $aud->date_prochaine_audience->format('d/m/Y') }}</strong>
                            </div>
                        @endif
                    </td>

                    <td>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                            {{ $aud->typeAudience->nom ?? '—' }}
                        </span>
                    </td>

                    <td class="text-muted small">
                        <i class="bi bi-bank me-1"></i>
                        {{ $aud->dossierTribunal->tribunal->nom_tribunal ?? '—' }}
                    </td>

                    <td class="text-muted small">
                        {{ $aud->resultat_audience ?? $aud->actions_demandees ?? '—' }}
                    </td>

                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('audiences.show', $aud) }}"
                               class="btn btn-sm btn-outline-primary"
                               title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>

                            @can('update', $aud)
                            <a href="{{ route('audiences.edit', $aud) }}"
                               class="btn btn-sm btn-outline-warning"
                               title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>{{-- /tab-audiences --}}

{{-- ══════════════════════════════════════════════════════
     ONGLET 4 : JUGEMENTS
══════════════════════════════════════════════════════ --}}
<div class="tab-pane fade" id="tab-jugements">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-semibold mb-0">
            <i class="bi bi-hammer me-2 text-primary"></i>Jugements
        </h6>

        @php
            $peutAvoirJugement = $dossier->dossierTribunaux->contains(function ($dt) {
                return $dt->peutAvoirJugement();
            });
        @endphp

        @if($peutAvoirJugement)
            <a href="{{ route('jugements.create', ['dossier_id' => $dossier->id]) }}"
               class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Enregistrer un jugement
            </a>
        @endif
    </div>

    @php
        $jugements = $dossier->dossierTribunaux->flatMap->jugements;
    @endphp

    @if($jugements->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-hammer fs-1 d-block mb-2 opacity-25"></i>
            Aucun jugement enregistré.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Tribunal</th>
                        <th>Décision</th>
                        <th>Finance</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jugements as $jugement)
                    <tr>
                        <td>{{ $jugement->date_jugement?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $jugement->dossierTribunal->tribunal->nom_tribunal ?? '—' }}</td>
                        <td>{{ Str::limit($jugement->decision ?? '—', 40) }}</td>
                        <td>
                            @if($jugement->finance)
                                <span class="badge bg-success">Oui</span>
                            @else
                                <span class="badge bg-warning text-dark">Non</span>
                            @endif
                        </td>
                        <td class="text-end">
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


{{-- ══════════════════════════════════════════════════════
     ONGLET 5 : FINANCES
══════════════════════════════════════════════════════ --}}
<div class="tab-pane fade" id="tab-finances">

    @php
        $finances = $dossier->dossierTribunaux
            ->flatMap->jugements
            ->pluck('finance')
            ->filter();

        $jugementsSansFinance = $dossier->dossierTribunaux
            ->flatMap->jugements
            ->filter(fn($j) => !$j->finance);
    @endphp

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-semibold mb-0">
            <i class="bi bi-cash-stack me-2 text-success"></i>Finances liées aux jugements
        </h6>

        @if($jugementsSansFinance->isNotEmpty())
            <button class="btn btn-success btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#modalAjouterFinance">
                <i class="bi bi-plus-lg me-1"></i>Ajouter finance
            </button>
        @endif
    </div>

    @if($finances->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-cash-coin fs-1 d-block mb-2 opacity-25"></i>
            Aucune donnée financière enregistrée.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Jugement</th>
                        <th>Condamné</th>
                        <th>Payé</th>
                        <th>Restant</th>
                        <th>Statut</th>
                        <th>Date paiement</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($finances as $finance)
                    <tr>
                        <td>#{{ $finance->id_jugement }}</td>
                        <td>{{ number_format($finance->montant_condamne,2) }} DH</td>
                        <td>{{ number_format($finance->montant_paye,2) }} DH</td>
                        <td>{{ number_format($finance->montant_restant,2) }} DH</td>
                        <td>
                            @if($finance->statut_paiement === 'Complet')
                                <span class="badge bg-success">Complet</span>
                            @elseif($finance->statut_paiement === 'Partiel')
                                <span class="badge bg-warning text-dark">Partiel</span>
                            @else
                                <span class="badge bg-secondary">En attente</span>
                            @endif
                        </td>
                        <td>{{ $finance->date_paiement?->format('d/m/Y') ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('finances.show', $finance) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('finances.edit', $finance) }}"
                               class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>




{{-- ══════════════════════════════════════════════════════
         ONGLET 6 : DOCUMENTS
══════════════════════════════════════════════════════ --}}
<div class="tab-pane fade" id="tab-documents">

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-semibold mb-0"><i class="bi bi-paperclip me-2 text-primary"></i>Documents</h6>
            @can('update', $dossier)
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterDocument">
                <i class="bi bi-upload me-1"></i>Joindre un document
            </button>
            @endcan
        </div>

        @if($dossier->documents->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-file-earmark fs-1 d-block mb-2 opacity-25"></i>
                Aucun document joint à ce dossier.
            </div>
        @else
        <div class="row g-3">
            @foreach($dossier->documents as $doc)
            <div class="col-md-4 col-lg-3">
                <div class="card border h-100">
                    <div class="card-body d-flex flex-column align-items-center text-center py-4">
                        @php
                            $ext  = pathinfo($doc->nom_fichier ?? '', PATHINFO_EXTENSION);
                            $icon = match(strtolower($ext)) {
                                'pdf'         => 'bi-file-earmark-pdf text-danger',
                                'doc','docx'  => 'bi-file-earmark-word text-primary',
                                'xls','xlsx'  => 'bi-file-earmark-excel text-success',
                                'jpg','jpeg','png','gif' => 'bi-file-earmark-image text-warning',
                                default       => 'bi-file-earmark text-secondary',
                            };
                        @endphp
                        <i class="bi {{ $icon }} fs-1 mb-2"></i>
                        <div class="small fw-semibold text-truncate w-100" title="{{ $doc->nom_fichier }}">
                            {{ $doc->nom_fichier ?? 'Document' }}
                        </div>
                        @if($doc->typeDocument)
                            <span class="badge bga-light text-secondary border small mt-1">
                                {{ $doc->typeDocument->nom }}
                            </span>
                        @endif
                        <div class="text-muted" style="font-size:.7rem">
                            {{ $doc->created_at->format('d/m/Y') }}
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top d-flex gap-1 justify-content-center py-2">
                        <a href="{{ route('documents.download', [$dossier, $doc]) }}"
                           class="btn btn-sm btn-outline-primary flex-fill" title="Télécharger">
                            <i class="bi bi-download"></i>
                        </a>
                        @can('update', $dossier)
                        <form action="{{ route('documents.destroy', [$dossier, $doc]) }}" method="POST"
                              onsubmit="return confirm('Supprimer ce document ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

</div>{{-- /tab-documents --}}



{{-- ══════════════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════════════ --}}

{{-- ── MODAL : Ajouter une partie (avec recherche AJAX) ── --}}
<div class="modal fade" id="modalAjouterPartie" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-semibold">
                    <i class="bi bi-person-plus me-2 text-primary"></i>Ajouter une partie
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                {{-- ── ÉTAPE 1 : Recherche ── --}}
                <div class="mb-3 p-3 rounded-3 border bg-light">
                    <label class="form-label fw-semibold small text-muted text-uppercase mb-2"
                           style="letter-spacing:.05em">
                        <i class="bi bi-search me-1"></i>Étape 1 — Rechercher une partie existante
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text"
                               id="recherchePartie"
                               class="form-control"
                               placeholder="Identifiant (CIN / RC) ou nom de la partie…"
                               autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" id="btnNouvellePartie">
                            <i class="bi bi-plus-lg me-1"></i>Nouvelle
                        </button>
                    </div>
                    <div class="form-text">
                        Tapez au moins 2 caractères pour rechercher. Sélectionnez un résultat pour
                        pré-remplir le formulaire, ou cliquez sur <strong>Nouvelle</strong> pour
                        saisir une partie inédite.
                    </div>

                    {{-- Dropdown résultats --}}
                    <div id="resultatRecherche"
                         class="list-group mt-1 shadow-sm"
                         style="display:none; max-height:240px; overflow-y:auto; position:relative; z-index:1060;">
                    </div>

                    {{-- Bandeau partie sélectionnée --}}
                    <div id="partieSelectionnee" class="alert alert-success py-2 px-3 mt-2 d-none small mb-0">
                        <i class="bi bi-check-circle me-1"></i>
                        Partie existante sélectionnée :
                        <strong id="partieSelectionneeNom"></strong>
                        <a href="#" id="btnDeselectionner" class="ms-2 text-danger small">(changer)</a>
                    </div>
                </div>

                {{-- ── ÉTAPE 2 : Formulaire ── --}}
                <div class="border-top pt-3 mb-3">
                    <p class="small text-muted fw-semibold text-uppercase mb-0" style="letter-spacing:.05em">
                        Étape 2 — Informations de la partie
                    </p>
                </div>

                <form id="formAjouterPartie"
                      action="{{ route('dossiers.parties.store', $dossier) }}"
                      method="POST">
                @csrf

                {{-- ID caché : rempli quand une partie existante est sélectionnée --}}
                <input type="hidden" name="partie_id" id="hidden_partie_id">

                <div class="row g-3">

                    {{-- Identité --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            Identifiant unique (CIN / RC) <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="identifiant_unique"
                               id="field_identifiant"
                               class="form-control"
                               placeholder="Ex : AB123456"
                               required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            Nom / Dénomination <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="nom_partie"
                               id="field_nom"
                               class="form-control"
                               placeholder="Nom complet ou raison sociale"
                               required>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold small">Type de personne</label>
                        <select name="type_personne" id="field_type_personne" class="form-select">
                            <option value="Physique">Physique</option>
                            <option value="Morale">Morale</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold small">Téléphone</label>
                        <input type="text" name="telephone" id="field_telephone"
                               class="form-control" placeholder="06XXXXXXXX">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold small">Email</label>
                        <input type="email" name="email" id="field_email"
                               class="form-control" placeholder="exemple@mail.com">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Adresse</label>
                        <textarea name="adresse" id="field_adresse"
                                  class="form-control" rows="2"></textarea>
                    </div>

                    <div class="col-12"><hr class="my-1"></div>

                    {{-- Rôle / avocat / date --}}
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold small">
                            Rôle dans le dossier <span class="text-danger">*</span>
                        </label>
                        <select name="id_type_partie" class="form-select" required>
                            <option value="">— Sélectionner —</option>
                            @foreach($typesPartie as $tp)
                                <option value="{{ $tp->id }}">{{ $tp->type_partie }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold small">Avocat représentant</label>
                        <select name="id_avocat" class="form-select">
                            <option value="">— Aucun —</option>
                            @foreach($avocats as $av)
                                <option value="{{ $av->id }}">{{ $av->nom_avocat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold small">
                            Date d'entrée <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="date_entree" class="form-control"
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="hidden" name="est_institution" value="0">
                            <input class="form-check-input" type="checkbox"
                                   name="est_institution" value="1" id="checkInstitution">
                            <label class="form-check-label small" for="checkInstitution">
                                Cette partie est une institution
                            </label>
                        </div>
                    </div>
                </div>
                </form>

            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="formAjouterPartie" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Ajouter au dossier
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ── MODALS : Modifier chaque partie ── --}}
@foreach($dossierParties as $dp)
<div class="modal fade" id="modalEditPartie{{ $dp->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">
                    <i class="bi bi-pencil me-2 text-warning"></i>
                    Modifier : {{ $dp->partie->nom_partie ?? '—' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
            <form id="formEditPartie{{ $dp->id }}"
                  action="{{ route('dossiers.parties.update', [$dossier, $dp]) }}"
                  method="POST">
            @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            Rôle dans le dossier <span class="text-danger">*</span>
                        </label>
                        <select name="id_type_partie" class="form-select" required>
                            @foreach($typesPartie as $tp)
                                <option value="{{ $tp->id }}" @selected($dp->id_type_partie == $tp->id)>
                                    {{ $tp->type_partie }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Avocat représentant</label>
                        <select name="id_avocat" class="form-select">
                            <option value="">— Aucun —</option>
                            @foreach($avocats as $av)
                                <option value="{{ $av->id }}" @selected($dp->id_avocat == $av->id)>
                                    {{ $av->nom_avocat }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Date d'entrée</label>
                        <input type="date" name="date_entree" class="form-control"
                               value="{{ $dp->date_entree?->format('Y-m-d') }}">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="hidden" name="est_institution" value="0">
                            <input class="form-check-input" type="checkbox"
                                   name="est_institution" value="1"
                                   @checked($dp->est_institution)
                                   id="checkEditInst{{ $dp->id }}">
                            <label class="form-check-label small" for="checkEditInst{{ $dp->id }}">
                                Institution
                            </label>
                        </div>
                    </div>
                </div>
            </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="formEditPartie{{ $dp->id }}" class="btn btn-warning">
                    <i class="bi bi-check-lg me-1"></i>Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach


{{-- ── MODAL : Assigner un tribunal ── --}}
<div class="modal fade" id="modalAjouterTribunal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">
                    <i class="bi bi-bank me-2 text-primary"></i>Assigner un tribunal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAjouterTribunal"
                      action="{{ route('dossiers.tribunaux.store', $dossier) }}"
                      method="POST">
                @csrf
                <div class="row g-3">

                    {{-- Région --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            Région <span class="text-danger">*</span>
                        </label>
                        <select id="modal_region" class="form-select">
                            <option value="">— Sélectionner une région —</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}">{{ $region->region }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Province --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            Province <span class="text-danger">*</span>
                        </label>
                        <select id="modal_province" class="form-select" disabled>
                            <option value="">— Sélectionner d'abord une région —</option>
                        </select>
                    </div>

                    {{-- Degré --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            Degré de juridiction <span class="text-danger">*</span>
                        </label>
                        <select id="modal_degre" name="id_degre" class="form-select" disabled required>
                            <option value="">— Sélectionner d'abord une province —</option>
                        </select>
                    </div>

                    {{-- Tribunal --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            Tribunal <span class="text-danger">*</span>
                        </label>
                        <select id="modal_tribunal" name="id_tribunal" class="form-select" disabled required>
                            <option value="">— Sélectionner d'abord un degré —</option>
                        </select>
                    </div>

                    {{-- Dates --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            Date de saisine <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="date_debut" class="form-control"
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Date de fin</label>
                        <input type="date" name="date_fin" class="form-control">
                    </div>

                </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="formAjouterTribunal" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Assigner
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ── MODALS : Modifier chaque tribunal ── --}}
@foreach($dossier->dossierTribunaux as $dt)
<div class="modal fade" id="modalEditTribunal{{ $dt->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">
                    <i class="bi bi-pencil me-2 text-warning"></i>
                    Modifier : {{ $dt->tribunal->nom_tribunal ?? '—' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
            <form id="formEditTribunal{{ $dt->id }}"
                  action="{{ route('dossiers.tribunaux.update', [$dossier, $dt]) }}"
                  method="POST">
            @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Degré de juridiction</label>
                        <select name="id_degre" class="form-select" required>
                            @foreach($degresJuridiction as $d)
                                <option value="{{ $d->id }}" @selected($dt->id_degre == $d->id)>
                                    {{ $d->degre_juridiction }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Date de saisine</label>
                        <input type="date" name="date_debut" class="form-control"
                               value="{{ $dt->date_debut?->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Date de fin</label>
                        <input type="date" name="date_fin" class="form-control"
                               value="{{ $dt->date_fin?->format('Y-m-d') }}">
                    </div>
                </div>
            </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="formEditTribunal{{ $dt->id }}" class="btn btn-warning">
                    <i class="bi bi-check-lg me-1"></i>Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach

{{-- ── MODAL : Ajouter une finance ── --}}
<div class="modal fade" id="modalAjouterFinance" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-semibold">
                    <i class="bi bi-cash-stack me-2 text-success"></i>
                    Ajouter une finance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formAjouterFinance"
                      action="{{ route('finances.store') }}"
                      method="POST">
                    @csrf

                    {{-- Jugement --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Jugement <span class="text-danger">*</span>
                        </label>
                        <select name="id_jugement" class="form-select" required>
                            <option value="">— Sélectionner un jugement —</option>
                            @foreach($dossier->dossierTribunaux->flatMap->jugements as $jugement)
                                @if(!$jugement->finance)
                                    <option value="{{ $jugement->id }}">
                                        Jugement #{{ $jugement->id }}
                                        — {{ $jugement->date_jugement?->format('d/m/Y') }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <div class="form-text">
                            Seuls les jugements sans finance apparaissent ici.
                        </div>
                    </div>

                    {{-- Montant condamné --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Montant condamné <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                            step="0.01"
                            min="0"
                            name="montant_condamne"
                            id="montant_condamne"
                            class="form-control"
                            placeholder="0.00"
                            required>
                    </div>

                    {{-- Montant payé --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Montant payé
                        </label>
                        <input type="number"
                            step="0.01"
                            min="0"
                            name="montant_paye"
                            id="montant_paye"
                            class="form-control"
                            placeholder="0.00">
                    </div>

                    {{-- Date paiement --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Date de paiement
                        </label>
                        <input type="date"
                               name="date_paiement"
                               class="form-control">
                    </div>

                    {{-- Statut --}}
                    <div class="form-check">
                        <input type="hidden" name="est_solde" value="0">
                        <input class="form-check-input"
                               type="checkbox"
                               name="est_solde"
                               value="1"
                               id="checkSolde">
                        <label class="form-check-label small" for="checkSolde">
                            Marquer comme soldé
                        </label>
                    </div>

                </form>
            </div>

            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>

                <button type="submit" form="formAjouterFinance" class="btn btn-success">
                    <i class="bi bi-check-lg me-1"></i>Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ── MODAL : Joindre un document ── --}}
<div class="modal fade" id="modalAjouterDocument" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">
                    <i class="bi bi-upload me-2 text-primary"></i>Joindre un document
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
            <form id="formAjouterDocument"
            action="{{ route('documents.store', $dossier) }}"
            method="POST"
            enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold small">Fichier</label>
                <input type="file" name="fichier" class="form-control" required>
                <div class="form-text">PDF, Word, Excel, images — max 10 Mo</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Titre</label>
                <input type="text" name="titre_document" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Type de document</label>
                <select name="id_type_document" class="form-select" required>
                    <option value="">— Sélectionner —</option>
                    @foreach($typesDocuments as $type)
                        <option value="{{ $type->id }}">{{ $type->type_document }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Date de dépôt</label>
                <input type="date" name="date_depot" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Partie (optionnel)</label>
                <select name="id_partie" class="form-select">
                    <option value="">— Aucune —</option>
                    @foreach($parties as $partie)
                        <option value="{{ $partie->id }}">{{ $partie->nom_partie }}</option>
                    @endforeach
                </select>
            </div>

        </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="formAjouterDocument" class="btn btn-primary">
                    <i class="bi bi-upload me-1"></i>Joindre
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Réactiver le bon onglet après redirection (hash dans l'URL) ──────────────
(function () {
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector(`[data-bs-target="${hash}"]`);
        if (tab) new bootstrap.Tab(tab).show();
    }
})();

// ── Recherche de parties existantes (modal Ajouter une partie) ───────────────
(function () {

    const input        = document.getElementById('recherchePartie');
    const dropdown     = document.getElementById('resultatRecherche');
    const bandeauOK    = document.getElementById('partieSelectionnee');
    const nomOK        = document.getElementById('partieSelectionneeNom');
    const btnDesel     = document.getElementById('btnDeselectionner');
    const btnNouvelle  = document.getElementById('btnNouvellePartie');

    // Champs du formulaire ciblés par leur id
    const F = {
        id:           document.getElementById('hidden_partie_id'),
        identifiant:  document.getElementById('field_identifiant'),
        nom:          document.getElementById('field_nom'),
        type_personne:document.getElementById('field_type_personne'),
        telephone:    document.getElementById('field_telephone'),
        email:        document.getElementById('field_email'),
        adresse:      document.getElementById('field_adresse'),
    };

    let timer = null;

    // ── Verrouiller / déverrouiller les champs identité ──
    function lockFields(lock) {
        const textFields = ['identifiant', 'nom', 'email', 'adresse'];
        textFields.forEach(k => {
            F[k].readOnly = lock;
            F[k].classList.toggle('bg-light', lock);
            F[k].classList.toggle('text-muted', lock);
        });
        F.type_personne.disabled = lock;
        F.type_personne.classList.toggle('bg-light', lock);
    }

    // ── Remplir le formulaire depuis une partie sélectionnée ──
    function selectionner(p) {
        F.id.value           = p.id;
        F.identifiant.value  = p.identifiant_unique ?? '';
        F.nom.value          = p.nom_partie         ?? '';
        F.email.value        = p.email              ?? '';
        F.telephone.value    = p.telephone          ?? '';
        F.adresse.value      = p.adresse            ?? '';

        // Synchroniser le <select> type_personne
        Array.from(F.type_personne.options).forEach(o => {
            o.selected = (o.value === p.type_personne);
        });

        lockFields(true);
        nomOK.textContent = `${p.nom_partie} (${p.identifiant_unique})`;
        bandeauOK.classList.remove('d-none');
        fermerDropdown();
        input.value = '';
    }

    // ── Vider la sélection ──
    function deselectionner() {
        F.id.value = '';
        lockFields(false);
        bandeauOK.classList.add('d-none');
        ['identifiant', 'nom', 'email', 'telephone', 'adresse'].forEach(k => F[k].value = '');
        F.type_personne.selectedIndex = 0;
        F.type_personne.disabled = false;
        F.type_personne.classList.remove('bg-light');
    }

    function fermerDropdown() {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
    }

    // ── Échapper le HTML pour éviter les XSS dans les résultats ──
    function esc(str) {
        return (str ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));
    }

    // ── Afficher les résultats dans le dropdown ──
    function afficher(parties, query) {
        dropdown.innerHTML = '';

        if (!parties.length && query.length < 2) {
            fermerDropdown();
            return;
        }

        // Résultats trouvés
        parties.forEach(p => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'list-group-item list-group-item-action py-2 px-3';
            btn.innerHTML = `
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="fw-semibold small">${esc(p.nom_partie)}</div>
                        <div class="text-muted" style="font-size:.75rem">
                            <span class="font-monospace">${esc(p.identifiant_unique)}</span>
                            ${p.type_personne ? ' &middot; ' + esc(p.type_personne) : ''}
                            ${p.email         ? ' &middot; ' + esc(p.email)         : ''}
                        </div>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary flex-shrink-0">
                        Sélectionner
                    </span>
                </div>`;
            btn.addEventListener('click', () => selectionner(p));
            dropdown.appendChild(btn);
        });

        // Option "Créer une nouvelle partie"
        const creer = document.createElement('button');
        creer.type = 'button';
        creer.className = 'list-group-item list-group-item-action py-2 px-3 text-primary';
        creer.innerHTML = `<i class="bi bi-plus-circle me-1"></i>
            Créer une nouvelle partie <strong>« ${esc(query)} »</strong>`;
        creer.addEventListener('click', () => {
            deselectionner();
            // Pré-remplir selon si la saisie ressemble à un identifiant ou à un nom
            const ressembleId = /^[A-Za-z0-9\-]{3,}$/.test(query);
            if (ressembleId) {
                F.identifiant.value = query;
            } else {
                F.nom.value = query;
            }
            fermerDropdown();
            input.value = '';
            (ressembleId ? F.nom : F.identifiant).focus();
        });
        dropdown.appendChild(creer);

        // Aucun résultat mais query valide : message d'info avant "Créer"
        if (!parties.length) {
            const info = document.createElement('div');
            info.className = 'list-group-item py-2 px-3 text-muted small';
            info.textContent = 'Aucune partie trouvée pour cette recherche.';
            dropdown.insertBefore(info, creer);
        }

        dropdown.style.display = 'block';
    }

    // ── Appel AJAX avec debounce 280 ms ──
    input.addEventListener('input', () => {
        clearTimeout(timer);
        const q = input.value.trim();

        if (q.length < 2) {
            fermerDropdown();
            return;
        }

        timer = setTimeout(async () => {
            try {
                const res = await fetch("{{ route('dossiers.parties.search', $dossier) }}?q=" + q, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });
                if (!res.ok) throw new Error('Erreur réseau');
                const data = await res.json();
                afficher(data, q);
            } catch (err) {
                console.error('Recherche partie :', err);
            }
        }, 280);
    });

    // ── Fermer le dropdown en cliquant hors du modal ──
    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            fermerDropdown();
        }
    });

    // ── Bouton "Nouvelle" : vider tout et donner le focus ──
    btnNouvelle.addEventListener('click', () => {
        deselectionner();
        fermerDropdown();
        input.value = '';
        F.identifiant.focus();
    });

    // ── Lien "changer" dans le bandeau ──
    btnDesel.addEventListener('click', e => {
        e.preventDefault();
        deselectionner();
        input.focus();
    });

    // ── Reset complet à chaque ouverture du modal ──
    document.getElementById('modalAjouterPartie')
        .addEventListener('show.bs.modal', () => {
            deselectionner();
            fermerDropdown();
            input.value = '';
        });


    document.getElementById('checkSolde').addEventListener('change', function () {
        const paye = document.getElementById('montant_paye');
        const condamne = document.getElementById('montant_condamne');

        if (this.checked) {
            paye.value = condamne.value;
        }
    });

})();

// ── Cascade Région → Province → Degré → Tribunal (modal assigner tribunal) ──
(function () {

    const selRegion   = document.getElementById('modal_region');
    const selProvince = document.getElementById('modal_province');
    const selDegre    = document.getElementById('modal_degre');
    const selTribunal = document.getElementById('modal_tribunal');

    function reset(sel, placeholder) {
        sel.innerHTML = `<option value="">${placeholder}</option>`;
        sel.disabled  = true;
        sel.value     = '';
    }

    // Région → Provinces
    selRegion.addEventListener('change', async function () {
        reset(selProvince, '— Chargement… —');
        reset(selDegre,    '— Sélectionner d\'abord une province —');
        reset(selTribunal, '— Sélectionner d\'abord un degré —');

        if (!this.value) {
            reset(selProvince, '— Sélectionner d\'abord une région —');
            return;
        }

        try {
            const res  = await fetch(`/api/regions/${this.value}/provinces`);
            const data = await res.json();

            selProvince.innerHTML = '<option value="">— Sélectionner une province —</option>';
            data.forEach(p => {
                selProvince.innerHTML += `<option value="${p.id}">${p.province}</option>`;
            });
            selProvince.disabled = false;
        } catch (e) {
            reset(selProvince, '— Erreur de chargement —');
        }
    });

    // Province → Degrés
    selProvince.addEventListener('change', async function () {
        reset(selDegre,    '— Chargement… —');
        reset(selTribunal, '— Sélectionner d\'abord un degré —');

        if (!this.value) {
            reset(selDegre, '— Sélectionner d\'abord une province —');
            return;
        }

        try {
            const res  = await fetch(`/api/provinces/${this.value}/degres`);
            const data = await res.json();

            selDegre.innerHTML = '<option value="">— Sélectionner un degré —</option>';
            data.forEach(d => {
                selDegre.innerHTML += `<option value="${d.id}">${d.degre_juridiction}</option>`;
            });
            selDegre.disabled = false;
        } catch (e) {
            reset(selDegre, '— Erreur de chargement —');
        }
    });

    // Degré → Tribunaux
    selDegre.addEventListener('change', async function () {
        reset(selTribunal, '— Chargement… —');

        if (!this.value) {
            reset(selTribunal, '— Sélectionner d\'abord un degré —');
            return;
        }

        const provinceId = selProvince.value;

        try {
            const res  = await fetch(`/api/provinces/${provinceId}/degres/${this.value}/tribunaux`);
            const data = await res.json();

            selTribunal.innerHTML = '<option value="">— Sélectionner un tribunal —</option>';
            data.forEach(t => {
                selTribunal.innerHTML += `<option value="${t.id}">${t.nom_tribunal}</option>`;
            });
            selTribunal.disabled = data.length === 0;

            if (data.length === 0) {
                selTribunal.innerHTML = '<option value="">— Aucun tribunal disponible —</option>';
            }
        } catch (e) {
            reset(selTribunal, '— Erreur de chargement —');
        }
    });

    // Reset complet à chaque ouverture du modal
    document.getElementById('modalAjouterTribunal')
        .addEventListener('show.bs.modal', function () {
            selRegion.value = '';
            reset(selProvince, '— Sélectionner d\'abord une région —');
            reset(selDegre,    '— Sélectionner d\'abord une province —');
            reset(selTribunal, '— Sélectionner d\'abord un degré —');
        });

})();
</script>
@endpush