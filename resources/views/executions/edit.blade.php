@extends('layouts.app')

@section('title', 'Modifier exécution')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('executions.index') }}">Exécutions</a></li>
    <li class="breadcrumb-item"><a href="{{ route('executions.show', $execution) }}">Exécution</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')

{{-- HEADER --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning me-2"></i>
            Modifier l’exécution
        </h4>
        <p class="text-muted small mb-0">
            {{ $execution->numero_dossier_execution }}
            — {{ $execution->jugement->dossierTribunal->tribunal->nom_tribunal ?? '—' }}
        </p>
    </div>

    <a href="{{ route('executions.show', $execution) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la fiche
    </a>
</div>

<form action="{{ route('executions.update', $execution) }}" method="POST">
@csrf
@method('PUT')

<div class="row g-4">

    {{-- ── COLONNE PRINCIPALE ── --}}
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-shield me-2 text-warning"></i>
                    Informations de l’exécution
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    {{-- Numéro --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Numéro</label>
                        <div class="form-control bg-light text-muted">
                            {{ $execution->numero_dossier_execution }}
                        </div>
                    </div>

                    {{-- Jugement --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Jugement</label>
                        <div class="form-control bg-light text-muted">
                            #{{ $execution->jugement->id }}
                            · {{ $execution->jugement->date_jugement->format('d/m/Y') }}
                            · {{ $execution->jugement->dossierTribunal->tribunal->nom_tribunal ?? '—' }}
                        </div>
                    </div>

                    {{-- Date notification --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            Date notification <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                               name="date_notification"
                               class="form-control @error('date_notification') is-invalid @enderror"
                               value="{{ old('date_notification', $execution->date_notification?->format('Y-m-d')) }}"
                               required>
                        @error('date_notification')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Date exécution --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Date exécution</label>
                        <input type="date"
                               name="date_execution"
                               class="form-control @error('date_execution') is-invalid @enderror"
                               value="{{ old('date_execution', optional($execution->date_execution)->format('Y-m-d')) }}">
                        @error('date_execution')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Statut --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            Statut <span class="text-danger">*</span>
                        </label>
                        <select name="statut_execution"
                                class="form-select @error('statut_execution') is-invalid @enderror"
                                required>
                            @foreach($statuts as $s)
                                <option value="{{ $s->id }}"
                                    @selected($execution->statut_execution == $s->id)>
                                    {{ $s->statut_execution }}
                                </option>
                            @endforeach
                        </select>
                        @error('statut_execution')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Responsable --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Responsable</label>
                        <div class="form-control bg-light text-muted">
                            {{ $execution->responsable->name ?? '—' }}
                        </div>
                    </div>

                    {{-- Observations --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Observations</label>
                        <textarea name="observations"
                                  rows="4"
                                  class="form-control"
                                  placeholder="Notes internes...">{{ old('observations', $execution->observations) }}</textarea>
                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- ── SIDEBAR ── --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-muted"></i>Résumé
                </h6>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-6 text-muted fw-normal">Statut</dt>
                    <dd class="col-6">{{ $execution->statut?->statut_execution ?? '—' }}</dd>

                    <dt class="col-6 text-muted fw-normal">Notification</dt>
                    <dd class="col-6">{{ $execution->date_notification?->format('d/m/Y') ?? '—' }}</dd>

                    <dt class="col-6 text-muted fw-normal">Exécution</dt>
                    <dd class="col-6">
                        {{ $execution->date_execution?->format('d/m/Y') ?? 'En attente' }}
                    </dd>

                    <dt class="col-6 text-muted fw-normal">Créé le</dt>
                    <dd class="col-6">{{ $execution->created_at->format('d/m/Y') }}</dd>
                </dl>
            </div>
        </div>

        @if($execution->date_execution)
            <div class="alert alert-success border-0 small">
                <i class="bi bi-check-circle me-2"></i>
                Cette exécution est terminée.
            </div>
        @endif

    </div>

</div>

{{-- ACTIONS --}}
<div class="d-flex gap-2 justify-content-end mt-2">
    <a href="{{ route('executions.show', $execution) }}" class="btn btn-outline-secondary">
        Annuler
    </a>
    <button type="submit" class="btn btn-warning px-4">
        <i class="bi bi-check-lg me-1"></i>Enregistrer
    </button>
</div>

</form>

@endsection