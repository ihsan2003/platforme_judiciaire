@extends('layouts.app')

@section('title', 'Nouveau tribunal')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tribunaux.index') }}">Tribunaux</a></li>
    <li class="breadcrumb-item active">Nouveau tribunal</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-building-add text-primary me-2"></i>Nouveau tribunal
        </h4>
        <p class="text-muted small mb-0">Enregistrez les informations d'un nouveau tribunal.</p>
    </div>
    <a href="{{ route('tribunaux.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
</div>

<form action="{{ route('tribunaux.store') }}" method="POST">
@csrf

<div class="row g-4">

    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-building me-2 text-primary"></i>Informations du tribunal
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
                               value="{{ old('nom_tribunal') }}"
                               placeholder="Ex : Tribunal de Première Instance de Casablanca"
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
                                <option value="{{ $type->id }}" @selected(old('id_type_tribunal') == $type->id)>
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
                                <option value="{{ $province->id }}" @selected(old('id_province') == $province->id)>
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
                    <i class="bi bi-info-circle me-2 text-muted"></i>À savoir
                </h6>
            </div>
            <div class="card-body small text-muted">
                <ul class="mb-0 ps-3">
                    <li class="mb-2">Le tribunal sera associé à une province et donc à une région.</li>
                    <li class="mb-2">Des juges pourront ensuite lui être rattachés.</li>
                    <li>Le tribunal peut être assigné à des dossiers judiciaires.</li>
                </ul>
            </div>
        </div>
    </div>

</div>

{{-- ── Actions ── --}}
<div class="d-flex gap-2 justify-content-end mt-4">
    <a href="{{ route('tribunaux.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>Annuler
    </a>
    <button type="submit" class="btn btn-primary px-4">
        <i class="bi bi-check-lg me-1"></i>Enregistrer
    </button>
</div>

</form>
@endsection