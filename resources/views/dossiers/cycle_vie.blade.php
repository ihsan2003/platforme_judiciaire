{{--
    resources/views/dossiers/cycle_vie.blade.php
    ─────────────────────────────────────────────
    Visualisation du cycle de vie complet d'un dossier judiciaire marocain.

    Règles métier encodées :
    RG-CYC-01 : Chaque degré est une instance autonome (DossierTribunal).
    RG-CYC-02 : الدرجة الأولى est accessible une seule fois (ordre=1).
    RG-CYC-03 : L'appel (الاستئناف) n'est ouvert qu'après un jugement au 1er degré.
    RG-CYC-04 : La cassation (النقض) n'est ouverte qu'après un jugement en appel.
    RG-CYC-05 : Chaque instance possède ses propres audiences (jamais mélangées).
    RG-CYC-06 : Une seule audience الحكم par instance → déclenche un seul jugement.
    RG-CYC-07 : La date du jugement = date de l'audience الحكم.

    Usage dans routes/web.php :
        Route::get('dossiers/{dossier}/cycle-vie', [DossierJudiciaireController::class, 'cycleVie'])
             ->name('dossiers.cycle-vie');

    Usage dans DossierJudiciaireController :
        public function cycleVie(DossierJudiciaire $dossier)
        {
            $dossier->load([
                'dossierTribunaux' => fn($q) => $q->orderBy('date_debut'),
                'dossierTribunaux.tribunal.typeTribunal',
                'dossierTribunaux.degre',
                'dossierTribunaux.audiences.typeAudience',
                'dossierTribunaux.audiences.juge',
                'dossierTribunaux.jugements.juge',
                'dossierTribunaux.jugements.finance',
                'dossierTribunaux.jugements.recours.typeRecours',
                'dossierTribunaux.jugements.executions.statut',
                'typeAffaire',
                'statut',
            ]);
            return view('dossiers.cycle_vie', compact('dossier'));
        }
--}}

@extends('layouts.app')

@section('title', 'Cycle de vie — ' . $dossier->numero_dossier_interne)

@push('styles')
<style>
/* ── Variables ───────────────────────────────────────── */
:root {
    --deg1-color  : #1a6b3a;  /* vert  — 1er degré     */
    --deg1-light  : #e8f5ee;
    --deg2-color  : #1a3a6b;  /* bleu  — استئناف       */
    --deg2-light  : #e8eef5;
    --deg3-color  : #6b1a1a;  /* rouge — نقض            */
    --deg3-light  : #f5e8e8;
    --aud-color   : #b45309;  /* ambre — audience       */
    --houkm-color : #7c3aed;  /* violet — الحكم          */
    --jug-color   : #0f766e;  /* teal  — jugement       */
    --rec-color   : #c2410c;  /* orange — recours       */
    --exec-color  : #0369a1;  /* cyan  — exécution      */
    --timeline-w  : 3px;
    --dot-size    : 44px;
    --connector   : #cbd5e1;
}

/* ── Layout ──────────────────────────────────────────── */
.cv-wrapper {
    max-width: 960px;
    margin: 0 auto;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

/* ── En-tête dossier ─────────────────────────────────── */
.cv-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
    border-radius: 16px;
    color: #fff;
    padding: 28px 32px;
    margin-bottom: 36px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}
.cv-header-title { font-size: 1.5rem; font-weight: 800; letter-spacing: -.01em; }
.cv-header-sub   { font-size: .85rem; opacity: .7; margin-top: 4px; }
.cv-header-meta  { display: flex; gap: 24px; flex-wrap: wrap; }
.cv-meta-item    { text-align: center; }
.cv-meta-value   { font-size: 1.4rem; font-weight: 700; color: #c8a84b; }
.cv-meta-label   { font-size: .7rem; opacity: .7; text-transform: uppercase; letter-spacing: .06em; }

/* ── Degré container ─────────────────────────────────── */
.degree-block {
    position: relative;
    margin-bottom: 0;
}

/* Connecteur vertical entre degrés */
.degree-connector {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 8px 0;
    position: relative;
    z-index: 1;
}
.degree-connector-line {
    width: var(--timeline-w);
    flex: 1;
    min-height: 48px;
    background: var(--connector);
}
.degree-connector-arrow {
    font-size: 1.4rem;
    color: var(--connector);
    line-height: 1;
    margin: 2px 0;
}
.degree-connector-label {
    background: #f1f5f9;
    border: 1px solid var(--connector);
    border-radius: 20px;
    padding: 4px 14px;
    font-size: .72rem;
    font-weight: 600;
    color: #64748b;
    letter-spacing: .04em;
    margin: 6px 0;
}

/* ── Carte de degré ──────────────────────────────────── */
.degree-card {
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
    border: 2px solid transparent;
    transition: box-shadow .2s;
}
.degree-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,.12); }

.degree-card.deg-1 { border-color: var(--deg1-color); }
.degree-card.deg-2 { border-color: var(--deg2-color); }
.degree-card.deg-3 { border-color: var(--deg3-color); }
.degree-card.deg-closed { opacity: .85; }

