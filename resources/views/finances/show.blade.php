{{-- resources/views/finances/show.blade.php --}}
@extends('layouts.app')

@section('title', 'المالية — حكم بتاريخ ' . ($finance->jugement?->date_jugement?->format('d/m/Y') ?? '—'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('finances.index') }}">المالية</a></li>
    <li class="breadcrumb-item active">تفاصيل المالية</li>
@endsection

@section('content')

@php
    $jugement = $finance->jugement;
    $dt       = $jugement?->dossierTribunal;
    $dossier  = $dt?->dossier;

    $condamne = $finance->montant_condamne ?? 0;
    $paye     = $finance->montant_paye ?? 0;
    $restant  = $finance->montant_restant ?? 0;
    $pct      = $condamne > 0 ? min(100, round(($paye / $condamne) * 100)) : 0;
    $pctColor = $pct >= 100 ? 'success' : ($pct > 0 ? 'warning' : 'danger');

    $sp       = $finance->statut_paiement ?? '—';
    $spColor  = match($sp) { 'Complet' => 'success', 'Partiel' => 'warning', default => 'secondary' };
@endphp

{{-- ══ العنوان الرئيسي ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">

            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success bg-opacity-10 d-flex align-items-center justify-content-center"
                     style="width:56px;height:56px">
                    <i class="bi bi-cash-stack fs-3 text-success"></i>
                </div>

                <div>
                    <h4 class="fw-bold mb-1">
                        المالية — حكم بتاريخ {{ $jugement?->date_jugement?->format('d/m/Y') ?? '—' }}
                    </h4>

                    @if($dossier)
                        <a href="{{ route('dossiers.show', $dossier) }}"
                           class="text-muted small text-decoration-none">
                            <i class="bi bi-folder2-open me-1"></i>
                            {{ $dossier->numero_dossier_interne }}
                        </a>
                    @endif

                    <div class="mt-1">
                        <span class="badge bg-{{ $spColor }}">
                            {{ $sp === 'Complet' ? 'مكتمل' : ($sp === 'Partiel' ? 'جزئي' : '—') }}
                        </span>

                        @if($jugement?->est_definitif)
                            <span class="badge bg-success bg-opacity-15 text-white border border-success border-opacity-25 ms-1">
                                <i class="bi bi-check-circle me-1"></i>
                                حكم نهائي
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
    
                <x-modal-delete
                    :action="route('finances.destroy', $finance)"
                    modal-id="deleteFinance{{ $finance->id }}"
                    title="حذف العملية المالية"
                    :description="'مالية بتاريخ ' . $finance->created_at->format('d/m/Y')"
                />

                <a href="{{ route('finances.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>رجوع
                </a>
            </div>
        </div>

        <hr class="my-3">

        <div class="row g-2 small text-muted">
            <div class="col-sm-3">
                <i class="bi bi-bank me-1"></i>
                <strong>المحكمة :</strong> {{ $dt?->tribunal?->nom_tribunal ?? '—' }}
            </div>

            <div class="col-sm-3">
                <i class="bi bi-layers me-1"></i>
                <strong>الدرجة :</strong> {{ $dt?->degre?->degre_juridiction ?? '—' }}
            </div>

            <div class="col-sm-3">
                <i class="bi bi-person-workspace me-1"></i>
                <strong>القاضي :</strong> {{ $jugement?->juge?->nom_complet ?? '—' }}
            </div>

            <div class="col-sm-3">
                <i class="bi bi-clock me-1"></i>
                <strong>آخر تحديث :</strong> {{ $finance->updated_at->diffForHumans() }}
            </div>
        </div>
    </div>
</div>

{{-- ══ المحتوى ══ --}}
<div class="row g-4">

    {{-- العمود الرئيسي --}}
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-bar-chart me-2 text-primary"></i>تفاصيل مالية
                </h6>
            </div>

            <div class="card-body">

                {{-- نسبة التحصيل --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold small">نسبة التحصيل</span>
                        <span class="fw-bold text-{{ $pctColor }}">{{ $pct }}%</span>
                    </div>

                    <div style="height:12px;background:#e2e8f0;border-radius:6px;overflow:hidden;">
                        <div style="width:{{ $pct }}%;height:100%;border-radius:6px;
                            background:{{ $pct >= 100 ? '#16a34a' : ($pct > 50 ? '#d97706' : '#ef4444') }}">
                        </div>
                    </div>
                </div>

                <div class="row g-3">

                    {{-- المحكوم به --}}
                    <div class="col-sm-4">
                        <div class="p-3 rounded border text-center h-100">
                            <div class="text-muted small fw-semibold mb-1">المحكوم به</div>
                            <div class="fw-bold fs-5">{{ number_format($condamne, 2, '.', ',') }}</div>
                            <div class="text-muted small">درهم</div>
                        </div>
                    </div>

                    {{-- المدفوع --}}
                    <div class="col-sm-4">
                        <div class="p-3 rounded border text-center h-100"
                             style="border-color:#a7f3d0!important;background:#f0fdf4">
                            <div class="text-success small fw-semibold mb-1">المبلغ المدفوع</div>
                            <div class="fw-bold fs-5 text-success">{{ number_format($paye, 2, '.', ',') }}</div>
                            <div class="text-muted small">درهم</div>
                        </div>
                    </div>

                    {{-- المتبقي --}}
                    <div class="col-sm-4">
                        <div class="p-3 rounded border text-center h-100"
                             style="{{ $restant > 0 ? 'border-color:#fca5a5!important;background:#fff5f5' : 'border-color:#a7f3d0!important;background:#f0fdf4' }}">
                            <div class="{{ $restant > 0 ? 'text-danger' : 'text-success' }} small fw-semibold mb-1">
                                المبلغ المتبقي
                            </div>
                            <div class="fw-bold fs-5 {{ $restant > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($restant, 2, '.', ',') }}
                            </div>
                            <div class="text-muted small">درهم</div>
                        </div>
                    </div>

                    {{-- المطالبات --}}
                    @if($finance->montant_reclame_demandeur || $finance->montant_reclame_defendeur)
                    <div class="col-sm-6">
                        <div class="p-3 rounded border h-100">
                            <div class="text-muted small fw-semibold mb-1">مبلغ المطالب به (المدعي)</div>
                            <div class="fw-semibold">
                                {{ $finance->montant_reclame_demandeur ? number_format($finance->montant_reclame_demandeur, 2, '.', ',').' درهم' : '—' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="p-3 rounded border h-100">
                            <div class="text-muted small fw-semibold mb-1">مبلغ المطالب به (المدعى عليه)</div>
                            <div class="fw-semibold">
                                {{ $finance->montant_reclame_defendeur ? number_format($finance->montant_reclame_defendeur, 2, '.', ',').' درهم' : '—' }}
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- تاريخ الدفع --}}
                    @if($finance->date_paiement)
                    <div class="col-12">
                        <div class="p-3 rounded border">
                            <div class="text-muted small fw-semibold mb-1">تاريخ الدفع</div>
                            <span class="fw-semibold text-success">
                                <i class="bi bi-calendar-check me-1"></i>
                                {{ $finance->date_paiement->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- الحكم --}}
        @if($jugement)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-gavel me-2 text-primary"></i>الحكم المرتبط
                </h6>
                <a href="{{ route('jugements.show', $jugement) }}" class="btn btn-sm btn-outline-primary">
                    عرض
                </a>
            </div>

            <div class="card-body small">
                <div class="row g-3">

                    <div class="col-sm-4">
                        <div class="text-muted mb-1">التاريخ</div>
                        <div class="fw-semibold">{{ $jugement->date_jugement?->format('d/m/Y') ?? '—' }}</div>
                    </div>

                    <div class="col-sm-4">
                        <div class="text-muted mb-1">القاضي</div>
                        <div class="fw-semibold">{{ $jugement->juge?->nom_complet ?? '—' }}</div>
                    </div>

                    <div class="col-sm-4">
                        <div class="text-muted mb-1">النوع</div>
                        {{ $jugement->est_definitif ? 'نهائي' : 'غير نهائي' }}
                    </div>

                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- العمود الجانبي --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">ملخص</h6>
            </div>

            <div class="card-body small">
                <dl class="row mb-0">

                    <dt class="col-6 text-muted">حالة الدفع</dt>
                    <dd class="col-6"><span class="badge bg-{{ $spColor }}">{{ $sp }}</span></dd>

                    <dt class="col-6 text-muted">تاريخ الدفع</dt>
                    <dd class="col-6">{{ $finance->date_paiement?->format('d/m/Y') ?? '—' }}</dd>

                    <dt class="col-6 text-muted">تاريخ الإنشاء</dt>
                    <dd class="col-6">{{ $finance->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted">آخر تعديل</dt>
                    <dd class="col-6">{{ $finance->updated_at->format('d/m/Y') }}</dd>

                </dl>
            </div>
        </div>

    </div>
</div>

@endsection