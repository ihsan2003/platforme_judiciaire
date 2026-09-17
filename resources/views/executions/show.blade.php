{{-- resources/views/executions/show_ar.blade.php --}}
@extends('layouts.app')

@section('title', 'التنفيذ ' . $execution->numero_dossier_execution)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">الرئيسية</a>
    </li>

    <li class="breadcrumb-item">
        <a href="{{ route('executions.index') }}">التنفيذات</a>
    </li>

    <li class="breadcrumb-item active">
        {{ $execution->numero_dossier_execution }}
    </li>
@endsection

@section('content')

@php
    $statutLabel = $execution->statut?->statut_execution ?? '—';

    $statutColor = match(true) {
        str_contains($statutLabel, 'Terminé') => ['bg' => 'success', 'icon' => 'shield-check'],
        str_contains($statutLabel, 'cours')   => ['bg' => 'warning', 'icon' => 'hourglass-split'],
        str_contains($statutLabel, 'Suspendu')=> ['bg' => 'danger',  'icon' => 'pause-circle'],
        default                               => ['bg' => 'secondary','icon' => 'dash-circle'],
    };

    $textColor = $statutColor['bg'] === 'secondary'
        ? 'white'
        : $statutColor['bg'];

    $dossier  = $execution->jugement?->dossierTribunal?->dossier;
    $tribunal = $execution->jugement?->dossierTribunal?->tribunal;
    $jugement = $execution->jugement;
    $finance  = $jugement?->finance;
@endphp

<div dir="rtl">

{{-- ══ HEADER ══ --}}
<div class="card border-0 shadow-sm mb-4">

    <div class="card-body">

        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">

            {{-- Identity --}}
            <div class="d-flex align-items-center gap-3">

                <div class="rounded-3 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                     style="width:56px;height:56px">

                    <i class="bi bi-shield fs-3 text-primary"></i>

                </div>

                <div class="d-flex flex-column">

                    <h4 class="fw-bold mb-0 font-monospace">
                        {{ $execution->numero_dossier_execution }}
                    </h4>

                    @if($dossier)

                        <a href="{{ route('dossiers.show', $dossier) }}" class="text-decoration-none">

                            <i class="bi bi-folder2-open ms-1"></i>

                            {{ $dossier->numero_dossier_tribunal }}

                        </a>

                    @endif

                    @if($jugement)

                        <a href="{{ route('jugements.show', $jugement) }}" class="text-decoration-none">

                            <i class="bi bi-hammer ms-1"></i>

                            {{ $jugement->date_jugement }}

                        </a>

                    @endif

                    <div class="mt-1">

                        <span class="badge bg-{{ $statutColor['bg'] }} bg-opacity-15 text-{{ $textColor }} border border-{{ $statutColor['bg'] }} border-opacity-25">

                            <i class="bi bi-{{ $statutColor['icon'] }} ms-1"></i>

                            {{ $statutLabel }}

                        </span>

                    </div>

                </div>

            </div>

            {{-- Actions --}}
            <div class="d-flex gap-2 flex-wrap">

                <a href="{{ route('executions.edit', $execution) }}"
                   class="btn btn-warning btn-sm">

                    <i class="bi bi-pencil ms-1"></i>

                    تعديل

                </a>

                @role('admin')
                <x-modal-delete
                    :action="route('executions.destroy', $execution)"
                    modal-id="deleteExecution{{ $execution->id }}"
                    title="حذف التنفيذ"
                    :description="'تنفيذ بتاريخ ' . $execution->date_notification->format('d/m/Y')"
                />
                @endrole

                <a href="{{ route('executions.index') }}"
                   class="btn btn-outline-secondary btn-sm">

                    <i class="bi bi-arrow-right ms-1"></i>

                    رجوع

                </a>

            </div>

        </div>

        {{-- Metadata --}}
        <hr class="my-3">

        <div class="row g-2 small text-muted">

            <div class="col-sm-3">

                <i class="bi bi-bell ms-1"></i>

                <strong>التبليغ :</strong>

                {{ $execution->date_notification?->format('d/m/Y') ?? '—' }}

            </div>

            <div class="col-sm-3">

                <i class="bi bi-calendar-check ms-1"></i>

                <strong>تم التنفيذ في :</strong>

                @if($execution->date_execution)

                    <span class="text-success fw-semibold">
                        {{ $execution->date_execution->format('d/m/Y') }}
                    </span>

                @else

                    <span class="badge bg-warning text-dark bg-opacity-20">
                        في الانتظار
                    </span>

                @endif

            </div>

            <div class="col-sm-3">

                <i class="bi bi-person ms-1"></i>

                <strong>المسؤول :</strong>

                {{ $execution->responsable?->name ?? '—' }}

            </div>

            <div class="col-sm-3">

                <i class="bi bi-clock ms-1"></i>

                <strong>آخر تحديث :</strong>

                {{ $execution->updated_at->diffForHumans() }}

            </div>

        </div>

    </div>

