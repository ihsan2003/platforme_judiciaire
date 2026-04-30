{{-- resources/views/audiences/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Audience du ' . $audience->date_audience->format('d/m/Y'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('audiences.index') }}">Audiences</a></li>
    <li class="breadcrumb-item active">{{ $audience->date_audience->format('d/m/Y') }}</li>
@endsection

@section('content')

@php
    $dt      = $audience->dossierTribunal;
    $dossier = $dt?->dossier;

    $estPassee  = $audience->date_audience->isPast();
    $estAujourd = $audience->date_audience->isToday();
    $estFuture  = $audience->date_audience->isFuture();

    $badgeColor = $estAujourd ? 'danger' : ($estPassee ? 'secondary' : 'success');
    $badgeLabel = $estAujourd ? "Aujourd'hui" : ($estPassee ? 'Passée' : 'À venir');

    $isHoukm = $audience->typeAudience?->type_audience === 'الحكم';
@endphp

{{-- ══ EN-TÊTE ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">

            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                     style="width:56px;height:56px">
                    <i class="bi bi-calendar-event fs-3 text-primary"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0">
                        Audience du {{ $audience->date_audience->format('d/m/Y') }}
                    </h4>
                    <div class="mt-1 d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge bg-{{ $badgeColor }} bg-opacity-15 text-{{ $badgeColor }} border border-{{ $badgeColor }} border-opacity-25">
                            <i class="bi bi-circle-fill me-1" style="font-size:.5rem;vertical-align:middle"></i>
                            {{ $badgeLabel }}
                        </span>
                        @if($isHoukm)
                            <span class="badge bg-warning text-dark border border-warning border-opacity-25" dir="rtl">
                                جلسة الحكم
                            </span>
                        @else
                            <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-25" dir="rtl">
                                {{ $audience->typeAudience?->type_audience ?? '—' }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('audiences.edit', $audience) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
                @if(!$isHoukm || !$dt->aUnJugement())
                    <form action="{{ route('audiences.destroy', $audience) }}" method="POST"
                          onsubmit="return confirm('Supprimer cette audience ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash me-1"></i>Supprimer
                        </button>
                    </form>
                @endif
                <a href="{{ route('audiences.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>
        </div>

        <hr class="my-3">
        <div class="row g-2 small text-muted">
            <div class="col-sm-3">
                <i class="bi bi-bank me-1"></i>
                <strong>Tribunal :</strong>
                {{ $dt?->tribunal?->nom_tribunal ?? '—' }}
            </div>
            <div class="col-sm-3">
                <i class="bi bi-layers me-1"></i>
                <strong>Degré :</strong>
                {{ $dt?->degre?->degre_juridiction ?? '—' }}
            </div>
            <div class="col-sm-3">
                <i class="bi bi-person-workspace me-1"></i>
                <strong>Juge :</strong>
                {{ $audience->juge?->nom_complet ?? '—' }}
            </div>
            <div class="col-sm-3">
                <i class="bi bi-folder2-open me-1"></i>
                <strong>Dossier :</strong>
                @if($dossier)
                    <a href="{{ route('dossiers.show', $dossier) }}" class="text-decoration-none text-primary">
                        {{ $dossier->numero_dossier_interne }}
                    </a>
                @else
                    —
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">

        {{-- Détails de l'audience --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-primary"></i>Détails
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- Présences --}}
                    <div class="col-sm-6">
                        <div class="p-3 rounded border h-100">
                            <div class="small text-muted fw-semibold mb-2">Présence du demandeur</div>
                            @if($audience->presence_demandeur)
                                <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25">
                                    <i class="bi bi-check-circle me-1"></i>Présent
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25">
                                    <i class="bi bi-x-circle me-1"></i>Absent
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded border h-100">
                            <div class="small text-muted fw-semibold mb-2">Présence du défendeur</div>
                            @if($audience->presence_defendeur)
                                <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25">
                                    <i class="bi bi-check-circle me-1"></i>Présent
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25">
                                    <i class="bi bi-x-circle me-1"></i>Absent
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Prochaine audience --}}
                    <div class="col-12">
                        <div class="p-3 rounded border">
                            <div class="small text-muted fw-semibold mb-1">Prochaine audience (renvoi)</div>
                            @if($audience->date_prochaine_audience)
                                <span class="fw-semibold text-primary">
                                    <i class="bi bi-calendar-arrow-down me-1"></i>
                                    {{ $audience->date_prochaine_audience->format('d/m/Y') }}
                                </span>
                                <span class="text-muted small ms-2">
                                    (dans {{ now()->diffInDays($audience->date_prochaine_audience, false) }} jours)
                                </span>
                            @else
                                <span class="text-muted small">Aucun renvoi enregistré</span>
                            @endif
                        </div>
                    </div>

                    {{-- Résultat --}}
                    @if($audience->resultat_audience)
                    <div class="col-12">
                        <div class="p-3 rounded border">
                            <div class="small text-muted fw-semibold mb-2">Résultat de l'audience</div>
                            <div class="small" style="white-space:pre-wrap;line-height:1.7">{{ $audience->resultat_audience }}</div>
                        </div>
                    </div>
                    @endif

                    {{-- Actions demandées --}}
                    @if($audience->actions_demandees)
                    <div class="col-12">
                        <div class="p-3 rounded border">
                            <div class="small text-muted fw-semibold mb-2">Actions demandées</div>
                            <div class="small" style="white-space:pre-wrap;line-height:1.7">{{ $audience->actions_demandees }}</div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- Autres audiences de la même instance --}}
        @if($autresAudiences->isNotEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-calendar3 me-2 text-secondary"></i>
                    Autres audiences de cette instance
                    <span class="badge bg-secondary ms-1">{{ $autresAudiences->count() }}</span>
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 small text-muted fw-semibold">Date</th>
                            <th class="small text-muted fw-semibold">Type</th>
                            <th class="small text-muted fw-semibold">Résultat</th>
                            <th class="text-end pe-3 small text-muted fw-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($autresAudiences as $a)
                        <tr>
                            <td class="ps-3 small fw-semibold">
                                {{ $a->date_audience->format('d/m/Y') }}
                                @if($a->date_audience->isToday())
                                    <span class="badge bg-danger ms-1">Aujourd'hui</span>
                                @elseif($a->date_audience->isFuture())
                                    <span class="badge bg-success bg-opacity-15 text-success ms-1">À venir</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 small" dir="rtl">
                                    {{ $a->typeAudience?->type_audience ?? '—' }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ \Str::limit($a->resultat_audience ?? '—', 40) }}
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('audiences.show', $a) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

    {{-- ── Colonne latérale ── --}}
    <div class="col-lg-4">

        {{-- Règles métier actives --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-shield-check me-2 text-info"></i>Règles métier
                </h6>
            </div>
            <div class="card-body small">

                @if($isHoukm)
                    <div class="alert alert-warning py-2 mb-3">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Audience الحكم</strong> — unique par instance. Le jugement doit avoir la même date.
                    </div>
                @endif

                <dl class="row mb-0">
                    <dt class="col-7 text-muted fw-normal">Instance</dt>
                    <dd class="col-5">
                        @if($dt?->estOuverte())
                            <span class="badge bg-success bg-opacity-15 text-success">Ouverte</span>
                        @else
                            <span class="badge bg-secondary">Clôturée</span>
                        @endif
                    </dd>

                    <dt class="col-7 text-muted fw-normal">Jugement rendu</dt>
                    <dd class="col-5">
                        @if($dt?->aUnJugement())
                            <span class="badge bg-warning text-dark">Oui</span>
                        @else
                            <span class="badge bg-secondary">Non</span>
                        @endif
                    </dd>

                    <dt class="col-7 text-muted fw-normal">Audience الحكم</dt>
                    <dd class="col-5">
                        @if($dt?->audienceHoukm())
                            <span class="badge bg-warning text-dark">Présente</span>
                        @else
                            <span class="badge bg-secondary">Absente</span>
                        @endif
                    </dd>
                </dl>

            </div>
        </div>

        {{-- Navigation rapide --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 d-flex flex-column gap-2">
                @if($dossier)
                    <a href="{{ route('dossiers.show', $dossier) }}#tab-audiences"
                       class="btn btn-outline-primary w-100 btn-sm">
                        <i class="bi bi-folder2-open me-1"></i>Voir le dossier
                    </a>
                @endif
                @if($dt?->peutAvoirJugement())
                    <a href="{{ route('jugements.create', ['dossier_id' => $dossier?->id]) }}"
                       class="btn btn-outline-success w-100 btn-sm">
                        <i class="bi bi-hammer me-1"></i>Enregistrer le jugement
                    </a>
                @endif
                <a href="{{ route('audiences.create', ['dossier_id' => $dossier?->id, 'dossier_tribunal_id' => $dt?->id]) }}"
                   class="btn btn-outline-secondary w-100 btn-sm">
                    <i class="bi bi-calendar-plus me-1"></i>Nouvelle audience (même instance)
                </a>
            </div>
        </div>

    </div>
</div>

@endsection