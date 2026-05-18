@extends('layouts.app')

@section('title', 'تنفيذ جديد')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">الرئيسية</a>
    </li>

    <li class="breadcrumb-item">
        <a href="{{ route('executions.index') }}">التنفيذات</a>
    </li>

    <li class="breadcrumb-item active">
        إنشاء
    </li>
@endsection

@section('content')

<div dir="rtl">

<div class="row justify-content-center">
    <div class="col-lg-6">

        <form action="{{ route('executions.store') }}" method="POST">
            @csrf

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-shield-check ms-2 text-primary"></i>
                        تنفيذ جديد
                    </h6>
                </div>

                <div class="card-body">

                    {{-- Jugement --}}
                    <div class="mb-3">

                        <label class="form-label fw-semibold small">
                            الحكم
                            <span class="text-danger">*</span>
                        </label>

                        @if($selectedJugement)

                            <div class="form-control form-control-sm bg-light">
                                #{{ $selectedJugement->id }}
                                — {{ $selectedJugement->date_jugement->format('d/m/Y') }}
                                · {{ $selectedJugement->dossierTribunal->tribunal->nom_tribunal ?? '' }}
                            </div>

                            <input type="hidden" name="id_jugement" value="{{ $selectedJugement->id }}">

                        @else

                            <div class="text-danger small">
                                Aucun jugement sélectionné
                            </div>

                        @endif

                    </div>

                    <div class="row">
                        {{-- Date notification --}}
                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-semibold small">
                                تاريخ التبليغ
                                <span class="text-danger">*</span>
                            </label>

                            <input type="date"
                                name="date_notification"
                                class="form-control form-control-sm @error('date_notification') is-invalid @enderror"
                                value="{{ date('Y-m-d') }}"
                                required>

                            @error('date_notification')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Date exécution --}}
                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-semibold small">
                                تاريخ التنفيذ
                            </label>

                            <input type="date"
                                name="date_execution"
                                class="form-control form-control-sm @error('date_execution') is-invalid @enderror">

                            @error('date_execution')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>
                    </div>

                    {{-- Observations --}}
                    <div class="mb-3">

                        <label class="form-label fw-semibold small">
                            ملاحظات
                        </label>

                        <textarea name="observations"
                                  rows="3"
                                  class="form-control form-control-sm"
                                  placeholder="ملاحظات داخلية..."></textarea>

                    </div>

                </div>

            </div>

            {{-- ACTIONS --}}
            <div class="d-flex justify-content-between">

                <a href="{{ route('executions.index') }}"
                   class="btn btn-outline-secondary btn-sm">

                    <i class="bi bi-arrow-right ms-1"></i>

                    رجوع

                </a>

                <button type="submit"
                        class="btn btn-primary btn-sm"
                        onclick="return confirm('هل تؤكد إنشاء التنفيذ؟')">

                    <i class="bi bi-check-circle ms-1"></i>

                    حفظ

                </button>

            </div>

        </form>

    </div>

</div>

</div>

@endsection