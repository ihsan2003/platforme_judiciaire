{{-- resources/views/finances/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Ajouter une finance')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('finances.index') }}">Finances</a></li>
    <li class="breadcrumb-item active">Ajouter</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-plus-circle text-success me-2"></i>Nouvelle entrée financière
        </h4>
        <p class="text-muted small mb-0">Enregistrez les montants liés à un jugement définitif.</p>
    </div>
    <a href="{{ route('finances.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
</div>

<div class="row g-4">

    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">

        <form action="{{ route('finances.store') }}" method="POST" id="formFinance">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-cash-stack me-2 text-success"></i>Informations financières
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- Jugement --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            Jugement <span class="text-danger">*</span>
                        </label>
                        <select name="id_jugement"
                                class="form-select @error('id_jugement') is-invalid @enderror"
                                required>
                            <option value="">— Sélectionner un jugement —</option>
                            @foreach($jugements as $j)
                                <option value="{{ $j->id }}" @selected(old('id_jugement') == $j->id)>
                                    Jugement du {{ $j->date_jugement?->format('d/m/Y') ?? '—' }}
                                    · {{ $j->dossierTribunal?->dossier?->numero_dossier_interne ?? '—' }}
                                    · {{ $j->dossierTribunal?->tribunal?->nom_tribunal ?? '—' }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_jugement')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Montants réclamés --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Montant réclamé (demandeur)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0"
                                   name="montant_reclame_demandeur"
                                   class="form-control @error('montant_reclame_demandeur') is-invalid @enderror"
                                   value="{{ old('montant_reclame_demandeur') }}"
                                   placeholder="0.00">
                            <span class="input-group-text">DH</span>
                        </div>
                        @error('montant_reclame_demandeur')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Montant réclamé (défendeur)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0"
                                   name="montant_reclame_defendeur"
                                   class="form-control @error('montant_reclame_defendeur') is-invalid @enderror"
                                   value="{{ old('montant_reclame_defendeur') }}"
                                   placeholder="0.00">
                            <span class="input-group-text">DH</span>
                        </div>
                        @error('montant_reclame_defendeur')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Montant condamné --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            Montant condamné <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0"
                                   name="montant_condamne"
                                   class="form-control @error('montant_condamne') is-invalid @enderror"
                                   value="{{ old('montant_condamne') }}"
                                   placeholder="0.00"
                                   required>
                            <span class="input-group-text">DH</span>
                        </div>
                        @error('montant_condamne')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Montant payé --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Montant payé</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0"
                                   name="montant_paye"
                                   class="form-control @error('montant_paye') is-invalid @enderror"
                                   value="{{ old('montant_paye', 0) }}"
                                   placeholder="0.00">
                            <span class="input-group-text">DH</span>
                        </div>
                        @error('montant_paye')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Date paiement --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Date de paiement</label>
                        <input type="date" name="date_paiement"
                               class="form-control @error('date_paiement') is-invalid @enderror"
                               value="{{ old('date_paiement') }}">
                        @error('date_paiement')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('finances.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i>Annuler
            </a>
            <button type="submit" class="btn btn-success px-4">
                <i class="bi bi-check-lg me-1"></i>Enregistrer
            </button>
        </div>

        </form>
    </div>

    {{-- ── Colonne latérale ── --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-info"></i>À savoir
                </h6>
            </div>
            <div class="card-body small text-muted">
                <ul class="ps-3 mb-0">
                    <li class="mb-2">Le statut de paiement est calculé automatiquement selon les montants saisis.</li>
                    <li class="mb-2">
                        <strong>Complet</strong> si le montant payé ≥ montant condamné.
                    </li>
                    <li class="mb-2">
                        <strong>Partiel</strong> si une partie a été réglée.
                    </li>
                    <li>
                        <strong>En attente</strong> si aucun paiement n'a été effectué.
                    </li>
                </ul>
            </div>
        </div>
    </div>

</div>

@endsection