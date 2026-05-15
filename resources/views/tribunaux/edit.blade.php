@extends('layouts.app')

@section('title', 'تعديل المحكمة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tribunaux.index') }}">المحاكم</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tribunaux.show', $tribunal) }}">{{ $tribunal->nom_tribunal }}</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning me-2"></i>
            تعديل المحكمة
        </h4>
        <p class="text-muted small mb-0">
            تحديث بيانات <strong>{{ $tribunal->nom_tribunal }}</strong>
        </p>
    </div>

    <a href="{{ route('tribunaux.show', $tribunal) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>
        العودة إلى التفاصيل
    </a>
</div>

<form action="{{ route('tribunaux.update', $tribunal) }}" method="POST">
@csrf @method('PUT')

<div class="row g-4">

    {{-- ── القسم الرئيسي ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-building me-2 text-warning"></i>
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
                               value="{{ old('nom_tribunal', $tribunal->nom_tribunal) }}"
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
                                <option value="{{ $type->id }}"
                                    @selected(old('id_type_tribunal', $tribunal->id_type_tribunal) == $type->id)>
                                    {{ $type->tribunal }}
                                </option>
                            @endforeach

                        </select>

                        @error('id_type_tribunal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            الجهة <span class="text-danger">*</span>
                        </label>

                        <select id="region" class="form-select">
                            <option value="">— اختر الجهة —</option>

                            @foreach($regions as $region)
                                <option value="{{ $region->id }}"
                                    @selected(optional($tribunal->province->region)->id == $region->id)>
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

    {{-- ── الجانب الجانبي ── --}}
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

                    <dt class="col-6 text-muted fw-normal">تاريخ الإنشاء</dt>
                    <dd class="col-6">{{ $tribunal->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">آخر تعديل</dt>
                    <dd class="col-6">{{ $tribunal->updated_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">القضاة</dt>
                    <dd class="col-6">
                        @php $nb = $tribunal->juges()->count(); @endphp
                        <span class="badge bg-{{ $nb > 0 ? 'info' : 'secondary' }} bg-opacity-15 text-white">
                            {{ $nb }} قاضٍ
                        </span>
                    </dd>

                    <dt class="col-6 text-muted fw-normal">الملفات</dt>
                    <dd class="col-6">
                        @php $nbD = $tribunal->dossierTribunaux()->count(); @endphp
                        <span class="badge bg-{{ $nbD > 0 ? 'primary' : 'secondary' }} bg-opacity-15 text-white">
                            {{ $nbD }} ملف
                        </span>
                    </dd>

                </dl>
            </div>

        </div>
    </div>

</div>

{{-- ── الأزرار ── --}}
<div class="d-flex gap-2 justify-content-end mt-4">

    <a href="{{ route('tribunaux.show', $tribunal) }}" class="btn btn-outline-secondary">
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

    let regionSelect = document.getElementById('region');
    let provinceSelect = document.getElementById('province');

    // 👇 الإقليم الحالي المخزن في قاعدة البيانات
    let selectedProvinceId = "{{ old('id_province', $tribunal->id_province) }}";

    function loadProvinces(regionId, selectedProvince = null) {

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
            if (!res.ok) throw new Error("Error loading provinces");
            return res.json();
        })
        .then(data => {

            provinceSelect.innerHTML = '<option value="">— اختر الإقليم —</option>';

            data.forEach(province => {

                let selected = (province.id == selectedProvince) ? 'selected' : '';

                provinceSelect.innerHTML += `
                    <option value="${province.id}" ${selected}>
                        ${province.province}
                    </option>
                `;
            });
        })
        .catch(err => {
            console.error(err);
            provinceSelect.innerHTML = '<option value="">تعذر تحميل الأقاليم</option>';
        });
    }

    // 👇 عند تغيير الجهة
    regionSelect.addEventListener('change', function () {
        loadProvinces(this.value);
    });

    // 👇 عند فتح الصفحة (مهم جداً)
    if (regionSelect.value) {
        loadProvinces(regionSelect.value, selectedProvinceId);
    }

});
</script>
@endpush