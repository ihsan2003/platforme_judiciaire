@extends('layouts.app')

@section('title', 'قاضٍ جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('juges.index') }}">القضاة</a></li>
    <li class="breadcrumb-item active">قاضٍ جديد</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-person-plus text-primary me-2"></i>
            قاضٍ جديد
        </h4>
        <p class="text-muted small mb-0">
            إدخال معلومات قاضٍ جديد
        </p>
    </div>

    <a href="{{ route('juges.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>
        العودة إلى القائمة
    </a>
</div>

<form action="{{ route('juges.store') }}" method="POST">
@csrf

<div class="row g-4">

    {{-- ── القسم الرئيسي ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-vcard me-2 text-primary"></i>
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
                               value="{{ old('nom_complet') }}"
                               placeholder="مثال: محمد العلوي"
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
                               value="{{ old('grade') }}"
                               placeholder="مثال: رئيس، مستشار..."
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
                               value="{{ old('specialisation') }}"
                               placeholder="مثال: القانون المدني، التجاري...">

                        @error('specialisation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- المحكمة --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            المحكمة <span class="text-danger">*</span>
                        </label>

                        <select name="id_tribunal"
                            id="tribunal"
                            class="form-select">
                        <option value="">— اختر المحكمة —</option>

                        @foreach($tribunaux as $tribunal)
                            <option value="{{ $tribunal->id }}">
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

</div>

{{-- ── الأزرار ── --}}
<div class="d-flex gap-2 justify-content-end mt-4">

    <a href="{{ route('juges.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>
        إلغاء
    </a>

    <button type="submit" class="btn btn-primary px-4">
        <i class="bi bi-check-lg me-1"></i>
        حفظ
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