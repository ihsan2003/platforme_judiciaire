@extends('layouts.app')

@section('title', 'Nouveau juge')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('juges.index') }}">Juges</a></li>
    <li class="breadcrumb-item active">Nouveau juge</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-person-plus text-primary me-2"></i>Nouveau juge
        </h4>
        <p class="text-muted small mb-0">Enregistrez les informations d'un nouveau juge.</p>
    </div>
    <a href="{{ route('juges.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
</div>

<form action="{{ route('juges.store') }}" method="POST">
@csrf

<div class="row g-4">

    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-vcard me-2 text-primary"></i>Informations du juge
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- Nom complet --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            Nom complet <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="nom_complet"
                               class="form-control @error('nom_complet') is-invalid @enderror"
                               value="{{ old('nom_complet') }}"
                               placeholder="Ex : Mohammed El Alaoui"
                               required>
                        @error('nom_complet')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Grade --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            Grade <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="grade"
                               class="form-control @error('grade') is-invalid @enderror"
                               value="{{ old('grade') }}"
                               placeholder="Ex : Président, Conseiller…"
                               required>
                        @error('grade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Spécialisation --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Spécialisation</label>
                        <input type="text"
                               name="specialisation"
                               class="form-control @error('specialisation') is-invalid @enderror"
                               value="{{ old('specialisation') }}"
                               placeholder="Ex : Droit civil, Droit commercial…">
                        @error('specialisation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tribunal --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            Tribunal <span class="text-danger">*</span>
                        </label>
                        <select name="id_tribunal"
                                class="form-select @error('id_tribunal') is-invalid @enderror"
                                required>
                            <option value="">— Sélectionner un tribunal —</option>
                            @foreach($tribunaux as $tribunal)
                                <option value="{{ $tribunal->id }}" @selected(old('id_tribunal') == $tribunal->id)>
                                    {{ $tribunal->nom_tribunal }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_tribunal')
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
                    <li class="mb-2">Le juge doit être rattaché à un tribunal.</li>
                    <li class="mb-2">Il pourra ensuite être assigné à des audiences.</li>
                    <li>Le grade et la spécialisation aident à la sélection lors de la création d'audiences.</li>
                </ul>
            </div>
        </div>
    </div>

</div>

{{-- ── Actions ── --}}
<div class="d-flex gap-2 justify-content-end mt-4">
    <a href="{{ route('juges.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>Annuler
    </a>
    <button type="submit" class="btn btn-primary px-4">
        <i class="bi bi-check-lg me-1"></i>Enregistrer
    </button>
</div>

</form>
@endsection