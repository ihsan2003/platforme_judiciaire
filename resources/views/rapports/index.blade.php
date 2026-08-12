@extends('layouts.app')

@section('title', 'التقرير الإحصائي')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">التقرير الإحصائي</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>توليد التقرير الإحصائي
        </h4>
        <p class="text-muted small mb-0">اختر الفترة الزمنية لتوليد تقرير إحصائي شامل بصيغة Word.</p>
    </div>
</div>

<div class="row g-4">
    {{-- ── Formulaire ── --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-calendar-range me-2 text-primary"></i>الفترة الزمنية
                </h6>
            </div>
            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger d-flex align-items-start" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                        <div>
                            <div class="fw-semibold mb-1">تعذّر توليد التقرير</div>
                            <ul class="mb-0 ps-3 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form id="rapport-form" method="POST" action="{{ route('rapports.export') }}">
                    @csrf

                    {{-- Périodes rapides --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">اختصارات سريعة</label>
                        <div class="d-flex flex-wrap gap-2" id="quick-ranges">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-range="today">اليوم</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-range="this_week">هذا الأسبوع</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-range="this_month">هذا الشهر</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-range="last_month">الشهر الماضي</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-range="this_year">هذه السنة</button>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">
                                من تاريخ <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-calendar-event text-muted"></i></span>
                                <input type="date"
                                       id="date_debut"
                                       name="date_debut"
                                       class="form-control @error('date_debut') is-invalid @enderror"
                                       required
                                       value="{{ old('date_debut') }}">
                                @error('date_debut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">
                                إلى تاريخ <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-calendar-check text-muted"></i></span>
                                <input type="date"
                                       id="date_fin"
                                       name="date_fin"
                                       class="form-control @error('date_fin') is-invalid @enderror"
                                       required
                                       value="{{ old('date_fin') }}">
                                @error('date_fin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div id="range-feedback" class="form-text mt-2"></div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <button type="submit" id="submit-btn" class="btn btn-primary px-4">
                            <span id="submit-icon"><i class="bi bi-file-earmark-word me-1"></i></span>
                            <span id="submit-spinner" class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>
                            <span id="submit-text">تحميل التقرير (Word)</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- ── Informations ── --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-primary"></i>محتوى التقرير
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="d-flex align-items-start mb-3">
                        <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                        <span>إحصائيات عامة حول الملفات والأحكام خلال الفترة المحددة.</span>
                    </li>
                    <li class="d-flex align-items-start mb-3">
                        <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                        <span>توزيع الملفات حسب طبيعة النزاع.</span>
                    </li>
                    <li class="d-flex align-items-start mb-3">
                        <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                        <span>توزيع الملفات حسب الجهة القضائية.</span>
                    </li>
                    <li class="d-flex align-items-start">
                        <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                        <span>يُنتج الملف بصيغة Word (docx) جاهز للطباعة أو التعديل.</span>
                    </li>
                </ul>
                <hr>
                <div class="text-muted small d-flex align-items-start">
                    <i class="bi bi-clock-history me-2 mt-1"></i>
                    <span>قد يستغرق توليد التقرير بضع ثوانٍ حسب حجم البيانات في الفترة المختارة.</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('rapport-form');
    const debutInput = document.getElementById('date_debut');
    const finInput = document.getElementById('date_fin');
    const feedback = document.getElementById('range-feedback');
    const submitBtn = document.getElementById('submit-btn');
    const submitIcon = document.getElementById('submit-icon');
    const submitSpinner = document.getElementById('submit-spinner');
    const submitText = document.getElementById('submit-text');

    function fmt(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    // ── Périodes rapides ──
    document.querySelectorAll('#quick-ranges [data-range]').forEach(btn => {
        btn.addEventListener('click', () => {
            const today = new Date();
            let debut, fin;

            switch (btn.dataset.range) {
                case 'today':
                    debut = fin = today;
                    break;
                case 'this_week': {
                    const day = (today.getDay() + 6) % 7; // lundi = 0
                    debut = new Date(today);
                    debut.setDate(today.getDate() - day);
                    fin = today;
                    break;
                }
                case 'this_month':
                    debut = new Date(today.getFullYear(), today.getMonth(), 1);
                    fin = today;
                    break;
                case 'last_month':
                    debut = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    fin = new Date(today.getFullYear(), today.getMonth(), 0);
                    break;
                case 'this_year':
                    debut = new Date(today.getFullYear(), 0, 1);
                    fin = today;
                    break;
            }

            debutInput.value = fmt(debut);
            finInput.value = fmt(fin);

            document.querySelectorAll('#quick-ranges [data-range]').forEach(b => b.classList.remove('active', 'btn-primary'));
            document.querySelectorAll('#quick-ranges [data-range]').forEach(b => b.classList.add('btn-outline-secondary'));
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('active', 'btn-primary');

            validateRange();
        });
    });

    // ── Validation dynamique de la plage ──
    function validateRange() {
        finInput.min = debutInput.value || '';

        if (debutInput.value && finInput.value) {
            const nbJours = Math.round((new Date(finInput.value) - new Date(debutInput.value)) / 86400000) + 1;

            if (finInput.value < debutInput.value) {
                feedback.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>تاريخ النهاية يجب أن يكون بعد تاريخ البداية</span>';
                submitBtn.disabled = true;
                return;
            }

            feedback.innerHTML = `<i class="bi bi-info-circle me-1"></i>المدة المختارة: ${nbJours} يوم`;
            submitBtn.disabled = false;
        } else {
            feedback.innerHTML = '';
            submitBtn.disabled = false;
        }
    }

    debutInput.addEventListener('change', validateRange);
    finInput.addEventListener('change', validateRange);
    validateRange();

    // ── État de chargement lors de la soumission ──
    form.addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitIcon.classList.add('d-none');
        submitSpinner.classList.remove('d-none');
        submitText.textContent = 'جاري توليد التقرير...';

        // Réactiver le bouton après un délai, au cas où le téléchargement échoue silencieusement
        setTimeout(() => {
            submitBtn.disabled = false;
            submitIcon.classList.remove('d-none');
            submitSpinner.classList.add('d-none');
            submitText.textContent = 'تحميل التقرير (Word)';
        }, 15000);
    });
})();
</script>
@endpush