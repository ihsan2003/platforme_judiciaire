{{-- resources/views/avertissements/_jugement_fields.blade.php
     Champs d'un jugement d'avertissement. $j = AvertissementJugement|null --}}
@php
    $idTypeDefaut = \App\Models\TypeJugement::idAuFond();
    $isNew = ! $j;
@endphp

<div class="row g-3">

    <div class="col-md-6">
        <label class="form-label fw-semibold small">نوع الحكم <span class="text-danger">*</span></label>
        <select name="id_type_jugement" class="form-select @if($isNew) @error('id_type_jugement') is-invalid @enderror @endif" required>
            @foreach($typesJugement as $type)
                <option value="{{ $type->id }}"
                    @selected((int) ($isNew ? old('id_type_jugement', $idTypeDefaut) : $j->id_type_jugement) === $type->id)>
                    {{ $type->type_jugement }}
                </option>
            @endforeach
        </select>
        @if($isNew) @error('id_type_jugement')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold small">تاريخ الحكم <span class="text-danger">*</span></label>
        <input type="date" name="date_jugement"
               class="form-control @if($isNew) @error('date_jugement') is-invalid @enderror @endif"
               max="{{ date('Y-m-d') }}"
               value="{{ $isNew ? old('date_jugement', date('Y-m-d')) : $j->date_jugement->format('Y-m-d') }}"
               required>
        @if($isNew) @error('date_jugement')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold small">القاضي</label>
        <select name="id_juge" class="form-select">
            <option value="">— بدون —</option>
            @foreach($juges as $juge)
                <option value="{{ $juge->id }}"
                    @selected((int) ($isNew ? old('id_juge') : $j->id_juge) === $juge->id)>
                    {{ $juge->nom_complet }}{{ $juge->grade ? ' — ' . $juge->grade : '' }}
                </option>
            @endforeach
        </select>
        @if($juges->isEmpty())
            <div class="form-text">لا يوجد قضاة مسجلون لهذه المحكمة.</div>
        @endif
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold small">منطوق الحكم <span class="text-danger">*</span></label>
        <textarea name="contenu_dispositif" rows="5"
                  class="form-control @if($isNew) @error('contenu_dispositif') is-invalid @enderror @endif"
                  required>{{ $isNew ? old('contenu_dispositif') : $j->contenu_dispositif }}</textarea>
        @if($isNew) @error('contenu_dispositif')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" name="est_definitif" value="1"
                   id="est_definitif_{{ $j?->id ?? 'new' }}"
                   @checked($isNew ? old('est_definitif') : $j->est_definitif)>
            <label class="form-check-label small fw-semibold" for="est_definitif_{{ $j?->id ?? 'new' }}">
                حكم نهائي
            </label>
        </div>
    </div>

</div>
