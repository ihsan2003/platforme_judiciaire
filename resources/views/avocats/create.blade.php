@extends('layouts.app')

@section('title', 'محامٍ جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('avocats.index') }}">المحامون</a></li>
    <li class="breadcrumb-item active">محامٍ جديد</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-person-plus text-primary me-2"></i>محامٍ جديد
        </h4>
        <p class="text-muted small mb-0">إدخال بيانات محامٍ جديد في النظام.</p>
    </div>
    <a href="{{ route('avocats.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>العودة إلى القائمة
    </a>
</div>

<form action="{{ route('avocats.store') }}" method="POST">
@csrf

<div class="row g-4 align-items-stretch">
    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-vcard me-2 text-primary"></i>معلومات المحامي
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- Nom --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            الاسم الكامل <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="nom_avocat"
                               class="form-control @error('nom_avocat') is-invalid @enderror"
                               value="{{ old('nom_avocat', request('nom')) }}"
                               placeholder="مثال: حسن بن علي"
                               required>
                        @error('nom_avocat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Téléphone --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            الهاتف <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-telephone text-muted"></i>
                            </span>
                            <input type="tel"
                                   name="telephone"
                                   class="form-control @error('telephone') is-invalid @enderror"
                                   value="{{ old('telephone') }}"
                                   placeholder="0612345678"
                                   pattern="^(\+212|00212|0)(5|6|7)[0-9]{8}$"
                                   title="الصيغة المطلوبة: 0612345678 أو +212612345678"
                                   required>
                            @error('telephone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text">الصيغة: 06XXXXXXXX أو +212XXXXXXXXX</div>
                    </div>

                    {{-- Email --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            البريد الإلكتروني <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-envelope text-muted"></i>
                            </span>
                            <input type="email"
                                   name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="contact@cabinet.ma"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ── Colonne latérale ── --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100 mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-lines-fill me-2 text-primary"></i>ربط بأطراف
                </h6>
            </div>
            <div class="card-body">
                <label class="form-label fw-semibold small">
                    الأطراف الممثَّلة
                </label>

                <div class="input-group">
                    <select id="parties-select"
                        name="parties[]"
                        class="form-select @error('parties') is-invalid @enderror"
                        multiple>

                        @foreach($parties as $p)
                            <option value="{{ $p->id }}"
                                @selected(in_array($p->id, old('parties', [])))>
                                {{ $p->nom_partie }}
                            </option>
                        @endforeach

                    </select>
                </div>

                @error('parties')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror

                <div class="form-text mt-2">
                    يمكنك ربط هذا المحامي بطرف أو أكثر يمثلهم.
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Actions ── --}}
<div class="d-flex gap-2 justify-content-end mt-4">
    <a href="{{ route('avocats.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>إلغاء
    </a>
    <button type="submit" class="btn btn-primary px-4">
        <i class="bi bi-check-lg me-1"></i>حفظ
    </button>
</div>

</form>
@endsection

@push('scripts')
<script>
new TomSelect('#parties-select', {
    plugins: ['remove_button'],

    create: function(input) {
        window.location.href = "{{ route('parties.create') }}?nom_partie=" + encodeURIComponent(input);
        return false;
    },

    placeholder: 'ابحث عن طرف ...',

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