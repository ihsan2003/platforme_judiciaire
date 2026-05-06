@extends('layouts.app')

@section('title', 'Modifier la partie')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('parties.index') }}">Parties</a></li>
    <li class="breadcrumb-item"><a href="{{ route('parties.show', $partie) }}">{{ $partie->nom_partie }}</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning me-2"></i>Modifier la partie
        </h4>
        <p class="text-muted small mb-0">Mise à jour de <strong>{{ $partie->nom_partie }}</strong></p>
    </div>
    <a href="{{ route('parties.show', $partie) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la fiche
    </a>
</div>

<form action="{{ route('parties.update', $partie) }}" method="POST">
@csrf @method('PUT')

<div class="row g-4">

    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-vcard me-2 text-warning"></i>Informations de la partie
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- Nom --}}
                    <div class="col-sm-8">
                        <label class="form-label fw-semibold small">
                            Nom / Dénomination <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="nom_partie"
                               class="form-control @error('nom_partie') is-invalid @enderror"
                               value="{{ old('nom_partie', $partie->nom_partie) }}"
                               required>
                        @error('nom_partie')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Type de personne --}}
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold small">
                            Type <span class="text-danger">*</span>
                        </label>
                        <select name="type_personne"
                                class="form-select @error('type_personne') is-invalid @enderror"
                                required>
                            <option value="">— Sélectionner —</option>
                            <option value="Physique" @selected(old('type_personne', $partie->type_personne) === 'Physique')>Physique</option>
                            <option value="Morale"   @selected(old('type_personne', $partie->type_personne) === 'Morale')>Morale</option>
                        </select>
                        @error('type_personne')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Identifiant unique --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            Identifiant unique <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-fingerprint text-muted"></i>
                            </span>
                            <input type="text"
                                   name="identifiant_unique"
                                   class="form-control @error('identifiant_unique') is-invalid @enderror"
                                   value="{{ old('identifiant_unique', $partie->identifiant_unique) }}"
                                   required>
                        </div>
                        @error('identifiant_unique')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Téléphone --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Téléphone</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-telephone text-muted"></i>
                            </span>
                            <input type="tel"
                                   name="telephone"
                                   class="form-control @error('telephone') is-invalid @enderror"
                                   value="{{ old('telephone', $partie->telephone) }}"
                                   pattern="^(\+212|00212|0)(5|6|7)[0-9]{8}$"
                                   title="Format attendu : 0612345678 ou +212612345678">
                            @error('telephone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text">Format : 06XXXXXXXX ou +212XXXXXXXXX</div>
                    </div>

                    {{-- Email --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-envelope text-muted"></i>
                            </span>
                            <input type="email"
                                   name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $partie->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Adresse --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Adresse</label>
                        <textarea name="adresse"
                                  class="form-control @error('adresse') is-invalid @enderror"
                                  rows="2">{{ old('adresse', $partie->adresse) }}</textarea>
                        @error('adresse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ── Colonne latérale ── --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-muted"></i>Résumé
                </h6>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-6 text-muted fw-normal">Identifiant</dt>
                    <dd class="col-6 font-monospace">{{ $partie->identifiant_unique }}</dd>

                    <dt class="col-6 text-muted fw-normal">Créée le</dt>
                    <dd class="col-6">{{ $partie->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">Modifiée le</dt>
                    <dd class="col-6">{{ $partie->updated_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">Dossiers</dt>
                    <dd class="col-6">
                        <span class="badge bg-info bg-opacity-15 text-white border border-info border-opacity-25">
                            {{ $partie->dossiers()->count() }} dossier(s)
                        </span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

</div>

{{-- ── Actions ── --}}
<div class="d-flex gap-2 justify-content-end mt-4">
    <a href="{{ route('parties.show', $partie) }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>Annuler
    </a>
    <button type="submit" class="btn btn-warning px-4">
        <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
    </button>
</div>

</form>
@endsection