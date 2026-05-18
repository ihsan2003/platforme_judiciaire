{{-- resources/views/finances/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'تعديل الملف المالي')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('finances.index') }}">الملفات المالية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('finances.show', $finance) }}">تفاصيل الملف المالي</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')

@php
    $jugement = $finance->jugement;
    $dt       = $jugement?->dossierTribunal;
    $dossier  = $dt?->dossier;
@endphp

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning me-2"></i>تعديل الملف المالي
        </h4>
        <p class="text-muted small mb-0">
            حكم بتاريخ {{ $jugement?->date_jugement?->format('d/m/Y') ?? '—' }}
            @if($dossier)
                — <a href="{{ route('dossiers.show', $dossier) }}" class="text-decoration-none">
                    {{ $dossier->numero_dossier_interne }}
                </a>
            @endif
        </p>
    </div>

    <a href="{{ route('finances.show', $finance) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>العودة إلى الملف
    </a>
</div>

<form action="{{ route('finances.update', $finance) }}" method="POST">
@csrf @method('PUT')

<div class="row g-4">

    {{-- ── العمود الرئيسي ── --}}
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-cash-stack me-2 text-warning"></i>المعلومات المالية
                </h6>
            </div>

            <div class="card-body">

                <div class="row g-3">

                    {{-- الحكم (قراءة فقط) --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">الحكم</label>

                        <div class="form-control bg-light text-muted">
                            حكم بتاريخ {{ $jugement?->date_jugement?->format('d/m/Y') ?? '—' }}
                            · {{ $dt?->dossier?->numero_dossier_interne ?? '—' }}
                            · {{ $dt?->tribunal?->nom_tribunal ?? '—' }}
                        </div>

                        <input type="hidden" name="id_jugement" value="{{ $finance->id_jugement }}">
                    </div>

                    {{-- المبالغ المطلوبة (المدعي) --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">المبلغ المطلوب (المدعي)</label>

                        <div class="input-group">
                            <input type="number" step="0.01" min="0"
                                   name="montant_reclame_demandeur"
                                   class="form-control @error('montant_reclame_demandeur') is-invalid @enderror"
                                   value="{{ old('montant_reclame_demandeur', $finance->montant_reclame_demandeur) }}"
                                   placeholder="0.00">
                            <span class="input-group-text">درهم</span>
                        </div>

                        @error('montant_reclame_demandeur')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- المبالغ المطلوبة (المدعى عليه) --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">المبلغ المطلوب (المدعى عليه)</label>

                        <div class="input-group">
                            <input type="number" step="0.01" min="0"
                                   name="montant_reclame_defendeur"
                                   class="form-control @error('montant_reclame_defendeur') is-invalid @enderror"
                                   value="{{ old('montant_reclame_defendeur', $finance->montant_reclame_defendeur) }}"
                                   placeholder="0.00">
                            <span class="input-group-text">درهم</span>
                        </div>

                        @error('montant_reclame_defendeur')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- المبلغ المحكوم به --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">
                            المبلغ المحكوم به <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <input type="number" step="0.01" min="0"
                                   name="montant_condamne"
                                   class="form-control @error('montant_condamne') is-invalid @enderror"
                                   value="{{ old('montant_condamne', $finance->montant_condamne) }}"
                                   placeholder="0.00"
                                   required>
                            <span class="input-group-text">درهم</span>
                        </div>

                        @error('montant_condamne')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- المبلغ المدفوع --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">المبلغ المدفوع</label>

                        <div class="input-group">
                            <input type="number" step="0.01" min="0"
                                   name="montant_paye"
                                   class="form-control @error('montant_paye') is-invalid @enderror"
                                   value="{{ old('montant_paye', $finance->montant_paye) }}"
                                   placeholder="0.00">
                            <span class="input-group-text">درهم</span>
                        </div>

                        @error('montant_paye')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- تاريخ الدفع --}}
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold small">تاريخ الدفع</label>

                        <input type="date"
                               name="date_paiement"
                               class="form-control @error('date_paiement') is-invalid @enderror"
                               value="{{ old('date_paiement', $finance->date_paiement?->format('Y-m-d')) }}">

                        @error('date_paiement')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- ── العمود الجانبي ── --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-muted"></i>ملخص الحالة
                </h6>
            </div>

            <div class="card-body small">

                @php
                    $condActuel = $finance->montant_condamne ?? 0;
                    $payeActuel = $finance->montant_paye ?? 0;
                    $pctActuel  = $condActuel > 0 ? min(100, round(($payeActuel / $condActuel) * 100)) : 0;
                    $spActuel   = $finance->statut_paiement ?? '—';
                    $spColor    = match($spActuel) { 'Complet' => 'success', 'Partiel' => 'warning', default => 'secondary' };
                @endphp

                <dl class="row mb-2">

                    <dt class="col-6 text-muted fw-normal">المحكوم به</dt>
                    <dd class="col-6 fw-semibold">{{ number_format($condActuel, 2, '.', ',') }} درهم</dd>

                    <dt class="col-6 text-muted fw-normal">المدفوع</dt>
                    <dd class="col-6 fw-semibold text-success">{{ number_format($payeActuel, 2, '.', ',') }} درهم</dd>

                    <dt class="col-6 text-muted fw-normal">المتبقي</dt>
                    <dd class="col-6 fw-semibold {{ $finance->montant_restant > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($finance->montant_restant, 2, '.', ',') }} درهم
                    </dd>

                    <dt class="col-6 text-muted fw-normal">الحالة</dt>
                    <dd class="col-6">
                        <span class="badge bg-{{ $spColor }}">{{ $spActuel }}</span>
                    </dd>

                </dl>

                <div style="height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;">
                    <div style="width:{{ $pctActuel }}%;height:100%;
                        background:{{ $pctActuel >= 100 ? '#16a34a' : ($pctActuel > 0 ? '#d97706' : '#ef4444') }}">
                    </div>
                </div>

                <div class="text-muted mt-1" style="font-size:.72rem">
                    {{ $pctActuel }}% تم التحصيل
                </div>

            </div>
        </div>

        <div class="alert alert-info border-0 small">
            <i class="bi bi-info-circle me-2"></i>
            يتم تحديث حالة الدفع تلقائياً عند الحفظ.
        </div>

    </div>
</div>

<div class="d-flex gap-2 justify-content-end mt-2">

    <a href="{{ route('finances.show', $finance) }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>إلغاء
    </a>

    <button type="submit" class="btn btn-warning px-4">
        <i class="bi bi-check-lg me-1"></i>حفظ التعديلات
    </button>

</div>

</form>

@endsection