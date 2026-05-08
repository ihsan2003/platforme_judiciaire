{{-- resources/views/finances/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Finance — Jugement du ' . ($finance->jugement?->date_jugement?->format('d/m/Y') ?? '—'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('finances.index') }}">Finances</a></li>
    <li class="breadcrumb-item active">Détail finance</li>
@endsection

@section('content')

@php
    $jugement = $finance->jugement;
    $dt       = $jugement?->dossierTribunal;
    $dossier  = $dt?->dossier;

    $condamne = $finance->montant_condamne ?? 0;
    $paye     = $finance->montant_paye ?? 0;
    $restant  = $finance->montant_restant ?? 0;
    $pct      = $condamne > 0 ? min(100, round(($paye / $condamne) * 100)) : 0;
    $pctColor = $pct >= 100 ? 'success' : ($pct > 0 ? 'warning' : 'danger');

    $sp       = $finance->statut_paiement ?? '—';
    $spColor  = match($sp) { 'Complet' => 'success', 'Partiel' => 'warning', default => 'secondary' };
@endphp

{{-- ══ EN-TÊTE ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">

            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success bg-opacity-10 d-flex align-items-center justify-content-center"
                     style="width:56px;height:56px">
                    <i class="bi bi-cash-stack fs-3 text-success"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1">Finance — Jugement du {{ $jugement?->date_jugement?->format('d/m/Y') ?? '—' }}</h4>
                    @if($dossier)
                        <a href="{{ route('dossiers.show', $dossier) }}"
                           class="text-muted small text-decoration-none">
                            <i class="bi bi-folder2-open me-1"></i>{{ $dossier->numero_dossier_interne }}
                        </a>
                    @endif
                    <div class="mt-1">
                        <span class="badge bg-{{ $spColor }}">{{ $sp }}</span>
                        @if($jugement?->est_definitif)
                            <span class="badge bg-success bg-opacity-15 text-white border border-success border-opacity-25 ms-1">
                                <i class="bi bi-check-circle me-1"></i>Jugement définitif
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('finances.edit', $finance) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
                <x-modal-delete
                                :action="route('finances.destroy', $finance)"
                                modal-id="deleteFinance{{ $finance->id }}"
                                title="Supprimer l'entrée financière"
                                :description="'Finance du ' . $finance->created_at->format('d/m/Y')"
                            />
                <a href="{{ route('finances.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>
        </div>

        <hr class="my-3">
        <div class="row g-2 small text-muted">
            <div class="col-sm-3">
                <i class="bi bi-bank me-1"></i>
                <strong>Tribunal :</strong> {{ $dt?->tribunal?->nom_tribunal ?? '—' }}
            </div>
            <div class="col-sm-3">
                <i class="bi bi-layers me-1"></i>
                <strong>Degré :</strong> {{ $dt?->degre?->degre_juridiction ?? '—' }}
            </div>
            <div class="col-sm-3">
                <i class="bi bi-person-workspace me-1"></i>
                <strong>Juge :</strong> {{ $jugement?->juge?->nom_complet ?? '—' }}
            </div>
            <div class="col-sm-3">
                <i class="bi bi-clock me-1"></i>
                <strong>Mis à jour :</strong> {{ $finance->updated_at->diffForHumans() }}
            </div>
        </div>
    </div>
</div>

{{-- ══ CONTENU ══ --}}
<div class="row g-4">

    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">

        {{-- Montants --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-bar-chart me-2 text-primary"></i>Détails financiers
                </h6>
            </div>
            <div class="card-body">

                {{-- Barre de progression globale --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold small">Taux de recouvrement</span>
                        <span class="fw-bold text-{{ $pctColor }}">{{ $pct }}%</span>
                    </div>
                    <div style="height:12px;background:#e2e8f0;border-radius:6px;overflow:hidden;">
                        <div style="width:{{ $pct }}%;height:100%;border-radius:6px;transition:width .5s;
                                    background:{{ $pct >= 100 ? '#16a34a' : ($pct > 50 ? '#d97706' : '#ef4444') }}">
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    {{-- Condamné --}}
                    <div class="col-sm-4">
                        <div class="p-3 rounded border text-center h-100">
                            <div class="text-muted small fw-semibold mb-1">Montant condamné</div>
                            <div class="fw-bold fs-5">{{ number_format($condamne, 2, ',', ' ') }}</div>
                            <div class="text-muted small">DH</div>
                        </div>
                    </div>
                    {{-- Payé --}}
                    <div class="col-sm-4">
                        <div class="p-3 rounded border text-center h-100" style="border-color:#a7f3d0!important;background:#f0fdf4">
                            <div class="text-success small fw-semibold mb-1">Montant payé</div>
                            <div class="fw-bold fs-5 text-success">{{ number_format($paye, 2, ',', ' ') }}</div>
                            <div class="text-muted small">DH</div>
                        </div>
                    </div>
                    {{-- Restant --}}
                    <div class="col-sm-4">
                        <div class="p-3 rounded border text-center h-100"
                             style="{{ $restant > 0 ? 'border-color:#fca5a5!important;background:#fff5f5' : 'border-color:#a7f3d0!important;background:#f0fdf4' }}">
                            <div class="{{ $restant > 0 ? 'text-danger' : 'text-success' }} small fw-semibold mb-1">Restant dû</div>
                            <div class="fw-bold fs-5 {{ $restant > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($restant, 2, ',', ' ') }}
                            </div>
                            <div class="text-muted small">DH</div>
                        </div>
                    </div>

                    {{-- Montants réclamés (si renseignés) --}}
                    @if($finance->montant_reclame_demandeur || $finance->montant_reclame_defendeur)
                    <div class="col-sm-6">
                        <div class="p-3 rounded border h-100">
                            <div class="text-muted small fw-semibold mb-1">Réclamé par le demandeur</div>
                            <div class="fw-semibold">
                                {{ $finance->montant_reclame_demandeur ? number_format($finance->montant_reclame_demandeur, 2, ',', ' ').' DH' : '—' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded border h-100">
                            <div class="text-muted small fw-semibold mb-1">Réclamé par le défendeur</div>
                            <div class="fw-semibold">
                                {{ $finance->montant_reclame_defendeur ? number_format($finance->montant_reclame_defendeur, 2, ',', ' ').' DH' : '—' }}
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Date de paiement --}}
                    @if($finance->date_paiement)
                    <div class="col-12">
                        <div class="p-3 rounded border">
                            <div class="text-muted small fw-semibold mb-1">Date du paiement</div>
                            <span class="fw-semibold text-success">
                                <i class="bi bi-calendar-check me-1"></i>
                                {{ $finance->date_paiement->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Jugement lié --}}
        @if($jugement)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-hammer me-2 text-primary"></i>Jugement associé
                </h6>
                <a href="{{ route('jugements.show', $jugement) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye me-1"></i>Voir
                </a>
            </div>
            <div class="card-body small">
                <div class="row g-3">
                    <div class="col-sm-4">
                        <div class="text-muted mb-1">Date</div>
                        <div class="fw-semibold">{{ $jugement->date_jugement?->format('d/m/Y') ?? '—' }}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted mb-1">Juge</div>
                        <div class="fw-semibold">{{ $jugement->juge?->nom_complet ?? '—' }}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted mb-1">Caractère</div>
                        @if($jugement->est_definitif)
                            <span class="badge bg-success bg-opacity-15 text-white border border-success border-opacity-25">
                                <i class="bi bi-check-circle me-1"></i>Définitif
                            </span>
                        @else
                            <span class="badge bg-warning text-dark">Non définitif</span>
                        @endif
                    </div>
                    @if($jugement->contenu_dispositif)
                    <div class="col-12">
                        <div class="text-muted mb-1">Dispositif</div>
                        <div class="p-2 bg-light rounded border small"
                             style="white-space:pre-wrap;max-height:80px;overflow:hidden;line-height:1.6">
                            {{ Str::limit($jugement->contenu_dispositif, 200) }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- ── Colonne latérale ── --}}
    <div class="col-lg-4">

        {{-- Résumé --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-muted"></i>Résumé
                </h6>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-6 text-muted fw-normal">Statut paiement</dt>
                    <dd class="col-6">
                        <span class="badge bg-{{ $spColor }}">{{ $sp }}</span>
                    </dd>

                    <dt class="col-6 text-muted fw-normal">Date paiement</dt>
                    <dd class="col-6">{{ $finance->date_paiement?->format('d/m/Y') ?? '—' }}</dd>

                    <dt class="col-6 text-muted fw-normal">Créé le</dt>
                    <dd class="col-6">{{ $finance->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">Modifié le</dt>
                    <dd class="col-6">{{ $finance->updated_at->format('d/m/Y') }}</dd>
                </dl>
            </div>
        </div>

        {{-- Navigation rapide --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 d-flex flex-column gap-2">
                @if($dossier)
                <a href="{{ route('dossiers.show', $dossier) }}#tab-finances"
                   class="btn btn-outline-primary w-100 btn-sm">
                    <i class="bi bi-folder2-open me-1"></i>Voir le dossier
                </a>
                @endif
                @if($jugement)
                <a href="{{ route('jugements.show', $jugement) }}"
                   class="btn btn-outline-secondary w-100 btn-sm">
                    <i class="bi bi-hammer me-1"></i>Voir le jugement
                </a>
                @endif
                <a href="{{ route('finances.edit', $finance) }}"
                   class="btn btn-outline-warning w-100 btn-sm">
                    <i class="bi bi-pencil me-1"></i>Modifier cette finance
                </a>
            </div>
        </div>

    </div>
</div>

@endsection