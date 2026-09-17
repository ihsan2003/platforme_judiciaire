document.addEventListener('DOMContentLoaded', function () {

    const dossierTribunalSelect =
        document.getElementById('id_dossier_tribunal');

    const jugeSelectEl =
        document.getElementById('id_juge');

    const hint =
        document.getElementById('juge_hint');

    const aucun =
        document.getElementById('juge_aucun');

    if (!dossierTribunalSelect || !jugeSelectEl) {
        return;
    }

    // Garder l'id du juge déjà enregistré avant que TomSelect
    // ne remplace le <select> par ses propres éléments.
    const initialPreselectedJugeId =
        jugeSelectEl.dataset.selectedId || null;

    // ── تهيئة حقل البحث (TomSelect) للقاضي ──
    const jugeSelect = new TomSelect(jugeSelectEl, {
        create: function (input) {
            const createUrl = jugeSelectEl.dataset.createUrl;
            if (createUrl) {
                window.location.href = createUrl + '?nom=' + encodeURIComponent(input);
            }
            return false;
        },
        sortField: { field: 'text', direction: 'asc' },
        placeholder: '— اختر المحكمة أولاً —',
        render: {
            no_results: function (data, escape) {
                return '<div class="no-results">لا توجد نتائج</div>';
            },
            option_create: function (data, escape) {
                return '<div class="create">➕ إضافة "' + escape(data.input) + '"</div>';
            }
        }
    });
    jugeSelect.disable();

    async function chargerJuges(tribunalId, preselectedJugeId = null) {

        jugeSelect.clear(true);
        jugeSelect.clearOptions();

        if (!tribunalId) {

            jugeSelect.control_input.placeholder = '— اختر المحكمة أولاً —';
            jugeSelect.disable();

            hint?.classList.add('d-none');
            aucun?.classList.add('d-none');

            return;
        }

        jugeSelect.control_input.placeholder = '— جار التحميل… —';
        jugeSelect.disable();

        try {

            const response =
                await fetch(`/api/tribunaux/${tribunalId}/juges`);

            if (!response.ok) {
                throw new Error('Erreur lors du chargement des juges');
            }

            const juges = await response.json();

            if (juges.length === 0) {

                jugeSelect.control_input.placeholder = '— لا يوجد قضاة —';
                aucun?.classList.remove('d-none');
                hint?.classList.add('d-none');

            } else {

                juges.forEach(function (juge) {
                    jugeSelect.addOption({
                        value: juge.id,
                        text: (juge.grade ? juge.grade + ' ' : '') + juge.nom_complet
                    });
                });

                jugeSelect.control_input.placeholder = '— اختر قاضيًا —';
                jugeSelect.refreshOptions(false);
                jugeSelect.enable();

                // Garder le juge déjà sélectionné
                if (
                    preselectedJugeId &&
                    juges.some(j => String(j.id) === String(preselectedJugeId))
                ) {
                    jugeSelect.setValue(preselectedJugeId, true);
                }

                hint?.classList.remove('d-none');
                aucun?.classList.add('d-none');
            }

        } catch (error) {

            console.error(error);

            jugeSelect.control_input.placeholder = '— خطأ في التحميل —';
            jugeSelect.disable();
        }
    }

    // Changement du dossier / tribunal
    dossierTribunalSelect.addEventListener(
        'change',
        async function () {

            const tribunalId =
                this.options[this.selectedIndex]
                    ?.dataset
                    ?.tribunalId;

            // Lorsqu'on change de tribunal,
            // on ne conserve pas l'ancien juge
            await chargerJuges(tribunalId);
        }
    );

    // Chargement initial de la page
    // avec le juge actuellement enregistré
    const tribunalId =
        dossierTribunalSelect
            .options[dossierTribunalSelect.selectedIndex]
            ?.dataset
            ?.tribunalId;

    if (tribunalId) {
        chargerJuges(
            tribunalId,
            initialPreselectedJugeId
        );
    }
});