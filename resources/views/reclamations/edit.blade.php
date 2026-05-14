{{-- resources/views/reclamations/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'تعديل الشكاية — ' . $reclamation->objet)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reclamations.index') }}">الشكايات</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reclamations.show', $reclamation) }}">تفاصيل الشكاية</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-12">
        
        {{-- الرأس --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-primary">تعديل بيانات الشكاية</h4>
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
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h6 class="mb-0 fw-bold text-dark">
                                <i class="bi bi-pencil-square ms-2 text-primary"></i>محتوى الشكاية
                            </h6>
                        </div>
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
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h6 class="mb-0 fw-bold text-dark">
                                <i class="bi bi-gear ms-2 text-primary"></i>الإعدادات
                            </h6>
                        </div>
                        <div class="card-body pt-0">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">تاريخ الاستلام <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-calendar-check"></i></span>
                                    <input type="date" name="date_reception" class="form-control @error('date_reception') is-invalid @enderror" 
                                           value="{{ old('date_reception', $reclamation->date_reception?->format('Y-m-d')) }}" required>
                                </div>
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
                                <label class="form-label fw-semibold small">المشتكي <span class="text-danger">*</span></label>
                                <select name="id_reclamant" class="form-select @error('id_reclamant') is-invalid @enderror" required>
                                    @foreach($reclamants as $reclamant)
                                        <option value="{{ $reclamant->id }}" @selected(old('id_reclamant', $reclamation->id_reclamant) == $reclamant->id)>
                                            {{ $reclamant->nom }} ({{ $reclamant->typeReclamant?->type_reclamant }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text small">يمكنك تعديل المشتكي إذا تم ربطه بالخطأ.</div>
                            </div>
                        </div>
                    </div>

                    {{-- تنبيه --}}
                    <div class="alert alert-info border-0 shadow-sm small">
                        <i class="bi bi-info-circle-fill ms-2"></i>
                        تعديل البيانات الأساسية لن يؤثر على <strong>سجل الإجراءات</strong> الذي تم تسجيله مسبقاً.
                    </div>
                </div>

                {{-- أزرار التحكم --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-link text-danger text-decoration-none fw-semibold" 
                                    onclick="if(confirm('هل أنت متأكد من رغبتك في إلغاء التعديلات؟')) window.location.href='{{ route('reclamations.show', $reclamation) }}'">
                                إلغاء التغييرات
                            </button>
                            <button type="submit" class="btn btn-primary px-5 fw-bold">
                                <i class="bi bi-check-circle ms-2"></i>حفظ التعديلات
                            </button>
                        </div>
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