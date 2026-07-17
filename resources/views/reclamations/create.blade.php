{{-- resources/views/reclamations/create_ar.blade.php --}}
@extends('layouts.app')

@section('title', 'شكاية جديدة')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">الرئيسية</a>
    </li>

    <li class="breadcrumb-item">
        <a href="{{ route('reclamations.index') }}">الشكايات</a>
    </li>

    <li class="breadcrumb-item active">
        جديدة
    </li>
@endsection

@section('content')

<div dir="rtl">

<div class="d-flex align-items-center justify-content-between mb-4">


    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-chat-left-text text-primary ms-2"></i>
            شكاية جديدة
        </h4>

        <p class="text-muted small mb-0">
            قم بتسجيل معلومات المشتكي وتفاصيل الشكاية.
        </p>
    </div>

    <a href="{{ route('reclamations.index') }}"
       class="btn btn-outline-secondary btn-sm">
        العودة إلى القائمة
        <i class="bi bi-arrow-right ms-1"></i>
    </a>

</div>

<form action="{{ route('reclamations.store') }}"
      method="POST"
      enctype="multipart/form-data">

@csrf

<div class="row g-4">

    {{-- ══ العمود الأيسر ══ --}}
    <div class="col-lg-7">

        {{-- معلومات المشتكي --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person ms-2 text-primary"></i>
                    معلومات المشتكي
                </h6>
            </div>

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-sm-8">

                        <label class="form-label fw-semibold small">
                            الاسم / التسمية <span class="text-danger">*</span>
                        </label>

                        <select id="reclamant-select" class="form-select @error('nom_reclamant') is-invalid @enderror @error('id_reclamant') is-invalid @enderror">
                            <option value=""></option>
                            @foreach($reclamants as $r)
                                <option value="{{ $r->id }}"
                                    @selected(old('id_reclamant') == $r->id)>
                                    {{ $r->nom }}
                                </option>
                            @endforeach
                        </select>

                        <input type="hidden" name="id_reclamant" id="id_reclamant_hidden" value="{{ old('id_reclamant') }}">
                        <input type="hidden" name="nom_reclamant" id="nom_reclamant_hidden" value="{{ old('nom_reclamant') }}">

                        @error('nom_reclamant')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('id_reclamant')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        <div class="form-text">
                            <i class="bi bi-info-circle ms-1"></i>
                            ابحث عن مشتكي موجود، أو اكتب اسما جديدا للإنشاء.
                        </div>

                    </div>

                    <div class="col-sm-4" id="col-type-reclamant">

                        <label class="form-label fw-semibold small">
                            نوع المشتكي <span class="text-danger">*</span>
                        </label>

                        <select id="id_type_reclamant"
                                name="id_type_reclamant"
                                class="form-select @error('id_type_reclamant') is-invalid @enderror"
                                required>

                            <option value="">
                                — اختر —
                            </option>

                            @foreach($typesReclamant as $type)

                                <option value="{{ $type->id }}"
                                    @selected(old('id_type_reclamant') == $type->id)>

                                    {{ $type->type_reclamant }}

                                </option>

                            @endforeach

                        </select>

                        @error('id_type_reclamant')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                {{-- بلوك: عرض معلومات مشتكي موجود تم اختياره --}}
                <div id="bloc-reclamant-info" class="alert alert-light border small mt-3 mb-0 d-none">
                    <i class="bi bi-person-check text-success ms-1"></i>
                    مشتكي موجود مسبقا —
                    <span id="info-type" class="fw-semibold"></span>
                    <span id="info-tel"></span>
                    <span id="info-email"></span>
                    <span id="info-adresse"></span>
                </div>

                {{-- بلوك: حقول مشتكي جديد --}}
                <div id="bloc-reclamant-nouveau" class="row g-3 mt-0">

                    <div class="col-sm-6">

                        <label class="form-label fw-semibold small">
                            الهاتف
                        </label>

                        <div class="input-group">

                            <input type="tel"
                                   name="telephone_reclamant"
                                   id="telephone_reclamant"
                                   class="form-control @error('telephone_reclamant') is-invalid @enderror"
                                   value="{{ old('telephone_reclamant') }}"
                                   placeholder="0612345678">

                            <span class="input-group-text">
                                <i class="bi bi-telephone text-muted"></i>
                            </span>

                        </div>

                        @error('telephone_reclamant')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-sm-6">

                        <label class="form-label fw-semibold small">
                            البريد الإلكتروني
                        </label>

                        <div class="input-group">

                            <input type="email"
                                   name="email_reclamant"
                                   id="email_reclamant"
                                   class="form-control @error('email_reclamant') is-invalid @enderror"
                                   value="{{ old('email_reclamant') }}"
                                   placeholder="contact@example.com">

                            <span class="input-group-text">
                                <i class="bi bi-envelope text-muted"></i>
                            </span>

                        </div>

                        @error('email_reclamant')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-12">

                        <label class="form-label fw-semibold small">
                            العنوان
                        </label>

                        <textarea name="adresse_reclamant"
                                  id="adresse_reclamant"
                                  class="form-control @error('adresse_reclamant') is-invalid @enderror"
                                  rows="2"
                                  placeholder="العنوان البريدي الكامل">{{ old('adresse_reclamant') }}</textarea>

                        @error('adresse_reclamant')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>

        </div>

        {{-- تفاصيل الشكاية --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-chat-left-dots ms-2 text-primary"></i>
                    تفاصيل الشكاية
                </h6>
            </div>

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-sm-12">

                        <label class="form-label fw-semibold small">
                            الموضوع <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="objet"
                               class="form-control @error('objet') is-invalid @enderror"
                               value="{{ old('objet') }}"
                               placeholder="ملخص مختصر للشكاية"
                               required>

                        @error('objet')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-12">

                        <label class="form-label fw-semibold small">
                            الوصف التفصيلي
                        </label>

                        <textarea name="details"
                                  class="form-control @error('details') is-invalid @enderror"
                                  rows="5"
                                  placeholder="قم بوصف الشكاية بالتفصيل...">{{ old('details') }}</textarea>

                        @error('details')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- ══ العمود الأيمن ══ --}}
    <div class="col-lg-5">

        {{-- الإعدادات والوثائق --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-sliders ms-2 text-primary"></i>
                    الإعدادات
                </h6>
            </div>

            <div class="card-body">

                <div class="mb-3">

                    <div class="mt-2">

                        <label class="form-label fw-semibold small">
                            نوع الشكاية <span class="text-danger">*</span>
                        </label>

                        <select name="id_type_reclamation"
                                class="form-select @error('id_type_reclamation') is-invalid @enderror"
                                required>

                            <option value="">
                                — اختر —
                            </option>

                            @foreach($typesReclamation as $type)

                                <option value="{{ $type->id }}"
                                    @selected(old('id_type_reclamation') == $type->id)>

                                    {{ $type->type_reclamation }}

                                </option>

                            @endforeach

                        </select>

                        @error('id_type_reclamation')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="mt-2">

                        <label class="form-label fw-semibold small">
                            تاريخ الاستلام <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               name="date_reception"
                               class="form-control @error('date_reception') is-invalid @enderror"
                               value="{{ old('date_reception', date('Y-m-d')) }}"
                               required>

                        @error('date_reception')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="mt-2">
                        <label class="form-label fw-semibold small">
                            الحالة الأولية
                        </label>

                        <select name="id_statut_reclamation"
                                class="form-select @error('id_statut_reclamation') is-invalid @enderror">

                            <option value="">
                                — اختر —
                            </option>

                            @foreach($statuts as $statut)

                                <option value="{{ $statut->id }}"
                                    @selected(old('id_statut_reclamation') == $statut->id)>

                                    {{ $statut->statut_reclamation }}

                                </option>

                            @endforeach

                        </select>

                        @error('id_statut_reclamation')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text">
                            إذا لم يتم تحديد الحالة، سيتم اعتماد "قيد المعالجة" بشكل افتراضي.
                        </div>
                    </div>

                </div>


                <div class="mb-3 mt-5">

                    <label class="form-label fw-semibold small">
                        وثيقة مرفقة (اختياري)
                    </label>

                    <input type="file"
                           name="document"
                           class="form-control @error('document') is-invalid @enderror"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">

                    @error('document')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="form-text">
                        PDF ،Word ،Excel ،صور — الحد الأقصى 10 Mo
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

{{-- ══ الأزرار ══ --}}
<div class="d-flex gap-2 justify-content-start mt-2">

    <button type="submit" class="btn btn-primary px-4">
        حفظ الشكاية
        <i class="bi bi-check-lg ms-1"></i>
    </button>

    <a href="{{ route('reclamations.index') }}"
       class="btn btn-outline-secondary">
        إلغاء
        <i class="bi bi-x-lg ms-1"></i>
    </a>

</div>

</form>

</div>

@endsection

@push('scripts')
<script>
(function () {
    // Données des réclamants existants (pour préremplissage / détection)
    const reclamants = @json($reclamants->keyBy('id'));

    const champIdReclamant = document.getElementById('id_reclamant_hidden');
    const champNomHidden   = document.getElementById('nom_reclamant_hidden');
    const champType        = document.getElementById('id_type_reclamant');
    const champTel         = document.getElementById('telephone_reclamant');
    const champEmail       = document.getElementById('email_reclamant');
    const champAdresse     = document.getElementById('adresse_reclamant');
    const blocNouveau      = document.getElementById('bloc-reclamant-nouveau');
    const blocInfo         = document.getElementById('bloc-reclamant-info');

    function afficherModeExistant(r) {
        blocNouveau.classList.add('d-none');
        blocInfo.classList.remove('d-none');

        document.getElementById('info-type').textContent    = r.type_reclamant?.type_reclamant ?? '—';
        document.getElementById('info-tel').textContent     = r.telephone ? ' — ' + r.telephone : '';
        document.getElementById('info-email').textContent   = r.email ? ' — ' + r.email : '';
        document.getElementById('info-adresse').textContent = r.adresse ? ' — ' + r.adresse : '';

        champType.required = false;
        champType.value = r.id_type_reclamant ?? '';
        champTel.value = r.telephone ?? '';
        champEmail.value = r.email ?? '';
        champAdresse.value = r.adresse ?? '';
    }

    function afficherModeNouveau(nom) {
        blocNouveau.classList.remove('d-none');
        blocInfo.classList.add('d-none');

        champType.required = true;
        champNomHidden.value = nom ?? '';
    }

    const tomSelect = new TomSelect('#reclamant-select', {
        create: function (input) {
            return { value: input, text: input };
        },
        persist: false,
        placeholder: 'ابحث عن مشتكي موجود أو اكتب اسما جديدا...',
        loadingText: 'جاري البحث...',

        render: {
            option_create: function (data, escape) {
                return `<div class="create">➕ إضافة مشتكي جديد باسم "${escape(data.input)}"</div>`;
            },
            no_results: function () {
                return `<div class="no-results">لا توجد نتائج</div>`;
            }
        },

        onItemAdd: function (value) {
            const r = reclamants[value];

            if (r) {
                champIdReclamant.value = value;
                afficherModeExistant(r);
            } else {
                champIdReclamant.value = '';
                afficherModeNouveau(value);
            }
        },

        onItemRemove: function () {
            champIdReclamant.value = '';
            champNomHidden.value = '';
            afficherModeNouveau('');
        }
    });

    // Pré-remplissage initial en cas de retour après erreur de validation ("old()")
    @if(old('id_reclamant'))
        tomSelect.setValue('{{ old('id_reclamant') }}');
    @elseif(old('nom_reclamant'))
        tomSelect.addOption({ value: '{{ old('nom_reclamant') }}', text: '{{ old('nom_reclamant') }}' });
        tomSelect.setValue('{{ old('nom_reclamant') }}');
    @endif
})();
</script>
@endpush