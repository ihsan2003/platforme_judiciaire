@extends('layouts.app')

@section('title', 'Nouvelle exécution')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('executions.index') }}">Exécutions</a></li>
    <li class="breadcrumb-item active">Créer</li>
@endsection

@section('content')

<div class="row g-4">

    {{-- ── COLONNE PRINCIPALE ── --}}
    <div class="col-lg-8">

        <form action="{{ route('executions.store') }}" method="POST">
            @csrf

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-shield-check me-2 text-primary"></i>
                        Nouvelle exécution
                    </h6>
                </div>

                <div class="card-body">

                    {{-- Jugement --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Jugement <span class="text-danger">*</span>
                        </label>
                        <select name="id_jugement"
                                class="form-select form-select-sm @error('id_jugement') is-invalid @enderror"
                                required>

                            <option value="">— Sélectionner un jugement —</option>

                            @foreach($jugements as $j)
                                <option value="{{ $j->id }}">
                                    #{{ $j->id }} —
                                    {{ $j->date_jugement->format('d/m/Y') }}
                                    · {{ $j->dossierTribunal->tribunal->nom_tribunal ?? '' }}
                                </option>
                            @endforeach

                        </select>

                        @error('id_jugement')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                    {{-- Date notification --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Date notification <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                               name="date_notification"
                               class="form-control form-control-sm @error('date_notification') is-invalid @enderror"
                               value="{{ date('Y-m-d') }}"
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
                               class="form-control form-control-sm @error('date_execution') is-invalid @enderror">

                        @error('date_execution')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Observations --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Observations</label>
                        <textarea name="observations"
                                  rows="3"
                                  class="form-control form-control-sm"
                                  placeholder="Notes internes..."></textarea>
                    </div>

                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="d-flex justify-content-between">
                <a href="{{ route('executions.index') }}"
                   class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>

                <button type="submit"
                        class="btn btn-primary btn-sm"
                        onclick="return confirm('Confirmer la création de l\'exécution ?')">
                    <i class="bi bi-check-circle me-1"></i>Enregistrer
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
                    Seuls les jugements <strong>définitifs</strong> peuvent être exécutés.
                </p>

                <p class="mb-2">
                    Une seule exécution est autorisée par jugement.
                </p>

                <p class="mb-0">
                    Le statut peut évoluer : En cours → Terminée.
                </p>

            </div>
        </div>

    </div>

</div>

@endsection