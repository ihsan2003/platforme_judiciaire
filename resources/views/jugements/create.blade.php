@extends('layouts.app')

@section('title', 'Créer un jugement')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('jugements.index') }}">Jugements</a></li>
    <li class="breadcrumb-item active">Créer</li>
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

            {{-- ══ BLOC 1 : Informations principales ══ --}}
            <div class="row g-3 mb-4">

                {{-- Instance / Tribunal --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">
                        Instance / Tribunal <span class="text-danger">*</span>
                    </label>
                    <select name="id_dossier_tribunal"
                            id="id_dossier_tribunal"
                            class="form-select @error('id_dossier_tribunal') is-invalid @enderror"
                            required>
                        <option value="">— Sélectionner —</option>
                        @foreach($dossierTribunaux as $dt)
                            <option value="{{ $dt->id }}"
                                @selected(old('id_dossier_tribunal', $defaultDossierTribunalId ?? null) == $dt->id)>
                                {{ $dt->dossier->numero_dossier_interne ?? '—' }}
                                · {{ $dt->tribunal->nom_tribunal ?? '—' }}
                                · {{ $dt->degre->degre_juridiction ?? '—' }}
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
                    <select id="id_juge"
                            name="id_juge"
                            class="form-select @error('id_juge') is-invalid @enderror"
                            required>
                        <option value="">— Sélectionner d'abord une instance —</option>
                    </select>
                    @error('id_juge')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Date du jugement --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">
                        Date du jugement <span class="text-danger">*</span>
                    </label>
                    <input type="date"
                           name="date_jugement"
                           id="date_jugement"
                           class="form-control @error('date_jugement') is-invalid @enderror"
                           value="{{ old('date_jugement') }}"
                           required>
                    <div id="date_houkm_hint" class="form-text text-info d-none">
                        <i class="bi bi-lock me-1"></i>
                        Date imposée par l'audience "الحكم"
                    </div>
                    @error('date_jugement')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Est définitif --}}
                <div class="col-md-4 d-flex align-items-end pb-1">
                    <div class="form-check">
                        <input class="form-check-input"
                               type="checkbox"
                               name="est_definitif"
                               value="1"
                               id="est_definitif"
                               @checked(old('est_definitif'))>
                        <label class="form-check-label small fw-semibold" for="est_definitif">
                            Jugement définitif
                        </label>
                    </div>
                </div>

                {{-- Dispositif --}}
                <div class="col-12">
                    <label class="form-label fw-semibold small">Dispositif</label>
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

            {{-- ══ BLOC 2 : Résultat & Parties ══ --}}
            <h6 class="fw-semibold mb-3">
                <i class="bi bi-balance-scale me-2 text-primary"></i>
                Résultat vis-à-vis de l'établissement &amp; Parties condamnées
            </h6>

            @php
                $institution   = $partiesDossier->first(fn($dp) => $dp->partie?->est_entraide);
                $autresParties = $partiesDossier->filter(fn($dp) => !$dp->partie?->est_entraide);
            @endphp

            {{-- ── Position de l'établissement ── --}}
            @if($institution)
            <div class="card border-0 shadow-sm mb-4"
                 style="border-left: 4px solid #0d6efd !important;">
                <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                    <i class="bi bi-building-fill text-primary"></i>
                    <h6 class="mb-0 fw-semibold">
                        Position de l'établissement :
                        <strong class="text-primary">{{ $institution->partie->nom_partie }}</strong>
                    </h6>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Position dans ce jugement <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($positionsInstitution as $pos)
                            @php
                                $color = match(true) {
                                    str_contains(strtolower($pos->position), 'pour')
                                        || str_contains(strtolower($pos->position), 'avec')  => 'success',
                                    str_contains(strtolower($pos->position), 'contre')        => 'danger',
                                    str_contains(strtolower($pos->position), 'partiel')       => 'warning',
                                    default                                                    => 'secondary',
                                };
                                $icon = match($color) {
                                    'success'  => 'trophy-fill',
                                    'danger'   => 'shield-x',
                                    'warning'  => 'slash-circle',
                                    default    => 'dash-circle',
                                };
                            @endphp
                            <div>
                                <input type="radio"
                                       class="btn-check"
                                       name="position_institution_etab"
                                       id="pos_{{ $pos->id }}"
                                       value="{{ $pos->id }}"
                                       data-label="{{ strtolower($pos->position) }}"
                                       @checked(old('position_institution_etab') == $pos->id)
                                       required>
                                <label class="btn btn-outline-{{ $color }} px-4"
                                       for="pos_{{ $pos->id }}">
                                    <i class="bi bi-{{ $icon }} me-2"></i>
                                    {{ $pos->position }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @error('position_institution_etab')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Montant de l'établissement si condamné (contre/partiel) --}}
                    <div id="bloc-etab-condamne" class="d-none">
                        <div class="border rounded p-3 border-danger bg-danger bg-opacity-5">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-building text-danger"></i>
                                <span class="fw-semibold small">
                                    {{ $institution->partie->nom_partie }}
                                </span>
                                <span class="badge bg-danger ms-1" style="font-size:.65rem">
                                    Établissement condamné
                                </span>
                            </div>
                            {{-- Champ caché pour inclure l'établissement dans parties[] --}}
                            <input type="hidden"
                                   name="parties[]"
                                   id="hidden_etab_partie"
                                   value="{{ $institution->partie->id }}"
                                   disabled>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-danger text-white border-danger">DH</span>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       name="montants[{{ $institution->partie->id }}]"
                                       id="montant_etab"
                                       class="form-control"
                                       value="{{ old('montants.'.$institution->partie->id) }}"
                                       placeholder="Montant condamné">
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            @else
            <div class="alert alert-warning small mb-4">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Aucune partie marquée comme établissement dans ce dossier.
            </div>
            @endif

            {{-- ── Parties adverses condamnées (visible si position = pour/avec) ── --}}
            <div id="bloc-parties-adverses"
                 class="{{ $autresParties->isEmpty() ? 'd-none' : 'd-none' }}">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                        <i class="bi bi-people text-secondary"></i>
                        <h6 class="mb-0 fw-semibold">Parties adverses condamnées</h6>
                        <span class="text-muted small ms-1">
                            (sélectionnez celle(s) condamnée(s) et saisissez le montant)
                        </span>
                    </div>
                    <div class="card-body">
                        @if($autresParties->isEmpty())
                            <div class="text-center py-3 text-muted small">
                                <i class="bi bi-people fs-2 d-block mb-2 opacity-25"></i>
                                Aucune partie adverse dans ce dossier.
                            </div>
                        @else
                        <div class="row g-3">
                            @foreach($autresParties as $dp)
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="parties[]"
                                               value="{{ $dp->partie->id }}"
                                               id="partie_{{ $dp->partie->id }}"
                                               @checked(old('parties') && in_array($dp->partie->id, old('parties', [])))>
                                        <label class="form-check-label small fw-semibold"
                                               for="partie_{{ $dp->partie->id }}">
                                            {{ $dp->partie->nom_partie }}
                                            <span class="badge bg-secondary ms-1" style="font-size:.65rem">
                                                {{ $dp->typePartie->type_partie ?? '—' }}
                                            </span>
                                        </label>
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">DH</span>
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               name="montants[{{ $dp->partie->id }}]"
                                               class="form-control"
                                               value="{{ old('montants.'.$dp->partie->id) }}"
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

            <hr class="my-4">

            {{-- ══ ACTIONS ══ --}}
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
// ══════════════════════════════════════════════════════════════════
// MAP : dossierTribunal.id → { date الحكم, id_tribunal }
// ══════════════════════════════════════════════════════════════════
const dtMap = {
    @foreach($dossierTribunaux as $dt)
        @php $ah = $dt->audienceHoukm(); @endphp
        {{ $dt->id }}: {
            date      : "{{ $ah?->date_audience?->format('Y-m-d') ?? '' }}",
            tribunalId: {{ $dt->id_tribunal }}
        },
    @endforeach
};

// Valeur old() pour restaurer le juge sélectionné après erreur de validation
const oldJugeId = "{{ old('id_juge') }}";

const selectDt    = document.getElementById('id_dossier_tribunal');
const selectJuge  = document.getElementById('id_juge');
const dateInput   = document.getElementById('date_jugement');
const dateHint    = document.getElementById('date_houkm_hint');

// ══════════════════════════════════════════════════════════════════
// CHANGEMENT D'INSTANCE → mise à jour date + juges
// ══════════════════════════════════════════════════════════════════
async function onDtChange() {
    const dtId = selectDt?.value;
    const info = dtMap[dtId];

    // ── Date الحكم ────────────────────────────────────────────────
    const date = info?.date ?? '';
    if (dateInput) {
        dateInput.value    = date;
        dateInput.readOnly = !!date;
    }
    if (dateHint) {
        dateHint.classList.toggle('d-none', !date);
    }

    // ── Juges filtrés par tribunal ────────────────────────────────
    if (!selectJuge) return;

    if (!dtId || !info) {
        selectJuge.innerHTML = '<option value="">— Sélectionner d\'abord une instance —</option>';
        selectJuge.disabled  = true;
        return;
    }

    selectJuge.innerHTML = '<option value="">— Chargement… —</option>';
    selectJuge.disabled  = true;

    try {
        const res   = await fetch(`/api/tribunaux/${info.tribunalId}/juges`);
        const juges = await res.json();

        selectJuge.innerHTML = '<option value="">— Sélectionner un juge —</option>';

        if (juges.length === 0) {
            selectJuge.innerHTML = '<option value="">— Aucun juge pour ce tribunal —</option>';
        } else {
            juges.forEach(j => {
                const opt       = document.createElement('option');
                opt.value       = j.id;
                opt.textContent = (j.grade ? j.grade + ' ' : '') + j.nom_complet;
                // Restaurer la sélection après old()
                if (oldJugeId && j.id == oldJugeId) opt.selected = true;
                selectJuge.appendChild(opt);
            });
            selectJuge.disabled = false;
        }
    } catch (e) {
        selectJuge.innerHTML = '<option value="">— Erreur de chargement —</option>';
        selectJuge.disabled  = false;
    }
}

selectDt?.addEventListener('change', onDtChange);

// ══════════════════════════════════════════════════════════════════
// POSITION ÉTABLISSEMENT → afficher/masquer les blocs condamnation
// ══════════════════════════════════════════════════════════════════
const radios            = document.querySelectorAll('input[name="position_institution_etab"]');
const blocEtab          = document.getElementById('bloc-etab-condamne');
const blocPartiesAdv    = document.getElementById('bloc-parties-adverses');
const hiddenEtab        = document.getElementById('hidden_etab_partie');
const montantEtab       = document.getElementById('montant_etab');

function onPositionChange(radio) {
    const label = radio.dataset.label ?? '';

    // Contre ou partiel → l'établissement est condamné
    const etabCondamne = label.includes('contre') || label.includes('partiel');
    // Pour ou avec → les parties adverses sont condamnées
    const adverseCondamne = label.includes('pour') || label.includes('avec');

    // Bloc établissement condamné
    if (blocEtab) {
        blocEtab.classList.toggle('d-none', !etabCondamne);
    }
    if (hiddenEtab) {
        hiddenEtab.disabled = !etabCondamne;
    }
    if (montantEtab) {
        montantEtab.required = etabCondamne;
        if (!etabCondamne) montantEtab.value = '';
    }

    // Bloc parties adverses condamnées
    if (blocPartiesAdv) {
        blocPartiesAdv.classList.toggle('d-none', !adverseCondamne);
    }
    // Décocher les parties adverses si on passe à "contre"
    if (!adverseCondamne) {
        document.querySelectorAll('#bloc-parties-adverses input[type="checkbox"]')
            .forEach(cb => cb.checked = false);
    }
}

radios.forEach(r => {
    r.addEventListener('change', () => onPositionChange(r));
});

// ══════════════════════════════════════════════════════════════════
// INIT au chargement (présélections + old())
// ══════════════════════════════════════════════════════════════════
window.addEventListener('DOMContentLoaded', () => {
    // Déclencher la cascade instance → juges + date
    if (selectDt?.value) onDtChange();

    // Restaurer la position si old()
    const checkedRadio = document.querySelector('input[name="position_institution_etab"]:checked');
    if (checkedRadio) onPositionChange(checkedRadio);
});
</script>
@endpush