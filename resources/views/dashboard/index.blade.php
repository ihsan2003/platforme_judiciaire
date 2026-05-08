{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')

{{-- ══ TITRE ══ --}}
<div class="card border-0 shadow-sm mb-4 overflow-hidden">

    <div
        class="position-relative"
        style="
            background-image: url('{{ asset('images/dashboard-bg.jpg') }}');
            background-size: cover;
            background-position: center;
            height: 220px;
        "
    >

        {{-- Contenu --}}
        <div
            class="position-relative h-100 d-flex align-items-center px-3 text-white"
            style="z-index:2;"
        >
            <div>
                <h1 class="fw-bold mb-2">Tableau de bord</h1>

                <p class="mb-0 fs-5 opacity-75">
                    {{ now()->translatedFormat('l d F Y') }}
                </p>
            </div>
        </div>

    </div>

</div>


{{-- ══ DOSSIERS ══ --}}
<h6 class="text-uppercase text-muted small fw-semibold mb-3 letter-spacing-1">
    <i class="bi bi-folder2 me-2"></i>Dossiers judiciaires
</h6>
<div class="row g-3 mb-4">
    @foreach([
        ['label' => 'Total',      'value' => $dossiers['total'],    'icon' => 'folder2-open',  'color' => 'primary'],
        ['label' => 'Actifs',     'value' => $dossiers['actifs'],   'icon' => 'activity',      'color' => 'success'],
        ['label' => 'En cours',   'value' => $dossiers['en_cours'], 'icon' => 'hourglass-split','color' => 'warning'],
        ['label' => 'Jugés',      'value' => $dossiers['juges'],    'icon' => 'journal-text',  'color' => 'info'],
        ['label' => 'Exécutés',   'value' => $dossiers['executes'], 'icon' => 'shield-check',  'color' => 'secondary'],
        ['label' => 'Ce mois',    'value' => $dossiers['ce_mois'],  'icon' => 'calendar-plus', 'color' => 'primary'],
    ] as $stat)
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="rounded-circle bg-{{ $stat['color'] }} bg-opacity-10 p-2 d-inline-flex mb-2">
                    <i class="bi bi-{{ $stat['icon'] }} fs-5 text-{{ $stat['color'] }}"></i>
                </div>
                <div class="fs-3 fw-bold lh-1 mb-1">{{ $stat['value'] }}</div>
                <div class="text-muted small">{{ $stat['label'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ══ RÉCLAMATIONS ══ --}}
<h6 class="text-uppercase text-muted small fw-semibold mb-3 letter-spacing-1">
    <i class="bi bi-chat-left-text me-2"></i>Réclamations
</h6>
<div class="row g-3 mb-4">
    @foreach([
        ['label' => 'Total',      'value' => $reclamations['total'],     'icon' => 'chat-left-text', 'color' => 'primary'],
        ['label' => 'Reçues',     'value' => $reclamations['recues'],    'icon' => 'inbox',          'color' => 'info'],
        ['label' => 'En cours',   'value' => $reclamations['en_cours'],  'icon' => 'arrow-repeat',   'color' => 'warning'],
        ['label' => 'Clôturées',  'value' => $reclamations['cloturees'], 'icon' => 'check-circle',   'color' => 'success'],
    ] as $stat)
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="rounded-circle bg-{{ $stat['color'] }} bg-opacity-10 p-2 d-inline-flex mb-2">
                    <i class="bi bi-{{ $stat['icon'] }} fs-5 text-{{ $stat['color'] }}"></i>
                </div>
                <div class="fs-3 fw-bold lh-1 mb-1">{{ $stat['value'] }}</div>
                <div class="text-muted small">{{ $stat['label'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ══ DEUX COLONNES : ALERTES + AGENDA ══ --}}
<div class="row g-4">

    {{-- Alertes --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>Alertes
                </h6>
            </div>
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="small">
                        <i class="bi bi-calendar-event text-primary me-2"></i>Audiences (7j)
                    </span>
                    <span class="badge bg-primary rounded-pill">{{ $alertes['audiences_proches'] }}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="small">
                        <i class="bi bi-clock text-warning me-2"></i>Jugements non définitifs
                    </span>
                    <span class="badge bg-warning text-dark rounded-pill">{{ $alertes['jugements_non_definitifs'] }}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="small">
                        <i class="bi bi-chat-dots text-danger me-2"></i>Réclamations en attente
                    </span>
                    <span class="badge bg-danger rounded-pill">{{ $alertes['reclamations_en_attente'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Agenda audiences ══ --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-calendar-week text-primary me-2"></i>Prochaines audiences (7 jours)
                </h6>
                <a href="{{ route('audiences.index') }}?periode=semaine" class="btn btn-sm btn-outline-primary">
                    Voir tout
                </a>
            </div>
            <div class="card-body p-0">
                @forelse($audiencesAVenir as $audience)
                <div class="d-flex align-items-start gap-3 px-3 py-2 border-bottom">
                    {{-- Badge date --}}
                    <div class="text-center flex-shrink-0" style="min-width:48px">
                        <div class="bg-primary text-white rounded-top small fw-bold px-1">
                            {{ $audience->date_audience->format('M') }}
                        </div>
                        <div class="border border-top-0 rounded-bottom fw-bold fs-5 lh-1 px-1">
                            {{ $audience->date_audience->format('d') }}
                        </div>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-truncate">
                            @if($audience->dossierTribunal?->dossier)
                                <a href="{{ route('dossiers.show', $audience->dossierTribunal->dossier) }}"
                                   class="text-decoration-none text-primary">
                                    {{ $audience->dossierTribunal->dossier->numero_dossier_interne }}
                                </a>
                            @else
                                <span class="text-muted">Dossier inconnu</span>
                            @endif
                        </div>
                        <div class="text-muted small text-truncate">
                            <i class="bi bi-bank me-1"></i>{{ $audience->dossierTribunal?->tribunal?->nom_tribunal ?? '?' }}
                            &nbsp;·&nbsp;
                            <i class="bi bi-person me-1"></i>{{ $audience->juge?->nom_complet ?? '—' }}
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        @if($audience->est_today)
                            <span class="badge bg-warning text-dark">Aujourd'hui</span>
                        @else
                            <span class="badge bg-success bg-opacity-15 text-white border border-success border-opacity-25">
                                {{ now()->startOfDay()->diffInDays($audience->date_audience->startOfDay()) }} J                         </span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-calendar-check fs-1 d-block mb-2 opacity-25"></i>
                    Aucune audience dans les 7 prochains jours
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

<div class="row g-4 mt-2">
 
    {{-- ── Donut : Dossiers par statut ── --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-pie-chart text-primary me-2"></i>Dossiers par statut
                </h6>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center py-3">
                <div style="position:relative; width:200px; height:200px;">
                    <canvas id="chartDossiersStatut"></canvas>
                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none;">
                        <div class="fs-4 fw-bold lh-1">{{ $dossiers['total'] }}</div>
                        <div class="text-muted" style="font-size:.7rem;">Total</div>
                    </div>
                </div>
                <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                    <span class="badge" style="background:#e8f4fd;color:#1a6dab;font-size:.72rem;">En cours : {{ $dossiers['en_cours'] }}</span>
                    <span class="badge" style="background:#e8f7ee;color:#1a6b3a;font-size:.72rem;">Jugés : {{ $dossiers['juges'] }}</span>
                    <span class="badge" style="background:#fff3cd;color:#856404;font-size:.72rem;">Exécutés : {{ $dossiers['executes'] }}</span>
                    <span class="badge" style="background:#f0f0f0;color:#555;font-size:.72rem;">Autres : {{ $dossiers['total'] - $dossiers['en_cours'] - $dossiers['juges'] - $dossiers['executes'] }}</span>
                </div>
            </div>
        </div>
    </div>
 
    {{-- ── Barres horizontales : Réclamations par statut ── --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-bar-chart-horizontal text-warning me-2"></i>Réclamations par statut
                </h6>
            </div>
            <div class="card-body py-3" style="position:relative; height:260px;">
                <canvas id="chartReclamations"></canvas>
            </div>
        </div>
    </div>
 
</div>
 
{{-- ── Ligne : Dossiers ouverts par mois (12 derniers mois) ── --}}
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-graph-up text-success me-2"></i>Évolution des dossiers — 12 derniers mois
        </h6>
    </div>
    <div class="card-body py-3" style="position:relative; height:280px;">
        <canvas id="chartEvolution"></canvas>
    </div>
</div>

<div class="row g-4 mt-2">

    {{-- ── 1. Dossiers par type d'affaire ── --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-diagram-3 text-primary me-2"></i>Dossiers par type d'affaire
                </h6>
            </div>
            <div class="card-body" style="height:300px;">
                <canvas id="chartAffaires"></canvas>
            </div>
        </div>
    </div>

    {{-- ── 2. Jugements pour / contre ── --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-balance-scale text-success me-2"></i>Résultats jugements
                </h6>
            </div>
            <div class="card-body d-flex justify-content-center align-items-center">
                <canvas id="chartPourContre"></canvas>
            </div>
        </div>
    </div>

    {{-- ── 3. Finances ── --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-cash-stack text-warning me-2"></i>Montants
                </h6>
            </div>
            <div class="card-body d-flex justify-content-center align-items-center">
                <canvas id="chartFinances"></canvas>
            </div>
        </div>
    </div>

</div>

{{-- ── 4. Évolution financière mensuelle ── --}}
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-graph-up-arrow text-success me-2"></i>Évolution financière (12 mois)
        </h6>
    </div>
    <div class="card-body" style="height:300px;">
        <canvas id="chartFinancesMensuel"></canvas>
    </div>
</div>

{{-- ══ DERNIERS DOSSIERS ══ --}}
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-clock-history text-primary me-2"></i>Derniers dossiers créés
        </h6>
        <a href="{{ route('dossiers.index') }}" class="btn btn-sm btn-outline-primary">Voir tous</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3 text-muted small fw-semibold">N° Interne</th>
                    <th class="text-muted small fw-semibold">Type d'affaire</th>
                    <th class="text-muted small fw-semibold">Tribunal</th>
                    <th class="text-muted small fw-semibold">Statut</th>
                    <th class="text-muted small fw-semibold">Ouverture</th>
                </tr>
            </thead>
            <tbody>
                @foreach($derniersDossiers as $dossier)
                <tr>
                    <td class="ps-3">
                        <a href="{{ route('dossiers.show', $dossier) }}"
                           class="fw-semibold text-decoration-none text-primary">
                            {{ $dossier->numero_dossier_interne }}
                        </a>
                    </td>
                    <td class="text-muted small">{{ $dossier->typeAffaire?->affaire ?? '—' }}</td>
                    <td class="text-muted small">
                        @foreach($dossier->dossierTribunaux as $dt)
                            <span class="badge bg-secondary bg-opacity-10 text-secondary me-1">
                                {{ $dt->tribunal?->nom_tribunal ?? '?' }}
                            </span>
                        @endforeach
                    </td>
                    <td>
                        @php
                            $s = $dossier->statut?->statut_dossier ?? '—';

                            $c = match(true) {
                                str_contains($s, 'cours')   => 'warning',
                                str_contains($s, 'Clôturé') => 'secondary',
                                str_contains($s, 'Jugé')    => 'success',
                                default                     => 'primary',
                            };

                            // Noir seulement pour warning, sinon blanc
                            $textClass = $c === 'warning' ? 'text-dark' : 'text-white';
                        @endphp

                        <span class="badge bg-{{ $c }} {{ $textClass }}">
                            {{ $s }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $dossier->date_ouverture?->format('d/m/Y') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

{{-- ══ SCRIPTS GRAPHIQUES ══ --}}
{{-- À insérer dans resources/views/dashboard/index.blade.php, à la FIN du fichier --}}
 
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
 
    /* ─── Données depuis Blade ─────────────────────────────────────── */
    const dossiersData = {
        enCours  : {{ $dossiers['en_cours'] }},
        juges    : {{ $dossiers['juges'] }},
        executes : {{ $dossiers['executes'] }},
        autres   : Math.max(0, {{ $dossiers['total'] }} - {{ $dossiers['en_cours'] }} - {{ $dossiers['juges'] }} - {{ $dossiers['executes'] }}),
        total    : {{ $dossiers['total'] }},
    };
 
    const reclData = {
        recues    : {{ $reclamations['recues'] }},
        enCours   : {{ $reclamations['en_cours'] }},
        cloturees : {{ $reclamations['cloturees'] }},
        enAttente : {{ $reclamations['en_attente'] }},
    };
 
 
    /* ─── Données mensuelles (dossiers ouverts par mois) ──────────── */
    /* Ces données doivent être passées depuis le contrôleur.           */
    /* Voir DashboardController.php — section "Évolution mensuelle"     */
    const evolutionLabels = {!! json_encode($evolutionMois['labels'] ?? []) !!};
    const evolutionValues = {!! json_encode($evolutionMois['values'] ?? []) !!};
 
    const affairesData = {
        labels: {!! json_encode($dossiersParAffaire['labels']) !!},
        values: {!! json_encode($dossiersParAffaire['values']) !!},
    };

    const pourContreData = {
        pour: {{ $resultatsJugements['pour'] }},
        contre: {{ $resultatsJugements['contre'] }},
    };

    const financesData = {
        pour: {{ $statsFinancesGraphe['montant_pour'] }},
        contre: {{ $statsFinancesGraphe['montant_contre'] }},
        total: {{ $statsFinancesGraphe['montant_total'] }},
    };

    const financesMensuel = {
        labels: {!! json_encode($statsFinancesGraphe['mensuel_labels']) !!},
        values: {!! json_encode($statsFinancesGraphe['mensuel_values']) !!}
    };
    /* ─── Couleurs ─────────────────────────────────────────────────── */
    const COLORS = {
        blue    : '#378ADD',
        green   : '#639922',
        amber   : '#BA7517',
        gray    : '#888780',
        pink    : '#D4537E',
        teal    : '#1D9E75',
        red     : '#E24B4A',
        purple  : '#7F77DD',
        blueFill: 'rgba(55,138,221,0.15)',
    };
 
    /* ═══════════════════════════════════════════════════════════════ */
    /* 1. DONUT — Dossiers par statut                                  */
    /* ═══════════════════════════════════════════════════════════════ */
    new Chart(document.getElementById('chartDossiersStatut'), {
        type: 'doughnut',
        data: {
            labels: ['En cours', 'Jugés', 'Exécutés', 'Autres'],
            datasets: [{
                data: [
                    dossiersData.enCours,
                    dossiersData.juges,
                    dossiersData.executes,
                    dossiersData.autres,
                ],
                backgroundColor: [COLORS.blue, COLORS.green, COLORS.amber, COLORS.gray],
                borderWidth: 0,
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label} : ${ctx.raw} (${Math.round(ctx.raw / dossiersData.total * 100)}%)`,
                    },
                },
            },
        },
    });
 
    /* ═══════════════════════════════════════════════════════════════ */
    /* 2. BARRES HORIZONTALES — Réclamations par statut               */
    /* ═══════════════════════════════════════════════════════════════ */
    new Chart(document.getElementById('chartReclamations'), {
        type: 'bar',
        data: {
            labels: ['Reçues', 'En cours', 'Clôturées', 'En attente'],
            datasets: [{
                label: 'Réclamations',
                data: [
                    reclData.recues,
                    reclData.enCours,
                    reclData.cloturees,
                    reclData.enAttente,
                ],
                backgroundColor: [COLORS.blue, COLORS.amber, COLORS.green, COLORS.red],
                borderRadius: 6,
                borderSkipped: false,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: ctx => ` ${ctx.raw} réclamation(s)` },
                },
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { precision: 0, font: { size: 11 } },
                    grid: { color: 'rgba(0,0,0,0.05)' },
                },
                y: {
                    ticks: { font: { size: 12 } },
                    grid: { display: false },
                },
            },
        },
    });
 

 
    /* ═══════════════════════════════════════════════════════════════ */
    /* 4. LIGNE — Évolution mensuelle des dossiers ouverts            */
    /* ═══════════════════════════════════════════════════════════════ */
    if (evolutionLabels.length > 0) {
        new Chart(document.getElementById('chartEvolution'), {
            type: 'line',
            data: {
                labels: evolutionLabels,
                datasets: [{
                    label: 'Dossiers ouverts',
                    data: evolutionValues,
                    borderColor: COLORS.blue,
                    backgroundColor: COLORS.blueFill,
                    borderWidth: 2,
                    pointBackgroundColor: COLORS.blue,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.35,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: ctx => ` ${ctx.raw} dossier(s) ouverts` },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, font: { size: 11 } },
                        grid: { color: 'rgba(0,0,0,0.05)' },
                    },
                    x: {
                        ticks: {
                            font: { size: 11 },
                            autoSkip: false,
                            maxRotation: 30,
                        },
                        grid: { display: false },
                    },
                },
            },
        });
    }

    new Chart(document.getElementById('chartAffaires'), {
        type: 'bar',
        data: {
            labels: affairesData.labels,
            datasets: [{
                data: affairesData.values,
                backgroundColor: COLORS.blue,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    new Chart(document.getElementById('chartPourContre'), {
        type: 'doughnut',
        data: {
            labels: ['Pour', 'Contre'],
            datasets: [{
                data: [pourContreData.pour, pourContreData.contre],
                backgroundColor: [COLORS.green, COLORS.red],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '70%',
            plugins: { legend: { position: 'bottom' } }
        }
    });

    new Chart(document.getElementById('chartFinances'), {
        type: 'pie',
        data: {
            labels: ['Pour établissement', 'Contre établissement'],
            datasets: [{
                data: [financesData.pour, financesData.contre],
                backgroundColor: [COLORS.teal, COLORS.red],
            }]
        },
        options: {
            plugins: {
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.raw.toLocaleString()} DH`
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('chartFinancesMensuel'), {
        type: 'line',
        data: {
            labels: financesMensuel.labels,
            datasets: [{
                data: financesMensuel.values,
                borderColor: COLORS.teal,
                backgroundColor: 'rgba(29,158,117,0.15)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
 
})();
</script>
@endpush
