{{-- resources/views/notifications/index.blade.php --}}
@extends('layouts.app')

@section('title', 'الإشعارات')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">الرئيسية</a>
    </li>
    <li class="breadcrumb-item active">الإشعارات</li>
@endsection

@section('content')

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">

    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-bell ms-2 text-primary"></i>
            الإشعارات
        </h4>

        <p class="text-muted small mb-0">
            التنبيهات والتذكير بالمواعيد
        </p>
    </div>

    <div class="d-flex gap-2 flex-wrap">

        {{-- Refresh --}}
        @can('manage users')
        <form method="POST" action="{{ route('notifications.generer') }}">
            @csrf

            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-repeat ms-1"></i>
                تحديث
            </button>
        </form>
        @endcan

        {{-- Mark all as read --}}
        @if($stats['non_lues'] > 0)
        <form method="POST" action="{{ route('notifications.tout-lire') }}">
            @csrf

            <button type="submit" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-check-all ms-1"></i>
                تحديد الكل كمقروء
            </button>
        </form>
        @endif

    </div>
</div>

{{-- Statistics --}}
<div class="row g-3 mb-4">

    @foreach([
        ['label' => 'غير مقروءة', 'value' => $stats['non_lues'], 'icon' => 'bell-fill', 'color' => 'primary'],
        ['label' => 'عاجلة', 'value' => $stats['danger'], 'icon' => 'exclamation-octagon', 'color' => 'danger'],
        ['label' => 'تنبيه', 'value' => $stats['warning'], 'icon' => 'exclamation-triangle', 'color' => 'warning'],
        ['label' => 'الإجمالي', 'value' => $stats['total'], 'icon' => 'bell', 'color' => 'secondary'],
    ] as $s)

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">

            <div class="card-body text-center py-3">

                <div class="rounded-circle bg-{{ $s['color'] }} bg-opacity-10 p-2 d-inline-flex mb-2">
                    <i class="bi bi-{{ $s['icon'] }} fs-5 text-{{ $s['color'] }}"></i>
                </div>

                <div class="fs-3 fw-bold lh-1 mb-1">
                    {{ $s['value'] }}
                </div>

                <div class="text-muted small">
                    {{ $s['label'] }}
                </div>

            </div>
        </div>
    </div>

    @endforeach

</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">

    <div class="card-body py-2">

        <form method="GET" class="d-flex gap-3 align-items-center flex-wrap">

            <div class="form-check mb-0">

                <input class="form-check-input"
                       type="checkbox"
                       name="non_lues"
                       value="1"
                       id="filterNonLues"
                       {{ request()->boolean('non_lues') ? 'checked' : '' }}
                       onchange="this.form.submit()">

                <label class="form-check-label small" for="filterNonLues">
                    غير المقروءة فقط
                </label>

            </div>

            <select name="niveau"
                    class="form-select form-select-sm w-auto"
                    onchange="this.form.submit()">

                <option value="">جميع المستويات</option>

                <option value="danger"
                    {{ request('niveau') === 'danger' ? 'selected' : '' }}>
                    🔴 عاجل
                </option>

                <option value="warning"
                    {{ request('niveau') === 'warning' ? 'selected' : '' }}>
                    🟡 تنبيه
                </option>

                <option value="info"
                    {{ request('niveau') === 'info' ? 'selected' : '' }}>
                    🔵 معلومات
                </option>

            </select>

            @if(request()->hasAny(['non_lues', 'niveau']))

                <a href="{{ route('notifications.index') }}"
                   class="btn btn-sm btn-outline-secondary">

                    <i class="bi bi-x-circle ms-1"></i>
                    إعادة التعيين
                </a>

            @endif

        </form>

    </div>

</div>

