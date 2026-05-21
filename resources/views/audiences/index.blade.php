{{-- resources/views/audiences/index.blade.php --}}
@extends('layouts.app')

@section('title', 'الجلسات')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الجلسات</li>
@endsection

@section('content')

{{-- ══ إحصائيات سريعة ══ --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-calendar-check fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['aujourd_hui'] }}</div>
                    <div class="text-muted small">جلسات اليوم</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-calendar-week fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['cette_semaine'] }}</div>
                    <div class="text-muted small">هذا الأسبوع</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                    <i class="bi bi-exclamation-triangle fs-4 text-danger"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['passees_sans_suite'] }}</div>
                    <div class="text-muted small">بدون إجراءات</div>
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
                <label class="form-label small text-muted fw-semibold">القاضي</label>
                <select name="juge" class="form-select ">
                    <option value="">جميع القضاة</option>
                    @foreach($juges as $juge)
                        <option value="{{ $juge->id }}" @selected(request('juge') == $juge->id)>
                            {{ $juge->nom_complet }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold">نوع الجلسة</label>
                <select name="type" class="form-select ">
                    <option value="">جميع الأنواع</option>
                    @foreach($typesAudience as $type)
                        <option value="{{ $type->id }}" @selected(request('type') == $type->id)>
                            {{ $type->type_audience }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex gap-1">

                <button class="btn btn-primary flex-fill" title="تصفية">
                    <i class="bi bi-funnel-fill"></i>
                </button>

                <a href="{{ route('audiences.index') }}"
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
            <i class="bi bi-gavel ml-2 text-primary"></i>مدرج الجلسات
            <span class="badge bg-primary mr-2">{{ $audiences->total() }}</span>
        </h5>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 text-end">
            <thead class="table-light">
                <tr>
                    <th class="pr-3 text-muted small fw-semibold">التاريخ</th>
                    <th class="text-muted small fw-semibold">رقم الملف</th>
                    <th class="text-muted small fw-semibold">المحكمة</th>
                    <th class="text-muted small fw-semibold">النوع</th>
                    <th class="text-muted small fw-semibold">القاضي</th>
                    <th class="text-muted small fw-semibold">الحضور</th>
                    <th class="text-muted small fw-semibold">الجلسة القادمة</th>
                    <th class="text-start pl-3 text-muted small fw-semibold">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($audiences as $audience)
                <tr class="{{ $audience->est_today ? 'table-warning' : '' }}">
                    <td class="pr-3">
                        <span class="fw-semibold">{{ $audience->date_audience->format('Y/m/d') }}</span>
                        @if($audience->est_today)
                            <span class="badge bg-warning text-dark mr-1">اليوم</span>
                        @elseif($audience->est_passee)
                            <span class="badge bg-secondary mr-1">سابقة</span>
                        @else
                            <span class="badge bg-success mr-1">قادمة</span>
                        @endif
                    </td>
                    <td>
                        @if($audience->dossierTribunal?->dossier)
                            <a href="{{ route('dossiers.show', $audience->dossierTribunal->dossier) }}"
                               class="text-decoration-none fw-semibold text-primary">
                                {{ $audience->dossierTribunal->dossier->numero_dossier_interne }}
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-muted small">
                        {{ $audience->dossierTribunal?->tribunal?->nom_tribunal ?? '—' }}
                    </td>
                    <td>
                        <span class="badge bg-info bg-opacity-15 text-dark border border-info border-opacity-25">
                            {{ $audience->typeAudience?->type_audience ?? '—' }}
                        </span>
                    </td>
                    <td class="text-muted small">
                        {{ $audience->juge?->nom_complet ?? '—' }}
                    </td>
                    <td>
                        <span class="ml-2" title="المدعي">
                            <i class="bi bi-person-fill {{ $audience->presence_demandeur ? 'text-success' : 'text-danger opacity-25' }}"></i>
                        </span>
                        <span title="المدعى عليه">
                            <i class="bi bi-person-fill {{ $audience->presence_defendeur ? 'text-success' : 'text-danger opacity-25' }}"></i>
                        </span>
                    </td>
                    <td class="text-muted small">
                        {{ $audience->date_prochaine_audience?->format('Y/m/d') ?? '—' }}
                    </td>
                    <td class="text-start pl-3">
                        <div class="d-flex gap-1 justify-content-start">
                            <a href="{{ route('audiences.show', $audience) }}"
                               class="btn btn-sm btn-outline-primary" title="عرض">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('audiences.edit', $audience) }}"
                               class="btn btn-sm btn-outline-warning" title="تعديل">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <x-modal-delete
                                :action="route('audiences.destroy', $audience)"
                                modal-id="deleteAudience{{ $audience->id }}"
                                title="حذف الجلسة"
                                trigger-label=""
                                :description="'جلسة بتاريخ ' . $audience->date_audience->format('Y/m/d')"
                            />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-25"></i>
                        لم يتم العثور على أي جلسات
                        @if(request()->hasAny(['juge','type','periode']))
                            — <a href="{{ route('audiences.index') }}">إعادة ضبط الفلاتر</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($audiences->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <span class="text-muted small">
            عرض {{ $audiences->firstItem() }} إلى {{ $audiences->lastItem() }}
            من أصل {{ $audiences->total() }} جلسة
        </span>
        {{ $audiences->links() }}
    </div>
    @endif
</div>

@endsection