document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('pelanggaranChart')?.getContext('2d');
    if(ctx && window.pelanggaranLabels) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: window.pelanggaranLabels,
                datasets: [{ label: 'Jumlah Pelanggaran', data: window.pelanggaranData, backgroundColor: 'rgba(54, 162, 235, 0.5)' }]
            }
        });
    }
});