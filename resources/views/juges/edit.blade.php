@extends('layouts.app')

@section('title', 'تعديل القاضي')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('juges.index') }}">القضاة</a></li>
    <li class="breadcrumb-item"><a href="{{ route('juges.show', $juge) }}">{{ $juge->nom_complet }}</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning me-2"></i>
            تعديل القاضي
        </h4>
        <p class="text-muted small mb-0">
            تحديث بيانات <strong>{{ $juge->nom_complet }}</strong>
        </p>
    </div>

    <a href="{{ route('juges.show', $juge) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>
        العودة إلى التفاصيل
    </a>
</div>

<form action="{{ route('juges.update', $juge) }}" method="POST">
@csrf @method('PUT')

<div class="row g-4">

    {{-- ── المعلومات الأساسية ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-vcard me-2 text-warning"></i>
                    معلومات القاضي
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    {{-- الاسم الكامل --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            الاسم الكامل <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="nom_complet"
                               class="form-control @error('nom_complet') is-invalid @enderror"
                               value="{{ old('nom_complet', $juge->nom_complet) }}"
                               required>

                        @error('nom_complet')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- الرتبة --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            الرتبة <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="grade"
                               class="form-control @error('grade') is-invalid @enderror"
                               value="{{ old('grade', $juge->grade) }}"
                               required>

                        @error('grade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- التخصص --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            التخصص
                        </label>

                        <input type="text"
                               name="specialisation"
                               class="form-control @error('specialisation') is-invalid @enderror"
                               value="{{ old('specialisation', $juge->specialisation) }}">
                    </div>

                    {{-- المحكمة (TomSelect) --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            المحكمة <span class="text-danger">*</span>
                        </label>

                        <select name="id_tribunal"
                                id="tribunal"
                                class="form-select @error('id_tribunal') is-invalid @enderror"
                                required>

                            <option value="">— اختر المحكمة —</option>

                            @foreach($tribunaux as $tribunal)
                                <option value="{{ $tribunal->id }}"
                                    @selected(old('id_tribunal', $juge->id_tribunal) == $tribunal->id)>
                                    {{ $tribunal->nom_tribunal }}
                                </option>
                            @endforeach

                        </select>

                        @error('id_tribunal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

        </div>
    </div>

    {{-- ── معلومات جانبية ── --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-muted"></i>
                    ملخص
                </h6>
            </div>

            <div class="card-body small">
                <dl class="row mb-0">

                    <dt class="col-6 text-muted fw-normal">المحكمة الحالية</dt>
                    <dd class="col-6">{{ $juge->tribunal->nom_tribunal ?? '—' }}</dd>

                    <dt class="col-6 text-muted fw-normal">تاريخ الإنشاء</dt>
                    <dd class="col-6">{{ $juge->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">آخر تعديل</dt>
                    <dd class="col-6">{{ $juge->updated_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">الجلسات</dt>
                    <dd class="col-6">{{ $juge->audiences()->count() }}</dd>

                    <dt class="col-6 text-muted fw-normal">الأحكام</dt>
                    <dd class="col-6">{{ $juge->jugements()->count() }}</dd>

                </dl>
            </div>
        </div>

    </div>

</div>

{{-- ── الأزرار ── --}}
<div class="d-flex gap-2 justify-content-end mt-4">

    <a href="{{ route('juges.show', $juge) }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>
        إلغاء
    </a>

    <button type="submit" class="btn btn-warning px-4">
        <i class="bi bi-check-lg me-1"></i>
        حفظ التعديلات
    </button>

</div>

</form>

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    new TomSelect('#tribunal', {
        create: false,
        placeholder: "ابحث عن المحكمة...",
        allowEmptyOption: true,
        sortField: { field: "text", direction: "asc" },
        render: {
            no_results: function() {
                return `<div class="no-results">لا توجد نتائج</div>`;
            }
        }
    });

});
</script>

@endpush