@extends('layouts.app')

@section('title', 'الأطراف')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">الرئيسية</a>
    </li>
    <li class="breadcrumb-item active">الأطراف</li>
@endsection

@push('styles')
<style>
body{
    direction: rtl;
    text-align: right;
    font-family: "Tajawal", sans-serif;
}

.table th,
.table td{
    vertical-align: middle;
}

.input-group .form-control{
    border-right: 0 !important;
}

.input-group-text{
    border-left: 0 !important;
}

.font-monospace{
    direction: ltr;
    display: inline-block;
}
</style>
@endpush

@section('content')

{{-- ══ الإحصائيات ══ --}}
<div class="row g-3 mb-4">

    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">

                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-people fs-4 text-primary"></i>
                </div>

                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ $parties->total() }}
                    </div>

                    <div class="text-muted small">
                        إجمالي الأطراف
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">

                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-person fs-4 text-success"></i>
                </div>

                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ \App\Models\Partie::where('type_personne', 'ذاتي')->count() }}
                    </div>

                    <div class="text-muted small">
                        أشخاص ذاتيون
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">

                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-building fs-4 text-warning"></i>
                </div>

                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ \App\Models\Partie::where('type_personne', 'Morale')->count() }}
                    </div>

                    <div class="text-muted small">
                        أشخاص اعتباريون
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- ══ الفلاتر ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">

        <form method="GET"
              action="{{ route('parties.index') }}"
              class="row g-2 align-items-end">

            <div class="col-md-3">
                <label class="form-label small text-muted fw-semibold">
                    بحث
                </label>

                <div class="input-group">

                    <input type="text"
                           name="search"
                           class="form-control border-end-0"
                           placeholder="الاسم، المعرف أو البريد أو رقم الهاتف..."
                           value="{{ request('search') }}">

                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">
                    نوع الشخص
                </label>

                <select name="type_personne" class="form-select">

                    <option value="">
                        جميع الأنواع
                    </option>

                    <option value="ذاتي"
                        @selected(request('type_personne') === 'ذاتي')>
                        ذاتي
                    </option>

                    <option value="اعتباري"
                        @selected(request('type_personne') === 'اعتباري')>
                        اعتباري
                    </option>

                </select>
            </div>

            <div class="col-md-1 d-flex gap-2">

                <button class="btn btn-primary">
                    <i class="bi bi-funnel-fill ms-1"></i>
                </button>

                <a href="{{ route('parties.index') }}"
                   class="btn btn-outline-secondary">
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
            <i class="bi bi-people ms-2 text-primary"></i>
            الأطراف

            <span class="badge bg-primary me-2">
                {{ $parties->total() }}
            </span>
        </h5>

        <a href="{{ route('parties.create') }}"
           class="btn btn-primary btn-sm">

            <i class="bi bi-plus-lg ms-1"></i>
            طرف جديد
        </a>

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">
                <tr>
                    <x-sortable-th column="nom" class="pe-3 text-muted small fw-semibold">
                        الاسم / التسمية
                    </x-sortable-th>
 
                    <x-sortable-th column="identifiant" class="text-muted small fw-semibold">
                        المعرف
                    </x-sortable-th>
 
                    <x-sortable-th column="type" class="text-muted small fw-semibold">
                        النوع
                    </x-sortable-th>
 
                    <x-sortable-th column="telephone" class="text-muted small fw-semibold">
                        الهاتف
                    </x-sortable-th>
 
                    <x-sortable-th column="email" class="text-muted small fw-semibold">
                        البريد الإلكتروني
                    </x-sortable-th>
 
                    <th class="text-muted small fw-semibold">
                        الملفات
                    </th>
 
                    <th class="text-start ps-3 text-muted small fw-semibold">
                        الإجراءات
                    </th>
                </tr>
            </thead>

            <tbody>

                @forelse($parties as $partie)

                <tr>

                    <td class="pe-3">

                        <div class="d-flex align-items-center gap-3">

                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                                bg-{{ $partie->type_personne === 'Morale' ? 'warning' : 'success' }} bg-opacity-10"
                                 style="width:38px;height:38px">

                                <i class="bi bi-{{ $partie->type_personne === 'Morale' ? 'building' : 'person' }}
                                    text-{{ $partie->type_personne === 'Morale' ? 'warning' : 'success' }}">
                                </i>

                            </div>

                            <div>

                                <div class="fw-semibold">
                                    {{ $partie->nom_partie }}
                                </div>

                                @if($partie->adresse)
                                    <div class="text-muted small text-truncate"
                                         style="max-width:200px">

                                        <i class="bi bi-geo-alt ms-1"></i>
                                        {{ $partie->adresse }}

                                    </div>
                                @endif

                            </div>

                        </div>

                    </td>

                    <td>

                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 font-monospace">
                            {{ $partie->identifiant_unique }}
                        </span>

                    </td>

                    <td>

                        @php
                            $isMorale = $partie->type_personne === 'اعتباري';
                            $color = $isMorale ? 'warning' : 'success';
                            $textColor = $isMorale ? 'text-dark' : 'text-white';
                            $icon = $isMorale ? 'bi-building' : 'bi-person';
                        @endphp

                        <span class="badge bg-{{ $color }} bg-opacity-15 {{ $textColor }} border border-{{ $color }} border-opacity-25">

                            <i class="bi {{ $icon }} ms-1"></i>

                            {{ $partie->type_personne === 'اعتباري'
                                ? 'اعتباري'
                                : 'ذاتي' }}

                        </span>

                    </td>

                    <td>

                        @if($partie->telephone)

                            <a href="tel:{{ $partie->telephone }}"
                               class="text-decoration-none text-muted small">

                                <i class="bi bi-telephone ms-1"></i>
                                {{ $partie->telephone }}

                            </a>

                        @else
                            <span class="text-muted small">—</span>
                        @endif

                    </td>

                    <td>

                        @if($partie->email)

                            <a href="mailto:{{ $partie->email }}"
                               class="text-decoration-none text-muted small">

                                <i class="bi bi-envelope ms-1"></i>
                                {{ $partie->email }}

                            </a>

                        @else
                            <span class="text-muted small">—</span>
                        @endif

                    </td>

                    <td>

                        @php $nb = $partie->dossiers()->count(); @endphp

                        @if($nb > 0)

                            <span class="badge bg-info bg-opacity-15 text-white border border-info border-opacity-25">

                                <i class="bi bi-folder2 ms-1"></i>

                                {{ $nb }}
                                ملف{{ $nb > 1 ? '' : '' }}

                            </span>

                        @else

                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                لا يوجد
                            </span>

                        @endif

                    </td>

                    <td class="text-start ps-3">

                        <div class="d-flex gap-1 justify-content-start">

                            <a href="{{ route('parties.show', $partie) }}"
                               class="btn btn-sm btn-outline-primary"
                               title="عرض">

                                <i class="bi bi-eye"></i>

                            </a>

                            <a href="{{ route('parties.edit', $partie) }}"
                               class="btn btn-sm btn-outline-warning"
                               title="تعديل">

                                <i class="bi bi-pencil"></i>

                            </a>

                            <form action="{{ route('parties.destroy', $partie) }}"
                                  method="POST"
                                  onsubmit="return confirm('هل تريد حذف هذا الطرف؟')">

                                @csrf
                                @method('DELETE')

                                <button class="btn btn-sm btn-outline-danger"
                                        title="حذف">

                                    <i class="bi bi-trash"></i>

                                </button>

                            </form>

                        </div>

                    </td>

                </tr>

                @empty

                <tr>

                    <td colspan="7"
                        class="text-center py-5 text-muted">

                        <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>

                        لا توجد أطراف

                        @if(request()->hasAny(['search', 'type_personne']))
                            —
                            <a href="{{ route('parties.index') }}">
                                إعادة تعيين الفلاتر
                            </a>
                        @endif

                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($parties->hasPages())

    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">

        <span class="text-muted small">

            عرض
            {{ $parties->firstItem() }}
            -
            {{ $parties->lastItem() }}

            من أصل
            {{ $parties->total() }}
            طرف

        </span>

        {{ $parties->links() }}

    </div>

    @endif

</div>

@endsection