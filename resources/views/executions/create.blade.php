@extends('layouts.app')

@section('title', 'تنفيذ جديد')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">الرئيسية</a>
    </li>

    <li class="breadcrumb-item">
        <a href="{{ route('executions.index') }}">التنفيذات</a>
    </li>

    <li class="breadcrumb-item active">
        إنشاء
    </li>
@endsection

@push('styles')
<style>
    .jugement-card{
        border: 1px solid #e3e6ea;
        border-radius: .5rem;
        background: #f8f9fb;
        padding: .9rem 1rem;
    }
    .jugement-card .jugement-icon{
        width: 42px;
        height: 42px;
        border-radius: .5rem;
        background: rgba(26,58,92,.08);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .info-step{
        display: flex;
        gap: .75rem;
        align-items: flex-start;
    }
    .info-step .step-num{
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: var(--primary);
        color: #fff;
        font-size: .75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-shield-check text-primary me-2"></i>تنفيذ جديد
        </h4>
        <p class="text-muted small mb-0">
            تسجيل إجراء تنفيذ جديد مرتبط بحكم قضائي نهائي.
        </p>
    </div>

    <a href="{{ route('executions.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-right ms-1"></i>
        رجوع للقائمة
    </a>
</div>

<form action="{{ route('executions.store') }}" method="POST" id="execution-form">
    @csrf

    <div class="row g-4">

        {{-- ── Formulaire ── --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                        بيانات التنفيذ
                    </h6>
                </div>

                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger d-flex align-items-start" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                            <div>
                                <div class="fw-semibold mb-1">تعذّر حفظ التنفيذ</div>
                                <ul class="mb-0 ps-3 small">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    {{-- Jugement --}}
                    <div class="mb-4">

                        <label class="form-label fw-semibold small">
                            الحكم المعني
                            <span class="text-danger">*</span>
                        </label>

                        @if($selectedJugement)

                            <div class="jugement-card d-flex align-items-center gap-3">

                                <div class="jugement-icon">
                                    <i class="bi bi-file-earmark-check text-primary fs-5"></i>
                                </div>

                                <div class="flex-grow-1">
                                    <div class="fw-semibold">
                                        حكم رقم #{{ $selectedJugement->id }}
                                    </div>
                                    <div class="text-muted small">
                                        <i class="bi bi-calendar3 ms-1"></i>
                                        {{ $selectedJugement->date_jugement->format('d/m/Y') }}
                                        <span class="mx-1">·</span>
                                        <i class="bi bi-bank ms-1"></i>
                                        {{ $selectedJugement->dossierTribunal->tribunal->nom_tribunal ?? '—' }}
                                        @if($selectedJugement->dossierTribunal->dossier ?? null)
                                            <span class="mx-1">·</span>
                                            <i class="bi bi-folder2 ms-1"></i>
                                            {{ $selectedJugement->dossierTribunal->dossier->numero_dossier_tribunal }}
                                        @endif
                                    </div>
                                </div>

                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle ms-1"></i>
                                    نهائي
                                </span>

                            </div>

                            <input type="hidden" name="id_jugement" value="{{ $selectedJugement->id }}">

                            <div class="form-text">
                                <a href="{{ route('executions.create') }}">
                                    <i class="bi bi-arrow-repeat ms-1"></i>
                                    اختيار حكم آخر
                                </a>
                            </div>

                        @else

                            <select id="jugement-select"
                                name="id_jugement"
                                class="form-select @error('id_jugement') is-invalid @enderror"
                                required>

                                <option value="">— اختر الحكم —</option>

                                @foreach($jugements as $jug)
                                    <option value="{{ $jug->id }}" @selected(old('id_jugement') == $jug->id)>
                                        #{{ $jug->id }}
                                        — {{ $jug->date_jugement->format('d/m/Y') }}
                                        — {{ $jug->dossierTribunal->tribunal->nom_tribunal ?? '' }}
                                        @if($jug->dossierTribunal->dossier ?? null)
                                            ({{ $jug->dossierTribunal->dossier->numero_dossier_tribunal }})
                                        @endif
                                        @if($jug->juge)
                                            — {{ $jug->juge->nom_complet }}
                                        @endif
                                    </option>
                                @endforeach

                            </select>

                            @error('id_jugement')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror

                            @if($jugements->isEmpty())
                                <div class="form-text text-danger">
                                    <i class="bi bi-exclamation-circle ms-1"></i>
                                    لا توجد أحكام نهائية متاحة للتنفيذ حاليًا.
                                </div>
                            @else
                                <div class="form-text">
                                    تظهر هنا الأحكام النهائية التي لم يُفتح لها تنفيذ بعد.
                                </div>
                            @endif

                        @endif

                    </div>

                    {{-- Observations --}}
                    <div class="mt-3">

                        <div class="d-flex justify-content-between">
                            <label class="form-label fw-semibold small">
                                ملاحظات
                            </label>
                            <span class="form-text" id="obs-count">0/1000</span>
                        </div>

                        <textarea name="observations"
                                  id="observations"
                                  rows="4"
                                  maxlength="1000"
                                  class="form-control @error('observations') is-invalid @enderror"
                                  placeholder="ملاحظات داخلية حول ملف التنفيذ...">{{ old('observations') }}</textarea>

                        @error('observations')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>

        </div>

        {{-- ── Dates + Informations ── --}}
        <div class="col-lg-5">

            {{-- Dates --}}
            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-calendar-range me-2 text-primary"></i>
                        التواريخ
                    </h6>
                </div>

                <div class="card-body">

                    {{-- Date notification --}}
                    <div class="mb-3">

                        <label class="form-label fw-semibold small">
                            تاريخ التبليغ
                            <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <input type="date"
                                id="date_notification"
                                name="date_notification"
                                class="form-control @error('date_notification') is-invalid @enderror"
                                value="{{ old('date_notification', date('Y-m-d')) }}"
                                required>
                        </div>

                        @error('date_notification')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- Date exécution --}}
                    <div>

                        <label class="form-label fw-semibold small">
                            تاريخ التنفيذ
                        </label>

                        <div class="input-group">
                            <input type="date"
                                id="date_execution"
                                name="date_execution"
                                class="form-control @error('date_execution') is-invalid @enderror"
                                value="{{ old('date_execution') }}">
                        </div>

                        @error('date_execution')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror

                        <div id="date-feedback" class="form-text"></div>

                    </div>

                </div>
            </div>

            <div class="d-grid gap-2">

                    <button type="submit" id="submit-btn" class="btn btn-primary px-4">
                        <span id="submit-icon"><i class="bi bi-check-lg ms-1"></i></span>
                        <span id="submit-spinner" class="spinner-border spinner-border-sm ms-1 d-none" role="status" aria-hidden="true"></span>
                        <span id="submit-text">حفظ التنفيذ</span>
                    </button>

                    <a href="{{ route('executions.index') }}"
                    class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-2"></i>
                        إلغاء
                    </a>

                </div>

        </div>

    </div>

</form>

@endsection

@push('scripts')
<script>
(function () {
    // ── Sélecteur de jugement (si aucun jugement présélectionné) ──
    var jugementSelectEl = document.getElementById('jugement-select');
    if (jugementSelectEl) {
        new TomSelect('#jugement-select', {
            placeholder: 'ابحث عن حكم برقمه أو المحكمة أو رقم الملف...',
            loadingText: 'جاري البحث...',
            render: {
                no_results: function () {
                    return '<div class="no-results">لا توجد نتائج</div>';
                }
            }
        });
    }

    // ── Validation de la date d'exécution par rapport à la notification ──
    var notifInput = document.getElementById('date_notification');
    var execInput = document.getElementById('date_execution');
    var dateFeedback = document.getElementById('date-feedback');

    function validateDates() {
        if (notifInput.value) {
            execInput.min = notifInput.value;
        }

        if (notifInput.value && execInput.value && execInput.value < notifInput.value) {
            dateFeedback.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle ms-1"></i>يجب أن يكون تاريخ التنفيذ بعد تاريخ التبليغ أو مساويًا له</span>';
        } else {
            dateFeedback.innerHTML = '';
        }
    }

    notifInput.addEventListener('change', validateDates);
    execInput.addEventListener('change', validateDates);
    validateDates();

    // ── Compteur de caractères pour les observations ──
    var obsInput = document.getElementById('observations');
    var obsCount = document.getElementById('obs-count');

    function updateObsCount() {
        obsCount.textContent = obsInput.value.length + '/1000';
    }

    obsInput.addEventListener('input', updateObsCount);
    updateObsCount();

    // ── État de chargement + confirmation à la soumission ──
    var form = document.getElementById('execution-form');
    var submitBtn = document.getElementById('submit-btn');
    var submitIcon = document.getElementById('submit-icon');
    var submitSpinner = document.getElementById('submit-spinner');
    var submitText = document.getElementById('submit-text');

    form.addEventListener('submit', function (e) {
        if (!confirm('هل تؤكد إنشاء التنفيذ؟')) {
            e.preventDefault();
            return;
        }

        submitBtn.disabled = true;
        submitIcon.classList.add('d-none');
        submitSpinner.classList.remove('d-none');
        submitText.textContent = 'جاري الحفظ...';
    });
})();
</script>
@endpush