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
<style>

    /* Bell Animation */
    @keyframes bellShake {
        0%, 100% { transform: rotate(0deg); }
        15%      { transform: rotate(15deg); }
        30%      { transform: rotate(-13deg); }
        45%      { transform: rotate(10deg); }
        60%      { transform: rotate(-8deg); }
        75%      { transform: rotate(5deg); }
    }

    #notifBtn:hover .bi-bell {
        animation: bellShake .5s ease-in-out;
    }

    /* Notification Item */
    .notif-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 14px;
        border-bottom: 1px solid #f3f4f6;
        cursor: pointer;
        transition: background .15s;
        text-decoration: none;
        color: inherit;
        direction: rtl;
        text-align: right;
    }

    .notif-item:hover {
        background: #f8f9ff;
    }

    .notif-item.non-lue {
        background: #fff8f0;
    }

    .notif-item.non-lue:hover {
        background: #fff3e0;
    }

    .notif-icon {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: .95rem;
    }

    .notif-icon.danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .notif-icon.warning {
        background: #fef3c7;
        color: #d97706;
    }

    .notif-icon.info {
        background: #dbeafe;
        color: #2563eb;
    }

    .notif-message {
        font-size: .82rem;
        font-weight: 500;
        line-height: 1.3;
        color: #111827;
    }

    .notif-details {
        font-size: .75rem;
        color: #6b7280;
        margin-top: 2px;
    }

    .notif-time {
        font-size: .7rem;
        color: #9ca3af;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .dot-non-lue {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #f59e0b;
        flex-shrink: 0;
        margin-top: 6px;
    }

</style>
@endpush

@push('scripts')

<script>

(function () {

    const btn       = document.getElementById('notifBtn');
    const list      = document.getElementById('notif-list');
    const badge     = document.getElementById('notif-badge');
    const header    = document.getElementById('notif-count-header');
    const loading   = document.getElementById('notif-loading');
    const btnTout   = document.getElementById('btn-tout-lire');

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute('content');

    let loaded = false;

    /* Load notifications on first click */
    btn.addEventListener('show.bs.dropdown', function () {

        if (!loaded) {
            chargerNotifications();
        }
    });

    /* Mark all as read */
    btnTout?.addEventListener('click', async function (e) {

        e.stopPropagation();

        try {

            const res = await fetch('{{ route('notifications.tout-lire') }}', {

                method: 'POST',

                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            });

            const data = await res.json();

            if (data.success) {

                mettreAJourBadge(0);

                loaded = false;

                chargerNotifications();
            }

        } catch (err) {

            console.error('Erreur:', err);
        }
    });

    /* AJAX Load */
    async function chargerNotifications() {

        if (loading) {
            loading.style.display = 'block';
        }

        try {

            const res = await fetch('{{ route('notifications.dropdown') }}', {

                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
            });

            const data = await res.json();

            mettreAJourBadge(data.total_non_lues);

            renderNotifications(data.notifications);

            loaded = true;

        } catch (err) {

            list.innerHTML = `
                <p class="text-center text-danger small py-3">
                    خطأ أثناء تحميل الإشعارات
                </p>`;
        }
    }

    /* Render HTML */
    function renderNotifications(items) {

        if (!items || items.length === 0) {

            list.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-bell-slash fs-2 d-block mb-2 opacity-25"></i>
                    <span class="small">لا توجد إشعارات جديدة</span>
                </div>`;

            return;
        }

        const html = items.map(n => `

            <a class="notif-item non-lue"
               href="${n.url_action || '#'}"
               data-id="${n.id}"
               onclick="marquerLue(event, ${n.id}, '${n.url_action || ''}')">

                <div class="notif-icon ${n.niveau}">
                    <i class="bi ${n.icone}"></i>
                </div>

                <div class="flex-grow-1 min-w-0">

                    <div class="notif-message">
                        ${htmlEscape(n.message)}
                    </div>

                    ${n.details
                        ? `<div class="notif-details">${htmlEscape(n.details)}</div>`
                        : ''
                    }

                    <div class="notif-time mt-1">
                        ${n.temps}
                    </div>
                </div>

                <div class="dot-non-lue"></div>

            </a>

        `).join('');

        list.innerHTML = html;
    }

    /* Mark one notification as read */
    window.marquerLue = async function (event, id, urlAction) {

        event.preventDefault();

        try {

            await fetch(`/notifications/${id}/lire`, {

                method: 'POST',

                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
            });

        } catch (e) {}

        if (urlAction) {

            window.location.href = urlAction;

        } else {

            const item = document.querySelector(`.notif-item[data-id="${id}"]`);

            if (item) {

                item.classList.remove('non-lue');

                item.querySelector('.dot-non-lue')?.remove();
            }

            const current = parseInt(badge.textContent) || 0;

            mettreAJourBadge(Math.max(0, current - 1));
        }
    };

    /* Badge */
    function mettreAJourBadge(count) {

        badge.textContent = count > 99 ? '99+' : count;

        if (header) {
            header.textContent = count;
        }

        if (count > 0) {

            badge.classList.remove('d-none');

            btn.querySelector('.bi-bell')
                ?.classList.remove('text-muted');

        } else {

            badge.classList.add('d-none');

            btn.querySelector('.bi-bell')
                ?.classList.add('text-muted');
        }
    }

    /* Auto refresh every 5 min */
    setInterval(async function () {

        try {

            const res = await fetch('{{ route('notifications.compteur') }}', {

                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
            });

            const data = await res.json();

            mettreAJourBadge(data.count);

            loaded = false;

        } catch (e) {}

    }, 5 * 60 * 1000);

    function htmlEscape(str) {

        return str
            ? String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
            : '';
    }

})();
</script>

@endpush
@endonce