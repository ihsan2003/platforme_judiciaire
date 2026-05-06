{{-- resources/views/notifications/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Notifications')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item active">Notifications</li>
@endsection

@section('content')

{{-- En-tête --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-bell me-2 text-primary"></i>Notifications
        </h4>
        <p class="text-muted small mb-0">Alertes et rappels de délais</p>
    </div>
    <div class="d-flex gap-2">
        {{-- Générer manuellement (debug / admin) --}}
        @can('manage users')
        <form method="POST" action="{{ route('notifications.generer') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-repeat me-1"></i>Actualiser
            </button>
        </form>
        @endcan

        @if($stats['non_lues'] > 0)
        <form method="POST" action="{{ route('notifications.tout-lire') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-check-all me-1"></i>Tout marquer comme lu
            </button>
        </form>
        @endif
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([
        ['label' => 'Non lues',  'value' => $stats['non_lues'], 'icon' => 'bell-fill',    'color' => 'primary'],
        ['label' => 'Urgentes',  'value' => $stats['danger'],   'icon' => 'exclamation-octagon', 'color' => 'danger'],
        ['label' => 'Attention', 'value' => $stats['warning'],  'icon' => 'exclamation-triangle','color' => 'warning'],
        ['label' => 'Total',     'value' => $stats['total'],    'icon' => 'bell',          'color' => 'secondary'],
    ] as $s)
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="rounded-circle bg-{{ $s['color'] }} bg-opacity-10 p-2 d-inline-flex mb-2">
                    <i class="bi bi-{{ $s['icon'] }} fs-5 text-{{ $s['color'] }}"></i>
                </div>
                <div class="fs-3 fw-bold lh-1 mb-1">{{ $s['value'] }}</div>
                <div class="text-muted small">{{ $s['label'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filtres --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-3 align-items-center flex-wrap">
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" name="non_lues" value="1"
                       id="filterNonLues"
                       {{ request()->boolean('non_lues') ? 'checked' : '' }}
                       onchange="this.form.submit()">
                <label class="form-check-label small" for="filterNonLues">Non lues seulement</label>
            </div>
            <select name="niveau" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <option value="">Tous les niveaux</option>
                <option value="danger"  {{ request('niveau') === 'danger'  ? 'selected' : '' }}>🔴 Urgent</option>
                <option value="warning" {{ request('niveau') === 'warning' ? 'selected' : '' }}>🟡 Attention</option>
                <option value="info"    {{ request('niveau') === 'info'    ? 'selected' : '' }}>🔵 Info</option>
            </select>
            @if(request()->hasAny(['non_lues', 'niveau']))
                <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Réinitialiser
                </a>
            @endif
        </form>
    </div>
</div>

{{-- Liste --}}
<div class="card border-0 shadow-sm">
    @forelse($notifications as $notif)
    <div class="notif-row d-flex align-items-start gap-3 px-4 py-3 border-bottom {{ !$notif->est_lue ? 'bg-notif-unread' : '' }}"
         id="notif-row-{{ $notif->id }}">

        {{-- Icône --}}
        <div class="notif-icon-lg flex-shrink-0 {{ $notif->couleur }}">
            <i class="bi {{ $notif->icone }}"></i>
        </div>

        {{-- Contenu --}}
        <div class="flex-grow-1 min-w-0">
            <div class="d-flex align-items-start justify-content-between gap-2">
                <div>
                    <span class="badge bg-{{ $notif->couleur }} bg-opacity-15 text-{{ $notif->couleur }} border border-{{ $notif->couleur }} border-opacity-25 mb-1"
                          style="font-size:.7rem;">
                        {{ $notif->categorie }}
                    </span>
                    <div class="fw-semibold" style="font-size:.9rem;">
                        {{ $notif->message }}
                    </div>
                    @if($notif->details)
                    <div class="text-muted small mt-1">{{ $notif->details }}</div>
                    @endif
                    <div class="text-muted" style="font-size:.75rem; margin-top:4px;">
                        <i class="bi bi-clock me-1"></i>{{ $notif->created_at->diffForHumans() }}
                        @if($notif->est_lue && $notif->date_lecture)
                            &nbsp;·&nbsp; <i class="bi bi-check2-all me-1 text-success"></i>Lu {{ $notif->date_lecture->diffForHumans() }}
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-1 flex-shrink-0">
                    @if($notif->url_action)
                    <form method="POST" action="{{ route('notifications.lire', $notif) }}">
                        @csrf
                        <button type="submit"
                                class="btn btn-sm btn-{{ $notif->couleur }} px-3"
                                style="font-size:.78rem;">
                            <i class="bi bi-arrow-right me-1"></i>Voir
                        </button>
                    </form>
                    @endif

                    @if(!$notif->est_lue)
                    <form method="POST" action="{{ route('notifications.lire', $notif) }}">
                        @csrf
                        <button type="submit"
                                class="btn btn-sm btn-outline-secondary px-2"
                                style="font-size:.78rem;"
                                title="Marquer comme lu">
                            <i class="bi bi-check2"></i>
                        </button>
                    </form>
                    @endif

                    <form method="POST" action="{{ route('notifications.destroy', $notif) }}">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="btn btn-sm btn-outline-danger px-2"
                                style="font-size:.78rem;"
                                title="Supprimer"
                                onclick="return confirm('Supprimer cette notification ?')">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Indicateur non lu --}}
        @if(!$notif->est_lue)
        <div class="flex-shrink-0 pt-2">
            <span class="dot-indicator bg-{{ $notif->couleur }}"></span>
        </div>
        @endif
    </div>
    @empty
    <div class="text-center py-5 text-muted">
        <i class="bi bi-bell-slash fs-1 d-block mb-3 opacity-25"></i>
        <p class="mb-0">Aucune notification{{ request()->hasAny(['non_lues', 'niveau']) ? ' correspondant aux filtres' : '' }}</p>
        @if(request()->hasAny(['non_lues', 'niveau']))
            <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary mt-2">
                Voir toutes
            </a>
        @endif
    </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($notifications->hasPages())
<div class="mt-3">
    {{ $notifications->links() }}
</div>
@endif

@endsection

@push('styles')
<style>
    .bg-notif-unread { background: #fffbf0 !important; }

    .notif-row { transition: background .15s; }
    .notif-row:hover { background: #f8f9ff !important; }
    .notif-row:last-child { border-bottom: none !important; }

    .notif-icon-lg {
        width: 42px; height: 42px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .notif-icon-lg.danger  { background: #fee2e2; color: #dc2626; }
    .notif-icon-lg.warning { background: #fef3c7; color: #d97706; }
    .notif-icon-lg.info    { background: #dbeafe; color: #2563eb; }
    .notif-icon-lg.secondary { background: #f3f4f6; color: #6b7280; }

    .dot-indicator {
        display: inline-block;
        width: 8px; height: 8px;
        border-radius: 50%;
    }
</style>
@endpush