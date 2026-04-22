{{-- resources/views/audiences/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nouvelle audience')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('audiences.index') }}">Audiences</a></li>
    <li class="breadcrumb-item active">Nouvelle audience</li>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-calendar-plus me-2 text-primary"></i>Créer une audience
                </h5>
            </div>

            <div class="card-body p-4">

                @if($dateAudienceParDefaut)
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-2"></i>
                        La date de cette audience a été proposée automatiquement à partir
                        de la prochaine audience enregistrée précédemment :
                        <strong>{{ \Carbon\Carbon::parse($dateAudienceParDefaut)->format('d/m/Y') }}</strong>
                    </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('audiences.store') }}">
                    @csrf

                    {{-- Dossier / Tribunal --}}
                    <div class="mb-3">
                        <label for="id_dossier_tribunal" class="form-label fw-semibold">
                            Dossier & Tribunal <span class="text-danger">*</span>
                        </label>
                        <select name="id_dossier_tribunal"
                                id="id_dossier_tribunal"
                                class="form-select @error('id_dossier_tribunal') is-invalid @enderror"
                                required>
                            <option value="">— Sélectionner un dossier —</option>

                            @foreach($dossierTribunaux as $dt)
                                <option value="{{ $dt->id }}"
                                    @selected(
                                        old('id_dossier_tribunal', $dossierTribunaux->count() === 1 ? $dt->id : null) == $dt->id
                                    )>
                                    {{ $dt->dossier?->numero_dossier_interne ?? 'Dossier #'.$dt->id_dossier }}
                                    — {{ $dt->tribunal?->nom_tribunal ?? 'Tribunal #'.$dt->id_tribunal }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Type --}}
                    <div class="mb-3">
                        <label for="id_type_audience" class="form-label fw-semibold">
                            Type d'audience <span class="text-danger">*</span>
                        </label>
                        <select name="id_type_audience" id="id_type_audience"
                                class="form-select @error('id_type_audience') is-invalid @enderror"
                                required>
                            <option value="">— Sélectionner —</option>
                            @foreach($typesAudience as $type)
                                <option value="{{ $type->id }}" @selected(old('id_type_audience') == $type->id)>
                                    {{ $type->libelle ?? $type->type_audience }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Juge --}}
                    <div class="mb-3">
                        <label for="id_juge" class="form-label fw-semibold">
                            Juge <span class="text-danger">*</span>
                        </label>
                        <select name="id_juge" id="id_juge"
                                class="form-select @error('id_juge') is-invalid @enderror"
                                required>
                            <option value="">— Sélectionner —</option>
                            @foreach($juges as $juge)
                                <option value="{{ $juge->id }}" @selected(old('id_juge') == $juge->id)>
                                    {{ $juge->nom_complet }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dates --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="date_audience" class="form-label fw-semibold">
                                Date de l'audience <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="date_audience"
                                   id="date_audience"
                                   class="form-control @error('date_audience') is-invalid @enderror"
                                   value="{{ old('date_audience', $dateAudienceParDefaut ?? date('Y-m-d')) }}"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label for="date_prochaine_audience" class="form-label fw-semibold">
                                Prochaine audience
                            </label>
                            <input type="date"
                                   name="date_prochaine_audience"
                                   id="date_prochaine_audience"
                                   class="form-control @error('date_prochaine_audience') is-invalid @enderror"
                                   value="{{ old('date_prochaine_audience') }}">
                        </div>
                    </div>

                    {{-- Présence --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="presence_demandeur"
                                       value="1"
                                       @checked(old('presence_demandeur'))>
                                <label class="form-check-label">
                                    Présence du demandeur
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="presence_defendeur"
                                       value="1"
                                       @checked(old('presence_defendeur'))>
                                <label class="form-check-label">
                                    Présence du défendeur
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Résultat --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Résultat</label>
                        <textarea name="resultat_audience"
                                  class="form-control"
                                  rows="3">{{ old('resultat_audience') }}</textarea>
                    </div>

                    {{-- Actions --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Actions demandées</label>
                        <textarea name="actions_demandees"
                                  class="form-control"
                                  rows="3">{{ old('actions_demandees') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer
                        </button>
                        <a href="{{ route('audiences.index') }}" class="btn btn-outline-secondary">
                            Annuler
                        </a>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

@endsection