{{-- resources/views/audiences/show.blade.php --}}
@extends('layouts.app')

@section('title', 'جلسة بتاريخ ' . $audience->date_audience->format('d/m/Y'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('audiences.index') }}">الجلسات</a></li>
    <li class="breadcrumb-item active">{{ $audience->date_audience->format('d/m/Y') }}</li>
@endsection

@section('content')

@php
    $dt      = $audience->dossierTribunal;
    $dossier = $dt?->dossier;

    $estPassee  = $audience->date_audience->isPast();
    $estAujourd = $audience->date_audience->isToday();
    $estFuture  = $audience->date_audience->isFuture();

    $badgeColor = $estAujourd ? 'danger' : ($estPassee ? 'secondary' : 'success');
    $badgeLabel = $estAujourd ? 'اليوم' : ($estPassee ? 'منتهية' : 'قادمة');

    $isHoukm = $audience->typeAudience?->type_audience === 'الحكم';

    $textClass = in_array($badgeColor, ['warning','info'])
        ? 'text-dark'
        : 'text-white';
@endphp

<div class="card border-0 shadow-sm mb-4" dir="rtl">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">

            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                     style="width:56px;height:56px">
                    <i class="bi bi-calendar-event fs-3 text-primary"></i>
                </div>

                <div>
                    <h4 class="fw-bold mb-0">
                        جلسة بتاريخ {{ $audience->date_audience->format('d/m/Y') }}
                    </h4>

                    <div class="mt-1 d-flex flex-wrap gap-2 align-items-center">

                        <span class="badge bg-{{ $badgeColor }} {{ $textClass }} border border-{{ $badgeColor }} border-opacity-25">
                            <i class="bi bi-circle-fill ms-1" style="font-size:.5rem;vertical-align:middle"></i>
                            {{ $badgeLabel }}
                        </span>

                        @if($isHoukm)
                            <span class="badge bg-warning text-dark border border-warning border-opacity-25">
                                جلسة الحكم
                            </span>
                        @else
                            <span class="badge bg-info text-dark border border-info border-opacity-25">
                                {{ $audience->typeAudience?->type_audience ?? '—' }}
                            </span>
                        @endif

                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('audiences.edit', $audience) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil ms-1"></i>تعديل
                </a>

                @if(!$isHoukm || !$dt->aUnJugement())
                    <x-modal-delete
                        :action="route('audiences.destroy', $audience)"
                        modal-id="deleteAudience{{ $audience->id }}"
                        title="حذف الجلسة"
                        :description="'جلسة بتاريخ ' . $audience->date_audience->format('d/m/Y')"
                    />
                @endif

                <a href="{{ route('audiences.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-right ms-1"></i>رجوع
                </a>
            </div>
        </div>

        <hr class="my-3">

        <div class="row g-2 small text-muted">

            <div class="col-sm-3">
                <i class="bi bi-bank ms-1"></i>
                <strong>المحكمة :</strong>
                {{ $dt?->tribunal?->nom_tribunal ?? '—' }}
            </div>

            <div class="col-sm-3">
                <i class="bi bi-layers ms-1"></i>
                <strong>الدرجة :</strong>
                {{ $dt?->degre?->degre_juridiction ?? '—' }}
            </div>

            <div class="col-sm-3">
                <i class="bi bi-person-workspace ms-1"></i>
                <strong>القاضي :</strong>
                {{ $audience->juge?->nom_complet ?? '—' }}
            </div>

            <div class="col-sm-3">
                <i class="bi bi-folder2-open ms-1"></i>
                <strong>الملف :</strong>

                @if($dossier)
                    <a href="{{ route('dossiers.show', $dossier) }}"
                       class="text-decoration-none text-primary">
                        {{ $dossier->numero_dossier_interne }}
                    </a>
                @else
                    —
                @endif
            </div>

        </div>
    </div>
</div>

