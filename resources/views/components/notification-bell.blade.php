{{--
    resources/views/components/notification-bell.blade.php
    مكوّن جرس الإشعارات — نسخة عربية RTL
--}}

@php
    $countNonLues = \App\Models\Notification::pourUtilisateur(auth()->id())->nonLues()->count();
    $dangerCount  = \App\Models\Notification::pourUtilisateur(auth()->id())->nonLues()->parNiveau('danger')->count();
    $badgeColor   = $dangerCount > 0 ? 'danger' : 'warning';
@endphp

<div class="dropdown" id="notif-dropdown">

    <button class="btn btn-sm btn-light position-relative"
            id="notifBtn"
            data-bs-toggle="dropdown"
            data-bs-auto-close="outside"
            aria-expanded="false"
            title="الإشعارات">

        <i class="bi bi-bell fs-5 {{ $countNonLues > 0 ? 'text-' . $badgeColor : 'text-muted' }}"></i>

        @if($countNonLues > 0)
            <span class="position-absolute top-0 translate-middle badge rounded-pill bg-{{ $badgeColor }}"
                  id="notif-badge"
                  style="font-size:.65rem; transform: translate(-60%,-30%) !important;">
                {{ $countNonLues > 99 ? '99+' : $countNonLues }}
            </span>
        @else
            <span class="position-absolute top-0 translate-middle badge rounded-pill bg-{{ $badgeColor }} d-none"
                  id="notif-badge"
                  style="font-size:.65rem; transform: translate(-60%,-30%) !important;">
                0
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div class="dropdown-menu dropdown-menu-start shadow-lg p-0"
         style="width: 380px; max-height: 520px; border-radius: 12px; overflow: hidden;"
         id="notif-panel">

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom bg-white"
             style="border-radius: 12px 12px 0 0;">

            <span class="fw-semibold" style="font-size:.9rem;">
                <i class="bi bi-bell ms-2 text-primary"></i>
                الإشعارات

                <span class="badge bg-secondary me-1"
                      id="notif-count-header">
                    {{ $countNonLues }}
                </span>
            </span>

            <div class="d-flex gap-2">

                <button class="btn btn-xs btn-outline-secondary py-0 px-2"
                        style="font-size:.75rem;"
                        id="btn-tout-lire"
                        title="تحديد الكل كمقروء">

                    <i class="bi bi-check-all ms-1"></i>
                    قراءة الكل
                </button>

                <a href="{{ route('notifications.index') }}"
                   class="btn btn-xs btn-outline-primary py-0 px-2"
                   style="font-size:.75rem;">

                    عرض الكل
                </a>
            </div>
        </div>

        {{-- Notifications list --}}
        <div id="notif-list"
             style="overflow-y: auto; max-height: 400px;">

            <div class="text-center py-4 text-muted small"
                 id="notif-loading">

                <div class="spinner-border spinner-border-sm ms-2"
                     role="status"></div>

                جاري التحميل...
            </div>
        </div>

        {{-- Footer --}}
        <div class="border-top px-3 py-2 bg-light text-center"
             style="border-radius: 0 0 12px 12px;">

            <a href="{{ route('notifications.index') }}"
               class="text-decoration-none small text-primary">

                إدارة جميع الإشعارات ←
            </a>
        </div>
    </div>
</div>

@once

@push('styles')
    @vite('resources/css/notification-bell.css')
@endpush

@push('scripts')
<script>
    window.pageData = {
        notifRoutes: {
            toutLire: "{{ route('notifications.tout-lire') }}",
            dropdown: "{{ route('notifications.dropdown') }}",
            compteur: "{{ route('notifications.compteur') }}",
        }
    };
</script>
@vite('resources/js/notification-bell.js')
@endpush
@endonce