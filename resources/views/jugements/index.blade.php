{{-- resources/views/jugements/index.blade.php --}}
@extends('layouts.app')

@section('title', 'الأحكام')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">الرئيسية</a>
    </li>
    <li class="breadcrumb-item active">الأحكام</li>
@endsection

@section('content')

{{-- ══ الإحصائيات ══ --}}
<div class="row g-3 mb-4" dir="rtl">

    <div class="col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">

                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-journal-text fs-4 text-primary"></i>
                </div>

                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ $stats['total'] }}
                    </div>
                    <div class="text-muted small">
                        المجموع
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">

                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-check-circle fs-4 text-success"></i>
                </div>

                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ $stats['definitifs'] }}
                    </div>
                    <div class="text-muted small">
                        نهائية
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">

                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-arrow-repeat fs-4 text-warning"></i>
                </div>

                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ $stats['en_appel'] }}
                    </div>
                    <div class="text-muted small">
                        قيد الاستئناف
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">

                <div class="rounded-circle bg-info bg-opacity-10 p-3">
                    <i class="bi bi-shield-check fs-4 text-info"></i>
                </div>

                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ $stats['executes'] }}
                    </div>
                    <div class="text-muted small">
                        منفذة
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>


{{-- ══ الفلاتر ══ --}}
<div class="card border-0 shadow-sm mb-4" dir="rtl">

    <div class="card-body">

        <form method="GET" class="row g-2 align-items-end">

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">
                    القاضي
                </label>

                <select name="juge" class="form-select ">
                    <option value="">جميع القضاة</option>

                    @foreach($juges as $juge)
                        <option value="{{ $juge->id }}"
                                @selected(request('juge') == $juge->id)>
                            {{ $juge->nom_complet }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">
                    الصفة
                </label>

                <select name="definitif" class="form-select ">

                    <option value="">الكل</option>

                    <option value="oui"
                            @selected(request('definitif') === 'oui')>
                        نهائية
                    </option>

                    <option value="non"
                            @selected(request('definitif') === 'non')>
                        غير نهائية
                    </option>

                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">
                    الدرجة
                </label>

                <select name="degre" class="form-select ">

                    <option value="">الكل</option>

                    @foreach($degres as $degre)
                        <option value="{{ $degre->id }}"
                                @selected(request('degre') == $degre->id)>
                            {{ $degre->degre_juridiction }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">
                    وضعية المؤسسة
                </label>

                <select name="position" class="form-select">

                    <option value="">الكل</option>

                    <option value="contre"
                            @selected(request('position') === 'contre')>
                        ضد المؤسسة
                    </option>

                    <option value="partiel"
                            @selected(request('position') === 'partiel')>
                        جزئي
                    </option>

                    <option value="pour"
                            @selected(request('position') === 'pour')>
                        لصالح المؤسسة
                    </option>

                </select>
            </div>

            <div class="col-md-1 d-flex gap-1">

                <button class="btn btn-primary flex-fill" title="تصفية">
                    <i class="bi bi-funnel-fill"></i>
                </button>

                <a href="{{ route('jugements.index') }}"
                   class="btn btn-outline-secondary"
                   title="إعادة التعيين">
                    <i class="bi bi-x-lg"></i>
                </a>

            </div>

        </form>

    </div>
</div>

{{-- ══ الجدول ══ --}}
<div class="card border-0 shadow-sm" dir="rtl">

    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">

        <h5 class="mb-0 fw-semibold">

            <i class="bi bi-journal-text ms-2 text-primary"></i>
            الأحكام

            <span class="badge bg-primary me-2">
                {{ $jugements->total() }}
            </span>

        </h5>

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">

                <tr>
                    <x-sortable-th column="date" class="text-muted small fw-semibold ps-3">
                     التاريخ
                    </x-sortable-th>
                    <x-sortable-th column="dossier">
                        الملف
                    </x-sortable-th>

                    <x-sortable-th column="tribunal">
                        المحكمة
                    </x-sortable-th>

                    <x-sortable-th column="degre">
                        الدرجة
                    </x-sortable-th>
                    <x-sortable-th column="juge" class="text-muted small fw-semibold ps-3">
                     القاضي
                    </x-sortable-th>
                    <x-sortable-th column="position">
                        وضعية المؤسسة
                    </x-sortable-th>
                    <x-sortable-th column="definitif" class="text-muted small fw-semibold ps-3">
                     الصفة
                    </x-sortable-th>
                    <th class="text-muted small fw-semibold">الطعن</th>
                    <x-sortable-th column="execution">
                        التنفيذ
                    </x-sortable-th>
                    <th class="text-start ps-3 text-muted small fw-semibold">
                        الإجراءات
                    </th>
                </tr>

            </thead>

            <tbody>

                @forelse($jugements as $jugement)

                <tr>

                    <td class="pe-3 fw-semibold">
                        {{ $jugement->date_jugement->format('d/m/Y') }}
                    </td>

                    <td>

                        @if($jugement->dossierTribunal?->dossier)

                            <a href="{{ route('dossiers.show', $jugement->dossierTribunal->dossier) }}"
                               class="text-decoration-none fw-semibold text-primary">

                                {{ $jugement->dossierTribunal->dossier->numero_dossier_tribunal }}

                            </a>

                        @else
                            <span class="text-muted">—</span>
                        @endif

                    </td>

                    <td class="text-muted small">
                        {{ $jugement->dossierTribunal?->tribunal?->nom_tribunal ?? '—' }}
                    </td>

                    <td class="text-muted small">
                       {{ $jugement->dossierTribunal?->degre?->degre_juridiction ?? '—' }}
                   </td>

                    <td class="text-muted small">
                        {{ $jugement->juge?->nom_complet ?? '—' }}
                    </td>

                     <td>
                       @php
                           $etabPartie = $jugement->parties
                               ->first(fn($p) => $p->est_entraide);
                           $posId    = $etabPartie?->pivot->id_position_institution;
                           $posLabel = $posId ? ($positionsParId[$posId]->position ?? null) : null;
                       @endphp

                       @if($etabPartie === null || $posLabel === null)
                           <span class="text-muted">—</span>
                       @elseif($posLabel === 'ضد')
                           <span class="badge bg-danger bg-opacity-15 text-white border border-danger border-opacity-25">
                               <i class="bi bi-shield-x ms-1"></i> ضد المؤسسة
                           </span>
                       @elseif($posLabel === 'جزئي')
                           <span class="badge text-white" style="background:#BA7517">
                               <i class="bi bi-dash-circle ms-1"></i> جزئي
                           </span>
                       @else
                           <span class="badge bg-success bg-opacity-15 text-white border border-success border-opacity-25">
                               <i class="bi bi-trophy-fill ms-1"></i> لصالح المؤسسة
                           </span>
                       @endif
                   </td>

                    <td>

                        @if($jugement->est_definitif)

                            <span class="badge bg-success bg-opacity-15 text-white border border-success border-opacity-25">
                                <i class="bi bi-check-circle ms-1"></i>
                                نهائي
                            </span>

                        @else

                            @php
                                $delai = $jugement->delai_recours_restant;
                            @endphp

                            <span class="badge {{ $delai !== null && $delai <= 5 ? 'bg-danger' : 'bg-warning text-dark' }} bg-opacity-15 border border-opacity-25">

                                <i class="bi bi-clock ms-1"></i>
                                غير نهائي

                                @if($delai !== null)
                                    ({{ $delai }} يوم)
                                @endif

                            </span>

                        @endif

                    </td>

                    <td>

                        @if($jugement->recours->isNotEmpty())

                            <span class="badge bg-warning bg-opacity-15 text-black border border-warning border-opacity-25">

                                <i class="bi bi-arrow-repeat ms-1"></i>
                                {{ $jugement->recours->count() }} طعن

                            </span>

                        @else
                            <span class="text-muted small">—</span>
                        @endif

                    </td>

                    <td>

                        @if($jugement->executions->isNotEmpty())

                            <span class="badge bg-info bg-opacity-15 text-white border border-info border-opacity-25">

                                <i class="bi bi-shield-check ms-1"></i>

                                {{ $jugement->executions->first()->statut?->statut_execution ?? 'قيد التنفيذ' }}

                            </span>

                        @else
                            <span class="text-muted small">—</span>
                        @endif

                    </td>

                    <td class="text-start ps-3">

                        <div class="d-flex gap-1 justify-content-start">

                            <a href="{{ route('jugements.show', $jugement) }}"
                               class="btn btn-sm btn-outline-primary"
                               title="عرض">

                                <i class="bi bi-eye"></i>

                            </a>

                            <a href="{{ route('jugements.edit', $jugement) }}"
                               class="btn btn-sm btn-outline-warning"
                               title="تعديل">

                                <i class="bi bi-pencil"></i>

                            </a>

                            <x-modal-delete
                                :action="route('jugements.destroy', $jugement)"
                                modal-id="deleteJugement{{ $jugement->id }}"
                                title="حذف الحكم"
                                trigger-label=""
                                :description="'حكم بتاريخ ' . $jugement->date_jugement->format('d/m/Y')"
                            />

                        </div>

                    </td>

                </tr>

                @empty

                <tr>

                    <td colspan="8" class="text-center py-5 text-muted">

                        <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-25"></i>

                        لا توجد أحكام

                        @if(request()->hasAny(['juge','definitif']))
                            —
                            <a href="{{ route('jugements.index') }}">
                                إعادة تعيين الفلاتر
                            </a>
                        @endif

                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($jugements->hasPages())

    <div class="card-footer bg-white d-flex justify-content-between align-items-center">

        <span class="text-muted small">

            عرض {{ $jugements->firstItem() }}–{{ $jugements->lastItem() }}
            من أصل {{ $jugements->total() }} حكم

        </span>

        {{ $jugements->links() }}

    </div>

    @endif

</div>

@endsection