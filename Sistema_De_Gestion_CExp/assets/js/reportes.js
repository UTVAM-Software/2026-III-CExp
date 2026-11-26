document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('graficaTendencia');
    if (!canvas || typeof Chart === 'undefined') return;

    const leer = (attr) => {
        try {
            return JSON.parse(canvas.dataset[attr] || '[]');
        } catch { return []; }
    };

    const crearSerie = (label, data, backgroundColor) => ({
        label,
        data,
        backgroundColor,
        borderRadius: 6,
        borderSkipped: false,
        maxBarThickness: 28
    });

    new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels: leer('etiquetas'), //<- pinta debajo de las barras los ultimos 6 meses 
            datasets: [
                crearSerie('Consultas Realizadas', leer('consultas'), '#4e73df'),
                crearSerie('Citas Agendadas', leer('citas'), '#a6c1ee')
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false, backgroundColor: '#1e293b', padding: 10, cornerRadius: 8 }
            },
            scales: {
                x: { grid: { display: false }, border: { display: false }, ticks: { color: '#6c757d', font: { size: 12, weight: '500' } } },
                y: { beginAtZero: true, border: { display: false }, ticks: { precision: 0, color: '#6c757d', font: { size: 11 } }, grid: { color: '#f1f4f9' } }
            }
        }
    });
});
