@extends('layouts.app')

@section('title', 'إضافة حكم جديد')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">الرئيسية</a>
    </li>

    <li class="breadcrumb-item">
        <a href="{{ route('jugements.index') }}">الأحكام</a>
    </li>

    <li class="breadcrumb-item active">
        حكم جديد
    </li>
@endsection

@section('content')

    {{-- ══════════════════════════════════════════════════════════════════════════
        Page Header
    ═══════════════════════════════════════════════════════════════════════════ --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-plus-circle text-primary me-2"></i>
                حكم جديد
            </h4>
            <p class="text-muted small mb-0">
                أدخل معلومات الحكم بدقة لضمان متابعة فعّالة.
            </p>
        </div>

        <a href="{{ route('jugements.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-right me-1"></i>
            العودة إلى القائمة
        </a>
    </div>

    <form action="{{ route('jugements.store') }}" method="POST" id="jugementForm" dir="rtl">
        @csrf

        <div class="row g-4">
            <div class="col-lg-8 mx-auto">

                {{-- ─────────────────────────────────────────────────────────────────
                    بطاقة المعلومات الرئيسية
                ───────────────────────────────────────────────────────────────── --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            المعلومات الرئيسية
                        </h6>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-3">

                            {{-- الملف / المحكمة --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">
                                    الملف / المحكمة
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-folder2-open text-muted"></i>
                                    </span>
                                    <select name="id_dossier_tribunal"
                                            id="id_dossier_tribunal"
                                            class="form-select border-start-0 @error('id_dossier_tribunal') is-invalid @enderror"
                                            required>
                                        <option value="">— اختر —</option>
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
                            </div>

                            {{-- القاضي --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">
                                    القاضي
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-person text-muted"></i>
                                    </span>
                                    <select id="id_juge"
                                            name="id_juge"
                                            class="form-select border-start-0 @error('id_juge') is-invalid @enderror"
                                            required>
                                        <option value="">— اختر الملف أولاً —</option>
                                    </select>
                                    @error('id_juge')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- تاريخ الحكم --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">
                                    تاريخ الحكم
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-calendar-event text-muted"></i>
                                    </span>
                                    <input type="date"
                                        name="date_jugement"
                                        id="date_jugement"
                                        class="form-control border-start-0 @error('date_jugement') is-invalid @enderror"
                                        value="{{ old('date_jugement') }}"
                                        required>
                                    @error('date_jugement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- حكم نهائي --}}
                            <div class="col-md-6 d-flex align-items-end pb-1">
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        name="est_definitif"
                                        value="1"
                                        id="est_definitif"
                                        @checked(old('est_definitif'))>
                                    <label class="form-check-label small fw-semibold text-primary"
                                        for="est_definitif">
                                        حكم نهائي
                                    </label>
                                </div>
                            </div>

                            {{-- منطوق الحكم --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-dark">
                                    منطوق الحكم
                                </label>
                                <textarea name="contenu_dispositif"
                                        class="form-control @error('contenu_dispositif') is-invalid @enderror"
                                        rows="3"
                                        placeholder="محتوى الحكم...">{{ old('contenu_dispositif') }}</textarea>
                                @error('contenu_dispositif')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>

                {{-- ─────────────────────────────────────────────────────────────────
                    بطاقة نتيجة الحكم - المؤسسة
                ───────────────────────────────────────────────────────────────── --}}
                @php
                    $institution   = $partiesDossier->first(fn($dp) => $dp->partie?->est_entraide);
                    $autresParties = $partiesDossier->filter(fn($dp) => !$dp->partie?->est_entraide);
                @endphp

                @if($institution)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                        <i class="bi bi-building-fill text-primary"></i>
                        <h6 class="mb-0 fw-semibold">
                            وضعية المؤسسة :
                            <strong class="text-primary">{{ $institution->partie->nom_partie }}</strong>
                        </h6>
                    </div>

                    <div class="card-body p-4">

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-dark">
                                وضعية المؤسسة في هذا الحكم
                                <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($positionsInstitution as $pos)
                                @php
                                    $color = match(true) {
                                        str_contains(strtolower($pos->position), 'مع') => 'success',
                                        str_contains(strtolower($pos->position), 'ضد') => 'danger',
                                        str_contains(strtolower($pos->position), 'جزئي') => 'warning',
                                        default => 'secondary',
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
                                        <i class="bi bi-{{ $icon }} me-1"></i>
                                        {{ $pos->position }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            @error('position_institution_etab')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- المؤسسة محكوم عليها --}}
                        <div id="bloc-etab-condamne" class="d-none">

                            <div id="condamnation-card" class="condamnation-card danger">

                                <div class="condamnation-header">

                                    <div class="d-flex align-items-center">

                                        <div id="condamnation-icon" class="condamnation-icon">

                                            <i class="bi bi-building-fill"></i>

                                        </div>

                                        <div>

                                            <h6 class="mb-1 fw-bold">
                                                {{ $institution->partie->nom_partie }}
                                            </h6>

                                            <span id="condamnation-badge" class="condamnation-badge">

                                                <i class="bi bi-exclamation-circle-fill me-1"></i>

                                                المؤسسة محكوم عليها

                                            </span>

                                        </div>

                                    </div>

                                </div>

                                <input type="hidden"
                                    name="parties[]"
                                    id="hidden_etab_partie"
                                    value="{{ $institution->partie->id }}"
                                    disabled>

                                <div class="mt-4">

                                    <label class="form-label fw-semibold text-muted small mb-2">

                                        المبلغ المحكوم به

                                    </label>

                                    <div class="input-group amount-group">

                                        <span class="input-group-text">

                                            <i class="bi bi-cash-stack me-2"></i>

                                            درهم

                                        </span>

                                        <input type="number"
                                            step="0.01"
                                            min="0"
                                            name="montants[{{ $institution->partie->id }}]"
                                            id="montant_etab"
                                            class="form-control"
                                            value="{{ old('montants.'.$institution->partie->id) }}"
                                            placeholder="أدخل المبلغ المحكوم به">

                                    </div>

                                </div>

                            </div>

                        </div>

                        {{--  بطاقة الأطراف الأخرى --}}
                        <div id="bloc-parties-adverses" class="d-none">
                            <div class="condamnation-card success">

                                <div class="condamnation-header">

                                    <div class="d-flex align-items-center">

                                        <div class="condamnation-icon">

                                            <i class="bi bi-people-fill"></i>

                                        </div>

                                        <div>

                                            <h6 class="mb-1 fw-bold">

                                                الأطراف الأخرى المحكوم عليها

                                            </h6>

                                            <span class="condamnation-badge">

                                                <i class="bi bi-check-circle-fill me-1"></i>

                                                المؤسسة رابحة في هذا الحكم

                                            </span>

                                        </div>

                                    </div>

                                </div>

                                @if($autresParties->isEmpty())

                                    <div class="text-center py-4 text-muted">

                                        <i class="bi bi-people fs-1 opacity-25 d-block mb-2"></i>

                                        لا توجد أطراف أخرى في هذا الملف

                                    </div>

                                @else

                                    <div class="row g-3 mt-2">

                                        @foreach($autresParties as $dp)

                                        <div class="col-md-6">

                                            <div class="party-item-card">

                                                <div class="d-flex justify-content-between align-items-center mb-3">

                                                    <div class="form-check">

                                                        <input class="form-check-input"
                                                            type="checkbox"
                                                            name="parties[]"
                                                            value="{{ $dp->partie->id }}"
                                                            id="partie_{{ $dp->partie->id }}"
                                                            @checked(old('parties') && in_array($dp->partie->id, old('parties', [])))>

                                                        <label class="form-check-label fw-semibold"
                                                            for="partie_{{ $dp->partie->id }}">

                                                            {{ $dp->partie->nom_partie }}

                                                        </label>

                                                    </div>

                                                    <span class="party-type-badge">

                                                        {{ $dp->typePartie->type_partie ?? '—' }}

                                                    </span>

                                                </div>

                                                <div class="input-group amount-group">

                                                    <span class="input-group-text">

                                                        <i class="bi bi-cash-stack me-2"></i>

                                                        درهم

                                                    </span>

                                                    <input type="number"
                                                        step="0.01"
                                                        min="0"
                                                        name="montants[{{ $dp->partie->id }}]"
                                                        class="form-control"
                                                        value="{{ old('montants.'.$dp->partie->id) }}"
                                                        placeholder="المبلغ المحكوم به">

                                                </div>

                                            </div>

                                        </div>

                                        @endforeach

                                    </div>

                                @endif

                            </div>
                        </div>

                    </div>
                </div>
                @else
                <div class="alert alert-warning small mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    لا يوجد طرف محدد كمؤسسة في هذا الملف.
                </div>
                @endif

                {{-- ─────────────────────────────────────────────────────────────────
                    أزرار الإرسال والإلغاء
                ───────────────────────────────────────────────────────────────── --}}
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-2"></i>
                        إنشاء الحكم
                    </button>
                    <a href="{{ route('jugements.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-x-lg me-2"></i>
                        إلغاء
                    </a>
                </div>

            </div>
        </div>

    </form>

@endsection

@push('styles')
<style>
    .form-control,
    .form-select {
        text-align: right;
    }

    .input-group > .form-control,
    .input-group > .form-select {
        border-radius: 0.375rem 0 0 0.375rem !important;
    }

    .input-group > .input-group-text:first-child {
        border-radius: 0 0.375rem 0.375rem 0 !important;
    }

    .card {
        border-radius: 0.5rem;
        transition: box-shadow 0.2s ease-in-out;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }

    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }

    .bi {
        vertical-align: -0.125em;
    }

    .badge {
        font-weight: 500;
    }
    /* ==========================
    CARTE CONDAMNATION
    ========================== */

    .condamnation-card{

        background:#fff;

        border:1px solid #f1d3d6;

        border-right:5px solid #dc3545;

        border-radius:16px;

        padding:20px;

        transition:all .3s ease;

        box-shadow:0 4px 15px rgba(0,0,0,.04);

    }

    .condamnation-header{

        display:flex;

        align-items:center;

        justify-content:space-between;

    }

    .condamnation-icon{

        width:48px;

        height:48px;

        border-radius:12px;

        display:flex;

        align-items:center;

        justify-content:center;

        font-size:1.2rem;

        margin-left:12px;

        transition:.3s;

    }

    .condamnation-badge{

        display:inline-flex;

        align-items:center;

        padding:6px 12px;

        border-radius:30px;

        font-size:.75rem;

        font-weight:600;

        transition:.3s;

    }

    .amount-group{

        overflow:hidden;

        border-radius:12px;

    }

    .amount-group .input-group-text{

        border:none;

        font-weight:600;

        transition:.3s;

    }

    .amount-group .form-control{

        min-height:48px;

    }

    /* ==========================
    DANGER
    ========================== */

    .condamnation-card.danger{

        border-color:#f1d3d6;

        border-right-color:#dc3545;

        background:#fff;

    }

    .condamnation-card.danger .condamnation-icon{

        background:rgba(220,53,69,.1);

        color:#dc3545;

    }

    .condamnation-card.danger .condamnation-badge{

        background:rgba(220,53,69,.1);

        color:#dc3545;

    }

    .condamnation-card.danger .input-group-text{

        background:#dc3545;

        color:#fff;

    }

    /* ==========================
    WARNING
    ========================== */

    .condamnation-card.warning{

        border-color:#ffe69c;

        border-right-color:#ffc107;

    }

    .condamnation-card.warning .condamnation-icon{

        background:rgba(255,193,7,.15);

        color:#b58100;

    }

    .condamnation-card.warning .condamnation-badge{

        background:rgba(255,193,7,.15);

        color:#b58100;

    }

    .condamnation-card.warning .input-group-text{

        background:#ffc107;

        color:#212529;

    }


    /* ==========================
    SUCCESS
    ========================== */

    .condamnation-card.success{

        border-color:#d1e7dd;

        border-right-color:#198754;

        background:#ffffff;

    }

    .condamnation-card.success .condamnation-icon{

        background:rgba(25,135,84,.12);

        color:#198754;

    }

    .condamnation-card.success .condamnation-badge{

        background:rgba(25,135,84,.12);

        color:#198754;

    }

    .condamnation-card.success .input-group-text{

        background:#198754;

        color:#fff;

    }

    /* ==========================
    PARTY ITEM
    ========================== */

    .party-item-card{

        border:1px solid #e9ecef;

        border-radius:14px;

        padding:16px;

        background:#fff;

        transition:.25s;

    }

    .party-item-card:hover{

        border-color:#198754;

        box-shadow:0 5px 15px rgba(25,135,84,.08);

    }

    .party-type-badge{

        background:rgba(25,135,84,.1);

        color:#198754;

        padding:5px 10px;

        border-radius:20px;

        font-size:.75rem;

        font-weight:600;

    }

    .party-item-card .form-check-input:checked{

        background:#198754;

        border-color:#198754;

    }

</style>
@endpush

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

    // old() لاسترجاع القاضي بعد خطأ التحقق
    const oldJugeId = "{{ old('id_juge') }}";

    const selectDt    = document.getElementById('id_dossier_tribunal');
    const selectJuge  = document.getElementById('id_juge');
    const dateInput   = document.getElementById('date_jugement');
    const dateHint    = document.getElementById('date_houkm_hint');

    // ══════════════════════════════════════════════════════════════════
    // تغيير الملف → تحديث التاريخ + القضاة
    // ══════════════════════════════════════════════════════════════════
    async function onDtChange() {

        const dtId = selectDt?.value;
        const info = dtMap[dtId];

        // تاريخ الحكم
        const date = info?.date ?? '';

        if (dateInput) {
            dateInput.value    = date;
            dateInput.readOnly = !!date;
        }

        if (dateHint) {
            dateHint.classList.toggle('d-none', !date);
        }

        // القضاة حسب المحكمة
        if (!selectJuge) return;

        if (!dtId || !info) {

            selectJuge.innerHTML =
                '<option value="">— اختر الملف أولاً —</option>';

            selectJuge.disabled = true;

            return;
        }

        selectJuge.innerHTML =
            '<option value="">— جاري التحميل... —</option>';

        selectJuge.disabled = true;

        try {

            const res   =
                await fetch(`/api/tribunaux/${info.tribunalId}/juges`);

            const juges = await res.json();

            selectJuge.innerHTML =
                '<option value="">— اختر القاضي —</option>';

            if (juges.length === 0) {

                selectJuge.innerHTML =
                    '<option value="">— لا يوجد قضاة —</option>';

            } else {

                juges.forEach(j => {

                    const opt = document.createElement('option');

                    opt.value =
                        j.id;

                    opt.textContent =
                        (j.grade ? j.grade + ' ' : '') + j.nom_complet;

                    if (oldJugeId && j.id == oldJugeId)
                        opt.selected = true;

                    selectJuge.appendChild(opt);

                });

                selectJuge.disabled = false;
            }

        } catch (e) {

            selectJuge.innerHTML =
                '<option value="">— خطأ في التحميل —</option>';

            selectJuge.disabled = false;

        }
    }

    selectDt?.addEventListener('change', onDtChange);

    // ══════════════════════════════════════════════════════════════════
    // وضعية المؤسسة → إظهار/إخفاء البلوكات
    // ══════════════════════════════════════════════════════════════════
    const radios         = document.querySelectorAll('input[name="position_institution_etab"]');
    const blocEtab       = document.getElementById('bloc-etab-condamne');
    const blocPartiesAdv = document.getElementById('bloc-parties-adverses');
    const hiddenEtab     = document.getElementById('hidden_etab_partie');
    const montantEtab    = document.getElementById('montant_etab');

    function onPositionChange(radio) {

    const label = radio.dataset.label ?? '';

    const etabCondamne =
        label.includes('ضد') || label.includes('جزئي');

    const adverseCondamne =
        label.includes('مع');

    // =========================
    // Institution
    // =========================

    if (blocEtab) {
        blocEtab.classList.toggle('d-none', !etabCondamne);
    }

    if (hiddenEtab) {
        hiddenEtab.disabled = !etabCondamne;
    }

    if (montantEtab) {

        montantEtab.required = etabCondamne;

        if (!etabCondamne) {
            montantEtab.value = '';
        }
    }

    // =========================
    // Parties adverses
    // =========================

    if (blocPartiesAdv) {

        blocPartiesAdv.classList.toggle(
            'd-none',
            !adverseCondamne
        );
    }

    if (!adverseCondamne) {

        document.querySelectorAll(
            '#bloc-parties-adverses input[type="checkbox"]'
        ).forEach(cb => {

            cb.checked = false;

        });

        document.querySelectorAll(
            '#bloc-parties-adverses input[type="number"]'
        ).forEach(input => {

            input.value = '';

        });
    }

    }


    radios.forEach(r => {
        r.addEventListener('change', () => onPositionChange(r));
    });

    // ══════════════════════════════════════════════════════════════════
    // INIT
    // ══════════════════════════════════════════════════════════════════
    window.addEventListener('DOMContentLoaded', () => {

        if (selectDt?.value)
            onDtChange();

        const checkedRadio =
            document.querySelector('input[name="position_institution_etab"]:checked');

        if (checkedRadio)
            onPositionChange(checkedRadio);

    });
</script>
<script>

    document.addEventListener('DOMContentLoaded', function () {

        const radios = document.querySelectorAll(
            'input[name="position_institution_etab"]'
        );

        const bloc = document.getElementById('bloc-etab-condamne');

        const card = document.getElementById('condamnation-card');

        const hiddenPartie = document.getElementById('hidden_etab_partie');

        function updateCondamnationBlock() {

            const selected = document.querySelector(
                'input[name="position_institution_etab"]:checked'
            );

            if (!selected) {

                bloc.classList.add('d-none');
                return;
            }

            const label = selected.dataset.label.trim();

            card.classList.remove('danger', 'warning');

            if (label.includes('ضد')) {

                bloc.classList.remove('d-none');

                hiddenPartie.disabled = false;

                card.classList.add('danger');

            }

            else if (label.includes('جزئي')) {

                bloc.classList.remove('d-none');

                hiddenPartie.disabled = false;

                card.classList.add('warning');

            }

            else {

                bloc.classList.add('d-none');

                hiddenPartie.disabled = true;
            }
        }

        radios.forEach(radio => {

            radio.addEventListener('change', updateCondamnationBlock);

        });

        updateCondamnationBlock();

    });

</script>
@endpush