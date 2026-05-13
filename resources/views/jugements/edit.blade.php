{{-- resources/views/jugements/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'تعديل الحكم بتاريخ ' . $jugement->date_jugement->format('d/m/Y'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">الرئيسية</a>
    </li>

    <li class="breadcrumb-item">
        <a href="{{ route('jugements.index') }}">الأحكام</a>
    </li>

    <li class="breadcrumb-item">
        <a href="{{ route('jugements.show', $jugement) }}">
            الحكم #{{ $jugement->id }}
        </a>
    </li>

    <li class="breadcrumb-item active">
        تعديل
    </li>
@endsection

@section('content')

@php
    $dt = $jugement->dossierTribunal;
    $audienceHoukm = $dt?->audienceHoukm();
    $dateHoukm = $audienceHoukm?->date_audience?->format('Y-m-d');
@endphp

<div class="d-flex align-items-center justify-content-between mb-4" dir="rtl">

    <div>

        <h4 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning ms-2"></i>
            تعديل الحكم
        </h4>

        <p class="text-muted small mb-0">

            بتاريخ {{ $jugement->date_jugement->format('d/m/Y') }}

            — {{ $dt?->tribunal?->nom_tribunal ?? '—' }}

            ({{ $dt?->degre?->degre_juridiction ?? '—' }})

        </p>

    </div>

    <a href="{{ route('jugements.show', $jugement) }}"
       class="btn btn-outline-secondary btn-sm">

        <i class="bi bi-arrow-right ms-1"></i>
        العودة إلى التفاصيل

    </a>

</div>

@if($jugement->est_definitif)

    <div class="alert alert-warning mb-4" dir="rtl">

        <i class="bi bi-exclamation-triangle ms-2"></i>

        هذا الحكم <strong>نهائي</strong>.
        يمكن فقط تعديل منطوق الحكم.

    </div>

@endif

<form action="{{ route('jugements.update', $jugement) }}"
      method="POST">

@csrf
@method('PUT')

<div class="row g-4" dir="rtl">

    {{-- العمود الرئيسي --}}
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h6 class="mb-0 fw-semibold">

                    <i class="bi bi-hammer ms-2 text-warning"></i>
                    معلومات الحكم

                </h6>

            </div>

            <div class="card-body">

                <div class="row g-3">

                    {{-- الملف القضائي --}}
                    <div class="col-12">

                        <label class="form-label fw-semibold small">
                            الملف القضائي
                        </label>

                        <div class="form-control bg-light text-muted">

                            {{ $dt?->dossier?->numero_dossier_interne ?? '—' }}

                            ·

                            {{ $dt?->tribunal?->nom_tribunal ?? '—' }}

                            ({{ $dt?->degre?->degre_juridiction ?? '—' }})

                        </div>

                        <div class="form-text">
                            لا يمكن تغيير الملف بعد إنشاء الحكم.
                        </div>

                    </div>

                    {{-- القاضي --}}
                    <div class="col-md-6">

                        <label class="form-label fw-semibold small">

                            القاضي
                            <span class="text-danger">*</span>

                        </label>

                        <select name="id_juge"
                                class="form-select @error('id_juge') is-invalid @enderror"
                                required>

                            <option value="">
                                — اختر —
                            </option>

                            @foreach($juges as $juge)

                                <option value="{{ $juge->id }}"
                                    @selected(old('id_juge', $jugement->id_juge) == $juge->id)>

                                    {{ $juge->nom_complet }}

                                    @if($juge->tribunal)
                                        ({{ $juge->tribunal->nom_tribunal }})
                                    @endif

                                </option>

                            @endforeach

                        </select>

                        @error('id_juge')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- تاريخ الحكم --}}
                    <div class="col-md-6">

                        <label class="form-label fw-semibold small">

                            تاريخ الحكم
                            <span class="text-danger">*</span>

                        </label>

                        <input type="date"
                               name="date_jugement"
                               class="form-control @error('date_jugement') is-invalid @enderror"
                               value="{{ old('date_jugement', $jugement->date_jugement->format('Y-m-d')) }}"
                               {{ $dateHoukm ? 'readonly' : '' }}
                               required>

                        @error('date_jugement')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- نهائي --}}
                    <div class="col-12">

                        <div class="form-check form-switch">

                            <input class="form-check-input"
                                   type="checkbox"
                                   name="est_definitif"
                                   value="1"
                                   id="est_definitif"
                                   @checked(old('est_definitif', $jugement->est_definitif))
                                   @disabled($jugement->est_definitif && $jugement->executions()->exists())>

                            <label class="form-check-label"
                                   for="est_definitif">

                                حكم نهائي

                            </label>

                        </div>

                        @if($jugement->est_definitif && $jugement->executions()->exists())

                            <div class="form-text text-warning">

                                <i class="bi bi-lock ms-1"></i>

                                هذا الحكم مرتبط بتنفيذ،
                                لذلك لا يمكن تعديل صفته النهائية.

                            </div>

                        @endif

                    </div>

                    {{-- منطوق الحكم --}}
                    <div class="col-12">

                        <label class="form-label fw-semibold small">
                            منطوق الحكم
                        </label>

                        <textarea name="contenu_dispositif"
                                  class="form-control @error('contenu_dispositif') is-invalid @enderror"
                                  rows="6"
                                  placeholder="محتوى الحكم...">{{ old('contenu_dispositif', $jugement->contenu_dispositif) }}</textarea>

                        @error('contenu_dispositif')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>

        </div>

        {{-- الأطراف --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h6 class="mb-0 fw-semibold">

                    <i class="bi bi-people ms-2 text-primary"></i>

                    الأطراف والمبالغ المحكوم بها

                </h6>

            </div>

            <div class="card-body">

                @if($parties->isEmpty())

                    <div class="text-center py-4 text-muted small">

                        <i class="bi bi-people fs-2 d-block mb-2 opacity-25"></i>

                        لا توجد أطراف متاحة.

                    </div>

                @else

                <div class="row g-3">

                    @foreach($parties as $partie)

                    @php
                        $isLinked  = in_array($partie->id, $partiesLiees);
                        $montantPivot = $jugement->parties->find($partie->id)?->pivot->montant_condamne;
                    @endphp

                    <div class="col-md-6">

                        <div class="border rounded p-3 {{ $isLinked ? 'border-primary bg-primary bg-opacity-5' : '' }}">

                            <div class="form-check mb-2">

                                <input class="form-check-input"
                                       type="checkbox"
                                       name="parties[]"
                                       value="{{ $partie->id }}"
                                       id="partie_{{ $partie->id }}"
                                       @checked($isLinked)>

                                <label class="form-check-label small fw-semibold"
                                       for="partie_{{ $partie->id }}">

                                    {{ $partie->nom_partie }}

                                    <span class="text-muted font-monospace ms-1"
                                          style="font-size:.7rem">

                                        ({{ $partie->identifiant_unique }})

                                    </span>

                                </label>

                            </div>

                            <div class="input-group input-group-sm">

                                <span class="input-group-text">
                                    درهم
                                </span>

                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       name="montants[{{ $partie->id }}]"
                                       class="form-control"
                                       value="{{ old('montants.'.$partie->id, $montantPivot) }}"
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

    {{-- العمود الجانبي --}}
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
                        الملف
                    </dt>

                    <dd class="col-6 fw-semibold">
                        {{ $dt?->dossier?->numero_dossier_interne ?? '—' }}
                    </dd>

                    <dt class="col-6 text-muted fw-normal">
                        المحكمة
                    </dt>

                    <dd class="col-6">
                        {{ $dt?->tribunal?->nom_tribunal ?? '—' }}
                    </dd>

                    <dt class="col-6 text-muted fw-normal">
                        الدرجة
                    </dt>

                    <dd class="col-6">
                        {{ $dt?->degre?->degre_juridiction ?? '—' }}
                    </dd>

                    <dt class="col-6 text-muted fw-normal">
                        جلسة الحكم
                    </dt>

                    <dd class="col-6">

                        @if($audienceHoukm)

                            <span class="text-warning fw-semibold">

                                {{ $audienceHoukm->date_audience->format('d/m/Y') }}

                            </span>

                        @else

                            <span class="text-muted">—</span>

                        @endif

                    </dd>

                    <dt class="col-6 text-muted fw-normal">
                        الطعون
                    </dt>

                    <dd class="col-6">

                        <span class="badge bg-{{ $jugement->recours->isEmpty() ? 'secondary' : 'warning text-dark' }}">

                            {{ $jugement->recours->count() }}

                        </span>

                    </dd>

                    <dt class="col-6 text-muted fw-normal">
                        التنفيذات
                    </dt>

                    <dd class="col-6">

                        <span class="badge bg-{{ $jugement->executions->isEmpty() ? 'secondary' : 'info' }}">

                            {{ $jugement->executions->count() }}

                        </span>

                    </dd>

                </dl>

            </div>

        </div>

        @if($jugement->recours->isNotEmpty())

            <div class="alert alert-warning border-0 small" dir="rtl">

                <i class="bi bi-exclamation-triangle ms-2"></i>

                توجد طعون على هذا الحكم.
                يرجى الحذر أثناء التعديل.

            </div>

        @endif

        @if($jugement->executions->isNotEmpty())

            <div class="alert alert-danger border-0 small" dir="rtl">

                <i class="bi bi-shield-check ms-2"></i>

                هذا الحكم مرتبط بتنفيذ جارٍ.
                التعديلات محدودة.

            </div>

        @endif

    </div>

</div>

{{-- الإجراءات --}}
<div class="d-flex gap-2 justify-content-end mt-2" dir="rtl">

    <a href="{{ route('jugements.show', $jugement) }}"
       class="btn btn-outline-secondary">

        <i class="bi bi-x-lg ms-1"></i>
        إلغاء

    </a>

    <button type="submit"
            class="btn btn-warning px-4">

        <i class="bi bi-check-lg ms-1"></i>
        حفظ التعديلات

    </button>

</div>

</form>

@endsection