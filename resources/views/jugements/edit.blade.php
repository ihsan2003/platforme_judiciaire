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
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')

@php
    $dt            = $jugement->dossierTribunal;
    $audienceHoukm = $dt?->audienceHoukm();
    $dateHoukm     = $audienceHoukm?->date_audience?->format('Y-m-d');

    // Charger les parties du dossier (même logique que create)
    $partiesDossier = \App\Models\DossierPartie::with(['partie', 'typePartie'])
        ->where('id_dossier', $dt?->id_dossier)
        ->get();

    $institution   = $partiesDossier->first(fn($dp) => $dp->partie?->est_entraide);
    $autresParties = $partiesDossier->filter(fn($dp) => !$dp->partie?->est_entraide);

    // Données actuelles de la pivot jugement_parties (pour pré-remplissage)
    $partiesLiees    = $jugement->parties->pluck('id')->toArray();
    $positionsActuelles = $jugement->parties->mapWithKeys(fn($p) => [
        $p->id => $p->pivot->id_position_institution
    ]);
    $montantsActuels = $jugement->parties->mapWithKeys(fn($p) => [
        $p->id => $p->pivot->montant_condamne
    ]);

    // Position institution actuelle
    $positionEtabActuelle = $institution
        ? ($positionsActuelles->get($institution->partie?->id) ?? null)
        : null;

    // Charger les positions disponibles
    $positionsInstitution = \App\Models\PositionInstitution::orderBy('position')->get();
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
        يمكنك تعديل جميع المعلومات باستثناء حذف التنفيذات المرتبطة.
    </div>
@endif

<form action="{{ route('jugements.update', $jugement) }}" method="POST">
@csrf
@method('PUT')

