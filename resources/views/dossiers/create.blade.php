@extends('layouts.app')

@section('title', 'ملف قضائي جديد')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">الرئيسية</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('dossiers.index') }}">الملفات</a>
    </li>
    <li class="breadcrumb-item active">ملف جديد</li>
@endsection

@section('content')

{{-- ══════════════════════════════════════════════════════════════════════════
     Page Header
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-folder-plus text-primary me-2"></i>
            إضافة ملف قضائي جديد
        </h4>
        <p class="text-muted small mb-0">
            قم بإدخال معلومات الملف القضائي بدقة لضمان متابعة فعّالة.
        </p>
    </div>

    <a href="{{ route('dossiers.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-right me-1"></i>
        العودة إلى القائمة
    </a>
</div>

<form action="{{ route('dossiers.store') }}" method="POST" id="dossierForm">
@csrf

<div class="row g-4">

    {{-- ══════════════════════════════════════════════════════════════════════
         العمود الرئيسي
    ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="col-lg-8 mx-auto">

        {{-- ─────────────────────────────────────────────────────────────────
             بطاقة معلومات الملف الأساسية
        ───────────────────────────────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-folder2-open me-2 text-primary"></i>
                    معلومات الملف الأساسية
                </h6>
            </div>

            <div class="card-body p-4">
                <div class="row g-3">

                    {{-- رقم الملف الداخلي --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-dark">
                            رقم الملف الداخلي
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-file-earmark-text text-muted"></i>
                            </span>
                            <input type="text"
                                   name="numero_dossier_interne"
                                   class="form-control border-start-0 @error('numero_dossier_interne') is-invalid @enderror"
                                   value="{{ old('numero_dossier_interne') }}"
                                   placeholder="مثال: DOS-2025-001"
                                   required>
                            @error('numero_dossier_interne')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- رقم ملف المحكمة --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-dark">
                            رقم ملف المحكمة
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-building text-muted"></i>
                            </span>
                            <input type="text"
                                   name="numero_dossier_tribunal"
                                   class="form-control border-start-0 @error('numero_dossier_tribunal') is-invalid @enderror"
                                   value="{{ old('numero_dossier_tribunal') }}"
                                   placeholder="مثال: TRB-2025-001">
                            @error('numero_dossier_tribunal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- نوع القضية --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-dark">
                            نوع القضية
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-bookmark text-muted"></i>
                            </span>
                            <select name="id_type_affaire"
                                    class="form-select border-start-0 @error('id_type_affaire') is-invalid @enderror"
                                    required>
                                <option value="">— اختر نوع القضية —</option>
                                @foreach($typesAffaire as $type)
                                    <option value="{{ $type->id }}"
                                        @selected(old('id_type_affaire') == $type->id)>
                                        {{ $type->affaire }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_type_affaire')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ─────────────────────────────────────────────────────────────────
             بطاقة التواريخ والمواعيد
        ───────────────────────────────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-calendar3 me-2 text-success"></i>
                    التواريخ والمواعيد
                </h6>
            </div>

            <div class="card-body p-4">
                <div class="row g-3">

                    {{-- تاريخ فتح الملف --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-dark">
                            تاريخ فتح الملف
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-calendar-event text-muted"></i>
                            </span>
                            <input type="date"
                                   name="date_ouverture"
                                   class="form-control border-start-0 @error('date_ouverture') is-invalid @enderror"
                                   value="{{ old('date_ouverture', date('Y-m-d')) }}"
                                   required>
                            @error('date_ouverture')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- تاريخ الإغلاق --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-dark">
                            تاريخ الإغلاق
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-calendar-check text-muted"></i>
                            </span>
                            <input type="date"
                                   name="date_cloture"
                                   class="form-control border-start-0 @error('date_cloture') is-invalid @enderror"
                                   value="{{ old('date_cloture') }}">
                            @error('date_cloture')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
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
                إنشاء الملف
            </button>
            <a href="{{ route('dossiers.index') }}" class="btn btn-outline-secondary px-4">
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
    /* تحسينات RTL للنموذج */
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

    /* تحسين مظهر البطاقات */
    .card {
        border-radius: 0.5rem;
    }

    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }

    /* تأثير hover للبطاقات */
    .card {
        transition: box-shadow 0.2s ease-in-out;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }

    /* تحسين حقول الإدخال */
    .form-control:focus,
    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }

    /* تنسيق القائمة الجانبية */
    .list-group-item {
        border-left: 0;
        border-right: 0;
    }

    .list-group-item:first-child {
        border-top: 0;
    }

    .list-group-item:last-child {
        border-bottom: 0;
    }

    /* تحسين الأيقونات */
    .bi {
        vertical-align: -0.125em;
    }

    /* تنسيق الشارات */
    .badge {
        font-weight: 500;
    }
</style>
@endpush