{{-- Notifications List --}}
<div class="card border-0 shadow-sm">

    @forelse($notifications as $notif)

    <div class="notif-row d-flex align-items-start gap-3 px-4 py-3 border-bottom
        {{ !$notif->est_lue ? 'bg-notif-unread' : '' }}"
        id="notif-row-{{ $notif->id }}">

        {{-- Icon --}}
        <div class="notif-icon-lg flex-shrink-0 {{ $notif->couleur }}">
            <i class="bi {{ $notif->icone }}"></i>
        </div>

        {{-- Content --}}
        <div class="flex-grow-1 min-w-0">

            <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">

                <div>

                    <span class="badge bg-{{ $notif->couleur }}
                        bg-opacity-15
                        text-{{ $notif->couleur }}
                        border
                        border-{{ $notif->couleur }}
                        border-opacity-25 mb-1"
                        style="font-size:.7rem;">

                        {{ $notif->categorie }}

                    </span>

                    <div class="fw-semibold" style="font-size:.9rem;">
                        {{ $notif->message }}
                    </div>

                    @if($notif->details)
                    <div class="text-muted small mt-1">
                        {{ $notif->details }}
                    </div>
                    @endif

                    <div class="text-muted"
                         style="font-size:.75rem; margin-top:4px;">

                        <i class="bi bi-clock ms-1"></i>
                        {{ $notif->created_at->diffForHumans() }}

                        @if($notif->est_lue && $notif->date_lecture)

                            &nbsp;·&nbsp;

                            <i class="bi bi-check2-all ms-1 text-success"></i>

                            تمت القراءة
                            {{ $notif->date_lecture->diffForHumans() }}

                        @endif

                    </div>

                </div>

                {{-- Actions --}}
                <div class="d-flex gap-1 flex-shrink-0">

                    {{-- View --}}
                    @if($notif->url_action)

                    <form method="POST"
                          action="{{ route('notifications.lire', $notif) }}">

                        @csrf

                        <button type="submit"
                                class="btn btn-sm btn-{{ $notif->couleur }} px-3"
                                style="font-size:.78rem;">

                            <i class="bi bi-arrow-left ms-1"></i>
                            عرض

                        </button>

                    </form>

                    @endif

                    {{-- Mark as read --}}
                    @if(!$notif->est_lue)

                    <form method="POST"
                          action="{{ route('notifications.lire', $notif) }}">

                        @csrf

                        <button type="submit"
                                class="btn btn-sm btn-outline-secondary px-2"
                                style="font-size:.78rem;"
                                title="تحديد كمقروء">

                            <i class="bi bi-check2"></i>

                        </button>

                    </form>

                    @endif

                    {{-- Delete --}}
                    <form method="POST"
                          action="{{ route('notifications.destroy', $notif) }}">

                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="btn btn-sm btn-outline-danger px-2"
                                style="font-size:.78rem;"
                                title="حذف"
                                onclick="return confirm('هل تريد حذف هذا الإشعار؟')">

                            <i class="bi bi-trash3"></i>

                        </button>

                    </form>

                </div>

            </div>

        </div>

        {{-- Unread Dot --}}
        @if(!$notif->est_lue)

        <div class="flex-shrink-0 pt-2">
            <span class="dot-indicator bg-{{ $notif->couleur }}"></span>
        </div>

        @endif

    </div>

    @empty

    <div class="text-center py-5 text-muted">

        <i class="bi bi-bell-slash fs-1 d-block mb-3 opacity-25"></i>

        <p class="mb-0">

            لا توجد إشعارات

            {{ request()->hasAny(['non_lues', 'niveau']) ? ' مطابقة للفلاتر' : '' }}

        </p>

        @if(request()->hasAny(['non_lues', 'niveau']))

        <a href="{{ route('notifications.index') }}"
           class="btn btn-sm btn-outline-primary mt-2">

            عرض الكل

        </a>

        @endif

    </div>

    @endforelse

</div>

{{-- Pagination --}}
@if($notifications->hasPages())

<div class="mt-3">
    {{ $notifications->links() }}
</div>

@endif

@endsection

@push('styles')

<style>

    .bg-notif-unread {
        background: #fffbf0 !important;
    }

    .notif-row {
        transition: background .15s;
    }

    .notif-row:hover {
        background: #f8f9ff !important;
    }

    .notif-row:last-child {
        border-bottom: none !important;
    }

    .notif-icon-lg {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .notif-icon-lg.danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .notif-icon-lg.warning {
        background: #fef3c7;
        color: #d97706;
    }

    .notif-icon-lg.info {
        background: #dbeafe;
        color: #2563eb;
    }

    .notif-icon-lg.secondary {
        background: #f3f4f6;
        color: #6b7280;
    }

    .dot-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    html[dir="rtl"] .breadcrumb-item + .breadcrumb-item::before {
        float: right;
        padding-left: .5rem;
        padding-right: 0;
    }

</style>

@endpush