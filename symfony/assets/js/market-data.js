document.addEventListener('click', (event) => {
    const row = event.target.closest('#holdings-table [data-crypto-row]');
    if (!row) return;

    const selected = row.dataset.cryptoRow;
    document.getElementById('market-data-empty').hidden = true;
    document.querySelectorAll('[data-market-panel]').forEach((panel) => {
        panel.hidden = panel.dataset.marketPanel !== selected;
    });
    document.querySelectorAll('[data-crypto-select]').forEach((button) => {
        button.setAttribute('aria-pressed', String(button.dataset.cryptoSelect === selected));
    });
    document.dispatchEvent(new CustomEvent('holding:selected', {
        detail: { id: row.dataset.coinId, name: row.dataset.coinName, symbol: row.dataset.coinSymbol },
    }));
});