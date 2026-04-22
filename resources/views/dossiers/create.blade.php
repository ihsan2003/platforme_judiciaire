@extends('layouts.app')

@section('title', 'Nouveau dossier')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dossiers.index') }}">Dossiers</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-folder-plus text-primary me-2"></i>Nouveau dossier judiciaire</h4>
        <p class="text-muted small mb-0">Renseignez les informations du dossier et assignez un tribunal.</p>
    </div>
    <a href="{{ route('dossiers.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
</div>

<form action="{{ route('dossiers.store') }}" method="POST">
@csrf

<div class="row g-4">

    {{-- ══ COLONNE GAUCHE : Informations principales ══ --}}
    <div class="col-lg-7">

        {{-- Identification --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-card-text me-2 text-primary"></i>Identification du dossier</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            N° dossier interne <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="numero_dossier_interne"
                               class="form-control @error('numero_dossier_interne') is-invalid @enderror"
                               value="{{ old('numero_dossier_interne') }}"
                               placeholder="Ex : DOS-2025-001">
                        @error('numero_dossier_interne')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">N° dossier tribunal</label>
                        <input type="text" name="numero_dossier_tribunal"
                               class="form-control @error('numero_dossier_tribunal') is-invalid @enderror"
                               value="{{ old('numero_dossier_tribunal') }}"
                               placeholder="Ex : TRB-2025-001">
                        @error('numero_dossier_tribunal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            Type d'affaire <span class="text-danger">*</span>
                        </label>
                        <select name="id_type_affaire"
                                class="form-select @error('id_type_affaire') is-invalid @enderror">
                            <option value="">— Sélectionner —</option>
                            @foreach($typesAffaire as $type)
                                <option value="{{ $type->id }}" @selected(old('id_type_affaire') == $type->id)>
                                    {{ $type->affaire }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_type_affaire')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            Date d'ouverture <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="date_ouverture"
                               class="form-control @error('date_ouverture') is-invalid @enderror"
                               value="{{ old('date_ouverture', date('Y-m-d')) }}">
                        @error('date_ouverture')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Date de clôture</label>
                        <input type="date" name="date_cloture"
                               class="form-control @error('date_cloture') is-invalid @enderror"
                               value="{{ old('date_cloture') }}">
                        @error('date_cloture')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- ══ ACTIONS ══ --}}
<div class="d-flex gap-2 justify-content-end mt-2">
    <a href="{{ route('dossiers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>Annuler
    </a>
    <button type="submit" class="btn btn-primary px-4">
        <i class="bi bi-check-lg me-1"></i>Créer le dossier
    </button>
</div>

</form>

@endsection

