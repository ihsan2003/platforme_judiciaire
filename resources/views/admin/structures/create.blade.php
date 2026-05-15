@extends('layouts.app')

@section('title', 'هيكل جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.structures.index') }}">الهيكليات</a></li>
    <li class="breadcrumb-item active">جديد</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-diagram-3 text-primary me-2"></i>إنشاء هيكل جديد
        </h4>
        <p class="text-muted small mb-0">
            إنشاء هيكل رئيسي أو هيكل فرعي
        </p>
    </div>

    <a href="{{ route('admin.structures.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>العودة إلى القائمة
    </a>
</div>

<div class="row g-4">

    {{-- ── النموذج ── --}}
    <div class="col-lg-8">

        <form action="{{ route('admin.structures.store') }}" method="POST">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-building me-2 text-primary"></i>معلومات الهيكل
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    {{-- الاسم --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            اسم الهيكل <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-building text-muted"></i>
                            </span>

                            <input type="text"
                                   name="nom"
                                   class="form-control @error('nom') is-invalid @enderror"
                                   value="{{ old('nom') }}"
                                   placeholder="مثال: المديرية الجهوية بالدار البيضاء"
                                   required autofocus>
                        </div>

                        @error('nom')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- النوع --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            نوع الهيكل <span class="text-danger">*</span>
                        </label>

                        <select name="id_type_structure"
                                class="form-select @error('id_type_structure') is-invalid @enderror"
                                required>
                            <option value="">— اختر النوع —</option>

                            @foreach($typesStructure as $type)
                                <option value="{{ $type->id }}" @selected(old('id_type_structure') == $type->id)>
                                    {{ $type->type_structure }}
                                </option>
                            @endforeach
                        </select>

                        @error('id_type_structure')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- الهيكل الأب --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            الهيكل الأب
                            <span class="text-muted fw-normal">(اختياري)</span>
                        </label>

                        <select name="id_parent" id="parent"
                                class="form-select @error('id_parent') is-invalid @enderror">
                            <option value="">— بدون (هيكل رئيسي) —</option>

                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}">
                                    {{ $parent->nom }}
                                </option>
                            @endforeach
                        </select>

                        @error('id_parent')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="form-text">
                            اتركه فارغًا لإنشاء هيكل من المستوى الأول
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.structures.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i>إلغاء
            </a>

            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i>إنشاء الهيكل
            </button>
        </div>

        </form>
    </div>

    {{-- ── الجانب الأيمن ── --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-diagram-3 me-2 text-success"></i>الهرمية
                </h6>
            </div>

            <div class="card-body small">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-building-fill text-primary"></i>
                    <span class="fw-semibold">هيكل رئيسي</span>
                </div>

                <div class="d-flex align-items-center gap-2 ms-3">
                    <i class="bi bi-arrow-return-left text-muted"></i>
                    <i class="bi bi-building text-secondary"></i>
                    <span class="text-muted">هيكل فرعي</span>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    new TomSelect('#parent', {
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