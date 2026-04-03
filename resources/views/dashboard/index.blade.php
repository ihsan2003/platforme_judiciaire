{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')

{{-- ══ TITRE ══ --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Tableau de bord</h4>
        <p class="text-muted small mb-0">{{ now()->translatedFormat('l d F Y') }}</p>
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
                            <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25">
                                J-{{ now()->diffInDays($audience->date_audience) }}
                            </span>
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
                        @endphp
                        <span class="badge bg-{{ $c }} bg-opacity-15 text-{{ $c }} border border-{{ $c }} border-opacity-25">
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
