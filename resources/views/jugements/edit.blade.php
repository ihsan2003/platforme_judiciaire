{{-- resources/views/jugements/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Modifier le jugement du ' . $jugement->date_jugement->format('d/m/Y'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('jugements.index') }}">Jugements</a></li>
    <li class="breadcrumb-item"><a href="{{ route('jugements.show', $jugement) }}">Jugement #{{ $jugement->id }}</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')

@php
    $dt = $jugement->dossierTribunal;
    $audienceHoukm = $dt?->audienceHoukm();
    $dateHoukm = $audienceHoukm?->date_audience?->format('Y-m-d');
@endphp

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning me-2"></i>Modifier le jugement
        </h4>
        <p class="text-muted small mb-0">
            Du {{ $jugement->date_jugement->format('d/m/Y') }}
            — {{ $dt?->tribunal?->nom_tribunal ?? '—' }}
            ({{ $dt?->degre?->degre_juridiction ?? '—' }})
        </p>
    </div>
    <a href="{{ route('jugements.show', $jugement) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la fiche
    </a>
</div>

@if($jugement->est_definitif)
    <div class="alert alert-warning mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Ce jugement est <strong>définitif</strong>. Seul le dispositif peut être corrigé.
    </div>
@endif

<form action="{{ route('jugements.update', $jugement) }}" method="POST">
@csrf @method('PUT')

<div class="row g-4">

    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-hammer me-2 text-warning"></i>Informations du jugement
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- Instance — lecture seule en édition --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Instance judiciaire</label>
                        <div class="form-control bg-light text-muted">
                            {{ $dt?->dossier?->numero_dossier_interne ?? '—' }}
                            · {{ $dt?->tribunal?->nom_tribunal ?? '—' }}
                            ({{ $dt?->degre?->degre_juridiction ?? '—' }})
                        </div>
                        <div class="form-text">L'instance ne peut pas être modifiée après création.</div>
                    </div>

                    {{-- Juge --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            Juge <span class="text-danger">*</span>
                        </label>
                        <select name="id_juge"
                                class="form-select @error('id_juge') is-invalid @enderror"
                                required>
                            <option value="">— Sélectionner —</option>
                            @foreach($juges as $juge)
                                <option value="{{ $juge->id }}"
                                    @selected(old('id_juge', $jugement->id_juge) == $juge->id)>
                                    {{ $juge->nom_complet }}
                                    @if($juge->tribunal)
                                        ({{ $juge->tribunal->nom_tribunal }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('id_juge')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Date jugement — imposée par جلسة الحكم --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            Date du jugement <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                               name="date_jugement"
                               class="form-control @error('date_jugement') is-invalid @enderror"
                               value="{{ old('date_jugement', $jugement->date_jugement->format('Y-m-d')) }}"
                               {{ $dateHoukm ? 'readonly' : '' }}
                               required>
                        @if($dateHoukm)
                            <div class="form-text text-info">
                                <i class="bi bi-lock me-1"></i>
                                Date imposée par l'audience الحكم du
                                {{ \Carbon\Carbon::parse($dateHoukm)->format('d/m/Y') }}
                            </div>
                        @endif
                        @error('date_jugement')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Définitif --}}
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="est_definitif"
                                   value="1"
                                   id="est_definitif"
                                   @checked(old('est_definitif', $jugement->est_definitif))
                                   @disabled($jugement->est_definitif && $jugement->executions()->exists())>
                            <label class="form-check-label" for="est_definitif">
                                Jugement définitif
                            </label>
                        </div>
                        @if($jugement->est_definitif && $jugement->executions()->exists())
                            <div class="form-text text-warning">
                                <i class="bi bi-lock me-1"></i>
                                Ce jugement a une exécution — le caractère définitif ne peut plus être modifié.
                            </div>
                        @endif
                    </div>

                    {{-- Dispositif --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Dispositif du jugement</label>
                        <textarea name="contenu_dispositif"
                                  class="form-control @error('contenu_dispositif') is-invalid @enderror"
                                  rows="6"
                                  placeholder="Contenu du jugement…">{{ old('contenu_dispositif', $jugement->contenu_dispositif) }}</textarea>
                        @error('contenu_dispositif')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- Parties impliquées --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-people me-2 text-primary"></i>Parties et montants condamnés
                </h6>
            </div>
            <div class="card-body">
                @if($parties->isEmpty())
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-people fs-2 d-block mb-2 opacity-25"></i>
                        Aucune partie disponible.
                    </div>
                @else
                <div class="row g-3">
                    @foreach($parties as $partie)
                    @php
                        $isLinked  = in_array($partie->id, $partiesLiees);
                        $montantPivot = $jugement->parties->find($partie->id)?->pivot->montant_condamne;
                    @endphp
                    <div class="col-md-6">
                        <div class="border rounded p-3 {{ $isLinked ? 'border-primary bg-primary bg-opacity-5' : '' }}">
                            <div class="form-check mb-2">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="parties[]"
                                       value="{{ $partie->id }}"
                                       id="partie_{{ $partie->id }}"
                                       @checked($isLinked)>
                                <label class="form-check-label small fw-semibold" for="partie_{{ $partie->id }}">
                                    {{ $partie->nom_partie }}
                                    <span class="text-muted font-monospace ms-1" style="font-size:.7rem">
                                        ({{ $partie->identifiant_unique }})
                                    </span>
                                </label>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">DH</span>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       name="montants[{{ $partie->id }}]"
                                       class="form-control"
                                       value="{{ old('montants.'.$partie->id, $montantPivot) }}"
                                       placeholder="Montant condamné">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ── Colonne latérale ── --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-muted"></i>Résumé
                </h6>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-6 text-muted fw-normal">Dossier</dt>
                    <dd class="col-6 fw-semibold">{{ $dt?->dossier?->numero_dossier_interne ?? '—' }}</dd>

                    <dt class="col-6 text-muted fw-normal">Tribunal</dt>
                    <dd class="col-6">{{ $dt?->tribunal?->nom_tribunal ?? '—' }}</dd>

                    <dt class="col-6 text-muted fw-normal">Degré</dt>
                    <dd class="col-6" dir="rtl">{{ $dt?->degre?->degre_juridiction ?? '—' }}</dd>

                    <dt class="col-6 text-muted fw-normal">Audience الحكم</dt>
                    <dd class="col-6">
                        @if($audienceHoukm)
                            <span class="text-warning fw-semibold">
                                {{ $audienceHoukm->date_audience->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </dd>

                    <dt class="col-6 text-muted fw-normal">Recours déposés</dt>
                    <dd class="col-6">
                        <span class="badge bg-{{ $jugement->recours->isEmpty() ? 'secondary' : 'warning text-dark' }}">
                            {{ $jugement->recours->count() }}
                        </span>
                    </dd>

                    <dt class="col-6 text-muted fw-normal">Exécutions</dt>
                    <dd class="col-6">
                        <span class="badge bg-{{ $jugement->executions->isEmpty() ? 'secondary' : 'info' }}">
                            {{ $jugement->executions->count() }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>

        @if($jugement->recours->isNotEmpty())
        <div class="alert alert-warning border-0 small">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Des recours ont été déposés sur ce jugement. Soyez prudent lors de la modification.
        </div>
        @endif

        @if($jugement->executions->isNotEmpty())
        <div class="alert alert-danger border-0 small">
            <i class="bi bi-shield-check me-2"></i>
            Ce jugement a une exécution en cours. Les modifications sont limitées.
        </div>
        @endif

    </div>
</div>

{{-- Actions --}}
<div class="d-flex gap-2 justify-content-end mt-2">
    <a href="{{ route('jugements.show', $jugement) }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>Annuler
    </a>
    <button type="submit" class="btn btn-warning px-4">
        <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
    </button>
</div>

</form>

@endsection