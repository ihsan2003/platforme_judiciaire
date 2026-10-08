document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('id_type_affaire');
    const anneeInput = document.getElementById('annee_mahakim');
    const codeInput = document.getElementById('code_mahakim');
    const ordreInput = document.getElementById('ordre_mahakim');
    const preview = document.getElementById('preview_mahakim');
    const hiddenInput = document.getElementById('numero_dossier_tribunal');

    if (!typeSelect || !anneeInput || !codeInput || !ordreInput || !preview || !hiddenInput) {
        return;
    }

    function updatePreview() {
        const annee = anneeInput.value || '—';
        const code = codeInput.value || '—';
        const ordre = ordreInput.value || '—';

        const final = `${annee} / ${code} / ${ordre}`;
        preview.innerText = final;
        hiddenInput.value = final;
    }

    // Remplissage automatique du code désactivé : le code (رمز الفئة) est saisi à la main.
    // typeSelect.addEventListener('change', function () {
    //     const selectedOption = this.options[this.selectedIndex];
    //     codeInput.value = selectedOption.getAttribute('data-code') || '';
    //     updatePreview();
    // });

    [anneeInput, codeInput, ordreInput].forEach(el => {
        el.addEventListener('input', updatePreview);
    });

    // Initialiser l'aperçu (retour de validation / page edit)
    if (codeInput.value || ordreInput.value) {
        updatePreview();
    }
});
