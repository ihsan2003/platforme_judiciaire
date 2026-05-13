@extends('layouts.app')

@section('title', 'تعديل الملف')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dossiers.index') }}">الملفات</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dossiers.show', $dossier) }}">{{ $dossier->numero_dossier_interne }}</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning me-2"></i>تعديل الملف
        </h4>
        <p class="text-muted small mb-0">
            الملف رقم <strong>{{ $dossier->numero_dossier_interne }}</strong>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('dossiers.show', $dossier) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>العودة إلى الملف
        </a>
    </div>
</div>

<form action="{{ route('dossiers.update', $dossier) }}" method="POST">
@csrf @method('PUT')

<div class="row g-4">

    {{-- ══ العمود الرئيسي ══ --}}
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-card-text me-2 text-warning"></i>معلومات الملف
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            رقم الملف الداخلي <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="numero_dossier_interne"
                               class="form-control @error('numero_dossier_interne') is-invalid @enderror"
                               value="{{ old('numero_dossier_interne', $dossier->numero_dossier_interne) }}">
                        @error('numero_dossier_interne')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">رقم ملف المحكمة</label>
                        <input type="text" name="numero_dossier_tribunal"
                               class="form-control @error('numero_dossier_tribunal') is-invalid @enderror"
                               value="{{ old('numero_dossier_tribunal', $dossier->numero_dossier_tribunal) }}">
                        @error('numero_dossier_tribunal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            نوع القضية <span class="text-danger">*</span>
                        </label>
                        <select name="id_type_affaire"
                                class="form-select @error('id_type_affaire') is-invalid @enderror">
                            <option value="">— اختر —</option>
                            @foreach($typesAffaire as $type)
                                <option value="{{ $type->id }}"
                                    @selected(old('id_type_affaire', $dossier->id_type_affaire) == $type->id)>
                                    {{ $type->affaire }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_type_affaire')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            الحالة <span class="text-danger">*</span>
                        </label>
                        <select name="id_statut_dossier"
                                class="form-select @error('id_statut_dossier') is-invalid @enderror">
                            <option value="">— اختر —</option>
                            @foreach($statutDossiers as $statut)
                                <option value="{{ $statut->id }}"
                                    @selected(old('id_statut_dossier', $dossier->id_statut_dossier) == $statut->id)>
                                    {{ $statut->statut_dossier }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_statut_dossier')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            تاريخ الافتتاح <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="date_ouverture"
                               class="form-control @error('date_ouverture') is-invalid @enderror"
                               value="{{ old('date_ouverture', $dossier->date_ouverture?->format('Y-m-d')) }}">
                        @error('date_ouverture')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">تاريخ الإغلاق</label>
                        <input type="date" name="date_cloture"
                               class="form-control @error('date_cloture') is-invalid @enderror"
                               value="{{ old('date_cloture', $dossier->date_cloture?->format('Y-m-d')) }}">
                        @error('date_cloture')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ══ العمود الجانبي ══ --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-muted"></i>ملخص
                </h6>
            </div>

            <div class="card-body small">
                <dl class="row mb-0">

                    <dt class="col-5 text-muted">أنشئ بواسطة</dt>
                    <dd class="col-7">{{ $dossier->createdBy->name ?? '—' }}</dd>

                    <dt class="col-5 text-muted">تاريخ الإنشاء</dt>
                    <dd class="col-7">{{ $dossier->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-5 text-muted">آخر تعديل</dt>
                    <dd class="col-7">{{ $dossier->updated_at->format('d/m/Y') }}</dd>

                    <dt class="col-5 text-muted">الأطراف</dt>
                    <dd class="col-7">
                        <span class="badge bg-primary bg-opacity-10 text-primary">
                            {{ $dossier->parties->count() }} طرف
                        </span>
                    </dd>

                    <dt class="col-5 text-muted">المحاكم</dt>
                    <dd class="col-7">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                            {{ $dossier->dossierTribunaux->count() }} محكمة
                        </span>
                    </dd>

                </dl>
            </div>
        </div>

        <div class="alert alert-warning border-0 small mt-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            لتعديل <strong>الأطراف</strong> أو <strong>المحاكم</strong> يرجى الرجوع إلى صفحة الملف.
        </div>

    </div>
</div>

<div class="d-flex gap-2 justify-content-end mt-4">
    <a href="{{ route('dossiers.show', $dossier) }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>إلغاء
    </a>
    <button type="submit" class="btn btn-warning px-4">
        <i class="bi bi-check-lg me-1"></i>حفظ التعديلات
    </button>
</div>

</form>

@endsection