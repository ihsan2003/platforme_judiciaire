{{-- resources/views/jugements/show-ar.blade.php --}}
@extends('layouts.app')

@section('title', 'حكم بتاريخ ' . $jugement->date_jugement->format('d/m/Y'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">الرئيسية</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('jugements.index') }}">الأحكام</a>
    </li>
    <li class="breadcrumb-item active">
        الحكم #{{ $jugement->id }}
    </li>
@endsection

@section('content')

{{-- ══ الرأس ══ --}}
<div class="card border-0 shadow-sm mb-4" dir="rtl">
    <div class="card-body">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">

            <div>
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-hammer ms-2 text-primary"></i>
                    حكم بتاريخ {{ $jugement->date_jugement->format('d/m/Y') }}
                </h5>

                <div class="text-muted small">
                    <i class="bi bi-bank ms-1"></i>

                    {{ $jugement->dossierTribunal->tribunal->nom_tribunal ?? '—' }}

                    &nbsp;·&nbsp;

                    <a href="{{ route('dossiers.show', $jugement->dossierTribunal->dossier) }}"
                       class="text-decoration-none">
                        {{ $jugement->dossierTribunal->dossier->numero_dossier_interne ?? '—' }}
                    </a>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">

                {{-- حالة الطعن --}}
                @if($jugement->est_definitif)
                    <span class="badge bg-success bg-opacity-15 text-white border border-success border-opacity-25 fs-6 px-3 py-2">
                        <i class="bi bi-check-circle ms-1"></i>
                        حكم نهائي
                    </span>
                @else
                    @php $delai = $jugement->delai_recours_restant; @endphp

                    <span class="badge bg-warning bg-opacity-15 text-black border border-warning border-opacity-25 fs-6 px-3 py-2">
                        <i class="bi bi-clock ms-1"></i>
                        {{ $jugement->statut_recours_label }}
                    </span>
                @endif

                {{-- الإجراءات --}}
                <a href="{{ route('jugements.edit', $jugement) }}"
                   class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-pencil ms-1"></i>
                    تعديل
                </a>

            </div>
        </div>

        <hr class="my-3">

        <div class="row g-2 small text-muted">

            <div class="col-sm-3">
                <strong>القاضي :</strong>
                {{ $jugement->juge->nom_complet ?? '—' }}
            </div>

            <div class="col-sm-3">
                <strong>الملف :</strong>
                {{ $jugement->dossierTribunal->dossier->numero_dossier_interne ?? '—' }}
            </div>

            <div class="col-sm-3">
                <strong>حالة الملف :</strong>
                {{ $jugement->dossierTribunal->dossier->statut->statut_dossier ?? '—' }}
            </div>

            <div class="col-sm-3">
                <strong>تم الإنشاء بواسطة :</strong>
                {{ $jugement->createdBy->name ?? '—' }}
            </div>

        </div>
    </div>
</div>

<div class="row g-4" dir="rtl">

    {{-- ── العمود الرئيسي ── --}}
    <div class="col-lg-8">

        {{-- منطوق الحكم --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-file-text ms-2 text-primary"></i>
                    منطوق الحكم
                </h6>
            </div>

            <div class="card-body">
                <div class="p-3 bg-light rounded border"
                     style="white-space:pre-wrap; font-family: inherit; line-height:1.8">

                    {{ $jugement->contenu_dispositif ?? '—' }}

                </div>
            </div>
        </div>

        {{-- الأطراف --}}
        @if($jugement->parties->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-people ms-2 text-primary"></i>
                    الأطراف
                </h6>
            </div>

            <div class="card-body p-0">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th class="pe-3 small text-muted">الاسم</th>
                            <th class="small text-muted">المعرف</th>
                            <th class="small text-muted">المبلغ المحكوم به</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($jugement->parties as $partie)
                        <tr>

                            <td class="pe-3 fw-semibold">
                                {{ $partie->nom_partie }}
                            </td>

                            <td class="text-muted small font-monospace">
                                {{ $partie->identifiant_unique }}
                            </td>

                            <td>
                                @if($partie->pivot->montant_condamne)
                                    <strong>
                                        {{ number_format($partie->pivot->montant_condamne, 2) }} درهم
                                    </strong>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                        </tr>
                        @endforeach

                    </tbody>

                </table>

            </div>
        </div>
        @endif

        {{-- الطعون --}}
        @if($jugement->recours->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-arrow-repeat ms-2 text-warning"></i>
                    الطعون المودعة
                </h6>
            </div>

            <div class="card-body p-0">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th class="pe-3 small text-muted">النوع</th>
                            <th class="small text-muted">التاريخ</th>
                            <th class="small text-muted">داخل الأجل</th>
                            <th class="small text-muted">الأسباب</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($jugement->recours as $recours)
                        <tr>

                            <td class="pe-3 fw-semibold">
                                <span class="badge bg-warning bg-opacity-15 text-black border border-warning border-opacity-25">
                                    {{ $recours->typeRecours->type_recours ?? '—' }}
                                </span>
                            </td>

                            <td class="text-muted small">
                                {{ $recours->date_recours->format('d/m/Y') }}
                            </td>

                            <td>
                                @if($recours->est_dans_delais)
                                    <span class="badge bg-success bg-opacity-15 text-white">
                                        ✓ داخل الأجل
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-15 text-white">
                                        ✗ خارج الأجل
                                    </span>
                                @endif
                            </td>

                            <td class="text-muted small">
                                {{ Str::limit($recours->motifs ?? '—', 60) }}
                            </td>

                        </tr>
                        @endforeach

                    </tbody>

                </table>

            </div>
        </div>
        @endif

    </div>

    {{-- ── العمود الجانبي ── --}}
    <div class="col-lg-4">

        {{-- المالية --}}
        @if($jugement->finance)
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-cash-stack ms-2 text-success"></i>
                    الوضعية المالية
                </h6>
            </div>

            <div class="card-body small">

                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">المبلغ المحكوم به</span>
                    <strong>
                        {{ number_format($jugement->finance->montant_condamne, 2) }} درهم
                    </strong>
                </div>

                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">المبلغ المؤدى</span>
                    <strong class="text-success">
                        {{ number_format($jugement->finance->montant_paye, 2) }} درهم
                    </strong>
                </div>

                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">المبلغ المتبقي</span>
                    <strong class="text-danger">
                        {{ number_format($jugement->finance->montant_restant, 2) }} درهم
                    </strong>
                </div>

            </div>
        </div>
        @endif

        {{-- إجراءات الطعن --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-arrow-repeat ms-2 text-warning"></i>
                    إجراءات الطعن
                </h6>
            </div>

            <div class="card-body">

                @if($jugement->est_definitif)

                    <div class="alert alert-success py-2 small mb-0">
                        <i class="bi bi-check-circle ms-1"></i>
                        هذا الحكم <strong>نهائي</strong> ولا يقبل أي طعن.
                    </div>

                @elseif($jugement->recours->isNotEmpty())

                    <div class="alert alert-warning py-2 small mb-0">
                        <i class="bi bi-info-circle ms-1"></i>
                        تم بالفعل إيداع طعن بخصوص هذا الحكم.
                    </div>

                @elseif(!$jugement->peutFaireObjetRecours())

                    <div class="alert alert-secondary py-2 small mb-2">
                        <i class="bi bi-clock ms-1"></i>
                        انتهى الأجل القانوني للطعن.
                    </div>

                    <form action="{{ route('jugements.cloture-sans-recours', $jugement) }}"
                          method="POST"
                          onsubmit="return confirm('تأكيد جعل الحكم نهائياً وإغلاق الملف؟')">

                        @csrf

                        <button class="btn btn-secondary btn-sm w-100">
                            <i class="bi bi-lock ms-1"></i>
                            إغلاق بدون طعن
                        </button>

                    </form>

                @else

                    @php $delaiRestant = $jugement->delai_recours_restant; @endphp

                    @if($delaiRestant !== null && $delaiRestant <= 5)

                        <div class="alert alert-danger py-2 small mb-3">
                            <i class="bi bi-exclamation-triangle ms-1"></i>

                            <strong>تنبيه :</strong>
                            تبقى <strong>{{ $delaiRestant }} يوم</strong>
                            لإيداع الطعن.
                        </div>

                    @else

                        <div class="alert alert-info py-2 small mb-3">
                            <i class="bi bi-clock ms-1"></i>
                            الأجل المتبقي :
                            <strong>{{ $delaiRestant ?? '—' }} يوم</strong>
                        </div>

                    @endif

                    <form action="{{ route('jugements.recours.store', $jugement) }}"
                          method="POST">

                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">
                                نوع الطعن <span class="text-danger">*</span>
                            </label>

                            <select name="id_type_recours"
                                    class="form-select form-select-sm @error('id_type_recours') is-invalid @enderror"
                                    required>

                                <option value="">— اختر —</option>

                                @foreach(\App\Models\TypeRecours::orderBy('type_recours')->get() as $tr)
                                    <option value="{{ $tr->id }}">
                                        {{ $tr->type_recours }}
                                        ({{ $tr->delai_legal_jours }} يوم)
                                    </option>
                                @endforeach

                            </select>

                            @error('id_type_recours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">
                                تاريخ الطعن <span class="text-danger">*</span>
                            </label>

                            <input type="date"
                                   name="date_recours"
                                   class="form-control form-control-sm @error('date_recours') is-invalid @enderror"
                                   value="{{ date('Y-m-d') }}"
                                   min="{{ $jugement->date_jugement->format('Y-m-d') }}"
                                   required>

                            @error('date_recours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">
                                أسباب الطعن
                            </label>

                            <textarea name="motifs"
                                      class="form-control form-control-sm"
                                      rows="3"
                                      placeholder="أسباب الطعن..."></textarea>
                        </div>

                        <button type="submit"
                                class="btn btn-warning btn-sm w-100"
                                onclick="return confirm('تأكيد إيداع الطعن؟ سيتم تحديث حالة الملف.')">

                            <i class="bi bi-arrow-repeat ms-1"></i>
                            إيداع الطعن
                        </button>

                    </form>

                    <hr class="my-3">

                    <form action="{{ route('jugements.cloture-sans-recours', $jugement) }}"
                          method="POST"
                          onsubmit="return confirm('تأكيد إغلاق الحكم نهائياً بدون طعن؟')">

                        @csrf

                        <button class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-x-circle ms-1"></i>
                            إغلاق بدون طعن
                        </button>

                    </form>

                @endif

            </div>
        </div>

        {{-- التنفيذ --}}
        @if($jugement->est_definitif && $jugement->executions->isEmpty())
        <div class="card border-0 shadow-sm">

            <div class="card-body text-center py-3">

                <a href="{{ route('executions.create', ['jugement_id' => $jugement->id]) }}"
                   class="btn btn-primary btn-sm">

                    <i class="bi bi-shield-check ms-1"></i>
                    بدء التنفيذ

                </a>

            </div>
        </div>
        @endif

        {{-- ملفات التنفيذ --}}
        @if($jugement->executions->isNotEmpty())
        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-shield-check ms-2 text-info"></i>
                    التنفيذات
                </h6>
            </div>

            <div class="list-group list-group-flush">

                @foreach($jugement->executions as $exec)

                <div class="list-group-item small d-flex justify-content-between align-items-center">

                    <span class="font-monospace">
                        {{ $exec->numero_dossier_execution }}
                    </span>

                    <span class="badge bg-info bg-opacity-15 text-white">
                        {{ $exec->statut->statut_execution ?? '—' }}
                    </span>

                </div>

                @endforeach

            </div>
        </div>
        @endif

        <div class="mt-3" dir="rtl">

            @if($jugement->dossierTribunal?->dossier)

            <a href="{{ route('dossiers.show', $jugement->dossierTribunal->dossier) }}#tab-jugements"
            class="btn btn-outline-primary btn-sm me-2 w-100">

                <i class="bi bi-folder2-open ms-1"></i>
                عرض الملف

            </a>

            @endif

        </div>

    </div>
</div>



@endsection