@extends('layouts.app')

@section('title', 'إضافة حكم جديد')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">الرئيسية</a>
    </li>

    <li class="breadcrumb-item">
        <a href="{{ route('jugements.index') }}">الأحكام</a>
    </li>

    <li class="breadcrumb-item active">
        حكم جديد
    </li>
@endsection

@section('content')

    {{-- ══════════════════════════════════════════════════════════════════════════
        Page Header
    ═══════════════════════════════════════════════════════════════════════════ --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-plus-circle text-primary me-2"></i>
                حكم جديد
            </h4>
            <p class="text-muted small mb-0">
                أدخل معلومات الحكم بدقة لضمان متابعة فعّالة.
            </p>
        </div>

        <a href="{{ route('jugements.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-right me-1"></i>
            العودة إلى القائمة
        </a>
    </div>

    <form action="{{ route('jugements.store') }}" method="POST" id="jugementForm" dir="rtl">
        @csrf

        <div class="row g-4">

            {{-- Colonne principale --}}
            <div class="col-lg-8">

                {{-- ─────────────────────────────────────────────────────────────────
                    بطاقة المعلومات الرئيسية
                ───────────────────────────────────────────────────────────────── --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            المعلومات الرئيسية
                        </h6>
                    </div>

                    <div class="card-body p-4">
                        @php
                            $idPrep  = $typesJugement->firstWhere('type_jugement', \App\Models\TypeJugement::PREPARATOIRE)?->id;
                            $idFond  = $typesJugement->firstWhere('type_jugement', \App\Models\TypeJugement::AU_FOND)?->id;
                            // ?type=preparatoire (boutons de la fiche dossier / audience) présélectionne le حكم تمهيدي
                            $typeSel = old('id_type_jugement', match (request('type')) {
                                'preparatoire' => $idPrep,
                                'fond'         => $idFond,
                                default        => $idFond,
                            });
                        @endphp

                        <div class="row g-3">

                            {{-- نوع الحكم --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-dark">
                                    نوع الحكم <span class="text-danger">*</span>
                                </label>
                                {{-- Type imposé par le bouton d'origine : affiché en texte, non modifiable --}}
                                @php $typeObj = $typesJugement->firstWhere('id', $typeSel); @endphp
                                <input type="hidden" name="id_type_jugement" id="id_type_jugement" value="{{ $typeSel }}">
                                <div>
                                    <span class="badge fs-6 fw-semibold px-3 py-2 {{ (string) $typeSel === (string) $idPrep ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-primary-subtle text-primary-emphasis border border-primary-subtle' }}">
                                        {{ $typeObj?->type_jugement ?? '—' }}
                                    </span>
                                </div>
                                @error('id_type_jugement')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- الملف / المحكمة --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">
                                    الملف / المحكمة
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-folder2-open text-muted"></i>
                                    </span>
                                    <select name="id_dossier_tribunal"
                                            id="id_dossier_tribunal"
                                            class="form-select border-start-0 @error('id_dossier_tribunal') is-invalid @enderror"
                                            required>
                                        <option value="">— اختر —</option>
                                        @foreach($dossierTribunaux as $dt)
                                            <option value="{{ $dt->id }}"
                                                @selected(old('id_dossier_tribunal', $defaultDossierTribunalId ?? null) == $dt->id)>
                                                {{ $dt->dossier->numero_dossier_tribunal ?? '—' }}
                                                · {{ $dt->tribunal->nom_tribunal ?? '—' }}
                                                · {{ $dt->degre->degre_juridiction ?? '—' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_dossier_tribunal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- القاضي --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">
                                    القاضي
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-person text-muted"></i>
                                    </span>
                                    <select id="id_juge"
                                            name="id_juge"
                                            class="form-select border-start-0 @error('id_juge') is-invalid @enderror"
                                            required>
                                        <option value="">— اختر الملف أولاً —</option>
                                    </select>
                                    @error('id_juge')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- تاريخ الحكم --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">
                                    تاريخ الحكم
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-calendar-event text-muted"></i>
                                    </span>
                                    <input type="date"
                                        name="date_jugement"
                                        id="date_jugement"
                                        class="form-control border-start-0 @error('date_jugement') is-invalid @enderror"
                                        value="{{ old('date_jugement') }}"
                                        required>
                                    @error('date_jugement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div id="date_houkm_hint" class="form-text d-none">
                                    التاريخ مطابق لجلسة "الحكم" لهذه الدرجة.
                                </div>
                                <div id="date_prep_hint" class="form-text d-none">
                                    يجب أن يطابق تاريخ إحدى جلسات هذه الدرجة:
                                    <span id="date_prep_list" class="fw-semibold"></span>
                                </div>
                            </div>

                            {{-- حكم نهائي --}}
                            <div class="col-md-6 d-flex align-items-end pb-1" id="bloc-definitif">
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        name="est_definitif"
                                        value="1"
                                        id="est_definitif"
                                        @checked(old('est_definitif'))>
                                    <label class="form-check-label small fw-semibold text-primary"
                                        for="est_definitif">
                                        حكم نهائي
                                    </label>
                                </div>
                            </div>

                            {{-- منطوق الحكم --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-dark">
                                    منطوق الحكم
                                </label>
                                <textarea name="contenu_dispositif"
                                        class="form-control @error('contenu_dispositif') is-invalid @enderror"
                                        rows="3"
                                        placeholder="محتوى الحكم...">{{ old('contenu_dispositif') }}</textarea>
                                @error('contenu_dispositif')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">
                {{-- ─────────────────────────────────────────────────────────────────
                    بطاقة نتيجة الحكم - المؤسسة
                ───────────────────────────────────────────────────────────────── --}}
                <div id="bloc-fond">
                @php
                    $institution   = $partiesDossier->first(fn($dp) => $dp->partie?->est_entraide);
                    $autresParties = $partiesDossier->filter(fn($dp) => !$dp->partie?->est_entraide);
                @endphp

                @if($institution)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                        <i class="bi bi-building-fill text-primary"></i>
                        <h6 class="mb-0 fw-semibold">
                            وضعية المؤسسة :
                            <strong class="text-primary">{{ $institution->partie->nom_partie }}</strong>
                        </h6>
                    </div>

                    <div class="card-body p-4">

                        <div class="mb-3">
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($positionsInstitution as $pos)
                                @php
                                    $color = match(true) {
                                        str_contains(strtolower($pos->position), 'مع') => 'success',
                                        str_contains(strtolower($pos->position), 'ضد') => 'danger',
                                        default => 'secondary',
                                    };
                                    $icon = match($color) {
                                        'success'  => 'trophy-fill',
                                        'danger'   => 'shield-x',
                                        'warning'  => 'slash-circle',
                                        default    => 'dash-circle',
                                    };
                                @endphp
                                <div>
                                    <input type="radio"
                                        class="btn-check"
                                        name="position_institution_etab"
                                        id="pos_{{ $pos->id }}"
                                        value="{{ $pos->id }}"
                                        data-label="{{ strtolower($pos->position) }}"
                                        @checked(old('position_institution_etab') == $pos->id)
                                        required>
                                    <label class="btn btn-outline-{{ $color }} px-4"
                                        for="pos_{{ $pos->id }}">
                                        <i class="bi bi-{{ $icon }} me-1"></i>
                                        {{ $pos->position }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            @error('position_institution_etab')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- المؤسسة محكوم عليها --}}
                        <div id="bloc-etab-condamne" class="d-none">

                            <div id="condamnation-card" class="condamnation-card danger">

                                <div class="condamnation-header">

                                    <div class="d-flex align-items-center">

                                        <div id="condamnation-icon" class="condamnation-icon">

                                            <i class="bi bi-building-fill"></i>

                                        </div>

                                        <div>

                                            <h6 class="mb-1 fw-bold">
                                                {{ $institution->partie->nom_partie }}
                                            </h6>

                                            <span id="condamnation-badge" class="condamnation-badge">

                                                <i class="bi bi-exclamation-circle-fill me-1"></i>

                                                المؤسسة محكوم عليها

                                            </span>

                                        </div>

                                    </div>

                                </div>

                                <input type="hidden"
                                    name="parties[]"
                                    id="hidden_etab_partie"
                                    value="{{ $institution->partie->id }}">

                                <div class="mt-4">

                                    <label class="form-label fw-semibold text-muted small mb-2">

                                        المبلغ المحكوم به

                                    </label>

                                    <div class="input-group amount-group">

                                        <span class="input-group-text">

                                            <i class="bi bi-cash-stack me-2"></i>

                                            درهم

                                        </span>

                                        <input type="number"
                                            step="0.01"
                                            min="0"
                                            name="montants[{{ $institution->partie->id }}]"
                                            id="montant_etab"
                                            class="form-control"
                                            value="{{ old('montants.'.$institution->partie->id) }}"
                                            placeholder="أدخل المبلغ المحكوم به">

                                    </div>

                                </div>

                            </div>

                        </div>

                        {{--  بطاقة الأطراف الأخرى --}}
                        <div id="bloc-parties-adverses" class="d-none">
                            <div class="condamnation-card success">

                                <div class="condamnation-header">

                                    <div class="d-flex align-items-center">

                                        <div class="condamnation-icon">

                                            <i class="bi bi-people-fill"></i>

                                        </div>

                                        <div>

                                            <h6 class="mb-1 fw-bold">

                                                الأطراف الأخرى المحكوم عليها

                                            </h6>

                                            <span class="condamnation-badge">

                                                <i class="bi bi-check-circle-fill me-1"></i>

                                                المؤسسة رابحة في هذا الحكم

                                            </span>

                                        </div>

                                    </div>

                                </div>

                                @if($autresParties->isEmpty())

                                    <div class="text-center py-4 text-muted">

                                        <i class="bi bi-people fs-1 opacity-25 d-block mb-2"></i>

                                        لا توجد أطراف أخرى في هذا الملف

                                    </div>

                                @else

                                    <div class="row g-3 mt-2">

                                        @foreach($autresParties as $dp)

                                        <div class="col-12-md">

                                            <div class="party-item-card">

                                                <div class="d-flex justify-content-between align-items-center mb-3">

                                                    <div class="form-check">

                                                        <input class="form-check-input"
                                                            type="checkbox"
                                                            name="parties[]"
                                                            value="{{ $dp->partie->id }}"
                                                            id="partie_{{ $dp->partie->id }}"
                                                            @checked(old('parties') && in_array($dp->partie->id, old('parties', [])))>

                                                        <label class="form-check-label fw-semibold"
                                                            for="partie_{{ $dp->partie->id }}">

                                                            {{ $dp->partie->nom_partie }}

                                                        </label>

                                                    </div>

                                                    <span class="party-type-badge">

                                                        {{ $dp->typePartie->type_partie ?? '—' }}

                                                    </span>

                                                </div>

                                                <div class="input-group amount-group">

                                                    <span class="input-group-text">

                                                        <i class="bi bi-cash-stack me-2"></i>

                                                        درهم

                                                    </span>

                                                    <input type="number"
                                                        step="0.01"
                                                        min="0"
                                                        name="montants[{{ $dp->partie->id }}]"
                                                        class="form-control"
                                                        value="{{ old('montants.'.$dp->partie->id) }}"
                                                        placeholder="المبلغ المحكوم به">

                                                </div>

                                            </div>

                                        </div>

                                        @endforeach

                                    </div>

                                @endif

                            </div>
                        </div>

                    </div>
                </div>
                @else
                <div class="alert alert-warning small mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    لا يوجد طرف محدد كمؤسسة في هذا الملف.
                </div>
                @endif
                </div>{{-- /#bloc-fond --}}

                {{-- ─────────────────────────────────────────────────────────────────
                    ملخص الملف
                ───────────────────────────────────────────────────────────────── --}}
                <div id="bloc-resume-prep" class="card border-0 shadow-sm mb-4 d-none">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                        <i class="bi bi-journal-text text-warning"></i>
                        <h6 class="mb-0 fw-semibold">ملخص الملف</h6>
                    </div>
                    <div class="card-body p-4">

                        <div id="resume-vide" class="text-muted small">
                            اختر الملف لعرض ملخصه.
                        </div>

                        <dl id="resume-contenu" class="row mb-0 small d-none">
                            <dt class="col-5 text-muted fw-normal">رقم الملف</dt>
                            <dd class="col-7 fw-semibold" id="resume-numero"></dd>

                            <dt class="col-5 text-muted fw-normal">المحكمة</dt>
                            <dd class="col-7" id="resume-tribunal"></dd>

                            <dt class="col-5 text-muted fw-normal">الدرجة</dt>
                            <dd class="col-7" id="resume-degre"></dd>

                            <dt class="col-5 text-muted fw-normal">تاريخ فتح الدرجة</dt>
                            <dd class="col-7" id="resume-debut"></dd>

                            <dt class="col-5 text-muted fw-normal">عدد الجلسات</dt>
                            <dd class="col-7" id="resume-nbaud"></dd>

                            <dt class="col-5 text-muted fw-normal">آخر جلسة</dt>
                            <dd class="col-7" id="resume-deraud"></dd>

                            <dt class="col-5 text-muted fw-normal">الجلسة القادمة</dt>
                            <dd class="col-7" id="resume-prochaine"></dd>

                            <dt class="col-5 text-muted fw-normal">أحكام تمهيدية سابقة</dt>
                            <dd class="col-7" id="resume-prep"></dd>
                        </dl>

                    </div>
                </div>

                {{-- ─────────────────────────────────────────────────────────────────
                    أزرار الإرسال والإلغاء
                ───────────────────────────────────────────────────────────────── --}}
                <div class="d-grid gap-2">

                    <button type="submit"
                            class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>
                        <span id="submit-label">إنشاء الحكم</span>
                    </button>

                    <a href="{{ route('jugements.index') }}"
                    class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-2"></i>
                        إلغاء
                    </a>

                </div>

            </div>
        </div>

    </form>

@endsection

@push('styles')
<style>
    .form-control,
    .form-select {
        text-align: right;
    }

    .input-group > .form-control,
    .input-group > .form-select {
        border-radius: 0.375rem 0 0 0.375rem !important;
    }

    .input-group > .input-group-text:first-child {
        border-radius: 0 0.375rem 0.375rem 0 !important;
    }

    .card {
        border-radius: 0.5rem;
        transition: box-shadow 0.2s ease-in-out;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }

    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }

    .bi {
        vertical-align: -0.125em;
    }

    .badge {
        font-weight: 500;
    }
    /* ==========================
    CARTE CONDAMNATION
    ========================== */

    .condamnation-card{

        background:#fff;

        border:1px solid #f1d3d6;

        border-right:5px solid #dc3545;

        border-radius:16px;

        padding:20px;

        transition:all .3s ease;

        box-shadow:0 4px 15px rgba(0,0,0,.04);

    }

    .condamnation-header{

        display:flex;

        align-items:center;

        justify-content:space-between;

    }

    .condamnation-icon{

        width:48px;

        height:48px;

        border-radius:12px;

        display:flex;

        align-items:center;

        justify-content:center;

        font-size:1.2rem;

        margin-left:12px;

        transition:.3s;

    }

    .condamnation-badge{

        display:inline-flex;

        align-items:center;

        padding:6px 12px;

        border-radius:30px;

        font-size:.75rem;

        font-weight:600;

        transition:.3s;

    }

    .amount-group{

        overflow:hidden;

        border-radius:12px;

    }

    .amount-group .input-group-text{

        border:none;

        font-weight:600;

        transition:.3s;

    }

    .amount-group .form-control{

        min-height:48px;

    }

    /* ==========================
    DANGER
    ========================== */

    .condamnation-card.danger{

        border-color:#f1d3d6;

        border-right-color:#dc3545;

        background:#fff;

    }

    .condamnation-card.danger .condamnation-icon{

        background:rgba(220,53,69,.1);

        color:#dc3545;

    }

    .condamnation-card.danger .condamnation-badge{

        background:rgba(220,53,69,.1);

        color:#dc3545;

    }

    .condamnation-card.danger .input-group-text{

        background:#dc3545;

        color:#fff;

    }

    /* ==========================
    WARNING
    ========================== */

    .condamnation-card.warning{

        border-color:#ffe69c;

        border-right-color:#ffc107;

    }

    .condamnation-card.warning .condamnation-icon{

        background:rgba(255,193,7,.15);

        color:#b58100;

    }

    .condamnation-card.warning .condamnation-badge{

        background:rgba(255,193,7,.15);

        color:#b58100;

    }

    .condamnation-card.warning .input-group-text{

        background:#ffc107;

        color:#212529;

    }


    /* ==========================
    SUCCESS
    ========================== */

    .condamnation-card.success{

        border-color:#d1e7dd;

        border-right-color:#198754;

        background:#ffffff;

    }

    .condamnation-card.success .condamnation-icon{

        background:rgba(25,135,84,.12);

        color:#198754;

    }

    .condamnation-card.success .condamnation-badge{

        background:rgba(25,135,84,.12);

        color:#198754;

    }

    .condamnation-card.success .input-group-text{

        background:#198754;

        color:#fff;

    }

    /* ==========================
    PARTY ITEM
    ========================== */

    .party-item-card{

        border:1px solid #e9ecef;

        border-radius:14px;

        padding:16px;

        background:#fff;

        transition:.25s;

    }

    .party-item-card:hover{

        border-color:#198754;

        box-shadow:0 5px 15px rgba(25,135,84,.08);

    }

    .party-type-badge{

        background:rgba(25,135,84,.1);

        color:#198754;

        padding:5px 10px;

        border-radius:20px;

        font-size:.75rem;

        font-weight:600;

    }

    .party-item-card .form-check-input:checked{

        background:#198754;

        border-color:#198754;

    }

</style>
@endpush

@push('scripts')
<script>
    // ══════════════════════════════════════════════════════════════════
    // MAP : dossierTribunal.id → { date الحكم, id_tribunal }
    // ══════════════════════════════════════════════════════════════════
    const dtMap = {
        @foreach($dossierTribunaux as $dt)
            @php
                $ah      = $dt->audienceHoukmPourJugementAuFond();
                $derAud  = $dt->audiences->sortByDesc('date_audience')->first();
                $prepDt  = $dt->jugements->where('id_type_jugement', $idPrep);
                $derPrep = $prepDt->sortByDesc('date_jugement')->first();
            @endphp
            {{ $dt->id }}: {
                date      : "{{ $ah?->date_audience?->format('Y-m-d') ?? '' }}",
                dates     : @json($dt->audiences->map(fn($a) => $a->date_audience->format('Y-m-d'))->unique()->sort()->values()),
                tribunalId: {{ $dt->id_tribunal }},
                // ملخص الملف (حكم تمهيدي)
                numero    : @json($dt->dossier->numero_dossier_tribunal ?? '—'),
                tribunal  : @json($dt->tribunal->nom_tribunal ?? '—'),
                degre     : @json($dt->degre->degre_juridiction ?? '—'),
                debut     : @json($dt->date_debut?->format('d/m/Y') ?? '—'),
                nbAud     : {{ $dt->audiences->count() }},
                derAud    : @json($derAud ? $derAud->date_audience->format('d/m/Y') . ' - جلسة ' . ($derAud->typeAudience->type_audience ?? '—') : '—'),
                prochaine : @json($derAud?->date_prochaine_audience?->format('d/m/Y') ?? '—'),
                nbPrep    : {{ $prepDt->count() }},
                derPrep   : @json($derPrep?->date_jugement?->format('d/m/Y') ?? '—')
            },
        @endforeach
    };

    // old() لاسترجاع القاضي بعد خطأ التحقق
    const oldJugeId = "{{ old('id_juge') }}";

    const selectDt    = document.getElementById('id_dossier_tribunal');
    const dateInput   = document.getElementById('date_jugement');
    const dateHint    = document.getElementById('date_houkm_hint');
    const datePrepHint = document.getElementById('date_prep_hint');
    const datePrepList = document.getElementById('date_prep_list');

    // نوع الحكم : تمهيدي / فاصل في الموضوع
    const ID_PREPARATOIRE = "{{ $idPrep }}";
    const typeInput       = document.getElementById('id_type_jugement');
    const blocFond        = document.getElementById('bloc-fond');
    const blocDefinitif   = document.getElementById('bloc-definitif');
    const hintPrep        = document.getElementById('hint-preparatoire');
    const submitLabel     = document.getElementById('submit-label');
    const blocResume      = document.getElementById('bloc-resume-prep');

    // ملخص الملف (حكم تمهيدي) : يُحدَّث عند تغيير الملف
    function renderResume(info) {
        const vide    = document.getElementById('resume-vide');
        const contenu = document.getElementById('resume-contenu');
        if (!vide || !contenu) return;

        vide.classList.toggle('d-none', !!info);
        contenu.classList.toggle('d-none', !info);
        if (!info) return;

        const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
        set('resume-numero',   info.numero);
        set('resume-tribunal', info.tribunal);
        set('resume-degre',    info.degre);
        set('resume-debut',    info.debut);
        set('resume-nbaud',    info.nbAud);
        set('resume-deraud',   info.derAud);
        set('resume-prochaine',info.prochaine);
        set('resume-prep',     info.nbPrep > 0 ? `${info.nbPrep} (آخرها: ${info.derPrep})` : 'لا يوجد');
    }

    function estPreparatoire() {
        return typeInput?.value === ID_PREPARATOIRE;
    }

    // ══════════════════════════════════════════════════════════════════
    // القاضي — حقل بحث TomSelect (يُعبَّأ ديناميكياً حسب المحكمة)
    // ══════════════════════════════════════════════════════════════════
    const tsJuge = new TomSelect("#id_juge", {
        create: function(input) {
            window.location.href = "{{ route('juges.create') }}?nom=" + encodeURIComponent(input);
            return false;
        },
        sortField: { field: "text", direction: "asc" },
        placeholder: "— اختر الملف أولاً —",
        render: {
            no_results: function(data, escape) {
                return `<div class="no-results">لا توجد نتائج</div>`;
            },
            option_create: function(data, escape) {
                return `<div class="create">➕ إضافة "${escape(data.input)}"</div>`;
            }
        }
    });
    tsJuge.disable();

    // ══════════════════════════════════════════════════════════════════
    // تغيير الملف → تحديث التاريخ + القضاة
    // ══════════════════════════════════════════════════════════════════
    async function onDtChange() {

        const dtId = selectDt?.value;
        const info = dtMap[dtId];
        renderResume(info);

        // تاريخ الحكم
        // - حكم فاصل : يُفرض تاريخ جلسة "الحكم" (للقراءة فقط)
        // - حكم تمهيدي : تاريخ حر يطابق إحدى جلسات الدرجة (يُتحقق منه في الخادم)
        const prep = estPreparatoire();
        const date = prep ? '' : (info?.date ?? '');

        if (dateInput) {
            if (prep) {
                if (dateInput.readOnly) dateInput.value = '';
                dateInput.readOnly = false;
            } else {
                dateInput.value    = date;
                dateInput.readOnly = !!date;
            }
        }

        if (dateHint) {
            dateHint.classList.toggle('d-none', prep || !date);
        }

        if (datePrepHint) {
            const dates = info?.dates ?? [];
            datePrepList.textContent = dates.map(d => d.split('-').reverse().join('/')).join(' ، ');
            datePrepHint.classList.toggle('d-none', !(prep && dates.length));
        }

        // القضاة حسب المحكمة
        if (!tsJuge) return;

        tsJuge.clear(true);
        tsJuge.clearOptions();

        if (!dtId || !info) {

            tsJuge.control_input.placeholder = '— اختر الملف أولاً —';
            tsJuge.disable();

            return;
        }

        tsJuge.control_input.placeholder = '— جاري التحميل... —';
        tsJuge.disable();

        try {

            const res   =
                await fetch(`/api/tribunaux/${info.tribunalId}/juges`);

            const juges = await res.json();

            if (juges.length === 0) {

                tsJuge.control_input.placeholder = '— لا يوجد قضاة —';

            } else {

                juges.forEach(j => {

                    tsJuge.addOption({
                        value: j.id,
                        text: (j.grade ? j.grade + ' ' : '') + j.nom_complet
                    });

                });

                tsJuge.control_input.placeholder = '— اختر القاضي —';
                tsJuge.refreshOptions(false);
                tsJuge.enable();

                if (oldJugeId && juges.some(j => String(j.id) === String(oldJugeId))) {
                    tsJuge.setValue(oldJugeId, true);
                }
            }

        } catch (e) {

            tsJuge.control_input.placeholder = '— خطأ في التحميل —';
            tsJuge.disable();

        }
    }

    selectDt?.addEventListener('change', onDtChange);

    function onTypeChange() {
        const prep = estPreparatoire();

        blocFond?.classList.toggle('d-none', prep);
        blocDefinitif?.classList.toggle('d-none', prep);
        hintPrep?.classList.toggle('d-none', !prep);
        blocResume?.classList.toggle('d-none', !prep);

        // Les champs "fond" ne sont pas envoyés pour un حكم تمهيدي
        blocFond?.querySelectorAll('input, select, textarea').forEach(el => el.disabled = prep);
        if (prep) {
            const def = document.getElementById('est_definitif');
            if (def) def.checked = false;
        }

        if (submitLabel) {
            submitLabel.textContent = prep ? 'تسجيل الحكم التمهيدي' : 'إنشاء الحكم';
        }

        if (selectDt?.value) onDtChange();
    }

    // Le type est fixe (champ caché) : onTypeChange() est appelé une seule fois à l'initialisation (voir plus bas)

    // ══════════════════════════════════════════════════════════════════
    // وضعية المؤسسة → إظهار/إخفاء البلوكات
    // ══════════════════════════════════════════════════════════════════
    const radios         = document.querySelectorAll('input[name="position_institution_etab"]');
    const blocEtab       = document.getElementById('bloc-etab-condamne');
    const blocPartiesAdv = document.getElementById('bloc-parties-adverses');
    const hiddenEtab     = document.getElementById('hidden_etab_partie');
    const montantEtab    = document.getElementById('montant_etab');

    function onPositionChange(radio) {

    const label = radio.dataset.label ?? '';

    const etabCondamne =
        label.includes('ضد');

    const adverseCondamne =
        label.includes('مع');

    // =========================
    // Institution
    // =========================

    if (blocEtab) {
        blocEtab.classList.toggle('d-none', !etabCondamne);
    }

    // NOTE: hiddenEtab ne doit JAMAIS être désactivé — l'institution doit
    // toujours avoir une ligne dans jugement_parties, quelle que soit sa
    // position (مع / ضد). Seul le montant dépend de la position.

    if (montantEtab) {

        // Le montant n'est jamais obligatoire : qu'il s'agisse d'une
        // position "مع" (pour) ou "ضد" (contre), l'utilisateur peut
        // laisser le champ vide (il sera enregistré à 0 par défaut).
        if (!etabCondamne) {
            montantEtab.value = '';
        }
    }

    // =========================
    // Parties adverses
    // =========================

    if (blocPartiesAdv) {

        blocPartiesAdv.classList.toggle(
            'd-none',
            !adverseCondamne
        );
    }

    if (!adverseCondamne) {

        document.querySelectorAll(
            '#bloc-parties-adverses input[type="checkbox"]'
        ).forEach(cb => {

            cb.checked = false;

        });

        document.querySelectorAll(
            '#bloc-parties-adverses input[type="number"]'
        ).forEach(input => {

            input.value = '';

        });
    }

    }


    radios.forEach(r => {
        r.addEventListener('change', () => onPositionChange(r));
    });

    // ══════════════════════════════════════════════════════════════════
    // INIT
    // ══════════════════════════════════════════════════════════════════
    window.addEventListener('DOMContentLoaded', () => {

        onTypeChange();   // applique le type puis, si un dossier est choisi, onDtChange()

        const checkedRadio =
            document.querySelector('input[name="position_institution_etab"]:checked');

        if (checkedRadio)
            onPositionChange(checkedRadio);

    });
</script>
<script>

    document.addEventListener('DOMContentLoaded', function () {

        const radios = document.querySelectorAll(
            'input[name="position_institution_etab"]'
        );

        const bloc = document.getElementById('bloc-etab-condamne');

        const card = document.getElementById('condamnation-card');

        const hiddenPartie = document.getElementById('hidden_etab_partie');

        function updateCondamnationBlock() {

            const selected = document.querySelector(
                'input[name="position_institution_etab"]:checked'
            );

            if (!selected) {

                bloc.classList.add('d-none');
                return;
            }

            const label = selected.dataset.label.trim();

            card.classList.remove('danger', 'warning');

            if (label.includes('ضد')) {

                bloc.classList.remove('d-none');

                card.classList.add('danger');

            }

            else {

                bloc.classList.add('d-none');
            }
        }

        radios.forEach(radio => {

            radio.addEventListener('change', updateCondamnationBlock);

        });

        updateCondamnationBlock();

    });

</script>
@endpush