</div>

{{-- ══ MAIN CONTENT ══ --}}
<div class="row g-4">

    {{-- LEFT COLUMN --}}
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm mb-4"
             style="border-right: 4px solid {{ $estContreInstitution ? '#0d6efd' : '#198754' }} !important;">

            <div class="card-header bg-white py-3 d-flex align-items-center gap-2">

                <div class="rounded-circle {{ $estContreInstitution ? 'bg-primary' : 'bg-success' }} bg-opacity-10 d-flex align-items-center justify-content-center"
                     style="width:32px;height:32px;flex-shrink:0">

                    <i class="bi {{ $estContreInstitution ? 'bi-building-fill text-primary' : 'bi-people-fill text-success' }}"
                       style="font-size:.85rem"></i>

                </div>

                <h6 class="mb-0 fw-semibold">
                    {{ $estContreInstitution ? 'المؤسسة المعنية' : 'الأطراف المعنية بالتنفيذ' }}
                </h6>

                <span class="badge {{ $estContreInstitution ? 'bg-primary' : 'bg-success' }} me-auto">
                    {{ $estContreInstitution ? 'مؤسسة' : $partiesConcernees->count() . ' طرف' }}
                </span>

            </div>

            <div class="card-body">

                @forelse($partiesConcernees as $partieConcernee)

                    <div class="d-flex align-items-start gap-3 {{ !$loop->last ? 'mb-3 pb-3 border-bottom' : '' }}">

                        <div class="rounded-3 {{ $estContreInstitution ? 'bg-primary' : 'bg-success' }} bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:48px;height:48px">

                            <i class="bi {{ $estContreInstitution ? 'bi-bank text-primary' : 'bi-person text-success' }} fs-5"></i>

                        </div>

                        <div class="flex-grow-1">

                            <div class="fw-bold fs-6">
                                {{ $partieConcernee->partie?->nom_partie ?? '—' }}
                            </div>

                            <div class="text-muted small mt-1 d-flex flex-wrap gap-3">

                                @if($partieConcernee->partie?->identifiant_unique)
                                    <span>
                                        <i class="bi bi-fingerprint ms-1"></i>

                                        <span class="font-monospace">
                                            {{ $partieConcernee->partie->identifiant_unique }}
                                        </span>
                                    </span>
                                @endif

                                @if($partieConcernee->typePartie)
                                    <span>
                                        <i class="bi bi-tag ms-1"></i>

                                        {{ $partieConcernee->typePartie->type_partie }}
                                    </span>
                                @endif

                                @if($partieConcernee->partie?->telephone)
                                    <span>
                                        <i class="bi bi-telephone ms-1"></i>

                                        {{ $partieConcernee->partie->telephone }}
                                    </span>
                                @endif

                                @if($partieConcernee->partie?->email)
                                    <span>
                                        <i class="bi bi-envelope ms-1"></i>

                                        {{ $partieConcernee->partie->email }}
                                    </span>
                                @endif

                                @if($partieConcernee->avocat)
                                    <span>
                                        <i class="bi bi-briefcase ms-1"></i>

                                        الأستاذ {{ $partieConcernee->avocat->nom_avocat }}
                                    </span>
                                @endif

                            </div>

                            @if($partieConcernee->partie?->adresse)

                                <div class="text-muted small mt-1">

                                    <i class="bi bi-geo-alt ms-1"></i>

                                    {{ $partieConcernee->partie->adresse }}

                                </div>

                            @endif

                        </div>

                    </div>

                @empty

                    <div class="text-center py-3 text-muted small">

                        <i class="bi bi-building fs-2 d-block mb-2 opacity-25"></i>

                        لم يتم تحديد أي طرف معني بهذا التنفيذ.

                    </div>

                @endforelse

            </div>

        </div>

    @if($finance)
        {{-- الوضعية المالية --}}
        @php
            $spLabel = $finance->statut_paiement ?? '—';

            $spColor = match($spLabel) {
                'مكتمل' => 'success',
                'جزئي'  => 'warning',
                default => 'secondary',
            };

            // Couleur du texte selon la couleur du badge
            $spTextColor = $spColor === 'warning' ? 'dark' : 'white';

            $pct = $finance->montant_condamne > 0
                ? min(100, round(($finance->montant_paye / $finance->montant_condamne) * 100))
                : 0;

            $pctColor = $pct >= 100
                ? 'success'
                : ($pct > 0 ? 'warning' : 'danger');
        @endphp

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">

                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-cash-stack ms-2 text-success"></i>
                    الوضعية المالية
                </h6>

                <span class="badge bg-{{ $spColor }} bg-opacity-15 text-{{ $spTextColor }} border border-{{ $spColor }} border-opacity-25">
                    {{ $spLabel }}
                </span>

            </div>

            <div class="card-body small">

                <dl class="row mb-0">

                    <dt class="col-7 text-muted fw-normal">
                        المبلغ المحكوم به
                    </dt>

                    <dd class="col-5 text-end fw-semibold" dir="ltr">
                        {{ number_format($finance->montant_condamne, 2, '.', ',') }} DH
                    </dd>

                    <dt class="col-7 text-muted fw-normal">
                        المبلغ المؤدى
                    </dt>

                    <dd class="col-5 text-end fw-semibold text-success" dir="ltr">
                        {{ number_format($finance->montant_paye, 2, '.', ',') }} DH
                    </dd>

                    <dt class="col-7 text-muted fw-normal">
                        المبلغ المتبقي
                    </dt>

                    <dd class="col-5 text-end fw-semibold {{ $finance->montant_restant > 0 ? 'text-danger' : 'text-success' }}" dir="ltr">
                        {{ number_format($finance->montant_restant, 2, '.', ',') }} DH
                    </dd>

                    @if($finance->date_paiement)

                        <dt class="col-7 text-muted fw-normal">
                            تاريخ آخر أداء
                        </dt>

                        <dd class="col-5 text-end">
                            {{ $finance->date_paiement->format('d/m/Y') }}
                        </dd>

                    @endif

                </dl>

                <div class="progress mt-3" style="height:8px">
                    <div class="progress-bar bg-{{ $pctColor }}" style="width: {{ $pct }}%"></div>
                </div>

                <div class="text-muted small mt-1 text-start">
                    {{ $pct }}% من المبلغ المحكوم به تم أداؤه
                </div>

                @if($finance->est_solde)

                    <div class="alert alert-success py-2 px-3 small mt-3 mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        تم تسديد كامل المبلغ المحكوم به.
                    </div>

                @elseif(!$finance->est_finance_valide)

                    <div class="alert alert-warning py-2 px-3 small mt-3 mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        هذا الحكم لم يعد هو الحكم المعتمد في الخلاصة المالية للملف.
                    </div>

                @endif

                <div class="text-end mt-3">

                    <a href="{{ route('finances.show', $finance) }}" class="btn btn-outline-success">
                        <i class="bi bi-eye ms-1"></i>
                        عرض التفاصيل المالية
                    </a>
                    <a href="{{ route('finances.edit', $finance) }}" class="btn btn-outline-warning">
                        <i class="bi bi-pen ms-1"></i>
                        تعديل
                    </a>

                </div>

            </div>

        </div>
        @endif

    </div>

    {{-- RIGHT COLUMN --}}
    <div class="col-lg-4">

        {{-- Execution info --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h6 class="mb-0 fw-semibold">

                    <i class="bi bi-info-circle ms-2 text-primary"></i>

                    معلومات التنفيذ

                </h6>

            </div>

            <div class="card-body small">

                <dl class="row mb-0">

                    <dt class="col-6 text-muted fw-normal">
                        رقم التنفيذ
                    </dt>

                    <dd class="col-6 fw-semibold font-monospace">
                        {{ $execution->numero_dossier_execution }}
                    </dd>

                    <dt class="col-6 text-muted fw-normal">
                        الحالة
                    </dt>

                    <dd class="col-6">

                        <span class="badge bg-{{ $statutColor['bg'] }} bg-opacity-15 text-{{ $textColor }}">

                            <i class="bi bi-{{ $statutColor['icon'] }} ms-1"></i>

                            {{ $statutLabel }}

                        </span>

                    </dd>

                    <dt class="col-6 text-muted fw-normal">
                        المسؤول
                    </dt>

                    <dd class="col-6">
                        {{ $execution->responsable?->name ?? '—' }}
                    </dd>

                    <dt class="col-6 text-muted fw-normal">
                        التبليغ
                    </dt>

                    <dd class="col-6">
                        {{ $execution->date_notification?->format('d/m/Y') ?? '—' }}
                    </dd>

                    <dt class="col-6 text-muted fw-normal">
                        التنفيذ
                    </dt>

                    <dd class="col-6">

                        @if($execution->date_execution)

                            <span class="text-success fw-semibold">

                                {{ $execution->date_execution->format('d/m/Y') }}

                            </span>

                        @else

                            <span class="badge bg-warning text-dark bg-opacity-20">
                                في الانتظار
                            </span>

                        @endif

                    </dd>

                    <dt class="col-6 text-muted fw-normal">
                        تاريخ الإنشاء
                    </dt>

                    <dd class="col-6">
                        {{ $execution->created_at->format('d/m/Y') }}
                    </dd>

                </dl>

            </div>

        </div>

    </div>

</div>

</div>

@endsection