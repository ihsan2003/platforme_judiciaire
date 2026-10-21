{{-- resources/views/avertissements/_form.blade.php
     Champs communs create/edit. Variable optionnelle : $avertissement
     Variables attendues : $typesAffaire, $statuts, $idStatutDefaut, $regions, $provinces, $tribunaux, $idRegion, $parties,
     $idDemandeurDefaut, $idDefendeurDefaut --}}
@php
    $a = $avertissement ?? null;
    $idProvinceSel = old('id_province', $a?->id_province);
    $idTribunalSel = old('id_tribunal', $a?->id_tribunal);
@endphp

<div class="row g-4">

    {{-- ══ Informations principales ══ --}}
    <div class="col-12 col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                    معلومات الإنذار الأساسية
                </h6>
            </div>

            <div class="card-body p-4">
                <div class="row g-3">

                    {{-- نوعية الملف --}}
                    <div class="col-6 mb-2">
                        <label for="id_type_affaire" class="form-label fw-semibold small">
                            نوعية الملف <span class="text-danger">*</span>
                        </label>
                        <select name="id_type_affaire" id="id_type_affaire"
                                class="form-select @error('id_type_affaire') is-invalid @enderror" required>
                            <option value="">— اختر نوعية الملف —</option>
                            @foreach($typesAffaire as $type)
                                <option value="{{ $type->id }}"
                                    @selected(old('id_type_affaire', $a?->id_type_affaire) == $type->id)>
                                    {{ $type->affaire }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_type_affaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- المحكمة المختصة : liste complète, indépendante de l'إقليم --}}
                    <div class="col-6 mb-2">
                        <label for="id_tribunal" class="form-label fw-semibold small">
                            المحكمة المختصة <span class="text-danger">*</span>
                        </label>
                        <select name="id_tribunal" id="id_tribunal"
                                class="@error('id_tribunal') is-invalid @enderror" required>
                            <option value="">— اختر المحكمة —</option>
                            @foreach($tribunaux as $tribunal)
                                <option value="{{ $tribunal->id }}" @selected($idTribunalSel == $tribunal->id)>
                                    {{ $tribunal->nom_tribunal }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_tribunal')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    {{-- الجهة > الإقليم / العمالة (المدينة) --}}
                    <div class="col-12">
                        <div class="p-3 bg-light rounded border">
                            <div class="row g-3">

                                <div class="col-lg-6">
                                    <label for="id_region" class="form-label fw-semibold small">
                                        الجهة <span class="text-danger">*</span>
                                    </label>
                                    <select name="id_region" id="id_region" class="form-select" required>
                                        <option value="">— اختر الجهة —</option>
                                        @foreach($regions as $region)
                                            <option value="{{ $region->id }}" @selected($idRegion == $region->id)>
                                                {{ $region->region }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-6">
                                    <label for="id_province" class="form-label fw-semibold small">
                                        الإقليم / العمالة (المدينة) <span class="text-danger">*</span>
                                    </label>
                                    <select name="id_province" id="id_province"
                                            class="form-select @error('id_province') is-invalid @enderror"
                                            @disabled($provinces->isEmpty()) required>
                                        @if($provinces->isEmpty())
                                            <option value="">— اختر الجهة أولاً —</option>
                                        @else
                                            <option value="">— اختر الإقليم —</option>
                                            @foreach($provinces as $province)
                                                <option value="{{ $province->id }}" @selected($idProvinceSel == $province->id)>
                                                    {{ $province->province }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('id_province')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- موضوع النزاع --}}
                    <div class="col-12 mb-2">
                        <label for="objet_litige" class="form-label fw-semibold small">موضوع النزاع</label>
                        <textarea name="objet_litige" id="objet_litige" rows="3"
                                  class="form-control @error('objet_litige') is-invalid @enderror">{{ old('objet_litige', $a?->objet_litige) }}</textarea>
                        @error('objet_litige')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- الإجراء المتخذ --}}
                    <div class="col-12 mb-2">
                        <label for="action_service" class="form-label fw-semibold small">
                            الإجراء المتخذ من طرف مصلحة المنازعات والشؤون القانونية
                        </label>
                        <textarea name="action_service" id="action_service" rows="3"
                                  class="form-control @error('action_service') is-invalid @enderror">{{ old('action_service', $a?->action_service) }}</textarea>
                        @error('action_service')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- ملاحظات --}}
                    <div class="col-12 mb-2">
                        <label for="commentaire" class="form-label fw-semibold small">
                            ملاحظات
                        </label>
                        <textarea name="commentaire" id="commentaire" rows="3"
                                  class="form-control @error('commentaire') is-invalid @enderror">{{ old('commentaire', $a?->commentaire) }}</textarea>
                        @error('commentaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ══ Colonne latérale : حالة + أطراف + actions ══ --}}
    <div class="col-12 col-md-4">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-flag me-2 text-primary"></i>الحالة
                </h6>
            </div>
            <div class="card-body p-4">
                <label for="id_statut_avertissement" class="form-label fw-semibold small">حالة الإنذار</label>
                <select name="id_statut_avertissement" id="id_statut_avertissement"
                        class="form-select @error('id_statut_avertissement') is-invalid @enderror">
                    @foreach($statuts as $statut)
                        <option value="{{ $statut->id }}"
                            @selected(old('id_statut_avertissement', $a?->id_statut_avertissement ?? $idStatutDefaut) == $statut->id)>
                            {{ $statut->statut_avertissement }}
                        </option>
                    @endforeach
                </select>
                @error('id_statut_avertissement')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-people me-2 text-success"></i>الأطراف
                </h6>
            </div>

            <div class="card-body p-4">

                {{-- المدعي --}}
                <div class="mb-3">
                    <label for="id_demandeur" class="form-label fw-semibold small">
                        المدعي
                    </label>

                    <select
                        name="id_demandeur"
                        id="id_demandeur"
                        data-create-url="{{ route('parties.create') }}"
                        class="@error('id_demandeur') is-invalid @enderror"
                    >
                        <option value="">— اختر المدعي —</option>

                        @foreach($parties as $partie)
                            <option
                                value="{{ $partie->id }}"
                                @selected(
                                    old('id_demandeur', $a ? $a->id_demandeur : $idDemandeurDefaut)
                                    == $partie->id
                                )
                            >
                                {{ $partie->nom_partie }}
                            </option>
                        @endforeach
                    </select>

                    @error('id_demandeur')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- المدعى عليه --}}
                <div>
                    <label for="id_defendeur" class="form-label fw-semibold small">
                        المدعى عليه
                    </label>

                    <select
                        name="id_defendeur"
                        id="id_defendeur"
                        data-create-url="{{ route('parties.create') }}"
                        class="@error('id_defendeur') is-invalid @enderror"
                    >
                        <option value="">— اختر المدعى عليه —</option>

                        @foreach($parties as $partie)
                            <option
                                value="{{ $partie->id }}"
                                @selected(
                                    old('id_defendeur', $a ? $a->id_defendeur : $idDefendeurDefaut)
                                    == $partie->id
                                )
                            >
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

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>
                {{ $a ? 'حفظ التعديلات' : 'حفظ الإنذار' }}
            </button>
            <a href="{{ $a ? route('avertissements.show', $a) : route('avertissements.index') }}"
               class="btn btn-outline-secondary">إلغاء</a>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Cascade Région > Province (المدينة) ── */
    const selRegion   = document.getElementById('id_region');
    const selProvince = document.getElementById('id_province');

    selRegion.addEventListener('change', async function () {
        selProvince.innerHTML = '<option value="">— جاري التحميل... —</option>';
        selProvince.disabled = true;
        if (!this.value) {
            selProvince.innerHTML = '<option value="">— اختر الجهة أولاً —</option>';
            return;
        }
        try {
            const data = await (await fetch(`/api/regions/${this.value}/provinces`)).json();
            selProvince.innerHTML = '<option value="">— اختر الإقليم —</option>';
            data.forEach(p => selProvince.innerHTML += `<option value="${p.id}">${p.province}</option>`);
            selProvince.disabled = false;
        } catch { selProvince.innerHTML = '<option value="">— خطأ —</option>'; }
    });

    /* ── Listes de recherche (TomSelect) ── */
    const opts = { allowEmptyOption: true, maxOptions: 1000, placeholder: 'ابحث...' };
    new TomSelect('#id_tribunal', opts);

    
    /* ── Listes de recherche TomSelect avec création de partie ── */

    function initialiserPartie(selector, placeholder) {
        const element = document.querySelector(selector);

        if (!element) return;

        return new TomSelect(element, {
            allowEmptyOption: true,
            maxOptions: 1000,
            placeholder: placeholder,
            createOnBlur: false,
            closeAfterSelect: true,

            create: function (input) {
                const createUrl = element.dataset.createUrl;

                if (createUrl && input.trim()) {
                    window.location.href =
                        createUrl + '?nom=' + encodeURIComponent(input.trim());
                }

                // Ne pas ajouter le texte comme une partie existante.
                return false;
            },

            sortField: [
                { field: 'text', direction: 'asc' }
            ],

            render: {
                no_results: function () {
                    return '<div class="no-results p-2">لا توجد نتائج</div>';
                },

                option_create: function (data, escape) {
                    return '<div class="create p-2">' +
                        '➕ إضافة "' + escape(data.input) + '"' +
                        '</div>';
                }
            }
        });
    }

    initialiserPartie('#id_demandeur', 'ابحث عن المدعي...');
    initialiserPartie('#id_defendeur', 'ابحث عن المدعى عليه...');


});
</script>
@endpush
