{{-- resources/views/audiences/create.blade.php --}}
@extends('layouts.app')

@section('title', 'جلسة جديدة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('audiences.index') }}">الجلسات</a></li>
    <li class="breadcrumb-item active">جلسة جديدة</li>
@endsection

@section('content')

{{-- ══════════════════════════════════════════════════════════════════════════
    Page Header
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-calendar-plus text-primary me-2"></i>
            إنشاء جلسة جديدة
        </h4>
        <p class="text-muted small mb-0">
            أدخل معلومات الجلسة بدقة لضمان متابعة فعّالة.
        </p>
    </div>

    <a href="{{ route('audiences.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-right me-1"></i>
        العودة إلى القائمة
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger mb-4">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('audiences.store') }}" id="audienceForm" dir="rtl">
@csrf

<div class="row g-4">
<div class="col-lg-8 mx-auto">

    {{-- ─────────────────────────────────────────────────────────────────
        بطاقة المعلومات الرئيسية
    ───────────────────────────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-folder2-open me-2 text-primary"></i>
                معلومات الملف والمحكمة
            </h6>
        </div>

        <div class="card-body p-4">
            <div class="row g-3">

                {{-- الملف / المحكمة --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-dark">
                        الملف والمحكمة
                        <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-folder2 text-muted"></i>
                        </span>
                        <select name="id_dossier_tribunal"
                                id="id_dossier_tribunal"
                                class="form-select border-start-0 @error('id_dossier_tribunal') is-invalid @enderror"
                                required>
                            <option value="">— اختر ملفًا —</option>
                            @foreach($dossierTribunaux as $dt)
                                <option value="{{ $dt->id }}"
                                        data-tribunal-id="{{ $dt->id_tribunal }}"
                                        @selected(old('id_dossier_tribunal',
                                            $dossierTribunaux->count() === 1 ? $dt->id : null) == $dt->id)>
                                    {{ $dt->dossier?->numero_dossier_tribunal ?? 'ملف #'.$dt->id_dossier }}
                                    — {{ $dt->tribunal?->nom_tribunal ?? 'محكمة #'.$dt->id_tribunal }}
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
                        <select name="id_juge" id="id_juge"
                                class="form-select border-start-0 @error('id_juge') is-invalid @enderror"
                                required>
                            <option value="">— اختر المحكمة أولاً —</option>
                            @foreach($juges as $juge)
                                <option value="{{ $juge->id }}" @selected(old('id_juge') == $juge->id)>
                                    {{ $juge->grade ? $juge->grade.' ' : '' }}{{ $juge->nom_complet }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_juge')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div id="juge_hint" class="form-text text-info d-none">
                        <i class="bi bi-info-circle me-1"></i>
                        القائمة يتم تصفيتها حسب المحكمة المختارة.
                    </div>
                    <div id="juge_aucun" class="form-text text-warning d-none">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        لا يوجد قضاة مسجلون لهذه المحكمة.
                    </div>
                </div>

                {{-- نوع الجلسة --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-dark">
                        نوع الجلسة
                        <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-bookmark text-muted"></i>
                        </span>
                        <select name="id_type_audience" id="id_type_audience"
                                class="form-select border-start-0 @error('id_type_audience') is-invalid @enderror"
                                required>
                            <option value="">— اختر —</option>
                            @foreach($typesAudience as $type)
                                <option value="{{ $type->id }}" @selected(old('id_type_audience') == $type->id)>
                                    {{ $type->libelle ?? $type->type_audience }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_type_audience')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ─────────────────────────────────────────────────────────────────
        بطاقة التواريخ والحضور
    ───────────────────────────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-calendar3 me-2 text-success"></i>
                التواريخ والحضور
            </h6>
        </div>

        <div class="card-body p-4">
            <div class="row g-3">

                {{-- تاريخ الجلسة --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-dark">
                        تاريخ الجلسة
                        <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-calendar-event text-muted"></i>
                        </span>
                        <input type="date"
                               name="date_audience"
                               id="date_audience"
                               class="form-control border-start-0 @error('date_audience') is-invalid @enderror"
                               value="{{ old('date_audience', $dateAudienceParDefaut ?? date('Y-m-d')) }}"
                               required>
                        @error('date_audience')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- الجلسة القادمة --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-dark">
                        الجلسة القادمة
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-calendar-check text-muted"></i>
                        </span>
                        <input type="date"
                               name="date_prochaine_audience"
                               id="date_prochaine_audience"
                               class="form-control border-start-0 @error('date_prochaine_audience') is-invalid @enderror"
                               value="{{ old('date_prochaine_audience') }}">
                        @error('date_prochaine_audience')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- الحضور --}}
                <div class="col-md-6 d-flex align-items-center">
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               name="presence_demandeur"
                               value="1"
                               id="presence_demandeur"
                               @checked(old('presence_demandeur'))>
                        <label class="form-check-label small fw-semibold" for="presence_demandeur">
                            حضور المدعي
                        </label>
                    </div>
                </div>

                <div class="col-md-6 d-flex align-items-center">
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               name="presence_defendeur"
                               value="1"
                               id="presence_defendeur"
                               @checked(old('presence_defendeur'))>
                        <label class="form-check-label small fw-semibold" for="presence_defendeur">
                            حضور المدعى عليه
                        </label>
                    </div>
                </div>

                <div class="col-md-6 d-flex align-items-center">
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                            type="checkbox"
                            name="presence_avocat_entraide"
                            value="1"
                            id="presence_avocat_entraide"
                            @checked(old('presence_avocat_entraide', $audience->presence_avocat_entraide ?? false))>
                        <label class="form-check-label small fw-semibold" for="presence_avocat_entraide">
                            حضور محامي المؤسسة
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─────────────────────────────────────────────────────────────────
        بطاقة النتائج والإجراءات
    ───────────────────────────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-card-text me-2 text-secondary"></i>
                النتائج والإجراءات
            </h6>
        </div>

        <div class="card-body p-4">
            <div class="row g-3">

                <div class="col-12">
                    <label class="form-label fw-semibold small text-dark">النتيجة</label>
                    <textarea name="resultat_audience"
                              class="form-control @error('resultat_audience') is-invalid @enderror"
                              rows="3"
                              placeholder="نتيجة الجلسة...">{{ old('resultat_audience') }}</textarea>
                    @error('resultat_audience')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold small text-dark">الإجراءات المطلوبة</label>
                    <textarea name="actions_demandees"
                              class="form-control @error('actions_demandees') is-invalid @enderror"
                              rows="3"
                              placeholder="الإجراءات المطلوبة...">{{ old('actions_demandees') }}</textarea>
                    @error('actions_demandees')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            </div>
        </div>
    </div>

    {{-- ─────────────────────────────────────────────────────────────────
        أزرار الإرسال والإلغاء
    ───────────────────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-2"></i>
            حفظ الجلسة
        </button>
        <a href="{{ route('audiences.index') }}" class="btn btn-outline-secondary px-4">
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
</style>
@endpush

@push('scripts')
<script>
document.getElementById('id_dossier_tribunal')
    ?.addEventListener('change', async function () {
        const tribunalId = this.options[this.selectedIndex]?.dataset?.tribunalId;
        const jugeSelect = document.getElementById('id_juge');
        const hint       = document.getElementById('juge_hint');
        const aucun      = document.getElementById('juge_aucun');

        if (!tribunalId) {
            jugeSelect.innerHTML = '<option value="">— اختر المحكمة أولاً —</option>';
            hint.classList.add('d-none');
            aucun.classList.add('d-none');
            return;
        }

        jugeSelect.innerHTML = '<option value="">— جار التحميل… —</option>';
        jugeSelect.disabled  = true;

        try {
            const res   = await fetch(`/api/tribunaux/${tribunalId}/juges`);
            const juges = await res.json();

            jugeSelect.innerHTML = '<option value="">— اختر قاضيًا —</option>';

            if (juges.length === 0) {
                aucun.classList.remove('d-none');
                hint.classList.add('d-none');
            } else {
                juges.forEach(j => {
                    const opt   = document.createElement('option');
                    opt.value   = j.id;
                    opt.textContent = (j.grade ? j.grade + ' ' : '') + j.nom_complet;
                    jugeSelect.appendChild(opt);
                });
                hint.classList.remove('d-none');
                aucun.classList.add('d-none');
            }

            jugeSelect.disabled = false;

        } catch (e) {
            jugeSelect.innerHTML = '<option value="">— خطأ في التحميل —</option>';
            jugeSelect.disabled  = false;
        }
    });

window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('id_dossier_tribunal');
    if (sel?.value) sel.dispatchEvent(new Event('change'));
});
</script>
@endpush