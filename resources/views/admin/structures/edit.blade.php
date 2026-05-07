@extends('layouts.app')

@section('title', 'Modifier — ' . $structure->nom)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.structures.index') }}">Structures</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.structures.show', $structure) }}">{{ $structure->nom }}</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning me-2"></i>Modifier la structure
        </h4>
        <p class="text-muted small mb-0">
            Modification de <strong>{{ $structure->nom }}</strong>
        </p>
    </div>
    <a href="{{ route('admin.structures.show', $structure) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la fiche
    </a>
</div>

<form action="{{ route('admin.structures.update', $structure) }}" method="POST">
@csrf @method('PUT')

<div class="row g-4">

    {{-- ── Formulaire ── --}}
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-building me-2 text-warning"></i>Informations de la structure
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
                                   value="{{ old('nom', $structure->nom) }}"
                                   required>
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
                                <option value="{{ $type->id }}"
                                        @selected(old('id_type_structure', $structure->id_type_structure) == $type->id)>
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
                                <option value="{{ $parent->id }}"
                                        @selected(old('id_parent', $structure->id_parent) == $parent->id)>
                                    {{ $parent->nom }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_parent')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.structures.show', $structure) }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i>Annuler
            </a>
            <button type="submit" class="btn btn-warning px-4">
                <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
            </button>
        </div>

    </div>

    {{-- ── Résumé ── --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-muted"></i>Résumé
                </h6>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-6 text-muted fw-normal">ID</dt>
                    <dd class="col-6 font-monospace">#{{ $structure->id }}</dd>

                    <dt class="col-6 text-muted fw-normal">Créée le</dt>
                    <dd class="col-6">{{ $structure->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">Type actuel</dt>
                    <dd class="col-6">
                        <span class="badge bg-primary bg-opacity-15 text-primary">
                            {{ $structure->typeStructure?->type_structure ?? '—' }}
                        </span>
                    </dd>

                    <dt class="col-6 text-muted fw-normal">Parente</dt>
                    <dd class="col-6">{{ $structure->parent?->nom ?? '—' }}</dd>

                    @if($structure->enfants->count() > 0)
                    <dt class="col-6 text-muted fw-normal">Sous-structures</dt>
                    <dd class="col-6">
                        <span class="badge bg-success bg-opacity-10 text-success">
                            {{ $structure->enfants->count() }}
                        </span>
                    </dd>
                    @endif
                </dl>
            </div>
        </div>

        @if($structure->enfants->count() > 0)
        <div class="alert alert-warning border-0 small">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Cette structure possède <strong>{{ $structure->enfants->count() }} sous-structure(s)</strong>.
            La modifier n'affecte pas ses enfants.
        </div>
        @endif
    </div>

</div>

</form>

@endsection