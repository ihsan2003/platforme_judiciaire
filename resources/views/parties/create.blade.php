@extends('layouts.app')

@section('title', 'Nouvelle partie')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('parties.index') }}">Parties</a></li>
    <li class="breadcrumb-item active">Nouvelle partie</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-person-plus text-primary me-2"></i>Nouvelle partie
        </h4>
        <p class="text-muted small mb-0">Enregistrez les informations d'une nouvelle partie.</p>
    </div>
    <a href="{{ route('parties.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
</div>

<form action="{{ route('parties.store') }}" method="POST">
@csrf

<div class="row g-4">

    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-vcard me-2 text-primary"></i>Informations de la partie
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
                               value="{{ old('nom_partie') }}"
                               placeholder="Nom complet ou raison sociale"
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
                            <option value="Physique" @selected(old('type_personne') === 'Physique')>Physique</option>
                            <option value="Morale"   @selected(old('type_personne') === 'Morale')>Morale</option>
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
                                   value="{{ old('identifiant_unique') }}"
                                   placeholder="CIN, RC, CNSS…"
                                   required>
                        </div>
                        @error('identifiant_unique')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="form-text">CIN pour les personnes physiques, RC pour les personnes morales.</div>
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
                                   value="{{ old('telephone') }}"
                                   placeholder="0612345678"
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
                                   value="{{ old('email') }}"
                                   placeholder="contact@exemple.ma">
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
                                  rows="2"
                                  placeholder="Adresse postale complète">{{ old('adresse') }}</textarea>
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
                    <i class="bi bi-info-circle me-2 text-muted"></i>À savoir
                </h6>
            </div>
            <div class="card-body small text-muted">
                <ul class="mb-0 ps-3">
                    <li class="mb-2">L'identifiant unique doit être différent pour chaque partie.</li>
                    <li class="mb-2">Le téléphone doit être au format marocain.</li>
                    <li>La partie pourra ensuite être liée à des dossiers judiciaires.</li>
                </ul>
            </div>
        </div>
    </div>

</div>

{{-- ── Actions ── --}}
<div class="d-flex gap-2 justify-content-end mt-4">
    <a href="{{ route('parties.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>Annuler
    </a>
    <button type="submit" class="btn btn-primary px-4">
        <i class="bi bi-check-lg me-1"></i>Enregistrer
    </button>
</div>

</form>
@endsection