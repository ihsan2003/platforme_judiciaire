@php
    $r = $reclamation ?? null;

    $typeCourant = old('id_type_reclamation', $r?->id_type_reclamation);
    $visible     = $idTypeJudiciaire && (string) $typeCourant === (string) $idTypeJudiciaire;

    $selDegre      = old('id_degre', $r?->id_degre);
    $selTribunal   = old('id_tribunal', $r?->id_tribunal);
    $selAffaire    = old('id_type_affaire', $r?->id_type_affaire);
    // المدعي / المدعى عليه : 2 valeurs seulement, toujours opposées l'une à l'autre
    //   'institution' = la partie est_entraide, 'reclamant' = le réclamant (المشتكي)
    //   Défauts : المدعي = institution, المدعى عليه = réclamant
    $roleDemandeurEnreg = $r?->id_demandeur
        ? ((int) $r->id_demandeur === (int) ($institution?->id) ? 'institution' : 'reclamant')
        : 'institution';
    $selDemandeur = old('role_demandeur', $roleDemandeurEnreg);
    $selDefendeur = old('role_defendeur', $selDemandeur === 'institution' ? 'reclamant' : 'institution');
    $libInstitution = $institution?->nom_partie ?? 'المؤسسة';
@endphp

<div class="mb-2 d-none" id="bloc-dossier">
    <div class="row g-3">

                {{-- درجة التقاضي --}}
                <div class="col-12 pt-2">
                    <label for="id_degre" class="form-label fw-semibold small">
                        درجة التقاضي <span class="text-danger">*</span>
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
                        المحكمة <span class="text-danger">*</span>
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
                        نوع القضية (الشعبة) <span class="text-danger">*</span>
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
                    <label for="role_demandeur" class="form-label fw-semibold small">المدعي</label>
                    <select name="role_demandeur" id="role_demandeur"
                            class="form-select form-select-sm @error('role_demandeur') is-invalid @enderror">
                        <option value="institution" @selected($selDemandeur === 'institution')>{{ $libInstitution }}</option>
                        <option value="reclamant"   @selected($selDemandeur === 'reclamant')>المشتكي</option>
                    </select>
                    @error('role_demandeur')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- المدعى عليه --}}
                <div class="col-6 mt-2">
                    <label for="role_defendeur" class="form-label fw-semibold small">المدعى عليه</label>
                    <select name="role_defendeur" id="role_defendeur"
                            class="form-select form-select-sm @error('role_defendeur') is-invalid @enderror">
                        <option value="institution" @selected($selDefendeur === 'institution')>{{ $libInstitution }}</option>
                        <option value="reclamant"   @selected($selDefendeur === 'reclamant')>المشتكي</option>
                    </select>
                    @error('role_defendeur')
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

    /* ── المدعي / المدعى عليه : toujours opposés (institution ⇄ المشتكي) ── */
    const roleDem = document.getElementById('role_demandeur');
    const roleDef = document.getElementById('role_defendeur');
    const opposé  = v => v === 'institution' ? 'reclamant' : 'institution';
    roleDem.addEventListener('change', () => { roleDef.value = opposé(roleDem.value); });
    roleDef.addEventListener('change', () => { roleDem.value = opposé(roleDef.value); });

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