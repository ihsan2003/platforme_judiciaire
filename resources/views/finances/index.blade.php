{{-- resources/views/finances/index_ar.blade.php --}}
@extends('layouts.app')

@section('title', 'المالية')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">الرئيسية</a>
    </li>
    <li class="breadcrumb-item active">المالية</li>
@endsection

@section('content')

<div dir="rtl">

{{-- ══ الإحصائيات ══ --}}
@php
    $totalCondamne = $finances->sum('montant_condamne');
    $totalPaye     = $finances->sum('montant_paye');
    $totalRestant  = $finances->sum(fn($f) => $f->montant_restant);
    $totalSoldes   = $finances->filter(fn($f) => $f->est_solde)->count();
@endphp

<div class="row g-3 mb-4">

    <div class="col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">

                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-cash-stack fs-4 text-primary"></i>
                </div>

                <div>
                    <div class="fs-5 fw-bold lh-1" dir="ltr">
                        {{ number_format($totalCondamne, 2, ',', ' ') }} DH
                    </div>
                    <div class="text-muted small">إجمالي المبلغ المحكوم به</div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">

                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-check2-circle fs-4 text-success"></i>
                </div>

                <div>
                    <div class="fs-5 fw-bold lh-1 text-success" dir="ltr">
                        {{ number_format($totalPaye, 2, ',', ' ') }} DH
                    </div>
                    <div class="text-muted small">إجمالي المبلغ المدفوع</div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-sm-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">

                <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                    <i class="bi bi-hourglass-split fs-4 text-danger"></i>
                </div>

                <div>
                    <div class="fs-5 fw-bold lh-1 text-danger" dir="ltr">
                        {{ number_format($totalRestant, 2, ',', ' ') }} DH
                    </div>
                    <div class="text-muted small">المبلغ المتبقي</div>
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
                        {{ $totalSoldes }}
                    </div>
                    <div class="text-muted small">ملفات مسددة</div>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- ══ الجدول ══ --}}
<div class="card border-0 shadow-sm">

    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">

        <h5 class="mb-0 fw-semibold">
            <i class="bi bi-cash-stack ms-2 text-primary"></i>
            الحالة المالية
            <span class="badge bg-primary me-2">{{ $finances->count() }}</span>
        </h5>

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">

                <tr>

                    <th class="pe-3 text-muted small fw-semibold">
                        الحكم
                    </th>

                    <th class="text-muted small fw-semibold">
                        الملف / المحكمة
                    </th>

                    <th class="text-muted small fw-semibold text-center">
                        المحكوم به
                    </th>

                    <th class="text-muted small fw-semibold text-center">
                        المدفوع
                    </th>

                    <th class="text-muted small fw-semibold text-center">
                        المتبقي
                    </th>

                    <th class="text-muted small fw-semibold" style="min-width:120px">
                        نسبة الأداء
                    </th>

                    <th class="text-muted small fw-semibold">
                        الحالة
                    </th>

                    <th class="text-start ps-3 text-muted small fw-semibold">
                        الإجراءات
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($finances as $finance)

                @php
                    $jugement = $finance->jugement;
                    $dt       = $jugement?->dossierTribunal;

                    $pct = $finance->montant_condamne > 0
                        ? min(100, round(($finance->montant_paye / $finance->montant_condamne) * 100))
                        : 0;

                    $sp = $finance->statut_paiement ?? '—';

                    $spColor = match($sp) {
                        'Complet' => 'success',
                        'Partiel' => 'warning',
                        default   => 'secondary'
                    };
                @endphp

                <tr>

                    <td class="pe-3">
                        <span class="fw-semibold small">
                            {{ $jugement?->date_jugement?->format('d/m/Y') ?? '—' }}
                        </span>

                        @if($jugement?->est_definitif)
                            <span class="badge bg-success bg-opacity-15 text-white ms-1"
                                  style="font-size:.6rem">
                                نهائي
                            </span>
                        @endif
                    </td>

                    <td>
                        @if($dt?->dossier)

                            <a href="{{ route('dossiers.show', $dt->dossier) }}"
                               class="text-decoration-none fw-semibold text-primary d-block">

                                {{ $dt->dossier->numero_dossier_interne }}

                            </a>

                        @endif

                        <span class="text-muted small">
                            {{ $dt?->tribunal?->nom_tribunal ?? '—' }}
                        </span>
                    </td>

                    <td class="fw-semibold text-center" dir="ltr">
                        {{ number_format($finance->montant_condamne, 2, ',', ' ') }} DH
                    </td>

                    <td class="text-success fw-semibold text-center" dir="ltr">
                        {{ number_format($finance->montant_paye, 2, ',', ' ') }} DH
                    </td>

                    <td class="{{ $finance->montant_restant > 0 ? 'text-danger' : 'text-success' }} fw-semibold text-center" dir="ltr">
                        {{ number_format($finance->montant_restant, 2, ',', ' ') }} DH
                    </td>

                    <td>
                        <div class="d-flex align-items-center gap-2">

                            <div class="flex-grow-1"
                                 style="height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;">

                                <div style="width:{{ $pct }}%;height:100%;
                                            background:{{ $pct >= 100 ? '#16a34a' : ($pct > 0 ? '#d97706' : '#ef4444') }};">
                                </div>

                            </div>

                            <span class="text-muted" style="font-size:.72rem">
                                {{ $pct }}%
                            </span>

                        </div>
                    </td>

                    <td>
                        <span class="badge bg-{{ $spColor }}">
                            {{ $sp }}
                        </span>
                    </td>

                    <td class="text-start ps-3">

                        <div class="d-flex gap-1">

                            <a href="{{ route('finances.show', $finance) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>

                            <a href="{{ route('finances.edit', $finance) }}"
                               class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <x-modal-delete
                                :action="route('finances.destroy', $finance)"
                                modal-id="deleteFinance{{ $finance->id }}"
                                title="حذف السجل المالي"
                                trigger-label=""
                                :description="' الحالة مالية بتاريخ' . $finance->created_at->format('d/m/Y')"
                            />

                        </div>

                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">

                        <i class="bi bi-cash-coin fs-1 d-block mb-2 opacity-25"></i>

                        لا توجد بيانات مالية مسجلة.

                    </td>
                </tr>

                @endforelse

            </tbody>

            @if($finances->count() > 1)

            <tfoot class="table-light fw-semibold small">

                <tr>

                    <td colspan="2" class="pe-3">
                        المجموع
                    </td>

                    <td class="text-center" dir="ltr">
                        {{ number_format($totalCondamne, 2, ',', ' ') }} DH
                    </td>

                    <td class="text-success text-center" dir="ltr">
                        {{ number_format($totalPaye, 2, ',', ' ') }} DH
                    </td>

                    <td class="{{ $totalRestant > 0 ? 'text-danger' : 'text-success' }} text-center" dir="ltr">
                        {{ number_format($totalRestant, 2, ',', ' ') }} DH
                    </td>

                    <td colspan="3"></td>

                </tr>

            </tfoot>

            @endif

        </table>

    </div>

</div>

</div>

@endsection