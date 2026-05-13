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

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-folder-plus text-primary me-2"></i>
            إضافة ملف قضائي جديد
        </h4>
        <p class="text-muted small mb-0">
            قم بإدخال معلومات الملف وتعيين المحكمة.
        </p>
    </div>

    <a href="{{ route('dossiers.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>
        العودة إلى القائمة
    </a>
</div>

<form action="{{ route('dossiers.store') }}" method="POST">
@csrf

<div class="row g-4">

    {{-- ══ العمود الرئيسي ══ --}}
    <div class="col-lg-7">

        {{-- بطاقة التعريف --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-card-text me-2 text-primary"></i>
                    معلومات الملف
                </h6>
            </div>

            <div class="card-body">

                <div class="row g-3">

                    {{-- رقم الملف الداخلي --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            رقم الملف الداخلي <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="numero_dossier_interne"
                               class="form-control @error('numero_dossier_interne') is-invalid @enderror"
                               value="{{ old('numero_dossier_interne') }}"
                               placeholder="مثال: DOS-2025-001">

                        @error('numero_dossier_interne')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- رقم المحكمة --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            رقم ملف المحكمة
                        </label>

                        <input type="text"
                               name="numero_dossier_tribunal"
                               class="form-control @error('numero_dossier_tribunal') is-invalid @enderror"
                               value="{{ old('numero_dossier_tribunal') }}"
                               placeholder="مثال: TRB-2025-001">

                        @error('numero_dossier_tribunal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- نوع القضية --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            نوع القضية <span class="text-danger">*</span>
                        </label>

                        <select name="id_type_affaire"
                                class="form-select @error('id_type_affaire') is-invalid @enderror">

                            <option value="">— اختر —</option>

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

                    {{-- تاريخ الفتح --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            تاريخ فتح الملف <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               name="date_ouverture"
                               class="form-control @error('date_ouverture') is-invalid @enderror"
                               value="{{ old('date_ouverture', date('Y-m-d')) }}">

                        @error('date_ouverture')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- تاريخ الإغلاق --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            تاريخ الإغلاق
                        </label>

                        <input type="date"
                               name="date_cloture"
                               class="form-control @error('date_cloture') is-invalid @enderror"
                               value="{{ old('date_cloture') }}">

                        @error('date_cloture')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

            </div>
        </div>

    </div>

</div>

{{-- ══ الأزرار ══ --}}
<div class="d-flex gap-2 justify-content-end mt-2">

    <a href="{{ route('dossiers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>
        إلغاء
    </a>

    <button type="submit" class="btn btn-primary px-4">
        <i class="bi bi-check-lg me-1"></i>
        إنشاء الملف
    </button>

</div>

</form>

@endsection