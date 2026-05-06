@extends('layouts.app')

@section('title', 'Modifier l\'avocat')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('avocats.index') }}">Avocats</a></li>
    <li class="breadcrumb-item"><a href="{{ route('avocats.show', $avocat) }}">{{ $avocat->nom_avocat }}</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning me-2"></i>Modifier l'avocat
        </h4>
        <p class="text-muted small mb-0">
            Mise à jour de <strong>{{ $avocat->nom_avocat }}</strong>
        </p>
    </div>
    <a href="{{ route('avocats.show', $avocat) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la fiche
    </a>
</div>

<form action="{{ route('avocats.update', $avocat) }}" method="POST">
@csrf @method('PUT')

<div class="row g-4">

    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-vcard me-2 text-warning"></i>Informations de l'avocat
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- Nom --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            Nom complet <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="nom_avocat"
                               class="form-control @error('nom_avocat') is-invalid @enderror"
                               value="{{ old('nom_avocat', $avocat->nom_avocat) }}"
                               required>
                        @error('nom_avocat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Téléphone --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            Téléphone <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-telephone text-muted"></i>
                            </span>
                            <input type="tel"
                                   name="telephone"
                                   class="form-control @error('telephone') is-invalid @enderror"
                                   value="{{ old('telephone', $avocat->telephone) }}"
                                   pattern="^(\+212|00212|0)(5|6|7)[0-9]{8}$"
                                   title="Format attendu : 0612345678 ou +212612345678"
                                   required>
                            @error('telephone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text">Format : 06XXXXXXXX ou +212XXXXXXXXX</div>
                    </div>

                    {{-- Email --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            Email <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-envelope text-muted"></i>
                            </span>
                            <input type="email"
                                   name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $avocat->email) }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
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
                    <dt class="col-6 text-muted fw-normal">Créé le</dt>
                    <dd class="col-6">{{ $avocat->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">Modifié le</dt>
                    <dd class="col-6">{{ $avocat->updated_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">Dossiers liés</dt>
                    <dd class="col-6">
                    @php
                    $nb = $avocat->dossierParties()->count();

                    $color = $nb > 0 ? 'info' : 'secondary';

                    $textColor = $nb > 0 ? 'text-white' : 'text-white';
                    @endphp     
                        <span class="badge bg-{{ $color }} bg-opacity-15 {{ $textColor }}">
                            {{ $nb }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

</div>

{{-- ── Actions ── --}}
<div class="d-flex gap-2 justify-content-end mt-4">
    <a href="{{ route('avocats.show', $avocat) }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>Annuler
    </a>
    <button type="submit" class="btn btn-warning px-4">
        <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
    </button>
</div>

</form>
@endsection