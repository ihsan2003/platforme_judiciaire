@extends('layouts.app')

@section('title', 'ملف قضائي جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dossiers.index') }}">الملفات</a></li>
    <li class="breadcrumb-item active">ملف جديد</li>
@endsection

@section('content')

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-folder-plus text-primary me-2"></i>إضافة ملف قضائي جديد
            </h4>
            <p class="text-muted small mb-0">قم بإدخال معلومات الملف بدقة لضمان المتابعة.</p>
        </div>
        <a href="{{ route('dossiers.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-right me-1"></i>العودة إلى القائمة
        </a>
    </div>

    <form action="{{ route('dossiers.store') }}" method="POST" id="dossierForm">
    @csrf

    <div class="row g-4">
        <div class="col-lg-8 mx-auto">

            {{-- معلومات الملف الأساسية --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-folder2-open me-2 text-primary"></i>معلومات الملف الأساسية
                    </h6>
                </div>

                <div class="card-body p-4">
                    <div class="row g-3">



                        {{-- نوع القضية --}}
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold small">نوع القضية <span class="text-danger">*</span></label>
                            <select name="id_type_affaire" id="id_type_affaire" class="form-select @error('id_type_affaire') is-invalid @enderror" required>
                                <option value="" data-code="">— اختر نوع القضية —</option>
                                @foreach($typesAffaire as $type)
                                    <option value="{{ $type->id }}" data-code="{{ $type->code }}" @selected(old('id_type_affaire') == $type->id)>
                                        {{ $type->affaire }} (رمز: {{ $type->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_type_affaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- توليد رقم المحكمة --}}
                        <div class="col-12">
                            <div class="p-3 bg-light rounded border">
                                <label class="form-label fw-bold small mb-3 text-primary">توليد رقم ملف المحكمة (Mahakim)</label>
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4">
                                        <label class="small mb-1">السنة</label>
                                        <input type="number" name="annee_mahakim" id="annee_mahakim" class="form-control text-center fw-bold" value="{{ date('Y') }}" min="1900" max="2100">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small mb-1">رمز الفئة</label>
                                        <input type="text" name="code_mahakim" id="code_mahakim" class="form-control text-center bg-white fw-bold" readonly value="{{ old('code_mahakim') }}">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="small mb-1">رقم الترتيب</label>
                                        <input type="number" name="ordre_mahakim" id="ordre_mahakim" class="form-control text-center fw-bold" placeholder="مثال: 450" value="{{ old('ordre_mahakim') }}">
                                    </div>
                                </div>
                                <div class="mt-3 text-center">
                                    <div class="small text-muted mb-1">الرقم النهائي الذي سيتم تسجيله:</div>
                                    <div id="preview_mahakim" class="h5 fw-bold text-dark border-bottom d-inline-block px-4 pb-1" style="letter-spacing: 2px;">— / — / —</div>
                                    <input type="hidden" name="numero_dossier_tribunal" id="numero_dossier_tribunal">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- التواريخ --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-calendar3 me-2 text-success"></i>التواريخ
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">تاريخ فتح الملف <span class="text-danger">*</span></label>
                            <input type="date" name="date_ouverture" class="form-control" value="{{ old('date_ouverture', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">تاريخ الإغلاق</label>
                            <input type="date" name="date_cloture" class="form-control" value="{{ old('date_cloture') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-5">إنشاء الملف</button>
                <a href="{{ route('dossiers.index') }}" class="btn btn-outline-secondary">إلغاء</a>
            </div>

        </div>
    </div>
    </form>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('id_type_affaire');
        const anneeInput = document.getElementById('annee_mahakim');
        const codeInput  = document.getElementById('code_mahakim');
        const ordreInput = document.getElementById('ordre_mahakim');
        const preview    = document.getElementById('preview_mahakim');
        const hiddenInput = document.getElementById('numero_dossier_tribunal');

        function updatePreview() {
            const annee = anneeInput.value || '—';
            const code  = codeInput.value || '—';
            const ordre = ordreInput.value || '—';
            
            const final = `${annee} / ${code} / ${ordre}`;
            preview.innerText = final;
            hiddenInput.value = final;
        }

        typeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            codeInput.value = selectedOption.getAttribute('data-code') || '';
            updatePreview();
        });

        [anneeInput, ordreInput].forEach(el => {
            el.addEventListener('input', updatePreview);
        });

        // Initialiser si retour de validation
        if(typeSelect.value) {
            const selectedOption = typeSelect.options[typeSelect.selectedIndex];
            codeInput.value = selectedOption.getAttribute('data-code') || '';
            updatePreview();
        }
    });
</script>
@endpush

@push('styles')
<style>
    .form-control:focus, .form-select:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1); }
    #preview_mahakim { direction: ltr; }
</style>
@endpush
