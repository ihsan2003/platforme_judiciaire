{{-- resources/views/reclamations/index_ar.blade.php --}}
@extends('layouts.app')

@section('title', 'الشكايات')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">الرئيسية</a>
    </li>
    <li class="breadcrumb-item active">الشكايات</li>
@endsection

@section('content')

<div dir="rtl">

{{-- ══ الإحصائيات ══ --}}

<div class="row g-3 mb-4">
    @foreach([
        ['label' => 'المجموع',        'value' => $stats['total'],      'icon' => 'chat-left-text',  'color' => 'primary'],
        ['label' => 'المستلمة',       'value' => $stats['recues'],     'icon' => 'inbox',            'color' => 'info'],
        ['label' => 'قيد المعالجة',   'value' => $stats['en_cours'],   'icon' => 'arrow-repeat',     'color' => 'warning'],
        ['label' => 'المغلقة',        'value' => $stats['cloturees'],  'icon' => 'check-circle',     'color' => 'success'],
        ['label' => 'في الانتظار',    'value' => $stats['en_attente'], 'icon' => 'hourglass-split',  'color' => 'danger'],
        ['label' => 'هذا الشهر',      'value' => $stats['ce_mois'],    'icon' => 'calendar-plus',    'color' => 'secondary'],
    ] as $stat)
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="rounded-circle bg-{{ $stat['color'] }} bg-opacity-10 p-2 d-inline-flex mb-2">
                    <i class="bi bi-{{ $stat['icon'] }} fs-5 text-{{ $stat['color'] }}"></i>
                </div>

                <div class="fs-3 fw-bold lh-1 mb-1">
                    {{ $stat['value'] }}
                </div>

                <div class="text-muted small">
                    {{ $stat['label'] }}
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ══ الفلاتر ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">

            <div class="col-md-3">
                <label class="form-label small text-muted fw-semibold">
                    بحث
                </label>

                <div class="input-group">
                    <input type="text"
                           name="search"
                           class="form-control border-end-0"
                           placeholder="موضوع الشكاية، اسم المشتكي أو بريده الإلكتروني..."
                           value="{{ request('search') }}">

                    <span class="input-group-text bg-white">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">
                    الحالة
                </label>

                <select name="statut" class="form-select">
                    <option value="">كل الحالات</option>

                    @foreach($statuts as $statut)
                        <option value="{{ $statut->id }}"
                            @selected(request('statut') == $statut->id)>
                            {{ $statut->statut_reclamation }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">
                    نوع المشتكي
                </label>

                <select name="type_reclamant" class="form-select">
                    <option value="">كل الأنواع</option>

                    @foreach($typesReclamant as $type)
                        <option value="{{ $type->id }}"
                            @selected(request('type_reclamant') == $type->id)>
                            {{ $type->type_reclamant }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">
                    نوع الشكاية
                </label>

                <select name="type_reclamation" class="form-select">
                    <option value="">كل الأنواع</option>

                    @foreach($typesReclamation as $type)
                        <option value="{{ $type->id }}"
                            @selected(request('type_reclamation') == $type->id)>
                            {{ $type->type_reclamation }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">
                    من تاريخ
                </label>

                <input type="date"
                       name="date_debut"
                       class="form-control"
                       value="{{ request('date_debut') }}">
            </div>

            <div class="col-md-1 d-flex gap-1">
                <button class="btn btn-primary flex-fill" title="تصفية">
                    <i class="bi bi-funnel-fill"></i>
                </button>

                <a href="{{ route('reclamations.index') }}"
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
            <i class="bi bi-chat-left-text ms-2 text-primary"></i>
            الشكايات
            <span class="badge bg-primary me-2">
                {{ $reclamations->total() }}
            </span>

        </h5>

        <a href="{{ route('reclamations.create') }}"
           class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg ms-1"></i>
            شكاية جديدة
        </a>

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">
                <tr>
                    <x-sortable-th column="reclamant" class="pe-3 text-muted small fw-semibold">
                        المشتكي
                    </x-sortable-th>
 
                    <x-sortable-th column="type" class="text-muted small fw-semibold">
                         نوع المشتكي
                    </x-sortable-th>
 
                    <x-sortable-th column="objet" class="text-muted small fw-semibold">
                        الموضوع
                    </x-sortable-th>

                    <x-sortable-th column="type_reclamation" class="text-muted small fw-semibold">
                        نوع الشكاية

                    </x-sortable-th>
 
                    <x-sortable-th column="date" class="text-muted small fw-semibold">
                        تاريخ الاستلام
                    </x-sortable-th>
 
                    <x-sortable-th column="statut" class="text-muted small fw-semibold">
                        الحالة
                    </x-sortable-th>
 
                    <th class="text-muted small fw-semibold">
                        آخر إجراء
                    </th>
 
                    <th class="text-muted small fw-semibold text-start ps-3">
                        الإجراءات
                    </th>
                </tr>
            </thead>

            <tbody>

                @forelse($reclamations as $reclamation)

                @php
                    $statut = $reclamation->statut?->statut_reclamation ?? '—';

                    $color  = match(true) {
                        $statut === 'Reçue'     => 'info',
                        $statut === 'En cours'  => 'warning',
                        $statut === 'Clôturée'  => 'success',
                        default                 => 'secondary',
                    };

                    $textColor = match($color) {
                        'warning' => 'text-dark',
                        default   => 'text-'.$color,
                    };

                    $derniereAction = $reclamation->actions->first();
                @endphp

                <tr>

                    <td class="pe-3">
                        <div class="fw-semibold">
                            {{ $reclamation->reclamant?->nom ?? '—' }}
                        </div>

                        @if($reclamation->reclamant?->email)
                            <div class="text-muted small">
                                {{ $reclamation->reclamant->email }}
                            </div>
                        @endif
                    </td>

                    <td>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                            {{ $reclamation->reclamant?->typeReclamant?->type_reclamant ?? '—' }}
                        </span>
                    </td>

                    <td>
                        <a href="{{ route('reclamations.show', $reclamation) }}"
                           class="text-decoration-none fw-semibold text-dark">
                            {{ Str::limit($reclamation->objet, 55) }}
                        </a>
                    </td>

                    <td>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                            {{ $reclamation->typeReclamation?->type_reclamation ?? '—' }}
                        </span>
                    </td>

                    <td class="text-muted small">
                        {{ $reclamation->date_reception?->format('d/m/Y') ?? '—' }}
                    </td>

                    <td>
                        <span class="badge bg-{{ $color }} bg-opacity-15 text-{{ $textColor }} border border-{{ $color }} border-opacity-25">
                            <i class="bi bi-circle-fill ms-1"
                               style="font-size:.45rem;vertical-align:middle"></i>

                            {{ $statut }}
                        </span>
                    </td>

                    <td class="text-muted small">

                        @if($derniereAction)

                            <div>
                                {{ $derniereAction->typeAction?->type_action ?? '—' }}
                            </div>

                            <div class="text-muted"
                                 style="font-size:.72rem">
                                {{ $derniereAction->date_action?->format('d/m/Y') }}
                            </div>

                        @else

                            <span class="text-muted fst-italic">
                                لا توجد إجراءات
                            </span>

                        @endif

                    </td>

                    <td class="text-start ps-3">

                        <div class="d-flex gap-1 justify-content-start">

                            <a href="{{ route('reclamations.show', $reclamation) }}"
                               class="btn btn-sm btn-outline-primary"
                               title="عرض">
                                <i class="bi bi-eye"></i>
                            </a>

                            <a href="{{ route('reclamations.edit', $reclamation) }}"
                               class="btn btn-sm btn-outline-warning"
                               title="تعديل">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <x-modal-delete
                                :action="route('reclamations.destroy', $reclamation)"
                                modal-id="deleteReclamation{{ $reclamation->id }}"
                                title="حذف الشكاية"
                                trigger-label=""
                                :description="'شكاية بتاريخ ' . $reclamation->date_reception->format('d/m/Y')"
                            />

                        </div>

                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">

                        <i class="bi bi-chat-left-x fs-1 d-block mb-2 opacity-25"></i>

                        لا توجد شكايات

                        @if(request()->hasAny([
                            'search',
                            'statut',
                            'type_reclamant',
                            'type_reclamation',
                            'periode',
                            'date_debut',
                            'date_fin'
                        ]))

                            —
                            <a href="{{ route('reclamations.index') }}">
                                إعادة تعيين الفلاتر
                            </a>

                        @endif

                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($reclamations->hasPages())

    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">

        {{ $reclamations->links() }}

        <span class="text-muted small">
            عرض
            {{ $reclamations->firstItem() }}
            -
            {{ $reclamations->lastItem() }}

            من أصل
            {{ $reclamations->total() }}
            شكاية
        </span>

    </div>

    @endif

</div>

</div>

@endsection