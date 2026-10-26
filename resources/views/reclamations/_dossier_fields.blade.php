@php
    $r = $reclamation ?? null;

    $typeCourant = old('id_type_reclamation', $r?->id_type_reclamation);
    $visible     = $idTypeJudiciaire && (string) $typeCourant === (string) $idTypeJudiciaire;

    $selDegre      = old('id_degre', $r?->id_degre);
    $selTribunal   = old('id_tribunal', $r?->id_tribunal);
    $selAffaire    = old('id_type_affaire', $r?->id_type_affaire);
    // المدعي : valeur enregistrée, sinon l'institution (partie est_entraide)
    $selDemandeur  = old('id_demandeur', $r?->id_demandeur ?? $idDemandeurDefaut);
    // المدعى عليه : valeur enregistrée, sinon vide = le réclamant (converti en partie à l'enregistrement)
    $selDefendeur  = old('id_defendeur', $r?->id_defendeur);
@endphp

<div class="mb-2 d-none" id="bloc-dossier">
    <div class="row g-3">

                {{-- درجة التقاضي --}}
                <div class="col-12 pt-2">
                    <label for="id_degre" class="form-label fw-semibold small">
                        درجة التقاضي
                    </label>
                    <select name="id_degre" id="id_degre"
                            class="form-select form-select-sm @error('id_degre') is-invalid @enderror">
                        <option value="">— اختر الدرجة —</option>
                        @foreach($degres as $degre)
                            <option value="{{ $degre->id }}"
                                @selected((string) $selDegre === (string) $degre->id)>
                                {{ $degre->degre_juridiction }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_degre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- المحكمة --}}
                <div class="col-12 mt-2">
                    <label for="id_tribunal" class="form-label fw-semibold small">
                        المحكمة
                    </label>
                    <select name="id_tribunal" id="id_tribunal"
                            class="form-select form-select-sm @error('id_tribunal') is-invalid @enderror">
                        <option value="">— اختر المحكمة —</option>
                        @foreach($tribunaux as $tribunal)
                            <option value="{{ $tribunal->id }}"
                                @selected((string) $selTribunal === (string) $tribunal->id)>
                                {{ $tribunal->nom_tribunal }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_tribunal')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- نوع القضية --}}
                <div class="col-12 mt-2">
                    <label for="id_type_affaire" class="form-label fw-semibold small">
                        نوع القضية (الشعبة)
                    </label>
                    <select name="id_type_affaire" id="id_type_affaire"
                            class="form-select form-select-sm @error('id_type_affaire') is-invalid @enderror">
                        <option value="">— اختر النوع —</option>
                        @foreach($typesAffaire as $affaire)
                            <option value="{{ $affaire->id }}"
                                @selected((string) $selAffaire === (string) $affaire->id)>
                                {{ $affaire->affaire }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_type_affaire')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- المدعي --}}
                <div class="col-6 mt-2">
                    <label for="id_demandeur" class="form-label fw-semibold small">
                        المدعي
                    </label>
                    <select name="id_demandeur" id="id_demandeur"
                            class="form-select form-select-sm @error('id_demandeur') is-invalid @enderror">
                        <option value="">— اختر المدعي —</option>
                        @foreach($parties as $partie)
                            <option value="{{ $partie->id }}"
                                @selected((string) $selDemandeur === (string) $partie->id)>
                                {{ $partie->nom_partie }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_demandeur')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- المدعى عليه --}}
                <div class="col-6 mt-2">
                    <label for="id_defendeur" class="form-label fw-semibold small">
                        المدعى عليه
                    </label>
                    <select name="id_defendeur" id="id_defendeur"
                            class="form-select form-select-sm @error('id_defendeur') is-invalid @enderror">
                        <option value="">المشتكي (تلقائياً)</option>
                        @foreach($parties as $partie)
                            <option value="{{ $partie->id }}"
                                @selected((string) $selDefendeur === (string) $partie->id)>
                                {{ $partie->nom_partie }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_defendeur')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

            </div>
</div>


@push('scripts')
<script>
(function () {
    const bloc          = document.getElementById('bloc-dossier');
    const selType       = document.querySelector('select[name="id_type_reclamation"]');
    const idJudiciaire  = @json($idTypeJudiciaire);
    const selDegre      = document.getElementById('id_degre');
    const tribunaux     = @json($tribunaux);

    /* ── Afficher le bloc seulement pour le type « منازعات قضائية » ── */
    function basculerBloc() {
        const actif = idJudiciaire !== null && selType && String(selType.value) === String(idJudiciaire);
        bloc.classList.toggle('d-none', !actif);
    }
    if (selType) {
        selType.addEventListener('change', basculerBloc);
    }
    basculerBloc();

    /* ── Listes de recherche ── */
    const opts = { allowEmptyOption: true, maxOptions: 1000, closeAfterSelect: true };
    const tsTribunal  = new TomSelect('#id_tribunal',  { ...opts, placeholder: 'ابحث عن المحكمة...' });
    new TomSelect('#id_demandeur', { ...opts, placeholder: 'ابحث عن المدعي...' });
    new TomSelect('#id_defendeur', { ...opts, placeholder: 'المشتكي (تلقائيا)' });

    /* ── Les tribunaux proposés dépendent de la درجة choisie ── */
    function filtrerTribunaux() {
        const degre   = selDegre.value;
        const courant = tsTribunal.getValue();

        tsTribunal.clear(true);
        tsTribunal.clearOptions();

        tribunaux
            .filter(t => !degre || !t.id_degre || String(t.id_degre) === degre)
            .forEach(t => tsTribunal.addOption({ value: String(t.id), text: t.nom_tribunal }));

        tsTribunal.refreshOptions(false);

        if (courant && tsTribunal.options[courant]) {
            tsTribunal.setValue(courant, true);
        }
    }
    selDegre.addEventListener('change', filtrerTribunaux);
    filtrerTribunaux();
})();
</script>
@endpush
