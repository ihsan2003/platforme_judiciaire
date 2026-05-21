@extends('layouts.app')

@section('title', 'القضاة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">القضاة</li>
@endsection

@section('content')

{{-- ══ الإحصائيات ══ --}}
<div class="row g-3 mb-4">

    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-person-workspace fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $juges->total() }}</div>
                    <div class="text-muted small">إجمالي القضاة</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-building fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ \App\Models\Tribunal::has('juges')->count() }}
                    </div>
                    <div class="text-muted small">المحاكم المرتبطة</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-calendar-event fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ \App\Models\Juge::whereMonth('created_at', now()->month)->count() }}
                    </div>
                    <div class="text-muted small">هذا الشهر</div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ══ الفلاتر ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">

        <form method="GET" class="row g-2 align-items-end">

            {{-- Recherche --}}
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">
                    بحث
                </label>

                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="اسم القاضي أو المحكمة..."
                       value="{{ request('search') }}">
            </div>

            {{-- Spécialité --}}
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">
                    التخصص
                </label>

                <select name="specialisation" class="form-select">

                    <option value="">كل التخصصات</option>

                    @foreach(
                        \App\Models\Juge::select('specialisation')
                            ->distinct()
                            ->orderBy('specialisation')
                            ->pluck('specialisation') as $specialisation
                    )

                        <option value="{{ $specialisation }}"
                            @selected(request('specialisation') == $specialisation)>
                            {{ $specialisation }}
                        </option>

                    @endforeach

                </select>
            </div>

            {{-- Buttons --}}
            <div class="col-md-1 d-flex gap-2">

                <button class="btn btn-primary flex-fill">
                    <i class="bi bi-funnel-fill me-1"></i>
                </button>

                <a href="{{ route('juges.index') }}"
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
            <i class="bi bi-person-workspace me-2 text-primary"></i>
            القضاة
            <span class="badge bg-primary ms-2">{{ $juges->total() }}</span>
        </h5>

        <a href="{{ route('juges.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>
            قاضٍ جديد
        </a>

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">
                <tr>
                    <th class="ps-3 text-muted small fw-semibold">القاضي</th>
                    <th class="text-muted small fw-semibold">الدرجة</th>
                    <th class="text-muted small fw-semibold">التخصص</th>
                    <th class="text-muted small fw-semibold">المحكمة</th>
                    <th class="text-muted small fw-semibold">الجلسات القادمة</th>
                    <th class="pe-3 text-muted small fw-semibold">الإجراءات</th>
                </tr>
            </thead>

            <tbody>

                @forelse($juges as $juge)
                <tr>

                    <td class="ps-3">
                        <div class="d-flex align-items-center gap-3">

                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:38px;height:38px">
                                <span class="fw-semibold text-primary small">
                                    {{ strtoupper(substr($juge->nom_complet, 0, 2)) }}
                                </span>
                            </div>

                            <div>
                                <div class="fw-semibold">{{ $juge->nom_complet }}</div>
                                <div class="text-muted small">قاضٍ</div>
                            </div>

                        </div>
                    </td>

                    <td>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                            {{ $juge->grade ?? '—' }}
                        </span>
                    </td>

                    <td class="text-muted small">
                        {{ $juge->specialisation ?? '—' }}
                    </td>

                    <td>
                        @if($juge->tribunal)
                            <span class="badge bg-info bg-opacity-15 text-white border border-info border-opacity-25">
                                <i class="bi bi-building me-1"></i>
                                {{ $juge->tribunal->nom_tribunal }}
                            </span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>

                    <td>
                        @php
                            $nbAud = $juge->audiences()
                                ->whereDate('date_audience', '>=', today())
                                ->count();
                        @endphp

                        @if($nbAud > 0)
                            <span class="badge bg-warning bg-opacity-15 text-black border border-warning border-opacity-25">
                                <i class="bi bi-calendar-event me-1"></i>
                                {{ $nbAud }} جلسة
                            </span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                لا يوجد
                            </span>
                        @endif
                    </td>

                    <td class="pe-3">
                        <div class="d-flex gap-1">

                            <a href="{{ route('juges.show', $juge) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>

                            <a href="{{ route('juges.edit', $juge) }}"
                               class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form action="{{ route('juges.destroy', $juge) }}" method="POST"
                                  onsubmit="return confirm('هل تريد حذف هذا القاضي؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>

                        </div>
                    </td>

                </tr>
                @empty

                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-person-x fs-1 d-block mb-2 opacity-25"></i>
                        لا يوجد قضاة
                        @if(request()->hasAny(['search', 'tribunal']))
                            — <a href="{{ route('juges.index') }}">إعادة التصفية</a>
                        @endif
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($juges->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">

        <span class="text-muted small">
            عرض {{ $juges->firstItem() }} - {{ $juges->lastItem() }}
            من أصل {{ $juges->total() }} قاضٍ
        </span>

        {{ $juges->links() }}

    </div>
    @endif

</div>

@endsection