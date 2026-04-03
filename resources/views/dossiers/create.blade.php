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
                            Statut <span class="text-danger">*</span>
                        </label>
                        <select name="id_statut_dossier"
                                class="form-select @error('id_statut_dossier') is-invalid @enderror">
                            <option value="">— Sélectionner —</option>
                            @foreach($statutDossiers as $statut)
                                <option value="{{ $statut->id }}" @selected(old('id_statut_dossier') == $statut->id)>
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

    {{-- ══ COLONNE DROITE : Tribunal initial ══ --}}
    <div class="col-lg-5">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-bank me-2 text-primary"></i>Tribunal initial</h6>
                <span class="badge bg-secondary bg-opacity-10 text-secondary">Optionnel</span>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Vous pouvez assigner un tribunal dès la création, ou le faire plus tard depuis la fiche du dossier.
                </p>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Tribunal</label>
                    <select name="id_tribunal" class="form-select" id="selectTribunal">
                        <option value="">— Aucun pour l'instant —</option>
                        @foreach($tribunaux as $t)
                            <option value="{{ $t->id }}" @selected(old('id_tribunal') == $t->id)>
                                {{ $t->nom_tribunal }}
                                @if($t->typeTribunal) ({{ $t->typeTribunal->nom }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="champsComplementsTribunal" style="{{ old('id_tribunal') ? '' : 'display:none' }}">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Degré de juridiction</label>
                        <select name="id_degre" class="form-select">
                            <option value="">— Sélectionner —</option>
                            @foreach($degresJuridiction as $d)
                                <option value="{{ $d->id }}" @selected(old('id_degre') == $d->id)>
                                    {{ $d->degre_juridiction }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Date de saisine</label>
                            <input type="date" name="date_debut_tribunal" class="form-control"
                                   value="{{ old('date_debut_tribunal') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Date de fin</label>
                            <input type="date" name="date_fin_tribunal" class="form-control"
                                   value="{{ old('date_fin_tribunal') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Aide --}}
        <div class="alert alert-info border-0 small">
            <i class="bi bi-info-circle-fill me-2"></i>
            Après création, vous pourrez ajouter des <strong>parties</strong>, des <strong>audiences</strong>
            et des <strong>jugements</strong> directement depuis la fiche du dossier.
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

@push('scripts')
<script>
    // Afficher / masquer les champs complémentaires du tribunal
    document.getElementById('selectTribunal').addEventListener('change', function () {
        const champs = document.getElementById('champsComplementsTribunal');
        champs.style.display = this.value ? 'block' : 'none';
    });
</script>
@endpush