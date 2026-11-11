(() => {
    const setExpanded = (card, expanded) => {
        const answer = card.querySelector('[data-flashcard-answer]');
        const toggle = card.querySelector('[data-flashcard-toggle]');
        if (!answer || !toggle) return;

        answer.hidden = !expanded;
        toggle.setAttribute('aria-expanded', String(expanded));
        const label = toggle.querySelector('[data-flashcard-label]');
        if (label) {
            label.textContent = expanded ? 'إخفاء المعنى' : 'إظهار المعنى';
        } else {
            toggle.textContent = expanded ? 'إخفاء المعنى' : 'إظهار المعنى';
        }
    };

    document.querySelectorAll('[data-flashcard]').forEach((card) => {
        const toggle = card.querySelector('[data-flashcard-toggle]');
        if (!toggle) return;

        setExpanded(card, false);
        toggle.addEventListener('click', () => {
            setExpanded(card, toggle.getAttribute('aria-expanded') !== 'true');
        });
    });
})();