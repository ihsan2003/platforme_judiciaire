document.addEventListener("DOMContentLoaded", function () {
    // 1. Initialisation du premier Select (Nouveau)
    const selectNouveau = new TomSelect("#field_avocat_nouveau_select", {
        create: function (input) {
            window.location.href = window.pageData.avocatsCreateUrl + "?nom=" + encodeURIComponent(input);
            return false;
        },
        sortField: { field: "text", direction: "asc" },
        placeholder: "— بدون محامي —",
        render: {
            no_results: function (data, escape) {
                return `<div class="no-results">لا توجد نتائج</div>`;
            },

            option_create: function (data, escape) {
                return `<div class="create">➕ إضافة "${escape(data.input)}"</div>`;
            }
        }
    });

    // 2. Initialisation du deuxième Select (Modification)
    const selectModif = new TomSelect("#field_avocat_modif_select", {
        create: function (input) {
            window.location.href = window.pageData.avocatsCreateUrl + "?nom=" + encodeURIComponent(input);
            return false;
        },
        sortField: { field: "text", direction: "asc" },
        placeholder: "— بدون محامي —",
        render: {
            no_results: function (data, escape) {
                return `<div class="no-results">لا توجد نتائج</div>`;
            },

            option_create: function (data, escape) {
                return `<div class="create">➕ إضافة "${escape(data.input)}"</div>`;
            }
        }
    });

    // Exemple si vous gérez le clic sur #btnModifierAvocat :
    document.getElementById('btnModifierAvocat').addEventListener('click', function () {
        // Votre code existant pour afficher le bloc...

        // Activer Tom Select proprement :
        selectModif.enable();
    });
});

/* ── Réactiver l'onglet depuis l'URL (fragment) ─── */
(function () {
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector(`[data-bs-target="${hash}"]`);
        if (tab) new bootstrap.Tab(tab).show();
    }
})();

