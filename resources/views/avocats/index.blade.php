@extends('layouts.app')

@section('title', 'المحامون')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">المحامون</li>
@endsection

@section('content')

{{-- ══ STATS ══ --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-person-badge fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $avocats->total() }}</div>
                    <div class="text-muted small">إجمالي المحامين</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-briefcase fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">
                        {{ \App\Models\Avocat::has('Parties')->count() }}
                    </div>
                    <div class="text-muted small">مع ملفات نشطة</div>
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
                    <div class="fs-2 fw-bold lh-1">
                        {{ \App\Models\Avocat::whereMonth('created_at', now()->month)->count() }}
                    </div>
                    <div class="text-muted small">هذا الشهر</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ FILTRES ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">بحث</label>
                <div class="input-group">
                    <input type="text"
                           name="search"
                           class="form-control border-start-0"
                           placeholder="الاسم، البريد الإلكتروني أو الهاتف..."
                           value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">ترتيب حسب</label>
                <select name="sort" class="form-select">
                    <option value="nom_avocat" @selected(request('sort','nom_avocat') === 'nom_avocat')>الاسم (أ→ي)</option>
                    <option value="created_at" @selected(request('sort') === 'created_at')>الأحدث</option>
                </select>
            </div>

            <div class="col-md-1 d-flex gap-2">
                <button class="btn btn-primary flex-fill" title="تصفية">
                    <i class="bi bi-funnel-fill me-1"></i>
                </button>
                <a href="{{ route('avocats.index') }}" class="btn btn-outline-secondary" title="إعادة تعيين">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ══ TABLE ══ --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <h5 class="mb-0 fw-semibold">
            <i class="bi bi-person-badge me-2 text-primary"></i>المحامون
            <span class="badge bg-primary ms-2">{{ $avocats->total() }}</span>
        </h5>
        <a href="{{ route('avocats.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>محامٍ جديد
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <x-sortable-th column="nom" class="ps-3 text-muted small fw-semibold">المحامي</x-sortable-th>
                    <x-sortable-th column="telephone" class="text-muted small fw-semibold">الهاتف</x-sortable-th>
                    <x-sortable-th column="email" class="text-muted small fw-semibold">البريد الإلكتروني</x-sortable-th>
                    <th class="pe-3 text-muted small fw-semibold">الملفات</th>
                    <th class="pe-3 text-muted small fw-semibold">الإجراءات</th>
                </tr>
            </thead>

            <tbody>
                @forelse($avocats as $avocat)
                <tr>
                    <td class="ps-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:38px;height:38px">
                                <span class="fw-semibold text-primary small">
                                    {{ strtoupper(substr($avocat->nom_avocat, 0, 2)) }}
                                </span>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $avocat->nom_avocat }}</div>
                                <div class="text-muted small">محامٍ</div>
                            </div>
                        </div>
                    </td>

                    <td>
                        @if($avocat->telephone)
                            <a href="tel:{{ $avocat->telephone }}"
                               class="text-decoration-none text-muted small">
                                <i class="bi bi-telephone me-1"></i>{{ $avocat->telephone }}
                            </a>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>

                    <td>
                        @if($avocat->email)
                            <a href="mailto:{{ $avocat->email }}"
                               class="text-decoration-none text-muted small">
                                <i class="bi bi-envelope me-1"></i>{{ $avocat->email }}
                            </a>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>

                    <td>
                        @php $nb = $avocat->dossiers()->count(); @endphp
                        @if($nb > 0)
                            <span class="badge bg-info bg-opacity-15 text-white border border-info border-opacity-25">
                                <i class="bi bi-folder2 me-1"></i>{{ $nb }} ملف{{ $nb > 1 ? 'ات' : '' }}
                            </span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                لا توجد ملفات
                            </span>
                        @endif
                    </td>

                    <td class="pe-3">
                        <div class="d-flex gap-1">
                            <a href="{{ route('avocats.show', $avocat) }}"
                               class="btn btn-sm btn-outline-primary" title="عرض">
                                <i class="bi bi-eye"></i>
                            </a>

                            <a href="{{ route('avocats.edit', $avocat) }}"
                               class="btn btn-sm btn-outline-warning" title="تعديل">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form action="{{ route('avocats.destroy', $avocat) }}" method="POST"
                                  onsubmit="return confirm('هل تريد حذف هذا المحامي؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="حذف">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-person-x fs-1 d-block mb-2 opacity-25"></i>
                        لم يتم العثور على أي محامٍ
                        @if(request('search'))
                            للبحث « {{ request('search') }} »
                            — <a href="{{ route('avocats.index') }}">إعادة تعيين</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($avocats->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
        <span class="text-muted small">
            عرض {{ $avocats->firstItem() }}–{{ $avocats->lastItem() }}
            من أصل {{ $avocats->total() }} محامٍ
        </span>
        {{ $avocats->links() }}
    </div>
    @endif
</div>

@endsection