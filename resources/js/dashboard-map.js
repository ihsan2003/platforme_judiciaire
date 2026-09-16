(function () {
    'use strict';

    const mapData = window.dashboardMapData;
    if (!mapData || typeof d3 === 'undefined') return;

    // ── Configuration ─────────────────────────────────────────────────────────
    const API_URL   = mapData.apiUrl;
    const GEOJSON   = "/geojson/regions.json";
    const GEO_KEY   = "name";           // propriété dans le GeoJSON
    const DB_KEY    = "nom_region";     // clé dans la réponse API
    const TOTAL_KEY = "total_dossiers"; // clé du count
    const TRIB_KEY  = "total_tribunaux";

    // ── Fonction de normalisation des noms de régions ──────────────────────────
    function normalizeRegionName(name) {
        if (!name) return '';
        return name
            .trim()
            .replace(/\s+/g, ' ')           // Normaliser les espaces
            .replace(/–|—|−/g, '-')          // Normaliser les tirets
            .toLowerCase()
            .replace(/[\u064B-\u0652]/g, ''); // Supprimer les diacritiques arabes
    }

    // ── Éléments DOM ──────────────────────────────────────────────────────────
    const wrapper  = document.getElementById('morocco-map-wrapper');
    const svgEl    = document.getElementById('morocco-map');
    const tooltip  = document.getElementById('map-tooltip');
    const loader   = document.getElementById('map-loader');
    const legendEl = document.getElementById('map-legend');
    const tbody    = document.getElementById('map-table-body');

    // ── Chargement parallèle ──────────────────────────────────────────────────
    Promise.all([
        fetch(API_URL, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()),
        d3.json(GEOJSON)
    ])
    .then(([apiData, geoData]) => {
        loader.classList.add('d-none');
        loader.style.display = 'none';

        // ── Créer une Map normalisée pour la recherche ────────────────────────
        const counts = new Map();

        apiData.forEach(d => {
            const originalName = d[DB_KEY]?.trim();
            const normalizedName = normalizeRegionName(originalName);
            counts.set(normalizedName, d);
        });

        console.log('✓ Données API reçues:', apiData.length, 'régions');
        console.log('✓ Noms API normalisés:', Array.from(counts.keys()));

        const max = d3.max(apiData, d => +d[TOTAL_KEY]) || 1;

        // Palette de couleur — dégradé doré → bleu-nuit, cohérent avec le thème
        // du tableau de bord (--accent: #c8a84b, --primary: #1a3a5c).
        const colorScale = d3.scaleSequential()
            .domain([0, max])
            .interpolator(d3.interpolateRgb('#e9c46a', '#1a3a5c'));

        const noDataColor = '#e2e8f0';

        // ── Projection ───────────────────────────────────────────────────────
        const W = wrapper.clientWidth;
        const H = wrapper.clientHeight;

        const projection = d3.geoMercator()
            .fitSize([W - 20, H - 20], geoData);

        const pathGen = d3.geoPath().projection(projection);

        const svg = d3.select(svgEl)
            .attr('viewBox', `0 0 ${W} ${H}`)
            .attr('preserveAspectRatio', 'xMidYMid meet');

        // ── Dessin des régions ────────────────────────────────────────────────
        svg.selectAll('path')
            .data(geoData.features)
            .join('path')
            .attr('d', pathGen)
            .attr('fill', d => {
                const geoName = d.properties[GEO_KEY]?.trim();
                const normalizedGeoName = normalizeRegionName(geoName);
                const row = counts.get(normalizedGeoName);

                if (!row) {
                    console.warn('⚠ Pas de match pour:', geoName, '(normalisé:', normalizedGeoName + ')');
                }

                return row ? colorScale(+row[TOTAL_KEY]) : noDataColor;
            })
            .attr('stroke', '#fff')
            .attr('stroke-width', 1.2)
            .style('cursor', 'pointer')
            .style('transition', 'opacity .15s')
            .on('mousemove', function (event, d) {
                const geoName = d.properties[GEO_KEY]?.trim() ?? '—';
                const normalizedGeoName = normalizeRegionName(geoName);
                const row = counts.get(normalizedGeoName);
                const tot  = row ? Number(row[TOTAL_KEY]).toLocaleString('ar-MA') : '٠';
                const trib = row ? Number(row[TRIB_KEY]).toLocaleString('ar-MA') : '٠';

                d3.select(this)
                    .attr('stroke', '#1a3a5c')
                    .attr('stroke-width', 2.5)
                    .style('opacity', '.85');

                const rect = wrapper.getBoundingClientRect();
                let tx = event.clientX - rect.left + 14;
                let ty = event.clientY - rect.top  - 50;

                if (tx + 200 > W) tx = event.clientX - rect.left - 214;

                tooltip.style.display = 'block';
                tooltip.style.left    = tx + 'px';
                tooltip.style.top     = ty + 'px';
                tooltip.innerHTML = `
                    <div style="font-weight:700;font-size:14px;margin-bottom:6px;
                                border-bottom:1px solid rgba(255,255,255,.25);
                                padding-bottom:6px;">${geoName}</div>
                    <div style="display:flex;justify-content:space-between;gap:16px;">
                        <span>عدد الملفات</span>
                        <span style="color:#e9c46a;font-weight:700;">${tot}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;gap:16px;margin-top:4px;">
                        <span>المحاكم</span>
                        <span style="color:#6ee7b7;font-weight:700;">${trib}</span>
                    </div>`;
            })
            .on('mouseleave', function () {
                d3.select(this)
                    .attr('stroke', '#fff')
                    .attr('stroke-width', 1.2)
                    .style('opacity', '1');
                tooltip.style.display = 'none';
            });

        // ── Légende ───────────────────────────────────────────────────────────
        const steps = [0, 0.25, 0.5, 0.75, 1];
        legendEl.innerHTML =
            '<span class="me-1">أقل</span>' +
            steps.map(t => {
                const val = Math.round(t * max);
                return `<span title="${val}"
                              style="display:inline-block;width:22px;height:14px;
                                     border-radius:3px;background:${colorScale(t * max)};
                                     border:1px solid #cbd5e1;"></span>`;
            }).join('') +
            '<span class="ms-1">أكثر</span>';

        // ── Tableau récapitulatif ─────────────────────────────────────────────
        const totalGlobal = apiData.reduce((s, d) => s + (+d[TOTAL_KEY] || 0), 0);
        const sorted = [...apiData].sort((a, b) => b[TOTAL_KEY] - a[TOTAL_KEY]);

        tbody.innerHTML = sorted.map((row) => {
            const pct = totalGlobal > 0
                ? ((+row[TOTAL_KEY] / totalGlobal) * 100).toFixed(1)
                : 0;
            const barColor = colorScale(+row[TOTAL_KEY]);
            return `
                <tr>
                    <td>
                        <span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:${barColor};margin-inline-end:8px;"></span>
                        <span style="font-weight:600;color:#1a3a5c">${row[DB_KEY] ?? '—'}</span>
                    </td>
                    <td class="text-center fw-bold">${Number(row[TOTAL_KEY]).toLocaleString('ar-MA')}</td>
                    <td class="text-center text-muted">${Number(row[TRIB_KEY]).toLocaleString('ar-MA')}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="pct-bar flex-grow-1" style="margin-top:0">
                                <div class="pct-fill" style="width:${pct}%;background:${barColor};"></div>
                            </div>
                            <small class="text-muted" style="min-width:38px;">${pct}%</small>
                        </div>
                    </td>
                </tr>`;
        }).join('');

        console.log('✓ Carte rendue avec succès!');
    })
    .catch(err => {
        console.error('✗ Erreur carte :', err);
        loader.innerHTML = `
            <div class="text-center text-danger p-4">
                <i class="bi bi-exclamation-triangle fs-3"></i>
                <p class="mt-2 mb-0">تعذر تحميل الخريطة</p>
                <small class="text-muted d-block mt-2">${err.message}</small>
            </div>`;
    });
})();