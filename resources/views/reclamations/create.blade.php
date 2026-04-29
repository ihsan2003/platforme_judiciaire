{{-- resources/views/reclamations/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nouvelle réclamation')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reclamations.index') }}">Réclamations</a></li>
    <li class="breadcrumb-item active">Nouvelle</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-chat-left-text text-primary me-2"></i>Nouvelle réclamation
        </h4>
        <p class="text-muted small mb-0">Enregistrez les informations du réclamant et de sa réclamation.</p>
    </div>
    <a href="{{ route('reclamations.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
</div>

<form action="{{ route('reclamations.store') }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="row g-4">

    {{-- ══ COLONNE GAUCHE ══ --}}
    <div class="col-lg-7">

        {{-- Réclamant --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person me-2 text-primary"></i>Informations du réclamant
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-8">
                        <label class="form-label fw-semibold small">
                            Nom / Dénomination <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nom_reclamant"
                               class="form-control @error('nom_reclamant') is-invalid @enderror"
                               value="{{ old('nom_reclamant') }}"
                               placeholder="Nom complet ou raison sociale" required>
                        @error('nom_reclamant')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold small">
                            Type de réclamant <span class="text-danger">*</span>
                        </label>
                        <select name="id_type_reclamant"
                                class="form-select @error('id_type_reclamant') is-invalid @enderror" required>
                            <option value="">— Sélectionner —</option>
                            @foreach($typesReclamant as $type)
                                <option value="{{ $type->id }}" @selected(old('id_type_reclamant') == $type->id)>
                                    {{ $type->type_reclamant }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_type_reclamant')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Téléphone</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone text-muted"></i></span>
                            <input type="tel" name="telephone_reclamant"
                                   class="form-control @error('telephone_reclamant') is-invalid @enderror"
                                   value="{{ old('telephone_reclamant') }}"
                                   placeholder="0612345678">
                        </div>
                        @error('telephone_reclamant')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope text-muted"></i></span>
                            <input type="email" name="email_reclamant"
                                   class="form-control @error('email_reclamant') is-invalid @enderror"
                                   value="{{ old('email_reclamant') }}"
                                   placeholder="contact@example.com">
                        </div>
                        @error('email_reclamant')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Adresse</label>
                        <textarea name="adresse_reclamant"
                                  class="form-control @error('adresse_reclamant') is-invalid @enderror"
                                  rows="2"
                                  placeholder="Adresse postale complète">{{ old('adresse_reclamant') }}</textarea>
                        @error('adresse_reclamant')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Réclamation --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-chat-left-dots me-2 text-primary"></i>Détails de la réclamation
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-8">
                        <label class="form-label fw-semibold small">
                            Objet <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="objet"
                               class="form-control @error('objet') is-invalid @enderror"
                               value="{{ old('objet') }}"
                               placeholder="Résumé bref de la réclamation" required>
                        @error('objet')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold small">
                            Date de réception <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="date_reception"
                               class="form-control @error('date_reception') is-invalid @enderror"
                               value="{{ old('date_reception', date('Y-m-d')) }}" required>
                        @error('date_reception')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Description détaillée</label>
                        <textarea name="details"
                                  class="form-control @error('details') is-invalid @enderror"
                                  rows="5"
                                  placeholder="Décrivez la réclamation en détail…">{{ old('details') }}</textarea>
                        @error('details')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ══ COLONNE DROITE ══ --}}
    <div class="col-lg-5">

        {{-- Statut & Document --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-sliders me-2 text-primary"></i>Paramètres
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Statut initial</label>
                    <select name="id_statut_reclamation"
                            class="form-select @error('id_statut_reclamation') is-invalid @enderror">
                        <option value="">— Statut par défaut (Reçue) —</option>
                        @foreach($statuts as $statut)
                            <option value="{{ $statut->id }}"
                                    @selected(old('id_statut_reclamation') == $statut->id)>
                                {{ $statut->statut_reclamation }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_statut_reclamation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Si non renseigné, le statut sera « Reçue » par défaut.</div>
                </div>

                <hr>

                <div class="mb-0">
                    <label class="form-label fw-semibold small">Document joint (optionnel)</label>
                    <input type="file" name="document"
                           class="form-control @error('document') is-invalid @enderror"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    @error('document')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">PDF, Word, Excel, images — max 10 Mo</div>
                </div>
            </div>
        </div>

        {{-- Aide --}}
        <div class="alert alert-light border shadow-sm small">
            <div class="fw-semibold mb-1">
                <i class="bi bi-info-circle text-primary me-1"></i>À savoir
            </div>
            <ul class="mb-0 ps-3">
                <li>Si le réclamant existe déjà (même nom + type), ses coordonnées seront mises à jour.</li>
                <li>Vous pourrez ajouter des actions de suivi depuis la fiche de la réclamation.</li>
                <li>Le statut peut être modifié à tout moment.</li>
            </ul>
        </div>

    </div>
</div>

{{-- ══ ACTIONS ══ --}}
<div class="d-flex gap-2 justify-content-end mt-2">
    <a href="{{ route('reclamations.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>Annuler
    </a>
    <button type="submit" class="btn btn-primary px-4">
        <i class="bi bi-check-lg me-1"></i>Enregistrer la réclamation
    </button>
</div>

</form>
@endsection