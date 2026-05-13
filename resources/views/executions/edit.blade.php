@extends('layouts.app')

@section('title', 'تعديل التنفيذ')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">الرئيسية</a>
    </li>

    <li class="breadcrumb-item">
        <a href="{{ route('executions.index') }}">التنفيذات</a>
    </li>

    <li class="breadcrumb-item">
        <a href="{{ route('executions.show', $execution) }}">التنفيذ</a>
    </li>

    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')

<div dir="rtl">

{{-- HEADER --}}
<div class="d-flex align-items-center justify-content-between mb-4">

    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning ms-2"></i>
            تعديل التنفيذ
        </h4>

        <p class="text-muted small mb-0">
            {{ $execution->numero_dossier_execution }}
            — {{ $execution->jugement->dossierTribunal->tribunal->nom_tribunal ?? '—' }}
        </p>
    </div>

    <a href="{{ route('executions.show', $execution) }}"
       class="btn btn-outline-secondary btn-sm">

        <i class="bi bi-arrow-right ms-1"></i>
        الرجوع إلى البطاقة

    </a>

</div>

<form action="{{ route('executions.update', $execution) }}" method="POST">

@csrf
@method('PUT')

<div class="row g-4">

    {{-- ── MAIN COLUMN ── --}}
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-shield ms-2 text-warning"></i>
                    معلومات التنفيذ
                </h6>
            </div>

            <div class="card-body">

                <div class="row g-3">

                    {{-- Numéro --}}
                    <div class="col-md-6">

                        <label class="form-label fw-semibold small">
                            الرقم
                        </label>

                        <div class="form-control bg-light text-muted">
                            {{ $execution->numero_dossier_execution }}
                        </div>

                    </div>

                    {{-- Jugement --}}
                    <div class="col-md-6">

                        <label class="form-label fw-semibold small">
                            الحكم
                        </label>

                        <div class="form-control bg-light text-muted">

                            #{{ $execution->jugement->id }}

                            · {{ $execution->jugement->date_jugement->format('d/m/Y') }}

                            · {{ $execution->jugement->dossierTribunal->tribunal->nom_tribunal ?? '—' }}

                        </div>

                    </div>

                    {{-- Date notification --}}
                    <div class="col-md-6">

                        <label class="form-label fw-semibold small">
                            تاريخ التبليغ
                            <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               name="date_notification"
                               class="form-control @error('date_notification') is-invalid @enderror"
                               value="{{ old('date_notification', $execution->date_notification?->format('Y-m-d')) }}"
                               required>

                        @error('date_notification')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- Date exécution --}}
                    <div class="col-md-6">

                        <label class="form-label fw-semibold small">
                            تاريخ التنفيذ
                        </label>

                        <input type="date"
                               name="date_execution"
                               class="form-control @error('date_execution') is-invalid @enderror"
                               value="{{ old('date_execution', optional($execution->date_execution)->format('Y-m-d')) }}">

                        @error('date_execution')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- Statut --}}
                    <div class="col-md-6">

                        <label class="form-label fw-semibold small">
                            الحالة
                            <span class="text-danger">*</span>
                        </label>

                        <select name="statut_execution"
                                class="form-select @error('statut_execution') is-invalid @enderror"
                                required>

                            @foreach($statuts as $s)

                                <option value="{{ $s->id }}"
                                    @selected($execution->statut_execution == $s->id)>

                                    {{ $s->statut_execution }}

                                </option>

                            @endforeach

                        </select>

                        @error('statut_execution')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- Responsable --}}
                    <div class="col-md-6">

                        <label class="form-label fw-semibold small">
                            المسؤول
                        </label>

                        <div class="form-control bg-light text-muted">
                            {{ $execution->responsable->name ?? '—' }}
                        </div>

                    </div>

                    {{-- Observations --}}
                    <div class="col-12">

                        <label class="form-label fw-semibold small">
                            ملاحظات
                        </label>

                        <textarea name="observations"
                                  rows="4"
                                  class="form-control"
                                  placeholder="ملاحظات داخلية...">{{ old('observations', $execution->observations) }}</textarea>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- ── SIDEBAR ── --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle ms-2 text-muted"></i>
                    ملخص
                </h6>
            </div>

            <div class="card-body small">

                <dl class="row mb-0">

                    <dt class="col-6 text-muted fw-normal">
                        الحالة
                    </dt>

                    <dd class="col-6">
                        {{ $execution->statut?->statut_execution ?? '—' }}
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
                        {{ $execution->date_execution?->format('d/m/Y') ?? 'في الانتظار' }}
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

        @if($execution->date_execution)

            <div class="alert alert-success border-0 small">

                <i class="bi bi-check-circle ms-2"></i>

                تم الانتهاء من هذا التنفيذ.

            </div>

        @endif

    </div>

</div>

{{-- ACTIONS --}}
<div class="d-flex gap-2 justify-content-end mt-2">

    <a href="{{ route('executions.show', $execution) }}"
       class="btn btn-outline-secondary">

        إلغاء

    </a>

    <button type="submit" class="btn btn-warning px-4">

        <i class="bi bi-check-lg ms-1"></i>

        حفظ

    </button>

</div>

</form>

</div>

@endsection