document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('accuratePanelOverlay');
    const wrapper = document.getElementById('accuratePanelWrapper');

    if (!overlay || !wrapper) return;

    function closeAllPanels() {
        wrapper.querySelectorAll('.accurate-panel.show').forEach(p => p.classList.remove('show'));
        overlay.classList.remove('show');
        document.querySelectorAll('.rail-item.active').forEach(r => r.classList.remove('active'));
    }

    function openPanel(panelId, railEl) {
        closeAllPanels();
        const panel = document.getElementById(panelId);
        if (!panel) return;

        panel.classList.add('show');
        overlay.classList.add('show');
        if (railEl) railEl.classList.add('active');
    }

    // klik rail item -> buka panel level 1
    document.querySelectorAll('.rail-item[data-target]').forEach(rail => {
        rail.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const alreadyOpen = document.getElementById(targetId)?.classList.contains('show');
            if (alreadyOpen) {
                closeAllPanels();
            } else {
                openPanel(targetId, this);
            }
        });
    });

    // klik kartu yang punya anak -> buka panel level berikutnya (di sebelah kanan)
    document.querySelectorAll('.acc-card-parent[data-target]').forEach(card => {
        card.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const panel = document.getElementById(targetId);
            if (!panel) return;

            // tutup panel dengan level >= level panel ini dulu, biar gak numpuk aneh
            const level = parseInt(panel.getAttribute('data-level'), 10);
            wrapper.querySelectorAll('.accurate-panel.show').forEach(p => {
                if (parseInt(p.getAttribute('data-level'), 10) >= level) {
                    p.classList.remove('show');
                }
            });

            panel.classList.add('show');
            overlay.classList.add('show');
        });
    });

    // tombol close di header tiap panel
    document.querySelectorAll('[data-close-panel]').forEach(btn => {
        btn.addEventListener('click', closeAllPanels);
    });

    // ---------- SEARCH / FILTER KARTU MENU DI TIAP PANEL ----------
    document.querySelectorAll('.accurate-panel').forEach(panel => {
        const searchBox = panel.querySelector('.accurate-panel-search');
        if (!searchBox) return; // panel dengan <=6 kartu gak dikasih search box

        const input = searchBox.querySelector('.acc-search-input');
        const clearBtn = searchBox.querySelector('.acc-search-clear');
        const grid = panel.querySelector('.accurate-panel-grid');
        const cards = grid.querySelectorAll('.acc-card');
        const emptyState = grid.querySelector('.acc-search-empty');

        function filterCards() {
            const query = input.value.trim().toLowerCase();
            searchBox.classList.toggle('has-value', query.length > 0);

            let visibleCount = 0;
            cards.forEach(card => {
                const text = card.getAttribute('data-search') || '';
                const match = text.includes(query);
                card.classList.toggle('acc-hidden', !match);
                if (match) visibleCount++;
            });

            if (emptyState) {
                emptyState.style.display = (visibleCount === 0 && query.length > 0) ? 'block' : 'none';
            }
        }

        input.addEventListener('input', filterCards);

        clearBtn.addEventListener('click', function () {
            input.value = '';
            filterCards();
            input.focus();
        });

        // reset pencarian tiap kali panel dibuka lagi
        const observer = new MutationObserver(() => {
            if (!panel.classList.contains('show')) {
                input.value = '';
                filterCards();
            }
        });
        observer.observe(panel, { attributes: true, attributeFilter: ['class'] });
    });

    // klik overlay (area gelap) -> tutup semua panel
    overlay.addEventListener('click', closeAllPanels);

    // ESC untuk nutup
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAllPanels();
    });
});