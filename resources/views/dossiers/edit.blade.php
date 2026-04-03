@extends('layouts.app')

@section('title', 'Modifier le dossier')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dossiers.index') }}">Dossiers</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dossiers.show', $dossier) }}">{{ $dossier->numero_dossier_interne }}</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning me-2"></i>Modifier le dossier
        </h4>
        <p class="text-muted small mb-0">
            Dossier <strong>{{ $dossier->numero_dossier_interne }}</strong>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('dossiers.show', $dossier) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour à la fiche
        </a>
    </div>
</div>

<form action="{{ route('dossiers.update', $dossier) }}" method="POST">
@csrf @method('PUT')

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-card-text me-2 text-warning"></i>Informations du dossier</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            N° dossier interne <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="numero_dossier_interne"
                               class="form-control @error('numero_dossier_interne') is-invalid @enderror"
                               value="{{ old('numero_dossier_interne', $dossier->numero_dossier_interne) }}">
                        @error('numero_dossier_interne')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">N° dossier tribunal</label>
                        <input type="text" name="numero_dossier_tribunal"
                               class="form-control @error('numero_dossier_tribunal') is-invalid @enderror"
                               value="{{ old('numero_dossier_tribunal', $dossier->numero_dossier_tribunal) }}">
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
                                <option value="{{ $type->id }}"
                                    @selected(old('id_type_affaire', $dossier->id_type_affaire) == $type->id)>
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
                            Statut <span class="text-danger">*</span>
                        </label>
                        <select name="id_statut_dossier"
                                class="form-select @error('id_statut_dossier') is-invalid @enderror">
                            <option value="">— Sélectionner —</option>
                            @foreach($statutDossiers as $statut)
                                <option value="{{ $statut->id }}"
                                    @selected(old('id_statut_dossier', $dossier->id_statut_dossier) == $statut->id)>
                                    {{ $statut->statut_dossier }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_statut_dossier')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            Date d'ouverture <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="date_ouverture"
                               class="form-control @error('date_ouverture') is-invalid @enderror"
                               value="{{ old('date_ouverture', $dossier->date_ouverture?->format('Y-m-d')) }}">
                        @error('date_ouverture')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">Date de clôture</label>
                        <input type="date" name="date_cloture"
                               class="form-control @error('date_cloture') is-invalid @enderror"
                               value="{{ old('date_cloture', $dossier->date_cloture?->format('Y-m-d')) }}">
                        @error('date_cloture')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Colonne droite : résumé --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2 text-muted"></i>Résumé</h6>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Créé par</dt>
                    <dd class="col-7">{{ $dossier->createdBy->name ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Créé le</dt>
                    <dd class="col-7">{{ $dossier->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-5 text-muted">Modifié le</dt>
                    <dd class="col-7">{{ $dossier->updated_at->format('d/m/Y') }}</dd>

                    <dt class="col-5 text-muted">Parties</dt>
                    <dd class="col-7">
                        <span class="badge bg-primary bg-opacity-10 text-primary">
                            {{ $dossier->parties->count() }} partie(s)
                        </span>
                    </dd>

                    <dt class="col-5 text-muted">Tribunaux</dt>
                    <dd class="col-7">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                            {{ $dossier->dossierTribunaux->count() }} tribunal(x)
                        </span>
                    </dd>
                </dl>
            </div>
        </div>

        <div class="alert alert-warning border-0 small mt-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Pour modifier les <strong>parties</strong> ou les <strong>tribunaux</strong>,
            rendez-vous sur la fiche du dossier.
        </div>
    </div>
</div>

<div class="d-flex gap-2 justify-content-end mt-4">
    <a href="{{ route('dossiers.show', $dossier) }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>Annuler
    </a>
    <button type="submit" class="btn btn-warning px-4">
        <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
    </button>
</div>

</form>
@endsection