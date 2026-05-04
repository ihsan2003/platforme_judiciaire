@extends('layouts.app')

@section('title', 'Dossier ' . $dossier->numero_dossier_interne)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dossiers.index') }}">Dossiers</a></li>
    <li class="breadcrumb-item active">{{ $dossier->numero_dossier_interne }}</li>
@endsection

@push('styles')
<style>
/* ── Palette dossier ─────────────────────────────── */
:root {
    --deg1 : #1a6b3a;  --deg1-light : #e8f5ee;  --deg1-muted : #a7d9b8;
    --deg2 : #1a3a6b;  --deg2-light : #e8eef5;  --deg2-muted : #a7bfd9;
    --deg3 : #6b1a1a;  --deg3-light : #f5e8e8;  --deg3-muted : #d9a7a7;
    --houkm : #7c3aed;
    --jug   : #0f766e;
    --rec   : #c2410c;
    --exec  : #0369a1;
    --tl-w  : 2px;
}

/* ── En-tête dossier ─────────────────────────────── */
.dossier-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
    border-radius: 16px;
    padding: 28px 32px;
    color: #fff;
    margin-bottom: 24px;
}
.dossier-header-kpi {
    display: flex; gap: 28px; flex-wrap: wrap; align-items: flex-end;
}
.kpi-item { text-align: center; }
.kpi-val  { font-size: 1.5rem; font-weight: 800; color: #c8a84b; line-height: 1; }
.kpi-lab  { font-size: .68rem; opacity: .7; text-transform: uppercase; letter-spacing: .06em; }

/* ── Onglets ─────────────────────────────────────── */
.dossier-tabs .nav-link {
    font-weight: 600; font-size: .85rem;
    color: #64748b; border: none;
    padding: .6rem 1.1rem;
    border-bottom: 3px solid transparent;
    border-radius: 0;
    transition: all .15s;
}
.dossier-tabs .nav-link.active {
    color: #1a3a6b; border-bottom-color: #1a3a6b; background: none;
}
.dossier-tabs .nav-link:hover:not(.active) {
    color: #1a3a6b; border-bottom-color: #e2e8f0; background: none;
}

/* ── Cartes de degré ─────────────────────────────── */
.deg-card { border-radius: 14px; overflow: hidden; border: 2px solid transparent; margin-bottom: 20px; }
.deg-card.deg-1 { border-color: var(--deg1); }
.deg-card.deg-2 { border-color: var(--deg2); }
.deg-card.deg-3 { border-color: var(--deg3); }
.deg-card.deg-closed { opacity: .82; }

.deg-header { padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
.deg-header.deg-1 { background: var(--deg1); color: #fff; }
.deg-header.deg-2 { background: var(--deg2); color: #fff; }
.deg-header.deg-3 { background: var(--deg3); color: #fff; }

.deg-num {
    width: 32px; height: 32px; border-radius: 50%;
    background: rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: .85rem; flex-shrink: 0;
}
.deg-title { font-weight: 700; font-size: 1rem; }
.deg-sub   { font-size: .78rem; opacity: .8; }

.deg-body  { background: #fff; }

/* ── Timeline audiences ──────────────────────────── */
.aud-timeline { padding: 20px 20px 8px 52px; position: relative; }
.aud-timeline::before {
    content: '';
    position: absolute; left: 28px; top: 24px; bottom: 12px;
    width: var(--tl-w); background: #e2e8f0; border-radius: 2px;
}

.aud-item { position: relative; margin-bottom: 12px; }
.aud-dot {
    position: absolute; left: -38px; top: 10px;
    width: 20px; height: 20px; border-radius: 50%;
    border: 2.5px solid #fff; box-shadow: 0 0 0 2px #e2e8f0;
    background: #b45309;
    display: flex; align-items: center; justify-content: center;
    font-size: .55rem; color: #fff; flex-shrink: 0;
}
.aud-dot.houkm  { background: var(--houkm); box-shadow: 0 0 0 3px rgba(124,58,237,.3); width: 24px; height: 24px; left: -40px; top: 8px; }
.aud-dot.future { background: #3b82f6; }

.aud-card {
    border: 1px solid #e2e8f0; border-radius: 10px;
    padding: 10px 14px; background: #fff;
    transition: border-color .15s, box-shadow .15s;
}
.aud-card:hover { border-color: #94a3b8; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
.aud-card.houkm { border-color: rgba(124,58,237,.4); background: #fdf4ff; }
.aud-card.future{ border-color: rgba(59,130,246,.35); background: #eff6ff; }

.aud-card-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px; margin-bottom: 4px; }
.aud-date { font-weight: 700; font-size: .88rem; }
.aud-type-badge {
    font-size: .7rem; font-weight: 700; padding: 2px 8px; border-radius: 10px;
}
.aud-type-badge.normal { background: #fef3c7; color: #92400e; }
.aud-type-badge.houkm  { background: #f3e8ff; color: #6b21a8; }
.aud-type-badge.future { background: #dbeafe; color: #1d4ed8; }

.aud-meta { font-size: .78rem; color: #64748b; display: flex; flex-wrap: wrap; gap: 10px; }
.aud-renvoi { font-size: .74rem; color: #64748b; margin-top: 4px; padding-top: 4px; border-top: 1px dashed #e2e8f0; }

/* ── Jugement bloc ───────────────────────────────── */
.jug-block {
    margin: 0 20px 20px;
    border: 2px solid var(--jug); border-radius: 12px;
    background: #f0fdfa; padding: 16px 18px;
}
.jug-block-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 10px; }
.jug-title { font-weight: 800; font-size: .95rem; color: var(--jug); display: flex; align-items: center; gap: 6px; }
.jug-meta  { font-size: .8rem; color: #475569; display: flex; flex-wrap: wrap; gap: 14px; margin-bottom: 8px; }
.jug-dispositif {
    font-size: .82rem; color: #334155; background: #fff;
    border: 1px solid #ccfbf1; border-left: 3px solid var(--jug);
    padding: 8px 12px; border-radius: 6px; max-height: 70px; overflow: hidden;
    position: relative; line-height: 1.6;
}
.jug-dispositif.open { max-height: none; }

/* Finance mini ─────────────────────────────────── */
.fin-bar { height: 6px; border-radius: 3px; background: #e2e8f0; overflow: hidden; margin-top: 4px; }
.fin-bar-fill { height: 100%; border-radius: 3px; transition: width .5s; }

/* Recours ──────────────────────────────────────── */
.rec-block {
    margin: 8px 20px 16px;
    border: 2px dashed var(--rec); border-radius: 10px;
    background: #fff7ed; padding: 12px 16px;
}

/* Exécution ─────────────────────────────────────  */
.exec-block {
    margin: 8px 20px 20px;
    border: 2px solid var(--exec); border-radius: 10px;
    background: #f0f9ff; padding: 12px 16px;
}

/* Connecteur entre degrés ──────────────────────── */
.deg-connector {
    display: flex; flex-direction: column; align-items: center;
    padding: 4px 0; margin-bottom: 0; position: relative; z-index: 1;
}
.deg-connector-line { width: 2px; height: 32px; background: #cbd5e1; }
.deg-connector-tag {
    background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 20px;
    padding: 3px 12px; font-size: .7rem; font-weight: 600; color: #64748b;
    margin: 4px 0;
}

/* Aucun contenu ────────────────────────────────── */
.empty-state {
    padding: 20px; border-radius: 10px; background: #f8fafc;
    border: 1px dashed #cbd5e1; text-align: center;
    color: #94a3b8; font-size: .85rem; margin: 12px 20px 20px;
}

/* Formulaire recours inline ─────────────────────  */
.recours-form-wrap {
    margin: 0 20px 20px;
    border: 1px solid #e2e8f0; border-radius: 10px;
    background: #fffbeb; padding: 14px;
}

/* Alerte RG ─────────────────────────────────────  */
.rg-alert { padding: 8px 12px; border-radius: 8px; font-size: .79rem; margin: 8px 20px; display: flex; gap: 8px; }
.rg-alert.warn { background: #fef9c3; border-left: 3px solid #eab308; color: #713f12; }
.rg-alert.info { background: #e0f2fe; border-left: 3px solid #0284c7; color: #075985; }

/* Badges génériques ─────────────────────────────  */
.pill { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: .72rem; font-weight: 600; }
.pill-white   { background: rgba(255,255,255,.18); color: #fff; border: 1px solid rgba(255,255,255,.3); }
.pill-success { background: #dcfce7; color: #166534; }
.pill-warning { background: #fef3c7; color: #92400e; }
.pill-danger  { background: #fee2e2; color: #991b1b; }
.pill-muted   { background: #f1f5f9; color: #64748b; }
.pill-info    { background: #e0f2fe; color: #075985; }
.pill-purple  { background: #f3e8ff; color: #6b21a8; }

/* Barre de progression dossier ─────────────────── */
.progress-steps {
    display: flex; gap: 4px; background: #f1f5f9;
    border-radius: 10px; padding: 4px; flex-wrap: wrap; margin-bottom: 24px;
}
.progress-step {
    flex: 1; min-width: 110px; padding: 8px 14px;
    border-radius: 7px; display: flex; align-items: center; gap: 8px;
    font-size: .76rem; font-weight: 600; color: #94a3b8;
}
.progress-step.s-deg1 { background: var(--deg1); color: #fff; }
.progress-step.s-deg2 { background: var(--deg2); color: #fff; }
.progress-step.s-deg3 { background: var(--deg3); color: #fff; }
.progress-step-num {
    width: 22px; height: 22px; border-radius: 50%;
    background: rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: .73rem; font-weight: 800; flex-shrink: 0;
}

/* Finances & Exécutions sections ────────────────── */
.section-card { border: none; box-shadow: 0 2px 8px rgba(0,0,0,.06); border-radius: 12px; }
</style>
@endpush

@section('content')

@php
    /* ── Instances triées chronologiquement ─────────── */
    $instances = $dossier->dossierTribunaux->sortBy('date_debut');

    /* ── Couleur/classe Bootstrap selon l'ordre du degré ─ */
    $degColor = function($dt) {
        return match($dt->degre?->ordre ?? 0) { 1 => 'deg-1', 2 => 'deg-2', 3 => 'deg-3', default => 'deg-1' };
    };

    /* ── Statistiques globales ───────────────────────── */
    $totalAudiences  = $instances->flatMap->audiences->count();
    $totalJugements  = $instances->flatMap->jugements->count();
    $totalExecutions = $instances->flatMap->jugements->flatMap->executions->count();
    $totalFinances   = $instances->flatMap->jugements->pluck('finance')->filter()->sum('montant_condamne');
    $totalPaye       = $instances->flatMap->jugements->pluck('finance')->filter()->sum('montant_paye');

    $manquants = $dossier->typesPartiesManquants();
    $peutAudience = $dossier->peutAvoirAudience();
@endphp

{{-- ══════════════════════════════════════════════
     EN-TÊTE DOSSIER
══════════════════════════════════════════════ --}}
<div class="dossier-header mb-4">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center"
                 style="width:52px;height:52px;background:rgba(255,255,255,.12);flex-shrink:0">
                <i class="bi bi-folder2-open fs-3 text-warning"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-white">{{ $dossier->numero_dossier_interne }}</h4>
                @if($dossier->numero_dossier_tribunal)
                    <div class="small" style="opacity:.7">
                        <i class="bi bi-bank me-1"></i>N° tribunal : {{ $dossier->numero_dossier_tribunal }}
                    </div>
                @endif
                <div class="mt-1 d-flex flex-wrap gap-2">
                    @php
                        $statut = $dossier->statutDossier->statut_dossier ?? '—';
                        $sc = match(true) {
                            str_contains($statut, 'Actif')    => 'success',
                            str_contains($statut, 'Clôturé')  => 'secondary',
                            str_contains($statut, 'Suspendu') => 'warning',
                            default => 'primary',
                        };
                    @endphp
                    <span class="pill pill-white">
                        <i class="bi bi-circle-fill" style="font-size:.4rem"></i>
                        {{ $statut }}
                    </span>
                    <span class="pill pill-white">
                        <i class="bi bi-tag"></i>
                        {{ $dossier->typeAffaire->affaire ?? '—' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- KPIs ─────────────────────────────────── --}}
        <div class="dossier-header-kpi">
            <div class="kpi-item">
                <div class="kpi-val">{{ $dossierParties->count() }}</div>
                <div class="kpi-lab">Parties</div>
            </div>
            <div class="kpi-item">
                <div class="kpi-val">{{ $instances->count() }}</div>
                <div class="kpi-lab">Instance(s)</div>
            </div>
            <div class="kpi-item">
                <div class="kpi-val">{{ $totalAudiences }}</div>
                <div class="kpi-lab">Audiences</div>
            </div>
            <div class="kpi-item">
                <div class="kpi-val">{{ $totalJugements }}</div>
                <div class="kpi-lab">Jugements</div>
            </div>
            <div class="kpi-item">
                <div class="kpi-val">{{ number_format($totalFinances, 0, ',', ' ') }}<small style="font-size:.5em;font-weight:600;opacity:.8"> DH</small></div>
                <div class="kpi-lab">Montant condamné</div>
            </div>
        </div>

        {{-- Actions ─────────────────────────────── --}}
        <div class="d-flex flex-wrap gap-2 align-items-start">
            @can('update', $dossier)
                <a href="{{ route('dossiers.edit', $dossier) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
            @endcan
            @can('delete', $dossier)
                <form action="{{ route('dossiers.destroy', $dossier) }}" method="POST"
                      onsubmit="return confirm('Archiver ce dossier ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-light btn-sm">
                        <i class="bi bi-archive me-1"></i>Archiver
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <hr class="mt-3 mb-2" style="border-color:rgba(255,255,255,.15)">
    <div class="row g-2 small" style="opacity:.75">
        <div class="col-sm-3 text-white">
            <i class="bi bi-calendar-event me-1"></i>
            <strong>Ouverture :</strong> {{ $dossier->date_ouverture?->format('d/m/Y') ?? '—' }}
        </div>
        <div class="col-sm-3 text-white">
            <i class="bi bi-calendar-check me-1"></i>
            <strong>Clôture :</strong> {{ $dossier->date_cloture?->format('d/m/Y') ?? 'En cours' }}
        </div>
        <div class="col-sm-3 text-white">
            <i class="bi bi-person me-1"></i>
            <strong>Créé par :</strong> {{ $dossier->createdBy->name ?? '—' }}
        </div>
        <div class="col-sm-3 text-white">
            <i class="bi bi-clock me-1"></i>
            <strong>Mis à jour :</strong> {{ $dossier->updated_at->diffForHumans() }}
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     ONGLETS
══════════════════════════════════════════════ --}}
<ul class="nav dossier-tabs border-bottom mb-0" id="dossierTabs">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-parties">
            <i class="bi bi-people me-1"></i>Parties
            <span class="badge bg-primary ms-1 rounded-pill" style="font-size:.65rem">{{ $dossierParties->count() }}</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-instances">
            <i class="bi bi-diagram-3 me-1"></i>Instances & Audiences
            <span class="badge bg-success ms-1 rounded-pill" style="font-size:.65rem">{{ $instances->count() }}</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-jugements">
            <i class="bi bi-hammer me-1"></i>Jugements
            <span class="badge bg-dark ms-1 rounded-pill" style="font-size:.65rem">{{ $totalJugements }}</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-finances">
            <i class="bi bi-cash-stack me-1"></i>Finances
            <span class="badge bg-success ms-1 rounded-pill" style="font-size:.65rem">{{ $instances->flatMap->jugements->pluck('finance')->filter()->count() }}</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-executions">
            <i class="bi bi-shield-check me-1"></i>Exécutions
            <span class="badge bg-danger ms-1 rounded-pill" style="font-size:.65rem">{{ $totalExecutions }}</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-documents">
            <i class="bi bi-paperclip me-1"></i>Documents
            <span class="badge bg-warning text-dark ms-1 rounded-pill" style="font-size:.65rem">{{ $dossier->documents->count() }}</span>
        </button>
    </li>
</ul>

<div class="tab-content border border-top-0 rounded-bottom bg-white shadow-sm p-4" id="dossierTabContent">

    {{-- ══════════════════════════════════════════
         ONGLET 1 : PARTIES
    ══════════════════════════════════════════ --}}
    <div class="tab-pane fade show active" id="tab-parties">

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-semibold mb-0"><i class="bi bi-people me-2 text-primary"></i>Parties impliquées</h6>
            @can('update', $dossier)
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterPartie">
                    <i class="bi bi-person-plus me-1"></i>Ajouter une partie
                </button>
            @endcan
        </div>

        @if(!$peutAudience && count($manquants) > 0)
        <div class="alert alert-warning border-0 small mb-3">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Rôle(s) manquant(s) :</strong>
            @foreach($manquants as $m)
                <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 mx-1" dir="rtl">{{ $m }}</span>
            @endforeach
            — Impossible de créer des audiences tant que ces rôles ne sont pas présents.
        </div>
        @endif

        @if($dossierParties->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>
                Aucune partie enregistrée.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="small text-muted fw-semibold">Identifiant</th>
                        <th class="small text-muted fw-semibold">Nom / Dénomination</th>
                        <th class="small text-muted fw-semibold">Type</th>
                        <th class="small text-muted fw-semibold">Rôle</th>
                        <th class="small text-muted fw-semibold">Avocat</th>
                        <th class="small text-muted fw-semibold">Date d'entrée</th>
                        <th class="small text-muted fw-semibold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dossierParties as $dp)
                    <tr>
                        <td class="text-muted small font-monospace">{{ $dp->partie->identifiant_unique ?? '—' }}</td>
                        <td>
                            <div class="fw-semibold">{{ $dp->partie->nom_partie ?? '—' }}</div>
                            @if($dp->partie?->email)<div class="text-muted small">{{ $dp->partie->email }}</div>@endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $dp->partie->type_personne === 'Morale' ? 'warning' : 'success' }} bg-opacity-15 text-{{ $dp->partie->type_personne === 'Morale' ? 'warning' : 'success' }}">
                                <i class="bi bi-{{ $dp->partie->type_personne === 'Morale' ? 'building' : 'person' }} me-1"></i>
                                {{ $dp->partie->type_personne ?? '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary">{{ $dp->typePartie->type_partie ?? '—' }}</span>
                        </td>
                        <td class="text-muted small">
                            @if($dp->avocat)<i class="bi bi-briefcase me-1"></i>{{ $dp->avocat->nom_avocat }}@else —@endif
                        </td>
                        <td class="text-muted small">{{ $dp->date_entree?->format('d/m/Y') ?? '—' }}</td>
                        <td class="text-end">
                            @can('update', $dossier)
                            <div class="d-flex gap-1 justify-content-end">
                                <button class="btn btn-sm btn-outline-warning"
                                        data-bs-toggle="modal" data-bs-target="#modalEditPartie{{ $dp->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('dossiers.parties.destroy', [$dossier, $dp]) }}" method="POST"
                                      onsubmit="return confirm('Retirer cette partie ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-person-dash"></i></button>
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


    {{-- ══════════════════════════════════════════
         ONGLET 2 : INSTANCES & AUDIENCES
         (organisation hiérarchique par degré)
    ══════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-instances">

        {{-- Barre de progression ──────────────── --}}
        @if($instances->isNotEmpty())
        <div class="progress-steps mb-4">
            @foreach($instances->sortBy(fn($dt) => $dt->degre?->ordre) as $idx => $dt)
            @php
                $ord = $dt->degre?->ordre ?? 0;
                $cls = match($ord) { 1 => 's-deg1', 2 => 's-deg2', 3 => 's-deg3', default => '' };
            @endphp
            <div class="progress-step {{ $cls }}">
                <div class="progress-step-num">{{ $idx + 1 }}</div>
                <div>
                    <div style="font-size:.75rem;font-weight:700;line-height:1.2">{{ $dt->degre?->degre_juridiction ?? '—' }}</div>
                    <div style="font-size:.65rem;opacity:.8">
                        {{ is_null($dt->date_fin) ? 'En cours' : 'Clôturée' }}
                    </div>
                </div>
            </div>
            @if(!$loop->last)
                <div style="font-size:.85rem;color:#94a3b8;align-self:center;padding:0 2px">›</div>
            @endif
            @endforeach
        </div>
        @endif

        {{-- Bouton assigner tribunal ─────────── --}}
        <div class="d-flex justify-content-end mb-3">
            @can('update', $dossier)
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterTribunal">
                    <i class="bi bi-plus-lg me-1"></i>Assigner un tribunal
                </button>
            @endcan
        </div>

        @if($instances->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-bank fs-1 d-block mb-2 opacity-25"></i>
                Aucun tribunal assigné à ce dossier.
                <div class="mt-2 small">Assignez un tribunal pour commencer à enregistrer des audiences.</div>
            </div>
        @else

        @foreach($instances->sortBy('date_debut') as $loopIdx => $dt)
        @php
            $ord          = $dt->degre?->ordre ?? 0;
            $colorCls     = $degColor($dt);
            $isClosed     = !is_null($dt->date_fin);
            $audiences    = $dt->audiences->sortBy('date_audience');
            $audienceHoukm= $dt->audienceHoukm();
            $jugement     = $dt->jugements->sortByDesc('date_jugement')->first();
            $recours      = $jugement?->recours?->first();
        @endphp

        {{-- Connecteur entre instances ────────── --}}
        @if($loopIdx > 0)
        @php $prevDt = $instances->values()[$loopIdx - 1]; @endphp
        <div class="deg-connector">
            <div class="deg-connector-line"></div>
            <div class="deg-connector-tag">
                @if($prevRecours = $prevDt->jugements->first()?->recours?->first())
                    <i class="bi bi-arrow-repeat me-1"></i>
                    {{ $prevRecours->typeRecours->type_recours ?? 'Transition' }}
                    — {{ $prevRecours->date_recours->format('d/m/Y') }}
                @else
                    <i class="bi bi-arrow-down me-1"></i> Transition de degré
                @endif
            </div>
            <div class="deg-connector-line"></div>
        </div>
        @endif

        {{-- Carte de l'instance ─────────────── --}}
        <div class="deg-card {{ $colorCls }} {{ $isClosed ? 'deg-closed' : '' }} shadow-sm">

            {{-- En-tête ─────────────────────── --}}
            <div class="deg-header {{ $colorCls }}">
                <div class="d-flex align-items-center gap-3">
                    <div class="deg-num">{{ $loopIdx + 1 }}</div>
                    <div>
                        <div class="deg-title">{{ $dt->degre?->degre_juridiction ?? '—' }}</div>
                        <div class="deg-sub">
                            <i class="bi bi-bank me-1"></i>{{ $dt->tribunal?->nom_tribunal ?? '—' }}
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    {{-- Statut instance --}}
                    @if($isClosed)
                        <span class="pill pill-white"><i class="bi bi-lock-fill"></i> Clôturée</span>
                    @elseif($jugement)
                        <span class="pill pill-white"><i class="bi bi-hammer"></i> Jugement rendu</span>
                    @elseif($audienceHoukm)
                        <span class="pill pill-white"><i class="bi bi-hourglass-split"></i> En délibéré</span>
                    @else
                        <span class="pill pill-white"><i class="bi bi-activity"></i> En cours</span>
                    @endif

                    <span class="pill pill-white" style="font-size:.68rem">
                        <i class="bi bi-calendar3"></i> {{ $dt->date_debut?->format('d/m/Y') }} → {{ $dt->date_fin?->format('d/m/Y') ?? 'Présent' }}
                    </span>
                    <span class="pill pill-white" style="font-size:.68rem">
                        <i class="bi bi-calendar-event"></i> {{ $audiences->count() }} audience(s)
                    </span>

                    {{-- Actions instance --}}
                    @can('update', $dossier)
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);font-size:.75rem"
                                data-bs-toggle="modal" data-bs-target="#modalEditTribunal{{ $dt->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        @if(!$isClosed && $peutAudience)
                        <a href="{{ route('audiences.create', ['dossier_id' => $dossier->id, 'dossier_tribunal_id' => $dt->id]) }}"
                           class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);font-size:.75rem">
                            <i class="bi bi-calendar-plus me-1"></i>Audience
                        </a>
                        @endif
                    </div>
                    @endcan
                </div>
            </div>

            {{-- RG alerts ────────────────────── --}}
            @if($audiences->isEmpty())
                <div class="rg-alert info">
                    <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                    <span>Aucune audience dans cette instance pour le moment.</span>
                </div>
            @elseif(!$audienceHoukm && $audiences->isNotEmpty())
                <div class="rg-alert warn">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
                    <span>L'audience <strong>الحكم</strong> n'a pas encore été enregistrée — le jugement ne peut pas être saisi.</span>
                </div>
            @elseif($audienceHoukm && !$jugement)
                <div class="rg-alert warn">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
                    <span>Audience <strong>الحكم</strong> du <strong>{{ $audienceHoukm->date_audience->format('d/m/Y') }}</strong> enregistrée — vous pouvez saisir le jugement.</span>
                </div>
            @endif

            {{-- ── TIMELINE AUDIENCES ───────── --}}
            @if($audiences->isEmpty())
            <div class="empty-state">
                <i class="bi bi-calendar-x d-block mb-1 fs-4 opacity-30"></i>
                Aucune audience dans cette instance.
                @if(!$isClosed && $peutAudience)
                <div class="mt-2">
                    <a href="{{ route('audiences.create', ['dossier_id' => $dossier->id, 'dossier_tribunal_id' => $dt->id]) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-calendar-plus me-1"></i>Planifier
                    </a>
                </div>
                @endif
            </div>
            @else
            <div class="aud-timeline">
                @foreach($audiences as $aud)
                @php
                    $isHoukm = $aud->typeAudience?->type_audience === 'الحكم';
                    $isFuture = $aud->date_audience?->isFuture();
                    $typeLabel = $aud->typeAudience?->type_audience ?? '—';
                    $dotCls  = $isHoukm ? 'houkm' : ($isFuture ? 'future' : '');
                    $cardCls = $isHoukm ? 'houkm' : ($isFuture ? 'future' : '');
                    $badgeCls= $isHoukm ? 'houkm' : ($isFuture ? 'future' : 'normal');
                @endphp
                <div class="aud-item">
                    <div class="aud-dot {{ $dotCls }}">
                        <i class="bi {{ $isHoukm ? 'bi-gavel' : 'bi-calendar-event' }}"></i>
                    </div>
                    <div class="aud-card {{ $cardCls }}">
                        <div class="aud-card-head">
                            <div class="d-flex align-items-center gap-2">
                                <span class="aud-date">{{ $aud->date_audience?->format('d/m/Y') ?? '—' }}</span>
                                @if($aud->date_audience?->isToday())
                                    <span class="pill pill-danger" style="font-size:.65rem;padding:1px 6px">Aujourd'hui</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="aud-type-badge {{ $badgeCls }}" dir="rtl">
                                    {{ $typeLabel }}
                                    @if($isHoukm) <strong class="ms-1">← Finale</strong>@endif
                                </span>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('audiences.show', $aud) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:.7rem"><i class="bi bi-eye"></i></a>
                                    @can('update', $dossier)
                                    <a href="{{ route('audiences.edit', $aud) }}" class="btn btn-sm btn-outline-warning py-0 px-2" style="font-size:.7rem"><i class="bi bi-pencil"></i></a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        <div class="aud-meta">
                            @if($aud->juge)<span><i class="bi bi-person me-1"></i>{{ $aud->juge->nom_complet }}</span>@endif
                            <span>
                                <i class="bi bi-people me-1"></i>
                                Dem. : {{ $aud->presence_demandeur ? '✓' : '✗' }}
                                · Déf. : {{ $aud->presence_defendeur ? '✓' : '✗' }}
                            </span>
                            @if($aud->resultat_audience)
                                <span><i class="bi bi-chat-left-text me-1"></i>{{ Str::limit($aud->resultat_audience, 55) }}</span>
                            @endif
                        </div>
                        @if($aud->date_prochaine_audience && !$isHoukm)
                        <div class="aud-renvoi">
                            <i class="bi bi-calendar-arrow-down text-muted"></i>
                            Renvoi au <strong>{{ $aud->date_prochaine_audience->format('d/m/Y') }}</strong>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach

                {{-- Bouton ajout rapide si pas encore de الحكم --}}
                @if(!$audienceHoukm && !$isClosed && $peutAudience)
                <div class="aud-item" style="margin-top:6px">
                    <div class="aud-dot" style="background:#e2e8f0;border:2px dashed #94a3b8">
                        <i class="bi bi-plus" style="color:#94a3b8"></i>
                    </div>
                    <div style="padding:6px 0">
                        <a href="{{ route('audiences.create', ['dossier_id' => $dossier->id, 'dossier_tribunal_id' => $dt->id]) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-calendar-plus me-1"></i>Planifier une audience
                        </a>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- ── SÉPARATEUR الحكم → Jugement ─ --}}
            @if($audienceHoukm)
            <div class="mx-4 my-1 d-flex align-items-center gap-2" style="font-size:.75rem;color:#6b21a8;font-weight:600">
                <div style="flex:1;height:1px;background:rgba(124,58,237,.2)"></div>
                <span><i class="bi bi-arrow-down me-1"></i>Audience الحكم du {{ $audienceHoukm->date_audience->format('d/m/Y') }} → Jugement</span>
                <div style="flex:1;height:1px;background:rgba(124,58,237,.2)"></div>
            </div>
            @endif

            {{-- ── JUGEMENT ─────────────────── --}}
            @if($jugement)
            <div class="jug-block">
                <div class="jug-block-header">
                    <div class="jug-title">
                        <i class="bi bi-hammer"></i>
                        Jugement du {{ $jugement->date_jugement->format('d/m/Y') }}
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @if($jugement->est_definitif)
                            <span class="pill pill-success"><i class="bi bi-check-circle-fill"></i> Définitif</span>
                        @else
                            @php $dr = $jugement->delai_recours_restant; @endphp
                            @if($dr !== null)
                                @if($dr > 10)
                                    <span class="pill pill-success" style="font-size:.68rem"><i class="bi bi-clock"></i> {{ $dr }}j restants</span>
                                @elseif($dr > 0)
                                    <span class="pill pill-warning" style="font-size:.68rem"><i class="bi bi-exclamation-triangle-fill"></i> {{ $dr }}j !</span>
                                @else
                                    <span class="pill pill-muted" style="font-size:.68rem">Délai expiré</span>
                                @endif
                            @endif
                        @endif
                        <a href="{{ route('jugements.show', $jugement) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:.75rem">
                            <i class="bi bi-eye me-1"></i>Voir
                        </a>
                    </div>
                </div>
                <div class="jug-meta">
                    @if($jugement->juge)<span><i class="bi bi-person-workspace me-1"></i>{{ $jugement->juge->nom_complet }}</span>@endif
                    @if($jugement->finance)
                    @php $f = $jugement->finance; $pct = $f->montant_condamne > 0 ? min(100, round(($f->montant_paye / $f->montant_condamne) * 100)) : 0; @endphp
                    <span><i class="bi bi-cash me-1"></i>{{ number_format($f->montant_condamne, 2) }} DH</span>
                    <span><i class="bi bi-check2-circle me-1 text-success"></i>{{ number_format($f->montant_paye, 2) }} DH payés</span>
                    @endif
                </div>
                @if($jugement->contenu_dispositif)
                <div class="jug-dispositif" id="disp-{{ $jugement->id }}">
                    {{ $jugement->contenu_dispositif }}
                    <div style="position:absolute;bottom:0;left:0;right:0;height:25px;background:linear-gradient(transparent,#f0fdfa)" id="disp-fade-{{ $jugement->id }}"></div>
                </div>
                <button class="btn btn-link btn-sm p-0 mt-1" style="font-size:.73rem;color:var(--jug)"
                        onclick="document.getElementById('disp-{{ $jugement->id }}').classList.toggle('open');document.getElementById('disp-fade-{{ $jugement->id }}').style.display=document.getElementById('disp-{{ $jugement->id }}').classList.contains('open')?'none':''">
                    <i class="bi bi-chevron-down me-1"></i>Voir le dispositif complet
                </button>
                @endif
                @if($jugement->finance)
                @php $f = $jugement->finance; $pct = $f->montant_condamne > 0 ? min(100, round(($f->montant_paye / $f->montant_condamne) * 100)) : 0; $pctCol = $pct >= 100 ? '#16a34a' : ($pct > 0 ? '#d97706' : '#ef4444'); @endphp
                <div class="fin-bar mt-2"><div class="fin-bar-fill" style="width:{{ $pct }}%;background:{{ $pctCol }}"></div></div>
                @endif
            </div>

                {{-- Recours ─────────────────── --}}
                @if($recours)
                <div class="rec-block">
                    <div style="font-weight:700;font-size:.88rem;color:var(--rec);display:flex;align-items:center;gap:6px;margin-bottom:6px">
                        <i class="bi bi-arrow-repeat"></i>
                        {{ $recours->typeRecours->type_recours ?? 'Recours' }}
                        — {{ $recours->date_recours->format('d/m/Y') }}
                        @if(($recours->est_dans_delais ?? false))
                            <span class="pill pill-success" style="font-size:.65rem">✓ Dans les délais</span>
                        @else
                            <span class="pill pill-danger" style="font-size:.65rem">⚠ Hors délai</span>
                        @endif
                    </div>
                    @if($recours->motifs)
                    <div style="font-size:.8rem;color:#7c2d12">{{ Str::limit($recours->motifs, 100) }}</div>
                    @endif
                </div>

                {{-- Exécutions ───────────────── --}}
                @elseif($jugement->est_definitif)
                    @foreach($jugement->executions as $exec)
                    @php $sl = $exec->statut?->statut_execution ?? '—'; $sc = str_contains($sl,'Terminé') ? '#16a34a' : (str_contains($sl,'cours') ? '#d97706' : '#64748b'); @endphp
                    <div class="exec-block">
                        <div style="font-weight:700;font-size:.88rem;color:var(--exec);display:flex;align-items:center;gap:6px;margin-bottom:4px">
                            <i class="bi bi-shield-check"></i> {{ $exec->numero_dossier_execution }}
                            <a href="{{ route('executions.show', $exec) }}" class="btn btn-sm btn-outline-primary py-0 px-2 ms-auto" style="font-size:.7rem"><i class="bi bi-eye"></i></a>
                        </div>
                        <div style="font-size:.8rem;color:#0c4a6e;display:flex;flex-wrap:wrap;gap:12px">
                            <span><i class="bi bi-bell me-1"></i>Notifié le {{ $exec->date_notification?->format('d/m/Y') ?? '—' }}</span>
                            <span class="pill" style="font-size:.65rem;background:#e0f2fe;color:{{ $sc }}">{{ $sl }}</span>
                            @if($exec->date_execution)<span class="pill pill-success" style="font-size:.65rem">Exécuté le {{ $exec->date_execution->format('d/m/Y') }}</span>@endif
                        </div>
                    </div>
                    @endforeach
                    @if($jugement->executions->isEmpty())
                    <div class="empty-state">
                        <i class="bi bi-hourglass-split d-block mb-1 fs-4 opacity-30"></i>
                        Jugement définitif — en attente d'exécution.
                        <div class="mt-2">
                            <a href="{{ route('executions.create', ['jugement_id' => $jugement->id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-plus-lg me-1"></i>Lancer l'exécution
                            </a>
                        </div>
                    </div>
                    @endif

                {{-- Recours possible ────────── --}}
                @elseif($jugement->peutFaireObjetRecours() && $jugement->recours->isEmpty())
                <div class="recours-form-wrap">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-arrow-repeat text-warning"></i>
                        <strong class="small">Déposer un recours</strong>
                        @php $drr = $jugement->delai_recours_restant; @endphp
                        @if($drr !== null && $drr <= 5)
                            <span class="pill pill-danger" style="font-size:.65rem">Urgent — {{ $drr }}j</span>
                        @endif
                    </div>
                    <form action="{{ route('jugements.recours.store', $jugement) }}" method="POST">
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <select name="id_type_recours" class="form-select form-select-sm" required>
                                    <option value="">— Type de recours —</option>
                                    @foreach(\App\Models\TypeRecours::orderBy('type_recours')->get() as $tr)
                                        <option value="{{ $tr->id }}">{{ $tr->type_recours }} ({{ $tr->delai_legal_jours }}j)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="date_recours" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="motifs" class="form-control form-control-sm" placeholder="Motifs (optionnel)">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-warning btn-sm w-100" onclick="return confirm('Déposer le recours ?')">
                                    <i class="bi bi-send me-1"></i>Déposer
                                </button>
                            </div>
                        </div>
                    </form>
                    <form action="{{ route('jugements.cloture-sans-recours', $jugement) }}" method="POST" class="mt-2" onsubmit="return confirm('Clôturer sans recours ?')">
                        @csrf
                        <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle me-1"></i>Clôturer sans recours</button>
                    </form>
                </div>
                @elseif(!$jugement->est_definitif && !$jugement->peutFaireObjetRecours() && $jugement->recours->isEmpty())
                <div class="mx-4 mb-4">
                    <form action="{{ route('jugements.cloture-sans-recours', $jugement) }}" method="POST" onsubmit="return confirm('Clôturer sans recours ?')">
                        @csrf
                        <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-lock me-1"></i>Délai expiré — Clôturer sans recours</button>
                    </form>
                </div>
                @endif

            {{-- Pas encore de jugement --}}
            @elseif($audienceHoukm && !$isClosed)
            <div class="empty-state">
                <i class="bi bi-hammer d-block mb-1 fs-4 opacity-30"></i>
                L'audience الحكم a eu lieu — veuillez saisir le jugement.
                <div class="mt-2">
                    <a href="{{ route('jugements.create', ['dossier_id' => $dossier->id]) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Saisir le jugement
                    </a>
                </div>
            </div>
            @elseif(!$isClosed && $audiences->isNotEmpty())
            <div class="empty-state" style="margin:0 20px 20px">
                <i class="bi bi-hourglass d-block mb-1 fs-4 opacity-30"></i>
                En attente de l'audience finale <strong dir="rtl">الحكم</strong>.
            </div>
            @endif

        </div>{{-- /.deg-card --}}
        @endforeach

        @endif{{-- /instances non vides --}}
    </div>{{-- /tab-instances --}}


    {{-- ══════════════════════════════════════════
         ONGLET 3 : JUGEMENTS (vue synthétique)
    ══════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-jugements">
        @php $jugements = $instances->flatMap->jugements->sortByDesc('date_jugement'); @endphp

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-semibold mb-0"><i class="bi bi-hammer me-2 text-primary"></i>Jugements</h6>
            @php $peutJugement = $instances->contains(fn($dt) => $dt->peutAvoirJugement()); @endphp
            @if($peutJugement && $peutAudience)
                <a href="{{ route('jugements.create', ['dossier_id' => $dossier->id]) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Enregistrer un jugement
                </a>
            @endif
        </div>

        @if($jugements->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-hammer fs-1 d-block mb-2 opacity-25"></i>
                Aucun jugement enregistré.
            </div>
        @else
        @foreach($jugements as $jug)
        @php
            $dtJ = $jug->dossierTribunal;
            $peutR = $jug->peutFaireObjetRecours();
            $dr = $jug->delai_recours_restant;
            $bordCol = $jug->est_definitif ? 'var(--jug)' : ($peutR ? '#1a3a6b' : '#94a3b8');
        @endphp
        <div class="card border mb-3" style="border-width:2px!important;border-color:{{ $bordCol }}!important;">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <i class="bi bi-hammer me-1 text-primary"></i>
                    <strong>Jugement du {{ $jug->date_jugement->format('d/m/Y') }}</strong>
                    <span class="text-muted small ms-2">
                        — {{ $dtJ->tribunal->nom_tribunal ?? '—' }}
                        <span class="badge bg-{{ match($dtJ->degre?->ordre??0){1=>'success',2=>'primary',3=>'danger',default=>'secondary'} }} ms-1" style="font-size:.65rem">
                            {{ $dtJ->degre?->degre_juridiction ?? '—' }}
                        </span>
                    </span>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    @if($jug->est_definitif)
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Définitif</span>
                    @elseif($peutR)
                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>{{ $dr }}j restants</span>
                    @elseif($jug->recours->isNotEmpty())
                        <span class="badge bg-warning text-dark"><i class="bi bi-arrow-repeat me-1"></i>Recours</span>
                    @else
                        <span class="badge bg-secondary">Délai expiré</span>
                    @endif
                    <a href="{{ route('jugements.show', $jug) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                    <a href="{{ route('jugements.edit', $jug) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                </div>
            </div>
            <div class="card-body py-2 small">
                <div class="row g-2 mb-1">
                    <div class="col-auto text-muted"><i class="bi bi-person me-1"></i>{{ $jug->juge->nom_complet ?? '—' }}</div>
                    @if($jug->finance)
                    <div class="col-auto text-muted">
                        <i class="bi bi-cash me-1"></i>
                        Condamné : <strong>{{ number_format($jug->finance->montant_condamne, 2) }} DH</strong>
                        — Payé : <strong class="text-success">{{ number_format($jug->finance->montant_paye, 2) }} DH</strong>
                    </div>
                    @endif
                </div>
                @if($jug->recours->isNotEmpty())
                    @foreach($jug->recours as $r)
                    <div class="p-2 rounded mb-1" style="background:#fff3cd;border-left:3px solid #ffc107">
                        <i class="bi bi-arrow-repeat text-warning me-1"></i>
                        <strong>{{ $r->typeRecours->type_recours ?? '—' }}</strong> — {{ $r->date_recours->format('d/m/Y') }}
                        @if($r->motifs)<em class="text-muted"> — {{ Str::limit($r->motifs, 70) }}</em>@endif
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
        @endforeach
        @endif
    </div>{{-- /tab-jugements --}}


    {{-- ══════════════════════════════════════════
         ONGLET 4 : FINANCES
    ══════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-finances">
        @php
            $finances = $instances->flatMap->jugements->pluck('finance')->filter();
            $jugSansFinance = $instances->flatMap->jugements->filter(fn($j) => !$j->finance);
        @endphp

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-semibold mb-0"><i class="bi bi-cash-stack me-2 text-success"></i>Finances</h6>
            @if($jugSansFinance->isNotEmpty())
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterFinance">
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
                        <th class="small text-muted fw-semibold">Jugement</th>
                        <th class="small text-muted fw-semibold">Degré</th>
                        <th class="small text-muted fw-semibold">Condamné</th>
                        <th class="small text-muted fw-semibold">Payé</th>
                        <th class="small text-muted fw-semibold">Restant</th>
                        <th class="small text-muted fw-semibold">Progression</th>
                        <th class="small text-muted fw-semibold">Statut</th>
                        <th class="small text-muted fw-semibold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($finances as $fin)
                    @php
                        $jFin = $fin->jugement;
                        $dtFin = $jFin?->dossierTribunal;
                        $pct = $fin->montant_condamne > 0 ? min(100, round(($fin->montant_paye / $fin->montant_condamne) * 100)) : 0;
                        $pctCol = $pct >= 100 ? 'success' : ($pct > 0 ? 'warning' : 'danger');
                    @endphp
                    <tr>
                        <td class="fw-semibold small">{{ $jFin?->date_jugement?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ match($dtFin?->degre?->ordre??0){1=>'success',2=>'primary',3=>'danger',default=>'secondary'} }}" style="font-size:.65rem">
                                {{ $dtFin?->degre?->degre_juridiction ?? '—' }}
                            </span>
                        </td>
                        <td class="fw-semibold">{{ number_format($fin->montant_condamne, 2) }} DH</td>
                        <td class="text-success fw-semibold">{{ number_format($fin->montant_paye, 2) }} DH</td>
                        <td class="{{ $fin->montant_restant > 0 ? 'text-danger' : 'text-success' }} fw-semibold">{{ number_format($fin->montant_restant, 2) }} DH</td>
                        <td style="min-width:100px">
                            <div class="fin-bar"><div class="fin-bar-fill bg-{{ $pctCol }}" style="width:{{ $pct }}%"></div></div>
                            <div style="font-size:.68rem;color:#64748b;margin-top:2px">{{ $pct }}%</div>
                        </td>
                        <td>
                            @php $sp = $fin->statut_paiement ?? '—'; @endphp
                            <span class="badge bg-{{ match($sp){ 'Complet' => 'success', 'Partiel' => 'warning', default => 'secondary' } }}">{{ $sp }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('finances.show', $fin) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('finances.edit', $fin) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light fw-semibold small">
                    <tr>
                        <td colspan="2">Total</td>
                        <td>{{ number_format($finances->sum('montant_condamne'), 2) }} DH</td>
                        <td class="text-success">{{ number_format($finances->sum('montant_paye'), 2) }} DH</td>
                        <td class="{{ $finances->sum('montant_condamne') - $finances->sum('montant_paye') > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($finances->sum('montant_condamne') - $finances->sum('montant_paye'), 2) }} DH
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>{{-- /tab-finances --}}


    {{-- ══════════════════════════════════════════
         ONGLET 5 : EXÉCUTIONS
    ══════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-executions">
        @php
            $tousJug = $instances->flatMap->jugements->sortByDesc('date_jugement');
            $toutesExec = $tousJug->flatMap->executions;
            $jugDefSansExec = $tousJug->first(fn($j) => $j->est_definitif && $j->executions->isEmpty());
        @endphp

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-semibold mb-0"><i class="bi bi-shield-check me-2 text-danger"></i>Exécutions</h6>
            @if($jugDefSansExec)
                <a href="{{ route('executions.create', ['jugement_id' => $jugDefSansExec->id]) }}" class="btn btn-danger btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Lancer une exécution
                </a>
            @endif
        </div>

        @if($toutesExec->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-shield-x fs-1 d-block mb-2 opacity-25"></i>
                Aucune exécution enregistrée.
                @if($jugDefSansExec)<div class="mt-2 small">Un jugement définitif est disponible — vous pouvez lancer l'exécution.</div>@endif
            </div>
        @else
        @foreach($tousJug as $jug)
            @if($jug->executions->isEmpty()) @continue @endif
            <div class="card border mb-3" style="border-left:3px solid var(--exec)!important">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="small">
                        <i class="bi bi-hammer text-primary me-1"></i>
                        <strong>Jugement du {{ $jug->date_jugement->format('d/m/Y') }}</strong>
                        <span class="text-muted ms-2">— {{ $jug->dossierTribunal->tribunal->nom_tribunal ?? '—' }}</span>
                        <span class="badge bg-{{ match($jug->dossierTribunal?->degre?->ordre??0){1=>'success',2=>'primary',3=>'danger',default=>'secondary'} }} ms-1" style="font-size:.63rem">
                            {{ $jug->dossierTribunal?->degre?->degre_juridiction ?? '—' }}
                        </span>
                    </div>
                    <a href="{{ route('jugements.show', $jug) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>Jugement</a>
                </div>
                <div class="card-body p-0">
                    @foreach($jug->executions as $exec)
                    @php $sl = $exec->statut?->statut_execution ?? '—'; $sc = str_contains($sl,'Terminé') ? 'success' : (str_contains($sl,'cours') ? 'warning' : 'secondary'); @endphp
                    <div class="p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <div class="fw-semibold font-monospace">{{ $exec->numero_dossier_execution }}</div>
                                <div class="small text-muted mt-1 d-flex flex-wrap gap-3">
                                    <span><i class="bi bi-bell me-1"></i>Notifié le <strong>{{ $exec->date_notification?->format('d/m/Y') ?? '—' }}</strong></span>
                                    <span><i class="bi bi-person me-1"></i>{{ $exec->responsable?->name ?? '—' }}</span>
                                    @if($exec->date_execution)
                                        <span class="text-success"><i class="bi bi-calendar-check me-1"></i>Exécuté le {{ $exec->date_execution->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="badge bg-{{ $sc }} bg-opacity-15 text-{{ $sc }} border border-{{ $sc }} border-opacity-25">{{ $sl }}</span>
                                <a href="{{ route('executions.show', $exec) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('executions.edit', $exec) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endforeach
        @endif
    </div>{{-- /tab-executions --}}


    {{-- ══════════════════════════════════════════
         ONGLET 6 : DOCUMENTS
    ══════════════════════════════════════════ --}}
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
            @php
                $ext  = strtolower(pathinfo($doc->fichier_path ?? '', PATHINFO_EXTENSION));
                $icon = match($ext) {
                    'pdf' => 'bi-file-earmark-pdf text-danger',
                    'doc','docx' => 'bi-file-earmark-word text-primary',
                    'xls','xlsx' => 'bi-file-earmark-excel text-success',
                    'jpg','jpeg','png','gif' => 'bi-file-earmark-image text-warning',
                    default => 'bi-file-earmark text-secondary',
                };
            @endphp
            <div class="col-md-4 col-lg-3">
                <div class="card border h-100">
                    <div class="card-body d-flex flex-column align-items-center text-center py-4">
                        <i class="bi {{ $icon }} fs-1 mb-2"></i>
                        <div class="small fw-semibold text-truncate w-100" title="{{ $doc->titre_document }}">
                            {{ $doc->titre_document ?? 'Document' }}
                        </div>
                        @if($doc->typeDocument)
                            <span class="badge bg-light text-secondary border small mt-1">{{ $doc->typeDocument->type_document }}</span>
                        @endif
                        <div class="text-muted" style="font-size:.7rem">{{ $doc->date_depot?->format('d/m/Y') ?? '—' }}</div>
                    </div>
                    <div class="card-footer bg-white border-top d-flex gap-1 justify-content-center py-2">
                        <a href="{{ route('documents.download', [$dossier, $doc]) }}" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="bi bi-download"></i>
                        </a>
                        @can('update', $dossier)
                        <form action="{{ route('documents.destroy', [$dossier, $doc]) }}" method="POST" onsubmit="return confirm('Supprimer ce document ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>{{-- /tab-documents --}}

</div>{{-- /tab-content --}}


{{-- ══════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════ --}}

{{-- Modal : Ajouter une partie ──────────────────────── --}}
<div class="modal fade" id="modalAjouterPartie" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-semibold"><i class="bi bi-person-plus me-2 text-primary"></i>Ajouter une partie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Recherche AJAX --}}
                <div class="mb-3 p-3 rounded-3 border bg-light">
                    <label class="form-label fw-semibold small text-muted text-uppercase mb-2" style="letter-spacing:.05em">
                        <i class="bi bi-search me-1"></i>Rechercher une partie existante
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="recherchePartie" class="form-control" placeholder="Identifiant (CIN / RC) ou nom…" autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" id="btnNouvellePartie">
                            <i class="bi bi-plus-lg me-1"></i>Nouvelle
                        </button>
                    </div>
                    <div id="resultatRecherche" class="list-group mt-1 shadow-sm" style="display:none;max-height:220px;overflow-y:auto;position:relative;z-index:1060"></div>
                    <div id="partieSelectionnee" class="alert alert-success py-2 px-3 mt-2 d-none small mb-0">
                        <i class="bi bi-check-circle me-1"></i>Partie sélectionnée : <strong id="partieSelectionneeNom"></strong>
                        <a href="#" id="btnDeselectionner" class="ms-2 text-danger small">(changer)</a>
                    </div>
                </div>

                <form id="formAjouterPartie" action="{{ route('dossiers.parties.store', $dossier) }}" method="POST">
                @csrf
                <input type="hidden" name="partie_id" id="hidden_partie_id">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Identifiant unique <span class="text-danger">*</span></label>
                        <input type="text" name="identifiant_unique" id="field_identifiant" class="form-control" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Nom / Dénomination <span class="text-danger">*</span></label>
                        <input type="text" name="nom_partie" id="field_nom" class="form-control" required>
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
                        <input type="tel" name="telephone" id="field_telephone" class="form-control" pattern="^(\+212|00212|0)(5|6|7)[0-9]{8}$">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold small">Email</label>
                        <input type="email" name="email" id="field_email" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Adresse</label>
                        <textarea name="adresse" id="field_adresse" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Avocat</label>
                        <div id="bloc_avocat_nouveau">
                            <select name="id_avocat" id="field_avocat_nouveau_select" class="form-select">
                                <option value="">— Aucun avocat —</option>
                                @foreach($avocats as $av)
                                    <option value="{{ $av->id }}">{{ $av->nom_avocat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="bloc_avocat_existant" class="d-none">
                            <div class="input-group">
                                <input type="text" id="field_avocat_display" class="form-control bg-light text-muted" readonly>
                                <button type="button" class="btn btn-outline-secondary" id="btnModifierAvocat"><i class="bi bi-pencil me-1"></i>Modifier</button>
                            </div>
                            <div id="bloc_avocat_modif" class="d-none mt-2">
                                <select name="id_avocat" id="field_avocat_modif_select" class="form-select" disabled>
                                    <option value="">— Aucun avocat —</option>
                                    @foreach($avocats as $av)
                                        <option value="{{ $av->id }}">{{ $av->nom_avocat }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Rôle dans le dossier <span class="text-danger">*</span></label>
                        <select name="id_type_partie" class="form-select" required>
                            <option value="">— Sélectionner —</option>
                            @foreach($typesPartie as $tp)
                                <option value="{{ $tp->id }}">{{ $tp->type_partie }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Date d'entrée <span class="text-danger">*</span></label>
                        <input type="date" name="date_entree" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                </form>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="formAjouterPartie" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Ajouter au dossier
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modals modifier chaque partie ─────────────────── --}}
@foreach($dossierParties as $dp)
<div class="modal fade" id="modalEditPartie{{ $dp->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold"><i class="bi bi-pencil me-2 text-warning"></i>Modifier : {{ $dp->partie->nom_partie ?? '—' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditPartie{{ $dp->id }}" action="{{ route('dossiers.parties.update', [$dossier, $dp]) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Rôle dans le dossier <span class="text-danger">*</span></label>
                        <select name="id_type_partie" class="form-select" required>
                            @foreach($typesPartie as $tp)
                                <option value="{{ $tp->id }}" @selected($dp->id_type_partie == $tp->id)>{{ $tp->type_partie }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Date d'entrée</label>
                        <input type="date" name="date_entree" class="form-control" value="{{ $dp->date_entree?->format('Y-m-d') }}">
                    </div>
                </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="formEditPartie{{ $dp->id }}" class="btn btn-warning">
                    <i class="bi bi-check-lg me-1"></i>Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach

{{-- Modal : Assigner un tribunal (cascade région > province > degré > tribunal) --}}
<div class="modal fade" id="modalAjouterTribunal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold"><i class="bi bi-bank me-2 text-primary"></i>Assigner un tribunal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAjouterTribunal" action="{{ route('dossiers.tribunaux.store', $dossier) }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Région <span class="text-danger">*</span></label>
                        <select id="modal_region" class="form-select">
                            <option value="">— Sélectionner —</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}">{{ $region->region }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Province <span class="text-danger">*</span></label>
                        <select id="modal_province" class="form-select" disabled>
                            <option value="">— Sélectionner d'abord une région —</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Degré <span class="text-danger">*</span></label>
                        <select id="modal_degre" name="id_degre" class="form-select" disabled required>
                            <option value="">— Sélectionner d'abord une province —</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Tribunal <span class="text-danger">*</span></label>
                        <select id="modal_tribunal" name="id_tribunal" class="form-select" disabled required>
                            <option value="">— Sélectionner d'abord un degré —</option>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Date de saisine <span class="text-danger">*</span></label>
                        <input type="date" name="date_debut" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Date de fin</label>
                        <input type="date" name="date_fin" class="form-control">
                    </div>
                </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="formAjouterTribunal" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Assigner
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modals modifier chaque tribunal ──────────────── --}}
@foreach($dossier->dossierTribunaux as $dt)
<div class="modal fade" id="modalEditTribunal{{ $dt->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold"><i class="bi bi-pencil me-2 text-warning"></i>{{ $dt->tribunal->nom_tribunal ?? '—' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditTribunal{{ $dt->id }}" action="{{ route('dossiers.tribunaux.update', [$dossier, $dt]) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Degré</label>
                        <select name="id_degre" class="form-select" required>
                            @foreach($degresJuridiction as $d)
                                <option value="{{ $d->id }}" @selected($dt->id_degre == $d->id)>{{ $d->degre_juridiction }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Date de saisine</label>
                        <input type="date" name="date_debut" class="form-control" value="{{ $dt->date_debut?->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Date de fin</label>
                        <input type="date" name="date_fin" class="form-control" value="{{ $dt->date_fin?->format('Y-m-d') }}">
                    </div>
                </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="formEditTribunal{{ $dt->id }}" class="btn btn-warning">
                    <i class="bi bi-check-lg me-1"></i>Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach

{{-- Modal : Ajouter une finance ─────────────────── --}}
<div class="modal fade" id="modalAjouterFinance" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold"><i class="bi bi-cash-stack me-2 text-success"></i>Ajouter une finance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAjouterFinance" action="{{ route('finances.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Jugement <span class="text-danger">*</span></label>
                    <select name="id_jugement" class="form-select" required>
                        <option value="">— Sélectionner —</option>
                        @foreach($instances->flatMap->jugements->filter(fn($j) => !$j->finance) as $jSF)
                            <option value="{{ $jSF->id }}">
                                Jugement du {{ $jSF->date_jugement?->format('d/m/Y') }}
                                — {{ $jSF->dossierTribunal?->degre?->degre_juridiction ?? '—' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Montant condamné <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="montant_condamne" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Montant payé</label>
                    <input type="number" step="0.01" min="0" name="montant_paye" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Date de paiement</label>
                    <input type="date" name="date_paiement" class="form-control">
                </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="formAjouterFinance" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal : Joindre un document ─────────────────── --}}
<div class="modal fade" id="modalAjouterDocument" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold"><i class="bi bi-upload me-2 text-primary"></i>Joindre un document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAjouterDocument" action="{{ route('documents.store', $dossier) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Fichier <span class="text-danger">*</span></label>
                    <input type="file" name="fichier" class="form-control" required>
                    <div class="form-text">PDF, Word, Excel, images — max 10 Mo</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre_document" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Type de document <span class="text-danger">*</span></label>
                    <select name="id_type_document" class="form-select" required>
                        <option value="">— Sélectionner —</option>
                        @foreach($typesDocuments as $type)
                            <option value="{{ $type->id }}">{{ $type->type_document }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Date de dépôt <span class="text-danger">*</span></label>
                    <input type="date" name="date_depot" class="form-control" value="{{ date('Y-m-d') }}" required>
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
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="formAjouterDocument" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Joindre</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* ── Réactiver l'onglet depuis l'URL (fragment) ─── */
(function () {
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector(`[data-bs-target="${hash}"]`);
        if (tab) new bootstrap.Tab(tab).show();
    }
})();

/* ── Recherche AJAX parties ───────────────────── */
(function () {
    const input       = document.getElementById('recherchePartie');
    const dropdown    = document.getElementById('resultatRecherche');
    const bandeauOK   = document.getElementById('partieSelectionnee');
    const nomOK       = document.getElementById('partieSelectionneeNom');
    const btnDesel    = document.getElementById('btnDeselectionner');
    const btnNouvelle = document.getElementById('btnNouvellePartie');
    const btnModifier = document.getElementById('btnModifierAvocat');

    const blocExistant  = document.getElementById('bloc_avocat_existant');
    const blocNouveau   = document.getElementById('bloc_avocat_nouveau');
    const blocModif     = document.getElementById('bloc_avocat_modif');
    const avocatDisplay = document.getElementById('field_avocat_display');
    const avocatModif   = document.getElementById('field_avocat_modif_select');
    const avocatNvx     = document.getElementById('field_avocat_nouveau_select');

    const F = {
        id: document.getElementById('hidden_partie_id'),
        identifiant: document.getElementById('field_identifiant'),
        nom: document.getElementById('field_nom'),
        type_personne: document.getElementById('field_type_personne'),
        telephone: document.getElementById('field_telephone'),
        email: document.getElementById('field_email'),
        adresse: document.getElementById('field_adresse'),
    };

    let timer = null;

    function lockFields(lock) {
        ['identifiant','nom','email','adresse','telephone'].forEach(k => {
            if (!F[k]) return;
            F[k].readOnly = lock;
            F[k].classList.toggle('bg-light', lock);
        });
        if (F.type_personne) { F.type_personne.disabled = lock; F.type_personne.classList.toggle('bg-light', lock); }
    }

    function showAvocatExistant(nom, id) {
        blocExistant?.classList.remove('d-none');
        blocNouveau?.classList.add('d-none');
        if (avocatDisplay) avocatDisplay.value = nom || 'Aucun avocat';
        if (avocatNvx) { avocatNvx.disabled = true; avocatNvx.name = ''; }
        if (avocatModif) { avocatModif.disabled = true; avocatModif.name = ''; }
        if (id && avocatModif) Array.from(avocatModif.options).forEach(o => o.selected = (o.value == id));
    }

    function showAvocatNouveau() {
        blocExistant?.classList.add('d-none');
        blocNouveau?.classList.remove('d-none');
        if (avocatNvx) { avocatNvx.disabled = false; avocatNvx.name = 'id_avocat'; }
        if (avocatModif) { avocatModif.disabled = true; avocatModif.name = ''; }
    }

    function selectPartie(p) {
        if (F.id) F.id.value = p.id;
        if (F.identifiant) F.identifiant.value = p.identifiant_unique ?? '';
        if (F.nom) F.nom.value = p.nom_partie ?? '';
        if (F.email) F.email.value = p.email ?? '';
        if (F.telephone) F.telephone.value = p.telephone ?? '';
        if (F.adresse) F.adresse.value = p.adresse ?? '';
        if (F.type_personne) Array.from(F.type_personne.options).forEach(o => o.selected = (o.value === p.type_personne));
        lockFields(true);
        if (nomOK) nomOK.textContent = `${p.nom_partie} (${p.identifiant_unique})`;
        bandeauOK?.classList.remove('d-none');
        closeDropdown();
        if (input) input.value = '';
        showAvocatExistant(p.avocat_nom, p.id_avocat);
    }

    function deselect() {
        if (F.id) F.id.value = '';
        lockFields(false);
        bandeauOK?.classList.add('d-none');
        ['identifiant','nom','email','telephone','adresse'].forEach(k => { if(F[k]) F[k].value = ''; });
        if (F.type_personne) { F.type_personne.selectedIndex = 0; F.type_personne.disabled = false; F.type_personne.classList.remove('bg-light'); }
        showAvocatNouveau();
    }

    function closeDropdown() { if(dropdown) { dropdown.style.display = 'none'; dropdown.innerHTML = ''; } }

    function renderResults(parties, query) {
        if (!dropdown) return;
        dropdown.innerHTML = '';
        parties.forEach(p => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'list-group-item list-group-item-action py-2 px-3';
            btn.innerHTML = `<div class="fw-semibold small">${p.nom_partie ?? ''}</div>
                <div class="text-muted" style="font-size:.75rem"><span class="font-monospace">${p.identifiant_unique ?? ''}</span>${p.avocat_nom ? ' · ' + p.avocat_nom : ''}</div>`;
            btn.addEventListener('click', () => selectPartie(p));
            dropdown.appendChild(btn);
        });
        const creer = document.createElement('button');
        creer.type = 'button';
        creer.className = 'list-group-item list-group-item-action py-2 px-3 text-primary';
        creer.innerHTML = `<i class="bi bi-plus-circle me-1"></i>Créer « ${query} »`;
        creer.addEventListener('click', () => { deselect(); if(F.nom) F.nom.value = query; closeDropdown(); if(input) input.value = ''; });
        if (!parties.length) {
            const info = document.createElement('div');
            info.className = 'list-group-item py-2 px-3 text-muted small';
            info.textContent = 'Aucune partie trouvée.';
            dropdown.appendChild(info);
        }
        dropdown.appendChild(creer);
        dropdown.style.display = 'block';
    }

    input?.addEventListener('input', () => {
        clearTimeout(timer);
        const q = input.value.trim();
        if (q.length < 2) { closeDropdown(); return; }
        timer = setTimeout(async () => {
            try {
                const res = await fetch(`{{ route('dossiers.parties.search', $dossier) }}?q=${encodeURIComponent(q)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error();
                renderResults(await res.json(), q);
            } catch {}
        }, 280);
    });

    document.addEventListener('click', e => {
        if (!input?.contains(e.target) && !dropdown?.contains(e.target)) closeDropdown();
    });

    btnNouvelle?.addEventListener('click', () => { deselect(); closeDropdown(); if(input) input.value = ''; F.identifiant?.focus(); });
    btnDesel?.addEventListener('click', e => { e.preventDefault(); deselect(); input?.focus(); });

    btnModifier?.addEventListener('click', () => {
        blocModif?.classList.toggle('d-none');
        const visible = !blocModif?.classList.contains('d-none');
        if (avocatModif) { avocatModif.disabled = !visible; avocatModif.name = visible ? 'id_avocat' : ''; }
        if (btnModifier) btnModifier.innerHTML = visible ? '<i class="bi bi-x me-1"></i>Annuler' : '<i class="bi bi-pencil me-1"></i>Modifier';
    });

    document.getElementById('modalAjouterPartie')?.addEventListener('show.bs.modal', () => {
        deselect(); closeDropdown(); if(input) input.value = '';
    });

    showAvocatNouveau();
})();

/* ── Cascade Région > Province > Degré > Tribunal ─ */
(function () {
    const selRegion   = document.getElementById('modal_region');
    const selProvince = document.getElementById('modal_province');
    const selDegre    = document.getElementById('modal_degre');
    const selTribunal = document.getElementById('modal_tribunal');

    function reset(sel, ph) { if(!sel) return; sel.innerHTML = `<option value="">${ph}</option>`; sel.disabled = true; }

    selRegion?.addEventListener('change', async function () {
        reset(selProvince, '— Chargement… —');
        reset(selDegre, '— Sélectionner d\'abord une province —');
        reset(selTribunal, '— Sélectionner d\'abord un degré —');
        if (!this.value) { reset(selProvince, '— Sélectionner d\'abord une région —'); return; }
        try {
            const data = await (await fetch(`/api/regions/${this.value}/provinces`)).json();
            selProvince.innerHTML = '<option value="">— Sélectionner une province —</option>';
            data.forEach(p => selProvince.innerHTML += `<option value="${p.id}">${p.province}</option>`);
            selProvince.disabled = false;
        } catch { reset(selProvince, '— Erreur —'); }
    });

    selProvince?.addEventListener('change', async function () {
        reset(selDegre, '— Chargement… —');
        reset(selTribunal, '— Sélectionner d\'abord un degré —');
        if (!this.value) { reset(selDegre, '— Sélectionner d\'abord une province —'); return; }
        try {
            const data = await (await fetch(`/api/provinces/${this.value}/degres`)).json();
            selDegre.innerHTML = '<option value="">— Sélectionner un degré —</option>';
            data.forEach(d => selDegre.innerHTML += `<option value="${d.id}">${d.degre_juridiction}</option>`);
            selDegre.disabled = false;
        } catch { reset(selDegre, '— Erreur —'); }
    });

    selDegre?.addEventListener('change', async function () {
        reset(selTribunal, '— Chargement… —');
        if (!this.value) { reset(selTribunal, '— Sélectionner d\'abord un degré —'); return; }
        try {
            const data = await (await fetch(`/api/provinces/${selProvince.value}/degres/${this.value}/tribunaux`)).json();
            selTribunal.innerHTML = '<option value="">— Sélectionner un tribunal —</option>';
            if (!data.length) { selTribunal.innerHTML = '<option value="">— Aucun tribunal disponible —</option>'; return; }
            data.forEach(t => selTribunal.innerHTML += `<option value="${t.id}">${t.nom_tribunal}</option>`);
            selTribunal.disabled = false;
        } catch { reset(selTribunal, '— Erreur —'); }
    });

    document.getElementById('modalAjouterTribunal')?.addEventListener('show.bs.modal', () => {
        if(selRegion) selRegion.value = '';
        reset(selProvince, '— Sélectionner d\'abord une région —');
        reset(selDegre, '— Sélectionner d\'abord une province —');
        reset(selTribunal, '— Sélectionner d\'abord un degré —');
    });
})();
</script>
@endpush