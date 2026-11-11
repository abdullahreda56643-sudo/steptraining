(() => {
    const app = document.getElementById('vocab-app');
    if (!app) return;

    const cards = Array.from(app.querySelectorAll('[data-vocab-card]'));
    const search = document.querySelector('[data-vocab-search]');
    const filters = Array.from(document.querySelectorAll('[data-vocab-filter]'));
    const emptyState = document.querySelector('[data-vocab-empty]');
    const visibleCount = document.querySelector('[data-vocab-visible-count]');
    let activeFilter = 'all';

    const updateSummary = (previousStatus, nextStatus) => {
        if (previousStatus && previousStatus !== nextStatus) {
            [previousStatus, nextStatus].forEach((status, index) => {
                const target = document.querySelector(`[data-vocab-count="${status}"]`);
                if (!target) return;
                const current = Number(target.textContent) || 0;
                target.textContent = String(Math.max(0, current + (index === 0 ? -1 : 1)));
            });
        }

        const counts = { new: 0, review: 0, known: 0 };
        cards.forEach((card) => {
            const status = card.dataset.status || 'new';
            if (Object.prototype.hasOwnProperty.call(counts, status)) counts[status] += 1;
        });
        Object.entries(counts).forEach(([status, count]) => {
            const filterCount = document.querySelector(`[data-vocab-filter="${status}"] span`);
            if (filterCount) filterCount.textContent = String(count);
        });
        const allCount = document.querySelector('[data-vocab-filter="all"] span');
        if (allCount) allCount.textContent = String(cards.length);
    };

    const applyFilters = () => {
        const term = (search?.value || '').trim().toLocaleLowerCase();
        let visible = 0;
        cards.forEach((card) => {
            const matchesStatus = activeFilter === 'all' || card.dataset.status === activeFilter;
            const matchesSearch = !term || (card.dataset.search || '').toLocaleLowerCase().includes(term);
            const isVisible = matchesStatus && matchesSearch;
            card.hidden = !isVisible;
            if (isVisible) visible += 1;
        });
        if (visibleCount) visibleCount.textContent = String(visible);
        if (emptyState) emptyState.hidden = visible !== 0;
    };

    filters.forEach((button) => {
        button.addEventListener('click', () => {
            activeFilter = button.dataset.vocabFilter || 'all';
            filters.forEach((filter) => {
                filter.setAttribute('aria-pressed', String(filter === button));
            });
            applyFilters();
        });
    });
    search?.addEventListener('input', applyFilters);

    const saveProgress = async (card, button) => {
        const error = card.querySelector('.vocab-error');
        const buttons = card.querySelectorAll('.vocab-progress');
        buttons.forEach((item) => { item.disabled = true; });
        error.textContent = '';
        error.classList.add('hidden');

        try {
            const response = await fetch(app.dataset.saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': app.dataset.csrfToken
                },
                body: JSON.stringify({
                    _csrf: app.dataset.csrfToken,
                    vocab_id: Number(card.dataset.vocabId),
                    status: button.dataset.status
                })
            });
            const payload = await response.json();
            if (!response.ok || !payload.ok) {
                throw new Error(payload.error || 'تعذر حفظ تقدم المفردات.');
            }

            const previousStatus = card.dataset.status || 'new';
            const status = payload.data.status;
            card.dataset.status = status;
            const badge = card.querySelector('[data-vocab-status]');
            if (badge) badge.textContent = status === 'known' ? 'أعرفها' : 'للمراجعة';
            buttons.forEach((item) => {
                item.classList.toggle('is-selected', item.dataset.status === status);
            });
            updateSummary(previousStatus, status);
            applyFilters();
        } catch (requestError) {
            error.textContent = requestError instanceof Error ? requestError.message : 'تعذر حفظ تقدم المفردات.';
            error.classList.remove('hidden');
        } finally {
            buttons.forEach((item) => { item.disabled = false; });
        }
    };

    app.querySelectorAll('.vocab-progress').forEach((button) => {
        button.addEventListener('click', () => saveProgress(button.closest('[data-vocab-id]'), button));
    });

    updateSummary();
    applyFilters();
})();
