@extends('layouts.app')

@section('title', 'تعديل الطرف')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('parties.index') }}">الأطراف</a></li>
    <li class="breadcrumb-item"><a href="{{ route('parties.show', $partie) }}">{{ $partie->nom_partie }}</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning me-2"></i>تعديل الطرف
        </h4>
        <p class="text-muted small mb-0">
            تحديث بيانات <strong>{{ $partie->nom_partie }}</strong>
        </p>
    </div>

    <a href="{{ route('parties.show', $partie) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>العودة إلى البطاقة
    </a>
</div>

<form action="{{ route('parties.update', $partie) }}" method="POST">
@csrf
@method('PUT')

<div class="row g-4">

    {{-- ── العمود الرئيسي ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-vcard me-2 text-warning"></i>
                    معلومات الطرف
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    {{-- الاسم --}}
                    <div class="col-sm-7">
                        <label class="form-label fw-semibold small">
                            الاسم / التسمية <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="nom_partie"
                               class="form-control @error('nom_partie') is-invalid @enderror"
                               value="{{ old('nom_partie', $partie->nom_partie) }}"
                               required>

                        @error('nom_partie')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- المعرف الفريد --}}
                    <div class="col-sm-5">
                        <label class="form-label fw-semibold small">
                            المعرف الفريد <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-fingerprint text-muted"></i>
                            </span>

                            <input type="text"
                                   name="identifiant_unique"
                                   class="form-control @error('identifiant_unique') is-invalid @enderror"
                                   value="{{ old('identifiant_unique', $partie->identifiant_unique) }}"
                                   required>
                        </div>

                        @error('identifiant_unique')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- نوع الشخص --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            النوع <span class="text-danger">*</span>
                        </label>

                        <select name="type_personne"
                                class="form-select @error('type_personne') is-invalid @enderror"
                                required>

                            <option value="">— اختر —</option>

                            <option value="Physique"
                                @selected(old('type_personne', $partie->type_personne) === 'Physique')>
                                شخص طبيعي
                            </option>

                            <option value="Morale"
                                @selected(old('type_personne', $partie->type_personne) === 'Morale')>
                                شخص اعتباري
                            </option>
                        </select>

                        @error('type_personne')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- تاريخ الميلاد --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            تاريخ الميلاد
                        </label>

                        <div class="input-group">

                            <input type="date"
                                name="date_naissance"
                                class="form-control @error('date_naissance') is-invalid @enderror"
                                value="{{ old('date_naissance', optional($partie->date_naissance)->format('Y-m-d')) }}">
                        </div>

                        @error('date_naissance')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- الهاتف --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">الهاتف</label>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-telephone text-muted"></i>
                            </span>

                            <input type="tel"
                                   name="telephone"
                                   class="form-control @error('telephone') is-invalid @enderror"
                                   value="{{ old('telephone', $partie->telephone) }}"
                                   pattern="^(\+212|00212|0)(5|6|7)[0-9]{8}$"
                                   title="الصيغة المطلوبة: 0612345678 أو +212612345678">

                            @error('telephone')
                                <div class="invalid-feedback">{{ $message }}</div>
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
                            <span class="input-group-text bg-white">
                                <i class="bi bi-envelope text-muted"></i>
                            </span>

                            <input type="email"
                                   name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $partie->email) }}">

                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- العنوان --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">العنوان</label>

                        <textarea name="adresse"
                                  class="form-control @error('adresse') is-invalid @enderror"
                                  rows="2">{{ old('adresse', $partie->adresse) }}</textarea>

                        @error('adresse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ── العمود الجانبي ── --}}
    <div class="col-lg-4">

    {{-- ── AVOCAT ── --}}
    <div class="card border-0 shadow-sm mb-3">

        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-person-badge me-2 text-primary"></i>
                المحامي
            </h6>
        </div>

        <div class="card-body">

            <label class="form-label fw-semibold small">
                اختيار المحامي
            </label>

            <div class="input-group">

                <select id="avocat-select"
                        name="id_avocat"
                        class="form-select @error('id_avocat') is-invalid @enderror">

                    <option value="">— بدون محامٍ —</option>

                    @foreach($avocats as $av)
                        <option value="{{ $av->id }}"
                            @selected(old('id_avocat', $partie->id_avocat) == $av->id)>

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
                يمكنك البحث أو اختيار محامٍ من القائمة.
            </div>

        </div>
    </div>

    {{-- ── SUMMARY ── --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-info-circle me-2 text-muted"></i>
                الملخص
            </h6>
        </div>

        <div class="card-body small">
            <dl class="row mb-0">

                <dt class="col-6 text-muted fw-normal">المعرف</dt>
                <dd class="col-6 font-monospace">{{ $partie->identifiant_unique }}</dd>

                <dt class="col-6 text-muted fw-normal">العمر</dt>
                <dd class="col-6">
                    {{ $partie->date_naissance ? $partie->date_naissance->age . ' سنة' : '—' }}
                </dd>

                <dt class="col-6 text-muted fw-normal">تاريخ الإنشاء</dt>
                <dd class="col-6">{{ $partie->created_at->format('d/m/Y') }}</dd>

                <dt class="col-6 text-muted fw-normal">آخر تعديل</dt>
                <dd class="col-6">{{ $partie->updated_at->format('d/m/Y') }}</dd>

                <dt class="col-6 text-muted fw-normal">الملفات</dt>
                <dd class="col-6">
                    <span class="badge bg-info bg-opacity-15 text-white border border-info border-opacity-25">
                        {{ $partie->dossiers()->count() }} ملف(ات)
                    </span>
                </dd>

            </dl>
        </div>
    </div>

</div>

</div>

{{-- ── الإجراءات ── --}}
<div class="d-flex gap-2 justify-content-end mt-4">

    <a href="{{ route('parties.show', $partie) }}"
       class="btn btn-outline-secondary">
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