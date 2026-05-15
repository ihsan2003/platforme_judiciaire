@extends('layouts.app')

@section('title', 'تعديل — ' . $structure->nom)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.structures.index') }}">الهيكليات</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.structures.show', $structure) }}">{{ $structure->nom }}</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning me-2"></i>تعديل الهيكل
        </h4>
        <p class="text-muted small mb-0">
            تعديل <strong>{{ $structure->nom }}</strong>
        </p>
    </div>

    <a href="{{ route('admin.structures.show', $structure) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>العودة إلى التفاصيل
    </a>
</div>

<form action="{{ route('admin.structures.update', $structure) }}" method="POST">
@csrf @method('PUT')

<div class="row g-4">

    {{-- ── النموذج ── --}}
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-building me-2 text-warning"></i>معلومات الهيكل
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
                                   value="{{ old('nom', $structure->nom) }}"
                                   required>
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
                                <option value="{{ $type->id }}"
                                        @selected(old('id_type_structure', $structure->id_type_structure) == $type->id)>
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
                                <option value="{{ $parent->id }}"
                                        @selected(old('id_parent', $structure->id_parent) == $parent->id)>
                                    {{ $parent->nom }}
                                </option>
                            @endforeach
                        </select>

                        @error('id_parent')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- ── الملخص ── --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-muted"></i>ملخص
                </h6>
            </div>

            <div class="card-body small">
                <dl class="row mb-0">

                    <dt class="col-6 text-muted fw-normal">المعرف</dt>
                    <dd class="col-6 font-monospace">#{{ $structure->id }}</dd>

                    <dt class="col-6 text-muted fw-normal">تاريخ الإنشاء</dt>
                    <dd class="col-6">{{ $structure->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">النوع الحالي</dt>
                    <dd class="col-6">
                        <span class="badge bg-primary bg-opacity-15 text-white">
                            {{ $structure->typeStructure?->type_structure ?? '—' }}
                        </span>
                    </dd>

                    <dt class="col-6 text-muted fw-normal">الهيكل الأب</dt>
                    <dd class="col-6">{{ $structure->parent?->nom ?? '—' }}</dd>

                    @if($structure->enfants->count() > 0)
                    <dt class="col-6 text-muted fw-normal">الهيكليات الفرعية</dt>
                    <dd class="col-6">
                        <span class="badge bg-success bg-opacity-10 text-success">
                            {{ $structure->enfants->count() }}
                        </span>
                    </dd>
                    @endif

                </dl>
            </div>
        </div>

    </div>

</div>

<div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.structures.show', $structure) }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i>إلغاء
            </a>

            <button type="submit" class="btn btn-warning px-4">
                <i class="bi bi-check-lg me-1"></i>حفظ التعديلات
            </button>
        </div>

</form>

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