@extends('layouts.app')

@section('title', 'Nouvelle structure')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.structures.index') }}">Structures</a></li>
    <li class="breadcrumb-item active">Nouvelle</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-diagram-3 text-primary me-2"></i>Nouvelle structure
        </h4>
        <p class="text-muted small mb-0">Créez une structure principale ou une sous-structure.</p>
    </div>
    <a href="{{ route('admin.structures.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
</div>

<div class="row g-4">

    {{-- ── Formulaire ── --}}
    <div class="col-lg-8">
        <form action="{{ route('admin.structures.store') }}" method="POST">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-building me-2 text-primary"></i>Informations de la structure
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- Nom --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            Nom de la structure <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-building text-muted"></i>
                            </span>
                            <input type="text"
                                   name="nom"
                                   class="form-control @error('nom') is-invalid @enderror"
                                   value="{{ old('nom') }}"
                                   placeholder="Ex : Direction Régionale de Casablanca"
                                   required autofocus>
                        </div>
                        @error('nom')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Type --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            Type de structure <span class="text-danger">*</span>
                        </label>
                        <select name="id_type_structure"
                                class="form-select @error('id_type_structure') is-invalid @enderror"
                                required>
                            <option value="">— Sélectionner un type —</option>
                            @foreach($typesStructure as $type)
                                <option value="{{ $type->id }}" @selected(old('id_type_structure') == $type->id)>
                                    {{ $type->type_structure }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_type_structure')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Parent --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            Structure parente
                            <span class="text-muted fw-normal">(optionnel)</span>
                        </label>
                        <select name="id_parent"
                                class="form-select @error('id_parent') is-invalid @enderror">
                            <option value="">— Aucune (structure principale) —</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" @selected(old('id_parent') == $parent->id)>
                                    {{ $parent->nom }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_parent')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Laissez vide pour créer une structure de premier niveau.</div>
                    </div>

                </div>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.structures.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i>Annuler
            </a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i>Créer la structure
            </button>
        </div>

        </form>
    </div>

    {{-- ── Aide ── --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-info"></i>À propos des structures
                </h6>
            </div>
            <div class="card-body small text-muted">
                <p class="mb-2">
                    Les structures permettent d'organiser les <strong>actions de suivi</strong> des réclamations
                    par entité organisationnelle.
                </p>
                <p class="mb-0">
                    Une structure peut avoir des <strong>sous-structures</strong> en lui assignant une structure parente.
                    Il n'y a qu'un seul niveau de hiérarchie possible.
                </p>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-diagram-3 me-2 text-success"></i>Hiérarchie
                </h6>
            </div>
            <div class="card-body small">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-building-fill text-primary"></i>
                    <span class="fw-semibold">Structure principale</span>
                </div>
                <div class="d-flex align-items-center gap-2 ms-3">
                    <i class="bi bi-arrow-return-right text-muted"></i>
                    <i class="bi bi-building text-secondary"></i>
                    <span class="text-muted">Sous-structure</span>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection