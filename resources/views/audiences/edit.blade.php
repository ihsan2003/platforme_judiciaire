{{-- resources/views/audiences/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'تعديل الجلسة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('audiences.index') }}">الجلسات</a></li>
    <li class="breadcrumb-item">
        <a href="{{ route('audiences.show', $audience) }}">
            جلسة {{ $audience->date_audience->format('d/m/Y') }}
        </a>
    </li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')

<div class="row justify-content-center" dir="rtl">
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-pencil ms-2 text-warning"></i>
                    تعديل جلسة {{ $audience->date_audience->format('d/m/Y') }}
                </h5>
            </div>

            <div class="card-body p-4">

                @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('audiences.update', $audience) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        {{-- الملف / المحكمة --}}
                        <div class="col-md-6">
                            <label for="id_dossier_tribunal" class="form-label fw-semibold">
                                الملف والمحكمة <span class="text-danger">*</span>
                            </label>

                            <select name="id_dossier_tribunal" id="id_dossier_tribunal"
                                    class="form-select @error('id_dossier_tribunal') is-invalid @enderror"
                                    required>

                                <option value="">— اختر ملفًا —</option>

                                @foreach($dossierTribunaux as $dt)
                                    <option value="{{ $dt->id }}"
                                        @selected(old('id_dossier_tribunal', $audience->id_dossier_tribunal) == $dt->id)>

                                        {{ $dt->dossier?->numero_dossier_interne ?? 'ملف #'.$dt->id_dossier }}
                                        —
                                        {{ $dt->tribunal?->nom_tribunal ?? 'محكمة #'.$dt->id_tribunal }}

                                    </option>
                                @endforeach
                            </select>

                            @error('id_dossier_tribunal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- القاضي --}}
                        <div class="col-md-6">
                            <label for="id_juge" class="form-label fw-semibold">
                                القاضي <span class="text-danger">*</span>
                            </label>

                            <select name="id_juge" id="id_juge"
                                    class="form-select @error('id_juge') is-invalid @enderror"
                                    required>

                                <option value="">— اختر القاضي —</option>

                                @foreach($juges as $juge)
                                    <option value="{{ $juge->id }}"
                                        @selected(old('id_juge', $audience->id_juge) == $juge->id)>

                                        {{ $juge->nom_complet }}

                                        @if($juge->tribunal)
                                            ({{ $juge->tribunal->nom_tribunal }})
                                        @endif

                                    </option>
                                @endforeach
                            </select>

                            @error('id_juge')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        {{-- نوع الجلسة --}}
                        <div class="col-md-6">
                            <label for="id_type_audience" class="form-label fw-semibold">
                                نوع الجلسة <span class="text-danger">*</span>
                            </label>

                            <select name="id_type_audience" id="id_type_audience"
                                    class="form-select @error('id_type_audience') is-invalid @enderror"
                                    required>

                                <option value="">— اختر النوع —</option>

                                @foreach($typesAudience as $type)
                                    <option value="{{ $type->id }}"
                                        @selected(old('id_type_audience', $audience->id_type_audience) == $type->id)>

                                        {{ $type->libelle ?? $type->type_audience }}

                                    </option>
                                @endforeach
                            </select>

                            @error('id_type_audience')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- التواريخ --}}
                    <div class="row g-3 mb-3">

                        <div class="col-md-6">
                            <label for="date_audience" class="form-label fw-semibold">
                                تاريخ الجلسة <span class="text-danger">*</span>
                            </label>

                            <input type="date"
                                   name="date_audience"
                                   id="date_audience"
                                   class="form-control @error('date_audience') is-invalid @enderror"
                                   value="{{ old('date_audience', $audience->date_audience->format('Y-m-d')) }}"
                                   required>

                            @error('date_audience')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="date_prochaine_audience" class="form-label fw-semibold">
                                الجلسة القادمة
                            </label>

                            <input type="date"
                                   name="date_prochaine_audience"
                                   id="date_prochaine_audience"
                                   class="form-control @error('date_prochaine_audience') is-invalid @enderror"
                                   value="{{ old('date_prochaine_audience', $audience->date_prochaine_audience?->format('Y-m-d')) }}">

                            @error('date_prochaine_audience')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    {{-- الحضور --}}
                    <div class="row g-3 mb-3">

                        <div class="col-md-6">
                            <div class="form-check form-switch">

                                <input class="form-check-input"
                                       type="checkbox"
                                       name="presence_demandeur"
                                       id="presence_demandeur"
                                       value="1"
                                       @checked(old('presence_demandeur', $audience->presence_demandeur))>

                                <label class="form-check-label fw-semibold" for="presence_demandeur">
                                    حضور المدعي
                                </label>

                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch">

                                <input class="form-check-input"
                                       type="checkbox"
                                       name="presence_defendeur"
                                       id="presence_defendeur"
                                       value="1"
                                       @checked(old('presence_defendeur', $audience->presence_defendeur))>

                                <label class="form-check-label fw-semibold" for="presence_defendeur">
                                    حضور المدعى عليه
                                </label>

                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch">

                                <input class="form-check-input"
                                       type="checkbox"
                                       name="presence_avocat_institution"
                                       id="presence_avocat_institution"
                                       value="1"
                                       @checked(old('presence_avocat_institution', $audience->presence_avocat_institution))>

                                <label class="form-check-label fw-semibold" for="presence_avocat_institution">
                                    حضور محامي المؤسسة
                                </label>

                            </div>
                        </div>

                    </div>

                    {{-- نتيجة الجلسة --}}
                    <div class="mb-3">
                        <label for="resultat_audience" class="form-label fw-semibold">
                            نتيجة الجلسة
                        </label>

                        <textarea name="resultat_audience"
                                  id="resultat_audience"
                                  class="form-control @error('resultat_audience') is-invalid @enderror"
                                  rows="3">{{ old('resultat_audience', $audience->resultat_audience) }}</textarea>

                        @error('resultat_audience')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- الإجراءات المطلوبة --}}
                    <div class="mb-4">
                        <label for="actions_demandees" class="form-label fw-semibold">
                            الإجراءات المطلوبة
                        </label>

                        <textarea name="actions_demandees"
                                  id="actions_demandees"
                                  class="form-control @error('actions_demandees') is-invalid @enderror"
                                  rows="3">{{ old('actions_demandees', $audience->actions_demandees) }}</textarea>

                        @error('actions_demandees')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- الأزرار --}}
                    <div class="d-flex gap-2 justify-content-end">

                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-lg ms-1"></i>
                            حفظ التعديلات
                        </button>

                        <a href="{{ route('audiences.show', $audience) }}"
                           class="btn btn-outline-secondary">

                            <i class="bi bi-x-lg ms-1"></i>
                            إلغاء
                        </a>

                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

@endsection