@extends('layouts.app')

@section('title', 'ملف قضائي جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dossiers.index') }}">الملفات</a></li>
    <li class="breadcrumb-item active">ملف جديد</li>
@endsection

@section('content')

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-folder-plus text-primary me-2"></i>إضافة ملف قضائي جديد
            </h4>
            <p class="text-muted small mb-0">قم بإدخال معلومات الملف بدقة لضمان المتابعة.</p>
        </div>
        <a href="{{ route('dossiers.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-right me-1"></i>العودة إلى القائمة
        </a>
    </div>

    <form action="{{ route('dossiers.store') }}" method="POST" id="dossierForm">
    @csrf

        <div class="row g-4">

            {{-- Informations principales --}}
            <div class="col-12 col-md-8">

                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-folder2-open me-2 text-primary"></i>
                            معلومات الملف الأساسية
                        </h6>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-3">

                            {{-- الشعبة --}}
                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold small">
                                    الشعبة
                                    <span class="text-danger">*</span>
                                </label>

                                <select
                                    name="id_type_affaire"
                                    id="id_type_affaire"
                                    class="form-select @error('id_type_affaire') is-invalid @enderror"
                                    required
                                >
                                    <option value="" data-code="">— اختر الشعبة —</option>

                                    @foreach($typesAffaire as $type)
                                        <option
                                            value="{{ $type->id }}"
                                            data-code="{{ $type->code }}"
                                            @selected(old('id_type_affaire') == $type->id)
                                        >
                                            {{ $type->affaire }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('id_type_affaire')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- المحكمة (الدرجة الأولى) + رقم الملف بهذه المحكمة --}}
                            <div class="col-12">
                                <div class="p-3 bg-light rounded border">
                                    <div class="small fw-semibold mb-2">
                                        <i class="bi bi-bank me-1 text-primary"></i>
                                        المحكمة{{ $premierDegre ? ' — ' . $premierDegre->degre_juridiction : '' }}
                                        <span class="text-danger">*</span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="small mb-1">الجهة</label>
                                            <select id="create_region" class="form-select form-select-sm">
                                                <option value="">— اختر الجهة —</option>
                                                @foreach($regions as $region)
                                                    <option value="{{ $region->id }}">{{ $region->region }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="small mb-1">الإقليم / العمالة</label>
                                            <select id="create_province" class="form-select form-select-sm" disabled>
                                                <option value="">— اختر الجهة أولاً —</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="small mb-1">الجماعة الترابية</label>
                                            <select id="create_commune" name="id_commune" class="form-select form-select-sm" disabled>
                                                <option value="">— اختر الإقليم أولاً —</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small mb-1">المحكمة <span class="text-danger">*</span></label>
                                            <select id="create_tribunal" name="id_tribunal"
                                                    class="form-select form-select-sm @error('id_tribunal') is-invalid @enderror"
                                                    data-degre="{{ $premierDegre?->id }}" disabled required>
                                                <option value="">— اختر الإقليم أولاً —</option>
                                            </select>
                                            @error('id_tribunal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small mb-1">الغرفة</label>
                                            <select id="create_chambre" name="id_chambre"
                                                    class="form-select form-select-sm @error('id_chambre') is-invalid @enderror" disabled>
                                                <option value="">— اختر المحكمة أولاً —</option>
                                            </select>
                                            @error('id_chambre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="small fw-semibold mb-2">رقم الملف بالمحكمة</div>
                                <x-numero-dossier format="standard" :use-old="true" />
                                @error('numero_dossier_tribunal')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- موضوع النزاع --}}
                            <div class="col-12 mb-3">
                                <label for="objet_litige" class="form-label fw-semibold small">موضوع النزاع</label>
                                <textarea
                                    name="objet_litige"
                                    id="objet_litige"
                                    rows="3"
                                    class="form-control @error('objet_litige') is-invalid @enderror">{{ old('objet_litige') }}</textarea>
                                @error('objet_litige')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- الإجراء المتخذ من طرف المصلحة --}}
                            <div class="col-12 mb-3">
                                <label for="action_service" class="form-label fw-semibold small">الإجراء المتخذ من طرف المصلحة</label>
                                <textarea
                                    name="action_service"
                                    id="action_service"
                                    rows="3"
                                    class="form-control @error('action_service') is-invalid @enderror">{{ old('action_service') }}</textarea>
                                @error('action_service')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            {{-- Dates --}}
            <div class="col-12 col-md-4">

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-calendar3 me-2 text-success"></i>
                            التواريخ
                        </h6>
                    </div>

                    <div class="card-body p-4">

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">
                                تاريخ فتح الملف
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="date"
                                name="date_ouverture"
                                class="form-control"
                                value="{{ old('date_ouverture', date('Y-m-d')) }}"
                                required>
                        </div>

                        <div>
                            <label class="form-label fw-semibold small">
                                تاريخ الإغلاق
                            </label>

                            <input
                                type="date"
                                name="date_cloture"
                                class="form-control"
                                value="{{ old('date_cloture') }}">
                        </div>

                    </div>

                </div>

                <div class="d-grid gap-2">

                    <button type="submit"
                            class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>
                        إنشاء الملف
                    </button>

                    <a href="{{ route('dossiers.index') }}"
                    class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-2"></i>
                        إلغاء
                    </a>

                </div>

            </div>

        </div>

    </form>

