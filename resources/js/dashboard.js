(function () {
    const data = window.dashboardChartData;
    if (!data || typeof Chart === 'undefined') return;

    const BLUE  = '#378ADD';
    const GREEN = '#639922';
    const AMBER = '#BA7517';
    const GRAY  = '#888780';
    const RED   = '#E24B4A';

    const { evoLabels, evoVals, affLabels, affVals, finLabels, finVals,
            dossActifs, dossJuges, dossTotal, pourVal, contreVal,
            avocatPresentVal, avocatAbsentVal,
            reclamEnCoursVal, reclamTraiteesVal, reclamClotureesVal,
            reclamTypeLabels, reclamTypeVals } = data;

    const dossHifd = Math.max(0, dossTotal - dossActifs - dossJuges);

    const defaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
    };

    /* Configuration globale Chart.js pour RTL */
    Chart.defaults.rtl = true;

    /* Évolution mensuelle dossiers */
    new Chart(document.getElementById('chartEvo'), {
        type: 'line',
        data: {
            labels: evoLabels,
            datasets: [{
                data: evoVals,
                borderColor: BLUE,
                backgroundColor: 'rgba(55,138,221,0.08)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: BLUE,
                fill: true,
                tension: .35,
            }]
        },
        options: {
            ...defaults,
            scales: {
                x: { ticks: { font: { size: 10 }, maxRotation: 30 }, grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.04)' } }
            }
        }
    });

    /* Dossiers par type d'affaire */
    new Chart(document.getElementById('chartAffaires'), {
        type: 'bar',
        data: {
            labels: affLabels,
            datasets: [{
                data: affVals,
                backgroundColor: BLUE,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            ...defaults,
            scales: {
                x: { ticks: { font: { size: 10 } }, grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.04)' } }
            }
        }
    });

    /* Donut statuts */
    new Chart(document.getElementById('chartStatut'), {
        type: 'doughnut',
        data: {
            labels: ['نشطة', 'محكومة', 'محفوظة'],
            datasets: [{
                data: [dossActifs, dossJuges, dossHifd],
                backgroundColor: [BLUE, GREEN, GRAY],
                borderWidth: 0,
                hoverOffset: 5,
            }]
        },
        options: {
            cutout: '72%',
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
        }
    });

    /* Donut pour/contre */
    new Chart(document.getElementById('chartPourContre'), {
        type: 'doughnut',
        data: {
            labels: ['لصالح المؤسسة (مع)', 'ضد المؤسسة'],
            datasets: [{
                data: [pourVal, contreVal],
                backgroundColor: [GREEN, RED],
                borderWidth: 0,
                hoverOffset: 5,
            }]
        },
        options: {
            cutout: '72%',
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
        }
    });

    /* Donut présence de l'avocat dans les audiences */
    new Chart(document.getElementById('chartAvocatPresence'), {
        type: 'doughnut',
        data: {
            labels: ['حاضر', 'غائب'],
            datasets: [{
                data: [avocatPresentVal, avocatAbsentVal],
                backgroundColor: [GREEN, RED],
                borderWidth: 0,
                hoverOffset: 5,
            }]
        },
        options: {
            cutout: '72%',
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
        }
    });

    /* Donut réclamations par statut */
    new Chart(document.getElementById('chartReclamationsStatut'), {
        type: 'doughnut',
        data: {
            labels: ['قيد المعالجة', 'تمت المعالجة', 'مغلقة'],
            datasets: [{
                data: [reclamEnCoursVal, reclamTraiteesVal, reclamClotureesVal],
                backgroundColor: [AMBER, GREEN, GRAY],
                borderWidth: 0,
                hoverOffset: 5,
            }]
        },
        options: {
            cutout: '72%',
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
        }
    });

    /* Réclamations par type */
    new Chart(document.getElementById('chartReclamationsType'), {
        type: 'bar',
        data: {
            labels: reclamTypeLabels,
            datasets: [{
                data: reclamTypeVals,
                backgroundColor: BLUE,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            ...defaults,
            indexAxis: 'y',
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.04)' } },
                y: { ticks: { font: { size: 10 } }, grid: { display: false } }
            }
        }
    });

    /* Évolution financière mensuelle */
    new Chart(document.getElementById('chartFinancesMensuel'), {
        type: 'line',
        data: {
            labels: finLabels,
            datasets: [{
                data: finVals,
                borderColor: GREEN,
                backgroundColor: 'rgba(99,153,34,0.08)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: GREEN,
                fill: true,
                tension: .35,
            }]
        },
        options: {
            ...defaults,
            scales: {
                x: { ticks: { font: { size: 10 }, maxRotation: 30 }, grid: { display: false } },
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { size: 10 },
                        callback: v => v >= 1000000 ? (v / 1000000).toFixed(1) + 'M' : (v >= 1000 ? (v / 1000).toFixed(0) + 'K' : v)
                    },
                    grid: { color: 'rgba(0,0,0,0.04)' }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: c => ` ${Number(c.raw).toLocaleString('fr-MA')} درهم` } }
            }
        }
    });
})();