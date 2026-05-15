@extends('layouts.app')

@section('title', 'محكمة جديدة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tribunaux.index') }}">المحاكم</a></li>
    <li class="breadcrumb-item active">محكمة جديدة</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-building-add text-primary me-2"></i>
            محكمة جديدة
        </h4>
        <p class="text-muted small mb-0">
            إدخال معلومات محكمة جديدة
        </p>
    </div>

    <a href="{{ route('tribunaux.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>
        العودة إلى القائمة
    </a>
</div>

<form action="{{ route('tribunaux.store') }}" method="POST">
@csrf

<div class="row g-4">

    {{-- ── القسم الرئيسي ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-building me-2 text-primary"></i>
                    معلومات المحكمة
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    {{-- اسم المحكمة --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            اسم المحكمة <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="nom_tribunal"
                               class="form-control @error('nom_tribunal') is-invalid @enderror"
                               value="{{ old('nom_tribunal') }}"
                               placeholder="مثال: المحكمة الابتدائية بالدار البيضاء"
                               required>

                        @error('nom_tribunal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- نوع المحكمة --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            نوع المحكمة <span class="text-danger">*</span>
                        </label>

                        <select name="id_type_tribunal"
                                class="form-select @error('id_type_tribunal') is-invalid @enderror"
                                required>

                            <option value="">— اختر —</option>

                            @foreach($types as $type)
                                <option value="{{ $type->id }}" @selected(old('id_type_tribunal') == $type->id)>
                                    {{ $type->tribunal }}
                                </option>
                            @endforeach

                        </select>

                        @error('id_type_tribunal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- الجهة --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            الجهة <span class="text-danger">*</span>
                        </label>

                        <select id="region" class="form-select">
                            <option value="">— اختر الجهة —</option>

                            @foreach($regions as $region)
                                <option value="{{ $region->id }}">
                                    {{ $region->region }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- الإقليم --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            الإقليم <span class="text-danger">*</span>
                        </label>

                        <select name="id_province"
                                id="province"
                                class="form-select @error('id_province') is-invalid @enderror"
                                required>

                            <option value="">— اختر الإقليم —</option>
                        </select>

                        @error('id_province')
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

    <a href="{{ route('tribunaux.index') }}" class="btn btn-outline-secondary">
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
document.getElementById('region').addEventListener('change', function () {
    let regionId = this.value;
    let provinceSelect = document.getElementById('province');

    provinceSelect.innerHTML = '<option value="">جاري التحميل...</option>';

    if (!regionId) {
        provinceSelect.innerHTML = '<option value="">— اختر الإقليم —</option>';
        return;
    }

    fetch(`/api/regions/${regionId}/provinces`, {
        headers: {
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(res => {
        if (!res.ok) throw new Error("خطأ في الاتصال بالخادم");
        return res.json();
    })
    .then(data => {
        provinceSelect.innerHTML = '<option value="">— اختر الإقليم —</option>';

        data.forEach(province => {
            provinceSelect.innerHTML += `
                <option value="${province.id}">
                    ${province.province}
                </option>
            `;
        });
    })
    .catch(err => {
        console.error(err);
        provinceSelect.innerHTML = '<option value="">تعذر تحميل الأقاليم</option>';
    });
});
</script>
@endpush