
(function () {

    const routes = window.pageData?.notifRoutes;
    const btn       = document.getElementById('notifBtn');
    const list      = document.getElementById('notif-list');
    const badge     = document.getElementById('notif-badge');
    const header    = document.getElementById('notif-count-header');
    const loading   = document.getElementById('notif-loading');
    const btnTout   = document.getElementById('btn-tout-lire');

    if (!routes || !btn) return;

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

            const res = await fetch(routes.toutLire, {

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

            const res = await fetch(routes.dropdown, {

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

            const res = await fetch(routes.compteur, {

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