@endsection

@push('scripts')
    @vite('resources/js/numero-dossier.js')
    <script>
    /* Cascade Région > Province > (Commune) / Tribunal de première instance */
    (function () {
        const selRegion   = document.getElementById('create_region');
        const selProvince = document.getElementById('create_province');
        const selCommune  = document.getElementById('create_commune');
        const selTribunal = document.getElementById('create_tribunal');
        const selChambre  = document.getElementById('create_chambre');
        if (!selRegion || !selProvince || !selTribunal) return;

        const degreId = selTribunal.dataset.degre;
        const reset = (sel, ph) => { sel.innerHTML = `<option value="">${ph}</option>`; sel.disabled = true; };
        const remplir = (sel, items, ph, label) => {
            sel.innerHTML = `<option value="">${ph}</option>` +
                items.map(i => `<option value="${i.id}">${i[label]}</option>`).join('');
            sel.disabled = false;
        };

        selRegion.addEventListener('change', async function () {
            reset(selProvince, '— جاري التحميل... —');
            reset(selCommune, '— اختر الإقليم أولاً —');
            reset(selTribunal, '— اختر الإقليم أولاً —');
            reset(selChambre, '— اختر المحكمة أولاً —');
            if (!this.value) { reset(selProvince, '— اختر الجهة أولاً —'); return; }
            try {
                const data = await (await fetch(`/api/regions/${this.value}/provinces`)).json();
                remplir(selProvince, data, '— اختر الإقليم —', 'province');
            } catch { reset(selProvince, '— خطأ —'); }
        });

        selProvince.addEventListener('change', async function () {
            reset(selCommune, '— جاري التحميل... —');
            reset(selTribunal, '— جاري التحميل... —');
            reset(selChambre, '— اختر المحكمة أولاً —');
            if (!this.value) {
                reset(selCommune, '— اختر الإقليم أولاً —');
                reset(selTribunal, '— اختر الإقليم أولاً —');
                return;
            }
            fetch(`/api/provinces/${this.value}/communes`).then(r => r.json()).then(data => {
                if (!data.length) { reset(selCommune, '— لا توجد جماعات لهذا الإقليم —'); return; }
                remplir(selCommune, data, '— اختر الجماعة —', 'commune');
            }).catch(() => reset(selCommune, '— خطأ —'));
            try {
                const data = await (await fetch(`/api/provinces/${this.value}/degres/${degreId}/tribunaux`)).json();
                if (!data.length) { reset(selTribunal, '— لا توجد محاكم متاحة —'); return; }
                remplir(selTribunal, data, '— اختر المحكمة —', 'nom_tribunal');
            } catch { reset(selTribunal, '— خطأ —'); }
        });

        // Chambres du tribunal choisi (facultatif)
        async function chargerChambres(idTribunal, preselect = '') {
            if (!selChambre) return;
            if (!idTribunal) { reset(selChambre, '— اختر المحكمة أولاً —'); return; }
            reset(selChambre, '— جاري التحميل... —');
            try {
                const data = await (await fetch(`/api/tribunaux/${idTribunal}/chambres`)).json();
                if (!data.length) { reset(selChambre, '— لا توجد غرف لهذه المحكمة —'); return; }
                remplir(selChambre, data, '— بدون غرفة —', 'chambre');
                if (preselect) selChambre.value = preselect;
            } catch { reset(selChambre, '— خطأ —'); }
        }
        selTribunal.addEventListener('change', function () { chargerChambres(this.value); });
    })();
    </script>
@endpush

@push('styles')
<style>
    .form-control:focus, .form-select:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1); }
</style>
@endpush