<div class="row g-4" dir="rtl">

    {{-- العمود الرئيسي --}}
    <div class="col-lg-8">

        {{-- تفاصيل الجلسة --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle ms-2 text-primary"></i>
                    تفاصيل الجلسة
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    {{-- حضور الأطراف --}}
                    <div class="col-sm-6">
                        <div class="p-3 rounded border h-100">

                            <div class="small text-muted fw-semibold mb-2">
                                حضور المدعي
                            </div>

                            @if($audience->presence_demandeur)
                                <span class="badge bg-success bg-opacity-15 text-white border border-success border-opacity-25">
                                    <i class="bi bi-check-circle ms-1"></i>
                                    حاضر
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-15 text-white border border-danger border-opacity-25">
                                    <i class="bi bi-x-circle ms-1"></i>
                                    غائب
                                </span>
                            @endif

                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="p-3 rounded border h-100">

                            <div class="small text-muted fw-semibold mb-2">
                                حضور المدعى عليه
                            </div>

                            @if($audience->presence_defendeur)
                                <span class="badge bg-success bg-opacity-15 text-white border border-success border-opacity-25">
                                    <i class="bi bi-check-circle ms-1"></i>
                                    حاضر
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-15 text-white border border-danger border-opacity-25">
                                    <i class="bi bi-x-circle ms-1"></i>
                                    غائب
                                </span>
                            @endif

                        </div>
                    </div>

                    {{-- الجلسة القادمة --}}
                    <div class="col-12">
                        <div class="p-3 rounded border">

                            <div class="small text-muted fw-semibold mb-1">
                                الجلسة القادمة (التأجيل)
                            </div>

                            @if($audience->date_prochaine_audience)

                                <span class="fw-semibold text-primary">
                                    <i class="bi bi-calendar-arrow-down ms-1"></i>
                                    {{ $audience->date_prochaine_audience->format('d/m/Y') }}
                                </span>

                                <span class="text-muted small me-2">
                                    ({{ $audience->date_prochaine_audience->diffForHumans() }})
                                </span>

                            @else
                                <span class="text-muted small">
                                    لا يوجد تأجيل مسجل
                                </span>
                            @endif

                        </div>
                    </div>

                    {{-- نتيجة الجلسة --}}
                    @if($audience->resultat_audience)
                    <div class="col-12">
                        <div class="p-3 rounded border">

                            <div class="small text-muted fw-semibold mb-2">
                                نتيجة الجلسة
                            </div>

                            <div class="small"
                                 style="white-space:pre-wrap;line-height:1.7">
                                {{ $audience->resultat_audience }}
                            </div>

                        </div>
                    </div>
                    @endif

                    {{-- الإجراءات المطلوبة --}}
                    @if($audience->actions_demandees)
                    <div class="col-12">
                        <div class="p-3 rounded border">

                            <div class="small text-muted fw-semibold mb-2">
                                الإجراءات المطلوبة
                            </div>

                            <div class="small"
                                 style="white-space:pre-wrap;line-height:1.7">
                                {{ $audience->actions_demandees }}
                            </div>

                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- جلسات أخرى --}}
        @if($autresAudiences->isNotEmpty())

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-calendar3 ms-2 text-secondary"></i>

                    جلسات أخرى لنفس الملف

                    <span class="badge bg-secondary me-1">
                        {{ $autresAudiences->count() }}
                    </span>
                </h6>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th class="pe-3 small text-muted fw-semibold">التاريخ</th>
                            <th class="small text-muted fw-semibold">النوع</th>
                            <th class="small text-muted fw-semibold">النتيجة</th>
                            <th class="text-start ps-3 small text-muted fw-semibold">الإجراء</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($autresAudiences as $a)

                        <tr>

                            <td class="pe-3 small fw-semibold">

                                {{ $a->date_audience->format('d/m/Y') }}

                                @if($a->date_audience->isToday())
                                    <span class="badge bg-danger me-1">
                                        اليوم
                                    </span>

                                @elseif($a->date_audience->isFuture())
                                    <span class="badge bg-success bg-opacity-15 text-white me-1">
                                        قادمة
                                    </span>
                                @endif

                            </td>

                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 small">
                                    {{ $a->typeAudience?->type_audience ?? '—' }}
                                </span>
                            </td>

                            <td class="text-muted small">
                                {{ \Str::limit($a->resultat_audience ?? '—', 40) }}
                            </td>

                            <td class="text-start ps-3">
                                <a href="{{ route('audiences.show', $a) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>

                        </tr>

                        @endforeach

                    </tbody>

                </table>
            </div>
        </div>

        @endif

    </div>

    {{-- العمود الجانبي --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm">

            <div class="card-body py-3 d-flex flex-column gap-2">

                @if($dossier)
                    <a href="{{ route('dossiers.show', $dossier) }}#tab-audiences"
                       class="btn btn-outline-primary w-100 btn-sm">
                        <i class="bi bi-folder2-open ms-1"></i>
                        عرض الملف
                    </a>
                @endif

                @if($dt?->peutAvoirJugement())
                    <a href="{{ route('jugements.create', ['dossier_id' => $dossier?->id]) }}"
                       class="btn btn-outline-success w-100 btn-sm">
                        <i class="bi bi-hammer ms-1"></i>
                        تسجيل الحكم
                    </a>
                @endif

                <a href="{{ route('audiences.create', ['dossier_id' => $dossier?->id, 'dossier_tribunal_id' => $dt?->id]) }}"
                   class="btn btn-outline-secondary w-100 btn-sm">
                    <i class="bi bi-calendar-plus ms-1"></i>
                    جلسة جديدة (نفس الملف)
                </a>

            </div>
        </div>

    </div>
</div>

@endsection