{{-- resources/views/executions/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Exécution ' . $execution->numero_dossier_execution)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('executions.index') }}">Exécutions</a></li>
    <li class="breadcrumb-item active">{{ $execution->numero_dossier_execution }}</li>
@endsection

@section('content')

@php
    $statutLabel = $execution->statut?->statut_execution ?? '—';
    $statutColor = match(true) {
        str_contains($statutLabel, 'Terminé') => ['bg' => 'success', 'icon' => 'shield-check'],
        str_contains($statutLabel, 'cours')   => ['bg' => 'warning', 'icon' => 'hourglass-split'],
        str_contains($statutLabel, 'Suspendu')=> ['bg' => 'danger',  'icon' => 'pause-circle'],
        default                               => ['bg' => 'secondary','icon' => 'dash-circle'],
    };

    $dossier  = $execution->jugement?->dossierTribunal?->dossier;
    $tribunal = $execution->jugement?->dossierTribunal?->tribunal;
    $jugement = $execution->jugement;
    $finance  = $jugement?->finance;
@endphp

{{-- ══ EN-TÊTE ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">

            {{-- Identité --}}
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                     style="width:56px;height:56px">
                    <i class="bi bi-shield fs-3 text-primary"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 font-monospace">{{ $execution->numero_dossier_execution }}</h4>
                    @if($dossier)
                        <a href="{{ route('dossiers.show', $dossier) }}"
                           class="text-muted small text-decoration-none">
                            <i class="bi bi-folder2-open me-1"></i>
                            {{ $dossier->numero_dossier_interne }}
                        </a>
                    @endif
                    <div class="mt-1">
                        <span class="badge bg-{{ $statutColor['bg'] }} bg-opacity-15 text-{{ $statutColor['bg'] }} border border-{{ $statutColor['bg'] }} border-opacity-25">
                            <i class="bi bi-{{ $statutColor['icon'] }} me-1"></i>{{ $statutLabel }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('executions.edit', $execution) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
                <form action="{{ route('executions.destroy', $execution) }}" method="POST"
                      onsubmit="return confirm('Supprimer cette exécution ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash me-1"></i>Supprimer
                    </button>
                </form>
                <a href="{{ route('executions.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>
        </div>

        {{-- Métadonnées --}}
        <hr class="my-3">
        <div class="row g-2 small text-muted">
            <div class="col-sm-3">
                <i class="bi bi-bell me-1"></i>
                <strong>Notification :</strong>
                {{ $execution->date_notification?->format('d/m/Y') ?? '—' }}
            </div>
            <div class="col-sm-3">
                <i class="bi bi-calendar-check me-1"></i>
                <strong>Exécuté le :</strong>
                @if($execution->date_execution)
                    <span class="text-success fw-semibold">{{ $execution->date_execution->format('d/m/Y') }}</span>
                @else
                    <span class="badge bg-warning text-dark bg-opacity-20">En attente</span>
                @endif
            </div>
            <div class="col-sm-3">
                <i class="bi bi-person me-1"></i>
                <strong>Responsable :</strong>
                {{ $execution->responsable?->name ?? '—' }}
            </div>
            <div class="col-sm-3">
                <i class="bi bi-clock me-1"></i>
                <strong>Mis à jour :</strong>
                {{ $execution->updated_at->diffForHumans() }}
            </div>
        </div>
    </div>
</div>

{{-- ══ CONTENU PRINCIPAL ══ --}}
<div class="row g-4">

    {{-- ── Colonne gauche ── --}}
    <div class="col-lg-8">

        {{-- ── ÉTABLISSEMENT (Institution) ── --}}
        <div class="card border-0 shadow-sm mb-4"
             style="border-left: 4px solid #0d6efd !important;">
            <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                     style="width:32px;height:32px;flex-shrink:0">
                    <i class="bi bi-building-fill text-primary" style="font-size:.85rem"></i>
                </div>
                <h6 class="mb-0 fw-semibold">Établissement concerné</h6>
                <span class="badge bg-primary ms-auto">Institution</span>
            </div>
            <div class="card-body">
                @if($institution)
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-3 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:48px;height:48px">
                            <i class="bi bi-bank text-primary fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-6">{{ $institution->partie?->nom_partie ?? '—' }}</div>
                            <div class="text-muted small mt-1 d-flex flex-wrap gap-3">
                                @if($institution->partie?->identifiant_unique)
                                    <span>
                                        <i class="bi bi-fingerprint me-1"></i>
                                        <span class="font-monospace">{{ $institution->partie->identifiant_unique }}</span>
                                    </span>
                                @endif
                                @if($institution->typePartie)
                                    <span>
                                        <i class="bi bi-tag me-1"></i>
                                        {{ $institution->typePartie->type_partie }}
                                    </span>
                                @endif
                                @if($institution->partie?->telephone)
                                    <span>
                                        <i class="bi bi-telephone me-1"></i>
                                        {{ $institution->partie->telephone }}
                                    </span>
                                @endif
                                @if($institution->partie?->email)
                                    <span>
                                        <i class="bi bi-envelope me-1"></i>
                                        {{ $institution->partie->email }}
                                    </span>
                                @endif
                                @if($institution->avocat)
                                    <span>
                                        <i class="bi bi-briefcase me-1"></i>
                                        Me. {{ $institution->avocat->nom_avocat }}
                                    </span>
                                @endif
                            </div>
                            @if($institution->partie?->adresse)
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $institution->partie->adresse }}
                                </div>
                            @endif
                        </div>

                        {{-- Finance de l'institution (côté droit de la carte) --}}
                        @if($finance && $finance->montant_condamne)
                        <div class="border-start ps-3 ms-2 text-end flex-shrink-0">
                            @php
                                $condamne = $finance->montant_condamne ?? 0;
                                $paye     = $finance->montant_paye ?? 0;
                                $restant  = $finance->montant_restant ?? 0;
                                $pct      = $condamne > 0 ? min(100, round(($paye / $condamne) * 100)) : 0;
                            @endphp
                            <div class="text-muted small mb-1">Montant condamné</div>
                            <div class="fw-bold fs-5 text-danger">
                                {{ number_format($condamne, 2, ',', ' ') }} DH
                            </div>
                            <div class="text-muted small">
                                Payé : <span class="text-success fw-semibold">{{ number_format($paye, 2, ',', ' ') }} DH</span>
                            </div>
                            <div class="mt-2" style="width:120px;margin-left:auto">
                                <div class="progress" style="height:6px;border-radius:3px;">
                                    <div class="progress-bar bg-{{ $pct >= 100 ? 'success' : ($pct > 50 ? 'warning' : 'danger') }}"
                                         style="width:{{ $pct }}%"></div>
                                </div>
                                <div class="text-muted mt-1" style="font-size:.72rem">{{ $pct }}% réglé</div>
                            </div>
                        </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-3 text-muted small">
                        <i class="bi bi-building fs-2 d-block mb-2 opacity-25"></i>
                        Aucune partie marquée comme institution dans ce dossier.
                        @if($dossier)
                            <div class="mt-2">
                                <a href="{{ route('dossiers.show', $dossier) }}#tab-parties"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil me-1"></i>Corriger dans le dossier
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Parties adverses ── --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-people me-2 text-secondary"></i>Parties adverses
                </h6>
                <span class="badge bg-secondary ms-auto">{{ $autresParties->count() }}</span>
            </div>

            @if($autresParties->isEmpty())
                <div class="card-body text-center py-4 text-muted small">
                    <i class="bi bi-people fs-2 d-block mb-2 opacity-25"></i>
                    Aucune autre partie.
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 small text-muted fw-semibold">Nom</th>
                            <th class="small text-muted fw-semibold">Identifiant</th>
                            <th class="small text-muted fw-semibold">Rôle</th>
                            <th class="small text-muted fw-semibold">Type</th>
                            <th class="small text-muted fw-semibold">Avocat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($autresParties as $dp)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold small">{{ $dp->partie?->nom_partie ?? '—' }}</div>
                                @if($dp->partie?->email)
                                    <div class="text-muted" style="font-size:.75rem">{{ $dp->partie->email }}</div>
                                @endif
                            </td>
                            <td class="text-muted small font-monospace">
                                {{ $dp->partie?->identifiant_unique ?? '—' }}
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                    {{ $dp->typePartie?->type_partie ?? '—' }}
                                </span>
                            </td>
                            <td>
                                @php $tp = $dp->partie?->type_personne; @endphp
                                <span class="badge bg-{{ $tp === 'Morale' ? 'warning' : 'success' }} bg-opacity-15 text-{{ $tp === 'Morale' ? 'warning' : 'success' }}">
                                    <i class="bi bi-{{ $tp === 'Morale' ? 'building' : 'person' }} me-1"></i>
                                    {{ $tp ?? '—' }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                @if($dp->avocat)
                                    <i class="bi bi-briefcase me-1"></i>{{ $dp->avocat->nom_avocat }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- ── Jugement lié ── --}}
        @if($jugement)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-hammer me-2 text-primary"></i>Jugement lié
                </h6>
                <a href="{{ route('jugements.show', $jugement) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye me-1"></i>Voir
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3 small">
                    <div class="col-sm-4">
                        <div class="text-muted mb-1">Date du jugement</div>
                        <div class="fw-semibold">{{ $jugement->date_jugement?->format('d/m/Y') ?? '—' }}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted mb-1">Juge</div>
                        <div class="fw-semibold">{{ $jugement->juge?->nom_complet ?? '—' }}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted mb-1">Tribunal</div>
                        <div class="fw-semibold">{{ $tribunal?->nom_tribunal ?? '—' }}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted mb-1">Caractère</div>
                        @if($jugement->est_definitif)
                            <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25">
                                <i class="bi bi-check-circle me-1"></i>Définitif
                            </span>
                        @else
                            <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-25">
                                Non définitif
                            </span>
                        @endif
                    </div>
                    @if($dossier)
                    <div class="col-sm-8">
                        <div class="text-muted mb-1">Dossier</div>
                        <a href="{{ route('dossiers.show', $dossier) }}"
                           class="text-decoration-none fw-semibold text-primary small">
                            <i class="bi bi-folder2-open me-1"></i>{{ $dossier->numero_dossier_interne }}
                        </a>
                        <span class="text-muted ms-2 small">— {{ $dossier->typeAffaire?->affaire ?? '—' }}</span>
                    </div>
                    @endif
                </div>

                @if($jugement->contenu_dispositif)
                <div class="border-top pt-3 mt-3">
                    <div class="text-muted small mb-2 fw-semibold">Dispositif</div>
                    <div class="p-3 bg-light rounded border small"
                         style="white-space:pre-wrap; line-height:1.7; max-height:140px; overflow-y:auto;">
                        {{ $jugement->contenu_dispositif }}
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>

    {{-- ── Colonne droite ── --}}
    <div class="col-lg-4">

        {{-- Infos exécution --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-primary"></i>Informations
                </h6>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-6 text-muted fw-normal">N° exécution</dt>
                    <dd class="col-6 fw-semibold font-monospace">{{ $execution->numero_dossier_execution }}</dd>

                    <dt class="col-6 text-muted fw-normal">Statut</dt>
                    <dd class="col-6">
                        <span class="badge bg-{{ $statutColor['bg'] }} bg-opacity-15 text-{{ $statutColor['bg'] }}">
                            {{ $statutLabel }}
                        </span>
                    </dd>

                    <dt class="col-6 text-muted fw-normal">Responsable</dt>
                    <dd class="col-6">{{ $execution->responsable?->name ?? '—' }}</dd>

                    <dt class="col-6 text-muted fw-normal">Notification</dt>
                    <dd class="col-6">{{ $execution->date_notification?->format('d/m/Y') ?? '—' }}</dd>

                    <dt class="col-6 text-muted fw-normal">Exécution</dt>
                    <dd class="col-6">
                        @if($execution->date_execution)
                            <span class="text-success fw-semibold">
                                {{ $execution->date_execution->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="badge bg-warning text-dark bg-opacity-20">En attente</span>
                        @endif
                    </dd>

                    <dt class="col-6 text-muted fw-normal">Créé le</dt>
                    <dd class="col-6">{{ $execution->created_at->format('d/m/Y') }}</dd>
                </dl>
            </div>
        </div>

        {{-- Finance --}}
        @if($finance)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-cash-stack me-2 text-success"></i>Finance
                </h6>
            </div>
            <div class="card-body small">
                @php
                    $condamne = $finance->montant_condamne ?? 0;
                    $paye     = $finance->montant_paye ?? 0;
                    $pct      = $condamne > 0 ? min(100, round(($paye / $condamne) * 100)) : 0;
                    $restant  = $finance->montant_restant ?? 0;
                @endphp

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Progression</span>
                        <span class="fw-semibold {{ $pct >= 100 ? 'text-success' : 'text-warning' }}">{{ $pct }}%</span>
                    </div>
                    <div class="progress" style="height:8px;border-radius:4px;">
                        <div class="progress-bar bg-{{ $pct >= 100 ? 'success' : ($pct > 50 ? 'warning' : 'danger') }}"
                             style="width:{{ $pct }}%"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Condamné</span>
                    <strong>{{ number_format($condamne, 2, ',', ' ') }} DH</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Payé</span>
                    <strong class="text-success">{{ number_format($paye, 2, ',', ' ') }} DH</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Restant dû</span>
                    <strong class="{{ $restant > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($restant, 2, ',', ' ') }} DH
                    </strong>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Statut</span>
                    @php
                        $sp = $finance->statut_paiement ?? '—';
                        $sc = match($sp) { 'Complet' => 'success', 'Partiel' => 'warning', default => 'secondary' };
                    @endphp
                    <span class="badge bg-{{ $sc }}">{{ $sp }}</span>
                </div>

                @if($finance->date_paiement)
                <div class="border-top pt-2 mt-1 text-muted" style="font-size:.8rem">
                    <i class="bi bi-calendar-check me-1"></i>
                    Paiement le {{ $finance->date_paiement->format('d/m/Y') }}
                </div>
                @endif
            </div>
        </div>
        @else
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center py-4 text-muted small">
                <i class="bi bi-cash-coin fs-2 d-block mb-2 opacity-25"></i>
                Aucune donnée financière.
            </div>
        </div>
        @endif

        {{-- Navigation rapide --}}
        @if($dossier)
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 d-flex flex-column gap-2">
                <a href="{{ route('dossiers.show', $dossier) }}"
                   class="btn btn-outline-primary w-100 btn-sm">
                    <i class="bi bi-folder2-open me-1"></i>Voir le dossier
                </a>
                @if($jugement)
                <a href="{{ route('jugements.show', $jugement) }}"
                   class="btn btn-outline-secondary w-100 btn-sm">
                    <i class="bi bi-hammer me-1"></i>Voir le jugement
                </a>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

@endsection