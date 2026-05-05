@extends('layouts.app')

@section('title', 'Modifier le tribunal')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tribunaux.index') }}">Tribunaux</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tribunaux.show', $tribunal) }}">{{ $tribunal->nom_tribunal }}</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning me-2"></i>Modifier le tribunal
        </h4>
        <p class="text-muted small mb-0">
            Mise à jour de <strong>{{ $tribunal->nom_tribunal }}</strong>
        </p>
    </div>
    <a href="{{ route('tribunaux.show', $tribunal) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la fiche
    </a>
</div>

<form action="{{ route('tribunaux.update', $tribunal) }}" method="POST">
@csrf @method('PUT')

<div class="row g-4">

    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-building me-2 text-warning"></i>Informations du tribunal
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- Nom --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            Nom du tribunal <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="nom_tribunal"
                               class="form-control @error('nom_tribunal') is-invalid @enderror"
                               value="{{ old('nom_tribunal', $tribunal->nom_tribunal) }}"
                               required>
                        @error('nom_tribunal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Type de tribunal --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            Type de tribunal <span class="text-danger">*</span>
                        </label>
                        <select name="id_type_tribunal"
                                class="form-select @error('id_type_tribunal') is-invalid @enderror"
                                required>
                            <option value="">— Sélectionner —</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}"
                                    @selected(old('id_type_tribunal', $tribunal->id_type_tribunal) == $type->id)>
                                    {{ $type->tribunal }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_type_tribunal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Province --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            Province <span class="text-danger">*</span>
                        </label>
                        <select name="id_province"
                                class="form-select @error('id_province') is-invalid @enderror"
                                required>
                            <option value="">— Sélectionner —</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province->id }}"
                                    @selected(old('id_province', $tribunal->id_province) == $province->id)>
                                    {{ $province->province }}
                                    @if($province->region)
                                        ({{ $province->region->region }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('id_province')
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
                    <dt class="col-6 text-muted fw-normal">Créé le</dt>
                    <dd class="col-6">{{ $tribunal->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">Modifié le</dt>
                    <dd class="col-6">{{ $tribunal->updated_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">Juges</dt>
                    <dd class="col-6">
                        @php $nb = $tribunal->juges()->count(); @endphp
                        <span class="badge bg-{{ $nb > 0 ? 'info' : 'secondary' }} bg-opacity-15 text-{{ $nb > 0 ? 'info' : 'secondary' }}">
                            {{ $nb }} juge(s)
                        </span>
                    </dd>

                    <dt class="col-6 text-muted fw-normal">Dossiers</dt>
                    <dd class="col-6">
                        @php $nbD = $tribunal->dossierTribunaux()->count(); @endphp
                        <span class="badge bg-{{ $nbD > 0 ? 'primary' : 'secondary' }} bg-opacity-15 text-{{ $nbD > 0 ? 'primary' : 'secondary' }}">
                            {{ $nbD }} dossier(s)
                        </span>
                    </dd>
                </dl>
            </div>
        </div>

        @if($tribunal->dossierTribunaux()->count() > 0)
        <div class="alert alert-warning border-0 small">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Ce tribunal est lié à <strong>{{ $tribunal->dossierTribunaux()->count() }} dossier(s)</strong>.
            Toute modification impactera ces dossiers.
        </div>
        @endif
    </div>

</div>

{{-- ── Actions ── --}}
<div class="d-flex gap-2 justify-content-end mt-4">
    <a href="{{ route('tribunaux.show', $tribunal) }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>Annuler
    </a>
    <button type="submit" class="btn btn-warning px-4">
        <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
    </button>
</div>

</form>
@endsection