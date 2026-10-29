{{--
    Saisie du numéro de dossier d'une instance, selon le format de la juridiction.

      standard  : السنة / رمز الفئة / الرقم          ex. 2024/7120/1994
      cassation : السنة / رمز الهيئة / الغرفة / الرقم  ex. 2025/1/4/6327

    Les deux groupes sont rendus ; seul le groupe actif est activé (les champs
    désactivés ne sont pas soumis). resources/js/numero-dossier.js bascule le
    format quand le champ « driver » change (degré ou type de recours).
    Le serveur assemble numero[...] via App\Support\NumeroDossier::fusionner().

    Props :
      format  'standard' | 'cassation'
      value   numéro stocké (pré-remplissage)
      driver  sélecteur CSS du <select> qui pilote le format (optionnel)
      useOld  reprendre old() après une erreur de validation
--}}
@props(['format' => 'standard', 'value' => null, 'driver' => null, 'useOld' => false])

@php
    $p = \App\Support\NumeroDossier::decomposer($value);
    // Numéro enregistré dans un autre format que celui attendu : on ne pré-remplit pas de segments décalés.
    // (à faire AVANT de créer $v : une arrow function capture $p par valeur)
    if ($value && $p['format'] !== $format) { $p = \App\Support\NumeroDossier::decomposer(null); }
    $v = fn (string $k, $defaut = null) => $useOld ? old("numero.$k", $p[$k] ?? $defaut) : ($p[$k] ?? $defaut);
    $annee = $v('annee', date('Y'));
@endphp

<div {{ $attributes->class(['p-3 bg-light rounded border']) }}
     data-numero-widget
     data-format="{{ $format }}"
     @if($driver) data-driver="{{ $driver }}" @endif>

    {{-- Format standard --}}
    <div class="row g-2 align-items-end" data-numero-group="standard" @if($format !== 'standard') hidden @endif>
        <div class="col-4">
            <label class="small mb-1">السنة</label>
            <input type="number" name="numero[annee]" value="{{ $annee }}" min="1900" max="2100"
                   class="form-control text-center fw-bold" @disabled($format !== 'standard')>
        </div>
        <div class="col-4">
            <label class="small mb-1">رمز الفئة</label>
            <input type="text" name="numero[code]" value="{{ $v('code') }}" maxlength="4" inputmode="numeric"
                   placeholder="7120" class="form-control text-center fw-bold" @disabled($format !== 'standard')>
        </div>
        <div class="col-4">
            <label class="small mb-1">رقم الترتيب</label>
            <input type="number" name="numero[ordre]" value="{{ $v('ordre') }}" min="1" max="999999"
                   placeholder="1994" class="form-control text-center fw-bold" @disabled($format !== 'standard')>
        </div>
    </div>

    {{-- Format cassation --}}
    <div class="row g-2 align-items-end" data-numero-group="cassation" @if($format !== 'cassation') hidden @endif>
        <div class="col-3">
            <label class="small mb-1">السنة</label>
            <input type="number" name="numero[annee]" value="{{ $annee }}" min="1900" max="2100"
                   class="form-control text-center fw-bold" @disabled($format !== 'cassation')>
        </div>
        <div class="col-3">
            <label class="small mb-1">رمز الهيئة</label>
            <input type="number" name="numero[hay_a]" value="{{ $v('hay_a') }}"
                   min="1" max="{{ \App\Support\NumeroDossier::HAY_A_MAX }}"
                   placeholder="1 - {{ \App\Support\NumeroDossier::HAY_A_MAX }}"
                   class="form-control text-center fw-bold" @disabled($format !== 'cassation')>
        </div>
        <div class="col-3">
            <label class="small mb-1">الغرفة</label>
            <input type="number" name="numero[chambre]" value="{{ $v('chambre') }}"
                   min="1" max="{{ \App\Support\NumeroDossier::CHAMBRE_MAX }}"
                   placeholder="1 - {{ \App\Support\NumeroDossier::CHAMBRE_MAX }}"
                   class="form-control text-center fw-bold" @disabled($format !== 'cassation')>
        </div>
        <div class="col-3">
            <label class="small mb-1">رقم الترتيب</label>
            <input type="text" name="numero[ordre]" value="{{ $v('ordre') }}" maxlength="5" inputmode="numeric"
                   placeholder="6327" class="form-control text-center fw-bold" @disabled($format !== 'cassation')>
        </div>
    </div>

    <div class="mt-3 text-center">
        <div class="small text-muted mb-1">الرقم النهائي الذي سيتم تسجيله</div>
        <div data-numero-preview dir="ltr" class="h5 fw-bold text-dark border-bottom d-inline-block px-4 pb-1"
             style="letter-spacing:2px;">—</div>
        <div class="small text-muted mt-1" dir="ltr">
            <span data-numero-exemple="standard" @if($format !== 'standard') hidden @endif>{{ \App\Support\NumeroDossier::exemple('standard') }}</span>
            <span data-numero-exemple="cassation" @if($format !== 'cassation') hidden @endif>{{ \App\Support\NumeroDossier::exemple('cassation') }}</span>
        </div>
    </div>
</div>
