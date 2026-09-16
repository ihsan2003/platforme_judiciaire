document.addEventListener('DOMContentLoaded', function () {

    const dossierTribunalSelect =
        document.getElementById('id_dossier_tribunal');

    const jugeSelect =
        document.getElementById('id_juge');

    const hint =
        document.getElementById('juge_hint');

    const aucun =
        document.getElementById('juge_aucun');

    if (!dossierTribunalSelect || !jugeSelect) {
        return;
    }

    async function chargerJuges(tribunalId, preselectedJugeId = null) {

        if (!tribunalId) {

            jugeSelect.innerHTML =
                '<option value="">— اختر المحكمة أولاً —</option>';

            hint?.classList.add('d-none');
            aucun?.classList.add('d-none');

            return;
        }

        jugeSelect.innerHTML =
            '<option value="">— جار التحميل… —</option>';

        jugeSelect.disabled = true;

        try {

            const response =
                await fetch(`/api/tribunaux/${tribunalId}/juges`);

            if (!response.ok) {
                throw new Error('Erreur lors du chargement des juges');
            }

            const juges = await response.json();

            jugeSelect.innerHTML =
                '<option value="">— اختر قاضيًا —</option>';

            if (juges.length === 0) {

                aucun?.classList.remove('d-none');
                hint?.classList.add('d-none');

            } else {

                juges.forEach(function (juge) {

                    const option =
                        document.createElement('option');

                    option.value = juge.id;

                    option.textContent =
                        (juge.grade ? juge.grade + ' ' : '') +
                        juge.nom_complet;

                    // Garder le juge déjà sélectionné
                    if (
                        preselectedJugeId &&
                        String(juge.id) === String(preselectedJugeId)
                    ) {
                        option.selected = true;
                    }

                    jugeSelect.appendChild(option);
                });

                hint?.classList.remove('d-none');
                aucun?.classList.add('d-none');
            }

            jugeSelect.disabled = false;

        } catch (error) {

            console.error(error);

            jugeSelect.innerHTML =
                '<option value="">— خطأ في التحميل —</option>';

            jugeSelect.disabled = false;
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
    const preselectedJugeId =
        jugeSelect.dataset.selectedId || null;

    const tribunalId =
        dossierTribunalSelect
            .options[dossierTribunalSelect.selectedIndex]
            ?.dataset
            ?.tribunalId;

    if (tribunalId) {
        chargerJuges(
            tribunalId,
            preselectedJugeId
        );
    }
});