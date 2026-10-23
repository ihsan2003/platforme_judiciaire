{{--
    Localisation de la réclamation : الجهة > الإقليم / العمالة > الجماعة الترابية
    Variables attendues : $regions, $provinces, $communes, $idRegion, $idProvinceSel, $idCommuneSel
    Seuls id_province et id_commune sont enregistrés (id_region sert uniquement à filtrer la cascade).
--}}
<div class="card border-0 shadow-sm mb-4">

    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-geo-alt ms-2 text-primary"></i>
            الموقع الجغرافي
        </h6>
    </div>

    <div class="card-body">
        <div class="row g-3">

            <div class="col-lg-4">
                <label for="id_region" class="form-label fw-semibold small">
                    الجهة
                </label>
                <select name="id_region" id="id_region"
                        class="form-select @error('id_region') is-invalid @enderror">
                    <option value="">— اختر الجهة —</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}" @selected($idRegion == $region->id)>
                            {{ $region->region }}
                        </option>
                    @endforeach
                </select>
                @error('id_region')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-lg-4">
                <label for="id_province" class="form-label fw-semibold small">
                    الإقليم / العمالة (المدينة)
                </label>
                <select name="id_province" id="id_province"
                        class="form-select @error('id_province') is-invalid @enderror"
                        @disabled($provinces->isEmpty())>
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

            <div class="col-lg-4">
                <label for="id_commune" class="form-label fw-semibold small">
                    الجماعة الترابية
                </label>
                <select name="id_commune" id="id_commune"
                        class="form-select @error('id_commune') is-invalid @enderror"
                        @disabled($communes->isEmpty())>
                    @if($communes->isEmpty())
                        <option value="">— اختر الإقليم أولاً —</option>
                    @else
                        <option value="">— اختر الجماعة —</option>
                        @foreach($communes as $commune)
                            <option value="{{ $commune->id }}" @selected($idCommuneSel == $commune->id)>
                                {{ $commune->commune }}
                            </option>
                        @endforeach
                    @endif
                </select>
                @error('id_commune')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    /* ── Cascade الجهة > الإقليم (المدينة) > الجماعة ── */
    const selRegion   = document.getElementById('id_region');
    const selProvince = document.getElementById('id_province');
    const selCommune  = document.getElementById('id_commune');

    function resetCommunes(message = '— اختر الإقليم أولاً —') {
        selCommune.innerHTML = `<option value="">${message}</option>`;
        selCommune.disabled = true;
    }

    function resetProvinces(message = '— اختر الجهة أولاً —') {
        selProvince.innerHTML = `<option value="">${message}</option>`;
        selProvince.disabled = true;
    }

    selRegion.addEventListener('change', async function () {
        resetCommunes();

        if (!this.value) {
            resetProvinces();
            return;
        }

        selProvince.innerHTML = '<option value="">— جاري التحميل... —</option>';
        selProvince.disabled = true;

        try {
            const data = await (await fetch(`/api/regions/${this.value}/provinces`)).json();
            selProvince.innerHTML = '<option value="">— اختر الإقليم —</option>';
            data.forEach(p => selProvince.innerHTML += `<option value="${p.id}">${p.province}</option>`);
            selProvince.disabled = false;
        } catch {
            selProvince.innerHTML = '<option value="">— خطأ —</option>';
        }
    });

    selProvince.addEventListener('change', async function () {
        resetCommunes();

        if (!this.value) return;

        selCommune.innerHTML = '<option value="">— جاري التحميل... —</option>';

        try {
            const data = await (await fetch(`/api/provinces/${this.value}/communes`)).json();

            if (!data.length) {
                selCommune.innerHTML = '<option value="">— لا توجد جماعات لهذا الإقليم —</option>';
                return;
            }

            selCommune.innerHTML = '<option value="">— اختر الجماعة —</option>';
            data.forEach(c => selCommune.innerHTML += `<option value="${c.id}">${c.commune}</option>`);
            selCommune.disabled = false;
        } catch {
            selCommune.innerHTML = '<option value="">— خطأ —</option>';
        }
    });
})();
</script>
@endpush