/* En-tête de la carte de degré */
.degree-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 22px;
    flex-wrap: wrap;
    gap: 12px;
}
.degree-header.deg-1 { background: var(--deg1-color); color: #fff; }
.degree-header.deg-2 { background: var(--deg2-color); color: #fff; }
.degree-header.deg-3 { background: var(--deg3-color); color: #fff; }

.degree-badge {
    display: inline-flex; align-items: center; gap: 8px;
}
.degree-badge-icon {
    width: 36px; height: 36px; border-radius: 50%;
    background: rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; font-weight: 800;
}
.degree-badge-text { font-size: 1.05rem; font-weight: 700; }
.degree-badge-sub  { font-size: .75rem; opacity: .8; }

.degree-status-pills { display: flex; gap: 8px; flex-wrap: wrap; }
.pill {
    padding: 4px 12px; border-radius: 20px; font-size: .72rem;
    font-weight: 600; letter-spacing: .03em;
    display: inline-flex; align-items: center; gap: 4px;
}
.pill-white   { background: rgba(255,255,255,.2); color: #fff; border: 1px solid rgba(255,255,255,.3); }
.pill-success { background: #dcfce7; color: #166534; }
.pill-warning { background: #fef3c7; color: #92400e; }
.pill-danger  { background: #fee2e2; color: #991b1b; }
.pill-info    { background: #e0f2fe; color: #075985; }
.pill-purple  { background: #f3e8ff; color: #6b21a8; }
.pill-muted   { background: #f1f5f9; color: #64748b; }

/* Corps de la carte de degré */
.degree-body { background: #fff; padding: 24px; }

/* ── Tribunal info ───────────────────────────────────── */
.tribunal-info {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 16px; border-radius: 10px;
    background: #f8fafc; border: 1px solid #e2e8f0;
    margin-bottom: 20px; flex-wrap: wrap;
}
.tribunal-info-name { font-weight: 700; font-size: .95rem; }
.tribunal-info-meta { font-size: .8rem; color: #64748b; display: flex; gap: 16px; flex-wrap: wrap; }

/* ── Timeline audiences ──────────────────────────────── */
.audiences-section-label {
    font-size: .75rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: #94a3b8;
    margin-bottom: 12px;
    display: flex; align-items: center; gap: 8px;
}
.audiences-section-label::after {
    content: ''; flex: 1; height: 1px; background: #e2e8f0;
}

.audiences-timeline {
    position: relative;
    padding-left: 32px;
    margin-bottom: 20px;
}
.audiences-timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 10px;
    bottom: 10px;
    width: var(--timeline-w);
    background: linear-gradient(to bottom, #e2e8f0 0%, #e2e8f0 100%);
    border-radius: 2px;
}

.aud-item {
    position: relative;
    margin-bottom: 10px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.aud-dot {
    position: absolute;
    left: -32px;
    top: 8px;
    width: 22px; height: 22px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #e2e8f0;
    background: var(--aud-color);
    display: flex; align-items: center; justify-content: center;
    font-size: .55rem; color: #fff;
    flex-shrink: 0;
}
.aud-dot.is-houkm {
    background: var(--houkm-color);
    box-shadow: 0 0 0 3px rgba(124,58,237,.25);
    width: 26px; height: 26px;
    left: -34px; top: 6px;
}
.aud-dot.is-future {
    background: #3b82f6;
}

.aud-card {
    flex: 1;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    padding: 10px 14px;
    background: #fff;
    transition: border-color .15s;
}
.aud-card:hover { border-color: #94a3b8; }
.aud-card.is-houkm {
    border-color: rgba(124,58,237,.4);
    background: #fdf4ff;
}
.aud-card.is-future {
    border-color: rgba(59,130,246,.35);
    background: #eff6ff;
}

.aud-card-header {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 6px;
    margin-bottom: 4px;
}
.aud-date { font-weight: 700; font-size: .88rem; }
.aud-type {
    font-size: .72rem; font-weight: 700; padding: 2px 8px;
    border-radius: 10px; letter-spacing: .03em;
}
.aud-type.normal { background: #fef3c7; color: #92400e; }
.aud-type.houkm  { background: #f3e8ff; color: #6b21a8; }
.aud-type.future { background: #dbeafe; color: #1d4ed8; }

.aud-detail {
    font-size: .78rem; color: #64748b;
    display: flex; flex-wrap: wrap; gap: 10px;
}
.aud-renvoie {
    font-size: .75rem; color: #64748b;
    margin-top: 4px;
    padding-top: 4px; border-top: 1px dashed #e2e8f0;
    display: flex; align-items: center; gap: 4px;
}

/* Absence d'audiences */
.no-audiences {
    padding: 16px; border-radius: 8px;
    background: #f8fafc; border: 1px dashed #cbd5e1;
    text-align: center; color: #94a3b8; font-size: .85rem;
    margin-bottom: 20px;
}

/* ── Jugement ────────────────────────────────────────── */
.jugement-block {
    border-radius: 10px;
    border: 2px solid var(--jug-color);
    background: #f0fdfa;
    padding: 16px 18px;
    margin-bottom: 16px;
}
.jugement-header {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 10px; margin-bottom: 10px;
}
.jugement-title {
    font-weight: 800; font-size: .95rem;
    color: var(--jug-color);
    display: flex; align-items: center; gap: 8px;
}
.jugement-meta {
    font-size: .8rem; color: #475569;
    display: flex; flex-wrap: wrap; gap: 14px;
    margin-bottom: 8px;
}
.jugement-dispositif {
    font-size: .82rem; color: #334155;
    background: #fff;
    border: 1px solid #ccfbf1;
    border-left: 3px solid var(--jug-color);
    padding: 8px 12px; border-radius: 6px;
    max-height: 80px; overflow: hidden;
    position: relative;
}
.jugement-dispositif.expanded { max-height: none; }
.jugement-dispositif-fade {
    position: absolute; bottom: 0; left: 0; right: 0;
    height: 30px;
    background: linear-gradient(to bottom, transparent, #fff);
}

/* Finance ligne */
.finance-line {
    margin-top: 10px; padding-top: 10px; border-top: 1px solid #a7f3d0;
    display: flex; flex-wrap: wrap; gap: 16px; font-size: .8rem;
}
.finance-progress {
    margin-top: 6px; height: 6px; border-radius: 3px;
    background: #e2e8f0; overflow: hidden;
}
.finance-progress-bar { height: 100%; border-radius: 3px; transition: width .5s ease; }

/* ── Recours ─────────────────────────────────────────── */
.recours-block {
    border-radius: 10px;
    border: 2px dashed var(--rec-color);
    background: #fff7ed;
    padding: 14px 16px;
    margin-bottom: 16px;
}
.recours-title {
    font-weight: 700; font-size: .88rem;
    color: var(--rec-color);
    display: flex; align-items: center; gap: 6px;
    margin-bottom: 6px;
}
.recours-meta { font-size: .8rem; color: #7c2d12; display: flex; flex-wrap: wrap; gap: 12px; }

/* ── Exécution ───────────────────────────────────────── */
.exec-block {
    border-radius: 10px;
    border: 2px solid var(--exec-color);
    background: #f0f9ff;
    padding: 14px 16px;
}
.exec-title {
    font-weight: 700; font-size: .88rem;
    color: var(--exec-color);
    display: flex; align-items: center; gap: 6px;
    margin-bottom: 6px;
}
.exec-meta { font-size: .8rem; color: #0c4a6e; display: flex; flex-wrap: wrap; gap: 12px; }

/* ── État vide ───────────────────────────────────────── */
.pending-block {
    border-radius: 10px;
    border: 2px dashed #cbd5e1;
    background: #f8fafc;
    padding: 14px 16px;
    text-align: center;
    color: #94a3b8; font-size: .84rem;
}

/* ── Indicateur de progression ───────────────────────── */
.progress-bar-container {
    display: flex; align-items: center; gap: 0;
    margin-bottom: 32px;
    background: #f1f5f9;
    border-radius: 12px;
    padding: 4px;
    flex-wrap: wrap;
    gap: 4px;
}
.progress-step {
    flex: 1; min-width: 120px;
    padding: 10px 16px; border-radius: 9px;
    display: flex; align-items: center; gap: 8px;
    font-size: .78rem; font-weight: 600;
    color: #94a3b8;
    transition: all .2s;
}
.progress-step.done   { background: var(--deg1-color); color: #fff; }
.progress-step.active { background: #1a3a6b; color: #fff; }
.progress-step.appel  { background: var(--deg2-color); color: #fff; }
.progress-step.naqd   { background: var(--deg3-color); color: #fff; }
.progress-step-num {
    width: 22px; height: 22px; border-radius: 50%;
    background: rgba(255,255,255,.25);
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem; font-weight: 800; flex-shrink: 0;
}

/* ── Alerte RG ───────────────────────────────────────── */
.rg-alert {
    border-radius: 8px;
    padding: 10px 14px;
    font-size: .8rem;
    display: flex; align-items: flex-start; gap: 8px;
    margin-bottom: 14px;
}
.rg-alert.warn { background: #fef9c3; border-left: 3px solid #eab308; color: #713f12; }
.rg-alert.info { background: #e0f2fe; border-left: 3px solid #0284c7; color: #075985; }
.rg-alert.err  { background: #fee2e2; border-left: 3px solid #ef4444; color: #7f1d1d; }

/* ── Compte-à-rebours délai de recours ───────────────── */
.delai-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px; font-size: .72rem; font-weight: 700;
}
.delai-ok     { background: #dcfce7; color: #166534; }
.delai-warn   { background: #fef3c7; color: #92400e; }
.delai-danger { background: #fee2e2; color: #991b1b; }
.delai-expire { background: #f1f5f9; color: #64748b; }
</style>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dossiers.index') }}">Dossiers</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dossiers.show', $dossier) }}">{{ $dossier->numero_dossier_interne }}</a></li>
    <li class="breadcrumb-item active">Cycle de vie</li>
@endsection

@section('content')
@php
    /* ── Tri des instances par date de début ─────────────────────── */
    $instances = $dossier->dossierTribunaux->sortBy('date_debut');

    /* ── Helpers ─────────────────────────────────────────────────── */
    $degreeLabel = fn($dt) => $dt->degre?->degre_juridiction ?? '—';

    $degreeOrder = fn($dt) => $dt->degre?->ordre ?? 99;

    $degreeColor = function($dt) {
        $ordre = $dt->degre?->ordre ?? 99;
        return match($ordre) {
            1 => 'deg-1',
            2 => 'deg-2',
            3 => 'deg-3',
            default => 'deg-1',
        };
    };

    $degreeHeaderClass = function($dt) {
        $ordre = $dt->degre?->ordre ?? 99;
        return match($ordre) {
            1 => 'deg-1',
            2 => 'deg-2',
            3 => 'deg-3',
            default => 'deg-1',
        };
    };

    $degreeCssVar = function($dt) {
        $ordre = $dt->degre?->ordre ?? 99;
        return match($ordre) {
            1 => '#1a6b3a',
            2 => '#1a3a6b',
            3 => '#6b1a1a',
            default => '#1a6b3a',
        };
    };

    /* Icônes Bootstrap pour les types d'audience */
    $audIcon = function(string $type): string {
        return match(true) {
            str_contains($type, 'الحكم')      => 'bi-gavel',
            str_contains($type, 'تداول')      => 'bi-arrow-repeat',
            str_contains($type, 'إجراء')      => 'bi-clipboard-check',
            str_contains($type, 'اثبات')      => 'bi-search',
            str_contains($type, 'خبرة')       => 'bi-person-check',
            default                            => 'bi-calendar-event',
        };
    };
@endphp

<div class="cv-wrapper">

{{-- ══════════════════════════════════════════════════════
     EN-TÊTE DOSSIER
══════════════════════════════════════════════════════ --}}
<div class="cv-header">
    <div>
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="rounded-3 d-flex align-items-center justify-content-center"
                 style="width:48px;height:48px;background:rgba(255,255,255,.15)">
                <i class="bi bi-diagram-3 fs-4 text-warning"></i>
            </div>
            <div>
                <div class="cv-header-title">{{ $dossier->numero_dossier_interne }}</div>
                <div class="cv-header-sub">
                    {{ $dossier->typeAffaire->affaire ?? '—' }}
                    · Ouvert le {{ $dossier->date_ouverture?->format('d/m/Y') }}
                </div>
            </div>
        </div>
        <div>
            <span class="pill pill-white">
                <i class="bi bi-circle-fill" style="font-size:.45rem"></i>
                {{ $dossier->statut->statut_dossier ?? '—' }}
            </span>
        </div>
    </div>

    <div class="cv-header-meta">
        <div class="cv-meta-item">
            <div class="cv-meta-value">{{ $instances->count() }}</div>
            <div class="cv-meta-label">Instance(s)</div>
        </div>
        <div class="cv-meta-item">
            <div class="cv-meta-value">
                {{ $instances->flatMap->audiences->count() }}
            </div>
            <div class="cv-meta-label">Audiences</div>
        </div>
        <div class="cv-meta-item">
            <div class="cv-meta-value">
                {{ $instances->flatMap->jugements->count() }}
            </div>
            <div class="cv-meta-label">Jugement(s)</div>
        </div>
        <div class="cv-meta-item text-end">
            <a href="{{ route('dossiers.show', $dossier) }}"
               class="btn btn-sm"
               style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3)">
                <i class="bi bi-arrow-left me-1"></i>Fiche dossier
            </a>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     BARRE DE PROGRESSION
══════════════════════════════════════════════════════ --}}
<div class="progress-bar-container mb-4">
    @forelse($instances->sortBy(fn($dt) => $dt->degre?->ordre) as $idx => $dt)
    @php
        $hasJugement = $dt->jugements->isNotEmpty();
        $isClosed    = !is_null($dt->date_fin);
        $color       = $degreeHeaderClass($dt);
    @endphp
    <div class="progress-step {{ $isClosed ? ($color === 'deg-1' ? 'done' : ($color === 'deg-2' ? 'appel' : 'naqd')) : 'active' }}">
        <div class="progress-step-num">{{ $idx + 1 }}</div>
        <div>
            <div style="font-size:.75rem;font-weight:700">{{ $degreeLabel($dt) }}</div>
            <div style="font-size:.65rem;opacity:.8">
                {{ $isClosed ? 'Clôturée' : ($hasJugement ? 'Jugement rendu' : 'En cours') }}
            </div>
        </div>
    </div>
    @if(!$loop->last)
    <div style="font-size:.9rem;color:#94a3b8;padding:0 4px;align-self:center">›</div>
    @endif
    @empty
    <div class="progress-step" style="color:#94a3b8">
        <i class="bi bi-hourglass me-2"></i>Aucune instance
    </div>
    @endforelse

    @if($instances->isEmpty())
    {{-- Squelette des 3 degrés possibles --}}
    @foreach(['الدرجة الأولى','الاستئناف','النقض'] as $i => $lbl)
    <div class="progress-step">
        <div class="progress-step-num" style="background:rgba(0,0,0,.08);color:#94a3b8">{{ $i+1 }}</div>
        <div style="font-size:.75rem">{{ $lbl }}</div>
    </div>
    @if(!$loop->last)
    <div style="font-size:.9rem;color:#cbd5e1;padding:0 4px;align-self:center">›</div>
    @endif
    @endforeach
    @endif
</div>

{{-- ══════════════════════════════════════════════════════
     CORPS : instances triées par date_debut
══════════════════════════════════════════════════════ --}}
@forelse($instances as $loopIdx => $dt)
@php
    /* ── Données de cette instance ─────────────────── */
    $audiences      = $dt->audiences->sortBy('date_audience');
    $audienceHoukm  = $dt->audienceHoukm();
    $jugement       = $dt->jugements->sortByDesc('date_jugement')->first();
    $recours        = $jugement?->recours?->first();
    $finance        = $jugement?->finance;
    $executions     = $jugement?->executions ?? collect();
    $isClosed       = !is_null($dt->date_fin);
    $hasFuture      = $audiences->contains(fn($a) => $a->date_audience?->isFuture());

    /* ── Alertes RG ────────────────────────────────── */
    $rgAlerts = [];
    if ($audiences->isEmpty())
        $rgAlerts[] = ['type' => 'info', 'msg' => 'RG-CYC-05 : Aucune audience dans cette instance pour le moment.'];
    if (!$audienceHoukm && $audiences->isNotEmpty())
        $rgAlerts[] = ['type' => 'warn', 'msg' => 'RG-CYC-06 : L\'audience "الحكم" n\'a pas encore été enregistrée — le jugement ne peut pas être saisi.'];
    if ($audienceHoukm && !$jugement)
        $rgAlerts[] = ['type' => 'warn', 'msg' => 'RG-CYC-07 : L\'audience "الحكم" existe — vous pouvez maintenant saisir le jugement (date : ' . $audienceHoukm->date_audience->format('d/m/Y') . ').'];
@endphp

{{-- Connecteur entre instances (pas avant la première) --}}
@if($loopIdx > 0)
<div class="degree-connector">
    <div class="degree-connector-line"></div>
    <div class="degree-connector-arrow"><i class="bi bi-arrow-down-circle-fill" style="color:var(--connector)"></i></div>
    @php $prevDt = $instances->values()[$loopIdx - 1]; @endphp
    @if($recours = $prevDt->jugements->first()?->recours?->first())
    <div class="degree-connector-label">
        <i class="bi bi-arrow-repeat me-1"></i>
        {{ $recours->typeRecours->type_recours ?? 'Recours' }}
        du {{ $recours->date_recours->format('d/m/Y') }}
    </div>
    @else
    <div class="degree-connector-label">Transition de degré</div>
    @endif
    <div class="degree-connector-line"></div>
</div>
@endif

{{-- Carte de l'instance --}}
<div class="degree-block">
<div class="degree-card {{ $degreeColor($dt) }} {{ $isClosed ? 'deg-closed' : '' }}">

    {{-- ── En-tête de l'instance ───────────────────── --}}
    <div class="degree-header {{ $degreeHeaderClass($dt) }}">
        <div class="degree-badge">
            <div class="degree-badge-icon">{{ $dt->degre?->ordre ?? '?' }}</div>
            <div>
                <div class="degree-badge-text">{{ $degreeLabel($dt) }}</div>
                <div class="degree-badge-sub">
                    {{ $dt->tribunal->nom_tribunal ?? '—' }}
                </div>
            </div>
        </div>
        <div class="degree-status-pills">
            {{-- Statut de l'instance --}}
            @if($isClosed)
                <span class="pill pill-white"><i class="bi bi-lock-fill"></i> Clôturée</span>
            @elseif($jugement)
                <span class="pill pill-white"><i class="bi bi-hammer"></i> Jugement rendu</span>
            @elseif($audienceHoukm)
                <span class="pill pill-white"><i class="bi bi-hourglass-split"></i> En délibéré</span>
            @else
                <span class="pill pill-white"><i class="bi bi-activity"></i> En cours</span>
            @endif

            {{-- Compteurs --}}
            <span class="pill pill-white">
                <i class="bi bi-calendar3"></i> {{ $audiences->count() }} audience(s)
            </span>

            {{-- Dates --}}
            <span class="pill pill-white" style="font-size:.68rem">
                <i class="bi bi-calendar-event"></i>
                {{ $dt->date_debut?->format('d/m/Y') }} → {{ $dt->date_fin?->format('d/m/Y') ?? 'En cours' }}
            </span>
        </div>
    </div>

    {{-- ── Corps ───────────────────────────────────── --}}
    <div class="degree-body">

        {{-- Alertes RG --}}
        @foreach($rgAlerts as $alert)
        <div class="rg-alert {{ $alert['type'] }}">
            <i class="bi bi-{{ $alert['type'] === 'warn' ? 'exclamation-triangle' : 'info-circle' }}-fill flex-shrink-0 mt-1"></i>
            <span>{{ $alert['msg'] }}</span>
        </div>
        @endforeach

        {{-- ── Section AUDIENCES ─────────────────── --}}
        <div class="audiences-section-label">
            <i class="bi bi-calendar3 text-amber-600"></i>
            Audiences de cette instance
            <span class="pill pill-muted" style="font-size:.65rem;margin-left:4px">
                {{ $audiences->count() }}
            </span>
        </div>

        @if($audiences->isEmpty())
        <div class="no-audiences">
            <i class="bi bi-calendar-x d-block mb-1 fs-4 opacity-30"></i>
            Aucune audience enregistrée dans cette instance.
            @if(!$isClosed)
            <div class="mt-2">
                <a href="{{ route('audiences.create', ['dossier_id' => $dossier->id, 'dossier_tribunal_id' => $dt->id]) }}"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-calendar-plus me-1"></i>Planifier une audience
                </a>
            </div>
            @endif
        </div>
        @else
        <div class="audiences-timeline">
            @foreach($audiences as $aud)
            @php
                $isHoukm = $aud->typeAudience?->type_audience === 'الحكم';
                $isFuture = $aud->date_audience?->isFuture();
                $typeLabel = $aud->typeAudience?->type_audience ?? '—';
            @endphp
            <div class="aud-item">
                <div class="aud-dot {{ $isHoukm ? 'is-houkm' : ($isFuture ? 'is-future' : '') }}">
                    <i class="bi {{ $audIcon($typeLabel) }}"></i>
                </div>
                <div class="aud-card w-100 {{ $isHoukm ? 'is-houkm' : ($isFuture ? 'is-future' : '') }}">
                    <div class="aud-card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="aud-date">
                                {{ $aud->date_audience?->format('d/m/Y') ?? '—' }}
                            </span>
                            @if($aud->date_audience?->isToday())
                                <span class="pill pill-danger" style="font-size:.65rem;padding:1px 7px">Aujourd'hui</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="aud-type {{ $isHoukm ? 'houkm' : ($isFuture ? 'future' : 'normal') }}">
                                @if($isHoukm)<i class="bi bi-gavel me-1"></i>@endif
                                {{ $typeLabel }}
                                @if($isHoukm)<strong class="ms-1">← Finale</strong>@endif
                            </span>
                            @if(!$isClosed && !$isHoukm)
                            <a href="{{ route('audiences.show', $aud) }}"
                               class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:.7rem">
                                <i class="bi bi-eye"></i>
                            </a>
                            @endif
                        </div>
                    </div>

                    <div class="aud-detail">
                        @if($aud->juge)
                        <span><i class="bi bi-person me-1"></i>{{ $aud->juge->nom_complet }}</span>
                        @endif
                        @if($aud->resultat_audience)
                        <span><i class="bi bi-chat-left-text me-1"></i>{{ Str::limit($aud->resultat_audience, 60) }}</span>
                        @endif
                        @if($aud->presence_demandeur !== null || $aud->presence_defendeur !== null)
                        <span>
                            <i class="bi bi-people me-1"></i>
                            Demandeur : {{ $aud->presence_demandeur ? '✓' : '✗' }}
                            · Défendeur : {{ $aud->presence_defendeur ? '✓' : '✗' }}
                        </span>
                        @endif
                    </div>

                    @if($aud->date_prochaine_audience && !$isHoukm)
                    <div class="aud-renvoie">
                        <i class="bi bi-calendar-arrow-down text-muted"></i>
                        Renvoi au <strong>{{ $aud->date_prochaine_audience->format('d/m/Y') }}</strong>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach

            {{-- Bouton ajout audience si pas de الحكم et instance ouverte --}}
            @if(!$audienceHoukm && !$isClosed)
            <div class="aud-item" style="margin-top:6px">
                <div class="aud-dot" style="background:#e2e8f0;border:2px dashed #94a3b8">
                    <i class="bi bi-plus text-secondary"></i>
                </div>
                <div style="padding:8px 0">
                    <a href="{{ route('audiences.create', ['dossier_id' => $dossier->id, 'dossier_tribunal_id' => $dt->id]) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-calendar-plus me-1"></i>Planifier une audience
                    </a>
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- ══════════════════════════════════════════
             RG-CYC-06 : Séparateur الحكم → Jugement
        ══════════════════════════════════════════ --}}
        @if($audienceHoukm)
        <div class="text-center my-3" style="position:relative;">
            <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);
                        background:#fff;padding:0 12px;font-size:.75rem;font-weight:700;
                        color:var(--houkm-color);letter-spacing:.04em;white-space:nowrap;z-index:1;">
                <i class="bi bi-arrow-down me-1"></i>
                Audience الحكم du {{ $audienceHoukm->date_audience->format('d/m/Y') }}
                → Jugement
            </div>
            <hr style="border-color:rgba(124,58,237,.25);border-width:1px">
        </div>
        @endif

        {{-- ── Section JUGEMENT ───────────────────── --}}
        @if($jugement)
        <div class="jugement-block">
            <div class="jugement-header">
                <div class="jugement-title">
                    <i class="bi bi-hammer"></i>
                    Jugement du {{ $jugement->date_jugement->format('d/m/Y') }}
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @if($jugement->est_definitif)
                        <span class="pill pill-success"><i class="bi bi-check-circle-fill"></i> Définitif</span>
                    @else
                        @php
                            $dr = $jugement->delai_recours_restant;
                        @endphp
                        @if($dr !== null)
                            @if($dr > 10)
                                <span class="delai-badge delai-ok">
                                    <i class="bi bi-clock"></i> {{ $dr }}j avant expiration
                                </span>
                            @elseif($dr > 0)
                                <span class="delai-badge delai-warn">
                                    <i class="bi bi-exclamation-triangle-fill"></i> {{ $dr }}j restants
                                </span>
                            @elseif($dr === 0)
                                <span class="delai-badge delai-danger">
                                    <i class="bi bi-alarm-fill"></i> Expire aujourd'hui
                                </span>
                            @else
                                <span class="delai-badge delai-expire">Délai expiré</span>
                            @endif
                        @endif
                    @endif
                    <a href="{{ route('jugements.show', $jugement) }}"
                       class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size:.75rem">
                        <i class="bi bi-eye me-1"></i>Voir
                    </a>
                </div>
            </div>

            <div class="jugement-meta">
                @if($jugement->juge)
                <span><i class="bi bi-person-workspace me-1"></i>{{ $jugement->juge->nom_complet }}</span>
                @endif
                <span>
                    <i class="bi bi-check2-square me-1"></i>
                    Caractère :
                    <strong>{{ $jugement->est_definitif ? 'Définitif' : 'Susceptible de recours' }}</strong>
                </span>
            </div>

            @if($jugement->contenu_dispositif)
            <div class="jugement-dispositif" id="disp-{{ $jugement->id }}">
                {{ $jugement->contenu_dispositif }}
                <div class="jugement-dispositif-fade" id="disp-fade-{{ $jugement->id }}"></div>
            </div>
            <button class="btn btn-link btn-sm p-0 mt-1" style="font-size:.75rem;color:var(--jug-color)"
                    onclick="toggleDisp({{ $jugement->id }})">
                <i class="bi bi-chevron-down me-1" id="disp-chevron-{{ $jugement->id }}"></i>Voir le dispositif complet
            </button>
            @endif

            {{-- Finance --}}
            @if($finance)
            @php
                $pct = $finance->montant_condamne > 0
                    ? min(100, round(($finance->montant_paye / $finance->montant_condamne) * 100))
                    : 0;
                $pctColor = $pct >= 100 ? '#16a34a' : ($pct > 0 ? '#d97706' : '#ef4444');
            @endphp
            <div class="finance-line">
                <div class="w-100">
                    <div class="d-flex justify-content-between mb-1" style="font-size:.78rem">
                        <span><i class="bi bi-cash-stack me-1"></i>Condamné : <strong>{{ number_format($finance->montant_condamne, 2) }} DH</strong></span>
                        <span>Payé : <strong style="color:#16a34a">{{ number_format($finance->montant_paye, 2) }} DH</strong></span>
                        <span>Restant : <strong style="color:{{ $finance->montant_restant > 0 ? '#ef4444' : '#16a34a' }}">{{ number_format($finance->montant_restant, 2) }} DH</strong></span>
                        <span>
                            @if($pct >= 100)
                                <span class="pill pill-success" style="font-size:.65rem">Soldé ✓</span>
                            @elseif($pct > 0)
                                <span class="pill pill-warning" style="font-size:.65rem">Partiel ({{ $pct }}%)</span>
                            @else
                                <span class="pill pill-muted" style="font-size:.65rem">En attente</span>
                            @endif
                        </span>
                    </div>
                    <div class="finance-progress">
                        <div class="finance-progress-bar" style="width:{{ $pct }}%;background:{{ $pctColor }}"></div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- ── Section RECOURS ────────────────────── --}}
        @if($recours = $jugement->recours->first())
        <div class="my-3 text-center" style="font-size:.75rem;color:#94a3b8">
            <i class="bi bi-arrow-down"></i> Recours déposé <i class="bi bi-arrow-down"></i>
        </div>
        <div class="recours-block">
            <div class="recours-title">
                <i class="bi bi-arrow-repeat"></i>
                {{ $recours->typeRecours->type_recours ?? 'Recours' }}
                — {{ $recours->date_recours->format('d/m/Y') }}
            </div>
            <div class="recours-meta">
                @if($recours->motifs)
                <span><i class="bi bi-chat-left-text me-1"></i>{{ Str::limit($recours->motifs, 100) }}</span>
                @endif
                @php
                    $estDansDelai = $recours->est_dans_delais ?? null;
                    $delai = $recours->typeRecours?->delai_legal_jours;
                @endphp
                @if($delai)
                <span><i class="bi bi-clock me-1"></i>Délai légal : {{ $delai }}j</span>
                @endif
                @if($estDansDelai !== null)
                <span class="pill {{ $estDansDelai ? 'pill-success' : 'pill-danger' }}" style="font-size:.65rem">
                    {{ $estDansDelai ? '✓ Dans les délais' : '⚠ Hors délai' }}
                </span>
                @endif
            </div>
        </div>

        {{-- ── Section EXÉCUTIONS ─────────────────── --}}
        @elseif($jugement->est_definitif)
            @if($executions->isNotEmpty())
                @foreach($executions as $exec)
                @php
                    $statExec = $exec->statut?->statut_execution ?? '—';
                    $execColor = str_contains($statExec, 'Terminé') ? '#16a34a'
                        : (str_contains($statExec, 'cours') ? '#d97706' : '#64748b');
                @endphp
                <div class="my-3 text-center" style="font-size:.75rem;color:#94a3b8">
                    <i class="bi bi-arrow-down"></i> Exécution <i class="bi bi-arrow-down"></i>
                </div>
                <div class="exec-block">
                    <div class="exec-title">
                        <i class="bi bi-shield-check"></i>
                        Exécution {{ $exec->numero_dossier_execution }}
                    </div>
                    <div class="exec-meta">
                        <span><i class="bi bi-bell me-1"></i>Notifiée le {{ $exec->date_notification?->format('d/m/Y') ?? '—' }}</span>
                        @if($exec->responsable)
                        <span><i class="bi bi-person me-1"></i>{{ $exec->responsable->name }}</span>
                        @endif
                        <span class="pill" style="font-size:.65rem;background:#e0f2fe;color:{{ $execColor }}">
                            {{ $statExec }}
                        </span>
                        @if($exec->date_execution)
                        <span class="pill pill-success" style="font-size:.65rem">
                            <i class="bi bi-calendar-check me-1"></i>Exécutée le {{ $exec->date_execution->format('d/m/Y') }}
                        </span>
                        @endif
                        <a href="{{ route('executions.show', $exec) }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:.7rem">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            @else
                <div class="my-3 text-center" style="font-size:.75rem;color:#94a3b8">
                    <i class="bi bi-arrow-down"></i>
                </div>
                <div class="pending-block">
                    <i class="bi bi-hourglass-split d-block mb-1 fs-4 opacity-30"></i>
                    Jugement définitif — en attente d'exécution
                    <div class="mt-2">
                        <a href="{{ route('executions.create', ['jugement_id' => $jugement->id]) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-lg me-1"></i>Lancer l'exécution
                        </a>
                    </div>
                </div>
            @endif

        {{-- Jugement non-définitif, délai non expiré, pas encore de recours --}}
        @elseif($jugement->peutFaireObjetRecours())
        <div class="pending-block mt-3">
            <i class="bi bi-clock-history d-block mb-1 fs-4 opacity-30"></i>
            En attente de recours ou de clôture sans recours.
        </div>
        @else
        {{-- Délai expiré, pas de recours → clôture manuelle possible --}}
        <div class="rg-alert warn mt-3">
            <i class="bi bi-exclamation-triangle-fill"></i>
            Délai de recours expiré et aucun recours déposé. Clôturez manuellement le jugement.
            <form action="{{ route('jugements.cloture-sans-recours', $jugement) }}"
                  method="POST" class="d-inline ms-2"
                  onsubmit="return confirm('Clôturer sans recours ?')">
                @csrf
                <button class="btn btn-sm btn-warning py-0 px-2" style="font-size:.75rem">Clôturer</button>
            </form>
        </div>
        @endif

        {{-- Pas encore de jugement --}}
        @elseif($audienceHoukm)
        <div class="pending-block">
            <i class="bi bi-hammer d-block mb-1 fs-4 opacity-30"></i>
            L'audience الحكم a eu lieu le <strong>{{ $audienceHoukm->date_audience->format('d/m/Y') }}</strong>.
            <div class="mt-2">
                <a href="{{ route('jugements.create', ['dossier_id' => $dossier->id]) }}"
                   class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Saisir le jugement
                </a>
            </div>
        </div>
        @elseif(!$isClosed)
        <div class="pending-block">
            <i class="bi bi-calendar-x d-block mb-1 fs-4 opacity-30"></i>
            En attente de l'audience finale <strong class="ar">الحكم</strong>
            pour pouvoir saisir un jugement.
        </div>
        @endif

    </div>{{-- /.degree-body --}}
</div>{{-- /.degree-card --}}
</div>{{-- /.degree-block --}}

@empty
{{-- Aucune instance --}}
<div class="text-center py-5 text-muted">
    <i class="bi bi-diagram-3 fs-1 d-block mb-3 opacity-20"></i>
    <h5 class="fw-semibold">Aucune instance judiciaire</h5>
    <p class="small mb-3">Assignez un tribunal au dossier pour commencer le cycle de vie.</p>
    <a href="{{ route('dossiers.show', $dossier) }}#tab-tribunaux"
       class="btn btn-primary">
        <i class="bi bi-bank me-2"></i>Assigner un tribunal
    </a>
</div>
@endforelse

{{-- ── Pied de page : légende ─────────────────────── --}}
@if($instances->isNotEmpty())
<div class="card border-0 shadow-sm mt-4">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap gap-3 align-items-center">
            <span class="small text-muted fw-semibold text-uppercase" style="letter-spacing:.05em">Légende</span>
            <span class="pill" style="background:#fef3c7;color:#92400e;font-size:.7rem">
                <i class="bi bi-calendar-event me-1"></i>Audience normale
            </span>
            <span class="pill" style="background:#f3e8ff;color:#6b21a8;font-size:.7rem">
                <i class="bi bi-gavel me-1"></i>Audience الحكم (finale)
            </span>
            <span class="pill" style="background:#f0fdfa;color:#0f766e;border:1px solid #a7f3d0;font-size:.7rem">
                <i class="bi bi-hammer me-1"></i>Jugement
            </span>
            <span class="pill" style="background:#fff7ed;color:#c2410c;border:1px dashed #fed7aa;font-size:.7rem">
                <i class="bi bi-arrow-repeat me-1"></i>Recours
            </span>
            <span class="pill" style="background:#f0f9ff;color:#0369a1;border:1px solid #bae6fd;font-size:.7rem">
                <i class="bi bi-shield-check me-1"></i>Exécution
            </span>
        </div>
    </div>
</div>
@endif

</div>{{-- /.cv-wrapper --}}
@endsection

@push('scripts')
<script>
function toggleDisp(id) {
    const el      = document.getElementById('disp-' + id);
    const fade    = document.getElementById('disp-fade-' + id);
    const chevron = document.getElementById('disp-chevron-' + id);
    if (el.classList.contains('expanded')) {
        el.classList.remove('expanded');
        if (fade) fade.style.display = '';
        if (chevron) { chevron.classList.remove('bi-chevron-up'); chevron.classList.add('bi-chevron-down'); }
    } else {
        el.classList.add('expanded');
        if (fade) fade.style.display = 'none';
        if (chevron) { chevron.classList.remove('bi-chevron-down'); chevron.classList.add('bi-chevron-up'); }
    }
}
</script>
@endpush