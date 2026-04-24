@extends('layouts.app')

@section('title', 'Modifier exécution')

@section('breadcrumb') <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li> <li class="breadcrumb-item"><a href="{{ route('executions.index') }}">Exécutions</a></li> <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')

<div class="row g-4">

```
{{-- ── COLONNE PRINCIPALE ── --}}
<div class="col-lg-8">

    <form action="{{ route('executions.update', $execution) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-pencil-square me-2 text-warning"></i>
                    Modifier exécution
                </h6>
            </div>

            <div class="card-body">

                {{-- Numéro (readonly) --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Numéro exécution</label>
                    <input type="text"
                           class="form-control form-control-sm"
                           value="{{ $execution->numero_dossier_execution }}"
                           readonly>
                </div>

                {{-- Jugement --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Jugement</label>

                    <input type="text"
                        class="form-control form-control-sm"
                        value="#{{ $execution->jugement->id }} —
                        {{ $execution->jugement->date_jugement->format('d/m/Y') }}
                        · {{ $execution->jugement->dossierTribunal->tribunal->nom_tribunal ?? '' }}"
                        readonly>
                </div>

                {{-- Date notification --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold small">
                        Date notification <span class="text-danger">*</span>
                    </label>

                    <input type="date"
                           name="date_notification"
                           class="form-control form-control-sm @error('date_notification') is-invalid @enderror"
                           value="{{ old('date_notification', $execution->date_notification?->format('Y-m-d')) }}"
                           required>

                    @error('date_notification')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Date exécution --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold small">
                        Date exécution
                    </label>

                    <input type="date"
                           name="date_execution"
                           class="form-control form-control-sm @error('date_execution') is-invalid @enderror"
                           value="{{ old('date_execution', optional($execution->date_execution)->format('Y-m-d')) }}">

                    @error('date_execution')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Statut --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold small">
                        Statut <span class="text-danger">*</span>
                    </label>

                    <select name="statut_execution"
                            class="form-select form-select-sm @error('statut_execution') is-invalid @enderror"
                            required>

                        @foreach($statuts as $s)
                            <option value="{{ $s->id }}"
                                {{ $execution->statut_execution == $s->id ? 'selected' : '' }}>
                                {{ $s->statut_execution }}
                            </option>
                        @endforeach

                    </select>

                    @error('statut_execution')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Responsable (readonly affiché) --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Responsable</label>
                    <input type="text"
                           class="form-control form-control-sm"
                           value="{{ $execution->responsable->name ?? '' }}"
                           readonly>
                </div>

                {{-- Observations --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Observations</label>
                    <textarea name="observations"
                              rows="3"
                              class="form-control form-control-sm"
                              placeholder="Notes internes...">{{ old('observations', $execution->observations) }}</textarea>
                </div>

            </div>
        </div>

        {{-- ACTIONS --}}
        <div class="d-flex justify-content-between">
            <a href="{{ route('executions.show', $execution) }}"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>

            <button type="submit"
                    class="btn btn-warning btn-sm"
                    onclick="return confirm('Confirmer la modification ?')">
                <i class="bi bi-check-circle me-1"></i>Mettre à jour
            </button>
        </div>

    </form>

</div>

{{-- ── SIDEBAR ── --}}
<div class="col-lg-4">

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-info-circle me-2 text-info"></i>
                Informations
            </h6>
        </div>

        <div class="card-body small text-muted">

            <p class="mb-2">
                Le numéro, le responsable et le statut sont générés automatiquement.
            </p>

            <p class="mb-2">
                Seules certaines informations peuvent être modifiées.
            </p>

            <p class="mb-0">
                Une exécution terminée ne doit plus être modifiée.
            </p>

        </div>
    </div>

</div>
```

</div>

@endsection
