@extends('layouts.app')

@section('title', $avocat->nom_avocat)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('avocats.index') }}">المحامون</a></li>
    <li class="breadcrumb-item active">{{ $avocat->nom_avocat }}</li>
@endsection

@section('content')

{{-- ══ HEADER ══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">

            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:56px;height:56px">
                    <span class="fw-bold text-primary fs-5">
                        {{ strtoupper(substr($avocat->nom_avocat, 0, 2)) }}
                    </span>
                </div>
                <div>
                    <h4 class="fw-bold mb-0">{{ $avocat->nom_avocat }}</h4>
                    <div class="text-muted small mt-1">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                            <i class="bi bi-person-badge me-1"></i>محامٍ
                        </span>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('avocats.edit', $avocat) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>تعديل
                </a>

                @if($avocat->parties->count() > 0)
                    <button class="btn btn-outline-danger btn-sm" disabled title="غير ممكن: مرتبط بملفات">
                        <i class="bi bi-trash me-1"></i>حذف
                    </button>
                @else
                    <form action="{{ route('avocats.destroy', $avocat) }}" method="POST"
                          onsubmit="return confirm('هل تريد حذف هذا المحامي؟')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash me-1"></i>حذف
                        </button>
                    </form>
                @endif

                <a href="{{ route('avocats.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>رجوع
                </a>
            </div>
        </div>

        <hr class="my-3">

        {{-- CONTACT --}}
        <div class="row g-2 small text-muted">
            <div class="col-sm-4">
                <i class="bi bi-telephone me-1"></i>
                <strong>الهاتف:</strong>
                {{ $avocat->telephone ?? '—' }}
            </div>

            <div class="col-sm-4">
                <i class="bi bi-envelope me-1"></i>
                <strong>البريد الإلكتروني:</strong>
                {{ $avocat->email ?? '—' }}
            </div>

            <div class="col-sm-4">
                <i class="bi bi-clock me-1"></i>
                <strong>آخر تحديث:</strong>
                {{ $avocat->updated_at->diffForHumans() }}
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ══ PARTIES ══ --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-people me-2 text-primary"></i>
                    الأطراف المرتبطة
                    <span class="badge bg-primary ms-1">{{ $avocat->parties->count() }}</span>
                </h6>
            </div>

            <div class="card-body p-0">

                @if($avocat->parties->isEmpty())
                    <div class="text-center p-5 text-muted">
                        لا توجد أطراف مرتبطة بهذا المحامي.
                    </div>
                @else

                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الطرف</th>
                            <th>الهاتف</th>
                            <th>البريد</th>
                            <th>عدد القضايا</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($avocat->parties as $partie)
                        <tr>
                            <td class="fw-semibold">{{ $partie->nom_partie }}</td>
                            <td>{{ $partie->telephone ?? '—' }}</td>
                            <td>{{ $partie->email ?? '—' }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $partie->dossiers->count() }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                @endif

            </div>
        </div>
    </div>

    {{-- ══ SIDE ══ --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">معلومات</h6>
            </div>

            <div class="card-body small">
                <div class="d-flex justify-content-between">
                    <span class="text-muted">تاريخ الإنشاء</span>
                    <span>{{ $avocat->created_at->format('d/m/Y') }}</span>
                </div>

                <div class="d-flex justify-content-between mt-2">
                    <span class="text-muted">آخر تعديل</span>
                    <span>{{ $avocat->updated_at->format('d/m/Y') }}</span>
                </div>

                <div class="d-flex justify-content-between mt-2">
                    <span class="text-muted">عدد الأطراف</span>
                    <span class="badge bg-primary">{{ $avocat->parties->count() }}</span>
                </div>
            </div>
        </div>

        {{-- WARNING --}}
        @if($avocat->parties->count() > 0)
        <div class="card border-0 shadow-sm mt-3 border-start border-warning border-3">
            <div class="card-body small">
                <div class="fw-semibold mb-1 text-warning">
                    لا يمكن الحذف
                </div>
                <div class="text-muted">
                    هذا المحامي مرتبط بـ
                    <strong>{{ $avocat->parties->count() }}</strong>
                    طرف/أطراف.
                </div>
            </div>
        </div>
        @endif

    </div>

</div>

@endsection