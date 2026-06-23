@extends('layouts.app')

@section('title', 'طرف جديد')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">الرئيسية</a>
    </li>

    <li class="breadcrumb-item">
        <a href="{{ route('parties.index') }}">الأطراف</a>
    </li>

    <li class="breadcrumb-item active">
        طرف جديد
    </li>
@endsection

@push('styles')
<style>
    body{
        direction: rtl;
        text-align: right;
        font-family: "Tajawal", sans-serif;
    }

    .input-group .form-control{
        border-right: 0 !important;
    }

    .input-group-text{
        border-left: 0 !important;
    }

    .form-control,
    .form-select{
        text-align: right;
    }

    .font-ltr{
        direction: ltr;
        text-align: left;
    }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">

    <div>

        <h4 class="fw-bold mb-1">
            <i class="bi bi-person-plus text-primary ms-2"></i>
            طرف جديد
        </h4>

        <p class="text-muted small mb-0">
            قم بإدخال معلومات الطرف الجديد.
        </p>

    </div>

    <a href="{{ route('parties.index') }}"
       class="btn btn-outline-secondary btn-sm">

        <i class="bi bi-arrow-right ms-1"></i>
        العودة إلى القائمة

    </a>

</div>

<form action="{{ route('parties.store') }}" method="POST">
@csrf

<div class="row g-4">

    {{-- ── العمود الرئيسي ── --}}
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white border-bottom py-3">

                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-vcard ms-2 text-primary"></i>
                    معلومات الطرف
                </h6>

            </div>

            <div class="card-body">

                <div class="row g-3">

                    {{-- الاسم --}}
                    <div class="col-sm-7">

                        <label class="form-label fw-semibold small">
                            الاسم / التسمية
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="nom_partie"
                               class="form-control @error('nom_partie') is-invalid @enderror"
                               value="{{ old('nom_partie') }}"
                               placeholder="الاسم الكامل أو اسم الشركة"
                               required>

                        @error('nom_partie')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- المعرف --}}
                    <div class="col-sm-5">

                        <label class="form-label fw-semibold small">
                            رقم التعريف
                            <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">

                            <input type="text"
                                   name="identifiant_unique"
                                   class="form-control font-ltr @error('identifiant_unique') is-invalid @enderror"
                                   value="{{ old('identifiant_unique') }}"
                                   placeholder="CIN, RC, CNSS..."
                                   required>

                            <span class="input-group-text bg-white">
                                <i class="bi bi-fingerprint text-muted"></i>
                            </span>

                        </div>

                        @error('identifiant_unique')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text">
                            CIN للأشخاص الذاتيين، و RC للأشخاص الاعتباريين.
                        </div>

                    </div>

                    {{-- النوع --}}
                    <div class="col-sm-6">

                        <label class="form-label fw-semibold small">
                            النوع
                            <span class="text-danger">*</span>
                        </label>

                        <select name="type_personne"
                                class="form-select @error('type_personne') is-invalid @enderror"
                                required>

                            <option value="">
                                — اختر —
                            </option>

                            <option value="ذاتي"
                                @selected(old('type_personne') === 'ذاتي')>
                                شخص ذاتي
                            </option>

                            <option value="اعتباري"
                                @selected(old('type_personne') === 'اعتباري')>
                                شخص اعتباري
                            </option>

                        </select>

                        @error('type_personne')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- تاريخ الازدياد --}}
                    <div class="col-sm-6">

                        <label class="form-label fw-semibold small">
                            تاريخ الميلاد
                            <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                            name="date_naissance"
                            class="form-control @error('date_naissance') is-invalid @enderror"
                            value="{{ old('date_naissance') }}">

                        @error('date_naissance')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- الهاتف --}}
                    <div class="col-sm-6">

                        <label class="form-label fw-semibold small">
                            الهاتف
                        </label>

                        <div class="input-group">

                            <input type="tel"
                                   name="telephone"
                                   class="form-control font-ltr @error('telephone') is-invalid @enderror"
                                   value="{{ old('telephone') }}"
                                   placeholder="0612345678"
                                   pattern="^(\+212|00212|0)(5|6|7)[0-9]{8}$"
                                   title="الصيغة المطلوبة: 0612345678 أو +212612345678">

                            <span class="input-group-text bg-white">
                                <i class="bi bi-telephone text-muted"></i>
                            </span>

                            @error('telephone')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="form-text">
                            الصيغة: 06XXXXXXXX أو +212XXXXXXXXX
                        </div>

                    </div>

                    {{-- البريد الإلكتروني --}}
                    <div class="col-sm-6">

                        <label class="form-label fw-semibold small">
                            البريد الإلكتروني
                        </label>

                        <div class="input-group">

                            <input type="email"
                                   name="email"
                                   class="form-control font-ltr @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="contact@exemple.ma">

                            <span class="input-group-text bg-white">
                                <i class="bi bi-envelope text-muted"></i>
                            </span>

                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>

                    {{-- العنوان --}}
                    <div class="col-12">

                        <label class="form-label fw-semibold small">
                            العنوان
                        </label>

                        <textarea name="adresse"
                                  class="form-control @error('adresse') is-invalid @enderror"
                                  rows="2"
                                  placeholder="العنوان البريدي الكامل">{{ old('adresse') }}</textarea>

                        @error('adresse')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- ── العمود الجانبي ── --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm mb-3">

            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-badge ms-2 text-primary"></i>
                    تعيين المحامي
                </h6>
            </div>

            <div class="card-body">

                <label class="form-label fw-semibold small">
                    المحامي المسؤول
                </label>

                <div class="input-group">

                    <select id="avocat-select"
                        name="id_avocat"
                        class="form-select @error('id_avocat') is-invalid @enderror">

                        <option value="">
                            — بدون محامٍ —
                        </option>

                        @foreach($avocats as $av)
                            <option value="{{ $av->id }}"
                                @selected(old('id_avocat') == $av->id)>

                                {{ $av->nom_avocat }}

                            </option>
                        @endforeach

                    </select>
                </div>

                @error('id_avocat')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror

                <div class="form-text mt-2">
                    يمكنك ربط هذا الطرف بمحامٍ مسؤول عنه.
                </div>

            </div>

        </div>

    </div>

</div>

{{-- ── الإجراءات ── --}}
<div class="d-flex gap-2 justify-content-start mt-4">

    <a href="{{ route('parties.index') }}"
       class="btn btn-outline-secondary">

        <i class="bi bi-x-lg ms-1"></i>
        إلغاء

    </a>

    <button type="submit"
            class="btn btn-primary px-4">

        <i class="bi bi-check-lg ms-1"></i>
        حفظ

    </button>

</div>

</form>

@endsection

@push('scripts')
<script>
new TomSelect('#avocat-select', {
    create: function(input) {
        window.location.href = "{{ route('avocats.create') }}?nom=" + encodeURIComponent(input);
        return false;
    },

    placeholder: 'ابحث عن محامٍ ...',

    loadingText: 'جاري البحث...',

    render: {
        no_results: function(data, escape) {
            return `<div class="no-results">لا توجد نتائج</div>`;
        },

        option_create: function(data, escape) {
            return `<div class="create">➕ إضافة "${escape(data.input)}"</div>`;
        }
    }
});
</script>
@endpush