<div class="row g-4" dir="rtl">

    {{-- ══ العمود الرئيسي ══ --}}
    <div class="col-lg-8">

        {{-- ── المعلومات الرئيسية ── --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-hammer ms-2 text-warning"></i>
                    معلومات الحكم
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- الملف القضائي — lecture seule --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">الملف القضائي</label>
                        <div class="form-control bg-light text-muted">
                            {{ $dt?->dossier?->numero_dossier_interne ?? '—' }}
                            ·
                            {{ $dt?->tribunal?->nom_tribunal ?? '—' }}
                            ({{ $dt?->degre?->degre_juridiction ?? '—' }})
                        </div>
                        {{-- Champ caché indispensable : le <div> ci-dessus n'est
                             qu'un affichage, il n'envoie aucune valeur au serveur. --}}
                        <input type="hidden" name="id_dossier_tribunal" value="{{ $dt?->id }}">
                        <div class="form-text">لا يمكن تغيير الملف بعد إنشاء الحكم.</div>
                    </div>

                    {{-- القاضي --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            القاضي <span class="text-danger">*</span>
                        </label>
                        <select name="id_juge"
                                class="form-select @error('id_juge') is-invalid @enderror"
                                required>
                            <option value="">— اختر —</option>
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
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- تاريخ الحكم --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            تاريخ الحكم <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                               name="date_jugement"
                               id="date_jugement"
                               class="form-control @error('date_jugement') is-invalid @enderror"
                               value="{{ old('date_jugement', $jugement->date_jugement->format('Y-m-d')) }}"
                               {{ $dateHoukm ? 'readonly' : '' }}
                               required>
                        
                        @error('date_jugement')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- حكم نهائي --}}
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="est_definitif"
                                   value="1"
                                   id="est_definitif"
                                   @checked(old('est_definitif', $jugement->est_definitif))
                                   @disabled($jugement->est_definitif && $jugement->executions()->exists())>
                            <label class="form-check-label" for="est_definitif">حكم نهائي</label>
                        </div>
                        @if($jugement->est_definitif && $jugement->executions()->exists())
                            <div class="form-text text-warning">
                                <i class="bi bi-lock ms-1"></i>
                                هذا الحكم مرتبط بتنفيذ، لذلك لا يمكن تعديل صفته النهائية.
                            </div>
                        @endif
                    </div>

                    {{-- منطوق الحكم --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">منطوق الحكم</label>
                        <textarea name="contenu_dispositif"
                                  class="form-control @error('contenu_dispositif') is-invalid @enderror"
                                  rows="6"
                                  placeholder="محتوى الحكم...">{{ old('contenu_dispositif', $jugement->contenu_dispositif) }}</textarea>
                        @error('contenu_dispositif')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- ── بلوك المؤسسة ── --}}
        @if($institution)

        <div class="card border-0 shadow-sm mb-4"
             style="border-right: 4px solid #0d6efd !important;">

            <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                <i class="bi bi-building-fill text-primary"></i>
                <h6 class="mb-0 fw-semibold">
                    وضعية المؤسسة :
                    <strong class="text-primary">{{ $institution->partie->nom_partie }}</strong>
                </h6>
            </div>

            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label fw-semibold small">
                        وضعية المؤسسة في هذا الحكم
                        <span class="text-danger">*</span>
                    </label>

                    <div class="d-flex flex-wrap gap-2">
                        @foreach($positionsInstitution as $pos)
                        @php
                            $color = match(true) {
                                str_contains(strtolower($pos->position), 'ضد')   => 'danger',
                                str_contains(strtolower($pos->position), 'جزئي') => 'warning',
                                default                                           => 'success',
                            };
                            $icon = match($color) {
                                'success' => 'trophy-fill',
                                'danger'  => 'shield-x',
                                'warning' => 'slash-circle',
                                default   => 'dash-circle',
                            };
                            // Pré-sélectionner depuis old() ou la valeur actuelle en BDD
                            $isChecked = old('position_institution_etab') !== null
                                ? old('position_institution_etab') == $pos->id
                                : $positionEtabActuelle == $pos->id;
                        @endphp

                        <div>
                            <input type="radio"
                                   class="btn-check"
                                   name="position_institution_etab"
                                   id="pos_{{ $pos->id }}"
                                   value="{{ $pos->id }}"
                                   data-label="{{ strtolower($pos->position) }}"
                                   @checked($isChecked)
                                   required>
                            <label class="btn btn-outline-{{ $color }} px-4"
                                   for="pos_{{ $pos->id }}">
                                <i class="bi bi-{{ $icon }} ms-2"></i>
                                {{ $pos->position }}
                            </label>
                        </div>
                        @endforeach
                    </div>

                    @error('position_institution_etab')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- المؤسسة محكوم عليها --}}
                <div id="bloc-etab-condamne" class="d-none">
                    <div class="border rounded p-3 border-danger bg-danger bg-opacity-5">

                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-building text-danger"></i>
                            <span class="fw-semibold small">{{ $institution->partie->nom_partie }}</span>
                            <span class="badge bg-danger me-1" style="font-size:.65rem">
                                المؤسسة محكوم عليها
                            </span>
                        </div>

                        <input type="hidden"
                               name="parties[]"
                               id="hidden_etab_partie"
                               value="{{ $institution->partie->id }}">

                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-danger text-white border-danger">درهم</span>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="montants[{{ $institution->partie->id }}]"
                                   id="montant_etab"
                                   class="form-control"
                                   value="{{ old('montants.'.$institution->partie->id, $montantsActuels->get($institution->partie->id)) }}"
                                   placeholder="المبلغ المحكوم به">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        @else

        <div class="alert alert-warning small mb-4">
            <i class="bi bi-exclamation-triangle ms-2"></i>
            لا يوجد طرف محدد كمؤسسة في هذا الملف.
        </div>

        @endif

        {{-- ── الأطراف الأخرى ── --}}
        <div id="bloc-parties-adverses" class="d-none">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                    <i class="bi bi-people text-secondary"></i>
                    <h6 class="mb-0 fw-semibold">الأطراف الأخرى المحكوم عليها</h6>
                    <span class="text-muted small me-1">(اختر الأطراف وأدخل المبالغ)</span>
                </div>

                <div class="card-body">

                    @if($autresParties->isEmpty())
                        <div class="text-center py-3 text-muted small">
                            <i class="bi bi-people fs-2 d-block mb-2 opacity-25"></i>
                            لا توجد أطراف أخرى في هذا الملف.
                        </div>
                    @else
                    <div class="row g-3">
                        @foreach($autresParties as $dp)
                        @php
                            // Pré-cocher si la partie était déjà liée au jugement
                            $isLinked = in_array($dp->partie->id, $partiesLiees);
                            $montantExistant = $montantsActuels->get($dp->partie->id);
                        @endphp
                        <div class="col-md-6">
                            <div class="border rounded p-3 {{ $isLinked ? 'border-primary bg-primary bg-opacity-5' : '' }}">

                                <div class="form-check mb-2">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="parties[]"
                                           value="{{ $dp->partie->id }}"
                                           id="partie_{{ $dp->partie->id }}"
                                           @checked(
                                               old('parties')
                                                   ? in_array($dp->partie->id, old('parties', []))
                                                   : $isLinked
                                           )>
                                    <label class="form-check-label small fw-semibold"
                                           for="partie_{{ $dp->partie->id }}">
                                        {{ $dp->partie->nom_partie }}
                                        <span class="badge bg-secondary me-1" style="font-size:.65rem">
                                            {{ $dp->typePartie->type_partie ?? '—' }}
                                        </span>
                                    </label>
                                </div>

                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">درهم</span>
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           name="montants[{{ $dp->partie->id }}]"
                                           class="form-control"
                                           value="{{ old('montants.'.$dp->partie->id, $montantExistant) }}"
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

        {{-- ── الإجراءات ── --}}
        <div class="d-flex gap-2 justify-content-between mt-2">
            <a href="{{ route('jugements.show', $jugement) }}"
               class="btn btn-outline-secondary">
                <i class="bi bi-x-lg ms-1"></i>إلغاء
            </a>
            <button type="submit" class="btn btn-warning px-4">
                <i class="bi bi-check-lg ms-1"></i>حفظ التعديلات
            </button>
        </div>

    </div>{{-- /col-lg-8 --}}

    {{-- ══ العمود الجانبي ══ --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle ms-2 text-muted"></i>ملخص
                </h6>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-6 text-muted fw-normal">الملف</dt>
                    <dd class="col-6 fw-semibold">
                        <a></a>{{ $dt?->dossier?->numero_dossier_tribunal ?? '—' }}
                    </dd>

                    <dt class="col-6 text-muted fw-normal">المحكمة</dt>
                    <dd class="col-6">{{ $dt?->tribunal?->nom_tribunal ?? '—' }}</dd>

                    <dt class="col-6 text-muted fw-normal">الدرجة</dt>
                    <dd class="col-6">{{ $dt?->degre?->degre_juridiction ?? '—' }}</dd>

                    <dt class="col-6 text-muted fw-normal">جلسة الحكم</dt>
                    <dd class="col-6">
                        @if($audienceHoukm)
                            <span class="text-warning fw-semibold">
                                {{ $audienceHoukm->date_audience->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </dd>

                    <dt class="col-6 text-muted fw-normal">الطعون</dt>
                    <dd class="col-6">
                        <span class="badge bg-{{ $jugement->recours->isEmpty() ? 'secondary' : 'warning text-dark' }}">
                            {{ $jugement->recours->count() }}
                        </span>
                    </dd>

                    <dt class="col-6 text-muted fw-normal">التنفيذات</dt>
                    <dd class="col-6">
                        <span class="badge bg-{{ $jugement->executions->isEmpty() ? 'secondary' : 'info' }}">
                            {{ $jugement->executions->count() }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>

        {{-- Résumé des montants actuels --}}
        @if($jugement->finance)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-cash-stack ms-2 text-success"></i>الوضعية المالية الحالية
                </h6>
            </div>
            <div class="card-body small">
                @php
                    $f   = $jugement->finance;
                    $pct = $f->montant_condamne > 0
                        ? min(100, round(($f->montant_paye / $f->montant_condamne) * 100))
                        : 0;
                @endphp
                <dl class="row mb-2">
                    <dt class="col-7 text-muted fw-normal">المحكوم به</dt>
                    <dd class="col-5 fw-semibold">
                        {{ number_format($f->montant_condamne, 2) }} د.م
                    </dd>
                    <dt class="col-7 text-muted fw-normal">المؤدى</dt>
                    <dd class="col-5 fw-semibold text-success">
                        {{ number_format($f->montant_paye, 2) }} د.م
                    </dd>
                    <dt class="col-7 text-muted fw-normal">المتبقي</dt>
                    <dd class="col-5 fw-semibold text-danger">
                        {{ number_format($f->montant_restant, 2) }} د.م
                    </dd>
                </dl>
                <div style="height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;">
                    <div style="width:{{ $pct }}%;height:100%;border-radius:3px;
                                background:{{ $pct>=100 ? '#16a34a' : ($pct>0 ? '#d97706' : '#ef4444') }}">
                    </div>
                </div>
                <div class="text-muted mt-1" style="font-size:.72rem">{{ $pct }}% مستردة</div>
            </div>
        </div>
        @endif

        @if($jugement->recours->isNotEmpty())
            <div class="alert alert-warning border-0 small" dir="rtl">
                <i class="bi bi-exclamation-triangle ms-2"></i>
                توجد طعون على هذا الحكم. يرجى الحذر أثناء التعديل.
            </div>
        @endif

        @if($jugement->executions->isNotEmpty())
            <div class="alert alert-danger border-0 small" dir="rtl">
                <i class="bi bi-shield-check ms-2"></i>
                هذا الحكم مرتبط بتنفيذ جارٍ. يرجى الحذر أثناء التعديل.
            </div>
        @endif

    </div>{{-- /col-lg-4 --}}

</div>{{-- /row --}}

</form>

@endsection

@push('scripts')
<script>
// ══════════════════════════════════════════════════════════════════
// الوضعية المبدئية (للـ edit : lire depuis les radios pré-cochés)
// ══════════════════════════════════════════════════════════════════
const radios         = document.querySelectorAll('input[name="position_institution_etab"]');
const blocEtab       = document.getElementById('bloc-etab-condamne');
const blocPartiesAdv = document.getElementById('bloc-parties-adverses');
const hiddenEtab     = document.getElementById('hidden_etab_partie');
const montantEtab    = document.getElementById('montant_etab');

function onPositionChange(radio) {
    const label = radio.dataset.label ?? '';

    // المؤسسة محكوم عليها : contient "ضد" ou "جزئي"
    const etabCondamne = label.includes('ضد') || label.includes('جزئي');

    // الأطراف الأخرى محكوم عليها : contient "مع" (pour / en faveur)
    const adverseCondamne = !etabCondamne;

    // ── بلوك المؤسسة ─────────────────────────────────────────────
    if (blocEtab) {
        blocEtab.classList.toggle('d-none', !etabCondamne);
    }
    // NOTE: hiddenEtab ne doit JAMAIS être désactivé — l'institution doit
    // toujours avoir une ligne dans jugement_parties, quelle que soit sa
    // position (مع / ضد / جزئي). Seul le montant dépend de la position.
    if (montantEtab) {
        montantEtab.required = etabCondamne;
        if (!etabCondamne) montantEtab.value = '';
    }

    // ── بلوك الأطراف الأخرى ──────────────────────────────────────
    if (blocPartiesAdv) {
        blocPartiesAdv.classList.toggle('d-none', !adverseCondamne);
    }

    // إلغاء التحديد عند تغيير الوضعية نحو "ضد"
    if (!adverseCondamne) {
        document.querySelectorAll(
            '#bloc-parties-adverses input[type="checkbox"]'
        ).forEach(cb => cb.checked = false);
    }
}

// Attacher les listeners
radios.forEach(r => {
    r.addEventListener('change', () => onPositionChange(r));
});

// ══════════════════════════════════════════════════════════════════
// INIT : appliquer l'état initial au chargement (valeur pré-cochée)
// ══════════════════════════════════════════════════════════════════
window.addEventListener('DOMContentLoaded', () => {
    const checkedRadio = document.querySelector(
        'input[name="position_institution_etab"]:checked'
    );
    if (checkedRadio) {
        onPositionChange(checkedRadio);
    }
});
</script>
@endpush