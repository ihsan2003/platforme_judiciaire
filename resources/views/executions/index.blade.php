{{-- resources/views/executions/index_ar.blade.php --}}
@extends('layouts.app')

@section('title', 'التنفيذات')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">الرئيسية</a>
    </li>
    <li class="breadcrumb-item active">التنفيذات</li>
@endsection

@section('content')

<div dir="rtl">

{{-- ══ الإحصائيات ══ --}}
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-shield fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['total'] }}</div>
                    <div class="text-muted small">الإجمالي</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['en_cours'] }}</div>
                    <div class="text-muted small">قيد التنفيذ</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-shield-check fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['terminees'] }}</div>
                    <div class="text-muted small">مكتملة</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3">
                    <i class="bi bi-calendar-plus fs-4 text-info"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['ce_mois'] }}</div>
                    <div class="text-muted small">هذا الشهر</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ الفلاتر ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">

        <form method="GET" class="row g-2 align-items-end">

            {{-- Recherche --}}
            <div class="col-md-2">

                <label class="form-label small text-muted fw-semibold">
                    بحث
                </label>

                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="رقم التنفيذ أو المحكمة..."
                       value="{{ request('search') }}">

            </div>

            {{-- Statut --}}
            <div class="col-md-2">

                <label class="form-label small text-muted fw-semibold">
                    الحالة
                </label>

                <select name="statut"
                        class="form-select">

                    <option value="">كل الحالات</option>

                    @foreach($statuts as $statut)

                        <option value="{{ $statut->id }}"
                            @selected(request('statut') == $statut->id)>

                            {{ $statut->statut_execution }}

                        </option>

                    @endforeach

                </select>

            </div>

            {{-- Date notification --}}
            <div class="col-md-2">

                <label class="form-label small text-muted fw-semibold">
                    تاريخ التبليغ
                </label>

                <input type="date"
                       name="date_notification"
                       class="form-control"
                       value="{{ request('date_notification') }}">

            </div>

            {{-- Buttons --}}
            <div class="col-md-1 d-flex gap-2">

                <button class="btn btn-primary">

                    <i class="bi bi-funnel-fill ms-1"></i>

                </button>

                <a href="{{ route('executions.index') }}"
                   class="btn btn-outline-secondary">

                    <i class="bi bi-x-lg"></i>

                </a>

            </div>

        </form>

    </div>
</div>

{{-- ══ الجدول ══ --}}
<div class="card border-0 shadow-sm">

    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <h5 class="mb-0 fw-semibold">
            <i class="bi bi-shield ms-2 text-primary"></i>
            تنفيذ الأحكام

            <span class="badge bg-primary me-2">
                {{ $executions->total() }}
            </span>
        </h5>
    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">
                <tr>
                    <th class="pe-3 text-muted small fw-semibold">
                        رقم التنفيذ
                    </th>

                    <th class="text-muted small fw-semibold">
                        الحكم / الملف
                    </th>

                    <th class="text-muted small fw-semibold">
                        المحكمة
                    </th>

                    <th class="text-muted small fw-semibold">
                        الحالة
                    </th>

                    <th class="text-muted small fw-semibold">
                        المسؤول
                    </th>

                    <th class="text-muted small fw-semibold">
                        تاريخ التبليغ
                    </th>

                    <th class="text-muted small fw-semibold">
                        تاريخ التنفيذ
                    </th>

                    <th class="text-start ps-3 text-muted small fw-semibold">
                        الإجراءات
                    </th>
                </tr>
            </thead>

            <tbody>

                @forelse($executions as $execution)

                <tr>

                    <td class="pe-3">
                        <span class="fw-semibold font-monospace">
                            {{ $execution->numero_dossier_execution }}
                        </span>
                    </td>

                    <td>
                        @if($execution->jugement?->dossierTribunal?->dossier)

                            <a href="{{ route('dossiers.show', $execution->jugement->dossierTribunal->dossier) }}"
                               class="text-decoration-none fw-semibold text-primary d-block">

                                {{ $execution->jugement->dossierTribunal->dossier->numero_dossier_interne }}

                            </a>

                            <small class="text-muted">
                                حكم بتاريخ
                                {{ $execution->jugement->date_jugement->format('d/m/Y') }}
                            </small>

                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td class="text-muted small">
                        {{ $execution->jugement?->dossierTribunal?->tribunal?->nom_tribunal ?? '—' }}
                    </td>

                    <td>

                        @php
                            $statutLabel = $execution->statut?->statut_execution ?? '—';

                            $color = match(true) {
                                str_contains($statutLabel, 'Terminé')  => 'success',
                                str_contains($statutLabel, 'cours')    => 'warning',
                                str_contains($statutLabel, 'Suspendu') => 'danger',
                                default                                => 'secondary',
                            };

                            $textClass = in_array($color, ['warning', 'infos'])
                                ? 'text-dark'
                                : 'text-white';
                        @endphp

                        <span class="badge bg-{{ $color }} {{ $textClass }} border border-{{ $color }} border-opacity-25">
                            {{ $statutLabel }}
                        </span>

                    </td>

                    <td class="text-muted small">
                        {{ $execution->responsable?->name ?? '—' }}
                    </td>

                    <td class="text-muted small">
                        {{ $execution->date_notification?->format('d/m/Y') ?? '—' }}
                    </td>

                    <td class="text-muted small">

                        @if($execution->date_execution)

                            <span class="text-success fw-semibold">
                                {{ $execution->date_execution->format('d/m/Y') }}
                            </span>

                        @else

                            <span class="badge bg-warning text-dark bg-opacity-20">
                                في الانتظار
                            </span>

                        @endif

                    </td>

                    <td class="text-start ps-3">

                        <div class="d-flex gap-1 justify-content-start">

                            <a href="{{ route('executions.show', $execution) }}"
                               class="btn btn-sm btn-outline-primary"
                               title="عرض">

                                <i class="bi bi-eye"></i>

                            </a>

                            <a href="{{ route('executions.edit', $execution) }}"
                               class="btn btn-sm btn-outline-warning"
                               title="تعديل">

                                <i class="bi bi-pencil"></i>

                            </a>

                            <x-modal-delete
                                :action="route('executions.destroy', $execution)"
                                modal-id="deleteExecution{{ $execution->id }}"
                                title="حذف التنفيذ"
                                trigger-label=""
                                :description="'تنفيذ بتاريخ ' . $execution->date_notification->format('d/m/Y')"
                            />

                        </div>

                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">

                        <i class="bi bi-shield-x fs-1 d-block mb-2 opacity-25"></i>

                        لا توجد أي تنفيذات

                        @if(request()->hasAny(['statut','responsable']))
                            —
                            <a href="{{ route('executions.index') }}">
                                إعادة تعيين الفلاتر
                            </a>
                        @endif

                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($executions->hasPages())

    <div class="card-footer bg-white d-flex justify-content-between align-items-center">

        <span class="text-muted small">
            عرض
            {{ $executions->firstItem() }}
            –
            {{ $executions->lastItem() }}

            من أصل
            {{ $executions->total() }}
            تنفيذ
        </span>

        {{ $executions->links() }}

    </div>

    @endif

</div>

</div>

@endsection