let chartInstance  = null;
let selectedCoin   = null;
let selectedRange  = '24h';
let activeRequest  = null;
const historyCache = new Map();

function updateRangeButtons() {
    document.querySelectorAll('[data-chart-range]').forEach((button) => {
        button.disabled = !selectedCoin?.id;
        button.setAttribute('aria-pressed', String(button.dataset.chartRange === selectedRange));
    });
}

function showStatus(message) {
    const status = document.getElementById('chart-status');
    const canvas = document.getElementById('myChart');

    if (!status || !canvas) return;

    status.textContent = message;
    status.hidden = !message;
    canvas.hidden = Boolean(message);
}

async function loadChart() {
    const container = document.getElementById('price-chart');
    const canvas = document.getElementById('myChart');

    if (!container || !canvas || !selectedCoin) return;

    activeRequest?.abort();

    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }

    document.getElementById('chart-title').textContent = `${selectedCoin.name} (${selectedCoin.symbol}) price`;
    updateRangeButtons();

    if (!selectedCoin.id) {
        showStatus('Price history is unavailable for this holding.');
        return;
    }

    showStatus('Loading price history...');
    
    // Abort any active request before starting a new one
    const request = new AbortController();
    
    activeRequest = request;
    const cacheKey = `${selectedCoin.id}:${selectedRange}`;

    try {
        let cached = historyCache.get(cacheKey);
        if (!cached || Date.now() - cached.time > 300000) {
            const url = new URL(container.dataset.url, window.location.href);
            url.searchParams.set('id', selectedCoin.id);
            url.searchParams.set('range', selectedRange);
            const response = await fetch(url, { signal: request.signal, headers: { Accept: 'application/json' } });
            const result = await response.json();
            if (!response.ok) throw new Error(result.error || 'Unable to load price history.');
            cached = { points: result.points, time: Date.now() };
            historyCache.set(cacheKey, cached);
        }

        if (request.signal.aborted) return;

        if (!cached.points.length) {
            showStatus('No price history is available for this range.');
            return;
        }

        if (typeof Chart === 'undefined') throw new Error('The chart library could not be loaded.');

        const showTime = selectedRange === '24h' || selectedRange === '1w';
        const labels = cached.points.map((point) => new Date(point.timestamp).toLocaleString(undefined, showTime
            ? { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' }
            : { year: 'numeric', month: 'short', day: 'numeric' }));
        showStatus('');
        chartInstance = new Chart(canvas, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: `${selectedCoin.symbol} / USD`,
                    data: cached.points.map((point) => point.price),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    tension: 0.2,
                    pointRadius: 0,
                    pointHitRadius: 10,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                scales: { x: { ticks: { maxTicksLimit: 7 } }, y: { ticks: { callback: (value) => `$${Number(value).toLocaleString()}` } } },
            },
        });
    } catch (error) {
        if (error.name !== 'AbortError' && !request.signal.aborted) {
            showStatus(error.message || 'Unable to load price history.');
        }
    }
}

document.addEventListener('holding:selected', (event) => {
    selectedCoin = event.detail;
    selectedRange = '24h';
    loadChart();
});

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-chart-range]');
    if (!button || !selectedCoin?.id) return;
    selectedRange = button.dataset.chartRange;
    loadChart();
});

// Cleanup chart and abort active request before caching the page
document.addEventListener('turbo:before-cache', () => {
    activeRequest?.abort();
    chartInstance?.destroy();
    chartInstance = null;
    selectedCoin = null;
});

// Initialize chart on page load if a holding is already selected
document.addEventListener('turbo:load', () => {
    const row = document.querySelector('#holdings-table [data-crypto-select][aria-pressed="true"]')?.closest('[data-crypto-row]');
    if (!row) return;
    selectedCoin = { id: row.dataset.coinId, name: row.dataset.coinName, symbol: row.dataset.coinSymbol };
    loadChart();
});