/* ── Recherche AJAX parties (البحث عن الأطراف) ───────────────────── */
(function () {
    const input = document.getElementById('recherchePartie');
    const dropdown = document.getElementById('resultatRecherche');
    const bandeauOK = document.getElementById('partieSelectionnee');
    const nomOK = document.getElementById('partieSelectionneeNom');
    const btnDesel = document.getElementById('btnDeselectionner');
    const btnNouvelle = document.getElementById('btnNouvellePartie');
    const btnModifier = document.getElementById('btnModifierAvocat');

    const blocExistant = document.getElementById('bloc_avocat_existant');
    const blocNouveau = document.getElementById('bloc_avocat_nouveau');
    const blocModif = document.getElementById('bloc_avocat_modif');
    const avocatDisplay = document.getElementById('field_avocat_display');
    const avocatModif = document.getElementById('field_avocat_modif_select');
    const avocatNvx = document.getElementById('field_avocat_nouveau_select');

    const F = {
        id: document.getElementById('hidden_partie_id'),
        identifiant: document.getElementById('field_identifiant'),
        nom: document.getElementById('field_nom'),
        type_personne: document.getElementById('field_type_personne'),
        telephone: document.getElementById('field_telephone'),
        email: document.getElementById('field_email'),
        adresse: document.getElementById('field_adresse'),
    };

    let timer = null;

    function lockFields(lock) {
        ['identifiant', 'nom', 'email', 'adresse', 'telephone'].forEach(k => {
            if (!F[k]) return;
            F[k].readOnly = lock;
            F[k].classList.toggle('bg-light', lock);
        });
        if (F.type_personne) { F.type_personne.disabled = lock; F.type_personne.classList.toggle('bg-light', lock); }
    }

    function showAvocatExistant(nom, id) {
        blocExistant?.classList.remove('d-none');
        blocNouveau?.classList.add('d-none');
        if (avocatDisplay) avocatDisplay.value = nom || 'بدون محامي';
        if (avocatNvx) { avocatNvx.disabled = true; avocatNvx.name = ''; }
        if (avocatModif) { avocatModif.disabled = true; avocatModif.name = ''; }
        if (id && avocatModif) Array.from(avocatModif.options).forEach(o => o.selected = (o.value == id));
    }

    function showAvocatNouveau() {
        blocExistant?.classList.add('d-none');
        blocNouveau?.classList.remove('d-none');
        if (avocatNvx) { avocatNvx.disabled = false; avocatNvx.name = 'id_avocat'; }
        if (avocatModif) { avocatModif.disabled = true; avocatModif.name = ''; }
    }

    function selectPartie(p) {
        if (F.id) F.id.value = p.id;
        if (F.identifiant) F.identifiant.value = p.identifiant_unique ?? '';
        if (F.nom) F.nom.value = p.nom_partie ?? '';
        if (F.email) F.email.value = p.email ?? '';
        if (F.telephone) F.telephone.value = p.telephone ?? '';
        if (F.adresse) F.adresse.value = p.adresse ?? '';
        if (F.type_personne) Array.from(F.type_personne.options).forEach(o => o.selected = (o.value === p.type_personne));
        lockFields(true);
        if (nomOK) nomOK.textContent = `${p.nom_partie} (${p.identifiant_unique})`;
        bandeauOK?.classList.remove('d-none');
        closeDropdown();
        if (input) input.value = '';
        showAvocatExistant(p.avocat_nom, p.id_avocat);
    }

    function deselect() {
        if (F.id) F.id.value = '';
        lockFields(false);
        bandeauOK?.classList.add('d-none');
        ['identifiant', 'nom', 'email', 'telephone', 'adresse'].forEach(k => { if (F[k]) F[k].value = ''; });
        if (F.type_personne) { F.type_personne.selectedIndex = 0; F.type_personne.disabled = false; F.type_personne.classList.remove('bg-light'); }
        showAvocatNouveau();
    }

    function closeDropdown() { if (dropdown) { dropdown.style.display = 'none'; dropdown.innerHTML = ''; } }

    function renderResults(parties, query) {
        if (!dropdown) return;
        dropdown.innerHTML = '';
        parties.forEach(p => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'list-group-item list-group-item-action py-2 px-3 text-end'; // text-end pour RTL
            btn.innerHTML = `<div class="fw-semibold small">${p.nom_partie ?? ''}</div>
                <div class="text-muted" style="font-size:.75rem"><span class="font-monospace">${p.identifiant_unique ?? ''}</span>${p.avocat_nom ? ' · ' + p.avocat_nom : ''}</div>`;
            btn.addEventListener('click', () => selectPartie(p));
            dropdown.appendChild(btn);
        });
        const creer = document.createElement('button');
        creer.type = 'button';
        creer.className = 'list-group-item list-group-item-action py-2 px-3 text-primary text-end';
        creer.innerHTML = `<i class="bi bi-plus-circle me-1"></i>إنشاء « ${query} »`;
        creer.addEventListener('click', () => { deselect(); if (F.nom) F.nom.value = query; closeDropdown(); if (input) input.value = ''; });

        if (!parties.length) {
            const info = document.createElement('div');
            info.className = 'list-group-item py-2 px-3 text-muted small text-end';
            info.textContent = 'لم يتم العثور على أي طرف.';
            dropdown.appendChild(info);
        }
        dropdown.appendChild(creer);
        dropdown.style.display = 'block';
    }

    input?.addEventListener('input', () => {
        clearTimeout(timer);
        const q = input.value.trim();
        if (q.length < 2) { closeDropdown(); return; }
        timer = setTimeout(async () => {
            try {
                const res = await fetch(`${window.pageData.dossierPartiesSearchUrl}?q=${encodeURIComponent(q)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error();
                renderResults(await res.json(), q);
            } catch {}
        }, 280);
    });

    document.addEventListener('click', e => {
        if (!input?.contains(e.target) && !dropdown?.contains(e.target)) closeDropdown();
    });

    btnNouvelle?.addEventListener('click', () => { deselect(); closeDropdown(); if (input) input.value = ''; F.identifiant?.focus(); });
    btnDesel?.addEventListener('click', e => { e.preventDefault(); deselect(); input?.focus(); });

    btnModifier?.addEventListener('click', () => {
        blocModif?.classList.toggle('d-none');
        const visible = !blocModif?.classList.contains('d-none');
        if (avocatModif) { avocatModif.disabled = !visible; avocatModif.name = visible ? 'id_avocat' : ''; }
        if (btnModifier) btnModifier.innerHTML = visible ? '<i class="bi bi-x me-1"></i>إلغاء' : '<i class="bi bi-pencil me-1"></i>تعديل';
    });

    document.getElementById('modalAjouterPartie')?.addEventListener('show.bs.modal', () => {
        deselect(); closeDropdown(); if (input) input.value = '';
    });

    showAvocatNouveau();
})();

/* ── Cascade Région > Province > Degré > Tribunal (التسلسل الإداري) ─ */
(function () {
    const selRegion = document.getElementById('modal_region');
    const selProvince = document.getElementById('modal_province');
    const selDegre = document.getElementById('modal_degre');
    const selTribunal = document.getElementById('modal_tribunal');

    function reset(sel, ph) { if (!sel) return; sel.innerHTML = `<option value="">${ph}</option>`; sel.disabled = true; }

    selRegion?.addEventListener('change', async function () {
        reset(selProvince, '— جاري التحميل... —');
        reset(selDegre, '— اختر الإقليم أولاً —');
        reset(selTribunal, '— اختر الدرجة أولاً —');
        if (!this.value) { reset(selProvince, '— اختر الجهة أولاً —'); return; }
        try {
            const data = await (await fetch(`/api/regions/${this.value}/provinces`)).json();
            selProvince.innerHTML = '<option value="">— اختر الإقليم —</option>';
            data.forEach(p => selProvince.innerHTML += `<option value="${p.id}">${p.province}</option>`);
            selProvince.disabled = false;
        } catch { reset(selProvince, '— خطأ —'); }
    });

    selProvince?.addEventListener('change', async function () {
        reset(selDegre, '— جاري التحميل... —');
        reset(selTribunal, '— اختر الدرجة أولاً —');
        if (!this.value) { reset(selDegre, '— اختر الإقليم أولاً —'); return; }
        try {
            const data = await (await fetch(`/api/provinces/${this.value}/degres`)).json();
            selDegre.innerHTML = '<option value="">— اختر درجة التقاضي —</option>';
            data.forEach(d => selDegre.innerHTML += `<option value="${d.id}">${d.degre_juridiction}</option>`);
            selDegre.disabled = false;
        } catch { reset(selDegre, '— خطأ —'); }
    });

    selDegre?.addEventListener('change', async function () {
        reset(selTribunal, '— جاري التحميل... —');
        if (!this.value) { reset(selTribunal, '— اختر درجة التقاضي أولاً —'); return; }
        try {
            const data = await (await fetch(`/api/provinces/${selProvince.value}/degres/${this.value}/tribunaux`)).json();
            selTribunal.innerHTML = '<option value="">— اختر المحكمة —</option>';
            if (!data.length) { selTribunal.innerHTML = '<option value="">— لا توجد محاكم متاحة —</option>'; return; }
            data.forEach(t => selTribunal.innerHTML += `<option value="${t.id}">${t.nom_tribunal}</option>`);
            selTribunal.disabled = false;
        } catch { reset(selTribunal, '— خطأ —'); }
    });

    document.getElementById('modalAjouterTribunal')?.addEventListener('show.bs.modal', () => {
        if (selRegion) selRegion.value = '';
        reset(selProvince, '— اختر الجهة أولاً —');
        reset(selDegre, '— اختر الإقليم أولاً —');
        reset(selTribunal, '— اختر درجة التقاضي أولاً —');
    });
})();