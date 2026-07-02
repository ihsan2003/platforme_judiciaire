@extends('layouts.app')

@section('title', 'الملفات القضائية')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">الرئيسية</a>
    </li>
    <li class="breadcrumb-item active">الملفات</li>
@endsection

@section('content')

{{-- ══ الإحصائيات السريعة ══ --}}
<div class="row g-3 mb-4">

    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">

                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-folder2-open fs-4 text-primary"></i>
                </div>

                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['total'] }}</div>
                    <div class="text-muted small">إجمالي الملفات</div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">

                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-activity fs-4 text-success"></i>
                </div>

                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['actifs'] }}</div>
                    <div class="text-muted small">الملفات النشطة</div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">

                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-calendar-plus fs-4 text-warning"></i>
                </div>

                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['ce_mois'] }}</div>
                    <div class="text-muted small">خلال هذا الشهر</div>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- ══ الفلاتر ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">

        <form method="GET" class="row g-2 align-items-end">

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">
                    بحث
                </label>

                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search text-muted"></i>
                    </span>

                    <input type="text"
                           name="search"
                           class="form-control border-start-0"
                           placeholder="رقم الملف..."
                           value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">
                    نوع القضية
                </label>

                <select name="type" class="form-select">
                    <option value="">جميع الأنواع</option>

                    @foreach($typesAffaire as $type)
                        <option value="{{ $type->id }}"
                                @selected(request('type') == $type->id)>
                            {{ $type->affaire }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">
                    الحالة
                </label>

                <select name="statut" class="form-select">
                    <option value="">جميع الحالات</option>

                    @foreach($statutDossiers as $statut)
                        <option value="{{ $statut->id }}"
                                @selected(request('statut') == $statut->id)>
                            {{ $statut->statut_dossier }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">
                    من
                </label>

                <input type="date"
                       name="date_debut"
                       class="form-control"
                       value="{{ request('date_debut') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">
                    إلى
                </label>

                <input type="date"
                       name="date_fin"
                       class="form-control"
                       value="{{ request('date_fin') }}">
            </div>

            <div class="col-md-1 d-flex gap-1">

                <button class="btn btn-primary flex-fill" title="تصفية">
                    <i class="bi bi-funnel-fill"></i>
                </button>

                <a href="{{ route('dossiers.index') }}"
                   class="btn btn-outline-secondary"
                   title="إعادة التعيين">
                    <i class="bi bi-x-lg"></i>
                </a>

            </div>

        </form>

    </div>
</div>

{{-- ══ الجدول ══ --}}
<div class="card border-0 shadow-sm">

    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">

        <h5 class="mb-0 fw-semibold">
            <i class="bi bi-folder2 me-2 text-primary"></i>
            الملفات القضائية

            <span class="badge bg-primary ms-2">
                {{ $dossiers->total() }}
            </span>
        </h5>

        @can('create', App\Models\DossierJudiciaire::class)
            <a href="{{ route('dossiers.create') }}"
               class="btn btn-primary btn-sm">

                <i class="bi bi-plus-lg me-1"></i>
                ملف جديد
            </a>
        @endcan

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">
                <tr>
                    <th class="text-muted small fw-semibold ps-3">
                        رقم الملف
                    </th>

                    <th class="text-muted small fw-semibold">
                        رقم المحكمة
                    </th>

                    <th class="text-muted small fw-semibold">
                        الجهة
                    </th>

                    <th class="text-muted small fw-semibold">
                        نوع القضية
                    </th>

                    <th class="text-muted small fw-semibold">
                        المحكمة
                    </th>

                    <th class="text-muted small fw-semibold">
                        الحالة
                    </th>

                    <th class="text-muted small fw-semibold pe-3">
                        الإجراءات
                    </th>
                </tr>
            </thead>

            <tbody>

                @forelse($dossiers as $dossier)

                <tr>

                    <td class="ps-3">
                        <span class="fw-semibold">
                            #{{ $dossier->id }}
                        </span>
                    </td>

                    <td class="text-muted small">
                        {{ $dossier->numero_dossier_tribunal ?? '—' }}
                    </td>

                    <td class="text-muted small">
                        @foreach(
                            $dossier->dossierTribunaux
                                ->pluck('tribunal.province.region.region')
                                ->filter()
                                ->unique()
                            as $region
                        )
                            {{ $region }}
                        @endforeach
                    </td>

                    <td>
                        <span class="badge bg-info bg-opacity-15 text-white border border-info border-opacity-25">
                            {{ $dossier->typeAffaire->affaire ?? '—' }}
                        </span>
                    </td>

                    <td>
                        @forelse($dossier->dossierTribunaux as $dt)

                            <span class="badge bg-secondary bg-opacity-10 text-secondary me-1 mb-1">
                                <i class="bi bi-bank me-1"></i>
                                {{ $dt->tribunal->nom_tribunal ?? '?' }}
                            </span>

                        @empty
                            <span class="text-muted small">—</span>
                        @endforelse
                    </td>

                    <td>

                        @php
                            $statut = $dossier->statutDossier->statut_dossier ?? '—';

                            $color = match(true) {
                                str_contains($statut, 'Actif')    => 'success',
                                str_contains($statut, 'Clôturé')  => 'secondary',
                                str_contains($statut, 'Suspendu') => 'warning',
                                default                           => 'primary',
                            };

                            $textClass = in_array($color, ['warning', 'secondary'])
                                ? 'text-dark'
                                : 'text-white';
                        @endphp

                        <span class="badge bg-{{ $color }} {{ $textClass }}">
                            {{ $statut }}
                        </span>

                    </td>

                    <td class="text-end pe-3">

                        <div class="d-flex gap-1 justify-content-end">

                            {{-- عرض --}}
                            <a href="{{ route('dossiers.show', $dossier) }}"
                               class="btn btn-sm btn-outline-primary"
                               title="عرض التفاصيل">

                                <i class="bi bi-eye"></i>
                            </a>

                            {{-- تعديل --}}
                            @can('update', $dossier)

                            <a href="{{ route('dossiers.edit', $dossier) }}"
                               class="btn btn-sm btn-outline-warning"
                               title="تعديل">

                                <i class="bi bi-pencil"></i>
                            </a>

                            @endcan

                            @can('delete', $dossier)

                            <x-modal-delete
                                :action="route('dossiers.destroy', $dossier)"
                                modal-id="archiveDossier{{ $dossier->id }}"
                                trigger-label=""
                                trigger-class="btn btn-sm btn-outline-danger"
                                trigger-icon="bi-archive"
                                title="أرشفة الملف القضائي"
                                :description="'الملف رقم #' . $dossier->id"
                                warning="هل أنت متأكد من أرشفة هذا الملف؟ هذا الإجراء سيؤدي إلى نقل الملف إلى الأرشيف."
                                confirm-label="نعم، أرشفة"
                            />

                            @endcan

                        </div>

                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">

                        <i class="bi bi-folder-x fs-1 d-block mb-2 opacity-25"></i>

                        لا توجد ملفات

                        @if(request()->hasAny(['search','type','statut','date_debut','date_fin']))
                            —
                            <a href="{{ route('dossiers.index') }}">
                                إعادة تعيين الفلاتر
                            </a>
                        @endif

                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($dossiers->hasPages())

    <div class="card-footer bg-white d-flex justify-content-between align-items-center">

        <span class="text-muted small">
            عرض {{ $dossiers->firstItem() }} – {{ $dossiers->lastItem() }}
            من أصل {{ $dossiers->total() }} ملف
        </span>

        {{ $dossiers->links() }}

    </div>

    @endif

</div>

@endsection