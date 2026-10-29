/**
 * Composant « numéro de dossier » (resources/views/components/numero-dossier.blade.php).
 *
 * - Affiche le groupe de champs du format actif (standard | cassation) et
 *   désactive l'autre, pour que seuls les champs actifs soient soumis.
 * - Met à jour l'aperçu du numéro final.
 * - Si data-driver pointe vers un <select>, le format suit l'option choisie :
 *   data-numero-format de l'option, sinon « cassation » quand son libellé
 *   contient « نقض » (même critère que le serveur).
 */
const FORMATS = ['standard', 'cassation'];

function formatDeLOption(select) {
    const option = select.options[select.selectedIndex];
    if (!option || !option.value) return null;
    if (option.dataset.numeroFormat) return option.dataset.numeroFormat;
    if (option.dataset.ordre === '3' || option.textContent.includes('نقض')) return 'cassation';
    return 'standard';
}

function initWidget(root) {
    const preview = root.querySelector('[data-numero-preview]');
    const groups = Object.fromEntries(
        FORMATS.map(f => [f, root.querySelector(`[data-numero-group="${f}"]`)])
    );
    let format = root.dataset.format || 'standard';

    function majApercu() {
        const champs = groups[format]?.querySelectorAll('input, select') ?? [];
        const valeurs = Array.from(champs, c => c.value.trim());
        preview.textContent = valeurs.some(Boolean)
            ? valeurs.map(v => v || '—').join('/')
            : '—';
    }

    function appliquerFormat(nouveau) {
        if (!groups[nouveau]) return;
        format = nouveau;
        root.dataset.format = nouveau;
        FORMATS.forEach(f => {
            const actif = f === format;
            groups[f].hidden = !actif;
            groups[f].querySelectorAll('input, select').forEach(c => { c.disabled = !actif; });
            root.querySelector(`[data-numero-exemple="${f}"]`)?.toggleAttribute('hidden', !actif);
        });
        majApercu();
    }

    root.addEventListener('input', majApercu);
    root.addEventListener('change', majApercu);

    const driver = root.dataset.driver ? document.querySelector(root.dataset.driver) : null;
    if (driver) {
        driver.addEventListener('change', () => {
            const f = formatDeLOption(driver);
            if (f) appliquerFormat(f);
        });
    }

    appliquerFormat(format);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-numero-widget]').forEach(initWidget);
});
