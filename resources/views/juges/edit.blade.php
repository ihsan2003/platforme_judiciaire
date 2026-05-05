@extends('layouts.app')

@section('title', 'Modifier le juge')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('juges.index') }}">Juges</a></li>
    <li class="breadcrumb-item"><a href="{{ route('juges.show', $juge) }}">{{ $juge->nom_complet }}</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning me-2"></i>Modifier le juge
        </h4>
        <p class="text-muted small mb-0">
            Mise à jour de <strong>{{ $juge->nom_complet }}</strong>
        </p>
    </div>
    <a href="{{ route('juges.show', $juge) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la fiche
    </a>
</div>

<form action="{{ route('juges.update', $juge) }}" method="POST">
@csrf @method('PUT')

<div class="row g-4">

    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-vcard me-2 text-warning"></i>Informations du juge
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
                               value="{{ old('nom_complet', $juge->nom_complet) }}"
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
                               value="{{ old('grade', $juge->grade) }}"
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
                               value="{{ old('specialisation', $juge->specialisation) }}">
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
                                <option value="{{ $tribunal->id }}"
                                    @selected(old('id_tribunal', $juge->id_tribunal) == $tribunal->id)>
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
                    <i class="bi bi-info-circle me-2 text-muted"></i>Résumé
                </h6>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-6 text-muted fw-normal">Tribunal actuel</dt>
                    <dd class="col-6">{{ $juge->tribunal->nom_tribunal ?? '—' }}</dd>

                    <dt class="col-6 text-muted fw-normal">Créé le</dt>
                    <dd class="col-6">{{ $juge->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">Modifié le</dt>
                    <dd class="col-6">{{ $juge->updated_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">Audiences</dt>
                    <dd class="col-6">
                        @php $nbA = $juge->audiences()->count(); @endphp
                        <span class="badge bg-{{ $nbA > 0 ? 'info' : 'secondary' }} bg-opacity-15 text-{{ $nbA > 0 ? 'info' : 'secondary' }}">
                            {{ $nbA }} audience(s)
                        </span>
                    </dd>

                    <dt class="col-6 text-muted fw-normal">Jugements</dt>
                    <dd class="col-6">
                        @php $nbJ = $juge->jugements()->count(); @endphp
                        <span class="badge bg-{{ $nbJ > 0 ? 'primary' : 'secondary' }} bg-opacity-15 text-{{ $nbJ > 0 ? 'primary' : 'secondary' }}">
                            {{ $nbJ }} jugement(s)
                        </span>
                    </dd>
                </dl>
            </div>
        </div>

        @if($juge->audiences()->count() > 0)
        <div class="alert alert-warning border-0 small">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Ce juge est lié à <strong>{{ $juge->audiences()->count() }} audience(s)</strong>.
            Le changement de tribunal n'affectera pas les audiences passées.
        </div>
        @endif
    </div>

</div>

{{-- ── Actions ── --}}
<div class="d-flex gap-2 justify-content-end mt-4">
    <a href="{{ route('juges.show', $juge) }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>Annuler
    </a>
    <button type="submit" class="btn btn-warning px-4">
        <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
    </button>
</div>

</form>
@endsection