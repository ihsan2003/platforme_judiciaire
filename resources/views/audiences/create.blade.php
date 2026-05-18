{{-- resources/views/audiences/create.blade.php --}}
@extends('layouts.app')

@section('title', 'جلسة جديدة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('audiences.index') }}">الجلسات</a></li>
    <li class="breadcrumb-item active">جلسة جديدة</li>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-calendar-plus me-2 text-primary"></i>إنشاء جلسة
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

                <form method="POST" action="{{ route('audiences.store') }}">
                    @csrf

                    <div class="row g-3 mb-3">
                        {{-- الملف / المحكمة --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                الملف والمحكمة <span class="text-danger">*</span>
                            </label>
                            <select name="id_dossier_tribunal"
                                    id="id_dossier_tribunal"
                                    class="form-select @error('id_dossier_tribunal') is-invalid @enderror"
                                    required>
                                <option value="">— اختر ملفًا —</option>
                                @foreach($dossierTribunaux as $dt)
                                    <option value="{{ $dt->id }}"
                                            data-tribunal-id="{{ $dt->id_tribunal }}"
                                            @selected(old('id_dossier_tribunal',
                                                $dossierTribunaux->count() === 1 ? $dt->id : null) == $dt->id)>
                                        {{ $dt->dossier?->numero_dossier_interne ?? 'ملف #'.$dt->id_dossier }}
                                        — {{ $dt->tribunal?->nom_tribunal ?? 'محكمة #'.$dt->id_tribunal }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- القاضي --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                القاضي <span class="text-danger">*</span>
                            </label>

                            <select name="id_juge" id="id_juge"
                                    class="form-select @error('id_juge') is-invalid @enderror"
                                    required>
                                <option value="">— اختر المحكمة أولاً —</option>
                                @foreach($juges as $juge)
                                    <option value="{{ $juge->id }}" @selected(old('id_juge') == $juge->id)>
                                        {{ $juge->grade ? $juge->grade.' ' : '' }}{{ $juge->nom_complet }}
                                    </option>
                                @endforeach
                            </select>

                            <div id="juge_hint" class="form-text text-info d-none">
                                <i class="bi bi-info-circle me-1"></i>
                                القائمة يتم تصفيتها حسب المحكمة المختارة.
                            </div>
                            <div id="juge_aucun" class="form-text text-warning d-none">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                لا يوجد قضاة مسجلون لهذه المحكمة.
                            </div>
                        </div>
                    </div>


                    <div class="row g-3 mb-3">
                        {{-- النوع --}}
                        <div class="col-md-6">
                            <label for="id_type_audience" class="form-label fw-semibold">
                                نوع الجلسة <span class="text-danger">*</span>
                            </label>
                            <select name="id_type_audience" id="id_type_audience"
                                    class="form-select @error('id_type_audience') is-invalid @enderror"
                                    required>
                                <option value="">— اختر —</option>
                                @foreach($typesAudience as $type)
                                    <option value="{{ $type->id }}" @selected(old('id_type_audience') == $type->id)>
                                        {{ $type->libelle ?? $type->type_audience }}
                                    </option>
                                @endforeach
                            </select>
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
                                   value="{{ old('date_audience', $dateAudienceParDefaut ?? date('Y-m-d')) }}"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label for="date_prochaine_audience" class="form-label fw-semibold">
                                الجلسة القادمة
                            </label>
                            <input type="date"
                                   name="date_prochaine_audience"
                                   id="date_prochaine_audience"
                                   class="form-control @error('date_prochaine_audience') is-invalid @enderror"
                                   value="{{ old('date_prochaine_audience') }}">
                        </div>
                    </div>

                    {{-- الحضور --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="presence_demandeur"
                                       value="1"
                                       @checked(old('presence_demandeur'))>
                                <label class="form-check-label">
                                    حضور المدعي
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="presence_defendeur"
                                       value="1"
                                       @checked(old('presence_defendeur'))>
                                <label class="form-check-label">
                                    حضور المدعى عليه
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- النتيجة --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">النتيجة</label>
                        <textarea name="resultat_audience"
                                  class="form-control"
                                  rows="3">{{ old('resultat_audience') }}</textarea>
                    </div>

                    {{-- الإجراءات --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">الإجراءات المطلوبة</label>
                        <textarea name="actions_demandees"
                                  class="form-control"
                                  rows="3">{{ old('actions_demandees') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>حفظ
                        </button>
                        <a href="{{ route('audiences.index') }}" class="btn btn-outline-secondary">
                            إلغاء
                        </a>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('id_dossier_tribunal')
    ?.addEventListener('change', async function () {
        const tribunalId = this.options[this.selectedIndex]?.dataset?.tribunalId;
        const jugeSelect = document.getElementById('id_juge');
        const hint       = document.getElementById('juge_hint');
        const aucun      = document.getElementById('juge_aucun');

        if (!tribunalId) {
            jugeSelect.innerHTML = '<option value="">— اختر المحكمة أولاً —</option>';
            hint.classList.add('d-none');
            aucun.classList.add('d-none');
            return;
        }

        jugeSelect.innerHTML = '<option value="">— جار التحميل… —</option>';
        jugeSelect.disabled  = true;

        try {
            const res   = await fetch(`/api/tribunaux/${tribunalId}/juges`);
            const juges = await res.json();

            jugeSelect.innerHTML = '<option value="">— اختر قاضيًا —</option>';

            if (juges.length === 0) {
                aucun.classList.remove('d-none');
                hint.classList.add('d-none');
            } else {
                juges.forEach(j => {
                    const opt   = document.createElement('option');
                    opt.value   = j.id;
                    opt.textContent = (j.grade ? j.grade + ' ' : '') + j.nom_complet;
                    jugeSelect.appendChild(opt);
                });
                hint.classList.remove('d-none');
                aucun.classList.add('d-none');
            }

            jugeSelect.disabled = false;

        } catch (e) {
            jugeSelect.innerHTML = '<option value="">— خطأ في التحميل —</option>';
            jugeSelect.disabled  = false;
        }
    });

window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('id_dossier_tribunal');
    if (sel?.value) sel.dispatchEvent(new Event('change'));
});
</script>
@endpush