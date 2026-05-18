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
        إضافة
    </li>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4" dir="rtl">

            <div class="card-body">

                <h5 class="fw-bold mb-3">

                    <i class="bi bi-plus-circle ms-2 text-primary"></i>

                    حكم جديد

                </h5>

                <form action="{{ route('jugements.store') }}"
                    method="POST">

                    @csrf

                    {{-- ══ المعلومات الرئيسية ══ --}}
                    <div class="row g-3 mb-4">

                        {{-- الملف / المحكمة --}}
                        <div class="col-md-6">

                            <label class="form-label fw-semibold small">

                                الملف / المحكمة
                                <span class="text-danger">*</span>

                            </label>

                            <select name="id_dossier_tribunal"
                                    id="id_dossier_tribunal"
                                    class="form-select @error('id_dossier_tribunal') is-invalid @enderror"
                                    required>

                                <option value="">
                                    — اختر —
                                </option>

                                @foreach($dossierTribunaux as $dt)

                                    <option value="{{ $dt->id }}"
                                        @selected(old('id_dossier_tribunal', $defaultDossierTribunalId ?? null) == $dt->id)>

                                        {{ $dt->dossier->numero_dossier_interne ?? '—' }}

                                        ·

                                        {{ $dt->tribunal->nom_tribunal ?? '—' }}

                                        ·

                                        {{ $dt->degre->degre_juridiction ?? '—' }}

                                    </option>

                                @endforeach

                            </select>

                            @error('id_dossier_tribunal')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- القاضي --}}
                        <div class="col-md-4">

                            <label class="form-label fw-semibold small">

                                القاضي
                                <span class="text-danger">*</span>

                            </label>

                            <select id="id_juge"
                                    name="id_juge"
                                    class="form-select @error('id_juge') is-invalid @enderror"
                                    required>

                                <option value="">
                                    — اختر الملف أولاً —
                                </option>

                            </select>

                            @error('id_juge')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- تاريخ الحكم --}}
                        <div class="col-md-4">

                            <label class="form-label fw-semibold small">

                                تاريخ الحكم
                                <span class="text-danger">*</span>

                            </label>

                            <input type="date"
                                name="date_jugement"
                                id="date_jugement"
                                class="form-control @error('date_jugement') is-invalid @enderror"
                                value="{{ old('date_jugement') }}"
                                required>

                            @error('date_jugement')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- حكم نهائي --}}
                        <div class="col-md-4 d-flex align-items-end text-primary pb-1">

                            <div class="form-check">

                                <input class="form-check-input"
                                    type="checkbox"
                                    name="est_definitif"
                                    value="1"
                                    id="est_definitif"
                                    @checked(old('est_definitif'))>

                                <label class="form-check-label small fw-semibold"
                                    for="est_definitif">

                                    حكم نهائي

                                </label>

                            </div>

                        </div>

                        {{-- منطوق الحكم --}}
                        <div class="col-12">

                            <label class="form-label fw-semibold small">
                                منطوق الحكم
                            </label>

                            <textarea name="contenu_dispositif"
                                    class="form-control @error('contenu_dispositif') is-invalid @enderror"
                                    rows="3"
                                    placeholder="محتوى الحكم...">{{ old('contenu_dispositif') }}</textarea>

                            @error('contenu_dispositif')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>

                    {{-- ══ النتيجة والأطراف ══ --}}
                    <h6 class="fw-semibold mb-3">

                        <i class="bi bi-balance-scale ms-2 text-primary"></i>

                        نتيجة الحكم بالنسبة للمؤسسة والأطراف المحكوم عليها

                    </h6>

                    @php
                        $institution   = $partiesDossier->first(fn($dp) => $dp->partie?->est_entraide);
                        $autresParties = $partiesDossier->filter(fn($dp) => !$dp->partie?->est_entraide);
                    @endphp

                    {{-- المؤسسة --}}
                    @if($institution)

                    <div class="card border-0 shadow-sm mb-4"
                        style="border-right: 4px solid #0d6efd !important;">

                        <div class="card-header bg-white py-3 d-flex align-items-center gap-2">

                            <i class="bi bi-building-fill text-primary"></i>

                            <h6 class="mb-0 fw-semibold">

                                وضعية المؤسسة :

                                <strong class="text-primary">
                                    {{ $institution->partie->nom_partie }}
                                </strong>

                            </h6>

                        </div>

                        <div class="card-body">

                            <div class="mb-3">

                                <label class="form-label fw-semibold small">

                                    وضعية المؤسسة في هذا الحكم
                                    <span class="text-danger">*</span>

                                </label>

                                <div class="d-flex flex-wrap gap-2">

                                    @foreach($positionsInstitution as $pos)

                                    @php
                                        $color = match(true) {
                                            str_contains(strtolower($pos->position), 'مع')
                                                || str_contains(strtolower($pos->position), 'مع')  => 'success',

                                            str_contains(strtolower($pos->position), 'ضد')        => 'danger',

                                            str_contains(strtolower($pos->position), 'جزئي')       => 'warning',

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

                                            <i class="bi bi-{{ $icon }} ms-2"></i>

                                            {{ $pos->position }}

                                        </label>

                                    </div>

                                    @endforeach

                                </div>

                                @error('position_institution_etab')
                                    <div class="text-danger small mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            {{-- المؤسسة محكوم عليها --}}
                            <div id="bloc-etab-condamne"
                                class="d-none">

                                <div class="border rounded p-3 border-danger bg-danger bg-opacity-5">

                                    <div class="d-flex align-items-center gap-2 mb-2">

                                        <i class="bi bi-building text-danger"></i>

                                        <span class="fw-semibold small">

                                            {{ $institution->partie->nom_partie }}

                                        </span>

                                        <span class="badge bg-danger me-1"
                                            style="font-size:.65rem">

                                            المؤسسة محكوم عليها

                                        </span>

                                    </div>

                                    <input type="hidden"
                                        name="parties[]"
                                        id="hidden_etab_partie"
                                        value="{{ $institution->partie->id }}"
                                        disabled>

                                    <div class="input-group input-group-sm">

                                        <span class="input-group-text bg-danger text-white border-danger">
                                            درهم
                                        </span>

                                        <input type="number"
                                            step="0.01"
                                            min="0"
                                            name="montants[{{ $institution->partie->id }}]"
                                            id="montant_etab"
                                            class="form-control"
                                            value="{{ old('montants.'.$institution->partie->id) }}"
                                            placeholder="المبلغ المحكوم به">

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    @else

                    <div class="alert alert-warning small mb-4">

                        <i class="bi bi-exclamation-triangle ms-2"></i>

                        لا يوجد طرف محدد كمؤسسة في هذا الملف.

                    </div>

                    @endif

                    {{-- الأطراف الأخرى --}}
                    <div id="bloc-parties-adverses"
                        class="{{ $autresParties->isEmpty() ? 'd-none' : 'd-none' }}">

                        <div class="card border-0 shadow-sm mb-4">

                            <div class="card-header bg-white py-3 d-flex align-items-center gap-2">

                                <i class="bi bi-people text-secondary"></i>

                                <h6 class="mb-0 fw-semibold">

                                    الأطراف الأخرى المحكوم عليها

                                </h6>

                                <span class="text-muted small me-1">

                                    (اختر الأطراف وأدخل المبالغ)

                                </span>

                            </div>

                            <div class="card-body">

                                @if($autresParties->isEmpty())

                                    <div class="text-center py-3 text-muted small">

                                        <i class="bi bi-people fs-2 d-block mb-2 opacity-25"></i>

                                        لا توجد أطراف أخرى في هذا الملف.

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

                                                    <span class="badge bg-secondary me-1"
                                                        style="font-size:.65rem">

                                                        {{ $dp->typePartie->type_partie ?? '—' }}

                                                    </span>

                                                </label>

                                            </div>

                                            <div class="input-group input-group-sm">

                                                <span class="input-group-text">
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
                    {{-- الإجراءات --}}
                    <div class="d-flex justify-content-between">

                        <a href="{{ route('jugements.index') }}"
                        class="btn btn-outline-secondary">

                            <i class="bi bi-arrow-right ms-1"></i>

                            رجوع

                        </a>

                        <button type="submit"
                                class="btn btn-primary">

                            <i class="bi bi-check-circle ms-1"></i>

                            إنشاء الحكم

                        </button>

                    </div>

                </form>

            </div>

        </div>
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

    // المؤسسة محكوم عليها
    const etabCondamne =
        label.includes('ضد') || label.includes('جزئي');

    // الأطراف الأخرى محكوم عليها
    const adverseCondamne =
        label.includes('مع') || label.includes('مع');

    // بلوك المؤسسة
    if (blocEtab) {
        blocEtab.classList.toggle('d-none', !etabCondamne);
    }

    if (hiddenEtab) {
        hiddenEtab.disabled = !etabCondamne;
    }

    if (montantEtab) {

        montantEtab.required = etabCondamne;

        if (!etabCondamne)
            montantEtab.value = '';

    }

    // بلوك الأطراف الأخرى
    if (blocPartiesAdv) {
        blocPartiesAdv.classList.toggle('d-none', !adverseCondamne);
    }

    // إلغاء التحديد عند تغيير الوضعية
    if (!adverseCondamne) {

        document.querySelectorAll(
            '#bloc-parties-adverses input[type="checkbox"]'
        ).forEach(cb => cb.checked = false);

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
@endpush