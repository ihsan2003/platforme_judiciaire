@extends('layouts.app')

@section('title', 'الهيكليات')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الهيكليات</li>
@endsection

@section('content')

{{-- ══ الإحصائيات ══ --}}
@php
    $totalStructures = $structures->count() + $structures->sum(fn($s) => $s->enfants->count());
    $totalParents    = $structures->count();
    $totalEnfants    = $structures->sum(fn($s) => $s->enfants->count());
@endphp

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-diagram-3 fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $totalStructures }}</div>
                    <div class="text-muted small">إجمالي الهيكليات</div>
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
                    <div class="fs-2 fw-bold lh-1">{{ $totalParents }}</div>
                    <div class="text-muted small">الهيكليات الرئيسية</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-diagram-2 fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $totalEnfants }}</div>
                    <div class="text-muted small">الهيكليات الفرعية</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ الجدول ══ --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <h5 class="mb-0 fw-semibold">
            <i class="bi bi-diagram-3 me-2 text-primary"></i>الهيكل التنظيمي
            <span class="badge bg-primary ms-2">{{ $totalStructures }}</span>
        </h5>
        <a href="{{ route('admin.structures.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>هيكل جديد
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3 text-muted small fw-semibold">الهيكل</th>
                    <th class="text-muted small fw-semibold">النوع</th>
                    <th class="text-muted small fw-semibold">الهيكليات الفرعية</th>
                    <th class="pe-3 text-muted small fw-semibold">الإجراءات</th>
                </tr>
            </thead>

            <tbody>
                @forelse($structures as $structure)

                {{-- ── الهيكل الرئيسي ── --}}
                <tr class="fw-semibold">
                    <td class="ps-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary bg-opacity-15 d-flex align-items-center justify-content-center text-white"
                                 style="width:36px;height:36px;flex-shrink:0">
                                <i class="bi bi-building-fill fs-6"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $structure->nom }}</div>
                                @if($structure->enfants->count() > 0)
                                    <div class="text-muted" style="font-size:.72rem">
                                        {{ $structure->enfants->count() }} هيكل فرعي
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td>
                        <span class="badge bg-primary bg-opacity-15 text-white border border-primary border-opacity-25">
                            {{ $structure->typeStructure?->type_structure ?? '—' }}
                        </span>
                    </td>

                    <td>
                        @if($structure->enfants->count() > 0)
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-diagram-2 me-1"></i>{{ $structure->enfants->count() }}
                            </span>
                        @else
                            <span class="text-muted small fst-italic">لا يوجد</span>
                        @endif
                    </td>

                    <td class="pe-3">
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.structures.show', $structure) }}"
                               class="btn btn-sm btn-outline-primary" title="عرض">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.structures.edit', $structure) }}"
                               class="btn btn-sm btn-outline-warning" title="تعديل">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.structures.destroy', $structure) }}" method="POST"
                                  onsubmit="return confirm('هل تريد حذف « {{ $structure->nom }} » وجميع الهياكل الفرعية؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="حذف">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                {{-- ── الهيكليات الفرعية ── --}}
                @foreach($structure->enfants as $enfant)
                <tr class="table-light">
                    <td class="ps-3">
                        <div class="d-flex align-items-center gap-2" style="padding-left: 2rem">
                            <i class="bi bi-arrow-return-left text-muted me-1"></i>
                            <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center text-secondary"
                                 style="width:30px;height:30px;flex-shrink:0">
                                <i class="bi bi-building fs-6"></i>
                            </div>
                            <span class="small fw-semibold text-muted">{{ $enfant->nom }}</span>
                        </div>
                    </td>

                    <td>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25" style="font-size:.7rem">
                            {{ $enfant->typeStructure?->type_structure ?? '—' }}
                        </span>
                    </td>

                    <td></td>

                    <td class="pe-3">
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.structures.show', $enfant) }}"
                               class="btn btn-sm btn-outline-primary" title="عرض" style="padding:.2rem .45rem">
                                <i class="bi bi-eye" style="font-size:.75rem"></i>
                            </a>
                            <a href="{{ route('admin.structures.edit', $enfant) }}"
                               class="btn btn-sm btn-outline-warning" title="تعديل" style="padding:.2rem .45rem">
                                <i class="bi bi-pencil" style="font-size:.75rem"></i>
                            </a>
                            <form action="{{ route('admin.structures.destroy', $enfant) }}" method="POST"
                                  onsubmit="return confirm('هل تريد حذف « {{ $enfant->nom }} »؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="حذف" style="padding:.2rem .45rem">
                                    <i class="bi bi-trash" style="font-size:.75rem"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach

                @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                        <i class="bi bi-diagram-3 fs-1 d-block mb-2 opacity-25"></i>
                        لا توجد هيكليات
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection