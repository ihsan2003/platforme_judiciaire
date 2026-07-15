@extends('layouts.app')

@section('title', 'تعديل الملف #' . $dossier->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dossiers.index') }}">الملفات</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dossiers.show', $dossier) }}">{{ $dossier->id }}</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning me-2"></i>تعديل الملف القضائي
        </h4>
        <p class="text-muted small mb-0">
            أنت بصدد تعديل الملف رقم <strong>{{ $dossier->id }}</strong>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('dossiers.show', $dossier) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>العودة إلى الملف
        </a>
    </div>
</div>

<form action="{{ route('dossiers.update', $dossier) }}" method="POST">
@csrf @method('PUT')

<div class="row g-4">

    {{-- ══ العمود الرئيسي ══ --}}
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-card-text me-2 text-warning"></i>معلومات الملف
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
                                <option value="{{ $type->id }}" data-code="{{ $type->code }}" 
                                    @selected(old('id_type_affaire', $dossier->id_type_affaire) == $type->id)>
                                    {{ $type->affaire }} (رمز: {{ $type->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_type_affaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- تعديل رقم المحكمة --}}
                    @php
                        // Extraction des composants du numéro existant (ex: 2024 / 1201 / 450)
                        $parts = explode(' / ', $dossier->numero_dossier_tribunal);
                        $annee = $parts[0] ?? date('Y');
                        $code  = $parts[1] ?? '';
                        $ordre = $parts[2] ?? '';
                    @endphp

                    <div class="col-12">
                        <div class="p-3 bg-light rounded border border-warning border-opacity-25">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label class="small mb-1">السنة</label>
                                    <input type="number" name="annee_mahakim" id="annee_mahakim" class="form-control text-center fw-bold" value="{{ old('annee_mahakim', $annee) }}" min="1900" max="2100">
                                </div>
                                <div class="col-md-3">
                                    <label class="small mb-1">رمز الفئة</label>
                                    <input type="text" name="code_mahakim" id="code_mahakim" class="form-control text-center bg-white fw-bold" readonly value="{{ old('code_mahakim', $code) }}">
                                </div>
                                <div class="col-md-5">
                                    <label class="small mb-1">رقم الترتيب</label>
                                    <input type="number" name="ordre_mahakim" id="ordre_mahakim" class="form-control text-center fw-bold" placeholder="مثال: 450" value="{{ old('ordre_mahakim', $ordre) }}">
                                </div>
                            </div>
                            <div class="mt-3 text-center">
                                <div class="small text-muted mb-1">الرقم النهائي:</div>
                                <div id="preview_mahakim" class="h5 fw-bold text-dark border-bottom d-inline-block px-4 pb-1" style="letter-spacing: 2px;">{{ $dossier->numero_dossier_tribunal ?? '— / — / —' }}</div>
                                <input type="hidden" name="numero_dossier_tribunal" id="numero_dossier_tribunal" value="{{ $dossier->numero_dossier_tribunal }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 mt-4">
                        <label class="form-label fw-semibold small">حالة الملف <span class="text-danger">*</span></label>
                        <select name="id_statut_dossier" class="form-select @error('id_statut_dossier') is-invalid @enderror">
                            @foreach($statutDossiers as $statut)
                                <option value="{{ $statut->id }}" @selected(old('id_statut_dossier', $dossier->id_statut_dossier) == $statut->id)>
                                    {{ $statut->statut_dossier }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 mt-4">
                        <label class="form-label fw-semibold small">تاريخ الافتتاح <span class="text-danger">*</span></label>
                        <input type="date" name="date_ouverture" class="form-control" value="{{ old('date_ouverture', $dossier->date_ouverture?->format('Y-m-d')) }}">
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ══ العمود الجانبي ══ --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2 text-muted"></i>ملخص الملف</h6>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">الرقم</dt>
                    <dd class="col-7 fw-bold">{{ $dossier->id }}</dd>

                    <dt class="col-5 text-muted">تاريخ الإنشاء</dt>
                    <dd class="col-7">{{ $dossier->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-5 text-muted">بواسطة</dt>
                    <dd class="col-7">{{ $dossier->createdBy->name ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-warning py-2">
                <i class="bi bi-check-lg me-1"></i>حفظ التعديلات
            </button>
            <a href="{{ route('dossiers.show', $dossier) }}" class="btn btn-outline-secondary">
                إلغاء
            </a>
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
    });
</script>
@endpush

@push('styles')
<style>
    #preview_mahakim { direction: ltr; }
</style>
@endpush
