@extends('layouts.app')

@section('title', 'Créer un jugement')

@section('breadcrumb') <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li> <li class="breadcrumb-item"><a href="{{ route('jugements.index') }}">Jugements</a></li> <li class="breadcrumb-item active">Créer</li>
@endsection

@section('content')

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">

    <h5 class="fw-bold mb-3">
        <i class="bi bi-plus-circle me-2 text-primary"></i>
        Nouveau jugement
    </h5>

    <form action="{{ route('jugements.store') }}" method="POST">
        @csrf

        <div class="row g-3">

            {{-- Dossier Tribunal --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold small">
                    Instance / Tribunal <span class="text-danger">*</span>
                </label>

                <select name="id_dossier_tribunal"
                        class="form-select @error('id_dossier_tribunal') is-invalid @enderror"
                        required>
                    <option value="">— Sélectionner —</option>
                    @foreach($dossierTribunaux as $dt)
                        <option value="{{ $dt->id }}">
                            {{ $dt->dossier->numero_dossier_interne ?? '—' }}
                            · {{ $dt->tribunal->nom_tribunal ?? '—' }}
                        </option>
                    @endforeach
                </select>

                @error('id_dossier_tribunal')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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
                        <option value="{{ $juge->id }}">
                            {{ $juge->nom_complet }}
                        </option>
                    @endforeach
                </select>

                @error('id_juge')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Date --}}
            {{-- RG02 : date imposée par l'audience "الحكم" --}}
            <div class="col-md-4">
                <label class="form-label fw-semibold small">
                    Date du jugement <span class="text-danger">*</span>
                </label>

                @php
                    // On récupère la date de l'audience الحكم si un seul dossierTribunal est sélectionnable
                    $dateHoukm = null;
                    $dtSelectionne = $dossierTribunaux->first(); // ou selon sélection JS
                    if ($dtSelectionne) {
                        $ah = $dtSelectionne->audienceHoukm();
                        $dateHoukm = $ah?->date_audience?->format('Y-m-d');
                    }
                @endphp

                <input type="date"
                    name="date_jugement"
                    id="date_jugement"
                    class="form-control @error('date_jugement') is-invalid @enderror"
                    value="{{ old('date_jugement', $dateHoukm ?? '') }}"
                    {{ $dateHoukm ? 'readonly' : '' }}
                    required>

                @if($dateHoukm)
                    <div class="form-text text-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Date imposée par l'audience "الحكم" du {{ \Carbon\Carbon::parse($dateHoukm)->format('d/m/Y') }}
                    </div>
                @endif

                @error('date_jugement')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Est définitif --}}
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check mt-2">
                    <input class="form-check-input"
                           type="checkbox"
                           name="est_definitif"
                           value="1"
                           id="est_definitif">

                    <label class="form-check-label small" for="est_definitif">
                        Jugement définitif
                    </label>
                </div>
            </div>

            {{-- Dispositif --}}
            <div class="col-12">
                <label class="form-label fw-semibold small">
                    Dispositif
                </label>

                <textarea name="contenu_dispositif"
                          class="form-control @error('contenu_dispositif') is-invalid @enderror"
                          rows="5"
                          placeholder="Contenu du jugement...">{{ old('contenu_dispositif') }}</textarea>

                @error('contenu_dispositif')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

        </div>

        <hr class="my-4">

        {{-- Parties --}}
        <h6 class="fw-semibold mb-3">
            <i class="bi bi-people me-2 text-primary"></i>Parties
        </h6>

        <div class="row g-3">
            @foreach($parties as $partie)
                <div class="col-md-6">
                    <div class="border rounded p-2">

                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="parties[]"
                                   value="{{ $partie->id }}"
                                   id="partie_{{ $partie->id }}">

                            <label class="form-check-label small fw-semibold">
                                {{ $partie->nom_partie }}
                            </label>
                        </div>

                        <input type="number"
                               step="0.01"
                               name="montants[{{ $partie->id }}]"
                               class="form-control form-control-sm mt-2"
                               placeholder="Montant condamné (DH)">
                    </div>
                </div>
            @endforeach
        </div>

        <hr class="my-4">

        <div class="d-flex justify-content-between">
            <a href="{{ route('jugements.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i>Créer le jugement
            </button>
        </div>

    </form>

</div>

</div>

@endsection

@push('scripts')
<script>
// Map dossierTribunal.id → date audience الحكم
const dateHoukmMap = {
    @foreach($dossierTribunaux as $dt)
        @php $ah = $dt->audienceHoukm(); @endphp
        {{ $dt->id }}: "{{ $ah?->date_audience?->format('Y-m-d') ?? '' }}",
    @endforeach
};

document.getElementById('id_dossier_tribunal')
    ?.addEventListener('change', function () {
        const dateInput = document.getElementById('date_jugement');
        const date      = dateHoukmMap[this.value] ?? '';

        dateInput.value    = date;
        dateInput.readOnly = !!date;

        const hint = document.getElementById('date_houkm_hint');
        if (hint) hint.style.display = date ? 'block' : 'none';
    });
</script>
@endpush