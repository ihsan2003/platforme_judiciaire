@extends('layouts.app')

@section('title', 'تعديل الملف #' . $dossier->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dossiers.index') }}">الملفات</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dossiers.show', $dossier) }}">{{ $dossier->id }}</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning me-2"></i>تعديل الملف القضائي
        </h4>
        <p class="text-muted small mb-0">
            أنت بصدد تعديل الملف رقم <strong>{{ $dossier->id }}</strong>
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

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-card-text me-2 text-warning"></i>معلومات الملف
                </h6>
            </div>

            <div class="card-body p-4">
                <div class="row g-3">

                    {{-- الشعبة --}}
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-semibold small">الشعبة <span class="text-danger">*</span></label>
                        <select name="id_type_affaire" id="id_type_affaire" class="form-select @error('id_type_affaire') is-invalid @enderror" required>
                            <option value="" data-code="">— اختر الشعبة —</option>
                            @foreach($typesAffaire as $type)
                                <option value="{{ $type->id }}" data-code="{{ $type->code }}" 
                                    @selected(old('id_type_affaire', $dossier->id_type_affaire) == $type->id)>
                                    {{ $type->affaire }} 
                                </option>
                            @endforeach
                        </select>
                        @error('id_type_affaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- رقم الملف: خاص بكل محكمة (درجة) — يُعدَّل من نافذة « تعديل بيانات المحكمة » في تبويب المحاكم --}}
                    <div class="col-12">
                        <div class="p-3 bg-light rounded border small text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            رقم الملف خاص بكل محكمة (ابتدائية، استئناف، نقض). لتعديله، افتح تبويب المحاكم في صفحة الملف ثم « تعديل بيانات المحكمة ».
                            @if($dossier->numero_dossier_tribunal)
                                <div class="mt-1 fw-semibold text-dark" dir="ltr">{{ $dossier->numero_dossier_tribunal }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- موضوع النزاع --}}
                    <div class="col-12 mt-4">
                        <label for="objet_litige" class="form-label fw-semibold small">موضوع النزاع</label>
                        <textarea name="objet_litige" id="objet_litige" rows="3"
                                  class="form-control @error('objet_litige') is-invalid @enderror">{{ old('objet_litige', $dossier->objet_litige) }}</textarea>
                        @error('objet_litige')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- الإجراء المتخذ من طرف المصلحة --}}
                    <div class="col-12">
                        <label for="action_service" class="form-label fw-semibold small">الإجراء المتخذ من طرف المصلحة</label>
                        <textarea name="action_service" id="action_service" rows="3"
                                  class="form-control @error('action_service') is-invalid @enderror">{{ old('action_service', $dossier->action_service) }}</textarea>
                        @error('action_service')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ══ العمود الجانبي ══ --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2 text-muted"></i>ملخص الملف</h6>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">الرقم</dt>
                    <dd class="col-7 fw-bold">{{ $dossier->id }}</dd>

                    <dt class="col-5 text-muted">تاريخ الإنشاء</dt>
                    <dd class="col-7">{{ $dossier->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-5 text-muted">بواسطة</dt>
                    <dd class="col-7">{{ $dossier->createdBy->name ?? '—' }}</dd>

                    <dt class="col-5 text-muted">
                        حالة الملف <span class="text-danger">*</span>
                    </dt>
                    <dd>
                        <select name="id_statut_dossier" class="form-select @error('id_statut_dossier') is-invalid @enderror">
                            @foreach($statutDossiers as $statut)
                                <option value="{{ $statut->id }}" @selected(old('id_statut_dossier', $dossier->id_statut_dossier) == $statut->id)>
                                    {{ $statut->statut_dossier }}
                                </option>
                            @endforeach
                        </select>
                    </dd>

                    <dt class="col-5 text-muted">
                        تاريخ الافتتاح <span class="text-danger">*</span>
                    </dt>
                    <dd>
                        <input type="date" name="date_ouverture" class="form-control" value="{{ old('date_ouverture', $dossier->date_ouverture?->format('Y-m-d')) }}">
                    </dd>
                </dl>
            </div>
        </div>

        <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-warning py-2">
                <i class="bi bi-check-lg me-1"></i>حفظ التعديلات
            </button>
            <a href="{{ route('dossiers.show', $dossier) }}" class="btn btn-outline-secondary">
                إلغاء
            </a>
        </div>
    </div>
</div>

</form>

@endsection


