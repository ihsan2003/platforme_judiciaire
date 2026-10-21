@extends('layouts.app')

@section('title', $partie->nom_partie)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
<li class="breadcrumb-item"><a href="{{ route('parties.index') }}">الأطراف</a></li>
<li class="breadcrumb-item active">{{ $partie->nom_partie }}</li>
@endsection

@section('content')

{{-- ══ EN-TÊTE ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">

        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">

            {{-- identité --}}
            <div class="d-flex align-items-center gap-3">

                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                            bg-{{ $partie->type_personne === 'Morale' ? 'warning' : 'success' }} bg-opacity-10"
                     style="width:56px;height:56px">

                    <i class="bi bi-{{ $partie->type_personne === 'Morale' ? 'building' : 'person' }}
                              text-{{ $partie->type_personne === 'Morale' ? 'warning' : 'success' }} fs-3"></i>

                </div>

                <div>

                    <h4 class="fw-bold mb-0">
                        {{ $partie->nom_partie }}
                    </h4>

                    <div class="mt-1 d-flex flex-wrap gap-2 align-items-center">

                        @php
                            $isMorale = $partie->type_personne === 'Morale';

                            $bg = $isMorale ? 'warning' : 'success';
                            $icon = $isMorale ? 'building' : 'person';

                            $text = $isMorale ? 'text-dark' : 'text-white';
                        @endphp

                        <span class="badge bg-{{ $bg }} bg-opacity-15 {{ $text }} border border-{{ $bg }} border-opacity-25">
                            <i class="bi bi-{{ $icon }} me-1"></i>
                            {{ $partie->type_personne ?? '—' }}
                        </span>

                        {{-- identifiant --}}
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 font-monospace">
                            {{ $partie->identifiant_unique }}
                        </span>


                        {{-- date naissance + age --}}
                        @if($partie->date_naissance)
                        <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25">
                            <i class="bi bi-calendar-date me-1"></i>
                            {{ $partie->date_naissance->format('d/m/Y') }}
                            ({{ $partie->date_naissance->age }} سنة)
                        </span>
                        @endif

                    </div>
                </div>
            </div>

            {{-- actions --}}
            <div class="d-flex gap-2 flex-wrap">

                <a href="{{ route('parties.edit', $partie) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>تعديل
                </a>

                @role('admin')
                <form action="{{ route('parties.destroy', $partie) }}" method="POST"
                      onsubmit="return confirm('هل تريد حذف هذا الطرف ؟')">
                    @csrf @method('DELETE')

                    <button class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash me-1"></i>حذف
                    </button>
                </form>
                @endrole

                <a href="{{ route('parties.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>رجوع
                </a>

            </div>
        </div>

        <hr class="my-3">

        {{-- contact --}}
        <div class="row g-2 small text-muted">

            <div class="col-sm-3">
                <i class="bi bi-telephone me-1"></i>
                <strong>الهاتف:</strong>
                @if($partie->telephone)
                    <a href="tel:{{ $partie->telephone }}" class="text-decoration-none ms-1">
                        {{ $partie->telephone }}
                    </a>
                @else
                    <span class="ms-1">—</span>
                @endif
            </div>

            <div class="col-sm-4">
                <i class="bi bi-envelope me-1"></i>
                <strong>البريد:</strong>
                @if($partie->email)
                    <a href="mailto:{{ $partie->email }}" class="text-decoration-none ms-1">
                        {{ $partie->email }}
                    </a>
                @else
                    <span class="ms-1">—</span>
                @endif
            </div>

            <div class="col-sm-5">
                <i class="bi bi-geo-alt me-1"></i>
                <strong>العنوان:</strong>
                <span class="ms-1">{{ $partie->adresse ?? '—' }}</span>
            </div>

        </div>

    </div>
</div>

<div class="row g-4">

    {{-- ── DOSSIERS ── --}}
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">

                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-folder2-open me-2 text-primary"></i>
                    الملفات المرتبطة
                    <span class="badge bg-primary ms-1">
                        {{ $partie->dossiers->count() }}
                    </span>
                </h6>

            </div>

            @if($partie->dossiers->isEmpty())

                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-folder-x fs-1 d-block mb-2 opacity-25"></i>
                    لا توجد ملفات مرتبطة بهذه الجهة.
                </div>

            @else

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                        <tr>
                            <th>رقم الملف</th>
                            <th>الشعبة</th>
                            <th>الدور</th>
                            <th>المحامون في هذا الملف</th>
                            <th>الحالة</th>
                            <th class="text-end">عرض</th>
                        </tr>
                        </thead>

                        <tbody>

                        @foreach($partie->dossiers as $dossier)

                            @php
                                $statut = $dossier->statutDossier?->statut_dossier ?? '—';

                                $sc = $dossier->statutDossier?->couleur_bootstrap ?? 'primary';

                                $role = $dossier->pivot->id_type_partie
                                    ? optional(\App\Models\TypePartie::find($dossier->pivot->id_type_partie))->type_partie
                                    : '—';
                            @endphp

                            <tr>

                                <td>
                                    <a href="{{ route('dossiers.show', $dossier) }}"
                                       class="text-decoration-none fw-semibold text-primary">
                                        {{ $dossier->numero_dossier_tribunal ?? '—' }}
                                    </a>
                                </td>

                                <td class="text-muted small">
                                    {{ $dossier->typeAffaire->affaire ?? '—' }}
                                </td>

                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        {{ $role }}
                                    </span>
                                </td>

                                <td class="small">
                                    @forelse(($affectationsParDossier[$dossier->id] ?? collect()) as $aff)
                                        <div>
                                            <i class="bi bi-briefcase me-1 text-muted"></i>
                                            <a href="{{ route('avocats.show', $aff->id_avocat) }}" class="text-decoration-none">
                                                {{ $aff->avocat?->nom_avocat }}
                                            </a>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                {{ $aff->degre?->degre_juridiction ?? 'كل مراحل الملف' }}
                                            </span>
                                        </div>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                </td>

                                <td>
                                    <span class="badge bg-{{ $sc }} bg-opacity-15 text-white border border-{{ $sc }} border-opacity-25">
                                        {{ $statut }}
                                    </span>
                                </td>

                                <td class="text-end">
                                    <a href="{{ route('dossiers.show', $dossier) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>

                            </tr>

                        @endforeach

                        </tbody>
                    </table>

                </div>

            @endif

        </div>

        {{-- ── AVERTISSEMENTS (إنذارات) ── --}}
        <div class="card border-0 shadow-sm mt-4">

            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">

                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                    الإنذارات المرتبطة
                    <span class="badge bg-warning text-dark ms-1">
                        {{ $avertissements->count() }}
                    </span>
                </h6>

            </div>

            @if($avertissements->isEmpty())

                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-exclamation-triangle fs-1 d-block mb-2 opacity-25"></i>
                    لا توجد إنذارات مرتبطة بهذه الجهة.
                </div>

            @else

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                        <tr>
                            <th>الرقم</th>
                            <th>نوعية الملف</th>
                            <th>الدور</th>
                            <th>الطرف الآخر</th>
                            <th>المحكمة المختصة</th>
                            <th>الحالة</th>
                            <th class="text-end">عرض</th>
                        </tr>
                        </thead>

                        <tbody>

                        @foreach($avertissements as $avertissement)

                            @php
                                $estDemandeur = $avertissement->role_partie === 'demandeur';

                                $autre = $estDemandeur
                                    ? $avertissement->defendeur
                                    : $avertissement->demandeur;

                                $couleur = $avertissement->statut?->couleur_bootstrap ?? 'primary';
                                $textClass = $couleur === 'warning' ? 'text-dark' : 'text-white';
                            @endphp

                            <tr>

                                <td>
                                    <a href="{{ route('avertissements.show', $avertissement) }}"
                                       class="text-decoration-none fw-semibold text-primary">
                                        #{{ $avertissement->id }}
                                    </a>
                                </td>

                                <td class="text-muted small">
                                    {{ $avertissement->typeAffaire->affaire ?? '—' }}
                                </td>

                                <td>
                                    <span class="badge bg-{{ $estDemandeur ? 'primary' : 'danger' }} bg-opacity-10 text-{{ $estDemandeur ? 'primary' : 'danger' }}">
                                        {{ $estDemandeur ? 'المدعي' : 'المدعى عليه' }}
                                    </span>
                                </td>

                                <td class="small">
                                    @if($autre)
                                        <a href="{{ route('parties.show', $autre) }}" class="text-decoration-none">
                                            {{ $autre->nom_partie }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td class="text-muted small">
                                    {{ $avertissement->tribunal->nom_tribunal ?? '—' }}
                                </td>

                                <td>
                                    <span class="badge bg-{{ $couleur }} {{ $textClass }}">
                                        {{ $avertissement->statut->statut_avertissement ?? '—' }}
                                    </span>
                                </td>

                                <td class="text-end">
                                    <a href="{{ route('avertissements.show', $avertissement) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>

                            </tr>

                        @endforeach

                        </tbody>
                    </table>

                </div>

            @endif

        </div>

    </div>

    {{-- ── SIDEBAR ── --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm mb-3">

            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-muted"></i>
                    المعلومات
                </h6>
            </div>

            <div class="card-body small">

                <dl class="row mb-0">

                    <dt class="col-6 text-muted">المعرف</dt>
                    <dd class="col-6 font-monospace">{{ $partie->identifiant_unique }}</dd>

                    <dt class="col-6 text-muted">النوع</dt>
                    <dd class="col-6">{{ $partie->type_personne ?? '—' }}</dd>


                    {{-- date naissance --}}
                    @if($partie->date_naissance)
                    <dt class="col-6 text-muted">تاريخ الميلاد</dt>
                    <dd class="col-6">
                        {{ $partie->date_naissance->format('d/m/Y') }}
                        ({{ $partie->date_naissance->age }} سنة)
                    </dd>
                    @endif

                    <dt class="col-6 text-muted">الملفات</dt>
                    <dd class="col-6">
                        <span class="badge bg-info bg-opacity-15 text-white border border-info border-opacity-25">
                            {{ $partie->dossiers->count() }}
                        </span>
                    </dd>

                    <dt class="col-6 text-muted">الإنذارات</dt>
                    <dd class="col-6">
                        <span class="badge bg-warning bg-opacity-25 text-dark border border-warning border-opacity-50">
                            {{ $avertissements->count() }}
                        </span>
                    </dd>

                    <dt class="col-6 text-muted">تاريخ الإنشاء</dt>
                    <dd class="col-6">{{ $partie->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted">آخر تعديل</dt>
                    <dd class="col-6">{{ $partie->updated_at->format('d/m/Y') }}</dd>

                </dl>

            </div>
        </div>

    </div>

</div>

@endsection