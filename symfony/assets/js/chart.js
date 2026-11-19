let chartInstance = null;

function initChart() {
    const canvas = document.getElementById('myChart');
    
    if (!canvas || !window.chartData) {
        return;
    }

    if (window.chartData.labels.length === 0 || window.chartData.values.length === 0) {
        console.warn('Chart: Empty data');
        return;
    }
    
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js not loaded yet, retrying...');
        setTimeout(initChart, 100);
        return;
    }

    const ctx = canvas.getContext('2d');
    
    // Destroy existing chart
    if (chartInstance) {
        chartInstance.destroy();
    }
    
    try {
        chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: window.chartData.labels,
                datasets: [{
                    label: 'Portfolio Value',
                    data: window.chartData.values,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                    }
                }
            }
        });
    } catch (err) {
        console.error('Error initializing chart:', err);
    }
}

window.initChart = initChart;

// Initialize on page load
setTimeout(() => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChart);
    } else {
        initChart();
    }
}, 100);
