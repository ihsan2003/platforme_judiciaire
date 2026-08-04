{{-- resources/views/reclamations/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'تعديل الشكاية — ' . $reclamation->objet)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reclamations.index') }}">الشكايات</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-12">
        
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square me-2 text-primary"></i>تعديل بيانات الشكاية</h4>
                <p class="text-muted small mb-0">يرجى تحديث المعلومات الضرورية في النموذج أدناه.</p>
            </div>
            <a href="{{ route('reclamations.show', $reclamation) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-right ms-1"></i>العودة للشكاية
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger shadow-sm border-0 mb-4">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle ms-2"></i>يرجى تصحيح الأخطاء التالية:</div>
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('reclamations.update', $reclamation) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-4">
                {{-- القسم الأول: معلومات الشكاية --}}
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="mb-4">
                                <label class="form-label fw-semibold small">الموضوع <span class="text-danger">*</span></label>
                                <input type="text" name="objet" class="form-control @error('objet') is-invalid @enderror" 
                                       value="{{ old('objet', $reclamation->objet) }}" required placeholder="أدخل موضوع الشكاية...">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold small">التفاصيل / الوصف <span class="text-danger">*</span></label>
                                <textarea name="details" class="form-control @error('details') is-invalid @enderror" 
                                          rows="8" required placeholder="اشرح تفاصيل الشكاية هنا...">{{ old('details', $reclamation->details) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- القسم الثاني: الإعدادات والحالة --}}
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body pt-0">
                            <div class="mb-3 mt-3">
                                <label class="form-label fw-semibold small">تاريخ الاستلام <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="date" name="date_reception" class="form-control @error('date_reception') is-invalid @enderror" 
                                           value="{{ old('date_reception', $reclamation->date_reception?->format('Y-m-d')) }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">نوع الشكاية <span class="text-danger">*</span></label>
                                <select name="id_type_reclamation" class="form-select @error('id_type_reclamation') is-invalid @enderror" required>
                                    @foreach($typesReclamation as $type)
                                        <option value="{{ $type->id }}" @selected(old('id_type_reclamation', $reclamation->id_type_reclamation) == $type->id)>
                                            {{ $type->type_reclamation }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">حالة الشكاية <span class="text-danger">*</span></label>
                                <select name="id_statut_reclamation" class="form-select @error('id_statut_reclamation') is-invalid @enderror" required>
                                    @foreach($statuts as $statut)
                                        <option value="{{ $statut->id }}" @selected(old('id_statut_reclamation', $reclamation->id_statut_reclamation) == $statut->id)>
                                            {{ $statut->statut_reclamation }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-0">

                                <label class="form-label fw-semibold small">
                                    المشتكي <span class="text-danger">*</span>
                                </label>

                                <select id="reclamant-select" class="form-select @error('id_reclamant') is-invalid @enderror">
                                    <option value=""></option>
                                    @foreach($reclamants as $r)
                                        <option value="{{ $r->id }}"
                                            @selected(old('id_reclamant', $reclamation->id_reclamant) == $r->id)>
                                            {{ $r->nom }}
                                        </option>
                                    @endforeach
                                </select>

                                <input type="hidden" name="id_reclamant" id="id_reclamant_hidden"
                                    value="{{ old('id_reclamant', $reclamation->id_reclamant) }}">

                                @error('id_reclamant')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>
                    </div>

                    {{-- ══ الأزرار ══ --}}
                    <div class="d-grid gap-2 mt-3">

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg ms-1"></i>
                            حفظ التعديلات
                        </button>

                        <a href="{{ route('reclamations.show', $reclamation) }}"
                        class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg ms-1"></i>
                            إلغاء
                        </a>

                    </div>                   
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* تحسينات بصرية للنموذج */
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }
    .input-group-text {
        border-right: 1px solid #dee2e6 !important;
        border-left: 0 !important;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const reclamants = @json($reclamants->keyBy('id'));
    const champIdReclamant = document.getElementById('id_reclamant_hidden');

    const tomSelect = new TomSelect('#reclamant-select', {
        persist: false,
        placeholder: 'ابحث عن مشتكي...',
        onItemAdd: function (value) {
            champIdReclamant.value = value;
        },
        onItemRemove: function () {
            champIdReclamant.value = '';
        }
    });

    // Pré-sélection : priorité à old() (retour après erreur), sinon le réclamant actuel
    const valeurInitiale = '{{ old('id_reclamant', $reclamation->id_reclamant) }}';
    if (valeurInitiale) {
        tomSelect.setValue(valeurInitiale);
    }
})();
</script>
@endpush